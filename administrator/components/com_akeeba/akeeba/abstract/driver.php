<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2010 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: driver.php 224 2010-08-23 16:02:49Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Database driver superclass. Used as the base of all Akeeba Engine database drivers.
 * Strongly based on Joomla!'s JDatabase class.
 */
abstract class AEAbstractDriver extends AEAbstractObject
{
	/** @var string The SQL query string */
	protected $sql = '';

	/** @var int The db server's error number */
	protected $errorNum = 0;

	/** @var string The db server's error string */
	protected $errorMsg = '';

	/** @var string The prefix used in the database, if any */
	protected $table_prefix = '';

	/** @var string The database name */
	protected $database;

	/** @var resource The db conenction resource */
	protected $resource = '';

	/** @var resource The internal db cursor */
	protected $cursor = null;

	/** @var int Query's limit */
	protected $limit = 0;

	/** @var int Query's offset */
	protected $offset = 0;

	/** @var string Quote for named objects */
	protected $nameQuote = '';

	/** @var bool Support for UTF-8 */
	protected $utf;

	/**
	 * Database object constructor
	 * @param	array	List of options used to configure the connection
	 */
	public function __construct( $options )
	{
		$prefix		= array_key_exists('prefix', $options)		? $options['prefix']	: '';
		$database	= array_key_exists('database', $options)	? $options['database']	: '';

		$this->table_prefix	= $prefix;
		$this->database	= $database;
	}

	/**
	 * Database object destructor
	 * @return bool
	 */
	public function __destruct()
	{
		return $this->close();
	}

	/**
	 * By default, when the object is shutting down, the connection is closed
	 */
	public function _onSerialize()
	{
		$this->close();
	}

	public function __wakeup()
	{
		$this->open();
	}

	/**
	 * Opens a database connection. It MUST be overriden by children classes
	 * @return bool
	 */
	public function open()
	{
		// Determine utf-8 support
		$this->utf = $this->hasUTF();

		// Set charactersets (needed for MySQL 4.1.2+)
		if ($this->utf){
			$this->setUTF();
		}

		$this->select($this->database);
	}

	/**
	 * Select a database for use
	 * @param	string $database
	 * @return	boolean True if the database has been successfully selected
	 */
	abstract public function select($database);

	/**
	 * Closes the database connection
	 */
	abstract public function close();

	/**
	 * Determines UTF support
	 * @return bool
	 */
	abstract public function hasUTF();

	/**
	 * Custom settings for UTF support
	 */
	abstract public function setUTF();

	/**
	 * Get the error number
	 * @return int The error number for the most recent query
	 */
	public final function getErrorNum() {
		return $this->errorNum;
	}

	/**
	 * Get the error message
	 * @return string The error message for the most recent query
	 */
	public final function getErrorMsg($escaped = false)
	{
		if($escaped) {
			return addslashes($this->errorMsg);
		} else {
			return $this->errorMsg;
		}
	}

	/**
	 * Get a database escaped string
	 * @param	string	The string to be escaped
	 * @param	bool	Optional parameter to provide extra escaping
	 * @return	string
	 */
	abstract public function getEscaped( $text, $extra = false );

	/**
	 * Quote an identifier name (field, table, etc)
	 * @param	string	The name
	 * @return	string	The quoted name
	 */
	public final function nameQuote( $s )
	{
		// Only quote if the name is not using dot-notation
		if (strpos( $s, '.' ) === false)
		{
			$q = $this->nameQuote;
			if (strlen( $q ) == 1) {
				return $q . $s . $q;
			} else {
				return $q{0} . $s . $q{1};
			}
		}
		else {
			return $s;
		}
	}

	/**
	 * Get the database table prefix
	 * @return string The database prefix
	 */
	public final function getPrefix()
	{
		return $this->table_prefix;
	}

	/**
	 * Sets the SQL query string for later execution.
	 * This function replaces a string identifier <var>$prefix</var> with the
	 * string held is the <var>table_prefix</var> class variable.
	 * @param string The SQL query
	 * @param string The offset to start selection
	 * @param string The number of results to return
	 * @param string The common table prefix
	 */
	public function setQuery( $sql, $offset = 0, $limit = 0, $prefix='#__' )
	{
		$this->sql		= $this->replacePrefix( $sql, $prefix );
		$this->limit	= (int) $limit;
		$this->offset	= (int) $offset;
		$this->cursor	= null;
	}

	/**
	 * This function replaces a string identifier <var>$prefix</var> with the
	 * string held is the <var>table_prefix</var> class variable.
	 * @access public
	 * @param string The SQL query
	 * @param string The common table prefix
	 */
	public final function replacePrefix( $sql, $prefix='#__' )
	{
		$sql = trim( $sql );

		$escaped = false;
		$quoteChar = '';

		$n = strlen( $sql );

		$startPos = 0;
		$literal = '';
		while ($startPos < $n) {
			$ip = strpos($sql, $prefix, $startPos);
			if ($ip === false) {
				break;
			}

			$j = strpos( $sql, "'", $startPos );
			$k = strpos( $sql, '"', $startPos );
			if (($k !== FALSE) && (($k < $j) || ($j === FALSE))) {
				$quoteChar	= '"';
				$j			= $k;
			} else {
				$quoteChar	= "'";
			}

			if ($j === false) {
				$j = $n;
			}

			$literal .= str_replace( $prefix, $this->table_prefix,substr( $sql, $startPos, $j - $startPos ) );
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n) {
				break;
			}

			// quote comes first, find end of quote
			while (TRUE) {
				$k = strpos( $sql, $quoteChar, $j );
				$escaped = false;
				if ($k === false) {
					break;
				}
				$l = $k - 1;
				while ($l >= 0 && $sql{$l} == '\\') {
					$l--;
					$escaped = !$escaped;
				}
				if ($escaped) {
					$j	= $k+1;
					continue;
				}
				break;
			}
			if ($k === FALSE) {
				// error in the query - no end quote; ignore it
				break;
			}
			$literal .= substr( $sql, $startPos, $k - $startPos + 1 );
			$startPos = $k+1;
		}
		if ($startPos < $n) {
			$literal .= substr( $sql, $startPos, $n - $startPos );
		}
		return $literal;
	}

	/**
	 * Get the active query
	 * @return string The current value of the internal SQL vairable
	 */
	public function getQuery()
	{
		return $this->sql;
	}

	/**
	 * Execute the query
	 * @return mixed A database resource if successful, FALSE if not.
	 */
	abstract public function query();

	/**
	 * This method loads the first field of the first row returned by the query.
	 * @return mixed The value returned in the query or null if the query failed.
	 */
	abstract public function loadResult();

	/**
	 * Load an array of single field results into an array
	 * @return mixed An array, or null if query failed
	 */
	abstract public function loadResultArray($numinarray = 0);

	/**
	 * Fetch a result row as an associative array
	 * @param bool $free_cursor If true, frees the cursor after returning the result
	 * @return array An associative array, null if query failed or false on end of data
	 */
	abstract public function loadAssoc($free_cursor = false);

	/**
	 * Load a associactive list of database rows
	 * @param string The field name of a primary key
	 * @return array If key is empty as sequential list of returned records.
	 */
	abstract public function loadAssocList( $key=null );

	/**
	 * Load a list of database rows (numeric column indexing)
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 * @param string The field name of a primary key
	 * @return array
	 */
	abstract public function loadRowList( $key=null );

	/**
	 * Get the version of the database connector
	 * @return string The database server's version number
	 */
	abstract public function getVersion();

	/**
	 * Get a quoted database escaped string
	 *
	 * @param	string	A string
	 * @param	boolean	Default true to escape string, false to leave the string unchanged
	 * @return	string
	 */
	public final function Quote( $text, $escaped = true )
	{
		return '\''.($escaped ? $this->getEscaped( $text ) : $text).'\'';
	}

	/**
	 * Returns the last INSERT auto_increase column's value
	 * @return int
	 */
	abstract public function insertid();

	/**
	 * Returns an array with the names of tables, views, procedures, functions and triggers
	 * in the database. The table names are the keys of the tables, whereas the value is
	 * the type of each element: table, view, merge, temp, procedure, function or trigger.
	 * Note that merge are MRG_MYISAM tables and temp is non-permanent data table, usually
	 * set up as temporary, black hole or federated tables. These two types should never,
	 * ever, have their data dumped in the SQL dump file.
	 *
	 * @param bool $abstract Return abstract or normal names? Defaults to true (abstract names)
	 * @return array
	 */
	public function getTables($abstract = true)
	{
		static $tables = array();

		if(!empty($tables)) return $tables;

		$sql = "SHOW TABLES";
		$this->setQuery($sql);
		$all_tables = $this->loadResultArray();

		if(!empty($all_tables))
		{
			// Start by adding tables and views to the list
			foreach($all_tables as $table_name)
			{
				if($abstract) $table_name = $this->getAbstract($table_name);
				$tables[$table_name] = 'table';
			}

			// Loop all metadatas
			foreach($all_tables as $table_metadata)
			{
				$table_name = $table_metadata;
				$table_abstract = $this->getAbstract($table_metadata);
				$type = 'table';

				if($abstract) $table_metadata = $table_abstract;

				$create = $this->get_create($table_abstract, $table_name, $type);
				// Scan for the table engine.
				$engine = null; // So that we detect VIEWs correctly

				if( $type == 'table' )
				{
					$engine = 'MyISAM'; // So that even with MySQL 4 hosts we don't screw this up
					$engine_keys = array('ENGINE=', 'TYPE=');
					foreach($engine_keys as $engine_key)
					{
						$start_pos = strrpos($create, $engine_key);
						if( $start_pos !== false )
						{
							// Advance the start position just after the position of the ENGINE keyword
							$start_pos += strlen($engine_key);
							// Try to locate the space after the engine type
							$end_pos = stripos($create, ' ', $start_pos);
							if( $end_pos === false)
							{
								// Uh... maybe it ends with ENGINE=EngineType;
								$end_pos = stripos($create, ';');
							}
							if( $end_pos !== '')
							{
								// Grab the string
								$engine = substr( $create, $start_pos, $end_pos - $start_pos );
							}
						}
					}
					$engine = strtoupper($engine);
				}

				switch($engine)
				{
					// Views -- FIX: They are detected based on their CREATE STATEMENT
					case null:
						$tables[$table_metadata] = 'view';
						break;

					// Merge tables
					case 'MRG_MYISAM':
						$tables[$table_metadata] = 'merge';
						break;

					// Tables whose data we do not back up (memory, federated and can-have-no-data tables)
					case 'MEMORY':
					case 'EXAMPLE':
					case 'BLACKHOLE':
					case 'FEDERATED':
						$tables[$table_metadata] = 'temp';
						break;

					// Normal tables
					default:
						break;
				} // switch
			} // foreach
		} // if !empty

		// If we have MySQL > 5.0 add the list of stored procedures, stored functions
		// and triggers
		$registry =& AEFactory::getConfiguration();
		$enable_entities = $registry->get('engine.dump.native.advanced_entitites', true);
		$compatibility = $registry->get('engine.dump.common.mysql_compatibility', 0);
		$verParts = explode( '.', $this->getVersion() );
		if ( ($verParts[0] == 5) && $enable_entities && ($compatibility == 0) )
		{
			// 1. Stored procedures
			$sql = "SHOW PROCEDURE STATUS WHERE ".$this->nameQuote('Db') ."=".$this->Quote($this->database);
			$this->setQuery( $sql );
			$all_entries = $this->loadResultArray(1);
			if(is_array($all_entries))
			if(count($all_entries))
			foreach( $all_entries as $table_name )
			{
				if($abstract) $table_name = $this->getAbstract($table_name);
				$tables[$table_name] = 'procedure';
			}

			// 2. Stored functions
			$sql = "SHOW FUNCTION STATUS WHERE ".$this->nameQuote('Db') ."=".$this->Quote($this->database);
			$this->setQuery( $sql );
			$all_entries = $this->loadResultArray(1);
			// If we have filters, make sure the tables pass the filtering
			if(is_array($all_entries))
			if(count($all_entries))
			foreach( $all_entries as $table_name )
			{
				if($abstract) $table_name = $this->getAbstract($table_name);
				$tables[$table_name] = 'function';
			}

			// 3. Triggers
			$sql = "SHOW TRIGGERS";
			$this->setQuery( $sql );
			$all_entries = $this->loadResultArray();
			// If we have filters, make sure the tables pass the filtering
			if(is_array($all_entries))
			if(count($all_entries))
			foreach( $all_entries as $table_name )
			{
				if($abstract) $table_name = $this->getAbstract($table_name);
				$tables[$table_name] = 'trigger';
			}

		}

		return $tables;
	}

	/**
	 * Gets the CREATE TABLE command for a given table/view
	 * @param string $table_abstract The abstracted name of the entity
	 * @param string $table_name The name of the table
	 * @param string $type The type of the entity to scan. If it's found to differ, the correct type is returned.
	 * @return string The CREATE command, w/out newlines
	 */
	protected function get_create( $table_abstract, $table_name, &$type )
	{
		$sql = "SHOW CREATE TABLE `$table_abstract`";
		$this->setQuery( $sql );
		$temp = $this->loadRowList();
		$table_sql = $temp[0][1];
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

		$table_sql = str_replace( $table_name , $table_abstract, $table_sql );

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

		return $table_sql;
	}

	/**
	 * Returns the abstracted name of a database object
	 * @param string $tableName
	 * @return srting
	 */
	public function getAbstract( $tableName )
	{
		$prefix = $this->getPrefix();

		// Don't return abstract names for non-CMS tables
		if(is_null($prefix)) return $tableName;

		switch( $prefix )
		{
			case '':
				// This is more of a hack; it assumes all tables are CMS tables if the prefix is empty.
				return '#__' . $tableName;
				break;

			default:
				// Normal behaviour for 99% of sites
				$tableAbstract = $tableName;
				if(!empty($prefix)) {
					if( substr($tableName, 0, strlen($prefix)) == $prefix ) {
						$tableAbstract = '#__' . substr($tableName, strlen($prefix));
					} else {
						$tableAbstract = $tableName;
					}
				}

				return $tableAbstract;
				break;
		}
	}

}