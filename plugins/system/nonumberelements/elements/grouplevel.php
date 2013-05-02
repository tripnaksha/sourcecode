<?php
/**
 * Element: Group Level
 * Displays a select box of backend group levels
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
 * Group Level Element
 *
 * Available extra parameters:
 * root				The user group to use as root (default = USERS)
 * showroot			Show the root in the list
 * multiple			Multiple options can be selected
 * notregistered	Add an option for 'Not Registered' users
 */
class JElementGroupLevel extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'GroupLevel';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		$root =				$this->def( $node->attributes( 'root' ), 'USERS' );
		$showroot =			$node->attributes( 'showroot' );
		if ( strtoupper( $root ) == 'USERS' && $showroot == '' ) { $showroot = 0; }
		$multiple =			$node->attributes( 'multiple' );
		$notregistered =	$node->attributes( 'notregistered' );

		$control = $control_name.'['.$name.']';
		$attribs = 'class="inputbox"';

		$acl		=& JFactory::getACL();
		$options =	$acl->get_group_children_tree( null, $root, $showroot );
		if ( $notregistered ) {
			$no_user =			'';
			$no_user->value =	0;
			$no_user->text =		'&nbsp; '.JText::_( 'Not Registered' );
			$no_user->disable =	'';
			array_unshift( $options, $no_user );
		}

		if ( $multiple ) {
			if( !is_array( $value ) ) {
				$value = explode( ',', $value );
			}

			$attribs .= ' multiple="multiple"';
			$control .= '[]';

			if ( in_array( 29, $value ) ) {
				$value[] = 18;
				$value[] = 19;
				$value[] = 20;
				$value[] = 21;
			}
			if ( in_array( 30, $value ) ) {
				$value[] = 23;
				$value[] = 24;
				$value[] = 25;
			}
		}

		return JHTML::_( 'select.genericlist', $options, $control, $attribs, 'value', 'text', $value, $control_name.$name );
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}