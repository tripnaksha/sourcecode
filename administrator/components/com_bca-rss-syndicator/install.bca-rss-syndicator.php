<?php

// Security check to ensure this file is being included by a parent file.
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

$database	= & JFactory::getDBO();

$database->setQuery("CREATE TABLE IF NOT EXISTS `#__bcarsssyndicator` 
(
	`id` tinyint(4) NOT NULL auto_increment, 
	`msg` varchar(100) default NULL,			  		
	`defaultType` varchar(4) default NULL, 
	`count` varchar(4) default NULL, 
	`orderby` varchar(10) default NULL,
	`numWords` tinyint(4) unsigned default NULL, 
	`cache` smallint(9) default NULL, 
	`imgUrl` varchar(100) default NULL,
	`renderAuthorFormat` varchar(10) default 'NAME', 
	`renderHTML` tinyint(1) default '1', 
	`FPItemsOnly` tinyint(1) default '0',
	`description` text default NULL, PRIMARY KEY  (`id`) 
) TYPE=MyISAM;");

$database->query();

$database->setQuery("INSERT IGNORE INTO `#__bcarsssyndicator` (`id`, `msg`, `defaultType`, `count`, `orderby`, `numWords`, `cache`, `imgUrl`, `renderHTML`, `FPItemsOnly`) VALUES (1,'Get the latest news direct to your desktop','2.0','10','rdate',0,3600,'',1, 0);");
$database->query();

//xipat - vh Feb 09 2009: Change sql query, remove columns: feed_catsInTitle, msg_sectcat; add column: msg_exitems
$database->setQuery("CREATE TABLE IF NOT EXISTS `#__bcarsssyndicator_feeds` 
(
	`id` tinyint(4) NOT NULL auto_increment, 
	`feed_name` varchar(30) default NULL,
	`feed_description` text default NULL, `feed_type` varchar(10) default NULL, 
	`feed_cache` smallint(9) default NULL,
	`feed_imgUrl` varchar(100) default NULL, 
	`feed_button` varchar(100) default NULL, 
	`feed_renderAuthorFormat` varchar(10) default 'NAME',
  	`feed_renderHTML` tinyint(1) default '0',
	`feed_renderImages` INT(1) NOT NULL, 
	`msg_count` varchar(4) default NULL, 
	`msg_orderby` varchar(10) default NULL,
	`msg_numWords` tinyint(4) unsigned default NULL, 
	`msg_FPItemsOnly` tinyint(1) default '1', 
	`msg_sectlist` varchar(50) default NULL, 
	`msg_excatlist` varchar(100) default NULL, 
	`msg_fulltext` tinyint(1) default NULL, 
	`msg_exitems` varchar(250) default NULL,
	`published` tinyint(1) default NULL, PRIMARY KEY  (`id`) 
) TYPE=MyISAM;");
$database->query();
//xipat - vh Oct 28 2008
$database->setQuery("ALTER IGNORE TABLE `#__bcarsssyndicator_feeds`  DROP COLUMN `feed_catsInTitle`;");
$database->query();
$database->setQuery("ALTER IGNORE TABLE `#__bcarsssyndicator_feeds` CHANGE COLUMN `msg_sectcat` `msg_exitems` varchar(250) default NULL;");
$database->query();	
?>
<img src="<?php echo JURI::root(); ?>administrator/components/com_bca-rss-syndicator/assets/images/bca-rss.jpg" alt="Breast Cancer Awareness RSS Syndicator" title="Breast Cancer Awareness RSS Syndicator" /><br />
The Breast Cancer Awareness RSS Syndicator has been succesfully installed.

<p><a href="http://www.bodyhealthdebate.co.uk/breast-cancer-awareness-rss-syndicator-joomla" target="_blank">The Breast Cancer Awareness RSS Syndicator</a> was developed to promote <a href="http://www.bodyhealthdebate.co.uk/" target="_blank">breast cancer awareness</a> in the Joomla community.</p>

<p><a href="http://www.bodyhealthdebate.co.uk/" target="_blank">Click here to find out more about breast cancer awareness</a></p>

