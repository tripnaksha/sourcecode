<?php
/**
 * Element: Components
 * Displays a list of components with check boxes
 *
 * @package    NoNumber! Elements
 * @version    1.0.2
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

 /**
 * Components Element
 */
class JElementComponents extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Components';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		$frontend =		$this->def( $node->attributes( 'frontend' ), 1 );
		$admin =		$this->def( $node->attributes( 'admin' ), 1 );
		$show_content =	$this->def( $node->attributes( 'show_content' ), 0 );

		$components = JElementComponents::getComponents( $frontend, $admin, $show_content );

		// place a dummy hidden checkbox item in the list, to be able to deselect all (and still have a default)
		$list = "\n".'<input type="hidden" id="'.$control_name.$name.'x" name="'.$control_name.'['.$name.']'.'[]" value="x" checked="checked" />';
		if ( count( $components ) ) {
			foreach ( $components as $component ) {
				if ( ! is_array( $value ) ) $value = explode( ',', $value );
				$checked = ( in_array( $component->option, $value ) ) ? ' checked="checked"' : '';
				$list .= "\n".'<input type="checkbox" id="'.$control_name.$name.$component->option.'" name="'.$control_name.'['.$name.']'.'[]" value="'.$component->option.'"'.$checked.' />';
				$list .= $component->name.'<br />';
			}
		} else {
			$list .= JText::_( 'Component Not Found' );
		}

		return $list;
	}

	function getComponents( $frontend = 1, $admin = 1, $show_content = 0 )
	{
		$db   =& JFactory::getDBO();

		if ( !$frontend && !$admin ) {
			$query = 'SELECT '.$db->NameQuote( 'option' ).', name'
				.' FROM #__components'
				.' WHERE enabled = 1'
				.' AND parent = 0'
				;
			if ( !$show_content ) {
				$query .= ' AND '.$db->NameQuote( 'option' ).' <> "com_content"';
			}
			$query .= ' ORDER BY name';
			$db->setQuery( $query );
			$components = $db->loadObjectList();
		} else {
			if ( $frontend ) {
				// component subs
				$query = 'SELECT parent'
					.' FROM #__components'
					.' WHERE enabled = 1'
					.' AND parent != 0';
					' AND link != ""';
					' ORDER BY ordering, name'
					;
				$db->setQuery( $query );
				$subcomponents = $db->loadResultArray();
				$subcomponents = array_unique( $subcomponents );

				// main components
				$query = 'SELECT id'
					.' FROM #__components'
					.' WHERE enabled = 1'
					.' AND parent = 0'
					.' AND ( link != ""'
					;
					if ( count( $subcomponents ) ) {
						$query .= ' OR id IN ( '.implode( ',', $subcomponents ).' )';
					}
				$query .= ' )';
				$query .= ' ORDER BY ordering, name';
				$db->setQuery( $query );
				$component_ids = $db->loadResultArray();
			}

			if ( $admin ) {
				// component subs
				$query = 'SELECT parent'
					.' FROM #__components'
					.' WHERE enabled = 1'
					.' AND parent != 0'
					.' AND admin_menu_link != ""'
					;
				$db->setQuery( $query );
				$subcomponents = $db->loadResultArray();
				$subcomponents = array_unique( $subcomponents );

				// main components
				$query = 'SELECT id'
					.' FROM #__components'
					.' WHERE enabled = 1'
					.' AND parent = 0'
					.' AND ( admin_menu_link != ""'
					;
					if ( count( $subcomponents ) ) {
						$query .= ' OR id IN ( '.implode( ',', $subcomponents ).' )';
					}
				$query .= ' )';
				$db->setQuery( $query );
				if ( $frontend ) {
					$component_ids = array_intersect( $component_ids, $db->loadResultArray() );
				} else {
					$component_ids = $db->loadResultArray();
				}
			}

			$component_ids = array_unique( $component_ids );
			$query = 'SELECT '.$db->NameQuote( 'option' ).', name'
				.' FROM #__components'
				.' WHERE enabled = 1'
				.' AND parent = 0'
				;
				if ( count( $component_ids ) ) {
					$query .= ' AND id IN ( '.implode( ',', $component_ids ).' )';
				}
				if ( !$show_content ) {
					$query .= ' AND '.$db->NameQuote( 'option' ).' <> "com_content"';
				}
			$query .= ' ORDER BY name';
			$db->setQuery( $query );
			$components = $db->loadObjectList();
		}

		return $components;
	}

	function getComponentsArray( $frontend = 1, $admin = 1, $show_content = 0 )
	{
		$components = JElementComponents::getComponents( $frontend, $admin, $show_content );
		$components = array();
		foreach ( $components as $component ) {
			$components[] = $component->option;
		}
		return $components;
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}