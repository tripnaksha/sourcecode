<?php
/**
 * Element: Sections / Categories
 * Displays a (multiple) selectbox of available sections and categories
 *
 * @package    NoNumber! Elements
 * @version    1.0.3
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Sections / Categories Element
 */
class JElementSecsCats extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'SecsCats';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		$multiple =				$node->attributes( 'multiple' );
		$show_uncategorized =	$node->attributes( 'show_uncategorized' );

		$db =& JFactory::getDBO();

		if ( !is_array( $value ) ) {
			$value = explode( ',', $value );
		}

		$query = 'SELECT id, 0 as parent, title as name FROM #__sections WHERE published = 1 AND scope = "content" ORDER BY ordering';
		$db->setQuery( $query );
		$sections = $db->loadObjectList();
		for ( $i = 0; $i < count( $sections ); $i++ ) {
			$sec_name = explode( "\n",wordwrap( $sections[$i]->name, 86, "\n" ) );;
			$sec_name = $sec_name['0'];
			$sec_name = ( $sec_name != $sections[$i]->name ) ? $sec_name.'...' : $sec_name;
			$sections[$i]->title = $sec_name;
		}

		$children = array();
		$children[] = $sections;
		foreach ( $sections as $section ) {
			$query = 'SELECT CONCAT( '.$section->id.', ".", id ) as id, section as parent, title as name'
				.' FROM #__categories'
				.' WHERE published = 1'
				.' AND section = '.$section->id
				.' ORDER BY ordering';
			$db->setQuery( $query );
			$categories = $db->loadObjectList();
			for ( $i = 0; $i < count( $categories ); $i++ ) {
				$cat_name = explode( "\n",wordwrap( $categories[$i]->name, 86, "\n" ) );;
				$cat_name = $cat_name['0'];
				$cat_name = ( $cat_name != $categories[$i]->name ) ? $cat_name.'...' : $cat_name;
				$categories[$i]->name = $cat_name;
				if ( in_array( $section->id, $value ) ) {
					$value[] = $categories[$i]->id;
				}
			}
			$children[$section->id] = $categories;
		}

		// second pass - get an indent list of the items
		$list = JHTML::_( 'menu.treerecurse', 0, '', array(), $children, 9999, 0, 0 );

		// assemble items to the array
		$options =	array();
		if ( $show_uncategorized ) {
			$options[] = JHTML::_( 'select.option', '0', JText::_('Uncategorized'), 'value', 'text', 0 );
		}
		foreach ( $list as $item )
		{
			$options[] = JHTML::_( 'select.option', $item->id, $item->treename, 'value', 'text', 0 );
		}

		$attribs = 'class="inputbox"';
		$attribs .= ' size="'.( ( count( $options) > 10 ) ? 10 : count( $options) ).'"';
		if( $multiple ) $attribs .= ' multiple';

		return JHTML::_( 'select.genericlist', $options, ''.$control_name.'['.$name.'][]', $attribs, 'value', 'text', $value, $control_name.$name );
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}