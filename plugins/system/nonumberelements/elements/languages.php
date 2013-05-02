<?php
/**
 * Element: Languages
 * Displays a select box of languages
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
 * Templates Element
 */
class JElementLanguages extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Languages';

	function fetchElement( $name, $value, &$node, $control_name)
	{
		$size			= $node->attributes( 'size' );
		$multiple		= $node->attributes( 'multiple' );
		$client			= $this->def( $node->attributes( 'client' ), 'SITE' );
		
		$control = $control_name.'['.$name.']';
		$attribs = 'class="inputbox"';
		if ( $multiple ) {
			if( !is_array( $value ) ) { $value = explode( ',', $value ); }
			$attribs .= ' multiple="multiple"';
			$control .= '[]';
		}
		
		jimport('joomla.language.helper');
		$options = JLanguageHelper::createLanguageList( $value, constant( 'JPATH_'.strtoupper( $client ) ), true );
		if( $size ) {
			$attribs .= ' size="'.$size.'"';
		} else {
			$attribs .= ' size="'.( ( count( $options) > 10 ) ? 10 : count( $options) ).'"';
		}

		$list 	= JHTML::_( 'select.genericlist', $options, $control, $attribs, 'value', 'text', $value, $control_name.$name );

		return $list;
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}