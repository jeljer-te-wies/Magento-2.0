<?php


namespace Reload\Seo\Block\Adminhtml;

use \Magento\Framework\Registry;

class SeoKeyword extends \Magento\Framework\Data\Form\Element\Text
{
    protected $_registry;

    public function __construct(Registry $registry)
    {
        $this->_registry = $registry;

        parent::__construct();

        if ($this->_registry->registry('seo_score') != null)
        {
            $scoreObject = $this->_registry->registry('seo_score');
            $this->addElementValues($scoreObject->getKeyWords());
        }
    }

    public function doShit()
    {
        $value = '';
        if($this->_registry->registry('seo_score') != null)
        {
            //This is an edit action.
            $scoreObject = $this->_registry->registry('seo_score');
            $value = $scoreObject->getKeywords();

            //Update the product or category with the keywords, default keywords and if the defualt flag is set or not.
            $block->getDataObject()->setReloadSeoKeywords($scoreObject->getKeywords());
            $block->getDataObject()->setAttributeDefaultValue('reload_seo_keywords', $scoreObject->getDefaultKeywords());

            if($scoreObject->getKeywords() != null  && $scoreObject->getKeywords() != $scoreObject->getDefaultKeywords())
            {
                $block->getDataObject()->setExistsStoreValueFlag('reload_seo_keywords');
            }
        }

        //Clone the attribute
        $keywordsAttribute = clone $attribute;
        $keywordsAttribute->setAttributeCode('reload_seo_keywords');


        $keywordsElement = new \Magento\Framework\Data\Form\Element\Text(array(
            'label' => 'SEO keyword',
            'html_id' => 'reload_seo_keywords',
            'name' => 'reload_seo_keywords',
            'class' => 'input-text reload-seo-keywords-field',
            'entity_attribute' => $keywordsAttribute,
            'value' => $value
        ));

        $keywordsElement->setForm($block->getElement()->getForm());

        $html .= $block->render($keywordsElement);
    }
}