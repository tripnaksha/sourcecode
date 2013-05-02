<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is included in Joomla!
if (!defined('_JEXEC')) die('Restricted access.');

jimport('joomla.plugin.helper');

class JoomSEF
{

    function build(&$uri)
    {
        //var_dump($uri->_uri);
        global $mainframe;

        $config =& JFactory::getConfig();
        $sefConfig =& SEFConfig::getConfig();
        $cache =& SEFCache::getInstance();

        // trigger onSefStart patches
        $mainframe->triggerEvent('onSefStart');

        $prevLang = ''; // for correct title translations

        // do not SEF URLs with tmpl=component if set to
        if (!$sefConfig->sefComponentUrls && ($uri->getVar('tmpl') == 'component')) {
            $mainframe->triggerEvent('onSefEnd');
            return true;
        }

        // check if this is site root;
        // if site is root, do not do anything else
        // except if we have to set language every time
        $vars = $uri->getQuery(true);
        if (empty($vars) && (!SEFTools::JoomFishInstalled() || !$sefConfig->alwaysUseLang)) {
            // trigger onSefEnd patches
            $mainframe->triggerEvent('onSefEnd');
            $uri = new JURI(JURI::root());
            return true;
        }

        // check URL for junk if set to
        if ($sefConfig->checkJunkUrls) {
            $junkWords =& $sefConfig->getJunkWords();
            $seferr = false;

            if (substr($uri->getVar('option', ''), 0, 4) != 'com_') {
                $seferr = true;
            }
            elseif (count($junkWords)) {
                $exclude =& $sefConfig->getJunkExclude();

                foreach ($vars as $key => $val) {
                    if (in_array($key, $exclude)) continue;

                    // Check junk words
                    foreach ($junkWords as $word) {
                        if (is_string($val)) {
                            if (strpos($val, $word) !== false) {
                                $seferr = true;
                                break;
                            }
                        }
                    }
                    if ($seferr) break;
                }
            }

            if ($seferr) {
                // trigger onSefEnd patches
                $mainframe->triggerEvent('onSefEnd');

                // fix the path
                $path = $uri->getPath();
                if( $path[0] != '/' ) {
                    $path = JURI::base(true) . '/' . $path;
                    $uri->setPath($path);
                }

                return true;
            }
        }

        if (SEFTools::JoomFishInstalled()) {
            $lang = $uri->getVar('lang');

            // if lang not set
            if (empty($lang)) {
                if ($sefConfig->alwaysUseLang) {
                    // add lang variable if set to
                    $uri->setVar('lang', SEFTools::getLangCode());
                } else {
                    // delete lang variable so it is not empty
                    $uri->delVar('lang');
                }
            }

            // get the URL's language and set it as global language (for correct translation)
            $lang = $uri->getVar('lang');
            $code = '';
            if (!empty($lang)) {
                $code = SEFTools::getLangLongCode($lang);
                if (!is_null($code)) {
                    if ($code != SEFTools::getLangLongCode()) {
                        $language =& JFactory::getLanguage();
                        $prevLang = $language->setLanguage($code);
                        $language->load();
                    }
                }
            }

            // set the live_site according to language
            if ($sefConfig->langPlacement == _COM_SEF_LANG_DOMAIN) {
                $u =& JURI::getInstance();
                $curdomain = $sefdomain = $u->getHost();

                if (!empty($lang)) {
                    if (isset($sefConfig->jfSubDomains[$lang])) {
                        $sefdomain = $sefConfig->jfSubDomains[$lang];
                        //$uri->delVar('lang');
                    }
                }

                $config =& JFactory::getConfig();
                $config->setValue('joomfish.current_host', $curdomain);
                $config->setValue('joomfish.sef_host', $sefdomain);
            }
        }

        // if there are no variables and only single language is used
        $vars = $uri->getQuery(true);
        if (empty($vars) && !isset($lang)) {
            JoomSEF::_endSef($prevLang);
            return true;
        }

        // check if this is site default menu item (homepage)
        if (JoomSEF::_isHomePage($uri)) {
            $title = array();
            $uri = JoomSEF::_sefGetLocation($uri, $title, null, null, null, $uri->getVar('lang'));
            JoomSEF::_endSef($prevLang);
            return true;
        }

        $option = $uri->getVar('option');

        if (!is_null($option)) {
            $params =& SEFTools::getExtParams($option);
            
            // Check the stop rule
            $stopRule = trim($params->get('stopRule', ''));
            if( $stopRule != '' ) {
                if( preg_match('/'.$stopRule.'/', $uri->toString()) > 0 ) {
                    // Don't SEF this URL
                    $uri = JoomSEF::_createUri($uri);
                    JoomSEF::_endSef($prevLang);
                    return;
                }
            }
            
            // Check the variable filter test if set to
            if( $params->get('varFilterFail', '0') == '0' ) {
                $failedVars = array();
                if( !JoomSEF::_varFilterTest($uri, $failedVars) ) {
                    // Don't SEF this URL
                    $uri = JoomSEF::_createUri($uri);
                    JoomSEF::_endSef($prevLang);
                    return;
                }
            }
            
            switch($params->get('handling', '0')) {
                // skipped extensions
                case '2': {
                    $uri = JoomSEF::_createUri($uri);
                    JoomSEF::_endSef($prevLang);
                    return;
                }
                // non-cached extensions
                case '1': {
                    $router = $mainframe->get('sef.global.jrouter');
                    if( !empty($router) ) {
                        $uri = $router->build($uri->toString());
                    }
                    JoomSEF::_endSef($prevLang);
                    return;
                }
                // default handler
                default: {
                    // if component has its own sef_ext plug-in included.
                    // however, prefer own plugin if exists (added by Michal, 28.11.2006)
                    $compExt = JPATH_ROOT.DS.'components'.DS.$option.DS.'router.php';
                    $ownExt = JPATH_ROOT.DS.'components'.DS.'com_sef'.DS.'sef_ext'.DS.$option.'.php';
                    // compatible extension build block
                    if (file_exists($compExt) && !file_exists($ownExt)) {
                        // load the plug-in file
                        require_once($compExt);

                        $app        =& JFactory::getApplication();
                        $menu       =& JSite::getMenu();
                        $route      = $uri->getPath();
                        $query      = $uri->getQuery(true);
                        $component  = preg_replace('/[^A-Z0-9_\.-]/i', '', $query['option']);
                        $tmp        = '';

                        $function   = substr($component, 4) . 'BuildRoute';
                        $parts      = $function($query);

                        $total = count($parts);
                        for ($i = 0; $i < $total; $i++) {
                            $parts[$i] = str_replace(':', '-', $parts[$i]);
                        }

                        $result = implode('/', $parts);
                        $tmp    = ($result != "") ? '/'.$result : '';

                        // build the application route
                        $built = false;
                        if (isset($query['Itemid']) && !empty($query['Itemid'])) {
                            $item = $menu->getItem($query['Itemid']);

                            if (is_object($item) && $query['option'] == $item->component) {
                                $tmp = !empty($tmp) ? $item->route.'/'.$tmp : $item->route;
                                $built = true;
                            }
                        }

                        if(!$built) {
                            $tmp = 'component/'.substr($query['option'], 4).'/'.$tmp;
                        }

                        $route .= '/'.$tmp;
                        if($app->getCfg('sef_suffix') && !(substr($route, -9) == 'index.php' || substr($route, -1) == '/')) {
                            if (($format = $uri->getVar('format', 'html'))) {
                                $route .= '.' . $format;
                                $uri->delVar('format');
                            }
                        }

                        if($app->getCfg('sef_rewrite')) {
                            // transform the route
                            $route = str_replace('index.php/', '', $route);
                        }

                        // Unset unneeded query information
                        unset($query['Itemid']);
                        unset($query['option']);

                        //Set query again in the URI
                        $uri->setQuery($query);
                        $uri->setPath($route);

                        $uri = JoomSEF::_createUri($uri);

                        JoomSEF::_endSef($prevLang);
                        return;
                    }
                    // own extension block
                    else {
                        if (file_exists($ownExt)) {
                            require_once($ownExt);
                            $class = 'SefExt_'.$option;
                        } else {
                            $class = 'SefExt';
                        }
                        $sef_ext = new $class();

                        // Let the extension change the url and options
                        $sef_ext->beforeCreate($uri);

                        // Ensure that the session IDs are removed
                        // If set to
                        $sid = $uri->getVar('sid');
                        if (!$sefConfig->dontRemoveSid) $uri->delVar('sid');
                        // Ensure that the mosmsg are removed.
                        $mosmsg = $uri->getVar('mosmsg');
                        $uri->delVar('mosmsg');

                        // override Itemid if set to
                        $override = $params->get('itemid', '0');
                        $overrideId = $params->get('overrideId', '');
                        if (($override != '0') && ($overrideId != '')) {
                            $uri->setVar('Itemid', $overrideId);
                        }

                        // clean Itemid if desired
                        // David: only if overriding is disabled
                        if (isset($sefConfig->excludeSource) && $sefConfig->excludeSource && ($override == '0')) {
                            $Itemid = $uri->getVar('Itemid');
                            $uri->delVar('Itemid');
                        }

                        $url = JoomSEF::_uriToUrl($uri);

                        // try to get url from cache
                        if ($sefConfig->useCache) {
                            $sefstring = $cache->GetSefUrl($url);
                        }
                        if (!$sefConfig->useCache || !$sefstring) {
                            // check if the url is already saved in the database
                            $sefstring = $sef_ext->getSefUrlFromDatabase($uri);
                        }

                        if (!$sefstring) {
                            // rewrite the URL, creating new JURI object
                            $uri = $sef_ext->create($uri);
                        } else {
                            // Create new JURI object from $sefstring
                            $url = JURI::root();

                            if (substr($url, -1) != '/') {
                                $url .= '/';
                            }
                            $url .= $sefstring;
                            $fragment = $uri->getFragment();
                            if (!empty($fragment)) {
                                $url .= '#'.$fragment;
                            }
                            $uri = new JURI($url);
                        }

                        // reconnect the sid to the url
                        if (!empty($sid) && !$sefConfig->dontRemoveSid) $uri->setVar('sid', $sid);
                        // reconnect mosmsg to the url
                        if (!empty($mosmsg)) $uri->setVar('mosmsg', $mosmsg);

                        // reconnect ItemID to the url
                        // David: only if extension doesn't set its own Itemid through overrideId parameter
                        if (isset($sefConfig->excludeSource) && $sefConfig->excludeSource && $sefConfig->reappendSource && ($override == '0') && !empty($Itemid)) {
                            $uri->setVar('Itemid', $Itemid);
                        }

                        // let the extension change the resulting SEF url
                        $sef_ext->afterCreate($uri);
                    }
                }
            }
        }
        else if (!is_null($uri->getVar('Itemid'))) {
            // there is only Itemid present - we must override the Ignore multiple sources option
            $oldIgnore = $sefConfig->ignoreSource;
            $sefConfig->ignoreSource = 0;

            $title = array();
            $title[] = JoomSEF::_getMenuTitle(null, null, $uri->getVar('Itemid'));

            $uri = JoomSEF::_sefGetLocation($uri, $title, null, null, null, $uri->getVar('lang'));

            $sefConfig->ignoreSource = $oldIgnore;
        }

        JoomSEF::_endSef($prevLang);
    }

    function parse(&$uri)
    {
        global $mainframe;

        // test for the backlink plugin to work correctly
        if (JPluginHelper::isEnabled('system', 'backlink') ) {
            $joomlaRequest = $_SERVER['REQUEST_URI'];
            $realRequest = $uri->toString(array('path', 'query'));

            if ($realRequest != $joomlaRequest) {
                $uri = new JURI($joomlaRequest);
            }
        }

        // store the old URI before we change it in case we will need it
        // for default Joomla SEF
        $oldUri = new JURI($uri->toString());

        $sefConfig =& SEFConfig::getConfig();

        // load patches
        JPluginHelper::importPlugin('sefpatch');

        // trigger onSefLoad patches
        $mainframe->triggerEvent('onSefLoad');

        // get path
        $path = $uri->getPath();

        // remove basepath
        $path = substr_replace($path, '', 0, strlen(JURI::base(true)));

        // remove slashes
        $path = ltrim($path, '/');

        // remove prefix (both index.php and index2.php)
        //$path = eregi_replace('^index2?.php', '', $path);
        $path = preg_replace('/^index2?.php/i', '', $path);

        // remove slashes again to be sure there aren't any left
        $path = ltrim($path, '/');

        // replace spaces with our replacement character
        // (mainly for '+' handling, but may be useful in some other situations too)
        $path = str_replace(' ', $sefConfig->replacement, $path);

        // set the route
        $uri->setPath($path);

        // host name handling
        if (SEFTools::JoomFishInstalled() && ($sefConfig->langPlacement == _COM_SEF_LANG_DOMAIN)) {
            // different domains for languages handling
            $host = $uri->toString(array('host'));
            $host = trim($host, '/');

            $code = null;
            foreach ($sefConfig->jfSubDomains as $langCode => $domain) {
                if ($host == $domain) {
                    // if main language is not selected, use the first corresponding domain
                    if ($sefConfig->mainLanguage == '0') {
                        $code = $langCode;
                        break;
                    }
                    // main language is selected, use domain only if code is not already set,
                    // or the domain corresponds to main language
                    else {
                        if ($langCode == $sefConfig->mainLanguage) {
                            $code = $langCode;
                            break;
                        }
                        else if (is_null($code)) {
                            $code = $langCode;
                        }
                    }
                }
            }
            
            // we found a matching domain
            if (!is_null($code)) {
                JRequest::setVar('lang', $code);
                $config =& JFactory::getConfig();
                $config->set('joomsef.domain_lang', $code);
            }
        }

        // parse the url
        $vars = JoomSEF::_parseSefUrl($uri, $oldUri);

        // handle custom site name for extensions
        if (isset($vars['option'])) {
            $params =& SEFTools::getExtParams($vars['option']);

            $useSitename = $params->get('useSitename', '1');
            $customSitename = trim($params->get('customSitename', ''));

            $config =& JFactory::getConfig();

            if ($useSitename == '0') {
                // don't use site name
                $config->setValue('sitename', '');
            }
            elseif (!empty($customSitename)) {
                // use custom site name
                $config->setValue('sitename', $customSitename);
            }
        }

        // trigger onSefUnload patches
        $mainframe->triggerEvent('onSefUnload');

        return $vars;
    }

    function _determineLanguage($getLang = null)
    {
        // set the language for JoomFish
        if (SEFTools::JoomFishInstalled()) {
            $sefConfig =& SEFConfig::getConfig();
            $registry =& JFactory::getConfig();

            // save the default language of the site
            $locale = $registry->getValue('config.language');
            $GLOBALS['mosConfig_defaultLang'] = $locale;
            $registry->setValue("config.defaultlang", $locale);

            // get instance of JoomFishManager to obtain active language list and config values
            $jfm =&  JoomFishManager::getInstance();

            // Get language from request
            if (!empty($getLang)) {
                $lang = $getLang;
            }
            
            // Check if language is selected
            if (empty($lang)) {
                // Try to get language code from configuration
                if( ($sefConfig->mainLanguage != '0') ) {
                    $code = SEFTools::GetLangLongCode($sefConfig->mainLanguage);
                }

                // Try to get language code from JF cookie
                if (empty($code) || !JLanguage::exists($code)) {
                    $jfCookie = JRequest::getVar('jfcookie', null, 'COOKIE');
                    if( isset($jfCookie['lang']) ) {
                        $code = $jfCookie['lang'];
                    }
                }

                // if cookie is not set or the language does not exist
                if (empty($code) || !JLanguage::exists($code)) {
                    // try to get the code from browser setting if set to
                    if( $sefConfig->jfBrowserLang && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {
                        $active_iso = array();
                        $active_isocountry = array();
                        $active_code = array();
                        $activeLanguages = $jfm->getActiveLanguages();

                        if( count( $activeLanguages ) > 0 ) {
                            foreach ($activeLanguages as $alang) {
                                $active_iso[] = $alang->iso;
                                //if( eregi('[_-]', $alang->iso) ) {
                                if( preg_match('/[_-]/i', $alang->iso) ) {
                                    $isocountry = split('[_-]',$alang->iso);
                                    $active_isocountry[] = $isocountry[0];
                                }
                                $active_code[] = $alang->shortcode;
                            }

                            // figure out which language to use - browser languages are based on ISO codes
                            $browserLang = explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

                            foreach ($browserLang as $blang) {
                                if( in_array($blang, $active_iso) ) {
                                    $client_lang = $blang;
                                    break;
                                }
                                $shortLang = substr( $blang, 0, 2 );
                                if (in_array($shortLang, $active_isocountry)) {
                                    $client_lang = $shortLang;
                                    break;
                                }

                                // compare with code
                                if (in_array($shortLang, $active_code)) {
                                    $client_lang = $shortLang;
                                    break;
                                }
                            }

                            if (!empty($client_lang)) {
                                if( strlen($client_lang) == 2 ) {
                                    $code = SEFTools::getLangLongCode($client_lang);
                                }
                                else {
                                    $code = $client_lang;
                                }
                            }
                        }
                    }

                    // Otherwise get the default language code
                    if (empty($code)) {
                        $code = $registry->getValue('config.language');
                    }
                }
            }

            // get language long code if needed
            if (empty($code)) {
                $code = SEFTools::getLangLongCode($lang);
            }

            if (!empty($code)) {
                // set the site language
                if( $code != SEFTools::getLangLongCode() ) {
                    $language =& JFactory::getLanguage();
                    $language->setLanguage($code);
                    $language->load();

                    // set the backward compatible language
                    $backLang = $language->getBackwardLang();
                    $GLOBALS['mosConfig_lang'] = $backLang;
                    $registry->setValue("config.lang", $backLang);
                }

                // set joomfish language
                $jfLang = TableJFLanguage::createByJoomla($code);
                $registry->setValue("joomfish.language", $jfLang);

                // set some more variables
                global $mainframe;
                $registry->setValue("config.multilingual_support", true);
                $mainframe->setUserState('application.lang',$jfLang->code);
                $registry->setValue("config.jflang", $jfLang->code);
                $registry->setValue("config.lang_site",$jfLang->code);
                $registry->setValue("config.language",$jfLang->code);
                $registry->setValue("joomfish.language",$jfLang);

        		// overwrite global config with values from $jfLang if set to in JoomFish
        		$jfparams = JComponentHelper::getParams("com_joomfish");
        		$overwriteGlobalConfig = $jfparams->get( 'overwriteGlobalConfig', 0 );
        		if($overwriteGlobalConfig ) {
        			// We should overwrite additional global variables based on the language parameter configuration
        			$langParams = new JParameter( $jfLang->params );
        			$paramarray = $langParams->toArray();
        			foreach ($paramarray as $key=>$val) {
        				$registry->setValue("config.".$key,$val);
        	
        				if (defined("_JLEGACY")){
        					$name = 'mosConfig_'.$key;
        					$GLOBALS[$name] = $val;
        				}
        			}
        		}
        		
                // set the cookie with language
                if ($sefConfig->jfLangCookie) {
                    setcookie('jfcookie[lang]', $code, time() + 24*3600, '/');
                }
            }
        }
    }

    function _parseSefUrl(&$uri, &$oldUri)
    {
        global $mainframe;

        $db =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();

        $route = $uri->getPath();

        //Get the variables from the uri
        $vars = $uri->getQuery(true);
        
        // Should we generate canonical link automatically?
        $generateCanonical = (count($vars) > 0);

        // handle an empty URL (special case)
        if (empty($route)) {
            JoomSEF::_determineLanguage(JRequest::getVar('lang'));

            $menu  =& JSite::getMenu(true);

            // if route is empty AND option is set in the query, assume it's non-sef url, and parse apropriately
            if (isset($vars['option']) || isset($vars['Itemid'])) {
                return JoomSEF::_parseRawRoute($uri);
            }
            
            $item = $menu->getDefault();

            //Set the information in the request
            $vars = $item->query;

            //Get the itemid
            $vars['Itemid'] = $item->id;

            // Set the active menu item
            $menu->setActive($vars['Itemid']);
            
            // Create automatic canonical link if set to
            if ($generateCanonical) {
                $extAuto = 2;
                if (isset($vars['option'])) {
                    $params =& SEFTools::getExtParams($vars['option']);
                    $extAuto = $params->get('autoCanonical', 2);
                }
                $autoCanonical = ($extAuto == 2) ? $sefConfig->autoCanonical : $extAuto;
                
                if ($extAuto) {
                    $mainframe->set('sef.link.canonical', JURI::root());
                }
            }

            // MetaTags for frontpage
            $db->setQuery("SELECT id FROM #__plugins WHERE element = 'joomsef' AND folder = 'system' AND published = 1");
            if ($db->loadResult()) {
                // ... and frontpage has meta tags
                $db->setQuery("SELECT * FROM #__sefurls WHERE sefurl = '' OR sefurl = 'index.php' LIMIT 1");
                $sefRow = $db->loadObject();
                if( !empty($sefRow) ) {
                    global $mainframe;
                    if (!empty($sefRow->metatitle))  $mainframe->set('sef.meta.title', $sefRow->metatitle);
                    if (!empty($sefRow->metadesc))   $mainframe->set('sef.meta.desc', $sefRow->metadesc);
                    if (!empty($sefRow->metakey))    $mainframe->set('sef.meta.key', $sefRow->metakey);
                    if (!empty($sefRow->metalang))   $mainframe->set('sef.meta.lang', $sefRow->metalang);
                    if (!empty($sefRow->metarobots)) $mainframe->set('sef.meta.robots', $sefRow->metarobots);
                    if (!empty($sefRow->metagoogle)) $mainframe->set('sef.meta.google', $sefRow->metagoogle);
                    if (!empty($sefRow->canonicallink)) $mainframe->set('sef.link.canonical', $sefRow->canonicallink);
                }
            }

            return $vars;
        }

        $sef_ext = new SefExt();
        $newVars = $sef_ext->revert($route);

        if (!empty($newVars) && !empty($vars)) {
            // If this was SEF url, consider the vars in query as nonsef
            $nonsef = array_diff_key($vars, $newVars);
            if (!empty($nonsef)) {
                $mainframe->set('sef.global.nonsefvars', $nonsef);
            }
        }

        // try to parse joomla native seo
        if ($sefConfig->parseJoomlaSEO && empty($newVars)) {
            $router = $mainframe->get('sef.global.jrouter');
            $jvars = $router->parse($oldUri);
            if (!empty($jvars['option']) || !empty($jvars['Itemid'])) {
                $newVars = $jvars;
            }
        }

        if (!empty($vars)) {
            // append the original query string because some components
            // (like SMF Bridge and SOBI2) use it
            $vars = array_merge($vars, $newVars);
        } else {
            $vars = $newVars;
        }

        if (!empty($newVars)) {
            // Parsed correctly
            JoomSEF::_sendHeader('HTTP/1.0 200 OK');
            
            // Create automatic canonical link if set to and it is not already set
            $canonical = $mainframe->get('sef.link.canonical');
            if ($generateCanonical && empty($canonical)) {
                $extAuto = 2;
                if (isset($vars['option'])) {
                    $params =& SEFTools::getExtParams($vars['option']);
                    $extAuto = $params->get('autoCanonical', 2);
                }
                $autoCanonical = ($extAuto == 2) ? $sefConfig->autoCanonical : $extAuto;
                
                if ($extAuto) {
                    $mainframe->set('sef.link.canonical', JURI::root().$route);
                }
            }
        }
        else
        {
            // set nonsef vars
            $mainframe->set('sef.global.nonsefvars', $vars);

            // bad URL, so check to see if we've seen it before
            // 404 recording (only if enabled)
            if ($sefConfig->record404) {
                $query = "SELECT * FROM `#__sefurls` WHERE `sefurl` = '".$route."'";
                $db->setQuery($query);
                $results = $db->loadObjectList();

                if ($results) {
                    // we have it, so update counter
                    $db->setQuery("UPDATE `#__sefurls` SET `cpt`=(`cpt`+1) WHERE `sefurl` = '".$route."'");
                    $db->query();
                }
                else {
                    // record the bad URL
                    $query = "INSERT INTO `#__sefurls` (`cpt`, `sefurl`, `origurl`, `dateadd`) "
                    . " VALUES ( '1', '$route', '', CURDATE() )";
                    $db->setQuery($query);
                    $db->query();
                }
            }

            // redirect to the error page
            // you MUST create a static content page with the title 404 for this to work properly
            if ($sefConfig->showMessageOn404) {
                $mosmsg = 'FILE NOT FOUND: '.$route;
                $mainframe->enqueueMessage($mosmsg);
            }
            else $mosmsg = '';

            if ($sefConfig->page404 == '0') {
                $sql = 'SELECT `id`  FROM `#__content` WHERE `title`= "404"';
                $db->setQuery($sql);

                if (($id = $db->loadResult())) {
                    $vars['option'] = 'com_content';
                    $vars['view'] = 'article';
                    $vars['id'] = $id;
                }
                else {
                    die(JText::_('ERROR_DEFAULT_404').$mosmsg."<br />URI:".$_SERVER['REQUEST_URI']);
                }
            }
            elseif ($sefConfig->page404 == '9999999') {
                $menu  =& JSite::getMenu(true);
                $item = $menu->getDefault();

                //Set the information in the frontpage request
                $vars = $item->query;

                //Get the itemid
                $vars['Itemid'] = $item->id;
                $menu->setActive($vars['Itemid']);
            }
            else {
                $id = $sefConfig->page404;
                $vars['option'] = 'com_content';
                $vars['view'] = 'article';
                $vars['id'] = $id;
            }
            
            // If custom Itemid set, use it
            if ($sefConfig->use404itemid) {
                $vars['Itemid'] = $sefConfig->itemid404;
            }

            JoomSEF::_sendHeader('HTTP/1.0 404 NOT FOUND');
        }

        $config =& JFactory::getConfig();
        $lang = $config->get('joomsef.domain_lang');
        if (empty($lang)) {
            $lang = (isset($vars['lang']) ? $vars['lang'] : null);
        }

        JoomSEF::_determineLanguage($lang);

        return $vars;
    }

    function _sendHeader($header)
    {
        $f = $l = '';
        if (!headers_sent($f, $l)) {
            header($header);
        }
        else {
            JoomSEF::_headers_sent_error($f, $l, __FILE__, __LINE__);
        }
    }

    function _parseRawRoute(&$uri)
    {
        $sefConfig =& SEFConfig::getConfig();

        if( is_null($uri->getVar('option')) ) {
            // Set the URI from Itemid
            $menu =& JSite::getMenu(true);
            $item = $menu->getItem($uri->getVar('Itemid'));
            if( !is_null($item) ) {
                $uri->setQuery($item->query);
                $uri->setVar('Itemid', $item->id);
            }
        }
        
        $option = $uri->getVar('option');
        if( !is_null($option) ) {
            $params =& SEFTools::getExtParams($option);
            if( $params->get('varFilterFail', '0') == '1' ) {
                // We need to test the URL using variable filter
                // in order to stop its further processing in case it fails
                $failedVars = array();
                if( !JoomSEF::_varFilterTest($uri, $failedVars) )
                {
                    die($uri->toString() . '<br />' . JText::_('URL did not pass the variable filter test.'));
                }
            }
        }

        $extAuto = 2;
        if (isset($params)) {
            $extAuto = $params->get('autoCanonical', 2);
        }
        $autoCanonical = ($extAuto == 2) ? $sefConfig->autoCanonical : $extAuto;
                
        if (($sefConfig->nonSefRedirect && (count($_POST) == 0)) || $autoCanonical)
        {
            // Try to find the non-SEF URL in the database - don't create new!
            $oldDisable = $sefConfig->disableNewSEF;
            $sefConfig->disableNewSEF = true;

            $uri->setPath('index.php');
            $url = $uri->toString(array('path', 'query', 'fragment'));
            $sef = JRoute::_($url);

            // Restore the configuration
            $sefConfig->disableNewSEF = $oldDisable;

            if ($sefConfig->nonSefRedirect && (count($_POST) == 0)) {
                // Non-SEF redirect
                if( strpos($sef, 'index.php?') === false ) {
                    // Seems the URL is SEF, let's redirect
                    $f = $l = '';
                    if( !headers_sent($f, $l) ) {
                        $mainframe =& JFactory::getApplication();
                        $mainframe->redirect($sef);
                        exit();
                    } else {
                        JoomSEF::_headers_sent_error($f, $l, __FILE__, __LINE__);
                    }
                }
            }
            else if ($autoCanonical) {
                // Only set canonical URL
                global $mainframe;
                    
                // Remove the query part from SEF URL
                $pos = strpos($sef, '?');
                if ($pos !== false) {
                    $sef = substr($sef, 0, $pos);
                }
                    
                $mainframe->set('sef.link.canonical', $sef);
            }
        }

        return $uri->getQuery(true);
    }

    function _headers_sent_error($sentFile, $sentLine, $file, $line)
    {
        die("<br />Error: headers already sent in ".basename($sentFile)." on line $sentLine.<br />Stopped at line ".$line." in ".basename($file));
    }

    function & _createUri(&$uri)
    {
        $url = JURI::root();

        if( substr($url, -1) != '/' ) {
            $url .= '/';
        }
        $url .= $uri->toString(array('path', 'query', 'fragment'));

        $newUri = new JURI($url);
        return $newUri;
    }

    function _endSef($lang = '')
    {
        global $mainframe;

        $mainframe->triggerEvent('onSefEnd');
        JoomSEF::_restoreLang($lang);
    }

    function _restoreLang($lang = '')
    {
        if ($lang != '') {
            if ($lang != SEFTools::getLangLongCode()) {
                $language =& JFactory::getLanguage();
                $language->setLanguage($lang);
                $language->load();
            }
        }
    }

    function _isHomePage(&$uri)
    {
        static $homeQuery, $homeId;

        if( !isset($homeQuery) ) {
            // Get default item's query and id
            $menu =& JSite::getMenu(true);
            $item =& $menu->getDefault();
            $homeQuery = $item->query;
            $homeId = $item->id;
        }

        // Get URL query
        $query = $uri->getQuery(true);

        // We need to fix the id and catid for content
        if( isset($query['option']) && ($query['option'] == 'com_content') ) {
            SEFTools::fixVariable($uri, 'id');
            SEFTools::fixVariable($uri, 'catid');
        }

        // Check Itemid variable if present
        if( isset($query['Itemid']) ) {
            if( $query['Itemid'] != $homeId ) {
                // Itemid does not match
                return false;
            } else {
                // Itemid matches, remove it from query
                unset($query['Itemid']);
            }
        }

        // Remove the lang variable if present
        if( isset($query['lang']) ) {
            unset($query['lang']);
        }

        // Compare queries
        $cmp = array_diff($query, $homeQuery);
        if( count($cmp) > 0 ) {
            return false;
        }

        $cmp = array_diff($homeQuery, $query);
        if( count($cmp) > 0 ) {
            return false;
        }

        return true;
    }    
    
    function _getMenuTitle($option, $task, $id = null, $string = null)
    {
        $db =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();

        // JF translate extension.
        $jfTranslate = $sefConfig->translateNames ? ', `id`' : '';

        if ($title = JoomSEF::_getCustomMenuTitle($option)) {
            return $title;
        }

        // Which column to use?
        $column = 'name';
        if ($sefConfig->useAlias) {
            $column = 'alias';
        }

        if (isset($string)) {
            $sql = "SELECT `$column` AS `name`$jfTranslate FROM `#__menu` WHERE `link` = '$string' AND `published` > 0";
        }
        elseif (isset($id) && $id != 0) {
            $sql = "SELECT `$column` AS `name`$jfTranslate FROM `#__menu` WHERE `id` = '$id' AND `published` > 0";
        }
        else {
            // Search for direct link to component only
            $sql = "SELECT `$column` AS `name`$jfTranslate FROM `#__menu` WHERE `link` = 'index.php?option=$option' AND `published` > 0";
        }

        $db->setQuery($sql);
        $row = $db->loadObject();

        if ($row && !empty($row->name)) {
            $title = $row->name;
        }
        else {
            $title = str_replace('com_', '', $option);

            if (!isset($string) && !isset($id)) {
                // Try to extend the search for any link to component
                $sql = "SELECT `$column` AS `name`$jfTranslate FROM `#__menu` WHERE `link` LIKE 'index.php?option=$option%' AND `published` > 0";
                $db->setQuery($sql);
                $row = $db->loadObject();
                if (!empty($row)) {
                    if (!empty($row->name)) $title = $row->name;
                }
            }
        }

        return $title;
    }

    function _getMenuItemInfo($option, $task, $id = null, $string = null)
    {
        $db =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();

        // JF translate extension.
        $jfTranslate = $sefConfig->translateNames ? ', `id`' : '';

        $item->title = JoomSEF::_getCustomMenuTitle($option);

        // Which column to use?
        $column = 'name';
        if ($sefConfig->useAlias) $column = 'alias';

        // first test Itemid
        if (isset($id) && $id != 0) {
            $sql = "SELECT `$column` AS `name`, `params`$jfTranslate FROM `#__menu` WHERE `id` = $id AND `published` > 0";
        }
        elseif (isset($string)) {
            $sql = "SELECT `$column`AS `name`, `params` $jfTranslate FROM `#__menu` WHERE `link` = '$string' AND `published` > 0";
        }
        else {
            // Search for direct link to component only
            $sql = "SELECT `$column` AS `name`, `params` $jfTranslate FROM `#__menu` WHERE `link` = 'index.php?option=$option' AND `published` > 0";
        }

        $db->setQuery($sql);
        $row = $db->loadObject();

        if (!empty($row)) {
            if (!empty($row->name) && !$item->title) $item->title = $row->name;
            $item->params = new JParameter($row->params);
        }
        else {
            $item->title = str_replace('com_', '', $option);

            if (!isset($string) && !isset($id)) {
                // Try to extend the search for any link to component
                $sql = "SELECT `$column`, `params` AS `name`$jfTranslate FROM `#__menu` WHERE `link` LIKE 'index.php?option=$option%' AND `published` > 0";
                $db->setQuery($sql);
                $row = $db->loadObject();
                if (!empty($row)) {
                    if (!empty($row->name) && !$item->title) $item->title = $row->name;
                    $item->params = new JParameter($row->params);
                }
            }
        }

        return $item;
    }

    function _getCustomMenuTitle($option)
    {
        $db =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();
        $lang = SEFTools::getLangLongCode();

        static $titles;

        $jfTranslate = $sefConfig->translateNames ? ', `id`' : '';

        if( !isset($titles) ) {
            $titles = array();
        }

        if( !isset($titles[$lang]) ) {
            $db->setQuery("SELECT `file`, `title`$jfTranslate FROM `#__sefexts`");
            $titles[$lang] = $db->loadObjectList('file');
        }

        $file = $option.'.xml';
        if (isset($titles[$lang][$file]->title)) {
            return $titles[$lang][$file]->title;
        } else {
            return null;
        }
    }

    /**
     * Convert title to URL name.
     *
     * @param  string $title
     * @return string
     */
    function _titleToLocation(&$title)
    {
        $sefConfig =& SEFConfig::getConfig();

        // remove accented characters
        // $title = strtr($title,
        // replace non-ASCII characters.
        $title = strtr($title, $sefConfig->getReplacements());

        // remove quotes, spaces, and other illegal characters
        if( $sefConfig->allowUTF ) {
            $title = preg_replace(array('/\'/', '/[\s"\?\:\/\\\\]/', '/(^_|_$)/'), array('', $sefConfig->replacement, ''), $title);
        }
        else {
            $title = preg_replace(array('/\'/', '/[^a-zA-Z0-9\-!.,+]+/', '/(^_|_$)/'), array('', $sefConfig->replacement, ''), $title);
        }

        // Handling lower case
        if( $sefConfig->lowerCase ) {
            $title = JoomSEF::_toLowerCase($title);
        }

        return $title;
    }

    /**
     * Tries to correctly handle conversion to lowercase even for UTF-8 string
     *
     * @param unknown_type $str
     */
    function _toLowerCase($str)
    {
        $sefConfig =& SEFConfig::getConfig();

        if( $sefConfig->allowUTF ) {
            if( function_exists('mb_convert_case') ) {
                $str = mb_convert_case($str, MB_CASE_LOWER, 'UTF-8');
            }
        }
        else {
            $str = strtolower($str);
        }

        return $str;
    }
    
    function _utf8LowerCase($str)
    {
        if( function_exists('mb_convert_case') ) {
            $str = mb_convert_case($str, MB_CASE_LOWER, 'UTF-8');
        }
        else {
            $str = strtolower($str);
        }
        
        return $str;
    }
    
    /**
     * Find existing or create new SEO URL.
     *
     * @param JURI $uri
     * @param array $title
     * @param string $task
     * @param int $limit
     * @param int $limitstart
     * @param string $lang
     * @param array $nonSefVars
     * @param array $ignoreSefVars
     * @param array $metadata List of metadata to be stored. (metakeywords, metadesc, ..., canonicallink)
     * @param boolean $priority
     * @param boolean $pageHandled Set to true if the extension handles its pagination on its own
     * @return string
     */
    function _sefGetLocation(&$uri, &$title, $task = null, $limit = null, $limitstart = null, $lang = null, $nonSefVars = null, $ignoreSefVars = null, $metadata = null, $priority = null, $pageHandled = false)
    {
        global $mainframe;

        $db =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();
        $cache =& SEFCache::getInstance();

        // Get the default priority if not set
        if( is_null($priority) ) {
            $priority = JoomSEF::_getPriorityDefault($uri);
        }

        // Get the parameters for this component
        if( !is_null($uri->getVar('option')) ) {
            $params =& SEFTools::getExtParams($uri->getVar('option'));
        }
        
        // remove the menu title if set to for this component
        if( isset($params) && ($params->get('showMenuTitle', '1') == '0') ) {
            if ((count($title) > 1) &&
            ((count($title) != 2) || ($title[1] != '/')) &&
            ($title[0] == JoomSEF::_getMenuTitle(@$uri->getVar('option'), @$uri->getVar('task'), @$uri->getVar('Itemid')))) {
                array_shift($title);
            }
        }

        // add the page number if the extension does not handle it
        if( !$pageHandled && !is_null($uri->getVar('limitstart')) ) {
            $limit = $uri->getVar('limit');
            if( is_null($limit) ) {
                if( !is_null($uri->getVar('option')) ) {
                    $limit = intval($params->get('pageLimit', ''));
                    if( $limit == 0 ) {
                        $limit = 5;
                    }
                }
                else {
                    $limit = 5;
                }
            }
            $pageNum = intval($uri->getVar('limitstart') / $limit) + 1;
            $pagetext = strval($pageNum);
            if (($cnfPageText = $sefConfig->getPageText())) {
                $pagetext = str_replace('%s', $pageNum, $cnfPageText);
            }
            $title[] = $pagetext;
        }

        // get all the titles ready for urls.
        $location = array();
        foreach ($title as $titlePart) {
            if (strlen($titlePart) == 0) continue;
            $location[] = JoomSEF::_titleToLocation($titlePart);
        }

        // remove unwanted characters.
        $finalstrip = explode('|', $sefConfig->stripthese);
        $takethese = str_replace('|', '', $sefConfig->friendlytrim);
        if (strstr($takethese, $sefConfig->replacement) === FALSE) {
            $takethese .= $sefConfig->replacement;
        }

        $imptrim = implode('/', $location);

        if (!is_null($task)) {
            $task = str_replace($sefConfig->replacement.'-'.$sefConfig->replacement, $sefConfig->replacement, $task);
            $task = str_replace($finalstrip, '', $task);
            $task = trim($task,$takethese);
        }

        $imptrim = str_replace($sefConfig->replacement.'-'.$sefConfig->replacement, $sefConfig->replacement, $imptrim);
        $suffixthere = 0;
        $regexSuffix = str_replace('.', '\.', $sefConfig->suffix);
        $pregSuffix = addcslashes($regexSuffix, '/');
        //if (eregi($regexSuffix.'$', $imptrim)) {
        if (preg_match('/'.$pregSuffix.'$/i', $imptrim)) {
            $suffixthere = strlen($sefConfig->suffix);
        }

        $imptrim = str_replace($finalstrip, $sefConfig->replacement, substr($imptrim, 0, strlen($imptrim) - $suffixthere));
        $imptrim = str_replace($sefConfig->replacement.$sefConfig->replacement, $sefConfig->replacement, $imptrim);

        $suffixthere = 0;
        //if (eregi($regexSuffix.'$', $imptrim)) {
        if (preg_match('/'.$pregSuffix.'$/i', $imptrim)) {
            $suffixthere = strlen($sefConfig->suffix);
        }

        $imptrim = trim(substr($imptrim, 0, strlen($imptrim) - $suffixthere), $takethese);

        // add the task if set
        $imptrim .= (!is_null($task) ? '/'.$task.$sefConfig->suffix : '');

        // remove all the -/
        $imptrim = SEFTools::ReplaceAll($sefConfig->replacement.'/', '/', $imptrim);

        // remove all the /-
        $imptrim = SEFTools::ReplaceAll('/'.$sefConfig->replacement, '/', $imptrim);

        // Remove all the //
        $location = SEFTools::ReplaceAll('//', '/', $imptrim);

        // check if the location isn't too long for database storage and truncate it in that case
        $suffixthere = 0;
        //if (eregi($regexSuffix.'$', $location)) {
        if (preg_match('/'.$pregSuffix.'$/i', $location)) {
            $suffixthere = strlen($sefConfig->suffix);
        }
        $suffixLen = strlen($sefConfig->suffix);
        $maxlen = 240 + $suffixthere - $suffixLen;  // Leave some space for language and numbers
        if (strlen($location) > $maxlen) {
            // Temporarily remove the suffix
            //$location = ereg_replace($regexSuffix.'$', '', $location);
            $location = preg_replace('/'.$pregSuffix.'$/', '', $location);
            
            // Explode the location to parts
            $parts = explode('/', $location);
            do {
                // Find the key of the longest part
                $key = 0;
                $len = strlen($parts[0]);
                for( $i = 1, $n = count($parts); $i < $n; $i++ ) {
                    $tmpLen = strlen($parts[$i]);
                    if( $tmpLen > $len ) {
                        $key = $i;
                        $len = $tmpLen;
                    }
                }
                
                // Truncate the longest part
                $truncBy = strlen($location) - $maxlen;
                if( $truncBy > 10 ) {
                    $truncBy = 10;
                }
                $parts[$key] = substr($parts[$key], 0, -$truncBy);
                
                // Implode to location again
                $location = implode('/', $parts);
                
                // Add suffix if was there
                if( $suffixthere > 0 ) {
                    $location .= $sefConfig->suffix;
                }
            } while(strlen($location) > $maxlen);
        }

        // remove variables we don't want to be included in non-SEF URL
        // and build the non-SEF part of our SEF URL
        $nonSefUrl = '';

        // load the nonSEF vars from option parameters
        $paramNonSef = array();
        if( isset($params) ) {
            $nsef = $params->get('customNonSef', '');

            if( !empty($nsef) ) {
                // Some variables are set, let's explode them
                $paramNonSef = explode(';', $nsef);
            }
        }

        // get globally configured nonSEF vars
        $configNonSef = array();
        if( !empty($sefConfig->customNonSef) ) {
            $configNonSef = explode(';', $sefConfig->customNonSef);
        }
        
        // Get nonSEF vars from variable filter test if set to
        $failedVars = array();
        if( isset($params) ) {
            if( $params->get('varFilterFail', '0') == '2' ) {
                JoomSEF::_varFilterTest($uri, $failedVars);
            }
        }

        // combine all the nonSEF vars arrays
        $nsefvars = array_merge($paramNonSef, $configNonSef, $failedVars);
        if (!empty($nsefvars)) {
            foreach($nsefvars as $nsefvar) {
                // add each variable, that isn't already set, and that is present in our URL
                if( !isset($nonSefVars[$nsefvar]) && !is_null($uri->getVar($nsefvar)) ) {
                    $nonSefVars[$nsefvar] = $uri->getVar($nsefvar);
                }
            }
        }

        // nonSefVars - variables to exclude only if set to in configuration
        if ($sefConfig->appendNonSef && isset($nonSefVars)) {
            $vars = array_keys($nonSefVars);
            $q = SEFTools::RemoveVariables($uri, $vars);
            if ($q != '') {
                if ($nonSefUrl == '') {
                    $nonSefUrl = '?'.$q;
                }
                else {
                    $nonSefUrl .= '&amp;'.$q;
                }
            }
            // if $nonSefVars mixes with $GLOBALS['JOOMSEF_NONSEFVARS'], exclude the mixed vars
            // this is important to prevent duplicating params by adding JOOMSEF_NONSEFVARS to
            // $ignoreSefVars
            $gNonSef = $mainframe->get('sef.global.nonsefvars');
            if (!empty($gNonSef)) {
                foreach (array_keys($gNonSef) as $key) {
                    if (in_array($key, array_keys($nonSefVars))) unset($gNonSef[$key]);
                }
                $mainframe->set('sef.global.nonsefvars', $gNonSef);
            }
        }

        // if there are global variables to exclude, add them to ignoreSefVars array
        $gNonSef = $mainframe->get('sef.global.nonsefvars');
        if (!empty($gNonSef)) {
            if (!empty($ignoreSefVars)) {
                $ignoreSefVars = array_merge($gNonSef, $ignoreSefVars);
            } else {
                $ignoreSefVars = $gNonSef;
            }
        }

        // ignoreSefVars - variables to exclude allways
        if (isset($ignoreSefVars)) {
            $vars = array_keys($ignoreSefVars);
            $q = SEFTools::RemoveVariables($uri, $vars);
            if ($q != '') {
                if ($nonSefUrl == '') {
                    $nonSefUrl = '?'.$q;
                }
                else {
                    $nonSefUrl .= '&amp;'.$q;
                }
            }
        }
        
        // If the component requests strict accept variables filtering, remove the ones that don't match
        if( isset($params) && ($params->get('acceptStrict', '0') == '1') ) {
            $acceptVars =& SEFTools::getExtAcceptVars($uri->getVar('option'));
            $uriVars = $uri->getQuery(true);
            if( (count($acceptVars) > 0) && (count($uriVars) > 0) ) {
                foreach($uriVars as $name => $value) {
                    // Standard Joomla variables
                    if( in_array($name, array('option', 'Itemid', 'limit', 'limitstart', 'format', 'tmpl', 'lang')) ) {
                        continue;
                    }
                    // Accepted variables
                    if( in_array($name, $acceptVars) ) {
                        continue;
                    }
                    
                    // Variable not accepted, add it to non-SEF part of the URL
                    $value = urlencode($value);
                    if (strlen($nonSefUrl) > 0) {
                        $nonSefUrl .= '&amp;'.$name.'='.$value;
                    } else {
                        $nonSefUrl = '?'.$name.'='.$value;
                    }
                    $uri->delVar($name);
                }
            }
        }

        // always remove Itemid and store it in a separate column
        if (!is_null($uri->getVar('Itemid'))) {
            $Itemid = $uri->getVar('Itemid');
            $uri->delVar('Itemid');
        }

        // check for non-sef url first and avoid repeative lookups
        // we only want to look for title variations when adding new
        // this should also help eliminate duplicates.

        // David (284): ignore Itemid if set to
        if( isset($params) ) {
            $extIgnore = $params->get('ignoreSource', 2);
        } else {
            $extIgnore = 2;
        }
        $ignoreSource = ($extIgnore == 2 ? $sefConfig->ignoreSource : $extIgnore);

        /*$where = '';
        if (!$ignoreSource && isset($Itemid)) {
            $where .= " AND `Itemid` = '".$Itemid."'";
        }*/
        $url = JoomSEF::_uriToUrl($uri);

        // if cache is activated, search in cache first
        if ($sefConfig->useCache) {
            $realloc = $cache->GetSefUrl($url, @$Itemid);
        }
        // search if URL exists, if we do not use cache or URL was not cached
        if (!$sefConfig->useCache || !$realloc) {
            $query = "SELECT `sefurl`, `Itemid` FROM `#__sefurls` WHERE `origurl` = '" . addslashes(html_entity_decode(urldecode($url))) . "'" /*. $where*/;
            $db->setQuery($query);
            $sefurls = $db->loadAssocList('Itemid');
            // test if current Itemid record exists, if YES, use it, if NO, use first found
            $curId = isset($Itemid) ? $Itemid : '';
            $active = isset($sefurls[$curId]) ? $sefurls[$curId] : reset($sefurls);
            $realloc = $active['sefurl'];
        }
        // if not found, try to find the url without lang variable
        if (!$realloc && ($sefConfig->langPlacement == _COM_SEF_LANG_DOMAIN)) {
            $url = JoomSEF::_uriToUrl($uri, 'lang');

            if ($sefConfig->useCache) {
                $realloc = $cache->GetSefUrl($url, @$Itemid);
            }
            if (!$sefConfig->useCache || !$realloc) {
                $query = "SELECT `sefurl`, `Itemid` FROM `#__sefurls` WHERE `origurl` = '".addslashes(html_entity_decode(urldecode($url)))."'" /*. $where*/;
                $db->setQuery($query);
                $sefurls = $db->loadAssocList('Itemid');
           		// test if current Itemid record exists, if YES, use it, if NO, use first found
           		$curId = isset($Itemid) ? $Itemid : '';
            	$active = isset($sefurls[$curId]) ? $sefurls[$curId] : reset($sefurls);
            	$realloc = $active['sefurl'];
            }
        }

        // found a match, so we are done
        if (!is_null($realloc)) {
            // return found URL with non-SEF part appended
            if (($nonSefUrl != '') && (strstr($realloc, '?'))) {
                $nonSefUrl = str_replace('?', '&amp;', $nonSefUrl);
            }

            $url = JURI::root();

            if (substr($url, -1) != '/') $url .= '/';
            $url .= $realloc.$nonSefUrl;
            $fragment = $uri->getFragment();
            if (!empty($fragment)) $url .= '#'.$fragment;

            return new JURI($url);
        }
        // URL not found, so lets create it
        else {
            // return the original URL if we don't want to save new URLs
            if ($sefConfig->disableNewSEF) return $uri;

            $realloc = null;

            $suffixMust = false;
            // add lang to suffix, if set to
            if (SEFTools::JoomFishInstalled() && isset($lang) && $sefConfig->langPlacement == _COM_SEF_LANG_SUFFIX) {
                if (($sefConfig->mainLanguage == '0') || ($lang != $sefConfig->mainLanguage)) {
                    $suffix = '_'.$lang.$sefConfig->suffix;
                    $suffixMust = true;
                }
            }
            if (!isset($suffix)) {
                $suffix = $sefConfig->suffix;
            }
            $addFile = $sefConfig->addFile;
            if (($pos = strrpos($addFile, '.')) !== false) {
                $addFile = substr($addFile, 0, $pos);
            }

            // in case the created SEF URL is already in database for different non-SEF URL,
            // we need to distinguish them by using numbers, so let's find the first unused URL

            $leftPart = '';   // string to be searched before page number
            $rightPart = '';  // string to be searched after page number
            if (substr($location, -1) == '/' || strlen($location) == 0) {
                if (($pagetext = $sefConfig->getPageText())) {
                    // use global limit if NULL and set in globals
                    if (is_null($limit) && isset($_REQUEST['limit']) && $_REQUEST['limit'] > 0) $limit = $_REQUEST['limit'];
                    // if we are using pagination, try to calculate page number
                    if (!is_null($limitstart) && $limitstart > 0) {
                        // make sure limit is not 0
                        if ($limit == 0) {
                            $config =& JFactory::getConfig();
                            $listLimit = $config->getValue('list_limit');
                            $limit = ($listLimit > 0) ? $listLimit : 20;
                        }
                        $pagenum = $limitstart / $limit;
                        $pagenum++;
                    }
                    else $pagenum = 1;

                    if (strpos($pagetext, '%s') !== false) {
                        $page = str_replace('%s', $pagenum == 1 ? $addFile : $pagenum, $pagetext) . $suffix;

                        $pages = explode('%s', $pagetext);
                        $leftPart = $location . $pages[0];
                        $rightPart = $pages[1] . $suffix;
                    }
                    else {
                        $page = $pagetext.($pagenum == 1 ? $addFile : $sefConfig->pagerep . $pagenum) . $suffix;

                        $leftPart = $location . $pagetext . $sefConfig->pagerep;
                        $rightPart = $suffix;
                    }

                    $temploc = $location . ($pagenum == 1 && !$suffixMust ? '' : $page);
                }
                else {
                    $temploc = $location . ($suffixMust ? $sefConfig->pagerep.$suffix : '');

                    $leftPart = $location . $sefConfig->pagerep;
                    $rightPart = $suffix;
                }
            }
            elseif ($suffix) {
                if ($sefConfig->suffix != '/') {
                    //if (eregi($regexSuffix, $location)) {
                    if (preg_match('/'.$pregSuffix.'/i', $location)) {
                        $temploc = preg_replace('/' . $pregSuffix . '/', '', $location) . $suffix;

                        $leftPart = preg_replace('/' . $pregSuffix . '/', '', $location) . $sefConfig->pagerep;
                        $rightPart = $suffix;
                    }
                    else {
                        $temploc = $location . $suffix;

                        $leftPart = $location . $sefConfig->pagerep;
                        $rightPart = $suffix;
                    }
                }
                else {
                    $temploc = $location . $suffix;

                    $leftPart = $location . $sefConfig->pagerep;
                    $rightPart = $suffix;
                }
            }
            else {
                $temploc = $location . ($suffixMust ? $sefConfig->pagerep . $suffix : '');

                $leftPart = $location . $sefConfig->pagerep;
                $rightPart = $suffix;
            }

            // add language to path if set to
            if (SEFTools::JoomFishInstalled() && isset($lang) && $sefConfig->langPlacement == _COM_SEF_LANG_PATH) {
                if (($sefConfig->mainLanguage == '0') || ($lang != $sefConfig->mainLanguage)) {
                    $slash = ($temploc != '' && $temploc[0] == '/');
                    $temploc = $lang . ($slash || strlen($temploc) > 0  ? '/' : '') . $temploc;

                    $leftPart = $lang . '/' . $leftPart;
                }
            }

            if ($sefConfig->addFile) {
                //if (!eregi($regexSuffix . '$', $temploc) && substr($temploc, -1) == '/') {
                if (!preg_match('/'.$pregSuffix . '$/i', $temploc) && substr($temploc, -1) == '/') {
                    $temploc .= $sefConfig->addFile;
                }
            }

            // convert to lowercase if set to
            if ($sefConfig->lowerCase) {
                $temploc = JoomSEF::_toLowerCase($temploc);
                $leftPart = JoomSEF::_toLowerCase($leftPart);
                $rightPart = JoomSEF::_toLowerCase($rightPart);
            }

            $url = JoomSEF::_uriToUrl($uri);

            // see if we have a result for this location
            $sql = "SELECT `id`, `origurl`, `Itemid`, `sefurl` FROM `#__sefurls` WHERE `sefurl` = '$temploc' AND `origurl` != ''";
            $db->setQuery($sql);
            $row = $db->loadObject();

            $realloc = JoomSEF::_checkRow($row, $ignoreSource, @$Itemid, $url, $metadata, $temploc, $priority, $uri->getVar('option'));
            
            // the correct URL could not be used, we must find the first free number
            if( is_null($realloc) ) {
                // let's get all the numbered pages
                $sql = "SELECT `id`, `origurl`, `Itemid`, `sefurl` FROM `#__sefurls` WHERE `sefurl` LIKE '{$leftPart}%{$rightPart}'";
                $db->setQuery($sql);
                $pages = $db->loadObjectList();

                // create associative array of form number => URL info
                $urls = array();
                if (!empty($pages)) {
                    $leftLen = strlen($leftPart);
                    $rightLen = strlen($rightPart);

                    foreach ($pages as $page) {
                        $sefurl = $page->sefurl;

                        // separate URL number
                        $urlnum = substr($sefurl, $leftLen, strlen($sefurl) - $leftLen - $rightLen);

                        // use only if it's really numeric
                        if (is_numeric($urlnum)) {
                            $urls[intval($urlnum)] = $page;
                        }
                    }
                }

                $i = 2;
                do {
                    $temploc = $leftPart . $i . $rightPart;
                    $row = null;
                    if (isset($urls[$i])) {
                        $row = $urls[$i];
                    }

                    $realloc = JoomSEF::_checkRow($row, $ignoreSource, @$Itemid, $url, $metadata, $temploc, $priority, $uri->getVar('option'));
                    
                    $i++;
                } while( is_null($realloc) );
            }
        }

        // return found URL with non-SEF part appended
        if (($nonSefUrl != '') && (strstr($realloc, '?'))) {
            $nonSefUrl = str_replace('?', '&amp;', $nonSefUrl);
        }

        $url = JURI::root();

        if (substr($url, -1) != '/') $url .= '/';
        $url .= $realloc.$nonSefUrl;
        $fragment = $uri->getFragment();
        if (!empty($fragment)) {
            $url .= '#'.$fragment;
        }

        return new JURI($url);
    }

    function enabled(&$plugin)
    {
        global $mainframe;
       
        $big = $mainframe->get('sef.global.meta', '');

        $cosi = 'file';
        $cosi = implode($cosi(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_sef'.DS.'sef.xml'));
        $cosi = md5($cosi);

        if ($big == $cosi) return true;
        else $plugin = $plugin;

        $cosi = 'getDo'.'cument';
        $doc =& JFactory::$cosi();
        $cache = 'getB'.'uffer';
        $cacheBuf =& $doc->$cache('component');

        $cacheBuf2 = 
        'PGRpdj48YSBocmVmPSJodHRwOi8vd3'.
        'd3LmFydGlvLm5ldCIgc3R5bGU9ImZv'.
        'bnQtc2l6ZTogOHB4OyB2aXNpYmlsaX'.
        'R5OiB2aXNpYmxlOyBkaXNwbGF5OiBp'.
        'bmxpbmU7IiB0aXRsZT0iV2ViIGRldm'.
        'Vsb3BtZW50LCBKb29tbGEsIENNUywg'.
        'Q1JNLCBPbmxpbmUgc2hvcCBzb2Z0d2'.
        'FyZSwgZGF0YWJhc2VzIj5Kb29tbGEg'.
        'U0VGIFVSTHMgYnkgQXJ0aW88L2E+PC'.
        '9kaXY+';

        $cache = 'setB'.'uffer';
        $cosi = 'getC'.'md';
//        if (JRequest::$cosi('fo'.'rmat') != 'r'.'aw')
//        $doc->$cache($cacheBuf . base64_decode($cacheBuf2), 'component');

        return true;
    }
    
    /**
     * Checks the found row
     *
     */
    function _checkRow(&$row, $ignoreSource, $Itemid, $url, &$metadata, $temploc, $priority, $option)
    {
        $realloc = null;

        $db =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();

        $numberDuplicates = $sefConfig->numberDuplicates;
        
        if( !empty($option) ) {
            $params =& SEFTools::getExtParams($option);
            $extDuplicates = $params->get('numberDuplicates', '2');
            if( $extDuplicates != '2' ) {
                $numberDuplicates = $extDuplicates;
            }
        }
        
        if( ($row != false) && !is_null($row) ) {
            if ($ignoreSource || (!$ignoreSource && (empty($Itemid) || $row->Itemid == $Itemid))) {
                // ... check that it matches original URL
                if ($row->origurl == $url) {
                    // found the matching object
                    // it probably should have been found sooner
                    // but is checked again here just for CYA purposes
                    // and to end the loop
                    $realloc = $row->sefurl;
                }
                else if ($sefConfig->langPlacement == _COM_SEF_LANG_DOMAIN) {
                    // check if the urls differ only by lang variable
                    if (SEFTools::removeVariable($row->origurl, 'lang') == SEFTools::removeVariable($url, 'lang')) {
                        $db->setQuery("UPDATE `#__sefurls` SET `origurl` = '".SEFTools::removeVariable($row->origurl, 'lang')."' WHERE `id` = '".$row->id."' LIMIT 1");

                        // if error occured.
                        if (!$db->query()) {
                            JError::raiseError('JoomSEF Error', JText::_('Could not update SEF URL in database: ') . $db->getErrorMsg());
                        }

                        $realloc = $row->sefurl;
                    }
                }
            }

            // The found URL is not the same
            if( !$numberDuplicates ) {
                // But duplicates management is turned on
                // so we can save the same SEF URL for different non-SEF URL
                JoomSEF::_saveNewURL($Itemid, $metadata, $priority, $temploc, $url);
                $realloc = $temploc;
            }
        }
        // URL not found
        else {
            // first, try to search among 404s
            $query = "SELECT `id` FROM `#__sefurls` WHERE `sefurl` = '$temploc' AND `origurl` = ''";
            $db->setQuery($query);
            $id = $db->loadResult();

            // if 404 exists, rewrite it to the new URL
            if (!is_null($id)) {
                $sqlId = (!empty($Itemid) ? ", `Itemid` = '$Itemid'" : '');
                $query = "UPDATE `#__sefurls` SET `origurl` = '" . mysql_escape_string(html_entity_decode(urldecode($url)))."'$sqlId, `priority` = '$priority' WHERE `id` = '$id' LIMIT 1";
                $db->setQuery($query);

                // if error occured
                if (!$db->query()) {
                    JError::raiseError('JoomSEF Error', JText::_('Could not update SEF URL in database: ') . $db->getErrorMsg());
                }
            }
            // else save URL in the database as new record
            else {
                JoomSEF::_saveNewURL($Itemid, $metadata, $priority, $temploc, $url);
            }
            $realloc = $temploc;
        }

        return $realloc;
    }

    /**
     * Inserts new SEF URL to database
     *
     */
    function _saveNewURL($Itemid, &$metadata, $priority, $temploc, $url)
    {
        $db =& JFactory::getDBO();
        
        $col = $val = '';
        if( !empty($Itemid) ) {
            $col = ', `Itemid`';
            $val = ", '$Itemid'";
        }

        $metakeys = $metavals = '';
        if (is_array($metadata) && count($metadata) > 0) {
            foreach($metadata as $metakey => $metaval) {
                $metakeys .= ", `$metakey`";
                $metavals .= ", '".str_replace(array("\\", "'", ';'), array("\\\\", "\\'", "\\;"), $metaval)."'";
            }
        }
        
        // get trace information if set to
        $sefConfig =& SEFConfig::getConfig();        
        if (@$sefConfig->trace) {
        	$traceinfo = "'" . mysql_escape_string(JoomSEF::_getDebugInfo($sefConfig->traceLevel)) . "'";
        }
        else $traceinfo = "NULL";        

        $query = 'INSERT INTO `#__sefurls` (`sefurl`, `origurl`, `priority`' . $col . $metakeys . ', `trace`) ' .
        "VALUES ('".$temploc."', '" . mysql_escape_string(html_entity_decode(urldecode($url)))."', '$priority'" . $val . $metavals . ", " . $traceinfo . ")";
        $db->setQuery($query);

        // if error occured
        if (!$db->query()) {
            JError::raiseError('JoomSEF Error', JText::_('Could not save the SEF URL to database: ') . $db->getErrorMsg());
        }
    }
    
    function _uriToUrl($uri, $removeVariables = null)
    {
        // Create new JURI object
        $url = new JURI($uri->toString());

        // Remove variables if needed
        if (!empty($removeVariables)) {
            if (is_array($removeVariables)) {
                foreach ($removeVariables as $var) {
                    $url->delVar($var);
                }
            } else {
                $url->delVar($removeVariables);
            }
        }

        // sort variables
        ksort($url->_vars);
        $opt = $url->getVar('option');
        if( !is_null($opt) ) {
            $url->delVar('option');
            array_unshift($url->_vars, array('option' => $opt));
        }
        $url->_query = null;

        // Create string for db
        return $url->toString(array('path', 'query'));
    }

    /**
     * Get SEF titles of content items.
     *
     * @param  string $task
     * @param  int $id
     * @return string
     */
    function _getContentTitles($task, $id)
    {
        $database =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();

        $title = array();
        // JF translate extension.
        $jfTranslate = $sefConfig->translateNames ? ', `id`' : '';
        $title_field = 'title';
        if ($sefConfig->useAlias)  $title_field = 'alias';

        switch ($task) {
            case 'section':
            case 'blogsection': {
                if (isset($id)) {
                    $sql = "SELECT `$title_field` AS `section`$jfTranslate FROM `#__sections` WHERE `id` = '$id'";
                }
                break;
            }
            case 'category':
            case 'blogcategory':
                if (isset($id)) {
                    if ($sefConfig->showSection || !$sefConfig->showCat) {
                        $sql = 'SELECT s.'.$title_field.' AS section'.($jfTranslate ? ', s.id AS section_id' : '')
                        .($sefConfig->showCat ? ', c.'.$title_field.' AS category'.($jfTranslate ? ', c.id' : '') : '')
                        .' FROM #__categories as c '
                        .'LEFT JOIN #__sections AS s ON c.section = s.id '
                        .'WHERE c.id = '.$id;
                    }
                    else $sql = "SELECT `$title_field` AS `category`$jfTranslate FROM #__categories WHERE `id` = $id";
                }
                break;
            case 'article':
                if (isset($id)) {
                    if ($sefConfig->useAlias) {
                        // verify title alias is not empty
                        $database->setQuery("SELECT alias$jfTranslate FROM #__content WHERE id = $id");
                        $title_field = $database->loadResult() ? 'alias' : 'title';
                    }
                    else $title_field = 'title';
                    if ($sefConfig->showSection || !$sefConfig->showCat) {
                        $sql = 'SELECT '.($sefConfig->showSection ? 's.'.$title_field.' AS section'.($jfTranslate ? ', s.id AS section_id' : '').', ' : '').
                        ($sefConfig->showCat ? 'c.'.$title_field.' AS category'.($jfTranslate ? ', c.id AS category_id' : '').', ' : '').
                        'a.'.$title_field.' AS title'.($jfTranslate ? ', a.id' : '').' FROM #__content as a'.
                        ' LEFT JOIN #__sections AS s ON a.sectionid = s.id '.
                        ($sefConfig->showCat ? ' LEFT JOIN #__categories AS c ON a.catid = c.id ' : '').
                        ' WHERE a.id = '.$id;
                    }
                    else {
                        $sql = 'SELECT '.($sefConfig->showCat ? 'c.'.$title_field.' AS category'.($jfTranslate ? ', c.id AS category_id' : '').', ' : '')
                        .'a.'.$title_field.' AS title'.($jfTranslate ? ', a.id' : '').' FROM #__content as a'.
                        ($sefConfig->showCat ? ' LEFT JOIN #__categories AS c ON a.catid = c.id ' : '').
                        ' WHERE a.id = '.$id;
                    }
                }
                break;
            default:
                $sql = '';
        }

        if ($sql) {
            $database->setQuery($sql);
            $row = $database->loadObject();

            if (isset($row->section)) {
                $title[] = $row->section;
                if ($sefConfig->contentUseIndex && ($task == 'section')) {
                    $title[] = '/';
                }
            }
            if (isset($row->category)) {
                $title[] = $row->category;
                if ($sefConfig->contentUseIndex && ($task == 'category')) {
                    $title[] = '/';
                }
            }
            if (isset($row->title)) $title[] = $row->title;
        }
        return $title;
    }

    /**
     * Returns the Joomla category for given id
     *
     * @param int $catid
     * @return string
     */
    function _getCategories($catid, $useAlias = false)
    {
        $sefConfig =& SEFConfig::getConfig();
        $database =& JFactory::getDBO();

        $jfTranslate = $sefConfig->translateNames ? ', `id`' : '';

        $cat_table = "#__categories";
        $field = 'title';
        if( $useAlias ) {
            $field = 'alias';
        }

        // Let's find the Joomla category name for given category ID
        $title = '';
        if (isset($catid) && $catid != 0){
            $query = "SELECT `$field` AS `title` $jfTranslate FROM `$cat_table` WHERE `id` = '$catid'";
            $database->setQuery($query);
            $rows = $database->loadObjectList();

            if ($database->getErrorNum()) die($database->stderr());
            elseif (@count($rows) > 0 && !empty($rows[0]->title)) $title = $rows[0]->title;
        }
        return $title;
    }

    /**
     * Returns the default priority value for the url
     *
     * @param JURI $uri
     * @return int
     */
    function _getPriorityDefault(&$uri)
    {
        $itemid = $uri->getVar('Itemid');

        if( is_null($itemid) ) {
            return _COM_SEF_PRIORITY_DEFAULT;
        }
        else {
            return _COM_SEF_PRIORITY_DEFAULT_ITEMID;
        }
    }

    function _getDebugInfo($traceLevel = 3)
    {
        $trace = debug_backtrace();
        $debuginfo = ''; $tr = 0;
        foreach ($trace as $row) {
        	if (@$row['class'] == 'JRouterJoomsef' && @$row['function'] == 'build') {
        		// this starts tracing for next 3 rounds        		
       			$tr = 1;
       			continue; 
        	}        	
        	elseif ($tr == 0) continue;
        	        	
        	$file = isset($row['file']) ? str_replace(JPATH_BASE, '', $row['file']) : 'n/a';
        	$args = array();
        	foreach ($row['args'] as $arg) {
        		if (is_object($arg)) $args[] = get_class($arg);
        		elseif (is_array($arg)) $args[] = 'Array';
        		else $args[] = "'" . $arg . "'";
        	}
        	$debuginfo .= '#' . $tr . ': ' . @$row['class'] . @$row['type'] . @$row['function'] . "(" . implode(', ', $args) .  "), " . $file . ' line ' . @$row['line'] . "\n";
        	
        	if ($tr == $traceLevel) break;
        	$tr++;
        }
        
        return $debuginfo;
	}    
    
    /**
     * Test the URL using variable filter.
     * Returns true if URL passes the test, false otherwise.
     *
     * @param JURI $uri
     * @return boolean
     */
    function _varFilterTest(&$uri, &$failedVars)
    {
        $failedVars = array();
        $ret = true;
        
        $option = $uri->getVar('option');
        if( is_null($option) ) {
            // URLs without option set pass the test
            return true;
        }
        
        // Get the filters by variables
        $filtersByVar =& SEFTools::getExtFiltersByVars($option);
        
        if (count($filtersByVar) == 0) {
            return true;
        }
        
        // Loop through variables testing them
        // Variable will pass the test when it matches:
        // (POS1 OR ... OR POSn) AND !(NEG1 OR ... NEGn)
        foreach ($filtersByVar as $var => $filterTypes) {
            $varValue = $uri->getVar($var);
            if( is_null($varValue) ) {
                // Variable is not present in URL
                continue;
            }
            
            // Check the positive filters
            if (isset($filterTypes['pos']) && count($filterTypes['pos']) > 0) {
                // Variable must match at least one positive filter
                $ok = false;
                foreach($filterTypes['pos'] as $filter) {
                    if( preg_match('/'.str_replace('/', '\/', $filter).'/', $varValue) > 0 ) {
                        $ok = true;
                        break;
                    }
                }
                if (!$ok) {
                    // variable didn't pass any of the positive filters
                    if (!is_null($failedVars)) {
                        // If $failedVars is not null, add variable to the array and continue testing
                        $failedVars[] = $var;
                        $ret = false;
                        continue;
                    }
                    else {
                        // Otherwise return from function immediately
                        return false;
                    }
                }
            }
            
            // Check the negative filters
            if (isset($filterTypes['neg']) && count($filterTypes['neg']) > 0)  {
                // Variable must not match any of the negative filters
                foreach($filterTypes['neg'] as $filter) {
                    if( preg_match('/'.str_replace('/', '\/', $filter).'/', $varValue) > 0 ) {
                        if( !is_null($failedVars) ) {
                            // If $failedVars is not null, add variable to the array and continue testing
                            $failedVars[] = $var;
                            $ret = false;
                            break;
                        }
                        else {
                            // Otherwise return from function immediately
                            return false;
                        }
                    }
                }
            }
        }
        
        return $ret;
    }
}
?>
