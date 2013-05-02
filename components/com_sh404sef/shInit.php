<?php
/**
 * SEF module for Joomla!
 *
 * @author      $Author: shumisha $
 * @copyright   Yannick Gaultier - 2007
 * @package     sh404SEF-15
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     $Id: shInit.php 866 2009-01-17 14:05:21Z silianacom-svn $
 */

// Security check to ensure this file is being included by a parent file.

if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

if (!defined('sh404SEF_ABS_PATH')) {
  define('sh404SEF_ABS_PATH', JPATH_ROOT );
}

if (!defined('sh404SEF_FRONT_ABS_PATH')) {
  define('sh404SEF_FRONT_ABS_PATH', sh404SEF_ABS_PATH.'components/com_sh404sef/');
}

if (!defined('sh404SEF_ADMIN_ABS_PATH')) {
  define('sh404SEF_ADMIN_ABS_PATH', sh404SEF_ABS_PATH.'administrator/components/com_sh404sef/');
}

$shPageInfo = null;  // will hold collected info on current page request

$sefConfig = & shRouter::shGetConfig();

if ($sefConfig->Enabled) {
   
  // V 1.3.1 allow internal redirect for outbound links
  if (sh404SEF_REDIRECT_OUTBOUND_LINKS) {
    $shTask = JRequest::getVar('shtask', '', 'GET');
    $shTarget = JRequest::getVar('shtarget', '', 'GET');
    if ($shTask == 'redirect' && !empty($shTarget)) {
      shRedirect($shTarget, '', '303');
    }
  }
   
  _log(str_repeat('-', 69));
  _log('New request : '.(empty($_SERVER['REQUEST_URI']) ? '': $_SERVER['REQUEST_URI']));
  _log(str_repeat('-', 69));
  _log("\n".'$_SERVER', $_SERVER);

  // do security checks
  shDoSecurityChecks();
  shCleanUpSecLogFiles(); // see setting in class file for clean up frequency

  if ($sefConfig->Enabled) {
    $sef404 = sh404SEF_FRONT_ABS_PATH.'sh404sef.php';
    $JConfig =& JFactory::getConfig();
    if (is_readable($sef404)) {
      // V 1.2.4.r special processing for SEF without mod_rewrite
      $shPageInfo->shSaveRequestURI = $_SERVER['REQUEST_URI']; // V x
      // V 1.2.4.j 2007-04-16 use own config secure url to fix ssl switch management
      // V 1.2.4.t moved here from sef404.php
      $http_host = explode(':', $_SERVER['HTTP_HOST'] );
      if( (!empty( $_SERVER['HTTPS'] )
      && strtolower( $_SERVER['HTTPS'] ) != 'off' || isset( $http_host[1] ) && $http_host[1] == 443)) {
        $shTemp =    str_replace( 'http://', '', $GLOBALS['shConfigLiveSite']);
        $shTemp =    str_replace( 'https://', '', $shTemp); // some sites may be running https !
        $shPageInfo->shHttpsSave = empty( $sefConfig->shConfig_live_secure_site) ? 'https://'. $shTemp
        : $sefConfig->shConfig_live_secure_site;
        _log('Request in SSL mode'.$shPageInfo->shHttpsSave);
      } else
      $shPageInfo->shHttpsSave = null;
      if (!empty($shPageInfo->shHttpsSave)) {
        //$mosConfig_live_site = $shPageInfo->shHttpsSave; // does not work in 1.5. We can't change the base in JURI
        // as it is stored statically
        $JConfig->setValue('config.live_site', $shPageInfo->shHttpsSave);
      }
      if (!empty($sefConfig->shRewriteMode)
      && strpos( $_SERVER['REQUEST_URI'], $sefConfig->shRewriteStrings[$sefConfig->shRewriteMode]) !== false ) {
        $bits = explode($sefConfig->shRewriteStrings[$sefConfig->shRewriteMode], $_SERVER['REQUEST_URI']);
        $shPageInfo->base = (isset($bits[0]) ? $bits[0] : '').'/';
        $shPageInfo->index = 'index.php';
        $shPageInfo->base = str_replace( '/'.$shPageInfo->index, '', $shPageInfo->base);
        if (isset($bits[1]))
        $_SERVER['REQUEST_URI'] = $shPageInfo->base.$bits[1];
        else
        $_SERVER['REQUEST_URI'] = $shPageInfo->base;
        $_SERVER['PHP_SELF'] = $shPageInfo->base.$shPageInfo->index;
      } else {
        $shPageInfo->index = str_replace($GLOBALS['shConfigLiveSite'],'',$_SERVER['PHP_SELF']);
        $shPageInfo->base = dirname($shPageInfo->index);
        if ($shPageInfo->base =="\\") $shPageInfo->base = "/";
        $shPageInfo->base .= (($shPageInfo->base == "/") ? "" :"/");
        $shPageInfo->index = basename($shPageInfo->index);
      }
      // use new Joomla config value, when base uri auto detect does not work
      // only do it when regular http. J1.5 does not handle well https yet (host has to be same as http host)
      // we still use sh404sef http live site config, until Joomla finally gets its own
      if (empty( $shPageInfo->shHttpsSave)) {
        $shPageInfo->base = empty( $GLOBALS['shConfigLiveSite']) ? $shPageInfo->base
        : str_replace( 'http://' . $_SERVER['HTTP_HOST'], '', $GLOBALS['shConfigLiveSite']) .'/';
      }

      $shPageInfo->URI = array();
      if (isset ($_SERVER['REQUEST_URI'])) {
        //strip out the base
        $REQUEST = str_replace($GLOBALS['shConfigLiveSite'],'',$_SERVER['REQUEST_URI']);
        if (!empty($shPageInfo->shHttpsSave))
        $REQUEST = str_replace($shPageInfo->shHttpsSave,'',$_SERVER['REQUEST_URI']);
        $REQUEST = preg_replace('/^'.preg_quote($shPageInfo->base,'/').'/','',$REQUEST);
        // V 1.2.4. preserve ? / is it a good idea ?
        $shPageInfo->URI = new sh_Net_URL((empty($shPageInfo->shHttpsSave)?rtrim($GLOBALS['shConfigLiveSite'], '/'):$shPageInfo->shHttpsSave).'/'.ltrim($REQUEST,'/'));
      }else{
        $shPageInfo->QUERY_STRING = isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '';
        $shPageInfo->URI = new sh_Net_URL($shPageInfo->index.$shPageInfo->QUERY_STRING);
      }
      // V 1.2.4.s
      // TODO Redirect any host to $mosConfig_live_site - in case same site is accessed through synonyms domains
      if ($sefConfig->shAutoRedirectWww && empty($_POST) && !empty( $_SERVER['HTTP_HOST'])) {	  // mode = false do nothing, true = auto-redirect
        // Auto redirect will do a 301 from www to non-www if $GLOBALS['shConfigLiveSite'] does not have www
        // and from non-www to www if $GLOBALS['shConfigLiveSite'] has www
        _log( 'shPageInfo', $shPageInfo);
        _log( 'shConfigLiveSite = '. $GLOBALS['shConfigLiveSite']);
        $shTemp = explode('/', str_replace($shPageInfo->URI->protocol.'://', '', $GLOBALS['shConfigLiveSite']) );
        $shLiveSite = strtolower($shTemp[0]);
        _log( '$shLiveSite = '. $shLiveSite);
        if ($shLiveSite != strtolower($_SERVER['HTTP_HOST'].(!sh404SEF_USE_NON_STANDARD_PORT || empty($shPageInfo->URI->port) ? '' : ':'.$shPageInfo->URI->port))) {
          if     (substr( $shLiveSite, 0, 4) == 'www.'
          && strtolower('www.'.$_SERVER['HTTP_HOST'].(!sh404SEF_USE_NON_STANDARD_PORT || empty($shPageInfo->URI->port) ? '' : ':'.$shPageInfo->URI->port)) == $shLiveSite) {
            _log('Redirecting from non www to wwww');
            shRedirect( $shPageInfo->URI->protocol.'://www.'.$_SERVER['HTTP_HOST'].(!sh404SEF_USE_NON_STANDARD_PORT || empty($shPageInfo->URI->port) ? '' : ':'.$shPageInfo->URI->port).$shPageInfo->shSaveRequestURI);
          }
          if     (substr( $shLiveSite, 0, 4) != 'www.'
          && strtolower(substr( $_SERVER['HTTP_HOST'].(!sh404SEF_USE_NON_STANDARD_PORT || empty($shPageInfo->URI->port) ? '' : ':'.$shPageInfo->URI->port), 4)) == $shLiveSite) {
            _log('Redirecting from www to non wwww');
            shRedirect($shPageInfo->URI->protocol.'://'.str_replace('www.', '', $_SERVER['HTTP_HOST'].(!sh404SEF_USE_NON_STANDARD_PORT || empty($shPageInfo->URI->port) ? '' : ':'.$shPageInfo->URI->port)).$shPageInfo->shSaveRequestURI);
          }
        }
      }
      //Make sure host name matches our config, we need this later.
      if (strpos($GLOBALS['shConfigLiveSite'],$shPageInfo->URI->host) === false) {
        _log('Redirecting to home : host don\'t match our config');
        shRedirect($GLOBALS['shConfigLiveSite']);
      } else {
        $shPageInfo->shCurrentPageURL =  $shPageInfo->URI->protocol.'://'.$shPageInfo->URI->host.(!sh404SEF_USE_NON_STANDARD_PORT || empty($shPageInfo->URI->port) ? '' : ':'.$shPageInfo->URI->port).$shPageInfo->URI->path;
        _log('Current page URL : '.$shPageInfo->shCurrentPageURL);
        $shPageInfo->shCurrentPagePath = str_replace( $GLOBALS['shConfigLiveSite'], '', $shPageInfo->shCurrentPageURL);
        _log('Current page path : '.$shPageInfo->shCurrentPagePath);
        if (empty($sefConfig->shRewriteMode)) {
          $shPageInfo->baseUrl = $shPageInfo->shCurrentPageURL;
        } else {
          $shPageInfo->baseUrl = $GLOBALS['shConfigLiveSite'] . rtrim( $sefConfig->shRewriteStrings[$sefConfig->shRewriteMode], '/')
          . $shPageInfo->shCurrentPagePath;
        }
        // V 1.2.4.s PR2 : workaround for Virtuemart cookie check issue
        // see second part in shSef404.php
        if (shIsSearchEngine()) { // simulate doing successfull cookie check
          _log('Setting VMCHECK cookie for search engine');
          $_COOKIE['VMCHECK'] = 'OK';
          $_REQUEST['vmcchk'] = 1;
          // from VM 1.1.2 onward, result is stored in session, not cookie
          $_SESSION['VMCHECK'] = 'OK';
        }
      }
    } else
    JError::RaiseError( _COM_SEF_NOREAD."( $sef404 )<br />"._COM_SEF_CHK_PERMS);
  }
  // save page info
  shRouter::shPageInfo($shPageInfo);
}
?>
