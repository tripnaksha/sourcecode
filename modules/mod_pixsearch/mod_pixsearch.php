<?php
/**
* @package mod_pixsearch
* @copyright	Copyright (C) 2007 PixPro Stockholm AB. All rights reserved.
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL, see LICENSE.php
* PixSearch is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

/**
 * PixSearch
 *
 * Used to process Ajax searches on a Joomla Content.
 *
 * @author		Henrik Hussfelt <henrik@pixpro.net>
 * @package		mod_pixsearch
 * @since		1.5
 * @version     0.4.0
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

modPixsearchHelper::inizialize($params->get('include_css'), $params->get('offset_search_result'));

require(JModuleHelper::getLayoutPath('mod_pixsearch'));