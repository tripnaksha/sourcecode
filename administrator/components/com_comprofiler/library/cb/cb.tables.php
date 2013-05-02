<?php
/**
* Joomla/Mambo Community Builder
* @version $Id: cb.tables.php 567 2006-11-19 10:05:00Z beat $
* @package Community Builder
* @subpackage cb.tables.php
* @author Beat
* @copyright (C) 2008-2009 www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


class moscomprofilerPlugin extends comprofilerDBTable {
	/** @var int */
	var $id					=	null;
	/** @var varchar */
	var $name				=	null;
	/** @var varchar */
	var $element			=	null;
	/** @var varchar */
	var $type				=	null;
	/** @var varchar */
	var $folder				=	null;
	/** @var varchar */
	var $backend_menu		=	null;
	/** @var tinyint unsigned */
	var $access				=	null;
	/** @var int */
	var $ordering			=	null;
	/** @var tinyint */
	var $published			=	null;
	/** @var tinyint */
	var $iscore				=	null;
	/** @var tinyint */
	var $client_id			=	null;
	/** @var int unsigned */
	var $checked_out		=	null;
	/** @var datetime */
	var $checked_out_time	=	null;
	/** @var text */
	var $params				=	null;
	/**
	* Constructor
    * @param  CBdatabase  $db   A database connector object
	*/
	function moscomprofilerPlugin( &$db ) {
		$this->comprofilerDBTable( '#__comprofiler_plugin', 'id', $db );
	}
	function check() {
		$ok		=	( $this->name );
		if ( ! $ok ) {
			$this->_error	=	"Save not allowed";
		}
		return $ok;
	}
}
class moscomprofilerLists extends comprofilerDBTable {

	var $listid				=	null;
	var $title				=	null;
	var $description		=	null;
	var $published			=	null;
	var $default			=	null;
	var $usergroupids		=	null;
	var $useraccessgroupid	=	null;
	var $sortfields			=	null;
	var $filterfields		=	null;
	var $ordering			=	null;
	var $col1title			=	null;
	var $col1enabled		=	null;
	var $col1fields			=	null;
	var $col1captions		=	null;
	var $col2title			=	null;
	var $col2enabled		=	null;
	var $col2fields			=	null;
	var $col2captions		=	null;
	var $col3title			=	null;
	var $col3enabled		=	null;
	var $col3fields			=	null;
	var $col3captions		=	null;
	var $col4title			=	null;
	var $col4enabled		=	null;
	var $col4fields			=	null;
	var $col4captions		=	null;
	/** @var text */
	var $params				=	null;

    /**
    * Constructor
    * @param  CBdatabase  $db   A database connector object
    */
	function moscomprofilerLists( &$db ) {
	
		$this->comprofilerDBTable( '#__comprofiler_lists', 'listid', $db );
	
	} //end func

	function store( $listid=0, $updateNulls=false) {
			global $_CB_database, $_POST;
	
		if ( ( ! isset( $_POST['listid'] ) ) || $_POST['listid'] == null || $_POST['listid'] == '' ) {
			$this->listid = (int) $listid;
		} else {
			$this->listid = (int) cbGetParam( $_POST, 'listid', 0 );
		}
		$sql="SELECT COUNT(*) FROM #__comprofiler_lists WHERE listid = ".  (int) $this->listid;
		$_CB_database->SetQuery($sql);
		$total = $_CB_database->LoadResult();
		if($this->default==1) {
			$sql="UPDATE #__comprofiler_lists SET `default` = 0";
			$_CB_database->SetQuery($sql);
			$_CB_database->query();
		}
		if ( $total > 0 ) {
			// existing record
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
			
		} else {
			// new record
			$sql="SELECT MAX(ordering) FROM #__comprofiler_lists";
			$_CB_database->SetQuery($sql);
			$max = $_CB_database->LoadResult();
			$this->ordering=$max+1;
			$this->listid = null;
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if ( !$ret ) {
			$this->_error = get_class( $this )."::store failed <br />" . $this->_db->getErrorMsg();
			return false;
		} else {
			return true;
		}
	}


} //end class
class moscomprofilerFields extends comprofilerDBTable {

   var $fieldid			= null;
   var $name			= null;
   var $tablecolumns	= null;
   var $table			= null;
   var $title			= null;
   var $description		= null;
   var $type			= null;
   var $maxlength		= null;
   var $size			= null;
   var $required		= null;
   var $tabid			= null;
   var $ordering		= null;
   var $cols			= null;
   var $rows			= null;
   var $value			= null;
   var $default			= null;
   var $published		= null;
   var $registration	= null;
   var $profile			= null;
   var $displaytitle	= null;
   var $readonly		= null;
   var $searchable		= null;
   var $calculated		= null;
   var $sys				= null;
   var $pluginid		= null;
   /**
    * Field's params: once loaded properly contains:
    * @var cbParamsBase
    */
   var $params			= null;

    /**
    * Constructor
    * @param  CBdatabase  $db   A database connector object
    */
	function moscomprofilerFields( &$db ) {
		$this->comprofilerDBTable( '#__comprofiler_fields', 'fieldid', $db );
	}
	
	function store( $fieldid = 0, $updateNulls = false ) {
			global $_CB_database;

			$this->fieldid			=	$fieldid;

			$fieldHandler			=	new cbFieldHandler();

			$sql					=	'SELECT COUNT(*) FROM #__comprofiler_fields WHERE fieldid = ' . (int) $this->fieldid;
			$_CB_database->SetQuery( $sql );
			$total					=	$_CB_database->LoadResult();

			if ( $total > 0 ) {
				// existing record:
				$ret				=	$this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );	// escapes values
				if ( $ret ) {
					$ret			=	$fieldHandler->adaptSQL( $this );
				}
			} else {
				// new record:

				$sql				=	'SELECT COUNT(*) FROM #__comprofiler_fields WHERE name = ' . $_CB_database->Quote( $this->name );
				$_CB_database->SetQuery($sql);
				if ( $_CB_database->LoadResult() > 0 ) {
					$this->_error	=	"The field name ".$this->name." is already in use!";
					return false;
				}				
				$sql				=	'SELECT MAX(ordering) FROM #__comprofiler_fields WHERE tabid = ' . (int) $this->tabid;
				$_CB_database->SetQuery( $sql );
				$max				=	$_CB_database->LoadResult();
				$this->ordering		=	$max + 1;
				$this->fieldid		=	null;
				$this->table		=	$fieldHandler->getMainTable( $this );
				$this->tablecolumns	=	implode( ',', $fieldHandler->getMainTableColumns( $this ) );

				$ret				=	$fieldHandler->adaptSQL( $this );

				if ($ret) {
					$ret			=	$this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );		// do inserObject last to keep insertId intact
				}
			}
			if ( ! $ret ) {
				$this->_error		=	get_class( $this ) . "::store failed: " . addslashes( str_replace( "\n", '\n', $this->_error . ' ' . $this->_db->getErrorMsg() ) );
				return false;
			} else {
				return true;
			}
	}
	/**
	*	Delete method for fields deleting also fieldvalues, but not the data column in the comprofiler table.
	*	For that, deleteColumn() method must be called separately.
	*
	*	@param id of row to delete
	*	@return true if successful otherwise returns and error message
	*/
	function deleteDataDescr( $oid = null ) {
		$fieldHandler				=	new cbFieldHandler();
		$ret						=	$fieldHandler->adaptSQL( $this, 'drop' );
		if ( $ret ) {
			$ret					=	$this->delete( $oid );
		}
		return $ret;
	}
	/**
	*	Delete method for fields deleting also fieldvalues, but not the data column in the comprofiler table.
	*	For that, deleteDataDescr() method must be called instead.
	*
	*	@param id of row to delete
	*	@return true if successful otherwise returns and error message
	*/
	function delete( $oid = null ) {
		$k							=	$this->_tbl_key;
		if ( $oid ) {
			$this->$k				=	(int) $oid;
		}

		$result					=	true;
		
		//Find all fieldValues related to the field
		$this->_db->setQuery( "SELECT `fieldvalueid` FROM #__comprofiler_field_values WHERE `fieldid`=" . (int) $this->$k );
		$fieldvalues				=	$this->_db->loadObjectList();
		$rowFieldValues				=	new moscomprofilerFieldValues($this->_db);
		if ( count( $fieldvalues ) > 0 ) {
			//delete each field value related to a field
			foreach ( $fieldvalues AS $fieldvalue ) {
				$result				=	$rowFieldValues->delete( $fieldvalue->fieldvalueid ) && $result;
			}
		}
		//Now delete the field itself without deleting the user data, preserving it for reinstall
		//$this->deleteColumn( $this->table, $this->name );	// this would delete the user data
		$result						=	parent::delete( $this->$k ) && $result;
		return $result;
	}
	/**
	 * Returns the database columns used by the field
	 *
	 * @return array    Names of columns
	 */
	function getTableColumns() {
		if ( $this->tablecolumns !== null ) {
			if ( $this->tablecolumns === '' ) {
				return array();
			} else {
				return explode( ',', $this->tablecolumns );
			}
		} else {
			return array( $this->name );		// pre-CB 1.2 database structure support
		}
	}
	/**
	 * OBSOLETE DO NOT USE: kept in 1.2 for compatibility reasons only
	 * @access private
	 */
	function createColumn( $table, $column, $type) {
		global $_CB_database;

		if ( ( $table == '' ) || ( $type == '' ) ) {
			return true;
		}
		$sql = "SELECT * FROM " . $_CB_database->NameQuote( $table ) . " LIMIT 1";
		$_CB_database->setQuery($sql);
		$obj = null;
		if ( ! ( $_CB_database->loadObject( $obj ) && array_key_exists( $column, $obj ) ) ) {
			$sql = "ALTER TABLE " . $_CB_database->NameQuote( $table )
				 . "\n ADD " . $_CB_database->NameQuote( $column ) . " " . $type;		// don't escape type, as default text values are quoted
			$_CB_database->SetQuery( $sql );
			$ret = $_CB_database->query();
			if ( !$ret ) {
				$this->_error .= get_class( $this )."::createColumn failed <br />" . $this->_db->getErrorMsg();
				return false;
			} else {
				return true;
			}
		} else {
			return $this->changeColumn( $table, $column, $type);
		}
	}
	/**
	 * OBSOLETE DO NOT USE: kept in 1.2 for compatibility reasons only
	 * @access private
	 */
	function changeColumn( $table, $column, $type, $oldColName = null ) {
		global $_CB_database;

		if ( ( $table == '' ) || ( $type == '' ) ) {
			return true;
		}
		if ( $oldColName === null ) {
			$oldColName		=	$column;
		}
		$sql = "ALTER TABLE " . $_CB_database->NameQuote( $table )
				. "\n CHANGE " . $_CB_database->NameQuote( $oldColName )
				. " " . $_CB_database->NameQuote( $column )
				. " " . $type;														// don't escape type, as default text values are quoted
		$_CB_database->SetQuery( $sql );
		$ret = $_CB_database->query();
		if ( !$ret ) {
			$this->_error .= get_class( $this )."::changeColumn failed <br />" . $this->_db->getErrorMsg();
			return false;
		} else {
			return true;
		}
	}
	/**
	 * OBSOLETE DO NOT USE: kept in 1.2 for compatibility reasons only
	 * @access private
	 */
	function deleteColumn( $table, $column) {
			global $_CB_database;
		$sql = "ALTER TABLE " . $_CB_database->NameQuote( $table)
				. "\n DROP " .  $_CB_database->NameQuote( $column)
				;
		$_CB_database->SetQuery($sql);
		$ret = $_CB_database->query();
		if ( !$ret ) {
			$this->_error .= get_class( $this )."::deleteColumn failed <br />" . $this->_db->getErrorMsg();
			return false;
		} else {
			return true;
		}
	}
} //end class

class moscomprofilerTabs extends comprofilerDBTable {

   var $tabid				=	null;
   var $title				=	null;
   var $description			=	null;
   var $ordering			=	null;
   var $ordering_register	=	null;
   var $width				=	null;
   var $enabled				=	null;
   var $pluginclass			=	null;
   var $pluginid			=	null;
   var $fields				=	null;
   var $params				=	null;
   /**	@var int  system tab: >=1: from comprofiler core: can't be deleted. ==2: always enabled. ==3: collecting element (menu+status): rendered at end. */
   var $sys					=	null;
   var $displaytype			=	null;
   var $position			=	null;
   var $useraccessgroupid	=	null;

    /**
    * Constructor
    * @param  CBdatabase  $db   A database connector object
    */
	function moscomprofilerTabs( &$db ) {
	
		$this->comprofilerDBTable( '#__comprofiler_tabs', 'tabid', $db );
	
	} //end func

	function store( $tabid, $updateNulls=false) {
		global $_CB_database, $_POST;
	
		if ( ( ! isset( $_POST['tabid'] ) ) || $_POST['tabid'] == null || $_POST['tabid'] == '' ) {
			$this->tabid = (int) $tabid;
		} else {
			$this->tabid = (int) cbGetParam( $_POST, 'tabid', 0 );
		}
		$sql = "SELECT COUNT(*) FROM #__comprofiler_tabs WHERE tabid = ". (int) $this->tabid;
		$_CB_database->SetQuery($sql);
		$total = $_CB_database->LoadResult();
		if ( $total > 0 ) {
			// existing record
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );	// escapes values!
			
		} else {
			$sql = "SELECT MAX(ordering) FROM #__comprofiler_tabs";
			$_CB_database->SetQuery($sql);
			$max = $_CB_database->LoadResult();
			$this->ordering = $max + 1;
			// new record
			$this->tabid = null;
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
			
		}
		if ( !$ret ) {
			$this->_error = get_class( $this )."::store failed <br />" . $this->_db->getErrorMsg();
			return false;
		} else {
			return true;
		}
	}
} //end class
class moscomprofilerFieldValues extends comprofilerDBTable {
   var $fieldvalueid	=	null;
   var $fieldid			=	null;
   var $fieldtitle		=	null;
   var $ordering		=	null;
   var $sys				=	null;

    /**
    * Constructor
    *
    * @param  CBdatabase  $db   A database connector object
    */

	function moscomprofilerFieldValues( &$db ) {
	
		$this->comprofilerDBTable( '#__comprofiler_field_values', 'fieldvalueid', $db );
	
	} //end func

	function store( $fieldvalueid=0, $updateNulls=false) {
			global $_CB_database, $_POST;
	
		if ( ( ! isset( $_POST['fieldvalueid'] ) ) || $_POST['fieldvalueid'] == null || $_POST['fieldvalueid'] == '' ) {
			$this->fieldvalueid = (int) $fieldvalueid;
		} else {
			$this->fieldvalueid = (int) cbGetParam( $_POST, 'fieldvalueid', 0 );
		}
		$sql = "SELECT COUNT(*) FROM #__comprofiler_field_values WHERE fieldvalueid = " . (int) $this->fieldvalueid;
		$_CB_database->SetQuery($sql);
		$total = $_CB_database->LoadResult();
		if ( $total > 0 ) {
			// existing record
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
			
		} else {
			// new record
			$this->fieldvalueid = null;
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if ( !$ret) {
			$this->_error = get_class( $this )."::store failed <br />" . $this->_db->getErrorMsg();
			return false;
		} else {
			
			return true;
		}
	}


} //end class
class moscomprofiler extends comprofilerDBTable {

	// IMPORTANT: ALL VARIABLES HERE MUST BE NULL in order to not be updated if not set.
	var $id						=	null;
	var $user_id				=	null;
	var $firstname				=	null;
	var $middlename				=	null;
	var $lastname				=	null;
	var $hits					=	null;
	var $message_last_sent		=	null;
	var $message_number_sent	=	null;
	var $avatar					=	null;
	var $avatarapproved			=	null;
	var $approved				=	null;
	var $confirmed				=	null;
	var $lastupdate				=	null;
	var $registeripaddr			=	null;
	var $cbactivation			=	null;
	var $banned					=	null;
	var $banneddate				=	null;
	var $unbanneddate			=	null;
	var $bannedby				=	null;
	var $unbannedby				=	null;
	var $bannedreason			=	null;
	var $acceptedterms			=	null;

    /**
    * Constructor
    *
    * @param  CBdatabase  $db   A database connector object
    */
	function moscomprofiler( &$db ) {
		$this->comprofilerDBTable( '#__comprofiler', 'id', $db );
	}
	/**
	* Inserts a new row in the database table
	*
	* @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	* @return boolean                TRUE if successful otherwise FALSE
	*/
	function storeNew( $updateNulls = false ) {
		$ok					=	$this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		if ( ! $ok ) {
			$this->_error	=	strtolower(get_class($this))."::storeNew failed: " . $this->_db->getErrorMsg();
		}
		return $ok;
	}
	/**
	 * OBSOLETE AND BUGGY: DO NOT USE
	 * KEPT FOR 3PD COMPATIBILITY ONLY
	 */
	function storeExtras( $id=0, $updateNulls=false) {
		global $_CB_database, $_POST;
	
		if ( ( ! isset( $_POST['id'] ) ) || $_POST['id'] == null || $_POST['id'] == '' ) {
			$this->id = (int) $id;
		} else {
			$this->id = (int) cbGetParam( $_POST, 'id', 0 );
		}
		$sql = "SELECT count(*) FROM #__comprofiler WHERE id = ". (int) $this->id;
		$_CB_database->SetQuery($sql);
		$total = $_CB_database->LoadResult();
		if ( $total > 0 ) {
			// existing record
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );	// escapes values
			
		} else {
			// new record
			$sql = "SELECT MAX(id) FROM #__users";
			$_CB_database->SetQuery($sql);
			$last_id		= $_CB_database->LoadResult();
			$this->id		= $last_id;
			$this->user_id	= $last_id;
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );					// escapes values
			
		}
		if ( !$ret ) {
			$this->_error = get_class( $this )."::store failed <br />" . $this->_db->getErrorMsg();
			return false;
		} else {
			return true;
		}
	}
	/**
	*	Merges two object into one by reference ( avoids "_db", "_tbl", "_tbl_key", and $o2->($o2->_tbl_key) )
	*   @static function:
	*	@param object $o1 first object
	*	@param object $o2 second object
	*	@return object
	*/
	function & dbObjectsMerge( &$o1, &$o2 ) {
		$r = new stdClass();
		
		$class_vars = get_object_vars($o1);
		foreach ($class_vars as $name => $value) {
			if (($name != "_db") and ($name != "_tbl") and ($name != "_tbl_key")) {
				$r->$name =& $o1->$name;
			}
		}
		$class_vars = get_object_vars($o2);
		$k = $o2->_tbl_key;
		foreach ($class_vars as $name => $value) {
			if (($name != $k) and ($name != "_db") and ($name != "_tbl") and ($name != "_tbl_key")) {
				$r->$name =& $o2->$name;
			}
		}
		return $r;
	}

} // class moscomprofiler
/**
 * WIP: EXPERIMENTAL: use at your own risk, no backwards compatibility guarrantee
 *
 * Class for single cb User tables object
 *
 */
class moscomprofilerUser extends moscomprofiler  {
	/** @var string */
	var $name					=	null;
	/** @var string */
	var $username				=	null;
	/** @var string */
	var $email					=	null;
	/** @var string */
	var $password				=	null;
	/** @var string */
	var $usertype				=	null;
	/** @var int */
	var $block					=	null;
	/** @var int */
	var $sendEmail				=	null;
	/** @var int */
	var $gid					=	null;
	/** @var datetime */
	var $registerDate			=	null;
	/** @var datetime */
	var $lastvisitDate			=	null;
	/** @var string */
	var $activation				=	null;
	/** @var string */
	var $params					=	null;

	var $_cmsUserTable			=	'#__users';
	var $_cmsUserTableKey		=	'id';
	var $_cmsUserTableUsername	=	'username';
	var $_cmsUserTableEmail	=	'email';
	var $_cmsUserTableGid		=	'gid';
	
	/** CMS User object
	 *  @var mosUser */
	var $_cmsUser				=	null;
	/** CB user table row
	 *  @var moscomprofiler */
	var $_comprofilerUser		=	null;
	/** CB Tabs
	 *  @var cbTabs */
	var $_cbTabs				=	null;

	var $_nonComprofilerVars		 =	array( 'name', 'username', 'email', 'password', 'params'	, 	'usertype', 'block', 'sendEmail', 'gid', 'registerDate', 'activation', 'lastvisitDate' );
	var $_frontendNonComprofilerVars =	array( 'name', 'username', 'email', 'password', 'params' );
	/**
	 * Constructor
	 *
	 * @param  CBdatabase $db
	 * @return moscomprofilerUser
	 */
	function moscomprofilerUser( &$db ) {
		parent::moscomprofiler( $db );
	}
	/**
	*	Loads user from database
	*
	*	@param  int  $oid  [optional] User id
	*	@return boolean    TRUE: success, FALSE: error in database access
	*/
	function load( $oid = null ) {
		$k						=	$this->_tbl_key;
		
		if ($oid !== null) {
			$this->$k			=	(int) $oid;
		}
		
		$oid					=	$this->$k;
		
		if ( $oid === null ) {
			return false;
		}
		//BB fix : resets default values to all object variables, because NULL SQL fields do not overide existing variables !
		//Note: Prior to PHP 4.2.0, Uninitialized class variables will not be reported by get_class_vars().
		$class_vars				=	get_class_vars(get_class($this));
		foreach ( $class_vars as $name => $value ) {
			if (($name != $k) and ($name != "_db") and ($name != "_tbl") and ($name != "_tbl_key")) {
				$this->$name	=	$value;
			}
		}
		$this->reset();
		//end of BB fix.
	/*
		$query = "SELECT *"
		. "\n FROM " . $this->_tbl . " c, " . $this->_cmsUserTable . " u"
		. "\n WHERE c." . $this->_tbl_key . " = u." . $this->_cmsUserTableKey
		. " AND c." . $this->_tbl_key . " = " . (int) $oid
		;
		$this->_db->setQuery( $query );
		
		// the following is needed for being able to edit a backend user in CB from CMS which is not yet synchronized with CB:
	*/
		$query					=	'SELECT *'
		. "\n FROM " . $this->_cmsUserTable . ' AS u'
		. "\n LEFT JOIN " . $this->_tbl . ' AS c ON c.' . $this->_tbl_key . ' = u.' . $this->_cmsUserTableKey
		. " WHERE u." . $this->_cmsUserTableKey . ' = ' . (int) $oid
		;
		$this->_db->setQuery( $query );
		
		$arr					=	$this->_db->loadAssoc( );

		if ( $arr === null ) {
			$query				=	'SELECT *'
			. "\n FROM " . $this->_tbl . ' AS c'
			. "\n LEFT JOIN " . $this->_cmsUserTable . ' AS u ON c.' . $this->_tbl_key . ' = u.' . $this->_cmsUserTableKey
			. " WHERE c." . $this->_tbl_key . ' = ' . (int) $oid
			;
			$this->_db->setQuery( $query );
			
			$arr				=	$this->_db->loadAssoc( );
		}
		if ( $arr !== null ) {
			foreach ( $arr as $kk => $v ) {
				$this->$kk		=	$v;
			}
			// in case the left join is null, the second loaded id will be NULL and override id:
			$this->$k			=	(int) $oid;
			if ( checkJversion() == 0 ) {
				if ( checkJversion( 'dev_level' ) < 11 ) {
					// revert effect of _cbMakeHtmlSafe on user save in older joomla/mambo versions:
					$this->name		=	unHtmlspecialchars( $this->name );
				}
			}
			return true;
		} else {
			return false;
		}
	}
	/**
	*	Loads user username from database
	*
	*	@param  string   $username
	*	@return boolean    TRUE: success, FALSE: error in database access
	*/
	function loadByUsername( $username ) {
		return $this->_loadBy_field( $username, $this->_cmsUserTableUsername );
	}
	/**
	*	Loads user username from database
	*
	*	@param  string   $username
	*	@return boolean    TRUE: success, FALSE: error in database access
	*/
	function loadByEmail( $username ) {
		return $this->_loadBy_field( $username, $this->_cmsUserTableEmail );
	}
	/**
	*	Loads first user from database according to a given field
	*	@access private
	*
	*	@param  string   $fieldValue
	*	@param  string   $fieldName   Name of database field
	*	@return boolean    TRUE: success, FALSE: error in database access
	*/
	function _loadBy_field( $fieldValue, $fieldName ) {
		if ( $fieldValue == null ) {
			return false;
		}
		//BB fix : resets default values to all object variables, because NULL SQL fields do not overide existing variables !
		//Note: Prior to PHP 4.2.0, Uninitialized class variables will not be reported by get_class_vars().
		$class_vars				=	get_class_vars(get_class($this));
		foreach ($class_vars as $name => $value) {
			if ( ($name != $this->_tbl_key) and ($name != "_db") and ($name != "_tbl") and ($name != "_tbl_key") ) {
				$this->$name	=	$value;
			}
		}
		$this->reset();
		//end of BB fix.
		$query					=	'SELECT *'
		. "\n FROM " . $this->_cmsUserTable . ' AS u'
		. "\n LEFT JOIN " . $this->_tbl . ' AS c ON c.' . $this->_tbl_key . ' = u.' . $this->_cmsUserTableKey
		. " WHERE u." . $this->_db->NameQuote( $fieldName ) . ' = ' . $this->_db->Quote( $fieldValue )
		. " LIMIT 1"
		;
		$this->_db->setQuery( $query );
		
		$arr					=	$this->_db->loadAssoc( );

		if ( $arr ) {
			foreach ( $arr as $k => $v ) {
				$this->$k		=	$v;
			}
			return true;
		} else {
			return false;
		}
	}
	function bindSafely( &$array, $ui, $reason, &$oldUserComplete ) {
		global $_CB_framework, $ueConfig, $_PLUGINS;

		// Some basic sanitizations and securitizations: usertype will be re-computed based on gid in store()

		$this->id						=	(int) $this->id;
		$this->gid						=	(int) $this->gid;

		if ( ! $this->gid ) {
			$this->gid					=	null;
		}
		if ( $ui == 1 ) {
			if ( $this->id ) {
				// Front-end edit user: no changes in gid/usertype and confirmed/approved states
				$this->gid				=	(int) $oldUserComplete->gid;
				$this->usertype			=	$oldUserComplete->usertype;
				$this->block			=	(int) $oldUserComplete->block;
				$this->sendEmail		=	(int) $oldUserComplete->sendEmail;
				$this->confirmed		=	(int) $oldUserComplete->confirmed;
				$this->approved			=	(int) $oldUserComplete->approved;
			} else {
				// Front-end user registration: handle this here, so it is available to all plugins:
				$this->usertype			=	$_CB_framework->getCfg( 'new_usertype' );
				$this->gid				=	$_CB_framework->acl->get_group_id( $this->usertype, 'ARO' );

				if ( $ueConfig['reg_admin_approval'] == 0) {
					$this->approved		=	1;
				} else {
					$this->approved		=	0;
					$this->block		=	1;
				} 
				if ( $ueConfig['reg_confirmation'] == 0 ) {
					$this->confirmed	=	1;
				} else {
					$this->confirmed	=	0;
					$this->block		=	1;
				}
				if ( ( $this->confirmed == 1 ) && ( $this->approved == 1 ) ) {
					$this->block		=	0;
				} else {
					$this->block		=	1;
				}
				$this->sendEmail		=	0;

			}
			// Nb.: Backend user edit and new user are handled in core plugin CBfield_userparams field handler class
		}

		// By default, don't touch the hashed password, unless a new password is set by the saveTabsContents binding:
		$this->password					=	null;

		$this->_original_email			=	$this->email;						// needed for checkSafely()

		// Process the fields in form by CB field plugins:
	
		$_PLUGINS->loadPluginGroup('user');
	
		$this->_cbTabs					=	new cbTabs( 0, $ui, null, false );
		$this->_cbTabs->saveTabsContents( $this, $array, $reason );
		$errors							=	$_PLUGINS->getErrorMSG( false );
		if ( count( $errors ) > 0 ) {
			$this->_error				=	$errors;
			return false;
		}

		// Now do CMS-specific stuff, specially bugs-workarounds:
	
		$postCopy						=	array();
		if ( $ui == 1 ) {
			$vars						=	$this->_frontendNonComprofilerVars;
		} else {
			$vars						=	$this->_nonComprofilerVars;
		}
		foreach ( $vars as $k ) {
			if ( isset( $this->$k ) ) {
				$postCopy[$k]			=	$this->$k;
			}
		}
		if ( isset( $postCopy['password'] ) ) {
			$postCopy['verifyPass']		=	$postCopy['password'];			// Mambo and Joomla 1.0 has it in password2 and checks it in bind() !
			$postCopy['password2']		=	$postCopy['password'];			// Joomla 1.5 has it in password2 and checks it in bind() !
		}

		$this->_mapUsers();
		$row							=&	$this->_cmsUser;

		$pwd							=	$this->password;						// maybe cleartext at that stage.
		if ( $pwd == '' ) {
			$pwd						=	null;									// empty: don't update/change
			$this->password				=	null;
		}

		$rowBindResult					=	$row->bind( $postCopy );				// in Joomla 1.5, this modifies $postCopy and hashes password !
		if ( ! $rowBindResult ) {
			if ( checkJversion() == 1 ) {
				$this->_error			=	$row->getErrors();
				foreach ( array_keys( $this->_error ) as $ek ) {
					$this->_error[$ek]	=	stripslashes( $this->_error[$ek] );
				}
			} else {
				$this->_error			=	array( stripslashes( $row->getError() ) );
			}
			return false;
		}


		// Finally, emulate a pre-joomla 1.0.11 bug where jos_users was wtih htmlspecialchars ! :
		if ( checkJversion() == 0 ) {
			if ( checkJversion( 'dev_level' ) < 11 ) {
				_cbMakeHtmlSafe($row);
			}
		}
		$row->password					=	$pwd;		// J1.0: no htmlspecialchars on password, J1.5: restore cleartext password at this stage.
		return true;
	}

	function checkSafely() {
		global $_CB_framework;

		if ( $this->_cmsUser === null ) {
			$this->_mapUsers();
		}
		$row							=&	$this->_cmsUser;

		if ( is_callable( array( $row, 'check' ) ) ) {

			// fix a joomla 1.0 bug preventing from saving profile without changing email if site switched from uniqueemails = 0 to = 1 and duplicates existed
			$original_uniqueemail		=	$_CB_framework->getCfg( 'uniquemail' );
			if ( $_CB_framework->getCfg( 'uniquemail' ) && ( $row->email == $this->_original_email ) ) {
				global $mosConfig_uniquemail;	// this is voluntarily a MAMBO/JOOMLA 1.0 GLOBAL TO FIX A BUG
				$mosConfig_uniquemail	=	0;	// this is voluntarily a MAMBO/JOOMLA 1.0 GLOBAL TO FIX A BUG
			}

			$rowCheckResult				=	$row->check();

			if ( $original_uniqueemail && ( $row->email == $this->_original_email ) ) {
				$mosConfig_uniquemail	=	$original_uniqueemail;	// this is voluntarily a MAMBO/JOOMLA 1.0 GLOBAL TO FIX A BUG
			}

			if ( ! $rowCheckResult ) {
				$this->_error			=	( checkJversion() == 1 ? stripslashes( implode( '<br />', $row->getErrors() ) ) : stripslashes( $row->getError() ) );
				return false;
			}
		}
		return true;
	}
	/**
	* If table key (id) is NULL : inserts new rows
	* otherwise updates existing row in the database tables
	*
	* Can be overridden or overloaded by the child classes
	*
	* @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	* @return boolean                TRUE if successful otherwise FALSE
	*/
	function store( $updateNulls = false ) {
		global $_CB_framework, $_CB_database, $ueConfig;

		$isNew										=	( $this->id == 0 );

		$oldUsername								=	null;
		$oldGid										=	null;
		$oldBlock									=	null;

		if ( ! $isNew ) {
			// get actual username to update sessions in case:
			$sql			=	'SELECT ' . $_CB_database->NameQuote( $this->_cmsUserTableUsername )
							.	', '	. $_CB_database->NameQuote( $this->_cmsUserTableGid )
							.	', '	. $_CB_database->NameQuote( 'block' )
							.	' FROM ' . $_CB_database->NameQuote( $this->_cmsUserTable ) . ' WHERE ' . $_CB_database->NameQuote( $this->_cmsUserTableKey ) . ' = ' . (int) $this->user_id;
			$_CB_database->setQuery( $sql );
			$oldEntry								=	null;
			if ( $_CB_database->loadObject( $oldEntry ) ) {
				$oldUsername						=	$oldEntry->username;
				$oldGid								=	$oldEntry->gid;
				$oldBlock							=	$oldEntry->block;
			}
		}

		// insure usertype is in sync with gid:
/*
 * This could be a better method:
		if ( checkJversion() == 1 ) {
			$gdataArray								=	$_CB_framework->acl->get_group_data( (int) $this->gid, 'ARO' );
			if ( $gdataArray ) {
				$this->usertype						=	$gdataArray[3];
			} else {
				user_error( sprintf( 'comprofilerUser::store: gacl:get_group_data: for user_id %d, name of group_id %d not found in acl groups table.', $this->id, $this->gid ), E_USER_WARNING );
				$this->usertype						=	'Registered';
			}
		} else {
			$this->usertype							=	$_CB_framework->acl->get_group_name( (int) $gid, 'ARO' );
		}
*/
		if ( checkJversion() == 1 ) {
			$query									= 'SELECT name'
													. "\n FROM #__core_acl_aro_groups"
													. "\n WHERE id = " . (int) $this->gid
													;
		} else {
			$query									= 'SELECT name'
													. "\n FROM #__core_acl_aro_groups"
													. "\n WHERE group_id = " . (int) $this->gid
													;
		}
		$_CB_database->setQuery( $query );
		$this->usertype								=	$_CB_database->loadResult();

		// creates CMS and CB objects:
		$this->_mapUsers();

		// remove the previous email set in bindSafely() and needed for checkSafely():
		unset( $this->_original_email );

		// stores first into CMS to get id of user if new:
		if ( is_callable( array( $this->_cmsUser, 'store' ) ) ) {
			$result									=	$this->_cmsUser->store( $updateNulls );
			if ( ! $result ) {
				$this->_error						=	$this->_cmsUser->getError();
			}
		} else {
			$result									=	$this->_cmsUser->save();	// Joomla 1.5 native
			if ( ! $result ) {
				$this->_error						=	$this->_cmsUser->getError();
				if ( class_exists( 'JText' ) ) {
					$this->_error					=	JText::_( $this->_error );
				}
			}
		}
		if ( $result ) {
			// synchronize id and user_id:
			if ( $isNew ) {
				if ( $this->_cmsUser->id == 0 ) {
					// this is only for mambo 4.5.0 backwards compatibility. 4.5.2.3 $row->store() updates id on insert
					$sql			=	'SELECT ' . $_CB_database->NameQuote( $this->_cmsUserTableKey ) . ' FROM ' . $_CB_database->NameQuote( $this->_cmsUserTable ) . ' WHERE ' . $_CB_database->NameQuote( $this->_cmsUserTableUsername ) . ' = ' . $_CB_database->Quote( $this->username);
					$_CB_database->setQuery( $sql );
					$this->_cmsUser->id				=	(int) $_CB_database->loadResult();
				}
				$this->id							=	$this->_cmsUser->id;
				$this->_comprofilerUser->id		=	$this->_cmsUser->id;
			}

			if ( ( $this->confirmed == 0 ) && ( $this->cbactivation == '' ) && ( $ueConfig['reg_confirmation'] != 0 ) ) {
					$randomHash						=	md5( cbMakeRandomString() );
					$scrambleSeed					=	(int) hexdec(substr( md5 ( $_CB_framework->getCfg( 'secret' ) . $_CB_framework->getCfg( 'db' ) ), 0, 7));
					$scrambledId					=	$scrambleSeed ^ ( (int) $this->id );
					$this->cbactivation				=	'reg' . $randomHash . sprintf( '%08x', $scrambledId );
			}

			// stores CB user into comprofiler: if new, inserts, otherwise updates:
			if ( $this->user_id == 0 ) {
				$this->user_id						=	$this->_cmsUser->id;
				$this->_comprofilerUser->user_id	=	$this->user_id;
				$result								=	$this->_comprofilerUser->storeNew( $updateNulls );
			} else {
				$result								=	$this->_comprofilerUser->store( $updateNulls );
			}
			if ( ! $result ) {
				$this->_error						=	$this->_comprofilerUser->getError();
			}
		}
		if ( $result ) {
			// update the ACL:
			if ( checkJversion() == 1 ) {
				$query							=	'SELECT a.id AS aro_id, m.group_id FROM #__core_acl_aro AS a'
												.	"\n INNER JOIN #__core_acl_groups_aro_map AS m ON m.aro_id = a.id"
												.	"\n WHERE a.value = " . (int) $this->id
												;
			} else {
				$query							=	'SELECT a.aro_id, m.group_id FROM #__core_acl_aro AS a'
												.	"\n INNER JOIN #__core_acl_groups_aro_map AS m ON m.aro_id = a.aro_id"
												.	"\n WHERE a.value = " . (int) $this->id
												;
			}
			$_CB_database->setQuery( $query );
			$aro_group							=	null;
			$result								=	$_CB_database->loadObject( $aro_group );

			if ( $result && ( $aro_group->group_id != $this->gid ) ) {
				$query							=	'UPDATE #__core_acl_groups_aro_map'
												.	"\n SET group_id = " . (int) $this->gid
												.	"\n WHERE aro_id = " . (int) $aro_group->aro_id
												;
				$_CB_database->setQuery( $query );
				$result							=	$_CB_database->query();
			}
			if ( $result && ( ! $isNew ) && ( ( $oldUsername != $this->username ) || ( $aro_group->group_id != $this->gid ) || ( $oldGid != $this->gid ) || ( ( $oldBlock == 0 ) && ( $this->block == 1 ) ) ) ) {
				// Update current sessions state if there is a change in gid or in username:
				if ( $this->block == 0 ) {
					$sessionGid			=	1;
					if ( $_CB_framework->acl->is_group_child_of( $this->usertype, 'Registered', 'ARO' ) || $_CB_framework->acl->is_group_child_of( $this->usertype, 'Public Backend', 'ARO' ) ) {
						// Authors, Editors, Publishers and Super Administrators are part of the Special Group:
						$sessionGid		=	2;
					}
					$query				=	'UPDATE #__session '
										.	"\n SET usertype = " . $_CB_database->Quote( $this->usertype )
										.	', gid = ' . (int) $sessionGid
										.	', username = ' . $_CB_database->Quote( $this->username )
										.	"\n WHERE userid = " . (int) $this->id
										;
					//TBD: here maybe jaclplus fields update if JACLplus installed....
					$_CB_database->setQuery( $query );
					$result				=	$_CB_database->query();
				} else {
					// logout user now that user login has been blocked:
					if ( $_CB_framework->myId() == $this->id ) {
						$_CB_framework->logout();
					}
					$_CB_database->setQuery( "DELETE FROM #__session WHERE userid = " . (int) $this->id );			//TBD: check if this is enough for J 1.5
					$result				=	$_CB_database->query();
				}
			}
			if ( ! $result ) {
				$this->_error					=	$_CB_database->stderr();
				return false;
			}
		}
		return $result;
	}
	/**
	 * Saves a new or existing CB+CMS user
	 * WARNINGS:
	 * - You must verify authorization of user to perform this (user checkCBpermissions() )
	 * - You must $this->load() existing user first
	 *
	 * @param  array   $array   Raw unfiltered input, typically $_POST
	 * @param  int     $ui      1 = Front-end (limitted rights), 2 = Backend (almost unlimitted), 0 = automated (full)
	 * @param  string  $reason  'edit' or 'register'
	 * @return boolean
	 */
	function saveSafely( &$array, $ui, $reason ) {
		global $_CB_framework, $_CB_database, $ueConfig, $_PLUGINS;

		// Get current user state and store it into $oldUserComplete:
	
		$oldUserComplete						=	new moscomprofilerUser( $this->_db );
		foreach ( array_keys( get_object_vars( $this ) ) as $k ) {
			if( substr( $k, 0, 1 ) != '_' ) {		// ignore internal vars
				$oldUserComplete->$k			=	$this->$k;
			}
		}


		// 1) Process and validate the fields in form by CB field plugins:
		// 2) Bind the fields to CMS User:
		$bindResults							=	$this->bindSafely( $array, $ui, $reason, $oldUserComplete );

		if ( $bindResults ) {
			// During bindSafely, in saveTabContents, the validations have already taken place, for mandatory fields.
			if ( ( $this->name == '' ) && ( $this->username == '' ) && ( $this->email != '' ) ) {
				$this->username						=	$this->email;
				$this->_cmsUser->username			=	$this->username;
			}
			// Checks that name is set. If not, uses the username as name, as Mambo/Joola mosUser::store() uses name for ACL
			// and ACL bugs with no name.
			if ( $this->name == '' ) {
				$this->name							=	$this->username;
				$this->_cmsUser->name				=	$this->name;
			} elseif ( $this->username == '' ) {
				$this->username						=	$this->name;
				$this->_cmsUser->username			=	$this->username;
			}

			if ( ! $this->checkSafely() ) {
				$bindResults					=	false;
			}
		}	

		// For new registrations or backend user creations, set registration date and password if neeeded:
		$isNew									=	( ! $this->id );
		$newCBuser								=	( $oldUserComplete->user_id == null );
	
		if ( $isNew ) {
			$this->registerDate					=	date('Y-m-d H:i:s', $_CB_framework->now() );
		}

		if ( $bindResults ) {
			if ( $isNew ) {
				if ( $this->password == null ) {
					$this->password				=	cbMakeRandomString( 10, true );
					$ueConfig['emailpass']		=	1;		// set this global to 1 to force password to be sent to new users.
				}
			}

			// In backend only: if group has been changed and where original group was a Super Admin: check if there is at least a super-admin left:
			if ( $ui == 2 ) {
				$myGid							=	userGID( $_CB_framework->myId() );
				if ( ! $isNew ) {
					if ( $this->gid != $oldUserComplete->gid ) {
						if ( $oldUserComplete->gid == 25 ) {						
							// count number of active super admins
							$query				=	'SELECT COUNT( id )'
												.	"\n FROM #__users"
												.	"\n WHERE gid = 25"
												.	"\n AND block = 0"
												;
							$_CB_database->setQuery( $query );
							$count				=	$_CB_database->loadResult();

							if ( $count <= 1 ) {
								// disallow change if only one Super Admin exists
								$this->_error	=	'You cannot change this users Group as it is the only active Super Administrator for your site';
								return false;
							}
						}

						$user_group				=	strtolower( $_CB_framework->acl->get_group_name( $oldUserComplete->gid, 'ARO' ) );

						if ( ( $user_group == 'super administrator' && $myGid != 25 ) ) {
							// disallow change of super-Admin by non-super admin
							$this->_error		=	'You cannot change this users Group as you are not a Super Administrator for your site';
								return false;
						} elseif ( $this->id == $_CB_framework->myId() && $myGid == 25 ) {
							// CB-specific: disallow change of own Super Admin group:
							$this->_error		=	'You cannot change your own Super Administrator status for your site';
								return false;
						} else if ( $myGid == 24 && $oldUserComplete->gid == 24 ) {
							// disallow change of super-Admin by non-super admin
							$this->_error		=	'You cannot change the Group of another Administrator as you are not a Super Administrator for your site';
								return false;
						}	// ensure user can't add group higher than themselves done below
					}

				}
				// Security check to avoid creating/editing user to higher level than himself: CB response to artf4529.
				if ( ! in_array( $this->gid, getChildGIDS( $myGid ) ) ) {
					$this->_error				=	'illegal attempt to set user at higher level than allowed !';
					return false;
				}

			}

		}

		if ( $reason == 'edit' ) {
			if ( $ui == 1 ) {
				$_PLUGINS->trigger( 'onBeforeUserUpdate', array( &$this, &$this, &$oldUserComplete, &$oldUserComplete ) );
			} elseif ( $ui == 2 ) {
				if ( $isNew || $newCBuser ) {
					$_PLUGINS->trigger( 'onBeforeNewUser', array( &$this, &$this, false ) );
				} else {
					$_PLUGINS->trigger( 'onBeforeUpdateUser', array( &$this, &$this, &$oldUserComplete ) );
				}
			}
		} elseif ( $reason == 'register' ) {
			$_PLUGINS->trigger( 'onBeforeUserRegistration', array( &$this, &$this ) );
		}
		$beforeResult							=	! $_PLUGINS->is_errors();
		if ( ! $beforeResult ) {
			$this->_error						=	$_PLUGINS->getErrorMSG( false );			// $_PLUGIN collects all error messages, incl. previous ones.
		}

		// Saves tab plugins:
	
		// on edits, user params and block/email/approved/confirmed are done in cb.core predefined fields.
		// So now calls this and more (CBtabs are already created in $this->bindSafely() ).
		$pluginTabsResult						=	true;
		if ( $reason == 'edit' ) {
			$this->_cbTabs->savePluginTabs( $this, $array );
			$pluginTabsResult					=	! $_PLUGINS->is_errors();
			if ( ! $pluginTabsResult ) {
				$this->_error					=	$_PLUGINS->getErrorMSG( false );			// $_PLUGIN collects all error messages, incl. previous ones.
			}
		}

		if ( $bindResults && $beforeResult && $pluginTabsResult ) {
			// Hashes password for CMS storage:

			$clearTextPassword					=	$this->password;
			if ( $clearTextPassword ) {
				$hashedPassword					=	cbHashPassword( $clearTextPassword );
				$this->password					=	$hashedPassword;
			}

			// Stores user if it's a new user:

			if ( $isNew ) {
				if ( ! $this->store() ) {
					return false;
				}
			}

			// Restores cleartext password for the saveRegistrationPluginTabs:

			$this->password						=	$clearTextPassword;
		}

		if ( $reason == 'register' ) {
			if ( $bindResults && $beforeResult && $pluginTabsResult ) {
				// Sets the instance of user, to avoid reload from database, and loss of the cleartext password.
				CBuser::setUserGetCBUserInstance( $this );
			}
			// call here since we got to have a user id:
			$registerResults					=	array();
			$registerResults['tabs']			=	$this->_cbTabs->saveRegistrationPluginTabs( $this, $array );
			if ( $_PLUGINS->is_errors() ) {

				if ( $bindResults && $beforeResult && $pluginTabsResult ) {
					$plugins_error				=	$_PLUGINS->getErrorMSG( false );			// $_PLUGIN collects all error messages, incl. previous ones.
					if ( $isNew ) {
						// if it was a new user, and plugin gave error, revert the creation:
						$this->delete();
					}
					$this->_error				=	$plugins_error;
				} else {
					$this->_error				=	$_PLUGINS->getErrorMSG( false );			// $_PLUGIN collects all error messages, incl. previous ones.
				}
				$pluginTabsResult				=	false;
			}
		}

		if ( ! ( $bindResults && $beforeResult && $pluginTabsResult ) ) {
			// Normal error exit point:
			$_PLUGINS->trigger( 'onSaveUserError', array( &$this, $this->_error, $reason ) );
			if ( is_array( $this->_error ) ) {
				$this->_error						=	implode( '<br />', $this->_error );
			}
			return false;
		}

		// Stores the user (again if it's a new as the plugins might have changed the user record):
		if ( $clearTextPassword ) {
			$this->password						=	$hashedPassword;
		}
		if ( ! $this->store() ) {
			return false;
		}

		// Restores cleartext password for the onAfter and activation events:

		$this->password							=	$clearTextPassword;

		// Triggers onAfter and activateUser events:

		if ( $reason == 'edit' ) {
			if ( $ui == 1 ) {
				$_PLUGINS->trigger( 'onAfterUserUpdate', array( $this, $this, $oldUserComplete ) );
			} elseif ( $ui == 2 ) {
				if ( $isNew || $newCBuser ) {
					if ( $isNew ) {
						$ueConfig['emailpass']	=	1;		// set this global to 1 to force password to be sent to new users.
					}
					$_PLUGINS->trigger( 'onAfterNewUser', array( $this, $this, false, true ) );
					if ( $this->block == 0 && $this->approved == 1 && $this->confirmed ) {
						activateUser( $this, 2, 'NewUser', false, $isNew );
					}
				} else {
					if ( ( ! ( ( $oldUserComplete->approved == 1 || $oldUserComplete->approved == 2 ) && $oldUserComplete->confirmed ) )
						 && ($this->approved == 1 && $this->confirmed ) )
					{
						// first time a just registered and confirmed user got approved in backend through save user:
						if( isset( $ueConfig['emailpass'] ) && ( $ueConfig['emailpass'] == "1" ) && ( $this->password == '' ) ) {
							// generate the password is auto-generated and not set by the admin at this occasion:
							$pwd			=	cbMakeRandomString( 8, true );
							$this->password	=	$pwd;
							$pwd			=	cbHashPassword( $pwd );
							$_CB_database->setQuery( "UPDATE #__users SET password='" . $_CB_database->getEscaped($pwd) . "' WHERE id = " . (int) $this->id );
			    			$_CB_database->query();
						}
					}
					$_PLUGINS->trigger( 'onAfterUpdateUser', array( $this, $this, $oldUserComplete ) );
					if ( ( ! ( ( $oldUserComplete->approved == 1 || $oldUserComplete->approved == 2 ) && $oldUserComplete->confirmed ) )
						 && ($this->approved == 1 && $this->confirmed ) )
					{
						// first time a just registered and confirmed user got approved in backend through save user:
						activateUser( $this, 2, 'UpdateUser', false );
					}

				}
			}
		} elseif ( $reason == 'register' ) {
			$registerResults['after']			=	$_PLUGINS->trigger( 'onAfterUserRegistration', array( $this, $this, true ) );
			$registerResults['ok']				=	true;
			return $registerResults;
		}
		return true;
	}
	/**
	* Deletes this record (no checks)
	*
	* @param  int      $oid   Key id of row to delete (otherwise it's the one of $this)
	* @return boolean         TRUE if OK, FALSE if error
	*/
	function delete( $oid = null ) {
		$k								=	$this->_tbl_key;
		if ( $oid ) {
			$this->$k					=	(int) $oid;
		}
		$result							=	cbDeleteUser( $this->$k );
		if ( ! is_bool( result ) ) {
			$this->_error				=	$result;
			$result						=	false;
		}
		return $result;
	}

	function checkin( $oid = null ) {
		$this->_mapUsers();
		// Checks-in the row (on the CMSes where applicable):
		if ( is_callable( array( $this->_cmsUser, 'checkin' ) ) ) {
			return $this->_cmsUser->checkin();
		} else {
			return true;
		}

	}
	function _mapUsers() {
		global $_CB_framework;

		if ( $this->_cmsUser === null ) {
			$this->_cmsUser							=	$_CB_framework->_getCmsUserObject();
		}
		if ( $this->_comprofilerUser === null ) {
			$this->_comprofilerUser				=	new moscomprofiler( $this->_db );
		}

		//Note: Prior to PHP 4.2.0, Uninitialized class variables will not be reported by get_object_vars(), which is ok here
		foreach ( get_object_vars( $this ) as $name => $value ) {
			if ( $name[0] != '_' ) {
				if ( in_array( $name, $this->_nonComprofilerVars ) ) {
					$this->_cmsUser->$name			=	$value;
				} else {
					$this->_comprofilerUser->$name	=	$value;
				}
			}
		}

		$this->_cmsUser->id							=	$this->id;
		$this->_comprofilerUser->id				=	$this->id;
		$this->_comprofilerUser->user_id			=	$this->id;
	}
}
class moscomprofilerUserReport extends comprofilerDBTable {

   var $reportid			=	null;
   var $reporteduser		=	null;
   var $reportedbyuser		=	null;
   var $reportedondate		=	null;
   var $reportexplaination	=	null;
   var $reportedstatus		=	null;

	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db   A database connector object
	 */
   function moscomprofilerUserReport( &$db ) {
	
		$this->comprofilerDBTable( '#__comprofiler_userreports', 'reportid', $db );
	
	}
	/**
	 * Deletes all user reports from that user and for that user (called on user delete)
	 *
	 * @param int $userId
	 * @return boolean true if ok, false with warning on sql error
	 */
	function deleteUserReports( $userId ) {
		global $_CB_database;
		$sql='DELETE FROM #__comprofiler_userreports WHERE reporteduser = '.(int) $userId.' OR reportedbyuser = '.(int) $userId;
		$_CB_database->SetQuery($sql);
		if (!$_CB_database->query()) {
			echo 'SQL error' . $_CB_database->stderr(true);
			return false;
		}
		return true;
	}
} //end class
class moscomprofilerMember extends comprofilerDBTable {

   var $referenceid			=	null;
   var $memberid			=	null;
   var $accepted			=	null;
   var $pending				=	null;
   var $membersince			=	null;
   var $reason				=	null;
   var $description			=	null;
   var $type				=	null;

	/**
	 * Constructor
	 *
	 * @param  CBdatabase  $db   A database connector object
	 */
   function moscomprofilerMember( &$db ) {
		$this->comprofilerDBTable( '#__comprofiler_members', array( 'referenceid', 'memberid' ), $db );			//TBD: implement arrays for tablekeys.
	}
} //end class

?>
