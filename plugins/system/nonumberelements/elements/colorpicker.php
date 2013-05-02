<?php
/**
 * Element: ColorPicker
 * Displays a textfield with a color picker
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
 * ColorPicker Element
 *
 * Available extra parameters:
 * title			The title
 */
class JElementColorPicker extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'ColorPicker';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		$file_root =	str_replace( '\\', '/', str_replace( JPATH_SITE, '', dirname( __FILE__ ) ) );

		$document =& JFactory::getDocument();
		$document->addScript( JURI::root(true).$file_root.'/colorpicker/mooRainbow.js' );
		$document->addStyleSheet( JURI::root(true).$file_root.'/colorpicker/mooRainbow.css' );

		$script = "
			var colorpicker_".$control_name.$name." = '';
			window.addEvent( 'domready', function() {
				colorpicker_".$control_name.$name." = new MooRainbow( '".$control_name.$name."_button', {
					id: '".$control_name.$name."_button',
					wheel: true,
					imgPath: '".JURI::root(true).$file_root."/colorpicker/images/',
					onChange: function( color ) {
						$( '".$control_name.$name."_button' ).setStyle( 'background-color', color.hex );
						$( '".$control_name.$name."' ).value = color.hex;
					},
					onComplete: function( color ) {
						$( '".$control_name.$name."_button' ).setStyle( 'background-color', color.hex );
						$( '".$control_name.$name."' ).value = color.hex;
					}
				});
				colorpicker_".$control_name.$name.".manualSet( $( '".$control_name.$name."' ).value, 'hex' );
			});

		";
		$document->addScriptDeclaration( $script );

		$html = '<div id="'.$control_name.$name.'_button" class="input" style="float:left;width:15px;height:15px;border:1px solid silver;background-color:'.$value.';"></div>';
		$html .= '<input onchange="$( \''.$control_name.$name.'_button\' ).setStyle( \'background-color\', this.value );colorpicker_'.$control_name.$name.'.manualSet( this.value, \'hex\' )" type="text" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="'.$value.'" class="text_area" maxlength="7" size="8" />';

		return $html;
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}