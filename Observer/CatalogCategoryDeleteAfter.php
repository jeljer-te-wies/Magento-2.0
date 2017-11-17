<?php


namespace Reload\Seo\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Backend\Model\Session;
use \Reload\Seo\Helper\Data;

class CatalogCategoryDeleteAfter implements ObserverInterface
{
    protected $dataHelper;

    protected $session;

    protected $_messageManager;

    public function __construct(Data $dataHelper, Session $session, ManagerInterface $messageManager)
    {
        $this->dataHelper = $dataHelper;
        $this->session = $session;
        $this->_messageManager = $messageManager;
    }

    public function execute(Observer $observer)
    {
        try
        {
            //Tell the api to remove the item from the list.
            $this->dataHelper->removeItem($observer->getCategory());
        }
        catch(\Exception $ex)
        {
            //Hmz.
            $this->_messageManager->addError($ex->getMessage());
        }
    }
}