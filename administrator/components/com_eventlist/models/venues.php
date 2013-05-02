<?php
/**
 * @version 1.0 $Id: venues.php 815 2008-11-17 14:48:51Z julienv $
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
 * EventList Component Venues Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class EventListModelVenues extends JModel
{
	/**
	 * Category data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Category total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Categorie id
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

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);

	}

		/**
	 * Method to set the venues identifier
	 *
	 * @access	public
	 * @param	int Category identifier
	 */
	function setId($id)
	{
		// Set id and wipe data
		$this->_id	    = $id;
		$this->_data = null;
	}

	/**
	 * Method to get venues item data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the venues if they doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			$this->_data = $this->_additionals($this->_data);
		}

		return $this->_data;
	}

	/**
	 * Total nr of venues
	 *
	 * @access public
	 * @return integer
	 * @since 0.9
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
	 * Method to get a pagination object for the venues
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
	 * Method to build the query for the venues
	 *
	 * @access private
	 * @return string
	 * @since 0.9
	 */
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = 'SELECT l.*, u.email, u.name AS author'
				. ' FROM #__eventlist_venues AS l'
				. ' LEFT JOIN #__users AS u ON u.id = l.created_by'
				. $where
				. $orderby
				;

		return $query;
	}

	/**
	 * Method to build the orderby clause of the query for the venues
	 *
	 * @access private
	 * @return string
	 * @since 0.9
	 */
	function _buildContentOrderBy()
	{
		global $mainframe, $option;

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.venues.filter_order', 'filter_order', 'l.ordering', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.venues.filter_order_Dir', 'filter_order_Dir', '', 'word' );

	//	$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.', l.ordering';
		$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir;

		return $orderby;
	}

	/**
	 * Method to build the where clause of the query for the venues
	 *
	 * @access private
	 * @return string
	 * @since 0.9
	 */
	function _buildContentWhere()
	{
		global $mainframe, $option;

		$filter_state 		= $mainframe->getUserStateFromRequest( $option.'.filter_state', 'filter_state', '', 'word' );
		$filter 			= $mainframe->getUserStateFromRequest( $option.'.filter', 'filter', '', 'int' );
		$search 			= $mainframe->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
		$search 			= $this->_db->getEscaped( trim(JString::strtolower( $search ) ) );

		$where = array();

		/*
		* Filter state
		*/
		if ( $filter_state ) {
			if ( $filter_state == 'P' ) {
				$where[] = 'l.published = 1';
			} else if ($filter_state == 'U' ) {
				$where[] = 'l.published = 0';
			}
		}

		/*
		* Search venues
		*/
		if ($search && $filter == 1) {
			$where[] = ' LOWER(l.venue) LIKE \'%'.$search.'%\' ';
		}

		/*
		* Search city
		*/
		if ($search && $filter == 2) {
			$where[] = ' LOWER(l.city) LIKE \'%'.$search.'%\' ';
		}

		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

		return $where;
	}

	/**
	 * Method to get the userinformation of edited/submitted venues
	 *
	 * @access private
	 * @return object
	 * @since 0.9
	 */
	function _additionals($rows)
	{
		/*
		* Get editor name
		*/
		$count = count($rows);
		
		for ($i=0, $n=$count; $i < $n; $i++) {

			$query = 'SELECT name'
				. ' FROM #__users'
				. ' WHERE id = '.$rows[$i]->modified_by
				;

			$this->_db->setQuery( $query );
			$rows[$i]->editor = $this->_db->loadResult();
			
			/*
			* Get nr of assigned events
			*/
			$query = 'SELECT COUNT( id )'
				.' FROM #__eventlist_events'
				.' WHERE locid = ' . (int)$rows[$i]->id
				;
					
			$this->_db->setQuery($query);
			$rows[$i]->assignedevents = $this->_db->loadResult();
		}

		return $rows;
	}

	/**
	 * Method to (un)publish a venue
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user 	=& JFactory::getUser();
		$userid = $user->get('id');

		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__eventlist_venues'
					. ' SET published = '. (int) $publish
					. ' WHERE id IN ('. $cids .')'
					. ' AND ( checked_out = 0 OR ( checked_out = ' .$userid. ' ) )'
					;

			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
	}

	/**
	 * Method to move a venue
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function move($direction)
	{
		$row =& JTable::getInstance('eventlist_venues', '');

		if (!$row->load( $this->_id ) ) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->move( $direction )) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to remove a venue
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function delete($cid)
	{
		$cids = implode( ',', $cid );

		$query = 'SELECT v.id, v.venue, COUNT( e.locid ) AS numcat'
				. ' FROM #__eventlist_venues AS v'
				. ' LEFT JOIN #__eventlist_events AS e ON e.locid = v.id'
				. ' WHERE v.id IN ('. $cids .')'
				. ' GROUP BY v.id'
				;
		$this->_db->setQuery( $query );

		if (!($rows = $this->_db->loadObjectList())) {
			JError::raiseError( 500, $this->_db->stderr() );
			return false;
		}

		$err = array();
		$cid = array();
		foreach ($rows as $row) {
			if ($row->numcat == 0) {
				$cid[] = $row->id;
			} else {
				$err[] = $row->venue;
			}
		}

		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'DELETE FROM #__eventlist_venues'
					. ' WHERE id IN ('. $cids .')'
					;

			$this->_db->setQuery( $query );

			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		if (count( $err )) {
			$cids 	= implode( ', ', $err );
    		$msg 	= JText::sprintf( 'VENUE ASSIGNED EVENT', $cids );
    		return $msg;
		} else {
			$total 	= count( $cid );
			$msg 	= $total.' '.JText::_('VENUES DELETED');
			return $msg;
		}
	}
}
?>