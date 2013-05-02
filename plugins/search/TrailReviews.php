<?php
/**
 * @version		$Id: content.php 10860 2008-08-30 06:45:31Z willebil $
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onSearch', 'plgSearchTrailReviews' );
//$mainframe->registerEvent( 'onSearchAreas', 'plgSearchTrailReviewAreas' );

//JPlugin::loadLanguage( 'plg_search_content' );

/**
 * @return array An array of search areas
 */
/*
 *
 function &plgSearchTrailReviewAreas()
{
	static $areas = array(
		'TrailReviews' => 'TrailReview'
	);
	return $areas;
}
*/
/**
 * TrailReview Search method
 * The sql must return the following fields that are used in a common display
 * routine: href, title, section, created, text, browsernav
 * @param string Target search string
 * @param string mathcing option, exact|any|all
 * @param string ordering option, newest|oldest|popular|alpha|category
 * @param mixed An array if the search it to be restricted to areas, null if search all
 */
function plgSearchTrailReview( $text, $ordering='') //, $areas=null )
{
	global $mainframe;

	$db		=& JFactory::getDBO();
	$user	=& JFactory::getUser();

//	require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
	require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_searchtrailreviews'.DS.'helpers'.DS.'search.php');

	$searchText = $text;
/*	if (is_array( $areas )) {
		if (!array_intersect( $areas, array_keys( plgSearchTrailReviewAreas() ) )) {
			return array();
		}
	}
*/
	// load plugin params info
 	$plugin			=& JPluginHelper::getPlugin('search', 'content');
 	$pluginParams	= new JParameter( $plugin->params );

	$limit 			= $pluginParams->def( 'search_limit', 		50 );

	$nullDate 		= $db->getNullDate();
	$date =& JFactory::getDate();
	$now = $date->toMySQL();

	$text = trim( $text );
	if ($text == '') {
		return array();
	}

	$wheres = array();
	$text		= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
die($text);
	$wheres2 	= array();
	$wheres2[] 	= 'a.title LIKE '.$text;
	$wheres2[] 	= 'a.introtext LIKE '.$text;
	$wheres2[] 	= 'a.`fulltext` LIKE '.$text;
	$wheres2[] 	= 'a.metakey LIKE '.$text;
	$wheres2[] 	= 'a.metadesc LIKE '.$text;
	$where 		= '(' . implode( ') OR (', $wheres2 ) . ')';

	$morder = '';
	switch ($ordering) {
		case 'oldest':
			$order = 'a.created ASC';
			break;

		case 'popular':
			$order = 'a.hits DESC';
			break;

		case 'alpha':
			$order = 'a.title ASC';
			break;

		case 'category':
			$order = 'b.title ASC, a.title ASC';
			$morder = 'a.title ASC';
			break;

		case 'newest':
			default:
			$order = 'a.created DESC';
			break;
	}

	$rows = array();

	// search articles
	if ( $limit > 0 )
	{
		$query = 'SELECT a.title AS title, a.metadesc, a.metakey,'
		. ' a.created AS created,'
		. ' CONCAT(a.introtext, a.`fulltext`) AS text,'
		. ' CONCAT_WS( "/", u.title, b.title ) AS section,'
		. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'
		. ' CASE WHEN CHAR_LENGTH(b.alias) THEN CONCAT_WS(":", b.id, b.alias) ELSE b.id END as catslug,'
		. ' u.id AS sectionid,'
		. ' "2" AS browsernav'
		. ' FROM #__content AS a'
		. ' INNER JOIN #__categories AS b ON b.id=a.catid'
		. ' INNER JOIN #__sections AS u ON u.id = a.sectionid'
		. ' WHERE ( '.$where.' )'
		. ' AND a.state = 1'
		. ' AND u.published = 1'
		. ' AND b.published = 1'
		. ' AND a.access <= '.(int) $user->get( 'aid' )
		. ' AND b.access <= '.(int) $user->get( 'aid' )
		. ' AND u.access <= '.(int) $user->get( 'aid' )
		. ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
		. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'
		. ' GROUP BY a.id'
		. ' ORDER BY '. $order
		;
		$db->setQuery( $query, 0, $limit );
		$list = $db->loadObjectList();
		$limit -= count($list);

		if(isset($list))
		{
			foreach($list as $key => $item)
			{
				$list[$key]->href = ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid);
			}
		}
		$rows[] = $list;
	}

	$results = array();
	if(count($rows))
	{
		foreach($rows as $row)
		{
			$new_row = array();
			foreach($row AS $key => $article) {
				if(searchHelper::checkNoHTML($article, $searchText, array('text', 'title', 'metadesc', 'metakey'))) {
					$new_row[] = $article;
				}
			}
			$results = array_merge($results, (array) $new_row);
		}
	}

	return $results;
}
