<?php
/**
 * Element: Sections
 * Displays a selectbox of available sections
 *
 * @package    NoNumber! Elements
 * @version    1.0.1
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Sections Element
 */
class JElementSections extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Sections';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		global $CT_filter_sectionid;
		$CT_filter_sectionid = $value;

		$category = $node->attributes( 'category', 'category' );

		$db =& JFactory::getDBO();

		$query = 'SELECT id, title FROM #__sections WHERE published = 1 AND scope = "content" ORDER BY ordering';
		$db->setQuery( $query );
		$sections = $db->loadObjectList();
		for ( $i = 0; $i < count( $sections ); $i++ ) {
			$title = explode( "\n",wordwrap( $sections[$i]->title, 86, "\n" ) );;
			$title = $title['0'];
			$title = ( $title != $sections[$i]->title ) ? $title.'...' : $title;
			$sections[$i]->title = $title;
		}

		array_unshift( $sections, JHTML::_( 'select.option', '0', JText::_( 'Uncategorized' ), 'id', 'title' ) );
		array_unshift( $sections, JHTML::_( 'select.option', '-1', '- '.JText::_( 'Select section' ).' -', 'id', 'title' ) );

		$onchange = ' onchange="changeDynaList( \''.$control_name.$category.'\', sectioncategories, document.adminForm.'.$control_name.$name.'.options[document.adminForm.'.$control_name.$name.'.selectedIndex].value, 0, 0 );";';

		return JHTML::_( 'select.genericlist',  $sections, ''.$control_name.'['.$name.'][]', $onchange.' class="inputbox" size="1"', 'id', 'title', $value, $control_name.$name );
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}