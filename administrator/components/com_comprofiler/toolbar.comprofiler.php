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

global $_CB_framework, $mainframe;
// fix for Mambo, where component is executed in template after modules and toolbar:
global $_CB_Admin_Done, $_CB_database;
if ( ! $_CB_Admin_Done ) {
	if ( defined( 'JPATH_ADMINISTRATOR' ) ) {
		$_CB_joomla_adminpath = JPATH_ADMINISTRATOR;
	} else {
		$_CB_joomla_adminpath = $mainframe->getCfg( 'absolute_path' ) . '/administrator';
	}

	ob_start();
	require_once( $_CB_joomla_adminpath . '/components/com_comprofiler/admin.comprofiler.controller.php' );
	$_CB_Admin_Done		=	ob_get_contents();
	ob_end_clean();
}

// Backend toolbar is now in comprofiler.toolbar.php, except for plugin_menus:

switch ( $task ) {
	case 'pluginmenu':
		$plugin	=	new moscomprofilerPlugin( $_CB_database );
		$result	=	$plugin->load( (int) cbGetParam( $_GET, 'pluginid', -1 ) );
		if ( $result != null ) {
			global $_PLUGINS;
			$pluginMenuToolbarFile	=	$_CB_framework->getCfg( 'absolute_path' ) . '/' . $_PLUGINS->getPluginRelPath( $plugin ) . '/toolbar.' . $plugin->element . '.php';
			if ( file_exists( $pluginMenuToolbarFile ) ) {

				require_once( $mainframe->getPath( 'toolbar_html' ) );

				include_once( $pluginMenuToolbarFile );
				break;
			}
		}
		// done in comprofiler.toolbar.php:		TOOLBAR_usersextras::_DEFAULT_PLUGIN();
		break;

	default:
		break;
}

?>