<?php
/**
* Forum Tab Class for handling the CB tab api
* @version $Id: cb.simpleboardtab.php 415 2006-09-08 16:59:59Z beat $
* @package Community Builder
* @subpackage plug_cbsimpleboardtab.php
* @author JoomlaJoe and Beat (Nick A. fixed Fireboard support)
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
* Thanks to LucaZone, www.lucazone.net for Fireboard adaptation suggestions
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

$_PLUGINS->registerUserFieldTypes( array( 	'forumstats'	=> 'CBfield_forumstats'	) );
$_PLUGINS->registerUserFieldParams();

class CBfield_forumstats extends cbFieldHandler {
	/**
	 * Accessor:
	 * Returns a field in specified format
	 *
	 * @param  moscomprofilerFields  $field
	 * @param  moscomprofilerUser    $user
	 * @param  string                $output  'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string                $reason  'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches, 'list' for user-lists
	 * @param  int                   $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed                
	 */
	function getField( &$field, &$user, $output, $reason, $list_compare_types ) {

		static $stats							=	array();

		$value									=	null;

		if ( is_object( $user ) && $user->id ) {
			if ( ! isset( $stats[$user->id] ) ) {
				// $forumTab						=&	$this->getInstanceOfPluginClass( 'getForumTab' );
				// $stats[$user->id]				=	$forumTab->getExtraFields( $user );
				global $_PLUGINS;
				$params							=	array( &$user );
				$stats[$user->id]				=	$_PLUGINS->call( $this->getPluginId(), 'getExtraFields', 'getForumTab', $params );
			}

			if ( isset( $stats[$user->id][$field->name]['value'] ) ) {
				$value							=	$stats[$user->id][$field->name]['value'];
			} else {
				$value							=	'---';
			}
			
		}

		switch ( $output ) {
			case 'html':
			case 'rss':
				return $this->_formatFieldOutput( $field->name, $value, $output, false );
				break;

			case 'htmledit':
				/* Not possible for now:
				if ( $reason == 'search' ) {
					return	$this->_fieldSearchModeHtml( $field, $user, $this->_fieldEditToHtml( $field, $user, $reason, 'input', $field->type, $value, '' ), 'text' );
				} else {
					return $this->_fieldEditToHtml( $field, $user, $reason, 'input', $field->type, $value, '' );
				}
				*/
				return null;
				break;

			default:
				return $this->_formatFieldOutput( $field->name, $value, $output, false );
				break;
		}
	}
}

class getForumTab extends cbTabHandler {
	var $com_name = null;
	var $config_filename = null;
	/**
	* Constructor
	*/
	function getForumTab() {
		$this->cbTabHandler();
	}
	/**
	* Private method of CB: don't expect it to work after CB 1.1...couldn't do it in the constructor, since params are loaded after...
	*/
	function _loadParams( $pluginid, &$extraParams ) {
		global $_CB_framework;

		parent::_loadParams( $pluginid, $extraParams );

		$params					=	$this->params;
		$forumType				=	$params->get( 'forumType', 0 );

		if ( in_array( $forumType, array( 0, 2 ) ) && file_exists( $_CB_framework->getCfg('absolute_path').'/administrator/components/com_joomlaboard/joomlaboard_config.php' )) {
			$this->com_name		=	'com_joomlaboard';
			$this->sql_prefix	=	'sb';
			$this->config_filename = $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_joomlaboard/joomlaboard_config.php';
		} else if ( in_array( $forumType, array( 0, 3 ) ) && file_exists( $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_simpleboard/simpleboard_config.php' )) {
			$this->com_name		=	'com_simpleboard';
			$this->sql_prefix	=	'sb';
			$this->config_filename = $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_simpleboard/simpleboard_config.php';
		} elseif ( in_array( $forumType, array( 0, 1 ) ) && file_exists( $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_fireboard/' ) ) {
			$this->com_name		=	'com_fireboard';
			$this->sql_prefix	=	'fb';
			if ( file_exists( $_CB_framework->getCfg('absolute_path').'/administrator/components/com_fireboard/fireboard_config.php' ) ) {
				$this->config_filename = $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_fireboard/fireboard_config.php';
			} else {
				$this->config_filename = true;
			}
		}
	}
	function & _loadSbConfig( ) {
		global $_CB_database;

		if ( $this->config_filename === true ) {
			$query					=	'SELECT * FROM #__fb_config';
			$_CB_database->setQuery( $query );
			$fbConfig				=	$_CB_database->loadAssoc();
		} elseif ( is_string( $this->config_filename ) ) {
			if ( $this->com_name == 'com_fireboard' ) {
				global $fbConfig;
			} else {
				global $sbConfig;
				$fbConfig			=&	$sbConfig;
			}
			include_once ( $this->config_filename );
		}
		return $fbConfig;
	}
	function _sbVersion( $sbConfig ) {
		global $_CB_database;

		static $sbVersion	=	null;

		if ( $sbVersion === null ) {

			if ( isset( $sbConfig['version'] ) ) {
				$sbVersion	=	$sbConfig['version'];
			} elseif ( $this->com_name == 'com_fireboard' ) {
				$query		=	'SELECT `version` FROM `#__fb_version`'
							.	' ORDER BY `id` DESC LIMIT 1;';
				$_CB_database->setQuery( $query );
				$sbVersion	=	$_CB_database->loadResult();
			}
			if ( $sbVersion === null ) {
				$sbVersion	=	'0.0.0';
			}
		}

		return $sbVersion;
	}
	/**
	* ForumTab Internal method: returns an unescaped string if magic_quotes_gpc is on, correcting a SB 1.1.0 double-escaping bug!
	* @access private
	* @param string to unescape
	* @return string unescaped if needed
	*/
	function _sbUnEscape($string) {
		return ((get_magic_quotes_gpc()==1) ? stripslashes($string) : $string);	// correcting a SB 1.1.0 double-escaping bug!
	}
	/**
	* ForumTab Internal method: returns an escaped string if magic_quotes_gpc is on, correcting a SB 1.1.0 double-escaping bug!
	* @access private
	* @param string to escape
	* @return string escaped if needed
	*/
	function _sbEscape($string) {
		return ((get_magic_quotes_gpc()==1) ? addslashes($string) : $string);	// correcting a SB 1.1.0 double-escaping bug!
	}
	/**
	* ForumTab Internal method: returns $sbUserDetails for the $user
	* @access private
	* @param array sbConfig
	* @param object user being displayed
	* @return object sbUserDetails
	*/
	function _getSBstats($sbConfig, $user) {
		global $_CB_database, $_CB_framework;
		if( ( isset ( $sbConfig['showuserstats'] ) ? $sbConfig['showuserstats'] : $sbConfig['showstats'] ) || (!$sbConfig['showranking'] && !$sbConfig['showkarma'] && ! ( isset ( $sbConfig['poststats'] ) ? $sbConfig['poststats'] : $sbConfig['postStats'] ) ) ) {
			$sbVersion			=	$this->_sbVersion( $sbConfig );
			$supportsDbRanks	=	( $this->com_name == 'com_fireboard' )
									&& version_compare( preg_replace( "/(\\d+\\.\\d+\\.\\d+).*/", "\\1", $sbVersion ), '1.0.3', '>=' );
			if ( $supportsDbRanks ) {
				$query = "SELECT posts,karma,moderator,gid,rank FROM #__" . $this->sql_prefix . "_users sb, #__users u where sb.userid=u.id AND sb.userid=" . (int) $user->id;
			} else {
				$query = "SELECT posts,karma,moderator,gid FROM #__" . $this->sql_prefix . "_users sb, #__users u where sb.userid=u.id AND sb.userid=" . (int) $user->id;
			}
			$_CB_database->setQuery($query);
			$sbUserDetails=$_CB_database->loadObjectList();
			if(count($sbUserDetails)>0) $sbUserDetails=$sbUserDetails[0];
			if( (isset($sbUserDetails->posts)) and $sbUserDetails->posts != 0) {
				if($sbConfig['showranking']) {
					if ( $supportsDbRanks ) {	// fb ranks are into DB
						if ( 0 == $sbUserDetails->rank ) {		// in post-based ranks, u.rank=0
							$query = "SELECT * FROM #__" . $this->sql_prefix . "_ranks WHERE rank_special=0 AND rank_min<" . (int) $sbUserDetails->posts . " ORDER BY rank_min DESC LIMIT 0,1";
						} else {								// in special ranks, u.rank field refers to the fb_ranks table
							$query = "SELECT * FROM #__" . $this->sql_prefix . "_ranks WHERE rank_id=" . (int) $sbUserDetails->rank;
						}
						$_CB_database->setQuery($query);
						$userRank		=	null;
						$_CB_database->loadObject( $userRank );
						$params			= $this->params;
			         	$pathTemplate	= $params->get('TemplateRank', '/template/default/images/english');
						$sbs			= $_CB_framework->getCfg( 'live_site' ) . '/components/' . $this->com_name . $pathTemplate;
						$rText			= $userRank->rank_title;
						$rImg			= $sbs . '/ranks/' . $userRank->rank_image;
					} else {									// sb and jb ranks are into config file
						$uIsAdm="";
						$uIsMod="";
						if ( $sbUserDetails->gid > 0 ) {		//only get the groupname from the ACL if we're sure there is one
						$agrp=strtolower( $_CB_framework->acl->get_group_name( $sbUserDetails->gid, 'ARO' ) );
						if(strtolower($agrp)=="administrator" || strtolower($agrp)=="superadministrator"|| strtolower($agrp)=="super administrator") $uIsAdm=1;
						}
						$uIsMod=$sbUserDetails->moderator;
						$sbs = $_CB_framework->getCfg( 'live_site' ) . '/components/' . $this->com_name;
						$numPosts=$sbUserDetails->posts;
						$rText="";
						$rImg="";
						if ($numPosts>=0 && $numPosts<(int)$sbConfig['rank1']) { $rText=$sbConfig['rank1txt']; $rImg=$sbs.'/ranks/rank1.gif'; }
						if ($numPosts>=(int)$sbConfig['rank1'] && $numPosts<(int)$sbConfig['rank2']){$rText=$sbConfig['rank2txt']; $rImg=$sbs.'/ranks/rank2.gif';}
						if ($numPosts>=(int)$sbConfig['rank2'] && $numPosts<(int)$sbConfig['rank3']){$rText=$sbConfig['rank3txt']; $rImg=$sbs.'/ranks/rank3.gif';}
						if ($numPosts>=(int)$sbConfig['rank3'] && $numPosts<(int)$sbConfig['rank4']){$rText=$sbConfig['rank4txt']; $rImg=$sbs.'/ranks/rank4.gif';}
						if ($numPosts>=(int)$sbConfig['rank4'] && $numPosts<(int)$sbConfig['rank5']){$rText=$sbConfig['rank5txt']; $rImg=$sbs.'/ranks/rank5.gif';}
						if ($numPosts>=(int)$sbConfig['rank5']){$rText=$sbConfig['rank6txt']; $rImg=$sbs.'/ranks/rank6.gif';}
						if ($uIsMod){$rText=_RANK_MODERATOR; $rImg=$sbs.'/ranks/rankmod.gif';}
						if ($uIsAdm){$rText=_RANK_ADMINISTRATOR; $rImg=$sbs.'/ranks/rankadmin.gif';}
					}
					if($sbConfig['rankimages']){$sbUserDetails->msg_userrankimg = '<br /><img src="'.$rImg.'" alt="" />';}
					$sbUserDetails->msg_userrank = $rText;
				}
			} else $sbUserDetails = false;
		} else $sbUserDetails = false;
		return $sbUserDetails;
	}
	/**
	* ForumTab Internal method: returns html output of $sbUserDetails for the $user
	* @access private
	* @param array sbConfig
	* @param object user being displayed
	* @param object sbUserDetails
	* @return html code for tab
	*/
	function _getDisplaySBstats($sbConfig, $user, $params, $sbUserDetails) {
		$return="";
		$return .= "<div class=\"sectiontableheader\" style=\"text-align:left;padding-left:0px;padding-right:0px;width:50%;\">"._UE_FORUM_STATS."</div>";
		if ($sbUserDetails !== false) {
		$return .= "<table cellpadding=\"5\" cellspacing=\"0\" style=\"border:0;margin:0;padding:0;\" width=\"50%\">";
		if($sbConfig['showranking'] && ($params->get('statRanking', '1') == 1)) $return .= "<tr class=\"sectiontableentry1\"><td style=\"font-weight:bold;width:50%;\">".getLangDefinition($params->get('statRankingText', "_UE_FORUM_FORUMRANKING"))."</td><td>".$sbUserDetails->msg_userrank.($params->get('statRankingImg', '1')==1 ? $sbUserDetails->msg_userrankimg : "")."</td></tr>";
		if ( ( isset ( $sbConfig['poststats'] ) ? $sbConfig['poststats'] : $sbConfig['postStats'] ) && (($params->get('statPosts', '1')==2) || (($params->get('statPosts', '1')==1)&&($sbUserDetails !== false)))) {
			$return .= "<tr class=\"sectiontableentry2\"><td style=\"font-weight:bold;width:50%;\">"
					.getLangDefinition($params->get('statPostsText', "_UE_FORUM_TOTALPOSTS"))."</td><td>".$sbUserDetails->posts."</td></tr>";
		}
		if ($sbConfig['showkarma'] && ($sbUserDetails !== false) && (($params->get('statKarma', '1')==2)||(($params->get('statKarma', '1')==1)&&($sbUserDetails->karma!=0)))) {
			$return .= "<tr class=\"sectiontableentry1\"><td style=\"font-weight:bold;width:50%;\">"
					.getLangDefinition($params->get('statKarmaText', "_UE_FORUM_KARMA"))."</td><td>".$sbUserDetails->karma."</td></tr>";
		}
		$return .= "</table>";
		} else {
			$return = "";
		}
		return $return;
	}
/*	/*
	* ForumTab Internal method: sets User Status display according to $sbUserDetails for the $user
	* @access private
	* @param array sbConfig
	* @param object user being displayed
	* @param object sbUserDetails
	*
	function _setStatusMenuSBstats($sbConfig, $user, &$params, $sbUserDetails) {
		if ($sbConfig['showranking'] && ($params->get('statRanking', '1') == 1) && ($sbUserDetails !== false)) {
			$mi = array(); $mi["_UE_MENU_STATUS"][$params->get('statRankingText', "_UE_FORUM_FORUMRANKING")]["_UE_FORUM_FORUMRANKING"]=null;
			$this->addMenu( array(	"position"	=> "menuList" ,		// "menuBar", "menuList"
								"arrayPos"	=> $mi ,
								"caption"	=> $sbUserDetails->msg_userrank.($params->get('statRankingImg', '1')==1 ? $sbUserDetails->msg_userrankimg : "") ,
								"url"		=> "" ,		// can also be "<a ....>" or "javascript:void(0)" or ""
								"target"	=> "" ,		// e.g. "_blank"
								"img"		=> null ,	// e.g. "<img src='plugins/user/myplugin/images/icon.gif' width='16' height='16' alt='' />"
								"alt"		=> null ,	// e.g. "text"
								"tooltip"	=> "") );
		}
		if (( isset ( $sbConfig['poststats'] ) ? $sbConfig['poststats'] : $sbConfig['postStats'] ) && (($params->get('statPosts', '1')==2) || (($params->get('statPosts', '1')==1)&&($sbUserDetails !== false)))) {
			$mi = array(); $mi["_UE_MENU_STATUS"][$params->get('statPostsText', "_UE_FORUM_TOTALPOSTS")]["_UE_FORUM_TOTALPOSTS"]=null;
			$this->addMenu( array(	"position"	=> "menuList" ,
								"arrayPos"	=> $mi ,
								"caption"	=> (($sbUserDetails !== false) ? $sbUserDetails->posts : "0") ,
								"url"		=> "" ,
								"target"	=> "" ,
								"img"		=> null ,
								"alt"		=> null ,
								"tooltip"	=> "") );
		}
		if ($sbConfig['showkarma'] && ($sbUserDetails !== false) && (($params->get('statKarma', '1')==2)||(($params->get('statKarma', '1')==1)&&($sbUserDetails->karma!=0)))) {
			$mi = array(); $mi["_UE_MENU_STATUS"][$params->get('statKarmaText', "_UE_FORUM_KARMA")]["_UE_FORUM_KARMA"]=null;
			$this->addMenu( array(	"position"	=> "menuList" ,
								"arrayPos"	=> $mi ,
								"caption"	=> $sbUserDetails->karma ,
								"url"		=> "" ,
								"target"	=> "" ,
								"img"		=> null ,
								"alt"		=> null ,
								"tooltip"	=> "") );
		}
	}
	/*
	* Generates the menu and user status to display on the user profile by calling back $this->addMenu
	* @param  moscomprofilerTab   $tab       the tab database entry
	* @param  moscomprofilerUser  $user      the user being displayed
	* @param  int                 $ui        1 for front-end, 2 for back-end
	* @return boolean                        either true, or false if ErrorMSG generated
	*
	function getMenuAndStatus( &$tab, &$user, $ui) {

		if ( $this->com_name == 'com_fireboard' ) {
			global $fbConfig;
		} else {
			global $sbConfig;
			$fbConfig	=&	$sbConfig;
		}
		$params=$this->params;
		$newslettersRegList=$params->get('statDisplay', '1');
		if ($newslettersRegList==1) {
			if($this->config_filename) {
				include_once ( $this->config_filename );
			} else {
				$this->_setErrorMSG(_UE_SBNOTINSTALLED);
				return false;
			}
			$sbUserDetails = $this->_getSBstats($fbConfig, $user);
			$this->_setStatusMenuSBstats($fbConfig, $user, $params, $sbUserDetails);
		}
		return true;
	}
*/
	/**
	* ForumTab Internal method: sets User Status display according to $sbUserDetails for the $user
	* @access private
	* @param array sbConfig
	* @param object user being displayed
	* @param object sbUserDetails
	*/
	function & _getStats( $sbConfig, &$user, &$params, $sbUserDetails ) {
		$stats						=	array();

		if ( isset ( $sbConfig['showuserstats'] ) ? $sbConfig['showuserstats'] : $sbConfig['showstats'] ) {
			if ($sbConfig['showranking'] && ($params->get('statRanking', '1') == 1) && ($sbUserDetails !== false)) {
				$stats['forumrank']		=	array(	'title'		=>	$params->get('statRankingText', "_UE_FORUM_FORUMRANKING"),
													'value' 	=>	$sbUserDetails->msg_userrank.($params->get('statRankingImg', '1')==1 ? $sbUserDetails->msg_userrankimg : "")
												 );
			}
			if ( ( isset ( $sbConfig['poststats'] ) ? $sbConfig['poststats'] : $sbConfig['postStats'] ) && (($params->get('statPosts', '1')==2) || (($params->get('statPosts', '1')==1)&&($sbUserDetails !== false)))) {
				$stats['forumposts']	=	array(	'title'		=>	$params->get('statPostsText', "_UE_FORUM_TOTALPOSTS"),
													'value' 	=>	(($sbUserDetails !== false) ? $sbUserDetails->posts : "0")
												 );
			}
		}
		if ($sbConfig['showkarma'] && ($sbUserDetails !== false) && (($params->get('statKarma', '1')==2)||(($params->get('statKarma', '1')==1)&&($sbUserDetails->karma!=0)))) {
			$stats['forumkarma']	=	array(	'title'		=>	$params->get('statKarmaText', "_UE_FORUM_KARMA"),
												'value' 	=>	$sbUserDetails->karma
											 );
		}
		return $stats;
	}
	/**
	* Generates the info for the custom fields for the forum
	* @param  moscomprofilerUser  $user      the user being displayed
	* @return boolean                        either true, or false if ErrorMSG generated
	*/
	function getExtraFields( &$user ) {
		$params						=	$this->params;
		$newslettersRegList			=	$params->get('statDisplay', '1');
		if ( $newslettersRegList == 1 ) {
			if( $this->config_filename ) {
				$fbConfig			=&	$this->_loadSbConfig();
				$sbUserDetails		=	$this->_getSBstats( $fbConfig, $user );
				return $this->_getStats( $fbConfig, $user, $params, $sbUserDetails );
			} else {
				$this->_setErrorMSG( _UE_SBNOTINSTALLED );
			}
		}
		return null;
	}
	/**
	* Generates the HTML to display the user profile tab
	* @param  moscomprofilerTab   $tab       the tab database entry
	* @param  moscomprofilerUser  $user      the user being displayed
	* @param  int                 $ui        1 for front-end, 2 for back-end
	* @return mixed                          either string HTML for tab content, or false if ErrorMSG generated
	*/
	function getDisplayTab($tab,$user,$ui) {
		global $_CB_framework, $_CB_database;

		if ( $this->com_name == 'com_fireboard' ) {
			$base_url			=	$this->_getAbsURLwithParam(array(),'userProfile',false);

			$fbunsub			=	cbGetParam( $_GET, 'fbunsubthread', '' );
			if ( $fbunsub == '' ) {
				$fbunsub		=	'none';	
			}
			switch ( $fbunsub ) {
				case 'none':
					break;
				case 'all':
					$this->_fbunsuball($user);
					break;
				default:
					$fbunsub	=	(int) $fbunsub;
					if ( $fbunsub ) {
						$this->_fbunsubthread($user,$fbunsub);
					}
					break;	
			}
			$fbunfav			=	cbGetParam( $_GET, 'fbunfavthread', '' );
			if ( $fbunfav == '' ) {
				$fbunfav		=	'none';	
			}
			switch ( $fbunfav ) {
				case 'none':
					break;
				case 'all':
					$this->_fbunfavall($user);
					break;
				default:
					$fbunfav	=	(int) $fbunfav;
					if ( $fbunfav ) {
						$this->_fbunfavthread($user,$fbunfav);
					}
					break;	
			}
		}

		$return="";
		$searchForm="";
		if ($this->config_filename) {
			$fbConfig					=&	$this->_loadSbConfig();
		} else {
			$return = _UE_SBNOTINSTALLED;
			return $return;
		}
		$_CB_database->setQuery("SELECT id FROM #__menu WHERE link = " . $_CB_database->Quote( 'index.php?option=' . $this->com_name ) . " AND published = 1");
		$Itemid							=	(int) $_CB_database->loadResult();

		$return							.=	$this->_writeTabDescription( $tab, $user );
	
		$params							=&	$this->params;

		$newslettersRegList				=	$params->get( 'statDisplay', '1' );
		$sbUserDetails					=	$this->_getSBstats( $fbConfig, $user );
		if ( $newslettersRegList == 2 ) {
			$return						.=	$this->_getDisplaySBstats( $fbConfig, $user, $params, $sbUserDetails );
		}

		$postsNumber	= $params->get('postsNumber', '10');
		$pagingEnabled	= $params->get('pagingEnabled', 0);
		$searchEnabled	= $params->get('searchEnabled', 0);
		$pagingParams = $this->_getPaging(array(),array("fposts_"));
	
		if ($_CB_framework->myId() != $user->id) {
			//determine visitors allowable threads based on session
			$sql = "SELECT allowed FROM #__" . $this->sql_prefix . "_sessions WHERE userid = " . (int) $_CB_framework->myId() . " LIMIT 1";
			$_CB_database->setQuery($sql);
		//print $_CB_database->getQuery();
			$allowedCats=$_CB_database->loadResult();
			if($allowedCats==null) {
				//get only the publicly accessible forums..
		   		$_CB_database->setQuery( "SELECT id FROM #__" . $this->sql_prefix . "_categories WHERE published=1 AND pub_access=0");
		   		$allowed_forums=$_CB_database->loadObjectList();
		   		$i=0;
		   		$allow_forum = array();
		   		foreach ($allowed_forums as $af){
		      	if (count ($allow_forum) == 0 ){
		         	$allow_forum[0]=$af->id;
		      	}
		      	else {
		        	$allow_forum[$i]=$af->id;
		        	$i++;
		      	}
		   		}
		   		$allowedCats=implode(",",$allow_forum); 
			}
			if(strtolower($allowedCats)=='na') $allowedCats=null;
		} else {
			$allowedCats=null;	
		}
		
		if ($pagingEnabled) {
			if (!$searchEnabled) $pagingParams["fposts_search"]=null;
			//Count for paging
			//		$query = "SELECT count(*) FROM #__" . $this->sql_prefix . "_messages WHERE userid=".$user->id
			//				.($pagingParams["fposts_search"] ? " AND subject LIKE '%".cbEscapeSQLsearch($this->_sbEscape($pagingParams["fposts_search"]))."%'"
			//												 : "");
			$query="SELECT COUNT(*)"
				. "\n FROM #__" . $this->sql_prefix . "_messages AS a, "
				. "\n #__" . $this->sql_prefix . "_categories AS b, #__" . $this->sql_prefix . "_messages AS c, #__" . $this->sql_prefix . "_messages_text AS d" 
				. "\n WHERE a.catid = b.id"
				. "\n AND a.thread = c.id"
				. "\n AND a.id = d.mesid"
				. "\n AND a.hold = 0 AND b.published = 1"
				. "\n AND a.userid=" . (int) $user->id
				. ($allowedCats!=null ? "\n AND b.id IN ($allowedCats)" :"")
				. ($pagingParams["fposts_search"]	?  "\n AND (a.subject LIKE '%".cbEscapeSQLsearch($this->_sbEscape($pagingParams["fposts_search"]))."%'" 
														." OR d.message LIKE '%".cbEscapeSQLsearch($pagingParams["fposts_search"])."%')"
													: "");
			$_CB_database->setQuery($query);
			$total = $_CB_database->loadResult();
			if (!is_numeric($total)) $total = 0;
			$userHasPosts = ($total>0 || ($pagingParams["fposts_search"] && ($sbUserDetails !== false) && $sbUserDetails->posts>0));

			if ($pagingParams["fposts_limitstart"] === null) $pagingParams["fposts_limitstart"] = "0";
			if ($postsNumber > $total) $pagingParams["fposts_limitstart"] = "0";
			
			if ($searchEnabled) {
				$searchForm = $this->_writeSearchBox($pagingParams, "fposts_", "style=\"float:right;\"", "class=\"inputbox\"" );
			} else {
				$pagingParams["fposts_search"] = "0";
			}
			
		} else {
			$pagingParams["fposts_limitstart"] = "0";
			$pagingParams["fposts_search"] = "0";
		}
		switch ($pagingParams["fposts_sortby"]) {
			case "subject":
				$order = "a.subject ASC, a.time DESC";
				break;
			case "category":
				$order = "b.id ASC, a.time DESC";
				break;
			case "hits":
				$order = "c.hits DESC, a.time DESC";
				break;
			case "date":
			default:
				$order = "a.time DESC";
				break;
		}
		$query="SELECT a.* , b.id as category, b.name as catname, c.hits AS 'threadhits'"
		. "\n FROM #__" . $this->sql_prefix . "_messages AS a, "
		. "\n #__" . $this->sql_prefix . "_categories AS b, #__" . $this->sql_prefix . "_messages AS c, #__" . $this->sql_prefix . "_messages_text AS d" 
		. "\n WHERE a.catid = b.id"
		. "\n AND a.thread = c.id"
		. "\n AND a.id = d.mesid"
		. "\n AND a.hold = 0 AND b.published = 1"
		. "\n AND a.userid=" . (int) $user->id
		. ($allowedCats!=null ? "\n AND b.id IN ($allowedCats)" :"")		
		. ($pagingParams["fposts_search"]	?  "\n AND (a.subject LIKE '%".cbEscapeSQLsearch($this->_sbEscape($pagingParams["fposts_search"]))."%'" 
												  ." OR d.message LIKE '%".cbEscapeSQLsearch($pagingParams["fposts_search"])."%')"
											: "")
		. "\n ORDER  BY " . $order
		. "\n LIMIT " . (int) ( $pagingParams["fposts_limitstart"] ? $pagingParams["fposts_limitstart"] : 0 ) . "," . (int) $postsNumber;
		$_CB_database->setQuery( $query );
		//print $_CB_database->getQuery();

		$items = $_CB_database->loadObjectList();
		if(count($items) >0) {
			if ($pagingParams["fposts_search"]) $title = sprintf(_UE_FORUM_FOUNDPOSTS,$total);
			elseif ($pagingEnabled) $title = sprintf(_UE_FORUM_POSTS,$postsNumber);
			else $title = sprintf(_UE_FORUM_LASTPOSTS,$postsNumber);
			$return .= "<br /><div class=\"sectiontableheader\" style=\"text-align:left;padding-left:0px;padding-right:0px;margin:0px 0px 10px 0px;height:auto;width:100%;\">";
			$return .= $title;
			if ($pagingEnabled && $searchEnabled) $return .= $searchForm . "<br /><div style=\"clear:both;\">&nbsp;</div>";
			$return .= "\n<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" style=\"margin:0px;padding:0px;width:100%;\">";
			$return .= "\n\t<tr class=\"sectiontableheader\">";
			$return .= "<th>".$this->_writeSortByLink($pagingParams,"fposts_","date",_UE_FORUMDATE,true)."</th>";
			$return .= "<th>".$this->_writeSortByLink($pagingParams,"fposts_","subject",_UE_FORUMSUBJECT)."</th>";
			$return .= "<th>".$this->_writeSortByLink($pagingParams,"fposts_","category",_UE_FORUMCATEGORY)."</th>";
			$return .= "<th>".$this->_writeSortByLink($pagingParams,"fposts_","hits",_UE_FORUMHITS)."</th>";
			$return .= "</tr>";
			$i=2;
			foreach($items AS $item) {
				$i= ($i==1) ? 2 : 1;
				if(!ISSET($item->created)) $item->created="";
				$sbURL=cbSef("index.php?option=".$this->com_name.($Itemid ? '&amp;Itemid='.$Itemid : '')."&amp;func=view&amp;catid=".$item->catid."&amp;id=".$item->id)."#".$item->id;
				$return .= "\n\t<tr class=\"sectiontableentry$i\"><td>".getFieldValue("date",date("Y-m-d, H:i:s",$item->time))."</td><td><a href=\"".$sbURL."\">".$this->_sbUnescape($item->subject)."</a></td><td>".$item->catname."</td><td>".$item->threadhits."</td></tr>\n";
			}
			$return .= "\n</table></div>";
			if ($pagingEnabled && ($postsNumber < $total)) {
				$return .= "<div style='width:95%;text-align:center;'>"
				.$this->_writePaging($pagingParams,"fposts_",$postsNumber,$total)
				."</div>";
			}
			$return .= "";
		} else {
			if ($pagingEnabled && $userHasPosts && $searchEnabled && $pagingParams["fposts_search"]) {
				 $return .= "<br /><div class=\"sectiontableheader\" style=\"text-align:left;padding-left:0px;padding-right:0px;margin:0px;height:auto;width:100%;\">";
				 $return .= $searchForm;
				 $return .= "<br />"._UE_NOFORUMPOSTSFOUND;
				 $return .= "</div>";
			} else {
				$return .= _UE_NOFORUMPOSTS;
			}
		}

		// For the profile owner only: Subscriptions management:

		if( $_CB_framework->myId() == $user->id && $fbConfig['allowsubscriptions']) {
	      $_CB_database->setQuery( "SELECT thread FROM #__" . $this->sql_prefix . "_subscriptions WHERE userid=" . (int) $_CB_framework->myId() );
	      $subslist						=	$_CB_database->loadObjectList();
	      $csubslist					=	count($subslist);
		  $return 						.=	"<br /><div class=\"sectiontableheader\" style=\"text-align:left;padding-left:0px;padding-right:0px;margin:0px 0px 10px 0px;height:auto;width:100%;\">"
										.	( defined('_UE_USER_SUBSCRIPTIONS') ? getLangDefinition( "_UE_USER_SUBSCRIPTIONS" ) :
														( ( defined('_UE_FB_CONFIRMUNSUBSCRIBEALL') ) ? getLangDefinition( "_UE_FB_CONFIRMUNSUBSCRIBEALL" ) : "" ) )
										.	"<br />";
		  $return						.=	"\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"margin:0px;padding:0px;width:100%;\">";
		  $enum							=	1;	//reset value
		  $tabclass						=	array("sectiontableentry1", "sectiontableentry2");//alternating row CSS classes
		  $k							=	1; //value for alternating rows
		  if ( $csubslist > 0 ) {
		  	if ( $this->com_name == 'com_fireboard' ) {
				$unsuburl				=	'&amp;fbunsubthread=';
				$unsuballurl			=	'&amp;fbunsubthread=all';
			} else { // JB or SB
				$unsuburl				=	'&amp;func=userprofile&amp;do=unsubscribe&amp;thread=';
				$unsuballurl			=	'&amp;func=userprofile&amp;do=update';
			}
			switch ( $this->com_name ) {
				case 'com_fireboard':
					foreach ( $subslist as $subs ) {
						//get all message details for each subscription:
			  			$_CB_database->setQuery( "SELECT * FROM #__" . $this->sql_prefix . "_messages WHERE id = " . (int) $subs->thread );
			  			$subdet			=	$_CB_database->loadObjectList();
			  			foreach ( $subdet as $sub ) {
			  				$k			=	1 - $k;
			  				$return		.=	"\n\t<tr class=\"" . $tabclass[$k] . "\">";
			  				$return		.=	"\n\t\t<td>" . $enum . ": <a href=\""
					  					.	cbSef( 'index.php?option=' . $this->com_name . ( $Itemid ? '&amp;Itemid='. (int) $Itemid : '' ) . '&amp;func=view&amp;catid='. (int) $sub->catid . '&amp;id=' . (int) $sub->id )
					  					.	'">' . htmlspecialchars( $this->_sbUnescape( $sub->subject ) ) .'</a> - ' ._UE_GEN_BY. ' ' . htmlspecialchars( $sub->name ) . "</td>";
			  				$return		.=	"\n\t\t<td><a href=\""
					  					.	cbSef( $base_url . $unsuburl . $subs->thread )
					  					.	'">' ._UE_THREAD_UNSUBSCRIBE. "</a></td>";
			  				$return		.=	"\n\t</tr>";
			  				$enum++;
			  			}
		  			}
		  			$return				.=	"\n\t<tr>\n\t\t<td colspan=\"2\"><form action=\""
					      				.	cbSef( $base_url . $unsuballurl )
					      				.	'" method="post" name="postform" id="postform">'
					      				.	'<input type="hidden" name="do" value="update" />'
					      				.	'<input type="checkbox" onclick="if (document.forms[\'postform\'].elements[\'unsubscribeAll\'].checked=true && confirm(\''._UE_SB_CONFIRMUNSUBSCRIBEALL.'\')) { document.forms[\'postform\'].submit(\'Submit\')} else {document.forms[\'postform\'].elements[\'unsubscribeAll\'].checked=false};" name="unsubscribeAll" value="1" />'
					      				.	'<i>'._UE_USER_UNSUBSCRIBE_ALL."</i></form></td>\n\t</tr>";
					break;
				default:
					foreach ( $subslist as $subs ) {
						//get all message details for each subscription:
			  			$_CB_database->setQuery( "SELECT * FROM #__" . $this->sql_prefix . "_messages WHERE id = " . (int) $subs->thread );
			  			$subdet			=	$_CB_database->loadObjectList();
			  			foreach ( $subdet as $sub ) {
			  				$k			=	1 - $k;
			  				$return		.=	"\n\t<tr class=\"" . $tabclass[$k] . "\">";
			  				$return		.=	"\n\t\t<td>" . $enum . ": <a href=\""
					  					.	cbSef('index.php?option='.$this->com_name.($Itemid ? '&amp;Itemid='. (int) $Itemid : '').'&amp;func=view&amp;catid='. (int) $sub->catid.'&amp;id='. (int) $sub->id)
					  					.	'">'. htmlspecialchars( $this->_sbUnescape($sub->subject) ) .'</a> - ' ._UE_GEN_BY. ' ' . htmlspecialchars( $sub->name ) ."</td>";
			  				$return 	.=	"\n\t\t<td><a href=\""
					  					.	cbSef('index.php?option='.$this->com_name.($Itemid ? '&amp;Itemid='. (int) $Itemid : '').$unsuburl. (int) $subs->thread)
					  					.	'">' ._UE_THREAD_UNSUBSCRIBE. "</a></td>";
			  				$return .= "\n\t</tr>";
			  				$enum++;
			  			}
		  			}
		  			$return				.=	"\n\t<tr>\n\t\t<td colspan=\"2\"><form action=\""
					      				.	cbSef('index.php?option='.$this->com_name.($Itemid ? '&amp;Itemid='. (int) $Itemid : '').$unsuballurl)
					      				.	'" method="post" name="postform" id="postform">'
					      				.	'<input type="hidden" name="do" value="update" />'
					      				.	'<input type="checkbox" onclick="if (document.forms[\'postform\'].elements[\'unsubscribeAll\'].checked=true && confirm(\''._UE_SB_CONFIRMUNSUBSCRIBEALL.'\')) { document.forms[\'postform\'].submit(\'Submit\')} else {document.forms[\'postform\'].elements[\'unsubscribeAll\'].checked=false};" name="unsubscribeAll" value="1" />'
					      				.	'<i>'._UE_USER_UNSUBSCRIBE_ALL."</i></form></td>\n\t</tr>";
	      			break;
		  	}
		  } else {
		  	$return						.=	"\n\t<tr><td>"._UE_USER_NOSUBSCRIPTIONS."</td>\n\t</tr>";
		  }

		  $return						.=	"\n</table></div><br />";
		}

		// For the profile owner only: Favorites management:
		if ( ( $this->com_name == 'com_fireboard' ) && ( $_CB_framework->myId() == $user->id ) && $fbConfig['allowfavorites'] ) {
			$_CB_database->setQuery( "SELECT thread FROM #__" . $this->sql_prefix . "_favorites WHERE userid = " . (int) $_CB_framework->myId() );
			$favslist						=	$_CB_database->loadObjectList();
			$cfavslist						=	count( $favslist );
			$return							.=	"<br /><div class=\"sectiontableheader\" style=\"text-align:left;padding-left:0px;padding-right:0px;margin:0px 0px 10px 0px;height:auto;width:100%;\">"
											.	( defined('_UE_FB_FAVORITES') ? getLangDefinition( "_UE_FB_FAVORITES" ) :
												( ( defined('_UE_FB_CONFIRMUNFAVORITEALL') ) ? getLangDefinition( "_UE_FB_CONFIRMUNFAVORITEALL" ) : "" ) )
											.	"<br />";
			$return							.=	 "\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"margin:0px;padding:0px;width:100%;\">";
			$enum							=	1;		//reset value
			$tabclass						=	array("sectiontableentry1", "sectiontableentry2");//alternating row CSS classes
			$k								=	1;		//value for alternating rows
			if ( $cfavslist > 0 ) {
				if ( $this->com_name == 'com_fireboard' ) {
					$unfavurl				=	'&amp;fbunfavthread=';
					$unfavallurl			=	'&amp;fbunfavthread=all';
				}
				switch( $this->com_name ) {
					case 'com_fireboard':
						foreach ( $favslist as $favs ) {
							//get all message details for each subscription:
							$_CB_database->setQuery("SELECT * FROM #__" . $this->sql_prefix . "_messages WHERE id = " . (int) $favs->thread );
							$subdet			=	$_CB_database->loadObjectList();
							foreach ( $subdet as $sub ) {
								$k			=	1 - $k;
								$return		.=	"\n\t<tr class=\"".$tabclass[$k]."\">";
								$return		.=	"\n\t\t<td>".$enum.": <a href=\""
								.	cbSef( 'index.php?option='.$this->com_name.($Itemid ? '&amp;Itemid='.(int) $Itemid : '').'&amp;func=view&amp;catid='.(int) $sub->catid.'&amp;id='.(int) $sub->id )
								.	'">'. htmlspecialchars( $this->_sbUnescape($sub->subject) ) .'</a> - ' ._UE_GEN_BY. ' ' . htmlspecialchars( $sub->name ) ."</td>";
								$return		.=	"\n\t\t<td><a href=\""
								.	cbSef( $base_url . $unfavurl . $favs->thread )
								.	'">' ._UE_FB_REMOVE_FAVORITE_THREAD. "</a></td>";
								$return		.=	"\n\t</tr>";
								$enum++;
							}
						}
						$return				.=	"\n\t<tr>\n\t\t<td colspan=\"2\"><form action=\""
						.cbSef( $base_url . $unfavallurl )
						.'" method="post" name="postfavform" id="postfavform">'
						.'<input type="hidden" name="do" value="favupdate" />'
						.'<input type="checkbox" onclick="if (document.forms[\'postfavform\'].elements[\'unfavoriteAll\'].checked=true && confirm(\''._UE_FB_CONFIRMUNFAVORITEALL.'\')) { document.forms[\'postfavform\'].submit(\'Submit\')} else {document.forms[\'postfavform\'].elements[\'unfavoriteAll\'].checked=false};" name="unfavoriteAll" value="1" />'
						.'<i>'._UE_FB_REMOVE_FAVORITES_ALL."</i></form></td>\n\t</tr>";
						break;
				}
			} else{
				$return						.=	"\n\t<tr><td>"._UE_FB_NO_FAVORITES_FOUND."</td>\n\t</tr>";
			}

			$return 						.=	"\n</table></div><br />";
		}

		return $return;
	}
	function _fbunsubthread( &$user, $thread ) {
		global $_CB_database;
		
		$_CB_database->setQuery("DELETE FROM #__fb_subscriptions WHERE userid=".(int)$user->user_id . " AND thread=".(int)$thread);
		$_CB_database->query();
	}
	function _fbunsuball( &$user ) {
		global $_CB_database;
		
		$_CB_database->setQuery("DELETE FROM #__fb_subscriptions WHERE userid=".(int)$user->user_id);
		$_CB_database->query();
	}
	function _fbunfavthread( &$user, $thread ) {
		global $_CB_database;
		
		$_CB_database->setQuery("DELETE FROM #__fb_favorites WHERE userid=".(int)$user->user_id . " AND thread=".(int)$thread);
		$_CB_database->query();
	}
	function _fbunfavall( &$user ) {
		global $_CB_database;
		
		$_CB_database->setQuery("DELETE FROM #__fb_favorites WHERE userid=".(int) $user->user_id);
		$_CB_database->query();
	}
}	// end class getForumTab.
?>
