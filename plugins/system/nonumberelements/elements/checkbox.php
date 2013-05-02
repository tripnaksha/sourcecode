<?php
/**
 * Element: Checkbox
 * Displays options as checkboxes
 *
 * @package    NoNumber! Elements
 * @version    1.0.5
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Checkbox Element
 */
class JElementCheckbox extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Checkbox';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		if ( !is_array( $value ) ) {
			$value = explode( ',', $value );
		}

		$options = array();
		foreach ( $node->children() as $option ) {
			$val	= $option->attributes( 'value' );
			$text	= $option->data();
			$disabled = $option->attributes( 'disabled' );
			$option = '<input type="checkbox" id="'.$control_name.$name.$val.'" name="'.$control_name.'['.$name.'][]" value="'.$val.'"';
			if ( in_array( $val, $value ) ) {
				$option .= ' checked="checked"';
			}
			if ( $disabled ) {
				$option .= ' disabled="disabled"';
			}
			$option .= ' /> '.JText::_( $text );
			$options[] = $option;
		}

		return implode( '&nbsp;&nbsp;&nbsp;', $options );
	}
}

if( !function_exists( 'html_entity_decoder' ) ) {
	function html_entity_decoder( $given_html, $quote_style = ENT_QUOTES, $charset = 'UTF-8' )
	{
		if( phpversion() < '5.0.0' ) {
			$trans_table = array_flip( get_html_translation_table( HTML_SPECIALCHARS, $quote_style ) );
			$trans_table['&#39;'] = "'";
			return ( strtr( $given_html, $trans_table ) );
		}else {
			return html_entity_decode( $given_html, $quote_style, $charset );
		}
	}
}