<?php
/**
 * @package CONTENTSUBMIT
 * @link 	http://www.dioscouri.com
 * @license GNU/GPLv2
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
* installation function
*/
// ************************************************************************
function com_install( ) {
 	global $mainframe;
	
	$database = JFactory::getDBO();
	$query = " UPDATE `#__components` SET `admin_menu_link` = '' WHERE `option` = 'com_contentsubmit' ";
	$database->setQuery( $query );
	$database->query();


}
// ************************************************************************ 
