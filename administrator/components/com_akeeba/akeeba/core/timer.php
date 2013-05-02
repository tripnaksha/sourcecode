<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2010 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: timer.php 65 2010-02-09 14:48:40Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Timer class
 */
class AECoreTimer extends AEAbstractObject
{
	/** @var int Maximum execution time allowance per step */
	private $max_exec_time = null;

	/** @var int Timestamp of execution start */
	private $start_time = null;

	/**
	 * Public constructor, creates the timer object and calculates the execution time limits
	 * @return AECoreTimer
	 */
	public function __construct()
	{
		parent::__construct();

		// Initialize start time
		$this->start_time = $this->microtime_float();

		// Get configured max time per step and bias
		$configuration =& AEFactory::getConfiguration();
		$config_max_exec_time	= $configuration->get('akeeba.tuning.max_exec_time', 14);
		$bias					= $configuration->get('akeeba.tuning.run_time_bias', 75)/100;

		// Get PHP's maximum execution time (our upper limit)
		if(@function_exists('ini_get'))
		{
			$php_max_exec_time = @ini_get("maximum_execution_time");
			if ( (!is_numeric($php_max_exec_time)) || ($php_max_exec_time == 0) ) {
				// If we have no time limit, set a hard limit of about 10 seconds
				// (safe for Apache and IIS timeouts, verbose enough for users)
				$php_max_exec_time = 14;
			}
		}
		else
		{
			// If ini_get is not available, use a rough default
			$php_max_exec_time = 14;
		}

		// Apply an arbitrary correction to counter CMS load time
		$php_max_exec_time--;

		// Apply bias
		$php_max_exec_time = $php_max_exec_time * $bias;
		$config_max_exec_time = $config_max_exec_time * $bias;

		// Use the most appropriate time limit value
		if( $config_max_exec_time > $php_max_exec_time )
		{
			$this->max_exec_time = $php_max_exec_time;
		}
		else
		{
			$this->max_exec_time = $config_max_exec_time;
		}
	}

	/**
	 * Wake-up function to reset internal timer when we get unserialized
	 */
	public function __wakeup()
	{
		// Re-initialize start time on wake-up
		$this->start_time = $this->microtime_float();
	}

	/**
	 * Gets the number of seconds left, before we hit the "must break" threshold
	 * @return float
	 */
	public function getTimeLeft()
	{
		return $this->max_exec_time - $this->getRunningTime();
	}

	/**
	 * Gets the time elapsed since object creation/unserialization, effectively how
	 * long Akeeba Engine has been processing data
	 * @return float
	 */
	public function getRunningTime()
	{
		return $this->microtime_float() - $this->start_time;
	}

	/**
	 * Returns the current timestampt in decimal seconds
	 */
	private function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	/**
	 * Enforce the minimum execution time
	 * @param	bool	$log	Should I log what I'm doing? Default is true.
	 */
	public function enforce_min_exec_time($log = true)
	{
		// Try to get a sane value for PHP's maximum_execution_time INI parameter
		if(@function_exists('ini_get'))
		{
			$php_max_exec = @ini_get("maximum_execution_time");
		}
		else
		{
			$php_max_exec = 10;
		}
		if ( ($php_max_exec == "") || ($php_max_exec == 0) ) {
			$php_max_exec = 10;
		}
		// Decrease $php_max_exec time by 500 msec we need (approx.) to tear down
		// the application, as well as another 500msec added for rounding
		// error purposes. Also make sure this is never gonna be less than 0.
		$php_max_exec = max($php_max_exec * 1000 - 1000, 0);

		// Get the "minimum execution time per step" Akeeba Backup configuration variable
		$configuration =& AEFactory::getConfiguration();
		$minexectime = $configuration->get('akeeba.tuning.min_exec_time',0);
		if(!is_numeric($minexectime)) $minexectime = 0;

		// Make sure we are not over PHP's time limit!
		if($minexectime > $php_max_exec) $minexectime = $php_max_exec;

		// Get current running time
		$elapsed_time = $this->getRunningTime() * 1000;

			// Only run a sleep delay if we haven't reached the minexectime execution time
		if( ($minexectime > $elapsed_time) && ($elapsed_time > 0) )
		{
			$sleep_msec = $minexectime - $elapsed_time;
			if(function_exists('usleep'))
			{
				if($log) AEUtilLogger::WriteLog( _AE_LOG_DEBUG, "Sleeping for $sleep_msec msec, using usleep()" );
				usleep(1000 * $sleep_msec);
			}
			elseif(function_exists('time_nanosleep'))
			{
				if($log) AEUtilLogger::WriteLog( _AE_LOG_DEBUG, "Sleeping for $sleep_msec msec, using time_nanosleep()" );
				$sleep_sec = floor($sleep_msec / 1000);
				$sleep_nsec = 1000000 * ($sleep_msec - ($sleep_sec * 1000));
				time_nanosleep($sleep_sec, $sleep_nsec);
			}
			elseif(function_exists('time_sleep_until'))
			{
				if($log) AEUtilLogger::WriteLog( _AE_LOG_DEBUG, "Sleeping for $sleep_msec msec, using time_sleep_until()" );
				$until_timestamp = time() + $sleep_msec / 1000;
				time_sleep_until($until_timestamp);
			}
			elseif(function_exists('sleep'))
			{
				$sleep_sec = ceil($sleep_msec/1000);
				if($log) AEUtilLogger::WriteLog( _AE_LOG_DEBUG, "Sleeping for $sleep_sec seconds, using sleep()" );
				sleep( $sleep_sec );
			}
		}
		elseif( $elapsed_time > 0 )
		{
			// No sleep required, even if user configured us to be able to do so.
			if($log) AEUtilLogger::WriteLog( _AE_LOG_DEBUG, "No need to sleep; execution time: $elapsed_time msec; min. exec. time: $minexectime msec" );
		}
	}

	/**
	 * Reset the timer. It should only be used in CLI mode!
	 */
	public function resetTime()
	{
		$this->start_time = $this->microtime_float();
	}

}