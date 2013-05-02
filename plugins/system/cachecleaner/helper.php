<?php
/**
 * Plugin Helper File
 *
 * @package    Cache Cleaner
 * @version    1.1.1
 * @since      File available since Release v1.1.1
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl/cachecleaner
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Plugin that cleans cache
*/
class plgSystemCacheCleanerHelper
{
	function cleanCache( &$params )
	{
		$final_state = 1;

		$cache =& JFactory::getCache();

		// remove all folders in cache folder
		$cache->clean();
		$cache->gc();

		// remove all remaining folders and files in cache folder
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		for ( $i = 0; $i < 2; $i++) {
			$client	 =& JApplicationHelper::getClientInfo($i);

			$folders = JFolder::folders( $client->path.DS.'cache' );
			foreach ( $folders as $folder ) {
				$state = JFolder::delete( $client->path.DS.'cache'.DS.$folder );
				if ( !$state ) {
					$final_state = 0;
				}
			}

			$files = JFolder::files( $client->path.DS.'cache' );
			foreach ( $files as $file ) {
				if ( $file != 'index.html' ) {
					$state = JFile::delete( $client->path.DS.'cache'.DS.$file );
					if ( !$state ) {
						$final_state = 0;
					}
				}
			}
		}

		// Empty JRE cache db table
		$database =& JFactory::getDBO();
		$database->setQuery( 'TRUNCATE TABLE `#__jrecache_repository`' );
		$database->query();

		// Load language for messaging
		$lang =& JFactory::getLanguage();
		$lang->load( 'mod_cachecleaner', JPATH_ADMINISTRATOR );
		$file = JPATH_ADMINISTRATOR.DS.'language'.DS.'en-GB'.DS.'en-GB.mod_cachecleaner.ini';
		$lang->_load( $file, 'mod_cachecleaner', 0 );

		$error = 0;
		if ( !$final_state ) {
			$msg = JText::_( 'Not all cache could be removed' );
			$error = 1;
		} else {
			$msg = JText::_( 'Cache cleaned' );
		}
		if( JRequest::getInt( 'break' ) ) {
			echo $msg;
			exit;
		} else {
			$mainframe =& JFactory::getApplication();
			$mainframe->enqueueMessage( $msg, ( $error ? 'error' : 'message' ) );
		}
	}
}