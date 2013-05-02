<?php
/*
// JoomlaWorks "Disqus Comment System" Plugin for Joomla! 1.5.x - Version 2.1
// Copyright (c) 2006 - 2009 JoomlaWorks Ltd.
// Released under the GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
// More info at http://www.joomlaworks.gr
// Designed and developed by the JoomlaWorks team
// ***Last update: May 30th, 2009***
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Create a menu item selector
class JElementMenus extends JElement {

	var	$_name = 'menus';
	
	function fetchElement($name, $value, &$node, $control_name){
		
		$document =& JFactory::getDocument();
		$menus = array();
		
		// Create the 'all menus' listing
		$temp->value = '';
		$temp->text = JText::_("Select all menus");
		
		// Grab all the menus, grouped
		$menus = JHTML::_('menu.linkoptions');

		// Merge the above
		array_unshift($menus,$temp);

		// Output
		$output = JHTML::_('select.genericlist',  $menus, ''.$control_name.'['.$name.'][]', 'class="inputbox" style="width:90%;" multiple="multiple" size="12"', 'value', 'text', $value );
		
		return $output;
		
	}
	
} // end class
