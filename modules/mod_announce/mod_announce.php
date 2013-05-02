<?php
//no direct access
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// include the helper file
require_once(dirname(__FILE__).DS.'helper.php');

// get parameter from the module's configuration
$annCount		 = intval($params->get('count'));
$delay		 = intval($params->get('delay'));
$maxlength		 = $width > 20 ? $width : 20;
$text			 = $params->get('text', JText::_('search...'));
$set_Itemid		 = intval($params->get('set_itemid', 0));
$moduleclass_sfx	 = $params->get('moduleclass_sfx', '');
$searchurl		 = JRequest::getString('task');


// get the items to display from the helper
$anns = modAnnounceHelper::getList($params);

// include the template for display
require(JModuleHelper::getLayoutPath('mod_announce'));
?>