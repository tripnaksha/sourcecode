<?php
/**
 * sh404SEF support for com_search component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_search.php 866 2009-01-17 14:05:21Z silianacom-svn $
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
$shLangIso = shLoadPluginLanguage( 'com_search', $shLangIso, '_COM_SEF_SH_SEARCH');
// ------------------  load language file - adjust as needed ----------------------------------------                                           


  shRemoveFromGETVarsList('option');
  shRemoveFromGETVarsList('lang');
  shRemoveFromGETVarsList('Itemid');
  shRemoveFromGETVarsList('task');
  shRemoveFromGETVarsList('limit');
  if (isset($limitstart))  // V 1.2.4.r
    shRemoveFromGETVarsList('limitstart'); // limitstart can be zero
    
  //$title[] = getMenuTitle($option, (isset($task) ? @$task : null));
	$title[] = $sh_LANG[$shLangIso]['_COM_SEF_SH_SEARCH'];
	$ordering = isset($ordering) ? @$ordering : null;
  switch ($ordering) {
    case 'newest'   : 
      $title[] =  $sh_LANG[$shLangIso]['_SEARCH_NEWEST'];
      shRemoveFromGETVarsList('ordering');
    break;  
	  case 'oldest' : 
    $title[] = $sh_LANG[$shLangIso]['_SEARCH_OLDEST'];
      shRemoveFromGETVarsList('ordering');
    break;
	  case 'popular' : 
      $title[] = $sh_LANG[$shLangIso]['_SEARCH_POPULAR'];
      shRemoveFromGETVarsList('ordering');
    break;  
	  case 'alpha': 
    $title[] = $sh_LANG[$shLangIso]['_SEARCH_ALPHABETICAL'];
      shRemoveFromGETVarsList('ordering');
    break; 
	  case 'category':
      $title[] = $sh_LANG[$shLangIso]['_SEARCH_CATEGORY'];
      shRemoveFromGETVarsList('ordering'); 
    break;  
  }  
  
// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------
  
?>
