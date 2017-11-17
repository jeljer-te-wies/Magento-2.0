<?php
namespace Reload\Seo\Model\ResourceModel\Score;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Reload\Seo\Model\Score', 'Reload\Seo\Model\ResourceModel\Score');
    }
}