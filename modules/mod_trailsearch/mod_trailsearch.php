<?php
//no direct access
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// include the helper file
//require_once(dirname(__FILE__).DS.'helper.php');

// get parameter from the module's configuration
$userCount		 = $params->get('usercount');
$width			 = intval($params->get('width', 20));
$maxlength		 = $width > 20 ? $width : 20;
$text			 = $params->get('text', JText::_('search...'));
$set_Itemid		 = intval($params->get('set_itemid', 0));
$moduleclass_sfx	 = $params->get('moduleclass_sfx', '');
$searchurl		 = JRequest::getString('task');

// Look for search parameter in the url, if not available there, pick it up from the search input box.
if ($searchurl)
{
   $searchtext		 = JRequest::getString('task');
}
else
{
   $searchtext	 	 = JRequest::getString('searchword');

   // setting the cookie for search text entered in the searchbox. Retrieve this from the map.js file
   // and use to create map overlay.
   setcookie("searchPageCookie", $searchtext, 0);
   // Delete the cookie set from the js file which is entered from the dom object in the wrapper.
   setcookie("searchBoxCookie", "", time()-3600);
}

// get the items to display from the helper
// Not needed anymore as helper function not being used. Instead, search component used.
//$items = ModRouteSearchHelper::getItems($userCount, $text, $searchtext);

// include the template for display
require(JModuleHelper::getLayoutPath('mod_routesearch'));
?>