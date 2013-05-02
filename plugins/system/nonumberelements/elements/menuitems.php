<?php
/**
 * Element: MenuItems
 * Display a menuitem field with a button
 *
 * @package    NoNumber! Elements
 * @version    1.0.4
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * MenuItems Element
 */
class JElementMenuItems extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'MenuItems';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		JHTML::_( 'behavior.modal', 'a.modal' );

		$size		= $node->attributes( 'size' );
		$multiple	= $this->def( $node->attributes( 'multiple'), 1 );
		$showinput	= $node->attributes( 'showinput' );
		$state		= $node->attributes( 'state' );
		$disable	= $node->attributes( 'disable' );

		$db =& JFactory::getDBO();

		// load the list of menu types
		$query = 'SELECT menutype, title' .
				' FROM #__menu_types' .
				' ORDER BY title';
		$db->setQuery( $query );
		$menuTypes = $db->loadObjectList();

		// load the list of menu items
		$where = '';
		if ( $state != '' ) {
			$where = 'WHERE published = '.(int) $state;
		}
		$query = 'SELECT id, parent, name, menutype, type' .
				' FROM #__menu' .
				$where .
				' ORDER BY menutype, parent, ordering'
				;

		$db->setQuery($query);
		$menuItems = $db->loadObjectList();

		// establish the hierarchy of the menu
		$children = array();

		if ($menuItems)
		{
			// first pass - collect children
			foreach ($menuItems as $v)
			{
				$pt 	= $v->parent;
				$list 	= @$children[$pt] ? $children[$pt] : array();
				array_push( $list, $v );
				$children[$pt] = $list;
			}
		}

		// second pass - get an indent list of the items
		$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0 );

		// assemble into menutype groups
		$n = count( $list );
		$groupedList = array();
		foreach ($list as $k => $v) {
			$groupedList[$v->menutype][] = &$list[$k];
		}

		// assemble menu items to the array
		$options 	= array();

		foreach ($menuTypes as $count => $type)
		{
			if ( $count > 0 ) {
				$options[]	= JHTML::_('select.option',  '0', '&nbsp;', 'value', 'text', true);
			}
			$options[]	= JHTML::_('select.option',  $type->menutype, $type->title . ' - ' . JText::_( 'Top' ), 'value', 'text', true );
			if (isset( $groupedList[$type->menutype] ))
			{
				$n = count( $groupedList[$type->menutype] );
				for ($i = 0; $i < $n; $i++)
				{
					$item = &$groupedList[$type->menutype][$i];

					//If menutype is changed but item is not saved yet, use the new type in the list
					if ( JRequest::getString('option', '', 'get') == 'com_menus' ) {
						$currentItemArray = JRequest::getVar('cid', array(0), '', 'array');
						$currentItemId = (int) $currentItemArray['0'];
						$currentItemType = JRequest::getString('type', $item->type, 'get');
						if ( $currentItemId == $item->id && $currentItemType != $item->type) {
							$item->type = $currentItemType;
						}
					}

					$disable = strpos( $disable, $item->type ) !== false ? true : false;
					$item_name = '&nbsp;&nbsp;&nbsp;'.$item->treename;
					if ( $showinput) {
						$item_name .= ' ['.$item->id.']';
					}
					$options[] = JHTML::_('select.option',  $item->id, $item_name, 'value', 'text', $disable );

				}
			}
		}

		$attribs = 'class="inputbox"';

		if ( $showinput) {
			array_unshift( $options,JHTML::_('select.option',  '0', '&nbsp;', 'value', 'text', true) );
			array_unshift( $options, JHTML::_('select.option', '', '- '.JText::_('Select Item').' -') );

			if( $multiple ) {
				$onchange = 'if ( this.value ) { if ( '.$control_name.$name.'.value ) { '.$control_name.$name.'.value+=\',\'; } '.$control_name.$name.'.value+=this.value; } this.value=\'\';';
			} else {
				$onchange = 'if ( this.value ) { '.$control_name.$name.'.value=this.value;'.$control_name.$name.'_text.value=this.options[this.selectedIndex].innerHTML.replace( /^((&|&amp;|&#160;)nbsp;|-)*/gm, \'\' ).trim(); } this.value=\'\';';
			}
			$attribs .= ' onchange="'.$onchange.'"';

			$html 		= '<table cellpadding="0" cellspacing="0"><tr><td style="padding: 0px;">'."\n";
			if( !$multiple ) {
				$value_name = $value;
				if ( $value ) {
					foreach ( $menuItems as $item ) {
						if ( $item->id == $value ) {
							$value_name = $item->name.' ['.$value.']';;
							break;
						}
					}
				}
				$html 	.= '<input type="text" id="'.$control_name.$name.'_text" value="'.$value_name.'" class="inputbox" size="'.$size.'" disabled="disabled" />';
				$html 	.= '<input type="hidden" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="'.$value.'" />';
			} else {
				$html 	.= '<input type="text" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="'.$value.'" class="inputbox" size="'.$size.'" />';
			}
			$html 		.= '</td><td style="padding: 0px;"padding-left: 5px;>'."\n";
			$html 		.= JHTML::_('select.genericlist',  $options, '', $attribs, 'value', 'text', '', '');
			$html 		.= '</td></tr></table>'."\n";
		} else {
			if( $size ) {
				$attribs .= ' size="'.$size.'"';
			}else {
				$attribs .= ' size="'.( ( count( $options) > 10 ) ? 10 : count( $options) ).'"';
			}
			if( $multiple ) $attribs .= ' multiple';

			$html = JHTML::_( 'select.genericlist', $options, ''.$control_name.'['.$name.'][]', $attribs, 'value', 'text', $value, $control_name.$name );
		}
		return $html;
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}