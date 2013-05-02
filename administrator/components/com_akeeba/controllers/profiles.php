<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: profiles.php 211 2010-08-12 21:34:52Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.controller');

/**
 * MVC controller class for Profiles Administration page
 *
 */
class AkeebaControllerProfiles extends JController
{
	public function  __construct($config = array()) {
		parent::__construct($config);
		if(AKEEBA_JVERSION=='16')
		{
			// Access check, Joomla! 1.6 style.
			if (!JFactory::getUser()->authorise('core.admin', 'com_akeeba')) {
				$this->setRedirect('index.php?option=com_akeeba');
				return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
				$this->redirect();
			}
		}
	}

	/**
	 * Displays a list of profiles
	 *
	 */
	public function display()
	{
		parent::display();
	}

	/**
	 * Handles applying the changes (versus merely saving them)
	 */
	public function apply()
	{
		// Just delegate the task
		$this->save();
	}

	/**
	 * Processes saving an entry (new or old) and redirecting to the list view
	 *
	 */
	public function save()
	{
		$data = JRequest::get('POST');
		$task = JRequest::getCmd('task','save');

		$model =& $this->getModel('profiles');
		if($model->save($data))
		{
			// Show a "SAVE OK" message
			$message = JText::_('PROFILE_SAVE_OK');
			$type = 'message';
			if($task == 'apply')
			{
				$mytable =& $model->getSavedTable();
				$insertid = $mytable->id;
				$this->_switchProfile($insertid);
			}
		}
		else
		{
			// Show message on failure
			$message = JText::_('PROFILE_SAVE_ERROR');
			$message .= ' ['.$model->getError().']';
			$type = 'error';
		}

		// Redirect, based on task
		switch($task)
		{
			case 'save':
				$this->setRedirect('index.php?option='.JRequest::getCmd('option').'&view='.JRequest::getCmd('view'), $message, $type);
				break;

			case 'apply':
				$this->setRedirect('index.php?option='.JRequest::getCmd('option').'&view='.JRequest::getCmd('view').'&task=edit&id='.$insertid, $message, $type);
				break;
		}
	}

	/**
	 * Processes removing an entry and redirecting to list view
	 *
	 */
	public function remove()
	{
		// Capture active profile ID
		$session =& JFactory::getSession();
		$active_profile_id = $session->get('profile', null, 'akeeba');
		if(is_null($profile_id))
		{
			// No profile is set in the session; use default profile
			$session->set('profile', 1, 'akeeba');
			$active_profile_id = 1;
		}

		$model =& $this->getModel('profiles');

		// Capture profile to be deleted
		$id_list = $model->getAllIds();
		if(empty($id_list))
		{
			$message = JText::_('PROFILE_DELETE_ERROR');
			$type = 'error';
		}
		else
		{
			foreach($id_list as $deleted_profile)
			{
				$model->setId($deleted_profile);
				if($model->delete())
				{
					// Show a "SAVE OK" message
					$message = JText::_('PROFILE_DELETE_OK');
					$type = 'message';

					// If the deleted profile was the active profile, switch to default
					if($deleted_profile == $active_profile_id)
					{
						$this->_switchProfile(1);
						$configuration =& AEFactory::getConfiguration();
						AEPlatform::load_configuration(1);
					}
				}
				else
				{
					// Show message on failure
					$message = JText::_('PROFILE_DELETE_ERROR');
					$message .= ' ['.$model->getError().']';
					$type = 'error';
				}
			}
		}


		// Redirect
		$this->setRedirect('index.php?option='.JRequest::getCmd('option').'&view='.JRequest::getCmd('view'), $message, $type);
	}

	/**
	 * Shows a view where you can add a new record. Actually, delegates to edit().
	 *
	 */
	public function add()
	{
		$this->edit(); // Delegate execution
	}

	/**
	 * Shows the add/edit screen. Forces the layout, in order to show the correct form.
	 *
	 */
	public function edit()
	{
		JRequest::setVar('hidemainmenu', 1);
		JRequest::setVar('layout', 'default_edit');
		parent::display();
	}

	/**
	 * Copies the selected profile into a new record at the end of the list
	 *
	 */
	public function copy()
	{
		$model =& $this->getModel('profiles');
		if($model->copy())
		{
			// Show a "COPY OK" message
			$message = JText::_('PROFILE_COPY_OK');
			$type = 'message';
			$this->_switchProfile( $model->getId() );
		}
		else
		{
			// Show message on failure
			$message = JText::_('PROFILE_COPY_ERROR');
			$message .= ' ['.$model->getError().']';
			$type = 'error';
		}
		// Redirect
		$this->setRedirect('index.php?option='.JRequest::getCmd('option').'&view='.JRequest::getCmd('view'), $message, $type);
	}

	/**
	 * Cancel profile editing
	 *
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option='.JRequest::getCmd('option').'&view='.JRequest::getCmd('view'));
	}


	private function _switchProfile($id)
	{
		$session =& JFactory::getSession();
		$session->set('profile', $id, 'akeeba');
	}
}