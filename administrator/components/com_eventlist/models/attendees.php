<?php
/**
 * @version 1.0 $Id: attendees.php 815 2008-11-17 14:48:51Z julienv $
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

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * EventList Component attendees Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class EventListModelAttendees extends JModel
{
	/**
	 * Events data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Events total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Events total
	 *
	 * @var integer
	 */
	var $_event = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Events id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		global $mainframe, $option;

    $limit      = $mainframe->getUserStateFromRequest( $option.'.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
    $limitstart = $mainframe->getUserStateFromRequest( $option.JRequest::getCmd( 'view').'.limitstart', 'limitstart', 0, 'int' );
		
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		//set unlimited if export or print action | task=export or task=print
		$this->setState('unlimited', JRequest::getString('task'));

		$id = JRequest::getInt('id');
		$this->setId($id);


	}

	/**
	 * Method to set the category identifier
	 *
	 * @access	public
	 * @param	int Category identifier
	 */
	function setId($id)
	{
		// Set id and wipe data
		$this->_id	    = $id;
		$this->_data 	= null;
	}

	/**
	 * Method to get categories item data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();

			if ($this->getState('unlimited') == '') {
				$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			} else {
				$this->_data = $this->_getList($query);
			}
		}

		return $this->_data;
	}

	/**
	 * Method to get the total nr of the attendees
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to build the query for the attendees
	 *
	 * @access private
	 * @return integer
	 * @since 0.9
	 */
	function _buildQuery()
	{
		// Get the ORDER BY clause for the query
		$orderby	= $this->_buildContentOrderBy();
		$where		= $this->_buildContentWhere();

		$query = 'SELECT r.*, u.username, u.name, a.id, u.gid, u.email'
		. ' FROM #__eventlist_register AS r'
		. ' LEFT JOIN #__eventlist_events AS a ON r.event = a.id'
		. ' LEFT JOIN #__users AS u ON r.uid = u.id'
		. $where
		. $orderby
		;

		return $query;
	}

	/**
	 * Method to build the orderby clause of the query for the attendees
	 *
	 * @access private
	 * @return integer
	 * @since 0.9
	 */
	function _buildContentOrderBy()
	{
		global $mainframe, $option;

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.attendees.filter_order', 		'filter_order', 	'u.username', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.attendees.filter_order_Dir',	'filter_order_Dir',	'', 'word' );

		$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.', u.name';

		return $orderby;
	}

	/**
	 * Method to build the where clause of the query for the attendees
	 *
	 * @access private
	 * @return string
	 * @since 0.9
	 */
	function _buildContentWhere()
	{
		global $mainframe, $option;

		$filter 			= $mainframe->getUserStateFromRequest( $option.'.attendees.filter', 'filter', '', 'int' );
		$search 			= $mainframe->getUserStateFromRequest( $option.'.attendees.search', 'search', '', 'string' );
		$search 			= $this->_db->getEscaped( trim(JString::strtolower( $search ) ) );

		$where = array();

		$where[] = 'r.event = '.$this->_id;

		/*
		* Search name
		*/
		if ($search && $filter == 1) {
			$where[] = ' LOWER(u.name) LIKE \'%'.$search.'%\' ';
		}

		/*
		* Search username
		*/
		if ($search && $filter == 2) {
			$where[] = ' LOWER(u.username) LIKE \'%'.$search.'%\' ';
		}

		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

		return $where;
	}

	/**
	 * Get event data
	 *
	 * @access public
	 * @return object
	 * @since 0.9
	 */
	function getEvent()
	{
		$query = 'SELECT id, title, dates FROM #__eventlist_events WHERE id = '.$this->_id;

		$this->_db->setQuery( $query );

		$_event = $this->_db->loadObject();

		return $_event;
	}

	/**
	 * Delete registered users
	 *
	 * @access public
	 * @return true on success
	 * @since 0.9
	 */
	function remove($cid = array(), $event)
	{
		if (count( $cid ))
		{
			$user = implode(',', $cid);
			$event = (int)$event;
			
			$query = 'DELETE FROM #__eventlist_register WHERE uid IN ('. $user .') AND event = '.$event;

			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				JError::raiseError( 1001, $this->_db->getErrorMsg() );
			}
		}
		return true;
	}
}
?>