<?php
/**
* @version $Id: cb.acl.php 444 2008-02-07 02:25:39Z beat $
* @package Community Builder
* @subpackage cb.acl.php
* @author Beat and mambojoe
* @copyright (C) Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// no direct access
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


/**
 * CB 1.x ACL functions:
 */

	/**
	 * Checks if $oID userid is a moderator for CB
	 *
	 * @param int  $oID
	 * @return boolean   true is moderator, otherwise false
	 */
	function isModerator( $oID ) {
		global $ueConfig;

		static $uidArry		=	array();	// cache

		$oID				=	(int) $oID;
		if ( ! isset( $uidArry[$oID] ) ) {
			$uidArry[$oID]	=	( $oID && in_array( userGID( $oID ), getParentGIDS( $ueConfig['imageApproverGid'] ) ) );
		}
		return $uidArry[$oID];
	}
	/**
	 * Gives ACL group id of userid $oID
	 *
	 * @param int $oID   user id
	 * @return int       ACL group id
	 */
	function userGID( $oID ){
	  	global $_CB_database;
	
		static $uidArry			=	array();	// cache

		$oID					=	(int) $oID;
		if ( ! isset( $uidArry[$oID] ) ) {
		  	if( $oID > 0 ) {
				$query			=	"SELECT gid FROM #__users WHERE id = ".(int) $oID;
				$_CB_database->setQuery( $query );
				$uidArry[$oID]	=	(int) $_CB_database->loadResult();
			} else {
				$uidArry[$oID]	=	0;
			}
		}
		return $uidArry[$oID];
	}

	function allowAccess( $accessgroupid, $recurse, $usersgroupid) {
		if ($accessgroupid == -2 || ($accessgroupid == -1 && $usersgroupid > 0)) {
			//grant public access or access to all registered users
			return true;
		}
		else {
			//need to do more checking based on more restrictions
			if( $usersgroupid == $accessgroupid ) {
				//direct match
				return true;
			}
			else {
				if ($recurse=='RECURSE') {
					//check if there are children groups
	
					//$groupchildren=$_CB_framework->acl->get_group_children( $usersgroupid, 'ARO', $recurse );
					//print_r($groupchildren);
					$groupchildren=array();
					$groupchildren=getParentGIDS($accessgroupid);
					if ( is_array( $groupchildren ) && count( $groupchildren ) > 0) {
						if ( in_array($usersgroupid, $groupchildren) ) {
							//match
							return true;
						}
					}
				}
			}
			//deny access
			return false;
		}
	}
	function cbGetAllUsergroupsBelowMe( ) {
		global $_CB_framework;

		// ensure user can't add group higher than themselves
		if ( checkJversion() <= 0 ) {
			$my_groups 	= $_CB_framework->acl->get_object_groups( 'users', $_CB_framework->myId(), 'ARO' );
		} else {
			$aro_id		= $_CB_framework->acl->get_object_id( 'users', $_CB_framework->myId(), 'ARO' );
			$my_groups 	= $_CB_framework->acl->get_object_groups( $aro_id, 'ARO' );
		}
		if (is_array( $my_groups ) && count( $my_groups ) > 0) {
			$ex_groups = $_CB_framework->acl->get_group_children( $my_groups[0], 'ARO', 'RECURSE' );
			if ( $ex_groups === null ) {
				$ex_groups	=	array();		// Mambo
			}
		} else {
			$ex_groups		=	array();
		}

		$gtree = $_CB_framework->acl->get_group_children_tree( null, 'USERS', false );

		// remove users 'above' me
		$i = 0;
		while ($i < count( $gtree )) {
			if (in_array( $gtree[$i]->value, $ex_groups )) {
				array_splice( $gtree, $i, 1 );
			} else {
				$i++;
			}
		}
		return $gtree;
	}
	function getChildGIDS( $gid ) {
		global $_CB_database;

		static $gidsArry			=	array();	// cache

		$gid		=	(int) $gid;

		if ( ! isset( $gidsArry[$gid] ) ) {
			if ( checkJversion() <= 0 ) {
	           	$query	=	"SELECT g1.group_id, g1.name"
				."\n FROM #__core_acl_aro_groups g1"
				."\n LEFT JOIN #__core_acl_aro_groups g2 ON g2.lft >= g1.lft"
				."\n WHERE g2.group_id =" . (int) $gid
				."\n ORDER BY g1.name";
			} else {
	           	$query	=	"SELECT g1.id AS group_id, g1.name"
				."\n FROM #__core_acl_aro_groups g1"
				."\n LEFT JOIN #__core_acl_aro_groups g2 ON g2.lft >= g1.lft"
				."\n WHERE g2.id =" . (int) $gid
				."\n ORDER BY g1.name";
			}
			$standardlist		=	array( -2 );
			if( $gid > 0) {
				$standardlist[]	=	-1;
			}
	       	$_CB_database->setQuery( $query );
			$gidsArry[$gid]		=	$_CB_database->loadResultArray();
	      	if ( ! is_array( $gidsArry[$gid] ) ) {
	       		$gidsArry[$gid]	=	array();
	       	}
			$gidsArry[$gid]		=	array_merge( $gidsArry[$gid], $standardlist );
		}
		return $gidsArry[$gid];
	}
	
	function getParentGIDS( $gid ) {
		global $_CB_database;

		static $gidsArry			=	array();	// cache

		$gid		=	(int) $gid;

		if ( ! isset( $gidsArry[$gid] ) ) {
			if ( checkJversion() <= 0 ) {
	          	$query	=	"SELECT g1.group_id, g1.name"
				."\n FROM #__core_acl_aro_groups g1"
				."\n LEFT JOIN #__core_acl_aro_groups g2 ON g2.lft <= g1.lft"
				."\n WHERE g2.group_id =" . (int) $gid
				."\n ORDER BY g1.name";
			} else {
	          	$query	=	"SELECT g1.id AS group_id, g1.name"
				."\n FROM #__core_acl_aro_groups g1"
				."\n LEFT JOIN #__core_acl_aro_groups g2 ON g2.lft <= g1.lft"
				."\n WHERE g2.id =" . (int) $gid
				."\n ORDER BY g1.name";
			}
	       	$_CB_database->setQuery( $query );
			$gidsArry[$gid]		=	$_CB_database->loadResultArray();
	      	if ( ! is_array( $gidsArry[$gid] ) ) {
	       		$gidsArry[$gid]	=	array();
	       	}
		}
		return $gidsArry[$gid];
	}

	/**
	 * Backend: Check if users are of lower permissions than current user (if not super-admin) and if the user himself is not included
	 *
	 * @param array of userId $cid
	 * @param string $actionName to insert in message.
	 * @return string of error if error, otherwise null
	 */
	function checkCBpermissions( $cid, $actionName, $allowActionToMyself = false ) {
		global $_CB_database, $_CB_framework;

		$msg							=	null;
		if (is_array( $cid ) && count( $cid ) ) {
			$obj						=	new moscomprofilerUser( $_CB_database );
			foreach ($cid as $id) {
				if ( $id != 0 ) {
					if ( $obj->load( (int) $id ) ) {
						if ( checkJversion() <= 0 ) {
							$groups 	=	$_CB_framework->acl->get_object_groups( 'users', $id, 'ARO' );
						} else {
							$aro_id		=	$_CB_framework->acl->get_object_id( 'users', $id, 'ARO' );
							$groups 	=	$_CB_framework->acl->get_object_groups( $aro_id, 'ARO' );
						}
						if ( isset( $groups[0] ) ) {
							$this_group =	strtolower( $_CB_framework->acl->get_group_name( $groups[0], 'ARO' ) );
						} else {
							$this_group	=	'Registered';		// minimal user group in case the ACL table entry is missing
						}
					} else {
						$msg			.=	"User not found. ";
					}
				} else {
					$this_group			=	'Registered';		// minimal user group
					$obj->gid 			=	$_CB_framework->acl->get_group_id( $this_group, 'ARO' );
				}
	
				if ( ( ! $allowActionToMyself ) && ( $id == $_CB_framework->myId() ) ){
	 				$msg				.=	"You cannot ".$actionName." Yourself! ";
	 			} else {
	 				$myGid				=	userGID( $_CB_framework->myId() );
	 				if (($obj->gid == $myGid && !in_array($myGid, array(24, 25))) ||
	 					   ($id && $obj->gid && !in_array($obj->gid,getChildGIDS($myGid))))
	 				{
						$msg			.=	"You cannot ".$actionName." a `".$this_group."`. Only higher-level users have this power. ";
	 				}
				}
			}
		} else {
			$this_group 				=	'Registered';		// minimal user group
			$gid 						=	$_CB_framework->acl->get_group_id( $this_group, 'ARO' );

			$myGid						=	userGID( $_CB_framework->myId() );
			if ( ( ( $gid == $myGid ) && ! in_array( $myGid, array( 24, 25 ) ) ) ||
				   ( $gid && ! in_array( $gid, getChildGIDS( $myGid ) ) ) )
			{
				$msg					.=	"You cannot ".$actionName." a `".$this_group."`. Only higher-level users have this power. ";
			}
		}
		return $msg;
	}
	/**
	 * Frontend: Check if task is enabled in front-end and if user is himself, or a moderator allowed to perform a task onto that other user in frontend
	 *
	 * @param  int    $uid              userid  !!! WARNING if is 0 it will assign $_CB_framework->myId() to it !!!
	 * @param  string $ueConfigVarName  $ueConfig variable name to be checked if == 0: mods disabled, == 1.: all CB mods, > 1: it's the GID (24 or 25 for now)
	 * @return string|null              null: allowed, string: not allowed, error string
	 */
	function cbCheckIfUserCanPerformUserTask( &$uid, $ueConfigVarName ) {
		global $_CB_framework, $ueConfig;

		if ( $uid == 0 ) {
			$uid				=	$_CB_framework->myId();
		}

		if ( $uid == 0 ) {
			$ret				=	false;
		} elseif ( $uid == $_CB_framework->myId() ) {
			// user can perform task on himself:
			$ret				=	null;
		} else {
			if ( ( ! isset( $ueConfig[$ueConfigVarName] ) ) || ( $ueConfig[$ueConfigVarName] == 0 ) ) {
				$ret			=	_UE_FUNCTIONALITY_DISABLED;
			} elseif ( $ueConfig[$ueConfigVarName] == 1 ) {
				// site moderators can act on non-pears and above:
				$isModerator	=	isModerator( $_CB_framework->myId() );
				if ( ! $isModerator ) {
					$ret		=	false;
				} else {
					$cbUserIsModerator	=	isModerator( $uid );
					if ( $cbUserIsModerator ) {
						// moderator acting on other moderator: only if level below him:
						$ret	=	checkCBpermissions( array($uid), "edit", true );
					} else {
						// moderator acts on normal user: ok
						$ret	=	null;
					}
				}
			} elseif ( $ueConfig[$ueConfigVarName] > 1 ) {
				if ( in_array( userGID( $_CB_framework->myId() ), getParentGIDS( $ueConfig[$ueConfigVarName] ) ) ) {
					$ret		=	null;
				} else {
					$ret		=	false;
				}
			} else {
				$ret			=	false;	// Safeguard :)
			}
		}
		if ( $ret === false ) {
			$ret		=	_UE_NOT_AUTHORIZED;
			if ( $_CB_framework->myId() < 1 ) {
				$ret 	.=	'<br />' . _UE_DO_LOGIN;
			}
		}
		return $ret;
	}


// ----- NO MORE CLASSES OR FUNCTIONS PASSED THIS POINT -----
// Post class declaration initialisations
// some version of PHP don't allow the instantiation of classes
// before they are defined

?>
