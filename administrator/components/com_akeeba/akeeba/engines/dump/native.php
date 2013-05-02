<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2010 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: native.php 230 2010-08-25 09:21:24Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * A generic MySQL database dump class, using Joomla!'s JDatabase class for handling the connection.
 * Now supports views; merge, in-memory, federated, blackhole, etc tables
 * Configuration parameters:
 * host			<string>	MySQL database server host name or IP address
 * port			<string>	MySQL database server port (optional)
 * username		<string>	MySQL user name, for authentication
 * password		<string>	MySQL password, for authentication
 * database		<string>	MySQL database
 * dumpFile		<string>	Absolute path to dump file; must be writable (optional; if left blank it is automatically calculated)
 */
class AEDumpNative extends AEAbstractDump
{
	// **********************************************************************
	// Private fields
	// **********************************************************************

	/** @var array Contains the sorted (by dependencies) list of tables/views to backup */
	private $tables = array();

	/** @var array Contains the configuration data of the tables */
	private $tables_data = array();

	/** @var array Maps database table names to their abstracted format */
	private $table_name_map = array();

	/** @var array Contains the dependencies of tables and views (temporary) */
	private $dependencies = array();

	/** @var string The next table to backup */
	private $nextTable;

	/** @var integer The next row of the table to start backing up from */
	private $nextRange;

	/** @var integer Current table's row count */
	private $maxRange;

	/** @var bool Use extended INSERTs */
	private $extendedInserts = false;

	/** @var integer Maximum packet size for extended INSERTs, in bytes */
	private $packetSize = 0;

	/** @var string Extended INSERT query, while it's being constructed */
	private $query = '';

	/** @var int Dump part's maximum size */
	private $partSize = 0;

	/**
	 * Implements the constructor of the class
	 *
	 * @return AEDumpNative
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
		// Process parameters, passed to us using the setup() public method
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Processing parameters");
		if( is_array($this->_parametersArray) ) {
			$this->driver = array_key_exists('driver', $this->_parametersArray) ? $this->_parametersArray['driver'] : $this->driver;
			$this->host = array_key_exists('host', $this->_parametersArray) ? $this->_parametersArray['host'] : $this->host;
			$this->port = array_key_exists('port', $this->_parametersArray) ? $this->_parametersArray['port'] : $this->port;
			$this->username = array_key_exists('username', $this->_parametersArray) ? $this->_parametersArray['username'] : $this->username;
			$this->username = array_key_exists('user', $this->_parametersArray) ? $this->_parametersArray['user'] : $this->username;
			$this->password = array_key_exists('password', $this->_parametersArray) ? $this->_parametersArray['password'] : $this->password;
			$this->database = array_key_exists('database', $this->_parametersArray) ? $this->_parametersArray['database'] : $this->database;
			$this->prefix = array_key_exists('prefix', $this->_parametersArray) ? $this->_parametersArray['prefix'] : $this->prefix;
			$this->dumpFile = array_key_exists('dumpFile', $this->_parametersArray) ? $this->_parametersArray['dumpFile'] : $this->dumpFile;
			$this->processEmptyPrefix = array_key_exists('process_empty_prefix', $this->_parametersArray) ? $this->_parametersArray['process_empty_prefix'] : $this->processEmptyPrefix;
		}

		// Make sure we have self-assigned the first part
		$this->partNumber = 0;

		// Get DB backup only mode
		$configuration =& AEFactory::getConfiguration();

		// Find tables to be included and put them in the $_tables variable
		$this->enforceSQLCompatibility(); // Joomla! inadvertently sets MySQL40 compatibility and we have to work around it :(
		$this->getTablesToBackup();
		if($this->getError()) return;

		// Find where to store the database backup files
		$this->getBackupFilePaths($this->partNumber);

		// Remove any leftovers
		$this->removeOldFiles();

		// Initialize the extended INSERTs feature
		$this->extendedInserts = ($configuration->get('engine.dump.common.extended_inserts', 0) != 0);
		$this->packetSize = $configuration->get('engine.dump.common.packet_size', 0);
		if( $this->packetSize == 0 ) $this->extendedInserts = false;

		// Initialize the split dump feature
		$this->partSize = $configuration->get('engine.dump.common.splitsize', 1048756);
		if( AEUtilScripting::getScriptingParameter('db.saveasname','normal') == 'output' )
		{
			$this->partSize = 0;
		}
		if( ($this->partSize != 0) && ($this->packetSize != 0) && ($this->packetSize > $this->partSize) )
		{
			$this->packetSize = $this->partSize / 2;
		}

		// Initialize the algorithm
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Initializing algorithm for first run");
		$this->nextTable = array_shift( $this->tables );
		$this->nextRange = 0;
		$this->query = '';

		// FIX 2.2: First table of extra databases was not being written to disk.
		// This deserved a place in the Bug Fix Hall Of Fame. In subsequent calls to _init, the $fp in
		// _writeline() was not nullified. Therefore, the first dump chunk (that is, the first table's
		// definition and first chunk of its data) were not written to disk. This call causes $fp to be
		// nullified, causing it to be recreated, pointing to the correct file. Holly crap, it took me
		// half an hour to get it!
		$null = null;
		$this->writeline($null);

		// Finally, mark ourselves "prepared".
		$this->setState('prepared');
	}

	/**
	 * Implements the _run() abstract method
	 */
	protected function _run()
	{
		// Check if we are already done
		if ($this->getState() == 'postrun') {
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Already finished");
			$this->setStep("");
			$this->setSubstep("");
			return;
		}

		// Mark ourselves as still running (we will test if we actually do towards the end ;) )
		$this->setState('running');

		// Check if we are still adding a database dump part to the archive, or if
		// we have to post-process a part
		if( AEUtilScripting::getScriptingParameter('db.saveasname','normal') != 'output' )
		{
			$archiver =& AEFactory::getArchiverEngine();
			$configuration =& AEFactory::getConfiguration();

			if($configuration->get('engine.postproc.common.after_part',0))
			{
				if(!empty($archiver->finishedPart))
				{
					$filename = array_shift($archiver->finishedPart);
					AEUtilLogger::WriteLog(_AE_LOG_INFO, 'Preparing to post process '.basename($filename));
					$post_proc =& AEFactory::getPostprocEngine();
					$result = $post_proc->processPart( $filename );
					$this->propagateFromObject($post_proc);

					if($result === false)
					{
						$this->setWarning('Failed to process file '.basename($filename));
					}
					else
					{
						AEUtilLogger::WriteLog(_AE_LOG_INFO, 'Successfully processed file '.basename($filename));
					}

					// Should we delete the file afterwards?
					if(
						$configuration->get('engine.postproc.common.delete_after',false)
						&& $post_proc->allow_deletes
						&& ($result !== false)
					)
					{
						AEUtilLogger::WriteLog(_AE_LOG_DEBUG, 'Deleting already processed file '.basename($filename));
						AEPlatform::unlink($filename);
					}

					if($post_proc->break_after) {
						$configuration->set('volatile.breakflag', true);
						return;
					}
				}
			}

			if($configuration->get('volatile.engine.archiver.processingfile',false))
			{
				// We had already started archiving the db file, but it needs more time
				$finished = true;
				AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Continuing adding the SQL dump part to the archive");
				$archiver->addFile(null,null,null);
				$this->propagateFromObject($archiver);
				if($this->getError()) return;
				$finished = !$configuration->get('volatile.engine.archiver.processingfile',false);
				if($finished)
				{
					$this->getNextDumpPart();
				}
				else
				{
					return;
				}
			}
		}

		// Initialize local variables
		$db =& $this->getDB();
		if($this->getError()) return;

		if( !is_object($db) || ($db === false) )
		{
			$this->setError(__CLASS__.'::_run() Could not connect to database?!');
			return;
		}

		$outData	= ''; // Used for outputting INSERT INTO commands

		$this->enforceSQLCompatibility(); // Apply MySQL compatibility option
		if($this->getError()) return;

		// Touch SQL dump file
		$nada = "";
		$this->writeline($nada);

		// Get this table's information
		$tableName = $this->nextTable;
		$tableAbstract = trim( $this->table_name_map[$tableName] );
		$dump_records = $this->tables_data[$tableName]['dump_records'];

		// If it is the first run, find number of rows and get the CREATE TABLE command
		if( $this->nextRange == 0 )
		{
			if($this->getError()) return;
			$outCreate = $this->tables_data[$tableName]['create'];

			// Write the CREATE command
			if(!$this->writeDump($outCreate)) return;

			// Create drop statements if required (the key is defined by the scripting engine)
			$configuration =& AEFactory::getConfiguration();
			if( AEUtilScripting::getScriptingParameter('db.dropstatements',0) )
			{
				$dropStatement = $this->createDrop($this->tables_data[$tableName]['create']);
				if(!empty($dropStatement))
				{
					if(!$this->writeDump($outCreate)) return;
				}
			}

			if( $dump_records )
			{
				// We are dumping data from a table, get the row count
				$this->getRowCount( $tableAbstract );
			}
			else
			{
				// We should not dump any data
				AEUtilLogger::WriteLog(_AE_LOG_INFO, "Skipping dumping data of " . $tableAbstract);
				$this->maxRange = 0;
				$this->nextRange = 1;
				$outData = '';
				$numRows = 0;
			}
		}

		// Check if we have more work to do on this table
		$configuration =& AEFactory::getConfiguration();
		$batchsize = intval($configuration->get('engine.dump.common.batchsize', 1000));
		if($batchsize <= 0) $batchsize = 1000;
		if( ($this->nextRange < $this->maxRange) )
		{
			$timer =& AEFactory::getTimer();

			// Get the number of rows left to dump from the current table
			$sql = "SELECT * FROM `$tableAbstract`";
			if( $this->nextRange == 0 )
			{
				// First run, get a cursor to all records
				$db->setQuery( $sql, 0, $batchsize );
				AEUtilLogger::WriteLog(_AE_LOG_INFO, "Beginning dump of " . $tableAbstract);
			}
			else
			{
				// Subsequent runs, get a cursor to the rest of the records
				$db->setQuery( $sql, $this->nextRange, $batchsize );
				AEUtilLogger::WriteLog(_AE_LOG_INFO, "Continuing dump of " . $tableAbstract . " from record #{$this->nextRange}");
			}

			$this->query = '';
			$numRows = 0;
			$use_abstract = AEUtilScripting::getScriptingParameter('db.abstractnames', 1);
			while( is_array($myRow = $db->loadAssoc(false)) && ( $numRows < ($this->maxRange - $this->nextRange) ) ) {
				$this->createNewPartIfRequired();
				$numRows++;
				$numOfFields = count( $myRow );

				if(
					(!$this->extendedInserts) || // Add header on simple INSERTs, or...
					( $this->extendedInserts && empty($this->query) ) //...on extended INSERTs if there are no other data, yet
				)
				{
					$newQuery = true;
					if( $numOfFields > 0 ) $this->query = "INSERT INTO `" . (!$use_abstract ? $tableName : $tableAbstract) . "` VALUES ";
				}
				else
				{
					// On other cases, just mark that we should add a comma and start a new VALUES entry
					$newQuery = false;
				}

				$outData = '(';

				// Step through each of the row's values
				$fieldID = 0;

				// Used in running backup fix
				$isCurrentBackupEntry = false;

				// Fix 1.2a - NULL values were being skipped
				if( $numOfFields > 0 ) foreach( $myRow as $value )
				{
					// The ID of the field, used to determine placement of commas
					$fieldID++;

					// Fix 2.0: Mark currently running backup as successful in the DB snapshot
					// @todo Only run this on core tables
					if($tableAbstract == '#__ak_stats')
					{
						if($fieldID == 1)
						{
							// Compare the ID to the currently running
							$statistics =& AEFactory::getStatistics();
							$isCurrentBackupEntry = ($value == $statistics->getId());
						}
						elseif ($fieldID == 6)
						{
							// Treat the status field
							$value = $isCurrentBackupEntry ? 'complete' : $value;
						}
					}

					// Post-process the value
					if( is_null($value) )
					{
						$outData .= "NULL"; // Cope with null values
					} else {
						// Accommodate for runtime magic quotes
						$value = @get_magic_quotes_runtime() ? stripslashes( $value ) : $value;
						$outData .= $db->Quote($value);
					}
					if( $fieldID < $numOfFields ) $outData .= ', ';
				} // foreach
				$outData .= ')';

				if( $numOfFields )
				{
					// If it's an existing query and we have extended inserts
					if($this->extendedInserts && !$newQuery)
					{
						// Check the existing query size
						$query_length = strlen($this->query);
						$data_length = strlen($outData);
						if( ($query_length + $data_length) > $this->packetSize )
						{
							// We are about to exceed the packet size. Write the data so far.
							$this->query .= ";\n";
							if(!$this->writeDump($this->query)) return;
							// Then, start a new query
							$this->query = '';
							$this->query = "INSERT INTO `" . (!$use_abstract ? $tableName : $tableAbstract) . "` VALUES ";
							$this->query .= $outData;
						}
						else
						{
							// We have room for more data. Append $outData to the query.
							$this->query .= ', ';
							$this->query .= $outData;
						}
					}
					elseif($this->extendedInserts && $newQuery)
					// If it's a brand new insert statement in an extended INSERTs set
					{
						// Append the data to the INSERT statement
						$this->query .= $outData;
						// Let's see the size of the dumped data...
						$query_length = strlen($this->query);
						if($query_length >= $this->packetSize)
						{
							// This was a BIG query. Write the data to disk.
							$this->query .= ";\n";
							if(!$this->writeDump($this->query)) return;
							// Then, start a new query
							$this->query = '';
						}
					}
					else
					// It's a normal (not extended) INSERT statement
					{
						// Append the data to the INSERT statement
						$this->query .= $outData;
						// Write the data to disk.
						$this->query .= ";\n";
						if(!$this->writeDump($this->query)) return;
						// Then, start a new query
						$this->query = '';
					}
				}
				$outData = '';

				// Check for imminent timeout
				if( $timer->getTimeLeft() <= 0 ) {
					AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Breaking dump of $tableAbstract after $numRows rows; will continue on next step");
					break;
				}
			} // for (all rows left)

			// Advance the _nextRange pointer
			$this->nextRange += ($numRows != 0) ? $numRows : 1;

			$this->setStep($tableName);
			$this->setSubstep($this->nextRange . ' / ' . $this->maxRange);
		} // if more work on the table

		// Finalize any pending query
		// WARNING! If we do not do that now, the query will be emptied in the next operation and all
		// accumulated data will go away...
		if(!empty($this->query))
		{
			$this->query .= ";\n";
			if(!$this->writeDump($this->query)) return;
			$this->query = '';
		}

		// Check for end of table dump (so that it happens inside the same operation)
		if( !($this->nextRange < $this->maxRange) )
		{
			// Tell the user we are done with the table
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Done dumping " . $tableAbstract);

			if(count($this->tables) == 0)
			{
				// We have finished dumping the database!
				AEUtilLogger::WriteLog(_AE_LOG_INFO, "End of database detected; flushing the dump buffers...");
				$null = null;
				$this->writeDump($null);
				AEUtilLogger::WriteLog(_AE_LOG_INFO, "Database has been successfully dumped to SQL file(s)");
				$this->setState('postrun');
				$this->setStep('');
				$this->setSubstep('');
				$this->nextTable = '';
				$this->nextRange = 0;
			} elseif(count($this->tables) != 0) {
				// Switch tables
				$this->nextTable = array_shift( $this->tables );
				$this->nextRange = 0;
				$this->setStep($this->nextTable);
				$this->setSubstep('');
			}
		}

		$null = null;
		$this->writeline($null);
	}

	/**
	 * Implements the _finalize() abstract method
	 *
	 */
	protected function _finalize()
	{
		static $addedExtraSQL = false;

		// This makes sure that we don't re-add the extra SQL if the archiver needed more time
		// to include our file in the archive...
		if(!$addedExtraSQL)
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Adding any extra SQL statements imposed by the filters");
			$filters =& AEFactory::getFilters();
			$this->writeline( $filters->getExtraSQL($this->databaseRoot) );
		}

		// Close the file pointer (otherwise the SQL file is left behind)
		$this->closeFile();

		// If we are not just doing a main db only backup, add the SQL file to the archive
		$finished = true;
		$configuration =& AEFactory::getConfiguration();
		if( AEUtilScripting::getScriptingParameter('db.saveasname','normal') != 'output' )
		{
			$archiver =& AEFactory::getArchiverEngine();
			$configuration =& AEFactory::getConfiguration();

			if( $configuration->get('volatile.engine.archiver.processingfile',false) )
			{
				// We had already started archiving the db file, but it needs more time
				AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Continuing adding the SQL dump to the archive");
				$archiver->addFile(null,null,null);
				if($this->getError()) return;
				$finished = !$configuration->get('volatile.engine.archiver.processingfile',false);
			}
			else
			{
				// We have to add the dump file to the archive
				AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Adding the SQL dump to the archive");
				$archiver->addFileRenamed( $this->tempFile, $this->saveAsName );
				if($this->getError()) return;
				$finished = !$configuration->get('volatile.engine.archiver.processingfile',false);
			}
		}
		else
		{
			// We just have to move the dump file to its final destination
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Moving the SQL dump to its final location");
			$result = AEPlatform::move( $this->tempFile, $this->saveAsName );
			if(!$result)
			{
				$this->setError('Could not move the SQL dump to its final location');
			}
		}

		// Make sure that if the archiver needs more time to process the file we can supply it
		if($finished)
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Removing temporary file");
			AEUtilTempfiles::unregisterAndDeleteTempFile( $this->tempFile, true );
			if($this->getError()) return;

			$this->setState('finished');
		}
	}

	/**
	 * Applies the SQL compatibility setting
	 */
	protected function enforceSQLCompatibility()
	{
		$configuration =& AEFactory::getConfiguration();
		$db =& $this->getDB();
		if($this->getError()) return;

		$verParts = explode( '.', $db->getVersion() );
		if ( $verParts[0] == 5 ) {
			switch( $configuration->get('engine.dump.common.mysql_compatibility') )
			{
				case 1:
					$sql = "SET sql_mode='HIGH_NOT_PRECEDENCE,NO_TABLE_OPTIONS'";
					break;

				case 0:
				default:
					$sql = "SET sql_mode='HIGH_NOT_PRECEDENCE'";
					break;
			}

			$db->setQuery( $sql );
			$db->query();
		}

	}

	/**
	 * Gets the row count for table $tableAbstract. Also updates the $this->maxRange variable.
	 *
	 * @param string $tableAbstract The abstract name of the table (works with canonical names too, though)
	 * @return integer Row count of the table
	 */
	private function getRowCount( $tableAbstract )
	{
		$db =& $this->getDB();
		if($this->getError()) return;

		$sql = "SELECT COUNT(*) FROM `$tableAbstract`";
		$db->setQuery( $sql );
		$this->maxRange = $db->loadResult();
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Rows on " . $tableAbstract . " : " . $this->maxRange);

		return $this->maxRange;
	}

	/**
	 * Creates a new dump part
	 */
	private function getNextDumpPart()
	{

		// On database dump only mode we mustn't create part files!
		if( AEUtilScripting::getScriptingParameter('db.saveasname','normal') == 'output' ) return false;

		// If the archiver is still processing, quit
		$finished = true;
		$configuration =& AEFactory::getConfiguration();
		$archiver =& AEFactory::getArchiverEngine();
		if( $configuration->get('volatile.engine.archiver.processingfile',false) ) return false;

		// We have to add the dump file to the archive
		$this->closeFile();
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Adding the SQL dump part to the archive");
		$archiver->addFileRenamed( $this->tempFile, $this->saveAsName );
		if($this->getError()) return false;
		$finished = !$configuration->get('volatile.engine.archiver.processingfile',false);
		if(!$finished) return false; // Return if the file didn't finish getting added to the archive

		// Remove the old file
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Removing dump part's temporary file");
		AEUtilTempfiles::unregisterAndDeleteTempFile( $this->tempFile, true );

		// Create the new dump part
		$this->partNumber++;
		$this->getBackupFilePaths($this->partNumber);
		$null = null;
		$this->writeline($null);
		return true;
	}

	private function createNewPartIfRequired()
	{
		if( $this->partSize == 0 ) return true;
		$filesize = @filesize($this->tempFile);
		if( $this->extendedInserts )
		{
			$projectedSize = $filesize + $this->packetSize;
		}
		else
		{
			$projectedSize = $filesize + strlen($this->query);
		}
		if( $projectedSize > $this->partSize )
		{
			return $this->getNextDumpPart();
		}
		return true;
	}

// =============================================================================
// Dependency processing - the Twilight Zone starts here
// =============================================================================

	/**
	 * Scans the database for tables to be backed up and sorts them according to
	 * their dependencies on one another.
	 */
	private function getTablesToBackup()
	{
		// First, get a map of table names <--> abstract names
		$this->get_tables_mapping();
		if($this->getError()) return;

		// Find the type and CREATE command of each table/view in the database
		$this->get_tables_data();
		if($this->getError()) return;

		// Process dependencies and rearrange tables respecting them
		$this->process_dependencies();
		if($this->getError()) return;

		// Remove dependencies array
		$this->dependencies = array();
	}

	/**
	 * Generates a mapping between table names as they're stored in the database
	 * and their abstract representation.
	 */
	private function get_tables_mapping()
	{
		// Get a database connection
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Finding tables to include in the backup set");
		$db =& $this->getDB();
		if($this->getError()) return;

		// Reset internal tables
		$this->table_name_map = array();

		// Get the list of all database tables
		$sql = "SHOW TABLES";
		$db->setQuery( $sql );
		$all_tables = $db->loadResultArray();

		$registry =& AEFactory::getConfiguration();
		$root = $registry->get('volatile.database.root', '[SITEDB]');

		// If we have filters, make sure the tables pass the filtering
		$filters = AEFactory::getFilters();
		foreach( $all_tables as $table_name )
		{
			if( substr($table_name,0,3) == '#__' )
			{
				AEUtilLogger::WriteLog(_AE_LOG_WARNING, __CLASS__." :: Table $table_name has a prefix of #__. This would cause restoration errors; table skipped.");
				continue;
			}
			$table_abstract = $this->getAbstract($table_name);
			if( substr($table_abstract,0,4) != 'bak_' ) // Skip backup tables
			{
				// Apply exclusion filters
				if( !$filters->isFiltered($table_abstract, $root, 'dbobject', 'all') ) {
					AEUtilLogger::WriteLog(_AE_LOG_INFO, __CLASS__." :: Adding $table_name (internal name $table_abstract)");
					$this->table_name_map[$table_name] = $table_abstract;
				} else {
					AEUtilLogger::WriteLog(_AE_LOG_INFO, __CLASS__." :: Skipping $table_name (internal name $table_abstract)");
				}
			}
			else
			{
				AEUtilLogger::WriteLog(_AE_LOG_INFO, __CLASS__." :: Backup table $table_name automatically skipped.");
			}
		}

		// If we have MySQL > 5.0 add the list of stored procedures, stored functions
		// and triggers, but only if user has allows that and the target compatibility is
		// not MySQL 4!
		$enable_entities = $registry->get('engine.dump.native.advanced_entitites', true);
		$compatibility = $registry->get('engine.dump.common.mysql_compatibility', 0);
		$verParts = explode( '.', $db->getVersion() );
		if ( ($verParts[0] == 5) && $enable_entities && ($compatibility == 0) )
		{

			// Cache the database name if this is the main site's database

			// 1. Stored procedures
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Listing stored PROCEDUREs");
			$sql = "SHOW PROCEDURE STATUS WHERE `Db`=".$db->Quote($this->database);
			$db->setQuery( $sql );
			$all_entries = $db->loadResultArray(1);
			// If we have filters, make sure the tables pass the filtering
			if(is_array($all_entries))
			if(count($all_entries))

			foreach( $all_entries as $entity_name )
			{
				$entity_abstract = $this->getAbstract($entity_name);
				if(!(substr($entity_abstract,0,4) == 'bak_')) // Skip backup entities
				{
					if( !$filters->isFiltered($entity_abstract, $root, 'dbobject', 'all') ) $this->table_name_map[$entity_name] = $entity_abstract;
				}
			}

			// 2. Stored functions
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Listing stored FUNCTIONs");
			$sql = "SHOW FUNCTION STATUS WHERE `Db`=".$db->Quote($this->database);
			$db->setQuery( $sql );
			$all_entries = $db->loadResultArray(1);
			// If we have filters, make sure the tables pass the filtering
			if(is_array($all_entries))
			if(count($all_entries))
			foreach( $all_entries as $entity_name )
			{
				$entity_abstract = $this->getAbstract($entity_name);
				if(!(substr($entity_abstract,0,4) == 'bak_')) // Skip backup entities
				{
					// Apply exclusion filters if set
					if( !$filters->isFiltered($entity_abstract, $root, 'dbobject', 'all') ) $this->table_name_map[$entity_name] = $entity_abstract;
				}
			}

			// 3. Triggers
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Listing stored TRIGGERs");
			$sql = "SHOW TRIGGERS";
			$db->setQuery( $sql );
			$all_entries = $db->loadResultArray();
			// If we have filters, make sure the tables pass the filtering
			if(is_array($all_entries))
			if(count($all_entries))
			foreach( $all_entries as $entity_name )
			{
				$entity_abstract = $this->getAbstract($entity_name);
				if(!(substr($entity_abstract,0,4) == 'bak_')) // Skip backup entities
				{
					// Apply exclusion filters if set
					if( !$filters->isFiltered($entity_abstract, $root, 'dbobject', 'all') ) $this->table_name_map[$entity_name] = $entity_abstract;
				}
			}

		} // if MySQL 5
	}

	/**
	 * Populates the _tables array with the metadata of each table and generates
	 * dependency information for views and merge tables
	 */
	private function get_tables_data()
	{
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Starting CREATE TABLE and dependency scanning");

		// Get a database connection
		$db =& $this->getDB();
		if($this->getError()) return;

		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Got database connection");

		// Reset internal tables
		$this->tables_data = array();
		$this->dependencies = array();

		// Get a list of tables where their engine type is shown
		$sql = 'SHOW TABLES';
		$db->setQuery( $sql );
		$metadata_list = $db->loadRowList();

		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Got SHOW TABLES");

		// Get filters and filter root
		$registry =& AEFactory::getConfiguration();
		$root = $registry->get('volatile.database.root', '[SITEDB]');
		$filters =& AEFactory::getFilters();

		foreach($metadata_list as $table_metadata)
		{
			// Skip over tables not included in the backup set
			if(!array_key_exists($table_metadata[0], $this->table_name_map)) continue;

			// Basic information
			$table_name = $table_metadata[0];
			$table_abstract = $this->table_name_map[$table_metadata[0]];
			$new_entry = array(
				'type'			=> 'table',
				'dump_records'	=> true
			);

			// Get the CREATE command
			$dependencies = array();
			$new_entry['create'] = $this->get_create($table_abstract, $table_name, $new_entry['type'], $dependencies);
			$new_entry['dependencies'] = $dependencies;

			if( $new_entry['type'] == 'view' )
			{
				$new_entry['dump_records'] = false;
			} else {
				$new_entry['dump_records'] = true;
			}

			// Scan for the table engine.
			$engine = null; // So that we detect VIEWs correctly
			if( $new_entry['type'] == 'table' )
			{
				$engine = 'MyISAM'; // So that even with MySQL 4 hosts we don't screw this up
				$engine_keys = array('ENGINE=', 'TYPE=');
				foreach($engine_keys as $engine_key)
				{
					$start_pos = strrpos($new_entry['create'], $engine_key);
					if( $start_pos !== false )
					{
						// Advance the start position just after the position of the ENGINE keyword
						$start_pos += strlen($engine_key);
						// Try to locate the space after the engine type
						$end_pos = stripos($new_entry['create'], ' ', $start_pos);
						if( $end_pos === false)
						{
							// Uh... maybe it ends with ENGINE=EngineType;
							$end_pos = stripos($new_entry['create'], ';', $start_pos);
						}
						if( $end_pos !== false)
						{
							// Grab the string
							$engine = substr( $new_entry['create'], $start_pos, $end_pos - $start_pos );
							if(empty($engine))
							{
								AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "*** DEBUG *** $table_name - engine $engine");
								AEUtilLogger::WriteLog(_AE_LOG_DEBUG, $new_entry['create']);
								AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "start $start_pos - end $end_pos");
							}
						}
					}
				}
				$engine = strtoupper($engine);
			}

			switch($engine)
			{
				/*
				// Views -- They are detected based on their CREATE statement
				case null:
					$new_entry['type'] = 'view';
					$new_entry['dump_records'] = false;
					break;
				*/

				// Merge tables
				case 'MRG_MYISAM':
					$new_entry['type'] = 'merge';
					$new_entry['dump_records'] = false;
					break;

				// Tables whose data we do not back up (memory, federated and can-have-no-data tables)
				case 'MEMORY':
				case 'EXAMPLE':
				case 'BLACKHOLE':
				case 'FEDERATED':
					$new_entry['dump_records'] = false;
					break;

				// Normal tables and VIEWs
				default:
					break;
			} // switch

			// Table Data Filter - skip dumping table contents of filtered out tables
			if( $filters->isFiltered($table_abstract, $root, 'dbobject', 'content') )
			{
				$new_entry['dump_records'] = false;
			}

			$this->tables_data[$table_name] = $new_entry;
		} // foreach

		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Got table list");

		// If we have MySQL > 5.0 add stored procedures, stored functions and triggers
		$enable_entities = $registry->get('engine.dump.native.advanced_entitites', true);
		$compatibility = $registry->get('engine.dump.common.mysql_compatibility', 0);
		$verParts = explode( '.', $db->getVersion() );
		if ( ($verParts[0] == 5) && $enable_entities && ($compatibility == 0) )
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Listing MySQL entities");
			// Get a list of procedures
			$sql = 'SHOW PROCEDURE STATUS WHERE `Db`='.$db->Quote($this->database);
			$db->setQuery( $sql );
			$metadata_list = $db->loadRowList();

			if(is_array($metadata_list))
			if(count($metadata_list))
			foreach($metadata_list as $entity_metadata)
			{
				// Skip over entities not included in the backup set
				if(!array_key_exists($entity_metadata[1], $this->table_name_map)) continue;

				// Basic information
				$entity_name = $entity_metadata[1];
				$entity_abstract = $this->table_name_map[$entity_metadata[1]];
				$new_entry = array(
					'type'			=> 'procedure',
					'dump_records'	=> false
				);

				// There's no point trying to add a non-procedure entity
				if($entity_metadata[2] != 'PROCEDURE') continue;

				$dependencies = array();
				$new_entry['create'] = $this->get_create($entity_abstract, $entity_name, $new_entry['type'], $dependencies);
				$new_entry['dependencies'] = $dependencies;
				$this->tables_data[$entity_name] = $new_entry;
			} // foreach

			// Get a list of functions
			$sql = 'SHOW FUNCTION STATUS WHERE `Db`='.$db->Quote($this->database);
			$db->setQuery( $sql );
			$metadata_list = $db->loadRowList();

			if(is_array($metadata_list))
			if(count($metadata_list))
			foreach($metadata_list as $entity_metadata)
			{
				// Skip over entities not included in the backup set
				if(!array_key_exists($entity_metadata[1], $this->table_name_map)) continue;

				// Basic information
				$entity_name = $entity_metadata[1];
				$entity_abstract = $this->table_name_map[$entity_metadata[1]];
				$new_entry = array(
					'type'			=> 'function',
					'dump_records'	=> false
				);

				// There's no point trying to add a non-function entity
				if($entity_metadata[2] != 'FUNCTION') continue;

				$dependencies = array();
				$new_entry['create'] = $this->get_create($entity_abstract, $entity_name, $new_entry['type'], $dependencies);
				$new_entry['dependencies'] = $dependencies;
				$this->tables_data[$entity_name] = $new_entry;
			} // foreach

			// Get a list of triggers
			$sql = 'SHOW TRIGGERS';
			$db->setQuery( $sql );
			$metadata_list = $db->loadRowList();

			if(is_array($metadata_list))
			if(count($metadata_list))
			foreach($metadata_list as $entity_metadata)
			{
				// Skip over entities not included in the backup set
				if(!array_key_exists($entity_metadata[0], $this->table_name_map)) continue;

				// Basic information
				$entity_name = $entity_metadata[0];
				$entity_abstract = $this->table_name_map[$entity_metadata[0]];
				$new_entry = array(
					'type'			=> 'trigger',
					'dump_records'	=> false
				);

				$dependencies = array();
				$new_entry['create'] = $this->get_create($entity_abstract, $entity_name, $new_entry['type'], $dependencies);
				$new_entry['dependencies'] = $dependencies;
				$this->tables_data[$entity_name] = $new_entry;
			} // foreach

			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Got MySQL entities list");
		}

		// Only store unique values
		if(count($dependencies) > 0)
			$dependencies = array_unique($dependencies);
	}

	/**
	 * Gets the CREATE TABLE command for a given table/view/procedure/function/trigger
	 * @param string $table_abstract The abstracted name of the entity
	 * @param string $table_name The name of the table
	 * @param string $type The type of the entity to scan. If it's found to differ, the correct type is returned.
	 * @param array $dependencies The dependencies of this table
	 * @return string The CREATE command, w/out newlines
	 */
	private function get_create( $table_abstract, $table_name, &$type, &$dependencies )
	{
		$db =& $this->getDB();
		if($this->getError()) return;

		switch($type)
		{
			case 'table':
			case 'merge':
			case 'view':
				$sql = "SHOW CREATE TABLE `$table_abstract`";
				break;

			case 'procedure':
				$sql = "SHOW CREATE PROCEDURE `$table_abstract`";
				break;

			case 'function':
				$sql = "SHOW CREATE FUNCTION `$table_abstract`";
				break;

			case 'trigger':
				$sql = "SHOW CREATE TRIGGER `$table_abstract`";
				break;
		}

		$db->setQuery( $sql );
		$temp = $db->loadRowList();
		if( in_array($type, array('procedure','function','trigger')) )
		{
			$table_sql = $temp[0][2];
		}
		else
		{
			$table_sql = $temp[0][1];
		}
		unset( $temp );

		// Smart table type detection
		if( in_array($type, array('table','merge','view')) )
		{
			// Check for CREATE VIEW
			$pattern = '/^CREATE(.*) VIEW (.*)/i';
			$result = preg_match($pattern, $table_sql);
			if($result === 1)
			{
				// This is a view.
				$type = 'view';
			}
			else
			{
				// This is a table.
				$type = 'table';
			}

			// Is it a VIEW but we don't have SHOW VIEW privileges?
			if(empty($table_sql)) $type = 'view';
		}

		// Replace table name and names of referenced tables with their abstracted
		// forms and populate dependency tables at the same time

		// On DB only backup we don't want any replacing to take place, do we?
		if( !AEUtilScripting::getScriptingParameter('db.abstractnames',1) ) $old_table_sql = $table_sql;

		// Even on simple tables, we may have foreign key references.
		// As a result, we need to replace those referenced table names
		// as well. On views and merge arrays, we have referenced tables
		// by definition.
		$dependencies = array();
		// First, the table/view/merge table name itself:
		$table_sql = str_replace( $table_name , $table_abstract, $table_sql );
		// Now, loop for all table entries
		foreach($this->table_name_map as $ref_normal => $ref_abstract)
		{
			if( $pos = strpos($table_sql, "`$ref_normal`") )
			{
				// Add a reference hit
				$this->dependencies[$ref_normal][] = $table_name;
				// Add the dependency to this table's metadata
				$dependencies[] = $ref_normal;
				// Do the replacement
				$table_sql = str_replace("`$ref_normal`", "`$ref_abstract`", $table_sql);
			}
		}

		// On DB only backup we don't want any replacing to take place, do we?
		if( !AEUtilScripting::getScriptingParameter('db.abstractnames',1) ) $table_sql = $old_table_sql;

		// Replace newlines with spaces
		$table_sql = str_replace( "\n", " ", $table_sql ) . ";\n";
		$table_sql = str_replace( "\r", " ", $table_sql );
		$table_sql = str_replace( "\t", " ", $table_sql );

		// Post-process CREATE VIEW
		if($type == 'view')
		{
			$pos_view = strpos($table_sql, ' VIEW ');

			if($pos_view > 7 )
			{
				// Only post process if there are view properties between the CREATE and VIEW keywords
				$propstring = substr($table_sql, 7, $pos_view - 7); // Properties string
				// Fetch the ALGORITHM={UNDEFINED | MERGE | TEMPTABLE} keyword
				$algostring = '';
				$algo_start = strpos($propstring, 'ALGORITHM=');
				if($algo_start !== false)
				{
					$algo_end = strpos($propstring, ' ', $algo_start);
					$algostring = substr($propstring, $algo_start, $algo_end - $algo_start + 1);
				}
				// Create our modified create statement
				$table_sql = 'CREATE OR REPLACE '.$algostring.substr($table_sql, $pos_view);
			}
		}
		elseif($type == 'procedure')
		{
			$pos_entity = strpos($table_sql, ' PROCEDURE ');
			$table_sql = 'CREATE'.substr($table_sql, $pos_entity);
		}
		elseif($type == 'function')
		{
			$pos_entity = strpos($table_sql, ' FUNCTION ');
			$table_sql = 'CREATE'.substr($table_sql, $pos_entity);
		}
		elseif($type == 'trigger')
		{
			$pos_entity = strpos($table_sql, ' TRIGGER ');
			$table_sql = 'CREATE'.substr($table_sql, $pos_entity);
		}

		// Add DROP statements for DB only backup
		if( AEUtilScripting::getScriptingParameter('db.dropstatements',0) )
		{
			if( ($type == 'table') || ($type == 'merge') )
			{
				// Table or merge tables, get a DROP TABLE statement
				$drop = "DROP TABLE IF EXISTS `$table_name`;\n";
			}
			elseif($type == 'view')
			{
				// Views get a DROP VIEW statement
				$drop = "DROP VIEW IF EXISTS `$table_name`;\n";
			}
			elseif($type == 'procedure')
			{
				// Procedures get a DROP PROCEDURE statement and proper delimiter strings
				$drop = "DROP PROCEDURE IF EXISTS `$table_name`;\n";
				$drop .= "DELIMITER // ";
				$table_sql = str_replace( "\r", " ", $table_sql );
				$table_sql = str_replace( "\t", " ", $table_sql );
				$table_sql = rtrim($table_sql,";\n")." // DELIMITER ;\n";
			}
			elseif($type == 'function')
			{
				// Procedures get a DROP FUNCTION statement and proper delimiter strings
				$drop = "DROP FUNCTION IF EXISTS `$table_name`;\n";
				$drop .= "DELIMITER // ";
				$table_sql = str_replace( "\r", " ", $table_sql );
				$table_sql = rtrim($table_sql,";\n")."// DELIMITER ;\n";
			}
			elseif($type == 'trigger')
			{
				// Procedures get a DROP TRIGGER statement and proper delimiter strings
				$drop = "DROP TRIGGER IF EXISTS `$table_name`;\n";
				$drop .= "DELIMITER // ";
				$table_sql = str_replace( "\r", " ", $table_sql );
				$table_sql = str_replace( "\t", " ", $table_sql );
				$table_sql = rtrim($table_sql,";\n")."// DELIMITER ;\n";
			}
			$table_sql = $drop . $table_sql;
		}

		return $table_sql;
	}

	private function process_dependencies()
	{
		if(count($this->table_name_map) > 0)
			foreach($this->table_name_map as $table_name => $table_abstract)
			{
				$this->push_table($table_name);
			}
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Processed dependencies");
	}

	/**
	 * Pushes a table in the _tables stack, making sure it will appear after
	 * its dependencies and other tables/views depending on it will eventually
	 * appear after it. It's a complicated chicken-and-egg problem. Just make
	 * sure you don't have any bloody circular references!!
	 * @param string $table_name Canonical name of the table to push
	 * @param array $stack When called recursive, other views/tables previously processed in order to detect *ahem* dependency loops...
	 */
	private function push_table($table_name, $stack = array(), $currentRecursionDepth = 0)
	{
		// Load information
		$table_data = $this->tables_data[$table_name];
		$table_abstract = $this->table_name_map[$table_name];
		if(array_key_exists('dependencies', $table_data))
		{
			$referenced = $table_data['dependencies'];
		} else {
			$referenced = array();
		}
		unset($table_data);

		// Try to find the minimum insert position, so as to appear after the last referenced table
		$insertpos = false;
		if(count($referenced))
		{
			foreach($referenced as $referenced_table)
			{
				if(count($this->tables))
				{
					$newpos = array_search($referenced_table, $this->tables);
					if($newpos !== false) {
						if($insertpos === false)
						{
							$insertpos = $newpos;
						}
						else
						{
							$insertpos = max($insertpos, $newpos);
						}
					}
				}
			}
		}

		// Add to the _tables array
		if(count($this->tables) && !($insertpos === false)) {
			array_splice($this->tables, $insertpos+1, 0, $table_name);
		}
		else
		{
			$this->tables[] = $table_name;
		}

		// Here's what... Some other table/view might depend on us, so we must appear
		// before it (actually, it must appear after us). So, we scan for such
		// tables/views and relocate them
		if(count($this->dependencies))
		{
			if(array_key_exists($table_name, $this->dependencies))
			{
				foreach($this->dependencies[$table_name] as $depended_table)
				{
					// First, make sure that either there is no stack, or the
					// depended table doesn't belong it. In any other case, we
					// were fooled to follow an endless dependency loop and we
					// will simply bail out and let the user sort things out.
					if(count($stack) > 0)
						if(in_array($depended_table, $stack)) continue;

					$my_position = array_search($table_name, $this->tables);
					$remove_position = array_search($depended_table, $this->tables);
					if( ($remove_position !== false) && ($remove_position < $my_position) )
					{
						$stack[] = $table_name;
						array_splice($this->tables, $remove_position, 1);

						// Where should I put the other table/view now? Don't tell me.
						// I have to recurse...
						if($currentRecursionDepth < 19)
						{
							$this->push_table($depended_table, $stack, ++$currentRecursionDepth);
						}
					} // if remove_position
				} // foreach
			} // if in dependencies
		} // if there are dependencies
	}

	/**
	 * Creates a drop query from a CREATE query
	 * @param $query string The CREATE query to process
	 * @return string The DROP statement
	 */
	private function createDrop($query)
	{
		// Initialize
		$dropQuery = '';

		// Parse CREATE TABLE commands
		if( substr($query, 0, 12) == 'CREATE TABLE')
		{
			// Try to get the table name
			$restOfQuery = trim(substr($query, 12, strlen($query)-12 )); // Rest of query, after CREATE TABLE
			// Is there a backtick?
			if(substr($restOfQuery,0,1) == '`')
			{
				// There is... Good, we'll just find the matching backtick
				$pos = strpos($restOfQuery, '`', 1);
				$tableName = substr($restOfQuery,1,$pos - 1);
			}
			else
			{
				// Nope, let's assume the table name ends in the next blank character
				$pos = strpos($restOfQuery, ' ', 1);
				$tableName = substr($restOfQuery,1,$pos - 1);
			}
			unset($restOfQuery);
			// Try to drop the table anyway
			$dropQuery = 'DROP TABLE IF EXISTS `'.$tableName.'`;';
		}
		// Parse CREATE VIEW commands
		elseif( (substr($query, 0, 7) == 'CREATE ') && (strpos($query, ' VIEW ') !== false) )
		{
			// Try to get the view name
			$view_pos = strpos($query, ' VIEW ');
			$restOfQuery = trim( substr($query, $view_pos + 6) ); // Rest of query, after VIEW string
			// Is there a backtick?
			if(substr($restOfQuery,0,1) == '`')
			{
				// There is... Good, we'll just find the matching backtick
				$pos = strpos($restOfQuery, '`', 1);
				$tableName = substr($restOfQuery,1,$pos - 1);
			}
			else
			{
				// Nope, let's assume the table name ends in the next blank character
				$pos = strpos($restOfQuery, ' ', 1);
				$tableName = substr($restOfQuery,1,$pos - 1);
			}
			unset($restOfQuery);
			$dropQuery = 'DROP VIEW IF EXISTS `'.$tableName.'`;';
		}
		// CREATE PROCEDURE pre-processing
		elseif( (substr($query, 0, 7) == 'CREATE ') && (strpos($query, 'PROCEDURE ') !== false) )
		{
			// Try to get the procedure name
			$entity_keyword = ' PROCEDURE ';
			$entity_pos = strpos($query, $entity_keyword);
			$restOfQuery = trim( substr($query, $entity_pos + strlen($entity_keyword)) ); // Rest of query, after entity key string
			// Is there a backtick?
			if(substr($restOfQuery,0,1) == '`')
			{
				// There is... Good, we'll just find the matching backtick
				$pos = strpos($restOfQuery, '`', 1);
				$entity_name = substr($restOfQuery,1,$pos - 1);
			}
			else
			{
				// Nope, let's assume the entity name ends in the next blank character
				$pos = strpos($restOfQuery, ' ', 1);
				$entity_name = substr($restOfQuery,1,$pos - 1);
			}
			unset($restOfQuery);
			$dropQuery = 'DROP'.$entity_keyword.'IF EXISTS `'.$entity_name.'`;';
		}
		// CREATE FUNCTION pre-processing
		elseif( (substr($query, 0, 7) == 'CREATE ') && (strpos($query, 'FUNCTION ') !== false) )
		{
			// Try to get the procedure name
			$entity_keyword = ' FUNCTION ';
			$entity_pos = strpos($query, $entity_keyword);
			$restOfQuery = trim( substr($query, $entity_pos + strlen($entity_keyword)) ); // Rest of query, after entity key string
			// Is there a backtick?
			if(substr($restOfQuery,0,1) == '`')
			{
				// There is... Good, we'll just find the matching backtick
				$pos = strpos($restOfQuery, '`', 1);
				$entity_name = substr($restOfQuery,1,$pos - 1);
			}
			else
			{
				// Nope, let's assume the entity name ends in the next blank character
				$pos = strpos($restOfQuery, ' ', 1);
				$entity_name = substr($restOfQuery,1,$pos - 1);
			}
			unset($restOfQuery);

			// Try to drop the entity anyway
			$dropQuery = 'DROP'.$entity_keyword.'IF EXISTS `'.$entity_name.'`;';
		}
		// CREATE TRIGGER pre-processing
		elseif( (substr($query, 0, 7) == 'CREATE ') && (strpos($query, 'TRIGGER ') !== false) )
		{
			// Try to get the procedure name
			$entity_keyword = ' TRIGGER ';
			$entity_pos = strpos($query, $entity_keyword);
			$restOfQuery = trim( substr($query, $entity_pos + strlen($entity_keyword)) ); // Rest of query, after entity key string
			// Is there a backtick?
			if(substr($restOfQuery,0,1) == '`')
			{
				// There is... Good, we'll just find the matching backtick
				$pos = strpos($restOfQuery, '`', 1);
				$entity_name = substr($restOfQuery,1,$pos - 1);
			}
			else
			{
				// Nope, let's assume the entity name ends in the next blank character
				$pos = strpos($restOfQuery, ' ', 1);
				$entity_name = substr($restOfQuery,1,$pos - 1);
			}
			unset($restOfQuery);

			// Try to drop the entity anyway
			$dropQuery = 'DROP'.$entity_keyword.'IF EXISTS `'.$entity_name.'`;';
		}

		return $dropQuery;
	}
}