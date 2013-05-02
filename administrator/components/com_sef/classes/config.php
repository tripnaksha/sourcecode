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
defined('_JEXEC') or die();

define('_COM_SEF_LANG_PATH', 0);
define('_COM_SEF_LANG_SUFFIX', 1);
define('_COM_SEF_LANG_NONE', 2);
define('_COM_SEF_LANG_DOMAIN', 3);

define('_COM_SEF_BASE_IGNORE', 0);
define('_COM_SEF_BASE_HOMEPAGE', 1);
define('_COM_SEF_BASE_CURRENT', 2);
define('_COM_SEF_BASE_NONE', 3);

class SEFConfig
{
    /**
     * Whether to always add language version.
     * @var bool
     */
    var $alwaysUseLang = true;
    /* boolean, is JoomSEF enabled  */
    var $enabled = true;
    /* char,  Character to use for url replacement */
    var $replacement = "-";
    /* char,  Character to use for page spacer */
    var $pagerep = "-";
    /* strip these characters */
    var $stripthese = ",|~|!|@|%|^|*|(|)|+|<|>|:|;|{|}|[|]|---|--|..|.";
    /* string,  suffix for "files" */
    var $suffix = "";
    /* string,  file to display when there is none */
    var $addFile = '';
    /* trims friendly characters from where they shouldn't be */
    var $friendlytrim = "-|.";
    /**
     * generate canonical links
     * @var bool
     */
    var $canonicalLink = true;
    /**
     * page text
	 * @var string
     */
    var $pagetext = "JText::_('PAGE')-%s";
    /**
     * Should lang be part of path or suffix?
     * @var bool
     */
    var $langPlacement = _COM_SEF_LANG_PATH;
    /* boolean, convert url to lowercase */
    var $lowerCase = true;
    /* boolean, use the title_alias instead of the title */
    var $useAlias = false;
    /**
     * should we extract Itemid from URL?
     * @var bool
     */
    var $excludeSource = false;
    /**
     * should we extract Itemid from URL?
     * @var bool
     */
    var $reappendSource = false;
    /**
     * should we ignore multiple Itemids for the same page in database?
     * @var bool
     */
    var $ignoreSource = true;
    /**
     * excludes often changing variables from SEF URL and
     * appends them as non-SEF query 
     * @var bool
     */
    var $appendNonSef = true;
    /**
     * consider both URLs with/without / in  theend valid
     * @var bool
     */
    var $transitSlash = true;
    /**
     * whether to use cache
     * @var bool 
     */
    var $useCache = true;
    /** 
     * maximum count of URLs in cache
     * @var int 
     */
    var $cacheSize = 1000;
    /**
     * minimum hits count that URLs must have to get into cache
     * @var int 
     */
    var $cacheMinHits = 10;
    /**
     * record hits for URLs in cache?
     * @var unknown_type
     */
    var $cacheRecordHits = false;
    /**
     * translate titles in URLs using JoomFish
     * @var bool 
     */
    var $translateNames = true;
    /** int, id of #__content item to use for static page */
    var $page404 = 0;
    /**
     * record 404 pages?
     * @var bool
     */
    var $record404 = false;
    /**
     * if set to yes, the standard Joomla message will be also shown when 404
     * @var boolean
     */
    var $showMessageOn404 = false;
    /**
     * whether to set the ItemID variable when Default 404 Page is displayed
     * @var boolean */
    var $use404itemid = false;
    /**
     * ItemID used for the Default 404 page
     * @var int
     */
    var $itemid404 = 0;
    /**
     * Redirect nonSEF URLs to their SEF equivalents with 301 header?
     * @var bool
     */
    var $nonSefRedirect = false;
    /**
     * Use Moved Permanently redirection table?
     * @var bool
     */
    var $useMoved = true;
    /**
     * Use Moved Permanently redirection table?
     * @var bool
     */
    var $useMovedAsk = true;
    /** 
     * Definitions of replacement characters.
     * @var string
     */
    var $replacements = "Á|A, Â|A, Å|A, Ă|A, Ä|A, À|A, Ć|C, Ç|C, Č|C, Ď|D, É|E, È|E, Ë|E, Ě|E, Ì|I, Í|I, Î|I, Ï|I, Ĺ|L, Ń|N, Ň|N, Ñ|N, Ò|O, Ó|O, Ô|O, Õ|O, Ö|O, Ŕ|R, Ř|R, Š|S, Ś|O, Ť|T, Ů|U, Ú|U, Ű|U, Ü|U, Ý|Y, Ž|Z, Ź|Z, á|a, â|a, å|a, ä|a, à|a, ć|c, ç|c, č|c, ď|d, đ|d, é|e, ę|e, ë|e, ě|e, è|e, ì|i, í|i, î|i, ï|i, ĺ|l, ń|n, ň|n, ñ|n, ò|o, ó|o, ô|o, ő|o, ö|o, š|s, ś|s, ř|r, ŕ|r, ť|t, ů|u, ú|u, ű|u, ü|u, ý|y, ž|z, ź|z, ˙|-, ß|ss, Ą|A, µ|u, Ą|A, µ|u, ą|a, Ą|A, ę|e, Ę|E, ś|s, Ś|S, ż|z, Ż|Z, ź|z, Ź|Z, ć|c, Ć|C, ł|l, Ł|L, ó|o, Ó|O, ń|n, Ń|N";
    /* Array, contains predefined components. */
    var $predefined = array('0' => "com_login",'1' => "com_newsfeeds",'2' => "com_sef",'3' => "com_weblinks",'4' => "com_joomfish");
    /* String, contains URL to upgrade package located on server */
    var $serverUpgradeURL = "http://www.artio.cz/updates/joomsef3/upgrade.zip";
    /* String, contains URL to new version file located on server */
    var $serverNewVersionURL = "http://www.artio.cz/updates/joomla/joomsef3/version";
    /* String, contains URL to automatic upgrade script */
    var $serverAutoUpgrade = 'http://www.artio.net/joomla-auto-upgrade';
    /* String, contains URL to registration check script */
    var $serverLicenser = 'http://www.artio.net/license-check';
    /* Array, contains domains for different languages */
    var $langDomain = array();
    /**
     * List of alternative acepted domains. (delimited by comma)
     * @var string
     */
    var $altDomain;
    /**
     * If set to yes, new SEF URLs won't be generated and only those already
     * in database will be used
     * @var boolean
     */
    var $disableNewSEF = false;
    /**
     * If set to yes, the sid variable won't be removed from SEF url
     * @var boolean
     */
    var $dontRemoveSid = false;
    /**
     * If set to yes, the $_SERVER['QUERY_STRING'] will be set according to parsed variables
     * @var boolean
     */
    var $setQueryString = true;
    /**
     * If set to yes, the $_SERVER['QUERY_STRING'] will be set according to parsed variables
     * @var boolean
     */
    var $parseJoomlaSEO = true;
    /**
     * Semicolon separated list of global custom non-sef variables
     * @var string
     */
    var $customNonSef = '';
    /**
     * If enabled, JoomSEF will try to set language according to user's browser setting
     * @var boolean
     */
    var $jfBrowserLang = true;
    /**
     * If enabled, JoomSEF will store the user's language selection in a cookie for next visit
     *
     * @var boolean
     */
    var $jfLangCookie = true;
    /**
     * Array of [lang] => subdomain to use the subdomains for languages
     * @var array
     */
    var $jfSubDomains = array();
    /**
     * Whether to use default index file for content sections and categories
     * @var boolean
     */
    var $contentUseIndex = true;
    /**
     * If set to yes, the URL variables will be checked
     * to not contain the http://something.com or similar junk
     * @var boolean
     */
    var $checkJunkUrls = true;
    /**
     * Pipe (|) separated list of junk words to search for
     * @var string
     */
    var $junkWords = 'http:// http// https:// https// www. @';
    /**
     * Semicolon separated list of variables to exclude from junk check
     * @var boolean
     */
    var $junkExclude = '';
    /**
     * Sets the <base> tag behaviour
     * @var int
     */
    //var $baseHref = _COM_SEF_BASE_HOMEPAGE;
    /**
     * Sets if the non-SEF variables should be prevented from
     * overwriting the parsed ones
     * @var boolean
     */
    var $preventNonSefOverwrite = true;
    /**
     * Main language - this language won't have language code added to URL
     * @var mixed
     */
    var $mainLanguage = 0;
    /**
     * Whether to allow UTF-8 characters in URL
     * @var boolean
     */
    var $allowUTF = false;
    /**
     * Whether to number duplicate URLs or use the duplicates management system
     * @var boolean
     */
    var $numberDuplicates = false;
    /**
     * Artio site login name
     * @var string
     */
    var $artioUserName = '';
    /**
     * Artio site password
     * @var string
     */
    var $artioPassword = '';
    /**
     * Artio download id
     * @var string
     */
    var $artioDownloadId = '';
    /**
     * Enable URL source tracing
     * @var bool
     */
    var $trace = false;
    /**
     * Tracing depth if enabled
     * @var int
     */
    var $traceLevel = 3;
    /**
     * Create canonical link automatically for URLs with nonSEF variables
     *
     * @return SEFConfig
     */
    var $autoCanonical = true;
    /**
     * Whether to SEF URLs containing the tmpl=component variable
     *
     * @var bool
     */
    var $sefComponentUrls = false;

    function SEFConfig()
    {
        $sef_config_file = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_sef'.DS.'configuration.php';

        if (file_exists($sef_config_file)) {
            include($sef_config_file);
        }

        if (isset($enabled))
        $this->enabled = $enabled;
        if (isset($replacement))
        $this->replacement = $replacement;
        if (isset($pagerep))
        $this->pagerep = $pagerep;
        if (isset($stripthese))
        $this->stripthese = $stripthese;
        if (isset($friendlytrim))
        $this->friendlytrim = $friendlytrim;
        if (isset($suffix))
        $this->suffix = $suffix;
        if (isset($addFile))
        $this->addFile = $addFile;
        
        // page text
        if (isset($pagetext)) $this->pagetext = $pagetext;
        
        if (isset($lowerCase))
        $this->lowerCase = $lowerCase;
        if (isset($replacement))
        $this->useAlias = $useAlias;
        if (isset($page404))
        $this->page404 = $page404;
        if (isset($record404))
        $this->record404 = $record404;
        if (isset($showMessageOn404))
        $this->showMessageOn404 = $showMessageOn404;
        if (isset($use404itemid))
        $this->use404itemid = $use404itemid;
        if (isset($itemid404))
        $this->itemid404 = $itemid404;
        if (isset($predefined))
        $this->predefined = $predefined;
        if (isset($replacements))
        $this->replacements = $replacements;
        if (isset($langPlacement))
        $this->langPlacement = $langPlacement;
        if (isset($alwaysUseLang))
        $this->alwaysUseLang = $alwaysUseLang;
        if (isset($translateNames))
        $this->translateNames = $translateNames;
        if (isset($excludeSource))
        $this->excludeSource = $excludeSource;
        if (isset($reappendSource))
        $this->reappendSource = $reappendSource;
        if (isset($transitSlash))
        $this->transitSlash = $transitSlash;
        if (isset($appendNonSef))
        $this->appendNonSef = $appendNonSef;
        if (isset($langDomain))
        $this->langDomain = $langDomain;
        if (isset($altDomain))
        $this->altDomain = $altDomain;
        if (isset($ignoreSource))
        $this->ignoreSource = $ignoreSource;
        if (isset($useCache))
        $this->useCache = $useCache;
        if (isset($cacheSize))
        $this->cacheSize = $cacheSize;
        if (isset($cacheMinHits))
        $this->cacheMinHits = $cacheMinHits;
        if (isset($nonSefRedirect))
        $this->nonSefRedirect = $nonSefRedirect;
        if (isset($useMoved))
        $this->useMoved = $useMoved;
        if (isset($useMovedAsk))
        $this->useMovedAsk = $useMovedAsk;
        if (isset($disableNewSEF))
        $this->disableNewSEF = $disableNewSEF;
        if (isset($dontRemoveSid))
        $this->dontRemoveSid = $dontRemoveSid;
        if (isset($setQueryString))
        $this->setQueryString = $setQueryString;
        if (isset($parseJoomlaSEO))
        $this->parseJoomlaSEO = $parseJoomlaSEO;
        if (isset($customNonSef))
        $this->customNonSef = $customNonSef;
        if (isset($canonicalLink))
        $this->canonicalLink = $canonicalLink;
        if (isset($jfBrowserLang))
        $this->jfBrowserLang = $jfBrowserLang;
        if (isset($jfLangCookie))
        $this->jfLangCookie = $jfLangCookie;
        if (isset($jfSubDomains))
        $this->jfSubDomains = $jfSubDomains;
        if (isset($contentUseIndex))
        $this->contentUseIndex = $contentUseIndex;
        if (isset($checkJunkUrls))
        $this->checkJunkUrls = $checkJunkUrls;
        if (isset($junkWords))
        $this->junkWords = $junkWords;
        if (isset($junkExclude))
        $this->junkExclude = $junkExclude;
        //if (isset($baseHref))
        //$this->baseHref = $baseHref;
        if (isset($preventNonSefOverwrite))
        $this->preventNonSefOverwrite = $preventNonSefOverwrite;
        if (isset($mainLanguage))
        $this->mainLanguage = $mainLanguage;
        if (isset($allowUTF))
        $this->allowUTF = $allowUTF;
        if (isset($numberDuplicates))
        $this->numberDuplicates = $numberDuplicates;
        if (isset($artioUserName))
        $this->artioUserName = $artioUserName;
        if (isset($artioPassword))
        $this->artioPassword = $artioPassword;
        if (isset($artioDownloadId))
        $this->artioDownloadId = $artioDownloadId;
        if (isset($trace))
        $this->trace = $trace;
        if (isset($traceLevel))
        $this->traceLevel = $traceLevel;
        if (isset($autoCanonical))
        $this->autoCanonical = $autoCanonical;
        if (isset($cacheRecordHits))
        $this->cacheRecordHits = $cacheRecordHits;
        if (isset($sefComponentUrls))
        $this->sefComponentUrls = $sefComponentUrls;
    }
    
    function saveConfig($return_data = 0)
    {
        $database =& JFactory::getDBO();
        $sef_config_file = JPATH_COMPONENT_ADMINISTRATOR.DS.'configuration.php';

        $config_data = '';
        //build the data file
        $config_data .= "&lt;?php\n";
        foreach ($this as $key => $value) {
            if ($key != '0') {
                $config_data .= "\$$key = ";
                switch (gettype($value)) {
                    case 'boolean': {
                        $config_data .= ($value ? 'true' : 'false');
                        break;
                    }
                    case 'string': {
                        // The only character that needs to be escaped is double quote (")
                        $config_data .= '"' . str_replace('"', '\"', stripslashes($value)) . '"';
                        break;
                    }
                    case 'integer':
                    case 'double': {
                        $config_data .= strval($value);
                        break;
                    }
                    case 'array': {
                        $datastring = '';
                        foreach ($value as $key2 => $data) {
                            $datastring .= '\'' . $key2 . '\' => "' . str_replace('"', '\"', stripslashes($data)) . '",';
                        }
                        $datastring = substr($datastring, 0, - 1);
                        $config_data .= "array($datastring)";
                        break;
                    }
                    default: {
                        $config_data .= 'null';
                        break;
                    }
                }
            }
            $config_data .= ";\n";
        }
        $config_data .= '?>';
        if ($return_data == 1) {
            return $config_data;
        } else {
            // write to disk
            jimport( 'joomla.filesystem.file' );

            $trans_tbl = get_html_translation_table(HTML_ENTITIES);
            $trans_tbl = array_flip($trans_tbl);
            $config_data = strtr($config_data, $trans_tbl);
            $ret = JFile::write($sef_config_file, $config_data);

            return $ret;
        }
    }

    function getPageText()
    {
        $pagetext = $this->pagetext;
        
        // if JText is used, parse out the string
        if (strpos($pagetext, 'JText::_(') === 0) {
            // make sure we use single quotes
            $pagetext = str_replace('"', '\'', $pagetext);
            // find first and second quote
            $quot1 = strpos($pagetext, '\'');
            $quot2 = strpos($pagetext, '\'', $quot1 + 1);
            // replace JText text with real text
            $pagetext = JText::_(substr($pagetext, $quot1 + 1, $quot2 - strlen($pagetext))) . substr($pagetext, strpos($pagetext, ')', $quot2) + 1);
        }

        return $pagetext;
    }
    
    /**
     * Return array of URL characters to be replaced.
     *
     * @return array
     */
    function getReplacements()
    {
        static $replacements;
        
        if( isset($replacements) ) {
            return $replacements;
        }
        
        $replacements = array();
        
        $str = trim($this->replacements);
        if( $str != '' ) {
            $items = explode(',', $str);
            foreach ($items as $item) {
                @list ($src, $dst) = explode('|', trim($item));
                
                // $dst can be empty, so the character can be removed
                if( trim($src) == '' ) {
                    continue;
                }
                
                $replacements[trim($src)] = trim($dst);
            }
        }
        
        return $replacements;
    }

    function getAltDomain()
    {
        static $domains;
        
        if (!isset($domains)) {
            $domains = explode(',', $this->altDomain);
        }
        
        return $domains;
    }
    
    function &getJunkWords()
    {
        static $words;
        
        if (!isset($words)) {
            $words = explode(' ', $this->junkWords);
            
            if( count($words) ) {
                foreach($words as $key => $val) {
                    $words[$key] = trim($val);
                    
                    if( empty($words[$key]) ) {
                        unset($words[$key]);
                    }
                }
            }
        }
        
        return $words;
    }
    
    function &getJunkExclude()
    {
        static $excludes;
        
        if (!isset($excludes)) {
            $excludes = explode(';', $this->junkExclude);
            
            if( count($excludes) ) {
                foreach($excludes as $key => $val) {
                    $excludes[$key] = trim($val);
                    
                    if( empty($excludes[$key]) ) {
                        unset($excludes[$key]);
                    }
                }
            }
        }
        
        return $excludes;
    }

    /**
     * Set config variables.
     *
     * @param string $var
     * @param mixed $val
     * @return bool
     */
    function set($var, $val)
    {
        if (isset($this->$var)) {
            $this->$var = $val;
            return true;
        }
        return false;
    }

    function &getConfig()
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new SEFConfig();
        }
        return $instance;
    }

}
?>
