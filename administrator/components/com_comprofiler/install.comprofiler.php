<?php
/**
* Joomla/Mambo Community Builder
* @version $Id: install.comprofiler.php 567 2006-11-19 10:05:00Z beat $
* @package Community Builder
* @subpackage install.comprofiler.php
* @author JoomlaJoe and Beat
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

// Try extending time, as unziping/ftping took already quite some... :
@set_time_limit( 240 );

$memMax			=	trim( @ini_get( 'memory_limit' ) );
if ( $memMax ) {
	$last			=	strtolower( $memMax{strlen( $memMax ) - 1} );
	switch( $last ) {
		case 'g':
			$memMax	*=	1024;
		case 'm':
			$memMax	*=	1024;
		case 'k':
			$memMax	*=	1024;
	}
	if ( $memMax < 16000000 ) {
		@ini_set( 'memory_limit', '16M' );
	}
	if ( $memMax < 32000000 ) {
		@ini_set( 'memory_limit', '32M' );
	}
	if ( $memMax < 48000000 ) {
		@ini_set( 'memory_limit', '48M' );		// DOMIT XML parser can be very memory-hungry on PHP < 5.1.3
	}
}

ignore_user_abort( true );

/**
 * CB framework
 * @global CBframework $_CB_framework
 */
global $_CB_framework;

/** @global string $_CB_adminpath
 *  @global string $_CB_joomla_adminpath
 *  @global array $ueConfig
 */
global $_CB_adminpath, $ueConfig, $mainframe;
if ( defined( 'JPATH_ADMINISTRATOR' ) ) {
	include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );
	$_CB_adminpath		=	JPATH_ADMINISTRATOR . '/components/com_comprofiler/';
} else {
	$_CB_adminpath		=	$mainframe->getCfg( 'absolute_path' ). '/administrator/components/com_comprofiler/';
	include_once( $_CB_adminpath . '/plugin.foundation.php' );
}

$_CB_framework->cbset( '_ui', 2 );	// : we're in 1: frontend, 2: admin back-end

if( $_CB_framework->getCfg( 'debug' ) ) {
	ini_set( 'display_errors', true );
	error_reporting( E_ALL );
}


// Plugins are not yet installed: cbimport( 'language.front' );
cbimport( 'cb.tabs' );
cbimport( 'cb.adminfilesystem' );
cbimport( 'cb.xml.simplexml' );
cbimport( 'cb.dbchecker' );


function cbInstaller_field_exists( $table, $field ) {
	global $_CB_database;

	static $cache	=	array();

	if ( ! isset( $cache[$table] ) ) {
		$tableDesc	=	$_CB_database->getTableFields( array( $table ) );
		if ( isset( $tableDesc[$table] ) && is_array( $tableDesc[$table] ) ) {
			$cache[$table]	=	$tableDesc[$table];
		}
	}
	if ( isset( $cache[$table] ) ) {
		return isset( $cache[$table][$field] );
	}
	return false;
}

function com_install() {
  global $_CB_database, $_CB_framework;

  ob_start();

  # Show installation result to user
  ?>
 <div style="text-align:left;">
  <table width="100%" border="0">
    <tr>
      <td>
	<img src="../components/com_comprofiler/images/smcblogo.gif" />
      </td>
    </tr>
    <tr>
      <td>
    	<br />Copyright 2004 - 2009 MamboJoe/JoomlaJoe, Beat and CB team on joomlapolis.com . This component is released under the GNU/GPL version 2 License and parts under Community Builder Free License. All copyright statements must be kept. Derivate work must prominently duly acknowledge original work and include visible online links. Official site: <a href="http://www.joomlapolis.com">www.joomlapolis.com</a>
    	<br />
      </td>
    </tr>
    <tr>
      <td background="F0F0F0" colspan="2">
        <code>Installation Process:<br />
        <?php

          # Set up new icons for admin menu
          // echo "Start correcting icons in administration backend.<br />";
          if ( checkJversion() >= 1 ) {
	          $_CB_database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/static.png' WHERE admin_menu_link='option=com_comprofiler&task=showLists'");
          } else {
	          $_CB_database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/content.png' WHERE admin_menu_link='option=com_comprofiler&task=showLists'");
          }
          $iconresult[0] = $_CB_database->query();
          $_CB_database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/content.png' WHERE admin_menu_link='option=com_comprofiler&task=showField'");
          $iconresult[1] = $_CB_database->query();
          if ( checkJversion() >= 1 ) {
	          $_CB_database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/article.png' WHERE admin_menu_link='option=com_comprofiler&task=showTab'");
          } else {
	          $_CB_database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/content.png' WHERE admin_menu_link='option=com_comprofiler&task=showTab'");
          }
          $iconresult[2] = $_CB_database->query();
          $_CB_database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/config.png' WHERE admin_menu_link='option=com_comprofiler&task=showconfig'");
          $iconresult[3] = $_CB_database->query();
          if ( checkJversion() >= 1 ) {
	          $_CB_database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/user.png' WHERE admin_menu_link='option=com_comprofiler&task=showusers'");
          } else {
	          $_CB_database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/users.png' WHERE admin_menu_link='option=com_comprofiler&task=showusers'");
          }
          $iconresult[4] = $_CB_database->query();
          if ( checkJversion() >= 1 ) {
			  $_CB_database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/plugin.png' WHERE admin_menu_link='option=com_comprofiler&task=showPlugins'");
          } else {
			  $_CB_database->setQuery("UPDATE #__components SET admin_menu_img='js/ThemeOffice/install.png' WHERE admin_menu_link='option=com_comprofiler&task=showPlugins'");
          }
          $iconresult[5] = $_CB_database->query();
/* Despite numerous abstractions-level, the name of the component isn't taken from SQL but from xml <name> , same field as used to find comprofiler, so we can't display nicely:
          if ( checkJversion() >= 1 ) {
	          $_CB_database->setQuery("UPDATE #__components SET name='Community Builder' WHERE admin_menu_link='option=com_comprofiler'");
              $iconresult[6] = $_CB_database->query();
          }
*/
         foreach ($iconresult as $i=>$icresult) {
            if ($icresult) {
              // echo "<font color='green'>FINISHED:</font> Image of menu entry $i has been corrected.<br />";
            } else {
              echo "<font color='red'>ERROR:</font> Image of administration menu entry $i could not be corrected.<br />";
            }
          }
	 $_CB_database->setQuery("SELECT COUNT(*) FROM #__components WHERE link = 'option=com_comprofiler'");
         $components = $_CB_database->loadresult();
	 IF($components >= 1) {
		$_CB_database->setQuery("SELECT id FROM #__components WHERE link = 'option=com_comprofiler' ORDER BY id DESC LIMIT 1");
         	$comid = (int) $_CB_database->loadresult();
		$_CB_database->setQuery("DELETE FROM #__components WHERE link  = 'option=com_comprofiler' AND id != $comid  ");
         	$_CB_database->query();
		$_CB_database->setQuery("DELETE FROM #__components WHERE #__components.option = 'com_comprofiler' AND parent != $comid AND id != $comid ");
         	$_CB_database->query();
        // update front-end menus component id:
    		$_CB_database->setQuery("UPDATE #__menu SET componentid=" . $comid . " WHERE type = 'component' AND link LIKE '%option=com_comprofiler%'");
         	$_CB_database->query();
              	echo "<font color='green'>Administrator and frontend menus corrected.</font><br />";
	}

	//Manage Database Upgrades
	$MCBUpgrades = array();
	
	//Beta 3 Upgrade
	$MCBUpgrades[0]['test']		=	array( 'default', '#__comprofiler_lists' );
	$MCBUpgrades[0]['updates'][0] = "ALTER TABLE `#__comprofiler_lists`"
					."\n ADD `default` TINYINT( 1 ) DEFAULT '0' NOT NULL,"
					."\n ADD `usergroupids` VARCHAR( 255 ),"
					."\n ADD `sortfields` VARCHAR( 255 ),"
					."\n ADD `ordering` INT( 11 ) DEFAULT '0' NOT NULL AFTER `published`";
	$MCBUpgrades[0]['updates'][1] = "UPDATE #__comprofiler_lists SET `default`=1 WHERE published =1";
	$MCBUpgrades[0]['updates'][2] = "UPDATE #__comprofiler_lists SET usergroupids = '29, 18, 19, 20, 21, 30, 23, 24, 25', sortfields = '`username` ASC'";
	$MCBUpgrades[0]['updates'][3] = "ALTER TABLE `#__comprofiler` ADD `acceptedterms` TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER `bannedreason`";
	$MCBUpgrades[0]['message'] = "1.0 Beta 2 to 1.0 Beta 3";


	//Beta 4 Upgrade
	$MCBUpgrades[1]['test']		=	array( 'firstname', '#__comprofiler' );
	$MCBUpgrades[1]['updates'][0] = "ALTER TABLE #__comprofiler ADD `firstname` VARCHAR( 100 ) AFTER `user_id` ,"
					."\n ADD `middlename` VARCHAR( 100 ) AFTER `firstname` ,"
					."\n ADD `lastname` VARCHAR( 100 ) AFTER `middlename` ";
	$MCBUpgrades[1]['updates'][1] = "ALTER TABLE `#__comprofiler_fields` ADD `readonly` TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER `profile`";
	$MCBUpgrades[1]['updates'][3] = "ALTER TABLE `#__comprofiler_tabs` ADD `width` VARCHAR( 10 ) DEFAULT '.5' NOT NULL AFTER `ordering` ,"
					."\n ADD `enabled` TINYINT( 1 ) DEFAULT '1' NOT NULL AFTER `width` ," 
					."\n ADD `plugin` VARCHAR( 255 ) DEFAULT NULL AFTER `enabled`" ;

	$MCBUpgrades[1]['message'] = "1.0 Beta 3 to 1.0 Beta 4";

	//RC 1 Upgrade
	$MCBUpgrades[2]['test']		=	array( 'fields', '#__comprofiler_tabs' );
	$MCBUpgrades[2]['updates'][0] = "ALTER TABLE #__comprofiler_tabs ADD `plugin_include` VARCHAR( 255 ) AFTER `plugin` ,"
					."\n ADD `fields` TINYINT( 1 ) DEFAULT '1' NOT NULL AFTER `plugin_include` ";
	$MCBUpgrades[2]['updates'][1] = "INSERT INTO `#__comprofiler_tabs` ( `title`, `description`, `ordering`, `width`, `enabled`, `plugin`, `plugin_include`, `fields`, `sys`) VALUES " 
					."\n ( '_UE_CONTACT_INFO_HEADER', '', -4, '1', 1, 'getContactTab', NULL, 1, 1),"
					."\n ( '_UE_AUTHORTAB', '', -3, '1', 0, 'getAuthorTab', NULL, 0, 1),"
					."\n ( '_UE_FORUMTAB', '', -2, '1', 0, 'getForumTab', NULL, 0, 1),"	
					."\n ( '_UE_BLOGTAB', '', -1, '1', 0, 'getBlogTab', NULL, 0, 1);";
	$MCBUpgrades[2]['updates'][2] = "ALTER TABLE `#__comprofiler_lists` ADD `filterfields` VARCHAR( 255 ) AFTER `sortfields`;";
	$MCBUpgrades[2]['message'] = "1.0 Beta 4 to 1.0 RC 1";

	//RC 2 Upgrade
	$MCBUpgrades[3]['test']		=	array( 'description', '#__comprofiler_fields' );
	$MCBUpgrades[3]['updates'][0] = "ALTER TABLE `#__comprofiler_fields` ADD `description` MEDIUMTEXT  NOT NULL default '' AFTER `title` ";
	$MCBUpgrades[3]['updates'][1] = "ALTER TABLE `#__comprofiler_fields` CHANGE `title` `title` VARCHAR( 255 ) NOT NULL";
	$MCBUpgrades[3]['updates'][2] = "INSERT INTO `#__comprofiler_tabs` (`title`, `description`, `ordering`, `width`, `enabled`, `plugin`, `plugin_include`, `fields`, `sys`) VALUES " 
					."\n ( '_UE_CONNECTION', '',99, '1', 0, 'getConnectionTab', NULL, 0, 1);";
	$MCBUpgrades[3]['updates'][3] = "INSERT INTO `#__comprofiler_tabs` (`title`, `description`, `ordering`, `width`, `enabled`, `plugin`, `plugin_include`, `fields`, `sys`) VALUES " 
					."\n ( '_UE_NEWSLETTER_HEADER', '_UE_NEWSLETTER_INTRODCUTION', 99, '1', 0, 'getNewslettersTab', NULL, 0, 1);";
	$MCBUpgrades[3]['updates'][4] = "UPDATE `#__comprofiler_tabs` SET sys=2, enabled=1 WHERE plugin='getContactTab' ";
	$MCBUpgrades[3]['updates'][5] = "ALTER TABLE `#__comprofiler_lists` ADD `useraccessgroupid` INT( 9 ) DEFAULT '18' NOT NULL AFTER `usergroupids` ";
	$MCBUpgrades[3]['message'] = "1.0 RC 1 to 1.0 RC 2 part 1";
	
	$MCBUpgrades[4]['test']		=	array( 'params', '#__comprofiler_tabs' );
	$MCBUpgrades[4]['updates'][0] = "ALTER TABLE `#__comprofiler_tabs` CHANGE `plugin` `pluginclass` VARCHAR( 255 ) DEFAULT NULL , "
					."\n CHANGE `plugin_include` `pluginid` INT( 11 ) DEFAULT NULL ";
	$MCBUpgrades[4]['updates'][1] = "ALTER TABLE `#__comprofiler_tabs` ADD `params` MEDIUMTEXT AFTER `fields` ;";
	$MCBUpgrades[4]['updates'][2] = "ALTER TABLE `#__comprofiler_fields` ADD `pluginid` INT( 11 ) , "
					."\n ADD `params` MEDIUMTEXT; ";
	$MCBUpgrades[4]['updates'][3] = "UPDATE `#__comprofiler_tabs` SET pluginid=1 WHERE pluginclass='getContactTab' ";	
	$MCBUpgrades[4]['updates'][4] = "UPDATE `#__comprofiler_tabs` SET pluginid=1 WHERE pluginclass='getConnectionTab' ";
	$MCBUpgrades[4]['updates'][5] = "UPDATE `#__comprofiler_tabs` SET pluginid=3 WHERE pluginclass='getAuthorTab' ";	
	$MCBUpgrades[4]['updates'][6] = "UPDATE `#__comprofiler_tabs` SET pluginid=4 WHERE pluginclass='getForumTab' ";	
	$MCBUpgrades[4]['updates'][7] = "UPDATE `#__comprofiler_tabs` SET pluginid=5 WHERE pluginclass='getBlogTab' ";
	$MCBUpgrades[4]['updates'][8] = "UPDATE `#__comprofiler_tabs` SET pluginid=6 WHERE pluginclass='getNewslettersTab' ";														
	$MCBUpgrades[4]['message'] = "1.0 RC 1 to 1.0 RC 2 part 2";

	$MCBUpgrades[5]['test']		=	array( 'position', '#__comprofiler_tabs' );
	$MCBUpgrades[5]['updates'][1] = "ALTER TABLE `#__comprofiler_tabs`"
					."\n ADD `position` VARCHAR( 255 ) DEFAULT '' NOT NULL,"
					."\n ADD `displaytype` VARCHAR( 255 ) DEFAULT '' NOT NULL AFTER `sys`";
	$MCBUpgrades[5]['updates'][2] = "UPDATE `#__comprofiler_tabs` SET position='cb_tabmain', displaytype='tab' ";	
	$MCBUpgrades[5]['updates'][3] = "INSERT INTO `#__comprofiler_tabs` (`title`, `description`, `ordering`, `width`, `enabled`, `pluginclass`, `pluginid`, `fields`, `sys`, `position`, `displaytype`) VALUES " 
					."\n ( '_UE_MENU', '', -10, '1', 1, 'getMenuTab', 14, 0, 1, 'cb_head', 'html'),"
					."\n ( '_UE_CONNECTIONPATHS', '', -9, '1', 1, 'getConnectionPathsTab', 2, 0, 1, 'cb_head', 'html'),"
					."\n ( '_UE_PROFILE_PAGE_TITLE', '', -8, '1', 1, 'getPageTitleTab', 1, 0, 1, 'cb_head', 'html'),"
					."\n ( '_UE_PORTRAIT', '', -7, '1', 1, 'getPortraitTab', 1, 0, 1, 'cb_middle', 'html'),"
					."\n ( '_UE_USER_STATUS', '', -6, '.5', 1, 'getStatusTab', 14, 0, 1, 'cb_right', 'html'),"
					."\n ( '_UE_PMSTAB', '', -5, '.5', 0, 'getmypmsproTab', 15, 0, 1, 'cb_right', 'html');";
	$MCBUpgrades[5]['updates'][5] = "UPDATE `#__comprofiler_tabs` SET pluginid=2 WHERE pluginclass='getConnectionTab' ";
	$MCBUpgrades[5]['updates'][6] = "ALTER TABLE `#__comprofiler_members` ADD `reason` MEDIUMTEXT default NULL AFTER `membersince` ";
	$MCBUpgrades[5]['updates'][7] = "UPDATE `#__comprofiler_tabs` SET `pluginclass`=NULL, `pluginid`=NULL WHERE `pluginclass` != 'getContactTab' AND `fields` = 1";
	// this is from build 10 to 11:
	// changed back sys=3 -> 1 for _UE_MENU and _UE_USER_STATUS
	// $MCBUpgrades[5]['updates'][8] = "ALTER TABLE `#__comprofiler_fields` CHANGE `default` `default` MEDIUMTEXT DEFAULT NULL";
	// this last one is only for upgrades from build 8 to 9.
	$MCBUpgrades[5]['message'] = "1.0 RC 1 to 1.0 RC 2 part 3";

	// from 1.0.1 to 1.0.2: (includes RC2 to 1.0):
	$MCBUpgrades[6]['test']		=	array( 'cbactivation', '#__comprofiler' );
	// from RC2 to 1.0 stable:	in fact did it always up to now, since we can alter tables indefinitely.
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_fields` CHANGE `default` `default` MEDIUMTEXT DEFAULT NULL;";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_fields` CHANGE `tabid` `tabid` int(11) DEFAULT NULL;";
	$MCBUpgrades[6]['updates'][] = "UPDATE `#__users` SET usertype='Registered' WHERE usertype='';";	// fix effect of previous bug in CB registration
	// $MCBUpgrades[6]['message'] = "1.0 RC 2 to 1.0 stable";
	// from 1.0.1 to 1.0.2: (includes RC2 to 1.0):
	$MCBUpgrades[6]['updates'][] = "UPDATE `#__comprofiler_fields` SET `table`='#__users' WHERE name='email';";
	$MCBUpgrades[6]['updates'][] = "UPDATE `#__comprofiler_fields` SET `table`='#__users' WHERE name='lastvisitDate';";
	$MCBUpgrades[6]['updates'][] = "UPDATE `#__comprofiler_fields` SET `table`='#__users' WHERE name='registerDate';";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE #__comprofiler ADD `registeripaddr` VARCHAR( 50 ) DEFAULT '' NOT NULL AFTER `lastupdatedate`;";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE #__comprofiler ADD `cbactivation` VARCHAR( 50 ) DEFAULT '' NOT NULL AFTER `registeripaddr`;";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE #__comprofiler ADD `message_last_sent` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `hits`;";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE #__comprofiler ADD `message_number_sent` INT( 11 ) DEFAULT 0 NOT NULL AFTER `message_last_sent`;";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_field_values` ADD INDEX fieldid_ordering (`fieldid`, `ordering` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_fields` ADD INDEX `tabid_pub_prof_order` ( `tabid` , `published` , `profile` , `ordering` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_fields` ADD INDEX `readonly_published_tabid` ( `readonly` , `published` , `tabid` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_fields` ADD INDEX `registration_published_order` ( `registration` , `published` , `ordering` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_members` ADD INDEX `pamr` ( `pending` , `accepted` , `memberid` , `referenceid` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_members` ADD INDEX `aprm` ( `accepted` , `pending` , `referenceid` , `memberid` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_members` ADD INDEX `membrefid` ( `memberid` , `referenceid` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_plugin` ADD INDEX `type_pub_order` ( `type` , `published` , `ordering` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_tabs` ADD INDEX `enabled_position_ordering` ( `enabled` , `position` , `ordering` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_lists` ADD INDEX `pub_ordering` ( `published` , `ordering` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_lists` ADD INDEX `default_published` ( `default` , `published` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_userreports` ADD INDEX `status_user_date` ( `reportedstatus` , `reporteduser` , `reportedondate` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_userreports` ADD INDEX `reportedbyuser_ondate` ( `reportedbyuser` , `reportedondate` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_views` ADD INDEX `lastview` ( `lastview` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler_views` ADD INDEX `profile_id_lastview` (`profile_id`,`lastview`);";
	$MCBUpgrades[6]['updates'][] = "UPDATE `#__comprofiler` SET `user_id`=`id` WHERE 1>0;";	// fix in case something corrupt for unique key
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler` ADD UNIQUE KEY user_id (`user_id`);";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler` ADD INDEX `apprconfbanid` ( `approved` , `confirmed` , `banned` , `id` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler` ADD INDEX `avatappr_apr_conf_ban_avatar` ( `avatarapproved` , `approved` , `confirmed` , `banned` , `avatar` );";
	$MCBUpgrades[6]['updates'][] = "ALTER TABLE `#__comprofiler` ADD INDEX `lastupdatedate` ( `lastupdatedate` );";
	$MCBUpgrades[6]['message'] = "1.0 RC 2, 1.0 and 1.0.1 to 1.0.2";

	// from 1.0.2 to 1.1:
	$MCBUpgrades[7]['test']		=	array( 'ordering_register', '#__comprofiler_tabs' );
	$MCBUpgrades[7]['updates'][] = "ALTER TABLE `#__comprofiler_plugin` ADD `backend_menu` VARCHAR(255) NOT NULL DEFAULT '' AFTER `folder`;";
	$MCBUpgrades[7]['updates'][] = "ALTER TABLE `#__comprofiler_tabs` ADD `ordering_register` int(11) NOT NULL DEFAULT 10 AFTER `ordering`;";
	$MCBUpgrades[7]['updates'][] = "ALTER TABLE `#__comprofiler_tabs` ADD `useraccessgroupid` int(9) DEFAULT -2 NOT NULL AFTER `position`;";
	$MCBUpgrades[7]['updates'][] = "ALTER TABLE `#__comprofiler_tabs` ADD INDEX `orderreg_enabled_pos_order` ( `enabled` , `ordering_register` , `position` , `ordering` );";
	$MCBUpgrades[7]['updates'][] = "ALTER TABLE `#__comprofiler` ADD `unbannedby` int(11) default NULL AFTER `bannedby`;";
	$MCBUpgrades[7]['updates'][] = "ALTER TABLE `#__comprofiler` ADD `unbanneddate` datetime default NULL AFTER `banneddate`;";
	$MCBUpgrades[7]['updates'][] = "ALTER TABLE `#__comprofiler_field_values` CHANGE `fieldtitle` `fieldtitle` VARCHAR(255) NOT NULL DEFAULT '';";
	$MCBUpgrades[7]['message'] = "1.0.2 to 1.1";

	// from 1.1 to 1.2: uses new method...

	//Apply Upgrades
	foreach ($MCBUpgrades AS $MCBUpgrade) {
		//if it fails test then apply upgrade
		if ( ! cbInstaller_field_exists( $MCBUpgrade['test'][1], $MCBUpgrade['test'][0] ) ) {
			foreach( $MCBUpgrade['updates'] as $MCBScript ) {
				$_CB_database->setQuery( $MCBScript );
				if( ! $_CB_database->query() ) {
					//Upgrade failed
					print("<font color=red>".$MCBUpgrade['message']." failed! SQL error:" . $_CB_database->stderr(true)."</font><br />");
					// return;
				}
			}
			//Upgrade was successful
			print "<font color=green>".$MCBUpgrade['message']." Upgrade Applied Successfully.</font><br />";			
		} 
	}

	$sql="SELECT listid FROM #__comprofiler_lists ORDER BY ordering asc, published desc";
	$_CB_database->setQuery($sql);
	$lists = $_CB_database->loadObjectList();
	$order=0;
	if ( $lists ) {
		foreach($lists AS $list) {
			$_CB_database->setQuery("UPDATE #__comprofiler_lists SET ordering = $order WHERE listid='".$list->listid."'");
			$_CB_database->query();
			$order++;
		}
	}

	// fixing the tabid of installs before CB 1.0 RC 2:

	$dbChecker		=	new CBdbChecker( $_CB_database );
	$result			=	$dbChecker->checkCBMandatoryDb( false );
	if ( ! $result ) {
		$dbChecker		=	new CBdbChecker( $_CB_database );
		$result			=	$dbChecker->checkCBMandatoryDb( true, false );

		if ( $result == true ) {
			echo "<p><font color=green>Automatic database fixes of old core tabs and fields applied successfully.</font></p>";
		} elseif ( is_string( $result ) ) {
			echo "<p><font color=red>" . $result . "</font></p>";
		} else {
			echo "<div style='color:red;'>";
			echo "<h3><font color=red>Database fixing errors:</font></h3>";
			$errors		=	$dbChecker->getErrors( false );
			foreach ( $errors as $err ) {
				echo '<div style="font-size:115%">' . $err[0];
				if ( $err[1] ) {
					echo '<div style="font-size:90%">' . $err[1] . '</div>';
				}
				echo '</div>';
			}
			echo "</div>";
		}
		$logs			=	$dbChecker->getLogs( false );
		if ( count( $logs ) > 0 ) {
			echo "<div><a href='#' id='cbdetailsLinkShowOld' onclick=\"document.getElementById('cbdetailsdbcheckOld').style.display='';return false;\">Click to Show details</a></div>";
			echo "<div id='cbdetailsdbcheckOld' style='color:green;display:none;'>";
			foreach ( $logs as $err ) {
				echo '<div style="font-size:100%">' . $err[0];
				if ( $err[1] ) {
					echo '<div style="font-size:90%">' . $err[1] . '</div>';
				}
				echo '</div>';
			}
			echo '</div>';
		}
	}
	// now missing core tabs will be inserted in the new 1.2 upgrader in next step:
	// from CB 1.2 upwards:

	$dbChecker		=	new CBdbChecker( $_CB_database );
	$result			=	$dbChecker->checkDatabase( true, false );
	if ( $result == true ) {
		echo "<p><font color=green>Automatic database upgrade to current version applied successfully.</font></p>";
	} elseif ( is_string( $result ) ) {
		echo "<p><font color=red>" . $result . "</font></p>";
	} else {
		echo "<div style='color:red;'>";
		echo "<h3><font color=red>Database fixing errors:</font></h3>";
		$errors		=	$dbChecker->getErrors( false );
		foreach ( $errors as $err ) {
			echo '<div style="font-size:115%">' . $err[0];
			if ( $err[1] ) {
				echo '<div style="font-size:90%">' . $err[1] . '</div>';
			}
			echo '</div>';
		}
		echo "</div>";
	}
	$logs			=	$dbChecker->getLogs( false );
	if ( count( $logs ) > 0 ) {
		echo "<div><a href='#' id='cbdetailsLinkShow' onclick=\"document.getElementById('cbdetailsdbcheck').style.display='';return false;\">Click to Show details</a></div>";
		echo "<div id='cbdetailsdbcheck' style='color:green;display:none;'>";
		foreach ( $logs as $err ) {
			echo '<div style="font-size:100%">' . $err[0];
			if ( $err[1] ) {
				echo '<div style="font-size:90%">' . $err[1] . '</div>';
			}
			echo '</div>';
		}
		echo '</div>';
	}

	echo "<p>Core CB database upgrades done. If all lines above are in green, database upgrade completed successfully. Otherwise, please report exact errors and queries to forum, and try checking database again in components : community builder : tools : check database.</p>";

        ?>
		</code>
      </td>
    </tr>
    <tr>
	<td>
	<?php

$adminFS			=&	cbAdminFileSystem::getInstance();
$imagesPath			=	$_CB_framework->getCfg( 'absolute_path' ) . "/images";
$cbImages			=	$imagesPath . '/comprofiler';
$cbImagesGallery	=	$cbImages . '/gallery';

if ( $adminFS->isUsingStandardPHP() && ( ! $adminFS->file_exists( $cbImages ) ) && ! $adminFS->is_writable( $_CB_framework->getCfg( 'absolute_path' ) . "/images/" ) ) {
	print "<font color=red>". $imagesPath . "/ is not writable !</font><br />";
} else {
	if ( ! $adminFS->file_exists( $cbImages ) ) {
		if ( $adminFS->mkdir( $cbImages ) ) {
			print "<font color=green>" . $cbImages . "/ Successfully added.</font><br />";
		} else {
			print "<font color=red>" . $cbImages . "/ Failed to be to be created, please do so manually !</font><br />";
		}
	}  else {
		// print "<font color=green>" . $cbImages . "/ already exists.</font><br />";
	}
	if ( ! $adminFS->file_exists( $cbImagesGallery ) ) {
		if ( $adminFS->mkdir( $cbImagesGallery ) ) {
			print "<font color=green>" . $cbImagesGallery ."/ Successfully added.</font><br />";
		} else {
			print "<font color=red>" . $cbImagesGallery . "/ Failed to be to be created, please do so manually !</font><br />";
		}
	}  else {
		// print "<font color=green>" . $cbImagesGallery . "/ already exists.</font><br />";
	}
	if( $adminFS->file_exists( $cbImages ) ) {
		if ( ! is_writable( $cbImages ) ) {
			if( ! $adminFS->chmod( $cbImages, 0777 ) ) {
				if ( ! @chmod( $cbImages, 0777 ) ) {
					print "<font color=red>" . $cbImages . "/ Failed to be chmod'd to 777 please do so manually !</font><br />";
				}
			}
		}
		if( ! is_writable( $cbImages ) ) {
			print "<font color=red>" . $cbImages . "/ is not writable and failed to be chmod'd to 777 please do so manually !</font><br />";
		}
	}
	if ( $adminFS->file_exists( $cbImagesGallery ) ) {
		if( ! is_writable( $cbImagesGallery ) ) {
			if( ! $adminFS->chmod( $cbImagesGallery, 0777 ) ) {
				if ( ! @chmod( $cbImagesGallery, 0777 ) ) {
					print "<font color=red>" . $cbImagesGallery . "/ Failed to be chmod'd to 777 please do so manually !</font><br />";
				}
			}
		}
		if( ! is_writable( $cbImagesGallery ) ) {
			print "<font color=red>" . $cbImagesGallery . "/ is not writable and failed to be chmod'd to 777 please do so manually !</font><br />";
		}
		$galleryFiles = array("airplane.gif"
		,"ball.gif"
		,"butterfly.gif"
		,"car.gif"
		,"dog.gif"
		,"duck.gif"
		,"fish.gif"
		,"frog.gif"
		,"guitar.gif"
		,"kick.gif"
		,"pinkflower.gif"
		,"redflower.gif"
		,"skater.gif"
		,"index.html");
		foreach( $galleryFiles AS $galleryFile ) {
			if ( ! ( file_exists( $cbImagesGallery . '/' . $galleryFile ) && is_readable( $cbImagesGallery . '/' . $galleryFile ) ) ) {
				// try by www: we try it this way, as we can silence errors in php, but not in FTP:
				$result	=	@copy( $_CB_framework->getCfg( 'absolute_path' ) . "/components/com_comprofiler/images/gallery/".$galleryFile, $cbImagesGallery . '/' . $galleryFile );
				if ( ! $result ) {
					// otherwise try by FTP:
					$result		=	$adminFS->copy( $_CB_framework->getCfg( 'absolute_path' ) . "/components/com_comprofiler/images/gallery/".$galleryFile, $cbImagesGallery . '/' . $galleryFile );
				}
				if ( $result ) {
					// print "<font color=green>" . $galleryFile . " Successfully added to the gallery.</font><br />";
				} else {
					print "<font color=red>" . $galleryFile . " Failed to be added to the gallery please do so manually !</font><br />";
				}
			}
		}
	}
}
if ( ! ( $adminFS->file_exists( $cbImages ) && is_writable( $cbImages ) && $adminFS->file_exists( $cbImagesGallery ) ) ) {
		print "<br /><font color=red>Manually do the following:<br /> 1.) create ".$cbImages . "/ directory <br /> 2.) chmod it to 777 <br /> 3.) create ". $cbImagesGallery . "/ <br /> 4.) chmod it to 777 <br />5.) copy " . $_CB_framework->getCfg( 'absolute_path' ) . "/components/com_comprofiler/images/gallery/ and its contents to ". $cbImagesGallery . "/  </font><br />";
}
/*
if (!file_exists($_CB_framework->getCfg( 'absolute_path' ) . "/includes/domit")) {
	print "<font color='red'>".$_CB_framework->getCfg( 'absolute_path' ) . "/includes/domit/ does not exist! This is normal with mambo 4.5.0 and 4.6.1. Community Builder needs this library for handling plugins.<br />  You Must Manually do the following:<br /> 1.) create ".$_CB_framework->getCfg( 'absolute_path' ) . "/includes/domit/ directory <br /> 2.) chmod it to 777 <br /> 3.) copy corresponding content of a mambo 4.5.2 directory.</font><br /><br />\n";
}
*/
if ( ! ( $adminFS->file_exists( $_CB_framework->getCfg( 'absolute_path' ) . "/libraries/pcl") || $adminFS->file_exists($_CB_framework->getCfg( 'absolute_path' ) . '/administrator/includes/pcl' ) ) ) {
	print "<font color='red'>".$_CB_framework->getCfg( 'absolute_path' ) . "/administrator/includes/pcl/ does not exist! This is normal with mambo 4.5.0. Community Builder needs this library for handling plugins.<br />  Manually do the following:<br /> 1.) create ".$_CB_framework->getCfg( 'absolute_path' ) . "/administrator/includes/pcl/ directory <br /> 2.) chmod it to 777 <br /> 3.) copy corresponding content of a mambo 4.5.2 directory.</font><br /><br />\n";
}
?>
      </td>
    </tr>
  </table>
 </div>
  <?php
 		$ret					=	ob_get_contents();
		ob_end_clean();
		$_CB_framework->setUserState( "com_comprofiler_install", htmlspecialchars( htmlspecialchars( $ret ) ) );
?>
<div id="cbInstallNextStep" style="font-weight:bold;font-size:120%;background:#ffffdd;border:2px orange solid;padding:5px;">WAIT PLEASE: DO NOT INTERRUPT INSTALLATION PROCESS: PERFORMING SECOND INSTALLATION STEP: UNCOMPRESSING CORE PLUGINS. If this screen stays for over 2 minutes, <a href="index2.php?option=com_comprofiler&task=finishinstallation">please click here to continue next and last installation step</a>.</div>
<?php
		echo $ret;
// <script type="text/javascript">document.location.href='index2.php?option=com_comprofiler&task=finishinstallation'</script>
								//	Add Javascript to go to step 2:
		$jsStepTwo				=	"	$('form table.adminform').hide();"		// hides other uploads in j 1.5.
								.	"	$('a[href=index2.php?option=com_installer&element=component]').hide();"		// hides Continue in j 1.0 + Mambo
								.	"	$('#cbInstallNextStep').hide().fadeIn('1500', function() { $(this).fadeOut('1000', function() { $(this).fadeIn('1500', function() {"
								//	Get the href of the user profile link:
								.	"\n			window.location = 'index2.php?option=com_comprofiler&task=finishinstallation';"
								.	"\n		} ) } ) } );"
								;

		$_CB_framework->outputCbJQuery( $jsStepTwo );

		echo $_CB_framework->getAllJsPageCodes();

}

?>
