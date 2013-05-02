<?php
/**
* Joomla/Mambo Community Builder
* @version $Id: admin.comprofiler.php 610 2006-12-13 17:33:44Z beat $
* @package Community Builder
* @subpackage admin.comprofiler.php
* @author JoomlaJoe and Beat, database check function by Nick
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

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
/**
 * CB framework
 * @global CBframework $_CB_framework
 */
global $_CB_framework;
global $mainframe, $_CB_Admin_Done;

require_once( $mainframe->getPath( 'admin_html' ) );
/** @global string $_CB_adminpath
 *  @global array $ueConfig
 */
global $_CB_adminpath, $ueConfig;
if ( defined( 'JPATH_ADMINISTRATOR' ) ) {
	include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );
	$_CB_adminpath		=	JPATH_ADMINISTRATOR . '/components/com_comprofiler';
} else {
	$_CB_adminpath		=	$mainframe->getCfg( 'absolute_path' ). '/administrator/components/com_comprofiler';
	include_once( $_CB_adminpath . '/plugin.foundation.php' );
}

$_CB_framework->cbset( '_ui', 2 );	// : we're in 1: frontend, 2: admin back-end

if($_CB_framework->getCfg( 'debug' )) {
	ini_set( 'display_errors', true );
	error_reporting( E_ALL );
}

cbimport( 'language.front' );
cbimport( 'cb.tabs' );
cbimport( 'cb.imgtoolbox' );

if ( class_exists( 'JFactory' ) ) {	// Joomla 1.5 : for string WARNREG_EMAIL_INUSE used in error js popup.
	$lang			=&	JFactory::getLanguage();
	$lang->load( "com_users" );
}

// backend only:
cbimport( 'cb.adminfilesystem' );
cbimport( 'cb.installer' );
cbimport( 'cb.params' );
cbimport( 'cb.pagination' );

if ( ! $_CB_framework->check_acl( 'canManageUsers', $_CB_framework->myUserType() ) ) {
	cbRedirect( 'index2.php', _UE_NOT_AUTHORIZED, 'error' );
}
/** Backend menu: 'show' : only displays close button, 'edit' : special close button
 *  @global stdClass $_CB_Backend_Menu */
global $_CB_Backend_Menu;
$_CB_Backend_Menu	=	new stdClass();

$option				=	$_CB_framework->getRequestVar( 'option' );
$task				=	$_CB_framework->getRequestVar( 'task' );

global $_CB_Backend_Title, $_CB_Backend_task;
$_CB_Backend_Title	=	array();
$_CB_Backend_task	=	$task;
//$task				=	trim( cbGetParam( $_REQUEST, 'task', null ) );
$cid				=	cbGetParam( $_REQUEST, 'cid', array( 0 ) );
$uid				=	cbGetParam( $_REQUEST, 'uid', array( 0 ) );
if ( ! is_array( $cid )) {
	$cid			=	array ( (int) $cid );
}

$oldignoreuserabort	=	ignore_user_abort( true );

$taskPart1			=	strtok( $task, '.' );

$_CB_framework->document->_outputToHeadCollectionStart();
ob_start();

// remind step 2 if forgotten/failed:
$tgzFile			=	$_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/pluginsfiles.tgz';
if ( file_exists( $tgzFile ) ) {
	if ( in_array( $taskPart1, array( 'showusers', 'showconfig', 'showTab', 'showField', 'showLists', 'tools', 'showPlugins', '' ) ) ) {
		echo '<div class="cbWarning">Warning: file ' . $tgzFile . ' still exists. This is probably due to the fact that first installation step did not complete, or second installation step did not take place. If you are sure that first step has been performed, you need to execute second installation step before using CB. You can do this now by clicking here: '
		. '<a href="index2.php?option=com_comprofiler&task=finishinstallation">please click here to continue next and last installation step</a>.</div>';
	}
}


switch ( $taskPart1 ) {
	case "new":
		editUser( 0, $option );
		break;

	case "edit":
		editUser( intval( $cid[0] ), $option );
		break;

	case "save":
		cbSpoofCheck( 'user' );
		saveUser( $option );
		break;

	case "remove":
		cbSpoofCheck( 'user' );
		removeUsers( $cid, $option );
		break;
	
	case "block":
		cbSpoofCheck( 'user' );
		changeUserBlock( $cid, 1, $option );
		break;

	case "unblock":
		cbSpoofCheck( 'user' );
		changeUserBlock( $cid, 0, $option );
		break;

	case "approve":
		cbSpoofCheck( 'user' );
        approveUser( $cid, 1, $option );
        break;
	
	case "reject":
		cbSpoofCheck( 'user' );
        approveUser( $cid, 0, $option );
        break;

   	case "showconfig":
  		showConfig( $option );
  		break;

   	case "showinstruction":
   		global $_CB_database;
  		showInstructions( $_CB_database, $option, $_CB_framework->getCfg( 'lang' ) );
  		break;

   	case "saveconfig":
		cbSpoofCheck( 'config' );
  		saveConfig( $option );
      	break;

	case "newTab":
		editTab( 0, $option);
		break;

	case "editTab":
		editTab( intval( $cid[0] ), $option, $task );
		break;

	case "saveTab":
		cbSpoofCheck( 'tab' );
		saveTab( $option );
		break;

	case "removeTab":
		cbSpoofCheck( 'tab' );
		removeTabs( $cid, $option );
		break;

	case "showTab":
		showTab( $option );
		break;

	case "orderupTab":
	case "orderdownTab":
		cbSpoofCheck( 'tab' );
		orderTabs( $cid[0], ($task == 'orderupTab' ? -1 : 1), $option);
		break;

	case "newField":
		editField( 0, $option, $task );
		break;

	case "editField":
		editField( intval( $cid[0] ), $option, $task );
		break;

	case "reloadField":
		cbSpoofCheck( 'field' );
		editField( (int) cbGetParam( $_POST, 'fieldid', 0 ), $option, $task );
		break;

	case "saveField":
	case "applyField":
		cbSpoofCheck( 'field' );
		saveField( $option, $task );
		break;

	case "removeField":
		cbSpoofCheck( 'field' );
		removeField( $cid, $option );
		break;

	case "showField":
		showField( $option );
		break;

	case "orderupField":
		cbSpoofCheck( 'field' );
		orderFields( $cid[0], -1, $option );
		break;

	case "orderdownField":
		cbSpoofCheck( 'field' );
		orderFields( $cid[0], 1, $option );
		break;

	case "saveList":
		cbSpoofCheck( 'list' );
		saveList($option );
		break;

	case "editList":
		editList( $cid[0], 1, $option );
		break;
	case "newList":
		editList( 0, $option);
		break;

	case "showLists":
		showLists( $option );
		break;
	case "removeList":
		cbSpoofCheck( 'list' );
		removeList( $cid, $option );
		break;
	case "orderupList":
		cbSpoofCheck( 'list' );
		orderLists( $cid[0], -1, $option );
		break;

	case "orderdownList":
		cbSpoofCheck( 'list' );
		orderLists( $cid[0], 1, $option );
		break;

	case "fieldPublishedYes":
		cbSpoofCheck( 'field' );
        publishField( $cid, 1, $option );
        break;

	case "fieldPublishedNo":
		cbSpoofCheck( 'field' );
        publishField( $cid, 0, $option );
        break;

	case "fieldRequiredYes":
		cbSpoofCheck( 'field' );
        requiredField( $cid, 1, $option );
        break;

	case "fieldRequiredNo":
		cbSpoofCheck( 'field' );
        requiredField( $cid, 0, $option );
        break;

	case "fieldProfileYes1":
		cbSpoofCheck( 'field' );
        profileField( $cid, 1, $option );
        break;

	case "fieldProfileYes2":
		cbSpoofCheck( 'field' );
        profileField( $cid, 2, $option );
        break;

	case "fieldProfileNo":
		cbSpoofCheck( 'field' );
        profileField( $cid, 0, $option );
        break;

	case "fieldRegistrationYes":
		cbSpoofCheck( 'field' );
        registrationField( $cid, 1, $option );
        break;

	case "fieldRegistrationNo":
		cbSpoofCheck( 'field' );
        registrationField( $cid, 0, $option );
        break;

	case "fieldSearchableYes":
		cbSpoofCheck( 'field' );
        searchableField( $cid, 1, $option );
        break;

	case "fieldSearchableNo":
		cbSpoofCheck( 'field' );
        searchableField( $cid, 0, $option );
        break;

	case "listPublishedYes":
		cbSpoofCheck( 'list' );
        listPublishedField( $cid, 1, $option );
        break;

	case "listPublishedNo":
		cbSpoofCheck( 'list' );
        listPublishedField( $cid, 0, $option );
        break;

	case "listDefaultYes":
		cbSpoofCheck( 'list' );
        listDefaultField( $cid, 1, $option );
        break;

	case "listDefaultNo":
		cbSpoofCheck( 'list' );
        listDefaultField( $cid, 0, $option );
        break;

	case "tabPublishedYes":
		cbSpoofCheck( 'tab' );
        tabPublishedField( $cid, 1, $option );
        break;

	case "tabPublishedNo":
		cbSpoofCheck( 'tab' );
        tabPublishedField( $cid, 0, $option );
        break;

	case "tools":
		loadTools();
		break;

	case "loadSampleData":
		cbSpoofCheck( 'cbtools', 'GET' );
        loadSampleData();
        break;

	case "syncUsers":
		cbSpoofCheck( 'cbtools', 'GET' );
        syncUsers();
        break;

	case "checkcbdb":
		cbSpoofCheck( 'cbtools', 'GET' );
		checkcbdb( (int) cbGetParam( $_GET, 'databaseid', 0 ) );
		break;

	case "fixcbdb":
		cbSpoofCheck( 'cbtools', 'GET' );
		fixcbdb( (int) cbGetParam( $_GET, 'dryrun', 1 ), (int) cbGetParam( $_GET, 'databaseid', 0 ) );
		break;

	case "fixacldb":
		cbSpoofCheck( 'cbtools', 'GET' );
		fixacldb();
		break;

	case "fixcbmiscdb":
		cbSpoofCheck( 'cbtools', 'GET' );
		fixcbmiscdb();
		break;

	case "showusers":
		showUsers( $option );
		break;
		
	case 'savetaborder':
		cbSpoofCheck( 'tab' );
		saveTabOrder( $cid );
		break;
		
	case 'savefieldorder':
		cbSpoofCheck( 'field' );
		saveFieldOrder( $cid );
		break;	
	case 'savelistorder':
		cbSpoofCheck( 'list' );
		saveListOrder( $cid );
		break;	


	case 'newPlugin':
	case 'editPlugin':
		editPlugin( $option, $task,  $cid[0] );
		break;

	case 'savePlugin':
	case 'applyPlugin':
		cbSpoofCheck( 'plugin' );
		savePlugin( $option, $task );
		break;

	case 'deletePlugin':
		cbSpoofCheck( 'plugin' );
		removePlugin( $cid, $option );
		break;

	case 'cancelPlugin':
		cancelPlugin( $option );
		break;

	case 'cancelPluginAction':
		cancelPluginAction( $option );
		break;

	case 'publishPlugin':
	case 'unpublishPlugin':
		cbSpoofCheck( 'plugin' );
		publishPlugin( $cid, ($task == 'publishPlugin'), $option );
		break;

	case 'orderupPlugin':
	case 'orderdownPlugin':
		cbSpoofCheck( 'plugin' );
		orderPlugin( $cid[0], ($task == 'orderupPlugin' ? -1 : 1), $option);
		break;

	case 'accesspublic':
	case 'accessregistered':
	case 'accessspecial':
		cbSpoofCheck( 'plugin' );
		accessMenu( $cid[0], $task, $option );
		break;

	case 'savepluginorder':
		cbSpoofCheck( 'plugin' );
		savePluginOrder( $cid );
		break;

	case 'showPlugins':
		viewPlugins( $option);
		break;
		
	case 'installPluginUpload':
		cbSpoofCheck( 'plugin' );
		installPluginUpload();
		break;
		
	case 'installPluginDir':
		cbSpoofCheck( 'plugin' );
		installPluginDir();
		break;

	case 'installPluginURL':
		cbSpoofCheck( 'plugin' );
		installPluginURL();
		break;

	case 'pluginmenu':
		pluginMenu( $option, cbGetParam( $_REQUEST, 'pluginid', 0 ) );
		break;

	case 'latestVersion':
		latestVersion();
		break;

	case "fieldclass":
	case "tabclass":
	case "pluginclass":
		tabClass( $option, $task, cbGetParam( $_REQUEST, 'user', 0 ) );
		break;

	case "finishinstallation":
		finishInstallation( $option );
		break;

	default:
		// var_export( $ _POST );		//DEBUG!
		teamCredits(2);
		break;
}

echo $_CB_framework->getAllJsPageCodes();

$html		=	ob_get_contents();
ob_end_clean();

if ( in_array( $taskPart1, array( 'fieldclass', 'tabclass', 'pluginclass' ) ) || ( cbGetParam( $_GET, 'no_html', 0 ) == 1 ) ) {
	echo $html;
} else {
	$cssIE7fix	=	'--></style><!--[if lte IE 7]><style type="text/css">.cbtoolbarbar .cbtoolbar { width: 48px; }</style><![endif]--><style type="text/css"><!--';
	echo $_CB_framework->document->addHeadStyleInline( $cssIE7fix );
	echo $_CB_framework->document->_outputToHead();
	// fix the backend toolbar icons taking full width in joomla 1.5:
?>
<div style="margin:0px;border-width:0px;padding:0px;float:left;width:100%;text-align:left;"><div id="cbAdminMainWrapper" style="margin:0px;border-width:0px;padding:0px;float:none;width:auto;">
<?php
	echo '<div style="float:right;">';
	include( $_CB_adminpath . '/comprofiler.toolbar.php' );
	echo '</div>';
	if ( count( $_CB_Backend_Title ) > 0 ) {
		echo '<div class="header' . ( isset( $_CB_Backend_Title[0][0] ) ? ' ' . $_CB_Backend_Title[0][0] : '' ) . '">';
		echo $_CB_Backend_Title[0][1];
		echo '</div>';
		echo '<div style="clear:both;">';
		echo '</div>';
	}
	echo '<div style="float:left;width:100%;">';
	echo $html;
	echo '</div>';
	echo '<div style="clear:both;">';
	echo '</div>';
	
	echo '</div></div>';
}
if (!is_null($oldignoreuserabort)) ignore_user_abort($oldignoreuserabort);

// END OF MAIN.


function saveList( $option ) {
	global $_CB_database, $_POST;
	
	$row = new moscomprofilerLists( $_CB_database );

 	$_POST['params']	=	cbParamsEditorController::getRawParams( $_POST['params'] );
 
	if (!$row->bind( $_POST )) {
		echo "<script type=\"text/javascript\"> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->description	=	trim( $row->description );

	if(isset($_POST['col1'])) { $row->col1fields = implode("|*|",$_POST['col1']); } else { $row->col1fields = null; } ;
	if(isset($_POST['col2'])) { $row->col2fields = implode("|*|",$_POST['col2']); } else { $row->col2fields = null; } ;
	if(isset($_POST['col3'])) { $row->col3fields = implode("|*|",$_POST['col3']); } else { $row->col3fields = null; } ;
	if(isset($_POST['col4'])) { $row->col4fields = implode("|*|",$_POST['col4']); } else { $row->col4fields = null; } ;

	if ($row->col1enabled != 1) $row->col1enabled=0;
	if ($row->col2enabled != 1) $row->col2enabled=0;
	if ($row->col3enabled != 1) $row->col3enabled=0;
	if ($row->col4enabled != 1) $row->col4enabled=0;
	if ($row->col1captions != 1) $row->col1captions=0;
	if ($row->col2captions != 1) $row->col2captions=0;
	if ($row->col3captions != 1) $row->col3captions=0;
	if ($row->col4captions != 1) $row->col4captions=0;
	if (!$row->store( (int) $_POST['listid'],true)) {
		echo "<script type=\"text/javascript\"> alert('".$row->getError()."'); window.history.go(-2); </script>\n";
		exit();
	}

	cbRedirect( "index2.php?option=$option&task=showLists", "Successfully Saved List: ". $row->title );
}

function showLists( $option ) {
	global $_CB_database, $_CB_framework;

	$limit			=	(int) $_CB_framework->getCfg( 'list_limit' );
	if ( $limit == 0 ) {
		$limit = 10;
	}
	$limit			=	$_CB_framework->getUserStateFromRequest( "viewlistlimit", 'limit', $limit );
	$lastCBlist = $_CB_framework->getUserState( "view{$option}lastCBlist", null );
	if($lastCBlist=='showlists') {
		$limitstart	= $_CB_framework->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
		$lastSearch = $_CB_framework->getUserState( "search{$option}", null );
		$search		= $_CB_framework->getUserStateFromRequest( "search{$option}", 'search', '' );
		if ($lastSearch != $search) {
			$limitstart = 0;
			$_CB_framework->setUserState( "view{$option}limitstart", $limitstart );
		}
		$search = trim( strtolower( $search ) );
	} else {
		clearSearchBox();
		$search="";
		$limitstart = 0;
		$_CB_framework->setUserState( "view{$option}limitstart", $limitstart );
		$_CB_framework->setUserState( "view{$option}lastCBlist", "showlists" );
	}

	$where = array();
	if (isset( $search ) && $search!= "") {
		$search = cbEscapeSQLsearch( trim( strtolower( cbGetEscaped($search))));
		$where[] = "(a.title LIKE '%$search%' OR a.description LIKE '%$search%')";
	}

	$_CB_database->setQuery( "SELECT COUNT(*)"
		. "\n FROM #__comprofiler_lists AS a"
		. (count( $where ) ? "\nWHERE " . implode( ' AND ', $where ) : "")
	);
	$total = $_CB_database->loadResult();
	echo $_CB_database->getErrorMsg();
	if ($total <= $limitstart) $limitstart = 0;
	
	cbimport( 'cb.pagination' );
	$pageNav = new cbPageNav( $total, $limitstart, $limit  );
	$_CB_database->setQuery( "SELECT listid, title, description, published,`default`,ordering,useraccessgroupid"
		. "\nFROM #__comprofiler_lists a"
		. (count( $where ) ? "\nWHERE " . implode( ' AND ', $where ) : "")
		. "\n ORDER BY ordering"
		. "\nLIMIT ". (int) $pageNav->limitstart . ", " . (int) $pageNav->limit
	);

	$rows = $_CB_database->loadObjectList();
	if ($_CB_database->getErrorNum()) {
		echo $_CB_database->stderr();
		return false;
	}

	HTML_comprofiler::showLists( $rows, $pageNav, $search, $option );
	return true;
}

function editList( $fid='0', $option='com_comprofiler', $task = 'editList') {
	global $_CB_database, $_CB_framework, $ueConfig;

	$row					=	new moscomprofilerLists( $_CB_database );
	
	if ( $fid ) {
		// load the row from the db table
		$row->load( (int) $fid );
	} else {
		$row->col1enabled	=	'1';
	}

	$lists['published']		=	moscomprofilerHTML::yesnoSelectList( 'published', 'class="inputbox" size="1"', $row->published );
	$lists['default']		=	moscomprofilerHTML::yesnoSelectList( 'default', 'class="inputbox" size="1"', $row->default );
/*
	if ( checkJversion() <= 0 ) {
		$my_groups 	= $_CB_framework->acl->get_object_groups( 'users', $_CB_framework->myId(), 'ARO' );
	} else {
		$aro_id		= $_CB_framework->acl->get_object_id( 'users', $_CB_framework->myId(), 'ARO' );
		$my_groups 	= $_CB_framework->acl->get_object_groups( $aro_id, 'ARO' );
	}
*/
	$gtree2					=	array();
	$gtree2					=	array_merge( $gtree2, $_CB_framework->acl->get_group_children_tree( null, 'USERS', false ));

	$usergids				=	explode( ', ', $row->usergroupids );
	$ugids					=	array();
	foreach($usergids as $usergid) {
		$ugids[]->value		=	$usergid;
	}

	$lists['usergroups']	=	moscomprofilerHTML::selectList( $gtree2, 'usergroups', 'size="4" MULTIPLE onblur="loadUGIDs(this);" mosReq=1 mosLabel="User Groups"', 'value', 'text', $ugids, 1, false );

	$gtree3					=	array();
    $gtree3[]				=	moscomprofilerHTML::makeOption( -2 , '- Everybody -' );
    $gtree3[]				=	moscomprofilerHTML::makeOption( -1, '- All Registered Users -' );
	$gtree3					=	array_merge( $gtree3, $_CB_framework->acl->get_group_children_tree( null, 'USERS', false ));
	
	$lists['useraccessgroup']	=	moscomprofilerHTML::selectList( $gtree3, 'useraccessgroupid', 'size="4"', 'value', 'text', $row->useraccessgroupid, 2, false );

	
	
	$_CB_database->setQuery( "SELECT f.fieldid, f.title"
		. "\n FROM #__comprofiler_fields AS f"
		. "\n INNER JOIN #__comprofiler_plugin AS p ON (f.pluginid = p.id)"
		. "\n WHERE ( ( f.published = 1"
		. "\n           AND f.profile > 0 ) OR ( f.name = 'username' ) " . ( in_array( $ueConfig['name_format'], array( 1, 2, 4 ) ) ? "OR ( f.name = 'name' ) " : '' ) . ")"
		. "\n  AND p.published = 1"
		. "\n ORDER BY f.ordering"
	);
	$field								=	$_CB_database->loadObjectList();
	$fields								=	array();
	for ( $i = 0, $n = count( $field ) ; $i < $n ; $i++ ) {
		$fieldvalue						=&	$field[$i];
		$fields[$fieldvalue->title]		=	$fieldvalue->fieldid;
	}
	//print_r(array_values($fields));

	// params:
	$paramsEditorHtml			=	array();
	$options					=	array( 'option' => $option, 'task' => $task, 'cid' => $row->listid );

	// list-specific own parameters:
	cbimport( 'cb.xml.simplexml' );
	$listXml					=	new CBSimpleXMLElement( file_get_contents( $_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/xmlcb/cb.lists.xml' ) );
	$null						=	null;
	$params						=&	new cbParamsEditorController( $row->params, $listXml, $listXml, $null, null, 'cbxml', 'version', '1' );
	$params->setOptions( $options );
	$listParamsEditHtml			=	$params->draw( null, 'views', 'view', 'name', 'editlist' );
	$paramsEditorHtml[]			=	array( 'title' => "List parameters", 'content' => $listParamsEditHtml );
/*

	// params:
	$paramsEditorHtml			=	array();
	$options					=	array( 'option' => $option, 'task' => $task, 'cid' => $row->fieldid );

	// field-specific own parameters:
	$fieldHandler				=	new cbFieldHandler();
	$fieldOwnParamsEditHtml		=	$fieldHandler->drawParamsEditor( $row, $options );
	if ( $fieldOwnParamsEditHtml ) {
		$paramsEditorHtml[]		=	array( 'title' => "Field-specific Parameters", 'content' => $fieldOwnParamsEditHtml );
	}

	// additional non-specific other parameters:
	$fieldsParamsPlugins		=	$_PLUGINS->getUserFieldParamsPluginIds();
	foreach ($fieldsParamsPlugins as $pluginId => $fieldParamHandlerClassName ) {
		$fieldParamHandler		=	new $fieldParamHandlerClassName( $pluginId, $row );			// cbFieldParamsHandler();
		$addParamsHtml			=	$fieldParamHandler->drawParamsEditor( $options );
		if ( $addParamsHtml ) {
			$addParamsTitle		=	$fieldParamHandler->getFieldsParamsLabel();
			$paramsEditorHtml[]	=	array( 'title' => $addParamsTitle, 'content' => $addParamsHtml );
		}
	}
*/
	HTML_comprofiler::editList( $row, $lists,$fields, $option, $fid, $paramsEditorHtml );
}

function removeList( $cid, $option ) {
	global $_CB_database;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script type=\"text/javascript\"> alert('Select an item to delete'); window.history.go(-1);</script>\n";
		exit;
	}
	$msg = '';
	if (count( $cid )) {
		$obj = new moscomprofilerLists( $_CB_database );
		foreach ($cid as $id) {
				$obj->delete( $id );
			}
		}
	
	//if($msg!='') echo "<script type=\"text/javascript\"> alert('".$msg."'); window.history.go(-1);</script>\n";
	cbRedirect( "index2.php?option=$option&task=showLists", $msg );
}

function orderLists( $lid, $inc, $option ) {
	global $_CB_database;
	$row = new moscomprofilerLists( $_CB_database );
	$row->load( (int) $lid );
	$row->move( $inc );
	cbRedirect( "index2.php?option=$option&task=showLists" );
}

function showField( $option ) {
	global $_CB_database, $_CB_framework;

	_upgradeCbFieldsTableOldFields();

	$limit			=	(int) $_CB_framework->getCfg( 'list_limit' );
	if ( $limit == 0 ) {
		$limit = 10;
	}
	$limit			=	$_CB_framework->getUserStateFromRequest( "viewlistlimit", 'limit', $limit );
	$lastCBlist = $_CB_framework->getUserState( "view{$option}lastCBlist", null );
	if($lastCBlist=='showfields') {
		$limitstart	= $_CB_framework->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
		$lastSearch = $_CB_framework->getUserState( "search{$option}", null );
		$search		= $_CB_framework->getUserStateFromRequest( "search{$option}", 'search', '' );
		if ($lastSearch != $search) {
			$limitstart = 0;
			$_CB_framework->setUserState( "view{$option}limitstart", $limitstart );
		}
		$search = trim( strtolower( $search ) );
	} else {
		clearSearchBox();
		$search="";
		$limitstart = 0;
		$_CB_framework->setUserState( "view{$option}limitstart", $limitstart );
		$_CB_framework->setUserState( "view{$option}lastCBlist", "showfields" );
	}

	$where = array();
//	$where[] = "(f.sys = 0)";
	if (isset( $search ) && $search!= "") {
		$search = cbEscapeSQLsearch( trim( strtolower( cbGetEscaped($search))));
		$where[] = "(f.name LIKE '%$search%' OR f.type LIKE '%$search%')";
	}
	$where[]	 =	"t.useraccessgroupid IN (".implode(',',getChildGIDS(userGID( $_CB_framework->myId() ))).")";

	$_CB_database->setQuery( "SELECT COUNT(*)"
		. "\n FROM #__comprofiler_fields AS f, #__comprofiler_tabs AS t"
		. "\n WHERE (f.tabid = t.tabid) AND (t.fields = 1)" . ( count( $where ) ? ( " AND " . implode( ' AND ', $where ) ) : "" )
	);
	$total = $_CB_database->loadResult();
	echo $_CB_database->getErrorMsg();
	if ($total <= $limitstart) $limitstart = 0;

	cbimport( 'cb.pagination' );
	$pageNav = new cbPageNav( $total, $limitstart, $limit );
	$_CB_database->setQuery( "SELECT f.fieldid, f.title, f.name, f.description, f.type, f.required, f.published, "
		. "f.profile, f.ordering, f.registration, f.searchable, f.pluginid, f.sys, f.tablecolumns, "
		. "t.title AS 'tab', t.enabled AS 'tabenabled', t.pluginid AS 'tabpluginid', "
		. "p.name AS pluginname, p.published AS pluginpublished, "
		. "pf.name AS fieldpluginname, pf.published AS fieldpluginpublished "
		. "\n FROM #__comprofiler_fields AS f"
		. "\n INNER JOIN #__comprofiler_tabs AS t ON ( (f.tabid = t.tabid) AND (t.fields = 1) ) "
		. "\n LEFT JOIN #__comprofiler_plugin AS p ON p.id = t.pluginid"
		. "\n LEFT JOIN #__comprofiler_plugin AS pf ON pf.id = f.pluginid"
		. (count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : "")
		. "\n ORDER BY t.ordering, f.ordering"
		. "\n LIMIT " . (int) $pageNav->limitstart . ", " . (int) $pageNav->limit
	);

	$rows = $_CB_database->loadObjectList();
	if ($_CB_database->getErrorNum()) {
		echo $_CB_database->stderr();
		return false;
	}

	HTML_comprofiler::showFields( $rows, $pageNav, $search, $option );
	return true;
}

function _upgradeCbFieldsTableOldFields( ) {
	global $_CB_database;

	// Upgrade old-fashioned fields (and the ones created by Fireboard !)
	$query						=	'UPDATE #__comprofiler_fields SET tablecolumns = name, pluginid = 1 WHERE pluginid = 0';
	$_CB_database->setQuery( $query );
	$_CB_database->query();
}

function editField( $fid = 0, $option = 'com_comprofiler', $task = 'editField' ) {
	global $_CB_database, $_CB_framework, $_PLUGINS;

	$row = new moscomprofilerFields( $_CB_database );
	
	$paramsEditorHtml			=	null;

	if ( $fid == 0 ) {
		// default values for new types:
		$row->type				=	'text';
		$row->tabid				=	11;		// contact info by default
		$row->profile			=	1;
		$row->registration		=	1;
		$row->displaytitle		=	1;
		$row->published			=	1;
		$paramsEditorHtml		=	array( array( 'title' => "Parameters", 'content' => "<strong>To see Parameters, first save new field</strong>" ) );
	} else {
		// load the row from the db table
		$row->load( (int) $fid );
	
		$fieldTab				=	new moscomprofilerTabs( $_CB_database );
		// load the row from the db table
		$fieldTab->load( (int) $row->tabid );
	
		if ( ! in_array( $fieldTab->useraccessgroupid, getChildGIDS( userGID( $_CB_framework->myId() ) ) ) ) {
			echo "<script type=\"text/javascript\"> alert('Unauthorized Access'); window.history.go(-1);</script>\n";
			exit;
		}
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	if ( $task == 'reloadField' ) {
		if ( ! _prov_bind_CB_field( $row, $fid ) ) {
			echo "<script type=\"text/javascript\"> alert('" . $row->getError() . "'); window.history.go(-1); </script>\n";
			exit();
		}
	}

	// params:
	$paramsEditorHtml			=	array();
	$options					=	array( 'option' => $option, 'task' => $task, 'cid' => $row->fieldid );

	// field-specific own parameters:
	$fieldHandler				=	new cbFieldHandler();
	$fieldOwnParamsEditHtml		=	$fieldHandler->drawParamsEditor( $row, $options );
	if ( $fieldOwnParamsEditHtml ) {
		$paramsEditorHtml[]		=	array( 'title' => "Field-specific Parameters", 'content' => $fieldOwnParamsEditHtml );
	}

	// additional non-specific other parameters:
	$fieldsParamsPlugins		=	$_PLUGINS->getUserFieldParamsPluginIds();
	foreach ($fieldsParamsPlugins as $pluginId => $fieldParamHandlerClassName ) {
		$fieldParamHandler		=	new $fieldParamHandlerClassName( $pluginId, $row );			// cbFieldParamsHandler();
		$addParamsHtml			=	$fieldParamHandler->drawParamsEditor( $options );
		if ( $addParamsHtml ) {
			$addParamsTitle		=	$fieldParamHandler->getFieldsParamsLabel();
			$paramsEditorHtml[]	=	array( 'title' => $addParamsTitle, 'content' => $addParamsHtml );
		}
	}

	$where						=	array();
	$where[]					=	"`fields` = 1";
	$where[]	 				=	"useraccessgroupid IN (".implode(',',getChildGIDS(userGID( $_CB_framework->myId() ))).")";
	$_CB_database->setQuery("SELECT tabid, title FROM #__comprofiler_tabs WHERE " . implode( ' AND ', $where ) . " ORDER BY ordering");
	$tabs						=	$_CB_database->loadObjectList();
	$lists						=	array();
	$tablist					=	array();

	for ($i=0, $n=count( $tabs ); $i < $n; $i++) {
		$tab					=&	$tabs[$i];
	   	$tablist[]				=	moscomprofilerHTML::makeOption( $tab->tabid, getLangDefinition($tab->title) );
	}
	
	$lists['tabs']				=	moscomprofilerHTML::selectList( $tablist, 'tabid', 'class="inputbox" size="1" mosReq=1 mosLabel="Tab"', 'value', 'text', $row->tabid, 2 );

	
	$types						=	array();

	if ( $fid == 0 ) {
		$typeHandlers			=	array();
	
		$registeredTypes		=	$_PLUGINS->getUserFieldTypes();
		foreach ( $registeredTypes as $typ ) {
			$typeHandlers[$typ]	=	new cbFieldHandler();
			$tmpField			=	new moscomprofilerFields( $_CB_database );
			$tmpField->type		=	$typ;
			$typLabel			=	$typeHandlers[$typ]->getFieldTypeLabel( $tmpField );
			if ( $typLabel ) {
				$types[]		=	moscomprofilerHTML::makeOption( $typ, $typLabel );
			}
		}
	} else {
		$types[] = moscomprofilerHTML::makeOption( $row->type, $fieldHandler->getFieldTypeLabel( $row, false ) );
	}

	$webaddrtypes = array();

	$webaddrtypes[] = moscomprofilerHTML::makeOption( '0', 'URL only' );
	$webaddrtypes[] = moscomprofilerHTML::makeOption( '2', 'Hypertext and URL' );
	
	$profiles = array();

	$profiles[] = moscomprofilerHTML::makeOption( '0', 'No' );
	$profiles[] = moscomprofilerHTML::makeOption( '1', 'Yes: on 1 Line' );
	$profiles[] = moscomprofilerHTML::makeOption( '2', 'Yes: on 2 Lines' );

	$fvalues = $_CB_database->setQuery( "SELECT fieldtitle "
		. "\n FROM #__comprofiler_field_values"
		. "\n WHERE fieldid=" . (int) $fid
		. "\n ORDER BY ordering" );
	$fvalues = $_CB_database->loadObjectList();

	$lists['webaddresstypes'] = moscomprofilerHTML::selectList( $webaddrtypes, 'webaddresstypes', 'class="inputbox" size="1"', 'value', 'text', $row->rows, 2 );
		
	$lists['type'] = moscomprofilerHTML::selectList( $types, 'type', 'class="inputbox" size="1"', 'value', 'text', $row->type, 2 );

	$lists['required'] = moscomprofilerHTML::yesnoSelectList( 'required', 'class="inputbox" size="1"', $row->required );

	$lists['published'] = moscomprofilerHTML::yesnoSelectList( 'published', 'class="inputbox" size="1"', $row->published );

	$lists['readonly'] = moscomprofilerHTML::yesnoSelectList( 'readonly', 'class="inputbox" size="1"', $row->readonly );

	$lists['profile'] = moscomprofilerHTML::selectList( $profiles, 'profile', 'class="inputbox" size="1"', 'value', 'text', $row->profile, 2 );

	$lists['displaytitle'] = moscomprofilerHTML::yesnoSelectList( 'displaytitle', 'class="inputbox" size="1"', $row->displaytitle );

	if ( $row->tablecolumns != '' && ! in_array( $row->type, array( 'password', 'userparams' ) ) ) {
		$lists['searchable'] = moscomprofilerHTML::yesnoSelectList( 'searchable', 'class="inputbox" size="1"', $row->searchable );
	} else {
		$lists['searchable'] = _UE_NO . '<input type="hidden" name="searchable" value="0" />';
	}

	$lists['registration'] = moscomprofilerHTML::yesnoSelectList( 'registration', 'class="inputbox" size="1"', $row->registration );

	HTML_comprofiler::editfield( $row, $lists, $fvalues, $option, $paramsEditorHtml );
}
/**
 * A more extensive bind method for fields ( 	//TBD: should got to the moscomprofilerFields class).
 *
 * @param  moscomprofilerFields  $row
 * @param  int                   $fid
 * @return boolean
 */
function _prov_bind_CB_field( &$row, $fid ) {
	global $_PLUGINS, $_POST;

	if ( isset( $_POST['cb_default'] ) ) {
		$_POST['default']		=	$_POST['cb_default'];			// go around WysywigPro3 bug
		unset( $_POST['cb_default'] );
	}
	$bindSuccess				=	$row->bind( $_POST );

	if ( $bindSuccess ) {
		$row->description		=	trim( $row->description );

		$pluginid				=	$_PLUGINS->getUserFieldPluginId( $row->type );
		if ( $pluginid != 1 ) {
			$row->pluginid		=	$pluginid;		// not core plugin for now as we don't allow changing field types
		}
	
		if ( ! isset( $_POST['params'] ) ) {
			$_POST['params']	=	null;
		}
		if ( $fid && $row->pluginid ) {
			// handles field-specific parameters:
			$fieldHandler		=	new cbFieldHandler();
			$row->params		=	$fieldHandler->getRawParamsRaw( $row, $_POST['params'] );
		} else {
			// if not a plugin-specific field, handle parameters in standard way:
		 	$row->params		=	stripslashes( cbParamsEditorController::getRawParams( $_POST['params'] ) );
		}
	}
	return $bindSuccess;
}

function saveField( $option, $task ) {
	global $_CB_database, $_CB_framework, $_POST, $_PLUGINS;

	if ( ( $task == 'showField' ) || ! ( isset( $_POST['oldtabid'] ) && isset( $_POST['tabid'] ) && isset( $_POST['fieldid'] ) ) ) {
		cbRedirect( 'index2.php?option=' . $option . '&task=' . $task );
		return;
	}

	$fid					=	(int) $_POST['fieldid'];

	$row					=	new moscomprofilerFields( $_CB_database );

	if ( $fid ) {
		// load the row from the db table
		if ( ! $row->load( (int) $fid ) ) {
			echo "<script type=\"text/javascript\"> alert('Innexistant field'); window.history.go(-1);</script>\n";
			exit;
		}
	
		$fieldTab			=	new moscomprofilerTabs( $_CB_database );
		// load the row from the db table
		$fieldTab->load( (int) $row->tabid );
	
		if ( ! in_array( $fieldTab->useraccessgroupid, getChildGIDS( userGID( $_CB_framework->myId() ) ) ) ) {
			echo "<script type=\"text/javascript\"> alert('Unauthorized Access'); window.history.go(-1);</script>\n";
			exit;
		}
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	if ( ! _prov_bind_CB_field( $row, $fid ) ) {
		echo "<script type=\"text/javascript\"> alert('" . $row->getError() . "'); window.history.go(-1); </script>\n";
		exit();
	}

	// in case the above changed perms.... really ?
	$fieldTab				=	new moscomprofilerTabs( $_CB_database );
	$fieldTab->load( (int) $row->tabid );
	if ( ! in_array( $fieldTab->useraccessgroupid, getChildGIDS( userGID( $_CB_framework->myId() ) ) ) ) {
		echo "<script type=\"text/javascript\"> alert('Unauthorized Access'); window.history.go(-1);</script>\n";
		exit;
	}

	if ($row->type == 'webaddress') {
		$row->rows			=	$_POST['webaddresstypes'];
		if ( !(($row->rows == 0) || ($row->rows == 2)) ) {
			$row->rows = 0;
		}
	}
	if ( $_POST['oldtabid'] != $_POST['tabid'] ) {
		if ( $_POST['oldtabid'] !== '' ) {
			//Re-order old tab
			$sql			=	"UPDATE #__comprofiler_fields SET ordering = ordering-1 WHERE ordering > ".(int) $_POST['ordering']." AND tabid = ".(int) $_POST['oldtabid'];
			$_CB_database->setQuery($sql);
			$_CB_database->query();
		}
		//Select Last Order in New Tab
		$sql				=	"SELECT MAX(ordering) FROM #__comprofiler_fields WHERE tabid=".(int) $_POST['tabid'];
		$_CB_database->SetQuery($sql);
		$max				=	$_CB_database->LoadResult();
		$row->ordering		=	max( $max + 1, 1 );
	}

	if ( cbStartOfStringMatch( $row->name, 'cb_' ) ) {
		$row->name			=	str_replace(" ", "", strtolower($row->name));
	}
	if ( ! $row->check() ) {
		echo "<script type=\"text/javascript\"> alert('".$row->getError()."'); window.history.go(-2); </script>\n";
		exit();
	}
	if ( ! $row->store( (int) $fid ) ) {
		echo "<script type=\"text/javascript\"> alert('".$row->getError()."'); window.history.go(-2); </script>\n";
		exit();
	}
	$fieldNames				=	$_POST['vNames'];
	$j						=	1;
	if( $row->fieldid > 0 ) {
		$_CB_database->setQuery( "DELETE FROM #__comprofiler_field_values"
			. " WHERE fieldid = " . (int) $row->fieldid );
		if( $_CB_database->query() === false ) {
			echo $_CB_database->getErrorMsg();
		}
	} else {
		$_CB_database->setQuery( "SELECT MAX(fieldid) FROM #__comprofiler_fields");
		$maxID				=	$_CB_database->loadResult();
		$row->fieldid		=	$maxID;
		echo $_CB_database->getErrorMsg();
	}
	//for($i=0, $n=count( $fieldNames ); $i < $n; $i++) {
	foreach ($fieldNames as $fieldName) {
		if(trim($fieldName)!=null || trim($fieldName)!='') {
			$_CB_database->setQuery( "INSERT INTO #__comprofiler_field_values (fieldid,fieldtitle,ordering)"
				. " VALUES( " . (int) $row->fieldid . ",'".cbGetEscaped(trim($fieldName))."', " . (int) $j . ")"
			);
			if ( $_CB_database->query() === false ) {
				echo $_CB_database->getErrorMsg();
			}
			$j++;
		}

	}

	switch ( $task ) {
		case 'applyField':
			$msg = 'Successfully Saved changes to Field: '. $row->name;
			cbRedirect( 'index2.php?option='. $option .'&task=editField&cid='. $row->fieldid, $msg );
			break;
		case 'saveField':
		default:
			$msg = 'Successfully Saved Field: '. $row->name;
			cbRedirect( 'index2.php?option='. $option .'&task=showField' , $msg );
			break;
	}
}

function removeField( $cid, $option ) {
	global $_CB_database, $_CB_framework;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script type=\"text/javascript\"> alert('Select an item to delete'); window.history.go(-1);</script>\n";
		exit;
	}
	$msg = '';
	if (count( $cid )) {
		$obj = new moscomprofilerFields( $_CB_database );

		$deletedOkNames		=	array();

		foreach ($cid as $id) {
			$id = (int) $id;
			$obj->load( $id );

			$fieldTab = new moscomprofilerTabs( $_CB_database );
			$fieldTab->load( (int) $obj->tabid );
			if ( ! in_array( $fieldTab->useraccessgroupid, getChildGIDS( userGID( $_CB_framework->myId() ) ) ) ) {
				echo "<script type=\"text/javascript\"> alert('Unauthorized Access'); window.history.go(-1);</script>\n";
				exit;
			}

			$noDelete = 0;
			$_CB_database->setQuery("SELECT COUNT(*) FROM #__comprofiler_lists".
					" WHERE col1fields like '%|*|$id' OR col1fields like '$id|*|%' OR col1fields like '%|*|$id|*|%' OR col1fields='$id'".
					" OR col2fields like '%|*|$id' OR col2fields like '$id|*|%' OR col2fields like '%|*|$id|*|%' OR col2fields='$id'".
					" OR col3fields like '%|*|$id' OR col3fields like '$id|*|%' OR col3fields like '%|*|$id|*|%' OR col3fields='$id'".
					" OR col4fields like '%|*|$id' OR col4fields like '$id|*|%' OR col4fields like '%|*|$id|*|%' OR col4fields='$id'");
			$onList = $_CB_database->loadResult();
			if ($onList > 0) {
				$msg .= getLangDefinition($obj->title) . " cannot be deleted because it is on a List. \n";
				$noDelete = 1;
			}
			if ($obj->sys==1) {
				$msg .= getLangDefinition($obj->title) ." cannot be deleted because it is a system field. \n";
				$noDelete = 1;
			} 
			if ($noDelete != 1) {
				if ( $obj->deleteDataDescr( $id ) ) {
					$sql="UPDATE #__comprofiler_fields SET ordering = ordering-1 WHERE ordering > ".(int) $obj->ordering." AND tabid = ".(int) $obj->tabid;
					$_CB_database->setQuery($sql);
					$_CB_database->query();
					$deletedOkNames[]	=	$obj->title;
				}
			}
		}
	}

	if ( ! $msg ) {
		$msg = 'Successfully Deleted Fields: '. implode( ', ', $deletedOkNames );
	}
	cbRedirect( "index2.php?option=$option&task=showField", $msg );
}


function orderFields( $fid, $inc, $option ) {
	global $_CB_database, $_CB_framework;
	$row = new moscomprofilerFields( $_CB_database );
	$row->load( (int) $fid );

	$fieldTab = new moscomprofilerTabs( $_CB_database );
	$fieldTab->load( (int) $row->tabid );
	if ( ! in_array( $fieldTab->useraccessgroupid, getChildGIDS( userGID( $_CB_framework->myId() ) ) ) ) {
		echo "<script type=\"text/javascript\"> alert('Unauthorized Access'); window.history.go(-1);</script>\n";
		exit;
	}

	$row->move( $inc , "tabid='$row->tabid'");
	cbRedirect( "index2.php?option=$option&task=showField" );
}


function showTab( $option ) {
	global $_CB_database, $_CB_framework;

	$limit			=	(int) $_CB_framework->getCfg( 'list_limit' );
	if ( $limit == 0 ) {
		$limit = 10;
	}
	$limit			=	$_CB_framework->getUserStateFromRequest( "viewlistlimit", 'limit', $limit );
	$lastCBlist = $_CB_framework->getUserState( "view{$option}lastCBlist", null );
	if($lastCBlist=='showtab') {
		$limitstart	= $_CB_framework->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
		$lastSearch = $_CB_framework->getUserState( "search{$option}", null );
		$search		= $_CB_framework->getUserStateFromRequest( "search{$option}", 'search', '' );
		if ($lastSearch != $search) {
			$limitstart = 0;
			$_CB_framework->setUserState( "view{$option}limitstart", $limitstart );
		}
		$search = trim( strtolower( $search ) );
	} else {
		clearSearchBox();
		$search="";
		$limitstart = 0;
		$_CB_framework->setUserState( "view{$option}limitstart", $limitstart );
		$_CB_framework->setUserState( "view{$option}lastCBlist", "showtab" );
	}

	$where = array();
	if (isset( $search ) && $search!= "") {
		$search  =	cbEscapeSQLsearch( trim( strtolower( cbGetEscaped($search))));
		$where[] =	"(a.title LIKE '%$search%')";
	}

	$where[]	 =	"a.useraccessgroupid IN (".implode(',',getChildGIDS(userGID( $_CB_framework->myId() ))).")";

	$_CB_database->setQuery( "SELECT COUNT(*)"
		. "\nFROM #__comprofiler_tabs AS a"
		. (count( $where ) ? "\nWHERE " . implode( ' AND ', $where ) : "")
	);
	$total = $_CB_database->loadResult();
	echo $_CB_database->getErrorMsg();
	if ($total <= $limitstart) $limitstart = 0;

	cbimport( 'cb.pagination' );
	$pageNav = new cbPageNav( $total, $limitstart, $limit  );

	$_CB_database->setQuery( "SELECT a.*, p.name AS pluginname, p.published AS pluginpublished "
		. "\nFROM #__comprofiler_tabs AS a"
		. "\n LEFT JOIN #__comprofiler_plugin AS p ON p.id = a.pluginid"
		. (count( $where ) ? "\nWHERE " . implode( ' AND ', $where ) : "")
		. "\n ORDER BY position, ordering"
		. "\nLIMIT " . (int) $pageNav->limitstart . ", " . (int) $pageNav->limit
	);

	$rows = $_CB_database->loadObjectList();
	if ($_CB_database->getErrorNum()) {
		echo $_CB_database->stderr();
		return false;
	}

	HTML_comprofiler::showTabs( $rows, $pageNav, $search, $option );
	return true;
}

/**
* @param  string   $sql        SQL with ordering As value and 'name field' AS text
* @param  int      $chop       The length of the truncated headline
* @param  boolean  $translate  translate to CB language
* @return array                of makeOption
* @access private
*/
function _cbGetOrderingList( $sql, $chop = 30, $translate = true ) {
	global $_CB_database;

	$order				=	array();
	$_CB_database->setQuery( $sql );
	if ( false === ( $orders = $_CB_database->loadObjectList() ) ) {
		if ( $_CB_database->getErrorNum() ) {
			echo $_CB_database->stderr();
			return false;
		} else {
			$order[]	=	moscomprofilerHTML::makeOption( 1, 'first' );
			return $order;
		}
	}
	$order[]			=	moscomprofilerHTML::makeOption( 0, '0 ' . 'first' );
	for ( $i=0, $n = count( $orders ); $i < $n; $i++ ) {
		if ( $translate ) {
			$text		=	getLangDefinition( $orders[$i]->text );
		} else {
			$text		=	$orders[$i]->text;
		}
        if ( strlen( $text ) > $chop ) {
        	$text		=	substr( $text, 0, $chop ) . '...';
        }

		$order[]		=	moscomprofilerHTML::makeOption( $orders[$i]->value, $orders[$i]->value . ' (' . $text . ')' );
	}
	if ( isset( $orders[$i - 1] ) ) {
		$order[]		=	moscomprofilerHTML::makeOption( $orders[$i - 1]->value + 1, ( $orders[$i - 1]->value + 1 ) . ' ' . 'last' );
	}
	return $order;
}

function editTab( $tid='0', $option='com_comprofiler', $task='editTab' ) {
	global $_CB_database, $_CB_framework, $_PLUGINS;

	$row = new moscomprofilerTabs( $_CB_database );
	// load the row from the db table
	$row->load( (int) $tid );

	if ( $tid && ! in_array( $row->useraccessgroupid, getChildGIDS( userGID( $_CB_framework->myId() ) ) ) ) {
		echo "<script type=\"text/javascript\"> alert('Unauthorized Access'); window.history.go(-1);</script>\n";
		exit;
	}
 	$lists = array();
	if($row->sys=='2') $lists['enabled'] = "Yes";
	else $lists['enabled'] = moscomprofilerHTML::yesnoSelectList( 'enabled', 'class="inputbox" size="1"', $row->enabled );

	/*
	-------------------------
	!          head         !
	!-----------------------!
	!      !        !       !
	! left ! middle ! right !
	!      !        !       !
	!-----------------------!
	!                       !
	!        tabmain        !
	!                       !
	!-----------------------!
	!        underall       !
	-------------------------
	!      !        !       !
	! L1C1 ! L1C2   ! L1C3  !   L1C1...C9
	!      !        !       !
	!-----------------------!
	!      !        !       !
	! L2C1 ! L2C4   ! L2C8  !   ...
	!      !        !       !
	!-----------------------!
	!                       !
	!        L4C7           !
	!                       !
	!-----------------------!
	!          !            !
	!   L8C3   !    L8C4    !   ...L9C9
	!          !            !
	!-----------------------!
    ! + not_on_profile_1..9
	*/
	$position = array();
	$position[] = moscomprofilerHTML::makeOption( 'cb_head', _UE_POS_CB_HEAD );
	$position[] = moscomprofilerHTML::makeOption( 'cb_left', _UE_POS_CB_LEFT );
	$position[] = moscomprofilerHTML::makeOption( 'cb_middle', _UE_POS_CB_MIDDLE );
	$position[] = moscomprofilerHTML::makeOption( 'cb_right', _UE_POS_CB_RIGHT );
	$position[] = moscomprofilerHTML::makeOption( 'cb_tabmain', _UE_POS_CB_MAIN );
	$position[] = moscomprofilerHTML::makeOption( 'cb_underall', _UE_POS_CB_BOTTOM );
	for ( $i = 1 ; $i <= 9; $i++ ) {
		for ( $j = 1 ; $j <= 9; $j++ ) {
			$position[] = moscomprofilerHTML::makeOption( 'L'.$i.'C'.$j, 'Line ' . $i . ' Column ' . $j );
		}
	}
	for ( $i = 1 ; $i <= 9; $i++ ) {
		$position[] = moscomprofilerHTML::makeOption( 'not_on_profile_'.$i, 'Not displayed on profile ' . $i );
	}

	if ( ! $row->position ) {
		$row->position		=	'cb_tabmain';
	}
	$lists['position'] = moscomprofilerHTML::selectList( $position, 'position', 'class="inputbox" size="1"', 'value', 'text', $row->position, 2 );

	$displaytype = array();
	$displaytype[] = moscomprofilerHTML::makeOption( 'tab', _UE_DISPLAY_TAB );
	$displaytype[] = moscomprofilerHTML::makeOption( 'div', _UE_DISPLAY_DIV );
	$displaytype[] = moscomprofilerHTML::makeOption( 'html', _UE_DISPLAY_HTML );
	$displaytype[] = moscomprofilerHTML::makeOption( 'overlib', _UE_DISPLAY_OVERLIB );
	$displaytype[] = moscomprofilerHTML::makeOption( 'overlibfix', _UE_DISPLAY_OVERLIBFIX );
	$displaytype[] = moscomprofilerHTML::makeOption( 'overlibsticky', _UE_DISPLAY_OVERLIBSTICKY );
	$lists['displaytype'] = moscomprofilerHTML::selectList( $displaytype, 'displaytype', 'class="inputbox" size="1"', 'value', 'text', $row->displaytype, 2 );

	if ($tid) {
		if ( $row->ordering > -10000 && $row->ordering < 10000 ) {
			// build the html select list for ordering
			$query = "SELECT ordering AS value, title AS text"
			. "\n FROM #__comprofiler_tabs"
			. "\n WHERE position='" . $_CB_database->getEscaped( $row->position ) . "'"
			. "\n AND enabled > 0"
			. "\n AND ordering > -10000"
			. "\n AND ordering < 10000"
			. "\n ORDER BY ordering"
			;
			$order = _cbGetOrderingList( $query );
			$lists['ordering'] = moscomprofilerHTML::selectList( $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ), 2 );
		} else {
			$lists['ordering'] = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />This plugin cannot be reordered';
		}
	} else {
		$row->ordering 				= 999;
		$row->ordering_register		= 10;
		$row->published 			= 1;
		$row->description 			= '';
		$row->useraccessgroupid		= -2;
		$lists['ordering']	= '<input type="hidden" name="ordering" value="'. $row->ordering
							.'" />New items default to the last place. Ordering can be changed after this item is saved.';
	}

	$gtree3=array();
    $gtree3[] = moscomprofilerHTML::makeOption( -2 , '- Everybody -' );
    $gtree3[] = moscomprofilerHTML::makeOption( -1, '- All Registered Users -' );
	$gtree3 = array_merge( $gtree3, $_CB_framework->acl->get_group_children_tree( null, 'USERS', false ));
	
	$lists['useraccessgroup']=moscomprofilerHTML::selectList( $gtree3, 'useraccessgroupid', 'size="4"', 'value', 'text', $row->useraccessgroupid, 2, false );

	// params:
	$paramsEditorHtml			=	array();
	$options					=	array( 'option' => $option, 'task' => $task, 'cid' => $row->tabid );

	// additional non-specific other parameters:
	$_PLUGINS->loadPluginGroup( 'user' );

	$fieldsParamsPlugins		=	$_PLUGINS->getUserTabParamsPluginIds();
	foreach ($fieldsParamsPlugins as $pluginId => $fieldParamHandlerClassName ) {
		$fieldParamHandler		=	new $fieldParamHandlerClassName( $pluginId, $row );			// cbFieldParamsHandler();
		$addParamsHtml			=	$fieldParamHandler->drawParamsEditor( $options );
		if ( $addParamsHtml ) {
			$addParamsTitle		=	$fieldParamHandler->getFieldsParamsLabel();
			$paramsEditorHtml[]	=	array( 'title' => $addParamsTitle, 'content' => $addParamsHtml );
		}
	}
	
	HTML_comprofiler::edittab( $row, $option, $lists, $tid, $paramsEditorHtml );
}

function saveTab( $option ) {
	global $_CB_database, $_CB_framework, $_POST;

	if ( isset( $_POST['params'] ) ) {
	 	$_POST['params']	=	cbParamsEditorController::getRawParams( $_POST['params'] );
	} else {
		$_POST['params']	=	'';
	}

	if ( ! isset( $_POST['tabid'] ) || ( count( $_POST ) == 0 ) ) {
		echo "<script type=\"text/javascript\"> alert('Missing post values'); window.history.go(-2); </script>\n";
		exit();
	}
	if ( $_POST['tabid'] ) {
		$oldrow		=	new moscomprofilerTabs( $_CB_database );
		if ( $oldrow->load( (int) $_POST['tabid'] )
			&& 	( ! in_array( $oldrow->useraccessgroupid, getChildGIDS( userGID( $_CB_framework->myId() ) ) ) ) ) {
			echo "<script type=\"text/javascript\"> alert('Unauthorized Access'); window.history.go(-1);</script>\n";
			exit;
		}
	}

	$row = new moscomprofilerTabs( $_CB_database );
	if (!$row->bind( $_POST )) {
		echo "<script type=\"text/javascript\"> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if ( ! $row->ordering_register ) {
		$row->ordering_register		=	10;
	}
	
	$row->description	=	trim( $row->description );

	if (!$row->check()) {
		echo "<script type=\"text/javascript\"> alert('".$row->getError()."'); window.history.go(-2); </script>\n";
		exit();
	}
	if ( ! $row->store( (int) $_POST['tabid'] ) ) {
		echo "<script type=\"text/javascript\"> alert('".$row->getError()."'); window.history.go(-2); </script>\n";
		exit();
	}

	$row->checkin();
	cbRedirect( "index2.php?option=$option&task=showTab", "Successfully Saved Tab: ". $row->title );
}

function removeTabs( $cid, $option ) {
	global $_CB_database, $_CB_framework;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script type=\"text/javascript\"> alert('Select an item to delete'); window.history.go(-1);</script>\n";
		exit;
	}
	$msg = '';
	if (count( $cid )) {
		$obj = new moscomprofilerTabs( $_CB_database );
		foreach ($cid as $id) {
			$noDelete = 0;
			$obj->load( (int) $id );
			if ( ! in_array( $obj->useraccessgroupid, getChildGIDS( userGID( $_CB_framework->myId() ) ) ) ) {
				echo "<script type=\"text/javascript\"> alert('Unauthorized Access'); window.history.go(-1);</script>\n";
				exit;
			}

			$_CB_database->setQuery( "SELECT COUNT(*) FROM #__comprofiler_fields WHERE tabid=" . (int) $id );
			$onField = $_CB_database->loadResult();
			if( $obj->sys > 0 ) {
				$msg .= getLangDefinition($obj->title) ." cannot be deleted because it is a system tab. \n";
				$noDelete = 1;
			} 
			if( $obj->pluginid ) {
				$plugin	=	new moscomprofilerPlugin( $_CB_database );
				if ( $plugin->load( $obj->pluginid ) ) {
					$msg .= getLangDefinition($obj->title) ." cannot be deleted because it is a tab belonging to an installed plugin. \n";
					$noDelete = 1;
				}
			} 
			if( $onField > 0 ) {
				$msg .= getLangDefinition($obj->title) ." is being referenced by an existing field and cannot be deleted!";
				$noDelete = 1;
			} 
			if( $noDelete == 0 ) {
				$obj->delete( $id );
				$msg .= $obj->getError();
			}
		}
	}
	if ( $msg ) {
		echo "<script type=\"text/javascript\"> alert('" . str_replace( "\n", '\\n', addslashes( $msg ) ) . "'); window.history.go(-1);</script>\n";
		exit;
	}
	cbRedirect( "index2.php?option=$option&task=showTab" );
}


function orderTabs( $tid, $inc, $option ) {
	global $_CB_database, $_CB_framework;

	$row = new moscomprofilerTabs( $_CB_database );
	$row->load( (int) $tid );

	if ( ! in_array( $row->useraccessgroupid, getChildGIDS( userGID( $_CB_framework->myId() ) ) ) ) {
		echo "<script type=\"text/javascript\"> alert('Unauthorized Access'); window.history.go(-1);</script>\n";
		exit;
	}

	$row->move( $inc, "position='$row->position' AND ordering > -10000 AND ordering < 10000 "  );
	cbRedirect( "index2.php?option=$option&task=showTab" );
}

function showUsers( $option ) {
	global $_CB_database, $_CB_framework,$_POST, $_PLUGINS;

	$limit						=	(int) $_CB_framework->getCfg( 'list_limit' );
	if ( $limit == 0 ) {
		$limit					=	10;
	}
	$limit						=	$_CB_framework->getUserStateFromRequest( "viewlistlimit", 'limit', $limit );
	$filter_type				=	$_CB_framework->getUserStateFromRequest( "filter_type{$option}", 'filter_type', 0 );
	$filter_status				=	$_CB_framework->getUserStateFromRequest( "filter_status{$option}", 'filter_status', 0 );
	$filter_logged				=	intval( $_CB_framework->getUserStateFromRequest( "filter_logged{$option}", 'filter_logged', 0 ) );
	$lastCBlist					=	$_CB_framework->getUserState( "view{$option}lastCBlist", null );
	if( $lastCBlist == 'showusers' ) {
		$limitstart				=	$_CB_framework->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
		$lastSearch 			=	$_CB_framework->getUserState( "search{$option}", null );
		$search					=	$_CB_framework->getUserStateFromRequest( "search{$option}", 'search', '' );
		if ( $lastSearch != $search ) {
			$limitstart			=	0;
			$_CB_framework->setUserState( "view{$option}limitstart", $limitstart );
		}
		$search					=	trim( strtolower( $search ) );
	} else {
		$filter_type			=	0;
		$filter_status			=	0;
		$filter_logged			=	0;
		clearSearchBox();
		$search					=	'';
		$limitstart				=	0;
		$_CB_framework->setUserState( "view{$option}limitstart", $limitstart );
		$_CB_framework->setUserState( "view{$option}lastCBlist", "showusers" );
	}

	$tablesSQL					=	array( 'u'	=> '#__users AS u' );
	$joinsSQL					=	array( 'ue'	=> 'LEFT JOIN #__comprofiler AS ue ON u.id = ue.id' );
	$tablesWhereSQL				=	array();

	if ( isset( $search ) && ( $search != "") ) {
		$tablesWhereSQL[]		=	"(u.username LIKE '%" . cbEscapeSQLsearch( $_CB_database->getEscaped( $search ) ) . "%' OR u.email LIKE '%" . cbEscapeSQLsearch( $_CB_database->getEscaped( $search ) ) . "%' OR u.name LIKE '%" . cbEscapeSQLsearch( $_CB_database->getEscaped( $search ) ) . "%')";
	}
	if ( $filter_type ) {
		if ( $filter_type == 'Public Frontend' ) {
			$tablesWhereSQL[]	=	"(u.usertype = 'Registered' OR u.usertype = 'Author' OR u.usertype = 'Editor'OR u.usertype = 'Publisher')";
		} else if ( $filter_type == 'Public Backend' ) {
			$tablesWhereSQL[]	=	"(u.usertype = 'Manager' OR u.usertype = 'Administrator' OR u.usertype = 'Super Administrator')";
		} else {
			$tablesWhereSQL[]	=	"u.usertype = " . $_CB_database->Quote( $filter_type );
		}
	}
	$userstates	=	array(	'Blocked'								=>	'u.block = 1',
							'Enabled'								=>	'u.block = 0',
							'Unconfirmed'							=>	'ue.confirmed = 0',
							'Confirmed'								=>	'ue.confirmed = 1',
							'Unapproved'							=>	'ue.approved = 0',
							'Disapproved'							=>	'ue.approved = 2',
							'Approved'								=>	'ue.approved = 1',
							'Banned'								=>	'ue.banned <> 0',
							'Blocked + Unconfirmed + Unapproved'	=>	'(u.block = 1 AND ue.confirmed = 0 AND ue.approved = 0)',
							'Enabled + Unconfirmed + Unapproved'	=>	'(u.block = 0 AND ue.confirmed = 0 AND ue.approved = 0)',
							'Blocked + Confirmed + Unapproved'		=>	'(u.block = 1 AND ue.confirmed = 1 AND ue.approved = 0)',
							'Enabled + Confirmed + Unapproved'		=>	'(u.block = 0 AND ue.confirmed = 1 AND ue.approved = 0)',
							'Blocked + Unconfirmed + Disapproved'	=>	'(u.block = 1 AND ue.confirmed = 0 AND ue.approved = 2)',
							'Enabled + Unconfirmed + Disapproved'	=>	'(u.block = 0 AND ue.confirmed = 0 AND ue.approved = 2)',
							'Blocked + Confirmed + Disapproved'		=>	'(u.block = 1 AND ue.confirmed = 1 AND ue.approved = 2)',
							'Enabled + Confirmed + Disapproved'		=>	'(u.block = 0 AND ue.confirmed = 1 AND ue.approved = 2)',
							'Blocked + Unconfirmed + Approved'		=>	'(u.block = 1 AND ue.confirmed = 0 AND ue.approved = 1)',
							'Enabled + Unconfirmed + Approved'		=>	'(u.block = 0 AND ue.confirmed = 0 AND ue.approved = 1)',
							'Blocked + Confirmed + Approved'		=>	'(u.block = 1 AND ue.confirmed = 1 AND ue.approved = 1)',
							'Enabled + Confirmed + Approved'		=>	'(u.block = 0 AND ue.confirmed = 1 AND ue.approved = 1)' );
	if ( $filter_status ) {
		$tablesWhereSQL[]		=	$userstates[$filter_status];
	}
	if ( $filter_logged == 1 ) {
		$tablesWhereSQL[]		=	"s.userid = u.id";
	} else if ($filter_logged == 2) {
		$tablesWhereSQL[]		=	"s.userid IS NULL";
	}

	// exclude any child group id's for this user
	//$_CB_framework->acl->_debug = true;
	$pgids						=	$_CB_framework->acl->get_group_children( userGID( $_CB_framework->myId() ), 'ARO', 'RECURSE' );
	if ( is_array( $pgids ) && (count( $pgids ) > 0 ) ) {
		$tablesWhereSQL[]		=	"(u.gid NOT IN (" . implode( ',', $pgids ) . "))";
	}
	if ($filter_logged == 1 || $filter_logged == 2) {
		$joinsSQL[]				.=	"\n INNER JOIN #__session AS s ON s.userid = u.id";
	// } else {		done later, to avoid blocking site:
	//	$joinsSQL[]				.=	"\n LEFT JOIN #__session AS s ON s.userid = u.id";
	}

   	$_PLUGINS->loadPluginGroup('user');	
	$_PLUGINS->trigger( 'onBeforeBackendUsersListBuildQuery', array( &$tablesSQL, &$joinsSQL, &$tablesWhereSQL, $option ) );

	$queryFrom					=	"\n FROM " . implode( ', ', $tablesSQL ) 
								.	( count( $joinsSQL ) ? "\n " . implode( "\n ", $joinsSQL ) : '' )
								.	( count( $tablesWhereSQL ) ? "\n WHERE " . implode( ' AND ', $tablesWhereSQL ) : '' )
								;

	$query						=	"SELECT COUNT(u.id)"
								.	$queryFrom
								;
	$_CB_database->setQuery( $query );
	$total						=	$_CB_database->loadResult();
	if ( $total === null ) {
		echo $_CB_database->getErrorMsg();
	}
	if ( $total <= $limitstart ) {
		$limitstart				=	0;
	}

	cbimport( 'cb.pagination' );
	$pageNav					=	new cbPageNav( $total, $limitstart, $limit  );

	if ( checkJversion() <= 0 ) {
		$joinsSQL[]				=	"INNER JOIN #__core_acl_aro AS aro ON aro.value = u.id";					// map user to aro
		$joinsSQL[]				=	"INNER JOIN #__core_acl_groups_aro_map AS gm ON gm.aro_id = aro.aro_id";	// map aro to group
		$joinsSQL[]				=	"INNER JOIN #__core_acl_aro_groups AS g ON g.group_id = gm.group_id";
		$tablesWhereSQL[]		=	"aro.section_value = 'users'";

	} else {
		$joinsSQL[]				=	"INNER JOIN #__core_acl_aro AS aro ON aro.value = u.id";					// map user to aro
		$joinsSQL[]				=	"INNER JOIN #__core_acl_groups_aro_map AS gm ON gm.aro_id = aro.id";		// map aro to group
		$joinsSQL[]				=	"INNER JOIN #__core_acl_aro_groups AS g ON g.id = gm.group_id";
		$tablesWhereSQL[]		=	"aro.section_value = 'users'";
	}

	$queryFrom					=	"\n FROM " . implode( ', ', $tablesSQL ) 
								.	( count( $joinsSQL ) ? "\n " . implode( "\n ", $joinsSQL ) : '' )
								.	( count( $tablesWhereSQL ) ? "\n WHERE " . implode( ' AND ', $tablesWhereSQL ) : '' )
								;

	$query						=	"SELECT DISTINCT u.*, g.name AS groupname, ue.approved, ue.confirmed"
								.	$queryFrom
								.	"\n LIMIT " . (int) $pageNav->limitstart . ", " . (int) $pageNav->limit
								;
	$_CB_database->setQuery( $query );
	$rows						=	$_CB_database->loadObjectList( null, 'moscomprofilerUser', array( &$_CB_database ) );
	if ($_CB_database->getErrorNum()) {
		echo $_CB_database->stderr();
		return false;
	}
	// creates the CBUsers in cache corresponding to the $users:
	foreach ( array_keys( $rows ) as $k) {
		CBuser::setUserGetCBUserInstance( $rows[$k] );
	}

	$template				=	'SELECT COUNT(s.userid) FROM #__session AS s WHERE s.userid = ';
	$n						=	count( $rows );
	for ( $i = 0; $i < $n; $i++ ) {
		$row				=	&$rows[$i];
		$query				=	$template . (int) $row->id;
		$_CB_database->setQuery( $query );
		$row->loggedin		=	$_CB_database->loadResult();
	}

	// get list of Log Status for dropdown filter
	$logged[]				=	moscomprofilerHTML::makeOption( 0, '- Select Login State - ');
	$logged[]				=	moscomprofilerHTML::makeOption( 1, 'Logged In');
	$lists['logged']		=	moscomprofilerHTML::selectList( $logged, 'filter_logged', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_logged", 2 );

	// get list of Groups for dropdown filter
	$query					=	"SELECT name AS value, name AS text"
							.	"\n FROM #__core_acl_aro_groups"
							.	"\n WHERE name != 'ROOT'"
							.	"\n AND name != 'USERS'"
							;
	$types[]				=	moscomprofilerHTML::makeOption( '0', '- Select Group -' );
	$_CB_database->setQuery( $query );
	$types					=	array_merge( $types, $_CB_database->loadObjectList() );
	$lists['type']			=	moscomprofilerHTML::selectList( $types, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_type", 2 );

	$status[]				=	moscomprofilerHTML::makeOption( 0, '- Select User Status - ');
	foreach ( array_keys( $userstates ) as $k ) {
		$status[]			=	moscomprofilerHTML::makeOption( $k, $k );
	}
	$lists['status']		=	moscomprofilerHTML::selectList( $status, 'filter_status', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "$filter_status", 2 );

	$pluginAdditions		=	$_PLUGINS->trigger( 'onAfterBackendUsersList', array( 1, &$rows, &$pageNav, &$search, &$lists, $option ) );
	$pluginColumns			=	array();
	foreach ( $pluginAdditions as $addition ) {
		if ( is_array( $addition ) ) {
			$pluginColumns	=	array_merge( $pluginColumns, $addition );
		}
	}

	HTML_comprofiler::showUsers( $rows, $pageNav, stripslashes($search), $option, $lists, $pluginColumns );
	return true;
}

function editUser( $uid = '0', $option = 'users' ) {
	global $_CB_framework, $_PLUGINS;

	$msg						=	checkCBpermissions( array($uid), "edit", true );
	if ($msg) {
		echo "<script type=\"text/javascript\"> alert('".$msg."'); window.history.go(-1);</script>\n";
		exit;
	}

	$_PLUGINS->loadPluginGroup('user');

	$cbUser						=&	CBuser::getInstance( $uid );
	$cmsUserExists				=	( $uid != 0 ) && ( $cbUser !== null );
	if ( ! $cmsUserExists ) {
		$cbUser					=&	CBuser::getInstance( null );
	}
	$user						=&	$cbUser->getUserData();
/*
	$user						=	new moscomprofilerUser( $_CB_database );
	$cmsUserExists				=	$user->load( (int) $uid );
*/
	$comprofilerExists			=	( $user->user_id != null );
	if ( $cmsUserExists && $comprofilerExists ) {
		// Edit existing CB user:
		$newCBuser				=	'0';
	} else {
		$newCBuser				=	'1';
		if ( $cmsUserExists ) {
			// Edit existing CMS (but new CB) user:
			$user->approved		=	'1';
			$user->confirmed	=	'1';
		} else {
			// New user:
			$user->block		=	'0';
			$user->approved		=	'1';
			$user->confirmed	=	'1';
			$user->sendEmail	=	'0';
			$user->gid			=	$_CB_framework->acl->get_group_id( $_CB_framework->getCfg( 'new_usertype' ), 'ARO' );
		}
	}
	$null						=	null;
	HTML_comprofiler::edituser( $user, $option, $newCBuser, $null );
}

function saveUser( $option ) {
	global $_CB_framework, $_CB_database, $_POST, $_PLUGINS;

	if ( ! ( isset( $_POST['approved'] ) && isset( $_POST['confirmed'] ) && isset( $_POST['username'] ) ) ) {
		echo "<script type=\"text/javascript\"> alert('Not Authorized'); window.history.go(-1);</script>\n";
		exit;
	}

	// Check rights to access:

	$myGid						=	userGID( $_CB_framework->myId() );
	$userIdPosted				=	(int) cbGetParam($_POST, "id", 0 );
	if ( $userIdPosted == 0 ) {
		$_POST['id']			=	null;
	}
	if ( $userIdPosted != 0 ) {
		$msg					=	checkCBpermissions( array( $userIdPosted ), 'save', in_array( $myGid, array( 24, 25 ) ) );
	} else {
		$msg					=	checkCBpermissions( null, 'save', in_array( $myGid, array( 24, 25 ) ) );
	}
	if ($msg) {
		echo "<script type=\"text/javascript\"> alert('" . addslashes( $msg ) . "'); window.history.go(-1);</script>\n";
		exit;
	}

	$_PLUGINS->loadPluginGroup('user');

	// Get current user state:

	$userComplete				=	new moscomprofilerUser( $_CB_database );
	if ( $userIdPosted != 0 ) {
		if ( ! $userComplete->load( (int) $userIdPosted ) ) {
			echo "<script type=\"text/javascript\"> alert('" . addslashes( _UE_USER_PROFILE_NOT ) . "'); window.history.go(-1);</script>\n";
			return;
		}
	}

	// Store new user state:

	$saveResult					=	$userComplete->saveSafely( $_POST, $_CB_framework->getUi(), 'edit' );
	if ( ! $saveResult ) {
		$regErrorMSG			=	$userComplete->getError();

		$msg					=	checkCBpermissions( array( $userComplete->id ), "edit", true );
		if ($msg) {
			echo "<script type=\"text/javascript\"> alert('" . addslashes( $msg ) ."'); window.history.go(-1);</script>\n";
			exit;
		}

		echo "<script type=\"text/javascript\">alert('" . str_replace( '\\\\n', '\\n', addslashes( strip_tags( str_replace( '<br />', '\\n', $regErrorMSG ) ) ) ) . "'); </script>\n";
		global $_CB_Backend_task;
		$_CB_Backend_task		=	'edit';			// so the toolbar comes up...
		$_PLUGINS->loadPluginGroup( 'user' );		// resets plugin errors
		HTML_comprofiler::edituser( $userComplete, $option, ( $userComplete->user_id != null ? '0' : '1' ), $_POST );
		// echo "<script type=\"text/javascript\">alert('" . addslashes( str_replace( '<br />', '\n', $userComplete->getError() ) ) . "'); window.history.go(-1);</script>\n";
		return;
	}

	// Checks-in the row:
	$userComplete->checkin();

	cbRedirect( "index2.php?option=$option&task=showusers", "Successfully Saved User: ". $userComplete->username );
}

function deleteUsers( $cid, $inComprofilerOnly = false ) {
	global $_CB_framework, $_CB_database;
	
	$msg = checkCBpermissions( $cid, "delete" );
	
	if (!$msg && is_array( $cid ) && count( $cid )) {
		new cbTabs( 0, 2, null, false );		// loads plugins
		foreach ($cid as $id) {
			$obj		=&	$_CB_framework->_getCmsUserObject( (int) $id );
			if ( ( $obj !== null ) || $inComprofilerOnly ) {
				$count = 2;
				if ( ( $obj !== null ) && ( $obj->gid == 25 ) ) {
					// count number of active super admins
					$query = "SELECT COUNT( id )"
					. "\n FROM #__users"
					. "\n WHERE gid = 25"
					. "\n AND block = 0"
					;
					$_CB_database->setQuery( $query );
					$count = $_CB_database->loadResult();
				}
				
				if ( $count <= 1 && $obj->gid == 25 ) {
				// cannot delete Super Admin where it is the only one that exists
					$msg .= "You cannot delete this Super Administrator as it is the only active Super Administrator for your site";
				} else {
					// delete user
					$result = cbDeleteUser( $id, null, $inComprofilerOnly );
					if ( $result === null ) {
						$msg .= "User not found";
					} elseif (is_string( $result ) && ( $result != "" ) ) {
						$msg .= $result;
					}
				}
			} else {
				$msg .= "User not found";
			}
		}
	}
	return $msg;
}

function removeUsers( $cid, $option ) {

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script type=\"text/javascript\"> alert('Select an item to delete'); window.history.go(-1);</script>\n";
		exit;
	}

	$msg = deleteUsers($cid);
	if ($msg) {
		echo "<script type=\"text/javascript\"> alert('".$msg."'); window.history.go(-1);</script>\n";
		exit;
	}

	cbRedirect( "index2.php?option=$option&task=showusers", $msg );
}

/**
* Blocks or Unblocks one or more user records
* @param array An array of unique category id numbers
* @param integer 0 if unblock, 1 if blocking
* @param string The current url option
*/
function changeUserBlock( $cid=null, $block=1, $option ) {
	$action = $block ? 'block' : 'unblock';
	changeUsersStatus( $cid, $action, $block, $option );
}
/**
* Approves or Rejects one or more user records
* @param array An array of unique category id numbers
* @param integer 0 if reject, 1 if approve
* @param string The current url option
*/
function approveUser( $cid=null, $approved=1, $option ) {
	$action = $approved ? 'Approve' : 'Reject';
	changeUsersStatus( $cid, $action, $approved, $option );
}

/**
 * Change users status
 *
 * @param array of int $cid
 * @param string       $action   ( Approve, Reject, block, unblock )
 * @param int          $actionValue
 * @param string       $option
 */
function changeUsersStatus( $cid=null, $action, $actionValue, $option ) {
    global $_CB_database, $ueConfig, $_PLUGINS;
    
    if (count( $cid ) < 1) {
    	echo "<script type=\"text/javascript\"> alert('Select an item to $action'); window.history.go(-1);</script>\n";
    	exit;
    }
	$msg = checkCBpermissions( $cid, $action );
	if ($msg) {
		echo "<script type=\"text/javascript\"> alert('".$msg."'); window.history.go(-1);</script>\n";
		exit;
	}

	cbArrayToInts($cid);
	$cids = implode( ',', $cid );

    $_PLUGINS->loadPluginGroup('user');
	$query = "SELECT * FROM #__comprofiler c, #__users u WHERE c.id=u.id AND c.id IN ( " . $cids . " )";
	$_CB_database->setQuery($query);
	$users = $_CB_database->loadObjectList();
	
	foreach ( $users as $row ) {
		switch ( $action ) {
			case 'Approve':
			case 'Reject':
				if ($actionValue == 0) {
					$approved = 2;		// "rejected"
				} else {
					$approved = $actionValue;
				}
				$_PLUGINS->trigger( 'onBeforeUserApproval', array( $row, $approved ) );
				$_CB_database->setQuery( "UPDATE #__comprofiler SET approved=" . (int) $approved . " WHERE id = " . (int) $row->id );
				if ($_CB_database->query()) {
					if($approved==1) {
						if( isset( $ueConfig['emailpass'] ) && ( $ueConfig['emailpass'] == "1" ) ) {
							$pwd			=	cbMakeRandomString( 8, true );
							$row->password	=	$pwd;
							$pwd			=	cbHashPassword( $pwd );
							$_CB_database->setQuery( "UPDATE #__users SET password='" . $_CB_database->getEscaped($pwd) . "' WHERE id = " . (int) $row->id );
			    			$_CB_database->query();
							//createEmail($row, 'welcome', $ueConfig,null,1);
						} 
						$_PLUGINS->trigger('onAfterUserApproval',array($row,$approved,true));
						if ($row->approved == 0 && $approved == 1 && $row->confirmed == 1 ) {
							$row->approved = 1;
							activateUser($row, 2, "UserApproval", false);
						}
					}
				}
				break;
		
			case 'block':
			case 'unblock':
				$_PLUGINS->trigger( 'onBeforeUserBlocking', array( $row, $actionValue ) );
				$_CB_database->setQuery( "UPDATE #__users SET block = " . (int) $actionValue . " WHERE id = " . (int) $row->id );
				if ($_CB_database->query()) {
					// if action is to block a user, delete user acounts active sessions
					if ( $actionValue == 1 ) {
						$query = "DELETE FROM #__session"
					 	. "\n WHERE userid = " . (int) $row->id;
						$_CB_database->setQuery( $query );
						$_CB_database->query();
					}
				}
				break;
		
			default:
				echo "<script type=\"text/javascript\"> alert('unknown action ".$action."'); window.history.go(-1);</script>\n";
				exit;
				break;
		}
	}
    cbRedirect( "index2.php?option=$option&task=showusers" );
}

function cbIsEmail($email){
	$rBool=false;

	if(preg_match("/[\\w\\.\\-]+@\\w+[\\w\\.\\-]*?\\.\\w{1,4}/", $email)){
		$rBool=true;
	}
	return $rBool;
}
function showConfig( $option ) {
	global $_CB_framework, $_CB_database,$ueConfig;
	global $_CB_adminpath;

	cbimport( 'cb.adminfilesystem' );
	$adminFS			=&	cbAdminFileSystem::getInstance();

	$configfile			=	$_CB_adminpath."/ue_config.php";

	//Add code to check if config file is writeable.
	if ( $adminFS->isUsingStandardPHP() && ! is_writable($configfile)) {
		@chmod ($configfile, 0766);
		$permission = is_writable($configfile);
		if (!$permission) {
			echo "<center><h1><font color=red>" . _UE_WARNING . "...</font></h1><BR>";
			echo "<b>" . _UE_YOUR_CONFIG_FILE . ": $configfile <font color=red>" . _UE_IS_NOT_WRITABLE . "</font></b><br />";
			echo "<b>" . _UE_NEED_TO_CHMOD_CONFIG . "</b></center><br /><br />";
		}
	}
	
	$lists = array();	
	// make a standard yes/no list
	$yesno = array();
	$yesno[] = moscomprofilerHTML::makeOption( '0', _UE_NO );
	$yesno[] = moscomprofilerHTML::makeOption( '1', _UE_YES );
	
	$admin_allowcbregistration = array();
	$admin_allowcbregistration[] = moscomprofilerHTML::makeOption( '0', _UE_REG_ALLOWREG_SAME_AS_GLOBAL );
	$admin_allowcbregistration[] = moscomprofilerHTML::makeOption( '1', _UE_REG_ALLOWREG_YES );
	
	$conNotifyTypes=array();
	$conNotifyTypes[] = moscomprofilerHTML::makeOption( '0', _UE_NONE );
	$conNotifyTypes[] = moscomprofilerHTML::makeOption( '1', 'Email' );
	$conNotifyTypes[] = moscomprofilerHTML::makeOption( '2', 'PMS' );
	$conNotifyTypes[] = moscomprofilerHTML::makeOption( '3', 'PMS+Email' );
	
	$dateformats = array();
	$dateformats[] = moscomprofilerHTML::makeOption('Y/m/d','yyyy/mm/dd');
	$dateformats[] = moscomprofilerHTML::makeOption('d/m/y','dd/mm/yy');
	$dateformats[] = moscomprofilerHTML::makeOption('y/m/d','yy/mm/dd');
	$dateformats[] = moscomprofilerHTML::makeOption('d/m/Y','dd/mm/yyyy');
	$dateformats[] = moscomprofilerHTML::makeOption('m/d/y','mm/dd/yy');
	$dateformats[] = moscomprofilerHTML::makeOption('m/d/Y','mm/dd/yyyy');
	$dateformats[] = moscomprofilerHTML::makeOption('Y-m-d','yyyy-mm-dd');
	$dateformats[] = moscomprofilerHTML::makeOption('d-m-y','dd-mm-yy');
	$dateformats[] = moscomprofilerHTML::makeOption('y-m-d','yy-mm-dd');
	$dateformats[] = moscomprofilerHTML::makeOption('d-m-Y','dd-mm-yyyy');
	$dateformats[] = moscomprofilerHTML::makeOption('m-d-y','mm-dd-yy');
	$dateformats[] = moscomprofilerHTML::makeOption('m-d-Y','mm-dd-yyyy');
	$dateformats[] = moscomprofilerHTML::makeOption('Y.m.d','yyyy.mm.dd');
	$dateformats[] = moscomprofilerHTML::makeOption('d.m.y','dd.mm.yy');
	$dateformats[] = moscomprofilerHTML::makeOption('y.m.d','yy.mm.dd');
	$dateformats[] = moscomprofilerHTML::makeOption('d.m.Y','dd.mm.yyyy');
	$dateformats[] = moscomprofilerHTML::makeOption('m.d.y','mm.dd.yy');
	$dateformats[] = moscomprofilerHTML::makeOption('m.d.Y','mm.dd.yyyy');
	
	$calendartypes = array();
	$calendartypes[] = moscomprofilerHTML::makeOption('2', _UE_CALENDAR_TYPE_DROPDOWN_POPUP );
	$calendartypes[] = moscomprofilerHTML::makeOption('1', _UE_CALENDAR_TYPE_POPUP );

	$nameformats = array();
	$nameformats[] = moscomprofilerHTML::makeOption('1', _UE_REG_NAMEFORMAT_NAME_ONLY );
	$nameformats[] = moscomprofilerHTML::makeOption('2', _UE_REG_NAMEFORMAT_NAME_USERNAME );
	$nameformats[] = moscomprofilerHTML::makeOption('3', _UE_REG_NAMEFORMAT_USERNAME_ONLY );
	$nameformats[] = moscomprofilerHTML::makeOption('4', _UE_REG_NAMEFORMAT_USERNAME_NAME );

	$imgToolBox 				=	new imgToolBox();
	$imgToolBox->_IM_path		=	$ueConfig['im_path'];
	$imgToolBox->_NETPBM_path	=	$ueConfig['netpbm_path'];
	$imageLibs					=	$imgToolBox->getImageLibs();
	$conversiontype				=	array();
	if(array_key_exists('imagemagick',$imageLibs)|| ($ueConfig['conversiontype']=='1')) $conversiontype[] = moscomprofilerHTML::makeOption('1','ImageMagick');
	if(array_key_exists('netpbm',$imageLibs)	 || ($ueConfig['conversiontype']=='2')) $conversiontype[] = moscomprofilerHTML::makeOption('2','NetPBM');
	if(array_key_exists('gd1',$imageLibs['gd'])	 || ($ueConfig['conversiontype']=='3')) $conversiontype[] = moscomprofilerHTML::makeOption('3','GD1 library');
	if(array_key_exists('gd2',$imageLibs['gd'])	 || ($ueConfig['conversiontype']=='4')) $conversiontype[] = moscomprofilerHTML::makeOption('4','GD2 library');
	
	$namestyles = array();
	$namestyles[] = moscomprofilerHTML::makeOption('1', _UE_REG_NAMEFORMAT_SINGLE_FIELD );
	$namestyles[] = moscomprofilerHTML::makeOption('2', _UE_REG_NAMEFORMAT_TWO_FIELDS );
	$namestyles[] = moscomprofilerHTML::makeOption('3', _UE_REG_NAMEFORMAT_THREE_FIELDS );
	
	$emailhandling = array();
	$emailhandling[] = moscomprofilerHTML::makeOption('1', _UE_REG_EMAILDISPLAY_EMAIL_ONLY );
	$emailhandling[] = moscomprofilerHTML::makeOption('2', _UE_REG_EMAILDISPLAY_EMAIL_W_MAILTO );
	$emailhandling[] = moscomprofilerHTML::makeOption('3', _UE_REG_EMAILDISPLAY_EMAIL_W_FORM );
	$emailhandling[] = moscomprofilerHTML::makeOption('4', _UE_REG_EMAILDISPLAY_EMAIL_NO );

	$emailreplyto = array();
	$emailreplyto[] = moscomprofilerHTML::makeOption('1',_UE_A_FROM_USER );
	$emailreplyto[] = moscomprofilerHTML::makeOption('2',_UE_A_FROM_ADMIN );

	$email_checker = array();
	$email_checker[] = moscomprofilerHTML::makeOption( '0', _UE_NO );
	$email_checker[] = moscomprofilerHTML::makeOption( '1', _UE_REG_EMAILCHECKER_VALID_EMAIL_ONLY );
	$email_checker[] = moscomprofilerHTML::makeOption( '2', _UE_REG_EMAILCHECKER_NOT_REGISTERED_AND_VALID_EMAIL );

	$connectionDisplay = array();
	$connectionDisplay[] = moscomprofilerHTML::makeOption( '0', _UE_PUBLIC );
	$connectionDisplay[] = moscomprofilerHTML::makeOption( '1', _UE_PRIVATE );

	$noVersionCheck = array();
	$noVersionCheck[] = moscomprofilerHTML::makeOption( '0', _UE_AUTOMATIC );
	$noVersionCheck[] = moscomprofilerHTML::makeOption( '1', _UE_MANUAL );

	$userprofileEdits = array();
	$userprofileEdits[] = moscomprofilerHTML::makeOption( '0', _UE_NO );
	$userprofileEdits[] = moscomprofilerHTML::makeOption( '1', _UE_MODERATORS_AND_ABOVE );
	$userprofileEdits[] = moscomprofilerHTML::makeOption( '24', _UE_ADMINS_AND_SUPERADMINS_ONLY );
	$userprofileEdits[] = moscomprofilerHTML::makeOption( '25', _UE_SUPERADMINS_ONLY );

	$reg_show_icons_explain = array();
	$reg_show_icons_explain[] = moscomprofilerHTML::makeOption( '0', _UE_NO );
	$reg_show_icons_explain[] = moscomprofilerHTML::makeOption( '1', _UE_TOP );
	$reg_show_icons_explain[] = moscomprofilerHTML::makeOption( '2', _UE_BOTTOM );
	$reg_show_icons_explain[] = moscomprofilerHTML::makeOption( '3', _UE_TOP_AND_BOTTOM );

	$icons_display = array();
	$icons_display[] = moscomprofilerHTML::makeOption( '0', _UE_NO );
	$icons_display[] = moscomprofilerHTML::makeOption( '1', _UE_REQUIRED_ONLY );
	$icons_display[] = moscomprofilerHTML::makeOption( '2', _UE_PROFILE_ONLY );
	$icons_display[] = moscomprofilerHTML::makeOption( '3', _UE_REQUIRED_AND_PROFILE_ONLY );
	$icons_display[] = moscomprofilerHTML::makeOption( '4', _UE_INFO_ONLY );
	$icons_display[] = moscomprofilerHTML::makeOption( '5', _UE_REQUIRED_AND_INFO_ONLY );
	$icons_display[] = moscomprofilerHTML::makeOption( '6', _UE_PROFILE_AND_INFO_ONLY );
	$icons_display[] = moscomprofilerHTML::makeOption( '7', _UE_REQUIRED_PROFILE_AND_INFO );

	//TBD NEXT 9 LINES: CB 1.2 RC 2+4 + CB 1.2 specific : remove after !
	if ( ! defined( '_UE_USERNAME_OR_EMAIL' ) ) {
		DEFINE('_UE_USERNAME_OR_EMAIL','Username or email');
	}
	if ( ! defined( '_UE_USERNAME_OR_AUTH' ) ) {
		DEFINE('_UE_USERNAME_OR_AUTH','Username, email or enabled CMS authentication plugins');
	}
	if ( ! defined( '_UE_LOGIN_TYPE' ) ) {
		DEFINE('_UE_LOGIN_TYPE','Login field type');
	}
	if ( ! defined( '_UE_LOGIN_TYPE_DESC' ) ) {
		DEFINE('_UE_LOGIN_TYPE_DESC','Login can be by username + password, username or email + password, or email + password. The CB login module also adapts accordingly.');
	}
	$login_type = array();
	$login_type[] = moscomprofilerHTML::makeOption( '0', _UE_USERNAME );
	$login_type[] = moscomprofilerHTML::makeOption( '1', _UE_USERNAME_OR_EMAIL );
	$login_type[] = moscomprofilerHTML::makeOption( '2', _UE_EMAIL );
	$login_type[] = moscomprofilerHTML::makeOption( '3', _UE_USERNAME_OR_AUTH );

	// ensure user can't add group higher than themselves
	if ( checkJversion() <= 0 ) {
		$my_groups 	= $_CB_framework->acl->get_object_groups( 'users', $_CB_framework->myId(), 'ARO' );
	} else {
		$aro_id		= $_CB_framework->acl->get_object_id( 'users', $_CB_framework->myId(), 'ARO' );
		$my_groups 	= $_CB_framework->acl->get_object_groups( $aro_id, 'ARO' );
	}
	//print_r($my_groups);

	if (is_array( $my_groups ) && count( $my_groups ) > 0) {
		$ex_groups = $_CB_framework->acl->get_group_children( $my_groups[0], 'ARO', 'RECURSE' );
	} else {
		$ex_groups = array();
	}
	//print_r($ex_groups);
	$gtree = $_CB_framework->acl->get_group_children_tree( null, 'USERS', false );
	// remove users 'above' me

	$i = 0;
	if (is_array($ex_groups)) {
		while ($i < count( $gtree )) {
			if (in_array( $gtree[$i]->value, $ex_groups )) {
				array_splice( $gtree, $i, 1 );
			} else {
				$i++;
			}
		}
	}
	$gtree2=array();
        $gtree2[] = moscomprofilerHTML::makeOption( -2 , '- ' ._UE_GROUPS_EVERYBODY . ' -' );				// '- Everybody -'
        $gtree2[] = moscomprofilerHTML::makeOption( -1, '- ' . _UE_GROUPS_ALL_REG_USERS . ' -' );			// '- All Registered Users -'
	$gtree2 = array_merge( $gtree2, $_CB_framework->acl->get_group_children_tree( null, 'USERS', false ));


   	$lists['imageApproverGid'] = moscomprofilerHTML::selectList( $gtree, 'cfg_imageApproverGid', 'size="4"', 'value', 'text', $ueConfig['imageApproverGid'], 2, false );

	$lists['allow_profileviewbyGID']=moscomprofilerHTML::selectList( $gtree2, 'cfg_allow_profileviewbyGID', 'size="4"', 'value', 'text', $ueConfig['allow_profileviewbyGID'], 2, false );
	//$lists['allow_listviewbyGID']=moscomprofilerHTML::selectList( $gtree2, 'cfg_allow_listviewbyGID', 'size="4"', 'value', 'text', $ueConfig['allow_listviewbyGID'], 2 );
   // registered users only
  	$tempdir		=	array();
	$_CB_database->setQuery("SELECT `name`,`folder` FROM `#__comprofiler_plugin` WHERE `type`='templates' AND `published`=1 ORDER BY ordering");
	//echo $_CB_database->getQuery();
	$templates		=	$_CB_database->loadObjectList();
	foreach ( $templates AS $template ) {
		$tempdir[]	=	moscomprofilerHTML::makeOption( $template->folder , $template->name );
	}
	/*
	require($_CB_framework->getCfg('absolute_path').'/components/com_comprofiler/plugin/user/plug_yancintegration/yanc.php');
	$getNewslettersTab= new getNewslettersTab();
	$newslettersList = $getNewslettersTab->getNewslettersList();
	$newslettersRegList = array();
	if ($newslettersList !== false) {
		foreach ($newslettersList AS $nl) {
			$newslettersRegList[] = moscomprofilerHTML::makeOption( $nl->id, $nl->list_name);
		}
	}
	*/
	$cbFielfs = & new cbFields();
	$badHtmlFilter = & $cbFielfs->getInputFilter( array (), array (), 1, 1 );
	$lists['_filteredbydefault']	=	implode( ' ', $badHtmlFilter->tagBlacklist );
	if ( ! isset( $ueConfig['html_filter_allowed_tags'] ) ) {
		$ueConfig['html_filter_allowed_tags']	=	'';
	}

	$lists['allow_email_display'] = moscomprofilerHTML::selectList( $emailhandling, 'cfg_allow_email_display', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['allow_email_display'], 2 );
	
	$lists['allow_email_replyto'] = moscomprofilerHTML::selectList( $emailreplyto, 'cfg_allow_email_replyto', 'class="inputbox" size="1"', 'value', 'text', (isset($ueConfig['allow_email_replyto']) ? $ueConfig['allow_email_replyto'] : '1'), 2 );
	
	$lists['name_format'] = moscomprofilerHTML::selectList($nameformats, 'cfg_name_format','class="inputbox" size="1"', 'value', 'text', $ueConfig['name_format'], 2 );
	
	$lists['name_style'] = moscomprofilerHTML::selectList($namestyles, 'cfg_name_style','class="inputbox" size="1"', 'value', 'text', $ueConfig['name_style'], 2 );
	
	$lists['date_format'] = moscomprofilerHTML::selectList($dateformats, 'cfg_date_format','class="inputbox" size="1"', 'value', 'text', $ueConfig['date_format'], 2 );
	$lists['calendar_type'] = moscomprofilerHTML::selectList($calendartypes, 'cfg_calendar_type','class="inputbox" size="1"', 'value', 'text', ( isset( $ueConfig['calendar_type'] ) ? $ueConfig['calendar_type'] : '2' ), 2 );
	
	$lists['usernameedit'] = moscomprofilerHTML::selectList( $yesno, 'cfg_usernameedit', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['usernameedit'], 2 );
	
	$lists['allow_profilelink'] = moscomprofilerHTML::selectList( $yesno, 'cfg_allow_profilelink', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['allow_profilelink'], 2 );
	
	$lists['allow_email'] = moscomprofilerHTML::selectList( $yesno, 'cfg_allow_email', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['allow_email'], 2 );
	$lists['allow_onlinestatus'] = moscomprofilerHTML::selectList( $yesno, 'cfg_allow_onlinestatus', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['allow_onlinestatus'], 2 );
	$lists['allow_website'] = moscomprofilerHTML::selectList( $yesno, 'cfg_allow_website', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['allow_website'], 2 );
	$lists['icons_display'] = moscomprofilerHTML::selectList( $icons_display, 'cfg_icons_display', 'class="inputbox" size="1"', 'value', 'text', ( isset( $ueConfig['icons_display'] ) ? $ueConfig['icons_display'] : '7' ), 2 );
	$lists['login_type'] = moscomprofilerHTML::selectList( $login_type, 'cfg_login_type', 'class="inputbox" size="1"', 'value', 'text', ( isset( $ueConfig['login_type'] ) ? $ueConfig['login_type'] : '0' ), 2 );
	
	$lists['reg_enable_toc'] = moscomprofilerHTML::selectList( $yesno, 'cfg_reg_enable_toc', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['reg_enable_toc'], 2 );
	
	$lists['admin_allowcbregistration'] = moscomprofilerHTML::selectList( $admin_allowcbregistration, 'cfg_reg_admin_allowcbregistration', 'class="inputbox" size="1"', 'value', 'text', (isset($ueConfig['reg_admin_allowcbregistration']) ? $ueConfig['reg_admin_allowcbregistration'] : '0' ), 2 );
	$lists['emailpass'] = moscomprofilerHTML::selectList( $yesno, 'cfg_emailpass', 'class="inputbox" size="1"', 'value', 'text', (isset($ueConfig['emailpass']) ? $ueConfig['emailpass'] : '0' ), 2 );
	
	$lists['admin_approval'] = moscomprofilerHTML::selectList( $yesno, 'cfg_reg_admin_approval', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['reg_admin_approval'], 2 );
	
	$lists['confirmation'] = moscomprofilerHTML::selectList( $yesno, 'cfg_reg_confirmation', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['reg_confirmation'], 2 );

	$lists['reg_username_checker'] = moscomprofilerHTML::selectList( $yesno, 'cfg_reg_username_checker', 'class="inputbox" size="1"', 'value', 'text', ( isset( $ueConfig['reg_username_checker'] ) ? $ueConfig['reg_username_checker'] : '0' ), 2 );
	$lists['reg_email_checker'] = moscomprofilerHTML::selectList( $email_checker, 'cfg_reg_email_checker', 'class="inputbox" size="1"', 'value', 'text', ( isset( $ueConfig['reg_email_checker'] ) ? $ueConfig['reg_email_checker'] : '0' ), 2 );
	
	$lists['reg_show_login_on_page'] = moscomprofilerHTML::selectList( $yesno, 'cfg_reg_show_login_on_page', 'class="inputbox" size="1"', 'value', 'text', ( isset( $ueConfig['reg_show_login_on_page'] ) ? $ueConfig['reg_show_login_on_page'] : '0' ), 2 );
	
	$lists['reg_show_icons_explain'] = moscomprofilerHTML::selectList( $reg_show_icons_explain, 'cfg_reg_show_icons_explain', 'class="inputbox" size="1"', 'value', 'text', ( isset( $ueConfig['reg_show_icons_explain'] ) ? $ueConfig['reg_show_icons_explain'] : '3' ), 2 );
	
	$lists['allowAvatar'] = moscomprofilerHTML::selectList( $yesno, 'cfg_allowAvatar', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['allowAvatar'], 2 );
	
	$lists['allowAvatarUpload'] = moscomprofilerHTML::selectList( $yesno, 'cfg_allowAvatarUpload', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['allowAvatarUpload'], 2 );
	$lists['avatarResizeAlways'] = moscomprofilerHTML::selectList( $yesno, 'cfg_avatarResizeAlways', 'class="inputbox" size="1"', 'value', 'text', isset( $ueConfig['avatarResizeAlways'] ) ? $ueConfig['avatarResizeAlways'] : '1', 2 );
	
	$lists['allowAvatarGallery'] = moscomprofilerHTML::selectList( $yesno, 'cfg_allowAvatarGallery', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['allowAvatarGallery'], 2 );
	
	$lists['avatarUploadApproval'] = moscomprofilerHTML::selectList( $yesno, 'cfg_avatarUploadApproval', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['avatarUploadApproval'], 2 );

	$lists['allowUserReports'] = moscomprofilerHTML::selectList( $yesno, 'cfg_allowUserReports', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['allowUserReports'], 2 );
	$lists['allowModeratorsUserEdit'] = moscomprofilerHTML::selectList( $userprofileEdits, 'cfg_allowModeratorsUserEdit', 'class="inputbox" size="1"', 'value', 'text', isset($ueConfig['allowModeratorsUserEdit']) ? $ueConfig['allowModeratorsUserEdit'] : '0', 2 );
	$lists['allowUserBanning'] = moscomprofilerHTML::selectList( $yesno, 'cfg_allowUserBanning', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['allowUserBanning'], 2 );
	$lists['adminrequiredfields'] = moscomprofilerHTML::selectList( $yesno, 'cfg_adminrequiredfields', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['adminrequiredfields'], 2 );
	$lists['moderatorEmail'] = moscomprofilerHTML::selectList( $yesno, 'cfg_moderatorEmail', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['moderatorEmail'], 2 );
	$lists['allowModUserApproval'] = moscomprofilerHTML::selectList( $yesno, 'cfg_allowModUserApproval', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['allowModUserApproval'], 2 );
	$lists['templatedir'] = moscomprofilerHTML::selectList( $tempdir, 'cfg_templatedir', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['templatedir'], 2 );
	$lists['showEmptyTabs'] = moscomprofilerHTML::selectList( $yesno, 'cfg_showEmptyTabs', 'class="inputbox" size="1"', 'value', 'text', isset( $ueConfig['showEmptyTabs'] ) ? $ueConfig['showEmptyTabs'] : 0, 2 );
	$lists['showEmptyFields'] = moscomprofilerHTML::selectList( $yesno, 'cfg_showEmptyFields', 'class="inputbox" size="1"', 'value', 'text', isset( $ueConfig['showEmptyFields'] ) ? $ueConfig['showEmptyFields'] : 0, 2 );
	$lists['nesttabs'] = moscomprofilerHTML::selectList( $yesno, 'cfg_nesttabs', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['nesttabs'], 2 );
	$lists['xhtmlComply'] = moscomprofilerHTML::selectList( $yesno, 'cfg_xhtmlComply', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['xhtmlComply'], 2 );
	$lists['frontend_userparams'] = moscomprofilerHTML::selectList( $yesno, 'cfg_frontend_userparams', 'class="inputbox" size="1"', 'value', 'text', isset( $ueConfig['frontend_userparams'] ) ? $ueConfig['frontend_userparams'] : ( in_array( $_CB_framework->getCfg( "frontend_userparams" ), array( '1', null) ) ? '1' : '0' ), 2 );
	$lists['conversiontype'] = moscomprofilerHTML::selectList( $conversiontype, 'cfg_conversiontype', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['conversiontype'], 2 );
	$lists['allowConnections'] = moscomprofilerHTML::selectList( $yesno, 'cfg_allowConnections', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['allowConnections'], 2 );
	$lists['useMutualConnections'] = moscomprofilerHTML::selectList( $yesno, 'cfg_useMutualConnections', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['useMutualConnections'], 2 );
	$lists['autoAddConnections'] = moscomprofilerHTML::selectList( $yesno, 'cfg_autoAddConnections', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['autoAddConnections'], 2 );
	$lists['conNotifyTypes'] = moscomprofilerHTML::selectList( $conNotifyTypes, 'cfg_conNotifyType', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['conNotifyType'], 2 );
	$lists['connectionDisplay'] = moscomprofilerHTML::selectList( $connectionDisplay, 'cfg_connectionDisplay', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['connectionDisplay'], 2 );
	$lists['connectionPath'] = moscomprofilerHTML::selectList( $yesno, 'cfg_connectionPath', 'class="inputbox" size="1"', 'value', 'text', $ueConfig['connectionPath'], 2 );
	$lists['noVersionCheck'] = moscomprofilerHTML::selectList( $noVersionCheck, 'cfg_noVersionCheck', 'class="inputbox" size="1"', 'value', 'text', isset($ueConfig['noVersionCheck']) ? $ueConfig['noVersionCheck'] : '0', 2 );
	
	HTML_comprofiler::showConfig( $ueConfig, $lists, $option );
}

function saveConfig ( $option ) {
	global $_CB_adminpath, $_POST;

	cbimport( 'cb.adminfilesystem' );
	$adminFS			=&	cbAdminFileSystem::getInstance();

	$configfile			=	$_CB_adminpath."/ue_config.php";

	//Add code to check if config file is writeable.
	if ( $adminFS->isUsingStandardPHP() && ! is_writable($configfile)) {
		@chmod ($configfile, 0766);
		if (!is_writable($configfile)) {
			cbRedirect("index2.php?option=$option", "FATAL ERROR: Config File Not writeable" );
		}
	}

	// safely evaluate post:

	$newConfig		=	array();
	foreach ( $_POST as $k => $v ) {
		$newVal		=	cbGetParam( $_POST, $k, '', _CB_ALLOWRAW | _CB_NOTRIM );
		//TBD later when moving to DB storage:
		// $newVal	=	stripslashes( $newVal );
		// then check for stripslashes all over the place incl. in configuration display and email of welcome messages
		if ( is_array( $newVal ) ) {
			$newVal	=	implode( '|*|', $newVal );
		}
		if ( strpos( $k, 'cfg_' ) === 0 ) {
			$newK	=	addslashes( substr( $k, 4 ) );
			$newConfig[$newK]	=	$newVal;
		}
	}

	// compose PHP ueconfig.php file:

	$txt = "<?php\n";
	foreach ( $newConfig as $k => $v ) {
			$txt .= "\$ueConfig['" . $k . "']='$v';\n";
	}
	$txt .= "?>";

	// write file:
/*
	if (is_callable(array("JFile","write"))) {
		$result = JFile::write( $configfile, $txt );
	} else {
		$result = false;
		$fp = fopen( $configfile, "w");
		if ( $fp ) {
			$result = fwrite($fp, $txt, strlen($txt));
			fclose ($fp);
		}
	}
*/

	$result			=	$adminFS->file_put_contents( $configfile, $txt );
	if ( $result ) {
		if ( _cbAdmin_chmod( $configfile ) ) {
			$msg	=	"Configuration file saved";
		} else {
			$msg	=	'Failed to change the permissions of the config file '.$configfile;
		}
	} else {
		$msg		=	'Failed to create and write config file in '.$configfile;
	}

	if ( $result !== false ) {

		// adapt name fields to new name:
		_cbAdaptNameFieldsPublished( $newConfig );

		cbRedirect( "index2.php?option=$option&task=showconfig", $msg );
	} else {
		cbRedirect( "index2.php?option=$option", $msg . ': ' . "ERROR: Configuration file administrator/components/com_comprofiler/ue_config.php could not be written by webserver. Please change file permissions in your web-pannel." );
	}
}

function _cbAdaptNameFieldsPublished( &$newConfig ) {
	global $_CB_database;

	if ( ! isset( $newConfig['this_is_default_config'] ) ) {
		// checks and adapts only if it's not the default configuration:
		switch ( $newConfig['name_style'] ) {
			case 2:
				$sqlArray	=	array( 'name'	=>	0, 'firstname'	=>	1, 'middlename' => 0,	'lastname' => 1 );
				break;
			case 3:
				$sqlArray	=	array( 'name'	=>	0, 'firstname'	=>	1, 'middlename' => 1,	'lastname' => 1 );
				break;
			case 1:
			default:
				$sqlArray	=	array( 'name'	=>	1, 'firstname'	=>	0, 'middlename' => 0,	'lastname' => 0 );
				break;
		}
		foreach ( $sqlArray as $name => $published ) {
			$sql			=	'UPDATE #__comprofiler_fields SET '
							.	$_CB_database->NameQuote( 'published' )
							.	' = '
							.	(int) $published
							.	' WHERE '
							.	$_CB_database->NameQuote( 'name' )
							.	' = '
							.	$_CB_database->Quote( $name )
							;
			$_CB_database->setQuery( $sql );
			if ( ! $_CB_database->query() ) {
			    echo "<script type=\"text/javascript\"> alert('_cbAdaptNameFieldsPublished: db error: " . addslashes( $_CB_database->getErrorMsg() ) . "'); window.history.go(-1);</script>\n";
			    exit;
			}
		}
	}
}
function requiredField( $cid=null, $flag=1, $option ) {
    global $_CB_database;
    
	if (count( $cid ) < 1) {
   	    $action = $flag ? 'Make Required' : 'Make Non-required';
	    echo "<script type=\"text/javascript\"> alert('Select an item to $action'); window.history.go(-1);</script>\n";
	    exit;
	}

	foreach ($cid AS $cids) {
		$_CB_database->setQuery( "UPDATE #__comprofiler_fields SET required = " . (int) $flag . " WHERE fieldid = " . (int) $cids);
	    	$_CB_database->query();
		//print $_CB_database->getquery();
	}
    cbRedirect( "index2.php?option=$option&task=showField" );
}

function publishField( $cid=null, $flag=1, $option ) {
    global $_CB_database;
    
	if (count( $cid ) < 1) {
   	    $action = $flag ? 'Publish' : 'UnPublish';
	    echo "<script type=\"text/javascript\"> alert('Select an item to $action'); window.history.go(-1);</script>\n";
	    exit;
	}

	foreach ($cid AS $cids) {
		$_CB_database->setQuery( "UPDATE #__comprofiler_fields SET published = " . (int) $flag . " WHERE fieldid = " . (int) $cids . " AND sys = 0" );
	    	$_CB_database->query();
		//print $_CB_database->getquery();
	}
    cbRedirect( "index2.php?option=$option&task=showField" );
}

function registrationField( $cid=null, $flag=1, $option ) {
    global $_CB_database;
    
	if (count( $cid ) < 1) {
   	    $action = $flag ? 'Add to Registration' : 'Remove from Registration';
	    echo "<script type=\"text/javascript\"> alert('Select an item to $action'); window.history.go(-1);</script>\n";
	    exit;
	}

	foreach ($cid AS $cids) {
		$_CB_database->setQuery( "UPDATE #__comprofiler_fields SET registration = " . (int) $flag . " WHERE fieldid = " . (int) $cids);
	    	$_CB_database->query();
		//print $_CB_database->getquery();
	}
    cbRedirect( "index2.php?option=$option&task=showField" );
}

function searchableField( $cid=null, $flag=1, $option ) {
    global $_CB_database;

    $flag		=	$flag ? 1 : 0;
	$action		=	$flag ? 'field searchable in users-lists' : 'field not searchable in users-lists';
	if (count( $cid ) < 1) {
	    echo "<script type=\"text/javascript\"> alert('Select an item to make $action'); window.history.go(-1);</script>\n";
	    exit;
	}

	foreach ($cid AS $cids) {
		$_CB_database->setQuery( "UPDATE #__comprofiler_fields SET searchable = " . (int) $flag . " WHERE fieldid = " . (int) $cids);
	    	$_CB_database->query();
		//print $_CB_database->getquery();
	}
    cbRedirect( "index2.php?option=$option&task=showField", "Made " . $action );
}

function listPublishedField( $cid=null, $flag=1, $option ) {
    global $_CB_database;
    
	if (count( $cid ) < 1) {
   	    $action = $flag ? 'Publish' : 'UnPublish';
	    echo "<script type=\"text/javascript\"> alert('Select an item to $action'); window.history.go(-1);</script>\n";
	    exit;
	}

	foreach ($cid AS $cids) {
		$_CB_database->setQuery( "UPDATE #__comprofiler_lists SET published = " . (int) $flag . " WHERE listid = " . (int) $cids);
	    	$_CB_database->query();
		//print $_CB_database->getquery();
	}
    cbRedirect( "index2.php?option=$option&task=showLists" );
}
function tabPublishedField( $cid=null, $flag=1, $option ) {
    global $_CB_database;
    
	if (count( $cid ) < 1) {
   	    $action = $flag ? 'Publish' : 'UnPublish';
	    echo "<script type=\"text/javascript\"> alert('Select an item to $action'); window.history.go(-1);</script>\n";
	    exit;
	}

	foreach ($cid AS $cids) {
		$_CB_database->setQuery( "UPDATE #__comprofiler_tabs SET enabled = " . (int) $flag . " WHERE tabid = " . (int) $cids);
	    	$_CB_database->query();
		//print $_CB_database->getquery();
	}
    cbRedirect( "index2.php?option=$option&task=showTab" );
}
function listDefaultField( $cid=null, $flag=1, $option ) {
    global $_CB_database;
    
	if (count( $cid ) < 1) {
   	    $action = $flag ? 'Make Default' : 'Reset Default';
	    echo "<script type=\"text/javascript\"> alert('Select an item to $action'); window.history.go(-1);</script>\n";
	    exit;
	}
	
    $published = "";
	if($flag==1) {
		$published = ", published = 1";
	}
	foreach ($cid AS $cids) {
		$_CB_database->setQuery( "UPDATE #__comprofiler_lists SET `default` = 0");
	    	$_CB_database->query();
		$_CB_database->setQuery( "UPDATE #__comprofiler_lists SET `default` = " . (int) $flag . " $published WHERE listid = " . (int) $cids);
	    	$_CB_database->query();
		//print $_CB_database->getquery();
	}
    cbRedirect( "index2.php?option=$option&task=showLists" );
}

function profileField( $cid=null, $flag=1, $option ) {
    global $_CB_database;
    
	if (count( $cid ) < 1) {
   	    $action = $flag ? 'Add to Profile' : 'Remove from Profile';
	    echo "<script type=\"text/javascript\"> alert('Select an item to $action'); window.history.go(-1);</script>\n";
	    exit;
	}

	foreach ($cid AS $cids) {
		$_CB_database->setQuery( "UPDATE #__comprofiler_fields SET profile = " . (int) $flag . " WHERE fieldid = " . (int) $cids);
	    	$_CB_database->query();
		//print $_CB_database->getquery();
	}
    cbRedirect( "index2.php?option=$option&task=showField" );
}

function loadSampleData() {
    global $_CB_database;
	$sql				=	"SELECT COUNT(*) FROM #__comprofiler_fields"
						.	"\n WHERE name IN ('website','location','occupation','interests','company','address','city','state','zipcode','country','phone','fax')";
	$_CB_database->setQuery($sql);
    	$fieldCount		=	$_CB_database->loadresult();

	IF($fieldCount < 1) {
		$sqlStatements	=	array();

		$sqlStatements[0]['query']	= "INSERT IGNORE INTO `#__comprofiler_tabs` (`tabid`, `title`, `position`, `ordering`, `sys`, `displaytype`) "
			."\n VALUES (2, '_UE_ADDITIONAL_INFO_HEADER', 'cb_tabmain', 1, 0, 'tab')";
		$sqlStatements[0]['message'] = '<font color="green">Tab Added Successfully!</font><br />';

		$sqlStatements[1]['query'] = "ALTER TABLE `#__comprofiler` ADD `website` varchar(255) default NULL,"
			  ."\n ADD `location` varchar(255) default NULL,"
			  ."\n ADD `occupation` varchar(255) default NULL,"
			  ."\n ADD `interests` varchar(255) default NULL,"
			  ."\n ADD `company` varchar(255) default NULL,"
			  ."\n ADD `address` varchar(255) default NULL,"
			  ."\n ADD `city` varchar(255) default NULL,"
			  ."\n ADD `state` varchar(255) default NULL,"
			  ."\n ADD `zipcode` varchar(255) default NULL,"
			  ."\n ADD `country` varchar(255) default NULL,"
			  ."\n ADD `phone` varchar(255) default NULL,"
			  ."\n ADD `fax` varchar(255) default NULL";
		$sqlStatements[1]['message'] = '<font color="green">Schema Changes Added Successfully!</font><br />';

		$sqlStatements[2]['query'] = "INSERT IGNORE INTO `#__comprofiler_fields`  (`fieldid`, `name`, `tablecolumns`, `table`, `title`, `type`, `maxlength`, `size`, `required`, `tabid`, `ordering`, `cols`, `rows`, `value`, `default`, `published`, `registration`, `profile`, `calculated`, `sys`, `pluginid`) "
			."\n VALUES (30, 'website', 'website', '#__comprofiler', '_UE_Website', 'webaddress', 0, 0, 0, 2, 1, 0, 0, NULL, NULL, 1, 0, 1, 0, 0, 1),"
			."\n (31, 'location', 'location', '#__comprofiler', '_UE_Location', 'text', 50, 25, 0, 2, 2, 0, 0, NULL, NULL, 1, 0, 1, 0, 0, 1),"
			."\n (32, 'occupation', 'occupation', '#__comprofiler', '_UE_Occupation', 'text', 0, 0, 0, 2, 3, 0, 0, NULL, NULL, 1, 0, 1, 0, 0, 1),"
			."\n (33, 'interests', 'interests', '#__comprofiler', '_UE_Interests', 'text', 0, 0, 0, 2, 4, 0, 0, NULL, NULL, 1, 0, 1, 0, 0, 1),"
			."\n (34, 'company', 'company', '#__comprofiler', '_UE_Company', 'text', 0, 0, 0, 2, 5, 0, 0, NULL, NULL, 1, 1, 1, 0, 0, 1),"
			."\n (35, 'city', 'city', '#__comprofiler', '_UE_City', 'text', 0, 0, 0, 2, 6, 0, 0, NULL, NULL, 1, 1, 1, 0, 0, 1),"
			."\n (36, 'state', 'state', '#__comprofiler', '_UE_State', 'text', 10, 4, 0, 2, 7, 0, 0, NULL, NULL, 1, 1, 1, 0, 0, 1),"
			."\n (37, 'zipcode', 'zipcode', '#__comprofiler', '_UE_ZipCode', 'text', 0, 0, 0, 2, 8, 0, 0, NULL, NULL, 1, 1, 1, 0, 0, 1),"
			."\n (38, 'country', 'country', '#__comprofiler', '_UE_Country', 'text', 0, 0, 0, 2, 9, 0, 0, NULL, NULL, 1, 1, 1, 0, 0, 1),"
			."\n (40, 'address', 'address', '#__comprofiler', '_UE_Address', 'text', 0, 0, 0, 2, 10, 0, 0, NULL, NULL, 1, 1, 1, 0, 0, 1),"
			."\n (43, 'phone', 'phone', '#__comprofiler', '_UE_PHONE', 'text', 0, 0, 0, 2, 11, 0, 0, NULL, NULL, 1, 1, 1, 0, 0, 1),"
			."\n (44, 'fax', 'fax', '#__comprofiler', '_UE_FAX', 'text', 0, 0, 0, 2, 12, 0, 0, NULL, NULL, 1, 1, 1, 0, 0, 1)";
		$sqlStatements[2]['message'] = '<font color="green">Fields Added Successfully!</font><br />';

		$sqlStatements[3]['query'] = "INSERT INTO `#__comprofiler_lists` (`listid`, `title`, `description`, `published`, `default`, `usergroupids`, `sortfields`, `col1title`, `col1enabled`, `col1fields`, `col2title`, `col2enabled`, `col1captions`, `col2fields`, `col2captions`, `col3title`, `col3enabled`, `col3fields`, `col3captions`, `col4title`, `col4enabled`, `col4fields`, `col4captions`) "
					."\n VALUES (2, 'Members List', 'my Description', 1, 1, '29, 18, 19, 20, 21, 30, 23, 24, 25', '`username` ASC', 'Image', 1, '29', 'Username', 1, 0, '42', 0, 'Other', 1, '26|*|28|*|27', 1, '', 0, '', 0)";

		$sqlStatements[3]['message'] = '<font color="green">List Added Successfully!</font><br />'; 	

		foreach ($sqlStatements AS $sql) {
			$_CB_database->setQuery($sql['query']);
			if (!$_CB_database->query()) {
				print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
				return;
			} else {
				print $sql['message'];
			}
			//print $_CB_database->getquery();
		}
	} else {
		print "Sample Data is already loaded!";
	}
}

function syncUsers() {
    global $_CB_database, $ueConfig, $_PLUGINS;

	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

   	$_PLUGINS->loadPluginGroup('user');	
	$messages	=	$_PLUGINS->trigger( 'onBeforeSyncUser', true );
	foreach ( $messages as $msg ) {
		if ( $msg ) {
			echo "<p>" . $msg . "</p>";
		}
	}
	// 0a. delete user table for bad rows
	$sql = "DELETE FROM #__users WHERE id = 0";
	$_CB_database->setQuery($sql);
	if (!$_CB_database->query()) {
		print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
		return;
	}
	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print "<p><font color='orange'>Deleted ".$affected." not allowed user id 0 entry.</font></p>";
	}

	// 0b. delete comprofiler table for bad rows
	$sql = "DELETE FROM #__comprofiler WHERE id = 0";
	$_CB_database->setQuery($sql);
	if (!$_CB_database->query()) {
		print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
		return;
	}
	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print "<p><font color='orange'>Deleted ".$affected." not allowed user id 0 entry.</font></p>";
	}


    // 1. add missing comprofiler entries, guessing naming depending on CB's name style:
	switch ( $ueConfig['name_style'] ) {
		case 2:
			// firstname + lastname:
 			$sql = "INSERT IGNORE INTO #__comprofiler(id,user_id,lastname,firstname) "
 				  ." SELECT id,id, SUBSTRING_INDEX(name,' ',-1), "
 								 ."SUBSTRING( name, 1, length( name ) - length( SUBSTRING_INDEX( name, ' ', -1 ) ) -1 ) "
 				  ." FROM #__users";    		
		break;
		case 3:
			// firstname + middlename + lastname:
			$sql = "INSERT IGNORE INTO #__comprofiler(id,user_id,middlename,lastname,firstname) "
				 . " SELECT id,id,SUBSTRING( name, INSTR( name, ' ' ) +1,"
				 						  ." length( name ) - INSTR( name, ' ' ) - length( SUBSTRING_INDEX( name, ' ', -1 ) ) -1 ),"
				 		 ." SUBSTRING_INDEX(name,' ',-1),"
				 		 ." IF(INSTR(name,' '),SUBSTRING_INDEX( name, ' ', 1 ),'') "
				 . " FROM #__users";
    		break;
    	default:
 			// name only:
			$sql = "INSERT IGNORE INTO #__comprofiler(id,user_id) SELECT id,id FROM #__users";
   			break;
    }
	$_CB_database->setQuery($sql);
	if (!$_CB_database->query()) {
		print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
		return;
	}
	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print "<p><font color='orange'>Added ".$affected." new entries to Community Builder from users Table.</font></p>";
	}

	$sql = "UPDATE #__comprofiler SET `user_id`=`id`";
	$_CB_database->setQuery($sql);
	if (!$_CB_database->query()) {
		print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
		return;
	}
	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print "<p><font color='orange'>Fixed ".$affected." existing entries in Community Builder: fixed wrong user_id.</font></p>";
	}

	// 2. remove excessive comprofiler entries (e.g. if admin used mambo/joomla delete user function:
	$sql = "SELECT c.id FROM #__comprofiler c LEFT JOIN #__users u ON u.id = c.id WHERE u.id IS NULL";
	$_CB_database->setQuery($sql);
	$users = $_CB_database->loadResultArray();
	if ($_CB_database->getErrorNum()) {
		print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
		return;
	}
	if (count($users)) {
		print "<p><font color='orange'>Removing ".count($users)." entries from Community Builder missing in users Table.</font></p>";
		$msg = deleteUsers($users, true);
		print "<p>".$msg."</p>";
	}
	print "<font color=green>Joomla/Mambo User Table and Joomla/Mambo Community Builder User Table now in sync!</font>";

	$messages	=	$_PLUGINS->trigger( 'onAfterSyncUser', true );
	foreach ( $messages as $msg ) {
		if ( $msg ) {
			echo "<p>" . $msg . "</p>";
		}
	}
}

function checkcbdb( $dbId = 0 ) {
	global $_CB_database, $_CB_framework, $ueConfig, $_PLUGINS;

	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	HTML_comprofiler::secureAboveForm('checkcbdb');

	outputCbTemplate( 2 );
	outputCbJs( 2 );

	global $_CB_Backend_Title;
	$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-tools', "CB Tools: Check database: Results" ) );

	$cbSpoofField			=	cbSpoofField();
	$cbSpoofString			=	cbSpoofString( null, 'cbtools' );

	$version				=	$_CB_database->getVersion();
	$version				=	substr( $version, 0, strpos( $version, '-' ) );

	if ( $dbId == 0 ) {

		echo "<div style='text-align:left;'><p>Checking Community Builder Datbase:</p>";

		// 1. check comprofiler_field_values table for bad rows
		$sql = "SELECT fieldvalueid,fieldid FROM #__comprofiler_field_values WHERE fieldid=0";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $bad_rows === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} elseif (count($bad_rows)!=0) {
			echo "<p><font color=red>Warning: ".count($bad_rows)." entries in Community Builder comprofiler_field_values have bad fieldid values.</font></p>";
	   		foreach ($bad_rows as $bad_row) {
				if ( $bad_row->fieldvalueid == 0 ) {
					echo "<p><font color=red>ZERO fieldvalueid illegal: fieldvalueid=" . $bad_row->fieldvalueid . " fieldid=0</font></p>";
				} else {
					echo "<p><font color=red>fieldvalueid=" . $bad_row->fieldvalueid . " fieldid=0</font></p>";
				}
			}
			echo '<p><font color=red>This one can be fixed by <strong>first backing up database</strong> then <a href="index2.php?option=com_comprofiler&amp;task=fixcbmiscdb&amp;' . $cbSpoofField . '=' . $cbSpoofString . '">by clicking here</a>.</font></p>';
		} else {
			echo "<p><font color=green>All Community Builder comprofiler_field_values table fieldid rows all match existing fields.</font></p>";
		}
		
		// 2.	check if comprofiler_field_values table has entries where corresponding fieldtype value in comprofiler_fields table 
		//		does not allow values
		$sql = "SELECT v.fieldvalueid, v.fieldid, f.name, f.type FROM #__comprofiler_field_values as v, #__comprofiler_fields as f WHERE v.fieldid = f.fieldid AND f.type NOT IN ('checkbox','multicheckbox','select','multiselect','radio')";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $bad_rows === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} elseif (count($bad_rows)!=0) {
			echo "<p><font color=red>Warning: ".count($bad_rows)." entries in Community Builder comprofiler_field_values link back to fields of wrong fieldtype.</font></p>";
			foreach ($bad_rows as $bad_row) {
				echo "<p><font color=red>fieldvalueid=" . $bad_row->fieldvalueid . " fieldtype=" . $bad_row->type ."</font></p>";
			}
			echo "<p><font color=red>This one can be fixed in SQL using a tool like phpMyAdmin.</font></p>";
			// not done automatically since some fields might have field values ! echo '<p><font color=red>This one can be fixed by <strong>first backing up database</strong> then <a href="index2.php?option=com_comprofiler&amp;task=fixcbmiscdb&amp;' . $cbSpoofField . '=' . $cbSpoofString . '">by clicking here</a>.</font></p>';
		} else {
			echo "<p><font color=green>All Community Builder comprofiler_field_values table rows link to correct fieldtype fields in comprofiler_field table.</font></p>";
		}
		
		// 5.	check if all cb defined fields have corresponding comprofiler columns
		$sql = "SELECT * FROM #__comprofiler LIMIT 1";
		$_CB_database->setQuery($sql);
		$all_comprofiler_fields_and_values = $_CB_database->loadAssoc();
		
		$all_comprofiler_fields = array();
		if ( $all_comprofiler_fields_and_values === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} elseif ( is_array( $all_comprofiler_fields_and_values ) ) {
			while ( false != ( list( $_cbfield ) = each( $all_comprofiler_fields_and_values ) ) ) {
				array_push( $all_comprofiler_fields, $_cbfield );
			}
		}
	 	
		$sql							=	"SELECT * FROM #__comprofiler_fields WHERE `name` != 'NA' AND `table` = '#__comprofiler'";
		$_CB_database->setQuery( $sql );
		$field_rows						=	$_CB_database->loadObjectList( null, 'moscomprofilerFields', array( &$_CB_database ) );
		if ( $field_rows === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} else {
			$html_output					=	array();
			$cb11							=	true;
			foreach ( $field_rows as $field_row ) {
				if ( $field_row->tablecolumns !== null ) {
					// CB 1.2 way:
					if ( $field_row->tablecolumns != '' ) {
						$tableColumns			=	explode( ',', $field_row->tablecolumns );
						foreach ( $tableColumns as $col ) {
							if ( ! in_array( $col, $all_comprofiler_fields ) ) {
								$html_output[]	=	"<p><font color=red> - Field " . $field_row->name . " - Column " . $col . " is missing from comprofiler table.</font></p>";
							}
						}
					}
					$cb11					=	false;
				} else {
					// cb 1.1 way
					if ( ! in_array( $field_row->name, $all_comprofiler_fields ) ) {
						$html_output[] = "<p><font color=red> - Column " . $field_row->name . " is missing from comprofiler table.</font></p>";
					}
				}
			}
			if ( count( $html_output ) > 0 ) {
				echo "<p><font color=red>There are " . count( $html_output ) . " column(s) missing in the comprofiler table, which are defined as fields (rows in comprofiler_fields):</font></p>";
				echo implode( '', $html_output );
				echo "<p><font color=red>This one can be fixed by deleting and recreating the field(s) using components -&gt; Community Builder -&gt; Field Management.<br />"
					. "Please additionally make sure that columns in comprofiler table <strong>are not also duplicated in users table</strong>.</font></p>";
			} elseif ( $cb11 ) {
				echo "<p><font color=red>All Community Builder fields from comprofiler_fields are present as columns in the comprofiler table, but comprofiler_fields table is not yet upgraded to CB 1.2 table structure. Just going to Community Builder Fields Management will fix this automatically.</font></p>";
			} else {
				echo "<p><font color=green>All Community Builder fields from comprofiler_fields are present as columns in the comprofiler table.</font></p>";
			}
		}
		// 9. Check if images/comprofiler is writable:
		$folder = 'images/comprofiler/';
		if ( $ueConfig['allowAvatarUpload'] == 1 ) {
			echo "<p>Checking Community Builder folders:</p>";
			if ( ! is_writable( $_CB_framework->getCfg('absolute_path'). '/' . $folder ) ) {
				echo '<font color="red">Avatars and thumbnails folder: ' . $_CB_framework->getCfg('absolute_path') . '/' . $folder . ' is NOT writeable by the webserver.</font>';
			} else {
				echo '<font color="green">Avatars and thumbnails folder is Writeable.</font>';
			}
		}

		cbimport( 'cb.dbchecker' );
		$dbChecker				=	new CBdbChecker( $_CB_database );
		$result					=	$dbChecker->checkCBMandatoryDb( false );
		$dbName					=	"Core CB mandatory basics";
		$messagesAfter			=	array();
		$messagesBefore			=	array();
		HTML_comprofiler::fixcbdbShowResults( $dbChecker, false, false, $result, $messagesBefore, $messagesAfter, $dbName, $dbId );

		$dbChecker				=	new CBdbChecker( $_CB_database );
		$result					=	$dbChecker->checkDatabase( false );

	   	$_PLUGINS->loadPluginGroup('user');	
		$messagesAfter			=	$_PLUGINS->trigger( 'onAfterCheckCbDb', true );
	
		$dbName					=	"Core CB";
		$messagesBefore			=	array();
		HTML_comprofiler::fixcbdbShowResults( $dbChecker, false, false, $result, $messagesBefore, $messagesAfter, $dbName, $dbId );
		echo '</div>';
		// adapt published fields to global CB config (regarding name type)
		_cbAdaptNameFieldsPublished( $ueConfig );

	} elseif ( $dbId == 1 ) {
		// Check plugins db:
		$dbName					=	"CB plugin";
		$messagesBefore			=	array();
		$messagesAfter			=	array();

		cbimport( 'cb.installer' );
		$sql					=	'SELECT `id`, `name` FROM `#__comprofiler_plugin` ORDER BY `ordering`';
		$_CB_database->setQuery( $sql );
		$plugins				=	$_CB_database->loadObjectList();
		if ( is_array( $plugins ) ) {
			$cbInstaller		=	new cbInstallerPlugin();
			foreach ( $plugins as $plug ) {
				$result			=	$cbInstaller->checkDatabase( $plug->id, false );
				if ( is_bool( $result ) ) {
					HTML_comprofiler::fixcbdbShowResults( $cbInstaller, false, false, $result, $messagesBefore, $messagesAfter, $dbName . ' "' . $plug->name . '"', $dbId, false );
				} elseif ( is_string( $result ) ) {
					echo '<div style="color:orange;">' . $dbName . ' "' . $plug->name . '"' . ': ' . $result . '</div>';
				} else {
					echo '<div style="color:black;">' . $dbName . ' "' . $plug->name . '"' . ': no database or no database description.</div>';
				}
			}
		}
		$dbName					=	"CB plugins";
		$null					=	null;
		HTML_comprofiler::fixcbdbShowResults( $null, false, false, $result, array(), array(), $dbName, $dbId, true );

	} elseif ( $dbId == 2 ) {

		echo "<div style='text-align:left;'><p>Checking Users Datbase:</p>";

		// 3.	check if comprofiler table is in sync with users table	
		$sql = "SELECT c.id FROM #__comprofiler c LEFT JOIN #__users u ON u.id = c.id WHERE u.id IS NULL";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $bad_rows === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} elseif (count($bad_rows)!=0) {
			echo "<p><font color=red>Warning: ".count($bad_rows)." entries in Community Builder comprofiler table without corresponding user table rows.</font></p>";
			$badids	=	array();
			foreach ($bad_rows as $bad_row) {
				$badids[(int) $bad_row->id]	=	$bad_row->id;
			}
			echo "<p><font color=red>Following comprofiler id: " . implode( ', ', $badids ) . " are missing in user table" . ( isset( $badids[0] ) ? " This comprofiler entry with id 0 should be removed, as it's not allowed." : "" ) . "</font></p>";
			echo "<p><font color=red>This one can be fixed using menu Components-&gt; Community Builder-&gt; tools and then click `Synchronize users`.</font></p>";
		} else {
			echo "<p><font color=green>All Community Builder comprofiler table rows have links to user table.</font></p>";
		}
	
		// 4.	check if users table is in sync with comprofiler table	
		$sql = "SELECT u.id FROM #__users u LEFT JOIN #__comprofiler c ON c.id = u.id WHERE c.id IS NULL";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $bad_rows === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} elseif (count($bad_rows)!=0) {
			echo "<p><font color=red>Warning: ".count($bad_rows)." entries in users table without corresponding comprofiler table rows.</font></p>";
			$badids	=	array();
			foreach ($bad_rows as $bad_row) {
				$badids[(int) $bad_row->id]	=	$bad_row->id;
			}
			echo "<p><font color=red>users id: " . implode( ', ', $badids ) . " are missing in comprofiler table</font></p>";
			echo "<p><font color=red>This one can be fixed using menu Components-&gt; Community Builder-&gt; tools and then click `Synchronize users`.</font></p>";
		} else {
			echo "<p><font color=green>All users table rows have links to comprofiler table.</font></p>";
		}

		// 6.	check if users table has id=0 in it	
		$sql = "SELECT u.id FROM #__users u WHERE u.id = 0";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $bad_rows === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} elseif (count($bad_rows)!=0) {
			echo "<p><font color=red>Warning: ".count($bad_rows)." entries in users table with id=0.</font></p>";
			foreach ($bad_rows as $bad_row) {
				echo "<p><font color=red>users id=" . $bad_row->id . " is not allowed.</font></p>";
			}
			echo "<p><font color=red>This one can be fixed using menu Components-&gt; Community Builder-&gt; tools and then click `Synchronize users`.</font></p>";
			// echo "<p><font color=red>This one can be fixed in SQL using a tool like phpMyAdmin. <strong><u>You also need to check in SQL if id is autoincremented.<u><strong></font></p>";
		} else {
			echo "<p><font color=green>users table has no zero id row.</font></p>";
		}
		// 7.	check if comprofiler table has id=0 in it	
		$sql = "SELECT c.id FROM #__comprofiler c WHERE c.id = 0";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $bad_rows === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} elseif (count($bad_rows)!=0) {
			echo "<p><font color=red>Warning: ".count($bad_rows)." entries in comprofiler table with id=0.</font></p>";
			foreach ($bad_rows as $bad_row) {
				echo "<p><font color=red>comprofiler id=" . $bad_row->id . " is not allowed.</font></p>";
			}
			echo "<p><font color=red>This one can be fixed using menu Components-&gt; Community Builder-&gt; tools and then click `Synchronize users`.</font></p>";
		} else {
			echo "<p><font color=green>comprofiler table has no zero id row.</font></p>";
		}
		// 8.	check if comprofiler table has user_id != id in it	
		$sql = "SELECT c.id, c.user_id FROM #__comprofiler c WHERE c.id <> c.user_id";
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $bad_rows === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} elseif (count($bad_rows)!=0) {
			echo "<p><font color=red>Warning: ".count($bad_rows)." entries in comprofiler table with user_id <> id.</font></p>";
			foreach ($bad_rows as $bad_row) {
				echo "<p><font color=red>comprofiler id=" . $bad_row->id . " is different from user_id=" . $bad_row->user_id . ".</font></p>";
			}
			echo "<p><font color=red>This one can be fixed using menu Components-&gt; Community Builder-&gt; tools and then click `Synchronize users`.</font></p>";
		} else {
			echo "<p><font color=green>All rows in comprofiler table have user_id columns identical to id columns.</font></p>";
		}

		// 10.	check if #__core_acl_aro table is in sync with users table	: A: user -> aro
		if ( ! cbStartOfStringMatch( $version, '3.23' ) ) {
			$sql = "SELECT u.id FROM #__users u LEFT JOIN #__core_acl_aro a ON a.section_value = 'users' AND a.value = CAST( u.id AS CHAR ) WHERE a.value IS NULL";
		} else {
			$sql = "SELECT u.id FROM #__users u LEFT JOIN #__core_acl_aro a ON a.section_value = 'users' AND a.value = u.id WHERE a.value IS NULL";
		}
		// SELECT u.id FROM jos_users u LEFT JOIN jos_core_acl_aro a ON a.section_value = 'users' AND a.value = CAST( u.id AS CHAR ) WHERE a.value IS NULL
		// INSERT INTO jos_core_acl_aro (section_value,value,order_value,name,hidden) SELECT 'users' AS section_value, u.id AS value, 0 AS order_value, u.name as name, 0 AS hidden FROM jos_users u LEFT JOIN jos_core_acl_aro a ON a.section_value = 'users' AND a.value = CAST( u.id AS CHAR ) WHERE a.value IS NULL;
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $bad_rows === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} elseif ( count( $bad_rows ) != 0 ) {
			echo "<p><font color=red>Warning: ".count($bad_rows)." entries in the users table without corresponding core_acl_aro table rows.</font></p>";
			$badids	=	array();
			foreach ($bad_rows as $bad_row) {
				$badids[(int) $bad_row->id]	=	$bad_row->id;
			}
			echo "<p><font color=red>user id: " . implode( ', ', $badids ) . " are missing in core_acl_aro table" . ( isset( $badids[0] ) ? " This user entry with id 0 should be removed, as it's not allowed." : "" ) . "</font></p>";
			echo '<p><font color=red>This one can be fixed by <strong>first backing up database</strong> then <a href="index2.php?option=com_comprofiler&amp;task=fixacldb&amp;' . $cbSpoofField . '=' . $cbSpoofString . '">by clicking here</a>.</font></p>';
		} else {
			echo "<p><font color=green>All users table rows have ACL entries in core_acl_aro table.</font></p>";
		}

		// 11.	check if #__core_acl_aro table is in sync with users table	: B: aro -> user
		if ( checkJversion() == 1 ) {
			$sql = "SELECT a.value AS id, a.id AS aro_id FROM #__core_acl_aro a LEFT JOIN #__users u ON u.id = a.value WHERE a.section_value = 'users' AND u.id IS NULL";
		} else {
			$sql = "SELECT a.value AS id, a.aro_id FROM #__core_acl_aro a LEFT JOIN #__users u ON u.id = a.value WHERE a.section_value = 'users' AND u.id IS NULL";
			// SELECT a.value AS id, a.aro_id FROM jos_core_acl_aro a LEFT JOIN jos_users u ON u.id = a.value WHERE a.section_value = 'users' AND u.id IS NULL
			// DELETE a FROM jos_core_acl_aro AS a LEFT JOIN jos_users AS u ON u.id = a.value WHERE a.section_value = 'users' AND u.id IS NULL
		}
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $bad_rows === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} elseif (count($bad_rows)!=0) {
			echo "<p><font color=red>Warning: ".count($bad_rows)." entries in the core_acl_aro table without corresponding users table rows.</font></p>";
			$badids	=	array();
			foreach ($bad_rows as $bad_row) {
				$badids[(int) $bad_row->id]		=	"user id=" . $bad_row->id . " (aro_id=" . $bad_row->aro_id . ")";
			}
			echo "<p><font color=red>Following entries of core_acl_aro table are missing in users table: " . implode( ', ', $badids ) . '.' . ( isset( $badids[0] ) ? "<br /> This core_acl_aro entry with (user) value 0 should be removed, as it's not allowed." : "" ) . ( ( $bad_row->aro_id == 0 ) ? " This core_acl_aro entry with aro_id 0 should be removed, as it's not allowed." : "" ) . "</font></p>";
			echo '<p><font color=red>This one can be fixed by <strong>first backing up database</strong> then <a href="index2.php?option=com_comprofiler&amp;task=fixacldb&amp;' . $cbSpoofField . '=' . $cbSpoofString . '">by clicking here</a>.</font></p>';
		} else {
			echo "<p><font color=green>All ACL core_acl_aro table rows have corresponding entries in users table.</font></p>";
		}

		// 12.	check if #__core_acl_groups_aro_map table is in sync with #__core_acl_aro table	A: aro -> groups
		if ( checkJversion() == 1 ) {
			$sql = "SELECT a.value AS id, a.id AS aro_id FROM #__core_acl_aro a LEFT JOIN #__core_acl_groups_aro_map g ON g.aro_id = a.id WHERE g.aro_id IS NULL";
		} else {
			$sql = "SELECT a.value AS id, a.aro_id FROM #__core_acl_aro a LEFT JOIN #__core_acl_groups_aro_map g ON g.aro_id = a.aro_id WHERE g.aro_id IS NULL";
			// SELECT a.value AS id, a.aro_id FROM jos_core_acl_aro a LEFT JOIN jos_core_acl_groups_aro_map g ON g.aro_id = a.aro_id WHERE g.aro_id IS NULL
			// INSERT INTO jos_core_acl_groups_aro_map (aro_id,section_value,group_id) SELECT a.aro_id, '', 18 AS group_id FROM jos_core_acl_aro a LEFT JOIN jos_core_acl_groups_aro_map g ON g.aro_id = a.aro_id WHERE g.aro_id IS NULL
		}
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $bad_rows === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} elseif (count($bad_rows)!=0) {
			echo "<p><font color=red>Warning: ".count($bad_rows)." entries in the core_acl_aro table without corresponding core_acl_groups_aro_map table rows.</font></p>";
			$badids	=	array();
			foreach ($bad_rows as $bad_row) {
				$badids[(int) $bad_row->id]		=	"user id=" . $bad_row->id . " (aro_id=" . $bad_row->aro_id . ")";
			}
			echo "<p><font color=red>Following entries of core_acl_aro table are missing in core_acl_groups_aro_map table: " . implode( ', ', $badids ) . '.' . ( isset( $badids[0] ) ? "<br /> This core_acl_aro entry with (user) value 0 should be removed, as it's not allowed." : "" ) . ( ( $bad_row->aro_id == 0 ) ? " This core_acl_aro entry with aro_id 0 should be removed, as it's not allowed." : "" ) . "</font></p>";
			echo '<p><font color=red>This one can be fixed by <strong>first backing up database</strong> then <a href="index2.php?option=com_comprofiler&amp;task=fixacldb&amp;' . $cbSpoofField . '=' . $cbSpoofString . '">by clicking here</a>.</font></p>';
		} else {
			echo "<p><font color=green>All core_acl_aro table rows have ACL entries in core_acl_groups_aro_map table.</font></p>";
		}

		// 13.	check if #__core_acl_groups_aro_map table is in sync with #__core_acl_aro table	B: groups -> aro
		if ( checkJversion() == 1 ) {
			$sql = "SELECT g.aro_id AS id FROM #__core_acl_groups_aro_map g LEFT JOIN #__core_acl_aro a ON a.id = g.aro_id WHERE a.id IS NULL";
		} else {
			$sql = "SELECT g.aro_id AS id FROM #__core_acl_groups_aro_map g LEFT JOIN #__core_acl_aro a ON a.aro_id = g.aro_id WHERE a.aro_id IS NULL";
			// SELECT g.aro_id AS id FROM jos_core_acl_groups_aro_map g LEFT JOIN jos_core_acl_aro a ON a.aro_id = g.aro_id WHERE a.aro_id IS NULL
			// DELETE g FROM jos_core_acl_groups_aro_map g LEFT JOIN jos_core_acl_aro a ON a.aro_id = g.aro_id WHERE a.aro_id IS NULL
		}
		$_CB_database->setQuery($sql);
		$bad_rows = $_CB_database->loadObjectList();
		if ( $bad_rows === null ) {
			echo "<p><font color=red>ERROR: sql query: " . htmlspecialchars( $sql ) . " : returned error: " . stripslashes( $_CB_database->getErrorMsg() ) .  "</font></p>";
		} elseif (count($bad_rows)!=0) {
			echo "<p><font color=red>Warning: ".count($bad_rows)." entries in the core_acl_groups_aro_map without corresponding core_acl_aro table table rows.</font></p>";
			$badids	=	array();
			foreach ($bad_rows as $bad_row) {
				$badids[(int) $bad_row->id]		=	$bad_row->id;
			}
			echo "<p><font color=red>aro_id = " . implode( ', ', $badids ) . " are missing in core_acl_aro table table." . ( isset( $badids[0] ) ? " This entry with aro_id 0 should be removed, as it's not allowed." : "" ) . "</font></p>";
			echo '<p><font color=red>This one can be fixed by <strong>first backing up database</strong> then <a href="index2.php?option=com_comprofiler&amp;task=fixacldb&amp;' . $cbSpoofField . '=' . $cbSpoofString . '">by clicking here</a>.</font></p>';
		} else {
			echo "<p><font color=green>All core_acl_aro table rows have ACL entries in core_acl_groups_aro_map table.</font></p>";
		}

		$dbName					=	"Users";
		echo '</div>';

	} elseif ( $dbId == 3 ) {
		// adapt published fields to global CB config (regarding name type)
		_cbAdaptNameFieldsPublished( $ueConfig );

		// Check fields db:
		cbimport( 'cb.dbchecker' );
		$dbChecker				=	new CBdbChecker( $_CB_database );
		$result					=	$dbChecker->checkAllCBfieldsDb( false );
		$dbName					=	"CB fields data storage";
		$messagesBefore			=	array();

		$_PLUGINS->loadPluginGroup('user');	
		$messagesAfter			=	$_PLUGINS->trigger( 'onAfterCheckCbFieldsDb', true );

		HTML_comprofiler::fixcbdbShowResults( $dbChecker, false, false, $result, $messagesBefore, $messagesAfter, $dbName, $dbId );
		echo '</div>';
	}

	global $_CB_Backend_Title;
	$_CB_Backend_Title			=	array( 0 => array( 'cbicon-48-tools', "CB Tools: Check " . $dbName . " database: Results" ) );
}

function fixacldb() {
	global $_CB_database;
	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );
	$version								=	$_CB_database->getVersion();
	$version								=	substr( $version, 0, strpos( $version, '-' ) );

	// 1. put #__core_acl_aro table in sync with users table	: A: user -> aro
	if ( ! cbStartOfStringMatch( $version, '3.23' ) ) {
		$sql = "INSERT INTO #__core_acl_aro (section_value,value,order_value,name,hidden) SELECT 'users' AS section_value, u.id AS value, 0 AS order_value, u.name as name, 0 AS hidden FROM #__users u LEFT JOIN #__core_acl_aro a ON a.section_value = 'users' AND a.value = CAST( u.id AS CHAR ) WHERE a.value IS NULL";
	} else {
		$sql = "INSERT INTO #__core_acl_aro (section_value,value,order_value,name,hidden) SELECT 'users' AS section_value, u.id AS value, 0 AS order_value, u.name as name, 0 AS hidden FROM #__users u LEFT JOIN #__core_acl_aro a ON a.section_value = 'users' AND a.value = u.id WHERE a.value IS NULL";
	}
	
	$_CB_database->setQuery($sql);
	if (!$_CB_database->query()) {
		print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
		return;
	}
	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print "<p><font color='orange'>Added ".$affected." new entries to core_acl_aro table from users Table.</font></p>";
	}

	// 2. delete #__core_acl_aro table entries which are not in users table	: B: aro -> user
	if ( ! cbStartOfStringMatch( $version, '3.23' ) ) {
		if ( checkJversion() == 1 ) {
			$sql = "DELETE a FROM #__core_acl_aro a LEFT JOIN #__users u ON u.id = a.value WHERE a.section_value = 'users' AND u.id IS NULL";
		} else {
			$sql = "DELETE a FROM #__core_acl_aro a LEFT JOIN #__users u ON u.id = a.value WHERE a.section_value = 'users' AND u.id IS NULL";
		}
		$_CB_database->setQuery($sql);
		if (!$_CB_database->query()) {
			print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
			return;
		}
		$affected		=	$_CB_database->getAffectedRows();
		if ($affected) {
			print "<p><font color='orange'>Deleted ".$affected." core_acl_aro entries which didn't correspond to users table.</font></p>";
		}
	}

	// 3. add missing #__core_acl_groups_aro_map table entries to put in sync with #__core_acl_aro table	A: aro -> groups
	if ( checkJversion() == 1 ) {
		// $sql = "SELECT a.value AS id, a.id AS aro_id FROM #__core_acl_aro a LEFT JOIN #__core_acl_groups_aro_map g ON g.aro_id = a.id WHERE g.aro_id IS NULL";
		$sql = "INSERT INTO #__core_acl_groups_aro_map (aro_id,section_value,group_id) SELECT a.id AS aro_id, '', 18 AS group_id FROM #__core_acl_aro a LEFT JOIN #__core_acl_groups_aro_map g ON g.aro_id = a.id WHERE g.aro_id IS NULL";
	} else {
		// $sql = "SELECT a.value AS id, a.aro_id FROM #__core_acl_aro a LEFT JOIN #__core_acl_groups_aro_map g ON g.aro_id = a.aro_id WHERE g.aro_id IS NULL";
		$sql = "INSERT INTO #__core_acl_groups_aro_map (aro_id,section_value,group_id) SELECT a.aro_id, '', 18 AS group_id FROM #__core_acl_aro a LEFT JOIN #__core_acl_groups_aro_map g ON g.aro_id = a.aro_id WHERE g.aro_id IS NULL";
	}
	$_CB_database->setQuery($sql);
	if (!$_CB_database->query()) {
		print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
		return;
	}
	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print "<p><font color='orange'>Added ".$affected." new entries to core_acl_groups_aro_map table from core_acl_aro Table.</font></p>";
	}

	// 4. delete #__core_acl_groups_aro_map table entries which are not in sync with #__core_acl_aro table	B: groups -> aro
	if ( ! cbStartOfStringMatch( $version, '3.23' ) ) {
		if ( checkJversion() == 1 ) {
			$sql = "DELETE g FROM #__core_acl_groups_aro_map g LEFT JOIN #__core_acl_aro a ON a.id = g.aro_id WHERE a.id IS NULL";
		} else {
			$sql = "DELETE g FROM #__core_acl_groups_aro_map g LEFT JOIN #__core_acl_aro a ON a.aro_id = g.aro_id WHERE a.aro_id IS NULL";
		}
		$_CB_database->setQuery($sql);
		if (!$_CB_database->query()) {
			print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
			return;
		}
		$affected		=	$_CB_database->getAffectedRows();
		if ($affected) {
			print "<p><font color='orange'>Deleted ".$affected." core_acl_groups_aro_map entries which didn't correspond to core_acl_aro table.</font></p>";
		}
	}

	print "<font color=green>Joomla/Mambo User Table and Joomla/Mambo ACL Table should now be in sync!</font>";

}

function fixcbdb( $dryRun, $dbId = 0 ) {
	global $_CB_database, $ueConfig, $_PLUGINS;

	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	$dryRun						=	( $dryRun == 1 );

	if ( $dbId == 0 ) {
		$dbName					=	"Core CB";

		// Fix mandatory basics of core CB:
		cbimport( 'cb.dbchecker' );
		$dbChecker				=	new CBdbChecker( $_CB_database );
		$result					=	$dbChecker->checkCBMandatoryDb( true, $dryRun );
		$dbName					=	"Core CB mandatory basics";
		$messagesAfter			=	array();
		$messagesBefore			=	array();

		ob_start();
		HTML_comprofiler::fixcbdbShowResults( $dbChecker, true, $dryRun, $result, $messagesBefore, $messagesAfter, $dbName, $dbId );
		$html					=	ob_get_contents();
		ob_end_clean();


		// Fix core CB:

	   	$_PLUGINS->loadPluginGroup('user');	
		$messagesBefore			=	$_PLUGINS->trigger( 'onBeforeFixDb', array( $dryRun ) );
		$messagesBefore[]		=	$html;
		$dbName					=	"Core CB";
		$dbChecker				=	new CBdbChecker( $_CB_database );
		$result					=	$dbChecker->checkDatabase( true, $dryRun );

		$messagesAfter			=	$_PLUGINS->trigger( 'onAfterFixDb', array( $dryRun ) );

		// adapt published fields to global CB config (regarding name type)
		_cbAdaptNameFieldsPublished( $ueConfig );

	} elseif ( $dbId == 1 ) {
		// Fix plugin $dbId:
		$dbName					=	"CB plugin";
		$messagesBefore			=	array();
		$messagesAfter			=	array();

		cbimport( 'cb.installer' );
		$sql					=	'SELECT `id`, `name` FROM `#__comprofiler_plugin` ORDER BY `ordering`';
		$_CB_database->setQuery( $sql );
		$plugins				=	$_CB_database->loadObjectList();
		if ( is_array( $plugins ) ) {
			$cbInstaller		=	new cbInstallerPlugin();
			foreach ( $plugins as $plug ) {
				$result			=	$cbInstaller->checkDatabase( $plug->id, true, $dryRun );
				if ( is_bool( $result ) ) {
					HTML_comprofiler::fixcbdbShowResults( $cbInstaller, true, $dryRun, $result, $messagesBefore, $messagesAfter, $dbName . ' "' . $plug->name . '"', $dbId, false );
				} elseif ( is_string( $result ) ) {
					echo '<div style="color:orange;">' . $dbName . ' "' . $plug->name . '"' . ': ' . $result . '</div>';
				} else {
					echo '<div style="color:black;">' . $dbName . ' "' . $plug->name . '"' . ': no database or no database description.</div>';
				}
			}
		}
		$dbName					=	"CB plugins";

	} elseif ( $dbId == 3 ) {
		// adapt published fields to global CB config (regarding name type)
		_cbAdaptNameFieldsPublished( $ueConfig );

	   	$_PLUGINS->loadPluginGroup('user');	
		$messagesBefore			=	$_PLUGINS->trigger( 'onBeforeFixFieldsDb', array( $dryRun ) );

		// Check fields db:
		cbimport( 'cb.dbchecker' );
		$dbChecker				=	new CBdbChecker( $_CB_database );
		$result					=	$dbChecker->checkAllCBfieldsDb( true, $dryRun );
		$dbName					=	"CB fields data storage";
		$messagesAfter			=	array();
	}
	HTML_comprofiler::secureAboveForm('fixcbdb');

	outputCbTemplate( 2 );
	outputCbJs( 2 );

	global $_CB_Backend_Title;
	$_CB_Backend_Title			=	array( 0 => array( 'cbicon-48-tools', "CB Tools: Fix " . $dbName . " database: " . ( $dryRun ? 'Dry-run:' : 'Fixed:' ) . " Results" ) );

	HTML_comprofiler::fixcbdbShowResults( $dbChecker, true, $dryRun, $result, $messagesBefore, $messagesAfter, $dbName, $dbId );
}

function fixcbmiscdb() {
	global $_CB_database;
	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	// 1. delete comprofiler_field_values table for bad rows
	$sql = "DELETE FROM #__comprofiler_field_values WHERE fieldid=0";
	$_CB_database->setQuery($sql);
	if (!$_CB_database->query()) {
		print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
		return;
	}
	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print "<p><font color='orange'>Deleted ".$affected." comprofiler_field_values entries which didn't match any field.</font></p>";
	}

	// 2. delete comprofiler_field_values table has entries where corresponding fieldtype value in comprofiler_fields table 
	//		does not allow values
/* not done ! as some new fields might not be listed in here ! :
	$sql = "DELETE v FROM #__comprofiler_field_values as v, #__comprofiler_fields as f WHERE v.fieldid = f.fieldid AND f.type NOT IN ('checkbox','multicheckbox','select','multiselect','radio')";
	$_CB_database->setQuery($sql);
	if (!$_CB_database->query()) {
		print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
		return;
	}
	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print "<p><font color='orange'>Deleted ".$affected." comprofiler_field_values entries which didn't match any field.</font></p>";
	}
*/
	// 3. add missing #__core_acl_groups_aro_map table entries to put in sync with #__core_acl_aro table	A: aro -> groups
	if ( checkJversion() == 1 ) {
		// $sql = "SELECT a.value AS id, a.id AS aro_id FROM #__core_acl_aro a LEFT JOIN #__core_acl_groups_aro_map g ON g.aro_id = a.id WHERE g.aro_id IS NULL";
		$sql = "INSERT INTO #__core_acl_groups_aro_map (aro_id,section_value,group_id) SELECT a.id AS aro_id, '', 18 AS group_id FROM #__core_acl_aro a LEFT JOIN #__core_acl_groups_aro_map g ON g.aro_id = a.id WHERE g.aro_id IS NULL";
	} else {
		// $sql = "SELECT a.value AS id, a.aro_id FROM #__core_acl_aro a LEFT JOIN #__core_acl_groups_aro_map g ON g.aro_id = a.aro_id WHERE g.aro_id IS NULL";
		$sql = "INSERT INTO #__core_acl_groups_aro_map (aro_id,section_value,group_id) SELECT a.aro_id, '', 18 AS group_id FROM #__core_acl_aro a LEFT JOIN #__core_acl_groups_aro_map g ON g.aro_id = a.aro_id WHERE g.aro_id IS NULL";
	}
	$_CB_database->setQuery($sql);
	if (!$_CB_database->query()) {
		print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
		return;
	}
	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print "<p><font color='orange'>Added ".$affected." new entries to core_acl_groups_aro_map table from core_acl_aro Table.</font></p>";
	}

	// 4. delete #__core_acl_groups_aro_map table entries which are not in sync with #__core_acl_aro table	B: groups -> aro
	if ( checkJversion() == 1 ) {
		$sql = "DELETE g FROM #__core_acl_groups_aro_map g LEFT JOIN #__core_acl_aro a ON a.id = g.aro_id WHERE a.id IS NULL";
	} else {
		$sql = "DELETE g FROM #__core_acl_groups_aro_map g LEFT JOIN #__core_acl_aro a ON a.aro_id = g.aro_id WHERE a.aro_id IS NULL";
	}
	$_CB_database->setQuery($sql);
	if (!$_CB_database->query()) {
		print("<font color=red>SQL error" . $_CB_database->stderr(true)."</font><br />");
		return;
	}
	$affected		=	$_CB_database->getAffectedRows();
	if ($affected) {
		print "<p><font color='orange'>Deleted ".$affected." core_acl_groups_aro_map entries which didn't correspond to core_acl_aro table.</font></p>";
	}

	print "<font color=green>Joomla/Mambo User Table and Joomla/Mambo ACL Table should now be in sync!</font>";

}


function loadTools() {
	HTML_comprofiler::showTools();
}

/**
* Compacts the ordering sequence of the selected records
* @param array of table key ids which need to get saved ($row[]->ordering contains old ordering and $_POST['order'] contains new ordering)
* @param object derived from comprofilerDBTable of corresponding class
* @param string Additional "WHERE" query to limit ordering to a particular subset of records
*/
function saveOrder( $cid, &$row, $conditionStatement ) {
	global $_CB_database,$_POST;

	$total		= count( $cid );
	$order 		= cbGetParam( $_POST, 'order', array(0) );
	$conditions = array();
	$cidsChanged	= array();

    // update ordering values
	for( $i=0; $i < $total; $i++ ) {
		$row->load( (int) $cid[$i] );
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
	        if (!$row->store( (int) $cid[$i])) {
	            echo "<script type=\"text/javascript\"> alert('saveOrder:".$_CB_database->getErrorMsg()."'); window.history.go(-1); </script>\n";
	            exit();
	        } // if
	        $cidsChanged[] = $cid[$i];
	        // remember to updateOrder this group if multiple groups (conditionStatement gives the group)
	        if ($conditionStatement) {
	        	$condition=null;				// to make php checker happy: the next line defines $condition
	        	eval($conditionStatement);
	        	$found = false;
	        	foreach ( $conditions as $cond )
		        	if ($cond[1]==$condition) {
		        		$found = true;
		        		break;
		        	} // if
	        	if (!$found) $conditions[] = array($cid[$i], $condition);
	        }
		} // if
	} // for

	if ($conditionStatement) {
		// execute updateOrder for each group
		foreach ( $conditions as $cond ) {
			$row->load( (int) $cond[0] );
			$row->updateOrder( $cond[1], $cidsChanged );
		} // foreach
	} else if ($cidsChanged) {
		$row->load( (int) $cidsChanged[0] );
		$row->updateOrder( null, $cidsChanged );
	}
	return 'New ordering saved';
} // saveOrder

function saveFieldOrder( &$cid ) {
	global $_CB_database;
	$row = new moscomprofilerFields( $_CB_database );
	$msg = saveOrder( $cid, $row, "\$condition = \"tabid=\$row->tabid\";" );
	cbRedirect( 'index2.php?option=com_comprofiler&task=showField', $msg );
} // saveFieldOrder

function saveTabOrder( &$cid ) {
	global $_CB_database;
	$row 		= new moscomprofilerTabs( $_CB_database );
	$msg = saveOrder( $cid, $row, "\$condition = \"position='\$row->position' AND ordering > -10000 AND ordering < 10000 \";" );
	cbRedirect( 'index2.php?option=com_comprofiler&task=showTab', $msg );
} // saveTabOrder saveOrder

function saveListOrder( &$cid ) {
	global $_CB_database;
	$row 		= new moscomprofilerLists( $_CB_database );
	$msg = saveOrder( $cid, $row, null );
	cbRedirect( 'index2.php?option=com_comprofiler&task=showLists', $msg );
} // saveListOrder saveOrder




//plugin
function viewPlugins( $option ) {
	global $_CB_database, $_CB_framework;

	$limit			=	(int) $_CB_framework->getCfg( 'list_limit' );
	if ( $limit == 0 ) {
		$limit = 10;
	}
	$limit			=	$_CB_framework->getUserStateFromRequest( "viewlistlimit", 'limit', $limit );
	$lastCBlist = $_CB_framework->getUserState( "view{$option}lastCBlist", null );
	if ($lastCBlist == 'showplugins') {
		$limitstart 	= $_CB_framework->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
		$lastSearch = $_CB_framework->getUserState( "search{$option}", null );
		$search		= $_CB_framework->getUserStateFromRequest( "search{$option}", 'search', '' );
		if ($lastSearch != $search) {
			$limitstart = 0;
			$_CB_framework->setUserState( "view{$option}limitstart", $limitstart );
		}
		$search = trim( strtolower( $search ) );
		$filter_type	= $_CB_framework->getUserStateFromRequest( "filter_type{$option}", 'filter_type', "0" );
	} else {
		clearSearchBox();
		$search="";
		$limitstart = 0;
		$_CB_framework->setUserState( "view{$option}limitstart", $limitstart );
		$_CB_framework->setUserState( "view{$option}lastCBlist", "showplugins" );
		$filter_type = "0";
		$_CB_framework->setUserState( "filter_type{$option}", $filter_type );
	}
	$where=array();
	
	// used by filter
	if ( $filter_type ) {
		$where[] = "m.type = '$filter_type'";
	}
	if ( $search ) {
		$search = cbEscapeSQLsearch( trim( strtolower( cbGetEscaped($search))));
		$where[] = "LOWER( m.name ) LIKE '%$search%'";
	}

	// get the total number of records
	$query = "SELECT COUNT(*) FROM #__comprofiler_plugin AS m ". ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' );
	$_CB_database->setQuery( $query );
	$total = $_CB_database->loadResult();
	if ($total <= $limitstart) $limitstart = 0;

	cbimport( 'cb.pagination' );
	$pageNav = new cbPageNav( $total, $limitstart, $limit  );

	$query = "SELECT m.*, u.name AS editor, g.name AS groupname"
	. "\n FROM #__comprofiler_plugin AS m"
	. "\n LEFT JOIN #__users AS u ON u.id = m.checked_out"
	. "\n LEFT JOIN #__groups AS g ON g.id = m.access"
	. ( count( $where ) ? "\n WHERE " . implode( ' AND ', $where ) : '' )
	. "\n GROUP BY m.id"
	. "\n ORDER BY m.type ASC, m.ordering ASC, m.name ASC"
	. "\n LIMIT " . (int) $pageNav->limitstart . ", " . (int) $pageNav->limit
	;
	$_CB_database->setQuery( $query );
	$rows = $_CB_database->loadObjectList();
	if ($_CB_database->getErrorNum()) {
		echo $_CB_database->stderr();
		return false;
	}

	// get list of Positions for dropdown filter
	$query = "SELECT type AS value, type AS text"
	. "\n FROM #__comprofiler_plugin"
	. "\n GROUP BY type"
	. "\n ORDER BY type"
	;
	$types[] = moscomprofilerHTML::makeOption( '0', (!defined('_SEL_TYPE')) ? '- Select Type -' : _SEL_TYPE );		// Mambo 4.5.1 Compatibility
	$_CB_database->setQuery( $query );
	$types = array_merge( $types, $_CB_database->loadObjectList() );
	$lists['type']	= moscomprofilerHTML::selectList( $types, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_type, 2 );

	HTML_comprofiler::showPlugins( $rows, $pageNav, $option, $lists, $search );
	return true;
}

/**
* Saves the CB plugin after an edit form submit
*/
function savePlugin( $option, $task ) {
	global $_CB_database, $_PLUGINS;
	if ( $task == 'showPlugins' ) {
		cbRedirect( 'index2.php?option=' . $option . '&task=showPlugins');
		return;
	}
	
	$action = cbGetParam( $_REQUEST, 'action' );
	
	if ( ! $action ) {
		savePluginParams( $option, $task );
	} else {
		$uid	= cbGetParam( $_REQUEST, 'cid' );
		$row 	= new moscomprofilerPlugin($_CB_database);
		if ( $uid ) {
			$row->load( (int) $uid );
		}

		// get params values
		if ($row->type !== "language") {
			$_PLUGINS->loadPluginGroup($row->type,array( (int) $row->id), 0);
		}
		// xml file for plugin
		$element	=&	$_PLUGINS->loadPluginXML( 'action', $action, $row->id );

		$_REQUEST['task'] = 'editPlugin';		// so that the actionPath matches
		$params =& new cbParamsBase( $row->params );
		editPluginView( $row, $option, 'editPlugin', $uid, $action, $element, $task, $params );
	}
}
	
/**
* Saves the CB plugin params after an edit form submit
*/
function savePluginParams( $option, $task ) {
	global $_CB_database, $_POST;

	if ( isset( $_POST['params'] ) ) {
	 	$_POST['params']	=	cbParamsEditorController::getRawParams( $_POST['params'] );
	} else {
		$_POST['params']	=	null;
	}

	$row = new moscomprofilerPlugin( $_CB_database );
	if (!$row->bind( $_POST )) {
		echo "<script type=\"text/javascript\"> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->check()) {
		echo "<script type=\"text/javascript\"> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script type=\"text/javascript\"> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();

	$row->updateOrder( "type='".$_CB_database->getEscaped($row->type)."' AND ordering > -10000 AND ordering < 10000 " );

	switch ( $task ) {
		case 'applyPlugin':
			$msg = 'Successfully Saved changes to Plugin: '. $row->name;
			cbRedirect( 'index2.php?option='. $option .'&task=editPlugin&cid='. $row->id, $msg );

		case 'savePlugin':
		default:
			$msg = 'Successfully Saved Plugin: '. $row->name;
			cbRedirect( 'index2.php?option='. $option .'&task=showPlugins' , $msg );
			break;
	}
}

/**
* Compiles information to add or edit a plugin
* @param string The current GET/POST option
* @param integer The unique id of the record to edit
*/
function editPlugin( $option, $task, $uid) {
	global $_CB_database, $_CB_framework, $_PLUGINS, $_POST;
	
	$action	= cbGetParam( $_REQUEST, 'action', null );

	if ( ! $uid ) {
		$uid = cbGetParam( $_POST, 'id' );
	}

	$row 	= new moscomprofilerPlugin($_CB_database);
	if ( $uid ) {
		// load the row from the db table
		$row->load( (int) $uid );
	}
	// fail if checked out not by 'me'
	if ($row->checked_out && $row->checked_out <> $_CB_framework->myId() ) {
		echo "<script type=\"text/javascript\">alert('The plugin $row->name is currently being edited by another administrator'); document.location.href='index2.php?option=$option'</script>\n";
		exit(0);
	}

	// get params values
	if ( $row->type !== "language" && $row->id ) {
		$_PLUGINS->loadPluginGroup($row->type,array( (int) $row->id), 0);
	}

	// xml file for plugin
	$element = null;
	if ($uid) {
		$element	=&	$_PLUGINS->loadPluginXML( 'action', $action, $row->id );
	}

	if ( $element && ( $action === null ) ) {
		$adminActionsModel	=&	$element->getChildByNameAttr( 'actions', 'ui', 'admin' );
		if ( $adminActionsModel ) {
			$defaultAction	=&	$adminActionsModel->getChildByNameAttr( 'action', 'name', 'default' );
			$actionRequest	=	$defaultAction->attributes( 'request' );
			$actionAction	=	$defaultAction->attributes( 'action' );
			if ( ( $actionRequest === '' ) && ( $actionRequest === '' ) ) {
				$action = $actionAction;
			}
		}
	}
	if ( $element ) {
		$description		=&	$element->getChildByNameAttributes( 'description' );
	} else {
		$description		=	null;
	}
	if ( $description ) {
		$row->description	=	$description->data();
	} else {
		$row->description	=	'-';
	}
	if ( $action === null ) {
		
		$params =& new cbParamsEditorController( $row->params, $element, $element, $row );
		$options = array( 'option' => $option, 'task' => $task, 'pluginid' => $uid, 'tabid' => null );
		$params->setOptions( $options );
		editPluginSettingsParams( $row, $option, $task, $uid, $element, $params, $options );
		
	} else {
		$params =& new cbParamsBase( $row->params );
		editPluginView( $row, $option, $task, $uid, $action, $element, 'editPlugin', $params );
		
	}
}

function editPluginSettingsParams( &$row, $option, $task, $uid, &$element, &$params, &$options ) {
	global $_CB_database, $_CB_framework;

	$lists 	= array();

	// get list of groups
	if ($row->access == 99 || $row->client_id == 1) {
		$lists['access'] = 'Administrator<input type="hidden" name="access" value="99" />';
	} else {
		// build the html select list for the group access
		$_CB_database->setQuery( 'SELECT id AS value, name AS text FROM #__groups ORDER BY id' );
		$lists['access'] = moscomprofilerHTML::selectList( $_CB_database->loadObjectList(), 'access', 'class="inputbox" size="3"', 'value', 'text', intval( $row->access ), 2 );
	}

	if ($uid) {
		$row->checkout( $_CB_framework->myId() );

		if ( $row->ordering > -10000 && $row->ordering < 10000 ) {
			// build the html select list for ordering
			$query = "SELECT ordering AS value, name AS text"
			. "\n FROM #__comprofiler_plugin"
			. "\n WHERE type='" . $_CB_database->getEscaped( $row->type ) . "'"
			. "\n AND published > 0"
			. "\n AND ordering > -10000"
			. "\n AND ordering < 10000"
			. "\n ORDER BY ordering"
			;
			$order = _cbGetOrderingList( $query );
			$lists['ordering'] = moscomprofilerHTML::selectList( $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ), 2 );
		} else {
			$lists['ordering'] = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />This plugin cannot be reordered';
		}
		$lists['type'] = '<input type="hidden" name="type" value="'. $row->type .'" />'. $row->type;

		if ($element && $element->name() == 'cbinstall' && $element->attributes( 'type' ) == 'plugin' ) {
			$description =& $element->getElementByPath( 'description' );
			$row->description = ( $description ) ? trim( $description->data() ) : '';
		}

	} else {
		$row->folder 		= '';
		$row->ordering 		= 999;
		$row->published 	= 1;
		$row->description 	= '';

		$folders			= cbReadDirectory( $_CB_framework->getCfg('absolute_path') . '/components/com_comprofiler/plugin/' );
		$folders2			= array();
		foreach ($folders as $folder) {
		    if (is_dir( $_CB_framework->getCfg('absolute_path') . '/components/com_comprofiler/plugin/' . $folder ) && ( $folder <> 'CVS' ) ) {
		        $folders2[] = moscomprofilerHTML::makeOption( $folder );
			}
		}
		$lists['type']		= moscomprofilerHTML::selectList( $folders2, 'type', 'class="inputbox" size="1"', 'value', 'text', null, 2 );
		$lists['ordering']	= '<input type="hidden" name="ordering" value="'. $row->ordering .'" />New items default to the last place. Ordering can be changed after this item is saved.';
	}

	$Yesoptions = array();
	$Yesoptions[] = moscomprofilerHTML::makeOption( '1', _UE_YES );
	if ( ( $row->type == 'language' ) || ( $row->id == 1 ) ) {
		$row->published		=	1;
	} else {
		$Yesoptions[]		=	moscomprofilerHTML::makeOption( '0', _UE_NO );
	}
	$lists['published'] = moscomprofilerHTML::radioList( $Yesoptions, 'published', 'class="inputbox"', 'value', 'text', $row->published, 2 );

	HTML_comprofiler::editPlugin( $row, $lists, $params, $options );
}

function editPluginView( &$row, $option, $task, $uid, $action, &$element, $mode, &$pluginParams ) {
	global $_CB_database;

		if ( ! $row->id ) {
		echo 'Plugin id not found.';
		return null;;
	}
	if ( ! $element ) {
		echo 'No plugin XML found.';
		return null;
	}

	$adminHandlerModel	=& $element->getChildByNameAttr( 'handler', 'ui', 'admin' );
	if ( ! $adminHandlerModel ) {
		echo 'No admin handler defined in XML';
		return null;
	}
	$class	=	$adminHandlerModel->attributes( 'class' );
	if ( ! class_exists( $class ) ) {
		echo 'Admin handler class ' . $class . ' does not exist.';
		return null;
	}
	
	$handler	=&	new $class( $_CB_database );
	return $handler->editPluginView( $row, $option, $task, $uid, $action, $element, $mode, $pluginParams );
}

/**
* Compiles information to add or edit a plugin
* @param string The current GET/POST option
* @param integer The unique id of the record to edit
*/
function pluginMenu( $option, $uid) {
	global $_CB_database, $_CB_framework, $_PLUGINS, $_GET;

	if ( ! $uid ) {
		echo "<script type=\"text/javascript\">alert('No plugin selected'); document.location.href='index2.php?option=$option'</script>\n";
		exit(0);
	}

	$row 	= new moscomprofilerPlugin($_CB_database);

	// load the row from the db table
	$row->load( (int) $uid );

	// fail if checked out not by 'me'
	if ($row->checked_out && $row->checked_out <> $_CB_framework->myId() ) {
		echo "<script type=\"text/javascript\">alert('The plugin $row->name is currently being edited by another administrator'); document.location.href='index2.php?option=$option'</script>\n";
		exit(0);
	}
	$basepath	=	$_CB_framework->getCfg('absolute_path') . '/' . $_PLUGINS->getPluginRelPath( $row ) . '/';
	$phpfile = $basepath . "admin." . $row->element . '.php';

	// see if there is an xml install file, must be same name as element
	if (file_exists( $phpfile )) {
	// get params values
		if ( $row->type !== "language" ) {
			$_PLUGINS->loadPluginGroup($row->type,array( (int) $row->id), 0);
		}
		$menu = cbGetParam( $_REQUEST, 'menu' );
		$element	=&	$_PLUGINS->loadPluginXML( 'menu', $menu, $row->id );		// xml file for plugin

		$params =& new cbParamsEditorController( $row->params, $element, $element, $row );

		if ( cbGetParam( $_GET, 'no_html', 0 ) != 1 ) {
			outputCbTemplate( 2 );
			outputCbJs( 2 );
		    initToolTip( 2 );
		}

		require_once( $phpfile );
		$classname = $row->element . "Admin";
		$adminClass = new $classname();
		echo $adminClass->menu( $row, $menu, $params );		
	} else {
		echo "<script type=\"text/javascript\">alert('The plugin $row->name has no administrator file $phpfile'); document.location.href='index2.php?option=$option'</script>\n";
		exit(0);
	}
}

/**
* Deletes one or more plugins
*
* Also deletes associated entries in the #__comprofiler_plugin table.
* @param array An array of unique category id numbers
*/
function removePlugin( &$cid, $option ) {
	if (count( $cid ) < 1) {
		echo "<script type=\"text/javascript\"> alert('Select a plugin to delete'); window.history.go(-1);</script>\n";
		exit;
	}
	$installer = new cbInstallerPlugin();
	foreach($cid AS $id) {
		$ret	=	$installer->uninstall($id,$option);
		if ( ! $ret ) {
			break;
		}
	}
	if ( $ret ) {
		HTML_comprofiler::showInstallMessage( $installer->getError(), 'Uninstall Plugin - '.($ret ? 'Success' : 'Failed'),
		$installer->returnTo( $option, 'showPlugins' ) );
	}
}

/**
* Publishes or Unpublishes one or more plugins
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
*/
function publishPlugin( $cid=null, $publish=1, $option ) {
	global $_CB_database, $_CB_framework;

	if (count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		echo "<script type=\"text/javascript\"> alert('Select a plugin to $action'); window.history.go(-1);</script>\n";
		exit;
	}

	cbArrayToInts($cid);

	if ( $publish == 0 ) {
		foreach ( $cid as $id ) {
			$row			=	new moscomprofilerPlugin( $_CB_database );
			if ( $row->load( (int) $id ) ) {
				if ( ( $row->type == "language" ) && $row->published ) {
					cbRedirect( 'index2.php?option='. $option .'&task=showPlugins', 'Language plugins cannot be unpublished, only uninstalled', 'error' );
				} elseif ( ( $row->id == 1 ) && $row->published ) {
					cbRedirect( 'index2.php?option='. $option .'&task=showPlugins', 'Core plugin cannot be unpublished', 'error' );
				}
			} else {
				cbRedirect( 'index2.php?option='. $option .'&task=showPlugins', 'Plugin can not be found', 'error' );
			}
		}
	}
	$cids = implode( ',', $cid );

	$query = "UPDATE #__comprofiler_plugin SET published = " . (int) $publish
	. "\n WHERE id IN ($cids)"
	. "\n AND ((checked_out = 0) OR (checked_out = " . (int) $_CB_framework->myId() . "))"
	;
	$_CB_database->setQuery( $query );
	if (!$_CB_database->query()) {
		echo "<script type=\"text/javascript\"> alert('".$_CB_database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row = new moscomprofilerPlugin( $_CB_database );
		$row->checkin( $cid[0] );
	}

	cbRedirect( 'index2.php?option='. $option .'&task=showPlugins'  );
}

/**
* Cancels an edit operation
*/
function cancelPlugin( $option) {
	global $_CB_database, $_POST;

	$row = new moscomprofilerPlugin( $_CB_database );
	$row->bind( $_POST );
	$row->checkin();

	cbRedirect( 'index2.php?option='. $option .'&task=showPlugins' );
}

function cancelPluginAction( $option) {
	global $_POST;

	$pluginId	=	(int) cbGetParam( $_POST, 'cid' );
	if ( $pluginId ) {
		cbRedirect( 'index2.php?option='. $option .'&task=editPlugin&cid=' . $pluginId );
	} else {
		cbRedirect( 'index2.php?option='. $option .'&task=showPlugins' );
	}
}

/**
* Moves the order of a record
* @param integer The unique id of record
* @param integer The increment to reorder by
*/
function orderPlugin( $uid, $inc, $option ) {
	global $_CB_database;

	$row = new moscomprofilerPlugin( $_CB_database );
	$row->load( (int) $uid );
	$row->move( $inc, "type='$row->type' AND ordering > -10000 AND ordering < 10000 "  );

	cbRedirect( 'index2.php?option='. $option .'&task=showPlugins' );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access, $option ) {
	global $_CB_database;

	switch ( $access ) {
		case 'accesspublic':
			$access = 0;
			break;

		case 'accessregistered':
			$access = 1;
			break;

		case 'accessspecial':
			$access = 2;
			break;
	}

	$row = new moscomprofilerPlugin( $_CB_database );
	$row->load( (int) $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	cbRedirect( 'index2.php?option='. $option .'&task=showPlugins' );
	return null;
}

function savePluginOrder( &$cid ) {
	global $_CB_database;
	$row = new moscomprofilerPlugin( $_CB_database );
	$msg = saveOrder( $cid, $row, "\$condition = \"type='\$row->type' AND ordering > -10000 AND ordering < 10000 \";" );
	cbRedirect( 'index2.php?option=com_comprofiler&task=showPlugins', $msg );
} // savePluginOrder

function installPluginUpload() {
	global $_FILES;
	
	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	HTML_comprofiler::secureAboveForm('showPlugins');

	outputCbTemplate( 2 );
	outputCbJs( 2 );
    initToolTip( 2 );

	$option		=	"com_comprofiler";
	$task		=	"showPlugins";
	$client		=	0;
	//echo "installPluginUpload";
	
	$installer	=	new cbInstallerPlugin();

	// Check if file uploads are enabled
	if ( ! (bool) ini_get( 'file_uploads' ) ) {
		HTML_comprofiler::showInstallMessage( "The installer can't continue before file uploads are enabled. Please use the install from directory method.",
			'Installer - Error', $installer->returnTo( $option, $task, $client ) );
		exit();
	}

	// Check that the zlib is available
	if( ! extension_loaded( 'zlib' ) ) {
		HTML_comprofiler::showInstallMessage( "The installer can't continue before zlib is installed",
			'Installer - Error', $installer->returnTo( $option, $task, $client ) );
		exit();
	}

	$userfile				=	cbGetParam( $_FILES, 'userfile', null );

	if ( ! $userfile || ( $userfile == null ) ) {
		HTML_comprofiler::showInstallMessage( 'No file selected', 'Upload new plugin - error',
			$installer->returnTo( $option, $task, $client ));
		exit();
	}

//	$userfile['tmp_name']	=	stripslashes( $userfile['tmp_name'] );
//	$userfile['name']		=	stripslashes( $userfile['name'] );

	$msg		=	'';
	$localName	=	$_FILES['userfile']['name'];
	$resultdir	=	uploadFile( $_FILES['userfile']['tmp_name'], $localName , $msg );		// $localName is updated here
	
	if ( $resultdir !== false ) {
		if ( ! $installer->upload( $localName ) ) {
			if ( $installer->unpackDir() ) {
				cleanupInstall( $localName, $installer->unpackDir() );
			}
			HTML_comprofiler::showInstallMessage( $installer->getError(), 'Upload '.$task.' - Upload Failed',
				$installer->returnTo( $option, $task, $client ) );
		}
		$ret	=	$installer->install();

		cleanupInstall( $localName, null );

		HTML_comprofiler::showInstallMessage( $installer->getError(), 'Upload '.$task.' - ' . ( $ret ? 'Success' : 'Failed' ),
			$installer->returnTo( $option, $task, $client ) );
		cleanupInstall( $localName, $installer->unpackDir() );
	} else {
		HTML_comprofiler::showInstallMessage( $msg, 'Upload '.$task.' -  Upload Error',
			$installer->returnTo( $option, $task, $client ) );
	}
	
}

function _cbAdmin_chmod( $filename ) {
	global $_CB_framework;

	cbimport( 'cb.adminfilesystem' );
	$adminFS			=&	cbAdminFileSystem::getInstance();

	$origmask			=	null;
	if ( $_CB_framework->getCfg( 'dirperms' ) == '' ) {
		// rely on umask
		// $mode			=	0777;
		return true;
	} else {
		$origmask		=	@umask( 0 );
		$mode			=	octdec( $_CB_framework->getCfg( 'dirperms' ) );
	}

	$ret				=	$adminFS->chmod( $filename, $mode );

	if ( isset( $origmask ) ) {
		@umask( $origmask );
	}
	return $ret;
}

function uploadFile( $filename, &$userfile_name, &$msg ) {
	global $_CB_framework;

	cbimport( 'cb.adminfilesystem' );
	$adminFS			=&	cbAdminFileSystem::getInstance();

	$baseDir			=	_cbPathName( $_CB_framework->getCfg('tmp_path') );
	$userfile_name		=	$baseDir . $userfile_name;		// WARNING: this parameter is returned !

	if ( $adminFS->file_exists( $baseDir ) ) {
		if ( $adminFS->is_writable( $baseDir ) ) {
			if ( move_uploaded_file( $filename, $userfile_name ) ) {
//			    if ( _cbAdmin_chmod( $userfile_name ) ) {
			        return true;
//				} else {
//					$msg = 'Failed to change the permissions of the uploaded file.';
//				}
			} else {
				$msg = sprintf( 'Failed to move uploaded file to %s directory.', '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
			}
		} else {
		    $msg = sprintf( 'Upload failed as %s directory is not writable.', '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
		}
	} else {
	    $msg = sprintf( 'Upload failed as %s directory does not exist.', '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
	}
	return false;
}

function installPluginDir() {
	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	HTML_comprofiler::secureAboveForm('showPlugins');

	outputCbTemplate( 2 );
	outputCbJs( 2 );
    initToolTip( 2 );

	$option="com_comprofiler";
	$task="showPlugins";
	$client=0;
	// echo "installPluginDir";
	
	$installer = new cbInstallerPlugin();

	$userfile = cbGetParam( $_REQUEST, 'userfile', null );	
	
	// Check if file name exists
	if (!$userfile) {
		HTML_comprofiler::showInstallMessage( 'No file selected', 'Install new plugin from directory - error',
			$installer->returnTo( $option, $task, $client ) );
		exit();
	}

	$path = _cbPathName( $userfile );
	if (!is_dir( $path )) {
		$path = dirname( $path );
	}

	$ret = $installer->install( $path);

	HTML_comprofiler::showInstallMessage( $installer->getError(), 'Install new plugin from directory '.$userfile.' - '.($ret ? 'Success' : 'Failed'),
		$installer->returnTo( $option, $task, $client ) );
}


function installPluginURL() {
	global $_CB_framework;
	
	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	HTML_comprofiler::secureAboveForm('showPlugins');

	outputCbTemplate( 2 );
	outputCbJs( 2 );
    initToolTip( 2 );

	$option="com_comprofiler";
	$task="showPlugins";
	$client=0;
	// echo "installPluginURL";
	
	$installer = new cbInstallerPlugin();

	// Check that the zlib is available
	if(!extension_loaded('zlib')) {
		HTML_comprofiler::showInstallMessage( "The installer can't continue before zlib is installed",
			'Installer - Error', $installer->returnTo( $option, $task, $client ) );
		exit();
	}

	$userfileURL = cbGetParam( $_REQUEST, 'userfile', null );	

	if (!$userfileURL) {
		HTML_comprofiler::showInstallMessage( 'No URL selected', 'Upload new plugin - error',
			$installer->returnTo( $option, $task, $client ));
		exit();
	}


	cbimport( 'cb.adminfilesystem' );
	$adminFS			=&	cbAdminFileSystem::getInstance();

	if ( $adminFS->isUsingStandardPHP() ) {
		$baseDir		=	_cbPathName( $_CB_framework->getCfg('tmp_path') );
	} else {
		$baseDir		=	$_CB_framework->getCfg( 'absolute_path' ) . '/tmp/';
	}
	$userfileName		=	$baseDir . 'comprofiler_temp.zip';


	$msg			=	'';
	//echo "step-uploadfile<br />";
	$resultdir		=	uploadFileURL( $userfileURL, $userfileName, $msg );
	
	if ($resultdir !== false) {
		//echo "step-upload<br />";
		if (!$installer->upload( $userfileName )) {
			HTML_comprofiler::showInstallMessage( $installer->getError(), 'Download '.$userfileURL.' - Upload Failed',
				$installer->returnTo( $option, $task, $client ) );
		}
		//echo "step-install<br />";
		$ret = $installer->install();

		if ( $ret ) {
			HTML_comprofiler::showInstallMessage( $installer->getError(), 'Download '.$userfileURL.' - '.($ret ? 'Success' : 'Failed'),
													$installer->returnTo( $option, $task, $client ) );
		}
		cleanupInstall( $userfileName, $installer->unpackDir() );
	} else {
		HTML_comprofiler::showInstallMessage( $msg, 'Download '.$userfileURL.' -  Download Error',
												$installer->returnTo( $option, $task, $client ) );
	}
	
}

function uploadFileURL( $userfileURL, $userfile_name, &$msg ) {
	global $_CB_framework;

	include_once( $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_comprofiler/Snoopy.class.php' );

	cbimport( 'cb.adminfilesystem' );
	$adminFS					=&	cbAdminFileSystem::getInstance();

	if ( $adminFS->isUsingStandardPHP() ) {
		$baseDir				=	_cbPathName( $_CB_framework->getCfg('tmp_path') );
	} else {
		$baseDir				=	$_CB_framework->getCfg( 'absolute_path' ) . '/tmp';
	}

	if ( file_exists( $baseDir ) ) {
		if ( $adminFS->is_writable( $baseDir ) || ! $adminFS->isUsingStandardPHP() ) {
			
			$s					=	new Snoopy();
			$fetchResult		=	@$s->fetch( $userfileURL );
	  
			if ( $fetchResult && ! $s->error && ( $s->status == 200 ) ) {
				cbimport( 'cb.adminfilesystem' );
				$adminFS		=&	cbAdminFileSystem::getInstance();
				if ( $adminFS->file_put_contents( $baseDir . $userfile_name, $s->results ) ) {
					if ( _cbAdmin_chmod( $baseDir . $userfile_name ) ) {
						return true;
					} else {
						$msg = 'Failed to change the permissions of the uploaded file '.$baseDir.$userfile_name;
					}
				} else {
					$msg = 'Failed to create and write uploaded file in '.$baseDir.$userfile_name;
				}
			} else {
				$msg = 'Failed to download package file from <code>'.$userfileURL
						.'</code> to <code>/media</code> directory due to following' . ' ' . ( $s->error ? 'error' . ': ' . $s->error : 'status' . ': ' . $s->status . ': ' . $s->response_code );
			}
		} else {
		    $msg = sprintf( 'Upload failed as %s directory is not writable.', '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
		}
	} else {
	    $msg = sprintf( 'Upload failed as %s directory does not exist.', '<code>' . htmlspecialchars( $baseDir ) . '</code>' );
	}
	return false;
}

function clearSearchBox(){
	global $_CB_framework;
	$_CB_framework->setUserState('searchcom_comprofiler','');
}

// Ajax: administrator/index3.php?option=com_comprofiler&task=latestVersion&no_html=1&format=raw :
function latestVersion(){
	global $_CB_framework, $ueConfig;
	
	include_once( $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_comprofiler/Snoopy.class.php' );
	
	$s = new Snoopy();
	$s->read_timeout = 90;
	$s->referer = $_CB_framework->getCfg( 'live_site' );
	@$s->fetch('http://www.joomlapolis.com/versions/comprofilerversion.php?currentversion='.urlencode($ueConfig['version']));
	$version_info = $s->results;
	$version_info_pos = strpos($version_info, ":");
	if ($version_info_pos === false) {
		$version = $version_info;
		$info = null;
	} else {
		$version = substr( $version_info, 0, $version_info_pos );
		$info = substr( $version_info, $version_info_pos + 1 );
	}
	if($s->error || $s->status != 200){
    	echo '<font color="red">Connection to update server failed: ERROR: ' . $s->error . ($s->status == -100 ? 'Timeout' : $s->status).'</font>';
    } else if($version == $ueConfig['version']){
    	echo '<font color="green">' . $version . '</font>' . $info;
    } else {
    	echo '<font color="red">' . $version . '</font>' . $info;
    }
}

// NB for now duplicated in frontend and admin backend:
function tabClass( $option, $task, $uid ) {
	global $_PLUGINS, $_REQUEST, $_POST;

	if ( $uid ) {
		$cbUser				=&	CBuser::getInstance( (int) $uid );
		if ( $cbUser ) {
			$user			=&	$cbUser->getUserData();
		} else {
			$cbUser			=&	CBuser::getInstance( null );
			$user			=	null;
		}
	} else {
		$cbUser				=&	CBuser::getInstance( null );
		$user				=	null;
	}

	$unsecureChars			=	array( '/', '\\', ':', ';', '{', '}', '(', ')', "\"", "'", '.', ',', "\0", ' ', "\t", "\n", "\r", "\x0B" );
	if ( $task == 'fieldclass' ) {
		if ( $user && $user->id ) {
			$uid			=	$user->id;
		} else {
			$uid			=	0;
		}

		$msg				=	checkCBpermissions( array($uid), "edit", true );
		if ( $msg ) {
			echo $msg;
			return;
		}

		$fieldName			=	trim( substr( str_replace( $unsecureChars, '', urldecode( stripslashes( cbGetParam( $_REQUEST, "field" ) ) ) ), 0, 50 ) );
		if ( ! $fieldName ) {
			echo 'no field';
			return;
		}
	} elseif ( $task == 'tabclass' ) {
		$tabClassName		=	urldecode( stripslashes( cbGetParam( $_REQUEST, "tab" ) ) );
		if ( ! $tabClassName ) {
			return;
		}
		$pluginName			=	null;
		$tabClassName		=	substr( str_replace( $unsecureChars, '', $tabClassName ), 0, 32 );
		$method				=	'getTabComponent';
	} elseif ( $task == 'pluginclass' ) {
		$pluginName			=	urldecode( stripslashes( cbGetParam( $_REQUEST, "plugin" ) ) );
		if ( ! $pluginName ) {
			return;
		}
		$tabClassName		=	'CBplug_' . strtolower( substr( str_replace( $unsecureChars, '', $pluginName ), 0, 32 ) );
		$method				=	'getCBpluginComponent';
	}
	$tabs					=	$cbUser->_getCbTabs( false );
	if ( $task == 'fieldclass' ) {
		$result				=	$tabs->fieldCall( $fieldName, $user, $_POST, 'edit' );
	} else {
		$result				=	$tabs->tabClassPluginTabs( $user, $_POST, $pluginName, $tabClassName, $method );
	}
	if ( $result === false ) {
	 	if( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"" . $_PLUGINS->getErrorMSG() . "\"); </script>\n";
	 	}
	} elseif ( $result !== null ) {
		echo $result;
	}
}

function finishInstallation( $option ) {
	global $_CB_framework, $ueConfig, $task;

	// Try extending time, as unziping/ftping took already quite some... :
	@set_time_limit( 240 );

	HTML_comprofiler::secureAboveForm('finishInstallation');

	$tgzFile			=	$_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/pluginsfiles.tgz';
	$installerFile		=	$_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/';
	if ( file_exists( $installerFile . 'comprofiler.xml' ) ) {
		$installerFile	.=	'comprofiler.xml';
	} elseif ( file_exists( $installerFile . 'comprofilej.xml' ) ) {
		$installerFile	.=	'comprofilej.xml';
	}

	if ( ! file_exists( $tgzFile ) ) {
		echo _UE_NOT_AUTHORIZED;
		return;
	}

	$installer = new cbInstallerPlugin();
	$client				=	2;

	// Check that the zlib is available
	if(!extension_loaded('zlib')) {
		HTML_comprofiler::showInstallMessage( "The installer can't continue before zlib is installed",
			'Installer - Error', $installer->returnTo( $option, $task, $client ) );
		exit();
	}

	if ( ! $installer->upload( $tgzFile, true, false ) ) {
		HTML_comprofiler::showInstallMessage( "Uncompressing " . $tgzFile . " failed.",
			'Installer - Error', $installer->returnTo( $option, '', 2 ) );
		exit();
	}

	$installFrom		=	$installer->installDir();
	$installTo			=	$_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/plugin';
	$filesList			=	cbReadDirectory( $installFrom, '.', true );
	// create directories and remove them from file list:
	if ( ! $installer->mosMakePath( dirname( $installTo ) . '/', 'plugin' ) ) {
		HTML_comprofiler::showInstallMessage( 'Failed to create directory "' . $installTo . '/' . 'plugin' . '"',
			'Installer - Error', $installer->returnTo( $option, '', 2 ) );
		exit();
	}
	foreach ( $filesList as $k => $file ) {
		if ( basename( $file ) != $file ) {
			$newdir		=	dirname( $file );
			if ( ! $installer->mosMakePath( $installTo . '/', $newdir ) ) {
				HTML_comprofiler::showInstallMessage( 'Failed to create directory "' . $installTo . '/' . $newdir . '"',
					'Installer - Error', $installer->returnTo( $option, '', 2 ) );
				exit();
			}
		}
		if ( ! is_file( $installFrom . '/' . $file ) ) {
			unset( $filesList[$k] );
		}
	}

	$result				=	$installer->copyFiles( $installFrom, $installTo, $filesList, true );
	if ( $result === false ) {
		HTML_comprofiler::showInstallMessage( "Copying plugin files failed with error: " . $installer->getError(),
			'Installer - Error', $installer->returnTo( $option, '', 2 ) );
		exit();
	}

	$adminFS			=&	cbAdminFileSystem::getInstance();
	$result				=	$adminFS->deldir( _cbPathName( $installFrom . '/' ) );
	if ( $result === false ) {
		HTML_comprofiler::showInstallMessage( "Deleting expanded tgz file directory failed with an error.",
			'Installer - Error', $installer->returnTo( $option, '', 2 ) );
	}
	$tgzFileOS			=	_cbPathName( $tgzFile, false );
	$result				=	$adminFS->unlink( $tgzFileOS );
	if ( $result === false ) {
		HTML_comprofiler::showInstallMessage( "Deleting file " . $tgzFileOS . " failed with an error.",
			'Installer - Error', $installer->returnTo( $option, '', 2 ) );
	}

	// adapt published fields to global CB config (regarding name type)
	_cbAdaptNameFieldsPublished( $ueConfig );

	$htmlToDisplay		=	cbUnHtmlspecialchars( cbUnHtmlspecialchars( $_CB_framework->getUserState( "com_comprofiler_install", null ) ) );

	cbimport( 'cb.xml.simplexml' );
	$installerXml		=	new CBSimpleXMLElement( file_get_contents( $installerFile ) );
	if ( is_object( $installerXml ) ) {
		$description	=	$installerXml->getElementByPath( 'description' );
		if ( $description !== false ) {
			echo '<h2>' . $description->data() . '</h2>';
		}
	}
	echo $htmlToDisplay;
?>
<div style="font-weight:bold;font-size:110%;background:#ffffe4;border:2px green solid;padding:5px;margin-bottom:20px;"><font color="green">Second and last installation step of Community Builder Component (comprofiler) done successfully.</font></div><br />
<div style="font-weight:bold;font-size:125%;background:#ffffe4;border:2px green solid;padding:5px;">
<font color="green"><b>Installation finished. Important: Please read README.TXT and installation manual for further settings. <br /><br />We also have a PDF installation guide as well as a complete documentation available on <a href="http://www.joomlapolis.com">www.joomlapolis.com</a> which will help you making the most out of your Community Builder installation, while supporting this project.</b></font>
</div>
<?php
		$_CB_framework->setUserState( "com_comprofiler_install", '' );
}

?>
