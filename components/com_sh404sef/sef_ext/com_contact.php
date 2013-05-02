<?php
/**
 * sh404SEF support for com_contact component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_contact.php 866 2009-01-17 14:05:21Z silianacom-svn $
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

       
// do something about that Itemid thing
if (eregi('Itemid=[0-9]+', $string) === false) { // if no Itemid in non-sef URL
  //global $Itemid;
  if ($sefConfig->shInsertGlobalItemidIfNone && !empty($shCurrentItemid)) {
    $string .= '&Itemid='.$shCurrentItemid;  // append current Itemid 
    $Itemid = $shCurrentItemid;
    shAddToGETVarsList('Itemid', $Itemid); // V 1.2.4.m
  }  
  if ($sefConfig->shInsertTitleIfNoItemid)
  	$title[] = $sefConfig->shDefaultMenuItemName ? 
      $sefConfig->shDefaultMenuItemName : getMenuTitle($option, null, $shCurrentItemid );
  $shItemidString = $sefConfig->shAlwaysInsertItemid ? 
    _COM_SEF_SH_ALWAYS_INSERT_ITEMID_PREFIX.$sefConfig->replacement.$shCurrentItemid
    : '';
} else {  // if Itemid in non-sef URL
  $shItemidString = $sefConfig->shAlwaysInsertItemid ? 
    _COM_SEF_SH_ALWAYS_INSERT_ITEMID_PREFIX.$sefConfig->replacement.$Itemid
    : '';
}
  
// for contact page we always add something before contact name
$view = isset($view) ? @$view : null;
$Itemid = isset($Itemid) ? @$Itemid : null;
$shName = shGetComponentPrefix($option);
$shName = empty($shName) ? getMenuTitle($option, (isset($view) ? @$view : null), $Itemid ) : $shName;
if (!empty($shName) && $shName != '/') $title[] = $shName;  // V x

switch ($view) {
	case 'category' :
   
		// fetch cat namee
		if (!empty($catid)) {
			shRemoveFromGETVarsList('catid');
			$query  = "SELECT title, id FROM #__categories" ;
			$query .= "\n WHERE id=".$catid;
			$database->setQuery( $query );
			if (shTranslateUrl($option, $shLangName))
				$result = $database->loadObject();
			else $result = $database->loadObject( false); 
			if (!empty($result)) $title[] = $result->title;
			else $title[] = $catid;
		} else {
			$title[] = $view;
		}
		$title[] = '/';
	break;
	case 'contact' :
		// fetch contact name
		if (!empty($id)) {
			shRemoveFromGETVarsList('id');
			$query  = "SELECT name, id FROM #__contact_details" ;
			$query .= "\n WHERE id=".$id;
			$database->setQuery( $query );
			if (shTranslateUrl($option, $shLangName))
				$result = $database->loadObject();
			else $result = $database->loadObject( false); 
			if (!empty($result)) $title[] = $result->name;
			else $title[] = $id;
		}
		if (isset($sefConfig->suffix)) {
			$title[count($title)-1] .= $sefConfig->suffix;
		} else {
			$title[] = '/';
		}
	break;
}

shRemoveFromGETVarsList('option');
if (!empty($Itemid))
  shRemoveFromGETVarsList('Itemid');
shRemoveFromGETVarsList('lang');
if (!empty($view))
  shRemoveFromGETVarsList('view');
 
// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------	
	
?>
