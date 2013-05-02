<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: cpanel.php 69 2010-02-17 23:13:10Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

/**
 * The Control Panel model
 *
 */
class AkeebaModelCpanel extends JModel
{
	/**
	 * Contructor; dummy for now
	 *
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get an array of icon definitions for the Control Panel
	 *
	 * @return array
	 */
	public function getIconDefinitions()
	{
		AEPlatform::load_version_defines();
		$core	= $this->loadIconDefinitions(JPATH_COMPONENT_ADMINISTRATOR.DS.'views');
		$pro	= $this->loadIconDefinitions(JPATH_COMPONENT_ADMINISTRATOR.DS.'plugins'.DS.'views');
		$ret = array_merge_recursive($core, $pro);

		return $ret;
	}

	private function loadIconDefinitions($path)
	{
		$ret = array();

		if(!@file_exists($path.DS.'views.ini')) return $ret;

		$ini_data = AEUtilINI::parse_ini_file($path.DS.'views.ini', true);
		if(!empty($ini_data))
		{
			foreach($ini_data as $view => $def)
			{
				$task = array_key_exists('task',$def) ? $def['task'] : null;
				$ret[$def['group']][] = $this->_makeIconDefinition($def['icon'], JText::_($def['label']), $view, $task);
			}
		}

		return $ret;
	}

	/**
	 * Returns a list of available backup profiles, to be consumed by JHTML in order to build
	 * a drop-down
	 *
	 * @return array
	 */
	public function getProfilesList()
	{
		$db =& $this->getDBO();
		$query = "SELECT ".$db->nameQuote('id').", ".$db->nameQuote('description').
				" FROM ".$db->nameQuote('#__ak_profiles').
				" ORDER BY ".$db->nameQuote('id')." ASC";
		$db->setQuery($query);
		$rawList = $db->loadAssocList();

		$options = array();
		if(!is_array($rawList)) return $options;

		foreach($rawList as $row)
		{
			$options[] = JHTML::_('select.option', $row['id'], $row['description']);
		}

		return $options;
	}

	/**
	 * Returns the active Profile ID
	 *
	 * @return int The active profile ID
	 */
	public function getProfileID()
	{
		$session =& JFactory::getSession();
		return $session->get('profile', null, 'akeeba');
	}

	/**
	 * Creates an icon definition entry
	 *
	 * @param string $iconFile The filename of the icon on the GUI button
	 * @param string $label The label below the GUI button
	 * @param string $view The view to fire up when the button is clicked
	 * @return array The icon definition array
	 */
	public function _makeIconDefinition($iconFile, $label, $view = null, $task = null )
	{
		return array(
			'icon'	=> $iconFile,
			'label'	=> $label,
			'view'	=> $view,
			'task'	=> $task
		);
	}

	/**
	 * Was the last backup a failed one? Used to apply magic settings as a means of
	 * troubleshooting.
	 *
	 * @return bool
	 */
	public function isLastBackupFailed()
	{
		// Get the last backup record ID
		$list = AEPlatform::get_statistics_list(0,1);
		if(empty($list)) return false;
		$id = $list[0];

		$statmodel->setId($id);
		$record = AEPlatform::get_statistics($id);

		return ($record['status'] == 'fail');
	}

}