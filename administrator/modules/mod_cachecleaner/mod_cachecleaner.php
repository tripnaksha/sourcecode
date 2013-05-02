<?php
/**
 * Main Module File
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

/**
* Module that cleans cache
*/

$mainframe =& JFactory::getApplication();

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'cachecleaner'.DS.'helper.php';

require JModuleHelper::getLayoutPath( 'mod_cachecleaner'.DS.'cachecleaner' );