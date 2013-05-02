<?php
/**
 * Main Plugin File
 * Does all the magic!
 *
 * @package    Cache Cleaner
 * @version    1.1.1
 * @since      File available since Release v0.1.0
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl/cachecleaner
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport( 'joomla.event.plugin' );

/**
* Plugin that cleans cache
*/
class plgSystemCacheCleaner extends JPlugin
{
	/**
	* Constructor
	*
	* For php4 compatability we must not use the __constructor as a constructor for
	* plugins because func_get_args ( void ) returns a copy of all passed arguments
	* NOT references. This causes problems with cross-referencing necessary for the
	* observer design pattern.
	*/
	function plgSystemCacheCleaner( &$subject, $config )
	{
		if ( !JRequest::getInt( 'cleancache' ) ) { return; }

		parent::__construct( $subject, $config );
		
		$mainframe =& JFactory::getApplication();
		
		$params = new JParameter( $config['params'] );

		if (	( $mainframe->isAdmin() && JRequest::getInt( 'cleancache' ) )
			||	( JRequest::getInt( 'cleancache' ) == $params->get( 'frontend_secret' ) )
		) {
			require_once JPATH_SITE.DS.'plugins'.DS.'system'.DS.'cachecleaner'.DS.'helper.php';
			$this->helper =& new plgSystemCacheCleanerHelper;
			$this->helper->cleanCache( $params );
		}
	}
}