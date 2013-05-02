<?php
/**
 * sh404SEF prototype support for Community Builder component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_comprofiler.php 866 2009-01-17 14:05:21Z silianacom-svn $
 * {shSourceVersionTag: Version x - 2007-09-20}
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
$sefConfig = & shRouter::shGetConfig(); 
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
// ------------------  standard plugin initialize function - don't change ---------------------------

// ------------------  load language file - adjust as needed ----------------------------------------
$shLangIso = shLoadPluginLanguage( 'com_comprofiler', $shLangIso, '_SH404SEF_CB_VIEW_USER_DETAILS');
// ------------------  load language file - adjust as needed ----------------------------------------

shRemoveFromGETVarsList('option');
if (!empty($lang))
  shRemoveFromGETVarsList('lang');
if (!empty($limit))
  shRemoveFromGETVarsList('limit');  
if (isset($limitstart))
  shRemoveFromGETVarsList('limitstart'); 
  
$task = isset($task) ? @$task : null;
$Itemid = isset($Itemid) ? @$Itemid : null;   
 
// insert comp name from user input in backend
$shCBName = shGetComponentPrefix($option);
$shCBName = empty($shCBName) ?  getMenuTitle($option, $task, $Itemid, null, $shLangName ) : $shCBName;
$shCBName = (empty($shCBName) || $shCBName == '/') ? 'CB':$shCBName; // V 1.2.4.t 

// do something about that Itemid thing  V 1.2.4.m
if (eregi('Itemid=[0-9]+', $string) === false) { // if no Itemid in non-sef URL
  if ($sefConfig->shInsertGlobalItemidIfNone && !empty($shCurrentItemid)) {
    $string .= '&Itemid='.$shCurrentItemid;  // append current Itemid
    $Itemid = $shCurrentItemid; 
    shAddToGETVarsList('Itemid', $Itemid);
  }  
  if ($sefConfig->shInsertTitleIfNoItemid) {
  	$title[] = $shCBName;
  	// prevent from adding another time
  	$sefConfig->shInsertCBName = false;
  }	
  $shItemidString = '';
  if ($sefConfig->shAlwaysInsertItemid && (!empty($Itemid) || !empty($shCurrentItemid)))
    $shItemidString = _COM_SEF_SH_ALWAYS_INSERT_ITEMID_PREFIX.$sefConfig->replacement
        .(empty($Itemid)? $shCurrentItemid :$Itemid);
} else {  // if Itemid in non-sef URL
  $shItemidString = $sefConfig->shAlwaysInsertItemid ? 
    _COM_SEF_SH_ALWAYS_INSERT_ITEMID_PREFIX.$sefConfig->replacement.$Itemid
    : '';
  if ($sefConfig->shAlwaysInsertMenuTitle){
    //global $Itemid; V 1.2.4.g we want the string option, not current page !
    $title[] = $shCBName;
    // prevent from adding another time
  	$sefConfig->shInsertCBName = false;  
  }  
} 

if (!empty($Itemid))
  shRemoveFromGETVarsList('Itemid');  

$task = isset($task) ? @$task : null;

switch (strtolower($task))
{
    case 'userdetails':
      if ($sefConfig->shInsertCBName) $title[] = $shCBName;
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_VIEW_USER_DETAILS'];
      // add user name to URL if requested to do so. User id is in $user
      if (!empty($user) && $sefConfig->shCBInsertUserName) {
        $query  = "SELECT ".($sefConfig->shCBUseUserPseudo?'user':'')."name FROM #__users" ;
		    $query .= "\n WHERE id=".$user;
		    $database->setQuery( $query );
		    if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
		      $result = $database->loadResult(false);
        else $result = $database->loadResult();
		    $title[] = empty($result)?  // no name available
          $sh_LANG[$shLangIso]['_SH404SEF_CB_USER'].$sefConfig->replacement.$user // put ID
          : ($sefConfig->shCBInsertUserId ? $user.$sefConfig->replacement.$result : $result); // if name, put ID only if requested
        shRemoveFromGETVarsList('user');  
      }
      shRemoveFromGETVarsList('task');
    break;
    case 'userslist':
      if ($sefConfig->shInsertCBName) $title[] = $shCBName;
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_VIEW_USERS_LIST'];
      // manage listid
      if (!empty($listid)) {
        $query  = "SELECT listid, title FROM #__comprofiler_lists" ;
		    $query .= "\n WHERE listid=".$listid;
		    $database->setQuery( $query );
		    if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
		      $result = $database->loadObject(false);
        else $result = $database->loadObject();
		    $title[] = empty($result)?  // no name available
          $sh_LANG[$shLangIso]['_SH404SEF_CB_LIST'].$sefConfig->replacement.$listid // put ID
          : $result->title; // if name, put ID only if requested
        shRemoveFromGETVarsList('listid');  
      }
      shRemoveFromGETVarsList('task');
    break;
    case 'reportuser':
      if ($sefConfig->shInsertCBName) $title[] = $shCBName;
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_REPORT_USER'];
      // add user name if set to do so / user id is in $uid
      if ($sefConfig->shCBInsertUserName) {
        $query  = "SELECT ".($sefConfig->shCBUseUserPseudo?'user':'')."name FROM #__users" ;
		    $query .= "\n WHERE id=".$uid;
		    $database->setQuery( $query );
		    if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
		      $result = $database->loadResult( false);
		    else  $result = $database->loadResult();
		    $title[] = empty($result)?  // no name available
          $sh_LANG[$shLangIso]['_SH404SEF_CB_USER'].$sefConfig->replacement.$uid // put ID
          : ($sefConfig->shCBInsertUserId ? $uid.$sefConfig->replacement.$result : $result); // if name, put ID only if requested
        shRemoveFromGETVarsList('uid');  
      }
      shRemoveFromGETVarsList('task');
    break;
    case 'banprofile' :
      if ($sefConfig->shInsertCBName) $title[] = $shCBName;
      switch ($act) {
        case 0:
          $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_USER_UNBAN'];
          shRemoveFromGETVarsList('act');
          shRemoveFromGETVarsList('task');
        break;
        case 1:
          $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_USER_BAN'];
          shRemoveFromGETVarsList('act');
          shRemoveFromGETVarsList('task');
        break;
        case 2:
          $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_USER_BAN_REQUEST'];
          shRemoveFromGETVarsList('act');
          shRemoveFromGETVarsList('task');
        break;
      }
      // add user name if set to do so / user id is in $uid
      if ($sefConfig->shCBInsertUserName) {
        $query  = "SELECT ".($sefConfig->shCBUseUserPseudo?'user':'')."name FROM #__users" ;
		    $query .= "\n WHERE id=".$uid;
		    $database->setQuery( $query );
		    if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
		      $result = $database->loadResult( false);
		    else  $result = $database->loadResult();
		    $title[] = empty($result)?  // no name available
          $sh_LANG[$shLangIso]['_SH404SEF_CB_USER'].$sefConfig->replacement.$uid // put ID
          : ($sefConfig->shCBInsertUserId ? $uid.$sefConfig->replacement.$result : $result); // if name, put ID only if requested
        shRemoveFromGETVarsList('uid');  
      }
    break;
    case 'confirm':
      $dosef = false;
    break;
    case 'logout':
      if ($sefConfig->shInsertCBName) $title[] = $shCBName;
      if (!empty($sh_LANG[$shLangIso]['_SH404SEF_CB_REGISTRATION'])) 
        $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_REGISTRATION'];
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_LOGOUT'];
      shRemoveFromGETVarsList('task');
    break;
    case 'userprofile':
      if ($sefConfig->shCBShortUserURL) {
        $query  = "SELECT ".($sefConfig->shCBUseUserPseudo?'user':'')."name FROM #__users" ;
  		  $query .= "\n WHERE id=".$user;
  		  $database->setQuery( $query );
  		  if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
  		    $result = $database->loadResult( false);
        else $result = $database->loadResult();
  		  $title[] = empty($result)?  // no name available
            $sh_LANG[$shLangIso]['_SH404SEF_CB_USER'].$sefConfig->replacement.$user: $result;
        $title[] = '/';     
        shRemoveFromGETVarsList('user'); 
      } else {
        if ($sefConfig->shInsertCBName) $title[] = $shCBName;
        $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_VIEW_USER_PROFILE'];
        // add user name to URL if requested to do so. User id is in $user
        if ($sefConfig->shCBInsertUserName && !empty($user)) {  // V 1.2.4.r
          $query  = "SELECT ".($sefConfig->shCBUseUserPseudo?'user':'')."name FROM #__users" ;
  		    $query .= "\n WHERE id=".$user;
  		    $database->setQuery( $query );
  		    if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
  		      $result = $database->loadResult( false);
          else $result = $database->loadResult();
  		    $title[] = empty($result)?  // no name available
            $sh_LANG[$shLangIso]['_SH404SEF_CB_USER'].$sefConfig->replacement.$user // put ID
            : ($sefConfig->shCBInsertUserId ? $user.$sefConfig->replacement.$result : $result); // if name, put ID only if requested
          shRemoveFromGETVarsList('user');  
        }
      }
      shRemoveFromGETVarsList('task');
    break;
    case 'manageconnections':
      $dosef = false;
    break;
    case 'login':
      if ($sefConfig->shInsertCBName) $title[] = $shCBName;
      if (!empty($sh_LANG[$shLangIso]['_SH404SEF_CB_REGISTRATION'])) 
        $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_REGISTRATION'];
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_LOGIN']; 
      shRemoveFromGETVarsList('task'); 
    break;
    case 'lostpassword':
      if ($sefConfig->shInsertCBName) $title[] = $shCBName;
      // optional first part of URL, to be set in language file
      if (!empty($sh_LANG[$shLangIso]['_SH404SEF_CB_REGISTRATION'])) 
        $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_REGISTRATION'];
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_LOST_PASSWORD'];  
      shRemoveFromGETVarsList('task');
    break;
    case 'registers':
      if ($sefConfig->shInsertCBName) $title[] = $shCBName;
      // optional first part of URL, to be set in language file
      if (!empty($sh_LANG[$shLangIso]['_SH404SEF_CB_REGISTRATION'])) 
        $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_REGISTRATION'];
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_REGISTER'];
      shRemoveFromGETVarsList('task'); 
    break;
    case 'moderatebans':
      $dosef = false;
    break;
    case 'moderatereports':
      $dosef = false;
    break;
    case 'moderateimages':
      $dosef = false;
    break;
    case 'pendingapprovaluser':
      $dosef = false;
    break;
    case 'useravatar':
      $do = isset($do) ? @$do : null;
      switch (strtolower($do)) {
        case 'deleteavatar':
          if ($sefConfig->shInsertCBName) $title[] = $shCBName;
          $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_DELETE_AVATAR'];
          shRemoveFromGETVarsList('do');
          shRemoveFromGETVarsList('task');
        break;
        default:
          if ($sefConfig->shInsertCBName) $title[] = $shCBName;
          $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_MANAGE_AVATAR'];
          shRemoveFromGETVarsList('task');
        break;
      }
    break;
    case 'emailuser':
      if ($sefConfig->shInsertCBName) $title[] = $shCBName;
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_EMAIL_USER'];
      // add user name if set to do so / user id is in $uid
      if ($sefConfig->shCBInsertUserName) {
        $query  = "SELECT ".($sefConfig->shCBUseUserPseudo?'user':'')."name FROM #__users" ;
		    $query .= "\n WHERE id=".$uid;
		    $database->setQuery( $query );
		    if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
		      $result = $database->loadResult( false);
		    else  $result = $database->loadResult();
		    $title[] = empty($result)?  // no name available
          $sh_LANG[$shLangIso]['_SH404SEF_CB_USER'].$sefConfig->replacement.$uid // put ID
          : ($sefConfig->shCBInsertUserId ? $uid.$sefConfig->replacement.$result : $result); // if name, put ID only if requested
        shRemoveFromGETVarsList('uid');  
      }
      shRemoveFromGETVarsList('task');
    break;
    case 'teamcredits':
      if ($sefConfig->shInsertCBName) $title[] = $shCBName;
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_TEAM_CREDITS'];
      shRemoveFromGETVarsList('task');
    break;
    case '':
      if ($sefConfig->shInsertCBName) $title[] = $shCBName; // V 1.2.4.t
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_CB_MAIN_PAGE'];
      shRemoveFromGETVarsList('task');
    break;
    default:
      $dosef = false;
    break;
}

// V 1.2.4.s : fix for CB not passing $limit value in some URL : breaks pagination
if (isset($limitstart) && empty($limit)) {
  if (empty($ueConfig)) {
    $sh_CB_joomla_adminpath = sh404SEF_ABS_PATH. 'administrator';
    $sh_CB_adminpath = $sh_CB_joomla_adminpath. '/components/com_comprofiler';
    include($sh_CB_adminpath."/ue_config.php" );
  }
  $limit = $ueConfig['num_per_page'];
  shAddToGETVarsList('limit', $limit);
  shRemoveFromGETVarsList('limit');
}

// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------

?>
