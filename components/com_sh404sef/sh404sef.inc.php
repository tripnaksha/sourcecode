<?php
/**
 * SEF module for Joomla!
 *
 * @author      $Author: shumisha $
 * @copyright   Yannick Gaultier - 2007
 * @package     sh404SEF-15
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     $Id: sh404sef.php 833 2008-11-25 21:03:41Z silianacom-svn $
 *
 */

// Security check to ensure this file is being included by a parent file.
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

// V 1.2.4.p
define('_OPENSEF', '1');

global $shMosConfig_lang, $shMosConfig_locale, $shHomeLink, $mainframe;

$database	= & JFactory::getDBO();
$shPageInfo = & shRouter::shPageInfo();  // get page details gathered by system plugin
$sefConfig = & shRouter::shGetConfig();

switch ($shPageInfo->URI->path) {
  case $shPageInfo->base:
  case $shPageInfo->base.$shPageInfo->index: {
    _log('Processing homepage');
    $option = (isset($_GET['option'])) ? $_GET['option'] : (isset($_REQUEST['option'])) ? $_REQUEST['option'] : null;
    // shumisha : need to reset language to default if we are loading homepage. It's a bad fix !
    // shumisha 2077-03-28 : this breaks language when URL is home, but other data is passed through
    // POST. So let's try to improve by checking also that no option is requested
    if (is_null($option)) {
      _log('$option is not set');
      // V x redirect to Homepage if livesite/index.php or livesite/index.php?lang=xx is requested.
    		if ((empty($shPageInfo->URI->querystring) && empty($_POST)) // do not redirect if there is some post data!, will break ajax and more
    		|| ( (count($_GET)+count($_POST)) == 1 && !empty($_REQUEST['lang']))){
    		  $shLang = JRequest::getVar( 'lang');
    		  if (!empty($shLang)) {
    		    $shTemp = shGetNameFromIsoCode($shLang);
    		    if ($shTemp == $shMosConfig_locale)
    		    $shTemp = '';
    		    else $shTemp = 	$shLang;
    		  } else $shTemp = '';
    		  $shAnchor = empty($shPageInfo->URI->anchor) ? '':'#'.$shPageInfo->URI->anchor;
    		  if (!empty($sefConfig->shForcedHomePage)) { // V 1.2.4.t
    	  			$shTmp = $shTemp.$shAnchor;
    	  			$dest = shFinalizeURL($sefConfig->shForcedHomePage.(empty($shTmp) ? '' : '/'.$shTmp));
    		  } else {
    		    $shRewriteBit = empty($shTemp) ? '/':$sefConfig->shRewriteStrings[$sefConfig->shRewriteMode];
    		    $dest = shFinalizeURL($GLOBALS['shConfigLiveSite'].$shRewriteBit.$shTemp.$shAnchor);
    		  }
    		  if (strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false) { // IIS adds index.php to $_SERVER['REQUEST_URI'], even if it
    		    // was not requested by visitor
    		    if (empty($sefConfig->shForcedHomePage)) {
        				$target = str_replace('index.php', '', $shPageInfo->shSaveRequestURI);
        				$target = str_replace('Index.php', '', $target);  // sometimes IIS uses Index.php instead of index.php
        				$current = $shPageInfo->URI->protocol.'://'.$shPageInfo->URI->host.(!sh404SEF_USE_NON_STANDARD_PORT || empty($shPageInfo->URI->port) ? '' : ':'.$shPageInfo->URI->port)
        				.$target.$shAnchor;
    		    } else {
        				$current = $dest;  // if there is a splash page, we don't know what to do, so avoid infinite loop
    		    }
    		  } else {
    		    $current = $shPageInfo->URI->protocol.'://'.$shPageInfo->URI->host.(!sh404SEF_USE_NON_STANDARD_PORT || empty($shPageInfo->URI->port) ? '' : ':'.$shPageInfo->URI->port)
    		    .$shPageInfo->shSaveRequestURI.$shAnchor;
    		  }
    		  if (sh404SEF_REDIRECT_IF_INDEX_PHP && $dest != $current && strpos($current,'index2.php' ) === false) {// do not redirect if index2.php as well
    		    _log('Redirecting index.php or index.php?lang=xx from '.$current.' to '.$dest);
    		    shRedirect($dest);
    		  }
    		}
    		if ( empty($_GET['lang']) && empty($_REQUEST['lang'])) {
    		  $vars['lang'] = shGetIsoCodeFromName($shMosConfig_locale);
    		  $vars['lang'] = shGetIsoCodeFromName($shMosConfig_locale);
    		}
    		shCheckVMCookieRedirect(); // V 1.2.4.t

    		$menu =& shRouter::shGetMenu();
    		$shHomePage = & $menu->getDefault();
    		if ($shHomePage) {
    		  if ( (substr( $shHomePage->link, 0, 9) == 'index.php')  // if link on homepage is a local page
    		  && (!preg_match( '/Itemid=[0-9]*/', $shHomePage->link))) {  // and it does not have an Itemid
    		    $shHomePage->link .= ($shHomePage->link == 'index.php' ? '?':'&').'Itemid='.$shHomePage->id;  // then add itemid
    		  }
    		  $shHomeLink = $shHomePage->link;
    		  if (!strpos($shHomeLink,'lang=')) {
    		    //V 1.2.4.q protect against not existing
    		    $shDefaultIso = shGetIsoCodeFromName(shGetDefaultLang());
    		    $shSepString = (substr($shHomeLink, -9) == 'index.php' ? '?':'&');
    		    $shHomeLink .= $shSepString.'lang='.$shDefaultIso;
    		  }
    		  $shHomeLink = shSortUrl($shHomeLink);  // $shHomeLink has lang info, whereas $homepage->link may or may not
    		}

    		if ($shPageInfo->index == 'index.php') {  // prevents loading homepage if accessing index2.php (ajax calls)
    		  $vars['Itemid'] = $shHomePage->id;
    		  $_SERVER['QUERY_STRING'] = $shPageInfo->QUERY_STRING  = str_replace('index.php?','',$shHomeLink);
    		  $REQUEST_URI = $shPageInfo->base.'index.php?'.$shPageInfo->QUERY_STRING;
    		  $_SERVER['REQUEST_URI'] = $REQUEST_URI;
    		  $shPageInfo->shCurrentPageNonSef = 'index.php?'.$shPageInfo->QUERY_STRING;  // V 1.2.4.s
    		  _log('Homepage non-sef = '.$shPageInfo->shCurrentPageNonSef);
    		  $matches = array();
    		  if (preg_match("/option=([a-zA-Z_0-9]+)/", $shPageInfo->QUERY_STRING, $matches)) {
    		    $vars['option'] = $matches[1];
    		  }

    		  // shumisha V 1.2.4.k better handling of homepage if it is not com_frontpage
    		  parse_str($shPageInfo->QUERY_STRING,$varsTmp);
    		  $vars = array_merge($vars,$varsTmp);

    		  // that may help may extensions !
    		  //$_GET = array_merge($_GET,$vars);
    		  //$_REQUEST = array_merge($_REQUEST,$vars);

    		  // version x allow automatic language detection
    		  if (!shIsSearchEngine()) {
    		    shGuessLanguageAndRedirect( $shPageInfo->QUERY_STRING);
    		  }
    		  unset($matches);
    		  if (!headers_sent()) {
    		    header('HTTP/1.0 200 OK');
    		  }
    		  else {
    		    $url = $GLOBALS['shConfigLiveSite'].$_SERVER['QUERY_STRING'];
    		    _log('Headers already sent on homepage');
    		    JError::raiseError(500, "<br />SH404SEF : headers were already sent when I got control!<br />Killed at line ".__LINE__." in ".basename(__FILE__).": HEADERS ALREADY SENT (200)<br />URL=".@$uri->_uri.'<br />OPTION='.$vars['option'] );
    		  }
    		}
    }
    // V 1.2.4.j : optionnally redirect non-sef URL to SEF counterpart. This does not work however if Joomfish is active
    // as Joomfish has not been initialiaed at this point in time, and thus cannot translate. So we disable this function
    // if Joomfish is running (but enable it again if JF is running but current language is default languages, as this
    // is what JF will return)
    else {
      if ( $sefConfig->shRedirectNonSefToSef && (!empty($shPageInfo->URI->url)) && strpos( $shPageInfo->URI->url, 'tmpl=component') === false && empty($_POST)) {
        // try fetching from DB
         
        $shSefUrl = null;
        $shNonSefUrl = str_replace(empty($shPageInfo->shHttpsSave) ? $GLOBALS['shConfigLiveSite'] : $shPageInfo->shHttpsSave, '', $shPageInfo->URI->url);
        $shNonSefUrl = ltrim($shNonSefUrl, '/');
        if (!preg_match( '/(&|\?)lang=[a-zA-Z]{2,3}/iU', $shNonSefUrl)) {
          $shNonSefUrl = shSetURLVar($shNonSefUrl, 'lang',
          shGetIsoCodeFromName($GLOBALS[ 'shMosConfig_locale' ]));
        }
        $shNonSefUrl = shNormalizeNonSefUrl($shNonSefUrl);  // get back to 1.0.x non-sef url format
        $urlType = shGetSefURLFromCacheOrDB( $shNonSefUrl, $shSefUrl);
        if ($urlType == sh404SEF_URLTYPE_AUTO || $urlType == sh404SEF_URLTYPE_CUSTOM) {  // found a match
          $shRewriteBit = $sefConfig->shRewriteStrings[$sefConfig->shRewriteMode];
          $shSefUrl =  $GLOBALS['shConfigLiveSite'].$shRewriteBit.ltrim( $shSefUrl, '/')
          .(($shPageInfo->URI->anchor)?"#".$shPageInfo->URI->anchor:'');
          _log('redirecting non-sef to existing SEF : '.$shSefUrl);
          shRedirect( $shSefUrl);
        }
        if ( !shIsMultilingual() // V 1.2.4.q
        || (shIsMultilingual() == 'joomfish'
        && shGetNameFromIsoCode(shDecideRequestLanguage())
        == $GLOBALS[ 'shMosConfig_locale' ] )) { // $shMosConfig_locale is still deafult lang, as
          // language has not been discovered yet
          $GLOBALS['mosConfig_defaultLang'] = $shMosConfig_locale;  // V 1.2.4.t joomfish not initialised so we must do
          // this otherwise shGetDefaultLanguage will not work
          $lang = JRequest::getVar( 'lang', $shMosConfig_locale, 'GET' );
          $shUri = null;
          $shSefUrl = shSefRelToAbs($shNonSefUrl, $lang, $shUri);
          if (strpos( $shSefUrl, 'option=com') === false) {
            _log('redirecting non-sef to newly created SEF : '.$shSefUrl);
            shRedirect( $shSefUrl);
          }
        }
      } else {
        $shPageInfo->shCurrentPageNonSef = $shPageInfo->shCurrentPagePath;
      }
    }
    break;
  }
  case "": {
    JError::RaiseError( 500,
    _COM_SEF_STRANGE." URI->path=".$shPageInfo->URI->path.":<br />".basename(__FILE__)."-".__LINE__);
  }
  default: {
    // sanity check ok so process URI
    // strip out the base
    // V 1.3.1 always decode !
    //if ($sefConfig->shEncodeUrl) {
    if (!empty($shPageInfo->URI->path)) {
      $shPageInfo->URI->path = shUrlDecode($shPageInfo->URI->path);
      _log('URL decoding of URI->path');
    }
    if (count($shPageInfo->URI->querystring) > 0) {
      foreach ($shPageInfo->URI->querystring as $key=>$value)
      $shPageInfo->URI->querystring[$key] = rawurldecode($value);
    }
    if (!empty($shPageInfo->URI->anchor)) {
      $shPageInfo->URI->anchor = rawurldecode($shPageInfo->URI->anchor);
    }
    //}

    // V 1.2.4.t 301 redirect if Virtuemart cookie check. This has to be from a pre-existing link in Search engine index
    shCheckVMCookieRedirect();
    $path = preg_replace("/^".preg_quote($shPageInfo->base,"/")."/","",$shPageInfo->URI->path);
    $path_array = explode("/",$path);
    _log('Extracted path array : ', $path_array);
    $ext = getExt($path_array);
    $sef_ext_class = "sef_".$ext['name'];

    // V 1.2.4.p if other than J! SEF, then use sh404SEF to decode
    if ($sef_ext_class != 'sef_content' && $sef_ext_class != 'sef_component') {
      $sef_ext_class = 'sef_404';
      $ext['path'] = sh404SEF_FRONT_ABS_PATH.'sef_ext.php';
      $ext['name'] = '404';
    }

    //Dit is meestal sef_404 en anders heeft het te maken met sef advance
    require_once($ext['path']);
    eval("\$sef_ext = new $sef_ext_class;");

    $pos = 0;

    if (isset($_REQUEST['option'])) {
      $pos = array_search($_REQUEST['option'],$path_array);
    }
    if (!(($sef_ext_class == "sef_content")or($sef_ext_class == "sef_component"))) {
      if ($pos == 0) {
        array_unshift($path_array,"option");
      }
    }

    $_SEF_SPACE = $sefConfig->replacement;
    $shPageInfo->QUERY_STRING  = $sef_ext->revert($path_array, $pos);
    _log('Reverted query string = '.$shPageInfo->QUERY_STRING);
    //http://localhost/sil_base/content/view/12/1/mosConfig_absolute_path,ailleurs/
    shDoSecurityChecks( $shPageInfo->QUERY_STRING, false); // check this newly created URL
    // V 1.2.4.l added automatic redirect of Joomla standard SEF to sh404SEF URL.
    // V 1.2.4.p restrict automatic redirect to Joomla own sef, otherwise it breaks opensef/sefadvance sef_ext files
    // V x : allow redirect even if Joomfish, if URL is already in DB but check if reverted string is valid
    // may not be so in case of attacks or badly formed J! SEF url
    if ( is_valid($shPageInfo->QUERY_STRING) &&($sef_ext_class == 'sef_content' || $sef_ext_class == 'sef_component')) {  // if we have Joomla standard SEF
      if ( $sefConfig->shRedirectJoomlaSefToSef && $shPageInfo->URI->url && empty($_POST)) {// and are set to auto-redirect to SEF URLs
        // try fetching from DB
        $shSefUrl = null;
        $nonSefURL = 'index.php?'. $shPageInfo->QUERY_STRING;
        if (strpos( $nonSefURL, 'lang=') === false)
        $nonSefURL = shSetURLVar($nonSefURL, 'lang',
        shGetIsoCodeFromName($GLOBALS[ 'shMosConfig_locale' ]));
        $urlType = shGetSefURLFromCacheOrDB( shSortURL($nonSefURL), $shSefUrl);
        if ($urlType == sh404SEF_URLTYPE_AUTO || $urlType == sh404SEF_URLTYPE_CUSTOM) {  // found a match
          $shRewriteBit = $sefConfig->shRewriteStrings[$sefConfig->shRewriteMode];
          $shSefUrl =  (empty($shPageInfo->shHttpsSave) ? $GLOBALS['shConfigLiveSite'] : $shPageInfo->shHttpsSave).$shRewriteBit.ltrim( $shSefUrl, '/')
          .(($shPageInfo->URI->anchor)?"#".$shPageInfo->URI->anchor:'');
          _log('Redirecting J! sef URl to existing SEF URL : '.$shSefUrl);
          shRedirect( $shSefUrl);
        }
        if ( !shIsMultilingual() // V w 01/09/2007 22:02:40 if no joomfish
        || (shIsMultilingual() == 'joomfish'   // or joomfish, but default language, then we can redirect
        && shGetNameFromIsoCode(shDecideRequestLanguage())
        == $GLOBALS[ 'shMosConfig_locale' ])   // $shMosConfig_locale is still deafult lang, as
        // language has not been discovered yet
        ) {
          $shSefUrl = (empty($shPageInfo->shHttpsSave) ? $GLOBALS['shConfigLiveSite'] : $shPageInfo->shHttpsSave).'/index.php?'. $shPageInfo->QUERY_STRING;
          $GLOBALS['mosConfig_defaultLang'] = $shMosConfig_locale;  // V x 01/09/2007 22:05:11 joomfish not initialised so we must do
          // this otherwise shGetDefaultLanguage will not work
          // check if language in URL
          $shIsoCode = '';
          $shTemp = explode( 'lang,', $shSefUrl);
          if (!empty($shTemp[1]))
          $shIsoCode = explode('&', $shTemp[1]);
          $shUri = null;
          $shSefUrl = shSefRelToAbs($shSefUrl, shGetNameFromIsoCode( $shIsoCode), $shUri);
          if  (strpos( $shSefUrl, '/content/findkey') === false  // if this is no more a J! SEF URL, then redirect
          && strpos( $shSefUrl, '/content/view') === false
          && strpos( $shSefUrl, '/content/section') === false
          && strpos( $shSefUrl, '/content/category') === false
          && strpos( $shSefUrl, '/content/blogsection') === false
          && strpos( $shSefUrl, '/content/blogcategory') === false
          && strpos( $shSefUrl, '/content/archivesection') === false
          && strpos( $shSefUrl, '/content/archivecategory') === false
          && strpos( $shSefUrl, '/content/new') === false
          && strpos( $shSefUrl, '/content/vote') === false
          && strpos( $shSefUrl, '/component/option') === false) {
            _log('Redirecting J! sef URl to newly created SEF URL : '.$shSefUrl);
            shRedirect( $shSefUrl );
          }
        }
      }
    }
    if (is_valid($shPageInfo->QUERY_STRING)) {
      // V x : fix for login/logout in other than defautl language
      if ((isset($_GET['option']) && ($_GET['option'] == 'login' || $_GET['option'] == 'logout')) ||
      (isset($_POST['option']) && ($_POST['option'] == 'login' || $_POST['option'] == 'logout'))) {
        // we must preserve $option no matter what
        $_SERVER['QUERY_STRING'] = '';
        $REQUEST_URI = $shPageInfo->base.'index.php';
        $_SERVER['REQUEST_URI'] = $REQUEST_URI;
        $uri = shRouter::build($GLOBALS['shConfigLiveSite'].'/index.php');
        $shPageInfo->shCurrentPageNonSef = 'index.php';
        _log('Non-sef URl forced to index.php (login/logout)');
      } else {
        $anchor = ($shPageInfo->URI->anchor) ? '#'.$shPageInfo->URI->anchor:'';
        $incomingQueryString = str_replace('&amp;', '&', $shPageInfo->URI->getQueryString());
        $incomingQueryString = empty($incomingQueryString) ? '' : '&'.$incomingQueryString;
        $shPageInfo->QUERY_STRING = str_replace('&?', '&', $shPageInfo->QUERY_STRING.$incomingQueryString.$anchor);
        $_SERVER['QUERY_STRING'] = $shPageInfo->QUERY_STRING;
        $REQUEST_URI = $shPageInfo->base.'index.php?'.$shPageInfo->QUERY_STRING;
        $_SERVER['REQUEST_URI'] = $REQUEST_URI;
        $shPageInfo->shCurrentPageNonSef = 'index.php?'.$shPageInfo->QUERY_STRING; // V 1.2.4.s
        _log('Non-sef URL : '.$shPageInfo->shCurrentPageNonSef);

        // V 1.2.4.t better handling of params
        parse_str($shPageInfo->QUERY_STRING,$vars);
       
        // version x allow automatic language detection
        shGuessLanguageAndRedirect( $shPageInfo->QUERY_STRING);
         
      }
      if (!headers_sent()) {
        // save page info
        shRouter::shPageInfo($shPageInfo);
        header('HTTP/1.0 200 OK');
      } else{
        $url = $GLOBALS['shConfigLiveSite'].'/index.php?'.$_SERVER['QUERY_STRING'];
        _log('Headers already sent before getting control');
        print_r($path_array);
        JError::RaiseError( 500,
        	    	"<br />SH404SEF : headers were already sent when I got control!<br />Killed at line ".__LINE__." in ".basename(__FILE__).": HEADERS ALREADY SENT (200)<br />URL=".@$url.'<br />OPTION='.@$option);
      }
    } else {
      // 1.2.4t let's check if better result with trailing slash
      // or without
      $shLastBit = $path_array[count($path_array)-1];
      if ( (empty($sefConfig->suffix)
      || (!empty($sefConfig->suffix) && strpos($shLastBit, $sefConfig->suffix) === false) )
      && ( empty($sefConfig->addfile)
      || (!empty($sefConfig->addfile) && strpos($shLastBit, $sefConfig->addfile) === false) ))
      {
        $shTempPathArray = $path_array;
        if (!empty($shLastBit))   // if URl does not end with a /
        $shTempPathArray[] = '';  // add one
        else
        unset($shTempPathArray[count($shTempPathArray)-1]); // if it ends with a /, remove it
        $shQueryString = $sef_ext->revert($shTempPathArray, $pos);
        if (is_valid($shQueryString)) {  //let's redirect to this new URL
          $dest = str_replace($GLOBALS['shConfigLiveSite'].$sefConfig->shRewriteStrings[$sefConfig->shRewriteMode], '', $shPageInfo->URI->url);
          $dest = trim( str_replace($GLOBALS['shConfigLiveSite'], '', $dest), '/');
          _log('Redirecting to same with trailing slash added');
          shRedirect($GLOBALS['shConfigLiveSite'].$sefConfig->shRewriteStrings[$sefConfig->shRewriteMode]
          .$dest.(empty($shLastBit) ? '': '/'));
        }
      }
      // V 1.3.1 : check for aliases
      $path = $sefConfig->shEncodeUrl ? shUrlEncode( $path) :  $path;  // fix for encoded urls, thanks ByeVas
      $incomingUrl = $path;
      if (!empty($shPageInfo->URI->querystring)) {
        $tmp = '';
        foreach($shPageInfo->URI->querystring as $k => $v)
        $tmp .= '&'.$k.'='.$v;
        $incomingUrl .= '?'.ltrim($tmp, '&');
      }
      $query = "SELECT newurl FROM #__sh404sef_aliases WHERE alias = '".$incomingUrl."'";
      $database->setQuery($query);
      $dest = $database->loadResult();
      shCheckRedirect( $dest, $incomingUrl );
      // check also without the query string. useful for Google gclid var for instance
      $query = "SELECT newurl FROM #__sh404sef_aliases WHERE alias = '".$path."'";
      $database->setQuery($query);
      $dest = $database->loadResult();
      shCheckRedirect( $dest, $path );

      // bad URL, so check to see if we've seen it before
      // V 1.2.4.k 404 errors logging is now optional
      if ($sefConfig->shLog404Errors) {
        $query = "SELECT * FROM #__redirection WHERE oldurl = '".$path."'";
        $database->setQuery($query);
        $results=$database->loadObjectList();

        if ($results) {
          // we have, so update counter
          $database->setQuery("UPDATE #__redirection SET cpt=(cpt+1) WHERE oldurl = '".$path."'");
          $database->query();
        }
        else {
          // record the bad URL
          $query = 'INSERT INTO `#__redirection` ( `cpt` , `rank`, `oldurl` , `newurl` , `dateadd` ) ' // V 1.2.4.q
          . ' VALUES ( \'1\', \'0\',\''.$path.'\', \'\', CURDATE() );'
          . ' ';
          $database->setQuery($query);
          $database->query();
        }
        // add more details about 404 into security log file
        if ($sefConfig->shSecLogAttacks) {
          $sep = "\t";
          $logData  = date('Y-m-d').$sep.date('H:i:s').$sep.'Page not found (404)'.$sep.$_SERVER['REMOTE_ADDR'].$sep;
          $logData .= getHostByAddr( $_SERVER['REMOTE_ADDR']).$sep;
          $logData .= $_SERVER['HTTP_USER_AGENT'].$sep.$_SERVER['REQUEST_METHOD'].$sep.$_SERVER['REQUEST_URI'];
          $logData .= empty($_SERVER['HTTP_REFERER']) ? "\n" : $sep.$_SERVER['HTTP_REFERER']."\n";
          shLogToSecFile($logData);
        }
      }
      // redirect to the error page
      // You MUST create a static content page with the title 404 for this to work properly
      $mosmsg = ' ('.$GLOBALS['shConfigLiveSite'].'/'.ltrim($path, '/').')'; // V 1.2.4.t
      $vars['option'] = 'com_content';
      $vars['view'] = 'article';
      // V 1.2.4.t  set Itemid to homepage // V 1.3.1 RC : param to set Itemid
      if (!sh404SEF_PAGE_NOT_FOUND_FORCED_ITEMID) {
        $menu =& shRouter::shGetMenu();
        $shHomePage = & $menu->getDefault();
        $vars['Itemid'] = (empty($shHomePage)) ? null : $shHomePage->id;
    		} else
    		$vars['Itemid'] = sh404SEF_PAGE_NOT_FOUND_FORCED_ITEMID;

    		if ($sefConfig->page404 == '9999999')  // V 1.2.4.t 404 goes to frontpage not allowed anymore. Protect against older
    		$sefConfig->page404 == '0';           // configuration values carried over when upgrading
    		if ($sefConfig->page404 == '0') {
    		  $sql='SELECT id  FROM #__content WHERE `title`="404"';
    		  $database->setQuery( $sql );

    		  if (($id = $database->loadResult())) {
    		  } else {
    		    JError::raiseError( 404, JText::_( 'Component Not Found' ) . $mosmsg );
    		  }
    		} else{
    		  $id = $sefConfig->page404;
    		}
    		$vars['id'] = $id;
    		$shPageInfo->QUERY_STRING = 'option=com_content&view=article&id='.$id.(empty($vars['Itemid'])?'':'&Itemid='.$vars['Itemid']);
    		$uri = shRouter::build($GLOBALS['shConfigLiveSite'].'/index.php?'.$shPageInfo->QUERY_STRING);
    		$shPageInfo->shCurrentPageNonSef = 'index.php?'.$shPageInfo->QUERY_STRING;

    		if (!headers_sent()) {
    		  header('HTTP/1.0 404 NOT FOUND');
    		  // V x : include error page, faster than loading Joomla 404 page. Not recommended though, why not show
    		  // your site ?
    		  if (is_readable(sh404SEF_FRONT_ABS_PATH.'404-Not-Found.tpl.html')) {
    		    $errorPage = file_get_contents(sh404SEF_FRONT_ABS_PATH.'404-Not-Found.tpl.html');
    		    if ($errorPage !== false) {
    		      $errorPage = str_replace('%sh404SEF_404_URL%', $vars['mosmsg'], $errorPage);
    		      $errorPage = str_replace('%sh404SEF_404_SITE_URL%', $GLOBALS['shConfigLiveSite'], $errorPage);
    		      $errorPage = str_replace('%sh404SEF_404_SITE_NAME%', $mainframe->getCfg('sitename'), $errorPage);
    		      echo $errorPage;
    		      die();
    		    }
    		  }
    		}
    		else {
    		  $shUri = null;
    		  $url = shSefRelToAbs($GLOBALS['shConfigLiveSite']."/index.php?".$_SERVER['QUERY_STRING'], '', $shUri);
    		  print_r($path_array);
    		  JError::RaiseError( 500,
                  "<br />SH404SEF : headers were already sent when I got control!<br />Killed at line ".__LINE__." in ".basename(__FILE__).": HEADERS ALREADY SENT (200)<br />URL=".@$url.'<br />OPTION='.@$option);
    		}
    } //end bad url
  }//
}


?>
