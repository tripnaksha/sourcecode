<?php
/**
 * Security module for Joomla!
 *
 * @author      $Author: shumisha $
 * @copyright   Yannick Gaultier - 2007
 * @package     sh404SEF-15
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     $Id: shSec.php 840 2009-01-02 18:07:53Z silianacom-svn $
 *
 */

// Security check to ensure this file is being included by a parent file.
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

define('sh404SEF_DEBUG_HONEY_POT', false);

// V x : performs various security checks before allowing to go any further
function shDoSecurityChecks( $query = '', $fullCheck = true) {

  $sefConfig = shRouter::shGetConfig();

  if (!$sefConfig->shSecEnableSecurity) return '';

  $shQuery = empty($query) ? (empty($_SERVER['QUERY_STRING']) ? '' : urldecode( $_SERVER['QUERY_STRING'])) : urldecode($query) ;
   
  $shQuery = str_replace('&amp;', '&', $shQuery);

  $ip = empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];
  $uAgent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];

  // ip White/Black listing
  $shWhiteListedIp = shCheckIPList($ip, $sefConfig->ipWhiteList);
  if (!$shWhiteListedIp) {
    if (shCheckIPList($ip, $sefConfig->ipBlackList)) shDoRestrictedAccess('Blacklisted IP');
  }

  if (!$shWhiteListedIp && $fullCheck) {
    shDoAntiFloodCheck($ip);
  }

  // bad content in query string
  $c = shCheckConfigVars($shQuery);
  if ($c) shDoRestrictedAccess($c.' in URL');
  $c = shCheckBase64($shQuery);
  if ($c) shDoRestrictedAccess($c.' in URL');
  $c = shCheckScripts($shQuery);
  if ($c) shDoRestrictedAccess($c.' in URL');
  $c = shCheckStandardVars($_GET);
  if ($c) shDoRestrictedAccess($c.' in URL');
  $c = shCheckImgTxtCmd($shQuery); // V x
  if ($c) shDoRestrictedAccess($c.' in URL');

  // UserAgent White/Black listing
  if (!shCheckUAgentList($uAgent, $sefConfig->uAgentWhiteList)) {
    if (shCheckUAgentList($uAgent, $sefConfig->uAgentBlackList)) shDoRestrictedAccess('BlackListed user agent');
  }

  if (!$fullCheck) return;  // don't check POST and/or Honey pot if second check

  // check POST variables
  if ($sefConfig->shSecCheckPOSTData) {
    foreach($_POST as $key=>$value) {
      $c = shCheckConfigVars($key.'='.$value);
      if ($c) shDoRestrictedAccess($c.' in POST');
      $c = shCheckBase64($key.'='.$value);
      if ($c) shDoRestrictedAccess($c.' in POST');
      $c = shCheckScripts($key.'='.$value);
      if ($c) shDoRestrictedAccess($c.' in POST');
      $c = shCheckStandardVars($_POST);
      if ($c) shDoRestrictedAccess($c.' in POST');
      $c = shCheckImgTxtCmd($key.'='.$value); // V x
      if ($c) shDoRestrictedAccess($c.' in POST');
    }
  }
  // do Project Honey Pot check
  if (!$shWhiteListedIp && $sefConfig->shSecCheckHoneyPot) {
    shDoHoneyPotCheck($ip);
  }
}

function shSendEmailToAdmin($logData) {
  if (!sh404SEF_SEC_MAIL_ATTACKS_TO_ADMIN) return;
  global $mainframe;

  $subject = str_replace( '%sh404SEF_404_SITE_NAME%', $mainframe->getCfg('sitename'), sh404SEF_SEC_EMAIL_TO_ADMIN_SUBJECT);
  $details = array(
	     '           Date',
	"\n".'           Time',
	"\n".'          Cause',
	"\n".'             IP',
	"\n".'           Name',
	"\n".'     User agent',
	"\n"."\n".' Request method',
	"\n".'    Request URI',
	"\n".'        Comment');
  $items = explode("\t", $logData);
  $count = 0;
  $detailText = '';
  foreach ($details as $detail) {
    $detailText .= $detail . ' :: ' . trim($items[$count++]);
  }
  $body = str_replace( '%sh404SEF_404_SITE_URL%',$GLOBALS['shConfigLiveSite'], sh404SEF_SEC_EMAIL_TO_ADMIN_BODY);
  $body = str_replace( '%sh404SEF_404_ATTACK_DETAILS%', $detailText, $body);
  if (!defined('_ISO')) define('_ISO', 'charset=iso-8859-1');
  JUtility::sendMail( $mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname'), $mainframe->getCfg('mailfrom'),
  $subject, $body);
}

function shLogToSecFile($logData) {
  $shNum = 12*(intval(date('Y')) - 2000)+intval(date('m')); // number current month
  $shFileName = sh404SEF_ADMIN_ABS_PATH.'logs/'.date('Y').'-'.date('m').'-'.'sh404SEF_security_log.'.$shNum.'.txt';
  $fileIsThere = file_exists($shFileName);
  if (!$fileIsThere) { // create file
    $fileHeader = "Date\tTime\tCause\tIP\tName\tUser agent\tRequest method\tRequest URI\tComment\n";
  } else $fileHeader = '';
  if (!$fileIsThere || ($fileIsThere && is_writable($shFileName))) {
    $logFile=fopen( $shFileName,'ab');
    if ($logFile) {
      if (!empty($fileHeader))
      fWrite( $logFile, $fileHeader);
      fWrite( $logFile, $logData);
      fClose( $logFile);
    }
  }
}

function shCleanUpSecLogFiles(){  // delete security log files older than param

  $sefConfig = shRouter::shGetConfig();
  if (mt_rand(1, SH404SEF_PAGES_TO_CLEAN_LOGS) != 1) return; // probability = 1/SH404SEF_PAGES_TO_CLEAN_LOGS
  $curMonth = 12*(intval(date('Y')) - 2000)+intval(date('m'));
  if ($sefConfig->shSecLogAttacks) {
    if ($handle = opendir(sh404SEF_ADMIN_ABS_PATH.'logs/')) {
    		while (false !== ($file = readdir($handle))) {
    		  $matches = array();
    		  if ($file != '.' && $file != '..' &&preg_match('/\.[0-9]*\./', $file, $matches)) {
    		    $fileNum = trim($matches[0], '.');
    		    if ($curMonth-$fileNum > $sefConfig->monthsToKeepLogs) {
    		      @unlink(sh404SEF_ADMIN_ABS_PATH.'logs/'.$file);
    		      _log('Erasing security log file : '.$file);
    		    }
    		  }
    		}
    		closedir($handle);
    }
  }
}

function shDoRestrictedAccess( $causeText, $comment = '', $displayEntrance = false) {

  $sefConfig = shRouter::shGetConfig();
   
  if ($sefConfig->shSecLogAttacks) {  // log what's happening
    $sep = "\t";
   	$logData  = date('Y-m-d').$sep.date('H:i:s').$sep.$causeText.$sep.$_SERVER['REMOTE_ADDR'].$sep;
   	$logData .= getHostByAddr( $_SERVER['REMOTE_ADDR']).$sep;
   	$logData .= $_SERVER['HTTP_USER_AGENT'].$sep.$_SERVER['REQUEST_METHOD'].$sep.$_SERVER['REQUEST_URI'].$sep.$comment;
   	$logData .="\n";
   	shLogToSecFile ($logData);
  }
  // V x : we can possibly send email to site admin, but not log
  shSendEmailToAdmin($logData);

  // actually restrict access
  if (!headers_sent()) {
    header('HTTP/1.0 403 FORBIDDEN');
  }
  echo '<h1>Forbidden access</h1>';
  if($displayEntrance) {
    ?>
<script type="text/javascript">
    function setcookie( name, value, expires, path, domain, secure ) {
        // set time in milliseconds
        var today = new Date();
        today.setTime( today.getTime() );
    
        if ( expires ) {
            expires = expires * 1000 * 60 * 60 * 24;
        }
        var expires_date = new Date( today.getTime() + (expires) );
    
        document.cookie = name + "=" +escape( value ) +
        ( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) + 
        ( ( path ) ? ";path=" + path : "" ) + 
        ( ( domain ) ? ";domain=" + domain : "" ) +
        ( ( secure ) ? ";secure" : "" );
    }    
    function letmein() {
        setcookie('sh404SEF_user_click_notabot','true',1,'/', '', '');
        location.reload(true);
    }
    </script>
    <?php echo $sefConfig->shSecEntranceText; ?>
<a href="javascript:letmein()">&gt;&gt;&gt;&gt;&gt;&gt;</a>
<br />
<br />
<br />
<br />
<br />
<p><font size="2" color="grey"><?php echo $sefConfig->shSecSmellyPotText; ?><a
	href="http://planetozh.com/smelly.php">&gt;&gt;</a></font></p>
    <?php
} else {
  echo '<p><font size="2" color="grey">('.$causeText.')</font></p>';
}
die();
}

function shCheckConfigVars( $query) {
  if (empty($query)) return '';
  if (preg_match( '/mosConfig_[a-zA-Z_]{1,21}=/i', $query))
  return 'mosConfig_var';
  else return '';
}

function shCheckBase64( $query) {
  if (empty($query)) return '';
  if (preg_match( '/base64_encode.*\(.*\)/i', $query))
  return 'Base 64 encoded data';
  else return '';
}

function shCheckScripts( $query) {
  if (empty($query)) return '';
  if (preg_match( '/(\<).*script.*(\>)/i', $query))
  return '<script> tag';
  else return;
}

function shCheckImgTxtCmd( $query) {
  if (empty($query)) return '';
  $badCmds = array('gif\?cmd', 'gif&cmd', 'jpg\?cmd', 'jpg&cmd', 'txt\?cmd', 'txt&cmd', 'txt\?');
  foreach($badCmds as $badCmd) {
    if (preg_match( '/'.$badCmd.'/i', $query))
    return 'Image file name with command';
  }
  return;
}

function shCheckStandardVars($_ARRAY) {

  $sefConfig = shRouter::shGetConfig();

  foreach($_ARRAY as $k=>$v) {
    $k = str_replace('amp;', '', $k);  // if &amp;XXX is passed, then $_GET will have amp;XXX as a key !
    if (in_array($k, $sefConfig->shSecOnlyNumVars)) {
      if (!empty($v) && !is_numeric($v))
      return 'Var not numeric: '.$k;
    }
    if (in_array(strToLower($k), $sefConfig->shSecAlphaNumVars)) {
      if (preg_match('/[^._a-zA-Z0-9]/i', $v))
      return 'Var not alpha-numeric: '.$k;
    }
    if (in_array(strToLower($k), $sefConfig->shSecNoProtocolVars)) {
      // attempt to pass some URL
      if (preg_match('#(http|https|ftp):\/\/#is', $v))
      return 'Var contains outbound link: '.$k;
    }
  }
  return '';
}

function shCheckIpRange($ip, $ipExp) {
  if (empty($ip) || empty($ipExp) ) return false;
  $exp = '/'.str_replace('\*', '[0-9]{1,3}', preg_quote($ipExp)).'/';  // allow * wild card
  return preg_match( $exp, $ip);
}

function shCheckIPList( $ip, $ipList) {
  if (empty($ip) || empty($ipList)) return false;
  foreach($ipList as $ipInList)
  if (shCheckIpRange($ip, $ipInList))
  return true;
  return false;
}

function shCheckUAgentList( $uAgent, $uAgentList) {
  if (empty($uAgent) || empty($uAgentList)) return false;
  return in_array( $uAgent, $uAgentList);
}

/* ADAPTED FROM
 Script Name: Simple PHP http:BL implementation
 Script URI: http://planetozh.com/blog/my-projects/honey-pot-httpbl-simple-php-script/
 Description: Simple script to check an IP against Project Honey Pot's database and let only legitimate users access your script
 Author: Ozh
 Version: 1.0
 Author URI: http://planetozh.com/
 */

function shDoHoneyPotCheck( $ip) {

  $sefConfig = shRouter::shGetConfig();
  if (empty($_COOKIE['sh404SEF_user_click_notabot'])
  && empty($_COOKIE['sh404SEF_auto_notabot'])) {
    sh_ozh_httpbl_check($ip);
  } else {
    if ($sefConfig->shSecLogAttacks // log what's happening
    && !empty($_COOKIE['sh404SEF_user_click_notabot'])) {
      $causeText = 'Honey Pot but user clicked';
      $sep = "\t";
      $comment = '';
      $logData  = date('Y-m-d').$sep.date('H:i:s').$sep.$causeText.$sep.$_SERVER['REMOTE_ADDR'].$sep;
      $logData .= getHostByAddr( $_SERVER['REMOTE_ADDR']).$sep;
      $logData .= $_SERVER['HTTP_USER_AGENT'].$sep.$_SERVER['REQUEST_METHOD'].$sep.$_SERVER['REQUEST_URI'].$sep.$comment;
      $logData .="\n";
      shLogToSecFile ($logData);
    }
  }
}

function sh_ozh_httpbl_check( $ip) {

  $sefConfig = shRouter::shGetConfig();
  //$ip='203.144.160.250';  // bad address
  //$ip = '84.103.202.172';   	// good address
  // build the lookup DNS query
  // Example : for '127.9.1.2' you should query 'abcdefghijkl.2.1.9.127.dnsbl.httpbl.org'
  $lookup = $sefConfig->shSecHoneyPotKey . '.' . implode('.', array_reverse(explode ('.', $ip ))) . '.dnsbl.httpbl.org';
  // check query response
  $result = explode( '.', gethostbyname($lookup));
  if ($result[0] == 127) {
    // query successful !
    $activity = $result[1];
    $threat = $result[2];
    $type = $result[3];
    $typemeaning = '';
    if ($type == 0) $typemeaning .= 'Search Engine, ';
    if ($type & 1) $typemeaning .= 'Suspicious, ';
    if ($type & 2) $typemeaning .= 'Harvester, ';
    if ($type & 4) $typemeaning .= 'Comment Spammer, ';
    $typemeaning = trim($typemeaning,', ');

    //echo "$type : $typemeaning of level $threat <br />";

    // Now determine some blocking policy
    if (
    ($type >= 4 && $threat > 0) // Comment spammer with any threat level
    ||
    ($type < 4 && $threat > 20) // Other types, with threat level greater than 20
    ) {
      $block = true;
    }

    if ($block) {
      shDoRestrictedAccess( 'Caught by Honey Pot Project',
            					  'Type = '.$type.' | Threat= '.$threat.' | Act.= '.$activity.' | '.$typemeaning,
      true);
      die();
    } else {  // always set cookie to save time at next visit
      setCookie('sh404SEF_auto_notabot', 'OK', time()+86400, '/');
    }
  }
  // debug info
  if (sh404SEF_DEBUG_HONEY_POT) {
    $causeText = 'Debug: project Honey Pot response';
    $sep = "\t";
    $comment = 'PHP query result = '.$result[0];
    $logData  = date('Y-m-d').$sep.date('H:i:s').$sep.$causeText.$sep.$_SERVER['REMOTE_ADDR'].$sep;
    $logData .= getHostByAddr( $_SERVER['REMOTE_ADDR']).$sep;
    $logData .= $_SERVER['HTTP_USER_AGENT'].$sep.$_SERVER['REQUEST_METHOD'].$sep.$_SERVER['REQUEST_URI'].$sep.$comment;
    $logData .="\n";
    shLogToSecFile ($logData);
  }
}

function shDoAntiFloodCheck( $ip) {

  $sefConfig = shRouter::shGetConfig();

  if (!$sefConfig->shSecActivateAntiFlood  || empty($sefConfig->shSecAntiFloodPeriod)
  || ($sefConfig->shSecAntiFloodOnlyOnPOST && empty($_POST))
  || empty($sefConfig->shSecAntiFloodCount) || empty($ip)) return;
  $nextId = 1;
  $cTime = time();
  $count = 0;
  $floodData = shReadFile(sh404SEF_ADMIN_ABS_PATH . 'security/sh404SEF_AntiFlood_Data.dat');
  if (!empty($floodData)){
    // find next id
    $lastRec = $floodData[count($floodData)-1];
    $lastRecId = explode(',', $lastRec);
    if (!empty($lastRecId)) $nextId = intval($lastRecId[0]) + 1;
    // trim flood data : remove lines older than set time limit
    foreach ($floodData as $data) {
      $rec = explode(', ', $data);
      if (empty($rec[2]) || ($cTime-intVal($rec[2]) > $sefConfig->shSecAntiFloodPeriod) )
      unset($floodData[$count]);
      $count++;
    }
    $floodData = array_filter($floodData);
  }
  // we have only requests made in the last $sefConfig->shSecAntiFloodPeriod seconds left in $floodArray
  $count = 0;
  if (!empty($floodData)){
    foreach ($floodData as $data) {
      $rec = explode(',', $data);
      if (!empty($rec[1]) && trim($rec[1]) == $ip)
      $count++;
    }
  }
  // log current request
  $floodData[] = $nextId.', '.$ip.', '.$cTime;
  // write to file;
  $saveData = implode("\n", $floodData);
  shSaveFile(sh404SEF_ADMIN_ABS_PATH . 'security/sh404SEF_AntiFlood_Data.dat', $saveData);

  if ($count >= $sefConfig->shSecAntiFloodCount)
  shDoRestrictedAccess('Flooding', $count.' requests in less than '.$sefConfig->shSecAntiFloodPeriod.' seconds (max = '.$sefConfig->shSecAntiFloodCount.')');

}

?>