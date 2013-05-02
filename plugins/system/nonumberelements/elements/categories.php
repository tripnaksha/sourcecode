<?php
/**
 * Element: Categories
 * Displays a selectbox of available categories (needs sections element)
 *
 * @package    NoNumber! Elements
 * @version    1.0.1
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Categories Element
 */
class JElementCategories extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Categories';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		global $CT_filter_sectionid;

		$db =& JFactory::getDBO();

		$count = 0;
		$script = '<script language="javascript" type="text/javascript">'."\n";
		$script .= 'var sectioncategories = new Array;'."\n";
		$script .= 'sectioncategories['.$count++.'] = new Array( "-1","-1","- '.JText::_( 'Select section' ).' -" );'."\n";
		$script .= 'sectioncategories['.$count++.'] = new Array( "0","0","'.JText::_( 'Uncategorized' ).'" );'."\n";

		$query = 'SELECT id, title FROM #__sections WHERE published = 1 AND scope = "content" ORDER BY ordering';
		$db->setQuery( $query );
		$sections = $db->loadObjectList();
		$sec_count = count( $sections );
		for ( $i = 0; $i < $sec_count; $i++ ) {
			$query = 'SELECT c.id, c.title'
				.' FROM #__categories AS c'
				.' LEFT JOIN #__sections AS s'
				.' ON s.id = c.section'
				.' WHERE c.published = 1'
				.' AND s.id = '.$sections[$i]->id
				.' ORDER BY c.ordering';
			$db->setQuery( $query );
			$categories = $db->loadObjectList();
			$cat_count = count( $categories );
			if ( $cat_count > 1 ) {
				$script .= 'sectioncategories['.$count++.'] = new Array( "'.$sections[$i]->id.'","-1","- '.JText::_( 'Select category' ).' -" );'."\n";
			}
			for ( $j = 0; $j < $cat_count; $j++ ) {
				$title = explode( "\n",wordwrap( $categories[$j]->title, 86, "\n" ) );;
				$title = $title['0'];
				$title = ( $title != $categories[$j]->title ) ? $title.'...' : $title;
				$script .= 'sectioncategories['.$count++.'] = new Array( "'.$sections[$i]->id.'","'.$categories[$j]->id.'","'.$title.'" );'."\n";
			}
		}
		$script .= '</script>';

		$categories = array();
		if ( $CT_filter_sectionid >= 0 ) {
			$filter = ' WHERE cc.section = '.$db->Quote( $CT_filter_sectionid );
			$query = 'SELECT cc.id AS value, cc.title AS text, section'
				.' FROM #__categories AS cc'
				.' INNER JOIN #__sections AS s ON s.id = cc.section'
				.' WHERE cc.section = '.$db->Quote( $CT_filter_sectionid )
				.' ORDER BY s.ordering, cc.ordering';
			$db->setQuery( $query );
			$cats = $db->loadObjectList();
			if ( count( $cats ) > 1 ) {
				$categories[] = JHTML::_( 'select.option', '-1', '- '.JText::_( 'Select category' ).' -' );
			}
			$categories = array_merge( $categories, $cats );
		} else {
			$categories[] = JHTML::_( 'select.option', '-1', '- '.JText::_( 'Select section' ).' -' );
		}
		return $script . JHTML::_( 'select.genericlist', $categories, $control_name.'['.$name.'][]', 'class="inputbox" size="1"', 'value', 'text', $value, $control_name.$name );
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}