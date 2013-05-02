<?php
/**
* Joomla/Mambo Community Builder
* @version $Id: install.comprofiler.sql.php 567 2006-11-19 10:05:00Z beat $
* @package Community Builder
* @subpackage install.comprofiler.sql.php
* @author Beat
* @copyright (C) 2008-2009 Lightning MultiCom SA, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

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
class CBdbChecker {
	/**
	 * Database
	 * @var CBdatabase
	 */
	var $_db						=	null;
	/**
	 * SQL upgrader
	 * @var CBSQLupgrader
	 */
	var $_sqlUpgrader				=	null;
	var $_silentWhenOK				=	true;

	function CBdbChecker( &$db ) {
		$this->_db					=&	$db;
		$this->_silentWhenOK		=	false;
	}
	/**
	 * Returns the database CBSimpleXMLElement
	 *
	 * @return CBSimpleXMLElement
	 */
	function & _getCbDbXml() {
		global $_CB_framework;

		static $_cb_db_xml			=	null;

		if ( $_cb_db_xml == null ) {
			$filename				=	$_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/database/database.cbcore.xml';
			if ( is_readable( $filename ) ) {
				cbimport( 'cb.xml.simplexml' );
				$_cb_db_xml			=	new CBSimpleXMLElement( file_get_contents( $filename ) );
			}
		}
		return $_cb_db_xml;
	}
	/**
	 * Returns all errors logged
	 *
	 * @param  string|boolean  $implode         False: returns full array, if string: imploding string
	 * @param  string|boolean  $detailsImplode  False: no details, otherwise imploding string
	 * @return string|array
	 */
	function getErrors( $implode = "\n", $detailsImplode = false ) {
		return $this->_sqlUpgrader->getErrors( $implode, $detailsImplode );
	}
	/**
	 * Returns all logs logged
	 *
	 * @param  string|boolean  $implode         False: returns full array, if string: imploding string
	 * @param  string|boolean  $detailsImplode  False: no details, otherwise imploding string
	 * @return string|array
	 */
	function getLogs( $implode = "\n", $detailsImplode = false ) {
		return $this->_sqlUpgrader->getLogs( $implode, $detailsImplode );		
	}
	/**
	 * Checks the comprofiler_fields table and upgrades if needed
	 * Backend-use only.
	 * @access private
	 *
	 * @param  string          $tableName
	 * @param  boolean         $upgrade    False: only check table, True: upgrades table (depending on $dryRun)
	 * @param  boolean         $dryRun     True: doesn't do the modifying queries, but lists them, False: does the job
	 * @return string|boolean              Message to display
	 */
	function checkTable( $tableName, $upgrade = false, $dryRun = false ) {
		$xml							=	$this->_getCbDbXml();
		if ( $xml !== null ) {
			$db							=&	$xml->getElementByPath( 'database' );

			if ( $db !== false ) {
				$table					=	$db->getChildByNameAttr( 'table', 'name', $tableName );
				if ( $table !== false ) {
					cbimport( 'cb.sql.upgrader' );
					$this->_sqlUpgrader	=	new CBSQLupgrader( $this->_db, $this->_silentWhenOK );
					$this->_sqlUpgrader->setDryRun( $dryRun );
					$success			=	$this->_sqlUpgrader->checkXmlTableDescription( $table, '', $upgrade, null );

/*
var_dump( $success );
echo "<br>\nERRORS: " . $this->_sqlUpgrader->getErrors( "<br /><br />\n\n", "<br />\n" );
echo "<br>\nLOGS: " . $this->_sqlUpgrader->getLogs( "<br /><br />\n\n", "<br />\n" );
exit;
*/
				} else {
					$success			=	array( sprintf( 'Error: could not find element table name="%s" in XML file', $tableName ), null );
				}
			} else {
				$success				=	array( 'Error: could not find element "database" in XML file', null );
			}
		} else {
			$success					=	array( 'Error: could not find XML file', null );
		}
		return $success;
	}
	/**
	 * Checks the all tables and upgrades if needed
	 * Backend-use only.
	 * @access private
	 *
	 * @param  boolean         $upgrade    False: only check table, True: upgrades table (depending on $dryRun)
	 * @param  boolean         $dryRun     True: doesn't do the modifying queries, but lists them, False: does the job
	 * @return string                      Message to display
	 */
	function checkDatabase( $upgrade = false, $dryRun = false ) {
		$xml							=	$this->_getCbDbXml();
		if ( $xml !== null ) {
			$db							=&	$xml->getElementByPath( 'database' );
			if ( $db ) {
				cbimport( 'cb.sql.upgrader' );
				$this->_sqlUpgrader		=	new CBSQLupgrader( $this->_db, $this->_silentWhenOK );
				$this->_sqlUpgrader->setDryRun( $dryRun );
				$success				=	$this->_sqlUpgrader->checkXmlDatabaseDescription( $db, '', $upgrade, true );
/*
var_dump( $success );
echo "<br>\nERRORS: " . $this->_sqlUpgrader->getErrors( "<br /><br />\n\n", "<br />\n" );
echo "<br>\nLOGS: " . $this->_sqlUpgrader->getLogs( "<br /><br />\n\n", "<br />\n" );
exit;
*/
			} else {
				$success				=	array( 'Error: could not find element database in XML file', null );
			}
		} else {
			$success					=	array( 'Error: could not find XML file', null );
		}
		return $success;
	}
	/**
	 * Handles SQL XML for the type of the field (backend use only!)
	 * e.g.: array( '#__comprofiler_fields' ), true
	 * array( '#__comprofiler', '#__comprofiler_field_values', '#__comprofiler_fields', '#__comprofiler_lists', '#__comprofiler_members', '#__comprofiler_plugin', '#__comprofiler_tabs', '#__comprofiler_userreports', '#__comprofiler_views' ), false
	 * $_CB_database->getTableList(), false
	 *
	 * @param  array  $tablesArray  Array of tableNames: 
	 * @return string XML
	 */
	function _dumpAll( $tablesArray, $withContent ) {
		global $_CB_database;

		cbimport( 'cb.sql.upgrader' );
		$sqlUpgrader					=	new CBSQLupgrader( $_CB_database );

		$sqlUpgrader->setDryRun( true );

		$tableXml						=	$sqlUpgrader->dumpTableToXml( $tablesArray, $withContent );
		if ( class_exists( 'DOMDocument' ) ) {
			$doc						=	new DOMDocument( '1.0', 'UTF-8' );
			$doc->formatOutput			=	true;
			$domnode					=	dom_import_simplexml($tableXml);
			$domnode					=	$doc->importNode($domnode, true);
			$domnode					=	$doc->appendChild($domnode);
			$text						=	str_replace( array( '/>', "\n\n" ), array( ' />', "\n" ), $doc->saveXML() );
		} else {
			$text						=	$tableXml->asXML();
		}
		return $text;
	}

	/**
	 * CB-specific stuff:
	 */

	/**
	 * Checks the all data content tables of comprofiler fields and upgrades if needed
	 * Backend-use only.
	 * @access private
	 *
	 * @param  boolean         $upgrade    False: only check table, True: upgrades table (depending on $dryRun)
	 * @param  boolean         $dryRun     True: doesn't do the modifying queries, but lists them, False: does the job
	 * @return string                      Message to display
	 */
	function checkAllCBfieldsDb( $upgrade = false, $dryRun = false ) {
		cbimport( 'cb.sql.upgrader' );
		$this->_sqlUpgrader			=	new CBSQLupgrader( $this->_db, $this->_silentWhenOK );
		$this->_sqlUpgrader->setDryRun( $dryRun );

		$this->_db->setQuery( "SELECT f.*" 
//				f.fieldid, f.title, f.name, f.description, f.type, f.required, f.published, "
//			. "f.profile, f.ordering, f.registration, f.searchable, f.pluginid, f.sys, f.tablecolumns, "
//			. ", t.title AS 'tab', t.enabled AS 'tabenabled', t.pluginid AS 'tabpluginid', "
//			. "p.name AS pluginname, p.published AS pluginpublished, "
//			. "pf.name AS fieldpluginname, pf.published AS fieldpluginpublished "
			. "\n FROM #__comprofiler_fields AS f"
			. "\n INNER JOIN #__comprofiler_tabs AS t ON ( (f.tabid = t.tabid) AND (t.fields = 1) ) "
			. "\n LEFT JOIN #__comprofiler_plugin AS p ON p.id = t.pluginid"
			. "\n LEFT JOIN #__comprofiler_plugin AS pf ON pf.id = f.pluginid"
			. "\n ORDER BY t.ordering, f.ordering"
		);
	
		$rows = $this->_db->loadObjectList( 'fieldid', 'moscomprofilerFields', array( &$this->_db ) );
		if ($this->_db->getErrorNum()) {
			echo $this->_db->stderr();
			return false;
		}
		$ret						=	true;
		foreach ( $rows as $field ) {
			$fieldHandler			=	new cbFieldHandler();
			$success				=	$fieldHandler->checkFixSQL( $this->_sqlUpgrader, $field, $upgrade );
			if ( ! $success ) {
				$ret				=	false;
				// echo $field->_error;
			}
		}
		return $ret;
	}

	var $_tabsShouldBe	=	array(	11	=>	'getContactTab',
									12	=>	'getAuthorTab',
									13	=>	'getForumTab',
									14	=>	'getBlogTab',
									15	=>	'getConnectionTab',
									16	=>	'getNewslettersTab',
									17	=>	'getMenuTab',
									18	=>	'getConnectionPathsTab',
									19	=>	'getPageTitleTab',
									20	=>	'getPortraitTab',
									21	=>	'getStatusTab',
									22	=>	'getmypmsproTab',
								 );

	/**
	 * Checks a few CB-specific stuff and upgrades if needed
	 * Backend-use only.
	 * @access private
	 *
	 * @param  boolean         $upgrade    False: only check table, True: upgrades table (depending on $dryRun)
	 * @param  boolean         $dryRun     True: doesn't do the modifying queries, but lists them, False: does the job
	 * @return string                      Message to display
	 */
	function checkCBMandatoryDb( $upgrade = false, $dryRun = false ) {
		$success				=	$this->_checkIfCBMandatoryOK();
		if ( $upgrade && ! $success ) {
			$success			=	$this->_fixCBmandatoryDb( $dryRun );
		}
		return $success;
	}

	function _checkIfCBMandatoryOK() {
		$success									=	false;

		cbimport( 'cb.sql.upgrader' );
		$this->_sqlUpgrader		=	new CBSQLupgrader( $this->_db, $this->_silentWhenOK );
		// fixing the tabid of installs before CB 1.0 RC 2:
	
		$sql			=	'SELECT * FROM `#__comprofiler_tabs` ORDER BY `tabid`';		// `tabid`, `pluginclass`
		$this->_db->setQuery( $sql );
		$tabs			=	$this->_db->loadObjectList( 'tabid' );
		if ( $tabs === null ) {
			$this->_sqlUpgrader->_setError( 'Tabs selection query error: ' . $this->_db->getErrorMsg() );
		}
	
		// 0) check if all tabs are fine (as for new installs with CB 1.0 RC 2 included or more recent:
		//    so we avoid checking and messing with 3pd plugins which use CB pluginclasses:
		if ( is_array( $tabs ) ) {
			$success								=	true;
			foreach ( $tabs as $t ) {
	
				if ( isset( $this->_tabsShouldBe[$t->tabid] ) && ( $t->pluginclass == $this->_tabsShouldBe[$t->tabid] ) ) {
					// ok, cool, CORE tab: tabid and pluginclass match a core cb tab: no corrective action:
					continue;
				}
				if ( ( ! isset( $this->_tabsShouldBe[$t->tabid] ) ) && ( ! in_array( $t->pluginclass, $this->_tabsShouldBe ) ) ) {
					// ok, cool, NON-CORE tab: neither tabid nor pluginclass match a core cb tab: no corrective action:
					continue;
				}
				// well, we got a problem: either tabid XOR pluginclass of that tab are matching a CORE CB TAB:
				if ( isset( $this->_tabsShouldBe[$t->tabid] ) ) {
					$error							=	sprintf( 'This tab id is reserved for CB pluginclass "%s", so it needs to get another id.', $this->_tabsShouldBe[$t->tabid] );
				} else {
					$error							=	sprintf( 'This tab id is not right, this pluginclass is core CB and must have id %d, so it needs to change its id.', @implode( @array_keys( $this->_tabsShouldBe, $t->pluginclass ) ) );
				}
				
				$this->_sqlUpgrader->_setError( sprintf( 'Error on tab id %d with pluginclass "%s": %s', $t->tabid, $t->pluginclass, $error ) );
				$success							=	false;
				// break;
			}
		}
		return $success;
	}
	function _fixCBmandatoryDb( $dryRun ) {
		cbimport( 'cb.sql.upgrader' );
		$this->_sqlUpgrader		=	new CBSQLupgrader( $this->_db, $this->_silentWhenOK );
		$this->_sqlUpgrader->setDryRun( $dryRun );
		
		$sql			=	'SELECT * FROM `#__comprofiler_tabs` ORDER BY `tabid`';		// `tabid`, `pluginclass`
		$this->_db->setQuery( $sql );
		$tabs			=	$this->_db->loadObjectList( 'tabid' );
		if ( $tabs === null ) {
			$this->_sqlUpgrader->_setError( 'Tabs selection query error: ' . $this->_db->getErrorMsg() );
			return false;
		}

		$sql			=	'SELECT `fieldid`, `tabid` FROM `#__comprofiler_fields` ORDER BY `tabid`';
		$this->_db->setQuery( $sql );
		$fields			=	$this->_db->loadObjectList( 'fieldid' );
		if ( $fields === null ) {
			$this->_sqlUpgrader->_setError( sprintf( 'Fields selection query error: ' . $this->_db->getErrorMsg() ), $sql );
			return false;
		}

		// 1) count and index tabs by core pluginclass and tabid holding array of fieldsids, so we can delete empty duplicate core tabs:
		$coreTabs			=	array();
		foreach ( $tabs as $t ) {
			if ( in_array( $t->pluginclass, $this->_tabsShouldBe ) ) {
				$coreTabs[$t->pluginclass][$t->tabid]	=	array();
			}
		}

		// 2) group fieldids by tabid
		// 3) add fields to $coreTabs[pluginclass][tabid][fieldid]
		$tabsFields			=	array();
		foreach ( $fields as $f ) {
			if ( isset( $tabs[$f->tabid] ) ) {
				$tabsFields[$f->tabid][$f->fieldid]		=	$f->fieldid;
				if ( $tabs[$f->tabid]->pluginclass != '' ) {
					$coreTabs[$tabs[$f->tabid]->pluginclass][$f->tabid][$f->fieldid]	=	$f->fieldid;
				}
			}
		}

		// 4) delete empty duplicate core tabs according to $coreTabs[pluginclass][tabid][fieldid]
		foreach ( $coreTabs as /* $pluginClass => */ $tabIds ) {
			if ( count( $tabIds ) > 1 ) {
				// there is more than one core tab for this core plugin class ! We need to decide which to keep:
				$tabidCandidatesToKeep					=	array();
				// 1st priority: keep tabs that are enabled AND have fields:
				foreach ( $tabIds as $tId => $tFields ) {
					if ( ( $tabs[$tId]->enabled == 1 ) && ( count( $tFields ) > 0 ) ) {
						$tabidCandidatesToKeep[]		=	$tId;
					}
				}
				// 2nd priority: keep tabs that have fields:
				if ( count( $tabidCandidatesToKeep ) == 0 ) {
					foreach ( $tabIds as $tId => $tFields ) {
						if ( count( $tFields ) > 0 ) {
							$tabidCandidatesToKeep[]	=	$tId;
						}
					}
				}
				// 3rd priority: keep tabs that are enabled:
				if ( count( $tabidCandidatesToKeep ) == 0 ) {
					foreach ( $tabIds as $tId => $tFields ) {
						if ( $tabs[$tId]->enabled == 1 ) {
							$tabidCandidatesToKeep[]	=	$tId;
						}
					}
				}
				// 4th priority: keep tab with the correct id:
				if ( count( $tabidCandidatesToKeep ) == 0 ) {
					foreach ( $tabIds as $tId => $tFields ) {
						if ( isset( $this->_tabsShouldBe[$tId] ) && ( $tabs[$tId]->pluginclass == $this->_tabsShouldBe[$tId] ) ) {
							$tabidCandidatesToKeep[]	=	$tId;
						}
					}
				}
				// 5th priority: well no more priorities to think of ! : just take first one !
				if ( count( $tabidCandidatesToKeep ) == 0 ) {
					foreach ( $tabIds as $tId => $tFields ) {
						$tabidCandidatesToKeep[]		=	$tId;
						break;
					}
				}
				// ok, by now we got at least one tab to keep: let's see which, in case we got more than one:
				if ( count( $tabidCandidatesToKeep ) == 1 ) {
					$tabToKeep							=	(int) $tabidCandidatesToKeep[0];
				} else {
					$tabToKeep							=	null;
					// a) has the right core id:
					foreach ( $tabidCandidatesToKeep as $tId ) {
						if ( isset( $this->_tabsShouldBe[$tId] ) && ( $tabs[$tId]->pluginclass == $this->_tabsShouldBe[$tId] ) ) {
							$tabToKeep					=	$tId;
							break;
						}
					}
					// b) first with fields:
					if ( $tabToKeep === null ) {
						foreach ( $tabidCandidatesToKeep as $tId ) {
							if ( count( $coreTabs[$tabs[$tId]->pluginclass][$tId] ) > 0 ) {
								$tabToKeep				=	$tId;
								break;
							}
						}
					}
					// c) first enabled one:
					if ( $tabToKeep === null ) {
						foreach ( $tabidCandidatesToKeep as $tId ) {
							if ( $tabs[$tId]->enabled == 1 ) {
								$tabToKeep				=	$tId;
								break;
							}
						}
					}
					// d) first one:
					if ( $tabToKeep === null ) {
						foreach ( $tabidCandidatesToKeep as $tId ) {
							$tabToKeep					=	$tId;
							break;
						}
					}
				}

				if ( $tabToKeep !== null ) {
					$tabsToDelete					=	array_diff( array_keys( $tabIds ), array( $tabToKeep ) );
					// first reassign the fields of the tabs to delete:
					$fieldsToReassign				=	array();
					foreach ( $tabIds as $tId => $tFields ) {
						if ( ( $tId != $tabToKeep ) && count( $tFields ) > 0 ) {
							$fieldsToReassign		=	array_merge( $fieldsToReassign, $tFields );
						}
					}
					if ( count( $fieldsToReassign ) > 0 ) {
						cbArrayToInts( $fieldsToReassign );
						$sql	=	'UPDATE `#__comprofiler_fields` SET `tabid` = ' . (int) $tabToKeep . ' WHERE `fieldid` IN (' . implode( ',', $fieldsToReassign ) . ')';
						if ( ! $this->_sqlUpgrader->_doQuery( $sql ) ) {
							$this->_sqlUpgrader->_setError( 'Failed changing fieldids ' . implode( ',', $fieldsToReassign ) . ' from duplicates of kept core tabid: ' . $tabToKeep . ' because of error:' . $this->_db->getErrorMsg(), $sql );
							break;
						} else {
							$this->_sqlUpgrader->_setLog( 'Changed fieldids ' . implode( ',', $fieldsToReassign ) . ' from duplicates of kept core tabid: ' . $tabToKeep, $sql, 'change' );
						}
						
					}
					cbArrayToInts( $tabsToDelete );
					// c) remove duplicate core tabs:
					$sql		=	'DELETE FROM `#__comprofiler_tabs` WHERE `tabid` IN (' . implode( ',', $tabsToDelete ) . ')';
					if ( ! $this->_sqlUpgrader->_doQuery( $sql ) ) {
						$this->_sqlUpgrader->_setError( 'Failed deleting duplicates tabids ' . implode( ',', $tabsToDelete ) . ' of the used core tabid: ' . $tabToKeep . ' because of error:' . $this->_db->getErrorMsg(), $sql );
						break;
					} else {
						$this->_sqlUpgrader->_setLog( 'Deleted duplicate core tabs tabids ' . implode( ',', $tabsToDelete ) . ' of the used core tabid: ' . $tabToKeep, $sql, 'change' );
					}
					
				}
			}
		}

		// 5) refetch tabs with now free space at reserved positions:
		$sql			=	'SELECT * FROM `#__comprofiler_tabs` ORDER BY `tabid`';		// `tabid`, `pluginclass`
		$this->_db->setQuery( $sql );
		$tabs			=	$this->_db->loadObjectList( 'tabid' );
		if ( $tabs === null ) {
			$this->_sqlUpgrader->_setError( 'Tabs 2nd selection query error: ' . $this->_db->getErrorMsg(), $sql );
			return false;
		}
		unset( $coreTabs );		// this one is now invalid, and not needed anymore
		$sql			=	'SELECT `fieldid`, `tabid` FROM `#__comprofiler_fields` ORDER BY `tabid`';
		$this->_db->setQuery( $sql );
		$fields			=	$this->_db->loadObjectList( 'fieldid' );
		if ( $fields === null ) {
			$this->_sqlUpgrader->_setError( 'Fields 3nd selection query error: ' . $this->_db->getErrorMsg(), $sql );
			return false;
		}
		// group fieldids by tabid
		$tabsFields			=	array();
		foreach ( $fields as $f ) {
			if ( isset( $tabs[$f->tabid] ) ) {
				$tabsFields[$f->tabid][$f->fieldid]		=	$f->fieldid;
			}
		}

		// 6) check tabs one by one, making room in reserved positions:
		foreach ( $tabs as $t ) {

			if ( isset( $this->_tabsShouldBe[$t->tabid] ) && ( $t->pluginclass == $this->_tabsShouldBe[$t->tabid] ) ) {
				// ok, cool, tabid and plugin matches: no corrective action:
				continue;
			}

			if ( isset( $this->_tabsShouldBe[$t->tabid] ) ) {
				// not ok: tabid is taken by another tab: we need to relocate this tab at last position:

				// a) insert same tab in another tabid
				$oldTabId	=	$t->tabid;
				if ( ! $dryRun ) {
					$t->tabid	=	null;
					if ( ! $this->_db->insertObject( '#__comprofiler_tabs', $t, 'tabid' ) ) {
						$this->_sqlUpgrader->_setError( 'Failed moving (inserting) non-core tabid: ' . $oldTabId . ' because of error:' . $this->_db->getErrorMsg(), $sql );
						break;
					}				
					$t->tabid	=	$this->_db->insertid();
				} else {
					$t->tabid	=	$t->tabid + 10000;		// just to fake the insert
				}
				$this->_sqlUpgrader->_setLog( 'Inserted old tabid ' . $oldTabId . ' as new tabid ' . $t->tabid, ( $dryRun ? 'INSERT tabobject' : $this->_db->getQuery() ), 'change' );

				// b) change fields' tabid:
				if ( isset( $tabsFields[$oldTabId] ) && ( count( $tabsFields[$oldTabId] ) > 0 ) ) {
					$sql	=	'UPDATE `#__comprofiler_fields` SET `tabid` = ' . (int) $t->tabid . ' WHERE `tabid` = ' . (int) $oldTabId;
					if ( ! $this->_sqlUpgrader->_doQuery( $sql ) ) {
						$this->_sqlUpgrader->_setError( 'Failed changing fields from old non-core tab with core tabid: ' . $oldTabId . ' to new tabid: ' . $t->tabid . ' because of error:' . $this->_db->getErrorMsg(), $sql );
						break;
					} else {
						$this->_sqlUpgrader->_setLog( 'Changed fields from old non-core tab with core tabid: ' . $oldTabId . ' (that must be for ' . $this->_tabsShouldBe[$oldTabId] . ') to new tabid: ' . $t->tabid, $sql, 'change' );
					}
					
				}

				// c) remove old tab:
				$sql		=	'DELETE FROM `#__comprofiler_tabs` WHERE tabid = ' . (int) $oldTabId;
				if ( ! $this->_sqlUpgrader->_doQuery( $sql ) ) {
					$this->_sqlUpgrader->_setError( 'Failed deleting old non-core tabid: ' . $oldTabId . ' which is already copied to new tabid: ' . $t->tabid . ' because of error:' . $this->_db->getErrorMsg(), $sql );
					break;
				} else {
					$this->_sqlUpgrader->_setLog( 'Deleted old non-core tabid: ' . $oldTabId . ' which is already copied to new tabid: ' . $t->tabid, $sql, 'change' );
				}
				

			}
		}

		// 7) refetch tabs with now free space at reserved positions as well as fields and recompute $tabFields:
		$sql			=	'SELECT * FROM `#__comprofiler_tabs` ORDER BY `tabid`';		// `tabid`, `pluginclass`
		$this->_db->setQuery( $sql );
		$tabs			=	$this->_db->loadObjectList( 'tabid' );
		if ( $tabs === null ) {
			$this->_sqlUpgrader->_setError( 'Tabs 3rd selection query error: ' . $this->_db->getErrorMsg(), $sql );
			return false;
		}

		$sql			=	'SELECT `fieldid`, `tabid` FROM `#__comprofiler_fields` ORDER BY `tabid`';
		$this->_db->setQuery( $sql );
		$fields			=	$this->_db->loadObjectList( 'fieldid' );
		if ( $fields === null ) {
			$this->_sqlUpgrader->_setError( 'Fields 3nd selection query error: ' . $this->_db->getErrorMsg(), $sql );
			return false;
		}
		// group fieldids by tabid
		$tabsFields			=	array();
		foreach ( $fields as $f ) {
			if ( isset( $tabs[$f->tabid] ) ) {
				$tabsFields[$f->tabid][$f->fieldid]		=	$f->fieldid;
			}
		}

		// 8) check tabs one by one, moving tabs back to reserved positions if needed:
		foreach ( $tabs as $t ) {

			if ( isset( $this->_tabsShouldBe[$t->tabid] ) && ( $t->pluginclass == $this->_tabsShouldBe[$t->tabid] ) ) {
				// ok, cool, tabid and plugin matches: no corrective action:
				continue;
			}

			if ( ( ! isset( $this->_tabsShouldBe[$t->tabid] ) ) && in_array( $t->pluginclass, $this->_tabsShouldBe ) ) {
				// ok we found a core CB tab which doesn't have the right id: the right id is now free, so just update the tab:
				$newTabId	=	array_search( $t->pluginclass, $this->_tabsShouldBe );
				if ( $newTabId !== false ) {
					// a) move the core tab to the right tabid:
					$sql	=	'UPDATE `#__comprofiler_tabs` SET `tabid` = ' . (int) $newTabId . ' WHERE `tabid` = ' . (int) $t->tabid;
					if ( ! $this->_sqlUpgrader->_doQuery( $sql ) ) {
						$this->_sqlUpgrader->_setError( 'Failed moving core tab from old tabid: ' . $t->tabid . ' to new tabid: ' . $newTabId . ' because of error:' . $this->_db->getErrorMsg(), $sql );
						break;
					} else {
						$this->_sqlUpgrader->_setLog( 'Moved core tab from old tabid: ' . $t->tabid . ' to new tabid: ' . $newTabId, $sql, 'change' );
					}
					
					// b) change fields' tabid:
					if ( isset( $tabsFields[$t->tabid] ) && ( count( $tabsFields[$t->tabid] ) > 0 ) ) {
						$sql	=	'UPDATE `#__comprofiler_fields` SET `tabid` = ' . (int) $newTabId . ' WHERE `tabid` = ' . (int) $t->tabid;
						if ( ! $this->_sqlUpgrader->_doQuery( $sql ) ) {
							$this->_sqlUpgrader->_setError( 'Failed changing fields from old core tabid: ' . $oldTabId . ' to new tabid: ' . $t->tabid . ' because of error:' . $this->_db->getErrorMsg(), $sql );
							break;
						} else {
							$this->_sqlUpgrader->_setLog( 'Changed fields from old core tabid: ' . $oldTabId . ' to new tabid: ' . $t->tabid, $sql, 'change' );
						}
						
					}
				}
			}
		}
		// now missing core tabs will be inserted in the new 1.2 upgrader in next step.
		return true;
	}
}

?>
