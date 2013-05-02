<?php
/**
 * sh404SEF support for com_user component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_user.php 866 2009-01-17 14:05:21Z silianacom-svn $
 * {shSourceVersionTag: Version x - 2007-09-20}
 * 
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
$shLangIso = shLoadPluginLanguage( 'com_user', $shLangIso, '_COM_SEF_SH_VIEW_DETAILS');
// ------------------  load language file - adjust as needed ----------------------------------------

// remove common URL from GET vars list, so that they don't show up as query string in the URL
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');
if (!empty($Itemid))
  shRemoveFromGETVarsList('Itemid');
if (!empty($limit))  
shRemoveFromGETVarsList('limit');
if (isset($limitstart)) 
  shRemoveFromGETVarsList('limitstart'); // limitstart can be zero

$view = isset($view) ? @$view : null;   // make sure $view is defined
$task = isset($task) ? @$task : null;

// optional first part of URL, to be set in language file
if (!empty($sh_LANG[$shLangIso]['_COM_SEF_SH_REGISTRATION'])) 
  $title[] = $sh_LANG[$shLangIso]['_COM_SEF_SH_REGISTRATION'];

$noTask = false;
switch ($task) {
  case 'register':
  	$title[] =  $sh_LANG[$shLangIso]['_COM_SEF_SH_REGISTER'];
  	shRemoveFromGETVarsList('task');
  break;
  case 'activate':
  	$title[] =  $sh_LANG[$shLangIso]['_COM_SEF_SH_ACTIVATE'];
  	shRemoveFromGETVarsList('task');
  break;
  case 'logout':
  	$title[] =  $sh_LANG[$shLangIso]['_COM_SEF_SH_LOGOUT'];
  	shRemoveFromGETVarsList('task');
  break;
  default:
  	$noTask = true;
  break;
}

switch ($view) {
	case 'user' :
	  $title[] = $sh_LANG[$shLangIso]['_COM_SEF_SH_VIEW_DETAILS'];
	  shRemoveFromGETVarsList('view');
	break;
    case 'reset':
    	$title[] = $sh_LANG[$shLangIso]['_COM_SEF_SH_LOST_PASSWORD'];
    	shRemoveFromGETVarsList('view');
    break;
    case 'remind':
    	$title[] = $sh_LANG[$shLangIso]['_COM_SEF_SH_REMIND_USER_NAME'];
    	shRemoveFromGETVarsList('view');
    break;
    case 'login' :
    	$title[] = $sh_LANG[$shLangIso]['_COM_SEF_SH_LOGIN'];
    	shRemoveFromGETVarsList('view');
    break;
	default:
		if ($noTask) $dosef = false;  
	break;  
}

if (!empty($title))
  if (!empty($sefConfig->suffix)) {
	  $title[count($title)-1] .= $sefConfig->suffix;
  }
  else {
	  $title[] = '/';
  }
  
// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------
  
?>
