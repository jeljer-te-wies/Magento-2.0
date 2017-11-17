<?php
namespace Reload\Seo\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $setup->run("
            CREATE TABLE IF NOT EXISTS `{$setup->getTable('reload_seo_score')}` (
              `id` bigint(30) unsigned NOT NULL AUTO_INCREMENT,
              `type` varchar(50) NOT NULL,
              `reference_id` bigint(30) unsigned NOT NULL,
              `score` varchar(50) NOT NULL,
              `color` varchar(15) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `type` (`type`,`reference_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;
        ");

        $setup->run("
            CREATE TABLE IF NOT EXISTS `{$setup->getTable('reload_seo_scores_rule')}` (
              `id` bigint(30) unsigned NOT NULL AUTO_INCREMENT,
              `score_id` bigint(30) unsigned NOT NULL,
              `field` varchar(150) NOT NULL,
              `title` text NOT NULL,
              `status` varchar(25) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `score_id` (`score_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
        ");

        $setup->run("ALTER TABLE  `{$setup->getTable('reload_seo_score')}` ADD  `keywords` VARCHAR( 255 ) NOT NULL ;");

        $setup->run("ALTER TABLE `{$setup->getTable('reload_seo_scores_rule')}`
              ADD CONSTRAINT `reload_seo_scores_rule_ibfk_1` FOREIGN KEY (`score_id`) REFERENCES `{$setup->getTable('reload_seo_score')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $setup->run("ALTER TABLE  `{$setup->getTable('reload_seo_score')}` ADD  `store_id` INT( 11 ) NOT NULL AFTER  `reference_id`;");

        $setup->run("ALTER TABLE `{$setup->getTable('reload_seo_score')}` DROP INDEX `type`;");

        $setup->run("ALTER TABLE  `{$setup->getTable('reload_seo_score')}` ADD UNIQUE `type` (`type` ,`reference_id` ,`store_id`);");

        $setup->run("ALTER TABLE  `{$setup->getTable('reload_seo_score')}` ADD  `synonyms` VARCHAR( 255 ) NOT NULL ;");

        $setup->endSetup();
    }
}