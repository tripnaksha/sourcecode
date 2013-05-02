<?php
/**
 * sh404SEF support for com_content component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_sobi2.php 657 2008-08-08 09:38:49Z silianacom-svn $
 * {shSourceVersionTag: Version x - 2007-09-20}
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;  
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
// ------------------  standard plugin initialize function - don't change ---------------------------

// ------------------  load language file - adjust as needed ----------------------------------------
$shLangIso = shLoadPluginLanguage( 'com_sobi2', $shLangIso, '_SH404SEF_SOBI2_CREATE_NEW');
// ------------------  load language file - adjust as needed ----------------------------------------

// a few params
if (!defined('sh404SEF_SOBI2_PARAMS_ALWAYS_INCLUDE_CATS')) {
	define ('sh404SEF_SOBI2_PARAMS_ALWAYS_INCLUDE_CATS', 0);
	define ('sh404SEF_SOBI2_PARAMS_INCLUDE_ENTRY_ID', 0);
	define ('sh404SEF_SOBI2_PARAMS_INCLUDE_CAT_ID', 0);
}

shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');
if (!empty($Itemid))  
  shRemoveFromGETVarsList('Itemid');

// based on Sigsiu Online Business Index 2 sef_ext.php file for OpenSEF
	
global $shSobi2CatParents;
$shSobi2CatParents = array();
$shSobi2Details = false;
$sobiMenuId = null;
$shLimit = null;
    
    if (!function_exists('shSobi2GetLimit')) {
    function shSobi2GetLimit() {
    	// get DB
    	$database =& JFactory::getDBO();
    	$query = "SELECT `configValue` FROM `#__sobi2_config` WHERE (`configKey` = 'itemsInLine' OR `configKey` = 'lineOnSite')";
    	$database->setQuery( $query );
    	$l = $database->loadObjectList();
    	return (int)$l[0]->configValue * (int)$l[1]->configValue;
    }
    }
    
    if (!function_exists('shSobi2GetCatName')) {
    function shSobi2GetCatName($cid, &$title) {
      	global  $shSobi2CatParents;
      	// get DB
      	$database =& JFactory::getDBO();
      	  $sefConfig = shRouter::shGetConfig();
      	  shSobi2GetParentCats($cid);
		  if (!empty($shSobi2CatParents)) {
		    $shSobi2CatParents = array_reverse($shSobi2CatParents);
	  	  $catName = null;
  		  foreach($shSobi2CatParents as $cid) {			
	  		  $query = "SELECT `name` FROM `#__sobi2_categories` WHERE (`catid`= $cid AND `published` = 1)";
	  		  $database->setQuery( $query );
	  		  $cat = $database->loadResult();
	   		  $title[] = (sh404SEF_SOBI2_PARAMS_INCLUDE_CAT_ID ? $cid.$sefConfig->replacement:'')
	   		  	.html_entity_decode($cat); // V 1.2.4.T added html_entity_decode
	   		}  
	  	}
    }
    }
    
    if (!function_exists('shSobi2GetItemName')) {
    function shSobi2GetItemName($id){
    	$database =& JFactory::getDBO();
    	if (empty($id)) return '';
    	$sefConfig = shRouter::shGetConfig();
    	$query = "SELECT `title` FROM `#__sobi2_item` WHERE (`itemid`=$id AND `published` = 1)";
    	$database->setQuery( $query );
		$ret = (sh404SEF_SOBI2_PARAMS_INCLUDE_ENTRY_ID ? $id.$sefConfig->replacement:'')  // add id if setup to  
			.html_entity_decode($database->loadResult()); // V 1.2.4.t added html_entit_decode
		return $ret;   
    }
    }
    
    if (!function_exists('shSobi2GetItemCat')) {
    function shSobi2GetItemCat($id){
    	// get DB
    	$database =& JFactory::getDBO();
    	if (empty($id)) return '';
    	$query = "SELECT `catid` FROM `#__sobi2_cat_items_relations` WHERE (`itemid`=$id )";
    	$database->setQuery( $query );
		  $ret = $database->loadResult(); // V 1.2.4.t added html_entit_decode
		return $ret;   
    }
    }
    
    if (!function_exists('shSobi2GetParentCats')) {
	  function shSobi2GetParentCats ($catid) {
		global $shSobi2CatParents;
		// get DB
		$database =& JFactory::getDBO();
		$query = "SELECT `parentid` from `#__sobi2_cats_relations` WHERE `catid`= $catid";
		$database->setQuery( $query );
		if($catid != 1)
			array_push($shSobi2CatParents, $catid);
			if(sizeof($database->loadResult()) != 0)
				shSobi2GetParentCats($database->loadResult());	
	  }
    }
    
    $shSobi2Name = shGetComponentPrefix($option);
	if (!empty($shSobi2Name)) $title[] = $shSobi2Name;
	
	  $sobi2Task = isset($sobi2Task) ? @$sobi2Task : null;
        /* get the task */
        switch ($sobi2Task) {
          case 'addNew':
            $title[] = $sh_LANG[$shLangIso]['_SH404SEF_SOBI2_CREATE_NEW'];
            shRemoveFromGETVarsList('sobi2Task');
          break;
          case 'search':
            $title[] = $sh_LANG[$shLangIso]['_SH404SEF_SOBI2_SEARCH_ENTRY'];
            shRemoveFromGETVarsList('sobi2Task');
          break;
          case 'editSobi':
            $title[] = $sh_LANG[$shLangIso]['_SH404SEF_SOBI2_EDIT_ENTRY'];
            shRemoveFromGETVarsList('sobi2Task');
          break;
          case 'deleteSobi':
            $title[] = $sh_LANG[$shLangIso]['_SH404SEF_SOBI2_DELETE_ENTRY'];
            shRemoveFromGETVarsList('sobi2Task');
          break;
          case 'sobi2Details' :
            $title[] = $sh_LANG[$shLangIso]['_SH404SEF_SOBI2_ENTRY_DETAILS'];
            shRemoveFromGETVarsList('sobi2Task');
            $shSobi2Details = true;
          break;
          case'':
            $title[] = getMenuTitle($option, null, $Itemid, null, $shLangName );
            $title[] = '/';
          break;
          default:
            $dosef = false;
          break;
        }
        shRemoveFromGETVarsList('catid');
        /* get catid */
        if (isset($catid) && !$shSobi2Details) {  // if task=sobi2Details, catid is NOT item category, but Current category!
          shSobi2GetCatName($catid, $title);
          shRemoveFromGETVarsList('catid');
        }
       /* and now get sobiid */
        if (!empty($sobi2Id) && $shSobi2Details) {
          $shTemp = shSobi2GetItemName($sobi2Id);
          if (sh404SEF_SOBI2_PARAMS_ALWAYS_INCLUDE_CATS) {  // params say include cat, but there is no cat id  
          	$shCatId = shSobi2GetItemCat($sobi2Id);
          	if (!empty($shCatId)) {
          		shSobi2GetCatName($shCatId, $title);
          	}
          }
          $title[] = empty($shTemp) ? $sh_LANG[$shLangIso]['_SH404SEF_SOBI2_ENTRY'].$sobi2Id : $shTemp;
          shRemoveFromGETVarsList('sobi2Id');
        }
	
// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------
 
?>
