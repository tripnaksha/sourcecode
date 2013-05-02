<?php
/**
 * @version 1.0 $Id: group.php 662 2008-05-09 22:28:53Z schlu $
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

//no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * EventList Component Group Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class EventListModelGroup extends JModel
{
	/**
	 * Event id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Event data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Members data string
	 *
	 * @var string
	 */
	var $_members = null;

	/**
	 * available data array
	 *
	 * @var array
	 */
	var $_available = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int event identifier
	 */
	function setId($id)
	{
		// Set event id and wipe data
		$this->_id	    = $id;
		$this->_data	= null;
	}

	/**
	 * Logic for the Group edit screen
	 *
	 */
	function &getData()
	{

		if ($this->_loadData())
		{

		}
		else  $this->_initData();

		//$this->_loadData();
		return $this->_data;
	}

	/**
	 * Method to load content data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function _loadData()
	{
		//Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT *'
					. ' FROM #__eventlist_groups'
					. ' WHERE id = '.$this->_id
					;
			$this->_db->setQuery($query);

			$this->_data = $this->_db->loadObject();

			return (boolean) $this->_data;
			
		}
		return true;
	}

	/**
	 * Method to get the members data
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function &getMembers()
	{
    	$members = $this->_members();

    	$users = array();

    	if ($members) {
        	$query = 'SELECT id AS value, username, name'
        			. ' FROM #__users'
        			. ' WHERE id IN ('.$members.')'
        			. ' ORDER BY name ASC'
        			;

        	$this->_db->setQuery( $query );

        	$users = $this->_db->loadObjectList();

			$k = 0;
			for($i=0, $n=count( $users ); $i < $n; $i++) {
    			$item = &$users[$i];

				$item->text = $item->name.' ('.$item->username.')';

    			$k = 1 - $k;
			}

    	}

    	return $users;
	}

	/**
	 * Method to get the available users
	 *
	 * @access	public
	 * @return	mixed
	 * @since	0.9
	 */
	function &getAvailable()
	{
		$members = $this->_members();

    	// get non selected members
    	$query = 'SELECT id AS value, username, name FROM #__users';
    	$query .= ' WHERE block = 0' ;

    	if ($members) $query .= ' AND id NOT IN ('.$members.')' ;

    	$query .= ' ORDER BY name ASC';

    	$this->_db->setQuery($query);

    	$this->_available = $this->_db->loadObjectList();

    	$k = 0;
		for($i=0, $n=count( $this->_available ); $i < $n; $i++) {
    		$item = &$this->_available[$i];

			$item->text = $item->name.' ('.$item->username.')';

    		$k = 1 - $k;
		}

		return $this->_available;
	}

	/**
	 * Method to get the selected members
	 *
	 * @access	public
	 * @return	string
	 * @since	0.9
	 */
	function _members()
	{
    	//get selected members
		if ($this->_id){
			$query = 'SELECT member'
					. ' FROM #__eventlist_groupmembers'
					. ' WHERE group_id = '.$this->_id;

			$this->_db->setQuery ($query);

			$member_ids = $this->_db->loadResultArray();

			if (is_array($member_ids)) $this->_members = implode(',', $member_ids);
		}
		return $this->_members;
	}

	/**
	 * Method to initialise the group data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function _initData()
	{
		//Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$group = new stdClass();
			$group->id					= 0;
			$group->name				= null;
			$group->description			= null;
			$this->_data				= $group;
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to checkin/unlock the item
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function checkin()
	{
		if ($this->_id)
		{
			$group = & JTable::getInstance('eventlist_groups', '');
			return $group->checkin($this->_id);
		}
		return false;
	}


	
	/**
	 * Method to checkout/lock the item
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the item out
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the group with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$group = & JTable::getInstance('eventlist_groups', '');
			return $group->checkout($uid, $this->_id);
		}
		return false;
	}

	/**
	 * Tests if the event is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	0.9
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		} elseif ($this->_id < 1) {
			return false;
		} else {
			JError::raiseWarning( 0, 'Unable to Load Data');
			return false;
		}
	}
	/**
	 * Method to store the group
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		$row =& JTable::getInstance('eventlist_groups', '');

		//Bind the form fields to the table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the data is valid
		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		//Store the table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$members =JRequest::getVar('maintainers',  '', 'post', 'array');

		$this->_db->setQuery ('DELETE FROM #__eventlist_groupmembers WHERE group_id = '.$row->id);
		$this->_db->query();

		foreach ($members as $member){
			$member = intval($member);
			$this->_db->setQuery ("INSERT INTO #__eventlist_groupmembers (group_id, member) VALUES ($row->id, $member)");
			$this->_db->query();
		}

		return true;
	}
}
?>