<?php
namespace Reload\Seo\Model\ResourceModel\Scores\Rule;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Reload\Seo\Model\Scores\Rule', 'Reload\Seo\Model\ResourceModel\Scores\Rule');
    }
}