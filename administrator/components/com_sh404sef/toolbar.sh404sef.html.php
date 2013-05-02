<?php
/**
 * SEF module for Joomla!
 * Originally written for Mambo as 404SEF by W. H. Welch.
 *
 * @author      $Author: shumisha $
 * @copyright   Yannick Gaultier - 2007
 * @package     sh404SEF-15
 * @version     $Id: toolbar.sh404sef.html.php 657 2008-08-08 09:38:49Z silianacom-svn $
 * {shSourceVersionTag: Version x - 2007-09-20}
 */
/** ensure this file is being included by a parent file */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// Setup paths.
$sef_config_class = JPATH_ROOT.'/administrator/components/com_sh404sef/sh404sef.class.php';
$sef_config_file  = JPATH_ROOT.'/administrator/components/com_sh404sef/config/config.sef.php';
// Make sure class was loaded.
if (!class_exists('SEFConfig')) {
  if (is_readable($sef_config_class)) require_once($sef_config_class);
  else die(_COM_SEF_NOREAD."( $sef_config_class )<br />"._COM_SEF_CHK_PERMS);
}

// V 1.2.4.t include language file
shIncludeLanguageFile();

class TOOLBAR_sh404sef  {
  function _NEW() {
    JToolBarHelper::save();
    JToolBarHelper::cancel();
  }
  function _EDIT() {
    JToolBarHelper::save();
    JToolBarHelper::cancel();
  }
  function _INFO() {
    JToolBarHelper::back();
  }
  function _DEFAULT() {
    if (!defined('_COM_SEF_NEW_HOME_META')) shIncludeLanguageFile();  // quick fix for mambo 4.6.2
    JToolBarHelper::addNew('new');
    JToolBarHelper::editList();
    JToolBarHelper::deleteList();
    JToolBarHelper::divider();
    JToolBarHelper::addNew('homeAlias', _COM_SEF_HOME_ALIAS);  // V 1.3.1
    JToolBarHelper::divider();
    JToolBarHelper::addNew('newHomeMetaFromSEF', _COM_SEF_NEW_HOME_META);  // V 1.2.4.t
    JToolBarHelper::addNew('newMetaFromSEF', _COM_SEF_NEW_META);
    JToolBarHelper::divider();
    JToolBarHelper::addNew('viewDuplicates', _COM_SEF_MANAGE_DUPLICATES_BUTTON);
  }
  function _META() {
    if (!defined('_COM_SEF_NEW_HOME_META')) shIncludeLanguageFile();  // quick fix for mambo 4.6.2
    JToolBarHelper::addNew('newHomeMeta', _COM_SEF_NEW_HOME_META);  // V 1.2.4.t
    JToolBarHelper::addNew('newMeta', _COM_SEF_NEW_META);
    JToolBarHelper::divider();
    JToolBarHelper::editList();
    JToolBarHelper::deleteList();
  }
  function _DUPLICATES() {
    if (!defined('_COM_SEF_MANAGE_MAKE_MAIN_URL')) shIncludeLanguageFile();  // quick fix for mambo 4.6.2
    JToolBarHelper::addNew('makeMainUrl', _COM_SEF_MANAGE_MAKE_MAIN_URL);
    JToolBarHelper::divider();
    JToolBarHelper::back();
  }
  function _EDIT_HOME_META($task) {
    JToolBarHelper::save();
    $command = $task == 'newHomeMeta' ? 'deleteHomeMeta' : 'deleteHomeMetaFromSEF';
    JToolBarHelper::custom($command , 'delete.png', 'delete_f2.png', JText::_( 'DELETE' ), false);  // V 1.2.4.t
    JToolBarHelper::cancel();
  }

}
?>
