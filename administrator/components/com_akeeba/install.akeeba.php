<?php
/**
 * @package Akeeba
 * @copyright Copyright (C) 2009 Nicholas K. Dionysopoulos. All rights reserved.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

// Schema modification -- BEGIN

$db =& JFactory::getDBO();
$errors = array();

// Version 3.0 to 3.1 updates (performs autodection before running the commands)
$sql = 'SHOW CREATE TABLE `#__ak_stats`';
$db->setQuery($sql);
$ctableAssoc = $db->loadAssoc();
$ctable = $ctableAssoc[1];
if(!strstr($ctable, '`tag`'))
{
	// Smart schema update - NEW IN 3.1.b3

	if($db->hasUTF())
	{
		$charset = 'CHARSET=utf8';
	}
	else
	{
		$charset = '';
	}

	$sql = <<<ENDSQL
DROP TABLE IF EXISTS `#__ak_stats_bak`;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

	$sql = <<<ENDSQL
CREATE TABLE `#__ak_stats_bak` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `comment` longtext,
  `backupstart` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `backupend` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('run','fail','complete') NOT NULL DEFAULT 'run',
  `origin` varchar(30) NOT NULL DEFAULT 'backend',
  `type` varchar(30) NOT NULL DEFAULT 'full',
  `profile_id` bigint(20) NOT NULL DEFAULT '1',
  `archivename` longtext,
  `absolute_path` longtext,
  `multipart` int(11) NOT NULL DEFAULT '0',
  `tag` varchar(255) DEFAULT NULL,
  `filesexist` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_fullstatus` (`filesexist`,`status`),
  KEY `idx_stale` (`status`,`origin`)
) ENGINE=MyISAM DEFAULT $charset;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

	$sql = <<<ENDSQL
INSERT IGNORE INTO `#__ak_stats_bak`
	(`id`,`description`,`comment`,`backupstart`,`backupend`,`status`,`origin`,`type`,`profile_id`,`archivename`,`absolute_path`,`multipart`)
SELECT
  `id`,`description`,`comment`,`backupstart`,`backupend`,`status`,`origin`,`type`,`profile_id`,`archivename`,`absolute_path`,`multipart`
FROM
  `#__ak_stats`;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

	$sql = <<<ENDSQL
DROP TABLE IF EXISTS `#__ak_stats`;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

	$sql = <<<ENDSQL
CREATE TABLE `#__ak_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `comment` longtext,
  `backupstart` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `backupend` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('run','fail','complete') NOT NULL DEFAULT 'run',
  `origin` varchar(30) NOT NULL DEFAULT 'backend',
  `type` varchar(30) NOT NULL DEFAULT 'full',
  `profile_id` bigint(20) NOT NULL DEFAULT '1',
  `archivename` longtext,
  `absolute_path` longtext,
  `multipart` int(11) NOT NULL DEFAULT '0',
  `tag` varchar(255) DEFAULT NULL,
  `filesexist` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_fullstatus` (`filesexist`,`status`),
  KEY `idx_stale` (`status`,`origin`)
) ENGINE=MyISAM DEFAULT $charset;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

	$sql = <<<ENDSQL
INSERT IGNORE INTO `#__ak_stats` SELECT * FROM `#__ak_stats_bak`;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

	$sql = <<<ENDSQL
DROP TABLE IF EXISTS `#__ak_stats_bak`;
ENDSQL;
	$db->setQuery($sql);
	$status = $db->query();
	if(!$status && ($db->getErrorNum() != 1060)) {
		$errors[] = $db->getErrorMsg(true);
	}

}

// Schema modification -- END

global $mainframe;

if(is_object($mainframe))
{
	global $j15;
	// Joomla! 1.5 will have to load the translation strings
	$j15 = true;
	$jlang =& JFactory::getLanguage();
	$path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_akeeba';
	$jlang->load('com_akeeba.sys', $path, 'en-GB', true);
	$jlang->load('com_akeeba.sys', $path, $jlang->getDefault(), true);
	$jlang->load('com_akeeba.sys', $path, null, true);
} else {
	$j15 = false;
}

function pitext($key)
{
	global $j15;
	$string = JText::_($key);
	if($j15)
	{
		$string = str_replace('"_QQ_"', '"', $string);
	}
	echo $string;
}

function pisprint($key, $param)
{
	global $j15;
	$string = JText::sprintf($key, $param);
	if($j15)
	{
		$string = str_replace('"_QQ_"', '"', $string);
	}
	echo $string;
}

?>
<?php if(!empty($errors)): ?>
<div style="background-color: #900; color: #fff; font-size: large;">
	<h1><?php pitext('COM_AKEEBA_PIMYSQLERR_HEAD'); ?></h1>
	<p><?php pitext('COM_AKEEBA_PIMYSQLERR_BODY1'); ?></p>
	<p><?php pitext('COM_AKEEBA_PIMYSQLERR_BODY2'); ?></p>
	<p style="font-size: normal;">
<?php echo implode("<br/>", $errors); ?>
	</p>
</div>
<?php endif; ?>

<h1><?php pitext('COM_AKEEBA_PIHEADER'); ?></h1>
<h2><?php pitext('COM_AKEEBA_PIWELCOME') ?></h2>
<p>
	<?php pisprint('COM_AKEEBA_PITEXT1','http://www.akeebabackup.com/akeeba-backup-documentation/akeeba-backup-documentation/index.html') ?>
	<?php pisprint('COM_AKEEBA_PITEXT2','http://www.akeebabackup.com/support/forum.html') ?>
</p>
<p>
	<?php pisprint('COM_AKEEBA_PITEXT3',JURI::base().'index.php?option=com_akeeba&view=config') ?>
	<?php pisprint('COM_AKEEBA_PITEXT4',JURI::base().'index.php?option=com_akeeba&view=cpanel') ?>
	<?php pisprint('COM_AKEEBA_PITEXT5',JURI::base().'index.php?option=com_akeeba&view=backup') ?>
	<?php pitext('COM_AKEEBA_PITEXT6') ?>
</p>
<p style="font-size: bigger; margin: 5px; padding: 5px; background-color: #fefeff; color: navy; border: medium solid navy;">
	<?php pitext('COM_AKEEBA_PITEXTTRANSLATIONLINK') ?>
</p>