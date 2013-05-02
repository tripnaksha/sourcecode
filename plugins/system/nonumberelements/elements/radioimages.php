<?php
/**
 * Element: Radio Images
 * Displays a list of radio items and the images you can chose from
 .*
 * @package    NoNumber! Elements
 * @version    1.0.3
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Radio Images Element
 */
class JElementRadioImages extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'RadioImages';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		jimport( 'joomla.filesystem.folder' );
		jimport( 'joomla.filesystem.file' );

		// path to images directory
		$path =		JPATH_ROOT.DS.str_replace( '/', DS, $node->attributes( 'directory' ) );
		$filter =	$node->attributes( 'filter' );
		$exclude =	$node->attributes( 'exclude' );
		$stripExt =	$node->attributes( 'stripext' );
		$files =	JFolder::files( $path, $filter);

		$options = array ();

		if ( !$node->attributes( 'hide_none' ) ) {
			$options[] = JHTML::_( 'select.option', '-1', JText::_( 'Do not use' ).'<br />' );
		}

		if ( !$node->attributes( 'hide_default' ) ) {
			$options[] = JHTML::_( 'select.option', '', JText::_( 'Use default' ).'<br />' );
		}

		if ( is_array( $files) ) {
			foreach ( $files as $file) {
				if ( $exclude) {
					if (preg_match( chr( 1 ) . $exclude . chr( 1 ), $file ) ) {
						continue;
					}
				}
				if ( $stripExt) {
					$file = JFile::stripExt( $file );
				}
				$image = '<img src="../'.$node->attributes( 'directory' ).'/'.$file.'" style="padding-right: 10px;" title="'.$file.'" alt="'.$file.'" />';
				$options[] = JHTML::_( 'select.option', $file, $image);
			}
		}

		$list = JHTML::_( 'select.radiolist', $options, ''.$control_name.'['.$name.']', '', 'value', 'text', $value, $control_name.$name );

		$list = '<div style="float:left;">'.str_replace( '<input type="radio"', '</div><div style="float:left;"><input type="radio"', $list ).'</div>';
		$list = preg_replace( '#</label>(\s*)</div>#', '</label></div>\1', $list );
		$list = str_replace( '<br /></label></div>', '<br /></label></div><div style="clear:both;"></div>', $list );

		return $list;

	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}