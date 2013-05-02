<?php
/**
* Joomla/Mambo Community Builder
* @version $Id: comprofiler.php 609 2008-08-08 21:30:15Z beat $
* @package Community Builder
* @subpackage router.php
* @author Beat
* @copyright (C) Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


function comprofilerBuildRoute( &$query ) {
	$segments									=	array();

	if ( isset( $query['task'] ) ) {
	//  if ( empty( $query['Itemid'] ) ) {
		$task									=	strtolower( $query['task'] );
		$segments[]								=	$task;

		switch ( $task ) {
			case 'userprofile':
				if ( isset( $query['user'] ) && $query['user'] ) {
					if ( is_numeric( $query['user'] ) ) {
						$sql					=	'SELECT username FROM #__users WHERE id = '. (int) $query['user'];
						$database				=&	JFactory::getDBO();
						$database->setQuery( $sql, 0, 1 );
						$username				=	$database->loadResult();
						if ( $username && ! ( preg_match( '+[@:/\n\r\t\a\e\f\v\x00_]+', $username ) || is_numeric( $username ) ) ) {
							$query['user']		=	str_replace( '.', '_', $username );		// a dot (.) in a username is mishandled by the dot htaccess of joomla 1.5
						}
					}
					$segments[]					=	$query['user'];
					unset( $query['user'] );
				}
				break;

			case 'userslist':
				$listid							=	false;
				if ( isset( $query['listid'] ) && $query['listid'] ) {
					if ( is_numeric( $query['listid'] ) ) {
						$sql					=	'SELECT title FROM #__comprofiler_lists WHERE listid = '. (int) $query['listid'] . ' AND published = 1';
						$database				=&	JFactory::getDBO();
						$database->setQuery( $sql, 0, 2 );
						$listNames				=	$database->loadResultArray();
						if ( is_array( $listNames ) && ( count( $listNames ) == 1 ) ) {
							$query['listid']	=	$listNames[0];
						}
					}
					$segments[]					=	$query['listid'];
					unset( $query['listid'] );
					$listid						=	true;
				}
				if ( isset( $query['searchmode'] ) && $query['searchmode'] ) {
					if ( ! $listid ) {
						$segments[]				=	'0';
					}
					$segments[]					=	'search';
					unset( $query['searchmode'] );
				}
				break;
				
			default:
				break;
		}
		unset($query['task']);
	//  }
	}

	return $segments;
}

function comprofilerParseRoute( $segments ) {
	$vars										=	array();

	//Get the active menu item
	// $menu									=&	JSite::getMenu();
	// $item									=&	$menu->getActive();
	//
	// if ( ! isset( $item ) ) {
	$count										=	count( $segments );
	if ( $count > 0 ) {
		$vars['task']							=	strtolower( $segments[0] );

		switch ( $vars['task'] ) {
			case 'userprofile':
				if ( $count > 1 ) {
					// Joomla's 1.5 router.php unfortunately encodes '-' as ':' in the decoding,
					// so we do what we can as usernames with '-' are more common than usernames with ':':
					$user						=	str_replace( array( ':', '_' ), array( '-', '.' ), $segments[1] );
					if ( ! is_numeric( $user ) ) {
						$database				=&	JFactory::getDBO();
						$sql					=	'SELECT id FROM #__users WHERE username = '. $database->Quote( $user );
						$database->setQuery( $sql, 0, 2 );
						$userIds				=	$database->loadResultArray();
						if ( is_array( $userIds ) && ( count( $userIds ) == 1 ) ) {
							$user				=	$userIds[0];
						}
					}
					$vars['user']				=	$user;
				}
				break;

			case 'userslist':
				if ( $count > 1 ) {
					$listid						=	$segments[1];
					if ( ! is_numeric( $listid ) ) {
						$database				=&	JFactory::getDBO();
						$sql					=	'SELECT listid FROM #__comprofiler_lists WHERE title = '. $database->Quote( $listid ) . ' AND published = 1';
						$database->setQuery( $sql, 0, 2 );
						$listIds				=	$database->loadResultArray();
						if ( is_array( $listIds ) && ( count( $listIds ) == 1 ) ) {
							$listid				=	$listIds[0];
						}
					}
					$vars['listid']				=	(int) $listid;

					if ( $count > 2 ) {
						if ( $segments[2] == 'search' ) {
							$vars['searchmode']	=	1;
						}
					}
				}
				break;

			default:
				break;
		}
	}
	return $vars;
}

?>