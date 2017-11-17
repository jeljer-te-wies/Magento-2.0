<?php


namespace Reload\Seo\Block\Adminhtml;

use Magento\Framework\App\ObjectManager;

class SeoProcessUpdates extends \Magento\Framework\View\Element\AbstractBlock
{
    protected $_dataHelper;

    public function __construct(\Magento\Framework\View\Element\Context $context, \Reload\Seo\Helper\Data $dataHelper)
    {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context);
    }

    protected function _toHtml()
    {
        $requests = $this->_dataHelper->getScoreUpdateRequests();
        $requestsWithData = array();

        foreach($requests as $requestKey => $request)
        {
            //Load the category or product.

            $item = ObjectManager::getInstance()->create('Magento\Catalog\Model\\' . ucfirst($request['type']))
                ->setStoreId($request['store'])
                ->load($request['id']);

            //Obtain the data for the update.
            $dom = new \DOMDocument();
            $dom->loadHTML($this->getBlockHtml('formkey'));

            $requestsWithData[] = $this->_dataHelper->getDataForUpdate($item, $requestKey, $dom->getElementsByTagName('input')->item(0)->getAttribute('value'));
        }

        if(count($requestsWithData) > 0)
        {
            $message = __('The SEO scores are being updated.');
            $doneMessage = __('The SEO scores have been updated.');

            //Execute the javascript function to update the scores.
            return '<script type="text/javascript">reloadseo.processUpdates(' . json_encode($requestsWithData) . ', ' . json_encode($message) . ', ' . json_encode($doneMessage) . ');</script>';
        }

        return '';
    }
}