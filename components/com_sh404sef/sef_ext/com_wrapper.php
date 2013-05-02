<?php
/**
 * sh404SEF support for com_wrapper component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_wrapper.php 866 2009-01-17 14:05:21Z silianacom-svn $
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


  shRemoveFromGETVarsList('option');
  shRemoveFromGETVarsList('view');
  if (!empty($lang))
    shRemoveFromGETVarsList('lang'); 
  if (isset($Itemid))   
  shRemoveFromGETVarsList('Itemid');
  $shTemp = getMenuTitle($option, null, $Itemid, null, $shLangName );
  if (!empty($shTemp) && ($shTemp != '/')) $title[] = $shTemp; // V 1.2.4.t
	
// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------
  
?>
