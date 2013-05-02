<?php
/**
 * sh404SEF support for Mosets Remository component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_remository.php 866 2009-01-17 14:05:21Z silianacom-svn $
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
$shLangIso = shLoadPluginLanguage( 'com_remository', $shLangIso, '_SH404SEF_REMO_DOC_DOWNLOAD');
// ------------------  load language file - adjust as needed ----------------------------------------


/********************************************************
 * Utility Functions
 ********************************************************/

if (!function_exists('shGetContainerName')) {
  function shGetContainerName($id, $option, $shLangName) {

    if (empty($id)) return null;
    $sefConfig = & shRouter::shGetConfig();
    // get DB
    $database =& JFactory::getDBO();
    if (empty($sefConfig->shRemoInsertCategories))  // no categories
    return $title;
    $title = array();
    while ($id > 0) {
      $database->setQuery('SELECT id, name, parentid FROM #__downloads_containers WHERE id ='. $id);
      if (!shTranslateUrl($option, $shLangName))
      $rows = $database->loadRow(false);
      else $rows = $database->loadRow();
      $title[] = ($sefConfig->shRemoInsertCategoryId ? $id.$sefConfig->replacement : '').$rows[1];
      $id = $rows[2];
      if($sefConfig->shRemoInsertCategories == '1') // quit after 1rst cat if params say so
      break;
    }
    $title = array_reverse($title); // V w 27/08/2007 13:50:42
    $title[] = '/'; // V w 27/08/2007 13:52:53
    return $title;
  }
}

if (!function_exists('shGetFileName')) {
  function shGetFileName($id, $option, $shLangName) {
    if (empty($id)) return null;
    $sefConfig = & shRouter::shGetConfig();
    // get DB
    $database =& JFactory::getDBO();
    $database->setQuery('SELECT id, filetitle, containerid FROM #__downloads_files WHERE id = '.$id);
    if (!shTranslateUrl($option, $shLangName))
    $rows = $database->loadRow(false);
    else $rows = $database->loadRow();
    $title = shGetContainerName($rows[2], $option, $shLangName);
    if (count($title) > 1) array_pop($title);  // V w 27/08/2007 13:56:13 remove trailling slash
    $title[] = ($sefConfig->shRemoInsertDocId ? $id.$sefConfig->replacement : '').$rows[1];
    return $title;
  }
}
// V 1.2.4.s make sure user param prevails on guessed Itemid
if (empty($Itemid) && $sefConfig->shInsertGlobalItemidIfNone
&& !empty($shCurrentItemid)) {
  $string .= '&Itemid='.$shCurrentItemid; ;  // append current Itemid
  $Itemid = $shCurrentItemid;
  shAddToGETVarsList('Itemid', $Itemid); // V 1.2.4.m
}
$func = isset($func) ? $func : null;
$id = isset($id) ? $id : null;
$Itemid = isset($Itemid) ? $Itemid : null;
$limit = isset($limit) ? $limit : null;
$limitstart = isset($limitstart) ? $limitstart : null;
// orderby, no_html, chk, fname : not processed passed as GET vars
 
// insert component name from menu
$shRemoName = shGetComponentPrefix($option);
$shRemoName = empty($shRemoName) ?  getMenuTitle($option, null, $Itemid, null, $shLangName )
: $shRemoName;
$shRemoName = (empty($shRemoName) || $shRemoName == '/') ? 'Directory':$shRemoName; // V 1.2.4.t
if ($sefConfig->shInsertRemoName && !empty($shRemoName)) $title[] = $shRemoName;

switch ($func) {

  case 'select':
    //$title[] = $sh_LANG[$shLangIso]['_SH404SEF_REMO_DOC_SELECT']; // V w Simpler URL
    if( isset($id) ) {
      if ($shTemp = shGetContainerName($id, $option, $shLangName))
      $title = array_merge($title, $shTemp);
      shRemoveFromGETVarsList('id');
    }
    else {
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_REMO_DOC_SELECT']; // V v
    }
    shRemoveFromGETVarsList('func');
    break;
  case 'addfile':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_REMO_DOC_ADD'];
    if( isset($id) ) {
      if ($shTemp = shGetContainerName($id, $option, $shLangName))
      $title = array_merge($title, $shTemp);
      shRemoveFromGETVarsList('id');
    }
    shRemoveFromGETVarsList('func');
    break;
  case 'search':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_REMO_SEARCH_DOC'];
    shRemoveFromGETVarsList('func');
    break;
  case 'addmanyfiles':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_REMO_DOC_ADD_MANY'];
    shRemoveFromGETVarsList('func');
    break;
  case 'fileinfo':
    if( isset($id) ) {
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_REMO_DOC_DETAILS'];
      $title = array_merge($title, shGetFileName($id, $option, $shLangName));
      shRemoveFromGETVarsList('id');
      shRemoveFromGETVarsList('func');
    } else $dosef= false;
    break;
  case 'thumbupdate':
    if( isset($id) ) {
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_REMO_THUMB_UPDATE'];
      $title = array_merge($title, shGetFileName($id, $option, $shLangName));
      shRemoveFromGETVarsList('id');
      shRemoveFromGETVarsList('func');
    } else $dosef= false;
    break;
  case 'userupdate':
    if( isset($id) ) {
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_REMO_USER_UPDATE'];
      $title = array_merge($title, shGetFileName($id, $option, $shLangName));
      shRemoveFromGETVarsList('id');
      shRemoveFromGETVarsList('func');
    } else $dosef= false;
    break;
  case 'rss':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_REMO_RSS'];
    if( isset($id) ) {
      if ($shTemp = shGetContainerName($id, $option, $shLangName))
      $title = array_merge($title, $shTemp);
      shRemoveFromGETVarsList('id');
    }
    shRemoveFromGETVarsList('func');
    shRemoveFromGETVarsList('no_html');
    break;
  case 'download':
    if( isset($id) ) {
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_REMO_DOC_DOWNLOAD'];
      $title = array_merge($title, shGetFileName($id, $option, $shLangName));
      shRemoveFromGETVarsList('id');
      shRemoveFromGETVarsList('func');
    } else $dosef= false;
    break;
  case 'startdown':
    if( isset($id) ) {
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_REMO_DOC_START_DOWNLOAD'];
      $title = array_merge($title, shGetFileName($id, $option, $shLangName));
      shRemoveFromGETVarsList('id');
      shRemoveFromGETVarsList('func');
    } else $dosef= false;
    break;
  case 'showdown':
  case 'finishdown':
    $dosef = false;
    break;

  case '':
    if (empty( $title)) $title[] = $shRemoName; // at least put defautl name, even if told not to do so
    $title[] = '/';
    shRemoveFromGETVarsList('func');
    break;

  default:
    $dosef = false;
    break;

}

/* sh404SEF extension plugin : remove vars we have used, adjust as needed --*/
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');
shRemoveFromGETVarsList('Itemid');
if (isset($limit))
shRemoveFromGETVarsList('limit');
if (isset($limitstart))
shRemoveFromGETVarsList('limitstart'); // limitstart can be zero
/* sh404SEF extension plugin : end of remove vars we have used -------------*/


// ------------------  standard plugin finalize function - don't change ---------------------------
if ($dosef){
  $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString,
  (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null),
  (isset($shLangName) ? @$shLangName : null));
}
// ------------------  standard plugin finalize function - don't change ---------------------------

?>
