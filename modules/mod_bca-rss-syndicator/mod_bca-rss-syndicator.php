<?php
/**
* DS-Syndicate component for Joomla 1.5
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');
  
$message = $params->get('msg','');
$align = $params->get('align','left');
$ids = ltrim( $params->get('feedid','*') );
$cssClass = $params->get('moduleclass_sfx');
$link_to_feed_icon = (int) $params->get('link_to_feed_icon','1');

$feed_props = modBcaRssSyndicatorHelper::getFeeds($ids);
if (empty ($feed_props))
{
	echo '<div>' . JText::_('No feed specified.') . '</div>';
	return;
}
require(JModuleHelper::getLayoutPath('mod_bca-rss-syndicator'));

?>
