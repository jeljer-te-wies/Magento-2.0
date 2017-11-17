<?php


namespace Reload\Seo\Observer;

use \Magento\Framework\App\Request\Http;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

use \Reload\Seo\Helper\Data;

class CatalogProductLoadAfter implements ObserverInterface
{
    protected $_request;

    public function __construct(Http $request, Data $dataHelper)
    {
        $this->_request = $request;
        $this->_dataHelper = $dataHelper;
    }

    public function execute(Observer $observer)
    {

        if($this->_request->getControllerName() != 'catalog_category')
        {
            $this->_dataHelper->_registerScore($observer->getProduct()->getId(), 'product', $observer->getProduct());
        }
    }
}