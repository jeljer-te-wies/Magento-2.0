<?php
/**
 * @category   Reload
 * @package    Reload_Seo
 * @copyright  Copyright (c) 2013-2015 AndCode (http://www.andcode.nl)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Reload_Seo_Helper_Data is the helper class for this module, mostly it contains functions
 * to handle the request with the Reload API.
 */
namespace Reload\Seo\Helper;

use \Magento\Backend\Model\Session;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Locale\Resolver;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\Registry;
use \Magento\Framework\Phrase;
use \Magento\Store\Model\StoreManagerInterface;

use \Reload\Seo\Model\Score;

class Data extends ReloadAbstractHelper
{
    protected $_registry;

    protected $_session;

    protected $_score;

    protected $_resolver;

    protected $_backendHelper;

    public function __construct(Context $context,
                                Session $session,
                                Registry $registry,
                                StoreManagerInterface $storeManager,
                                Score $score,
                                Resolver $resolver,
                                \Magento\Backend\Helper\Data $backendHelper,
                                ManagerInterface $messageManager)
    {
        $this->_registry = $registry;
        $this->_session = $session;
        $this->_score = $score;
        $this->_resolver = $resolver;
        $this->_backendHelper = $backendHelper;
        parent::__construct($context, $storeManager, $messageManager);
    }

    /**
     * Variable for keeping track of the possible fields.
     * @var array
     */
    protected $fields = array(
        'description', 
        'short_description', 
        'meta_description', 
        'meta_keyword', 
        'meta_title', 
        'name',
        'url_key',
        'status',
    );

    /**
     * addScoreUpdateRequest adds a update request to the queue.
     * 
     * @param int       $id    The product or category id.
     * @param string    $type  Either product or category.
     * @param int       $store The store id.
     */
    public function addScoreUpdateRequest($id, $type, $store)
    {
        $requests = $this->getScoreUpdateRequests();

        $requests[$id . '-' . $type . '-' . $store] = array(
            'id' => $id,
            'type' => $type,
            'store' => $store
        );


        $this->_session->setScoreUpdateRequests(json_encode($requests));
    }

    /**
     * removeScoreUpdateRequest removes a update request from the queue and returns it.
     * 
     * @param string $requestKey The request key.
     *
     * @return array The update request.
     */
    public function removeScoreUpdateRequest($requestKey)
    {
        $requests = $this->getScoreUpdateRequests();

        $request = null;
        if(array_key_exists($requestKey, $requests))
        {
            $request = $requests[$requestKey];
            unset($requests[$requestKey]);
        }

        $this->_session->setScoreUpdateRequests(json_encode($requests));
        return $request;
    }

    /**
     * getScoreUpdateRequests gets the score update request queue.
     * 
     * @return array
     */
    public function getScoreUpdateRequests()
    {
        $requests = json_decode($this->_session->getScoreUpdateRequests(), true);
        if(!is_array($requests))
        {
            $requests = array();
        }

        return $requests;
    }

    /**
     * removeItem removes one product or category SEO status.
     * 
     * @param Mage_Catalog_Model_Category|Mage_Catalog_Model_Product $item
     * @return void
     */
    public function removeItem($item)
    {
        if($item instanceof \Magento\Catalog\Model\Category)
        {
            //The item is a category, the sku will be category-<id>
            $sku = 'category-' . $item->getId();

            //Prepare the error for later use.
            $error = new Phrase('Something went wrong while removing the category SEO status.');

            //Obtain the score object for later use.
            $score = $this->_score->loadById($item->getId(), 'category');
            $type = 'category';
        }
        elseif($item instanceof \Magento\Catalog\Model\Product)
        {
            //The item is a product, the sku will be used as is.
            $sku = $item->getSku();

            //Prepare the error for later use.
            $error = new Phrase('Something went wrong while removing the category SEO status.');

            //Obtain the score object for later use.
            $score = $this->_score->loadById($item->getId(), 'product');
            $type = 'product';
        }
        else
        {
            new \Exception('The requested items is not a product nor a category.');
        }

        //Create the url for the update.
        $url = $this->buildUrl('',
            array(
                'key' => $this->scopeConfig->getValue('seo/reload_seo_group/reload_seo_key'),
                'language' => $this->_resolver->getLocale(),
                'type' => $type,
                'framework' => 'magento',
                'product[sku]' => $sku,
                'website' => $this->_storeManager->getStore()->getBaseUrl(),
            )
        );

        //Execute the request.
        $result = $this->executeCurlRequest($url, null, false, true);
        if($result === null || !array_key_exists('sku', $result))
        {
            //Something went wrong, throw the prepared error.
            throw new \Exception($error);
        }

        try
        {
            $score->delete();
        }
        catch(\Exception $ex)
        {
            //Something went wrong, throw the prepared error.
            throw new \Exception($error);
        }
    }

    /**
     * getDataForUpdate prepares the data for a score update of a single item.
     * 
     * @param  Mage_Catalog_Model_Category|Mage_Catalog_Model_Product   $item
     * @param  string                                                   $requestKey
     * 
     * @return array
     */
    public function getDataForUpdate($item, $requestKey, $formKey)
    {
        //Create an array for the post data.
        $data = array();

        if($item instanceof \Magento\Catalog\Model\Product)
        {
            //The item is a product, the sku will be used as is.
            $data['product[sku]'] = $item->getSku();
            $data['product[status]'] = $item->getStatus();
            $data['product[visibility'] = $item->getVisibility();

            //Obtain the score object for later use.
            $score = $this->_score->loadById($item->getId(), 'product');
            $type = 'product';

            if($this->scopeConfig->getValue('seo/reload_seo_group/reload_seo_analyze_images'))
            {
                //Append the image data.
                $data['product[images]'] = array();
                foreach($item->getMediaGalleryImages() as $image)
                {
                    $data['product[images]'][] = array(
                        'url' => $image->getUrl(),
                        'name' => $image->getLabel(),
                    );
                }
            }
        }
        else
        {

            //The item is a category, the sku will be category-<id>
            $data['product[sku]'] = 'category-' . $item->getId();

            //Obtain the score object for later use.
            $score = $this->_score->loadById($item->getId(), 'category');
            $type = 'category';
        }

        if($score->getKeywords() == null && $this->scopeConfig->getValue('seo/reload_seo_group/reload_seo_title_default'));
        {
            $score->generateKeywords($item->getName());
        }

        $data['product[product_id]'] = $type . '-' . $item->getId();
        $data['product[keywords]'] = $score->getKeywords();
        $data['product[synonyms]'] = $score->getSynonyms();
        $data['product[store_id]'] = $item->getStoreId();

        //Obtain the field mapping by the type and loop over each field, obtain the data and store it.
        $fieldMapping = $this->getFieldMappings($type);
        foreach($fieldMapping as $external => $internal)
        {
            $data['product[' . $external . ']'] = $item->getData($internal);
        }

        //Create the url for the update.
        $url = $this->buildUrl('show',
            array(
                'key' => $this->scopeConfig->getValue('seo/reload_seo_group/reload_seo_key'),
                'language' => $this->_resolver->getLocale(),
                'type' => $type,
                'framework' => 'magento',
                'website' => $this->_storeManager->getStore()->getBaseUrl(),
            )
        );

        //Check if the sku is not null
        if($data['product[sku]'] === null)
        {
            $data['product[sku]'] = '0';
        }

        //Add the store data.
        $data['stores'] = $this->collectStores();

        return array(
            'data' => $data,
            'url' => $url,
            'save_url' => $this->_backendHelper->getUrl('reload_seo/seo/saveResult', array('request_key' => $requestKey)),
            'form_key' => $formKey,
            'request_key' => $requestKey
        );
    }

    /**
     * getFieldMappingsCustom creates the custom field mapping.
     * 
     * @param  string $type
     * @return array
     */
    public function getFieldMappingsCustom($type)
    {
        $fieldMapping = array();
        if($type === 'product')
        {
            foreach(explode(',', $this->scopeConfig->getValue('seo/reload_seo_mappings/reload_seo_mapping_custom')) as $custom)
            {
                $fieldMapping[$custom] = $custom;
            }
        }
        return $fieldMapping;
    }

    /**
     * getFieldMappings creates the field mapping.
     * 
     * @param  string $type
     * @return array
     */
    public function getFieldMappings($type)
    {
        $fieldMapping = array();
        if($type === 'product')
        {
            //We want the field mapping for a product, loop over all fields.
            foreach($this->fields as $field)
            {
                //Get the attribute code from the configuration, only add it when it was configured.
                $attributeCode = $this->getFieldAttributeCode($field);
                if($attributeCode !== null)
                {
                    $fieldMapping[$field] = $attributeCode;
                }
            }
        }
        else
        {
            //We want the field mapping for a category, the fields are fixed/hardcoded.
            $fieldMapping['name'] = 'name';
            $fieldMapping['description'] = 'description';
            $fieldMapping['url_key'] = 'url_key';
            $fieldMapping['meta_title'] = 'meta_title';
            $fieldMapping['meta_keyword'] = 'meta_keywords';
            $fieldMapping['meta_description'] = 'meta_description';
        }
        return $fieldMapping;
    }
    /**
     * _registerScore registers the score object for a category or a product.
     *
     * @param  int $referenceId
     * @param  string $type
     * @param  Mage_Catalog_Model_Product|Mage_Catalog_Model_Category $observerObject
     * @return void
     */
    public function _registerScore($referenceId, $type, $observerObject)
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

        if ($this->_registry->registry('seo_score') != null)
        {
            $this->_registry->unregister('seo_score');
        }

        $this->_registry->register('seo_score', $scoreObject);
    }

    /**
     * _afterSave saves the score for a product or a category.
     *
     * @param  int 		$id        The product or category id.
     * @param  string 	$type      Either product or category
     * @param  string  	$postField Either product or general
     *
     * @return void
     */
    public function _afterSave($id, $type, $postField)
    {
        $request = $this->_request;

        $post = $request->getPost($postField);

        try
        {
            if($post != null)
            {
                if(array_key_exists('reload_seo_keywords', $post))
                {
                    $keywords = $post['reload_seo_keywords'];
                }
                else
                {
                    $keywords = '';
                }

                if(array_key_exists('reload_seo_synonyms', $post))
                {
                    $synonyms = $post['reload_seo_synonyms'];
                }
                else
                {
                    $synonyms = '';
                }

                $this->_score->loadById($id, $type)->setKeywords($keywords)->setSynonyms($synonyms)->save();
            }
        }
        catch(\Exception $ex)
        {
            //Hmz.
            $this->_messageManager->addError(__('Something went wrong while updating the ' . $type . ' SEO status.'));
        }

        $storeId = (int) $request->getParam('store');
        $this->addScoreUpdateRequest($id, $type, $storeId);
    }

    /**
     * getFieldAttributeCode loads the configured attribute code from the configuration.
     * 
     * @param  string $field
     * @return string
     */
    protected function getFieldAttributeCode($field)
    {
        $attributeCode = $this->scopeConfig->getValue('seo/reload_seo_mappings/reload_seo_mapping_' . $field);
        if($attributeCode == null)
        {
            return null;
        }
        return $attributeCode;
    }
}
