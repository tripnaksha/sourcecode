<?php
/**
 * Element: Toggler
 * Adds slide in and out functionality to elements based on an elements value
 *
 * @package    NoNumber! Elements
 * @version    1.2.0
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Toggler Element
 *
 * To use this, make a start xml param tag with the param and value set
 * And an end xml param tag without the param and value set
 * Everything between those tags will be included in the slide
 *
 * Available extra parameters:
 * param			The name of the reference parameter
 * value			a comma seperated list of value on which to show the elements
 */
class JElementToggler extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Toggler';

	function fetchTooltip( $label, $description, &$node, $control_name, $name )
	{
		return;
	}

	function fetchElement( $name, $value, &$node, $control_name )
	{
		$option = JRequest::getCmd( 'option' );

		// do not place toggler stuff on JoomFish pages
		if ( $option == 'com_joomfish' ) { return; }

		$param =	$node->attributes( 'param' );
		$value =	$node->attributes( 'value' );
		$nofx =		$node->attributes( 'nofx' );

		$file_root =	str_replace( '\\', '/', str_replace( JPATH_SITE, '', dirname( __FILE__ ) ) );

		$document =& JFactory::getDocument();
		$document->addScript( JURI::root(true).$file_root.'/toggler.js' );
		$script = "
			window.addEvent( 'domready', function() {
				if ( !nnTogglerSet ) {
					nnTogglerSet = new nnToggler();
				}
			});
		";
		$document->addScriptDeclaration( $script );

		if ( $param != '' ) {
			$set_groups = explode( '|', $param );
			$set_values = explode( '|', $value );
			$ids = array();
			foreach ( $set_groups as $i => $group ) {
				$count = $i;
				if ( $count >= count( $set_values ) ) {
					$count = 0;
				}
				$values = explode( ',', $set_values[$count] );
				foreach ( $values as $val ) {
					$ids[] = $group.'.'.$val;
				}
			}
			$html = '<div id="'.implode( ' ', $ids ).'" class="nntoggler';
			if ( $nofx ) {
				$html .= ' nntoggler_nofx';
			}
			$html .= '" style="visibility: hidden;">';
			$html .= '<table width="100%" class="paramlist admintable" cellspacing="1">';
			$html .= '<tr style="height:auto;"><td style="padding:0px;height:auto;" colspan="2">';
		} else {
			$random = rand( 1000, 10000 );
			$html = '<div id="end-'.$random.'"></div><script>var enddiv = document.getElementById("end-'.$random.'");enddiv.parentNode.style.padding=0;</script>';
			$html .= '</td></tr></table>';
			$html .= '</div>';
		}

		return $html;
	}
}