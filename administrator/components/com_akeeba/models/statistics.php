<?php
/**
 * @package AkeebaBackup
 * @version $Id: statistics.php 179 2010-07-03 10:13:24Z nikosdion $
 * @license GNU General Public License, version 2 or later
 * @author Nicholas K. Dionysopoulos
 * @copyright Copyright 2006-2009 Nicholas K. Dionysopoulos
 * @since 1.3
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Akeeba statistics model class
 * used for all requirements of backup statistics in JP
 *
 */
class AkeebaModelStatistics extends JModel
{
	/** @var JPagination The JPagination object, used in the GUI */
	private $_pagination;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		global $mainframe;
		if(!is_object($mainframe)) {
			$app =& JFactory::getApplication();
		} else {
			$app = $mainframe;
		}

		parent::__construct();

		// Get the pagination request variables
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$limitstart = $app->getUserStateFromRequest(JRequest::getCmd('option','com_akeeba') .'profileslimitstart','limitstart',0);

		// Set the page pagination variables
		$this->setState('limit',$limit);
		$this->setState('limitstart',$limitstart);
	}


	/**
	 * Returns the same list as getStatisticsList(), but includes an extra field
	 * named 'meta' which categorises attempts based on their backup archive status
	 *
	 * @return array An object array of backup attempts
	 */
	public function &getStatisticsListWithMeta($overrideLimits = false)
	{
		$limitstart = $this->getState('limitstart');
		$limit = $this->getState('limit');
		if($overrideLimits)
		{
			$limitstart = 0;
			$limit = 0;
		}
		$allStats =& AEPlatform::get_statistics_list($limitstart, $limit);
		$valid =& AEPlatform::get_valid_backup_records();
		if(empty($valid)) $valid = array();

		// This will hold the entries whose files are no longer present and are
		// not already marked as such in the database
		$updateNonExistent = array();

		if(!empty($allStats))
		{
			$new_stats = array();

			foreach($allStats as $stat)
			{
				$total_size = 0;
				if(in_array($stat['id'], $valid))
				{
					$archives = AEUtilStatistics::get_all_filenames($stat);
					$stat['meta'] = (count($archives) > 0) ? 'ok' : 'obsolete';

					if($stat['meta'] == 'ok')
					{
						$total_size = 0;
						foreach($archives as $filename)
						{
							$total_size += @filesize($filename);
						}
					}
					else
					{
						if($stat['filesexist']) {
							$updateNonExistent[] = $stat['id'];
						}
					}
					$stat['size'] = $total_size;
				}
				else
				{
					switch($stat['status'])
					{
						case 'run':
							$stat['meta'] = 'pending';
							break;

						case 'fail':
							$stat['meta'] = 'fail';
							break;

						default:
							$stat['meta'] = 'obsolete';
							break;
					}
				}
				$new_stats[] = $stat;
			}
		}

		// Update records found as not having files any more
		if(count($updateNonExistent))
		{
			AEPlatform::invalidate_backup_records($updateNonExistent);
		}

		unset($valid);
		return $new_stats;
	}

	/**
	 * Returns the details of the latest backup as HTML
	 *
	 * @return string HTML
	 *
	 * @todo Move this into a helper class
	 */
	public function getLatestBackupDetails()
	{
		$db =& $this->getDBO();
		$query = 'SELECT max(id) FROM #__ak_stats';
		$db->setQuery($query);
		$id = $db->loadResult();

		$backup_types = AEUtilScripting::loadScripting();

		if(empty($id)) return '<p>'.JText::_('BACKUP_STATUS_NONE').'</p>';

		$record =& AEPlatform::get_statistics($id);

		jimport('joomla.utilities.date');

		switch($record['status'])
		{
			case 'run':
				$status = JText::_('STATS_LABEL_STATUS_PENDING');
				break;

			case 'fail':
				$status = JText::_('STATS_LABEL_STATUS_FAIL');
				break;

			case 'complete':
				$status = JText::_('STATS_LABEL_STATUS_OK');
				break;
		}

		switch($record['origin'])
		{
			case 'frontend':
				$origin = JText::_('STATS_LABEL_ORIGIN_FRONTEND');
				break;

			case 'backend':
				$origin = JText::_('STATS_LABEL_ORIGIN_BACKEND');
				break;

			case 'cli':
				$origin = JText::_('STATS_LABEL_ORIGIN_CLI');
				break;

			default:
				$origin = '&ndash;';
				break;
		}

		if(array_key_exists($record['type'],$backup_types['scripts']))
		{
			$type = AEPlatform::translate($backup_types['scripts'][ $record['type'] ]['text']);
		}
		else
		{
			$type = '';
		}

		$startTime = new JDate($record['backupstart']);

		$html = '<table>';
		$html .= '<tr><td>'.JText::_('STATS_LABEL_START').'</td><td>'.$startTime->toFormat(JText::_('DATE_FORMAT_LC4')).'</td></tr>';
		$html .= '<tr><td>'.JText::_('STATS_LABEL_DESCRIPTION').'</td><td>'.$record['description'].'</td></tr>';
		$html .= '<tr><td>'.JText::_('STATS_LABEL_STATUS').'</td><td>'.$status.'</td></tr>';
		$html .= '<tr><td>'.JText::_('STATS_LABEL_ORIGIN').'</td><td>'.$origin.'</td></tr>';
		$html .= '<tr><td>'.JText::_('STATS_LABEL_TYPE').'</td><td>'.$type.'</td></tr>';
		$html .= '</table>';

		return $html;
	}

	/**
	 * Delete the stats record whose ID is set in the model
	 * @param	int		$id		Backup record whose files we have to delete
	 * @return bool True on success
	 */
	public function delete($id)
	{
		$db =& $this->getDBO();

		if( (!is_numeric($id)) || ($id <= 0) )
		{
			$this->setError(JText::_('STATS_ERROR_INVALIDID'));
			return false;
		}

		// Try to delete files
		$this->deleteFile($id);
		if(!AEPlatform::delete_statistics($id))
		{
			$this->setError($db->getError());
			return false;
		}

		return true;
	}

	/**
	 * Delete the backup file of the stats record whose ID is set in the model
	 * @param	int		$id		Backup record whose files we have to delete
	 * @return bool True on success
	 */
	public function deleteFile($id)
	{
		$db =& $this->getDBO();

		if( (!is_numeric($id)) || ($id <= 0) )
		{
			$this->setError(JText::_('STATS_ERROR_INVALIDID'));
			return false;
		}

		$stat = AEPlatform::get_statistics($id);
		$allFiles = AEUtilStatistics::get_all_filenames($stat, false);
		$registry =& AEFactory::getConfiguration();

		$status = true;
		jimport('joomla.filesystem.file');
		foreach($allFiles as $filename)
		{
			$new_status = JFile::delete($filename);
			$status = $status ? $new_status : false;
		}

		return $status;
	}

	/**
	 * Get a pagination object
	 *
	 * @access public
	 * @return JPagination
	 *
	 */
	public function &getPagination()
	{
		if( empty($this->_pagination) )
		{
			// Import the pagination library
			jimport('joomla.html.pagination');

			// Prepare pagination values
			$total = AEPlatform::get_statistics_count();
			$limitstart = $this->getState('limitstart');
			$limit = $this->getState('limit');

			// Create the pagination object
			$this->_pagination = new JPagination($total, $limitstart, $limit);
		}

		return $this->_pagination;
	}

}