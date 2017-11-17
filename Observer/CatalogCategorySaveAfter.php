<?php


namespace Reload\Seo\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

use \Reload\Seo\Helper\Data;

class CatalogCategorySaveAfter implements ObserverInterface
{
    protected $_dataHelper;

    public function __construct(Data $dataHelper)
    {
        $this->_dataHelper = $dataHelper;
    }

    public function execute(Observer $observer)
    {
        $this->_dataHelper->_afterSave($observer->getCategory()->getId(), 'category', null);
    }
}