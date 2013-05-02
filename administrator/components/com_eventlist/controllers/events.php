<?php
/**
 * @version 1.0 $Id: events.php 662 2008-05-09 22:28:53Z schlu $
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * EventList Component Events Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class EventListControllerEvents extends EventListController
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'apply', 		'save' );
		$this->registerTask( 'copy',	 	'edit' );
	}

	/**
	 * Logic to publish events
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function publish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('events');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('EVENT PUBLISHED');

		$this->setRedirect( 'index.php?option=com_eventlist&view=events', $msg );
	}

	/**
	 * Logic to unpublish events
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function unpublish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('events');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('EVENT UNPUBLISHED');

		$this->setRedirect( 'index.php?option=com_eventlist&view=events', $msg );
	}

	/**
	 * Logic to archive events
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function archive()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to archive' ) );
		}

		$model = $this->getModel('events');
		if(!$model->publish($cid, -1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('EVENT ARCHIVED');

		$this->setRedirect( 'index.php?option=com_eventlist&view=events', $msg );
	}

	/**
	 * logic for cancel an action
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$group = & JTable::getInstance('eventlist_events', '');
		$group->bind(JRequest::get('post'));
		$group->checkin();

		$this->setRedirect( 'index.php?option=com_eventlist&view=events' );
	}

	/**
	 * logic to create the new event screen
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function add( )
	{
		global $option;

		$this->setRedirect( 'index.php?option='. $option .'&view=event' );
	}

	/**
	 * logic to create the edit event screen
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function edit( )
	{
		JRequest::setVar( 'view', 'event' );
		JRequest::setVar( 'hidemainmenu', 1 );

		$model 	= $this->getModel('event');
		$task 	= JRequest::getVar('task');

		if ($task == 'copy') {
			JRequest::setVar( 'task', $task );
		} else {
			
			$user	=& JFactory::getUser();
			// Error if checkedout by another administrator
			if ($model->isCheckedOut( $user->get('id') )) {
				$this->setRedirect( 'index.php?option=com_eventlist&view=events', JText::_( 'EDITED BY ANOTHER ADMIN' ) );
			}
			$model->checkout();
		}
		parent::display();
	}

	/**
	 * logic to save an event
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$task		= JRequest::getVar('task');

		$post = JRequest::get( 'post' );
		$post['datdescription'] = JRequest::getVar( 'datdescription', '', 'post','string', JREQUEST_ALLOWRAW );
		$post['datdescription']	= str_replace( '<br>', '<br />', $post['datdescription'] );

		$model = $this->getModel('event');

		if ($returnid = $model->store($post)) {

			switch ($task)
			{
				case 'apply' :
					$link = 'index.php?option=com_eventlist&controller=events&view=event&hidemainmenu=1&cid[]='.$returnid;
					break;

				default :
					$link = 'index.php?option=com_eventlist&view=events';
					break;
			}
			$msg	= JText::_( 'EVENT SAVED');

			$cache = &JFactory::getCache('com_eventlist');
			$cache->clean();

		} else {

			$msg 	= '';
			$link = 'index.php?option=com_eventlist&view=events';

		}

		$model->checkin();

		$this->setRedirect( $link, $msg );
 	}

	/**
	 * logic to remove an event
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
 	function remove()
	{
		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$total = count( $cid );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}

		$model = $this->getModel('events');
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$msg = $total.' '.JText::_( 'EVENTS DELETED');

		$cache = &JFactory::getCache('com_eventlist');
		$cache->clean();

		$this->setRedirect( 'index.php?option=com_eventlist&view=events', $msg );
	}
}
?>