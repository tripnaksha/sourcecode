<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: view.raw.php 188 2010-07-18 14:15:12Z nikosdion $
 * @since 2.1
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

class AkeebaViewLight extends JView
{
	public function display($tpl = null)
	{
		$task = JRequest::getCmd('task','default');

		switch($task)
		{
			case 'step':
				$kettenrad =& AEFactory::getKettenrad();
				$array = $kettenrad->getStatusArray();
				$this->assign('array', $array);
				break;

			case 'error':
				$this->assign('errormessage', JRequest::getVar('error',''));
				break;

			case 'done':
				break;

			case 'default':
			default:
				$model =& $this->getModel();
				$this->assignRef('profilelist', $model->getProfiles());
				break;
		}

		parent::display(JRequest::getCmd('tpl',null));
	}
}
