<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2010 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: mysql.php 151 2010-06-02 17:27:58Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * MySQL classic driver for Akeeba Engine
 */
class AEDriverMysql extends AEAbstractDriver
{
	/**
	 * Database object constructor
	 * @param	array	List of options used to configure the connection
	 */
	public function __construct( $options )
	{
		// Init
		$this->nameQuote = '`';

		$host		= array_key_exists('host', $options)	? $options['host']		: 'localhost';
		$port		= array_key_exists('port', $options)	? $options['port']		: '';
		$user		= array_key_exists('user', $options)	? $options['user']		: '';
		$password	= array_key_exists('password',$options)	? $options['password']	: '';
		$database	= array_key_exists('database',$options)	? $options['database']	: '';
		$prefix		= array_key_exists('prefix', $options)	? $options['prefix']	: '';
		$select		= array_key_exists('select', $options)	? $options['select']	: true;

		if(!empty($port)) $host .= ':'.$port;

		// finalize initialization
		parent::__construct($options);

		// Open the connection
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->database = $database;
		$this->open();
	}

	public function open()
	{
		// perform a number of fatality checks, then return gracefully
		if (!function_exists( 'mysql_connect' )) {
			$this->errorNum = 1;
			$this->errorMsg = 'The MySQL adapter "mysql" is not available.';
			return;
		}

		if (!($this->resource = @mysql_connect( $this->host, $this->user, $this->password, true ))) {
			$this->errorNum = 2;
			$this->errorMsg = 'Could not connect to MySQL';
			return;
		}

		parent::open();

		$this->select($this->database);
	}

	public function close()
	{
		$return = false;
		if (is_resource($this->cursor)) {
			mysql_free_result($this->cursor);
		}
		if (is_resource($this->resource)) {
			$return = mysql_close($this->resource);
		}
		$this->resource = null;
		return $return;
	}

	/**
	 * Select a database for use
	 * @param	string $database
	 * @return	boolean True if the database has been successfully selected
	 */
	public function select($database)
	{
		if ( ! $database )
		{
			return false;
		}

		if ( !mysql_select_db( $database, $this->resource )) {
			$this->errorNum = 3;
			$this->errorMsg = 'Could not connect to database';
			return false;
		}

		// if running mysql 5, set sql-mode to mysql40 - thereby circumventing strict mode problems
		$verParts = explode( '.', $this->getVersion() );
		if ( $verParts[0] == 5 ) {
			$this->setQuery( "SET sql_mode = 'HIGH_NOT_PRECEDENCE'" );
			$this->query();
			$this->resetErrors();
		}

		return true;
	}

	/**
	 * Determines UTF support
	 * @return bool
	 */
	public function hasUTF()
	{
		$verParts = explode( '.', $this->getVersion() );
		return ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int)$verParts[2] >= 2));
	}

	/**
	 * Custom settings for UTF support
	 */
	public function setUTF()
	{
		mysql_query( "SET NAMES 'utf8'", $this->resource );
	}

	/**
	 * Get a database escaped string
	 * @param	string	The string to be escaped
	 * @param	bool	Optional parameter to provide extra escaping
	 * @return	string
	 */
	public function getEscaped( $text, $extra = false )
	{
		$result = @mysql_real_escape_string( $text, $this->resource );
		if ($extra) {
			$result = addcslashes( $result, '%_' );
		}
		return $result;
	}

	/**
	 * Execute the query
	 * @return mixed A database resource if successful, FALSE if not.
	 */
	public function query()
	{
		if (!is_resource($this->resource)) {
			return false;
		}

		if(is_resource($this->cursor)) @mysql_free_result($this->cursor);

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->sql;
		if ($this->limit > 0 || $this->offset > 0) {
			$sql .= ' LIMIT '.$this->offset.', '.$this->limit;
		}

		$this->errorNum = 0;
		$this->errorMsg = '';
		$this->cursor = mysql_query( $sql, $this->resource );

		if (!$this->cursor)
		{
			$this->errorNum = mysql_errno( $this->resource );
			$this->errorMsg = mysql_error( $this->resource )." SQL=$sql";

			return false;
		}
		return $this->cursor;
	}

	/**
	 * This method loads the first field of the first row returned by the query.
	 * @return mixed The value returned in the query or null if the query failed.
	 */
	public function loadResult()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysql_fetch_row( $cur )) {
			$ret = $row[0];
		}
		mysql_free_result( $cur );
		return $ret;
	}

	/**
	 * Load an array of single field results into an array
	 * @return mixed An array, or null if query failed
	 */
	public function loadResultArray($numinarray = 0)
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_row( $cur )) {
			$array[] = $row[$numinarray];
		}
		mysql_free_result( $cur );
		return $array;
	}

	/**
	 * Fetch a result row as an associative array
	 * @param bool $free_cursor If true, frees the cursor after returning the result
	 * @return array An associative array, null if query failed or false on end of data
	 */
	public function loadAssoc($free_cursor = false)
	{
		if( !is_resource($this->cursor) )
		{
			if (!($this->cursor = $this->query())) {
				return null;
			}
		}
		$ret = null;
		if ($array = mysql_fetch_assoc( $this->cursor )) {
			$ret = $array;
		}
		else
		{
			$ret = false;
			$free_cursor = true;
		}
		if( $free_cursor ) {
			mysql_free_result( $this->cursor );
		}
		return $ret;
	}

	/**
	 * Load a associactive list of database rows
	 * @param string The field name of a primary key
	 * @return array If key is empty as sequential list of returned records.
	 */
	public function loadAssocList( $key=null )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_assoc( $cur )) {
			if ($key) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}

	/**
	 * Load a list of database rows (numeric column indexing)
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 * @param string The field name of a primary key
	 * @return array
	 */
	public function loadRowList( $key=null )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_row( $cur )) {
			if ($key !== null) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}

	/**
	 * Get the version of the database connector
	 * @return string The database server's version number
	 */
	public function getVersion()
	{
		return mysql_get_server_info( $this->resource );
	}

	/**
	 * Returns the last INSERT auto_increase column's value
	 * @return int
	 */
	public function insertid()
	{
		return mysql_insert_id( $this->resource );
	}

}