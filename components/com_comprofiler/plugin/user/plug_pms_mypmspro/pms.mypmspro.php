<?php
/**
* PMS Handler and Tab Class for handling the CB tab api
* @version $Id: pms.mypmspro.php 563 2006-11-18 14:32:37Z beat $
* @package Community Builder
* @subpackage pms.mypmspro.php
* @author JoomlaJoe and Beat, UddeIM part contributed by Benjamin, PMS Enhanced part contributed by Stefan, JIM part contributed by Nick
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

// register delete user code function
$_PLUGINS->registerFunction( 'onAfterDeleteUser', 'userDeleted','getmypmsproTab' );

class getmypmsproTab extends cbPMSHandler {
	/**
	* Constructor
	*/
	function getmypmsproTab() {
		$this->cbPMSHandler();
	}
	function _setStatusMenuSBstats($sbConfig, $user, &$params, $sbUserDetails) {
/* already done in the core cb.menu plugin:
		global $_CB_framework;
		IF($_CB_framework->myId()!=$user->id && $_CB_framework->myId() > 0) {
			$pmsurl=...
			$mi = array(); $mi["_UE_MENU_MESSAGES"]["_UE_PM_USER"]=null;
			$this->menuBar->addObjectItem($mi, _UE_PM_USER,cbSef($pmsurl), "",
			"","", _UE_MENU_PM_USER_DESC,"");
		}
*/
/* example for a status display:
		if ($sbConfig['postStats'] && ($params->get('statPosts', '1') == 1)) {
			$mi = array(); $mi["_UE_MENU_STATUS"][$params->get('statPostsText', "_UE_FORUM_TOTALPOSTS")]["_UE_FORUM_TOTALPOSTS"]=null;
			$_PLUGINS->addMenu( array(	"position"	=> "menuList" ,
								"arrayPos"	=> $mi ,
								"caption"	=> (($sbUserDetails !== false) ? $sbUserDetails->posts : "0") ,
								"url"		=> "" ,
								"target"	=> "" ,
								"img"		=> null ,
								"alt"		=> null ,
								"tooltip"	=> "") );
		}
*/
	}
	function _checkPMSinstalled($pmsType) {
		global $_CB_framework;
		
		$absolutePath = $_CB_framework->getCfg('absolute_path');
		
		if (!(	(($pmsType==1 || $pmsType==5) && file_exists( $absolutePath . '/components/com_pms/pms.php' ))
			||	($pmsType==2 && file_exists( $absolutePath . '/components/com_mypms/mypms.php' ))
			||	($pmsType==6 && file_exists( $absolutePath . '/components/com_jim/jim.php'	))
			||	(($pmsType==3 || $pmsType==4) && file_exists( $absolutePath . '/components/com_uddeim/uddeim.php' )))) {
			$this->_setErrorMSG(_UE_PMS_NOTINSTALLED);
			return false;
		}
		return true;
	}
	function _sendPMSProMSG($to,$from,$sub,$msg) {
		global $_CB_database;

		// escaping not necessary, already escaped before this internal function gets called
		$sql="INSERT INTO #__mypms (username,whofrom,time,readstate,subject,message,owner,sent_id) VALUES('"
			.$_CB_database->getEscaped($to)."','".$_CB_database->getEscaped($from)."',NOW(),0,'".$sub."','".$msg."','".$to."',0);";
		$_CB_database->SetQuery($sql);
		if (!$_CB_database->query()) {
			die("SQL error" . $_CB_database->stderr(true));
		}
	
	}
	function _sendPMSOSMSG($to,$from,$sub,$msg) {
		global $_CB_database;

		// escaping not necessary, already escaped before this internal function gets called
		$sql="INSERT INTO #__pms (username,whofrom,date,time,readstate,subject,message) VALUES('"		// MyPMS II
			.$_CB_database->getEscaped($to)."','".$_CB_database->getEscaped($from)."',CURDATE(),CURTIME(),0,'".$sub."','".$msg."');";
		$_CB_database->SetQuery($sql);
		if (!$_CB_database->query()) {
			$sql = "INSERT INTO #__pms (username,whofrom,date,readstate,subject,message) VALUES('"		// PMS OS
			.$to."','".$from."',NOW(),0,'".$sub."','".$msg."');";
			$_CB_database->SetQuery($sql);
			if (!$_CB_database->query()) {
				die("SQL error" . $_CB_database->stderr(true));
			}
		}
	}
	function _sendPMSJimMSG($to,$from,$sub,$msg) {
		global $_CB_database;
		
		// escaping not necessary, already escaped before this internal function gets called
		$sql="INSERT INTO #__jim (username,whofrom,date,readstate,subject,message) VALUES('"
			.$_CB_database->getEscaped($to)."','".$_CB_database->getEscaped($from)."',NOW(),0,'".$sub."','".$msg."');";
		$_CB_database->SetQuery($sql);
		if (!$_CB_database->query()) {
			die("SQL error" . $_CB_database->stderr(true));
		}
	}
	function _sendPMSenhancedMSG($to_id,$from_id,$sub,$msg) {
		global $_CB_framework, $_CB_database;

		// escaping not necessary, already escaped before this internal function gets called
		$sql="INSERT INTO #__pms (recip_id,sender_id,date,time,readstate,subject,message) VALUES(".$to_id.",".$from_id.",CURDATE(),CURTIME(),0,'".$sub."','".$msg."');";
		$_CB_database->SetQuery($sql);
		if (!$_CB_database->query()) {
			die("SQL error" . $_CB_database->stderr(true));
		}
		
		
		// email notification
		
		require_once( "administrator/components/com_pms/config.pms.php" );
		
		// get the right language if it exists
		if (file_exists('administrator/components/com_pms/language/'.$_CB_framework->getCfg( 'lang' ).'.php')) {
			include_once( 'administrator/components/com_pms/language/'.$_CB_framework->getCfg( 'lang' ).'.php' );
		}
		else include_once('administrator/components/com_pms/language/english.php');
		
		// get default configuration from database
		$_CB_database->setQuery("SELECT email_new, email_html, email_offline FROM #__pms_conf WHERE user_id=0");
		if (!$_CB_database->query()) {
			die("SQL error" . $_CB_database->stderr(true));
		}
		$rows = $_CB_database->loadObjectList();
		$row = $rows[0];
		$email_new = $row->email_new;
		$email_html = $row->email_html;
		$email_offline = $row->email_offline;
		
		// check settings of recip and override defaults if allowed
		if($allow_change_email_new==1)
		{
			// check if recip has personal settings, otherwise load defaults
			$_CB_database->setQuery("SELECT count(id) FROM #__pms_conf WHERE user_id=$to_id LIMIT 1");
			if(!$_CB_database->query()) die("SQL error" . $_CB_database->stderr(true));
			$result = $_CB_database->loadResult();
			if($result==1)
			{
				$_CB_database->setQuery("SELECT email_new, email_html, email_offline FROM #__pms_conf WHERE user_id=$to_id");
				if(!$_CB_database->query()) die("SQL error" . $_CB_database->stderr(true));
				$rows = $_CB_database->loadObjectList();
				$row = $rows[0];
				if($allow_change_email_new==1) $email_new = $row->email_new;
				if($allow_change_email_html==1) $email_html = $row->email_html;
				if($allow_change_email_offline==1) $email_offline = $row->email_offline;
			}
		}
		
		// send email notification
		if($email_new==1)
		{
			// get name and email of recip
			$_CB_database->setQuery("SELECT username, email FROM #__users WHERE id=$to_id");
			if(!$_CB_database->query()) die("SQL error" . $_CB_database->stderr(true));
			$rows = $_CB_database->loadObjectList();
			$row = $rows[0];
			$recip_name = $row->username;
			$recip_email = $row->email;
		
			// check if recip is offline
			$_CB_database->setQuery("SELECT count(session_id) FROM #__session WHERE userid=$to_id");
			if(!$_CB_database->query()) die("SQL error" . $_CB_database->stderr(true));
			$result = $_CB_database->loadResult();
			if($result==0 OR $email_offline==0)
			{
				$email_site_name = $_CB_framework->getCfg( 'sitename' );
				$email_sender_name = $_CB_framework->myUsername();
				$_CB_database->setQuery("SELECT email FROM #__users WHERE id=$from_id LIMIT 1");
				if(!$_CB_database->query()) die("SQL error" . $_CB_database->stderr(true));
				$email_sender_email = $_CB_database->loadResult();
				$email_recip_name = $recip_name;
				$email_recip_email = $recip_email;
				$message = stripslashes($msg);
				$subject = stripslashes($sub);
				if($email_html==1) $message = nl2br($message);
				
				$email_subject = sprintf(_PMS_EMAIL_SUBJECT_NEW, $email_site_name, $email_sender_name);
				if($email_html==0) $email_message = sprintf(_PMS_EMAIL_MESSAGE_NEW_TEXT, $email_recip_name, $email_sender_name, $email_site_name, $subject, $message, $email_site_name);
				else $email_message = sprintf(_PMS_EMAIL_MESSAGE_NEW_HTML, $email_recip_name, $email_sender_name, $email_site_name, $subject, $message, $email_site_name);

				comprofilerMail( $email_sender_email, $email_sender_name, $email_recip_email, $email_subject, $email_message, $email_html );
			} // end check if recip is offline
		} // end send email notification
	}
	
	function _sendPMSuddesysMSG($udde_toid,$udde_fromid,$to,$from,$sub,$msg) {
		global $_CB_database, $_CB_framework; 

		$params = $this->params;
		$pmsType = $params->get('pmsType', '1');
        $udde_sysm = "System";
        $config_realnames = "0";
        $config_cryptmode = 0;
        $config_cryptkey = 'uddeIMcryptkey';
        
		if ( ( $pmsType==4 ) && file_exists( $_CB_framework->getCfg('absolute_path') . "/components/com_uddeim/crypt.class.php" ) ) { // uddeIM 1.0+
			require_once( $_CB_framework->getCfg('absolute_path') . "/components/com_uddeim/crypt.class.php");

			if ( file_exists( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/config.class.php")) {
				include_once( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/config.class.php");
			}
			$config = new uddeimconfigclass();
			if ( isset($config->sysm_username)) {
				$udde_sysm = $config->sysm_username;		
			}
			if (isset($config->realnames)) {
				$config_realnames = $config->realnames;
			}
			if (isset($config->cryptmode)) {
				$config_cryptmode = $config->cryptmode;
			}
			if (file_exists( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_crypt.php" )) {
				require_once ( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_crypt.php" );
			}
			if (isset($config->cryptkey)) {
				$config_cryptkey = $config->cryptkey;
			}

		} else {
			if(file_exists( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_config.php")) {
				include_once( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_config.php");
			}
			if(isset($config_sysm_username)) {
				$udde_sysm = $config_sysm_username;
			}
		}		

		// format the message
		if($sub) {
			$udde_msg = "[b]".$sub."[/b]\n\n".$msg;
		} else {
			$udde_msg = $msg;
		}
		
		// now change the <strong> or <b> tags to BB Code
		$udde_msg = str_replace("<strong>","[b]",$udde_msg);
		$udde_msg = str_replace("<b>","[b]",$udde_msg);
		$udde_msg = str_replace("</strong>","[/b]",$udde_msg);
		$udde_msg = str_replace("</b>","[/b]",$udde_msg);
		
		// now change the links to BB code links
		$udde_msg = str_replace("<a href=\"", "[url=", $udde_msg);
		$udde_msg = str_replace("<a href=\\\"", "[url=", $udde_msg);		
		$udde_msg = str_replace("\">", "]", $udde_msg);
		$udde_msg = str_replace("\\\">", "]", $udde_msg);		
		$udde_msg = str_replace("</a>", "[/url]", $udde_msg);
		$udde_msg = str_replace("<br/>", "\n", $udde_msg);
		$udde_msg = str_replace("<br />", "\n", $udde_msg);
		$udde_msg = str_replace("<br>", "\n", $udde_msg);
		$udde_msg = str_replace("&amp;", "&", $udde_msg);
		
		// workaround
		// commands above made the closing bracket of the div to a ]
		// we change it back to a > here so that the next command can strip the div entirely
		$udde_msg = str_replace("cbNotice\\\"]", "cbNotice\\\">", $udde_msg);
		$udde_msg = str_replace("cbNotice]", "cbNotice\">", $udde_msg);
		$udde_msg = str_replace("cbNotice\\]", "cbNotice\">", $udde_msg);
		
		// now strip the remaining html tags
		$udde_msg = strip_tags($udde_msg);
		
		// get current time but recognize time offset
		$currentTime=time();
		$udde_time=$this->_pmsUddeGetTime($currentTime);
		
		// set the udde systemmessage username to the virtual sender
		$udde_sysm=$from;
		
		// try to find the realnames settings of udde
		// if(file_exists( $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_uddeim/uddeim_config.php')) {
			// include_once( $_CB_framework->getCfg('absolute_path') . '/administrator/components/com_uddeim/uddeim_config.php');
		if($config_realnames) {
			$sql="SELECT name FROM #__users WHERE id=".(int) $udde_fromid;
			$_CB_database->setQuery($sql);
			$quereply=$_CB_database->loadResult();
			if($quereply) {
				$udde_sysm=$quereply;
			}
		}

		if ($config_cryptmode==1) {
            if (function_exists('uddeIMencrypt')) { // this added for uddeIM 1.4+
			    $cm = uddeIMencrypt($udde_msg,$config_cryptkey,CRYPT_MODE_BASE64);
            } else {
                $cm = Encrypt($udde_msg,$config_cryptkey,CRYPT_MODE_BASE64);    
            }
			$sql="INSERT INTO #__uddeim (fromid, toid, message, datum, systemmessage, disablereply, cryptmode, crypthash) VALUES (".$udde_fromid.", ".$udde_toid.", '".$cm."', ".$udde_time.", '".$udde_sysm."', 0, 1,'".md5($config_cryptkey)."')";
		} else {
			$sql="INSERT INTO #__uddeim (fromid, toid, message, datum, systemmessage, disablereply) VALUES (".$udde_fromid.", ".$udde_toid.", '".$udde_msg."', ".$udde_time.", '".$udde_sysm."', 0)";
		}

		
		// escaping not necessary, already escaped before this internal function gets called
		// now insert the message as system message 
		// REPLY IS NOT DISABLED AS THE SYSTEMMESSAGE USERNAME WILL CONTAIN A VALID USERNAME
		if($udde_fromid && $udde_toid) {
			$_CB_database->SetQuery($sql);
			if (!$_CB_database->query()) {
				die("SQL error" . $_CB_database->stderr(true));
			}
		}
		
		// E-Mail notification code
		$this->_pmsUddeNotify($udde_fromid, $udde_toid, $udde_msg, $udde_sysm);
		
	}
	function _sendPMSuddeimMSG($udde_toid,$udde_fromid,$to,$from,$sub,$msg) {
		global $_CB_database, $_CB_framework; 

		$params = $this->params;
		$pmsType = $params->get('pmsType', '1');
        $udde_sysm = "System";
        $config_realnames = "0";
        $config_cryptmode = 0;
        $config_cryptkey = 'uddeIMcryptkey';
        
		if ($pmsType==4) { // uddeIM 1.0+
			require_once( $_CB_framework->getCfg('absolute_path') . "/components/com_uddeim/crypt.class.php");
			
			if(file_exists( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/config.class.php")) {
				include_once( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/config.class.php");
			}
			$config = new uddeimconfigclass();
			if(isset($config->sysm_username)) {
				$udde_sysm = $config->sysm_username;		
			}
			if (isset($config->realnames)) {
				$config_realnames = $config->realnames;
			}
			if (isset($config->cryptmode)) {
				$config_cryptmode = $config->cryptmode;
			}
            if (file_exists( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_crypt.php" )) {
				require_once ( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_crypt.php" );
			}
			if (isset($config->cryptkey)) {
				$config_cryptkey = $config->cryptkey;
			}
		} else {
			if(file_exists( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_config.php")) {
				include_once( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_config.php");
			}
			if(isset($config_sysm_username)) {
				$udde_sysm = $config_sysm_username;
			}
		}		
		// format the message
		if($sub) { // is actually impossible
			$udde_msg = "[b]".$sub."[/b]\n\n".$msg;
		} else {
			$udde_msg = $msg;
		}
		
		// strip any bb code that might be present, but only in 0.4
		if($pmsType==3) {
			require_once ( $_CB_framework->getCfg('absolute_path') . '/components/com_uddeim/bbparser.php' );
			$udde_msg=bbcode_strip($udde_msg);
		}
		
		// now strip the remaining html tags
		$udde_msg = strip_tags($udde_msg);
				
		// escape dangerous stuff
		// not necessary, already escaped before this internal function gets called
		
		// get current time but recognize time offset
		$currentTime=time();
		$udde_time=$this->_pmsUddeGetTime($currentTime);
		
		// set the udde systemmessage username to the virtual sender
		
		$udde_sysm=$from;

		if ($config_cryptmode==1) {
            if (function_exists('uddeIMencrypt')) { // this added for uddeIM 1.4+
                $cm = uddeIMencrypt($udde_msg,$config_cryptkey,CRYPT_MODE_BASE64);
            } else {
   			    $cm = Encrypt($udde_msg,$config_cryptkey,CRYPT_MODE_BASE64);
            }
   			$sql="INSERT INTO #__uddeim (fromid, toid, message, datum, cryptmode, crypthash) VALUES (".$udde_fromid.", ".$udde_toid.", '".$cm."', ".$udde_time.",1,'".md5($config_cryptkey)."')";
   		} else {
   			$sql="INSERT INTO #__uddeim (fromid, toid, message, datum) VALUES (".$udde_fromid.", ".$udde_toid.", '".$udde_msg."', ".$udde_time.")";
		}
			
		// now insert the message  
		if($udde_fromid && $udde_toid) {
			$_CB_database->SetQuery($sql);
			if (!$_CB_database->query()) {
				die("SQL error" . $_CB_database->stderr(true));
			}
		}

		// E-Mail notification code
		$udde_sysm="";
		$this->_pmsUddeNotify($udde_fromid, $udde_toid, $udde_msg, $udde_sysm);
		
	}
	
	
	/**
	* Sends a PMS message
	* @param int userId of receiver (ESCAPED)
	* @param int userId of sender (ESCAPED)
	* @param string subject of PMS message (ESCAPED Subject) 
	* @param string body of PMS message (html, ESCAPED Body)
	* @param boolean false: real user-to-user message = default; true: system-Generated by an action from user $fromid (if non-null)
	* @param boolean false: subject and message body UNESCAPED = default; true: ESCAPED
	* @return boolean : true for OK, or false if ErrorMSG generated. Special error: _UE_PMS_TYPE_UNSUPPORTED : if anonym fromid>=0 sysgenerated unsupported
	*/
	function sendUserPMS($toid, $fromid, $subject, $message, $systemGenerated=false, $escaped=false) {
		global $_CB_database;

		$params = $this->params;
		$pmsType = $params->get('pmsType', '1');

		if (!$this->_checkPMSinstalled($pmsType)) {
			return false;
		}

		$toid	= (int) $toid;
		$fromid	= (int) $fromid;
		if (!$escaped) {
			$subject = $_CB_database->getEscaped($subject);
			$message = $_CB_database->getEscaped($message);
		}

		if ($systemGenerated && ($fromid == 0)) {
			if (in_array($pmsType,array(1,2,6))) {
				$this->_setErrorMSG(_UE_PMS_TYPE_UNSUPPORTED);		// PMS OS, MyPMS Pro and JIM do not handle systemGenerated from nobody)
				return false;
			}
		}

		if ($fromid != 0) {
			$rowFrom = new moscomprofilerUser( $_CB_database );
			$rowFrom->load( (int) $fromid );
			$from = $rowFrom->username;
		} else {
			$from = null;
		}
		
		$rowTo = new moscomprofilerUser( $_CB_database );
		$rowTo->load( (int) $toid );	
		$to=$rowTo->username;
		
		SWITCH($pmsType) {
			case 1:		//MyPMS OS
				$this->_sendPMSOSMSG($to,$from,$subject,$message);
				return true;
				break;
			case 2:		//PMS Pro
				$this->_sendPMSProMSG($to,$from,$subject,$message);
				return true;
				break;
			case 3:		//UddeIM 0.4
			case 4:		//UddeIM 1.0
				if($systemGenerated || $fromid==0) {
					$this->_sendPMSuddesysMSG($toid,$fromid,$to,$from,$subject,$message);
				} else {
					$this->_sendPMSuddeimMSG($toid,$fromid,$to,$from,$subject,$message);				
				}
				return true;
				break;
			case 5:		//PMS enhanced 2.x by Stefan Klingner
				$this->_sendPMSenhancedMSG($toid,$fromid,$subject,$message);
				return true;
				break;
			case 6:		//JIM 1.0.1
				$this->_sendPMSJimMSG($to,$from,$subject,$message);
				return true;
				break;
			default:
				$this->_setErrorMSG("Incorrect PMS type");
				return false;
				break;
		}
	}
	/**
	* returns all the parameters needed for a hyperlink or a menu entry to do a pms action
	* @param int userId of receiver
	* @param int userId of sender
	* @param string subject of PMS message
	* @param string body of PMS message
	* @param int kind of link: 1: link to compose new PMS message for $toid user. 2: link to inbox of $fromid user; 3: outbox, 4: trashbox,
	  5: link to edit pms options
	* @return mixed array of string {"caption" => menu-text ,"url" => NON-cbSef relative url-link, "tooltip" => description} or false and errorMSG
	*/
	function getPMSlink($toid, $fromid, $subject, $message, $kind) {
		global $_CB_database;

		$params = $this->params;
		$pmsType = $params->get('pmsType', '1');

		if (!$this->_checkPMSinstalled($pmsType)) {
			return false;
		}
		
		SWITCH($pmsType) {
			case 1:		//MyPMS OS
				$rowTo = new moscomprofilerUser( $_CB_database );
				$rowTo->load( (int) $toid );	
				$pmsurlBase="index.php?option=com_pms";
				$pmsurlSend=$pmsurlBase."&amp;page=new&amp;id=".urlencode($rowTo->username);
				$pmsurlInbox=$pmsurlBase."&amp;page=index";
				$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_pms%'";
				break;
			case 2:		//PMS Pro
				$rowTo = new moscomprofilerUser( $_CB_database );
				$rowTo->load( (int) $toid );	
				$pmsurlBase="index.php?option=com_mypms";
				$pmsurlSend=$pmsurlBase."&amp;task=new&amp;to=".urlencode($rowTo->username);
				$pmsurlInbox=$pmsurlBase."&amp;task=inbox";
				$pmsurlOutbox=$pmsurlBase."&amp;task=sent";
				$pmsurlTrashbox=$pmsurlBase."&amp;task=trash";
				$pmsurlOptions=$pmsurlBase."&amp;task=editprofile";
				$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_mypms%'";
				break;
			case 3:		//UddeIM 0.4
				$pmsurlBase="index.php?option=com_uddeim";
				$pmsurlSend=$pmsurlBase."&amp;task=new&amp;recip=".$toid;
				$pmsurlInbox=$pmsurlBase."&amp;task=inbox";
				$pmsurlOutbox=$pmsurlBase."&amp;task=outbox";
				$pmsurlTrashbox=$pmsurlBase."&amp;task=trashcan";
				$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_uddeim%'";
				break;		
			case 4:		//UddeIM 1.0
				$pmsurlBase="index.php?option=com_uddeim";
				$pmsurlSend=$pmsurlBase."&amp;task=new&amp;recip=".$toid;
				$pmsurlInbox=$pmsurlBase."&amp;task=inbox";
				$pmsurlOutbox=$pmsurlBase."&amp;task=outbox";
				$pmsurlTrashbox=$pmsurlBase."&amp;task=trashcan";
				$pmsurlOptions=$pmsurlBase."&amp;task=settings";
				$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_uddeim%'";
				break;							
			case 5:		//PMS enhanced 2.x by Stefan Klingner
				$rowTo = new moscomprofilerUser( $_CB_database );
				$rowTo->load( (int) $toid );	
				$pmsurlBase="index.php?option=com_pms";
				$pmsurlSend=$pmsurlBase."&amp;page=new&amp;id=".urlencode($rowTo->username);
				$pmsurlInbox=$pmsurlBase."&amp;page=index";
				$pmsurlOutbox=$pmsurlBase."&amp;page=sent_items";
				$pmsurlTrashbox=$pmsurlBase."&amp;page=trash";
				$pmsurlOptions=$pmsurlBase."&amp;page=settings";
				$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_pms%'";
				break;
			case 6:		//JIM 1.0.1
				$rowTo = new moscomprofilerUser( $_CB_database );
				$rowTo->load( (int) $toid );	
				$pmsurlBase="index.php?option=com_jim";
				$pmsurlSend=$pmsurlBase."&amp;page=new&amp;id=".urlencode($rowTo->username);
				$pmsurlInbox=$pmsurlBase."&amp;page=index";
				$query_pms_link = "SELECT id FROM #__menu WHERE published>=0 AND link LIKE '%com_jim%'";
				break;
			default:
				$this->_setErrorMSG("Incorrect PMS type");
				return false;
				break;
		}
		$_CB_database->setQuery( $query_pms_link );
		$pms_id = $_CB_database->loadResult();
		if ($pms_id) {
			$pmsitemid = "&amp;Itemid=".$pms_id;
		} else {
			$pmsitemid = null;
		}

		switch($kind) {
			case 1:
				return array("caption"	=> $params->get('pmsMenuText', _UE_PM_USER),
							 "url"		=> $pmsurlSend.$pmsitemid,
							 "tooltip"	=> $params->get('pmsMenuDesc', _UE_MENU_PM_USER_DESC));
				break;
			case 2:
				return array("caption"	=> $params->get('pmsMenuInboxText', _UE_PM_INBOX),
							 "url"		=> $pmsurlInbox.$pmsitemid,
							 "tooltip"	=> $params->get('pmsMenuInboxDesc', _UE_MENU_PM_INBOX_DESC));
				break;
			case 3:
				if ($pmsType != 1 && $pmsType !=6) return array("caption"	=> $params->get('pmsMenuOutboxText', _UE_PM_OUTBOX),
												"url"		=> $pmsurlOutbox.$pmsitemid,
												"tooltip"	=> $params->get('pmsMenuOutboxDesc', _UE_MENU_PM_OUTBOX_DESC));
				break;
			case 4:
				if ($pmsType != 1 && $pmsType !=6) return array("caption"	=> $params->get('pmsMenuTrashboxText', _UE_PM_TRASHBOX),
												"url"		=> $pmsurlTrashbox.$pmsitemid,
												"tooltip"	=> $params->get('pmsMenuTrashboxDesc', _UE_MENU_PM_TRASHBOX_DESC));
				break;
			case 5:
				if ($pmsType == 2 || $pmsType == 5) return array("caption"	=> $params->get('pmsMenuOptionsText', _UE_PM_OPTIONS),
												"url"		=> $pmsurlOptions.$pmsitemid,
												"tooltip"	=> $params->get('pmsMenuOptionsDesc', _UE_MENU_PM_OPTIONS_DESC));
				break;

			default:
			break;
		}
		$this->_setErrorMSG("Function not supported by this PMS type");
		return false;
	}
	/**
	* gets PMS system capabilities
	* @return mixed array of string {"subject" => boolean ,"body" => boolean} or false if ErrorMSG generated
	*/
	function getPMScapabilites() {
		$params = $this->params;
		$pmsType		= $params->get('pmsType', '1');

		if (!$this->_checkPMSinstalled($pmsType)) {
			return false;
		}
		
		SWITCH($pmsType) {
			case 1:
			case 2:
			case 6:
				$capacity = array( "subject" => true, "body" => true);
				break;
			case 3:
			case 4:
				$capacity = array( "subject" => false, "body" => true);
				break;
			case 5:
				$capacity = array( "subject" => true, "body" => true);
				break;
			default:
				$this->_setErrorMSG("Incorrect PMS type");
				$capacity = false;
				break;
		}
		return $capacity;
	}
	/**
	* gets PMS unread messages count
	* @param	int user id
	* @return	mixed number of messages unread by user $userid or false if ErrorMSG generated
	*/
	function getPMSunreadCount($userid) {
		global $_CB_database;

		$params = $this->params;
		$pmsType = $params->get('pmsType', '1');

		if (!$this->_checkPMSinstalled($pmsType)) {
			return false;
		}

		$user = new moscomprofilerUser( $_CB_database );
		$user->load( (int) $userid );
		
		SWITCH($pmsType) {
			case 1:
				$query_pms_count = "SELECT count(id) FROM #__pms WHERE username='" . $_CB_database->getEscaped($user->username) ."' AND readstate=0";
				$_CB_database->setQuery( $query_pms_count );
				$total_pms = $_CB_database->loadResult();
				break;
			case 2:
				$query_pms_count = "SELECT count(id) FROM #__mypms WHERE username='" . $_CB_database->getEscaped($user->username) ."' AND readstate=0";
				$_CB_database->setQuery( $query_pms_count );
				$total_pms = $_CB_database->loadResult();
				break;
			case 3:
			case 4:
				$sql="SELECT count(id) FROM #__uddeim WHERE toread<1 AND toid=".(int) $userid;
				$_CB_database->setQuery($sql);
				$total_pms = $_CB_database->loadResult();	
				break;			
			case 5:
				$query_pms_count = "SELECT count(id) FROM #__pms WHERE recip_id=" . (int) $userid ." AND readstate%2=0 AND inbox=1";
				$_CB_database->setQuery( $query_pms_count );
				$total_pms = $_CB_database->loadResult();
				break;
			case 6:
				$query_pms_count = "SELECT count(id) FROM #__jim WHERE username='" . $_CB_database->getEscaped($user->username) ."' AND readstate=0";
				$_CB_database->setQuery( $query_pms_count );
				$total_pms = $_CB_database->loadResult();
				break;
			default:
				$this->_setErrorMSG("Incorrect PMS type");
				$total_pms = false;
				break;
		}
		return $total_pms;
	}

	/**
	* Generates the HTML to display the user profile tab
	* @param  moscomprofilerTab   $tab       the tab database entry
	* @param  moscomprofilerUser  $user      the user being displayed
	* @param  int                 $ui        1 for front-end, 2 for back-end
	* @return mixed                          either string HTML for tab content, or false if ErrorMSG generated
	*/
	function getDisplayTab($tab,$user,$ui) {
		global $_CB_framework, $_POST, $_CB_OneTwoRowsStyleToggle;

		if ( ! $_CB_framework->myId() ) {
			return null;
		}

		$return = "";

		$params = $this->params;
		$pmsType		= $params->get('pmsType', '1');
		$showTitle		= $params->get('showTitle', "1");
		$showSubject	= $params->get('showSubject', "1");
		$width			= $params->get('width', "30");
		$height			= $params->get('height', "5");

		$capabilities = $this->getPMScapabilites();

		if (!$this->_checkPMSinstalled($pmsType) || ($capabilities === false)) {
			return false;
		}
		if ($_CB_framework->myId() == $user->id) {
			return null;
		}

		$newsub = null;
		$newmsg = null;

		// send PMS from this tab form input:
		if ( cbGetParam( $_POST, $this->_getPagingParamName("sndnewmsg") ) == _UE_PM_SENDMESSAGE ) {
			$sender = $this->_getReqParam("sender", null);
			$recip = $this->_getReqParam("recip", null);
			if ( $sender && $recip && ( $sender == $_CB_framework->myId() ) && ( $recip == $user->id ) ) {
				cbSpoofCheck( 'pms' );
				$newsub = htmlspecialchars($this->_getReqParam("newsub", null));	//urldecode done in _getReqParam
				if($pmsType=='3' || $pmsType=='4') {
					$newmsg = $this->_getReqParam("newmsg", null);	
				} else {
					$newmsg = htmlspecialchars($this->_getReqParam("newmsg", null));	//don't allow html input on user profile!
				}
				if ( ( $newsub || $newmsg ) && isset( $_POST[$this->_getPagingParamName( "protect" )] ) ) {
					$parts	=	explode( '_', $this->_getReqParam('protect', '' ) );
					if ( ( count( $parts ) == 3 ) && ( $parts[0] == 'cbpms1' ) && ( strlen( $parts[2] ) == 32 ) && ( $parts[1] == md5($parts[2].$user->id.$user->lastvisitDate) ) )
					{
						if (!$newsub && $capabilities["subject"]) $newsub = _UE_PM_PROFILEMSG;
						if ($this->sendUserPMS($recip, $sender, $newsub, $newmsg, $systemGenerated=false, $escaped=true)) {
							$return .= "\n<script type='text/javascript'>alert('"._UE_PM_SENTSUCCESS."')</script>";
							$newsub = null;
							$newmsg = null;
						} else {
							$return .= "\n<script type='text/javascript'>alert('".$this->getErrorMSG()."')</script>";
						}
					} else {
						$return .= "\n<script type='text/javascript'>alert('"._UE_SESSIONTIMEOUT." "._UE_PM_NOTSENT." "._UE_TRYAGAIN."')</script>";
					}
				} else {
					$return .= "\n<script type='text/javascript'>alert('"._UE_PM_EMPTYMESSAGE." "._UE_PM_NOTSENT."')</script>";
				}
			}
		}
		// display Quick Message tab:
		$return .= "\n\t<div class=\"sectiontableentry".$_CB_OneTwoRowsStyleToggle."\" style=\"padding-bottom:5px;\">\n";
		$_CB_OneTwoRowsStyleToggle = ($_CB_OneTwoRowsStyleToggle == 1 ? 2 : 1);
		if($showTitle) $return .= "\t\t<div class=\"titleCell\" style=\"align: left; text-align:left; margin-left: 0px;\">"
							.unHtmlspecialchars(getLangDefinition($tab->title)).(($showSubject && $capabilities["subject"])?"" : ":")."</div>\n";
		$return .= $this->_writeTabDescription( $tab, $user );

		$base_url = $this->_getAbsURLwithParam(array());
		$return .= '<form method="post" action="'.$base_url.'">';
		$return .= '<table cellspacing="0" cellpadding="5" class="contentpane" style="border:0px;align:left;width:90%;">';
		if ($showSubject && $capabilities["subject"]) {
			$return .= '<tr><td><b>'._UE_EMAILFORMSUBJECT.'</b></td>';
			$return .= '<td><input type="text" class="inputbox" name="'.$this->_getPagingParamName("newsub")
					.'" size="'.($width-8).'" value="'.stripslashes($newsub).'" /></td></tr>';
			$return .= '<tr class="sectiontableentry1"><td colspan="2"><b>'._UE_EMAILFORMMESSAGE.'</b></td></tr>';
		}
		$return .= '<tr><td colspan="2"><textarea name="'.$this->_getPagingParamName("newmsg")
				.'" class="inputbox" rows="'.$height.'" cols="'.$width.'">'.stripslashes($newmsg).'</textarea></td></tr>';
		$return .= '<tr><td colspan="2"><input type="submit" class="button" name="'.$this->_getPagingParamName("sndnewmsg").'" value="'._UE_PM_SENDMESSAGE.'" /></td></tr>';
		$return .= '</table>';
		$return .= "<input type=\"hidden\"  name=\"".$this->_getPagingParamName("sender")."\" value=\"" . $_CB_framework->myId() . "\" />";
		$return .= "<input type=\"hidden\"  name=\"".$this->_getPagingParamName("recip")."\" value=\"$user->id\" />";

		$salt	=	cbMakeRandomString( 32 );
		$return .= "<input type=\"hidden\"  name=\"".$this->_getPagingParamName("protect")."\" value=\""
				. 'cbpms1_' . md5($salt.$user->id.$user->lastvisitDate) . '_' . $salt . "\" />";
		$return	.=	cbGetSpoofInputTag( 'pms' );
		$return .= '</form>';
		$return .= "</div>";

		return $return;
	}
	
	//****************************************************************************
	// UddeIM specific private methods:
	
	/**
	 * Udde PMS notification by email depending on user's settings
	 *
	 * @access private
	 * @param int $savefromid
	 * @param int $savetoid
	 * @param string $savemessage
	 * @param boolean $udde_sysm
	 */
	function _pmsUddeNotify ($savefromid, $savetoid, $savemessage, $udde_sysm) {
		global $_CB_database, $_CB_framework;

		$params = $this->params;
		$pmsType = $params->get('pmsType', '1');
        $config_realnames = "0";
        $config_cryptmode = 0;
        $config_cryptkey = 'uddeIMcryptkey';
        
		if ($pmsType==4 && file_exists( $_CB_framework->getCfg('absolute_path') . "/components/com_uddeim/crypt.class.php" ) ) { // uddeIM 1.0+
			require_once( $_CB_framework->getCfg('absolute_path') . "/components/com_uddeim/crypt.class.php");
			
			if(file_exists( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/config.class.php")) {
				include_once( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/config.class.php");
			}
			$config = new uddeimconfigclass();
			
			if ($config->notifydefault>0 || $config->popupdefault>0) {
				$sql="SELECT count(id) FROM #__uddeim_emn WHERE userid=".(int)$savetoid;
				$_CB_database->setQuery($sql);
				$entryexists=$_CB_database->loadResult();
				if (!$entryexists) {
					$sql="INSERT INTO #__uddeim_emn (status, popup, userid) VALUES (".(int)$config->notifydefault.", ".(int)$config->popupdefault.", ".(int)$savetoid.")";
					$_CB_database->setQuery($sql);
					$ret=$_CB_database->query();
				}
			}
			if (isset($config->realnames)) {
				$config_realnames = $config->realnames;
			}
			if (isset($config->cryptmode)) {
				$config_cryptmode = $config->cryptmode;
			}
            if (file_exists( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_crypt.php" )) {
				require_once ( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_crypt.php" );
			}
			if (isset($config->cryptkey)) {
				$config_cryptkey = $config->cryptkey;
			}
			if (isset($config->emailtrafficenabled)) {
				$config_emailtrafficenabled = $config->emailtrafficenabled;		
			}
			if (isset($config->quotedivider)) {
				$config_quotedivider = $config->quotedivider;	
			}
			if (isset($config->allowemailnotify)) {
				$config_allowemailnotify = $config->allowemailnotify;	
			}
		} else {
			if (file_exists( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_config.php")) {
				include_once( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_config.php");
			} else {
				return;
			}
		}		

// --		
		
		// is this a reply?
		$itisareply= (isset($config_quotedivider) ? stristr($savemessage, $config_quotedivider) : false);
		
		// is the receiver currently online?
		$sql="SELECT userid FROM #__session WHERE userid=".(int) $savetoid;
		$_CB_database->setQuery($sql);
		$currentlyonline=$_CB_database->loadResult();

		if (isset( $config_allowemailnotify )) {
			if ($config_allowemailnotify==1) {
				$sql="SELECT status FROM #__uddeim_emn WHERE userid=".(int) $savetoid;
				$_CB_database->setQuery($sql);
				$ison=$_CB_database->loadResult();
				if (($ison==1) || ($ison==2 && !$currentlyonline) || ($ison==10 && !$itisareply) || ($ison==20 && !$currentlyonline && !$itisareply))  {
					$this->_pmsUddeDispatchEMN($savefromid, $savetoid, $savemessage, 0, $udde_sysm); 
									// 0 stands for normal (not forgetmenot)
				} 
			} elseif ($config_allowemailnotify==2) {
				$sql="SELECT gid FROM #__users WHERE id=".(int) $savetoid;
				$_CB_database->setQuery($sql);
				$my_gid=$_CB_database->loadResult();
				// if ($my_gid>23) { // JACL support
				if ($my_gid==24||$my_gid==25) {		
					$sql="SELECT status FROM #__uddeim_emn WHERE userid=".(int) $savetoid;
					$_CB_database->setQuery($sql);
					$ison=$_CB_database->loadResult();
					if (($ison==1) || ($ison==2 && !$currentlyonline) || ($ison==10 && !$itisareply) || ($ison==20 && !$currentlyonline && !$itisareply))  {
						$this->_pmsUddeDispatchEMN($savefromid, $savetoid, $savemessage, 0, $udde_sysm); 
									// 0 stands for normal (not forgetmenot)
					} 	
				}	
			}
		}
	}
	
	/**
	 * Udde PMS notification by email
	 *
	 * @access private
	 * @param int $var_fromid
	 * @param int $var_toid
	 * @param string $var_message
	 * @param int $emn_option
	 * @param boolean $udde_sysm
	 */
	function _pmsUddeDispatchEMN($var_fromid, $var_toid, $var_message, $emn_option, $udde_sysm) {
		global $_CB_database, $_CB_framework;

// --
		$params = $this->params;
		$pmsType = $params->get('pmsType', '1');
        $udde_sysm = "System";
        $config_realnames = "0";
        $config_cryptmode = 0;
        $config_cryptkey = 'uddeIMcryptkey';
        
		if ($pmsType==4) { // uddeIM 1.0+
			require_once( $_CB_framework->getCfg('absolute_path') . "/components/com_uddeim/crypt.class.php");
			
			if(file_exists( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/config.class.php")) {
				include_once( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/config.class.php");
			}
			$config = new uddeimconfigclass();
			if (isset($config->realnames)) {
				$config_realnames = $config->realnames;
			}
			if (isset($config->cryptmode)) {
				$config_cryptmode = $config->cryptmode;
			}
            if (file_exists( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_crypt.php" )) {
				require_once ( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_crypt.php" );
			}
			if (isset($config->cryptkey)) {
				$config_cryptkey = $config->cryptkey;
			}
			if (isset($config->emailtrafficenabled)) {
				$config_emailtrafficenabled = $config->emailtrafficenabled;		
			}
			if (isset($config->emailwithmessage)) {
				$config_emailwithmessage = $config->emailwithmessage;
			} 
			if (isset($config->emn_sendername)) {
				$config_emn_sendername = $config->emn_sendername;
			}
			if (isset($config->emn_sendermail)) {
				$config_emn_sendermail = $config->emn_sendermail;
			}			
		} else {
			if (file_exists( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_config.php")) {
				include_once( $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim/uddeim_config.php");
			} else {
				return;
			}
		}		

// --		

		// load the uddeim lang file		
		$adminpath = $_CB_framework->getCfg('absolute_path') . "/administrator/components/com_uddeim";
		if (file_exists($adminpath.'/language/'.$_CB_framework->getCfg( 'lang' ).'.php')) {
			include_once($adminpath.'/language/'.$_CB_framework->getCfg( 'lang' ).'.php');
		} elseif (file_exists($adminpath.'/language/english.php')) {
			include_once($adminpath.'/language/english.php');
		} 
		
		// if e-mail traffic stopped, don't send.
		if (isset($config_emailtrafficenabled) && !($config_emailtrafficenabled > 0)) {
			return;
		}
		
		if (isset($config_realnames) && $config_realnames) {
			$sql = "SELECT name FROM #__users WHERE `id`=".(int) $var_fromid;
		} else {
			$sql = "SELECT username FROM #__users WHERE `id`=".(int) $var_fromid;	
		}
		$_CB_database->setQuery($sql);
		$var_fromname=$_CB_database->loadResult();
		if (!$var_fromname) {
			$var_fromname = $udde_sysm;
		}
		
		if (isset($config_realnames) && $config_realnames) {
			$sql = "SELECT name AS displayname, email FROM #__users WHERE `id`=".(int) $var_toid;	
		} else {
			$sql = "SELECT username AS displayname, email FROM #__users WHERE `id`=".(int) $var_toid;
		}
		$_CB_database->setQuery($sql);
		$results=$_CB_database->loadObjectList();
		foreach ($results as $result) {
			$var_toname = $result->displayname;
			$var_tomail = $result->email;
		}
		
		if (!$var_tomail) {
			return;
		}
		
		if ($emn_option==1) {
			$var_body = _UDDEIM_EMN_FORGETMENOT;
			$var_body = str_replace("%you%", $var_toname, $var_body);
			$var_body = str_replace("%site%", $_CB_framework->getCfg( 'sitename' ), $var_body);
		} else {
			if (isset($config_emailwithmessage) && $config_emailwithmessage) {
				$var_body = _UDDEIM_EMN_BODY_WITHMESSAGE;
				$var_body = str_replace("%you%", $var_toname, $var_body);
				$var_body = str_replace("%site%", $_CB_framework->getCfg( 'sitename' ), $var_body);	
				$var_body = str_replace("%user%", $var_fromname, $var_body);
				$var_body = str_replace("%pmessage%", $var_message, $var_body);	
			} else {
			$var_body=_UDDEIM_EMN_BODY_NOMESSAGE;
				$var_body = str_replace("%you%", $var_toname, $var_body);
				$var_body = str_replace("%site%", $_CB_framework->getCfg( 'sitename' ), $var_body);		
				$var_body = str_replace("%user%", $var_fromname, $var_body);			
			}
		}
		
		$subject=_UDDEIM_EMN_SUBJECT;
		$subject=str_replace("%site%", $_CB_framework->getCfg( 'sitename' ), $subject);
		
/*
		$header  = "MIME-Version: 1.0\n";
		// $header .= "Content-type: text/plain; charset=iso-8859-1\n";
		$header .= "Content-type: text/plain; charset=".$this->_pmsUddeGetCharsetname($config_mailcharset)."\n";
		$header .= "Organization: ".$_CB_framework->getCfg( 'sitename' )."\n";
		$header .= "Content-Transfer-encoding: 8bit\n";
		$header .= "From: \"".$config_emn_sendername."\" <".$config_emn_sendermail.">\n";
		$header .= "Reply-To: ".$config_emn_sendermail."\n";
		$header .= "Message-ID: <".md5(uniqid(time()))."@{$_SERVER['SERVER_NAME']}>\n";
		$header .= "Return-Path: ".$config_emn_sendermail."\n";            
		$header .= "X-Priority: 3\n"; 
		$header .= "X-MSmail-Priority: Low\n"; 
	            // $header .= "X-Mailer: PHP\r\n"; //hotmail and others dont like PHP mailer. --Microsoft Office Outlook, Build 11.0.5510
		$header .= "X-Mailer: Microsoft Office Outlook, Build 11.0.5510\n";
		$header .= "X-Sender: ".$config_emn_sendermail."\n";
	            
		// debug:
		// $header.="\n".$this->_pmsMailcompatible($var_body);
 		// die($header);
		
		// -----
		if(mail ($var_tomail,$subject,$this->_pmsMailcompatible($var_body),$header)) {
*/
//		$cbNotif = new cbNotification;
//		if ($cbNotif->sendUserEmail($var_toid,$var_fromid,$subject,$this->_pmsMailcompatible($var_body))) {
		if (comprofilerMail($config_emn_sendermail, $config_emn_sendername, $var_tomail,$subject,$this->_pmsMailcompatible($var_body))) {
			// set the remindersent status of this user to true
			$sql="SELECT count(id) FROM #__uddeim_emn WHERE userid=".(int) $var_toid;
			$_CB_database->setQuery($sql);
			$exists=$_CB_database->loadResult();
			if($exists) {
				$sql="UPDATE #__uddeim_emn SET remindersent=".(int) $this->_pmsUddeGetTime(time())." WHERE userid=".(int) $var_toid;
				$_CB_database->setQuery($sql);
				if (!$_CB_database->query()) {
					die("SQL error" . $_CB_database->stderr(true));
				}	
			} else {
				$sql="INSERT INTO #__uddeim_emn (userid, status, remindersent) VALUES (".(int) $var_toid.", 0, ".(int) $this->_pmsUddeGetTime(time()).")";
				$_CB_database->setQuery($sql);
				if (!$_CB_database->query()) {
					die("SQL error" . $_CB_database->stderr(true));
				} // end if database query
			} // end else
		} // end if mail
	} // end function

/*
	function _pmsUddeGetCharsetname($analias) {
	
		switch ($analias) {
	
			case "ISO8859-1":
				$notalias="ISO-8859-1";
				break;
			case "ISO8859-15":
				$notalias="ISO-8859-15";
				break;
			case "ISO-8859-1":
				$notalias="ISO-8859-1";
				break;
			case "ISO-8859-15":
				$notalias="ISO-8859-15";
				break;			
			case "UTF-8":
				$notalias="UTF-8";
				break;
			case "ibm866":
				$notalias="ibm866";
				break;
			case "866":
				$notalias="ibm866";
				break;
			case "cp866":
				$notalias="ibm866";
				break;
			case "cp1251":
				$notalias="Windows-1251";
				break;
			case "Windows-1251":
				$notalias="Windows-1251";
				break;
			case "win-1251":
				$notalias="Windows-1251";
				break;
			case "1251":
				$notalias="Windows-1251";
				break;
			case "cp1252":
				$notalias="Windows-1252";
				break;
			case "Windows-1252":
				$notalias="Windows-1252";
				break;
			case "1252":
				$notalias="Windows-1252";
				break;
			case "koi8-ru":
				$notalias="KOI8-R";
				break;
			case "koi8r":
				$notalias="KOI8-R";
				break;
			case "KOI8-R":
				$notalias="KOI8-R";
				break;		
			case "BIG5":
				$notalias="Big5";
				break;		
			case "Big5":
				$notalias="Big5";
				break;							
			case "950":
				$notalias="Big5";
				break;
			case "GB2312":
				$notalias="GB2312";
				break;			
			case "936":
				$notalias="GB2312";
				break;
			case "BIG5-HKSCS":
				$notalias="BIG5-HKSCS";
				break;
			case "SJIS":
				$notalias="Shift_JIS";
				break;
			case "Shift_JIS":
				$notalias="Shift_JIS";
				break;			
			case "932":
				$notalias="Shift_JIS";
				break;
			case "EUC-JP":
				$notalias="EUC-JP";
				break;
			case "EUCJP":
				$notalias="EUC-JP";
				break;
			default:
				$notalias=$analias;
				break;
		}
	
		if(!$notalias) {
			$notalias="ISO-8859-1";
		}
		return $notalias;
		
	}
*/

	function _pmsMailcompatible($string) {
	
		$string=str_replace('\\n', '#!CRLF!#', $string);

		$string=stripslashes($string);
	
		// bold
	    $string = preg_replace("/(\[b\])(.*?)(\[\/b\])/si","\\2",$string);
	
		// underline
	    $string = preg_replace("/(\[u\])(.*?)(\[\/u\])/si","\\2",$string);
	
		// italic
		$string = preg_replace("/(\[i\])(.*?)(\[\/i\])/si","\\2",$string);
	
		// size Max size is 7
		$string = preg_replace("/\[size=([1-7])\](.+?)\[\/size\]/si","\\2",$string);
	
		// color
		$string = preg_replace("%\[color=(.*?)\](.*?)\[/color\]%si","\\2",$string);
		
		// ul li replacements
		
		// lists
		$string = preg_replace("/(\[ul\])(.*?)(\[\/ul\])/si","\\2",$string);
		$string = preg_replace("/(\[ol\])(.*?)(\[\/ol\])/si","\\2",$string);
		$string = preg_replace("/(\[li\])(.*?)(\[\/li\])/si","\\2\\n",$string);
		
		// url replacement
		$string = preg_replace('/\[url\](.*?)javascript(.*?)\[\/url\]/si','',$string);
		$string = preg_replace('/\[url=(.*?)javascript(.*?)\](.*?)\[\/url\]/si','',$string);
		$string = preg_replace("/\[url\](.*?)\[\/url\]/si","\\1",$string);
		$string = preg_replace("/\[url=(.*?)\](.*?)\[\/url\]/si","\\2 (\\1)",$string);	
		
		// only front tag present
		$string = preg_replace("/\[url=(.*?)\]/si","",$string);	
		
		// img replacement
		// img
		$string = preg_replace("/\[img size=([0-9][0-9][0-9])\](.*?)\[\/img\]/si","",$string);
		$string = preg_replace("/\[img size=([0-9][0-9])\](.*?)\[\/img\]/si","",$string);
		$string = preg_replace("/\[img\](.*?)\[\/img\]/si","",$string);
		$string = preg_replace("/<img(.*?)javascript(.*?)>/si",'',$string);	
	
		// only front tag present
		$string = preg_replace("/\[img size=([0-9][0-9][0-9])\]]/si","",$string);
		$string = preg_replace("/\[img size=([0-9][0-9])\]]/si","",$string);
		
		// cut remaining single tags
		$string=str_replace(array("[i]","[/i]","[b]","[/b]","[u]","[/u]","[ul]","[/ul]","[ol]","[/ol]","[li]","[/li]"), "", $string);
	
	    $string = preg_replace('/\[url=(.*?)javascript(.*?)\]/si','',$string);	
	    $string = preg_replace("/\[img size=([0-9][0-9][0-9])\]/si","",$string);
	    $string = preg_replace("/\[img size=([0-9][0-9])\]/si","",$string);
	    $string = preg_replace("/\[size=([1-7])\]/si","",$string);
	    $string = preg_replace("%\[color=(.*?)\]%si","",$string);
		$string=str_replace(array("[img]","[/img]","[url]","[/url]","[/color]","[/size]"), "", $string);
			
		$string=str_replace("#!CRLF!#", "\n", $string);	 
		return $string;
	
	}	
		
	function _pmsUddeGetTime($currentTime) {
		global $_CB_framework;
		return $currentTime+($_CB_framework->getCfg( 'offset' )*3600);
	}	

	//  delete user code function
	function userDeleted($user, $success) {
		global $_CB_database,$_CB_framework;

		$params = $this->params;
		$pmsType = $params->get('pmsType', '1');

		if (!$this->_checkPMSinstalled($pmsType)) {
			return false;
		}
		$pmsUserDeleteOption = $params->get('pmsUserDeleteOption', '3');
		$pmsUserFunction = $params->get('pmsUserFunction','1');
		
        $cb_extra_rules = 0;
		SWITCH($pmsType) {
			case 1:		//MyPMS OS
				switch ($pmsUserDeleteOption) {
					case '1':	// Keep all messages
						$query_pms_delete = "";
						break;
					case '2':	// Remove all messages (received and sent)
					case '3':	// Remove received messages only
					case '4':	// Remove sent message only
						$query_pms_delete = "DELETE FROM #__pms WHERE username='" . $_CB_database->getEscaped($user->username) ."'";
						break;
					default:
						$query_pms_delete = "DELETE FROM #__pms WHERE username='" . $_CB_database->getEscaped($user->username) ."'";
						break;	
				}
				if(file_exists( $_CB_framework->getCfg('absolute_path') . "/components/com_pms/cb_extra.php")) {
					include_once( $_CB_framework->getCfg('absolute_path') . "/components/com_pms/cb_extra.php");
					if (function_exists('user_delete')) {
						$cb_extra_rules = 1;
					}
					if (function_exists('user_delete_ext')) {
						$cb_extra_rules = 2;
					}
				}		
				break;
			case 2:		//PMS Pro
				switch ($pmsUserDeleteOption) {
					case '1':	// Keep all messages
						$query_pms_delete = "";
						break;
					case '2':	// Remove all messages (received and sent)
					case '3':	// Remove received messages only
					case '4':	// Remove sent message only
						$query_pms_delete = "DELETE FROM #__mypms WHERE username='" . $_CB_database->getEscaped($user->username) ."'";
						break;
					default:
						$query_pms_delete = "DELETE FROM #__mypms WHERE username='" . $_CB_database->getEscaped($user->username) ."'";
						break;	
				}
				if(file_exists( $_CB_framework->getCfg('absolute_path') . "/components/com_mypms/cb_extra.php")) {
					include_once( $_CB_framework->getCfg('absolute_path') . "/components/com_mypms/cb_extra.php");
					if (function_exists('user_delete')) {
						$cb_extra_rules = 1;
					}
					if (function_exists('user_delete_ext')) {
						$cb_extra_rules = 2;
					}
				}		
				break;
			case 3:		//UddeIM 0.4
			case 4:		//UddeIM 1.0
				switch ($pmsUserDeleteOption) {
					case '1':	// Keep all messages
						$query_pms_delete = "";
						break;
					case '2':	// Remove all messages (received and sent)
						$query_pms_delete = "DELETE FROM #__uddeim WHERE fromid='" . (int) $user->id ."' OR toid='" . (int) $user->id . "'";
						break;
					case '3':	// Remove received messages only
						$query_pms_delete = "DELETE FROM #__uddeim WHERE toid='" . (int) $user->id . "'";
						break;
					case '4':	// Remove sent message only
						$query_pms_delete = "DELETE FROM #__uddeim WHERE fromid='" . (int) $user->id ."'";
						break;
					default:
						$query_pms_delete = "DELETE FROM #__uddeim WHERE fromid='" . (int) $user->id ."' OR toid='" . (int) $user->id . "'";
						break;	
				}
				$query_pms_delete_extra1 = "DELETE FROM #__uddeim_emn WHERE userid='" . (int) $user->id . "'";
				$query_pms_delete_extra2 = "DELETE FROM #__uddeim_blocks WHERE blocker='" . (int) $user->id . "' OR blocked='" . (int) $user->id . "'";
				if(file_exists( $_CB_framework->getCfg('absolute_path') . "/components/com_uddeim/cb_extra.php")) {
					include_once( $_CB_framework->getCfg('absolute_path') . "/components/com_uddeim/cb_extra.php");
					if (function_exists('user_delete')) {
						$cb_extra_rules = 1;
					}
					if (function_exists('user_delete_ext')) {
						$cb_extra_rules = 2;
					}
				}		
				break;		
			case 5:		//PMS enhanced 2.x by Stefan Klingner
				switch ($pmsUserDeleteOption) {
					case '1':	// Keep all messages
						$query_pms_delete = "";
						break;
					case '2':	// Remove all messages (received and sent)
						$query_pms_delete = "DELETE FROM #__pms WHERE recip_id='" . (int) $user->id . "' OR sender_id='" . (int) $user->id . "'";
						break;
					case '3':	// Remove received messages only
						$query_pms_delete = "DELETE FROM #__pms WHERE recip_id='" . (int) $user->id . "'";
						break;
					case '4':	// Remove sent message only
						$query_pms_delete = "DELETE FROM #__pms WHERE sender_id='" . (int) $user->id . "'";
						break;
					default:
						$query_pms_delete = "DELETE FROM #__pms WHERE recip_id='" . (int) $user->id . "' OR sender_id='" . (int) $user->id . "'";
						break;	
				}
				if(file_exists( $_CB_framework->getCfg('absolute_path') . "/components/com_pms/cb_extra.php")) {
					include_once( $_CB_framework->getCfg('absolute_path') . "/components/com_pms/cb_extra.php");
					if (function_exists('user_delete')) {
						$cb_extra_rules = 1;
					}
					if (function_exists('user_delete_ext')) {
						$cb_extra_rules = 2;
					}
				}		
				break;
			case 6:		//JIM 1.0.1
				$query_pms_delete = "DELETE FROM #__jim WHERE username='" . $_CB_database->getEscaped($user->username) ."'";
				if(file_exists( $_CB_framework->getCfg('absolute_path') . "/components/com_jim/cb_extra.php")) {
					include_once( $_CB_framework->getCfg('absolute_path') . "/components/com_jim/cb_extra.php");
					if (function_exists('user_delete')) {
						$cb_extra_rules = 1;
					}
					if (function_exists('user_delete_ext')) {
						$cb_extra_rules = 2;
					}
				}		
				break;
			default:
				$this->_setErrorMSG("Incorrect PMS type");
				return false;
				break;
		}
		
		if (!$cb_extra_rules || $pmsUserFunction=='1') {
			// print "Deleting pms data for user ".$user->id;
			if ($pmsUserDeleteOption != 1) {
				$_CB_database->setQuery( $query_pms_delete );
				if (!$_CB_database->query()) {
					$this->_setErrorMSG("SQL error " . $query_pms_delete . $_CB_database->stderr(true));
					return false;			
				}
			}
			if ($pmsType == 4 || $pmsType == 3) {
				$_CB_database->setQuery( $query_pms_delete_extra1 );
				if (!$_CB_database->query()) {
					$this->_setErrorMSG("SQL error " . $query_pms_delete_extra1 . $_CB_database->stderr(true));
					return false;			
				}			
				$_CB_database->setQuery( $query_pms_delete_extra2 );
				if (!$_CB_database->query()) {
					$this->_setErrorMSG("SQL error " . $query_pms_delete_extra2 . $_CB_database->stderr(true));
					return false;			
				}			
			}
			$cb_extra_return = true;
		} else {
			switch ($cb_extra_rules) {
				case 1:
					$cb_extra_return = user_delete($user->id);
					break;
				case 2:
			    	$cb_extra_return = user_delete_ext($user->id,$pmsUserDeleteOption);
			    	break;
			}	
		}
		return $cb_extra_return;
	}	
}	// end class getmypmsproTab.
?>
