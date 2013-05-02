<?php
/*
* @version		$Id: extravote.php 2008 vargas $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define('_JEXEC', 1);

// no direct access
defined('_JEXEC') or die('Restricted access');

define( 'DS', DIRECTORY_SEPARATOR );

define('JPATH_BASE', dirname(__FILE__).DS.'..'.DS.'..'.DS.'..' );

require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

jimport('joomla.database.database');
jimport('joomla.database.table');

$mainframe = &JFactory::getApplication('site');
$mainframe->initialise();

$user = &JFactory::getUser();

$plugin = &JPluginHelper::getPlugin('content', 'extravote');
$params = new JParameter($plugin->params);

if ( $params->get('access') == 1 && !$user->get('id') ) {
	echo 'login';
} else {
	$user_rating = JRequest::getInt('user_rating');
	$cid = JRequest::getInt('cid');
	$xid = JRequest::getVar('xid');
	$db  = &JFactory::getDBO();
	if (($user_rating >= 1) and ($user_rating <= 5)) {
		$currip = ( phpversion() <= '4.2.1' ? @getenv( 'REMOTE_ADDR' ) : $_SERVER['REMOTE_ADDR'] );
		if ( !(int)$xid ){
			$query = "SELECT * FROM #__content_rating WHERE content_id = " . $cid;
			$db->setQuery( $query );
			$votesdb = $db->loadObject();
			if ( !$votesdb ) {
				$query = "INSERT INTO #__content_rating ( content_id, lastip, rating_sum, rating_count )"
				. "\n VALUES ( " . $cid . ", " . $db->Quote( $currip ) . ", " . $user_rating . ", 1 )";
				$db->setQuery( $query );
				$db->query() or die( $db->stderr() );;
			} else {
				if ($currip != ($votesdb->lastip)) {
					$query = "UPDATE #__content_rating"
					. "\n SET rating_count = rating_count + 1, rating_sum = rating_sum + " .   $user_rating . ", lastip = " . $db->Quote( $currip )
					. "\n WHERE content_id = " . $cid;
					$db->setQuery( $query );
					$db->query() or die( $db->stderr() );
				} else {
					echo 'voted';
					exit();
				}
			}
		} else {
			$query = "SELECT * FROM #__content_extravote WHERE content_id=".$cid." AND extra_id=".$xid;
			$db->setQuery( $query );
			$votesdb = $db->loadObject();
			if ( !$votesdb ) {
				$query = "INSERT INTO #__content_extravote  (content_id,extra_id,lastip,rating_sum,rating_count)"
				. "\n VALUES (".$cid.",".$xid.",".$db->Quote($currip).",".$user_rating.",1)";
				$db->setQuery( $query );
				$db->query() or die( $db->stderr() );;
			} else {
				if ($currip != ($votesdb->lastip)) {
					$query = "UPDATE #__content_extravote"
					. "\n SET rating_count = rating_count + 1, rating_sum = rating_sum + " .  $user_rating . ", lastip = " . $db->Quote( $currip )
					. "\n WHERE content_id=".$cid." AND extra_id=".$xid;
					$db->setQuery( $query );
					$db->query() or die( $db->stderr() );
				} else {
					echo 'voted';
					exit();
				}
			}
		}
		echo 'thanks';
	}
}