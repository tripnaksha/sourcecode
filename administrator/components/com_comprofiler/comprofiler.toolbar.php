<?php
/**
* Joomla/Mambo Community Builder : User toolbar handler
* @version $Id: toolbar.comprofiler.php 41 2006-01-11 23:36:58Z beat $
* @package Community Builder
* @subpackage toolbar.comprofiler.php
* @author JoomlaJoe and Beat
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_CB_framework;

require_once( $mainframe->getPath( 'toolbar_html' ) );

global $_CB_Backend_Menu, $_CB_Backend_task;

switch ( $_CB_Backend_task ) {

	case "edit":
		TOOLBAR_usersextras::_EDIT();
		break;

	case "new":
		TOOLBAR_usersextras::_NEW();
		break;

	case "showconfig":
		TOOLBAR_usersextras::_EDIT_CONFIG();
		break;
	case "editTab":
		TOOLBAR_usersextras::_EDIT_TAB();
		break;

	case "newTab":
		TOOLBAR_usersextras::_NEW_TAB();
		break;

	case "showTab":
		TOOLBAR_usersextras::_DEFAULT_TAB();
		break;
	case "editField":
	case "reloadField":
		TOOLBAR_usersextras::_EDIT_FIELD();
		break;

	case "newField":
		TOOLBAR_usersextras::_NEW_FIELD();
		break;

	case "showField":
		TOOLBAR_usersextras::_DEFAULT_FIELD();
		break;
	case "editList":
		TOOLBAR_usersextras::_EDIT_LIST();
		break;

	case "newList":
		TOOLBAR_usersextras::_NEW_LIST();
		break;

	case "showLists":
		TOOLBAR_usersextras::_DEFAULT_LIST();
		break;
	case "showusers":
		TOOLBAR_usersextras::_DEFAULT();
		break;
	case "tools":
		//TOOLBAR_usersextras::_DEFAULT_LIST();
		break;
		
	case 'newPlugin':
	case 'editPlugin':
		if ( isset( $_CB_Backend_Menu->mode ) ) {
			if ( isset( $_CB_Backend_Menu->menuItems ) && $_CB_Backend_Menu->menuItems ) {
				TOOLBAR_usersextras::_PLUGIN_MENU( $_CB_Backend_Menu->menuItems );
			} elseif ( $_CB_Backend_Menu->mode == 'show' ) {
				TOOLBAR_usersextras::_PLUGIN_ACTION_SHOW();
			} elseif ( $_CB_Backend_Menu->mode == 'edit' ) {
				TOOLBAR_usersextras::_PLUGIN_ACTION_EDIT();
			}
		} else {
			TOOLBAR_usersextras::_EDIT_PLUGIN();
		}
		break;

	case 'pluginmenu':
		global $_CB_database;
		$plugin	=	new moscomprofilerPlugin( $_CB_database );
		$result	=	$plugin->load( (int) cbGetParam( $_GET, 'pluginid', -1 ) );
		if ( $result != null ) {
			global $_PLUGINS;
			$pluginMenuToolbarFile	=	$_CB_framework->getCfg( 'absolute_path' ) . '/' . $_PLUGINS->getPluginRelPath( $plugin ) . '/toolbar.' . $plugin->element . '.php';
			if ( file_exists( $pluginMenuToolbarFile ) ) {
				// done in toolbar.comprofiler.php :	include_once( $pluginMenuToolbarFile );
				break;
			}
		}
		TOOLBAR_usersextras::_DEFAULT_PLUGIN_MENU();
		break;

	case 'savePlugin':
	case 'applyPlugin':		
	case 'deletePlugin':		
	case 'cancelPlugin':		
	case 'publishPlugin':
	case 'unpublishPlugin':		
	case 'orderupPlugin':
	case 'orderdownPlugin':
	case 'accesspublic':
	case 'accessregistered':
	case 'accessspecial':		
	case 'savepluginorder':	
	case 'showPlugins':
	case 'pluginmenu':
		TOOLBAR_usersextras::_DEFAULT_PLUGIN();
		break;
/*
	default:
		TOOLBAR_usersextras::_DEFAULT();
		break;
*/
}
?>