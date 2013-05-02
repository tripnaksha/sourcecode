<?php
/**
 * SEF module for Joomla!
 * Originally written for Mambo as 404SEF by W. H. Welch.
 *
 * @author      $Author: shumisha $
 * @copyright   Yannick Gaultier - 2007
 * @package     sh404SEF-15
 * @version     $Id: admin.sh404sef.php 867 2009-01-17 14:06:33Z silianacom-svn $
 * {shSourceVersionTag: V 1.2.4.x - 2007-09-20}
 */

// Security check to ensure this file is being included by a parent file.
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');
// Ensure that user has access to this function.
$user = &JFactory::getUser();
if (!($user->usertype == 'Super Administrator' || $user->usertype == 'Administrator')) {
  $mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
}

// Setup paths.
$sef_config_class = JPATH_ADMINISTRATOR.'/components/com_sh404sef/sh404sef.class.php';
$sef_config_file  = JPATH_ADMINISTRATOR.'/components/com_sh404sef/config/config.sef.php';

// Make sure class was loaded.
if (!class_exists('SEFConfig')) {   // V 1.2.4.T was wrong variable name $SEFConfig_class instead of $sef_config_class
  if (is_readable($sef_config_class)) require_once($sef_config_class);
  else JError::RaiseError( 500, _COM_SEF_NOREAD."( $sef_config_class )<br />"._COM_SEF_CHK_PERMS);
}

// V 1.2.4.t include language file
shIncludeLanguageFile();

// shumisha 2007-03-13 added URL and iso code caching
require_once(JPATH_ROOT.'/components/com_sh404sef/shCache.php');

// get html class
require_once($mainframe->getPath('admin_html'));
jimport('joomla.html.html');

$cid    = JRequest::getVar( 'cid', array(0));
$sortby = JRequest::getVar( 'sortby', 0);
// V 1.2.4.q initialize variable, to prevent E_NOTICE errors
if (!isset($ViewModeId)) {
  $ViewModeId = JRequest::getVar( 'ViewModeId', 0);
}
if (!isset($section)) {
  $section = JRequest::getVar( 'section', null);
}
if (!isset($task)) {
  $task = JRequest::getVar( 'section', null);
}
if (!isset($eraseCache)) {
  $eraseCache = JRequest::getVar( 'eraseCache', null);
}
if (!isset($returnTo)) {
  $returnTo = JRequest::getVar( 'returnTo', 0);
}
$sefConfig = new SEFConfig();

if (!is_array($cid)) $cid = array(0);
// Action switch.

switch ($task) {
  case 'cancel': {
    cancelsh404($option, $section, $returnTo); // V 1.2.4.t added returnTo
    break;
  }
  case 'edit': {
    if ($section == 'meta') {
      editMeta($cid[0], $option);
    } else {
      editSEF($cid[0], $option);
    }
    break;
  }
  case 'help':
  case 'info': {
    HTML_sef::help();
    break;
  }
  case 'new': {
    editSEF(0, $option);
    break;
  }
  case 'newMeta': {
    editMeta(0, $option, 0);  // V 1.2.4.t  always return to Meta Mngt screen
    break;
  }
  case 'newMetaFromSEF': {
    editMeta(0, $option, 1, $cid[0]);  // V 1.2.4.t return to where we're coming from
    break;
  }
  case 'newHomeMeta': {
    editHomeMeta(0, $option, 0);  // V 1.2.4.t  always return to Meta Mngt screen
    break;
  }
  case 'newHomeMetaFromSEF': {
    editHomeMeta(0, $option, 1);  // V 1.2.4.t return to where we're coming from
    break;
  }
  case 'deleteHomeMeta': {
    deleteHomeMeta($option, 0);  // V 1.2.4.t return to where we're coming from
    break;
  }
  case 'deleteHomeMetaFromSEF': {
    deleteHomeMeta( $option, 1);  // V 1.2.4.t return to where we're coming from
    break;
  }
  case 'homeAlias' :
    editHomeAlias();
    break;
  case 'purge': {
    purge($option, $ViewModeId);
    break;
  }
  case 'purgeMeta': {
    purgeMeta($option);
    break;
  }
  case 'remove': {
    if ($section == 'meta') {
      removeMeta($cid, $option);
    } else {
      removeSEF($cid, $option);
    }
    break;
  }
  case 'save': {
    switch ($section) {
      case 'config' : saveConfig($eraseCache); break;
      case 'meta' : saveMeta($option, empty($returnTo)?0:$returnTo); break;
      case 'homeAlias' : saveHomeAlias(); break;
      default:
        saveSEF($option);
        break;
    }
    break;
  }
      case 'saveconfig': {
        saveConfig($eraseCache);
        break;
      }
      case 'showconfig': {
        showConfig ($option);
        break;
      }
      case 'view': {
        viewSEF($option, $ViewModeId);
        break;
      }
      case 'viewDuplicates': {
        viewDuplicates( !empty($cid[0]) ? $cid[0]:$id, $option);
        break;
      }
      case 'viewMeta': {
        viewMeta( $option);
        break;
      }
      case 'makeMainUrl': {
        makeMainUrl( !empty($cid[0]) ? $cid[0]:$id, $option);
        break;
      }
      case 'import_export': {
        HTML_sef::import_export($ViewModeId);
        break;
      }
      case 'import_export_meta': {
        HTML_sef::import_export_meta();
        break;
      }
      case 'import': {
        $userfile = JRequest::getVar( 'userfile', null, 'FILES');
        if (!$userfile) {
          echo '<p class="error">ERROR UPLOADING FILE</p>';
          exit();
        }
        else{
          import_custom_CSV($userfile, $ViewModeId);
          break;
        }
      }
      case 'setStandardAdmin':
        $sefConfig->shAdminInterfaceType = SH404SEF_STANDARD_ADMIN;
        saveConfig($eraseCache);
        break;
      case 'setAdvancedAdmin':
        $sefConfig->shAdminInterfaceType = SH404SEF_ADVANCED_ADMIN;
        saveConfig($eraseCache);
        break;
      case 'updateSecStats':
        updateSecStats();
        break;
      case 'importOpenSEF': {
        $userfile = JRequest::getVar( 'userfile', null, 'FILES');
        if (!$userfile) {
          echo '<p class="error">ERROR UPLOADING FILE</p>';
          exit();
        }
        else{
          import_custom_CSV_OPEN_SEF($userfile, $ViewModeId);
          break;
        }
      }
      case 'import_meta': {
        $userfile = JRequest::getVar( 'userfile', null, 'FILES');
        if (!$userfile) {
          echo '<p class="error">ERROR UPLOADING FILE</p>';
          exit();
        }
        else{
          import_custom_CSV_meta($userfile);
          break;
        }
      }
      case 'export': {
        export_custom_CSV('sh404SEF_sef_urls.csv', $ViewModeId);
        break;
      }
      case 'export_meta': {
        export_custom_CSV('sh404SEF_meta.csv', 4);
        break;
      }
      case 'dwnld': {
        $returnData = 1;
        $data =  $sefConfig->saveConfig($returnData);
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);
        $data =strtr($data, $trans_tbl);
        output_attachment('config.sef.php',$data);
        exit();
      }
      default: {
        include_once('components/com_sh404sef/404SEF_cpanel.php');
        displayCPanel();
        break;
      }
}

// V 1.2.4.q
function displayCPanel() {
  $sefConfig = & shRouter::shGetConfig();

  $database =& JFactory::getDBO();
  $sql = 'SELECT count(*) FROM #__redirection WHERE ';
  $database->setQuery($sql. "`dateadd` > '0000-00-00' and `newurl` = '' "); // 404
  $Count404 = $database->loadResult();
  $database->setQuery($sql. "`dateadd` > '0000-00-00' and `newurl` != '' " ); // custom
  $customCount = $database->loadResult();
  $database->setQuery($sql. "`dateadd` = '0000-00-00'"); // regular
  $sefCount = $database->loadResult();
  // calculate security stats
  $default = empty($sefConfig->shSecLastUpdated) ? '- -' : '0';
  $shSecStats['curMonth'] = empty($sefConfig->shSecCurMonth) ? $default : $sefConfig->shSecCurMonth;
  if (empty($sefConfig->shSecLastUpdated)) {
    $shSecStats['lastUpdated'] = $default;
  } else {
    $shSecStats['lastUpdated'] = date('Y-m-d H:i:s', $sefConfig->shSecLastUpdated);
  }
  $monthStart = mktime(0,0,0,
  empty($sefConfig->shSecLastUpdated) ? 0: intval(date('m', $sefConfig->shSecLastUpdated)),
  1,
  empty($sefConfig->shSecLastUpdated) ? 0 : intval(date('Y', $sefConfig->shSecLastUpdated)) );
  $hours = $sefConfig->shSecLastUpdated == $monthStart ? 0.0001 : ($sefConfig->shSecLastUpdated - $monthStart)/3600;
  $shSecStats['totalAttacks'] = empty($sefConfig->shSecTotalAttacks) ? $default : $sefConfig->shSecTotalAttacks;
  $shSecStats['totalAttacksHrs'] = $shSecStats['totalAttacks']/$hours;
  $shSecStats['totalConfigVars'] = empty($sefConfig->shSecTotalConfigVars) ? $default : $sefConfig->shSecTotalConfigVars;
  $shSecStats['totalConfigVarsHrs'] = $shSecStats['totalConfigVars']/$hours;
  $shSecStats['totalBase64'] = empty($sefConfig->shSecTotalBase64) ? $default : $sefConfig->shSecTotalBase64;
  $shSecStats['totalBase64Hrs'] = $shSecStats['totalBase64']/$hours;
  $shSecStats['totalScripts'] = empty($sefConfig->shSecTotalScripts) ? $default : $sefConfig->shSecTotalScripts;
  $shSecStats['totalScriptsHrs'] = $shSecStats['totalScripts']/$hours;
  $shSecStats['totalStandardVars'] = empty($sefConfig->shSecTotalStandardVars) ? $default : $sefConfig->shSecTotalStandardVars;
  $shSecStats['totalStandardVarsHrs'] = $shSecStats['totalStandardVars']/$hours;
  $shSecStats['totalImgTxtCmd'] = empty($sefConfig->shSecTotalImgTxtCmd) ? $default : $sefConfig->shSecTotalImgTxtCmd;
  $shSecStats['totalImgTxtCmdHrs'] = $shSecStats['totalImgTxtCmd']/$hours;
  $shSecStats['totalIPDenied'] = empty($sefConfig->shSecTotalIPDenied) ? $default : $sefConfig->shSecTotalIPDenied;
  $shSecStats['totalIPDeniedHrs'] = $shSecStats['totalIPDenied']/$hours;
  $shSecStats['totalUserAgentDenied'] = empty($sefConfig->shSecTotalUserAgentDenied) ? $default : $sefConfig->shSecTotalUserAgentDenied;
  $shSecStats['totalUserAgentDeniedHrs'] = $shSecStats['totalUserAgentDenied']/$hours;
  $shSecStats['totalFlooding'] = empty($sefConfig->shSecTotalFlooding) ? $default : $sefConfig->shSecTotalFlooding;
  $shSecStats['totalFloodingHrs'] = $shSecStats['totalFlooding']/$hours;
  $shSecStats['totalPHP'] = empty($sefConfig->shSecTotalPHP) ? $default : $sefConfig->shSecTotalPHP;
  $shSecStats['totalPHPHrs'] = $shSecStats['totalPHP']/$hours;
  $shSecStats['totalPHPUserClicked'] = empty($sefConfig->shSecTotalPHPUserClicked) ? $default : $sefConfig->shSecTotalPHPUserClicked;
  $shSecStats['totalPHPUserClickedHrs'] = $shSecStats['totalPHPUserClicked']/$hours;
  if (!empty($sefConfig->shSecTotalAttacks)) {
    $shSecStats['totalConfigVarsPct'] = round($sefConfig->shSecTotalConfigVars/$sefConfig->shSecTotalAttacks*100,1);
    $shSecStats['totalBase64Pct'] = round($sefConfig->shSecTotalBase64/$sefConfig->shSecTotalAttacks*100,1);
    $shSecStats['totalScriptsPct'] = round($sefConfig->shSecTotalScripts/$sefConfig->shSecTotalAttacks*100,1);
    $shSecStats['totalStandardVarsPct'] = round($sefConfig->shSecTotalStandardVars/$sefConfig->shSecTotalAttacks*100,1);
    $shSecStats['totalImgTxtCmdPct'] = round($sefConfig->shSecTotalImgTxtCmd/$sefConfig->shSecTotalAttacks*100,1);
    $shSecStats['totalIPDeniedPct'] = round($sefConfig->shSecTotalIPDenied/$sefConfig->shSecTotalAttacks*100,1);
    $shSecStats['totalUserAgentDeniedPct'] = round($sefConfig->shSecTotalUserAgentDenied/$sefConfig->shSecTotalAttacks*100,1);
    $shSecStats['totalFloodingPct'] = round($sefConfig->shSecTotalFlooding/$sefConfig->shSecTotalAttacks*100,1);
    $shSecStats['totalPHPPct'] = round($sefConfig->shSecTotalPHP/$sefConfig->shSecTotalAttacks*100,1);
    $shSecStats['totalPHPUserClickedPct'] = round($sefConfig->shSecTotalPHPUserClicked/$sefConfig->shSecTotalAttacks*100,1);
  } else {
    $shSecStats['totalConfigVarsPct'] = 0;
    $shSecStats['totalBase64Pct'] = 0;
    $shSecStats['totalScriptsPct'] = 0;
    $shSecStats['totalStandardVarsPct'] = 0;
    $shSecStats['totalImgTxtCmdPct'] = 0;
    $shSecStats['totalIPDeniedPct'] = 0;
    $shSecStats['totalUserAgentDeniedPct'] = 0;
    $shSecStats['totalFloodingPct'] = 0;
    $shSecStats['totalPHPPct'] = 0;
    $shSecStats['totalPHPUserClickedPct'] = 0;
  }
  displayCPanelHTML( $sefCount, $Count404, $customCount, $shSecStats);
}
/**
 * List the records
 * @param string The current GET/POST option
 * @param int The mode of view 0=
 */

function viewSEF($option, $ViewModeId = 0)
{
  global $mainframe;

  $list_limit = $mainframe->getCfg( 'list_limit', 10 );
  $database =& JFactory::getDBO();
  $catid = $mainframe->getUserStateFromRequest( "catid{$option}", 'catid', 0 );
  $limit = $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $list_limit );
  $limitstart = $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
  $ViewModeId = $mainframe->getUserStateFromRequest( "viewmode{$option}", 'viewmode', 0 );
  $SortById = $mainframe->getUserStateFromRequest( "SortBy{$option}", 'sortby', 0 );
  // V 1.2.4.q added search URL feature, taken from Joomla content page
  //$search = $mainframe->getUserStateFromRequest( "search{$option}{$sectionid}", 'search', '' );
  $search = $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
  if (get_magic_quotes_gpc()) {
    $search = stripslashes( $search );
  }
  //echo 'Recherche de : '.$search.'<br />';
  // V 1.2.4.q : initialize variables
  $is404mode = false;
  $where = '';
  if ($ViewModeId == 1) {
    $where = "`dateadd` > '0000-00-00' and `newurl` = '' ";
    // V 1.2.4.q : initialize variables
    $is404mode = true;
  }elseif ( $ViewModeId == 2 ) {
    $where = "`dateadd` > '0000-00-00' and `newurl` != '' ";
  }else{
    $where = "`dateadd` = '0000-00-00'";
  }
  if ( !empty($search) ) {  // V 1.2.4.q added search URL feature
    $where .= empty( $where) ? '': ' AND ' . "oldurl  LIKE '%" .
    $database->getEscaped( trim( strtolower( $search ) ) ) . "%'";
  }
  // make the select list for the filter
  $viewmode[] = JHTML::_('select.option', '0', _COM_SEF_SHOW0 );
  $viewmode[] = JHTML::_('select.option', '1', _COM_SEF_SHOW1 );
  $viewmode[] = JHTML::_('select.option', '2', _COM_SEF_SHOW2 );

  $lists['viewmode'] = JHTML::_('select.genericlist', $viewmode, 'viewmode',
    	"class=\"inputbox\"  onchange=\"document.adminForm.submit();\" size=\"1\"" ,
        'value', 'text', $ViewModeId);
  //$lists['viewmode'] = JHTML::_('select.genericlist', $viewmode, 'viewmode', "class=\"inputbox\"  onchange=\"document.adminForm.submit();\" size=\"1\"" ,
  //'value', 'text', $ViewModeId );
  // make the select list for the filter
  $orderby[] = JHTML::_('select.option', '0', _COM_SEF_SEFURL._COM_SEF_ASC);
  $orderby[] = JHTML::_('select.option', '1', _COM_SEF_SEFURL._COM_SEF_DESC );
  if ($is404mode != true) {
    $orderby[] = JHTML::_('select.option', '2', _COM_SEF_REALURL._COM_SEF_ASC );
    $orderby[] = JHTML::_('select.option', '3', _COM_SEF_REALURL._COM_SEF_DESC );
  }
  $orderby[] = JHTML::_('select.option', '4', _COM_SEF_HITS._COM_SEF_ASC );
  $orderby[] = JHTML::_('select.option', '5', _COM_SEF_HITS._COM_SEF_DESC );
  $lists['sortby'] = JHTML::_('select.genericlist', $orderby, 'sortby',
    	"class=\"inputbox\"  onchange=\"document.adminForm.submit();\" size=\"1\"" ,
        'value', 'text', $SortById);
  //$lists['sortby'] = JHTML::_('select.genericlist', $orderby,
  //  'sortby', "class=\"inputbox\"  onchange=\"document.adminForm.submit();\" size=\"1\"" ,
  //  'value', 'text', $SortById );
  switch ($SortById){
    case 1 :
      $sort = "`oldurl` DESC, `rank` ASC";
      break;
    case 2 :
      $sort = "`newurl`, `rank` ASC";
      break;
    case 3 :
      $sort = "`newurl` DESC, `rank` ASC";
      break;
    case 4 :
      $sort = "`cpt`";
      break;
    case 5 :
      $sort = "`cpt` DESC";
      break;
    default :
      $sort = "`oldurl`, `rank` ASC";
      break;
  }
  // get the total number of records
  $query = "SELECT count(*) FROM #__redirection WHERE ".$where;
  $database->setQuery( $query );
  $total = $database->loadResult();
  require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
  $pageNav = new mosPageNav( $total, $limitstart, $limit );
  // get the subset (based on limits) of required records
  $query = "SELECT * FROM #__redirection WHERE ".$where." ORDER BY ".$sort.
    " LIMIT $pageNav->limitstart,$pageNav->limit";
  $database->setQuery( $query );
  $rows = $database->loadObjectList();
  if ($database->getErrorNum()) {
    echo $database->stderr();
    return false;
  }
  //echo 'Requete : '.$query.'<br />';
  //var_dump($rows);
  //die();
  // V 1.2.4.q added search feature
  //HTML_sef::viewSEF( $rows, $lists, $pageNav, $option, $ViewModeId);
  HTML_sef::viewSEF( $rows, $lists, $pageNav, $option, $ViewModeId, $search );
}

function viewDuplicates( $id, $option)
{
  global $mainframe;
  $list_limit = $mainframe->getCfg( 'list_limit', 10 );
  $database =& JFactory::getDBO();
  $limit = $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $list_limit );
  $limitstart = $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
  $SortById = $mainframe->getUserStateFromRequest( "SortBy{$option}", 'sortby', 0 );
  // V 1.2.4.q added search URL feature, taken from Joomla content page
  //$search = $mainframe->getUserStateFromRequest( "search{$option}{$sectionid}", 'search', '' );
  //$search = $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
  //if (get_magic_quotes_gpc()) {
  //  $search = stripslashes( $search );
  //}
  // V 1.2.4.q : initialize variables
  $sql = 'SELECT oldurl FROM #__redirection WHERE id = "'.$id.'"';
  $database->setQuery($sql);
  $oldUrl = $database->loadResult();
  if (!empty($oldUrl)) {
    $where = 'oldurl = "'.$oldUrl.'"';
    //if ( !empty($search) ) {  // V 1.2.4.q added search URL feature
    //  $where .= empty( $where) ? '': ' AND ' . "oldurl  LIKE '%" .
    //                  $database->getEscaped( trim( strtolower( $search ) ) ) . "%'";
    //}
    //echo 'Ajout Requete : '.$where.'<br />';
    // make the select list for the filter
    $orderby[] = JHTML::_('select.option', '0', _COM_SEF_MANAGE_DUPLICATES_RANK._COM_SEF_ASC );
    $orderby[] = JHTML::_('select.option', '1', _COM_SEF_MANAGE_DUPLICATES_RANK._COM_SEF_DESC );
    $orderby[] = JHTML::_('select.option', '2', _COM_SEF_REALURL._COM_SEF_ASC );
    $orderby[] = JHTML::_('select.option', '3', _COM_SEF_REALURL._COM_SEF_DESC );
    $lists['sortby'] = JHTML::_('select.genericlist', $orderby, 'sortby',
    	"class=\"inputbox\"  onchange=\"document.adminForm.submit();\" size=\"1\"" ,
        'value', 'text', $SortById);
    //$lists['sortby'] = JHTML::_('select.genericlist', $orderby,
    //'sortby', "class=\"inputbox\"  onchange=\"document.adminForm.submit();\" size=\"1\"" ,
    //'value', 'text', $SortById );
    switch ($SortById){
      case 1 :
        $sort = "`rank` DESC";
        break;
      case 2 :
        $sort = "`oldurl` ASC";
        break;
      case 3 :
        $sort = "`oldurl` DESC";
        break;
      default:
        $sort = "`rank` ASC";
        break;
    }
    // get the total number of records
    $query = "SELECT count(*) FROM #__redirection WHERE ".$where;
    $database->setQuery( $query );
    $total = $database->loadResult();
    require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
    $pageNav = new mosPageNav( $total, $limitstart, $limit );
    // get the subset (based on limits) of required records
    $query = "SELECT * FROM #__redirection WHERE ".$where." ORDER BY ".$sort.
    " LIMIT $pageNav->limitstart,$pageNav->limit";
    $database->setQuery( $query );
    $rows = $database->loadObjectList();
    if ($database->getErrorNum()) {
      echo $database->stderr();
      return false;
    }
    // V 1.2.4.q added search feature
    //HTML_sef::viewSEF( $rows, $lists, $pageNav, $option, $ViewModeId);
    HTML_sef::viewDuplicates( $rows, $lists, $pageNav, $option, $id
    // ,$search
    );
  }
}

function makeMainUrl( $id, $option) {
  // find out about selected URL
  $database =& JFactory::getDBO();
  $sql = 'SELECT oldurl, rank,id FROM #__redirection WHERE id = "'.$id.'"';
  $database->setQuery($sql);
  $selectedUrl = $database->loadObject();
  if (!empty($selectedUrl)) {
    if ($selectedUrl->rank == 0) {
      shRedirect( 'index.php?option='.$option.'&task=view', _COM_SEF_BAD_DUPLICATES_NOTHING_TO_DO );
    } else {
      // we need to find what it the current main URL, then we'll swap ranks
      $sql = 'SELECT id, rank FROM #__redirection WHERE oldurl = "'.$selectedUrl->oldurl.'" ORDER BY `rank` ASC';
      $database->setQuery($sql);
      $prevMainUrl = $database->loadObject();
      //var_dump($prevMainUrl);
      if (!empty($prevMainUrl)) {  // update both URL
        $sql = 'UPDATE #__redirection SET rank ="'.$selectedUrl->rank.'" WHERE `id` = "'.$prevMainUrl->id.'"';
        $database->setQuery($sql);
        $shErr = !$database->query();
        $sql = 'UPDATE #__redirection SET rank="0" WHERE `id` = "'.$id.'"';
        $database->setQuery($sql);
        $shErr = !$database->query() && $shErr;
        shRedirect( 'index.php?option='.$option.'&task=view',
        $shErr ? _COM_SEF_MAKE_MAIN_URL_ERROR:_COM_SEF_MAKE_MAIN_URL_OK, '301', $shErr ? 'error' : 'message' );
      } else shRedirect( 'index.php?option='.$option.'&task=view', _COM_SEF_BAD_DUPLICATES_DATA, '301', 'error' );
    }
  } else shRedirect( 'index.php?option='.$option.'&task=view', _COM_SEF_BAD_DUPLICATES_DATA, '301', 'error' );
}

/**
 * Creates a new or edits and existing user record
 * @param int The id of the user, 0 if a new entry
 * @param string The current GET/POST option
 */

function editSEF( $id, $option ) {
  global $mainframe;
  $database =& JFactory::getDBO();
  $LinkTypeId = $mainframe->getUserStateFromRequest( "linktype{$option}", 'linktype', 0 );
  $SectionId = $mainframe->getUserStateFromRequest( "sectionid{$option}", 'sectionid', 0 );
  $CategoryId = $mainframe->getUserStateFromRequest( "categoryid{$option}", 'categoryid', 0 );
  $ContentId = $mainframe->getUserStateFromRequest( "contentid{$option}", 'contentid', 0 );
  $row = new shMosSEF( $database );
  // load the row from the db table
  $row->load( $id );
  if ($id) {
    // do stuff for existing records
    if ($row->dateadd != "0000-00-00" ) $row->dateadd = date("Y-m-d");
  } else {
    // do stuff for new records
    $row->dateadd = date("Y-m-d");
  }
  // V 1.3.1 fetch aliases
  $query = 'SELECT alias FROM #__sh404sef_aliases as a, #__redirection as r '
  . 'WHERE r.newurl = a.newurl AND r.id = \''.$id.'\'';
  $database->setQuery($query);
  $aliases = $database->loadObjectList();
  $lists['shAliasList'] = '';
  if (!empty($aliases))
  foreach($aliases as $alias)
  $lists['shAliasList'] .= $alias->alias."\n";
  HTML_sef::editSEF( $row, $lists, $option );
}

function viewMeta($option)
{
  global $mainframe;
  $list_limit = $mainframe->getCfg( 'list_limit', 10 );
  $database =& JFactory::getDBO();
  $limit = $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $list_limit );
  $limitstart = $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
  $SortById = $mainframe->getUserStateFromRequest( "SortBy{$option}", 'sortby', 0 );
  // V 1.2.4.q added search URL feature, taken from Joomla content page
  //$search = $mainframe->getUserStateFromRequest( "search{$option}{$sectionid}", 'search', '' );
  $search = $mainframe->getUserStateFromRequest( "searchMeta{$option}", 'searchMeta', '' );
  if (get_magic_quotes_gpc()) {
    $search = stripslashes( $search );
  }
  // V 1.2.4.t  exclude homepage meta, which are edited using a specific button, as URl may vary
  $where = 'newurl != \''.sh404SEF_HOMEPAGE_CODE.'\'';
  if ( !empty($search) ) {  // V 1.2.4.q added search URL feature
    $where .= " AND newurl LIKE '%" .
    $database->getEscaped( trim( strtolower( $search ) ) ) . "%'";
  }
  // make the select list for the filter
  $orderby[] = JHTML::_('select.option', '0', _COM_SEF_REALURL._COM_SEF_ASC );
  $orderby[] = JHTML::_('select.option', '1', _COM_SEF_REALURL._COM_SEF_DESC );
  $lists['sortby'] = JHTML::_('select.genericlist', $orderby, 'sortby',
    	"class=\"inputbox\"  onchange=\"document.adminForm.submit();\" size=\"1\"" ,
        'value', 'text', $SortById);
  //$lists['sortby'] = JHTML::_('select.genericlist', $orderby,
  //'sortby', "class=\"inputbox\"  onchange=\"document.adminForm.submit();\" size=\"1\"" ,
  //'value', 'text', $SortById );
  switch ($SortById){
    case 1 :
      $sort = "`newurl` DESC";
      break;
    default :
      $sort = "`newurl` ASC";
      break;
  }
  // get the total number of records
  $query = "SELECT count(*) FROM #__sh404SEF_meta WHERE ".$where;
  //echo '$query : '.$query.'<br />';
  $database->setQuery( $query );
  $total = $database->loadResult();
  require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
  $pageNav = new mosPageNav( $total, $limitstart, $limit );
  // get the subset (based on limits) of required records
  $query = "SELECT * FROM #__sh404SEF_meta WHERE ".$where." ORDER BY ".$sort.
    " LIMIT $pageNav->limitstart,$pageNav->limit";
  //echo '$query : '.$query.'<br />';
  $database->setQuery( $query );
  $rows = $database->loadObjectList();
  if ($database->getErrorNum()) {
    echo $database->stderr();
    return false;
  }
  HTML_sef::viewMeta( $rows, $lists, $pageNav, $option, $search );
}

/**
 * Creates a new or edits and existing user record
 * @param int The id of the user, 0 if a new entry, $returnTO = 0 : back to Metamanagement, 1 = to SEF URl list
 * @param string The current GET/POST option
 */

function editMeta( $id, $option, $returnTo = 0, $redirId = 0 ) {

  $database =& JFactory::getDBO();
  $row = new sh404SEFMeta( $database );
  // load the row from the db table
  $url = '';
  $row->load( $id );
  if ($redirId) {  // do stuff for existing records
    $sql = 'SELECT oldurl, newurl FROM #__redirection WHERE id=\''.$redirId.'\';';
    $database->setQuery($sql);
    $url = $database->loadObject();
    if (!empty($url)) {
      $row->newurl = $url->newurl;
      $sql = 'SELECT * from #__sh404SEF_meta WHERE newurl = \''.$url->newurl.'\';';
      $database->setQuery($sql);
      $newMeta = $database->loadObject();
      if (!empty($newMeta)) {
        $row->id = $newMeta->id;
        $row->metatitle = $newMeta->metatitle;
        $row->metadesc = $newMeta->metadesc;
        $row->metakey = $newMeta->metakey;
        $row->metarobots = $newMeta->metarobots;
        $row->metalang = $newMeta->metalang;
      }
    }
  }
  if ($returnTo == 1)  // V 1.2.4.t
  $editUrl = 0;
  else $editUrl = 1;
  HTML_sef::editMeta( $row, $option, $returnTo, $editUrl, empty($url) ? '':$url->oldurl);
}

// V 1.2.4.t edit homepage meta

function editHomeMeta( $id, $option, $returnTo = 0) { // 0 = return to Meta page, 1 = return to SEF URL page
  $database =& JFactory::getDBO();
  $row = new sh404SEFMeta( $database );
  // load the row from the db table
  $row->load( $id );
  $row->newurl = sh404SEF_HOMEPAGE_CODE;
  $sql = 'SELECT * from #__sh404SEF_meta WHERE newurl = "'.$row->newurl.'"';
  $database->setQuery($sql);
  $newMeta = $database->loadObject();
  if (!empty($newMeta)) {
    $row->id = $newMeta->id;
    $row->metatitle = $newMeta->metatitle;
    $row->metadesc = $newMeta->metadesc;
    $row->metakey = $newMeta->metakey;
    $row->metarobots = $newMeta->metarobots;
    $row->metalang = $newMeta->metalang;
  }
  HTML_sef::editMeta( $row, $option, $returnTo, 0, ''); // V 1.2.4.t never edit URL if home meta
}

function deleteHomeMeta( $option, $returnTo = 0) {
  $database =& JFactory::getDBO();
  $sql = 'DELETE from #__sh404SEF_meta WHERE newurl = "'.sh404SEF_HOMEPAGE_CODE.'"';
  $database->setQuery($sql);
  $database->query();
  if ($database->getErrorNum()) {
    $mosmsg = $database->stderr();
  } else $mosmsg = _COM_SEF_SUCCESSPURGE;
  $returnTask = empty($returnTo) ? '&task=viewMeta' : '&task=view';
  shRedirect( 'index.php?option='.$option.$returnTask, $mosmsg );
}
/**
 * Saves the record from an edit form submit
 * @param string The current GET/POST option
 */

function saveSEF( $option ) {
  $sefConfig = shRouter::shGetConfig();
  $database =& JFactory::getDBO();
  $errMsg = '';
  $row = new shMosSEF( $database );
  $saveOldUrl = JRequest::getVar( 'saveOldUrl', null, 'POST');
  if (!$row->bind( $_POST )) {
    echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
    exit();
  }
  // pre-save checks
  $check = $row->check();
  if (!$check) {
    echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
    exit();
  }
  if ($check === true) {
    // shumisha 2007-03-16 remove previous redirection from cache
    shLoadURLCache(); // must load cache from disk, so that it can be written back later, with new url
    $urlType = $row->dateadd == '0000-00-00' ? sh404SEF_URLTYPE_AUTO : sh404SEF_URLTYPE_CUSTOM; // V 1.2.4.t
    if (   ($urlType == sh404SEF_URLTYPE_CUSTOM)
    && !preg_match( '/(&|\?)lang=[a-zA-Z]{2,3}/iU', $row->newurl)) {  // no lang string, let's add default
    		$shTemp = explode( '_', $GLOBALS['mosConfig_locale']);
    		$shLangTemp = $shTemp[0] ? $shTemp[0] : 'en';
    		$row->newurl .= '&lang='.$shLangTemp;
    }
    $row->newurl = shSortUrl($row->newurl); // V 1.2.4.t
    $query = "SELECT newurl, rank, id FROM #__redirection WHERE oldurl = '".$row->oldurl."' ORDER BY rank ASC";
    $database->setQuery($query);
    $dbUrlList = $database->loadObjectList();
    if (count($dbUrlList) > 0) {   // there are URL with same SEF URL
      if (!$sefConfig->shRecordDuplicates) {  // we don't allow duplicates : reject this URL
        $errMsg = _COM_SEF_DUPLICATE_NOT_ALLOWED;
      }else {  // same SEF, but we allow duplicates
        $existingRecord = null;
        foreach ($dbUrlList as $urlInDB) {  // same SEF, but is the non-sef in this list of URl with same SEF ?
          if ($urlInDB->newurl == $row->newurl) {
            $existingRecord = $urlInDB;
            $errMsg = _COM_SEF_URLEXIST;
          }
        }
        if (empty( $existingRecord)) {  // this new non-sef does not already exists
          $shTemp = array('nonSefURL' => $row->newurl);   // which means we must update the record for the old non-sef url
          shRemoveURLFromCache($shTemp);  // remove the old url from cache
          $shNewMaxRank = $dbUrlList[count($dbUrlList)-1]->rank+1;
          $query = "UPDATE #__redirection SET oldurl='".$row->oldurl."', newurl='"
          .$row->newurl."', rank='".$shNewMaxRank."', dateadd='".$row->dateadd."' WHERE id = '".$row->id."'";  // update DB
          $database->setQuery($query);
          $database->query();
          shAddSefURLToCache( $row->newurl, $row->oldurl, $urlType); // put custom URL in DB and cache
          // V 1.3.1 add old SEF to alias list
          if (strpos($_POST['shAliasList'], $saveOldUrl) === false)
          $_POST['shAliasList'] .= $saveOldUrl. "\n";
        } else {
          // the old non-sef does not exists, we are creating a new record from scratch
          $shTemp = array('nonSefURL' => $row->newurl);
          shAddSefURLToCache( $row->newurl, $row->oldurl, $URLType);  // add also cache
          if (!$row->store()) {  // simply store URL. If there is already one with same non-sef, this will raise an error in store()
            echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
            exit();
          }

        }
      }
    } else {   // there is no URL with same SEF URL
      $shTemp = array('nonSefURL' => $row->newurl);
      shRemoveURLFromCache($shTemp);  // remove it from cache
      shAddSefURLToCache( $row->newurl, $row->oldurl, $URLType);  // add also cache
      if (!$row->store()) {  // simply store URL. If there is already one with same non-sef, this will raise an error in store()
        echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
        exit();
      }
      // V 1.3.1 add old SEF to alias list
      if (strpos($_POST['shAliasList'], $saveOldUrl) === false)
      $_POST['shAliasList'] .= $saveOldUrl. "\n";
    }
  }
  // V 1.3.1 update alias list in db in $row->shAliasList
  $aliases = JRequest::getVar( 'shAliasList', null, 'POST');
  saveAliases( $aliases, $row->newurl);
  shRedirect( 'index.php?option='.$option.'&task=view', $errMsg, '301', empty($errMsg) ? 'message' : 'error' );
}

// save aliases attached to non-sef newurl
function saveAliases($aliases, $newurl) {

  $database =& JFactory::getDBO();
  $aliases = explode("\n", $aliases);
  // delete them all. We should do a transaction, but not worth it
  $query = 'DELETE from #__sh404sef_aliases where newurl = \''.$newurl.'\'';
  $database->setQuery($query);
  $database->query();
  // Write new aliases
  if (!empty($aliases[0])) {
    $query = 'INSERT INTO #__sh404sef_aliases (newurl, alias) VALUES __shValues__;';
    $values = '';
    $endOfLine = array("\r\n", "\n", "\r");
    foreach($aliases as $alias) {
    		$alias = str_replace($endOfLine, '', $alias);
    		if (!empty($alias)) $values .=  '(\''.$newurl.'\', \''.$alias.'\'),';
    }
    $query = str_replace('__shValues__', rtrim($values, ','), $query);
    $database->setQuery($query);
    $database->query();
  }
}

function editHomeAlias() { // edit aliases for homepage

  $database =& JFactory::getDBO();
  $query = 'SELECT alias FROM #__sh404sef_aliases WHERE newurl = \''.sh404SEF_HOMEPAGE_CODE.'\';';
  $database->setQuery($query);
  $aliases = $database->loadObjectList();
  $lists['shAliasList'] = '';
  if (!empty($aliases))
  foreach($aliases as $alias)
  $lists['shAliasList'] .= $alias->alias."\n";
  HTML_sef::editHomeAlias( $lists );
}

function saveHomeAlias() { // save homepage aliases to DB
  $aliases = JRequest::getVar( 'shAliasList', 'POST');
  saveAliases( $aliases, sh404SEF_HOMEPAGE_CODE);
  shRedirect( 'index.php?option=com_sh404sef&task=view');
}

/**
 * Removes records
 * @param array An array of id keys to remove
 * @param string The current GET/POST option
 */

function removeSEF( &$cid, $option ) {
  $database =& JFactory::getDBO();
  if (!is_array( $cid ) || count( $cid ) < 1) {
    echo "<script> alert('"._COM_SEF_SELECT_DELETE."'); window.history.go(-1);</script>\n";
    exit;
  }
  if (count( $cid )) {
    $cids = implode( ',', $cid );
    // shumisha 2007-03-16 remove also from URL cache
    $query = "SELECT `newurl` FROM #__redirection"
    . "\n WHERE id IN ($cids)";
    $database->setQuery( $query );
    $rows = $database->loadResultArray();
    shLoadURLCache(); // must load cache from disk, so that it can be written back later properly
    shRemoveURLFromCache($rows);
    // shumisha end of change
    $query = "DELETE FROM #__redirection"
    . "\n WHERE id IN ($cids)"
    ;
    $database->setQuery( $query );
    if (!$database->query()) {
      echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
    }
  }
  shRedirect( 'index.php?option='.$option.'&task=view' );
}

/**
 * Saves the record from an edit form submit
 * @param string The current GET/POST option
 * $returnTo : 0 -> return to meta management, 1 return to SEF URL List
 */

function saveMeta( $option, $returnTo = 0 ) {

  $database =& JFactory::getDBO();
  $row = new sh404SEFMeta( $database );
  if (!$row->bind( $_POST )) {
    echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
    exit();
  }
  if ( $row->newurl != sh404SEF_HOMEPAGE_CODE &&  // don't add on homepage
  !preg_match( '/(&|\?)lang=[a-zA-Z]{2,3}/iU', $row->newurl)) {  // no lang string, let's add default
    $shTemp = explode( '_', $GLOBALS['mosConfig_locale']);
    $shLangTemp = $shTemp[0] ? $shTemp[0] : 'en';
    $row->newurl .= '&lang='.$shLangTemp;

  }

  if ( $row->newurl != sh404SEF_HOMEPAGE_CODE)
  $row->newurl = shSortUrl($row->newurl); // V 1.2.4.t
  // pre-save checks
  if (!$row->check()) {
    echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
    exit();
  }
  //echo '<br />after check; <br />';
  // save the changes
  if (!$row->store()) {
    echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
    exit();
  }
  $returnTask = empty($returnTo) ? '&task=viewMeta' : '&task=view';
  shRedirect( 'index.php?option='.$option.$returnTask );
}

/**
 * Removes records
 * @param array An array of id keys to remove
 * @param string The current GET/POST option
 */

function removeMeta( &$cid, $option ) {

  $database =& JFactory::getDBO();
  if (!is_array( $cid ) || count( $cid ) < 1) {
    echo "<script> alert('"._COM_SEF_SELECT_DELETE."'); window.history.go(-1);</script>\n";
    exit;
  }
  if (count( $cid )) {
    $cids = implode( ',', $cid );
    $query = "DELETE FROM #__sh404SEF_meta"
    . "\n WHERE id IN ($cids)";
    $database->setQuery( $query );
    if (!$database->query()) {
      echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
    }
  }
  shRedirect( 'index.php?option='.$option.'&task=viewMeta' );
}

/**
 * Cancels an edit operation
 * @param string The current GET/POST option
 */

function cancelsh404( $option, $section, $returnTo ) {  // V 1.2.4.s reworked for meta V 1.2.4.t added returnTo
  if (!empty($returnTo))
  shRedirect( 'index.php?option='.$option.'&task=view' );
  switch ($section) {
    case 'config':
      shRedirect( 'index.php?option='.$option );
      break;
    case 'meta' :
      shRedirect( 'index.php?option='.$option.'&task=viewMeta' );
      break;
    default:
      shRedirect( 'index.php?option='.$option.'&task=view' );
      break;
  }
}

function showConfig($option)
{
  global $sef_config_file;

  $sefConfig = & shRouter::shGetConfig();
  
  // special check for Joomfish 2.0 : must be sure href are not cached in language selection module
  // otherwise new SEF urls will not be created
  shDisableJFModuleCaching();
  
  $database =& JFactory::getDBO();
  $std_opt = 'class="inputbox" size="2"';
  $lists['enabled'] =  JHTML::_('select.booleanlist', 'Enabled', $std_opt, $sefConfig->Enabled );
  $lists['lowercase'] =  JHTML::_('select.booleanlist','LowerCase', $std_opt, $sefConfig->LowerCase );
  $lists['showsection'] =  JHTML::_('select.booleanlist','ShowSection', $std_opt, $sefConfig->ShowSection );
  $lists['showcat'] =  JHTML::_('select.booleanlist','ShowCat', $std_opt, $sefConfig->ShowCat );
  // shumisha 2007-04-01 new params for cache :
  $lists['shUseURLCache'] =  JHTML::_('select.booleanlist','shUseURLCache', $std_opt, $sefConfig->shUseURLCache );
  // shumisha 2007-04-03 new params for translation and Itemid :
  $lists['shTranslateURL'] =  JHTML::_('select.booleanlist','shTranslateURL', $std_opt, $sefConfig->shTranslateURL );
  $lists['shInsertLanguageCode'] =  JHTML::_('select.booleanlist','shInsertLanguageCode', $std_opt,
  $sefConfig->shInsertLanguageCode );
  $lists['shInsertGlobalItemidIfNone'] =  JHTML::_('select.booleanlist','shInsertGlobalItemidIfNone',
  $std_opt, $sefConfig->shInsertGlobalItemidIfNone );
  $lists['shInsertTitleIfNoItemid'] =  JHTML::_('select.booleanlist','shInsertTitleIfNoItemid',
  $std_opt, $sefConfig->shInsertTitleIfNoItemid );
  $lists['shAlwaysInsertMenuTitle'] =  JHTML::_('select.booleanlist','shAlwaysInsertMenuTitle',
  $std_opt, $sefConfig->shAlwaysInsertMenuTitle );
  $lists['shAlwaysInsertItemid'] =  JHTML::_('select.booleanlist','shAlwaysInsertItemid',
  $std_opt, $sefConfig->shAlwaysInsertItemid );
  // shumisha 2007-04-11 new params for Numerical Id insert :
  $lists['shInsertNumericalId'] =  JHTML::_('select.booleanlist','shInsertNumericalId',
  $std_opt, $sefConfig->shInsertNumericalId );
  // build the html select list for category : copied from rd_rss admin file
  // note : we could do only one request to db and sort in memory !
  $lookup = '';
  if ( $sefConfig->shInsertNumericalIdCatList ) {
    // V 1.2.4.q shInsertNumericalIdCatList can be empty so let's protect query
    $shANDCatList = implode(', ', $sefConfig->shInsertNumericalIdCatList);
    if (!empty($shANDCatList))
    $shANDCatList = "\n AND c.id IN ( ".$shANDCatList." )";
    $query = "SELECT c.id AS `value`, c.section AS `id`, CONCAT_WS( ' / ', s.title, c.title) AS `text`"
    . "\n FROM #__sections AS s"
    . "\n INNER JOIN #__categories AS c ON c.section = s.id"
    . "\n WHERE s.scope = 'content'"
    // V 1.2.4.q shInsertNumericalIdCatList can be empty so let's protect query
    . $shANDCatList
    . "\n ORDER BY s.name,c.name"
    ;
    $database->setQuery( $query );
    $lookup = $database->loadObjectList();
  }
  $category[] = JHTML::_('select.option', '', _COM_SEF_SH_INSERT_NUMERICAL_ID_ALL_CAT );
  $query = "SELECT c.id AS `value`, c.section AS `id`, CONCAT_WS( ' / ', s.title, c.title) AS `text`"
  . "\n FROM #__sections AS s"
  . "\n INNER JOIN #__categories AS c ON c.section = s.id"
  . "\n WHERE s.scope = 'content'"
  . "\n ORDER BY s.name,c.name"
  ;
  $database->setQuery( $query );
  $catList = $database->loadObjectList();
  if (is_array( $catList))
  $category = array_merge( $category, $catList);
  $category = JHTML::_('select.genericlist', $category, 'shInsertNumericalIdCatList[]',
    	'class="inputbox" size="10" multiple="multiple"' ,
        'value', 'text', $lookup);
  //$category = JHTML::_('select.genericlist', $category, 'shInsertNumericalIdCatList[]',
  //'class="inputbox" size="10" multiple="multiple"', 'value', 'text', $lookup );
  $lists['shInsertNumericalIdCatList'] = $category;
  // shumisha 2007-04-03 new params for Virtuemart plugin :
  $lists['shVmInsertShopName'] =  JHTML::_('select.booleanlist','shVmInsertShopName',
  $std_opt, $sefConfig->shVmInsertShopName );
  $lists['shInsertProductId'] =  JHTML::_('select.booleanlist','shInsertProductId',
  $std_opt, $sefConfig->shInsertProductId );
  $lists['shVmUseProductSKU'] =  JHTML::_('select.booleanlist','shVmUseProductSKU',
  $std_opt, $sefConfig->shVmUseProductSKU );
  $lists['shVmInsertManufacturerName'] =  JHTML::_('select.booleanlist','shVmInsertManufacturerName',
  $std_opt, $sefConfig->shVmInsertManufacturerName );
  $lists['shInsertManufacturerId'] =  JHTML::_('select.booleanlist','shInsertManufacturerId',
  $std_opt, $sefConfig->shInsertManufacturerId );
  $shVMInsertCat[] = JHTML::_('select.option', '0', _COM_SEF_SH_VM_DO_NOT_SHOW_CATEGORIES );
  $shVMInsertCat[] = JHTML::_('select.option', '1', _COM_SEF_SH_VM_SHOW_LAST_CATEGORY );
  $shVMInsertCat[] = JHTML::_('select.option', '2', _COM_SEF_SH_VM_SHOW_ALL_CATEGORIES );
  $lists['shVMInsertCategories'] = JHTML::_('select.genericlist', $shVMInsertCat, 'shVMInsertCategories',
      "class=\"inputbox\" size=\"1\"" , 'value', 'text',  $sefConfig->shVMInsertCategories); 
  $lists['shInsertCategoryId'] =  JHTML::_('select.booleanlist','shInsertCategoryId',
  $std_opt, $sefConfig->shInsertCategoryId );
  $lists['shVmInsertFlypage'] =  JHTML::_('select.booleanlist','shVmInsertFlypage',  // V 1.2.4.q
  $std_opt, $sefConfig->shVmInsertFlypage );
  // shumisha 2007-04-03 end of new params for Virtuemart plugin

  // V 1.2.4.q new param for URL encoding
  $lists['shEncodeUrl'] =  JHTML::_('select.booleanlist','shEncodeUrl',
  $std_opt, $sefConfig->shEncodeUrl );

  $lists['guessItemidOnHomepage'] =  JHTML::_('select.booleanlist','guessItemidOnHomepage',
  $std_opt, $sefConfig->guessItemidOnHomepage );

  $lists['shForceNonSefIfHttps'] =  JHTML::_('select.booleanlist','shForceNonSefIfHttps',  // V 1.2.4.q
  $std_opt, $sefConfig->shForceNonSefIfHttps );

  $shRewriteMode[] = JHTML::_('select.option', '0', _COM_SEF_SH_RW_MODE_NORMAL );
  $shRewriteMode[] = JHTML::_('select.option', '1', _COM_SEF_SH_RW_MODE_INDEXPHP );
  $shRewriteMode[] = JHTML::_('select.option', '2', _COM_SEF_SH_RW_MODE_INDEXPHP2 );

  $lists['shRewriteMode'] = JHTML::_('select.genericlist', $shRewriteMode, 'shRewriteMode',
      "class=\"inputbox\" size=\"1\"" , 'value', 'text',  $sefConfig->shRewriteMode); 

  $lists['shRecordDuplicates'] =  JHTML::_('select.booleanlist','shRecordDuplicates',  // V 1.2.4.r
  $std_opt, $sefConfig->shRecordDuplicates );
  $lists['shRemoveGeneratorTag'] =  JHTML::_('select.booleanlist','shRemoveGeneratorTag',  // V 1.2.4.r
  $std_opt, $sefConfig->shRemoveGeneratorTag );
  $lists['shPutH1Tags'] =  JHTML::_('select.booleanlist','shPutH1Tags',  // V 1.2.4.r
  $std_opt, $sefConfig->shPutH1Tags );
  $lists['shMetaManagementActivated'] =  JHTML::_('select.booleanlist','shMetaManagementActivated',  // V 1.2.4.r
  $std_opt, $sefConfig->shMetaManagementActivated );
  $lists['shInsertContentTableName'] =  JHTML::_('select.booleanlist','shInsertContentTableName',  // V 1.2.4.r
  $std_opt, $sefConfig->shInsertContentTableName );
  $lists['shAutoRedirectWww'] =  JHTML::_('select.booleanlist','shAutoRedirectWww',  // V 1.2.4.r
  $std_opt, $sefConfig->shAutoRedirectWww );
  $lists['shVmInsertProductName'] =  JHTML::_('select.booleanlist','shVmInsertProductName',  // V 1.2.4.s
  $std_opt, $sefConfig->shVmInsertProductName );

  $lists['usealias'] =  JHTML::_('select.booleanlist','UseAlias', $std_opt, $sefConfig->UseAlias );
  
  // shumisha 2007-04-11 new params for non-sef to sef 301 redirect :
  $lists['shRedirectNonSefToSef'] =  JHTML::_('select.booleanlist','shRedirectNonSefToSef',
  $std_opt, $sefConfig->shRedirectNonSefToSef );
  // shumisha 2007-05-04 new params for joomla sef to sef 301 redirect :
  $lists['shRedirectJoomlaSefToSef'] =  JHTML::_('select.booleanlist','shRedirectJoomlaSefToSef',
  $std_opt, $sefConfig->shRedirectJoomlaSefToSef );
  // shumisha 2007-04-25 new params to activate iJoomla magazine in content :
  $lists['shActivateIJoomlaMagInContent'] =  JHTML::_('select.booleanlist','shActivateIJoomlaMagInContent',
  $std_opt, $sefConfig->shActivateIJoomlaMagInContent );
  $lists['shInsertIJoomlaMagIssueId'] =  JHTML::_('select.booleanlist','shInsertIJoomlaMagIssueId',
  $std_opt, $sefConfig->shInsertIJoomlaMagIssueId );
  $lists['shInsertIJoomlaMagName'] =  JHTML::_('select.booleanlist','shInsertIJoomlaMagName',
  $std_opt, $sefConfig->shInsertIJoomlaMagName );
  $lists['shInsertIJoomlaMagMagazineId'] =  JHTML::_('select.booleanlist','shInsertIJoomlaMagMagazineId',
  $std_opt, $sefConfig->shInsertIJoomlaMagMagazineId );
  $lists['shInsertIJoomlaMagArticleId'] =  JHTML::_('select.booleanlist','shInsertIJoomlaMagArticleId',
  $std_opt, $sefConfig->shInsertIJoomlaMagArticleId );
  // shumisha 2007-04-27 new params for Community Builder :
  $lists['shInsertCBName'] =  JHTML::_('select.booleanlist','shInsertCBName',
  $std_opt, $sefConfig->shInsertCBName );
  $lists['shCBInsertUserName'] =  JHTML::_('select.booleanlist','shCBInsertUserName',
  $std_opt, $sefConfig->shCBInsertUserName );
  $lists['shCBInsertUserId'] =  JHTML::_('select.booleanlist','shCBInsertUserId',
  $std_opt, $sefConfig->shCBInsertUserId );
  $lists['shCBUseUserPseudo'] =  JHTML::_('select.booleanlist','shCBUseUserPseudo',
  $std_opt, $sefConfig->shCBUseUserPseudo );

  // V 1.2.4.k 404 errors loggin is now optional
  $lists['shLog404Errors'] =  JHTML::_('select.booleanlist','shLog404Errors',
  $std_opt, $sefConfig->shLog404Errors );
  $lists['shVmAdditionalText'] =  JHTML::_('select.booleanlist','shVmAdditionalText',
  $std_opt, $sefConfig->shVmAdditionalText );
  $lists['shVmInsertFlypage'] =  JHTML::_('select.booleanlist','shVmInsertFlypage',
  $std_opt, $sefConfig->shVmInsertFlypage );

  // V 1.2.4.m added fireboard params
  $lists['shInsertFireboardName'] =  JHTML::_('select.booleanlist','shInsertFireboardName',
  $std_opt, $sefConfig->shInsertFireboardName );

  $lists['shFbInsertCategoryName'] =  JHTML::_('select.booleanlist','shFbInsertCategoryName',
  $std_opt, $sefConfig->shFbInsertCategoryName );
  $lists['shFbInsertCategoryId'] =  JHTML::_('select.booleanlist','shFbInsertCategoryId',
  $std_opt, $sefConfig->shFbInsertCategoryId );
  $lists['shFbInsertMessageSubject'] =  JHTML::_('select.booleanlist','shFbInsertMessageSubject',
  $std_opt, $sefConfig->shFbInsertMessageSubject );
  $lists['shFbInsertMessageId'] =  JHTML::_('select.booleanlist','shFbInsertMessageId',
  $std_opt, $sefConfig-> shFbInsertMessageId);

  // V 1.2.4.r MyBlog params
  $lists['shInsertMyBlogName'] =  JHTML::_('select.booleanlist','shInsertMyBlogName',
  $std_opt, $sefConfig->shInsertMyBlogName );
  $lists['shMyBlogInsertPostId'] =  JHTML::_('select.booleanlist','shMyBlogInsertPostId',
  $std_opt, $sefConfig->shMyBlogInsertPostId );
  $lists['shMyBlogInsertTagId'] =  JHTML::_('select.booleanlist','shMyBlogInsertTagId',
  $std_opt, $sefConfig->shMyBlogInsertTagId );
  $lists['shMyBlogInsertBloggerId'] =  JHTML::_('select.booleanlist','shMyBlogInsertBloggerId',
  $std_opt, $sefConfig->shMyBlogInsertBloggerId );

  /* Docman parameters  V 1.2.4.r*/
  $lists['shInsertDocmanName'] =  JHTML::_('select.booleanlist','shInsertDocmanName',
  $std_opt, $sefConfig->shInsertDocmanName );
  $lists['shDocmanInsertDocId'] =  JHTML::_('select.booleanlist','shDocmanInsertDocId',
  $std_opt, $sefConfig->shDocmanInsertDocId );
  $lists['shDocmanInsertDocName'] =  JHTML::_('select.booleanlist','shDocmanInsertDocName',
  $std_opt, $sefConfig->shDocmanInsertDocName );
  $lists['shDMInsertCategoryId'] =  JHTML::_('select.booleanlist','shDMInsertCategoryId',  // V 1.2.4.t
  $std_opt, $sefConfig->shDMInsertCategoryId );
  $shDMInsertCat[] = JHTML::_('select.option', '0', _COM_SEF_SH_VM_DO_NOT_SHOW_CATEGORIES );
  $shDMInsertCat[] = JHTML::_('select.option', '1', _COM_SEF_SH_VM_SHOW_LAST_CATEGORY );
  $shDMInsertCat[] = JHTML::_('select.option', '2', _COM_SEF_SH_VM_SHOW_ALL_CATEGORIES );
  $lists['shDMInsertCategories'] = JHTML::_('select.genericlist', $shDMInsertCat, 'shDMInsertCategories', "class=\"inputbox\" size=\"1\"" , 'value', 'text',  $sefConfig->shDMInsertCategories);


  $lists['shInsertContentBlogName'] =  JHTML::_('select.booleanlist','shInsertContentBlogName',  // V 1.2.4.t
  $std_opt, $sefConfig->shInsertContentBlogName );

  $lists['shInsertMTreeName'] =  JHTML::_('select.booleanlist','shInsertMTreeName',  // V 1.2.4.t
  $std_opt, $sefConfig->shInsertMTreeName );
  $lists['shMTreeInsertListingName'] =  JHTML::_('select.booleanlist','shMTreeInsertListingName',  // V 1.2.4.t
  $std_opt, $sefConfig->shMTreeInsertListingName );
  $lists['shMTreeInsertListingId'] =  JHTML::_('select.booleanlist','shMTreeInsertListingId',  // V 1.2.4.t
  $std_opt, $sefConfig->shMTreeInsertListingId );
  $lists['shMTreePrependListingId'] =  JHTML::_('select.booleanlist','shMTreePrependListingId',  // V 1.2.4.t
  $std_opt, $sefConfig->shMTreePrependListingId );
  $shMTreeInsertCat[] = JHTML::_('select.option', '0', _COM_SEF_SH_VM_DO_NOT_SHOW_CATEGORIES );
  $shMTreeInsertCat[] = JHTML::_('select.option', '1', _COM_SEF_SH_VM_SHOW_LAST_CATEGORY );
  $shMTreeInsertCat[] = JHTML::_('select.option', '2', _COM_SEF_SH_VM_SHOW_ALL_CATEGORIES );
  $lists['shMTreeInsertCategories'] = JHTML::_('select.genericlist', $shMTreeInsertCat, 'shMTreeInsertCategories', "class=\"inputbox\" size=\"1\"" , 'value', 'text',  $sefConfig->shMTreeInsertCategories);
  $lists['shMTreeInsertCategoryId'] =  JHTML::_('select.booleanlist','shMTreeInsertCategoryId',  // V 1.2.4.t
  $std_opt, $sefConfig->shMTreeInsertCategoryId );
  $lists['shMTreeInsertUserName'] =  JHTML::_('select.booleanlist','shMTreeInsertUserName',  // V 1.2.4.t
  $std_opt, $sefConfig->shMTreeInsertUserName );
  $lists['shMTreeInsertUserId'] =  JHTML::_('select.booleanlist','shMTreeInsertUserId',  // V 1.2.4.t
  $std_opt, $sefConfig->shMTreeInsertUserId );

  $lists['shInsertNewsPName'] =  JHTML::_('select.booleanlist','shInsertNewsPName',  // V 1.2.4.t
  $std_opt, $sefConfig->shInsertNewsPName );
  $lists['shNewsPInsertCatId'] =  JHTML::_('select.booleanlist','shNewsPInsertCatId',  // V 1.2.4.t
  $std_opt, $sefConfig->shNewsPInsertCatId );
  $lists['shNewsPInsertSecId'] =  JHTML::_('select.booleanlist','shNewsPInsertSecId',  // V 1.2.4.t
  $std_opt, $sefConfig->shNewsPInsertSecId );

  /* Remository parameters  V 1.2.4.t  */
  $lists['shInsertRemoName'] =  JHTML::_('select.booleanlist','shInsertRemoName',
  $std_opt, $sefConfig->shInsertRemoName );
  $lists['shRemoInsertDocId'] =  JHTML::_('select.booleanlist','shRemoInsertDocId',
  $std_opt, $sefConfig->shRemoInsertDocId );
  $lists['shRemoInsertDocName'] =  JHTML::_('select.booleanlist','shRemoInsertDocName',
  $std_opt, $sefConfig->shRemoInsertDocName );
  $lists['shRemoInsertCategoryId'] =  JHTML::_('select.booleanlist','shRemoInsertCategoryId',  // V 1.2.4.t
  $std_opt, $sefConfig->shRemoInsertCategoryId );
  $shRemoInsertCat[] = JHTML::_('select.option', '0', _COM_SEF_SH_VM_DO_NOT_SHOW_CATEGORIES );
  $shRemoInsertCat[] = JHTML::_('select.option', '1', _COM_SEF_SH_VM_SHOW_LAST_CATEGORY );
  $shRemoInsertCat[] = JHTML::_('select.option', '2', _COM_SEF_SH_VM_SHOW_ALL_CATEGORIES );
  $lists['shRemoInsertCategories'] = JHTML::_('select.genericlist', $shRemoInsertCat, 'shRemoInsertCategories', "class=\"inputbox\" size=\"1\"" , 'value', 'text',  $sefConfig->shRemoInsertCategories);

  // V 1.2.4.t 16/08/2007 15:43:31
  $lists['shCBShortUserURL'] =  JHTML::_('select.booleanlist','shCBShortUserURL',
  $std_opt, $sefConfig->shCBShortUserURL );

  // V 1.2.4.t 19/08/2007 16:26:46
  $lists['shKeepStandardURLOnUpgrade'] =  JHTML::_('select.booleanlist','shKeepStandardURLOnUpgrade',
  $std_opt, $sefConfig->shKeepStandardURLOnUpgrade );
  $lists['shKeepCustomURLOnUpgrade'] =  JHTML::_('select.booleanlist','shKeepCustomURLOnUpgrade',
  $std_opt, $sefConfig->shKeepCustomURLOnUpgrade );
  $lists['shKeepMetaDataOnUpgrade'] =  JHTML::_('select.booleanlist','shKeepMetaDataOnUpgrade',
  $std_opt, $sefConfig->shKeepMetaDataOnUpgrade );

  // V 1.2.4.t 24/08/2007 12:56:16
  $lists['shMultipagesTitle'] =  JHTML::_('select.booleanlist','shMultipagesTitle',
  $std_opt, $sefConfig->shMultipagesTitle );


  // V x
  $lists['shKeepConfigOnUpgrade'] =  JHTML::_('select.booleanlist','shKeepConfigOnUpgrade',
  $std_opt, $sefConfig->shKeepConfigOnUpgrade );

  // security parameters  V x
  $lists['shSecEnableSecurity'] =  JHTML::_('select.booleanlist','shSecEnableSecurity',
  $std_opt, $sefConfig->shSecEnableSecurity );
  $lists['shSecLogAttacks'] =  JHTML::_('select.booleanlist','shSecLogAttacks',
  $std_opt, $sefConfig->shSecLogAttacks );
  $lists['shSecOnlyNumVars'] = implode("\n",$sefConfig->shSecOnlyNumVars);
  $lists['shSecAlphaNumVars'] = implode("\n",$sefConfig->shSecAlphaNumVars);
  $lists['shSecNoProtocolVars'] = implode("\n",$sefConfig->shSecNoProtocolVars);
  $lists['ipWhiteList'] = implode("\n",$sefConfig->ipWhiteList);
  $lists['ipBlackList'] = implode("\n",$sefConfig->ipBlackList);
  $lists['uAgentWhiteList'] = implode("\n",$sefConfig->uAgentWhiteList);
  $lists['uAgentBlackList'] = implode("\n",$sefConfig->uAgentBlackList);

  $lists['shSecCheckHoneyPot'] =  JHTML::_('select.booleanlist','shSecCheckHoneyPot',
  $std_opt, $sefConfig->shSecCheckHoneyPot );
  $lists['shSecActivateAntiFlood'] =  JHTML::_('select.booleanlist','shSecActivateAntiFlood',
  $std_opt, $sefConfig->shSecActivateAntiFlood );
  $lists['shSecAntiFloodOnlyOnPOST'] =  JHTML::_('select.booleanlist','shSecAntiFloodOnlyOnPOST',
  $std_opt, $sefConfig->shSecAntiFloodOnlyOnPOST );

  //$lists['insertSectionInBlogTableLinks'] =  JHTML::_('select.booleanlist','insertSectionInBlogTableLinks',
  //  $std_opt, $sefConfig->insertSectionInBlogTableLinks );

  // V x : per language insert iso code and translate URl params and page text

  $activeLanguages = shGetActiveLanguages();
  $lists['activeLanguages'][] = $GLOBALS['shMosConfig_locale'];  // put default in first place

  $shLangOption[] = JHTML::_('select.option', '0', _COM_SEF_SH_DEFAULT );
  $shLangOption[] = JHTML::_('select.option', '1', _COM_SEF_SH_YES );
  $shLangOption[] = JHTML::_('select.option', '2', _COM_SEF_SH_NO );

  foreach ($activeLanguages as $language) {
    $currentLang = $language->code;
    if ($currentLang != $GLOBALS['shMosConfig_locale']) $lists['activeLanguages'][] = $currentLang;
  		$lists['languages_'.$currentLang.'_translateURL'] =
  		JHTML::_('select.genericlist', $shLangOption, 'languages_'.$currentLang.'_translateURL',
  								 "class=\"inputbox\" size=\"1\"" , 'value', 'text',  $sefConfig->shLangTranslateList[$currentLang]);
  		$lists['languages_'.$currentLang.'_insertCode'] =
  		JHTML::_('select.genericlist', $shLangOption, 'languages_'.$currentLang.'_insertCode',
  								 "class=\"inputbox\" size=\"1\"" , 'value', 'text',  $sefConfig->shLangInsertCodeList[$currentLang]);	 
  }

  // V 1.3 RC shCustomTags params
  $lists['shInsertReadMorePageTitle'] =  JHTML::_('select.booleanlist','shInsertReadMorePageTitle',
  $std_opt, $sefConfig->shInsertReadMorePageTitle );
  $lists['shMultipleH1ToH2'] =  JHTML::_('select.booleanlist','shMultipleH1ToH2',
  $std_opt, $sefConfig->shMultipleH1ToH2 );

  // V 1.3.1 RC
  $lists['shVmUsingItemsPerPage'] =  JHTML::_('select.booleanlist','shVmUsingItemsPerPage',
  $std_opt, $sefConfig->shVmUsingItemsPerPage );
  $lists['shSecCheckPOSTData'] =  JHTML::_('select.booleanlist','shSecCheckPOSTData',
  $std_opt, $sefConfig->shSecCheckPOSTData );

  $lists['shInsertSMFName'] =  JHTML::_('select.booleanlist','shInsertSMFName',
  $std_opt, $sefConfig->shInsertSMFName );
  $lists['shInsertSMFBoardId'] =  JHTML::_('select.booleanlist','shInsertSMFBoardId',
  $std_opt, $sefConfig->shInsertSMFBoardId );
  $lists['shInsertSMFTopicId'] =  JHTML::_('select.booleanlist','shInsertSMFTopicId',
  $std_opt, $sefConfig->shInsertSMFTopicId );
  $lists['shinsertSMFUserName'] =  JHTML::_('select.booleanlist','shinsertSMFUserName',
  $std_opt, $sefConfig->shinsertSMFUserName );
  $lists['shInsertSMFUserId'] =  JHTML::_('select.booleanlist','shInsertSMFUserId',
  $std_opt, $sefConfig->shInsertSMFUserId );

  $lists['debugToLogFile'] =  JHTML::_('select.booleanlist','debugToLogFile',
  $std_opt, $sefConfig->debugToLogFile );

  // V 1.3.1
  $lists['shInsertOutboundLinksImage'] =  JHTML::_('select.booleanlist','shInsertOutboundLinksImage',
  $std_opt, $sefConfig->shInsertOutboundLinksImage );
  $shInsertImgLnk[] = JHTML::_('select.option', 'external-black.png', _COM_SEF_SH_OUTBOUND_LINKS_IMAGE_BLACK );
  $shInsertImgLnk[] = JHTML::_('select.option', 'external-white.png', _COM_SEF_SH_OUTBOUND_LINKS_IMAGE_WHITE );

  $lists['shImageForOutboundLinks'] = JHTML::_('select.genericlist', $shInsertImgLnk, 'shImageForOutboundLinks',
    	"class=\"inputbox\" size=\"1\"" , 'value', 'text',  $sefConfig->shImageForOutboundLinks);

  // get a list of the static content items for 404 page
  $query = "SELECT id, title"
  . "\n FROM #__content"
  . "\n WHERE sectionid = 0 AND title != '404'"
  . "\n AND catid = 0"
  . "\n ORDER BY ordering"
  ;
  $database->setQuery( $query );
  $items = $database->loadObjectList();
  $options = array(  JHTML::_('select.option', 0, "("._COM_SEF_DEF_404_PAGE.")")  );
  //$options[] = JHTML::_('select.option', 9999999, "(Front Page)" ); // 1.2.4.t
  // assemble menu items to the array
  foreach ( $items as $item ) {
    $options[] = JHTML::_('select.option', $item->id, $item->title );
  }
  $lists['page404'] = JHTML::_('select.genericlist', $options, 'page404', 'class="inputbox" size="1"', 'value', 'text', $sefConfig->page404 );
  $sql='SELECT id,introtext FROM #__content WHERE `title`="404"';
  $row = null;
  $database->setQuery($sql);
  $row = $database->loadObject(  );
  if (!empty($row) && !empty($row->introtext))  // V 1.2.4.t
  $txt404 = $row->introtext;
  else
  $txt404 = _COM_SEF_DEF_404_MSG;
  // get list of installed components for advanced config
  $installed_components = $undefined_components = array();
  $sql='SELECT SUBSTRING(link,8) AS name FROM #__components WHERE CHAR_LENGTH(link) > 0 ORDER BY name';
  $database->setQuery($sql);
  $installed_components = $database->loadResultArray();
  $installed_components = str_replace('com_', '', $installed_components); // V 1.2.4.m
  $undefined_components= array_values(array_diff($installed_components,array_intersect($sefConfig->predefined, $installed_components)));
  //build mode list and create the list
  $mode = array();
  $mode[] = JHTML::_('select.option', 0, _COM_SEF_USE_DEFAULT);
  $mode[] = JHTML::_('select.option', 1, _COM_SEF_NOCACHE);
  $mode[] = JHTML::_('select.option', 2, _COM_SEF_SKIP);
  $modeTranslate[] = JHTML::_('select.option', 0, _COM_SEF_SH_TRANSLATE_URL); // V 1.2.4.m
  $modeTranslate[] = JHTML::_('select.option', 1, _COM_SEF_SH_DO_NOT_TRANSLATE_URL);
  $modeInsertIso[] = JHTML::_('select.option', 0, _COM_SEF_SH_INSERT_LANGUAGE_CODE);
  $modeInsertIso[] = JHTML::_('select.option', 1, _COM_SEF_SH_DO_NOT_INSERT_LANGUAGE_CODE);
  $modeshDoNotOverrideOwnSef[] = JHTML::_('select.option', 0, _COM_SEF_SH_OVERRIDE_SEF_EXT);
  $modeshDoNotOverrideOwnSef[] = JHTML::_('select.option', 1, _COM_SEF_SH_DO_NOT_OVERRIDE_SEF_EXT);
  while (list($index, $name) = each($undefined_components)){
    $selectedmode = ((in_array($name,$sefConfig->nocache))*1)+((in_array($name,$sefConfig->skip))*2);
    $lists['adv_config'][$name]['manageURL'] = JHTML::_('select.genericlist', $mode, 'com_'.$name.'___manageURL', 'class="inputbox" size="1"', 'value', 'text',$selectedmode);
    $selectedmode = in_array($name,$sefConfig->notTranslateURLList);
    $lists['adv_config'][$name]['translateURL'] = JHTML::_('select.genericlist', $modeTranslate, 'com_'.$name.'___translateURL', 'class="inputbox" size="1"', 'value', 'text',$selectedmode);

    $selectedmode = in_array($name,$sefConfig->notInsertIsoCodeList);
    $lists['adv_config'][$name]['insertIsoCode'] = JHTML::_('select.genericlist', $modeInsertIso, 'com_'.$name.'___insertIsoCode', 'class="inputbox" size="1"', 'value', 'text',$selectedmode);

    $selectedmode = in_array($name,$sefConfig->shDoNotOverrideOwnSef);
    $lists['adv_config'][$name]['shDoNotOverrideOwnSef'] = JHTML::_('select.genericlist', $modeshDoNotOverrideOwnSef, 'com_'.$name.'___shDoNotOverrideOwnSef', 'class="inputbox" size="1"', 'value', 'text',$selectedmode);
    $defaultString = empty($sefConfig->defaultComponentStringList[@$name]) ? '':$sefConfig->defaultComponentStringList[$name];
    $compName = 'com_'.$name.'___defaultComponentString';
    $lists['adv_config'][$name]['defaultComponentString'] =
        	'<td width="150"><input type="text" name="'.$compName.'" value="'.$defaultString.'" size="30" maxlength="30"></td>';
  }

  // V 1.0.3
  $lists['defaultParamList'] = $sefConfig->defaultParamList;

  //  V 1.012
  $lists['useCatAlias'] =  JHTML::_('select.booleanlist','useCatAlias',
  $std_opt, $sefConfig->useCatAlias );
  $lists['useSecAlias'] =  JHTML::_('select.booleanlist','useSecAlias',
  $std_opt, $sefConfig->useSecAlias );
  $lists['useMenuAlias'] =  JHTML::_('select.booleanlist','useMenuAlias',
  $std_opt, $sefConfig->useMenuAlias );
  $lists['shEnableTableLessOutput'] =  JHTML::_('select.booleanlist','shEnableTableLessOutput',
  $std_opt, $sefConfig->shEnableTableLessOutput );
  
  HTML_sef::configuration($lists, $txt404);
}


function shSetArrayParam($value, &$param) {
  if (!empty($value)) {
    $param = explode("\n", $value);
    foreach ($param as $k=>$v) {
    		$param[$k] = trim($v);
    }
  } else
  $param = array();
  if (!empty($param))
  $param = array_filter($param);
}

function advancedConfig($key,$value){

  $sefConfig = & shRouter::shGetConfig();
  if ((strpos($key,"com_")) !== false) {
    // V 1.2.4.m
    $key = str_replace('com_','',$key);
    $param = explode('___',$key);
    switch ($param[1]) {
      case 'manageURL' :
        switch ($value) {
          case 1 :
            array_push($sefConfig->nocache,$param[0]);
            break;
          case 2 :
            array_push($sefConfig->skip,$param[0]);
            break;
        }
        break;
          case 'translateURL':
            if ($value == 1)
            array_push($sefConfig->notTranslateURLList,$param[0]);
            break;
          case 'insertIsoCode':
            if ($value == 1)
            array_push($sefConfig->notInsertIsoCodeList,$param[0]);
            break;
          case 'shDoNotOverrideOwnSef':
            if ($value == 1)
            array_push($sefConfig->shDoNotOverrideOwnSef,$param[0]);
            break;
          case 'defaultComponentString':
            $cleanedUpValue = empty($value) ? '': titleToLocation($value);
            $cleanedUpValue = trim( $cleanedUpValue, $sefConfig->friendlytrim);
            $sefConfig->defaultComponentStringList[$param[0]] = $cleanedUpValue;
            break;
    }
  } else {

    switch ($key){
      case 'shSecOnlyNumVars':
        shSetArrayParam($value, $sefConfig->shSecOnlyNumVars);
        break;
      case 'shSecAlphaNumVars':
        shSetArrayParam($value, $sefConfig->shSecAlphaNumVars);
        break;
      case 'shSecNoProtocolVars':
        shSetArrayParam($value, $sefConfig->shSecNoProtocolVars);
        break;
    }

    if (preg_match('/languages_([a-zA-Z]{2}-[a-zA-Z]{2})_translateURL/U', $key, $matches)) {
      $sefConfig->shLangTranslateList[$matches[1]] = $value;
    }
    if (preg_match('/languages_([a-zA-Z]{2}-[a-zA-Z]{2})_insertCode/U', $key, $matches)) {
      $sefConfig->shLangInsertCodeList[$matches[1]] = $value;
    }
    if (preg_match('/languages_([a-zA-Z]{2}-[a-zA-Z]{2})_pageText/U', $key, $matches)) {
      $sefConfig->pageTexts[$matches[1]] = $value;
    }
  }
}

function saveConfig($eraseCache) {

  global $sef_config_file;

  $sefConfig = & shRouter::shGetConfig();
  
  $database =& JFactory::getDBO();
  //set skip and nocache arrays, unless POST is empty, meaning this is first attempt to save config
  if (!empty($_POST)) {
    $sefConfig->skip = array();
    $sefConfig->nocache = array();
    $sefConfig->notTranslateURLList = array();
    $sefConfig->notInsertIsoCodeList = array();
    $sefConfig->shDoNotOverrideOwnSef = array();
    $sefConfig->shSecOnlyNumVars = array();
    $sefConfig->shSecAlphaNumVars = array();
    $sefConfig->shSecNoProtocolVars = array();
    $sefConfig->ipWhiteList = array();
    $sefConfig->ipBlackList = array();
    $sefConfig->uAgentWhiteList = array();
    $sefConfig->uAgentBlackList = array();
    $sefConfig->shLangTranslateList = array();
    $sefConfig->shLangInsertCodeList = array();
    $sefConfig->defaultComponentStringList = array();
  }
  if (empty($_POST['debugToLogFile'])) {
    $sefConfig->debugStartedAt = 0;
  } else {
    $sefConfig->debugStartedAt = empty($sefConfig->debugStartedAt) ? time() : $sefConfig->debugStartedAt;
  }
  if (!empty($_POST)) {
    foreach($_POST as $key => $value) {
    		$sefConfig->set($key, $value);
    		advancedConfig($key, $value);
    }
  }
  $shIntroText = empty($_POST) ? '' : $_POST['introtext'];
  $sql='SELECT id  FROM #__content WHERE `title`="404"';
  $database->setQuery( $sql );
  if ($id = $database->loadResult()){
    $sql = 'UPDATE #__content SET introtext="'.$shIntroText.'",  modified ="'.date("Y-m-d H:i:s").'" WHERE `id` = "'.$id.'";';
  }else{
    $sql='SELECT MAX(id)  FROM #__content';
    $database->setQuery( $sql );
    if ($max = $database->loadResult()){
      $max++;
      $sql = 'INSERT INTO #__content VALUES( "'.$max.'", "404", "404", "404", "'.$shIntroText.'", "", "1", "0", "0", "0", "2004-11-11 12:44:38", "62", "", "'.date("Y-m-d H:i:s").'", "62", "0", "2004-11-11 12:45:09", "2004-10-17 00:00:00", "0000-00-00 00:00:00", "", "", "menu_image=-1\nitem_title=0\npageclass_sfx=\nback_button=\nrating=0\nauthor=0\ncreatedate=0\nmodifydate=0\npdf=0\nprint=0\nemail=0", "1", "0", "0", "", "", "0", "0", "");';
    }
  }
  $database->setQuery( $sql );
  if (!$database->query()) {
    echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
  }
  $config_written = $sefConfig->saveConfig(0);
  if($config_written != 0) {
    // V 1.2.4.t 20/08/2007 16:14:37 added another confirm from user
    if ($eraseCache)
    shRedirect( $GLOBALS['shConfigLiveSite']
    .'/index.php?option=com_sh404sef&task=purge&viewmode=0&confirmed=0');
    else shRedirect( "index.php?option=com_sh404sef", _COM_SEF_CONFIG_UPDATED );
  }else shRedirect( "index.php?option=com_sh404sef", _COM_SEF_WRITE_ERROR, '301', 'error');
}

function purge($option, $ViewModeId=0  ) {

  global $mainframe;

  $database =& JFactory::getDBO();
  $ViewModeId = $mainframe->getUserStateFromRequest( "viewmode{$option}", 'viewmode', 0 );
  $SortById = $mainframe->getUserStateFromRequest( "SortBy{$option}", 'sortby', 0 );
  $confirmed = JRequest::getVar( 'confirmed', '' ); // mambo checks default value type, must be '' instead of 0
  switch ($ViewModeId) {
    case '1': // 404
      $where = "`dateadd` > '0000-00-00' and `newurl` = '' ";
      break;
    case '2':  // custom
      $where = "`dateadd` > '0000-00-00' and `newurl` != '' ";
      break;
    default:  // automatic
      $where = "`dateadd` = '0000-00-00'";
      break;
  }
  if ( !empty($confirmed)){
    $query = "DELETE FROM #__redirection WHERE ".$where;
    // shumisha 2007-03-14 URL caching : we must clear URL cache as well
    if (file_exists(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php'))
    unlink(JPATH_ROOT.'/components/com_sh404sef/cache/shCacheContent.php');
    // shumisha end of addition
    $database->setQuery( $query );
    if (!$database->query()) {
      echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
      //JError::RaiseError(500,$database->getErrorMsg());
    }else{
      $message = _COM_SEF_SUCCESSPURGE;
    }
    shRedirect('index.php?option=com_sh404sef', $message);
  }else{
    // get the total number of records
    $query = "SELECT count(*) FROM #__redirection WHERE ".$where;
    $database->setQuery( $query );
    $total = $database->loadResult();
    if (!$database->query()) {
      echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
    }
    switch ($total) {
      case '0';
      $message = _COM_SEF_NORECORDS;
      shRedirect('index.php?option=com_sh404sef', $message);
      break;
      case '1';
      $message = _COM_SEF_WARNDELETE.$total._COM_SEF_RECORD;
      break;
      default:
        $message = _COM_SEF_WARNDELETE.$total._COM_SEF_RECORDS;
    }
    HTML_sef::purge($option, $message, $confirmed);
  }
}

function purgeMeta($option ) {  // V 1.2.4.s

  $database =& JFactory::getDBO();
  $confirmed = JRequest::getVar( 'confirmed', 0 );
  if ( !empty($confirmed)){
    $query = "DELETE FROM #__sh404SEF_meta WHERE 1";
    $database->setQuery( $query );
    if (!$database->query()) {
      echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
    }else{
      $message = _COM_SEF_META_SUCCESS_PURGE;
    }
    shRedirect('index.php?option=com_sh404sef', $message);
  }else{
    // get the total number of records
    $query = "SELECT count(*) FROM #__sh404SEF_meta WHERE 1";
    $database->setQuery( $query );
    $total = $database->loadResult();
    if (!$database->query()) {
      echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
    }
    switch ($total) {
      case '0';
      $message = _COM_SEF_NORECORDS;
      shRedirect('index.php?option=com_sh404sef', $message);
      break;
      case '1';
      $message = _COM_SEF_WARNDELETE.$total._COM_SEF_RECORD;
      break;
      default:
        $message = _COM_SEF_WARNDELETE.$total._COM_SEF_RECORDS;
    }
    HTML_sef::purgeMeta($option, $message, $confirmed);
  }
}
function backup_custom(){
  global $mainframe;

  $database =& JFactory::getDBO();
  $SQL = array();
  $table = $mainframe->getCfg('dbprefix').'redirection';
  $query ="SELECT * FROM `$table` WHERE `dateadd` > '0000-00-00' and `newurl` != '' ";
  $database->setQuery( $query );
  if ($rows = $database->loadRowList()) {
    foreach ($rows as $row) {
      $SQL[] = "INSERT INTO `$table` VALUES('','".$row[1]."','".$row[2]."','".$row[3]."','".$row[4]."');\n";
    }
  }else{
    JError::RaiseError( 500, _COM_SEF_NOACCESS.$table);
  }
  return $SQL;
}

function shNonEmpty($string) {  // V 1.2.4.s
  if (empty($string))
  return '&nbsp';
  else return $string;
}
function shUnEmpty($string) {  // V 1.2.4.s
  if ($string == '&nbsp')
  return '';
  else return $string;
}

function backup_custom_CSV( $which = 0){ // which = 0:all, 2 = custom redirect, 1 = 404)

  $database =& JFactory::getDBO();
  $CSV = array();
  switch ($which) {
    case 1: // 404
      $where = "WHERE `dateadd` > '0000-00-00' and `newurl` == '' ";
      break;  // Custom
    case 2:
      $where = "WHERE `dateadd` > '0000-00-00' and `newurl` != '' ";
      break;
    default:  // default
      $where = '';
      break;
  }
  $CSV[] = "\"id\",\"Count\",\"Rank\",\"SEF URL\",\"non-SEF URL\",\"Date added\"\n"; // V 1.2.4.s
  $query ='SELECT * FROM #__redirection '.$where;
  $database->setQuery( $query );
  $rows = $database->loadRowList();
  if (!empty($rows)) {
    foreach ($rows as $row) {
      $CSV[] = "\"$row[0]\",\"$row[1]\",\"$row[2]\",\"$row[3]\",\"$row[4]\",\"$row[5]\"\n";  // V 1.2.4.s
    }
  }else{
    shRedirect('index.php?option=com_sh404sef',_COM_SEF_NOACCESS);
  }
  return $CSV;
}

function output_attachment($filename,&$data){

  if (!headers_sent()) {
    header ('Expires: 0');
    header ('Last-Modified: '.gmdate ('D, d M Y H:i:s', time()) . ' GMT');
    header ('Pragma: public');
    header ('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header ('Accept-Ranges: bytes');
    header ('Content-Length: ' . strlen($data));
    header ('Content-Type: Application/octet-stream');
    header ('Content-Disposition: attachment; filename="' . $filename . '"');
    header ('Connection: close');
    ob_end_clean(); //flush the mambo stuff from the ouput buffer
    print $data; // and send the sql
    die();
  }else{
    shRedirect('index.php?option=com_sh404sef',_COM_SEF_FATAL_ERROR_HEADERS, '301', 'error');
  }
}

function export_custom($filename){

  $database =& JFactory::getDBO();
  $sql_data = backup_custom();
  $sql_data = implode("\r", $sql_data);
  if (!headers_sent()) {
    while (ob_get_level() > 0) {
      ob_end_clean(); //flush the mambo stuff from the ouput buffer
    }
    // Determine Browser
    if (ereg( 'MSIE ([0-9].[0-9]{1,2})',$_SERVER["HTTP_USER_AGENT"],$log_version)) {
      $BROWSER_VER=$log_version[1];
      $BROWSER_AGENT='IE';
    } elseif (ereg( 'Opera ([0-9].[0-9]{1,2})',$_SERVER["HTTP_USER_AGENT"],$log_version)) {
      $BROWSER_VER=$log_version[1];
      $BROWSER_AGENT='OPERA';
    } elseif (ereg( 'Mozilla/([0-9].[0-9]{1,2})',$_SERVER["HTTP_USER_AGENT"],$log_version)) {
      $BROWSER_VER=$log_version[1];
      $BROWSER_AGENT='MOZILLA';
    } else {
      $BROWSER_VER=0;
      $BROWSER_AGENT='OTHER';
    }
    ob_start();
    header ('Expires: 0');
    header ('Last-Modified: '.gmdate ('D, d M Y H:i:s', time()) . ' GMT');
    header ('Pragma: public');
    header ('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header ('Accept-Ranges: bytes');
    header ('Content-Length: ' . strlen($sql_data));
    header ('Content-Type: Application/octet-stream');
    header ('Content-Disposition: attachment; filename="' . $filename . '"');
    header ('Connection: close');
    /*
     if ($BROWSER_AGENT == 'IE') {
     header('Content-Disposition: inline; filename="'.$filename.'";');
     header('Pragma: cache');
     header('Cache-Control: public, must-revalidate, max-age=0');
     header('Connection: close');
     header("Expires: ".gmdate("D, d M Y H:i:s", time()+60)." GMT");
     header("Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT");
     }else{
     $header="Content-Disposition: attachment; filename=".$filename.";";
     header($header );
     header("Content-Length: ".strlen($sql_data));
     }
     */
    echo($sql_data);
    ob_end_flush();
    die();
  }else{
    echo "Error! Not Good!";
    shRedirect('index.php?option=com_sh404sef', COM_SEF_FATAL_ERROR_HEADERS, '301', 'error');
  }
}

function import_custom($userfile){

  global $mainframe;
  $database =& JFactory::getDBO();
  $uploaddir = JPATH_ROOT.'/media/';
  $uploadfile = $uploaddir . basename($userfile['name']);
  if (move_uploaded_file($userfile['tmp_name'], $uploadfile)) {
    echo '<p class="message">'._COM_SEF_UPLOAD_OK.'</p>';
    $results = true;
    $lines = file($uploadfile);
    //		echo "<pre>";
    //		print_r($lines);
    //		echo "</pre>";
    foreach ($lines as $line){
      $line = trim($line);
      if( substr($line,0,40) == "INSERT INTO `".$mainframe->getCfg('dbprefix')."redirection` VALUES('',"){
        $database->setQuery( $line );
        if (! $database->query()){
          echo "<p class='error'>"._COM_SEF_ERROR_IMPORT."<pre>$line</pre></p>";
          $results = false;
        }
      }else{
        shRedirect('index.php?option=com_sh404sef',_COM_SEF_INVALID_SQL.substr($line,0,40), '301', 'error');
      }
    }
    unlink($uploadfile) OR shRedirect('index.php?option=com_sh404sef',_COM_SEF_NO_UNLINK, '301', 'error');
    if ($results) echo '<p class="message">'._COM_SEF_IMPORT_OK.'</p>';
    ?>
<form><input type="button" value="<?php echo _COM_SEF_PROCEED; ?>"
	onClick="javascript:location.href='index.php?option=com_sh404sef&task=view&viewmode=2'"></form>
    <?php
  }else{
    echo "<p class='error'>"._COM_SEF_WRITE_FAILED."</p>";
    $results = false;
  }
  return $result;
}

function export_custom_CSV($filename, $which = 0){ // which = 0:all, 1 = custom redirect, 2 = 404
  // which = 4  V 1.2.4.

  $database =& JFactory::getDBO();
  $csv_data = ($which == 4 )? backup_custom_CSV_meta() : backup_custom_CSV( $which);  // 1.2.4.t bug #166
  $csv_data = implode("\r", $csv_data);
  if (!headers_sent()) {
    while (ob_get_level() > 0) {
      ob_end_clean(); //flush the mambo stuff from the ouput buffer
    }
    // Determine Browser
    if (ereg( 'MSIE ([0-9].[0-9]{1,2})',$_SERVER["HTTP_USER_AGENT"],$log_version)) {
      $BROWSER_VER=$log_version[1];
      $BROWSER_AGENT='IE';
    } elseif (ereg( 'Opera ([0-9].[0-9]{1,2})',$_SERVER["HTTP_USER_AGENT"],$log_version)) {
      $BROWSER_VER=$log_version[1];
      $BROWSER_AGENT='OPERA';
    } elseif (ereg( 'Mozilla/([0-9].[0-9]{1,2})',$_SERVER["HTTP_USER_AGENT"],$log_version)) {
      $BROWSER_VER=$log_version[1];
      $BROWSER_AGENT='MOZILLA';
    } else {
      $BROWSER_VER=0;
      $BROWSER_AGENT='OTHER';
    }
    ob_start();
    header ('Expires: 0');
    header ('Last-Modified: '.gmdate ('D, d M Y H:i:s', time()) . ' GMT');
    header ('Pragma: public');
    header ('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header ('Accept-Ranges: bytes');
    header ('Content-Length: ' . strlen($csv_data));
    header ('Content-Type: Application/octet-stream');
    header ('Content-Disposition: attachment; filename="' . $filename . '"');
    header ('Connection: close');
    echo($csv_data);
    ob_end_flush();
    die();
  }else{
    echo "Error! Not Good!";
    shRedirect('index.php?option=com_sh404sef',_COM_SEF_FATAL_ERROR_HEADERS, '301', 'error');
  }
}

function import_custom_CSV($userfile, $ViewModeId=0) {

  $database =& JFactory::getDBO();
  $uploaddir = JPATH_ROOT.'/media/';
  $uploadfile = $uploaddir . basename($userfile['name']);
  if (move_uploaded_file($userfile['tmp_name'], $uploadfile)) {
    echo '<p class="message">'._COM_SEF_UPLOAD_OK.'</p>';
    $results = true;
    $lines = file($uploadfile);
    array_shift($lines);  // remove header line
    foreach ($lines as $line){
      $line = trim($line);
      $line = trim($line, '"');
      $lineBits = explode('","', $line);
      // V 1.2.4.s : previous version handling
      switch (count($lineBits)) {
        case 6 :
          $q = 'INSERT INTO `#__redirection` VALUES(\'\',"'.$lineBits[1].'", "'.$lineBits[2]
          .'", "'.$lineBits[3].'", "'.$lineBits[4].'", "'.$lineBits[5].'")';
          break;
        case 5 : // prior to version 1.2.4.q, no rank field : bug fixed in V t
          $q = 'INSERT INTO `#__redirection` VALUES(\'\',"'.$lineBits[1].'", \'10\', "'.$lineBits[2].'", "'
          .$lineBits[3].'", "'.$lineBits[4].'" )';
          break;
      }

      $database->setQuery( $q );

      if (! $database->query()){
        echo $database->stderr();
        echo "<p class='error'>"._COM_SEF_ERROR_IMPORT."<pre>$line</pre></p>";
        $results = false;
      }
    }
    unlink($uploadfile) OR shRedirect('index.php?option=com_sh404sef',_COM_SEF_NO_UNLINK, '301', 'error');
    if ($results) echo '<p class="message">'._COM_SEF_IMPORT_OK.'</p>';
    ?>
<form><input type="button" value="<?php echo _COM_SEF_PROCEED; ?>"
	onClick="javascript:location.href='index.php?option=com_sh404sef&task=view&viewmode=<?php echo $ViewModeId;?>'"></form>
    <?php
  }else{
    echo "<p class='error'>"._COM_SEF_WRITE_FAILED."</p>";
    $results = false;
  }
  return $results;
}


function import_custom_CSV_OPEN_SEF($userfile) {  // V 1.2.4.t

  $database =& JFactory::getDBO();
  $uploaddir = JPATH_ROOT.'/media/';
  $uploadfile = $uploaddir . basename($userfile['name']);
  if (move_uploaded_file($userfile['tmp_name'], $uploadfile)) {
    echo '<p class="message">'._COM_SEF_UPLOAD_OK.'</p>';
    $results = true;
    $fileContent = file($uploadfile);
    for ($i=1; $i<6;$i++) // remove header
    array_shift($fileContent);
    $lines = explode('" "', $fileContent[0]);  // only way I could find to split lines
    $shCount = 0;
    foreach ($lines as $line){
      $line = trim($line);
      $lineBits = explode(';', $line);
      $sefUrl = ltrim(trim($lineBits[2],'"'), '/');
      $nonSef = trim($lineBits[3], '"');
      if ($sefUrl != 'NULL' && $nonSsef != 'NULL') {  // don't import records without SEF or non-SEF URL
        $dateAdd = date('Y-m-d');
        if (!preg_match( '/(&|\?)lang=[a-zA-Z]{2,3}/iU', $nonSef)) {  // no lang string, let's add default
          $shTemp = explode( '_', $GLOBALS['mosConfig_locale']);
          $shLangTemp = $shTemp[0] ? $shTemp[0] : 'en';
          $nonSef .= '&lang='.$shLangTemp;
        }
        $nonSef = shSortUrl($nonSef);  // and sort URl so that we always find it
        // Link priority works opposed to rank. The highest LP wins, whereas the lowest ranks win
        // not so simple though, the OSEF system may have several links with same LP, whereas I don't want to have that
        // So let's do rank = 1000-LP. If there is one link with LP =99, it will be used, as it will have lowest rank;
        // if there are several, the first one will be used (just as in OpenSEF), but user is still able
        // to select the URL he wants touse, as doing so in sh404SEF cPanel will set rank=0
        $q = 'INSERT INTO `#__redirection` VALUES(\'\',"0", "'.(1000-(int)$lineBits[17])
        .'", "'.$sefUrl.'", "'.$nonSef.'", "'.$dateAdd.'")';



        $database->setQuery( $q );
        if (! $database->query()){
          echo $database->stderr();
          echo "<p class='error'>"._COM_SEF_ERROR_IMPORT."<pre>$line</pre></p>";
          $results = false;
        }

        $shCount++;
      }
    }
    unlink($uploadfile) OR shRedirect('index.php?option=com_sh404sef',_COM_SEF_NO_UNLINK, '301', 'error');
    if ($results) echo '<p class="message">'._COM_SEF_IMPORT_OK.' ('.$shCount.')</p>';
    ?>
<form><input type="button" value="<?php echo _COM_SEF_PROCEED; ?>"
	onClick="javascript:location.href='index.php?option=com_sh404sef&task=view&viewmode=2'"></form>
    <?php
  }else{
    echo "<p class='error'>"._COM_SEF_WRITE_FAILED."</p>";
    $results = false;
  }
  return $results;
}

function import_custom_CSV_meta($userfile) {  // V 1.2.4.s

  $database =& JFactory::getDBO();
  $uploaddir = JPATH_ROOT.'/media/';
  $uploadfile = $uploaddir . basename($userfile['name']);
  if (move_uploaded_file($userfile['tmp_name'], $uploadfile)) {
    echo '<p class="message">'._COM_SEF_UPLOAD_OK.'</p>';
    $results = true;
    $lines = file($uploadfile);
    array_shift($lines);  // remove header line
    foreach ($lines as $line){
      $line = trim($line);
      $line = trim($line, '"');
      $lineBits = explode('","', $line);
      //var_dump($lineBits);
      $q = 'INSERT INTO `#__sh404SEF_meta` VALUES(\'\',"'.shUnEmpty($lineBits[1]).'", "'.shUnEmpty($lineBits[2]).'", "'.shUnEmpty($lineBits[3]).'", "'.shUnEmpty($lineBits[4]).'", "'.shUnEmpty($lineBits[5]).'", "'.shUnEmpty($lineBits[6]).'")';
      $database->setQuery( $q );
      if (! $database->query()){
        echo "<p class='error'>"._COM_SEF_ERROR_IMPORT."<pre>$line</pre></p>";
        $results = false;
      }
    }
    unlink($uploadfile) OR shRedirect('index.php?option=com_sh404sef',_COM_SEF_NO_UNLINK, '301', 'error');
    if ($results) echo '<p class="message">'._COM_SEF_IMPORT_META_OK.'</p>';
    ?>
<form><input type="button" value="<?php echo _COM_SEF_PROCEED; ?>"
	onClick="javascript:location.href='index.php?option=com_sh404sef&task=viewMeta'"></form>
    <?php
  }else{
    echo "<p class='error'>"._COM_SEF_WRITE_FAILED."</p>";
    $results = false;
  }
  return $results;
}

function backup_custom_CSV_meta(){ // 1.2.4.s

  $database =& JFactory::getDBO();
  $CSV = array();
  $CSV[] = "\"id\",\"newurl\",\"metadesc\",\"metakey\",\"metatitle\",\"metalang\",\"metarobots\" \n"; // V 1.2.4.s
  $query ='SELECT * FROM #__sh404SEF_meta WHERE 1';
  $database->setQuery( $query );
  $rows = $database->loadRowList();
  if (!empty($rows)) {
    foreach ($rows as $row) {
      $CSV[] = "\"".shNonEmpty($row[0])."\",\"".shNonEmpty($row[1])."\",\"".shNonEmpty($row[2])."\",\"".shNonEmpty($row[3])."\",\"".shNonEmpty($row[4])."\",\"".shNonEmpty($row[5])."\",\"".shNonEmpty($row[6])."\" \n";  // V 1.2.4.s
    }
  }else{
    shRedirect('index.php?option=com_sh404sef',_COM_SEF_NOACCESS);
  }
  return $CSV;
}

function shResetSecStats() {
  $sefConfig = & shRouter::shGetConfig();

  $sefConfig->shSecCurMonth = '';
  $sefConfig->shSecLastUpdated = '';
  $sefConfig->shSecTotalAttacks = 0;
  $sefConfig->shSecTotalConfigVars = 0;
  $sefConfig->shSecTotalBase64 = 0;
  $sefConfig->shSecTotalScripts = 0;
  $sefConfig->shSecTotalStandardVars = 0;
  $sefConfig->shSecTotalImgTxtCmd = 0;
  $sefConfig->shSecTotalIPDenied = 0;
  $sefConfig->shSecTotalUserAgentDenied = 0;
  $sefConfig->shSecTotalFlooding = 0;
  $sefConfig->shSecTotalPHP = 0;
  $sefConfig->shSecTotalPHPUserClicked = 0;
}

function shDecodeSecLogLine( $line) {
  $sefConfig = & shRouter::shGetConfig();

  if (preg_match( '/[0-9]{2}\-[0-9]{2}\-[0-9]{2}/', $line)) { // this is not header or comment line
    $sefConfig->shSecTotalAttacks++;
    $bits = explode("\t", $line);
    switch (substr($bits[2],0, 15)) {
      case 'Flooding':
      		$sefConfig->shSecTotalFlooding++;
      		break;
      case 'Caught by Honey':
      		$sefConfig->shSecTotalPHP++;
        break;
      case 'Honey Pot but u':
        $sefConfig->shSecTotalPHPUserClicked++;
        break;
      case 'Var not numeric':
      case 'Var not alpha-n':
      case 'Var contains ou':
      		$sefConfig->shSecTotalStandardVars++;
      		break;
      case 'Image file name':
        $sefConfig->shSecTotalImgTxtCmd++;
        break;
      case '<script> tag in':
        $sefConfig->shSecTotalScripts++;
        break;
      case 'Base 64 encoded':
        $sefConfig->shSecTotalBase64++;
        break;
      case 'mosConfig_var i':
        $sefConfig->shSecTotalConfigVars++;
        break;
      case 'Blacklisted IP':
        $sefConfig->shSecTotalIPDenied++;
        break;
      case 'Blacklisted use':
        $sefConfig->shSecTotalUserAgentDenied++;
        break;
      default:  // if not one of those, then it's a 404, don't count it as an attack
        $sefConfig->shSecTotalAttacks--;
        break;

    }
  }
}

function shReadSecStatsFromFile( $shFileName) {
  $logFile=fopen( $shFileName,'r');
  if ($logFile) {
    while (!feof($logFile)) {
      $line = fgets($logFile, 4096);
      shDecodeSecLogLine( $line);
    }
    fClose( $logFile);
  }
}

function updateSecStats() {
  $sefConfig = & shRouter::shGetConfig();
  $shNum = 12*(intval(date('Y')) - 2000)+intval(date('m'));
  $shFileName = sh404SEF_ADMIN_ABS_PATH.'logs/'.date('Y').'-'.date('m').'-'.'sh404SEF_security_log.'.$shNum.'.txt';
  $fileIsThere = file_exists($shFileName) && is_readable($shFileName);
  shResetSecStats();
  shReadSecStatsFromFile($shFileName);
  $sefConfig->shSecCurMonth = date('M').'-'.date('Y');
  $sefConfig->shSecLastUpdated = time();
  saveConfig(false);  // write new stats to disk, don't erase URL cache
}

?>
