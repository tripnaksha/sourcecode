<?php

/**
 * sh404SEF support for com_weblinks component.
 * Copyright Yannick Gaultier (shumisha) - 2008
 * shumisha@gmail.com
 * @version     $Id: com_weblinks.php 866 2009-01-17 14:05:21Z silianacom-svn $
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
$shLangIso = shLoadPluginLanguage( 'com_weblinks', $shLangIso, '_COM_SEF_SH_CREATE_NEW_LINK');
// ------------------  load language file - adjust as needed ----------------------------------------
$view = isset($view) ? @$view : null;
$Itemid = isset($Itemid) ? @$Itemid : null;
$shWeblinksName = shGetComponentPrefix($option);
$shWeblinksName = empty($shWeblinksName) ?  
	getMenuTitle($option, isset($view) ? $view:null, isset($Itemid) ? $Itemid:null, null, $shLangName) : $shWeblinksName;
$shWeblinksName = (empty($shWeblinksName) || $shWeblinksName == '/') ? 'Newsfeed':$shWeblinksName;
if (!empty($shWeblinksName)) $title[] = $shWeblinksName;

switch ($view) {
  case 'weblink':  
    if (!empty($catid)) { // V 1.2.4.q
    	$arg2[] = sef_404::getcategories($catid, $shLangName);
    	$title = array_merge($title, $arg2);
    }                                      
    if (!empty($id)) {
	    $query = 'SELECT title, id FROM #__weblinks WHERE id = "'.$id.'"';
	    $database->setQuery($query);
	    if (shTranslateURL($option, $shLangName))
	      $rows = $database->loadObjectList( );
	    else  $rows = $database->loadObjectList( null, false);
	    if ($database->getErrorNum()) {
		    JError::raiseError(500, $database->stderr() );
	    }elseif( @count( $rows ) > 0 ){
		    if( !empty( $rows[0]->title ) ){
			    $title[] = $rows[0]->title;
		    }
	    }
    }
    else $title[] = '/'; // V 1.2.4.s
  break;
  case 'category':
  	if (!empty($id)) { // V 1.2.4.q
  		$arg2[] = sef_404::getcategories($id, $shLangName);
  		$title = array_merge($title, $arg2);
  		$title[] = '/';
  	}
  break;
  case 'new':
	  $title[] = $sh_LANG[$shLangIso]['_COM_SEF_SH_CREATE_NEW_LINK'] . $sefConfig->suffix;
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
if (!empty($view))
  shRemoveFromGETVarsList('view');
if (!empty($id))  
  shRemoveFromGETVarsList('id');
if (!empty($layout))  
  shRemoveFromGETVarsList('layout');  

// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------

?>
