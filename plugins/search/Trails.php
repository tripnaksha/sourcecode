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

$mainframe->registerEvent( 'onSearch', 'plgSearchTrails' );

/**
 * Trail Search method
 * The sql must return the following fields that are used in a common display
 * routine: href, title, section, created, text, browsernav
 * @param string Target search string
 * @param string mathcing option, exact|any|all
 * @param string ordering option, newest|oldest|popular|alpha|category
 * @param mixed An array if the search it to be restricted to areas, null if search all
 */
function plgSearchTrails( $text, $ordering='') //, $areas=null )
{
	global $mainframe;

	$db		=& JFactory::getDBO();
	$user	=& JFactory::getUser();
	$userid = $user->get('id');
	define("MAPS_HOST", "maps.google.com");

	$url = JFactory::getURI()->toString();
	if (strpos($url, ".com") !== false) {
	   $KEY = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQkUZHNJdizg5ywABG1vcOZLnlKKRQiK1QyIYbC7QJYSAvZi_ftqMywEg';
	}
	else if (strpos($url, ".in") !== false) {
	   $KEY = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBTGk7r6UUG2tv7pCXD49pEILMut2BSK1KyluFXiSlHDmPfgxKEcQu31zA';
	}
	else if (strpos($url, ".net") !== false) {
	   $KEY = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQVUtw86IlLILJmmHf_nHtc38TT4xQEwo2T9X3LZpg2rnfZGcOvR7jrgA';
	}
	else if (strpos($url, ".org") !== false) {
	   $KEY = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBRfh_bXew_S5fvS_f8On4C7bLoFWxSxwel7vDUce97Zyj7kn9hhDqcEhQ';
	}
	
	// Initialize delay in geocode speed
	$delay = 0;
	$base_url = "http://" . MAPS_HOST . "/maps/geo?output=xml" . "&key=" . $KEY;
	
	$searchText = $text;
	// load plugin params info
 	$plugin			=& JPluginHelper::getPlugin('search', 'trails');
 	$pluginParams	= new JParameter( $plugin->params );

	$limit 			= $pluginParams->def( 'search_limit', 50 );

	$nullDate 		= $db->getNullDate();
	$date =& JFactory::getDate();
	$now = $date->toMySQL();

	$text = trim( $text );
	if ($text == '') {
		return array();
	}

	$order = 'a.createTime DESC';
	$rows = array();
	$parts = '';

	if (strlen($text)>0)
	{
		$request_url = $base_url . "&q=" . urlencode($text);
		$xml = simplexml_load_file($request_url);// or die("url not loading");
	
		$status = $xml->Response->Status->code;

		if (strcmp($status, "200") == 0) {
		  // Successful geocode
		  $geocode_pending = false;
		  $coordinates = $xml->Response->Placemark->Point->coordinates;
		  $coordinatesSplit = split(",", $coordinates);

		  // Format: Longitude, Latitude, Altitude
		  $lat = $coordinatesSplit[1];
		  $lng = $coordinatesSplit[0];
		  
		  $southltd = $lat - 0.05;
		  $northltd = $lat + 0.05;
		  $southlng = $lng - 0.05;
		  $northlng = $lng + 0.05;
		  
		  if ($userid == 0)
		  {
			$part1 = "";
		  }
		  else
		  {
			$part1 = "SELECT DISTINCT b.id AS id, b.name, b.createTime" .
				" FROM jos_trailDetail AS a, jos_trailList AS b, jos_users AS c\n" .
				" WHERE b.name not like " . $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false ) . 
				" AND b.intro not like " . $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false ) .
				" AND a.Lng >= " . $southlng .
				" AND a.Lng <= " . $northlng .
				" AND a.Lat >= " . $southltd .
				" AND a.Lat <= " . $northltd .
				" AND a.Trail_ID = b.id" .
				" AND b.Private = 1" .
				" AND b.userId = " . $userid .
				" AND c.id = b.userId" .
				" UNION ALL ";
		  }

		  $part2 = "SELECT DISTINCT b.id AS id, b.name, b.createTime"  .
			" FROM jos_trailDetail AS a, jos_trailList AS b LEFT JOIN jos_users c \n" .
			" ON b.userId = c.id" .
			" WHERE b.name not like " . $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false ) . 
			" AND b.intro not like " . $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false ) .
			" AND a.Lng >= " . $southlng .
			" AND a.Lng <= " . $northlng .
			" AND a.Lat >= " . $southltd .
			" AND a.Lat <= " . $northltd .
			" AND a.Trail_ID = b.id" .
			" AND b.Private = 0" .
			" UNION ALL ";
		  $parts = $part1 . $part2;
		}
		else if (strcmp($status, "620") == 0) {
		  // sent geocodes too fast
		  $delay += 100000;
		} else {
		  // failure to geocode
		  #echo $text . " could not be found. ";
		  #echo "Received status " . $status . "\n";
		}
	}

	// search articles
	if ( $limit > 0 )
	{
		$query = 'SELECT a.id AS id, a.name, a.createTime'
		. ' FROM #__trailList AS a'
		. ' WHERE '
		. ' a.name like ' . $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false )
		. ' or a.intro like ' . $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false )
		. ' ORDER BY 3 DESC';
		
		$query = $parts . $query;

		$db->setQuery( $query, 0, $limit );
		$list = $db->loadObjectList();
		$limit -= count($list);

		if(isset($list))
		{
			foreach($list as $key => $item)
			{
//				$list[$key]->href = JURI::base() . 'index.php?option=com_traildisplay&Itemid=1&tview=' . $item->id . '&trailname=' . $item->name;
				$list[$key]->href = JRoute::_('index.php?option=com_routes&view=traildisplay&tview=' . $item->id . ':' . $item->name);
				$list[$key]->title =  $item->name . " - www.TripNaksha.com";
				$list[$key]->section = "Trail";
				$list[$key]->text = $item->name . " - Trail mapped on TripNaksha." ;
				$list[$key]->created = $item->createTime;
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
//				if(searchHelper::checkNoHTML($article, $searchText, array('text', 'title', 'metadesc', 'metakey')))
{
					$new_row[] = $article;
				}
			}
			$results = array_merge($results, (array) $new_row);
		}
	}

	return $results;
}
