<?php
/**
* @version		$Id: mod_search.php 10855 2008-08-29 22:47:34Z willebil $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once( dirname(__FILE__).DS.'helper.php' );

$order_by_ajax		 = $params->get('order_by', '');
$text_ajax	 		 = $params->get('text', '');

$document =& JFactory::getDocument();
		
$document->addscript(JURI::root(true).'modules'.DS.'mod_ajaxsearch'.DS.'js'.DS.'jquery-1.3.2.min.js');
$document->addscript(JURI::root(true).'modules'.DS.'mod_ajaxsearch'.DS.'js'.DS.'script.js');
$document->addStyleSheet(JURI::root(true).'modules'.DS.'mod_ajaxsearch'.DS.'css'.DS.'search.css');

require(JModuleHelper::getLayoutPath('mod_ajaxsearch'));
?>
