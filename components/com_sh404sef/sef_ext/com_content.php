<?php
/**
 * sh404SEF support for com_content component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_content.php 866 2009-01-17 14:05:21Z silianacom-svn $
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
$shLangIso = shLoadPluginLanguage( 'com_content', $shLangIso, '_COM_SEF_SH_CREATE_NEW');
// ------------------  load language file - adjust as needed ----------------------------------------

global $mainframe;

// get DB
$database =& JFactory::getDBO();

// 1.2.4.q this is content item, so let's try to improve missing Itemid handling
// retrieve section id to know whether this static or not
$shHomePageFlag = false;

$shHomePageFlag = !$shHomePageFlag ? shIsHomepage ($string): $shHomePageFlag;

if (!$shHomePageFlag) {  // we may have found that this is homepage, so we msut return an empty string
  // do something about that Itemid thing
  if (eregi('Itemid=[0-9]+', $string) === false) { // if no Itemid in non-sef URL
    // V 1.2.4.t moved back here
    if ($sefConfig->shInsertGlobalItemidIfNone && !empty($shCurrentItemid)) {
      $string .= '&Itemid='.$shCurrentItemid; ;  // append current Itemid
      $Itemid = $shCurrentItemid;
      shAddToGETVarsList('Itemid', $Itemid); // V 1.2.4.m
    }

    if ($sefConfig->shInsertTitleIfNoItemid)
    $title[] = $sefConfig->shDefaultMenuItemName ?
  		$sefConfig->shDefaultMenuItemName : getMenuTitle($option, (isset($view) ? @$view : null), $shCurrentItemid, null, $shLangName );  // V 1.2.4.q added forced language
  		$shItemidString = '';
  		if ($sefConfig->shAlwaysInsertItemid && (!empty($Itemid) || !empty($shCurrentItemid)))
    $shItemidString = _COM_SEF_SH_ALWAYS_INSERT_ITEMID_PREFIX.$sefConfig->replacement
    .(empty($Itemid)? $shCurrentItemid :$Itemid);
  } else {  // if Itemid in non-sef URL
    $shItemidString = $sefConfig->shAlwaysInsertItemid ?
    _COM_SEF_SH_ALWAYS_INSERT_ITEMID_PREFIX.$sefConfig->replacement.$Itemid
    : '';
    if ($sefConfig->shAlwaysInsertMenuTitle){
      //global $Itemid; V 1.2.4.g we want the string option, not current page !
      if ($sefConfig->shDefaultMenuItemName)
      $title[] = $sefConfig->shDefaultMenuItemName;// V 1.2.4.q added force language
      elseif ($menuTitle = getMenuTitle($option, (isset($view) ? @$view : null), $Itemid, '',$shLangName )) {
        //echo 'Menutitle = '.$menuTitle.'<br />';
        if ($menuTitle != '/') $title[] = $menuTitle;
      }
    }
  }
  // V 1.2.4.m
  shRemoveFromGETVarsList('option');
  shRemoveFromGETVarsList('lang');
  if (!empty($Itemid))
  shRemoveFromGETVarsList('Itemid');
  if (!empty($limit))
  shRemoveFromGETVarsList('limit');
  if (isset($limitstart))
  shRemoveFromGETVarsList('limitstart');

  $view = isset($view) ? @$view : null;
  switch ($view) {
    case 'archivecategory':
    case 'archivesection' :
    case 'archive' :
      $dosef = false;
      break;
    case 'frontpage' :  // new in J 1.5 : we already know this is not a real homepage, as otherwise the test on
      // $shHomePageFlag would have been triggered. So this is a view=frontpage used on
      // another page than home. If so, use menu title as a link
      if (!empty($format) && $format == 'feed'){  // exception is rss feed
        $title[] = $format;
        if (!empty($type) && $format != $type)
        $title[] = $type;
      } else {
        $title[] = getMenuTitle($option, 'frontpage', $Itemid, '',$shLangName );
      }
      break;
    default:
      if (sh404SEF_PDF_DIR && $view == 'article' && !empty($format) && $format == 'pdf') {
        $title[] = sh404SEF_PDF_DIR;		// insert pdf directory
      }

      if ($view=='article' && !empty($layout) && $layout == 'form') { // submit new article
        $title[] = $sh_LANG[$shLangIso]['_COM_SEF_SH_CREATE_NEW'];
        if (!empty($sectionid)) {
          $q = 'SELECT id, title FROM #__sections WHERE id = '.$sectionid;
          $database->setQuery($q);
          if (shTranslateUrl($option, $shLangName)) // V 1.2.4.m
          $sectionTitle = $database->loadObject( );
          else $sectionTitle = $database->loadObject( false);
          if ($sectionTitle) {
            $title[] = $sectionTitle->title;
            shRemoveFromGETVarsList('sectionid');
          }
        }
      }
       
      // V 1.2.4.j 2007/04/11 : numerical ID, on some categories only
      if ($sefConfig->shInsertNumericalId && isset($sefConfig->shInsertNumericalIdCatList)
      && !empty($id) && ($view == 'article')) {

        $q = 'SELECT id, catid, created FROM #__content WHERE id = '.$id;
        $database->setQuery($q);
        if (shTranslateUrl($option, $shLangName)) // V 1.2.4.m
        $contentElement = $database->loadObject( );
        else $contentElement = $database->loadObject( false);
        if ($contentElement) {
          $foundCat = array_search($contentElement->catid, $sefConfig->shInsertNumericalIdCatList);
          if (($foundCat !== null && $foundCat !== false)
          || ($sefConfig->shInsertNumericalIdCatList[0] == ''))  { // test both in case PHP < 4.2.0
            $shTemp = explode(' ', $contentElement->created);
            $title[] = str_replace('-','', $shTemp[0]).$contentElement->id;
          }
        }
      }

      // V 1.2.4.k 2007/04/25 : if activated, insert edition id and name from iJoomla magazine
      if (!empty($ed) && $sefConfig->shActivateIJoomlaMagInContent && $id && ($view == 'article')) {
        $q = 'SELECT id, title FROM #__magazine_categories WHERE id = '.$ed;
        $database->setQuery($q);
        if (shTranslateUrl($option, $shLangName)) // V 1.2.4.m
        $issueName = $database->loadObject( false);
        else $issueName = $database->loadObject( );
        if ($issueName) {
          $title[] = ($sefConfig->shInsertIJoomlaMagIssueId ? $ed.$sefConfig->replacement:'')
          .$issueName->title;
        }
        shRemoveFromGETVarsList('ed');
      }
      // end of edition id insertion

      if (empty($layout) || (!empty($layout) && $layout != 'form')) {
        if (!empty($title)) {
          $title = array_merge($title, sef_404::getContentTitles((isset($view) ? @$view : null),(isset($id) ? @$id : null),
          (isset($layout) ? @$layout : null), (isset($Itemid) ? @$Itemid : null), $shLangName)); // V 1.2.4.q added forced language
        } else {
          $title = sef_404::getContentTitles((isset($view) ? @$view : null),(isset($id) ? @$id : null),
          (isset($layout) ? @$layout : null),(isset($Itemid) ? @$Itemid : null), $shLangName); // V 1.2.4.q added forced language
        }
        if (!empty($format) && $format == 'feed'){
          $title[] = $format;
          if (!empty($type) && $format != $type)
          $title[] = $type;
        }
        if (!empty($print))
        $title[] = JText::_( 'Print' );
      }

  }
  // V 1.2.4.q
  shRemoveFromGETVarsList('view');
  if (isset($id))
  shRemoveFromGETVarsList('id');
  if (isset($layout))
  shRemoveFromGETVarsList('layout');
  // only remove format variable if forma tis html. In all other situations, leave it there as some
  // system plugins may cause pdf and rss to break if they call JFactory::getDocument() in the onAfterInitialize event handler
  // because at this time SEF url are not decoded yet.
  if (isset($format) && (!sh404SEF_PROTECT_AGAINST_DOCUMENT_TYPE_ERROR || (sh404SEF_PROTECT_AGAINST_DOCUMENT_TYPE_ERROR && $format == 'html')))
  shRemoveFromGETVarsList('format');
  if (isset($type))
  shRemoveFromGETVarsList('type');
  if (!empty($sectionid))
  shRemoveFromGETVarsList('sectionid');  // V 1.2.4.m
  if (!empty($catid))
  shRemoveFromGETVarsList('catid');   // V 1.2.4.m
  if (isset($showall))
  shRemoveFromGETVarsList('showall');
  if (empty($page))  // remove page if not set or 0
  shRemoveFromGETVarsList('page');
  if (isset($print))
  shRemoveFromGETVarsList('print');
  if (isset($tmpl) && $tmpl == 'component')   // remove if 'component', show otherwise as querystring
  shRemoveFromGETVarsList('tmpl');

  // ------------------  standard plugin finalize function - don't change ---------------------------
  if ($dosef){
    $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString,
    (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null),
    (isset($shLangName) ? @$shLangName : null), (isset($showall) ? @$showall : null));
  }
  // ------------------  standard plugin finalize function - don't change ---------------------------
} else { // this is multipage homepage
  $title[] = '/';
  $string = sef_404::sefGetLocation( $string, $title, null, (isset($limit) ? @$limit : null),
  (isset($limitstart) ? @$limitstart : null), (isset($shLangName) ? @$shLangName : null),
  (isset($showall) ? @$showall : null));
}
?>
