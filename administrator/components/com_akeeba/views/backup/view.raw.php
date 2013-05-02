<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: view.raw.php 240 2010-08-29 21:18:32Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

/**
 * The RAW mode view class for the backup page. It serves a double purpose. On one hand,
 * it processes AJAX requests. On the other hand (when task=step), it displays the iframe
 * contents.
 *
 */
class AkeebaViewBackup extends JView
{
	function display()
	{
		$ajax = JRequest::getCmd('ajax', 'start');
		// $tag = JRequest::getCmd('tag',null);
		// Do not take into account the tag passed in the URL query, as this file
		// is only called on back-end backups.
		$tag = 'backend';

		$ret_array = array();

		switch($ajax)
		{
			case 'start':
				// Description is passed through a strict filter which removes HTML
				$description = JRequest::getString('description','','default', null);
				// The comment is passed through the Safe HTML filter (note: use 2 to force no filtering)
				$comment = JRequest::getString('comment','','default', 4);
				$jpskey = JRequest::getVar('jpskey','');

				// Try resetting the engine
				AECoreKettenrad::reset();

				// Remove any stale memory files left over from the previous step
				if(empty($tag)) $tag = AEPlatform::get_backup_origin();
				$memory_filename = AEUtilTempvars::get_storage_filename($tag);
				@unlink($memory_filename);

				$kettenrad =& AECoreKettenrad::load($tag);
				$options = array(
					'description'	=> $description,
					'comment'		=> $comment,
					'jpskey'		=> $jpskey
				);
				$kettenrad->setup($options);
				$kettenrad->tick();
				$ret_array  = $kettenrad->getStatusArray();
				$kettenrad->resetWarnings(); // So as not to have duplicate warnings reports
				AECoreKettenrad::save();
				break;

			case 'step':
				$kettenrad =& AECoreKettenrad::load($tag);
				$kettenrad->tick();
				$ret_array  = $kettenrad->getStatusArray();
				$kettenrad->resetWarnings(); // So as not to have duplicate warnings reports
				AECoreKettenrad::save();

				if($ret_array['HasRun'] == 1)
				{
					// Clean up
					AEFactory::nuke();
					AEUtilTempvars::reset();
				}
				break;

			default:
				break;
		}

		$json = json_encode($ret_array);
		$this->assign('json', $json);

		parent::display(JRequest::getCmd('tpl',null));
	}
}
?>