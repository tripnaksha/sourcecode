<?php
/**
 * Element: Dependency
 * Displays an error if given file is not found
 *
 * @package    NoNumber! Elements
 * @version    1.0.7
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Dependency Element
 *
 * Available extra parameters:
 * label	The name of the extension that is needed
 * file		The file to check (from the root)
 */
class JElementDependency extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Dependency';

	function fetchTooltip( $label, $description, &$node, $control_name, $name )
	{
		return;
	}

	function fetchElement( $name, $value, &$node, $control_name )
	{
		$mainframe =& JFactory::getApplication();

		$label =	$this->def( $node->attributes( 'label' ), 'the main extension' );
		$file =		$node->attributes( 'file' );
		$file = 	str_replace( '/', DS, $file );
		
		$random = rand( 1000, 10000 );
		$html = '<div id="end-'.$random.'"></div><script>var enddiv = document.getElementById("end-'.$random.'");enddiv.parentNode.style.padding=0;</script>';

		if ( strpos( $file, '/administrator' ) === 0 ) {
			$file = str_replace( '/', DS, str_replace( '/administrator', JPATH_ADMINISTRATOR, $file ) );
		} else {
			$file = JPATH_SITE.str_replace( '/', DS, $file );
		}

		if ( !file_exists( $file ) ) {
			$msg = JText::sprintf( '-This extension needs the main extension to function', $label );
			if ( $msg == 'This extension needs the main extension to function' ) {
				$msg = 'This extension needs '.$label.' to function';
			}
			$message_set = 0;
			$messageQueue = $mainframe->getMessageQueue();
			foreach ( $messageQueue as $queue_message ) {
				if ( $queue_message['type'] == 'error' && $queue_message['message'] == $msg ) {
					$message_set = 1;
					break;
				}
			}
			if ( !$message_set ) {
				$mainframe->enqueueMessage( $msg, 'error' );
			}
		}

		return $html;
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}