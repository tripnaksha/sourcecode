<?php
/**
 * sh404SEF support for com_newsfeed component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_newsfeeds.php 866 2009-01-17 14:05:21Z silianacom-svn $
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
$shLangIso = shLoadPluginLanguage( 'com_newsfeeds', $shLangIso, '_COM_SEF_SH_CREATE_NEW_NEWSFEED');
// ------------------  load language file - adjust as needed ----------------------------------------


$shNewsfeedName = shGetComponentPrefix($option);
$shNewsfeedName = empty($shNewsfeedName) ?  
		getMenuTitle($option, isset($view) ? $view:null, isset($Itemid) ? $Itemid:null, null, $shLangName) : $shNewsfeedName;
$shNewsfeedName = (empty($shNewsfeedName) || $shNewsfeedName == '/') ? 'Newsfeed':$shNewsfeedName;
if (!empty($shNewsfeedName)) $title[] = $shNewsfeedName; // V 1.2.4.t                         
                         
$view = isset($view) ? $view : null;

switch ($view) {
  case 'newsfeed':
  	if (!empty($catid)) { // V 1.2.4.q
  		$title[] = sef_404::getcategories($catid, $shLangName);
  	}
    if (!empty($id)) {
	    $query = 'SELECT name, id FROM #__newsfeeds WHERE id = "'.$id.'"';
	    $database->setQuery($query);
	    if (shTranslateURL($option, $shLangName))
	      $rows = $database->loadObjectList( );
	    else  $rows = $database->loadObjectList( null, false);
	    if ($database->getErrorNum()) {
		    JError::raiseError(500, $database->stderr() );
	    }elseif( @count( $rows ) > 0 ){
		    if( !empty( $rows[0]->name ) ){
			    $title[] = $rows[0]->name;
		    }
	    }
    } 
    else $title[] = '/'; // V 1.2.4.s
  break;
  case 'category':
  	if (!empty($id)) { // V 1.2.4.q
  		$title[] = sef_404::getcategories($id, $shLangName);
  		$title[] = '/';
  	}
  break;
  case 'new':
	  $title[] = $sh_LANG[$shLangIso]['_COM_SEF_SH_CREATE_NEW_NEWSFEED'] . $sefConfig->suffix;
	break;
	default:
	  $title[] = '/'; // V 1.2.4.s
	break;
}

shRemoveFromGETVarsList('option');
if (!empty($Itemid))
  shRemoveFromGETVarsList('Itemid');
shRemoveFromGETVarsList('lang');
if (!empty($catid))
  shRemoveFromGETVarsList('catid');
if (!empty($id))
  shRemoveFromGETVarsList('id');  
if (!empty($view))
  shRemoveFromGETVarsList('view');
if (!empty($feedid))  
  shRemoveFromGETVarsList('feedid');

// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------

?>
