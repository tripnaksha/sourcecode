<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: dump.php 130 2010-05-04 08:56:40Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

abstract class AEAbstractDump extends AEAbstractPart
{
	// **********************************************************************
	// Configuration parameters
	// **********************************************************************

	/** @var string Prefix to this database */
	protected $prefix = '';

	/** @var string MySQL database server host name or IP address */
	protected $host = '';

	/** @var string MySQL database server port (optional) */
	protected $port = '';

	/** @var string MySQL user name, for authentication */
	protected $username = '';

	/** @var string MySQL password, for authentication */
	protected $password = '';

	/** @var string MySQL database */
	protected $database = '';

	/** @var string The database driver to use */
	protected $driver = '';

	/** @var string Absolute path to dump file; must be writable (optional; if left blank it is automatically calculated) */
	protected $dumpFile = '';

	/** @var string Data cache, used to cache data before being written to disk */
	protected $data_cache = '';

	/** @var int Size of the data cache, default 128Kb */
	protected $cache_size = 131072;

	/** @var bool Should I process empty prefixes when creating abstracted names? */
	protected $processEmptyPrefix = true;

	/** @var int Current dump file part number */
	public $partNumber = 0;

	private $fp;

	// **********************************************************************
	// Private fields
	// **********************************************************************

	/** @var string Absolute path to the temp file */
	protected $tempFile = '';

	/** @var string Relative path of how the file should be saved in the archive */
	protected $saveAsName = '';

	/**
	 * Find where to store the backup files
	 * @param $partNumber int The SQL part number, default is 0 (.sql)
	 */
	protected function getBackupFilePaths( $partNumber = 0 )
	{
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, 'XXX '.__CLASS__." :: Getting temporary file");
		$this->tempFile = AEUtilTempfiles::registerTempFile( dechex(crc32(microtime())).'.sql' );
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, 'XXX '.__CLASS__." :: Temporary file is {$this->tempFile}");
		// Get the base name of the dump file
		$partNumber = intval($partNumber);
		$baseName = $this->dumpFile;
		if($partNumber > 0)
		{
			// The file names are in the format dbname.sql, dbname.s01, dbname.s02, etc
			if( strtolower(substr($baseName, -4)) == '.sql' )
			{
				$baseName = substr($baseName, 0, -4).'.s'.sprintf('%02u', $partNumber);
			}
			else
			{
				$baseName = $baseName.'.s'.sprintf('%02u', $partNumber);
			}
		}

		switch(AEUtilScripting::getScriptingParameter('db.saveasname','normal'))
		{
			case 'output':
				// The SQL file will be stored uncompressed in the output directory
				$statistics =& AEFactory::getStatistics();
				$statRecord = $statistics->getRecord();
				$this->saveAsName = $statRecord['absolute_path'];
				break;

			case 'normal':
				// The SQL file will be stored in the installation/sql folder of the archive
				$this->saveAsName = 'installation/sql/'.$baseName;
				break;

			case 'short':
				// The SQL file will be stored on archive's root
				$this->saveAsName = $baseName;
				break;
		}

		if($partNumber > 0)
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "AkeebaDomainDBBackup :: Creating new SQL dump part #$partNumber");
		}
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "AkeebaDomainDBBackup :: SQL temp file is " . $this->tempFile);
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "AkeebaDomainDBBackup :: SQL file location in archive is " . $this->saveAsName);
	}

	/**
	 * Deletes any leftover files from previous backup attempts
	 *
	 */
	protected function removeOldFiles()
	{
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "AkeebaDomainDBBackup :: Deleting leftover files, if any");
		if( file_exists( $this->tempFile ) ) @unlink( $this->tempFile );
	}

	protected abstract function enforceSQLCompatibility();

	/**
	 * Returns a table's abstract name (replacing the prefix with the magic #__ string)
	 *
	 * @param string $tableName The canonical name, e.g. 'jos_content'
	 * @return string The abstract name, e.g. '#__content'
	 */
	protected function getAbstract( $tableName )
	{
		// Don't return abstract names for non-CMS tables
		if(is_null($this->prefix)) return $tableName;

		switch( $this->prefix )
		{
			case '':
				if($this->processEmptyPrefix)
				{
					// This is more of a hack; it assumes all tables are core CMS tables if the prefix is empty.
					return '#__' . $tableName;
				}
				else
				{
					// If $this->processEmptyPrefix (the process_empty_prefix config flag) is false, we don't
					// assume anything.
					return $tableName;
				}
				break;

			default:
				// Normal behaviour for 99% of sites
				// Fix 2.4 : Abstracting the prefix only if it's found in the beginning of the table name
				$tableAbstract = $tableName;
				if(!empty($this->prefix)) {
					if( substr($tableName, 0, strlen($this->prefix)) == $this->prefix ) {
						$tableAbstract = '#__' . substr($tableName, strlen($this->prefix));
					} else {
						// FIX 2.4: If there is no prefix, it's a non-Joomla! table.
						$tableAbstract = $tableName;
					}
				}

				return $tableAbstract;
				break;
		}
	}

	/**
	 * Writes the SQL dump into the output files. If it fails, it sets the error
	 *
	 * @param string $data Data to write to the dump file. Pass NULL to force flushing to file.
	 * @return boolean TRUE on successful write, FALSE otherwise
	 */
	protected function writeDump( &$data )
	{
		if(!empty($data)) $this->data_cache .= $data;
		if( (strlen($this->data_cache) >= $this->cache_size) || (is_null($data) && (!empty($this->data_cache)) ) )
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Writing ".strlen($this->data_cache)." bytes to the dump file");
			$result = $this->writeline( $this->data_cache );
			if( !$result )
			{
				$errorMessage = 'Couldn\'t write to the SQL dump file ' . $this->tempFile . '; check the temporary directory permissions and make sure you have enough disk space available.';
				$this->setError($errorMessage);
				return false;
			}
			$this->data_cache = '';
		}

		return true;
	}

	/**
	 * Saves the string in $fileData to the file $backupfile. Returns TRUE. If saving
	 * failed, return value is FALSE.
	 * @param string $fileData Data to write. Set to null to close the file handle.
	 * @return boolean TRUE is saving to the file succeeded
	 */
	protected function writeline(&$fileData) {
		if(!$this->fp)
		{
			$this->fp = @fopen($this->tempFile, 'a');
			if($this->fp === false)
			{
				$this->setError('Could not open '.$this->tempFile.' for append, in DB dump.');
				return;
			}
		}

		if(is_null($fileData))
		{
			if($this->fp) @fclose($this->fp);
			$this->fp = null;
			return true;
		}
		else
		{
			if ($this->fp) {
				$ret = fwrite($this->fp, $fileData);
				@clearstatcache();
				// Make sure that all data was written to disk
				return ($ret == strlen($fileData));
			} else {
				return false;
			}
		}
	}

	function _onSerialize()
	{
		$this->closeFile();
	}

	function __destruct()
	{
		$this->closeFile();
	}

	public function closeFile()
	{
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Closing SQL dump file.");
		if(is_resource($this->fp)) @fclose($this->fp);
	}

	/**
	 * Return an instance of AEAbstractDriver
	 *
	 * @return AEAbstractDriver
	 */
	protected function &getDB()
	{
		$host = $this->host . ($this->port != '' ? ':' . $this->port : '');
		$user = $this->username;
		$password =  $this->password;
		$driver = $this->driver;
		$database	= $this->database;
		$prefix 	= is_null($this->prefix) ? '' : $this->prefix;
		$options	= array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix );

		$db = AEFactory::getDatabase($options);

		if( $error = $db->getError() )
		{
			$this->setError(__CLASS__.' :: Database Error: '.$error);
			return false;
		}

		if( $db->getErrorNum() > 0 )
		{
			$this->setError(__CLASS__.' :: Database Error: '.$db->getErrorMsg());
			return false;
		}

		return $db;
	}

}