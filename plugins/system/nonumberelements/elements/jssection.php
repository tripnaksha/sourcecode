<?php
/**
 * Element: JSSection
 * Displays a multiselectbox of available JoomSuite Resources categories
 *
 * @package    NoNumber! Elements
 * @version    1.0.2
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * JSSection Element
 */
class JElementJSSection extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'JSSection';

	function fetchElement( $name, $value, &$node, $control_name)
	{
		if ( !file_exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_resource'.DS.'models'.DS.'record.php' ) ) {
			return 'JoomSuite Resources files not found...';
		}

		$conf =& JFactory::getConfig();
		$dbprefix =	$conf->getValue( 'config.dbprefix' );

		$db =& JFactory::getDBO();
		$sql = "SHOW tables like '".$dbprefix."js_res_category'";
		$db->setQuery( $sql );
		$tables = $db->loadObjectList();

		if ( !count( $tables ) ) {
			return 'JoomSuite Resources category table not found in database...';
		}

		$multiple =			$node->attributes( 'multiple' );
		$get_categories =	$this->def( $node->attributes( 'getcategories' ), 1 );

		if ( !is_array( $value ) ) {
			$value = explode( ',', $value );
		}

		$where = 'published = 1';
		if ( !$get_categories ) {
			$where .= ' AND parent = 0';
		}

		$sql = "SELECT id, parent, name FROM #__js_res_category WHERE ".$where;
		$db->setQuery( $sql );
		$menuItems = $db->loadObjectList();

		// establish the hierarchy of the menu
		// TODO: use node model
		$children = array();

		if ( $menuItems)
		{
			// first pass - collect children
			foreach ( $menuItems as $v )
			{
				$pt =	$v->parent;
				$list =	@$children[$pt] ? $children[$pt] : array();
				array_push( $list, $v );
				$children[$pt] = $list;
			}
		}

		// second pass - get an indent list of the items
		$list = JHTML::_( 'menu.treerecurse', 0, '', array(), $children, 9999, 0, 0 );

		// assemble items to the array
		$options =	array();
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