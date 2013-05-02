<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: profiles.php 126 2010-04-28 23:00:40Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

/**
 * The Profiles MVC model class
 *
 */
class AkeebaModelProfiles extends JModel
{
	/** @var int Profile ID */
	private $_id;

	/** @var int A list of all IDs passed to the page */
	private $id_list = array();

	/** @var stdClass Profile object */
	private $_profile;

	/** @var JTable The profiles table being updated */
	private $_table;

	/**
	 * Constructor. Sets the internal reference to Profile ID based on the request parameters.
	 *
	 */
	public function __construct()
	{
		global $mainframe;
		if(!is_object($mainframe)) {
			$app =& JFactory::getApplication();
		}
		else
		{
			$app = $mainframe;
		}

		parent::__construct();

		// Get the pagination request variables
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$limitstart = $app->getUserStateFromRequest(JRequest::getCmd('option','com_akeeba').'profileslimitstart','limitstart',0);

		// Set the page pagination variables
		$this->setState('limit',$limit);
		$this->setState('limitstart',$limitstart);

		// Get the ID from the request
		$cid = JRequest::getVar('cid', false);
		if($cid)
		{
			$cid = JRequest::getVar('cid', false, 'DEFAULT', 'array');
			if(!empty($cid))
			{
				$this->id_list = array();
				foreach($cid as $id)
				{
					$this->id_list[] = $id;
				}
			}
			$id = $cid[0];
		}
		else
		{
			$id = JRequest::getInt('id', 0);
			$this->id_list = array($id);
		}

		$this->setId($id);
	}

	/**
	 * Sets a Profile ID and resets internal data
	 *
	 * @param int $id Profile ID
	 */
	public function setId($id=0)
	{
		$this->_id = $id;
		$this->_profile = null;
	}

	/**
	 * Returns the currently set profile ID
	 * @return int
	 */
	public function getId()
	{
		return $this->_id;
	}

	public function getAllIds()
	{
		return $this->id_list;
	}

	/**
	 * Returns the entry for the profile whose ID is loaded in the model
	 *
	 * @return stdClass An object representing the profile
	 */
	public function &getProfile()
	{
		if(empty($this->_profile))
		{
			$db =& $this->getDBO();
			$query = "SELECT * FROM ".$db->nameQuote('#__ak_profiles')." WHERE ".
				$db->nameQuote('id')." = ".$this->_id;
			$db->setQuery($query);
			$this->_profile = $db->loadObject();
		}
		return $this->_profile;
	}

	/**
	 * Gets a list of all the profiles as an array of objects
	 *
	 * @param bool $overrideLimits If set, it will list all entries, without applying limits
	 * @return array List of profiles
	 */
	public function getProfilesList($overrideLimits = false)
	{
		if( empty($this->_list) )
		{
			$db =& $this->getDBO();
			$query = "SELECT * FROM ".$db->nameQuote('#__ak_profiles');
			$query .= ' ORDER BY '.$db->nameQuote('id').' ASC';
			$limitstart = $this->getState('limitstart');
			$limit = $this->getState('limit');
			if(!$overrideLimits)
				$this->_list = $this->_getList($query, $limitstart, $limit);
			else
				$this->_list = $this->_getList($query);
		}

		return $this->_list;
	}

	/**
	 * Saves a profile
	 *
	 * @param object|array $data The data to be bound and saved
	 * @return bool True on success
	 */
	public function save($data)
	{
		// Get the table
		$this->_table =& $this->getTable('Profile');
		// Try to save the data
		if(!$this->_table->save($data))
		{
			// Oops... Something wrong happened
			$this->setError($this->_table->getError());
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Returns the last saved table
	 *
	 * @return JTable
	 */
	public function &getSavedTable()
	{
		return $this->_table;
	}

	/**
	 * Attempts to delete the record whose ID is set in the model. Fails upon detecting
	 * an attempt to delete the default profile.
	 *
	 * @return bool True on success
	 */
	public function delete()
	{
		// Do not delete the default profile
		if($this->_id == 1)
		{
			$this->setError(JText::_('PROFILE_CANNOT_DELETE_DEFAULT'));
			return false;
		}
		// Check for invalid id's (not numeric, or <= 0)
		elseif( (!is_numeric($this->_id)) || ($this->_id <= 0) )
		{
			$this->setError(JText::_('PROFILE_INVALID_ID'));
			return false;
		}
		$db =& $this->getDBO();

		// Delete the profile itself
		$sql = 'DELETE FROM '.$db->nameQuote('#__ak_profiles').' WHERE '.
			$db->nameQuote('id').' = '.$this->_id;
		$db->setQuery($sql);
		if(!$db->query())
		{
			$this->setError($db->getError());
			return false;
		}

		return true;
	}

	/**
	 * Tries to copy the profile whose ID is set in the model to a new record
	 *
	 * @return bool True on success
	 */
	public function copy()
	{
		// Check for invalid id's (not numeric, or <= 0)
		if( (!is_numeric($this->_id)) || ($this->_id <= 0) )
		{
			$this->setError(JText::_('PROFILE_INVALID_ID'));
			return false;
		}

		$db =& $this->getDBO();

		// 1. Copy the profile itself
		// -- Load the profile using the TableProfile class
		$profileTable = $this->getTable('profile');
		if(!$profileTable->load($this->_id))
		{
			$this->setError($profileTable->getError());
			return false;
		}

		// Force creating a new record
		$profileTable->id = 0;
		// Try to save the new record
		if($profileTable->check())
		{
			if(!$profileTable->store(true))
			{
				$this->setError($profileTable->getError());
				return false;
			}
		}
		else
		{
			$this->setError($profileTable->getError());
			return false;
		}
		// Get the new Profile ID
		$newProfileID = $profileTable->id;

		$this->setId($newProfileID);

		return true;
	}

	/**
	 * Ensures that the user passed on a valid ID.
	 *
	 * @return bool True if the ID belongs to a valid profile, false otherwise
	 */
	public function checkID()
	{
		// Check for invalid id's (not numeric, or <= 0)
		if( (!is_numeric($this->_id)) || ($this->_id <= 0) ) return false;

		// Check for existing ID, or return false
		$myProfile =& $this->getProfile();
		return is_object($myProfile);
	}

	/**
	 * Get a pagination object
	 *
	 * @access public
	 * @return JPagination
	 *
	 */
	public function getPagination()
	{
		if( empty($this->_pagination) )
		{
			// Import the pagination library
			jimport('joomla.html.pagination');

			// Prepare pagination values
			$total = $this->getTotal();
			$limitstart = $this->getState('limitstart');
			$limit = $this->getState('limit');

			// Create the pagination object
			$this->_pagination = new JPagination($total, $limitstart, $limit);
		}

		return $this->_pagination;
	}

	/**
	 * Get number of profile items
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotal()
	{
		if( empty($this->_total) )
		{
			$db =& $this->getDBO();
			$query = "SELECT *  FROM ".$db->nameQuote('#__ak_profiles');
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}


}