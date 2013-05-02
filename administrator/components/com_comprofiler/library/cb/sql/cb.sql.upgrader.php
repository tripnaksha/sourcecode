<?php
/**
* @version $Id: cb.sql.upgrader.php 444 2008-03-05 02:25:39Z beat $
* @package Community Builder
* @subpackage cb.sql.upgrader.php
* @author Beat
* @copyright (C) 2007 Beat and Lightning MultiCom SA, 1009 Pully, Switzerland
* @license Lightning Proprietary. See licence. Allowed for free use within CB and for CB plugins.
*/

// no direct access
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

// cbimport('cb.xml.simplexml'); needed to use this.

/**
 * CB SQL versioning / upgrading functions:
 * 
 * WARNING:
 * This new library is experimental work in progress and should not be used directly by plugins and 3pds,
 * as it is subject to change without notice, and is not part of current CB API.
 * 
 * @access private
 * 
 */
class CBSQLupgrader {
	/** Database
	 * @var CBdatabase */
	var $_db				=	null;
	var $_silentTestLogs	=	true;
	var $_logs				=	array();
	var $_errors			=	array();
	var $_logsIndex			=	0;
	var $_dryRun			=	false;
	/**
	 * Constructor
	 *
	 * @param  CBdatabase     $db
	 * @param  boolean        $silentTestLogs  TRUE: Silent on successful tests
	 * @return CBSQLupgrader
	 */
	function CBSQLupgrader( &$db, $silentTestLogs = true ) {
		$this->_db								=&	$db;
		$this->_silentTestLogs					=	$silentTestLogs;
	}
	/**
	 * Sets if SQL tables changing queries should not be run (for a dryrun)
	 *
	 * @param  boolean  $dryRun  FALSE (default): tables are changed, TRUE: Dryrunning
	 */
	function setDryRun( $dryRun ) {
		$this->_dryRun							=	$dryRun;
	}
	/**
	 * LOGS OF ACTIONS AND OF ERRORS:
	 */
	/**
	 * Records error with details (details here is SQL query)
	 * @access private
	 *
	 * @param  string  $error
	 * @param  string  $info
	 */
	function _setError( $error, $info = null) {
		$this->_errors[++$this->_logsIndex]		=	array( $error, $info );
	}
	/**
	 * Returns all errors logged
	 *
	 * @param  string|boolean  $implode         False: returns full array, if string: imploding string
	 * @param  string|boolean  $detailsImplode  False: no details, otherwise imploding string
	 * @return string|array
	 */
	function getErrors( $implode = "\n", $detailsImplode = false ) {
		if ( $implode === false) {
			return $this->_errors;
		} else {
			$errors								=	array();
			if ( $detailsImplode ) {
				foreach ( $this->_errors as $errInfo ) {
					$errors[]					=	implode( $detailsImplode, $errInfo );
				}
			} else {
				foreach ( $this->_errors as $errInfo ) {
					$errors[]					=	$errInfo[0];
				}
			}
			return implode( $implode, $errors );
		}
	}
	/**
	 * Records logs with details (details here are SQL queries ( ";\n"-separated )
	 * @access private
	 *
	 * @param  string  $log
	 * @param  string  $info
	 * @param  string  $type  'ok': successful check, 'change': successful change
	 */
	function _setLog( $log, $info = null, $type ) {
		if ( ( $type != 'ok' ) || ! $this->_silentTestLogs ) {
			$this->_logs[++$this->_logsIndex]	=	array( $log, $info );
		}
	}
	/**
	 * Returns all logs logged
	 *
	 * @param  string|boolean  $implode         False: returns full array, if string: imploding string
	 * @param  string|boolean  $detailsImplode  False: no details, otherwise imploding string
	 * @return string|array
	 */
	function getLogs( $implode = "\n", $detailsImplode = false ) {
		if ( $implode === false) {
			return $this->_logs;
		} else {
			$logs								=	array();
			if ( $detailsImplode ) {
				foreach ( $this->_logs as $logInfo ) {
					$logs[]						=	implode( $detailsImplode, $logInfo );
				}
			} else {
				foreach ( $this->_logs as $logInfo ) {
					$logs[]						=	$logInfo[0];
				}
			}
			return implode( $implode, $logs );
		}
	}

	/**
	 * SQL ACCESS FUNCTIONS:
	 */

	/**
	 * Checks if a given table exists in the database
	 * @access private
	 *
	 * @param  string  $tableName  Name of table
	 * @return boolean
	 */
	function checkTableExists( $tableName ) {
		$allTables								=	$this->_db->getTableList();
		return ( in_array( $tableName, $allTables ) );
	}
	/**
	 * Checks if table exists in database and returns all fields of table.
	 * Otherwise returns boolean false.
	 * @access private
	 *
	 * @param  string  $tableName  Name of table
	 * @return array|boolean       Array of SHOW COLUMNS FROM ... in SQL or boolean FALSE
	 */
	function getAllTableColumns( $tableName ) {
		if ( $this->checkTableExists( $tableName ) ) {
			$fields								=	$this->_db->getTableFields( $tableName, false );
			if ( isset( $fields[$tableName] ) ) {
				return $fields[$tableName];
			}
		}
		return false;
	}
	/**
	 * Returns all indexes of the table
	 * @access private
	 *
	 * @param  string  $tableName  Name of table
	 * @return array               Array of SHOW INDEX FROM ... in SQL
	 */
	function getAllTableIndexes( $tableName ) {
		$sortedIndex							=	array();
		$idx									=	$this->_db->getTableIndex( $tableName );
		if ( is_array( $idx ) ) {
			foreach ( $idx as $n ) {
				$sortedIndex[$n->Key_name][$n->Seq_in_index]	=	array(	'name'		=>	$n->Column_name,
																			'size'		=>	$n->Sub_part,
																			'ordering'	=>	$n->Collation,

																			'type'		=>	( $n->Key_name == 'PRIMARY' ? 'primary' : ( $n->Non_unique == 0 ? 'unique' : '' ) ),
																			'using'		=>	( array_key_exists( 'Index_type', $n ) ? strtolower( $n->Index_type ) : ( $n->Comment == 'FULLTEXT' ? 'fulltext' : '' ) )	// mysql <4.0.2 support
																		 );
			}
		}
		return $sortedIndex;
	}

	/**
	 * COLUMNS CHECKS:
	 */

	/**
	 * Checks if a column exists and has the type of the parameters below:
	 * @access private
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  array               $allColumns       From $this->getAllTableColumns( $table )
	 * @param  CBSimpleXMLElement  $column           Column to check
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $change           TRUE: only true/false check type, FALSE: logs success and if mismatch, error details
	 * @return boolean             TRUE: identical (no check on indexes), FALSE: errors are in $this->getErrors()
	 */
	function checkColumnExistsType( $tableName, &$allColumns, &$column, $colNamePrefix, $change ) {
		$colName								=	$this->_prefixedName( $column, $colNamePrefix );
		if ( isset( $allColumns[$colName] ) ) {
			if ( ! cbStartOfStringMatch( $column->attributes( 'type' ), 'sql:' ) ) {
				$this->_setError( sprintf( 'Table %s Column %s type is %s instead of being prefixed by "sql:"', $tableName, $colName, $column->attributes( 'type' ) ) );
				return false;
			}
			if ( $column->attributes( 'strict' ) === 'false' ) {
				$this->_setLog( sprintf( 'Table %s Column %s exists but is not of strict type, so not checked.', $tableName, $colName ), null, 'ok' );
				return true;
			}
			$type								=	substr( $column->attributes( 'type' ), 4 );
			$shouldbeType						=	$type . ( $column->attributes( 'unsigned' ) === 'true' ? ' unsigned' : '' );
			if ( $allColumns[$colName]->Type !== $shouldbeType ) {
				if ( $change === false ) {
					$this->_setError( sprintf( 'Table %s Column %s type is %s instead of %s', $tableName, $colName, $allColumns[$colName]->Type, $shouldbeType ) );
				}
				return false;
			}
			if ( ( $column->attributes( 'null' ) === 'true' ) !== ( $allColumns[$colName]->Null == 'YES' ) ) {		//if ( $column->attributes( 'null' ) !== null ): no attribute NULL means NOT NULL
				if ( $change === false ) {
					$this->_setError( sprintf( 'Table %s Column %s NULL attribute is %s instead of %s', $tableName, $colName, $allColumns[$colName]->Null, ( $column->attributes( 'null' ) === 'true' ? 'YES' : 'NO') ) );
				}
				return false;
			}

			// BLOB and TEXT columns cannot have DEFAULT values. http://dev.mysql.com/doc/refman/5.0/en/blob.html
			$defaultValuePossible				=	! in_array( $type, array( 'text', 'blob', 'tinytext', 'mediumtext', 'longtext', 'tinyblob', 'mediumblob', 'longblob' ) );
			// autoincremented columns don't care for default values:
			$autoIncrementedColumn				=	! in_array( $column->attributes( 'auto_increment' ), array( null, '', 'false' ), true );

			if ( $defaultValuePossible && ! $autoIncrementedColumn ) {
				if ( $column->attributes( 'default' ) === null ) {
					if ( $column->attributes( 'null' ) === 'true' ) {
						$shouldbeDefault			=	array( null );
					} else {
						$shouldbeDefault			=	$this->defaultValuesOfTypes( $this->mysqlToXmlsql( $column->attributes( 'type' ) ) );
					}
				} else {
					$shouldbeDefault				=	( $column->attributes( 'default' ) === 'NULL' ? array( null ) : array( $column->attributes( 'default' ) ) );
				}
				if ( ! in_array( $allColumns[$colName]->Default, $shouldbeDefault, true ) ) {
					if ( $change === false ) {
						$this->_setError( sprintf( 'Table %s Column %s DEFAULT is %s instead of %s', $tableName, $colName, $this->displayNull( $allColumns[$colName]->Default ), $column->attributes( 'default' ) ) );
					}
					return false;
				}
			}
			$shouldbeExtra						=	( $autoIncrementedColumn ? 'auto_increment' : '' );
			if ( $allColumns[$colName]->Extra !== $shouldbeExtra ) {
				if ( $change === false ) {
					$this->_setError( sprintf( 'Table %s Column %s AUTO_INCREMENT attribute is "%s" instead of "%s"', $tableName, $colName, $allColumns[$colName]->Extra, $shouldbeExtra ) );
				}
				return false;
			}
			$this->_setLog( sprintf( 'Table %s Column %s structure is up-to-date.', $tableName, $colName ), null, 'ok' );
			return true;
		}
		if ( $change === false ) {
			$this->_setError( sprintf( 'Table %s Column %s does not exist', $tableName, $colName ), null );
		}
		return false;
	}
	/**
	 * Utility to display NULL for nulls and quotations.
	 *
	 * @param unknown_type $val
	 * @return unknown
	 */
	function displayNull( $val ) {
		if ( $val === null ) {
			return 'NULL';
		} elseif ( is_numeric( $val ) ) {
			return $val;
		} else {
			return "'" . $val . "'";
		}
	}
	/**
	 * Checks if a column exists and has the type of the parameters below:
	 * @access private
	 *
	 * @param  string              $tableName       Name of table (for error strings)
	 * @param  array               $allColumns      From $this->getAllTableColumns( $table )
	 * @param  CBSimpleXMLElement  $columns         Columns to check array of string  Name of columns which are allowed to (should) exist
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $drop            TRUE If drops unneeded columns or not
	 * @return boolean             TRUE: no other columns exist, FALSE: errors are in $this->getErrors()
	 */
	function checkOtherColumnsExist( $tableName, &$allColumns, &$columns, $colNamePrefix, $drop = false ) {
		$isMatching								=	false;
		if ( $columns->name() == 'columns' ) {
			$isMatching							=	true;
			foreach ( array_keys( $allColumns ) as $existingColumnName ) {
				if ( ! $this->_inXmlChildrenAttribute( $existingColumnName, $columns, 'column', 'name', $colNamePrefix ) ) {
					if ( $drop ) {
						if ( ! $this->dropColumn( $tableName, $existingColumnName ) ) {
							$isMatching			=	false;
						}
					} else {
						$isMatching				=	false;
						$this->_setError( sprintf( 'Table %s Column %s exists but should not exist', $tableName, $existingColumnName ), null );
					}
				}
			}
			if ( $isMatching && ! $drop ) {
				$this->_setLog( sprintf( 'Table %s has no unneeded columns.', $tableName ), null, 'ok' );
			}
		}
		return $isMatching;
	}

	/**
	 * INDEXES CHECKS:
	 */

	/**
	 * Checks if an index exists and has the type of the parameters below:
	 * @access private
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  array               $allIndexes       From $this->getAllTableIndexes( $table )
	 * @param  CBSimpleXMLElement  $index            Index to check
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $change           TRUE: only true/false check type, FALSE: logs success and if mismatch, error details
	 * @return boolean             TRUE: identical, FALSE: errors are in $this->getErrors()
	 */
	function checkIndexExistsType( $tableName, &$allIndexes, &$index, $colNamePrefix, $change ) {
		$indexName								=	$this->_prefixedName( $index, $colNamePrefix );
		if ( isset( $allIndexes[$indexName] ) && isset( $allIndexes[$indexName][1] ) ) {
			$idxType							=	$allIndexes[$indexName][1]['type'];
			$idxUsing							=	$allIndexes[$indexName][1]['using'];
			
			if ( $idxType != $index->attributes( 'type' ) ) {
				if ( $change === false ) {
					$this->_setError( sprintf( 'Table %s Index %s type is %s instead of %s', $tableName, $indexName, $idxType, $index->attributes( 'type' ) ) );
				}
				return false;
			}
			if ( $index->attributes( 'using' ) && ( $idxUsing != $index->attributes( 'using' ) ) ) {
				if ( $change === false ) {
					$indexShouldBeUsing			=	( $index->attributes( 'using' ) ? $index->attributes( 'using' ) : 'btree' );
					$this->_setError( sprintf( 'Table %s Index %s is using %s instead of %s', $tableName, $indexName, $idxUsing, $indexShouldBeUsing ) );
				}
				return false;
			}
			$sequence							=	1;
			foreach ( $index->children() as $column ) {
				if ( $column->name() == 'column' ) {
					$colName					=	$this->_prefixedName( $column, $colNamePrefix );
					if ( ! isset( $allIndexes[$indexName][$sequence] ) ) {
						if ( $change === false ) {
							$this->_setError( sprintf( 'Table %s Index %s Column %s is missing in index', $tableName, $indexName, $colName ) );
						}
						return false;						
					}
					if ( $allIndexes[$indexName][$sequence]['name'] != $colName ) {
						if ( $change === false ) {
							$this->_setError( sprintf( 'Table %s Index %s Column %s is not the intended column, but %s', $tableName, $indexName, $colName, $allIndexes[$indexName][$sequence]['name'] ) );
						}
						return false;
					}
					if ( $column->attributes( 'size' ) && ( $allIndexes[$indexName][$sequence]['size'] != $column->attributes( 'size' ) ) ) {
						if ( $change === false ) {
							$this->_setError( sprintf( 'Table %s Index %s Column %s Size is %d instead of %s', $tableName, $indexName, $colName, $allIndexes[$indexName][$sequence]['size'], $column->attributes( 'size' ) ) );
						}
						return false;
					}
					// don't check ordering, as it can't be checked, and is probably irrelevant.
					++$sequence;
				}
			}
			$this->_setLog( sprintf( 'Table %s Index %s is up-to-date.', $tableName, $indexName ), null, 'ok' );
			return true;
		}
		if ( $change === false ) {
			$this->_setError( sprintf( 'Table %s Index %s does not exist', $tableName, $indexName ), null );
		}
		return false;
	}
	/**
	 * Checks if no surnumerous indexes exist
	 * @access private
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  array               $allIndexes       From $this->getAllTableIndexes( $table )
	 * @param  CBSimpleXMLElement  $indexes          Indexes to check
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $drop             TRUE If drops unneeded columns or not
	 * @return boolean             TRUE: no other columns exist, FALSE: errors are in $this->getErrors()
	 */
	function checkOtherIndexesExist( $tableName, &$allIndexes, &$indexes, $colNamePrefix, $drop = false ) {
		$isMatching								=	false;
		if ( $indexes->name() == 'indexes' ) {
			$isMatching							=	true;
			foreach ( array_keys( $allIndexes ) as $existingIndexName ) {
				if ( ! $this->_inXmlChildrenAttribute( $existingIndexName, $indexes, 'index', 'name', $colNamePrefix ) ) {
					if ( $drop ) {
						if ( ! $this->dropIndex( $tableName, $existingIndexName ) ) {
							$isMatching			=	false;
						}
					} else {
						$isMatching				=	false;
						$this->_setError( sprintf( 'Table %s Index %s exists but should not exist', $tableName, $existingIndexName ), null );
					}
				}
			}
			if ( $isMatching && ! $drop ) {
				$this->_setLog( sprintf( 'Table %s has no unneeded indexes.', $tableName ), null, 'ok' );
			}
		}
		return $isMatching;
	}

	/**
	 * ROWS CHECKS:
	 */

	/**
	 * Checks if no surnumerous indexes exist
	 * @access private
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  CBSimpleXMLElement  $rows             <rows...>
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $drop             TRUE If drops unneeded columns or not
	 * @return boolean             TRUE: no other columns exist, FALSE: errors are in $this->getErrors()
	 */
	function checkOtherRowsExist( $tableName, &$rows, $colNamePrefix, $drop = false ) {
		$isMatching								=	false;
		if ( $rows->name() == 'rows' ) {
			$isMatching							=	true;
			// $strictRows						=	( ( $rows->attributes( 'strict' ) === 'true' ) );
			if ( true /* $strictRows */ ) {

				// Build $strictRows index of indexes:
				$rowIndexes						=	array();
				foreach ( $rows->children() as $row ) {
					if ( $row->name() == 'row' ) {
						$indexName				=	$this->_prefixedName( $row, $colNamePrefix, 'index', 'indextype' );
						$indexValue				=	$row->attributes( 'value' );
						$indexValueType			=	$row->attributes( 'valuetype' );
						$rowIndexes[$indexName][$indexValue]	=	$indexValueType;
					}
				}

				// Count and if asked, drop rows which don't match:
				$otherRowsCount					=	$this->countRows( $tableName, $rowIndexes, false );
				$isMatching						=	( ( $otherRowsCount !== null ) && ( $otherRowsCount == 0 ) );
				if ( ! $isMatching ) {
					if ( $drop ) {
						$isMatching				=	$this->dropRows( $tableName, $rowIndexes, false );
					} else {
						$this->_setError( sprintf( 'Table %s has %s rows which should not exist', $tableName, $otherRowsCount ), null );
					}
				}
			}
			
			if ( $isMatching && ! $drop ) {
				$this->_setLog( sprintf( 'Table %s has no unneeded rows.', $tableName ), null, 'ok' );
			}
		}
		return $isMatching;
	}
	/**
	 * Drops $row from table $tableName
	 * @access private
	 *
	 * @param  string   $tableName                   Name of table (for error strings)
	 * @param  CBSimpleXMLElement  $row              <row index="columnname" indextype="prefixname" value="123" valuetype="sql:int" /> to delete
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return boolean
	 */
	function dropRow( $tableName, &$row, $colNamePrefix ) {
		$indexName								=	$this->_prefixedName( $row, $colNamePrefix, 'index', 'indextype' );
		$indexValue								=	$row->attributes( 'value' );
		$indexValueType							=	$row->attributes( 'valuetype' );
		$selection								=	array( $indexName => array( $indexValue => $indexValueType ) );
		return $this->dropRows( $tableName, $selection, true );
	}

	/**
	 * CHANGES OF TABLE STRUCTURE:
	 */

	/**
	 * Changes if a column exists or Creates a new column
	 * @access private
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  array               $allColumns       From $this->getAllTableColumns( $table )
	 * @param  CBSimpleXMLElement  $column           Column to check
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  CBSimpleXMLElement  $columnNameAfter  The column which should be just before this one
	 * @return boolean  TRUE: identical (no check on indexes), FALSE: errors are in $this->getErrors()
	 */
	function changeColumn( $tableName, &$allColumns, &$column, $colNamePrefix, &$columnNameAfter ) {
		$colNamePrefixed							=	$this->_prefixedName( $column, $colNamePrefix );
		$fullColumnType								=	$this->_fullColumnType( $column );
		if ( $fullColumnType !== false ) {
			$version								=	$this->_db->getVersion();
			$version								=	substr( $version, 0, strpos( $version, '-' ) );

			switch ( $columnNameAfter ) {
				case null:
					$firstAfterSQL					=	'';
					break;
	
				case 1:
					$firstAfterSQL					=	' FIRST';
					break;
	
				default:
					$colNameAfterPrefixed			=	$this->_prefixedName( $columnNameAfter, $colNamePrefix );
					$firstAfterSQL					=	' AFTER ' . $this->_db->NameQuote( $colNameAfterPrefixed );
					break;
			}

			$sqlUpdate								=	'';
			$updateResult							=	true;
	
			if ( isset( $allColumns[$colNamePrefixed] ) ) {
				// column exists already, change it:
				if ( $column->attributes( 'oldname' ) && array_key_exists( $this->_prefixedName( $column, $colNamePrefix, 'oldname' ), $allColumns ) ) {
					$oldColName						=	$this->_prefixedName( $column, $colNamePrefix, 'oldname' );
				} else {
					$oldColName						=	$colNamePrefixed;
				}
				if ( $column->attributes( 'initialvalue' ) && ( $column->attributes( 'null' ) !== 'true' ) ) {
					// we do need to treat the old NULL values specially:
					$sqlUpdate						=	'UPDATE ' . $this->_db->NameQuote( $tableName )
													.	"\n SET " . $this->_db->NameQuote( $colNamePrefixed )
													.	' = ' . $this->_sqlCleanQuote( $column->attributes( 'initialvalue' ), $column->attributes( 'initialvaluetype' ) )
													.	"\n WHERE " . $this->_db->NameQuote( $oldColName ) . ' IS NULL' 
													;
					$updateResult					=	$this->_doQuery( $sqlUpdate );
				}
	
				$alteration							=	'CHANGE ' . $this->_db->NameQuote( $oldColName );
			} else {
				// column doesn't exist, create it:
				$alteration							=	'ADD';
			}
			$sql									=	'ALTER TABLE ' . $this->_db->NameQuote( $tableName )
													.	"\n " . $alteration
													.	' ' . $this->_db->NameQuote( $colNamePrefixed )
													.	' ' . $fullColumnType
													.	( ! cbStartOfStringMatch( $version, '3.23' ) ? $firstAfterSQL : '' )
													;
			$alterationResult						=	$this->_doQuery( $sql );
	
			if ( $alterationResult && ( $alteration == 'ADD' ) ) {
				if ( $column->attributes( 'initialvalue' ) ) {
					$sqlUpdate						=	'UPDATE ' . $this->_db->NameQuote( $tableName )
													.	"\n SET " . $this->_db->NameQuote( $colNamePrefixed )
													.	' = ' . $this->_sqlCleanQuote( $column->attributes( 'initialvalue' ), $column->attributes( 'initialvaluetype' ) ) 
													;
					$updateResult					=	$this->_doQuery( $sqlUpdate );
				}
			}
			if ( ! $alterationResult ) {
				$this->_setError( sprintf( '%s::changeColumn (%s) of Table %s Column %s failed with SQL error: %s', get_class( $this ), $alteration, $tableName, $colNamePrefixed, $this->_db->getErrorMsg() ), $sql );
				return false;
			} elseif ( ! $updateResult ) {
				$this->_setError( sprintf( '%s::changeColumn (UPDATE) of Table %s Column %s failed with SQL error: %s', get_class( $this ), $tableName, $colNamePrefixed, $this->_db->getErrorMsg() ), $sqlUpdate );
				return false;
			} else {
				$this->_setLog( sprintf( 'Table %s Column %s %s successfully, type: %s', $tableName, $colNamePrefixed, ( $alteration == 'ADD' ? 'created' : 'changed' ), $this->_fullColumnType( $column ) ),
								( $alteration == 'ADD' ? $sql . ( $sqlUpdate ? ";\n" . $sqlUpdate : '' ) : ( $sqlUpdate ?  $sqlUpdate . ";\n" : '' ) . $sql ),
								'change' );
				return true;
			}
		} else {
			$this->_setError( sprintf( '%s::changeColumn of Table %s Column %s failed because the column type %s could not be determined (not starting with sql:).', get_class( $this ), $tableName, $colNamePrefixed, $column->attributes( 'type' ) ) );
			return false;
		}
	}
	/**
	 * Changes if an index exists or Creates a new index
	 * @access private
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  array               $allColumns       From $this->getAllTableColumns( $table )
	 * @param  CBSimpleXMLElement  $column           Column to check
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return boolean  TRUE: identical (no check on indexes), FALSE: errors are in $this->getErrors()
	 */
	function changeIndex( $tableName, &$allIndexes, &$index, $colNamePrefix ) {
		$indexName								=	$this->_prefixedName( $index, $colNamePrefix );

		$queryParts								=	array();
		if ( isset( $allIndexes[$indexName] ) ) {
			// index exists already,drop it:
			if ( $indexName == 'PRIMARY') {
				$queryParts[]					=	'DROP PRIMARY KEY';
			} else {
				$queryParts[]					=	'DROP KEY ' . $this->_db->NameQuote( $indexName );
			}
			$alteration							=	'change';
		} else {
			$alteration							=	'new';
		}
		// Now create new index:
		$queryParts[]							=	'ADD ' . $this->_fullIndexType( $index, $colNamePrefix );

		$sql									=	'ALTER TABLE ' . $this->_db->NameQuote( $tableName )
												.	"\n " . implode( ",\n ", $queryParts )
												;
		$alterationResult						=	$this->_doQuery( $sql );

		if ( ! $alterationResult ) {
			$this->_setError( sprintf( '%s::changeIndex (%s) of Table %s Index %s failed with SQL error: %s', get_class( $this ), $alteration, $tableName, $indexName, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->_setLog( sprintf( 'Table %s Index %s successfully %s', $tableName, $indexName, ( $alteration == 'new' ? 'created' : 'changed' ) ), $sql, 'change' );
			return true;
		}
	}
	/**
	 * Checks if an index exists and has the type of the parameters below:
	 * @access private
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  CBSimpleXMLElement  $rows             <rows...>
	 * @param  CBSimpleXMLElement  $row              <row> to change
	 * @param  array               $allColumns       From $this->getAllTableColumns( $table ) : columns which were existing before upgrading columns called before this function
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $change           TRUE: changes row, FALSE: checks row
	 * @return boolean             TRUE: identical, FALSE: errors are in $this->getErrors()
	 */
	function checkOrChangeRow( $tableName, &$rows, &$row, &$allColumns, $colNamePrefix, $change = true ) {
		$indexName								=	$this->_prefixedName( $row, $colNamePrefix, 'index', 'indextype' );
		$indexValue								=	$row->attributes( 'value' );
		$indexValueType							=	$row->attributes( 'valuetype' );

		$rowsArray								=	$this->loadRows( $tableName, $indexName, $indexValue, $indexValueType );

		$mismatchingFields						=	array();

		if ( is_array( $rowsArray ) && ( count( $rowsArray ) > 0 ) ) {
			foreach ( $rowsArray as $rowData ) {
				foreach ( $row->children() as $field ) {
					if ( $field->name() == 'field' ) {
						$strictField			=	$field->attributes( 'strict' );
						$fieldName				=	$this->_prefixedName( $field, $colNamePrefix );
						if ( $strictField || ! isset( $allColumns[$fieldName] ) ) {
							// if field is strict, or if column has just been created: compare value to the should be one:
							$fieldValue			=	$field->attributes( 'value' );
							$fieldValueType		=	$field->attributes( 'valuetype' );
							if (	( ! isset( $allColumns[$fieldName] ) )
								||	( ! array_key_exists( $fieldName, $rowData ) )
								||	( ( $strictField === 'true' ) && ( $this->_adjustToStrictType( $rowData->$fieldName, $fieldValueType ) !== $this->_phpCleanQuote( $fieldValue, $fieldValueType ) ) )
								||	( ( $strictField === 'notnull' ) && ( $this->_adjustToStrictType( $rowData->$fieldName, $fieldValueType ) === null ) && ( $this->_phpCleanQuote( $fieldValue, $fieldValueType ) !== null ) )
								||	( ( $strictField === 'notzero' ) && ( ( ( $this->_adjustToStrictType( $rowData->$fieldName, $fieldValueType ) === null ) || ( $this->_adjustToStrictType( $rowData->$fieldName, $fieldValueType ) == 0 ) )
																			 && ( ! ( ( $this->_phpCleanQuote( $fieldValue, $fieldValueType ) === null ) || ( $this->_phpCleanQuote( $fieldValue, $fieldValueType ) === 0 ) ) ) ) )
								||	( ( $strictField === 'notempty' ) && ( ( ( $this->_adjustToStrictType( $rowData->$fieldName, $fieldValueType ) === null ) || ( $this->_adjustToStrictType( $rowData->$fieldName, $fieldValueType ) == '' ) )
																			 && ( ! ( ( $this->_phpCleanQuote( $fieldValue, $fieldValueType ) === null ) || ( $this->_phpCleanQuote( $fieldValue, $fieldValueType ) === '' ) ) ) ) )
							   )
							{
								$mismatchingFields[$fieldName]	=	$this->_sqlCleanQuote( $fieldValue, $fieldValueType );
							}
						}
					}
				}
				foreach ( $row->children() as $field ) {
					if ( $field->name() == 'field' ) {
						$strictField			=	$field->attributes( 'strict' );
						if ( $strictField == 'updatewithfield' ) {
							// if field should be updated same time than another field: check if the field is in the list to be upgraded:
							$strictSameAsField	=	$field->attributes( 'strictsameasfield' );
							if ( isset( $mismatchingFields[$strictSameAsField] ) ) {
								$fieldName		=	$this->_prefixedName( $field, $colNamePrefix );
								$fieldValue		=	$field->attributes( 'value' );
								$fieldValueType	=	$field->attributes( 'valuetype' );
								if ( ( ! array_key_exists( $fieldName, $rowData ) )
								||	( $this->_adjustToStrictType( $rowData->$fieldName, $fieldValueType ) !== $this->_phpCleanQuote( $fieldValue, $fieldValueType ) )
								   )
								{
									$mismatchingFields[$fieldName]	=	$this->_sqlCleanQuote( $fieldValue, $fieldValueType );
								}
							}
						}
					}
				}
			}

			if ( count( $mismatchingFields ) > 0 ) {
				if ( $change === true ) {
					return $this->setFields( $tableName, $row, $mismatchingFields, $colNamePrefix );
				} else {
					$texts						=	array();
					foreach ($mismatchingFields as $name => $val ) {
						$texts[]				=	sprintf( 'Field %s = %s instead of %s', $name, ( isset( $rowData->$name ) ? $rowData->$name : '""' ), $val );
					}
					$this->_setError( sprintf( 'Table %s Rows %s = %s : %s', $tableName, $indexName, $indexValue, implode( ', ', $texts ) ) );
					return false;
				}
			} else {
				if ( $change === false ) {
					$this->_setLog( sprintf( 'Table %s Rows %s = %s are up-to-date.', $tableName, $indexName, $this->_sqlCleanQuote( $indexValue, $indexValueType ) ), null, 'ok' );
				}
				return true;
			}
		} else {
			if ( $change === true ) {
				return $this->insertRow( $tableName, $row, $colNamePrefix );
			} else {
				$this->_setError( sprintf( 'Table %s Rows %s = %s do not exist', $tableName, $indexName, $this->_sqlCleanQuote( $indexValue, $indexValueType ) ), null );
			}
			return false;
		}
	}
	/**
	 * Load rows from table $tableName
	 * @access private
	 *
	 * @param  string   $tableName        Name of table (for error strings)
	 * @param  string   $indexName
	 * @param  string   $indexValue
	 * @param  string   $indexValueType
	 * @return boolean
	 */
	function loadRows( $tableName, $indexName, $indexValue, $indexValueType ) {
		$sql									=	'SELECT * FROM ' . $this->_db->NameQuote( $tableName );
		if ( $indexName ) {
			$sql								.=	"\n WHERE " . $this->_db->NameQuote( $indexName )
												.	' = '
												.	$this->_sqlCleanQuote( $indexValue, $indexValueType )
												;
		}
		$this->_db->setQuery( $sql );
		$result									=	$this->_db->loadObjectList();
		if ( $this->_db->getErrorMsg() ) {
			$this->_setError( sprintf( '%s::loadRows of Table %s Rows %s = %s failed with SQL error: %s', get_class( $this ), $tableName, $indexName, $this->_sqlCleanQuote( $indexValue, $indexValueType ), $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			// $this->_setLog( sprintf( 'Table %s Rows %s = %s successfully loaded', $tableName, $columnName, $this->_sqlCleanQuote( $indexValue, $indexValueType ) ), $sql, 'change' );
			return $result;
		}
	}
	/**
	 * Drop rows from table $tableName matching $selection
	 * @access private
	 *
	 * @param  string   $tableName
	 * @param  array    $selection        array( 'columnName' => array( 'columnValue' => 'columnValueType' ) )
	 * @param  boolean  $positiveSelect   TRUE: select corresponding to selection, FALSE: Select NOT the selection
	 * @return boolean                    TRUE: no error, FALSE: error (logged)
	 */
	function dropRows( $tableName, &$selection, $positiveSelect ) {
		$where									=	$this->_sqlBuiildSelectionWhere( $selection, $positiveSelect );
		$sql									=	'DELETE FROM ' . $this->_db->NameQuote( $tableName )
												.	"\n WHERE " . $where
												;
		if ( ! $this->_doQuery( $sql ) ) {
			$this->_setError( sprintf( '%s::dropRows of Table %s Row(s) %s failed with SQL error: %s', get_class( $this ), $tableName, $where, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->_setLog( sprintf( 'Table %s Row(s) %s successfully dropped', $tableName, $where ), $sql, 'change' );
			return true;
		}
	}
	/**
	 * Counts rows from table $tableName matching $selection
	 * @access private
	 *
	 * @param  string   $tableName
	 * @param  array    $selection        array( 'columnName' => array( 'columnValue' => 'columnValueType' ) )
	 * @param  boolean  $positiveSelect   TRUE: select corresponding to selection, FALSE: Select NOT the selection
	 * @return boolean                    TRUE: no error, FALSE: error (logged)
	 */
	function countRows( $tableName, &$selection, $positiveSelect ) {
		$where									=	$this->_sqlBuiildSelectionWhere( $selection, $positiveSelect );
		$sql									=	'SELECT COUNT(*) FROM ' . $this->_db->NameQuote( $tableName )
												.	"\n WHERE " . $where
												;
		$this->_db->setQuery( $sql );
		$result									=	$this->_db->loadResult();
		if ( $result === null ) {
			$this->_setError( sprintf( '%s::countRows of Table %s Row(s) %s failed with SQL error: %s', get_class( $this ), $tableName, $where, $this->_db->getErrorMsg() ), $sql );
		}
		return $result;
	}
	/**
	 * Counts rows from table $tableName matching $selection
	 * @access private
	 *
	 * @param  string              $tableName
	 * @param  CBSimpleXMLElement  $row                <row index="columnname" indextype="prefixname" value="123" valuetype="sql:int" /> to delete
	 * @param  array               $mismatchingFields  array( 'columnName' => 'SQL-safe value' )
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return boolean                                 TRUE: no error, FALSE: error (logged)
	 */
	function setFields( $tableName, &$row, &$mismatchingFields, $colNamePrefix ) {
		$indexName								=	$this->_prefixedName( $row, $colNamePrefix, 'index', 'indextype' );
		$indexValue								=	$row->attributes( 'value' );
		$indexValueType							=	$row->attributes( 'valuetype' );

		$selection								=	array( $indexName => array( $indexValue => $indexValueType ) );
		$where									=	$this->_sqlBuiildSelectionWhere( $selection, true );

		$setFields								=	array();
		foreach ( $mismatchingFields as $name => $quotedValue ) {
			$setFields[]						=	$this->_db->NameQuote( $name ) . ' = ' . $quotedValue;
		}
		$setFieldsText							=	implode( ', ', $setFields );
		$sql									=	'UPDATE ' . $this->_db->NameQuote( $tableName )
												.	"\n SET " . $setFieldsText
												.	"\n WHERE " . $where
												;
		if ( ! $this->_doQuery( $sql ) ) {
			$this->_setError( sprintf( '%s::setFields of Table %s Row %s Fields %s failed with SQL error: %s', get_class( $this ), $tableName, $where, $setFieldsText, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->_setLog( sprintf( 'Table %s Row %s successfully updated', $tableName, $where ), $sql, 'change' );
			return true;
		}
	}
	/**
	 * Checks if an index exists and has the type of the parameters below:
	 * @access private
	 *
	 * @param  string              $tableName        Name of table (for error strings)
	 * @param  CBSimpleXMLElement  $row              <row> to change
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return boolean             TRUE: success, FALSE: errors are in $this->getErrors()
	 */
	function insertRow( $tableName, &$row, $colNamePrefix ) {
		$indexName								=	$this->_prefixedName( $row, $colNamePrefix, 'index', 'indextype' );
		$indexValue								=	$row->attributes( 'value' );
		$indexValueType							=	$row->attributes( 'valuetype' );

		if ( $row->name() == 'row' ) {
			$sqlFieldNames						=	array();
			$sqlFieldValues						=	array();
			foreach ( $row->children() as $field ) {
				if ( $field->name() == 'field' ) {
					$fieldName					=	$this->_prefixedName( $field, $colNamePrefix );
					$fieldValue					=	$field->attributes( 'value' );
					$fieldValueType				=	$field->attributes( 'valuetype' );
					if ( ( $fieldName == $indexName ) && ( ( $fieldValue != $indexValue ) || ( $fieldValueType != $indexValueType ) ) ) {
						$this->_setError( sprintf( '%s::insertRow Error in XML: Table %s Row %s = %s (type %s) trying to insert different Field Value or type: %s = %s (type %s)', get_class( $this ), $tableName, $indexName, $indexValue, $indexValueType, $fieldName, $fieldValue, $fieldValueType ), null );
						return false;
					}
					if ( isset( $sqlFieldNames[$fieldName] ) ) {
						$this->_setError( sprintf( '%s::insertRow Error in XML: Table %s Row %s = %s : Field %s is defined twice in XML', get_class( $this ), $tableName, $indexName, $indexValue, $fieldName ), null );
						return false;
					}
					$sqlFieldNames[$fieldName]	=	$this->_db->NameQuote( $fieldName );
					$sqlFieldValues[$fieldName]	=	$this->_sqlCleanQuote( $fieldValue, $fieldValueType );
				}
			}
			if ( ! isset( $sqlFieldNames[$indexName] ) ) {
				$sqlFieldNames[$indexName]		=	$this->_db->NameQuote( $indexName );
				$sqlFieldValues[$indexName]		=	$this->_sqlCleanQuote( $indexValue, $indexValueType );
			}

			if ( count( $sqlFieldNames ) > 0 ) {
				$sqlColumnsText					=	'(' . implode( ',', $sqlFieldNames ) . ')';
				$sqlColumnsValues				=	array();
				$sqlColumnsValues[]				=	'(' . implode( ',', $sqlFieldValues ) . ')';
			} elseif ( $indexName ) {
				$sqlColumnsText					=	'(' . $this->_db->NameQuote( $indexName ) . ')';
				$sqlColumnsValues				=	'(' . $this->_sqlCleanQuote( $indexValue, $indexValueType );
			} else {
				$sqlColumnsText					=	null;
			}
			if ( $sqlColumnsText != null ) {
				$sql							=	'INSERT INTO ' . $this->_db->NameQuote( $tableName )
												.	"\n " . $sqlColumnsText
												.	"\n VALUES " . implode( ",\n        ", $sqlColumnsValues )
												;
				if ( ! $this->_doQuery( $sql ) ) {
					$this->_setError( sprintf( '%s::insertRow of Table %s Row %s = %s Fields %s = %s failed with SQL error: %s', get_class( $this ), $tableName, $indexName, $indexValue, $sqlColumnsText, $sqlColumnsValues, $this->_db->getErrorMsg() ), $sql );
					return false;
				} else {
					$this->_setLog( sprintf( 'Table %s Row %s = %s successfully updated', $tableName, $indexName, $indexValue ), $sql, 'change' );
					return true;
				}

			}
		}
		$this->_setError( sprintf( '%s::insertRow : Error in SQL: No values to insert Row %s = %s (type %s)', $tableName, $indexName, $indexValue, $indexValueType ), $sql );
		return true;
		
	}
	/**
	 * Builds SQL WHERE statement (without WHERE) based on array $selection
	 * @access private
	 *
	 * @param  array    $selection        array( 'columnName' => array( 'columnValue' => 'columnValueType' ) )
	 * @param  boolean  $positiveSelect   TRUE: select corresponding to selection, FALSE: Select NOT the selection
	 * @return boolean  True: no error, False: error (logged)
	 */
	function _sqlBuiildSelectionWhere( &$selection, $positiveSelect ) {
		$where									=	array();
		foreach ( $selection as $colName => $valuesArray ) {
			$values								=	array();
			foreach ( $valuesArray as $colValue => $colValueType ) {
				$values[]						=	$this->_sqlCleanQuote( $colValue, $colValueType );
			}
			if ( count( $values ) > 0 ) {
				if ( count( $values ) > 1 ) {
					$where[]					=	$this->_db->NameQuote( $colName ) . ' IN (' .implode( ',', $values ) . ')';
				} else {
					$where[]					=	$this->_db->NameQuote( $colName ) . ' = ' . $values[0];
				}
			}
		}
		$positiveWhere							=	'(' . implode( ') OR (', $where ) . ')';
		if ( $positiveSelect ) {
			return $positiveWhere;
		} else {
			return 'NOT(' . $positiveWhere . ')';
		}
	}
	/**
	 * Drops column $ColumnName from table $tableName
	 * @access private
	 *
	 * @param  string   $tableName        Name of table (for error strings)
	 * @param  string   $columnName       Old name of column to change
	 * @return boolean                    TRUE: no error, FALSE: errors are in $this->getErrors()
	 */
	function dropColumn( $tableName, $columnName ) {
		$sql									=	'ALTER TABLE ' . $this->_db->NameQuote( $tableName )
												.	"\n DROP COLUMN " . $this->_db->NameQuote( $columnName )
												;
		if ( ! $this->_doQuery( $sql ) ) {
			$this->_setError( sprintf( '%s::dropColumn of Table %s Column %s failed with SQL error: %s', get_class( $this ), $tableName, $columnName, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->_setLog( sprintf( 'Table %s Column %s successfully dropped', $tableName, $columnName ), $sql, 'change' );
			return true;
		}
	}
	/**
	 * Drops INDEX $indexName from table $tableName
	 * @access private
	 *
	 * @param  string   $tableName        Name of table (for error strings)
	 * @param  string   $indexName       Old name of column to change
	 * @return boolean                    TRUE: no error, FALSE: errors are in $this->getErrors()
	 */
	function dropIndex( $tableName, $indexName ) {
		$sql									=	'ALTER TABLE ' . $this->_db->NameQuote( $tableName );
		if ( $indexName == 'PRIMARY' ) {
			$sql								.=	"\n DROP PRIMARY KEY";
		} else {
			$sql								.=	"\n DROP KEY " . $this->_db->NameQuote( $indexName );
		}
		if ( ! $this->_doQuery( $sql ) ) {
			$this->_setError( sprintf( '%s::dropIndex of Table %s Index %s failed with SQL error: %s', get_class( $this ), $tableName, $indexName, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->_setLog( sprintf( 'Table %s Index %s successfully dropped', $tableName, $indexName ), $sql, 'change' );
			return true;
		}
	}
	/**
	 * Drops table $tableName
	 * @access private
	 *
	 * @param  string   $tableName        Name of table (for error strings)
	 * @return boolean                    TRUE: no error, FALSE: errors are in $this->getErrors()
	 */
	function dropTable( $tableName ) {
		$sql									=	'DROP TABLE ' . $this->_db->NameQuote( $tableName )
												;
		if ( ! $this->_doQuery( $sql ) ) {
			$this->_setError( sprintf( '%s::dropTable of Table %s failed with SQL error: %s', get_class( $this ), $tableName, $this->_db->getErrorMsg() ), $sql );
			return false;
		} else {
			$this->_setLog( sprintf( 'Table %s successfully dropped', $tableName ), $sql, 'change' );
			return true;
		}
	}
	/**
	 * Creates a new table
	 * @access private
	 *
	 * @param  CBSimpleXMLElement  $table  Table
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return boolean                               True: success, False: failure
	 */
	function createTable( &$table, $colNamePrefix ) {
		if ( $table->name() == 'table' ) {
			$tableName							=	$this->_prefixedName( $table, $colNamePrefix );
			$columns							=&	$table->getElementByPath( 'columns' );
			if ( $tableName && ( $columns !== false ) ) {
				$version						=	$this->_db->getVersion();
				$version						=	substr( $version, 0, strpos($version, '-' ) );

				$sqlColumns						=	array();
				$auto_increment_initial_value	=	'';
	
				foreach ( $columns->children() as $column ) {
					if ( $column->name() == 'column' ) {
						$colNamePrefixed		=	$this->_prefixedName( $column, $colNamePrefix );
						$sqlColumns[]			=	"\n " . $this->_db->NameQuote( $colNamePrefixed )
												.	' ' . $this->_fullColumnType( $column )
												;
						if ( (int) $column->attributes( 'auto_increment' ) ) {
							$auto_increment_initial_value	=	' AUTO_INCREMENT=' . (int) $column->attributes( 'auto_increment' );
						}
					}
				}

				$indexes						=&	$table->getElementByPath( 'indexes' );
				if ( $indexes !== false ) {
					foreach ( $indexes->children() as $index ) {
						if ( $index->name() == 'index' ) {
							$sqlIndexText		=	$this->_fullIndexType( $index, $colNamePrefix );
							if ( $sqlIndexText ) {
								$sqlColumns[]	=	"\n " . $sqlIndexText;
							}
						}
					}
				}
				$sql							=	'CREATE TABLE ' . $this->_db->NameQuote( $tableName )
												.	' ('
												.	implode( ',', $sqlColumns )
												.	"\n )"
												.	( ! cbStartOfStringMatch( $version, '3.23' ) ? ' ENGINE=MyISAM' : '' )
												.	$auto_increment_initial_value
												;
				if ( ! $this->_doQuery( $sql ) ) {
					$this->_setError( sprintf( '%s::createTableof Table %s failed with SQL error: %s', get_class( $this ), $tableName, $this->_db->getErrorMsg() ), $sql );
					return false;
				} else {
					$this->_setLog( sprintf( 'Table %s successfully created', $tableName ), $sql, 'change' );
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * UTILITY FUNCTIONS:
	 */

	/**
	 * Sets modifying query and performs it, IF NOT in dry run mode.
	 * If in dry run mode, returns true
	 * @access private
	 *
	 * @param  string  $sql
	 * @return boolean
	 */
	function _doQuery( $sql ) {
		if ( $this->_dryRun ) {
			return true;
		} else {
			$this->_db->SetQuery( $sql );
			return $this->_db->query();
		}
	}
	/**
	 * Utility: Checks if $needle is the $attribute of a child of $xml
	 * @access private
	 *
	 * @param  string              $needle
	 * @param  CBSimpleXMLElement  $xml
	 * @param  string              $attribute
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return boolean
	 */
	function _inXmlChildrenAttribute( $needle, &$xml, $name, $attribute, $colNamePrefix) {
		foreach ( $xml->children() as $chld ) {
			if ( $chld->name() == $name ) {
				$colNamePrefixed				=	$this->_prefixedName( $chld, $colNamePrefix, $attribute );
				if ( $needle == $colNamePrefixed ) {
					return true;
				}
			}
		}
		return false;
	}
	/**
	 * Converts a XML description of a SQL column into a full SQL type
	 *
	 *	<column name="_rate" nametype="namesuffix" type="sql:decimal(16,8)" unsigned="true" null="true" default="NULL" auto_increment="100" />
	 *
	 * Returns: $fulltype: 'decimal(16,8) unsigned NULL DEFAULT NULL'
	 * @access private
	 *
	 * @param  CBSimpleXMLElement  $column
	 * @return string|boolean              Full SQL creation type or FALSE in case of error
	 */
	function _fullColumnType( &$column ) {
		$fullType					=	false;

		if ( $column->name() == 'column' ) {
			// $colName				=	$column->attributes( 'name' );
			// $colNameType			=	$column->attributes( 'nametype' );
			// if ( $colNameType == 'namesuffix' ) {
			//	$colName			=	$colNamePrefix . $colName;
			// }
			$type					=	$column->attributes( 'type' );
			$unsigned				=	$column->attributes( 'unsigned' );
			$null					=	$column->attributes( 'null' );
			$default				=	$column->attributes( 'default' );
			$auto_increment			=	$column->attributes( 'auto_increment' );


			if ( cbStartOfStringMatch( $type, 'sql:' ) ) {
				$type				=	trim( substr( $type, 4 ) );		// remove 'sql:'
				if ( $type ) {
					$notQuoted		=	array( 'int', 'float', 'tinyint', 'bigint', 'decimal', 'boolean', 'bit', 'serial', 'smallint', 'mediumint', 'double', 'year' );
					$isInt			=	false;
					foreach ( $notQuoted as $n ) {
						if ( cbStartOfStringMatch( $type, $n ) ) {
							$isInt	=	true;
							break;
						}
					}
					$fullType		=	$type;
					if ( $unsigned == 'true' ) {
						$fullType	.=	' unsigned';
					}
					if ( $null !== 'true' ) {
						$fullType	.=	' NOT NULL';
					}
					if ( ! in_array( $type, array( 'text', 'blob', 'tinytext', 'mediumtext', 'longtext', 'tinyblob', 'mediumblob', 'longblob' ))) {
						// BLOB and TEXT columns cannot have DEFAULT values. http://dev.mysql.com/doc/refman/5.0/en/blob.html
						if ( $default !== null ) {
							$fullType	.=	' DEFAULT ' . ( ( $isInt || ( $default === 'NULL' ) ) ? $default : $this->_db->Quote( $default ) );
						} elseif ( ! $auto_increment ) {
							// MySQL 5.0.51a and b have a bug: they need a default value always to be able to return it correctly in SHOW COLUMNS FROM ...:
							if ( $null === 'true' ) {
								$default =	'NULL';
							} elseif ( $isInt ) {
								$default =	0;
							} elseif ( in_array( $type, array( 'datetime', 'date', 'time' ) ) ) {
								$default =	$this->_db->getNullDate( $type );
							} else {
								$default =	'';
							}
							$fullType	.=	' DEFAULT ' . ( ( $isInt || ( $default === 'NULL' ) ) ? $default : $this->_db->Quote( $default ) );
						}
					}
					if ( $auto_increment ) {
						$fullType	.=	' auto_increment';
					}
				}
			}
		}
		return $fullType;
	}
	/**
	 * Converts a mysql type with 'sql:' prefix to a xmlsql sql:/const: type (without prefix).
	 *
	 * @param  string  $type   MySql type, E.g.: 'sql:varchar(255)' (with 'sql:' prefix)
	 * @return string          Xmlsql type, E.g.: 'string' (without 'sql:' or 'const:' prefix)
	 */
	function mysqlToXmlsql( $type ) {
		$mysqlTypes		=	array(	'varchar'		=>	'string',
									'character'		=>	'string',
									'char'			=>	'string',
									'binary'		=>	'string',
									'varbinary'		=>	'string',
									'tinyblob'		=>	'string',
									'blob'			=>	'string',
									'mediumblob'	=>	'string',
									'longblob'		=>	'string',
									'tinytext'		=>	'string',
									'mediumtext'	=>	'string',
									'longtext'		=>	'string',
									'text'			=>	'string',
									'tinyint'		=>	'int',
									'smallint'		=>	'int',
									'mediumint'		=>	'int',
									'bigint'		=>	'int',
									'integer'		=>	'int',
									'int'			=>	'int',
									'bit'			=>	'int',
									'boolean'		=>	'int',
									'year'			=>	'int',
									'float'			=>	'float',
									'double'		=>	'float',
									'decimal'		=>	'float',
									'date'			=>	'date',
									'datetime'		=>	'datetime',
									'timestamp'		=>	'datetime',
									'time'			=>	'time',
									'enum'			=>	'string'
									// missing since not in SQL standard: SET, and ENUM above is a little simplified since only partly supported.
								 );
		$cleanedType	=	preg_replace( '/^sql:([^\\(]*)\\(?.*/', '$1', $type );
		if ( isset( $mysqlTypes[$cleanedType] ) ) {
			return $mysqlTypes[$cleanedType];
		} else {
			trigger_error( sprintf( 'mysqlToXmlsql: Unknown SQL type %s (i am extracting "%s" from type)', $type, $cleanedType ), E_USER_WARNING );
			return $type;
		}
	}
	/**
	 * Returns the possible default default values for that type
	 *
	 * @param  string $type
	 * @return array  of string
	 */
	function defaultValuesOfTypes( $type ) {
		$defaultNulls	=	array(	'string'		=>	array( ''  ),
									'int'			=>	array( '', '0' ),
									'float'			=>	array( '', '0' ),
									'date'			=>	array( '', '0000-00-00' ),
									'datetime'		=>	array( '', '0000-00-00 00:00:00' ),
									'time'			=>	array( '', '00:00:00' ),
									'enum'			=>	array( '' )
								 );
		if ( isset( $defaultNulls[$type] ) ) {
			return $defaultNulls[$type];
		} else {
			trigger_error( sprintf( 'defaultValuesOfTypes: Unknown SQL type %s', $type ), E_USER_WARNING );
			return array( '', 0 );
		}
	}
	/**
	 * Cleans and makes a value SQL safe depending on the type that is enforced.
	 * @access private
	 *
	 * @param  mixed   $fieldValue
	 * @param  string  $type
	 * @return string
	 */
	function _sqlCleanQuote( $fieldValue, $type ) {
		$typeArray		=	explode( ':', $type, 3 );
		if ( count( $typeArray ) < 2 ) {
			$typeArray	=	array( 'const' , $type );
		}

		switch ( $typeArray[1] ) {
			case 'int':
				$value		=	(int) $fieldValue;
				break;
			case 'float':
				$value		=	(float) $fieldValue;
				break;
			case 'formula':
				$value		=	$fieldValue;
				break;
			case 'field':						// this is temporarly handled here
				$value		=	$this->_db->NameQuote( $fieldValue );
				break;
			case 'datetime':
				if ( preg_match( '/^[0-9]{4}-[01][0-9]-[0-3][0-9] [0-2][0-9](:[0-5][0-9]){2}$/', $fieldValue ) ) {
					$value	=	$this->_db->Quote( $fieldValue );
				} else {
					$value	=	"''";
				}
				break;
			case 'date':
				if ( preg_match( '/^[0-9]{4}-[01][0-9]-[0-3][0-9]$/', $fieldValue ) ) {
					$value	=	$this->_db->Quote( $fieldValue );
				} else {
					$value	=	"''";
				}
				break;
			case 'string':
				$value		=	$this->_db->Quote( $fieldValue );
				break;
			case 'null':
				if ( $fieldValue != 'NULL' ) {
					trigger_error( sprintf( 'CBSQLUpgrader::_sqlCleanQuote: ERROR: field type sql:null has not NULL value' ) );
				}
				$value		=	'NULL';
				break;

			default:
				trigger_error( 'CBSQLUpgrader::_sqlQuoteValueType: ERROR_UNKNOWN_TYPE: ' . htmlspecialchars( $type ), E_USER_NOTICE );
				$value		=	$this->_db->Quote( $fieldValue );	// false;
				break;
		}
		return (string) $value;
	}
	/**
	 * Cleans and makes a value comparable to the SQL stored value in a comprofilerDBTable object, depending on the type that is enforced.
	 * @access private
	 *
	 * @param  mixed   $fieldValue
	 * @param  string  $type
	 * @return string
	 */
	function _phpCleanQuote( $fieldValue, $type ) {
		$typeArray		=	explode( ':', $type, 3 );
		if ( count( $typeArray ) < 2 ) {
			$typeArray	=	array( 'const' , $type );
		}

		switch ( $typeArray[1] ) {
			case 'int':
				$value		=	(int) $fieldValue;
				break;
			case 'float':
				$value		=	(float) $fieldValue;
				break;
			case 'formula':
				$value		=	$fieldValue;	// this is temporarly done so
				break;
			case 'field':						// this is temporarly handled here
				$value		=	$fieldValue;
				break;
			case 'datetime':
				if ( preg_match( '/^[0-9]{4}-[01][0-9]-[0-3][0-9] [0-2][0-9](:[0-5][0-9]){2}$/', $fieldValue ) ) {
					$value	=	(string) $fieldValue;
				} else {
					$value	=	'';
				}
				break;
			case 'date':
				if ( preg_match( '/^[0-9]{4}-[01][0-9]-[0-3][0-9]$/', $fieldValue ) ) {
					$value	=	(string) $fieldValue;
				} else {
					$value	=	'';
				}
				break;
			case 'string':
				$value		=	(string) $fieldValue;
				break;
			case 'null':
				if ( $fieldValue != 'NULL' ) {
					trigger_error( sprintf( 'CBSQLUpgrader::_phpCleanQuote: ERROR: field type sql:null has not NULL value' ) );
				}
				$value		=	null;
				break;

			default:
				trigger_error( 'CBSQLUpgrader::_sqlQuoteValueType: ERROR_UNKNOWN_TYPE: ' . htmlspecialchars( $type ), E_USER_NOTICE );
				$value		=	(string) $fieldValue;	// false;
				break;
		}
		return $value;
	}
	/**
	 * Cleans and makes a value comparable to the SQL stored value in a comprofilerDBTable object, depending on the type that is enforced.
	 * @access private
	 *
	 * @param  string|null  $fieldValue
	 * @param  string  $type
	 * @return mixed
	 */
	function _adjustToStrictType( $fieldValue, $type ) {
		$typeArray		=	explode( ':', $type, 3 );
		if ( count( $typeArray ) < 2 ) {
			$typeArray	=	array( 'const' , $type );
		}

		$value				=	$fieldValue;
		if ( $fieldValue !== null ) {
			switch ( $typeArray[1] ) {
				case 'int':
					if ( is_int( $fieldValue ) || preg_match( '/^\d++$/', $fieldValue ) ) {
						$value	=	(int) $fieldValue;
					}
					break;
				case 'float':
					if ( is_float( $fieldValue ) || ( preg_match( '/^(((+|-)?\d+(\.\d*)?([Ee](+|-)?\d+)?)|((+|-)?(\d*\.)?\d+([Ee](+|-)?\d+)?))$/', $fieldValue ) ) ) {
						$value	=	(float) $fieldValue;
					}
					break;
				case 'formula':
					$value		=	$fieldValue;	// this is temporarly done so
					break;
				case 'field':						// this is temporarly handled here
					$value		=	$fieldValue;
					break;
				case 'datetime':
					if ( preg_match( '/^[0-9]{4}-[01][0-9]-[0-3][0-9] [0-2][0-9](:[0-5][0-9]){2}$/', $fieldValue ) ) {
						$value	=	(string) $fieldValue;
					} else {
						$value	=	'';
					}
					break;
				case 'date':
					if ( preg_match( '/^[0-9]{4}-[01][0-9]-[0-3][0-9]$/', $fieldValue ) ) {
						$value	=	(string) $fieldValue;
					} else {
						$value	=	'';
					}
					break;
				case 'string':
					if ( is_string( $fieldValue ) ) {
						$value	=	(string) $fieldValue;
					}
					break;
				case 'null':
					if ( $fieldValue === null ) {
						$value	=	null;
					}
					break;

				default:
					trigger_error( 'CBSQLUpgrader::_sqlQuoteValueType: ERROR_UNKNOWN_TYPE: ' . htmlspecialchars( $type ), E_USER_NOTICE );
					$value		=	(string) $fieldValue;	// false;
					break;
			}
		}
		return $value;
	}
	/**
	 * Converts a XML description of a SQL index into a full SQL type
	 *
	 *	<index name="PRIMARY" type="primary">
	 *		<column name="id"	/>
	 *	</index>
	 *	<index name="rate_chars">
	 *		<column name="rate" />
	 *		<column name="_mychars" nametype="namesuffix" size="8" ordering="DESC" />
	 *	</index>
	 *	<index name="myrate" type="unique" using="btree">
	 *		<column name="rate" />
	 *	</index>
	 *
	 * Returns: $fulltype: 'decimal(16,8) unsigned NULL DEFAULT NULL'
	 * @access private
	 *
	 * @param  CBSimpleXMLElement  $index
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return string|boolean                        Full SQL creation type or NULL in case of no index/error
	 */
	function _fullIndexType( &$index, $colNamePrefix ) {
		$sqlIndexText							=	null;

		if ( $index->name() == 'index' ) {
			// first collect all columns of this index:
			$indexColumns						=	array();
			foreach ( $index->children() as $column ) {
				if ( $column->name() == 'column' ) {
					$colNamePrefixed			=	$this->_prefixedName( $column, $colNamePrefix );
					$indexColText				=	$this->_db->NameQuote( $colNamePrefixed );
					if ( $column->attributes( 'size' ) ) {
						$indexColText			.=	' (' . (int) $column->attributes( 'size' ) . ')';
					}
					if ( $column->attributes( 'ordering' ) ) {
						$indexColText			.=	' ' . $this->_db->getEscaped( $column->attributes( 'ordering' ) );
					}

					$indexColumns[]				=	$indexColText;
				}
			}
			if ( count( $indexColumns ) > 0 ) {
				// then build the index creation SQL:
				if ( $index->attributes( 'type' ) ) {
					// PRIMARY, UNIQUE, FULLTEXT, SPATIAL:
					$sqlIndexText				.=	$this->_db->getEscaped( strtoupper( $index->attributes( 'type' ) ) ) . ' ';
				}
				$sqlIndexText					.=	'KEY ';
				if ( $index->attributes( 'type' ) !== 'primary' ) {
					$sqlIndexText				.=	$this->_db->NameQuote( $this->_prefixedName( $index, $colNamePrefix ) ) . ' ';
				}
				if ( $index->attributes( 'using' ) ) {
					// BTREE, HASH, RTREE:
					$sqlIndexText				.=	'USING ' . $this->_db->getEscaped( $index->attributes( 'using' ) ) . ' ';
				}
				$sqlIndexText					.=	'(' . implode( ', ', $indexColumns ) . ')';
			}
		}
		return $sqlIndexText;
	}
	/**
	 * Prefixes the $attribute of $column (or table or other xml element) with
	 * $colNamePrefix if $column->attributes( 'nametype' ) == 'namesuffix' or 'nameprefix'
	 * @access private
	 *
	 * @param  CBSimpleXMLElement  $column
	 * @param  string              $colNamePrefix
	 * @param  string              $attribute      
	 * @param  string              $modifyingAttr
	 * @return string
	 */
	function _prefixedName( &$column, $colNamePrefix, $attribute = 'name', $modifyingAttr = 'nametype' ) {
		$colName								=	$column->attributes( $attribute );
		$colNameType							=	$column->attributes( $modifyingAttr );

		switch ( $colNameType ) {
			case 'nameprefix':
				$colName						.=	$colNamePrefix;
				
				break;
			case 'namesuffix':
				$colName						=	$colNamePrefix . $colName;
				break;
		
			default:
				break;
		}
		return $colName;
	}
	/**
	 * Checks if all columns of a xml description of all tables of a database matches the database
	 *
	 * Warning: if ( $change && $strictlyColumns ) it will DROP not described columns !!!
	 * @access private
	 *
	 * @param  CBSimpleXMLElement  $table
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean             $change           FALSE: only check, TRUE: change database to match description (deleting columns if $strictlyColumns == true)
	 * @param  boolean|null        $strictlyColumns  FALSE: allow for other columns, TRUE: doesn't allow for other columns, NULL: checks for attribute 'strict' in table
	 * @return boolean             TRUE: matches, FALSE: don't match
	 */
	function checkXmlTableDescription( &$table, $colNamePrefix = '', $change = false, $strictlyColumns = false ) {
		$isMatching								=	false;
		if ( $table->name() == 'table' ) {
			$tableName							=	$this->_prefixedName( $table, $colNamePrefix );
			$columns							=&	$table->getElementByPath( 'columns' );
			if ( $tableName && ( $columns !== false ) ) {
				if ( $strictlyColumns === null ) {
					$strictlyColumns			=	( $table->attributes( 'strict' ) == 'true' );
				}
				$isMatching						=	true;
				$allColumns						=	$this->getAllTableColumns( $tableName );
				if ( $allColumns === false ) {
					// table doesn't exist:
					if ( $change ) {
						if ( $this->createTable( $table, $colNamePrefix ) ) {
							$allColumns			=	$this->getAllTableColumns( $tableName );
						} else {
							$isMatching			=	false;
						}
					} else {
						$this->_setError( sprintf( 'Table %s does not exist', $tableName ), null );
						$isMatching				=	false;
					}
				} else {
					// Table exists:
					// 1) Check columns:
					if ( $strictlyColumns ) {
						$columnBefore			=	1;
					} else {
						$columnBefore			=	null;
					}
					foreach ( $columns->children() as $column ) {
						if ( $column->name() == 'column' ) {
							if ( ! $this->checkColumnExistsType( $tableName, $allColumns, $column, $colNamePrefix, $change ) ) {
								if ( ( ! $change ) || ( ! $this->changeColumn( $tableName, $allColumns, $column, $colNamePrefix, $columnBefore ) ) ) {
									$isMatching	=	false;
								}
							}
							$columnBefore		=	$column;
						}
					}
					if ( $strictlyColumns && ( $columns->attributes( 'strict' ) !== 'false' ) && ! $this->checkOtherColumnsExist( $tableName, $allColumns, $columns, $colNamePrefix, $change ) ) {
						$isMatching				=	false;
					}

					// 2) Check indexes:
					$indexes					=&	$table->getElementByPath( 'indexes' );
					if ( $indexes !== false ) {
						$allIndexes				=	$this->getAllTableIndexes( $tableName );
						foreach ( $indexes->children() as $index ) {
							if ( $index->name() == 'index' ) {
								if ( ! $this->checkIndexExistsType( $tableName, $allIndexes, $index, $colNamePrefix, $change ) ) {
									if ( ( ! $change ) || ( ! $this->changeIndex( $tableName, $allIndexes, $index, $colNamePrefix ) ) ) {
										$isMatching	=	false;
									}
								}
							}
						}
						if ( $strictlyColumns && ( $indexes->attributes( 'strict' ) !== 'false' ) && ! $this->checkOtherIndexesExist( $tableName, $allIndexes, $indexes, $colNamePrefix, $change ) ) {
							$isMatching			=	false;
						}
					}
				}
				// 3) Now that indexed table is checked (exists or has been created), Check rows:
				if ( $allColumns !== false ) {
					$rows						=&	$table->getElementByPath( 'rows' );
					if ( $rows !== false ) {
						foreach ( $rows->children() as $row ) {
							if ( $row->name() == 'row' ) {
								if ( ! $this->checkOrChangeRow( $tableName, $rows, $row, $allColumns, $colNamePrefix, $change ) ) {
									$isMatching	=	false;
								}
							}
						}
						if ( $strictlyColumns && ( $rows->attributes( 'strict' ) !== 'false' ) && ! $this->checkOtherRowsExist( $tableName, $rows, $colNamePrefix, $change ) ) {
							$isMatching			=	false;
						}
					}
				}
			}
		}
		return $isMatching;
	}
	/**
	 * Checks if all columns of a xml description of all tables of a database matches the database
	 *
	 * Warning: removes columns tables and columns which would be added by the changes to XML !!!
	 * @access private
	 *
	 * @param  CBSimpleXMLElement  $table
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  string              $change           'drop': uninstalls columns/tables
	 * @param  boolean|null        $strictlyColumns  FALSE: allow for other columns, TRUE: doesn't allow for other columns, NULL: checks for attribute 'strict' in table
	 * @return boolean             TRUE: matches, FALSE: don't match
	 */
	function dropXmlTableDescription( &$table, $colNamePrefix = '', $change = 'drop', $strictlyColumns = false ) {
		$isMatching										=	false;
		if ( ( $change == 'drop' ) && ( $table->name() == 'table' ) ) {
			$tableName									=	$this->_prefixedName( $table, $colNamePrefix );
			$columns									=&	$table->getElementByPath( 'columns' );
			if ( $tableName && ( $columns !== false ) ) {
				if ( $strictlyColumns === null ) {
					$strictlyColumns					=	( $table->attributes( 'strict' ) === 'true' );
				}
				$neverDropTable							=	( $table->attributes( 'drop' ) === 'never' );
				$isMatching								=	true;
				$allColumns								=	$this->getAllTableColumns( $tableName );
				if ( $allColumns === false ) {
					// table doesn't exist: do nothing
				} else {
					if ( $strictlyColumns && ( ! $neverDropTable ) ) {
						if ( in_array( $tableName, array( '#__comprofiler', '#_users', '#__comprofiler_fields' ) ) ) {
							// Safeguard against fatal error in XML file !
							$errorMsg					=	sprintf( 'Fatal error: Trying to delete core CB table %s not allowed.', $tableName );
							echo $errorMsg;
							trigger_error( $errorMsg, E_USER_ERROR );
							exit;
						}
						$this->dropTable( $tableName );
					} else {
						// 1) Drop rows:
						$rows								=&	$table->getElementByPath( 'rows' );
						if ( $rows !== false ) {
							$neverDropRows					=	( $rows->attributes( 'drop' ) === 'never' );
							if ( ! $neverDropRows ) {
								$strictRows					=	( ( $rows->attributes( 'strict' ) === 'true' ) );
								foreach ( $rows->children() as $row ) {
									if ( $row->name() == 'row' ) {
										$neverDropRow		=	( $row->attributes( 'drop' ) === 'never' );
										if ( ( $strictRows && ! $neverDropRow ) ) {
											if ( ! $this->dropRow( $tableName, $row, $colNamePrefix ) ) {
												$isMatching	=	false;
											}
										}
									}
								}
							}
						}
						// 2) Drop indexes:
						$indexes							=&	$table->getElementByPath( 'indexes' );
						if ( $indexes !== false ) {
							$neverDropIndexes				=	( $indexes->attributes( 'drop' ) === 'never' );
							if ( ! $neverDropIndexes ) {
								$allIndexes					=	$this->getAllTableIndexes( $tableName );
								foreach ( $indexes->children() as $index ) {
									if ( $index->name() == 'index' ) {
										$indexName			=	$this->_prefixedName( $index, $colNamePrefix );
										if ( $indexName == 'PRIMARY' ) {
											$neverDropIndex	=	( $index->attributes( 'drop' ) !== 'always' );
										} else {
											$neverDropIndex	=	( $index->attributes( 'drop' ) === 'never' );
										}
										if ( isset( $allIndexes[$indexName] ) && ! $neverDropIndex ) {
											if ( ! $this->dropIndex( $tableName, $indexName ) ) {
												$isMatching	=	false;
											}
										}
									}
								}
							}
						}
						// 3) Drop columns:
						$neverDropColumns					=	( $columns->attributes( 'drop' ) === 'never' );
						if ( ! $neverDropColumns ) {
							foreach ( $columns->children() as $column ) {
								if ( $column->name() == 'column' ) {
									$neverDropColumn		=	( $column->attributes( 'drop' ) === 'never' );
									$colNamePrefixed		=	$this->_prefixedName( $column, $colNamePrefix );
									if ( isset( $allColumns[$colNamePrefixed] ) && ! $neverDropColumn ) {
										if ( ! $this->dropColumn( $tableName, $colNamePrefixed ) ) {
											$isMatching		=	false;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $isMatching;
	}
	/**
	 * Checks if all columns of a xml description of all tables of a database matches the database
	 *
	 * Warning: if ( $change && $strictlyColumns ) it will DROP not described columns !!!
	 * @access private
	 *
	 * @param  string|array        $tablesNames      Name(s) of tables to dump
	 * @param  boolean             $withContent      FALSE: only structure, TRUE: also content
	 * @return CBSimpleXMLElement
	 */
	function dumpTableToXml( $tablesNames, $withContent = true ) {
		$db											=	new CBSimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><database version="1" />' );

		foreach ( (array) $tablesNames as $tableName ) {
			
			$table									=	$db->addChild( 'table' );
			$table->addAttribute( 'name', $tableName );
			$table->addAttribute( 'class', '' );
			$table->addAttribute( 'strict', 'false' );
			$table->addAttribute( 'drop', 'never' );
	
			// Columns:
	
			$allColumns								=	$this->getAllTableColumns( $tableName );
	
			$columns								=	$table->addChild( 'columns' );
			foreach ( $allColumns as $colEntry ) {
				$colTypeUnsigned					=	explode( ' ', $colEntry->Type );
				if ( count( $colTypeUnsigned ) == 1 ) {
					$colTypeUnsigned[1]				=	null;
				}
				$column								=	$columns->addChild( 'column' );
				$column->addAttribute( 'name', 		$colEntry->Field );
				$column->addAttribute( 'type',		'sql:' . $colTypeUnsigned[0] );
				if ( $colTypeUnsigned[1] === 'unsigned' ) {
					$column->addAttribute( 'unsigned',	( $colTypeUnsigned[1] === 'unsigned' ? 'true' : 'false' ) );
				}
				if ( $colEntry->Null === 'YES' ) {
					$column->addAttribute( 'null',		( $colEntry->Null === 'YES' ? 'true' : 'false' ) );
				}
				if ( $colEntry->Default !== null ) {
					if ( $colEntry->Null === 'YES' ) {
						$column->addAttribute( 'default', $colEntry->Default );
					} else {
						$defaultDefaultTypes		=	$this->defaultValuesOfTypes( $this->mysqlToXmlsql( 'sql:' . $colTypeUnsigned[0] ) );
						if ( ! in_array( $colEntry->Default, $defaultDefaultTypes ) ) {
							$column->addAttribute( 'default', $colEntry->Default );
						}
					}
				}
				if ( strpos( $colEntry->Extra, 'auto_increment' ) !== false ) {
					$tableStatus					=	$this->_db->getTableStatus( $tableName );
					if ( isset( $tableStatus[0]->Auto_increment ) ) {
						$lastAuto_increment			=	$tableStatus[0]->Auto_increment;
					} else {
						$lastAuto_increment			=	'100';
					}
					$column->addAttribute( 'auto_increment', $lastAuto_increment );
				}
			}
	
			// Indexes:
	
			$indexes								=	$table->addChild( 'indexes' );
			
			$primaryIndex							=	null;
	
			$allIndexes								=	$this->getAllTableIndexes( $tableName );
	
			foreach ( $allIndexes as $indexName => $sequenceInIndexArray ) {
				$type								=	$sequenceInIndexArray[1]['type'];
				$using								=	$sequenceInIndexArray[1]['using'];
	
				$index								=	$indexes->addChild( 'index' );
				$index->addAttribute( 'name',	$indexName );
				if ( $type != '' ) {
					$index->addAttribute( 'type',	$type );
				}
				if ( $using != 'btree' ) {
					$index->addAttribute( 'using',	$using );
				}
				foreach ( $sequenceInIndexArray as /* $sequenceInIndex => */ $indexAttributes ) {
					$column							=	$index->addChild( 'column' );
					$column->addAttribute( 'name', $indexAttributes['name'] );
					if ( $indexAttributes['size'] ) {
						$column->addAttribute( 'size', $indexAttributes['size'] );
					}
					if ( $indexAttributes['ordering'] != 'A' ) {
						$column->addAttribute( 'ordering', $indexAttributes['ordering'] );
					}
				}
				if ( $type == 'primary' ) {
					$primaryIndex					=	$index;
				}
			}
	
			// Content:
	
			if ( $withContent ) {
				$allRows							=	$this->loadRows( $tableName, null, null, null );
				if ( count( $allRows ) > 0 ) {
					$rows							=	$table->addChild( 'rows' );
	
					$primaryNames					=	null;
					if ( $primaryIndex !== null ) {
						foreach ( $primaryIndex->children() as $column ) {
							if ( $column->name() == 'column' ) {
								$primaryNames[]		=	$column->attributes( 'name' );
							}
						}
					}
	
					foreach ( $allRows as $rowData ) {
						$row								=	$rows->addChild( 'row' );
						// missing primary key here:
						$rowIndexName						=	array();
						$rowIndexValue						=	array();
						$rowIndexValueType					=	array();
						foreach ( get_object_vars( $rowData ) as $fieldDataName => $fieldDataValue ) {
							if ( $fieldDataName[0] != '_' ) {
								$typeColumn					=	$columns->getChildByNameAttributes( 'column', array( 'name' => $fieldDataName ) );
								$fieldDataValueType			=	'const:' . $this->mysqlToXmlsql( $typeColumn->attributes( 'type' ) );
								$field						=	$row->addChild( 'field' );
								if ( $fieldDataValue === null ) {
									$fieldDataValue			=	'NULL';
									$fieldDataValueType		=	'const:null';
								}
								$field->addAttribute( 'name', $fieldDataName );
								$field->addAttribute( 'value', $fieldDataValue );
								$field->addAttribute( 'valuetype', $fieldDataValueType );
								if ( in_array( $fieldDataName, $primaryNames ) ) {
									$field->addAttribute( 'strict', 'true' );
									$rowIndexName[]			=	$fieldDataName;
									$rowIndexValue[]		=	$fieldDataValue;
									$rowIndexValueType[]	=	$fieldDataValueType;
								}
							}
						}
						$row->addAttribute( 'index',	 implode( ' ', $rowIndexName ) );
						$row->addAttribute( 'value',	 implode( ' ', $rowIndexValue ) );
						$row->addAttribute( 'valuetype', implode( ' ', $rowIndexValueType ) );
					}
				}
			}
		}
		return $db;
	}
	/**
	 * Main function FOR CB INTERNAL USE ONLY:
	 */

	/**
	 * Checks if all columns of a xml description of all tables of a database matches the database
	 *
	 * Warning: if ( $change && $strictlyColumns ) it will DROP not described columns !!!
	 *
	 * 	<database version="1">
	 *		<table name="#__comprofiler" class="moscomprofiler">
	 *			<columns>
	 *				<column name="_rate" nametype="namesuffix" type="sql:decimal(16,8)" unsigned="true" null="true" default="NULL" auto_increment="100" />
	 *		<table name="#__comprofiler_hf2_" nametype="nameprefix" class="myClass" strict="true">
	 *			<indexes>
	 *				<index name="primary" type="primary">
	 *					<column name="id"	/>
	 *				</index>
	 *				<index name="rate_chars">
	 *					<column name="rate" />
	 *					<column name="_mychars" nametype="namesuffix" size="8" ordering="ASC" />
	 *				</index>
	 *				<index name="chars_rate_id" type="unique" using="btree">
	 *
	 * @param  CBSimpleXMLElement  $xml
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  boolean|string      $change           FALSE: only check, TRUE: change database to match description (deleting non-matching columns if $strictlyColumns == true), 'drop': uninstalls columns/tables
	 * @param  boolean             $strictlyColumns  FALSE: allow for other columns, TRUE: doesn't allow for other columns
	 * @return boolean             TRUE: matches, FALSE: don't match
	 */
	function checkXmlDatabaseDescription( &$db, $colNamePrefix = '', $change = false, $strictlyColumns = false ) {
		$isMatching								=	false;
		if ( $db->name() == 'database' ) {
			$isMatching							=	true;
			foreach ( $db->children() as $table ) {
				if ( $table->name() == 'table' ) {
					if ( is_bool( $change ) ) {
						$isMatching				=	$this->checkXmlTableDescription( $table, $colNamePrefix, $change, $strictlyColumns ) && $isMatching;
					} else {
						$isMatching				=	$this->dropXmlTableDescription( $table, $colNamePrefix, $change, $strictlyColumns ) && $isMatching;
					}
				}
			}
		}
		return $isMatching;
	}
	/**
	 * Returns main table name (pre/post/fixed with $colNamePrefix)
	 *
	 * <database>
	 * 		<table name="xyz" nametype="nameprefix" maintable="true">
	 *
	 * @param  CBSimpleXMLElement  $db
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @param  string|null         $default          Default table result to return if table not found in xml
	 * @return string
	 */
	function getMainTableName( &$db, $colNamePrefix = '', $default = null ) {
		$maintable								=&	$db->getChildByNameAttr( 'table', 'maintable', 'true' );
		if ( $maintable !== false ) {
			return $this->_prefixedName( $maintable, $colNamePrefix );
		}
		return $default;
	}
	/**
	 * Returns array of column names (pre/post/fixed with $colNamePrefix) of $table
	 *
	 * @param  CBSimpleXMLElement  $db
	 * @param  string              $colNamePrefix    Prefix to add to all column names
	 * @return array|boolean                         False if not found
	 */
	function getMainTableColumnsNames( &$db, $colNamePrefix = '' ) {
		$table									=&	$db->getChildByNameAttr( 'table', 'maintable', 'true' );
		if ( $table !== false ) {
			$columns							=&	$table->getElementByPath( 'columns' );
			if ( $columns !== false ) {
				$columnNamesArray				=	array();
				foreach ( $columns->children() as $column ) {
					if ( $column->name() == 'column' ) {
						$columnNamesArray[]		=	$this->_prefixedName( $column, $colNamePrefix );
					}
				}
				return $columnNamesArray;
			}
		}
		return false;
	}
}	// class CBSQLupgrader

?>
