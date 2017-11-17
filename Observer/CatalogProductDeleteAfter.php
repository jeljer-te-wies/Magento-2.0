<?php


namespace Reload\Seo\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Backend\Model\Session;

use \Reload\Seo\Helper\Data;

class CatalogProductDeleteAfter implements ObserverInterface
{
    protected $_session;

    protected $_dataHelper;

    protected $_messageManager;

    public function __construct(Data $dataHelper, Session $session, ManagerInterface $messageManager)
    {
        $this->_dataHelper = $dataHelper;
        $this->_session = $session;
        $this->_messageManager = $messageManager;
    }

    public function execute(Observer $observer)
    {
        try
        {
            //Tell the api to remove the item from the list.
            $this->_dataHelper->removeItem($observer->getProduct());
        }
        catch(\Exception $ex)
        {
            //Hmz.
            $this->_messageManager->addError($ex->getMessage());
        }
    }
}