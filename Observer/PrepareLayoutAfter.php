<?php


namespace Reload\Seo\Observer;

use \Magento\Framework\Phrase;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class PrepareLayoutAfter implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        //Obtain the block which is being prepared.
        $block = $observer->getBlock();
        if($block instanceof \Magento\Catalog\Block\Adminhtml\Product\Grid)
        {
            //If the block is a product grid, we want to add an seo_score column with a custom renderer.
            //Add the column after the entity_id column.
            $block->addColumnAfter('seo_score',
                array(
                    'header' => new Phrase('SEO Score'),
                    'width' => '50px',
                    'index' => 'seo_score',
                    'renderer' => '\Reload\Seo\Block\Adminhtml\Products\Renderer',
                    'align' => 'center',
                    'filter'    => false,
                ), 'entity_id');
        }
    }
}