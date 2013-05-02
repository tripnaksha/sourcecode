<?php
/**
 * Element: Slide
 * Element to create a new slide pane
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
 * Slide Element
 */
class JElementSlide extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Slide';

	function fetchTooltip( $label, $description, &$node, $control_name, $name )
	{
		return;
	}

	function fetchElement( $name, $value, &$node, $control_name )
	{
		$label =		$node->attributes( 'label' );
		$description =	$node->attributes( 'description' );
		$lang_folder =	$node->attributes( 'language_folder' );
		$show_apply =	$node->attributes( 'show_apply' );

		$html = '</td></tr></table></div></div>';
		$html .= '<div class="panel"><h3 class="jpane-toggler title" id="advanced-page"><span>';
		$html .= html_entity_decoder( JText::_( $label ) );
		$html .= '</span></h3>';
		$html .= '<div class="jpane-slider content"><table width="100%" class="paramlist admintable" cellspacing="1"><tr><td class="paramlist_value" colspan="2">';

		if ( $lang_folder ) {
			// Include extra language file
			$lang = JFactory::getLanguage();
			$lang = str_replace( '_', '-', $lang->_lang );

			if ( strpos( $lang_folder, '/administrator' ) === 0 ) {
				$lang_folder = str_replace( '/', DS, str_replace( '/administrator', JPATH_ADMINISTRATOR, $lang_folder ) );
			} else {
				$lang_folder = JPATH_SITE.str_replace( '/', DS, $lang_folder );
			}
			$lang_file = 'en-GB.inc.php';
			if ( file_exists( $lang_folder.DS.$lang_file ) ) {
				include $lang_folder.DS.$lang_file;
			}
			if ( $lang != 'en-GB' ) {
				$lang_file = $lang.'.inc.php';
				if ( !file_exists( $lang_folder.DS.$lang_file ) ) {
					$include_file = 'en-GB.inc.php';
				}
				if ( file_exists( $lang_folder.DS.$lang_file ) ) {
					include $lang_folder.DS.$lang_file;
				}
			}
		}

		if ( $description ) {
			$description = html_entity_decoder( JText::_( $description ) );
			$html .= '<div class="panel"><div style="padding: 2px 5px;">';
			if ( $show_apply ) {
				$apply_button = '<a href="#" onclick="submitbutton( \'apply\' );" title="'.JText::_( 'Apply' ).'"><img align="right" border="0" alt="'.JText::_( 'Apply' ).'" src="images/tick.png"/></a>';
				$html .= $apply_button;
			}
			$html .= $description;
			$html .= '<div style="clear: both;"></div></div></div>';
		}

		return $html;
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
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