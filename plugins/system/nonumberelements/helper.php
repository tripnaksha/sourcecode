<?php
/**
 * Plugin Helper File
 *
 * @package    NoNumber! Elements
 * @version    v1.2.1
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Plugin that replaces Sourcerer code with its HTML / CSS / JavaScript / PHP equivalent
*/
class plgSystemNoNumberElementsHelper
{
	function init() {
		$mainframe =& JFactory::getApplication();

		$file = JRequest::getVar( 'file' );
		$folder = JRequest::getVar( 'folder' );
		
		jimport( 'joomla.filesystem.file' );

		// only allow files that have .inc.php in the file name
		if ( !$file || ( strpos( $file, '.inc.php' ) === false ) ) {
			echo JText::_( 'Access Denied' );
			exit;
		}

		if ( !$mainframe->isAdmin() && !JRequest::getCmd( 'usetemplate' ) ) {
			$mainframe->setTemplate( 'system' );
		}
		$_REQUEST['tmpl'] = 'component';

		$mainframe->_messageQueue = array();

		$html = '';

		$path = JPATH_SITE;
		if ( $folder ) {
			$path .= DS.implode( DS, explode( '.', $folder ) );
		}
		$file = $path.DS.$file;

		if ( JFile::exists( $file ) ) {
			ob_start();
				include $file;
				$html = ob_get_contents();
			ob_end_clean();
		}

		$document = & JFactory::getDocument();
		$document->setBuffer( $html, 'component' );
		$document->addStyleSheet( JURI::root( true ).'/plugins/system/nonumberelements/css/default.css' );

		$mainframe->render();

		echo JResponse::toString( $mainframe->getCfg( 'gzip' ) );

		exit;
	}
}