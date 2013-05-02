<?php
/**
* @version		$Id: helper.php 10857 2008-08-30 06:41:16Z willebil $
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
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

class modLatestTrailsHelper
{
	function getList(&$params)
	{
		global $mainframe;

		$db		=& JFactory::getDBO();

		$count		= (int) $params->get('count', 5);

		$query = 'SELECT a.* ' .
			' FROM #__trailList AS a' .
			' WHERE private = 0' .
			' ORDER BY createTime DESC';
		$db->setQuery($query, 0, $count);
		$rows = $db->loadObjectList();

		$i		= 0;
		$lists	= array();
		foreach ( $rows as $row )
		{
			//$url = JURI::base() . 'index.php?option=com_traildisplay&tview=' . $row->id;
//			$url = JURI::base() . 'index.php?option=com_traildisplay&Itemid=1&tview=' . $row->id . '&trailname=' . $row->name; //str_replace(" ", "-", $row->name);
			$temp = str_replace(" ", "-", $row->name);
			$url = JRoute::_('index.php?option=com_routes&view=traildisplay&tview=' . $row->id . ':' . $temp);
//			$url = JRoute::_('index.php?option=com_routes&view=traildisplay&tview=' . $row->id . ':' . $row->name);
//			$url = JRoute::_('index.php?option=com_routes&view=traildisplay&tview=' . $row->id . ':' . str_replace(" ", "-", $row->name);
			$lists[$i]->link = $url;
			$lists[$i]->text = htmlspecialchars( $row->name );
			$i++;
		}

		return $lists;
	}
}
