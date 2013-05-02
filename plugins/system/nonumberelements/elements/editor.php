<?php
/**
 * Element: Editor
 * Displays an HTML editor text field
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
 * Editor Element
 *
 * Available extra parameters:
 * width			Width of the editor (default = 100%)
 * height			Width of the editor (default = 400)
 * newline			Show editor on a new line (under the other blocks)
 */
class JElementEditor extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Editor';

	function fetchTooltip( $label, $description, &$node, $control_name, $name )
	{
		return;
	}

	function fetchElement( $name, $value, &$node, $control_name )
	{
		$label =		$node->attributes( 'label' );
		$description =	$node->attributes( 'description' );
		$width =		$this->def( $node->attributes( 'width' ), '100%' );
		$height =		$this->def( $node->attributes( 'height' ), 400 );
		$newline =		$node->attributes( 'newline' );

		$value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );

		$option =	JRequest::getVar( 'option', '' );
		if ( $option == 'com_modules' ) {
			$name = $control_name.'['.$name.']';
		}

		$html = '';
		if ( $newline ) {
			$html .= JText::_( $description );
			$html .= '</td></tr></table>';
			$html .= '</div></div></fieldset></div>';
			$html .= '<div class="clr"></div><div><fieldset class="adminform">';
			if( $label != '' ) {
				$html .= '<legend>'.JText::_( $label ).'</legend>';
			}
			$html .= '<div><div><div><table width="100%" class="paramlist admintable" cellspacing="1"><tr><td class="paramlist_value" colspan="2">';
		} else {
			if( $label != '' ) {
				$html .= '<b>'.JText::_( $label ).'</b><br />';
			}
			if( $description != '' ) {
				$html .= JText::_( $description ).'<br />';
			}
		}

		$editor =& JFactory::getEditor();
		$html .= $editor->display( $name, $value, $width, $height, '60', '20', true );
		$html .= '<br clear="all" />';

		return $html;
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}