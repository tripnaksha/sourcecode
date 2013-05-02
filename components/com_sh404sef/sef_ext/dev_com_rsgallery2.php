<?php
/**
 * sh404SEF support for RS Gallery 2 component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: dev_com_rsgallery2.php 866 2009-01-17 14:05:21Z silianacom-svn $
 * {shSourceVersionTag: Version x - 2007-09-20}
 * Based on Daniel Tulp extension for Artio Joomsef
 * License : GNU/GPL 
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
$shLangIso = shLoadPluginLanguage( '', $shLangIso, '');
// ------------------  load language file - adjust as needed ----------------------------------------



// shumisha : insert component name from menu
$task = isset($task) ? @$task : null;
$Itemid = isset($Itemid) ? @$Itemid : null; 
$shRsGallery2Name = shGetComponentPrefix($option);
$shRsGallery2Name = empty($shRsGallery2Name) ?  getMenuTitle($option, $task, $Itemid, null, $shLangName ) : $shRsGallery2Name;
$shRsGallery2Name = (empty($shRsGallery2Name) || $shRsGallery2Name == '/') ? 'RSGallery1':$shRsGallery2Name;

if ($sefConfig->shInsertRsGallery2Name && !empty($shRsGallery2Name)) $title[] = $shRsGallery2Name;

//load gallery name
if (isset($catid)) {
    $query_gal = "
		SELECT `name`
		FROM `#__rsgallery2_galleries`
		WHERE `id` = $catid
		";
    $database->setQuery($query_gal);
    $gallery = $database->loadResult();
}
//load imagename
if (isset($limitstart)){
$order = $limitstart +1;
}
if (isset($order) && isset($catid)) {
    $query_name = "
		SELECT `title`
		FROM `#__rsgallery2_files`
		WHERE `ordering` = $order AND `gallery_id` = $catid
		";
    $database->setQuery($query_name);
    $name = $database->loadResult();
}
//apply to array title[]
if (!empty($option)) {
    $title[] = $option;
    // Unset the original URL variable not to interfere anymore.
    unset($vars['option']);
}
if (!empty($gallery)) {
    $title[] = $gallery;
    // Unset the original URL variable not to interfere anymore.
    unset($vars['catid']);
}

// Now message title read from DB is added as the next part of the SEF path.
if (!empty($name)) {
    $title[] = $name;
    // Unset the original URL variable not to interfere anymore.
    unset($vars['id']);
}

// work in progress 
$dosef = false;
  
/* sh404SEF extension plugin : remove vars we have used, adjust as needed --*/  
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');  
shRemoveFromGETVarsList('Itemid');
if (!empty($task))
  shRemoveFromGETVarsList('task');
if (!empty($limit))
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
