<?php
/**
 * SEF module for Joomla!
 * Originally written for Mambo as 404SEF by W. H. Welch.
 *
 * @author      $Author: shumisha $
 * @copyright   Yannick Gaultier - 2007
 * @package     sh404SEF-15
 * @version     $Id: install.sh404sef.php 657 2008-08-08 09:38:49Z silianacom-svn $
 *
 */

// Security check to ensure this file is being included by a parent file.
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

global $mainframe;
jimport('joomla.filesystem.file');

$front_live_site = rtrim(str_replace('/administrator', '', JURI::base()), '/');
$database	= & JFactory::getDBO();
// V 1.2.4.t improved upgrade data preservation
// V 1.2.4.q Copy existing config file from /media to current component. Used to recover configuration when upgrading
// V 1.2.4.s check if old file exists before deleting stub config file
$oldConfigFile = JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_'
.str_replace('/','_',str_replace('http://', '', $front_live_site)).'.php';
if (JFile::exists($oldConfigFile)) {
  // update old config files from VALID_MOS check to _JEXEC
  $config = JFile::read($oldConfigFile);
  if ($config && strpos( $config, 'VALID_MOS') !== false) {
    $config = str_replace( 'VALID_MOS', '_JEXEC', $config);
    JFile::write( $oldConfigFile, $config);  // write it back
  }
  // now get back old config
  @unlink(JPATH_ADMINISTRATOR. DS .'config' . DS . 'config.sef.php');
  JFile::copy( $oldConfigFile, JPATH_ADMINISTRATOR. DS .'components'.DS.'com_sh404sef'.DS.'config'.DS.'config.sef.php' );
}
// restore log files
if ($handle = @opendir(JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_logs')) {
  while (false !== ($file = readdir($handle))) {
    if ($file != '.' && $file != '..')
    JFile::copy(JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_logs'.DS.$file,
    JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'logs'.DS.$file);
  }
  closedir($handle);
}
// restore black/white lists
if ($handle = @opendir(JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_security')) {
  while (false !== ($file = readdir($handle))) {
    if ($file != '.' && $file != '..') {
      @unlink(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'security'.DS.$file);
      JFile::copy(JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_security'.DS.$file,
      JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'security'.DS.$file);
    }
  }
  closedir($handle);
}
// restore customized default params
$oldCustomConfigFile = JPATH_ROOT.DS.'media'.DS.'sh404_upgrade_conf_'
.str_replace('/','_',str_replace('http://', '', $front_live_site)).'.custom.php';
if (is_readable($oldCustomConfigFile) && filesize($oldCustomConfigFile) > 1000) {
  // update old config files from VALID_MOS check to _JEXEC
  $config = JFile::read($oldCustomConfigFile);
  if ($config && strpos( $config, 'VALID_MOS') !== false) {
    $config = str_replace( 'VALID_MOS', '_JEXEC', $config);
    JFile::write( $oldCustomConfigFile, $config);  // write it back
  }
  @unlink(JPATH_ADMINISTRATOR. DS .'custom.sef.php');
  $result = JFile::copy( $oldCustomConfigFile, JPATH_ADMINISTRATOR. DS.'components'.DS.'com_sh404sef'.DS.'custom.sef.php' );
}
$sef_config_class = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sh404sef'.DS.'sh404sef.class.php';
// Make sure class was loaded.
if (!class_exists('SEFConfig')) {   // V 1.2.4.T was wrong variable name $SEFConfig_class instead of $sef_config_class
  if (is_readable($sef_config_class)) require_once($sef_config_class);
  else JError::RaiseError( 500, _COM_SEF_NOREAD."( $sef_config_class )<br />"._COM_SEF_CHK_PERMS);
}
$sefConfig = new SEFConfig();

// install system plugin
$r1 = true === JFile::move( JPATH_ADMINISTRATOR. DS.'components'.DS.'com_sh404sef'.DS.'sysplugin'.DS.'shsef.php',
JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'shsef.php');
$r2 = true === JFile::move( JPATH_ADMINISTRATOR. DS.'components'.DS.'com_sh404sef'.DS.'sysplugin'.DS.'shsef.xml',
JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'shsef.xml');
if ($r1 && $r2) {
  $sql="INSERT INTO `#__plugins` ( `name`, `element`, `folder`, `access`, `ordering`, `published`, `iscore`, `client_id`, `checked_out`, `checked_out_time`, `params`) VALUES ('System - sh404sef', 'shsef', 'system', 0, 7, 1, 0, 0, 0, '0000-00-00 00:00:00', '');";
  $database->setQuery( $sql);
  $database->query();
} else {
  if ($r1 === true) JFile::delete(JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'shsef.php');  // don't leave anything behind
  if ($r2 === true) JFile::delete(JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'shsef.xml');
  JError::RaiseWarning( 500, JText::_('Could not install sh404SEF system plugin'));
}

if (file_exists(JPATH_ROOT.DS.'plugins'.DS.'system'.DS.'shsef.php')) {

  // success !
  echo '<div style="text-align: justify;">';
  echo '<h1>sh404SEF installed succesfully! Please read the following</h1>';
  echo 'If it is the first time you use sh404SEF, it has been installed but is <strong>disabled</strong> right now. You must first edit sh404SEF configuration (from the <a href="index.php?option=com_sh404sef" >sh404SEF Components</a> menu item of Joomla backend), <strong>enable it and save</strong> before it will become active. Before you do so, please read the next paragraphs which have important information for you.  If you are upgrading from a previous version of sh404SEF, then all your settings have been preserved, the component is activated and you can start browsing your site frontpage right away.';
  echo '<br /><br />';
  echo '<strong><font color="red">IMPORTANT</font></strong> : sh404SEF can operate under two modes : <strong><font color="red">WITH</font></strong> or <strong><font color="red">WITHOUT .htaccess</font></strong> file. The default setting is now to work <strong>without .htaccess file</strong>. I recommend you use it if you are not familiar with web servers, as it is generally difficult to find the right content for a .htaccess file.<br /><br />';
  echo '<strong>Without .htaccess file</strong> : simply go to sh404SEF configuration screen, review parameters, and save config. You can now browse the frontpage of your site to start generating SEF URL.<br />';
  echo '<strong>With .htaccess</strong> : you must activate this operating mode. To do so, go to sh404SEF configuration, select the Advanced tab, locate the "Rewrite mode" drop-down list and select \'with .htaccess\'. Then Save configuration and answer Ok when prompted to erase URl cache. However, before you can activate sh404SEF, you have to setup a .htaccess file. This file content depends on your hosting setup, so it is nearly impossible to tell you what should be in it. Joomla comes with the most generic .htaccess file. It will probably work right away on your system, or may need adjustments. The Joomla supplied file is called htaccess.txt, is located in the root directory of your site, and must be renamed into .htaccess before it will have any effect. You will find additional information about .htaccess at <a href="http://extensions.siliana.com/en/sh404SEF-and-url-rewriting/.htaccess-files-information.html">extensions.Siliana.com/</a>.<br /><br />';
  echo '<strong><font color="red">IMPORTANT</font><strong>: sh404SEF can build SEF URL for many Joomla components. It does it through a <strong>"plugin" system</strong>, and comes with a dedicated plugin for each of Joomla standard components (Contact, Weblinks, Newsfeed, Content of course,...). It also comes with native plugins for common components such as Community Builder, Fireboard, Virtuemart, Sobi2,... (<a href="http://extensions.siliana.com/en/sh404SEF-and-url-rewriting/list-of-available-plugins-for-sh404SEF-SEF-URL-rewriting-component.html">full list on our web site</a>). sh404SEF can also automatically make use of plugins designed for other SEF components such as OpenSEF or SEF Advanced. Such plugins are often delivered and installed automatically when you install a component. Please note that when using these "foreign" plugins, you may experience compatibility issues.<br />However, Joomla having several hundreds extensions available, not all of them have a plugin to tell sh404SEF how its URL should be built. When it does not have a plugin for a given component, sh404SEF will switch back to Joomla 1.0.x standard SEF URL, similar to mysite.com/component/option,com_sample/task,view/id,23/Itemid,45/. This is normal, and can\'t be otherwise unless someone writes a plugin for this component (your assistance in doing so is very much welcomed! Please post on the support forum if you have written a plugin for a component).<br />';
  echo '<br />';
  echo 'You will also find more documentation, including <a href="http://extensions.siliana.com/en/sh404SEF-and-url-rewriting/How-to-write-a-plugin-for-sh404SEF.html">on how to write plugins for sh404SEF</a> at <a href="http://extensions.siliana.com/">extensions.Siliana.com</a>';
  echo '<br />';

  echo  '<p class="message">Please <strong>read the documentation : it is available on <a href="index.php?option=com_sh404sef&task=info" >sh404SEF main control panel</a></p>';

} else  {
  echo '<strong><font color="red">Sorry, something went wrong while installing sh404SEF on your web site. Please try uninstalling first, then check permissions on your file system, and make sure Joomla can write to the /plugin directory. Or contact your site administrator for assistance. <br>You can also report this on our website at <a href="http://extensions.siliana.com/en/forum" >our support forum.</a></font>';
}


?>
