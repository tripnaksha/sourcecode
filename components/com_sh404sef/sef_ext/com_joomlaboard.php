<?php
/**
 * SEF module for Joomla!
 * Originally written for Mambo as 404SEF by W. H. Welch.
 *
 * @author      $Author: shumisha $
 * @copyright   Yannick Gaultier - 2007
 * @package     sh404SEF-15
 * @version     $Name$, ($Revision: 4994 $, $Date: 2005-11-03 20:50:05 +0100 (??t, 03 XI 2005) $)
 */

// Security check to ensure this file is being included by a parent file.
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

/**
* Toevoegen:
* viewforum en viewtopic weghalen?
* support voor: index.php?option=com_forum&Itemid=26&c=1
**/
extract($vars);

$catRewrite = true;
$msgRewrite = true;

if ($msgRewrite || $catRewrite) {
	if (file_exists(sh404SEF_ABS_PATH.'components/com_joomlaboard/forum.conf')) {
		require_once(sh404SEF_ABS_PATH.'components/com_joomlaboard/forum.conf');
	}

	global $message_cat_table_suffix, $message_table_suffix;

	if ($catRewrite && !empty($catid)) {
		$query = "
		SELECT `name`
		FROM `#__$message_cat_table_suffix`
		WHERE `id` = $catid
		";
		$database->setQuery($query);
		$catTitle = $database->loadResult();
	}
	if (isset($id)) $msgID = $id;
	elseif (isset($replyto)) $msgID = $replyto;
	else $msgID = null;
	if ($msgRewrite && !empty($msgID)) {
		$query = "
		SELECT `subject`
		FROM `#__$message_table_suffix`
		WHERE `id` = $msgID
		";
		$database->setQuery($query);
		$msgTitle = $database->loadResult();
	}
}

if (empty($task) && isset($func)) {
    $task = $func;
    unset($vars['func']);
    unset($func);
}

// First subdir
if (!empty($option)) {
    $title[] = getMenuTitle($option, $task, $Itemid);
	/*$title[] = getMenuTitle($option, @$this_task);
	$title[] = '/';
	unset($vars['option']);
	if (empty($title)) {
		$comp_name = str_replace('com_', '', $option);
		$title = $comp_name;
	}*/
}

// Page
/*if (!empty($page)) {
	$title[] = $page;
	$title[] = '/';
	unset($vars['page']);
}

// Mode
if (!empty($mode)) {
	$title[] = $mode;
	$title[] = '/';
	unset($vars['mode']);
}

// Search
if (!empty($search_id)) {
	$title[] = $search_id;
	$title[] = '/';
	unset($vars['search_id']);
}*/

// Category
if (!empty($catTitle)) {
	$title[] = $catTitle;
	unset($vars['catid']);
}
/*
// User
if (!empty($u)) {
	$title[] = $u . $sefConfig->suffix;
	unset($vars['u']);
}

// Forum
if (!empty($f)) {
	$title[] = $f;
	unset($vars['f']);
}
*/

// Topic
if (!empty($msgTitle)) {
	$title[] = (!isset($do) && !isset($func)) ? $msgTitle.$sefConfig->suffix : $msgTitle;
	unset($vars['id']);
}

// Func and do
if (isset($do) || isset($func)) {
    if (isset($func)) $oper[] = $func;
    if (isset($do))   $oper[] = $do;
    $title[] = join('-', $oper).$sefConfig->suffix;
}

/*
// Mark
if (!empty($mark)) {
	$title[] = 'mark';
	$title[] = '/';
	$title[] = $mark . $sefConfig->suffix;
	unset($vars['mark']);
}
*/

if (count($title) > 0) {
    $string = sef_404::sefGetLocation($string, $title, $task, (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), (isset($lang) ? @$lang : null));
}

?>
