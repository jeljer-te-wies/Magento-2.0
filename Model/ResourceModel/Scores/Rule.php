<?php
namespace Reload\Seo\Model\ResourceModel\Scores;

class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('reload_seo_scores_rule', 'id');
    }
}