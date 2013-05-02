<?php
/**
 * sh404SEF support for iJoomla magazinecomponent.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_news_portal.php 866 2009-01-17 14:05:21Z silianacom-svn $
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

// ------------------  standard plugin initialize function - don't change ---------------------------

// ------------------  load language file - adjust as needed ----------------------------------------
//$shLangIso = shLoadPluginLanguage( 'com_news_portal', $shLangIso, '_SH404SEF_NEWS_PORTAL');
// ------------------  load language file - adjust as needed ----------------------------------------


shRemoveFromGETVarsList('option');
if (!empty($lang))
  shRemoveFromGETVarsList('lang');
if (!empty($Itemid))
  shRemoveFromGETVarsList('Itemid');

if (!function_exists('shGetNEWSPSection')) {
  function shGetNEWSPSection( $sectionId, $option, $shLangName) {
  if (empty($sectionId)) return '';
  static $sectionData = null;
  $sefConfig = & shRouter::shGetConfig();
  
  if (!is_null($sectionData[$shLangName][$sectionId])) {
    // get DB
    $database =& JFactory::getDBO();
  
    $query = 'SELECT * FROM #__sections WHERE id=\''.$sectionId.'\'';
    $database->setQuery( $query );
    if (shTranslateUrl($option, $shLangName))
      $sections = $database->loadObjectList();
    else $sections = $database->loadObjectList(false);
    $ret = empty($sections) ? '' : ($sefConfig->shNewsPInsertSecId ? $sectionId.$sefConfig->replacement : '')
                                    .$sections[0]->title;
    if (!empty($ret)) $sectionData[$shLangName][$sectionId] = $ret;
  } else $ret = $sectionData[$shLangName][$sectionId];
  
  return $ret;
  }
}

if (!function_exists('shGetNEWSPCategories')) {
  function shGetNEWSPCategories( $catId, $option, $shLangName, &$cat, &$sec) {
    if (empty($catId)) return false;
    static $catData = null;
    $sefConfig = & shRouter::shGetConfig();
  
    if (!is_null($catData[$shLangName][$catId])) {
      // get DB
      $database =& JFactory::getDBO();
    
      $query = "SELECT c.id, c.section, c.title, s.id as sectionid, s.title as stitle"
           . "\n FROM #__categories as c, #__sections as s"
     	     . "\n WHERE "
  		     . "\n s.id = c.section"
  		     . "\n AND c.id = '".$catId."'";
      $database->setQuery( $query );
      if (shTranslateUrl($option, $shLangName))
        $categories = $database->loadObjectList();
      else $categories = $database->loadObjectList(false);
      if (!empty(	$categories)) {
			  $sec = ($sefConfig->shNewsPInsertSecId ? $sectionId.$sefConfig->replacement : '')
                .$categories[0]->stitle;  // section
			  $cat = ($sefConfig->shNewsPInsertCatId ? $sectionId.$sefConfig->replacement : '')
               .$categories[0]->title;   // category
			  $catData[$shLangName][$catId]['cat'] = $cat;
			  $catData[$shLangName][$catId]['sec'] = $sec;
			} 
  } else {
    $cat = $catData[$shLangName][$catId]['cat'];
    $sec = $catData[$shLangName][$catId]['sec'];
  }  
  
  return !empty($cat);
  }
}

$task = isset($task) ? $task : null;
$id = isset($id) ? $id : null;
$Itemid = isset($Itemid) ? $Itemid : null;
$limit = isset($limit) ? $limit : null;
$limitstart = isset($limitstart) ? $limitstart : null;
    
// shumisha : insert news portal name from menu
$shNewsPortalName = shGetComponentPrefix($option);
$shNewsPortalName = empty($shNewsPortalName) ?  getMenuTitle($option, @$task, $Itemid, '', $shLangName ) 
  : $shNewsPortalName;
$shNewsPortalName = (empty($shNewsPortalName) || $shNewsPortalName == '/') ? 'News':$shNewsPortalName; // V 1.2.4.t  

switch ($task)
{
   		 
    case 'section' :
      if ($sefConfig->shInsertNewsPortalName) $title[] = $shNewsPortalName;
     	$sectionTitle = shGetNEWSPSection( $id, $option, $shLangName);
      if (!empty($sectionTitle)) {
        $title[] = $sectionTitle;
        $title[] = '/';
        shRemoveFromGETVarsList('section');
      } else $dosef = false; 
    break;
    
    case 'category':
			$cat = '';
			$sec = '';
			if (shGetNEWSPCategories( $id, $option, $shLangName, $cat, $sec)) {
			  $title[] = $sec;  // section
			  $title[] = $cat;   // category
			  $title[] = '/';
			  shRemoveFromGETVarsList('category');
      } else $dosef = false;
    break;
    
    case '':
      $title[] = $shNewsPortalName;
      $title[] = '/';
    break;
    
    default:
      $dosef = false;
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
