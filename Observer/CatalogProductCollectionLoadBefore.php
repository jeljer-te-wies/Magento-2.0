<?php


namespace Reload\Seo\Observer;

use \Magento\Framework\App\Request\Http;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\Phrase;
use \Magento\Backend\Model\Session;

use \Reload\Seo\Model\Score;

class CatalogProductCollectionLoadBefore implements ObserverInterface
{
    protected $_session;

    protected $_score;

    protected $_request;

    protected $_messageManager;

    public function __construct(Session $session, Score $score, Http $request, ManagerInterface $messageManager)
    {
        $this->_session = $session;
        $this->_score = $score;
        $this->_request = $request;
        $this->_messageManager = $messageManager;
    }

    public function execute(Observer $observer)
    {
        try
        {
            $storeId = (int) $this->_request->getParam('store');

            //Obtain the collection from the observer.
            $collection = $observer->getCollection();

            //Add an left join to load the scores and colors from the scores table also load the product when the
            //score object does not exist.
            $collection = $collection->getSelect()->joinLeft(
                array(
                    'scores' => $this->_score->getResource()->getTable('reload_seo_score')
                ),
                "e.entity_id = scores.reference_id AND scores.type = 'product' AND scores.store_id = '" . $storeId . "'",
                array(
                    'seo_score' => 'scores.score',
                    'seo_color' => 'scores.color'
                )
            );

            //Obtain the session to get the sorting field and the sorting direction.
            $session =  $this->_session;

            if($session->getData('productGridsort') === 'seo_score' && $session->getData('productGriddir') != null)
            {
                //The user wants to sort by the score, we need to handle this ourselves.
                $collection = $collection->order('scores.score '. strtoupper($session->getData('productGriddir')));
            }

            //Set the collection in the observer so it gets used.
            $observer->setCollection($collection);
        }
        catch(\Exception $ex)
        {
            //Hmzzz
            $this->_messageManager->addError(new Phrase('Something went wrong while loading the product SEO statusses.'));
        }
    }
}