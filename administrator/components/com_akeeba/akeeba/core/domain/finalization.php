<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2010 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: finalization.php 186 2010-07-16 18:44:30Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Backup finalization domain
 */
class AECoreDomainFinalization extends AEAbstractPart
{

	private $action_queue = array();

	private $current_method = '';

	private $backup_parts = array();
	private $backup_parts_index = -1;

	private $update_stats = false;

	/**
	 * Implements the abstract method
	 * @see akeeba/abstract/AEAbstractPart#_prepare()
	 */
	protected function _prepare()
	{
		// Populate actions queue
		$this->action_queue = array(
			'mail_administrators',
			'remove_temp_files',
			'update_statistics',
			'run_post_processing',
			'apply_quotas',
		);

		// Make sure the break flag is not set
		$configuration =& AEFactory::getConfiguration();
		$configuration->get('volatile.breakflag', false);

		// Seed the method
		$this->current_method = array_shift($this->action_queue);

		// Set ourselves to running state
		$this->setState('running');
	}

	/**
	 * Implements the abstract method
	 * @see akeeba/abstract/AEAbstractPart#_run()
	 */
	protected function _run()
	{
		$configuration =& AEFactory::getConfiguration();

		if($this->getState() == 'postrun') return;

		$finished = (empty($this->action_queue)) && ($this->current_method == '');
		if($finished)
		{
			$this->setState('postrun');
			return;
		}

		$this->setState('running');

		$timer =& AEFactory::getTimer();

		// Continue processing while we have still enough time and stuff to do
		while( ($timer->getTimeLeft() > 0) && (!$finished) && (!$configuration->get('volatile.breakflag', false)) )
		{
			$method = $this->current_method;
			$status = $this->$method();
			if($status === true)
			{
				$this->current_method = '';
				$finished = (empty($this->action_queue));
				if(!$finished) $this->current_method = array_shift($this->action_queue);
			}
		}

		if($finished) $this->setState('postrun');
	}

	/**
	 * Implements the abstract method
	 * @see akeeba/abstract/AEAbstractPart#_finalize()
	 */
	protected function _finalize()
	{
		$this->setState('finished');
	}

	/**
	 * Sends an email to the administrators
	 * @return bool
	 */
	private function mail_administrators()
	{
		// Skip email for back-end backups
		if(AEPlatform::get_backup_origin() == 'backend' ) return true;

		$must_email = AEPlatform::get_platform_configuration_option('frontend_email_on_finish', 0) != 0;
		if(!$must_email) return true;

		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Preparing to send e-mail to administrators");

		$email = AEPlatform::get_platform_configuration_option('frontend_email_address', '');
		$email = trim($email);
		if( !empty($email) )
		{
			$emails = array($email);
		}
		else
		{
			$emails = AEPlatform::get_administrator_emails();
		}

		if(!empty($emails))
		{
			$subject = AEPlatform::translate('EMAIL_SUBJECT_OK');
			$body = AEPlatform::translate('EMAIL_BODY_OK');
			foreach($emails as $email)
			{
				AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Sending email to $email");
				AEPlatform::send_email($email, $subject, $body);
			}
		}

		return true;
	}

	/**
	 * Removes temporary files
	 * @return bool
	 */
	private function remove_temp_files()
	{
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Removing temporary files" );
		AEUtilTempfiles::deleteTempFiles();
		return true;
	}

	/**
	 * Runs the writer's post-processing steps
	 * @return bool
	 */
	private function run_post_processing()
	{
		// Do not run if the archive engine doesn't produce archives
		$configuration =& AEFactory::getConfiguration();
		$this->setSubstep('');

		AEUtilLogger::WriteLog(_AE_LOG_DEBUG,'Loading post-processing engine object');
		$post_proc =& AEFactory::getPostprocEngine();

		// Initialize the archive part list if required
		if(empty($this->backup_parts))
		{
			AEUtilLogger::WriteLog(_AE_LOG_INFO,'Initializing post-processing engine');

			// Populate array w/ absolute names of backup parts
			$statistics =& AEFactory::getStatistics();
			$stat = $statistics->getRecord();
			$this->backup_parts = AEUtilStatistics::get_all_filenames($stat, false);
			if(is_null($this->backup_parts)) {
				// No archive produced, or they are all already post-processed
				AEUtilLogger::WriteLog(_AE_LOG_INFO,'No archive files found to post-process');
				return true;
			}

			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, count($this->backup_parts).' files to process found');

			$this->backup_parts_index = 0;
			// If we have an empty array, do not run
			if(empty($this->backup_parts)) return true;

			// Break step before processing?
			if($post_proc->break_before)
			{
				AEUtilLogger::WriteLog(_AE_LOG_DEBUG, 'Breaking step before post-processing run');
				$configuration->set('volatile.breakflag', true);
				return false;
			}
		}

		// Make sure we don't accidentally break the step when not required to do so
		$configuration->set('volatile.breakflag', false);

		$filename = $this->backup_parts[$this->backup_parts_index];
		AEUtilLogger::WriteLog(_AE_LOG_INFO, 'Post processing file '.$filename);
		$this->setStep('Post-processing');
		$this->setSubstep( basename($filename) );
		$result = $post_proc->processPart( $filename );
		$this->propagateFromObject($post_proc);
		if($result === false)
		{
			AEUtilLogger::WriteLog(_AE_LOG_WARNING, 'Failed to process file '.$filename);
			AEUtilLogger::WriteLog(_AE_LOG_WARNING, 'Error received from the post-processing engine:');
			AEUtilLogger::WriteLog(_AE_LOG_WARNING, implode("\n", $this->getWarnings()) );
			$this->setWarning('Failed to process file '.$filename);
		}
		else
		{
			AEUtilLogger::WriteLog(_AE_LOG_INFO, 'Successfully processed file '.$filename);
		}

		// Should we delete the file afterwards?
		if(
			$configuration->get('engine.postproc.common.delete_after',false)
			&& $post_proc->allow_deletes
			&& ($result !== false)
		)
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, 'Deleting already processed file '.$filename);
			AEPlatform::unlink($filename);
		}

		if($result !== false)
		{
			// Move the index forward if the part finished processing
			$this->backup_parts_index++;

			// Are we past the end of the array (i.e. we're finished)?
			if( $this->backup_parts_index >= count($this->backup_parts) )
			{
				AEUtilLogger::WriteLog(_AE_LOG_INFO,'Post-processing has finished for all files');
				return true;
			}
		}
		else
		{
			// If the post-processing failed, make sure we don't process anything else
			$this->backup_parts_index = count($this->backup_parts);
			$this->setWarning('Post-processing interrupted -- no more files will be transferred');
			return true;
		}


		// Or, if we're here, we still have stuff to do...

		// Break step after processing?
		if($post_proc->break_after) $configuration->set('volatile.breakflag', true);
		// Indicate we're not done yet
		return false;
	}

	/**
	 * Updates the backup statistics record
	 * @return bool
	 */
	private function update_statistics()
	{
		// Force a step break before updating stats (works around MySQL gone away issues)
		if(!$this->update_stats)
		{
			$this->update_stats = true;
			$configuration =& AEFactory::getConfiguration();
			$configuration->set('volatile.breakflag', true);
			return false;
		}

		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Updating statistics" );
		// We finished normally. Fetch the stats record
		$statistics =& AEFactory::getStatistics();
		$registry =& AEFactory::getConfiguration();
		$data = array(
			'backupend'	=> AEPlatform::get_timestamp_mysql(),
			'status'	=> 'complete',
			'multipart'	=> $registry->get('volatile.statistics.multipart', 0)
		);
		$statistics->setStatistics($data);
		$this->propagateFromObject($statistics);

		return true;
	}

	/**
	 * Applies the size and count quotas
	 * @return bool
	 */
	private function apply_quotas()
	{
		$this->setSubstep('');

		// If no quota settings are enabled, quit
		$registry =& AEFactory::getConfiguration();
		$useCountQuotas = $registry->get('akeeba.quota.enable_count_quota');
		$useSizeQuotas = $registry->get('akeeba.quota.enable_size_quota');
		if(! ($useCountQuotas || $useSizeQuotas) )
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "No quotas were defined; old backup files will be kept intact" );
			return true; // No quota limits were requested
		}

		// Try to find the files to be deleted due to quota settings
		$statistics =& AEFactory::getStatistics();
		$latestBackupId = $statistics->getId();

		// Get quota values
		$countQuota = $registry->get('akeeba.quota.count_quota');
		$sizeQuota = $registry->get('akeeba.quota.size_quota');

		// Get valid-looking backup ID's
		$validIDs =& AEPlatform::get_valid_backup_records(true);

		// Create a list of valid files
		$allFiles = array();
		if(count($validIDs))
		{
			foreach($validIDs as $id)
			{
				$stat = AEPlatform::get_statistics($id);
				// Multipart processing
				$filenames = AEUtilStatistics::get_all_filenames($stat, true);
				if(!is_null($filenames))
				{
					// Only process existing files
					$filesize = 0;
					foreach($filenames as $filename)
					{
						$filesize += @filesize($filename);
					}
					$allFiles[] = array('id' => $id, 'filenames' => $filenames, 'size' => $filesize);
				}
			}
		}
		unset($validIDs);

		// If there are no files, exit early
		if(count($allFiles) == 0)
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "There were no old backup files to apply quotas on" );
			return true;
		}

		// Init arrays
		$ret = array();
		$leftover = array();

		// Do we need to apply count quotas?
		if($useCountQuotas && is_numeric($countQuota) && !($countQuota <= 0) )
		{
			// Are there more files than the quota limit?
			if( !(count($allFiles) > $countQuota) )
			{
				// No, effectively skip the quota checking
				$leftover =& $allFiles;
			}
			else
			{
				AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Processing count quotas" );
				// Yes, aply the quota setting. Add to $ret all entries minus the last
				// $countQuota ones.
				$totalRecords = count($allFiles);
				$checkLimit = $totalRecords - $countQuota;
				// Only process if at least one file (current backup!) is to be left
				for($count = 0; $count < $totalRecords; $count++)
				{
					$def = array_pop($allFiles);
					if(count($ret) < $checkLimit)
					{
						if($latestBackupId != $def['id']) {
							$ret[] = $def['filenames'];
						}
					}
					else
					{
						$leftover[] = $def;
					}
				}
				unset($allFiles);
			}
		}
		else
		{
			// No count quotas are applied
			$leftover =& $allFiles;
		}

		// Do we need to apply size quotas?
		if( $useSizeQuotas && is_numeric($sizeQuota) && !($sizeQuota <= 0) && (count($leftover) > 0) )
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Processing size quotas" );
			// OK, let's start counting bytes!
			$runningSize = 0;
			while(count($leftover) > 0)
			{
				// Each time, remove the last element of the backup array and calculate
				// running size. If it's over the limit, add the archive to the return array.
				$def = array_pop($leftover);
				$runningSize += $def['size'];
				if($runningSize >= $sizeQuota)
				{
					if($latestBackupId == $def['id'])
					{
						$runningSize -= $def['size'];
					}
					else
					$ret[] = $def['filenames'];
				}
			}
		}

		// Convert the $ret 2-dimensional array to single dimensional
		$quotaFiles = array();
		foreach($ret as $temp)
		{
			foreach($temp as $filename)
			{
				$quotaFiles[] = $filename;
			}
		}

		// Apply quotas
		if(count($quotaFiles) > 0)
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Applying quotas" );
			jimport('joomla.filesystem.file');
			foreach($quotaFiles as $file)
			{
				if(!@AEPlatform::unlink($file))
				{
					$this->setWarning("Failed to remove old backup file ".$file );
				}
			}
		}

		return true;
	}
}