<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: joomla.php 158 2010-06-10 08:46:49Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

class AEDriverPlatformJoomla extends AEAbstractDriver
{
	/** @var AEAbstractDriver The real database connection object */
	private $dbo;

	/**
	 * Database object constructor
	 * @param	array	List of options used to configure the connection
	 */
	public function __construct( $options )
	{
		// Get best matching Akeeba Backup driver instance
		if(class_exists('JFactory')) {
			$this->dbo = JFactory::getDBO();
		} else {
			$driver = AEPlatform::get_default_database_driver(false);
			$this->dbo = new $driver($options);
		}

		// Propagate errors
		$this->propagateFromObject($this->dbo);

		$this->nameQuote = '`';
		parent::__construct( $options );

		$this->database = $options['database'];
	}

	public function close()
	{
		if(method_exists($this->dbo, 'close')) $this->dbo->close();
	}

	public function open()
	{
		if(method_exists($this->dbo, 'open')) $this->dbo->open();
		$this->dbo->select($this->database);
	}

	/**
	 * Select a database for use
	 * @param	string $database
	 * @return	boolean True if the database has been successfully selected
	 */
	public function select($database)
	{
		return $this->dbo->select($database);
	}

	/**
	 * Determines UTF support
	 * @return bool
	 */
	public function hasUTF()
	{
		return $this->dbo->hasUTF();
	}

	/**
	 * Custom settings for UTF support
	 */
	public function setUTF()
	{
		return $this->dbo->setUTF();
	}

	/**
	 * Get a database escaped string
	 * @param	string	The string to be escaped
	 * @param	bool	Optional parameter to provide extra escaping
	 * @return	string
	 */
	public function getEscaped( $text, $extra = false )
	{
		return $this->dbo->getEscaped($text, $extra);
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
		$this->cursor = null;
		$this->dbo->setQuery($sql, $offset, $limit, $prefix);
	}

	public function getQuery()
	{
		return $this->dbo->getQuery();
	}

	/**
	 * Execute the query
	 * @return mixed A database resource if successful, FALSE if not.
	 */
	public function query()
	{
		$ret = $this->dbo->query();
		$this->propagateFromObject($this->dbo);
		return $ret;
	}

	/**
	 * This method loads the first field of the first row returned by the query.
	 * @return mixed The value returned in the query or null if the query failed.
	 */
	public function loadResult()
	{
		return $this->dbo->loadResult();
	}

	/**
	 * Load an array of single field results into an array
	 * @return mixed An array, or null if query failed
	 */
	public function loadResultArray($numinarray = 0)
	{
		return $this->dbo->loadResultArray();
	}

	/**
	 * Fetch a result row as an associative array
	 * @param bool $free_cursor If true, frees the cursor after returning the result
	 * @return array An associative array, null if query failed or false on end of data
	 */
	public function loadAssoc($free_cursor = false)
	{
		if($free_cursor || is_subclass_of($this->dbo, 'AEAbstractDriver') )
		{
			return $this->dbo->loadAssoc($free_cursor);
		}
		else
		{
			// Implement loadAssoc for JDatabase classes, in a Joomla! 1.6 / PHP5 manner
			if (is_null($this->cursor)) {
				if ( !($this->cursor = $this->query()) ) {
					$ret = null;
					return $ret;
				}
			}
			$ret = null;
			$isMySQLi = $this->is_mysqli();
			if($isMySQLi)
			{
				if ($array = @mysqli_fetch_assoc( $this->cursor )) {
					$ret = $array;
				}
				else
				{
					$ret = false;
					$free_cursor = true;
				}
			}
			else
			{
				if ($array = @mysql_fetch_assoc( $this->cursor )) {
					$ret = $array;
				}
				else
				{
					$ret = false;
					$free_cursor = true;
				}
			}
			if( $free_cursor ) {
				if($isMySQLi)
				{
					@mysqli_free_result( $this->cursor );
					$this->cursor = null;
				}
				else
				{
					@mysql_free_result( $this->cursor );
					$this->cursor = null;
				}
			}
			return $ret;
		}
	}

	/**
	 * Load a associactive list of database rows
	 * @param string The field name of a primary key
	 * @return array If key is empty as sequential list of returned records.
	 */
	public function loadAssocList( $key=null )
	{
		return $this->dbo->loadAssocList($key);
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
		return $this->dbo->loadRowList($key);
	}

	/**
	 * Get the version of the database connector
	 * @return string The database server's version number
	 */
	public function getVersion()
	{
		return $this->dbo->getVersion();
	}

	private function is_mysqli()
	{
		$isMySQLi = ($this->dbo->name == 'mysqli');
		return $isMySQLi;
	}

	/**
	 * Returns the last INSERT auto_increase column's value
	 * @return int
	 */
	public function insertid()
	{
		return $this->dbo->insertid();
	}

}