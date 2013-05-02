<?php

/**
 * SEF module for Joomla!
 * Originally written for Mambo as 404SEF by W. H. Welch.
 *
 * @author      $Author: shumisha $
 * @copyright   Yannick Gaultier - 2007
 * @package     sh404SEF-15
 * @version     $Id: toolbar.sh404sef.php 657 2008-08-08 09:38:49Z silianacom-svn $
 * {shSourceVersionTag: V 1.2.4.x - 2007-09-20}
 */

/** ensure this file is being included by a parent file */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

require_once( JApplicationHelper::getPath( 'toolbar_html' ) );

switch ( $task ) {
  case 'view':
    if (@$_GET['section'] == "config")
    TOOLBAR_sh404sef::_NEW();
    else
    TOOLBAR_sh404sef::_DEFAULT();
    break;
  case 'viewMeta':
    TOOLBAR_sh404sef::_META();
    break;
  case 'showconfig':
  case 'edit':
  case 'editMeta':
  case 'newMetaFromSEF':
  case 'homeAlias':
    TOOLBAR_sh404sef::_EDIT();
    break;
  case 'new':
  case 'newMeta':
    TOOLBAR_sh404sef::_NEW();
    break;
  case 'viewDuplicates':
    TOOLBAR_sh404sef::_DUPLICATES();
    break;
  case 'newHomeMeta':
  case 'newHomeMetaFromSEF':
    TOOLBAR_sh404sef::_EDIT_HOME_META($task);
    break;
  case 'info':
    TOOLBAR_sh404sef::_INFO();
    break;
  default:
    break;
}
?>
