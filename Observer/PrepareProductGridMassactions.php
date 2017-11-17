<?php


namespace Reload\Seo\Observer;

use \Magento\Framework\Phrase;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class PrepareProductGridMassactions implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        //Obtain the block in which the massactions are being prepared.
        $block = $observer->getBlock();
        if($block instanceof \Magento\Catalog\Block\Adminhtml\Product\Grid)
        {
            //If the block is an product grid obtain the massactions grid.
            $massactions = $block->getMassactionBlock();
            if($massactions != null)
            {
                //Add a mass action for updating the seo scores.
                $massactions->addItem(
                    'mass_update_seo',
                    [
                        'label' => __('Update SEO statusses'),
                        'url'   => $block->getUrl('reload_seo/seo/updateproducts', array('_current'=>true))
                    ]
                );
            }
        }
    }
}