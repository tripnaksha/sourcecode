<?php
/**
 * sh404SEF support for com_smf component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * Based on sef_ext.php file, Copyright (C) 2007 Matt Zuba
 * shumisha@gmail.com
 * @version     $Id: com_smf.php 866 2009-01-17 14:05:21Z silianacom-svn $
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
$sefConfig = & shRouter::shGetConfig();
// get DB
$database =& JFactory::getDBO();
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
// ------------------  standard plugin initialize function - don't change ---------------------------
// ------------------  load language file - adjust as needed ----------------------------------------
$shLangIso = shLoadPluginLanguage( 'com_smf', $shLangIso, '_SH404SEF_SMF_USER');
// ------------------  load language file - adjust as needed ----------------------------------------

$action = isset($action) ? $action : null;
$board = isset($board) ? $board : null;
$topic = isset($topic) ? $topic : null;

shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');
if (!empty($Itemid))  
  shRemoveFromGETVarsList('Itemid');

// a few params
if (!defined('sh404SEF_SMF_PARAMS_SIMPLE_URLS')) {
	define ('sh404SEF_SMF_PARAMS_SIMPLE_URLS', 0);
	define ('sh404SEF_SMF_PARAMS_TABLE_PREFIX', 'smf_');
	define ('sh404SEF_SMF_PARAMS_ENABLE_STICKY', 0);
}

if (!function_exists('shGetSMFBoardName')) {
function shGetSMFBoardName($board, $shLangIso, $shLangName, $option) {
	$sefConfig = & shRouter::shGetConfig();

	// get DB
    $database =& JFactory::getDBO();
	$title = '';
	if (!empty($board)) {
		if(sh404SEF_SMF_PARAMS_SIMPLE_URLS) {
			$title .= ' board ' . $sefConfig->replacement. $board;
		} else {
			if(strpos($board,'.') !== false ) {
				$page = substr($board, strpos($board,'.')+1);
				$board = substr($board, 0, strpos($board,'.'));
			}
			$query = 'SELECT name FROM '.sh404SEF_SMF_PARAMS_TABLE_PREFIX.'boards where ID_BOARD = '.$board.';';
			$database->setQuery( $query );
	  		if (!shTranslateUrl($option, $shLangName))
	    		$result = $database->loadObject( false);
		  	else   
		    	$result = $database->loadObject();
			if (!empty($result)) {
				if(isset($page) && ($page > 0)) {
					$page = ($page / $sefConfig->shSMFItemsPerPage) + 1;
				}	
				$title .= ($sefConfig->shInsertSMFBoardId ? $board.' ' : '').$result->name. (empty($page)? '':' '.$page);			
			}
		}
	}
	return $title;
}
}

if (!function_exists('shGetSMFTopicName')) {
function shGetSMFTopicName($topic, $shLangIso, $shLangName, $option){
	$sefConfig = & shRouter::shGetConfig();
	
	// get DB
    $database =& JFactory::getDBO();
	$title = array();
	if (!empty($topic)) {
    if (strpos( $topic, 'cur_topic_id') !== false )
      return '';
	if(sh404SEF_SMF_PARAMS_SIMPLE_URLS) {
		$title[] = ' topic ' . $sefConfig->replacement. $topic;
	} else { 
		// Split up the topic id and the starting value
		@list ($value, $start) = explode('.', $topic);
		if (strpos($start, 'msg') !== false)  
			return '';
			if(!isset($start))
				$start = '0';
			if(!is_numeric($value)) {
				$title[] = $value . '.' . $start;
			} else {
				$query = '	SELECT mf.subject, b.name
						  	FROM ('.sh404SEF_SMF_PARAMS_TABLE_PREFIX.'topics AS t, '
								.sh404SEF_SMF_PARAMS_TABLE_PREFIX.'messages AS mf, '
								.sh404SEF_SMF_PARAMS_TABLE_PREFIX.'boards AS b)
							WHERE t.ID_TOPIC = '.$value.
							' AND mf.ID_MSG = t.ID_FIRST_MSG
							AND b.ID_BOARD = t.ID_BOARD';
				$database->setQuery( $query );
	  			if (!shTranslateUrl($option, $shLangName))
		    		$result = $database->loadObject( false);
		  		else   
	    			$result = $database->loadObject($result);
				if (!empty($result)) {
					$title[] = $result->name;
					$title[] = ($sefConfig->shInsertSMFTopicId ? $value.' ' : '').$result->subject;
				}        
			}	        
		}		        
	}
	return $title;
}
}

if (!function_exists('shGetSMFUserName')) {
function shGetSMFUserName($u, $shLangIso, $shLangName, $option) {
	global $sh_LANG;
	$sefConfig = & shRouter::shGetConfig();
	// get DB
	$database =& JFactory::getDBO();
	$title = '';
	if(sh404SEF_SMF_PARAMS_SIMPLE_URLS) {
		$title = $sh_LANG[$shLangIso]['_SH404SEF_SMF_USER'] . $sefConfig->replacement . $u;
	} else {
		if(is_numeric($u))	{
			$query = 'SELECT memberName FROM '.sh404SEF_SMF_PARAMS_TABLE_PREFIX.'members where ID_MEMBER = '.$u.';';
			$database->setQuery( $query );
	  		if (!shTranslateUrl($option, $shLangName))
	    		$result = $database->loadObject(false);
	  		else   
	    		$result = $database->loadObject();
			if(!empty($result)) {
				$title = ($sefConfig->shInsertSMFUserId ? $u.$sefConfig->replacement : '').$result->memberName;
			}		
		}			
	}
	return $title;				
}
}

if (!function_exists('shGetSMFPagination')) {
function shGetSMFPagination($itemId, $shLangName) {
	$sefConfig = & shRouter::shGetConfig();

	$pageStr = '';
	if (empty($sefConfig->shSMFItemsPerPage)) return '';
	if (strpos($itemId, 'msg') !== false) return '';
	if (strpos($itemId, '.') === false) return '';
	@list ($id, $start) = explode('.', $itemId);
	if (empty($start)) return '';
	$pageNum = intval($start/$sefConfig->shSMFItemsPerPage);
	$pageNum++;
	if (!empty($sefConfig->pageTexts[$shLangName]) 
  	&& (false !== strpos($sefConfig->pageTexts[$shLangName], '%s'))){
  		$pageStr = str_replace('%s', $pageNum, $sefConfig->pageTexts[$shLangName]);
  } else {
  	$pageStr = $sefConfig->pagerep.$pageNum;
  } 
	return $pageStr;				
}
}

// shumisha : insert magazine name from menu
$shSMFName = shGetComponentPrefix($option);
$shSMFName = empty($shSMFName) ?  getMenuTitle($option, null, $Itemid, null, $shLangName ) : $shSMFName;
$shSMFName = (empty($shSMFName) || $shSMFName == '/') ? 'SMF-Forum':$shSMFName; // V 1.2.4.t 

// breakdown the $action param into subparams (separated by ;)
if (!empty($action)) {
	$tmp = explode(';', $action);
 	if (count($tmp) > 1) {
		$mainAction = $tmp[0];
		$tmp = str_replace(";","&",str_replace(",","=",$action));
		parse_str($tmp);  // extract other variables
	} else $mainAction = $action;
} else $mainAction = '';

switch ($mainAction) {

	case '':  // no action
		  $boardName = shGetSMFBoardName($board, $shLangIso, $shLangName, $option);
		  $topicName = shGetSMFTopicName($topic, $shLangIso, $shLangName, $option);
		  if (!empty($topicName)) {
		    if ($sefConfig->shInsertSMFName) $title[] = $shSMFName;
        $title = array_merge($title, $topicName);
        $pagination = shGetSMFPagination($topic, $shLangName);
        if (!empty($pagination))
		      $title[] = $pagination;
		    else $title[] = '/';
        if (strpos($topic, 'msg') === false)  // only remove topic var if no msgXX id
			     shRemoveFromGETVarsList('topic');
			  shRemoveFromGETVarsList('board');
		  } elseif (!empty($boardName)) {
		      if ($sefConfig->shInsertSMFName) $title[] = $shSMFName;
		      $title[] = $boardName;
		      $pagination = shGetSMFPagination($board, $shLangName);
		      if (!empty($pagination))
		        $title[] = $pagination;
		      else $title[] = '/';
		      shRemoveFromGETVarsList('board');
      }
		  if (empty($title)) {
			 $title[] = $shSMFName;
			 $title[] = '/';
		  }
	break;	

  case 'notify':  // on || off
  case 'notifyboard': // on || off
  case 'markasread': // topic || board
    if ($sefConfig->shInsertSMFName) $title[] = $shSMFName;
		$actionTitleIndex = '_SH404SEF_SMF_ACTION_'.strtolower($mainAction);
		if (!empty($sa)) {
			$sActionTitleIndex = $actionTitleIndex.'_'.strtolower($sa);
			$tmp = empty($sh_LANG[$shLangIso][$sActionTitleIndex]) ? $mainAction.$sefConfig->replacement.$sa : $sh_LANG[$shLangIso][$sActionTitleIndex];
			$title[] = $tmp;
			shRemoveFromGETVarsList( 'sa');
		} else {
		  $dosef = false;
    }
  break;

	case 'login': 
	case 'help':
	case 'activate': 
	case 'admin': 
	case 'stats': 
	case 'unread': 
	case 'unreadreplies': 
	case 'logout': 
	case 'collapse': 
	case 'recent': 
	case 'who': 
	case 'post': 
	case 'sendtopic': 
	case 'printpage': 
	case 'reporttm': 
	case 'helpadmin':
	case 'mlist':
	case 'quote':
	case 'deletemsg':
	case 'splittopics':
	case 'removetopic2':
	case 'lock':
	case 'sticky':
	case 'mergetopics':
		if ($sefConfig->shInsertSMFName) $title[] = $shSMFName;
		$actionTitleIndex = '_SH404SEF_SMF_ACTION_'.strtolower($mainAction);
		$title[] = $sh_LANG[$shLangIso][$actionTitleIndex];
		if (!empty($sa)) {
			$sactionTitleIndex = '_SH404SEF_SMF_SACTION_'.strtolower($sa);
			$tmp = empty($sh_LANG[$shLangIso][$sactionTitleIndex]) ? $sa : $sh_LANG[$shLangIso][$sactionTitleIndex];
			$title[] = $tmp;
			shRemoveFromGETVarsList( 'sa');
		}
	break;

	case 'search': // $main action is going tho be the same for search and search;advanced
		if ($sefConfig->shInsertSMFName) $title[] = $shSMFName;
		$actionTitleIndex = '_SH404SEF_SMF_ACTION_'.($action == $mainAction ? 'search':'advanced_search');
		$title[] = $sh_LANG[$shLangIso][$actionTitleIndex];
	break;
	case 'search2':
	 if ($sefConfig->shInsertSMFName) $title[] = $shSMFName;
	 $title[] = $sh_LANG[$shLangIso]['_SH404SEF_SMF_ACTION_search2'];
	break;
	
	case 'profile':
	case 'pm':
		if ($sefConfig->shInsertSMFName) $title[] = $shSMFName;
		$actionTitleIndex = '_SH404SEF_SMF_ACTION_'.strtolower($mainAction);
		$title[] = $sh_LANG[$shLangIso][$actionTitleIndex];
		$userName = $sefConfig->shinsertSMFUserName ? shGetSMFUserName( $u) : '';
		if (!empty($userName))
			$title[] = $userName;
	break;
	
	default:  // there is an action, but we don't know it : do nothing
		$dosef = false;
	break;
}

if (!empty($action) && $action == $mainAction)	// if no other command embedded in $action var, we can remove it
  shRemoveFromGETVarsList( 'action');

// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}
// ------------------  standard plugin finalize function - don't change ---------------------------
 
?>
