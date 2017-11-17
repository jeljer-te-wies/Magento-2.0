<?php

namespace Reload\Seo\Ui\DataProvider\Product\Form\Modifier;

use \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use \Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayManager;

class SeoFields extends AbstractModifier
{
    protected $_locator;

    protected $_arrayManager;

    protected $_registry;

    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        Registry $registry
    )
    {
        $this->_locator = $locator;
        $this->_arrayManager = $arrayManager;
        $this->_registry = $registry;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $keyWordsValue = '';
        $synonymsValue = '';

        if($this->_registry->registry('seo_score') != null)
        {
            //This is an edit action.
            $scoreObject = $this->_registry->registry('seo_score');
            $keyWordsValue = $scoreObject->getKeywords();
            $synonymsValue = $scoreObject->getSynonyms();

            //Update the product or category with the keywords, default keywords and if the default flag is set or not.
            $product = $this->_locator->getProduct();
            $product->setReloadSeoKeyWords($scoreObject->getKeyWords());
            $product->setAttributeDefaulValue('reload_seo_keywords', $scoreObject->getDefaultKeywords());

            $product->setReloadSeoSynonyms($scoreObject->getSynonyms());
            $product->setAttributeDefaultValue('reload_seo_synonyms', $scoreObject->getDefaultSynonyms());

            if($scoreObject->getKeywords() != null  && $scoreObject->getKeywords() != $scoreObject->getDefaultKeywords())
            {
                $product->setExistsStoreValueFlag('reload_seo_keywords');
            }

            if($scoreObject->getSynonyms() != null  && $scoreObject->getSynonyms() != $scoreObject->getDefaultSynonyms())
            {
                $product->setExistsStoreValueFlag('reload_seo_synonyms');
            }
        }

        $meta['product-details']['children']['reload_seo_keywords']['arguments']['data']['config']['value'] = $keyWordsValue;
        $meta['product-details']['children']['reload_seo_synonyms']['arguments']['data']['config']['value'] = $synonymsValue;

        return $meta;
    }
}