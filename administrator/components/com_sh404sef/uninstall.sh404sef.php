<?php

/**
 * SEF module for Joomla!
 * Originally written for Mambo as 404SEF by W. H. Welch.
 *
 * @author      $Author: shumisha $
 * @copyright   Yannick Gaultier - 2007
 * @package     sh404SEF-15
 * @version     $Id: uninstall.sh404sef.php 657 2008-08-08 09:38:49Z silianacom-svn $
 * {shSourceVersionTag: Version x - 2007-09-20}
 */


defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

global $mainframe;
jimport('joomla.filesystem.file');
$front_live_site = rtrim(str_replace('/administrator', '', JURI::base()), '/');

// V 1.2.4.t improved upgrading
function shDeletetable( $tableName) {
  $database	= & JFactory::getDBO();
  $sql = 'DROP TABLE #__'.$tableName;
  $database->setQuery( $sql);
  $database->query();
}

function shDeleteAllSEFUrl( $kind) {

  $database	= & JFactory::getDBO();
  $sql = 'DELETE FROM #__redirection WHERE ';
  If ($kind == 'Custom')
  $where = '`dateadd` > \'0000-00-00\' and `newurl` != \'\';';
  else
  $where = '`dateadd` = \'0000-00-00\';';
  $database->setQuery($sql.$where);
  $database->query();
}

$database	= & JFactory::getDBO();
// V 1.2.4.t before uninstalling modules, save their settings, if told to do so
$sef_config_class = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'sh404sef.class.php';
// Make sure class was loaded.
if (!class_exists('SEFConfig')) {
  if (is_readable($sef_config_class)) require_once($sef_config_class);
  else
  JError::RaiseError(500, _COM_SEF_NOREAD."( $sef_config_class )<br />"._COM_SEF_CHK_PERMS);
}
$sefConfig = new SEFConfig();
if (!$sefConfig->shKeepStandardURLOnUpgrade && !$sefConfig->shKeepCustomURLOnUpgrade) {
  shDeleteTable('redirection');
  shDeleteTable('sh404sef_aliases');
}
elseif (!$sefConfig->shKeepStandardURLOnUpgrade)
shDeleteAllSEFUrl('Standard');
elseif (!$sefConfig->shKeepCustomURLOnUpgrade) {
  shDeleteAllSEFUrl('Custom');
  shDeleteTable('sh404sef_aliases');
}
if (!$sefConfig->shKeepMetaDataOnUpgrade)
shDeleteTable('sh404SEF_meta');
 
// preserve configuration or not ?
if (!$sefConfig->shKeepConfigOnUpgrade) {
  JFile::delete( JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_'
  .str_replace('/','_',str_replace('http://', '', $front_live_site)).'.php');
  JFile::delete( JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_'
  .str_replace('/','_',str_replace('http://', '', $front_live_site)).'.custom.php');
  if ($handle = opendir(JPATH_ADMINISTRATOR.DS.'logs'.DS)) {
    while (false !== ($file = readdir($handle))) {
      if ($file != '.' && $file != '..')
      JFile::delete(JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_logs'.DS.$file);
    }
    closedir($handle);
  }
  if ($handle = opendir(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'security/')) {
    while (false !== ($file = readdir($handle))) {
      if ($file != '.' && $file != '..')
      JFile::delete(JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_security'.DS.$file);
    }
    closedir($handle);
  }
}
// must move log files out of the way, otherwise administrator/com_sh404sef/logs will not be deleted
// and next installation of com_sh404sef will fail
else { // if we keep config
  // make dest dir
  @mkdir(JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_logs');
  @mkdir(JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_security');
  if ($handle = opendir(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'logs'.DS)) {
    while (false !== ($file = readdir($handle))) {
      if ($file != '.' && $file != '..' && $file != 'index.html' )
      @rename(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'logs'.DS.$file,
      JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_logs'.DS.$file);
    }
    closedir($handle);
  }
  if ($handle = opendir(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'security'.DS)) {
    while (false !== ($file = readdir($handle))) {
      if ($file != '.' && $file != '..' && $file != 'index.html')
      @rename(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'security'.DS.$file,
      JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_security'.DS.$file);
    }
    closedir($handle);
  }
}
// remove system plugin
$database->setQuery( "DELETE FROM `#__plugins` WHERE `element`= 'shsef';");
$database->query();
JFile::delete( JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'shsef.php' );
JFile::delete( JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'shsef.xml' );

echo '<h3>sh404SEF has been succesfully uninstalled. </h3>';
echo '<br />';
if ($sefConfig->shKeepStandardURLOnUpgrade)
echo '- automatically generated SEF url have not been deleted (table #__redirection)<br />';
else
echo '- automatically generated SEF url have been deleted<br />';
echo '<br />';
if ($sefConfig->shKeepCustomURLOnUpgrade)
echo '- custom SEF url and aliases have not been deleted (table #__redirection and sh404sef_aliases)<br />';
else
echo '- custom SEF url and aliases have been deleted<br />';
echo '<br />';
if ($sefConfig->shKeepMetaDataOnUpgrade)
echo '- Custom Title and META data have not been deleted (table #__sh404sef_meta)<br />';
else
echo '- Custom Title and META data have been deleted<br />';
echo '<br />';

