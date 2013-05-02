<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 2, or later
 * @version $Id: light.php 241 2010-08-30 17:30:52Z nikosdion $
 * @since 2.1
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

defined('AKEEBA_BACKUP_ORIGIN') or define('AKEEBA_BACKUP_ORIGIN','lite');

// Load framework base classes
jimport('joomla.application.component.controller');

class AkeebaControllerLight extends JController
{
	/**
	 * Controller for the default task (login & profile selection)
	 */
	public function display()
	{
		// Enforce raw mode - I need to be in full control!
		$document =& JFactory::getDocument();
		if( $document->getType() != 'raw' )
		{
			$url = JURI::base().'index.php?option=com_akeeba&view=light&format=raw';
			$this->setRedirect($url);
			$this->redirect();
			return;
		}
		$document->setType('raw');
		parent::display(false);
	}

	/**
	 * Tries to authenticate the user and start the backup, or send him back to the default task
	 */
	public function authenticate()
	{
		// Enforce raw mode - I need to be in full control!
		$document =& JFactory::getDocument();
		$document->setType('raw');
		if(!$this->_checkPermissions())
		{
			parent::redirect();
		}
		else
		{
			$this->_setProfile();
			jimport('joomla.utilities.date');
			AECoreKettenrad::reset();
			$memory_filename = AEUtilTempvars::get_storage_filename(AKEEBA_BACKUP_ORIGIN);
			@unlink($memory_filename);
			$kettenrad =& AECoreKettenrad::load(AKEEBA_BACKUP_ORIGIN);
			$user =& JFactory::getUser();
			$userTZ = $user->getParam('timezone',0);
			$dateNow = new JDate();
			$dateNow->setOffset($userTZ);
			$description = JText::_('BACKUP_DEFAULT_DESCRIPTION').' '.$dateNow->toFormat(JText::_('DATE_FORMAT_LC2'));
			$options = array(
				'description'	=> $decription,
				'comment'		=> ''
			);
			$kettenrad->setup($options);
			$ret = $kettenrad->tick();
			AECoreKettenrad::save(AKEEBA_BACKUP_ORIGIN);
			$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=light&task=step&key='.JRequest::getVar('key').'&profile='.JRequest::getInt('profile').'&format=raw');
		}
	}

	/**
	 * Step through the backup, informing user of the progress
	 */
	public function step()
	{
		// Enforce raw mode - I need to be in full control!
		$document =& JFactory::getDocument();
		$document->setType('raw');

		JRequest::setVar('tpl','step');

		$kettenrad =& AECoreKettenrad::load(AKEEBA_BACKUP_ORIGIN);
		$array = $kettenrad->getStatusArray();

		if($array['Error'] != '')
		{
			// An error occured
			$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=light&format=raw&task=error&error='.$array['Error']);
			parent::redirect();
		}
		elseif($array['HasRun'] == 1)
		{
			// All done
			$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=light&format=raw&task=done');
			parent::redirect();
		}
		else
		{
			$kettenrad->tick();
			AECoreKettenrad::save(AKEEBA_BACKUP_ORIGIN);
			parent::display();
		}
	}

	/**
	 * Informs the user of an error condition (poor soul, he can't fix it w/out backend access)
	 */
	public function error()
	{
		// Enforce raw mode - I need to be in full control!
		$document =& JFactory::getDocument();
		$document->setType('raw');
		JRequest::setVar('tpl','error');
		parent::display();
	}

	/**
	 * Informs the user that all is done
	 */
	public function done()
	{
		// Enforce raw mode - I need to be in full control!
		$document =& JFactory::getDocument();
		$document->setType('raw');
		JRequest::setVar('tpl','done');
		parent::display();
	}

	/**
	 * Check that the user has sufficient permissions, or die in error
	 *
	 */
	private function _checkPermissions()
	{
		$component =& JComponentHelper::getComponent( 'com_akeeba' );
		$params = new JParameter($component->params);

		// Is frontend backup enabled?
		$febEnabled = $params->get('frontend_enable',0) != 0;
		if(!$febEnabled)
		{
			$message = JText::_('ERROR_NOT_ENABLED');
			$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=light&format=raw', $message, 'error');
			return false;
		}

		// Is the key good?
		$key = JRequest::getVar('key');
		$validKey=$params->get('frontend_secret_word','');
		$validKeyTrim = trim($validKey);
		if( ($key != $validKey) || (empty($validKeyTrim)) )
		{
			$message = JText::_('ERROR_INVALID_KEY');
			$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=light&format=raw', $message, 'error');
			return false;
		}

		return true;
	}

	private function _setProfile()
	{
		// Set profile
		$profile = JRequest::getInt('profile', 1);
		if(!is_numeric($profile)) $profile = 1;
		$session =& JFactory::getSession();
		$session->set('profile', $profile, 'akeeba');
		// Reload registry
		$registry =& AEFactory::getConfiguration();
		AEPlatform::load_configuration();
	}
}