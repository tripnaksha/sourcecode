<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: profile.php 71 2010-02-22 22:17:01Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

/**
 * The JTable child implementing #__ak_profiles data handling
 *
 */
class TableProfile extends JTable
{
	/** @var int Primary key */
	public $id;

	/** @var string Profile description */
	public $description;

	/** @var string JSON-encoded configuration information */
	public $configuration;

	/** @var string JSON-encoded filter information */
	public $filters;

	/**
	 * Constructor
	 *
	 * @param JDatabase $db Joomla!'s database
	 */
	public function __construct( &$db )
	{
		parent::__construct('#__ak_profiles', 'id', $db);
	}

	/**
	 * Validation check
	 *
	 * @return bool True if the contents are valid
	 */
	public function check()
	{
		if(!$this->description)
		{
			$this->setError(JText::_('TABLE_PROFILE_NODESCRIPTION'));
			return false;
		}

		return true;
	}

	/**
	 * Overloads the delete method to ensure we're not deleting the default profile
	 *
	 * @param int $id Optional; the record id
	 */
	public function delete( $id=null )
	{
		if (($id==1) || ( is_null($id) && ($this->id == 1) ))
		{
			$this->setError(JText::_('TABLE_PROFILE_CANNOTDELETEDEFAULT'));
			return false;
		}
		else
		return parent::delete($id);
	}
}