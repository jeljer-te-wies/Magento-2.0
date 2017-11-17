<?php
/**
 * @category   Reload
 * @package    Reload_Seo
 * @copyright  Copyright (c) 2013-2015 AndCode (http://www.andcode.nl)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Reload\Seo\Model\Scores;

use \Magento\Framework\Model\AbstractModel;

class Rule extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Reload\Seo\Model\ResourceModel\Scores\Rule');
    }
}