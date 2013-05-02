<?php
/**
 * Module Helper File
 *
 * @package    Cache Cleaner
 * @version    1.1.1
 * @since      File available since Release v1.0.0
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl/cachecleaner
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class modCacheCleaner
{
	function render( $params )
	{
		JHTML::_( 'behavior.modal' );


		$document =& JFactory::getDocument();
		$script = "
			var cachecleaner_root = '".JURI::base( true )."';
			var cachecleaner_msg = '".JText::_( 'Cleaning Cache' )."';
			var cachecleaner_msg_success = '".JText::_( 'Cache cleaned' )."';
			var cachecleaner_msg_failure = '".JText::_( 'Cache could not be cleaned' )."';";
		$document->addScriptDeclaration( $script );
		$document->addScript( JURI::base( true ).'/modules/mod_cachecleaner/cachecleaner/js/cachecleaner.js' );
		$document->addStyleSheet( JURI::base( true ).'/modules/mod_cachecleaner/cachecleaner/css/cachecleaner.css' );

		$text = JText::_( 'Clean Cache' );
		$class = '';
		if ( $params->get( 'display_link', 'both' ) == 'text' ) {
			$class = 'no_icon';
		} else if ( $params->get( 'display_link', 'both' ) == 'icon' ) {
			$text = '&nbsp;';
			$class = 'no_text';
		} 

		echo '<a href="javascript://" onclick="cachecleaner_load();return false;" onfocus="this.blur();"  class="'.$class.'" id="cachecleaner" title="'.JText::_( 'Clean Cache' ).'"><span>'.$text.'</span></a>';
	}
}