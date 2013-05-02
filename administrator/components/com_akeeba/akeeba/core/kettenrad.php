<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: kettenrad.php 228 2010-08-25 08:47:32Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * This is Akeeba Engine's heart. Kettenrad is reponsible for launching the
 * domain chain of a backup job.
 */
final class AECoreKettenrad extends AEAbstractPart
{
	private $array_cache = null;

	private $domain_chain = array();

	private $domain = '';

	private $class = '';

	private $tag = null;

	public function getTag()
	{
		if(empty($this->tag))
		{
			// If no tag exists, we resort to the pre-set backup origin
			$tag = AEPlatform::get_backup_origin();
			$this->tag = $tag;
		}
		return $this->tag;
	}

	protected function _prepare()
	{
		// Intialize the timer class
		$timer = AEFactory::getTimer();

		// Do we have a tag?
		if(!empty($this->_parametersArray['tag'])) {
			$this->tag = $this->_parametersArray['tag'];
		}
		// Make sure a tag exists (or create a new one)
		$this->tag = $this->getTag();

		// Reset the log
		AEUtilLogger::openLog($this->tag);
		AEUtilLogger::ResetLog($this->tag);

		// Reset the storage
		AEUtilTempvars::reset($this->tag);

		// Get the domain chain
		$this->domain_chain = AEUtilScripting::getDomainChain();

		// Mark this engine for Nesting Logging
		$this->nest_logging = true;

		// Preparation is over
		$this->array_cache = null;
		$this->setState('prepared');
	}

	protected function _run()
	{
		AEUtilLogger::openLog($this->tag);

		// Maybe we're already done or in an error state?
		if( ($this->getError()) || ($this->getState() == 'postrun')) return;

		// Set running state
		$this->setState('running');

		// Initialize operation counter
		$registry =& AEFactory::getConfiguration();
		$registry->set('volatile.operation_counter', 0);

		// Advance step counter
		$stepCounter = $registry->get('volatile.step_counter', 0);
		$registry->set('volatile.step_counter', ++$stepCounter);

		// Log step start number
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG,'====== Starting Step number '.$stepCounter.' ======');

		$timer =& AEFactory::getTimer();
		$finished = false;
		$error = false;
		$breakFlag = false; // BREAKFLAG is optionally passed by domains to force-break current operation

		// Loop until time's up, we're done or an error occured, or BREAKFLAG is set
		$this->array_cache = null;
		while( ( $timer->getTimeLeft() > 0 ) && (!$finished) && (!$error) && (!$breakFlag) ){
			// Reset the break flag
			$registry->set('volatile.breakflag', false);

			// Do we have to switch domains? This only happens if there is no active
			// domain, or the current domain has finished
			$have_to_switch = false;
			if($this->class == '')
			{
				$have_to_switch = true;
			}
			else
			{
				$object = AEFactory::getDomainObject($this->class);
				if( !is_object($object) )
				{
					$have_to_switch = true;
				}
				else
				{
					if( !in_array('getState', get_class_methods($object)) )
					{
						$have_to_switch = true;
					}
					else
					{
						if( $object->getState() == 'finished' ) $have_to_switch = true;
					}
				}

			}

			// Switch domain if necessary
			if($have_to_switch)
			{
				$registry->set('volatile.breakflag', true);
				$object = null; // Free last domain

				if(empty($this->domain_chain))
				{
					// Aw, we're done! No more domains to run.
					$this->setState('postrun');
					AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Kettenrad :: No more domains to process");
					$this->array_cache = null;
					return;
				}

				// Shift the next definition off the stack
				$this->array_cache = null;
				$new_definition = array_shift($this->domain_chain);
				if(array_key_exists('class', $new_definition))
				{
					$this->domain = $new_definition['domain'];
					$this->class = $new_definition['class'];
					// Get a working object
					$object = AEFactory::getDomainObject($this->class);
					$object->setup($this->_parametersArray);
				}
				else
				{
					AEUtilLogger::WriteLog(_AE_LOG_WARNING, "Kettenrad :: No class defined trying to switch domains. The backup will crash.");
					$this->domain = null;
					$this->class = null;
				}
			}
			else
			{
				if(!is_object($object)) $object = AEFactory::getDomainObject($this->class);
			}

			// Tick the object
			$result = $object->tick();

			// Propagate errors
			$object->propagateToObject($this);

			// Advance operation counter
			$currentOperationNumber = $registry->get('volatile.operation_counter', 0);
			$currentOperationNumber++;
			$registry->set('volatile.operation_counter', $currentOperationNumber);

			// Process return array
			$this->setDomain($this->domain);
			$this->setStep($result['Step']);
			$this->setSubstep($result['Substep']);

			// Check for BREAKFLAG
			$breakFlag = $registry->get('volatile.breakflag', false);

			// Process errors
			$error = false;
			if($this->getError())
			{
				$error = true;
			}

			// Check if the backup procedure should finish now
			$finished = $error ? true : !($result['HasRun']);

			// Log operation end
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG,'----- Finished operation '.$currentOperationNumber.' ------');
		} // while

		// Log the result
		if (!$error) {
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Successful Smart algorithm on ".get_class($object));
		} else {
			AEUtilLogger::WriteLog(_AE_LOG_ERROR, "Failed Smart algorithm on ".get_class($object));
		}

		// Log if we have to do more work or not
		if($object->getState() == 'running')
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Kettenrad :: More work required in domain '" . $this->domain."'");
		}
		elseif($object->getState() == 'finished')
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Kettenrad :: Domain '" . $this->domain."' has finished.");
		}

		// Log step end
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG,'====== Finished Step number '.$stepCounter.' ======');

		// We need to set the break flag for the part processing to not batch successive steps
		$registry->set('volatile.breakflag', true);
	}

	protected function _finalize()
	{
		// Open the log
		AEUtilLogger::openLog($this->tag);

		// Kill the cached array
		$this->array_cache = null;

		// Remove the memory file
		AEUtilTempvars::reset($this->tag);

		// All done.
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Kettenrad :: Just finished");
		$this->setState('finished');
	}

	/**
	 * Saves the whole factory to temporary storage
	 */
	public static function save($tag = null)
	{
		$kettenrad = AEFactory::getKettenrad();

		if(empty($tag)) {
			$kettenrad = AEFactory::getKettenrad();
			$tag = $kettenrad->tag;
		}

		$ret = $kettenrad->getStatusArray();
		if($ret['HasRun'] == 1) {
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Will not save a finished Kettenrad instance" );
		} else {
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Saving Kettenrad instance $tag" );
			// Save a Factory snapshot:
			AEUtilTempvars::set(AEFactory::serialize(), $tag);
		}
	}

	/**
	 * Loads the factory from the storage (if it exists) and returns a reference to the
	 * Kettenrad object.
	 * @param $tag string The backup tag to load
	 * @return AECoreKettenrad A reference to the Kettenrad object
	 */
	public static function &load($tag = null)
	{
		if(is_null($tag) && defined('AKEEBA_BACKUP_ORIGIN')) {
			$tag = AKEEBA_BACKUP_ORIGIN;
		}

		// In order to load anything, we need to have the correct profile loaded. Let's assume
		// that the latest backup record in this tag has the correct profile number set.
		$config = AEFactory::getConfiguration();
		if( empty($config->activeProfile) )
		{
			// Only bother loading a configuration if none has been already loaded
			$statList = AEPlatform::get_running_backups($tag);
			if(is_array($statList)) {
				$stat = array_pop($statList);
				$profile = $stat['profile_id'];
				AEPlatform::load_configuration($profile);
			}
		}

		AEUtilLogger::openLog($tag);
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Kettenrad :: Attempting to load from database");
		$serialized_factory = AEUtilTempvars::get($tag);
		if($serialized_factory !== false)
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, " -- Loaded stored Akeeba Factory");
			AEFactory::unserialize($serialized_factory);
		}
		else
		{
			// There is no serialized factory. Nuke the in-memory factory.
			AEFactory::nuke();
			AEPlatform::load_configuration();
		}
		unset($serialized_factory);
		return AEFactory::getKettenrad();
	}

	/**
	 * Resets the Kettenrad state, wipping out any pending backups and/or stale
	 * temporary data.
	 * @param array $config Configuration parameters for the reset operation
	 */
	public static function reset( $config = array() )
	{
		$default_config = array(
			'global'	=> true,	// Reset all origins when true
			'log'		=> false,	// Log our actions
		);

		$config = (object)array_merge($default_config, $config);

		// Pause logging if so desired
		if(!$config->log) AEUtilLogger::WriteLog(false,'');

		$tag = null;
		if(!$config->global)
		{
			// If we're not resetting globally, get a list of running backups per tag
			$tag = AEPlatform::get_backup_origin();
		}

		// Cache the factory before proceeding
		$factory = AEFactory::serialize();

		$runningList = AEPlatform::get_running_backups($tag);
		// Origins we have to clean
		$origins = array(
			AEPlatform::get_backup_origin()
		);

		// 1. Detect failed backups
		if(is_array($runningList) && !empty($runningList))
		{
			// The current timestamp
			$now = time();

			// Mark running backups as failed
			foreach($runningList as $running)
			{
				if(empty($tag))
				{
					// Check the timestamp of the log file to decide if it's stuck,
					// but only if a tag is not set
					$tstamp = @filemtime( AEUtilLogger::logName($running['origin']) );
					if($tstamp !== false)
					{
						// We can only check the timestamp if it's returned. If not, we assume the backup is stale
						$difference = abs($now - $tstamp);
						// Backups less than 3 minutes old are not considered stale
						if($difference < 180) continue;
					}
				}

				$filenames = AEUtilStatistics::get_all_filenames($running);
				// Process if there are files to delete...
				if(!is_null($filenames))
				{
					// Delete the failed backup's archive, if exists
					foreach($filenames as $failedArchive)
					{
						AEPlatform::unlink($failedArchive);
					}
				}

				// Mark the backup failed
				$running['status'] = 'fail';
				$running['multipart'] = 0;
				$dummy = null;
				AEPlatform::set_or_update_statistics( $running['id'], $running, $dummy );

				$origins[] = $running['origin'];
			}
		}

		if(!empty($origins))
		{
			$origins = array_unique($origins);
			foreach($origins as $tag)
			{
				AECoreKettenrad::load($tag);
				// Remove temporary files
				AEUtilTempfiles::deleteTempFiles();
				// Delete any stale temporary data
				AEUtilTempvars::reset($tag);
			}
		}

		// Reload the factory
		AEFactory::unserialize($factory);
		unset($factory);

		// Unpause logging if it was previously paused
		if(!$config->log) AEUtilLogger::WriteLog(true,'');
	}

	/**
	 * Returns a copy of the class's status array
	 * @return array
	 */
	public function getStatusArray()
	{
		if(empty($this->array_cache))
		{
			// Get the default table
			$array = $this->_makeReturnTable();

			// Add the archive name
			$statistics =& AEFactory::getStatistics();
			$record = $statistics->getRecord();
			$array['Archive'] = isset($record['archivename']) ? $record['archivename'] : '';

			// Translate HasRun to what the rest of the suite expects
			$array['HasRun'] = ($this->getState() == 'finished') ? 1 : 0;

			// Translate no errors
			$array['Error'] = ($array['Error'] == false) ? '' : $array['Error'];

			$array['tag'] = $this->tag;

			$this->array_cache = $array;
		}
		return $this->array_cache;
	}

}

/**
 * Timeout error handler
 */
function deadOnTimeOut()
{
	if( connection_status() == 1 ) {
		AEUtilLogger::WriteLog(_AE_LOG_ERROR, 'The process was aborted on user\'s request');
	}
	if( connection_status() >= 2 ) {
		AEUtilLogger::WriteLog(_AE_LOG_ERROR, AEPlatform::translate('KETTENRAD_TIMEOUT') );
	}
}
register_shutdown_function("deadOnTimeOut");