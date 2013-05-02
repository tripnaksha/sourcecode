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

class modAnnounceHelper
{
	function getList(&$params)
	{
		global $mainframe;

		$db		=& JFactory::getDBO();

		$count		= (int) $params->get('count', 5);

		$query = 'SELECT a.* ' .
			' FROM #__announce AS a' .
			' WHERE active = 1' .
			' ORDER BY createTime DESC';
		$db->setQuery($query, 0, $count);
		$rows = $db->loadObjectList();

		$i		= 0;
		$lists	= array();
		foreach ( $rows as $row )
		{
			$lists[$i]->link = $row->url;
			$lists[$i]->text = $row->text;
			$i++;
		}

		return $lists;
	}
}