<?php
/**
 * sh404SEF support for com_rd_rss component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_rd_rss.php 866 2009-01-17 14:05:21Z silianacom-svn $
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
$shLangIso = shLoadPluginLanguage( 'com_rd_rss', $shLangIso, '_COM_SEF_SH_RD_RSS');
// ------------------  load language file - adjust as needed ----------------------------------------


$title[] = $sh_LANG[$shLangIso]['_COM_SEF_SH_RD_RSS'];
       
// fetch contact name
if (!empty($id)) {
  $query  = "SELECT name, id FROM #__rd_rss" ;
  $query .= "\n WHERE id=".$id;
  $database->setQuery( $query );
  if (!shTranslateUrl($option, $shLangName)) // V 1.2.4.m
    $result = $database->loadObject(false);
  else $result = $database->loadObject();
	if (!empty($result)) $title[] = $result->name;
	else $title[] = $id;
}    

$title[] = '/';
shRemoveFromGETVarsList('option');
if (!empty($Itemid))
  shRemoveFromGETVarsList('Itemid');
shRemoveFromGETVarsList('lang');
shRemoveFromGETVarsList('id');

// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------
	
?>
