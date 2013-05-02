<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: init.php 243 2010-09-01 11:17:45Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Backup initialization domain
 */
class AECoreDomainInit extends AEAbstractPart
{
	/** @var string The backup description */
	private $description = '';

	/** @var string The backup comment */
	private $comment = '';

	/**
	 * Implements the constructor of the class
	 *
	 * @return AECoreDomainInit
	 */
	public function __construct()
	{
		parent::__construct();
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: New instance");
	}

	/**
	 * Implements the _prepare abstract method
	 *
	 */
	protected function _prepare()
	{
		// Load parameters (description and comment)
		$jpskey = '';
		if(!empty($this->_parametersArray))
		{
			$params = $this->_parametersArray;
			if( isset($params['description']) ) $this->description = $params['description'];
			if( isset($params['comment']) ) $this->comment = $params['comment'];
			if( isset($params['jpskey']) ) $jpskey = $params['jpskey'];
		}

		// Load configuration
		AEPlatform::load_configuration();

		// Initialize counters
		$registry =& AEFactory::getConfiguration();
		$registry->set('volatile.step_counter', 0);
		$registry->set('volatile.operation_counter', 0);
		$registry->set('engine.archiver.jps.key', $jpskey);


		// Initialize temporary storage
		AEUtilTempvars::reset();

		// Force load the tag
		$kettenrad = AEFactory::getKettenrad();
		$tag = $kettenrad->getTag();

		// Push the comment and description in temp vars for use in the installer phase
		$registry->set('volatile.core.description', $this->description);
		$registry->set('volatile.core.comment', $this->comment);

		$this->setState('prepared');
	}

	/**
	 * Implements the _run() abstract method
	 */
	protected function _run()
	{
		if( $this->getState() == 'postrun' )
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Already finished");
			$this->setStep('');
			$this->setSubstep('');
			return;
		} else {
			$this->setState('running');
		}

		// Load the version defines
		AEPlatform::load_version_defines();

		$registry =& AEFactory::getConfiguration();

		// Write log file's header
		AEUtilLogger::WriteLog(_AE_LOG_INFO, "--------------------------------------------------------------------------------");
		AEUtilLogger::WriteLog(_AE_LOG_INFO, "Akeeba Backup ".AKEEBA_VERSION.' ('.AKEEBA_DATE.')');
		AEUtilLogger::WriteLog(_AE_LOG_INFO, "Got backup?");
		AEUtilLogger::WriteLog(_AE_LOG_INFO, "--------------------------------------------------------------------------------");
		// PHP configuration variables are tried to be logged only for debug and info log levels
		if ($registry->get('akeeba.basic.log_level') >= _AE_LOG_INFO ) {
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "--- System Information ---" );
			if( function_exists('phpversion'))
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "PHP Version        :" . phpversion() );
			if(function_exists('php_uname'))
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "OS Version         :" . php_uname('s') );
			$db =& AEFactory::getDatabase();
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "DB Version         :" . $db->getVersion() );
			if (isset($_SERVER['SERVER_SOFTWARE'])) {
				$server = $_SERVER['SERVER_SOFTWARE'];
			} else if (($sf = getenv('SERVER_SOFTWARE'))) {
				$server = $sf;
			} else {
				$server = 'n/a';
			}
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "Web Server         :" . $server );
			if(function_exists('php_sapi_name'))
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "PHP Interface      :" . php_sapi_name() );
			AEPlatform::getPlatformVersion( $platform, $version );
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "$platform version    :" . $version );
			if(isset($_SERVER['HTTP_USER_AGENT']))
				AEUtilLogger::WriteLog(_AE_LOG_INFO, "User agent         :" . phpversion() <= "4.2.1" ? getenv( "HTTP_USER_AGENT" ) : $_SERVER['HTTP_USER_AGENT'] );
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "Safe mode          :" . ini_get("safe_mode") );
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "Display errors     :" . ini_get("display_errors") );
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "Error reporting    :" . self::error2string() );
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "Disabled functions :" . ini_get("disable_functions") );
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "open_basedir restr.:" . ini_get('open_basedir') );
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "Max. exec. time    :" . ini_get("max_execution_time") );
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "Memory limit       :" . ini_get("memory_limit") );
			if(function_exists("memory_get_usage"))
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "Current mem. usage :" . memory_get_usage() );
			if(function_exists("gzcompress")) {
				AEUtilLogger::WriteLog(_AE_LOG_INFO, "GZIP Compression   : available (good)" );
			} else {
				AEUtilLogger::WriteLog(_AE_LOG_INFO, "GZIP Compression   : n/a (no compression)" );
			}
			AEPlatform::log_platform_special_directories();
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "Output directory   :" . $registry->get('akeeba.basic.output_directory') );
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "Temporary directory:" . $registry->get('akeeba.basic.temporary_directory') );
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "--------------------------------------------------------------------------------");
		}

		// Quirks reporting
		$quirks = AEUtilQuirks::get_quirks(true);
		if( !empty($quirks) )
		{
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "Akeeba Backup has detected the following potential problems:" );
			foreach($quirks as $q)
			{
				AEUtilLogger::WriteLog(_AE_LOG_INFO, '- '.$q['code'].' '.$q['description'].' ('.$q['severity'].')' );
			}
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "You probably do not have to worry about them, but you should be aware of them." );
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "--------------------------------------------------------------------------------");
		}

		// Report profile ID
		$profile_id = AEPlatform::get_active_profile();
		AEUtilLogger::WriteLog(_AE_LOG_INFO, "Loaded profile #$profile_id");

		// Get archive name
		AEUtilFilesystem::get_archive_name($relativeArchiveName, $absoluteArchiveName);

		// ==== Stats initialisation ===
		$origin = AEPlatform::get_backup_origin(); // Get backup origin
		$profile_id = AEPlatform::get_active_profile(); // Get active profile

		$registry =& AEFactory::getConfiguration();
		$backupType = $registry->get('akeeba.basic.backup_type');
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Backup type is now set to '" . $backupType . "'");

		// Substitute "variables" in the archive name
		$description = AEUtilFilesystem::replace_archive_name_variables($this->description);
		$comment = AEUtilFilesystem::replace_archive_name_variables($this->comment);

		if($registry->get('volatile.writer.store_on_server', true) )
		{
			// Archive files are stored on our server
			$stat_relativeArchiveName = $relativeArchiveName;
			$stat_absoluteArchiveName = $absoluteArchiveName;
		}
		else
		{
			// Archive files are not stored on our server (FTP backup, cloud backup, sent by email, etc)
			$stat_relativeArchiveName = '';
			$stat_absoluteArchiveName = '';
		}

		$kettenrad = AEFactory::getKettenrad();
		$temp = array(
			'description'	=> $description,
			'comment'		=> $comment,
			'backupstart'	=> AEPlatform::get_timestamp_mysql(),
			'status'		=> 'run',
			'origin'		=> $origin,
			'type'			=> $backupType,
			'profile_id'	=> $profile_id,
			'archivename'	=> $stat_relativeArchiveName,
			'absolute_path'	=> $stat_absoluteArchiveName,
			'multipart'		=> 0,
			'filesexist'	=> 1,
			'tag'			=> $kettenrad->getTag()
		);
		// Save the entry
		$statistics =& AEFactory::getStatistics();
		$statistics->setStatistics($temp);
		if($statistics->getError())
		{
			$this->setError($statistics->getError());
			return;
		}

		$statistics->release_multipart_lock();

		// Initialize the archive.
		if (AEUtilScripting::getScriptingParameter('core.createarchive',true))
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Expanded archive file name: " . $absoluteArchiveName);

			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Initializing archiver engine");
			$archiver =& AEFactory::getArchiverEngine();
			$archiver->initialize($absoluteArchiveName);
			$archiver->setComment($comment); // Add the comment to the archive itself.
			$archiver->propagateToObject($this);
			if($this->getError())
			{
				return;
			}
		}

		$this->setState('postrun');
	}

	protected function _finalize()
	{
		$this->setState('finished');
	}

	public static function error2string()
	{
		if(function_exists('error_reporting'))
		{
			$value = error_reporting();
		} else {
			return "Not applicable; host too restrictive";
		}
		$level_names = array(
		E_ERROR => 'E_ERROR', E_WARNING => 'E_WARNING',
		E_PARSE => 'E_PARSE', E_NOTICE => 'E_NOTICE',
		E_CORE_ERROR => 'E_CORE_ERROR', E_CORE_WARNING => 'E_CORE_WARNING',
		E_COMPILE_ERROR => 'E_COMPILE_ERROR', E_COMPILE_WARNING => 'E_COMPILE_WARNING',
		E_USER_ERROR => 'E_USER_ERROR', E_USER_WARNING => 'E_USER_WARNING',
		E_USER_NOTICE => 'E_USER_NOTICE' );
		if(defined('E_STRICT')) $level_names[E_STRICT]='E_STRICT';
		$levels=array();
		if(($value&E_ALL)==E_ALL)
		{
			$levels[]='E_ALL';
			$value&=~E_ALL;
		}
		foreach($level_names as $level=>$name)
		if(($value&$level)==$level) $levels[]=$name;
		return implode(' | ',$levels);
	}
}