<?php
/**
 * sh404SEF support for Myblog component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * {shSourceVersionTag: Version x - 2007-09-20}
 * @version     $Id: com_myblog.php 866 2009-01-17 14:05:21Z silianacom-svn $
 * Based on Azrul.com extension for Artio Joomsef component
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
$shLangIso = shLoadPluginLanguage( 'com_myblog', $shLangIso, '_SH404SEF_MYBLOG_VIEW_BY_TAG');
// ------------------  load language file - adjust as needed ----------------------------------------

if (!function_exists('shFetchTagId')) {
  function shFetchTagId($catName, $option, $shLangName) {
    if (empty($catName)) return null;
    // get DB
    $database =& JFactory::getDBO();

    $sql = 'SELECT id from #__myblog_categories WHERE name = \''.$catName.'\'';
    $database->setQuery($sql);
    if (shTranslateUrl($option, $shLangName))
    $catId = $database->loadResult();
    else $catId = $database->loadResult( false);
    return isset($catId) ? $catId : '';
  }
}

if (!function_exists('shFetchUserId')) {
  function shFetchUserId( $blogger) {
    if (empty($blogger)) return null;
    // get DB
    $database =& JFactory::getDBO();
    $sql = 'SELECT id from #__users WHERE username = \''.$blogger.'\'';
    $database->setQuery($sql);
    $userId = $database->loadResult();
    return isset($userId) ? $userId : '';
  }
}

if (!function_exists('shFetchPostId')) {
  function shFetchPostId( $show, $option, $shLangName) {
    if (empty($show)) return null;
    // get DB
    $database =& JFactory::getDBO();
    $sql = 'SELECT contentid from #__myblog_permalinks WHERE permalink = \''.$show.'\'';
    $database->setQuery($sql);
    if (shTranslateUrl($option, $shLangName))
    $postId = $database->loadResult();
    else $postId = $database->loadResult( false);
    return isset($postId) ? $postId : '';
  }
}

//echo 'string = '.$string.'<br />';
// shumisha : insert component name from menu
$shMyBlogName = shGetComponentPrefix($option);
$shMyBlogName = empty($shMyBlogName) ?  getMenuTitle($option, null, @$Itemid, null, $shLangName ) : $shMyBlogName;
$shMyBlogName = $shMyBlogName == '/' ? 'myBlog':$shMyBlogName; // V 1.2.4.t

if ($sefConfig->shInsertMyBlogName && !empty($shMyBlogName)) $title[] = $shMyBlogName;

if (isset($blogger)) { // blogger url rewrite
  if ($sefConfig->shMyBlogInsertBloggerId) {
    $userId = shFetchUserId($blogger);
    $title[] = (!empty($userId) ? $userId.$sefConfig->replacement:'').$blogger; //append blogger name to url.
  } else  $title[] = $blogger;
  $title[] = "/";
  shRemoveFromGETVarsList('blogger');
}

if (isset($archive)) { // archive url rewrite
  $archive_arr = split("-", $archive);
  $title[] = $archive_arr[1]; //append 'Year'
  $title[] = "/";
  $title[] = $archive_arr[0]; //append 'Month'
  $title[] = "/";
  shRemoveFromGETVarsList('archive');
}

if (isset($category)) { // category url rewrite
  $title[] = $sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_VIEW_BY_TAG'];
  if ($sefConfig->shMyBlogInsertTagId) {
    $catId = shFetchTagId($category, $option, $shLangName);
    $title[] = (empty($catId) ? '':$catId.$sefConfig->replacement).$category; // append category name to url.
  } else  $title[] = $category;
  $title[] = "/";
  shRemoveFromGETVarsList('category');
}

if (!empty($admin) && !empty($lightbox)) {
  $title[] = $sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_VIEW_DASHBOARD'];
  shRemoveFromGETVarsList('admin');
  shRemoveFromGETVarsList('lightbox');
}

if (isset($show)) { // show parameter url rewrite.
  if (substr($show, strlen($show)-5)==".html")
  $show = substr($show, 0, strlen($show)-5);
  if ($sefConfig->shMyBlogInsertPostId) {
    $postId = shFetchPostId($show.'.html', $option, $shLangName);
    $title[] = (empty($postId) ? '':$postId.$sefConfig->replacement).$show; // append permalink to the url
  } else $title[] = $show;
  shRemoveFromGETVarsList('show');
} else if (isset($id)){ // view parameter rewrite
  // get DB
  $database =& JFactory::getDBO();
  $database->setQuery('SELECT p.permalink from #__myblog_permalinks as p WHERE p.contentid=\''.$id.'\'');
  $row = $database->loadResult();
  if ($row) {
    $tmp = $row;
    if (substr($tmp, strlen($tmp)-5)==".html")
    $tmp = substr($tmp, 0, strlen($tmp)-5);
    if ($sefConfig->shMyBlogInsertPostId) {
      $title[] = (isset($id) ? '':$id.$sefConfig->replacement).$tmp;
    } else $title[] = $tmp;
    shRemoveFromGETVarsList('id');
  }
}

$task = isset($task) ? @$task : null;

switch ($task) {
  case 'view':
    $title[]=$sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_VIEW'];
    break;
  case 'userblog':
    $title[]=$sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_USERBLOG'];
    break;
  case 'blogs':
    $title[]= $sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_VIEW_ALL_BLOGS'];
    break;
  case 'categories':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_VIEW_ITEMS_BY_TAG'];
    break;
  case 'search':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_SEARCH_BLOG'];
    break;
  case 'rss':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_SUBSCRIBE_RSS'];
    break;
    // fix for new version of myblog dashboard provided by ianrispin - march 2008
  case 'bloggerpref':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_BLOGGER_PREFERENCES'];
    break;
  case 'bloggerstats':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_BLOGGER_STATS'];
    break;
  case 'showcomments':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_SHOW_COMMENTS'];
    break;
  case 'delete':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_DELETE_BLOG'];
    break;
  case 'adminhome':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_MANAGE_MY_OWN_BLOG'];
    break;
  case 'ajaxupload':
    $title[] = $sh_LANG[$shLangIso]['_SH404SEF_MYBLOG_IMAGE_UPLOAD'];
    break;
}

if (empty($title)) $title[] = $shMyBlogName;

/* sh404SEF extension plugin : remove vars we have used, adjust as needed --*/
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');
if (isset($Itemid))
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
