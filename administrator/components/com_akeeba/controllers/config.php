<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: config.php 211 2010-08-12 21:34:52Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.controller');

/**
 * The Configuration Editor controller class
 *
 */
class AkeebaControllerConfig extends JController
{
	public function  __construct($config = array()) {
		parent::__construct($config);
		if(AKEEBA_JVERSION=='16')
		{
			// Access check, Joomla! 1.6 style.
			if (!JFactory::getUser()->authorise('akeeba.configure', 'com_akeeba')) {
				$this->setRedirect('index.php?option=com_akeeba');
				return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
				$this->redirect();
			}
		}
	}

	/**
	 * Displays the editor page
	 *
	 */
	public function display()
	{
		parent::display();
	}

	/**
	 * Handle the apply task which saves settings and shows the editor again
	 *
	 */
	public function apply()
	{
		// Get the var array from the request
		$var = JRequest::getVar('var', array(), 'default', 'array');
		// Make it into Akeeba Engine array format
		$data = array();
		foreach($var as $key => $value)
		{
			$data[$key] = $value;
		}
		// Forbid stupidly selecting the site's root as the output or temporary directory
		if( array_key_exists('akeeba.basic.output_directory', $data) )
		{
			$folder = $data['akeeba.basic.output_directory'];
			$folder = AEUtilFilesystem::translateStockDirs( $folder, true, true );

			$check = AEUtilFilesystem::translateStockDirs( '[SITEROOT]', true, true );

			if($check == $folder)
			{
				JError::raiseWarning(503, JText::_('CONFIG_OUTDIR_ROOT'));
				$data['akeeba.basic.output_directory'] = '[DEFAULT_OUTPUT]';
			}
		}
		if( array_key_exists('akeeba.basic.temporary_directory', $data) )
		{
			$folder = $data['akeeba.basic.temporary_directory'];
			$folder = AEUtilFilesystem::translateStockDirs( $folder, true, true );

			$check = AEUtilFilesystem::translateStockDirs( '[SITEROOT]', true, true );

			if($check == $folder)
			{
				JError::raiseWarning(503, JText::_('CONFIG_TMPDIR_ROOT'));
				$data['akeeba.basic.temporary_directory'] = '[SITETMP]';
			}
		}

		// Merge it
		$config =& AEFactory::getConfiguration();
		$config->mergeArray($data, false, false);
		// Save configuration
		AEPlatform::save_configuration();

		$this->setRedirect(JURI::base().'index.php?option='.JRequest::getCmd('option').'&view=config', JText::_('CONFIG_SAVE_OK'));
	}

	/**
	 * Handle the save task which saves settings and returns to the cpanel
	 *
	 */
	public function save()
	{
		$this->apply();
		$this->setRedirect(JURI::base().'index.php?option='.JRequest::getCmd('option'), JText::_('CONFIG_SAVE_OK'));
	}

	/**
	 * Handle the cancel task which doesn't save anything and returns to the cpanel
	 *
	 */
	public function cancel()
	{
		$this->setRedirect(JURI::base().'index.php?option='.JRequest::getCmd('option'));
	}
}