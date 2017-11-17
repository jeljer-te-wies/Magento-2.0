<?php
/**
 * @category   Reload
 * @package    Reload_Seo
 * @copyright  Copyright (c) 2013-2015 AndCode (http://www.andcode.nl)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Reload_Seo_Block_Adminhtml_Seo shows the actual seo score and rules for the current product or category.
 */
namespace Reload\Seo\Block\Adminhtml;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Locale\Resolver;
use \Magento\Framework\Phrase;
use \Magento\Framework\Registry;
use \Magento\Store\Model\App\Emulation;

use \Reload\Seo\Helper\Data;
use \Reload\Seo\Model\Score;

class Seo extends \Magento\Backend\Block\Template
{
	/**
	 * Variable to keep track if the score has been loaded from the database.
	 * @var boolean
	 */
	protected $_scoreLoaded = false;

	/**
	 * Variable to keep track of the loaded score model.
	 * @var Reload_Seo_Model_Score
	 */
	protected $_score;

    protected $_template = 'Reload_Seo/template/reload_seo/seo';

	protected $_registry;

	protected $_scoreObject;

	protected $_emulation;

	public $_dataHelper;

	public $_resolver;

	/**
	 * Basic constructor.
	 */
	public function __construct(Context $context, Registry $registry, Score $score, Emulation $emulation, Data $dataHelper, Resolver $resolver)
	{
		//Set the template for when this block is used through the observers or through ajax.
		$this->_registry = $registry;
		$this->_scoreObject = $score;
		$this->_emulation = $emulation;
		$this->_dataHelper = $dataHelper;
		$this->_resolver = $resolver;

		parent::__construct($context);
	}

	/**
	 * isProductView returns a boolean which indicates whether this is a product view or an category view.
	 * 
	 * @return boolean
	 */
	public function isProductView()
	{
		return (bool)$this->getIsProductView();
	}

	/**
	 * getScore loads the score model from the database linked to the current product or category.
	 * 
	 * @return Reload_Seo_Model_Score|null
	 */
	public function getScore()
	{
		//Check if the score has already been loaded.
		if(!$this->_scoreLoaded)
		{
			if($this->isProductView())
			{
				//This is an product view, obtain the product id.
                $referenceId = $this->_registry->registry('current_product')->getId();
				$type = 'product';
			}
			else
			{
				//This is an category view, obtain the category id.
                $referenceId = $this->_registry->registry('category')->getId();
				$type = 'category';
			}

			if($this->_registry->registry('seo_score') != null && $this->_registry->registry('seo_score')->getId() == $referenceId)
			{
			    $this->_score = $this->_registry->registry('seo_score');
			}
			else
			{
				//If the reference === null, we want to load the 0 object.
				if($referenceId === null)
				{
					$referenceId = 0;
				}

				//Load the score from the database where the reference_id and type matches.
				$this->_score = $this->_scoreObject->loadById($referenceId, $type);

				if($this->_score == null || $this->_score->getReferenceId() != $referenceId)
				{
					//No score object was found.
					$this->_score = null;
				}
			}
			
			$this->_scoreLoaded = true;
		}
		return $this->_score;
	}

	public function getBaseShopUrl()
	{
		if($this->getIsProductView())
		{
		    $currentProduct = $this->_registry->registry('current_product');
		    $storeId = $currentProduct->getStoreId();
		    $productId = $currentProduct->getId();

			$initialEnvironmentInfo = $this->_emulation->startEnvironmentEmulation($storeId);

            $productUrl = $this->_storeManager->getStore()->getBaseUrl(); //Mage::getModel('catalog/product')->load($productId)->getProductUrl();

            $this->_emulation->stopEnvironmentEmulation($initialEnvironmentInfo);

			return $productUrl;
		}
		else
		{
		    $storeId = $this->_registry->registry('category')->getStoreId();
		    $catId = $this->_registry->registry('category')->getId();

			$initialEnvironmentInfo = $this->_emulation->startEnvironmentEmulation($storeId);

			$catUrl = $this->_storeManager->getStore()->getBaseUrl(); //Mage::getModel('catalog/category')->load($catId)->getUrl();

            $this->_emulation->stopEnvironmentEmulation($initialEnvironmentInfo);

			return $catUrl;
		}
		return null;
	}

	public function getUpdateRequestKey()
	{
		if($this->isProductView())
		{
			//This is an product view, obtain the product id.
            $referenceId = $this->_registry->registry('current_product')->getId();
			$type = 'product';
		}
		else
		{
			//This is an category view, obtain the category id.
            $referenceId = $this->_registry->registry('category')->getId();
			$type = 'category';
		}

		$storeId = (int) $this->getStoreId();
		return $referenceId . '-' . $type . '-' . $storeId;
	}

	public function getStoreId()
	{
		if($this->isProductView())
		{
		    return $this->_registry->registry('current_product')->getStoreId();
		}
		else
		{
		    return $this->_registry->registry('category')->getStoreId();
		}
	}

    public function getScopeConfig()
    {
        return $this->_scopeConfig;
	}

    public function getApiKey()
    {
        return $this->_scopeConfig->getValue('seo/reload_seo_group/reload_seo_key');
	}

    public function getStoreManager()
    {
        return $this->_storeManager;
	}

	public function getMetaDescription()
    {
        if ($this->getScore()->getType() === 'product') {
            return $this->_registry->registry('product')->getMetaDescription();
        }

        return $this->_registry->registry('category')->getMetaDescription();
    }

    public function getMetaTitle()
    {
        if ($this->getScore()->getType() === 'product') {
            return $this->_registry->registry('product')->getMetaTitle();
        }
        return $this->_registry->registry('category')->getMetaTitle();
    }
}