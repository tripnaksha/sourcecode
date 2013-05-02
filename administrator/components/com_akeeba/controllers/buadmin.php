<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $id$
 * @version $Id: buadmin.php 211 2010-08-12 21:34:52Z nikosdion $
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.controller');

/**
 * The Backup Administrator class
 *
 */
class AkeebaControllerBuadmin extends JController
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
	 * Show a list of backup attempts
	 *
	 */
	public function display()
	{
		parent::display();
	}

	/**
	 * Downloads the backup file of a specific backup attempt,
	 * if it's available
	 *
	 */
	public function download()
	{
		$cid = JRequest::getVar('cid',array(),'default','array');
		$id = JRequest::getInt('id');
		$part = JRequest::getInt('part',-1);

		if(empty($id))
		{
			if(is_array($cid) && !empty($cid))
			{
				$id = $cid[0];
			}
			else
			{
				$id = -1;
			}
		}

		if($id <= 0)
		{
			$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_ERROR_INVALIDID'), 'error');
			parent::display();
			return;
		}

		$stat = AEPlatform::get_statistics($id);
		$allFilenames = AEUtilStatistics::get_all_filenames($stat);

		// Check single part files
		if( (count($allFilenames) == 1) && ($part == -1) )
		{
			$filename = array_shift($allFilenames);
		}
		elseif( (count($allFilenames) > 0) && (count($allFilenames) > $part) && ($part >= 0) )
		{
			$filename = $allFilenames[$part];
		}
		else
		{
			$filename = null;
		}

		if(is_null($filename) || empty($filename) || !@file_exists($filename) )
		{
			$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_ERROR_INVALIDDOWNLOAD'), 'error');
			parent::display();
			return;
		}
		else
		{
			$basename = @basename($filename);
			$filesize = @filesize($filename);
			$extension = strtolower(str_replace(".", "", strrchr($filename, ".")));

			JRequest::setVar('format','raw');
			@ob_end_clean();
			@clearstatcache();
			// Send MIME headers
			header('MIME-Version: 1.0');
			header('Content-Disposition: attachment; filename='.$basename);
			header('Content-Transfer-Encoding: binary');
			switch($extension)
			{
				case 'zip':
					// ZIP MIME type
					header('Content-Type: application/zip');
					break;

				default:
					// Generic binary data MIME type
					header('Content-Type: application/octet-stream');
					break;
			}
			// Notify of filesize, if this info is available
			if($filesize > 0) header('Content-Length: '.@filesize($filename));
			// Disable caching
			header('Expires: Mon, 20 Dec 1998 01:00:00 GMT');
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			if($filesize > 0)
			{
				// If the filesize is reported, use 2M chunks for echoing the data to the browser
				$blocksize = (2 << 20); //2M chunks
				$handle    = @fopen($filename, "r");
				// Now we need to loop through the file and echo out chunks of file data
				if($handle !== false) while(!@feof($handle)){
				    echo @fread($handle, $blocksize);
				}
			} else {
				// If the filesize is not reported, hope that readfile works
				@readfile($filename);
			}
			exit(0);
		}

	}

	/**
	 * Deletes one or several backup statistics records and their associated backup files
	 */
	public function remove()
	{
		$cid = JRequest::getVar('cid',array(),'default','array');
		$id = JRequest::getInt('id');
		if(empty($id))
		{
			if(!empty($cid) && is_array($cid))
			{
				foreach ($cid as $id)
				{
					$result = $this->_remove($id);
					if(!$result) $this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_ERROR_INVALIDID'), 'error');
				}
			}
			else
			{
				$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_ERROR_INVALIDID'), 'error');
				return;
			}
		}
		else
		{
			$result = $this->_remove($id);
			if(!$result) $this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_ERROR_INVALIDID'), 'error');
		}

		$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_MSG_DELETED'));

		parent::display();
	}

	/**
	 * Deletes backup files associated to one or several backup statistics records
	 */
	public function deletefiles()
	{
		$cid = JRequest::getVar('cid',array(),'default','array');
		$id = JRequest::getInt('id');
		if(empty($id))
		{
			if(!empty($cid) && is_array($cid))
			{
				foreach ($cid as $id)
				{
					$result = $this->_removeFiles($id);
					if(!$result) $this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_ERROR_INVALIDID'), 'error');
				}
			}
			else
			{
				$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_ERROR_INVALIDID'), 'error');
				return;
			}
		}
		else
		{
			$result = $this->_remove($id);
			if(!$result) $this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_ERROR_INVALIDID'), 'error');
		}

		$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_MSG_DELETEDFILE'));

		parent::display();
	}

	/**
	 * Removes the backup file linked to a statistics entry and the entry itself
	 *
	 * @return bool True on success
	 */
	private function _remove($id)
	{
		if($id <= 0)
		{
			$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_ERROR_INVALIDID'), 'error');
			return;
		}

		$model =& $this->getModel('statistics');
		return $model->delete($id);
	}

	/**
	 * Removes only the backup file linked to a statistics entry
	 *
	 * @return bool True on success
	 */
	private function _removeFiles($id)
	{
		if($id <= 0)
		{
			$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_ERROR_INVALIDID'), 'error');
			return;
		}

		$model =& $this->getModel('statistics');
		return $model->deleteFile($id);
	}

	public function showcomment()
	{
		$cid = JRequest::getVar('cid',array(),'default','array');
		$id = JRequest::getInt('id');

		if(empty($id))
		{
			if(is_array($cid) && !empty($cid))
			{
				$id = $cid[0];
			}
			else
			{
				$id = -1;
			}
		}

		if($id <= 0)
		{
			$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', JText::_('STATS_ERROR_INVALIDID'), 'error');
			parent::display();
			return;
		}

		JRequest::setVar('id', $id);

		parent::display();
	}

	/**
	 * Save an edited backup record
	 */
	public function save()
	{
		$id = JRequest::getInt('id');
		$description = JRequest::getString('description');
		$comment = JRequest::getVar('comment',null,'default','string',4);

		$statistic = AEPlatform::get_statistics(JRequest::getInt('id'));
		$statistic['description']	= $description;
		$statistic['comment']		= $comment;
		AEPlatform::set_or_update_statistics(JRequest::getInt('id'),$statistic,$self);

		if( !$this->getError() ) {
			$message = JText::_('STATS_LOG_SAVEDOK');
			$type = 'message';
		} else {
			$message = JText::_('STATS_LOG_SAVEERROR');
			$type = 'error';
		}
		$this->setRedirect(JURI::base().'index.php?option=com_akeeba&view=buadmin', $message, $type);
	}

	public function restore()
	{
		$id = null;
		$cid = JRequest::getVar('cid', array(), 'default', 'array');
		if(!empty($cid))
		{
			$id = intval($cid[0]);
			if($id <= 0) $id = null;
		}
		if(empty($id)) $id = JRequest::getInt('id', -1);
		if($id <= 0) $id = null;

		$url = JURI::base().'index.php?option=com_akeeba&view=restore&id='.$id;
		$this->setRedirect($url);
		$this->redirect();
		return;
	}
}