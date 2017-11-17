<?php
/**
 * @category   Reload
 * @package    Reload_Seo
 * @copyright  Copyright (c) 2013-2015 AndCode (http://www.andcode.nl)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Reload_Seo_Block_Adminhtml_Products_Renderer is used to render the product grid it's SEO score column
 */
namespace Reload\Seo\Block\Adminhtml\Products;

use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use \Magento\Framework\Phrase;
use \Magento\Framework\DataObject;

class Renderer extends AbstractRenderer
{
	/**
	 * render is called when a row is drawn in the grid.
	 * 
	 * @param  Varien_Object $row
	 * @return string
	 */
	public function render(DataObject $row)
	{
		if($this->getColumn()->getIndex() === 'seo_score')
		{
			$updateKey = $row->getId() . '-product-' . (int) $this->_request->getParam('store');

			//Only render the seo score column.
			if($row->getSeoScore() === null)
			{
				//No SEO score is known yet.
				return '<div class="seo-score-grid ' .$updateKey . '">' . new Phrase('Unknown') . '</div>';
			}

			//Create the score html and return it.
			$score = $row->getSeoScore();
			return '<div class="seo-score-grid ' .$updateKey . '"><div style="background-color: ' . $row->getSeoColor() . '; width: 18px; height: 18px; float: left; border-radius: 100px;"></div>' . $score . '</div>';
		}		 
	}

}