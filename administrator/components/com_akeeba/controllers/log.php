<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: log.php 211 2010-08-12 21:34:52Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.controller');

/**
 * Log view controller class
 *
 */
class AkeebaControllerLog extends JController
{
	public function  __construct($config = array()) {
		parent::__construct($config);
		if(AKEEBA_JVERSION=='16')
		{
			// Access check, Joomla! 1.6 style.
			if (!JFactory::getUser()->authorise('akeeba.download', 'com_akeeba')) {
				$this->setRedirect('index.php?option=com_akeeba');
				return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
				$this->redirect();
			}
		}
	}

	/**
	 * Display the log page
	 *
	 */
	public function display()
	{
		AEPlatform::load_configuration(AEPlatform::get_active_profile());

		parent::display();
	}

	// Renders the contents of the log's iframe
	public function iframe()
	{
		AEPlatform::load_configuration(AEPlatform::get_active_profile());

		parent::display();
	}

	public function download()
	{
		AEPlatform::load_configuration(AEPlatform::get_active_profile());

		$tag = JRequest::getCmd('tag',null);

		$filename = AEUtilLogger::logName($tag);

		@ob_end_clean(); // In case some braindead plugin spits its own HTML
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Content-Description: File Transfer");
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="Akeeba Backup Debug Log.txt"');
		echo "WARNING: Do not copy and paste lines from this file!\r\n";
		echo "You are supposed to ZIP and attach it in your support forum post.\r\n";
		echo "If you fail to do so, your support request will receive minimal priority.\r\n";
		echo "\r\n";
		echo "--- START OF RAW LOG --\r\n";
		@readfile($filename); // The at sign is necessary to skip showing PHP errors if the file doesn't exist or isn't readable for some reason
		echo "--- END OF RAW LOG ---\r\n";
	}
}