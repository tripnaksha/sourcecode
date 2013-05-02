<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: view.raw.php 51 2010-01-30 10:49:58Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

/**
 * Akeeba Backup Configuration AJAX proxy class (view part)
 *
 */
class AkeebaViewConfig extends JView
{
	function display()
	{
		$result = false;

		$ajax = JRequest::getCmd('ajax');
		switch($ajax)
		{
			// FTP Connection test for DirectFTP
			case 'testftp':
				// Grab request parameters
				$config = array(
					'host' => JRequest::getVar('host'),
					'port' => JRequest::getVar('port'),
					'user' => JRequest::getVar('user'),
					'pass' => JRequest::getVar('pass'),
					'initdir' => JRequest::getVar('initdir'),
					'usessl' => JRequest::getVar('usessl') == 'true',
					'passive' => JRequest::getVar('passive') == 'true'
				);

				// Perform the FTP connection test
				$test = new AEArchiverDirectftp();
				$test->initialize('', $config);
				$errors = $test->getError();
				if(empty($errors))
				{
					$result = true;
				}
				else
				{
					$result = $errors;
				}
				break;

			// Unrecognized AJAX task
			default:
				$result = false;
				break;
		}

		$this->assign('result', $result);
		parent::display('raw');
	}
}