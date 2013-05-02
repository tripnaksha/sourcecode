<?php
/**
 * Element: Text Area Plus
 * Displays a text area with extra options
 *
 * @package    NoNumber! Elements
 * @version    1.1.0
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Text Area Plus Element
 */
class JElementTextAreaPlus extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'TextAreaPlus';

	function fetchTooltip( $label, $description, &$node, $control_name, $name )
	{

		$rows =		$this->def( $node->attributes( 'rows' ), 4 );
		$example =	$node->attributes( 'example' );

		$html = '<label id="'.$control_name.$name.'-lbl" for="'.$control_name.$name.'"';
		if ( $description ) {
			$html .= ' class="hasTip" title="'.JText::_( $label).'::'.JText::_( $description).'">';
		} else {
			$html .= '>';
		}
		$html .= JText::_( $label ).'</label>';

		if( $example ) {
			$el = 'document.getElementById( \''.$control_name.$name.'\' )';
			$onclick = $el.'.value = \''.str_replace( "'", "\'", $example ).'\n\'+'.$el.'.value;'
				.'this.blur();return false;';
			$html .= '<br clear="all" />';
			$html .= '<div class="button2-left" style="float:right;margin-top:5px;"><div class="blank"><a href="javascript://;" onclick="'.$onclick.'">'.JText::_( 'Example' ).'</a></div></div>'."\n";
		}

		return $html;
	}

	function fetchElement( $name, $value, &$node, $control_name )
	{
		$resize =		$node->attributes( 'resize' );
		$width =		$this->def( $node->attributes( 'width' ), 400 );
		$minwidth =		$this->def( $node->attributes( 'minwidth' ), 200 );
		$minwidth =		min( $width, $minwidth );
		$maxwidth =		$this->def( $node->attributes( 'maxwidth' ), 1200 );
		$maxwidth =		max( $width, $maxwidth );
		$height =		$this->def( $node->attributes( 'height' ), 80 );
		$minheight =	$this->def( $node->attributes( 'minheight' ), 40 );
		$minheight =	min( $height, $minheight );
		$maxheight =	$this->def( $node->attributes( 'maxheight' ), 600 );
		$maxheight =	max( $height, $maxheight );
		$class =		$this->def( $node->attributes( 'class' ), 'text_area' );
		$class =		'class="'.$class.'"';
		$type =			$node->attributes( 'texttype' );

		if( $resize ) {
			$file_root =	str_replace( '\\', '/', str_replace( JPATH_SITE, '', dirname( __FILE__ ) ) );

			$document	=& JFactory::getDocument();
			$document->addScript( JURI::root(true).$file_root.'/textareaplus/textareaplus.js' );
			$document->addStyleSheet( JURI::root(true).$file_root.'/textareaplus/textareaplus.css' );
			$script = "
				window.addEvent( 'domready', function() {
					// not for Safari (and other webkit browsers) because it has its own resize option
					if ( !window.webkit ) {
						new TextAreaResizer( '".$control_name.$name."', { 'min_x':".$minwidth.", 'max_x':".$maxwidth.", 'min_y':".$minheight.", 'max_y':".$maxheight." } );
					}
				});

			";
			$document->addScriptDeclaration( $script );
		}

		if ( $type == 'html' ) {
			// Convert <br /> tags so they are not visible when editing
			$value = str_replace( '<br />', "\n", $value );
		} else if ( $type == 'regex' ) {
			// Protects the special characters
			$value = str_replace( '[:REGEX_ENTER:]', '\n', $value );
		}

		return '<textarea name="'.$control_name.'['.$name.']" style="width:'.$width.'px;height:'.$height.'px" '.$class.' id="'.$control_name.$name.'" >'.$value.'</textarea>';
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}