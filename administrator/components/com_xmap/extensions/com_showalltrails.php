<?php
/**
 * $Id: com_eventlist.php 47 2009-08-02 02:47:16Z guilleva $
 * $LastChangedDate: 2008-03-17 14:05:37 -0600 (lun, 17 mar 2008) $
 * $LastChangedBy: root $
s * Xmap by Guillermo Vargas
 * a sitemap component for Joomla! CMS (http://www.joomla.org)
 * Author Website: http://joomla.vargas.co.cr
 * Project License: GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

/*
 * Handles EventList Category structure
 */
class xmap_com_showalltrails
{
	/*
	 * Return Category tree
	 */
	function getTree( &$xmap, &$parent, $params )
	{
		$db = &JFactory::getDBO();
		$gid = intval($xmap->gid);

		$query = "SELECT id , name"
				. "\nFROM #__trailList"
				. "\nWHERE private = 0";
		$db->setQuery($query);
		$cats = $db->loadObjectList();

	 	$xmap->changeLevel(1);
		foreach($cats as $cat)
		{
			$node = new stdclass;
			$node->id   = $parent->id;
			$node->uid   = $parent->uid.'c'.$cat->id;
		   	$node->name = str_replace(" ", "-", $cat->name);
			$node->link = 'index.php?option=com_routes&view=traildisplay&amp;tview='.$cat->id.':'.$cat->name;
			$node->expandible = true;
			$xmap->printNode($node);
	    	}
		$xmap->changeLevel(-1);


	}
}
?>
