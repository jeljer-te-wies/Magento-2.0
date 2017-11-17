<?php


namespace Reload\Seo\Controller\Adminhtml\Seo;

use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\Backend\App\Action\Context;
use \Magento\Ui\Component\MassAction\Filter;
use \Reload\Seo\Helper\Massaction;

class updateproducts extends \Magento\Backend\App\Action
{
    protected $_massActionHelper;

    protected $_filter;

    protected $_collectionFactory;

    public function __construct(Context $context, Massaction $massActionHelper, Filter $filter, CollectionFactory $collectionFactory)
    {
        $this->_massActionHelper = $massActionHelper;
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    public function execute()
    {

        $collection = $this->_filter->getCollection($this->_collectionFactory->create());

        $productIds = array();

        foreach ($collection as $item) {
            $productIds[] = $item->getEntityId();
        }

        try
        {
            //Call the helper to update the products with the given ids.
            $this->_massActionHelper->updateProducts($productIds);

            //Set the success message.
            $this->messageManager->addSuccessMessage(__('The SEO statusses have been updated.'));
        }
        catch(\Exception $ex)
        {
            //Something went wrong, set the error message.
            $this->messageManager->addErrorMessage($ex->getMessage());
        }
        return $this->_redirect('adminhtml/catalog_product/index');
    }
}