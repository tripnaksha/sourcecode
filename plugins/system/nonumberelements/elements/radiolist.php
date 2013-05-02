<?php
/**
 * Element: Radio List
 * Displays a list of radio items with a break after each item
 *
 * @package    NoNumber! Elements
 * @version    1.0.0
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Radio List Element
 */
class JElementRadioList extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'RadioList';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		$options = array ();
		foreach ( $node->children() as $option)
		{
			$val =	$option->attributes( 'value' );
			$text =	$option->data();
			$options[] = JHTML::_( 'select.option', $val, JText::_( $text ).'<br />' );
		}

		return JHTML::_( 'select.radiolist', $options, ''.$control_name.'['.$name.']', '', 'value', 'text', $value, $control_name.$name );
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}