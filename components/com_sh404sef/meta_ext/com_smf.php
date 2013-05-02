<?php
/**
 * shCustomTags support for com_smf component.
 * Yannick Gaultier, shumisha
 * shumisha@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     $Id: com_smf.php 866 2009-01-17 14:05:21Z silianacom-svn $
 *
 *  This module must set $shCustomTitleTag, $shCustomDescriptionTag, $shCustomKeywordsTag, $shCustomRobotsTag according to specific component output
 *  
 * if you set a variable to '', this will ERASE the corresponding meta tag
 * if you set a variable to null, this will leave the corresponding meta tag UNCHANGED  
 *     
 * @package     shCustomTags
 * Some parts from:
 * 404SEFx support for VirtueMart component.
 * Mark Fabrizio, Joomlicious
 * fabrizim@owlwatch.com
 * http://www.joomlicious.com
 * 
 * {shSourceVersionTag: Version x - 2007-09-20} 
 *     
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// init languages system 
global $shMosConfig_locale, $Itemid, $sh_LANG;
global $action, $topic, $board, $context;

// get DB
$database =& JFactory::getDBO();

// V 1.2.4.q must comply with translation restrictions
$shLangName = empty($lang) ? $shMosConfig_locale : shGetNameFromIsoCode( $lang);
$shLangIso = isset($lang) ? $lang : shGetIsoCodeFromName( $shMosConfig_locale);
$shLangIso = shLoadPluginLanguage( 'com_smf', $shLangIso, '_SH404SEF_SMF_USER');
//-------------------------------------------------------------

$action = isset($action) ? @$action : null;
$board = isset($board) ? @$board : null;
$topic = isset($topic) ? @$topic : null;

$debug = 0;
if ($debug) echo 'sh$action = '.$action.'<br />';
if ($debug) echo 'sh$board = '.$board.'<br />';
if ($debug) echo 'sh$topic = '.$topic.'<br />';

global $shCustomTitleTag, $shCustomDescriptionTag, $shCustomKeywordsTag, $shCustomLangTag, $shCustomRobotsTag;
 
$shCustomLangTag = $shLangIso; // V 1.2.4.t bug #127

if (!function_exists('shGetSMFBoardName')) {
function shGetSMFBoardName($board, $shLangIso, $shLangName, $option) {

  $sefConfig = & shRouter::shGetConfig();
  
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
		    		$result= $database->loadObject( false);
		  		else   
	    			$result = $database->loadObject();
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

if (!function_exists('shGetSMFTopicDesc')) {
  function shGetSMFTopicDesc($topic, $shLangIso, $shLangName, $option) {
	$sefConfig = & shRouter::shGetConfig();
	
	$database =& JFactory::getDBO(); 
	$desc = '';
	if (!empty($topic)) {
		// Split up the topic id and the starting value
		@list ($value, $start) = explode('.', $topic);
		if(!isset($start))
			$start = '0';
			if(!is_numeric($value)) {
				$desc = $value . '.' . $start;
			} else {
				$query = '	SELECT mf.body
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
	    			$result = $database->loadObject();
				if (!empty($result)) {
					$desc = $result->body;
				}        
			}	        
	}
	return $desc;
  }
}

$shSMFName = shGetComponentPrefix($option);
$shSMFName = empty($shSMFName) ?  getMenuTitle($option, null, $Itemid, null, $shLangName ) : $shSMFName;
$shSMFName = (empty($shSMFName) || $shSMFName == '/') ? 'SMF-Forum':$shSMFName; // V 1.2.4.t 

if (!empty($action)) {
	$tmp = explode(';', $action);
	if ($debug) var_dump($tmp);
 	if (count($tmp) > 1) {
		$mainAction = $tmp[0];
		$tmp = str_replace(";","&",str_replace(",","=",$action));
		parse_str($tmp);  // extract other variables
		if ($debug) echo '$tmp = '.$tmp.'<br />';
	} else $mainAction = $action;
} else $mainAction = '';
if ($debug) echo '$mainAction = '.$mainAction.'<br />';

switch ($mainAction) {

	case '':  // no action
		  $boardName = shGetSMFBoardName($board, $shLangIso, $shLangName, $option);
		  $topicName = shGetSMFTopicName($topic, $shLangIso, $shLangName, $option);
		  if ($sefConfig->shInsertSMFName) $title[] = $shSMFName;
		  if (!empty($topicName)) {
			  if (empty($title))
			   $title = $topicName;
        else  $title = array_merge($title, $topicName);
		  } elseif (!empty($boardName)) {
		    $title[] = $boardName;
      }
		  if (empty($title)) {
			 $title[] = $shSMFName;
		  }
	break;	

	case 'search2':
	 if ($sefConfig->shInsertSMFName) $title[] = $shSMFName;
	 $title[] = $sh_LANG[$shLangIso]['_SH404SEF_SMF_ACTION_search2'];
	break;
	
}

  $title = array_reverse( $title);
  $shCustomTitleTag = ltrim(implode( ' | ', $title), '/ | ');
  
  // description :
  if (!empty($topic)) {
    $shCustomDescriptionTag = shGetSMFTopicDesc( $topic, $shLangIso, $shLangName, $option);
  }	
  // set robots tag
  if (!empty($context['robot_no_index']))
    $shCustomRobotsTag = 'follow, noindex';
?>
