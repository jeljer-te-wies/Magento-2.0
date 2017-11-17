<?php


namespace Reload\Seo\Block\Adminhtml\Products;

class ReloadConfigBlock extends \Magento\Framework\View\Element\AbstractBlock
{
    protected $_dataHelper;

    protected $_backendHelper;

    protected $_storeManager;

    public function __construct(\Reload\Seo\Helper\Data $dataHelper,
                                \Magento\Backend\Helper\Data $backendHelper,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\Framework\View\Element\Context $context)
    {
        $this->_dataHelper = $dataHelper;
        $this->_backendHelper = $backendHelper;
        $this->_storeManager = $storeManager;

        parent::__construct($context);
    }

    protected function _toHtml()
    {
        $html = '';

        $vars = array(
            //Obtain the API key from the configuration
            'api_key' => $this->_scopeConfig->getValue('seo/reload_seo_group/reload_seo_key'),

            //Create the validate key url.
            'check_url' => $this->_dataHelper->buildUrl('validate_key', array('key' => $this->_scopeConfig->getValue('seo/reload_seo_group/reload_seo_key'), 'website' => $this->_storeManager->getStore()->getBaseUrl())),

            //Create a set with default messages.
            'messages' => array(
                'empty_key' => __("No API key given, please enter your API key in the <a href='%s'>configuration</a>.", $this->_backendHelper->getUrl('adminhtml/system_config/edit/section/reload')),
                'default_invalid_message' => __("The given API key is invalid, please enter a valid API key in the <a href='%s'>configuration</a>.", $this->_backendHelper->getUrl('adminhtml/system_config/edit/section/reload'))
            ),

            //Get the config url.
            'config_url' => $this->_backendHelper->getUrl('adminhtml/system_config/edit/section/reload')
        );

        $html .= '<script type="text/javascript">reloadseo.checkKey(' . json_encode($vars) . ');</script>';

        return $html;
    }
}