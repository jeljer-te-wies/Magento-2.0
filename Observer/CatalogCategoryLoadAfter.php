<?php


namespace Reload\Seo\Observer;

use \Magento\Framework\Registry;
use \Magento\Framework\App\Request\Http;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

use Reload\Seo\Model\Score;

class CatalogCategoryLoadAfter implements ObserverInterface
{
    protected $_request;

    protected $_registry;

    protected $_score;

    public function __construct(Http $request, Registry $registry, Score $score)
    {
        $this->_request = $request;
        $this->_registry = $registry;
        $this->_score = $score;
    }

    public function execute(Observer $observer)
    {
        if($this->_request->getControllerName() != 'catalog_product')
        {
            $this->_registerScore($observer->getCategory()->getId(), 'category', $observer->getCategory());
        }
    }

    /**
     * _registerScore registers the score object for a category or a product.
     *
     * @param  int $referenceId
     * @param  string $type
     * @param  Mage_Catalog_Model_Product|Mage_Catalog_Model_Category $observerObject
     * @return void
     */
    protected function _registerScore($referenceId, $type, $observerObject)
    {
        //If the reference === null, we want to load the 0 object.
        if($referenceId === null)
        {
            $referenceId = 0;
        }

        //Load the score from the database where the reference_id and type matches.
        $scoreObject = $this->_score->loadById($referenceId, $type);

        if($scoreObject == null)
        {
            $scoreObject = $this->_score;
        }

        $observerObject->setScoreObject($scoreObject);
        $observerObject->setReloadSeoKeywords($scoreObject->getKeywords());
        $observerObject->setReloadSeoSynonyms($scoreObject->getSynonyms());
        $observerObject->setAttributeDefaultValue('reload_seo_keywords', $scoreObject->getDefaultKeywords());

        if($scoreObject->getKeywords() != null  && $scoreObject->getKeywords() != $scoreObject->getDefaultKeywords())
        {
            $observerObject->setExistsStoreValueFlag('reload_seo_keywords');
        }

        if($scoreObject->getSynonyms() != null && $scoreObject->getSynonyms() != $scoreObject->getDefaultSynonyms())
        {
            $observerObject->setExistsStoreValueFlag('reload_seo_synonyms');
        }

        if($this->_registry->registry('seo_score') != null)
        {
            $this->_registry->unregister('seo_score');
        }

        $this->_registry->register('seo_score', $scoreObject);
    }

}