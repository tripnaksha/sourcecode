<?php
/**
 * @version 1.0 $Id: details.php 662 2008-05-09 22:28:53Z schlu $
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
 * EventList Component Details Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class EventListModelDetails extends JModel
{
	/**
	 * Details data in details array
	 *
	 * @var array
	 */
	var $_details = null;


	/**
	 * registeres in array
	 *
	 * @var array
	 */
	var $_registers = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$id = JRequest::getInt('id');
		$this->setId((int)$id);
	}

	/**
	 * Method to set the details id
	 *
	 * @access	public
	 * @param	int	details ID number
	 */

	function setId($id)
	{
		// Set new details ID and wipe data
		$this->_id			= $id;
	}

	/**
	 * Method to get event data for the Detailsview
	 *
	 * @access public
	 * @return array
	 * @since 0.9
	 */
	function &getDetails( )
	{
		/*
		 * Load the Category data
		 */
		if ($this->_loadDetails())
		{
			$user	= & JFactory::getUser();

			// Is the category published?
			if (!$this->_details->published && $this->_details->catsid)
			{
				JError::raiseError( 404, JText::_("CATEGORY NOT PUBLISHED") );
			}

			// Do we have access to the category?
			if (($this->_details->access > $user->get('aid')) && $this->_details->catsid)
			{
				JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
			}

		}

		return $this->_details;
	}

	/**
	 * Method to load required data
	 *
	 * @access	private
	 * @return	array
	 * @since	0.9
	 */
	function _loadDetails()
	{
		if (empty($this->_details))
		{
			// Get the WHERE clause
			$where	= $this->_buildDetailsWhere();

			$query = 'SELECT a.id AS did, a.dates, a.enddates, a.title, a.times, a.endtimes, a.datdescription, a.meta_keywords, a.meta_description, a.datimage, a.registra, a.unregistra, a.locid, a.catsid, a.created_by,'
					. ' l.id AS locid, l.name as venue, "" as city, "" as state, "" as url, l.intro as locdescription, "" as locimage, "" as city, "" as plz, "" as street, "" as country, "" as map, l.userid AS venueowner,'
					. ' c.catname, c.published, c.access,'
					. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug,'
					. ' CASE WHEN CHAR_LENGTH(l.name) THEN CONCAT_WS(\':\', a.locid, l.name) ELSE a.locid END as venueslug,'
					. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug'
					. ' FROM #__eventlist_events AS a'
//					. ' LEFT JOIN #__eventlist_venues AS l ON a.locid = l.id'
					. ' LEFT JOIN #__trailList AS l ON l.id = a.locid'
					. ' LEFT JOIN #__eventlist_categories AS c ON c.id = a.catsid'
					. $where
					;
//die($query);
    		$this->_db->setQuery($query);
			$this->_details = $this->_db->loadObject();
			return (boolean) $this->_details;
		}
		return true;
	}

	/**
	 * Method to build the WHERE clause of the query to select the details
	 *
	 * @access	private
	 * @return	string	WHERE clause
	 * @since	0.9
	 */
	function _buildDetailsWhere()
	{
		$where = ' WHERE a.id = '.$this->_id;

		return $where;
	}

	/**
	 * Method to check if the user is allready registered
	 *
	 * @access	public
	 * @return	array
	 * @since	0.9
	 */
	function getUsercheck()
	{
		// Initialize variables
		$user 		= & JFactory::getUser();
		$userid		= (int) $user->get('id', 0);

		//usercheck
		$query = 'SELECT uid'
				. ' FROM #__eventlist_register'
				. ' WHERE uid = '.$userid
				. ' AND event = '.$this->_id
				;
		$this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}

	/**
	 * Method to get the registered users
	 *
	 * @access	public
	 * @return	object
	 * @since	0.9
	 */
	function getRegisters()
	{
		//avatars should be displayed
		$elsettings = & ELHelper::config();

		$avatar	= '';
		$join	= '';

		if ($elsettings->comunoption == 1 && $elsettings->comunsolution == 1) {
			$avatar = ', c.avatar';
			$join	= ' LEFT JOIN #__comprofiler as c ON c.user_id = r.uid';
		}

		$name = $elsettings->regname ? 'u.name' : 'u.username';

		//Get registered users
		$query = 'SELECT '.$name.' AS name, r.uid'
				. $avatar
				. ' FROM #__eventlist_register AS r'
				. ' LEFT JOIN #__users AS u ON u.id = r.uid'
				. $join
				. ' WHERE event = '.$this->_id
				;
		$this->_db->setQuery( $query );

		$this->_registers = $this->_db->loadObjectList();

		return $this->_registers;
	}

	/**
	 * Saves the registration to the database
	 *
	 * @access public
	 * @return true on success
	 * @since 0.7
	 */
	function userregister()
	{
		global $mainframe;

		$user 		= & JFactory::getUser();
		$elsettings = & ELHelper::config();
		$tzoffset	= $mainframe->getCfg('offset');

		$event 		= (int) $this->_id;
		$uid 		= (int) $user->get('id');

		// Must be logged in
		if ($uid < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		//IP
		$uip 		= $elsettings->storeip ? getenv('REMOTE_ADDR') : 'DISABLED';
		
		$obj = new stdClass();
		$obj->event 	= (int)$event;
		$obj->uid   	= (int)$uid;
		$obj->uregdate 	= gmdate('Y-m-d H:i:s');
		$obj->uip   	= $uip;
		$this->_db->insertObject('#__eventlist_register', $obj);

		return true;
	}
	/**
	 * Deletes a registered user
	 *
	 * @access public
	 * @return true on success
	 * @since 0.7
	 */
	function delreguser()
	{
		$user 	= & JFactory::getUser();

		$event 	= (int) $this->_id;
		$userid = $user->get('id');

		// Must be logged in
		if ($userid < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		$query = 'DELETE FROM #__eventlist_register WHERE event = '.$event.' AND uid= '.$userid;
		$this->_db->SetQuery( $query );

		if (!$this->_db->query()) {
				JError::raiseError( 500, $this->_db->getErrorMsg() );
		}

		return true;
	}
}
?>
