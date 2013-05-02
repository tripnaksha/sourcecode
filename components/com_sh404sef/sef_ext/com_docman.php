<?php
/**
 * sh404SEF support for com_docman component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_docman.php 866 2009-01-17 14:05:21Z silianacom-svn $
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
$shLangIso = shLoadPluginLanguage( 'com_docman', $shLangIso, '_SH404SEF_DOCMAN_DOC_DOWNLOAD');
// ------------------  load language file - adjust as needed ----------------------------------------


// utilitity function
if (!function_exists('shDocmanDocumentName')) {
  function shDocmanDocumentName($docId, $option, $shLangName) {
    if (empty($docId)) return '';
    $sefConfig = & shRouter::shGetConfig();
    // get DB
    $database =& JFactory::getDBO();
    
    $database->setQuery('SELECT dmname, catid FROM #__docman WHERE id = '.$docId);
    if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
		  $result = $database->loadObject( false);
    else $result= $database->loadObject($result);
    if ($database->getErrorNum()) {
      JError::raiseError(500, $database->stderr() );
    }
    
    return empty($result) ? '' : 
      ($sefConfig->shDocmanInsertDocId ? $docId.$sefConfig->replacement.$result->dmname:$result->dmname);
  }
}

 /**
 * Function vm_sef_get_category_array() is based on  
 * Mark Fabrizio, Joomlicious
 * fabrizim@owlwatch.com
 * http://www.joomlicious.com
 */
if( !function_exists( 'dm_sef_get_category_array' ) ){
  function dm_sef_get_category_array( $category_id, $option, $shLangName ){
  
      global $shMosConfig_locale;
      $sefConfig = & shRouter::shGetConfig();
      
      // get DB
      $database =& JFactory::getDBO();
    
	  static $DMtree = null;  
	  
	  if(empty($tree[$shMosConfig_locale])){
  		$q  = "SELECT id, title, parent_id FROM #__categories" ;  // load them all in memory
	  	$database->setQuery( $q );
	  	if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
	  	  $DMtree[$shMosConfig_locale] = $database->loadObjectList( 'id', false);  // V 1.2.4.m if Joomfish, and don't translate
	  	                                                    // use special call of loadObjectList, asking JF not to translate
	  	else  
		    $DMtree[$shMosConfig_locale] = $database->loadObjectList( 'id' );
  	}
	  $title=array();
	  if ($sefConfig->shDMInsertCategories == 1)    // only one category
	    $title[] = ($sefConfig->shDMInsertCategoryId ? 
                    $DMtree[$shMosConfig_locale][ $category_id ]->id.$sefConfig->replacement : '')   
                 .$DMtree[$shMosConfig_locale][ $category_id ]->title; 
    else 
      do {               // all categories and subcategories. We don't really need id, as path 
		    $title[] = ($sefConfig->shDMInsertCategoryId ? 
          $DMtree[$shMosConfig_locale][ $category_id ]->id.$sefConfig->replacement : '') // to category
          .$DMtree[$shMosConfig_locale][ $category_id ]->title;                           // will always be unique
		    $category_id = $DMtree[$shMosConfig_locale][ $category_id ]->parent_id;
	    } while( $category_id != 0 );
	  return array_reverse( $title );
  }
}

$task = isset($task) ? @$task : null;
$Itemid = isset($Itemid) ? @$Itemid : null;  // V 1.2.4.t

// shumisha : insert component name from menu
$shDocmanName = shGetComponentPrefix($option);
$shDocmanName = empty($shDocmanName) ?  getMenuTitle($option, null, $Itemid, null, $shLangName )
                                                 : $shDocmanName;
$shDocmanName = (empty($shDocmanName) || $shDocmanName == '/') ? 'Files':$shDocmanName; // V 1.2.4.t  


if ($sefConfig->shInsertDocmanName && !empty($shDocmanName)) $title[] = $shDocmanName;

if (!empty($gid) && (strpos($task,'doc_') !== false || $task == 'license_result'))
  $docName = shDocmanDocumentName($gid, $option, $shLangName);
else 
  $docName = '';

switch ($task)
{
    case 'cat_view':
      if (!empty($gid))
        $title = array_merge( $title, dm_sef_get_category_array( $gid, $option, $shLangName));
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_DOCMAN_VIEW_CAT'];  
    break;    
    case 'doc_download':
    case 'doc_edit':
    case 'doc_checkout':
    case 'doc_checkin':
    case 'doc_reset':
    case 'doc_move':
    case 'doc_move_process':
    case 'doc_delete':
    case 'doc_approve':
    case 'doc_details':
    case 'doc_unpublish':
    case 'doc_publish':
    case 'doc_view':
    case 'cancel':  // V 1.2.4.t
    case 'doc_save':  // V 1.2.4.t
    case 'save':  // V 1.2.4.t
    case 'doc_cancel':  // V 1.2.4.t
    case 'doc_update_process': // V 1.2.4.t  
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_DOCMAN_'.strtoupper($task)];
      if ($sefConfig->shDocmanInsertDocName && !empty($docName))
        $title[] = $docName;
    break;  
    case 'search_form':
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_DOCMAN_SEARCH_DOC'];
    break;
    case 'search_result':
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_DOCMAN_SEARCH_RESULTS'];
    break;
    case 'doc_update':
    case 'upload':
      // step not defined = choose upload method, step = 2 = select file step=3 = Enter details
      $step = isset($step) ? $step:null;
      if ($task == 'doc_update') {
        $title[] = $sh_LANG[$shLangIso]['_SH404SEF_DOCMAN_DOC_UPDATE'];
        if ($sefConfig->shDocmanInsertDocName && !empty($docName))
          $title[] = $docName;
      } else  
        $title[] = $sh_LANG[$shLangIso]['_SH404SEF_DOCMAN_UPLOAD'];
      switch ($step) {
        case 2:
          $title[] = $sh_LANG[$shLangIso]['_SH404SEF_DOCMAN_UPLOAD_SELECT_FILE'];
        break;
        case 3:
          $title[] = $sh_LANG[$shLangIso]['_SH404SEF_DOCMAN_UPLOAD_DETAILS'];
        break;
        default:
          $title[] = $sh_LANG[$shLangIso]['_SH404SEF_DOCMAN_UPLOAD_SELECT_METHOD'];
        break;
      }
      if (!empty($step))
        shRemoveFromGETVarsList('step');
    break;  
    case 'license_result':
      $title[] = $sh_LANG[$shLangIso]['_SH404SEF_DOCMAN_LICENSE_RESULT'];
      if ($sefConfig->shDocmanInsertDocName && !empty($docName))
        $title[] = $docName;
    break;
    default:
      if (!$sefConfig->shInsertDocmanName) {
        $title[] = $shDocmanName;
        $title[] = '/';
      } 
    break;  
}

shRemoveFromGETVarsList('option');
if (isset($lang))
  shRemoveFromGETVarsList('lang');  
if (isset($Itemid))
  shRemoveFromGETVarsList('Itemid');
if (isset($task))
  shRemoveFromGETVarsList('task');
if ((($sefConfig->shDocmanInsertDocName && !empty($docName)) || (strpos($task,'doc_') === false || $task == 'license_result')) && !empty($gid))
  shRemoveFromGETVarsList('gid');
  
// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------

?>