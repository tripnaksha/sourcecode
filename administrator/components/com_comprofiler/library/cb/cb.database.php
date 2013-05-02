<?php
/**
* @version $Id: cb.database.php 344 2007-07-25 02:25:39Z beat $
* @package Community Builder
* @subpackage cb.database.php
* @author Beat and various
* @copyright (C) Beat and various, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// no direct access
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


/* MAMBO 4.5.2.3 FIXES + EXTENSIONS: */

/**
*	Corrects bugs in mambo core database class and implements independant CB database layer ! :
*	1) NULL values from SQL tables are not loaded !
*	2) updateOrder method is buggy and does not allow to specify modified row ids to force them into the right position !
*/
class comprofilerDBTable {
	var $_tbl;
	var $_tbl_key;
	/** Database object:
	 * @var CBdatabase */
	var $_db;
	var $_error = '';

	/**
	*	Constructor to set table and key field
	*	Can be overloaded/supplemented by the child class
	*
	*	@param string      $table name of the table in the db schema relating to child class
	*	@param string      $key name of the primary key field in the table
	*	@param CBdatabase  $db  CB Database object
	*/
	function comprofilerDBTable( $table, $key, &$db ) {
		$this->_tbl			=	$table;
		$this->_tbl_key		=	$key;
		$this->_db			=&	$db;
	}
	/**
	*	Binds an array/hash from database to this object
	*
	*	@param  int $oid  optional argument, if not specifed then the value of current key is used
	*	@return mixed     any result from the database operation
	*/
	function load( $oid = null ) {
		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = $oid;
		}
		$oid = $this->$k;
		if ($oid === null) {
			return false;
		}
		
		//BB fix : resets default values to all object variables, because NULL SQL fields do not overide existing variables !
		//Note: Prior to PHP 4.2.0, Uninitialized class variables will not be reported by get_class_vars().
		$class_vars = get_class_vars(get_class($this));
		foreach ($class_vars as $name => $value) {
			if ( ($name != $k) and ($name != "_db") and ($name != "_tbl") and ($name != "_tbl_key") and ( substr( $name, 0 , 10 ) != "_history__" ) ) {
				$this->$name = $value;
			}
		}
		$this->reset();
		//end of BB fix.

		$query	=	"SELECT *"
				.	"\n FROM " . $this->_db->NameQuote( $this->_tbl )
				.	"\n WHERE " . $this->_db->NameQuote( $this->_tbl_key ) . " = " . $this->_db->Quote( $oid )
		;
		$this->_db->setQuery( $query );
		
		return $this->_db->loadObject( $this );
	}
	/**
	* If table key (id) is NULL : inserts a new row
	* otherwise updates existing row in the database table
	*
	* Can be overridden or overloaded by the child class
	*
	* @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	* @return boolean                TRUE if successful otherwise FALSE
	*/
	function store( $updateNulls = false ) {
		$k					=	$this->_tbl_key;

		if ( $this->$k != 0 ) {
			$ok				=	$this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
		} else {
			$ok				=	$this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		}

		if ( ! $ok ) {
			$this->_error	=	strtolower(get_class($this))."::store failed: " . $this->_db->getErrorMsg();
		}
		return $ok;
	}
	/**
	* @param string $where This is expected to be a valid (and safe!) SQL expression
	*/
	function move( $dirn, $where = '', $ordering = 'ordering' ) {
		$k = $this->_tbl_key;

		$sql = "SELECT $this->_tbl_key, $ordering FROM $this->_tbl";

		if ($dirn < 0) {
			$sql .= "\n WHERE $ordering < " . (int) $this->$ordering;
			$sql .= ($where ? "\n	AND $where" : '');
			$sql .= "\n ORDER BY $ordering DESC";
			$sql .= "\n LIMIT 1";
		} else if ($dirn > 0) {
			$sql .= "\n WHERE $ordering > " . (int) $this->$ordering;
			$sql .= ($where ? "\n	AND $where" : '');
			$sql .= "\n ORDER BY $ordering";
			$sql .= "\n LIMIT 1";
		} else {
			$sql .= "\nWHERE $ordering = " . (int) $this->$ordering;
			$sql .= ($where ? "\n AND $where" : '');
			$sql .= "\n ORDER BY $ordering";
			$sql .= "\n LIMIT 1";
		}

		$this->_db->setQuery( $sql );

		$row = null;
		if ($this->_db->loadObject( $row )) {
			$query = "UPDATE $this->_tbl"
			. "\n SET $ordering = " . (int) $row->$ordering
			. "\n WHERE $this->_tbl_key = " . $this->_db->Quote( $this->$k )
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				$err = $this->_db->getErrorMsg();
				die( $err );
			}

			$query = "UPDATE $this->_tbl"
			. "\n SET $ordering = " . (int) $this->$ordering
			. "\n WHERE $this->_tbl_key = " . $this->_db->Quote( $row->$k )
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				$err = $this->_db->getErrorMsg();
				die( $err );
			}

			$this->$ordering = $row->$ordering;
		} else {
			$query = "UPDATE $this->_tbl"
			. "\n SET $ordering = " . (int) $this->$ordering
			. "\n WHERE $this->_tbl_key = " . $this->_db->Quote( $this->$k )
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				$err = $this->_db->getErrorMsg();
				die( $err );
			}
		}
	}

	/**
	* Private utility method for updateOrder() back-called by usort() for comparing orderings
	*/
	function _cmp_obj($a, $b) {
		$k	=	$this->_cbc_cbc_ordering_tmp;
		if ( $a->$k == $b->$k ) {
           return 0;
       }
       return ( $a->$k > $b->$k ) ? +1 : -1;
   }
	/**
	* Compacts the ordering sequence of the selected records
	*
	* @param  string  $where     Additional where query to limit ordering to a particular subset of records
	* @param  array   $cids      of table key ids which should preserve their position (in addition of the negative positions) 
	* @param  string  $ordering  name of ordering column in table
	* @return boolean TRUE success, FALSE failed, with error of database updated.
	*/
	function updateOrder( $where = '' , $cids = null, $ordering = 'ordering' ) {
		$k = $this->_tbl_key;

		if (!array_key_exists( $ordering, get_class_vars( strtolower(get_class( $this )) ) )) {
			$this->_error = "WARNING: ".strtolower(get_class( $this ))." does not support ordering field" . $ordering . ".";
			return false;
		}

		if ($this->_tbl == "#__content_frontpage") {
			$order2 = ", content_id DESC";
		} else {
			$order2 = "";
		}

		$this->_db->setQuery( "SELECT $this->_tbl_key, $ordering FROM $this->_tbl"
		. ($where ? "\nWHERE $where" : '')
		. "\nORDER BY " . $ordering . $order2
		);

		if (!($orders = $this->_db->loadObjectList())) {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}

		$n=count( $orders );
		$iOfThis = null;
		
		if($cids !== null) {
			$cidsOrderings = array();			// determine list of reserved/changed ordering numbers
			for ($i=0; $i < $n; $i++) {
				if (in_array($orders[$i]->$k, $cids)) {
					$cidsOrderings[$orders[$i]->$k] = $orders[$i]->$ordering;
				}
			}

			$j = 1;								// change ordering numbers outside of reserved and negative ordering numbers list
			for ($i=0; $i < $n; $i++) {
				if ($orders[$i]->$k == $this->$k) {
					// place 'this' record in the desired location at the end !
					$iOfThis = $i;
					if ($orders[$i]->$ordering == $j) {
						$j++;
					}
				} else if (in_array($orders[$i]->$k, $cids)) {
					if ($orders[$i]->$ordering == $j) {
						$j++;
					}
				} else {
					if ($orders[$i]->$ordering >= 0) {
						$orders[$i]->$ordering = $j++;
					}
					while (in_array($orders[$i]->$ordering, $cidsOrderings)) {
						$orders[$i]->$ordering = $j++;
					}
				}
			}
		} else {
			$j = 1;
			for ($i=0; $i < $n; $i++) {
				if ($orders[$i]->$k == $this->$k) {
					// place 'this' record in the desired location at the end !
					$iOfThis = $i;
					if ($orders[$i]->$ordering == $j) {
						$j++;
					}
				} else if ($orders[$i]->$ordering != $this->$ordering && $this->$ordering > 0 && $orders[$i]->$ordering >= 0) {
					$orders[$i]->$ordering = $j++;
				} else if ($orders[$i]->$ordering == $this->$ordering && $this->$ordering > 0 && $orders[$i]->$ordering >= 0) {
					if ($orders[$i]->$ordering == $j) {
						$j++;
					}
					$orders[$i]->$ordering = $j++;
				}
			}
		}
		if ($iOfThis !== null) {
			$orders[$iOfThis]->$ordering = min( $this->$ordering, $j );
		}
		// sort entries by ->$ordering:
		$this->_cbc_cbc_ordering_tmp	=	$ordering;
		usort($orders, array( $this, "_cmp_obj" ));
		unset( $this->_cbc_cbc_ordering_tmp );

		// compact ordering:
		$j = 1;
		for ($i=0; $i < $n; $i++) {
			if ($orders[$i]->$ordering >= 0) {
				$orders[$i]->$ordering = $j++;
			}
		}

		for ($i=0; $i < $n; $i++) {
			if (($orders[$i]->$ordering >= 0) or ($orders[$i]->$k == $this->$k)) {
				$this->_db->setQuery( "UPDATE $this->_tbl"
				. "\nSET $ordering='".$orders[$i]->$ordering."' WHERE $k='".$orders[$i]->$k."'"
				);
				$this->_db->query();
			}
		}

		// if we didn't find to reorder the current record, make it last
		if (($iOfThis === null) && ($this->$ordering > 0)) {
			$order = $n+1;
			$this->_db->setQuery( "UPDATE $this->_tbl"
			. "\nSET $ordering='$order' WHERE $k='".$this->$k."'"
			);
			$this->_db->query();
		}
		return true;
	}

	/**
	* Resets public properties
	*
	* @param  mixed  $value  The value to set all properties to, default is null
	*/
	function reset( $value=null ) {
		$keys			=	$this->getPublicProperties();
		foreach ( $keys as $k ) {
			$this->$k	=	$value;
		}
	}
	/**
	* Gets the value of the class variable
	*
	* @param  string  $var  The name of the class variable
	* @return mixed         The value of the class var (or null if no var of that name exists)
	*/
	function get( $var ) {
		if ( isset( $this->$var ) ) {
			return $this->$var;
		} else {
			return null;
		}
	}
	/**
	* Sets the new value of the class variable
	*
	* @param  string  $var     The name of the class variable
	* @param  mixed   $newVal  The new value to assign to the variable
	*/
	function set( $var, $newVal ) {
		$this->$var		=	$newVal;
	}
	/**
	 * Returns an array of public properties
	 *
	 * @return array
	 */
	function getPublicProperties() {
		static $keys			=	null;

		if ( $keys === null ) {
			$keys				=	array();
			foreach ( array_keys( get_class_vars( get_class( $this ) ) ) as $k ) {
				if (substr( $k, 0, 1 ) != '_') {
					$keys[]		=	$k;
				}
			}
		}
		return $keys;
	}
	/**
	* Generic check for whether dependancies exist for this object in the db schema
	* OVERRIDE !
	*
	* @param  int  $oid  key index
	* @return boolean
	*/
	function canDelete( $oid = null ) {
		return true;
	}
	/**
	* Deletes this record (no checks)
	*
	* @param  int      $oid   Key id of row to delete (otherwise it's the one of $this)
	* @return boolean         TRUE if OK, FALSE if error
	*/
	function delete( $oid = null ) {
		$k					=	$this->_tbl_key;
		if ( $oid ) {
			$this->$k		=	(int) $oid;
		}

		$query				=	"DELETE FROM "	. $this->_db->NameQuote( $this->_tbl )
							.	"\n WHERE "		. $this->_db->NameQuote( $this->_tbl_key )
							.	" = "
							.	( is_int( $this->$k ) ? (int) $this->$k : $this->_db->Quote( $this->$k ) )
							;
		$this->_db->setQuery( $query );

		if ($this->_db->query()) {
			return true;
		} else {
			$this->_error	=	$this->_db->getErrorMsg();
			return false;
		}
	}

	/**
	* Tests if item is checked out
	* @param  int      A user id
	* @return boolean
	 */
	function isCheckedOut( $user_id = 0 ) {
		if ( $user_id ) {
			return ( $this->checked_out && ( $this->checked_out != $user_id ) );
		} else {
			return $this->checked_out;
		}
	}

	/**
	 * Checkout object from database
	 *
	 * @param  int  $who
	 * @param  int  $oid
	 * @return unknown
	 */
	function checkout( $who, $oid = null ) {
		if ( ! array_key_exists( 'checked_out', get_class_vars( strtolower( get_class( $this ) ) ) ) ) {
			$this->_error	=	"WARNING: " . strtolower( get_class( $this ) ) . " does not support checkouts.";
			return false;
		}

		$k				=	$this->_tbl_key;
		if ( $oid !== null ) {
			$this->$k	=	$oid;
		}
		$time			=	date( "Y-m-d H:i:s" );
		$query			=	"UPDATE " . $this->_db->NameQuote( $this->_tbl )
						.	"\n SET checked_out = " . (int) $who . ", checked_out_time = " . $this->_db->Quote( $time )
						.	"\n WHERE " . $this->_db->NameQuote( $this->_tbl_key ) . " = " . $this->_db->Quote( $this->$k )
						;
		$this->_db->setQuery( $query );
		return $this->_db->query();
	}

	function checkin( $oid = null ) {
		if ( ! array_key_exists( 'checked_out', get_class_vars( strtolower( get_class( $this ) ) ) ) ) {
			$this->_error	=	"WARNING: " . strtolower( get_class( $this ) ) . " does not support checkins.";
			return false;
		}
		$k				=	$this->_tbl_key;
		if ( $oid !== null ) {
			$this->$k	=	$oid;
		}
		$query			=	"UPDATE " . $this->_db->NameQuote( $this->_tbl )
						.	"\n SET checked_out = 0, checked_out_time = " . $this->_db->Quote( $this->_db->getNullDate() )
						.	"\n WHERE " . $this->_db->NameQuote( $this->_tbl_key ) . " = " . $this->_db->Quote( $this->$k )
						;
		$this->_db->setQuery( $query );
		return $this->_db->query();
	}

	// EXTENSIONS: EXPERIMENTAL IN CB 1.2, NOT PART OF API:
	/**
	* Loads an array of typed objects of a given class (same class as current object by default)
	* which inherit from this class.
	* @access private
	*
	* @param  string  $class          [optional] class name
	* @param  string  $key            [optional] key name in db to use as key of array
	* @param  array   $additionalVars [optional] array of string additional key names to add as vars to object
	* @return array   of objects of the same class (empty array if no objects)
	*/
	function & loadTrueObjects( $class = null, $key = "", $additionalVars = array() ) {
		$objectsArray = array();
		$resultsArray = $this->_db->loadAssocList( $key );
		if ( is_array($resultsArray) ) {
			if ( $class == null ) {
				$class = get_class($this);
			}
			foreach ( $resultsArray as $k => $value ) {
				$objectsArray[$k] =& new $class( $this->_db );
				// mosBindArrayToObject( $value, $objectsArray[$k], null, null, false );
				$objectsArray[$k]->bind( $value, null, null, false );
				foreach ( $additionalVars as $index ) {
					if ( array_key_exists( $index, $value ) ) {
						$objectsArray[$k]->$index = $value[$index];
					}
				}
			}
		}
		return $objectsArray;
	}
	/**
	*	Check values before store method  (override if needed)
	*	@return boolean  TRUE if the object is safe for saving
	*/
	function check( ) {
		return true;
	}
	/**
	* Copy the named array or object content into this object as vars
	* only existing vars of object are filled.
	* When undefined in array, object variables are kept.
	* 
	* WARNING: DOES addslashes / escape BY DEFAULT
	* 
	* Can be overridden or overloaded.
	*
	* @param  array|object  $array         The input array or object
	* @param  string        $ignore        Fields to ignore
	* @param  string        $prefix        Prefix for the array keys
	* @param  boolean       $checkSlashes  TRUE: if magic_quotes are ON, remove slashes (TRUE BY DEFAULT !)
	* @return boolean                      TRUE: ok, FALSE: error on array binding
	*/
	function bind( $array, $ignore='', $prefix = null, $checkSlashes = true  ) {
		if ( is_array( $array ) || is_object( $array ) ) {
			$ignore						=	' ' . $ignore . ' ';
			foreach ( get_object_vars( $this ) as $k => $v ) {
				if( substr( $k, 0, 1 ) != '_' ) {
					if ( strpos( $ignore, ' ' . $k . ' ') === false) {
						$ak				=	$prefix . $k;
						if ( is_array( $array ) && isset( $array[$ak] ) ) {
							$this->$k	=	( ( $checkSlashes && get_magic_quotes_gpc() ) ? cbStripslashes( $array[$ak] ) : $array[$ak] );
						} elseif ( isset( $array->$ak ) ) {
							$this->$k	=	( ( $checkSlashes && get_magic_quotes_gpc() ) ? cbStripslashes( $array->$ak ) : $array->$ak );
						}
					}
				}
			}
		} else {
			$this->_error				=	get_class( $this ) . "::bind failed: not an array.";
			return false;
		}
		return true;
	}
	/**
	 *	@return string Returns the error message
	 */
	function getError() {
		return $this->_error;
	}
}	// end class comprofilerDBTable

/**
*	Provides CMS database independance and
*	Corrects bugs and backwards compatibility issues in mambo core database class ! :
*	1) empty lists return empty arrays and not NULL values !
*	2) gives a coherent interface to CB, independant of various CMS flavors
*/
class CBdatabase {
	/**  Host CMS database class
	* @var database */
	var $_db;
	/* @var string  Holds database prefix replacer (replacing the $prefix (default '#__')). */
	var $_table_prefix		= '';

	/**
	* Database object constructor
	*/
	function CBdatabase( &$_CB_database ) {
		$this->_db					=&	$_CB_database;
		if ( isset( $_CB_database->_table_prefix ) ) {
			$this->_table_prefix	=	$_CB_database->_table_prefix;
		} else {
			global $_CB_framework;
			$this->_table_prefix	=	$_CB_framework->getCfg( 'dbprefix' );
		}
	}
	/**
	* Sets debug level
	* @param int
	*/
	function debug( $level ) {
		$this->_db->debug( $level );
	}
	/**
	* @return int The error number for the most recent query
	*/
	function getErrorNum( ) {
		return $this->_db->getErrorNum();
	}
	/**
	* @return string The error message for the most recent query
	*/
	function getErrorMsg( ) {
		return stripslashes( $this->_db->getErrorMsg() );
	}
	/**
	* @param int      $errorNum  The error number for the most recent query
	*/
	function setErrorNum( $errorNum) {
		$this->_db->_errorNum	=	$errorNum;
	}
	/**
	* @param  string  $errorMsg  The error message for the most recent query
	*/
	function setErrorMsg( $errorMsg ) {
		$this->_db->_errorMsg	=	$errorMsg;
	}
	/**
	* Get a database escaped string
	*
	* @param  string  $text
	* @param  boolean $escapeForLike
	* @return string
	*/
	function getEscaped( $text, $escapeForLike = false ) {
		return $this->_db->getEscaped( $text, $escapeForLike );
	}
	/**
	* Get a quoted database escaped string
	*
	* @param  string  $text
	* @param  boolean $escaped
	* @return string
	*/
	function Quote( $text, $escaped = true ) {
		return '\'' . ( $escaped ? $this->_db->getEscaped( $text ) : $text ) . '\'';
	}
	/**
	* Quote an identifier name (field, table, etc)
	*
	* @param  string  $s  The name
	* @return string      The quoted name
	*/
	function NameQuote( $s ) {
		return '`' . $s . '`';
	}
	/**
	* Returns the zero date/time
	*
	* @param  string  $dateTime  'datetime', 'date', 'time'
	* @return string  Quoted null/zero date string
	*/
	function getNullDate( $dateTime = 'datetime' ) {
		if ( $dateTime == 'datetime' ) {
			return '0000-00-00 00:00:00';
		} elseif ( $dateTime == 'date' ) {
			return '0000-00-00';
		} else {
			return '00:00:00';
		}
	}
	/**
	* Sets the SQL query string for later execution.
	*
	* This function replaces a string identifier $prefix with the
	* string held is the _table_prefix class variable.
	*
	* @param string $sql     The SQL query (casted to (string) )
	* @param int    $offset  The offset to start selection
	* @param int    $limit   The number of results to return
	* @param string $prefix  The common table prefix search for replacement string
	*/
	function setQuery( $sql, $offset = 0, $limit = 0, $prefix='#__' ) {
		$sql				=	(string) $sql;
		if ( in_array( checkJversion( 'product' ), array( 'Mambo', 'Elxis', 'MiaCMS' ) ) ) {
			if ( $offset || $limit ) {
				$sql		.=	" LIMIT ";
				if ( $offset ) {
					$sql	.=	( (int) $offset ) . ', ';
				}
				$sql		.=	( (int) $limit );
			}
			$this->_db->setQuery( $sql, $prefix );
		} else {
			$this->_db->setQuery( $sql, $offset, $limit, $prefix );
		}
	}
	/**
	 * Replace $prefix with $this->_table_prefix
	 *
	 * @param  string  $sql      SQL query
	 * @param  string  $prefix   Common table prefix
	 */
	function replacePrefix( $sql, $prefix='#__' ) {
		return $this->_db->replacePrefix( $sql, $prefix );
	}
	/**
	* @return string The current value of the internal SQL vairable
	*/
	function getQuery( ) {
		return $this->_db->getQuery();
	}
	/**
	* Execute the query
	* 
	* @param  string  the query (optional, it will use the setQuery one otherwise)
	* @return mixed A database resource if successful, FALSE if not.
	*/
	function query( $sql = null ) {
		if ( $sql !== null ) {
			$this->setQuery( $sql );
		}
		return $this->_db->query();
	}
	/**
	 * Executes a series of SQL orders, optionally as a transaction
	 *
	 * @param  boolean $abort_on_error      Aborts on error (true by default)
	 * @param  boolean $p_transaction_safe  Encloses all in a single transaction (false by default)
	 * @return boolean true: success, false: error(s)
	 */
	function query_batch( $abort_on_error = true, $p_transaction_safe = false) {
		return $this->_db->query_batch( $abort_on_error, $p_transaction_safe );
	}
	/** for compatibility only: */
	function queryBatch( $abort_on_error = true, $p_transaction_safe = false) {
		return $this->query_batch( $abort_on_error, $p_transaction_safe );
	}
	/**
	* @return int The number of affected rows in the previous operation
	*/
	function getAffectedRows( ) {
		if ( is_callable( array( $this->_db, 'getAffectedRows' ) ) ) {
			$affected	=	$this->_db->getAffectedRows();
		} elseif ( get_resource_type( $this->_db->_resource ) == 'mysql link' ) {
			$affected	=	mysql_affected_rows( $this->_db->_resource );
		} else {
			$affected	=	mysqli_affected_rows( $this->_db->_resource );
		}
		return $affected;
	}
	/**
	* Returns the number of rows returned from the most recent query.
	* 
	* @return int
	*/
	function getNumRows( $cur = null ) {
		return $this->_db->getNumRows( $cur );
	}
	/**
	 * Explain of SQL
	 * 
	 * @return string
	 */
	function explain( ) {
		return $this->_db->explain();
	}
	/**
	* This method loads the first field of the first row returned by the query.
	*
	* @return The value returned in the query or null if the query failed.
	*/
	function loadResult( ) {
		return $this->_db->loadResult();
	}
	function & _nullToArray( &$resultArray ) {
		if ( $resultArray === null ) {		// mambo strangeness
			$resultArray	=	array();
		}
		return $resultArray;
	}
	/**
	* Load an array of single field results into an array
	*/
	function loadResultArray( $numinarray = 0 ) {
		$resultArray	=	$this->_db->loadResultArray( $numinarray );
		return $this->_nullToArray( $resultArray );
	}
	/**
	* Fetch a result row as an associative array
	*
	* @return array
	*/
	function loadAssoc( ) {
		if ( is_callable( array( $this->_db, 'loadAssoc' ) ) ) {
			return $this->_db->loadAssoc( );
		} else {
			// new independant efficient implementation:
			if ( ! ( $cur = $this->query() ) ) {
				$result	=	null;
			} else {
				$result		=	$this->m_fetch_assoc( $cur );
				if ( ! $result ) {
					$result	=	null;
				}
				$this->m_free_result( $cur );
			}
			return $result;
		}
	}
	/**
	* Load a assoc list of database rows
	* 
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	*/
	function loadAssocList( $key = null ) {
		if ( ( $key == '' ) || ( checkJversion() >= 0 ) ) {
			$resultArray				=	$this->_db->loadAssocList( $key );
			return $this->_nullToArray( $resultArray );
		} else {
			// mambo 4.5.2 - 4.6.2 has a bug in key:
			if ( ! ( $cur = $this->query() ) ) {
				return null;
			}
			$array						=	array();
			while ( is_array( $row = $this->m_fetch_assoc( $cur ) ) ) {
				if ( $key ) {
					$array[$row[$key]]	=	$row;		//  $row->key is not an object, but an array
				} else {
					$array[]			=	$row;
				}
			}
			$this->m_free_result( $cur );
			return $array;
		}
	}
	/**
	* This global function loads the first row of a query into an object
	*
	* If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
	* If <var>object</var> has a value of null, then all of the returned query fields returned in the object.
	* @param  stdClass  $object
	* @return boolean            Success
	*/
	function loadObject( &$object ) {
		// return $this->_db->loadObject( $object );
		$cur			=	$this->query();
		if ( ! $cur ) {
			return false;
		}
		if ( $object != null ) {
			$array			=	$this->m_fetch_assoc( $cur );
			$this->m_free_result( $cur );
			if ( is_array( $array ) ) {

				foreach ( get_object_vars( $object ) as $k => $v) {
					if( substr( $k, 0, 1 ) != '_' ) {
						if ( isset( $array[$k] ) ) {
							$object->$k		=	$array[$k];
						}
					}
				}
				return true;
			}
		} else {
			$object		=	$this->m_fetch_object( $cur );
			$this->m_free_result( $cur );
			if ( is_object( $object ) ) {
				return true;
			} else {
				$object = null;
			}
		}
		return false;
	}
	/**
	* Load a list of database objects
	* If $key is not empty then the returned array is indexed by the value
	* the database key.  Returns NULL if the query fails.
	*
	* @param  string|array  $key          The field name of a primary key
	* @param  string|null   $className    The name of the class to instantiate, set the properties of and return. If not specified, a stdClass object is returned
	* @param  array|null    $ctor_params  An optional array of parameters to pass to the constructor for class_name objects
	* @return array                       If $key is empty as sequential list of returned records.
	*/
	function loadObjectList( $key = null, $className = null, $ctor_params = null ) {
		if ( ! ( $cur = $this->query() ) ) {
			return null;
		}
		$array													=	array();
		if ( ! $key ) {
			while ( is_object( $row = $this->m_fetch_object( $cur, $className, $ctor_params ) ) ) {
				$array[]										=	$row;
			}
		} elseif ( is_array( $key ) ) {
			if ( count( $key == 2 ) ) {
				list( $ka, $kb )								=	$key;
				while ( is_object( $row = $this->m_fetch_object( $cur, $className, $ctor_params ) ) ) {
					$array[$row->$ka][$row->$kb]				=	$row;
				}
			} elseif ( count( $key == 3 ) ) {
				list( $ka, $kb, $kc )							=	$key;
				while ( is_object( $row = $this->m_fetch_object( $cur, $className, $ctor_params ) ) ) {
					$array[$row->$ka][$row->$kb][$row->$kc]	=	$row;
				}
			}
		} else {
			while ( is_object( $row = $this->m_fetch_object( $cur, $className, $ctor_params ) ) ) {
				$array[$row->$key]								=	$row;
			}
		}
		$this->m_free_result( $cur );
		return $array;
	}
	/**
	* @return The first row of the query.
	*/
	function loadRow( ) {
		return $this->_db->loadRow();
	}
	/**
	* Load a list of database rows (numeric column indexing)
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	* If <var>key</var> is not empty then the returned array is indexed by the value
	* the database key.  Returns <var>null</var> if the query fails.
	*/
	function loadRowList( $key = null ) {
		$resultArray	=	$this->_db->loadRowList( $key );
		return $this->_nullToArray( $resultArray );
	}
	/**
	* Insert an object into database
	*
	* @param string   $table This is expected to be a valid (and safe!) table name
	* @param stdClass $object
	* @param string   $keyName
	* @param boolean  $verbose
	* @param boolean  TRUE if insert succeeded, FALSE when error
	*/
	function insertObject( $table, &$object, $keyName = NULL, $verbose=false ) {
		return $this->_db->insertObject( $table, $object, $keyName, $verbose );
	}
	/**
	* Updates an object into a database
	*
	* @param  string   $table        This is expected to be a valid (and safe!) table name
	* @param  stdClass $object
	* @param  string   $keyName
	* @param  boolean  $updateNulls
	* @return mixed    A database resource if successful, FALSE if not.
	*/
	function updateObject( $table, &$object, $keyName, $updateNulls=true ) {
		// return $this->_db->updateObject( $table, $object, $keyName, $updateNulls );
		$fmtsql = 'UPDATE ' . $this->NameQuote( $table ) . ' SET %s WHERE %s';
		$tmp = array();
		foreach (get_object_vars( $object ) as $k => $v) {
			if( is_array($v) or is_object($v) or $k[0] == '_' ) { // internal or NA field
				continue;
			}
			if( $k == $keyName ) { // PK not to be updated
				$where = $keyName . '=' . $this->Quote( $v );
				continue;
			}
			if( $v === NULL ) {
				if ( ! $updateNulls ) {
					continue;
				}
				$val = 'NULL';			// this case was missing in Mambo
		//	} elseif( $v == '' ) {		// this Mambo&Joomla error was triggering '' for int 0 !
		//		$val = "''";
			} elseif( is_int( $v ) ) {
				$val = (int) $v;
			} else {
				$val = $this->Quote( $v );
			}
			$tmp[] = $this->NameQuote( $k ) . '=' . $val;
		}
		$this->setQuery( sprintf( $fmtsql, implode( ",", $tmp ) , $where ) );
		return $this->query();
	}

	/**
	* Returns the formatted standard error message of SQL
	* @param  boolean $showSQL  If TRUE, displays the last SQL statement sent to the database
	* @return string  A standised error message
	*/
	function stderr( $showSQL = false ) {
		return $this->_db->stderr( $showSQL );
	}
	/**
	* Returns the insert_id() from Mysql
	*
	* @return int
	*/
	function insertid( ) {
		return $this->_db->insertid();
	}
	/**
	* Returns the version of MySQL
	*
	* @return string
	*/
	function getVersion( ) {
		return $this->_db->getVersion();
	}
	/**
	* Returns a list of tables, with the prefix changed if needed.
	*
	* @param  string  $tableName  Name of table (SQL LIKE pattern), null: all tables
	* @param  string  $prefix     Prefix to change back
	* @return array               A list of all the tables in the database
	*/
	function getTableList( $tableName = null, $prefix = '#__' ) {
		$this->setQuery( 'SHOW TABLES' . ( $tableName ? ' LIKE ' . $this->Quote( $tableName ) : '' ) );
		$tables							=	$this->loadResultArray();
		if ( $prefix ) {
			foreach ( $tables as $k => $n ) {
				$tables[$k]				=	preg_replace( '/^(' . $this->_table_prefix . ')/', $prefix, $n );
			}
		}
		return $tables;
	}
	/**
	* Returns the status of all tables, with the prefix changed if needed.
	*
	* @param  string  $tableName  Name of table (SQL LIKE pattern), null: all tables
	* @param  string  $prefix     Prefix to change back
	* @return array               A list of all the table statuses in the database
	*/
	function getTableStatus( $tableName = null, $prefix = '#__' ) {
		$this->setQuery( 'SHOW TABLE STATUS' . ( $tableName ? ' LIKE ' . $this->Quote( $this->replacePrefix( $tableName, $prefix ) ) : '' ) );
		$tables							=	$this->loadObjectList();
		if ( $prefix ) {
			foreach ( $tables as $k => $n ) {
				$tables[$k]->Name		=	preg_replace( '/^(' . $this->_table_prefix . ')/', $prefix, $n->Name );
			}
		}
		return $tables;
	}
	/**
	 * @param array A list of valid (and safe!) table names
	 * @return array A list the create SQL for the tables
	 */
	function getTableCreate( $tables ) {
		$createQueries					=	array();
		foreach ( $tables as $tableName ) {
			$this->setQuery( 'SHOW CREATE table ' . $this->NameQuote( $tableName ) );
			$this->query();
			$createQueries[$tableName]	=	$this->loadResultArray( 1 );
		}
		return $createQueries;
	}
	/**
	 * Gets the fields as in DESCRIBE of MySQL
	 *
	 * @param  array|string  $tables    A (list of) table names
	 * @param  boolean       $onlyType  TRUE: only type without size, FALSE: full DESCRIBE MySql
	 * @return array                    EITHER: array( tablename => array( fieldname => fieldtype ) ) or of => fieldDESCRIBE
	 */
	function getTableFields( $tables, $onlyType = true ) {
		$result							=	array();
		$tables							=	(array) $tables;
		foreach ( $tables as $tbl ) {
			$this->setQuery( 'SHOW COLUMNS FROM ' . $this->NameQuote( $tbl ) );
			$result[$tbl]				=	$this->loadObjectList( 'Field' );
			if ( is_array( $result[$tbl] ) && $onlyType ) {
				foreach ( $result[$tbl] as $k => $fld ) {
					$result[$tbl][$k]	=	preg_replace( '/[(0-9)]/','', $fld->Type );
				}
			}
		}
		return $result;
	}
	/**
	 * Gets the index of the table
	 *
	 * @param  array|string  $tables    A (list of) table names
	 * @return array                    As
	 */
	function getTableIndex( $table, $prefix = '#__' ) {
		$this->setQuery( 'SHOW INDEX FROM ' . $this->NameQuote( $table ) );
		$indexes						=	$this->loadObjectList();
		if ( $prefix ) {
			foreach ( $indexes as $k => $n ) {
				$indexes[$k]->Table		=	preg_replace( '/^(' . $this->_table_prefix . ')/', $prefix, $n->Table );
			}
		}
		return $indexes;
	}
	/**
	* Fudge method for ADOdb compatibility
	*/
	function GenID( $foo1=null, $foo2=null ) {
		return '0';
	}
	/**
	 * Checks if database's collation is case-INsensitive
	 * WARNING: individual table's fields might have a different collation
	 *
	 * @return boolean  TRUE if case INsensitive
	 */
	function isDbCollationCaseInsensitive( ) {
		static $result = null;

		if ( $result === null ) {
			$query = "SELECT IF('a'='A', 1, 0);";
			$this->setQuery( $query );
			$result		=	$this->loadResult();
		}
		return ( $result == 1 );
	}
	/**
	 * mysql/mysqli_fetch_assoc function
	 *
	 * @param  mysql_result  $cur  The result resource that is being evaluated. This result comes from a call to mysql_query().
	 * @return array|boolean|null         False OR null if no more
	 */
	function m_fetch_assoc( &$cur ) {
		if ( is_object( $cur ) && get_class( $cur ) == 'mysqli_result' ) {
			return mysqli_fetch_assoc( $cur );
		} else {
			return mysql_fetch_assoc( $cur );
		}
	}
	/**
	 * mysql / mysqli fetch_object
	 *
	 * @param  mysql_result  $cur          The result resource that is being evaluated. This result comes from a call to mysql_query()
	 * @param  string|null   $className    The name of the class to instantiate, set the properties of and return. If not specified, a stdClass object is returned
	 * @param  array|null    $ctor_params  An optional array of parameters to pass to the constructor for class_name objects
	 * @return object|boolean|null         False OR null if no more
	 */
	function m_fetch_object( &$cur, $className = null, $ctor_params = null ) {
		if ( is_object( $cur ) && ( get_class( $cur ) == 'mysqli_result' ) ) {
			// MySqli is PHP 5 only:
			if ( $className === null ) {
				return mysqli_fetch_object( $cur );
			} else {
				return mysqli_fetch_object( $cur, $className, $ctor_params );
			}
		} else {
			// MySql:
			if ( $className === null ) {
				return mysql_fetch_object( $cur );
			} elseif ( version_compare( phpversion(), '5.0.0', '>=' ) ) {
				return mysql_fetch_object( $cur, $className, $ctor_params );
			} else {
				$objArr			=	$this->m_fetch_assoc( $cur );
				if ( ! is_array( $objArr ) ) {
					return $objArr;
				}
				$obj			=	$this->_m_new_class( $className, $ctor_params );
				foreach ( $objArr as $k => $v) {
					$obj->$k	=	$v;
				}
				return $obj;
			}
		}
	}
	/**
	 * Emulation of a Reflection class for PHP 4 only
	 *
	 * In PHP 5.1.3+ that could be made as:
	 * $reflectionObj = new ReflectionClass($className);
	 * $obj = $reflectionObj->newInstanceArgs($cp); 
	 * But as mysql_fetch_object handles it already in PHP 5.0, it's not needed.
	 *
	 * @param  string  $className
	 * @param  array   $cp
	 * @return object
	 */
	function _m_new_class( $className, &$cp ) {
		if ( $cp === null ) {
			return new $className();
		} elseif ( is_array( $cp ) ) {
			switch ( count( $cp ) ) {
				case 0:
					return new $className();
					break;
				case 1:
					return new $className( $cp[0] );
					break;
				case 2:
					return new $className( $cp[0], $cp[1] );
					break;
				case 3:
					return new $className( $cp[0], $cp[1], $cp[2] );
					break;
				case 4:
					return new $className( $cp[0], $cp[1], $cp[2], $cp[3] );
					break;
				case 5:
					return new $className( $cp[0], $cp[1], $cp[2], $cp[3], $cp[4] );
					break;
				case 6:
					return new $className( $cp[0], $cp[1], $cp[2], $cp[3], $cp[4], $cp[5] );
					break;
				case 7:
					return new $className( $cp[0], $cp[1], $cp[2], $cp[3], $cp[4], $cp[5], $cp[6] );
					break;
				case 8:
					return new $className( $cp[0], $cp[1], $cp[2], $cp[3], $cp[4], $cp[5], $cp[6], $cp[7] );
					break;
				case 9:
					return new $className( $cp[0], $cp[1], $cp[2], $cp[3], $cp[4], $cp[5], $cp[6], $cp[7], $cp[8] );
					break;
				case 10:
					return new $className( $cp[0], $cp[1], $cp[2], $cp[3], $cp[4], $cp[5], $cp[6], $cp[7], $cp[8], $cp[9] );
					break;

				default:
					trigger_error( 'CBdatabase::m_fetch_object: constructor parameters count to large', E_USER_ERROR );
					exit;
					break;
			}
		}
	}
	function m_free_result( &$cur ) {
		if ( is_object( $cur ) && get_class( $cur ) == 'mysqli_result' ) {
			mysqli_free_result( $cur );
		} else {
			mysql_free_result( $cur );
		}
	}
}	// class CBdatabase

// ----- NO MORE CLASSES OR FUNCTIONS PASSED THIS POINT -----
// Post class declaration initialisations
// some version of PHP don't allow the instantiation of classes
// before they are defined

global $_CB_database, $_CB_framework;
/** @global CBdatabase $_CB_Database */
$_CB_database	=	new CBdatabase( $_CB_framework->_cmsDatabase );

?>
