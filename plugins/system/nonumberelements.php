<?php
/**
 * Main Plugin File
 * Does all the magic!
 *
 * @package    NoNumber! Elements
 * @version    v1.2.1
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Plugin that loads Elements
*/
class plgSystemNoNumberElements extends JPlugin
{
	/**
	* Constructor
	*
	* For php4 compatability we must not use the __constructor as a constructor for
	* plugins because func_get_args ( void ) returns a copy of all passed arguments
	* NOT references. This causes problems with cross-referencing necessary for the
	* observer design pattern.
	*/
	function plgSystemNoNumberElements( &$subject, $config )
	{
		$mainframe = &JFactory::getApplication();
		$page = JRequest::getVar( 'nn_qp' );

		if( !$mainframe->isAdmin() && !$page ) {
			return;
		}

		parent::__construct( $subject, $config );

		if( $mainframe->isAdmin() ) {
			//load the language file
			$this->loadLanguage();

			// Loads English language file as fallback (for undefined stuff in other language file)
			$file = JPATH_ADMINISTRATOR.DS.'language'.DS.'en-GB'.DS.'en-GB.plg_system_nonumberelements.ini';
			$lang =& JFactory::getLanguage();
			$lang->_load( $file, 'plg_system_nonumberelements', 0 );
		}

		if( $page ) {
			// Include the Helper
			require_once JPATH_SITE.DS.'plugins'.DS.'system'.DS.'nonumberelements'.DS.'helper.php';
			$this->helper =& new plgSystemNoNumberElementsHelper;
			$this->helper->init();
		}
	}
}