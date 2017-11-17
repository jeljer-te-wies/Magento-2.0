<?php
namespace Reload\Seo\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion())
        {
            if (version_compare($context->getVersion(), '1.3.3') < 0 || version_compare($context->getVersion(), '1.3.4') < 0)
            {
                $setup->run("ALTER TABLE  `{$setup->getTable('reload_seo_score')}` ADD  `synonyms` VARCHAR( 255 ) NOT NULL ;");
            }

            if (version_compare($context->getVersion(), '1.0.0') < 0 || version_compare($context->getVersion(), '1.0.1') < 0)
            {
                $setup->run("ALTER TABLE  `{$setup->getTable('reload_seo_score')}` ADD  `store_id` INT( 11 ) NOT NULL AFTER  `reference_id`;");

                $setup->run("ALTER TABLE `{$setup->getTable('reload_seo_score')}` DROP INDEX `type`;");

                $setup->run("ALTER TABLE  `{$setup->getTable('reload_seo_score')}` ADD UNIQUE `type` (`type` ,`reference_id` ,`store_id`);");
            }
        }

        $setup->endSetup();
    }
}