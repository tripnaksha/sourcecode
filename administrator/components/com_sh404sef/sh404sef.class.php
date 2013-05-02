<?php
/**
 * SEF module for Joomla! 1.5
 *
 * @author      $Author: shumisha $
 * @copyright   Yannick Gaultier - 2009
 * @package     sh404SEF-15
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     $Id: sh404sef.class.php 941 2009-06-07 07:38:43Z silianacom-svn $
 */

// Security check to ensure this file is being included by a parent file.
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

jimport('joomla.filesystem.file');

DEFINE ('SH404SEF_IS_INSTALLED', 1);

DEFINE ('sh404SEF_URLTYPE_404', -2);
DEFINE ('sh404SEF_URLTYPE_NONE', -1);
DEFINE ('sh404SEF_URLTYPE_AUTO', 0);
DEFINE ('sh404SEF_URLTYPE_CUSTOM', 1);
DEFINE ('sh404SEF_MAX_SEF_URL_LENGTH', 255);

DEFINE ('sh404SEF_HOMEPAGE_CODE', 'index.php?'.md5('sh404SEF Homepage url code'));

DEFINE ('SH404SEF_STANDARD_ADMIN', 1);  // define possible levels for adminstration complexity
DEFINE ('SH404SEF_ADVANCED_ADMIN', 2);

if (!defined('sh404SEF_ADMIN_ABS_PATH')) {
  define('sh404SEF_ADMIN_ABS_PATH', str_replace('\\','/',dirname(__FILE__)).'/');
}
if (!defined('sh404SEF_ABS_PATH')) {
  define('sh404SEF_ABS_PATH', str_replace( '/administrator/components/com_sh404sef', '', sh404SEF_ADMIN_ABS_PATH) );
}
if (!defined('sh404SEF_FRONT_ABS_PATH')) {
  define('sh404SEF_FRONT_ABS_PATH', sh404SEF_ABS_PATH.'components/com_sh404sef/');
}

// V 1.2.4.m
global $shHomeLink;
$shHomeLink = null;

// compatibility stuff
$lang =& JFactory::getLanguage();
$GLOBALS['shMosConfig_lang']   = $lang->get('backwardlang', 'english');
$GLOBALS['shMosConfig_locale']   = $lang->get('tag', 'en-GB');
$shTemp = explode( '-', $GLOBALS['shMosConfig_locale']);
$GLOBALS['shMosConfig_shortcode']   = $shTemp[0] ? $shTemp[0] : 'en';
$GLOBALS['shConfigLiveSite'] = rtrim( JURI::base(), '/');
$GLOBALS['shConfigFrontLiveSite'] = str_replace( '/administrator', '', $GLOBALS['shConfigLiveSite']);

class shMosSEF extends JTable  // updated to remove legacy mode
{

  /**
   * Error string
   *
   * @var		string
   * @access	protected
   */
  var $_error = '';

  /**
   * Error number
   *
   * @var		int
   * @access	protected
   */
  var $_errorNum = 0;

  /** @var int */
  var $id		= null;
  /** @var int */
  var $cpt	= null;
  /** @var int */
  var $rank	= null;
  /** @var string */
  var $oldurl	= null;
  /** @var string */
  var $newurl	= null;
  /** @var tinyint */
  /** @var date */
  var $dateadd	= null;



  /**
   * Constructor
   */
  function __construct( &$db)
  {
    parent::__construct( '#__redirection', 'id', $db );
  }

  function shMosSEF( &$_db ) {
    parent::__construct( '#__redirection', 'id', $_db );
  }

  function check() {
    //initialize
    $this->_error = null;
    $this->oldurl = trim($this->oldurl);
    $this->newurl = trim($this->newurl);
    // check for valid URLs
    if (($this->oldurl == '')||($this->newurl == '')){
      $this->_error .= _COM_SEF_EMPTYURL;
      return false;
    }
    if (eregi("^\/", $this->oldurl)) {
      $this->_error .= _COM_SEF_NOLEADSLASH;
    }
    if ((eregi("^index.php", $this->newurl)) === false ) {
      $this->_error .= _COM_SEF_BADURL;
    }
    // V 1.2.4.t remove this check. We check for pre-existing non-sef instead of SEF
    if (is_null($this->_error)) {
      // check for existing URLS
      $this->_db->setQuery( "SELECT id,oldurl FROM #__redirection WHERE `newurl` LIKE '".$this->newurl."'");
      $xid = $this->_db->loadObject();
      // V 1.3.1 don't raise error if both newurl and old url are same. It means we may have changed alias list
      if ($xid && $xid->id != intval( $this->id )) {
        $this->_error = _COM_SEF_URLEXIST;
        return false;
      }
      $identical = $xid->id == intval( $this->id ) && $xid->oldurl == $this->oldurl;
      return $identical ? 'identical' : true;
    }else{
      return false;
    }
  }

  /**
   * Legacy Method, use {@link JObject::getError()}  instead
   * @deprecated As of 1.5
   */
  function getError($i = null, $toString = true )
  {
    return $this->_error;
  }

  /**
   * Legacy Method, use {@link JObject::getError()}  instead
   * @deprecated As of 1.5
   */
  function getErrorNum()
  {
    return $this->_errorNum;
  }

}

class sh404SEFMeta extends JTable
{
  /**
   * Error number
   *
   * @var		string
   * @access	protected
   */
  var $_error = '';

  /**
   * Error number
   *
   * @var		int
   * @access	protected
   */
  var $_errorNum = 0;

  /** @var int */
  var $id		= null;
  /** @var string */
  var $newurl	= null;
  /** @var string */
  var $metadesc	= null;
  /** @var string */
  var $metakey	= null;
  /** @var string */
  var $metatitle	= null;
  /** @var string */
  var $metalang	= null;
  /** @var string */
  var $metarobots	= null;

  /**
   * Constructor
   */
  function __construct( &$db)
  {
    parent::__construct( '#__sh404SEF_meta', 'id', $db );
  }

  function sh404SEFMeta( &$_db ) {
    parent::__construct( '#__sh404SEF_meta', 'id', $_db );
  }

  function check() {
    //initialize
    $this->_error = null;
    $this->newurl = trim($this->newurl);
    $this->metadesc = trim($this->metadesc);
    $this->metakey = trim($this->metakey);
    $this->metatitle = trim($this->metatitle);
    $this->metalang = trim($this->metalang);
    $this->metarobots = trim($this->metarobots);
    // check for valid URLs
    if ($this->newurl == ''){
      $this->_error .= _COM_SEF_EMPTYURL;
      return false;
    }

    if ((eregi("^index.php", $this->newurl)) === false ) {
      $this->_error .= _COM_SEF_BADURL;
    }
    if (is_null($this->_error)) {
      // check for existing URLS
      $this->_db->setQuery( "SELECT id FROM #__sh404SEF_meta WHERE `newurl` LIKE '".$this->newurl."'");
      $xid = intval( $this->_db->loadResult() );
      if ($xid && $xid != intval( $this->id )) {
        $this->_error = _COM_SEF_URLEXIST;
        return false;
      }
      return true;
    }else{
      return false;
    }
  }

  /**
   * Legacy Method, use {@link JObject::getError()}  instead
   * @deprecated As of 1.5
   */
  function getError($i = null, $toString = true )
  {
    return $this->_error;
  }

  /**
   * Legacy Method, use {@link JObject::getError()}  instead
   * @deprecated As of 1.5
   */
  function getErrorNum()
  {
    return $this->_errorNum;
  }
}

class SEFConfig {

  /* string,  version number */
  var $version = '1.0.20_Beta - build_237 - Joomla 1.5.x - <a href="http://extensions.siliana.com/">extensions.Siliana.com</a>';
  /* boolean, is 404 SEF enabled  */
  var $Enabled = false;
  /* char,  Character to use for url replacement */
  var $replacement = '-';
  /* char,  Character to use for page spacer */
  var $pagerep = '-';
  /* strip these characters */
  var $stripthese = ',|~|!|@|%|^|(|)|<|>|:|;|{|}|[|]|&|`|â€ž|â€¹|â€™|â€˜|â€œ|â€�|â€¢|â€º|Â«|Â´|Â»|Â°';  // V 1.2.4.s removed *, breaks Bookmarks
  /* characters replacement table v 1.2.4.f April 4, 2007*/
  var $shReplacements = 'Å |S, Å’|O, Å½|Z, Å¡|s, Å“|oe, Å¾|z, Å¸|Y, Â¥|Y, Âµ|u, Ã€|A, Ã�|A, Ã‚|A, Ãƒ|A, Ã„|A, Ã…|A, Ã†|A, Ã‡|C, Ãˆ|E, Ã‰|E, ÃŠ|E, Ã‹|E, ÃŒ|I, Ã�|I, ÃŽ|I, Ã�|I, Ã�|D, Ã‘|N, Ã’|O, Ã“|O, Ã”|O, Ã•|O, Ã–|O, Ã˜|O, Ã™|U, Ãš|U, Ã›|U, Ãœ|U, Ã�|Y, ÃŸ|s, Ã |a, Ã¡|a, Ã¢|a, Ã£|a, Ã¤|a, Ã¥|a, Ã¦|a, Ã§|c, Ã¨|e, Ã©|e, Ãª|e, Ã«|e, Ã¬|i, Ã­|i, Ã®|i, Ã¯|i, Ã°|o, Ã±|n, Ã²|o, Ã³|o, Ã´|o, Ãµ|o, Ã¶|o, Ã¸|o, Ã¹|u, Ãº|u, Ã»|u, Ã¼|u, Ã½|y, Ã¿|y, ÃŸ|ss';
  
  /* string,  suffix for "files" */
  var $suffix = '.html';
  /* string,  file to display when there is none */
  var $addFile = '';
  /* trims friendly characters from where they shouldn't be */
  var $friendlytrim = '-|.';
  /* boolean, convert url to lowercase */
  var $LowerCase = false;
  /* boolean, include the section name in url */
  var $ShowSection = false;
  /* boolean, exclude the category name in url */
  var $ShowCat = true;
  /* boolean, use the title_alias instead of the title */
  var $UseAlias = true;
  /* int, id of #__content item to use for static page */
  var $page404 = 0;
  /* Array, contains predefined components. */
  var $predefined = array(
  //'contact',
	   	'frontpage',
  //'login',
  //'newsfeeds',
  //'search',
	   	'sh404sef'//,
  //'weblinks'
  );
  /* Array, contains components 404 SEF will ignore. */
  var $skip = array();
  /* Array, contains components 404 SEF will not add to the DB.
   * default style URLs will be generated for these components instead
   */
  var $nocache = array('events');
  // shumisha : additional parameters
  /* Array, contains components 404 SEF will override their own sef_ext file if it has its own plugin. */
  var $shDoNotOverrideOwnSef = array();
  /* boolean,  true (default) to log 404 errors to DB, false otherwise  */
  var $shLog404Errors = true;
  /* boolean,  true (default) to use in mem cache, false to disable  */
  var $shUseURLCache = true;
 	/* integer, max number of URL couple (sef + non-sef url) allowed in cache */
  var $shMaxURLInCache = 10000;
 	/* boolean,  true (default) to translate texts in URL */
  var $shTranslateURL = true;
 	/* boolean,  true (default) will always insert language iso code in URL (for other than default language) */
  var $shInsertLanguageCode = true;
 	/* Array, contains components sh404SEF will NOT translate URLs */
  var $notTranslateURLList = array();  // V 1.2.4.m
 	/* Array, contains components sh404SEF will NOT insert iso code in URL */
  var $notInsertIsoCodeList = array();
 	// cache management
 	/* boolean, true if insert Itemid of menu item is none exists */
  var $shInsertGlobalItemidIfNone = false;
 	/* boolean, if true insert title of menu item if no Itemid exists for the URL*/
  var $shInsertTitleIfNoItemid = false;
 	/* boolean, true if always insert title of menu item. URL Itemid is used, if any, or menu item title*/
  var $shAlwaysInsertMenuTitle= false;
 	/* boolean, true if always append Itemid of non-sef URL (or of current menu item if none) to SEF URL */
  var $shAlwaysInsertItemid= false; // v 1.2.4.f
 	/* string, default menu name, to be used if $shAlwaysInsertMenuTitle is true, to override menu title */
  var $shDefaultMenuItemName = '';
 	/* boolean, if true, Getvars not used in URl will be reappend to it  */
  var $shAppendRemainingGETVars = true;

  // virtuemart management
  /* boolean, true if always insert title of shop menu item */
  var $shVmInsertShopName= false;
  /* boolean, if true, product ID will be prepended to product name */
  var $shInsertProductId = false;
  /* boolean, if true, product sku will be used instead of name */
  var $shVmUseProductSKU = false;
  /* boolean, if true, product Manufacturer name will be included in URL */
  var $shVmInsertManufacturerName = false;
  /* boolean, if true, product if will be prepended to manufacturer name */
  var $shInsertManufacturerId = false;
  /* integer, if 0, no categories will be inserted in URL for a product
   if 1, only 'last' category will be inserted in URL
   if 2, all nested categories will be inserted in URL */
  var $shVMInsertCategories = 1;

  /* boolean, if true, an additional text will be appended to sef URl when browsing categories
   * ie : .../product_cat/view-all-products.html VS .../product_cat/     */
  var $shVmAdditionalText = true;
  /* boolean, if true, a flypage name will be inserted in URL     */
  var $shVmInsertFlypage = true;

  /* boolean, if true, category id will be prepended to category name */
  var $shInsertCategoryId = false;
  /* boolean, if true, numerical id will be prepended to URL, for inclusion in Googlenews  */
  var $shInsertNumericalId = false;
  /* text, list of categories of content to which numerical id should be applied  */
  var $shInsertNumericalIdCatList = '';
  /* boolean, if true, non-sef URL like index.php?option=com_content&task=view&id=12&Itemid=2 will be 301-redirected to their sef equivalent */
  var $shRedirectNonSefToSef = true;
  /* boolean, if true, Joomla sef URL like /content/view/12/61 will be 301-redirected to their sef equivalent */
  var $shRedirectJoomlaSefToSef = true;
  /* string, should be set to SSL secure URL of site if any used. No trailing / */
  var $shConfig_live_secure_site = '';
  /* boolean, if true, ed non-sef parameter will be interpreted as a iJoomla param in com_content plugin  */
  var $shActivateIJoomlaMagInContent = true;
  /* boolean, if true, issue id of iJoomla magazine will be prepended to category name */
  var $shInsertIJoomlaMagIssueId = false;
  /* boolean, if true, magazine name will be prepended to all URL */
  var $shInsertIJoomlaMagName = false;
  /* boolean, if true, magazine id will be inserted before magazine title */
  var $shInsertIJoomlaMagMagazineId = false;
  /* boolean, if true, article id will be inserted before article title */
  var $shInsertIJoomlaMagArticleId = false;


  /* boolean, if true, name of menu item leading to Community builder will be prepended to all URL */
  var $shInsertCBName = false;
  /* boolean, if true, user name will be inserte to all URL wher appropriate. Warning : this will
   *  increase DB space used? Normally user id is still passed as a GET param (ie ...?user=245)
   *  to save space and increase speed  */
  var $shCBInsertUserName = false;
  /* boolean, if true, id of user will be prepended to its name when previous option is activated
   *  in case two users have the same name */
  var $shCBInsertUserId = true;
  /* boolean, if true user pseudo will be used instead of name */
  var $shCBUseUserPseudo = true;

  /* integer, default value for Itemid when using lettermand newsletter component */
  var $shLMDefaultItemid = 0;

  /* boolean, if true, default name for board will be prepended to URL */
  var $shInsertFireboardName = false;
  /* boolean, if true name of forum category will be inserted in URL */
  var $shFbInsertCategoryName = true;
  /* boolean, if true, Category id will be prepended to category name, in case 2 categories have same name */
  var $shFbInsertCategoryId = false;
  /* boolean, if true, message subject will be inserted in URL */
  var $shFbInsertMessageSubject = true;
  /* boolean, if true message id will be prepended to subject, in case 2 messages have same subject */
  var $shFbInsertMessageId = true;

  /* MyBlog parameters  V 1.2.4.r*/
  var $shInsertMyBlogName = false;
  var $shMyBlogInsertPostId = true;
  var $shMyBlogInsertTagId = false;
  var $shMyBlogInsertBloggerId = true;

  /* Docman parameters  V 1.2.4.r*/
  var $shInsertDocmanName = false;
  var $shDocmanInsertDocId = true;
  var $shDocmanInsertDocName = true;
  /* integer, if 0, no categories will be inserted in URL for a product
   if 1, only 'last' category will be inserted in URL
   if 2, all nested categories will be inserted in URL */
  var $shDMInsertCategories = 1;
  /* boolean, if true, category id will be prepended to category name */
  var $shDMInsertCategoryId = false;

  /* boolean, if true, url will be urlencoded, needed for some non-latin languages */
  var $shEncodeUrl = false;

  /* boolean, if true, Itemid from url on homepage with com_content will be removed, so that com_content plugin
   *  can try guess amore appropriate one  */
  var $guessItemidOnHomepage = false; // V 1.2.4.q
  // V 1.2.4.q : added param to force non-sef if https, as we are not through with some shared ssl servers!
  var $shForceNonSefIfHttps = false;

  // V 1.2.4.s try SEF without mod_rewrite
  var $shRewriteMode = 1;  // 0 = mod_rewrite, 1 = AcceptpathInfo index.php 2 = AcceptPathInfo index.php?
  var $shRewriteStrings = array('/','/index.php/','/index.php?/');

  // V1.2.4.s  record duplicate URL param
  var $shRecordDuplicates = true;
  var $shRemoveGeneratorTag = true;
  var $shPutH1Tags = false;
  var $shMetaManagementActivated = true;
  var $shInsertContentTableName = true;
  var $shContentTableName = 'Table';

  // V 1.2.4.s auto redirect from www to non-www and vice-versa
  var $shAutoRedirectWww = true;
  var $shVmInsertProductName = true;

  // V 1.2.4.t
  /* string, exact URL for homepage, to replace the automatic one. Workaround for splash pagesNo trailing / */
  var $shForcedHomePage = '';
  var $shInsertContentBlogName = false;
  var $shContentBlogName = '';

  // Mosets Tree params
  var $shInsertMTreeName = false;
  var $shMTreeInsertListingName = true;
  var $shMTreeInsertListingId = true;
  var $shMTreePrependListingId = true;
  /* integer, if 0, no categories will be inserted in URL for a product
   if 1, only 'last' category will be inserted in URL
   if 2, all nested categories will be inserted in URL */
  var $shMTreeInsertCategories = 1;
  /* boolean, if true, category id will be prepended to category name */
  var $shMTreeInsertCategoryId = false;
  var $shMTreeInsertUserName = true;
  var $shMTreeInsertUserId = true;
   
  // iJoomla NewsPortal params
  var $shInsertNewsPName = false;
  var $shNewsPInsertCatId = false;
  var $shNewsPInsertSecId = false;
   
  /* Remository parameters  V 1.2.4.t*/
  var $shInsertRemoName = false;
  var $shRemoInsertDocId = true;
  var $shRemoInsertDocName = true;
  /* integer, if 0, no categories will be inserted in URL for a product
   if 1, only 'last' category will be inserted in URL
   if 2, all nested categories will be inserted in URL */
  var $shRemoInsertCategories = 1;
  /* boolean, if true, category id will be prepended to category name */
  var $shRemoInsertCategoryId = false;
   
  // boolean, if true, task = userProfile is accessed through mysite.com/username in CB
  var $shCBShortUserURL = false; //V 1.2.4.t

  // a set of boolean vars, to decide what to do with existing data when upgrading sh404SEF
  var $shKeepStandardURLOnUpgrade = true; //V 1.2.4.t
  var $shKeepCustomURLOnUpgrade = true; //V 1.2.4.t
  var $shKeepMetaDataOnUpgrade = true; //V 1.2.4.t
  var $shKeepModulesSettingsOnUpgrade = true; //V 1.2.4.t

  // boolean, to decide whether to replace page numbering by headings in multipage articles
  var $shMultipagesTitle = true; //V 1.2.4.t
   
  // compatiblity variables, for sef_ext files usage from OpenSef/SEf Advance
  var $encode_page_suffix = '';
  var $encode_space_char = '';
  var $encode_lowercase = '';
  var $encode_strip_chars = '';
  var $spec_chars_d;
  var $spec_chars;
  var $content_page_format;  // V 1.2.4.r
  var $content_page_name;  // V 1.2.4.r

  // V x
  var $shKeepConfigOnUpgrade = true;

  // security parameters  V x
  var $shSecEnableSecurity = true;
  var $shSecLogAttacks = true;
  var $shSecOnlyNumVars = array('itemid','limit', 'limitstart');
  var $shSecAlphaNumVars = array();
  var $shSecNoProtocolVars = array('task','option','no_html','mosmsg', 'lang');
  var $ipWhiteList = '';
  var $ipBlackList = '';
  var $uAgentWhiteList = '';
  var $uAgentBlackList = '';
  var $shSecCheckHoneyPot = false;
  var $shSecHoneyPotKey = '';
  var $shSecEntranceText ="<p>Sorry. You are visiting this site from a suspicious IP address, which triggered our protection system.</p>
    <p>If you <strong>ARE NOT</strong> a malware robot of any kind, please accept our apologies for the unconvenience. You can access the page by clicking here : ";
  var $shSecSmellyPotText = "The following link is here to further trap malicious internet robots, so please don't click on it : ";
  var $monthsToKeepLogs = 1;  // = 1 will keep current months log + the month before
  var $shSecActivateAntiFlood = true;
  var $shSecAntiFloodOnlyOnPOST = false;  // if true, antiflood is activated only if there is some POST data, as in a form
  var $shSecAntiFloodPeriod = 10;		// period over which requests from same IP are counted
  var $shSecAntiFloodCount = 10;		// max number of request from same IP in period above

  //var $insertSectionInBlogTableLinks = false; // default should be true, but set to false for compat reason

  /* Array, contains whether we should translate URLs per language */
  var $shLangTranslateList = array();  // V 1.2.4.m
  /* Array, contains whether we should insert iso code URLs per language */
  var $shLangInsertCodeList = array();
  /* Array, contains list of default initial URL fragement per component */
  var $defaultComponentStringList = array();  // V 1.2.4.m
  /* Array, contains pagination string, per language */
  var $pageTexts = array();

  var $shAdminInterfaceType = SH404SEF_STANDARD_ADMIN;

  // V 1.3 RC shCustomTags params
  var $shInsertNoFollowPDFPrint = true;
  var $shInsertReadMorePageTitle = true;
  var $shMultipleH1ToH2 = true;

  // V 1.3.1 RC
  var $shVmUsingItemsPerPage = false;  // set to true if using drop-down list to select number of items per page
  var $shSecCheckPOSTData = true;		 // if set to yes, POST data will not be checked for mosconfig, script, base64,
  // standard vars and cmd file in img names
  var $shSecCurMonth = 0;
  var $shSecLastUpdated = 0;
  var $shSecTotalAttacks = 0;
  var $shSecTotalConfigVars = 0;
  var $shSecTotalBase64 =0;
  var $shSecTotalScripts = 0;
  var $shSecTotalStandardVars = 0;
  var $shSecTotalImgTxtCmd = 0;
  var $shSecTotalIPDenied = 0;
  var $shSecTotalUserAgentDenied = 0;
  var $shSecTotalFlooding = 0;
  var $shSecTotalPHP = 0;
  var $shSecTotalPHPUserClicked = 0;
  // com_smf params
  var $shInsertSMFName = true;
  var $shSMFItemsPerPage = 20;
  var $shInsertSMFBoardId = true;
  var $shInsertSMFTopicId = true;
  var $shinsertSMFUserName = false;
  var $shInsertSMFUserId = true;

  // other
  var $appendToPageTitle = '';
  var $prependToPageTitle = '';
  var $debugToLogFile = false;
  var $debugStartedAt = 0;
  var $debugDuration = 3600;  // time in seconds to log debug data to file. if 0, unlimited, default = 1 hour

  // V 1.3.1
  var $shInsertOutboundLinksImage = false;
  var $shImageForOutboundLinks = 'external-black.png';  // default = black image

  // V 1.0.3
  var $defaultParamList = '';  // holds content of /administrator/components/custom.sef.php for editing

  // V 1.0.12
  var $useCatAlias = false;
  var $useSecAlias = false;
  var $useMenuAlias = false;
  var $shEnableTableLessOutput = false;

  // End of parameters

  function SEFConfig() {

    GLOBAL $sef_config_file, $mainframe;

    $sef_config_file = sh404SEF_ADMIN_ABS_PATH.'config/config.sef.php';

    if ($mainframe->isAdmin()) {
      $this->shCheckFilesAccess();
    }

    if (shFileExists($sef_config_file)) {
      include($sef_config_file);
    }

    // shumisha : 2007-04-01 version was missing !
    //if (isset($version))		$this->version		= $version;  // V 1.2.4.r : removed as would prevent update system to work : version was not updated
    // shumisha : 2007-04-01 new parameters !
    if (isset($shUseURLCache))		$this->shUseURLCache		= $shUseURLCache;
    // shumisha : 2007-04-01 new parameters !
    if (isset($shMaxURLInCache))		$this->shMaxURLInCache		= $shMaxURLInCache;
    // shumisha : 2007-04-01 new parameters !
    if (isset($shTranslateURL))		$this->shTranslateURL		= $shTranslateURL;
    //V 1.2.4.m
    if (isset($shInsertLanguageCode))		$this->shInsertLanguageCode		= $shInsertLanguageCode;
    if (isset($notTranslateURLList))		$this->notTranslateURLList		= $notTranslateURLList;
    if (isset($notInsertIsoCodeList))		$this->notInsertIsoCodeList		= $notInsertIsoCodeList;

    // shumisha : 2007-04-03 new parameters !
    if (isset($shInsertGlobalItemidIfNone))	$this->shInsertGlobalItemidIfNone	= $shInsertGlobalItemidIfNone;
    if (isset($shInsertTitleIfNoItemid))	$this->shInsertTitleIfNoItemid	= $shInsertTitleIfNoItemid;
    if (isset($shAlwaysInsertMenuTitle))	$this->shAlwaysInsertMenuTitle	= $shAlwaysInsertMenuTitle;
    if (isset($shAlwaysInsertItemid))	$this->shAlwaysInsertItemid	= $shAlwaysInsertItemid;
    if (isset($shDefaultMenuItemName))	$this->shDefaultMenuItemName = $shDefaultMenuItemName;
    if (isset($shAppendRemainingGETVars))	$this->shAppendRemainingGETVars = $shAppendRemainingGETVars;
    if (isset($shVmInsertShopName))	$this->shVmInsertShopName = $shVmInsertShopName;

    if (isset($shInsertProductId))	$this->shInsertProductId	= $shInsertProductId;
    if (isset($shVmUseProductSKU))	$this->shVmUseProductSKU	= $shVmUseProductSKU;
    if (isset($shVmInsertManufacturerName))
    $this->shVmInsertManufacturerName = $shVmInsertManufacturerName;
    if (isset($shInsertManufacturerId))	$this->shInsertManufacturerId = $shInsertManufacturerId;
    if (isset($shVMInsertCategories))	$this->shVMInsertCategories= $shVMInsertCategories;
    if (isset($shVmAdditionalText))	$this->shVmAdditionalText= $shVmAdditionalText;
    if (isset($shVmInsertFlypage))	$this->shVmInsertFlypage= $shVmInsertFlypage;

    if (isset($shInsertCategoryId))	$this->shInsertCategoryId= $shInsertCategoryId;
    if (isset($shReplacements))	$this->shReplacements= $shReplacements;

    if (isset($shInsertNumericalId))	$this->shInsertNumericalId = $shInsertNumericalId;
    if (isset($shInsertNumericalIdCatList))	$this->shInsertNumericalIdCatList = $shInsertNumericalIdCatList;

    if (isset($shRedirectNonSefToSef))	$this->shRedirectNonSefToSef = $shRedirectNonSefToSef;
    if (isset($shRedirectJoomlaSefToSef))	$this->shRedirectJoomlaSefToSef = $shRedirectJoomlaSefToSef;
    if (isset($shConfig_live_secure_site))
    $this->shConfig_live_secure_site = rtrim( $shConfig_live_secure_site, '/');

    if (isset($shActivateIJoomlaMagInContent))
    $this->shActivateIJoomlaMagInContent = $shActivateIJoomlaMagInContent;
    if (isset($shInsertIJoomlaMagIssueId))
    $this->shInsertIJoomlaMagIssueId = $shInsertIJoomlaMagIssueId;
    if (isset($shInsertIJoomlaMagName))
    $this->shInsertIJoomlaMagName = $shInsertIJoomlaMagName;
    if (isset($shInsertIJoomlaMagMagazineId))
    $this->shInsertIJoomlaMagMagazineId = $shInsertIJoomlaMagMagazineId;
    if (isset($shInsertIJoomlaMagArticleId))
    $this->shInsertIJoomlaMagArticleId = $shInsertIJoomlaMagArticleId;

    if (isset($shInsertCBName))
    $this->shInsertCBName = $shInsertCBName;
    if (isset($shCBInsertUserName))
    $this->shCBInsertUserName = $shCBInsertUserName;
    if (isset($shCBInsertUserId))
    $this->shCBInsertUserId = $shCBInsertUserId;
    if (isset($shCBUseUserPseudo))
    $this->shCBUseUserPseudo = $shCBUseUserPseudo;
     
    if (isset($shInsertMyBlogName))
    $this->shInsertMyBlogName = $shInsertMyBlogName;
    if (isset($shMyBlogInsertPostId))
    $this->shMyBlogInsertPostId = $shMyBlogInsertPostId;
    if (isset($shMyBlogInsertTagId))
    $this->shMyBlogInsertTagId = $shMyBlogInsertTagId;
    if (isset($shMyBlogInsertBloggerId))
    $this->shMyBlogInsertBloggerId = $shMyBlogInsertBloggerId;

   	if (isset($shInsertDocmanName))
   	$this->shInsertDocmanName = $shInsertDocmanName;
    if (isset($shDocmanInsertDocId))
    $this->shDocmanInsertDocId = $shDocmanInsertDocId;
   	if (isset($shDocmanInsertDocName))
   	$this->shDocmanInsertDocName = $shDocmanInsertDocName;

    if (isset($shLog404Errors))
    $this->shLog404Errors = $shLog404Errors;

    if (isset($shLMDefaultItemid))
    $this->shLMDefaultItemid = $shLMDefaultItemid;
     
    if (isset($shInsertFireboardName))
    $this->shInsertFireboardName = $shInsertFireboardName;
    if (isset($shFbInsertCategoryName))
    $this->shFbInsertCategoryName = $shFbInsertCategoryName;
    if (isset($shFbInsertCategoryId))
    $this->shFbInsertCategoryId = $shFbInsertCategoryId;
    if (isset($shFbInsertMessageSubject))
    $this->shFbInsertMessageSubject = $shFbInsertMessageSubject;
    if (isset($shFbInsertMessageId))
    $this->shFbInsertMessageId = $shFbInsertMessageId;
     
    if (isset($shDoNotOverrideOwnSef)) // V 1.2.4.m
    $this->shDoNotOverrideOwnSef = $shDoNotOverrideOwnSef;
     
    if (isset($shEncodeUrl)) // V 1.2.4.m
    $this->shEncodeUrl = $shEncodeUrl;

    if (isset($guessItemidOnHomepage)) // V 1.2.4.q
    $this->guessItemidOnHomepage = $guessItemidOnHomepage;

    if (isset($shForceNonSefIfHttps))	// V 1.2.4.q
    $this->shForceNonSefIfHttps= $shForceNonSefIfHttps;
     
    if (isset($shRewriteMode))	// V 1.2.4.s
    $this->shRewriteMode = $shRewriteMode;
    if (isset($shRewriteStrings))	// V 1.2.4.s
    $this->shRewriteStrings = $shRewriteStrings;

    if (isset($shRecordDuplicates))	// V 1.2.4.s
    $this->shRecordDuplicates = $shRecordDuplicates;
    if (isset($shMetaManagementActivated))	// V 1.2.4.s
    $this->shMetaManagementActivated = $shMetaManagementActivated;
    if (isset($shRemoveGeneratorTag))	// V 1.2.4.s
    $this->shRemoveGeneratorTag = $shRemoveGeneratorTag;
    if (isset($shPutH1Tags))	// V 1.2.4.s
    $this->shPutH1Tags = $shPutH1Tags;
    if (isset($shInsertContentTableName))	// V 1.2.4.s
    $this->shInsertContentTableName = $shInsertContentTableName;
    if (isset($shContentTableName))	// V 1.2.4.s
    $this->shContentTableName = $shContentTableName;
    if (isset($shAutoRedirectWww))	// V 1.2.4.s
    $this->shAutoRedirectWww = $shAutoRedirectWww;
    if (isset($shVmInsertProductName))	// V 1.2.4.s
    $this->shVmInsertProductName = $shVmInsertProductName;

    if (isset($shDMInsertCategories))	// V 1.2.4.t
    $this->shDMInsertCategories = $shDMInsertCategories;
    if (isset($shDMInsertCategoryId))	// V 1.2.4.t
    $this->shDMInsertCategoryId = $shDMInsertCategoryId;

    if (isset($shForcedHomePage))	// V 1.2.4.t
    $this->shForcedHomePage = $shForcedHomePage;
    if (isset($shInsertContentBlogName))	// V 1.2.4.t
    $this->shInsertContentBlogName = $shInsertContentBlogName;
    if (isset($shContentBlogName))	// V 1.2.4.t
    $this->shContentBlogName = $shContentBlogName;

    if (isset($shInsertMTreeName))	// V 1.2.4.t
    $this->shInsertMTreeName = $shInsertMTreeName;
    if (isset($shMTreeInsertListingName))	// V 1.2.4.t
    $this->shMTreeInsertListingName = $shMTreeInsertListingName;
    if (isset($shMTreeInsertListingId))	// V 1.2.4.t
    $this->shMTreeInsertListingId = $shMTreeInsertListingId;
    if (isset($shMTreePrependListingId))	// V 1.2.4.t
    $this->shMTreePrependListingId = $shMTreePrependListingId;
    if (isset($shMTreeInsertCategories))	// V 1.2.4.t
    $this->shMTreeInsertCategories = $shMTreeInsertCategories;
    if (isset($shMTreeInsertCategoryId))	// V 1.2.4.t
    $this->shMTreeInsertCategoryId = $shMTreeInsertCategoryId;
    if (isset($shMTreeInsertUserName))	// V 1.2.4.t
    $this->shMTreeInsertUserName = $shMTreeInsertUserName;
    if (isset($shMTreeInsertUserId))	// V 1.2.4.t
    $this->shMTreeInsertUserId = $shMTreeInsertUserId;

    if (isset($shInsertNewsPName))	// V 1.2.4.t
    $this->shInsertNewsPName = $shInsertNewsPName;
    if (isset($shNewsPInsertCatId))	// V 1.2.4.t
    $this->shNewsPInsertCatId = $shNewsPInsertCatId;
    if (isset($shNewsPInsertSecId))	// V 1.2.4.t
    $this->shNewsPInsertSecId = $shNewsPInsertSecId;
     
    if (isset($shInsertRemoName))  // V 1.2.4.t
    $this->shInsertRemoName = $shInsertRemoName;
    if (isset($shRemoInsertDocId))    // V 1.2.4.t
    $this->shRemoInsertDocId = $shRemoInsertDocId;
   	if (isset($shRemoInsertDocName))    // V 1.2.4.t
   	$this->shRemoInsertDocName = $shRemoInsertDocName;
    if (isset($shRemoInsertCategories))	// V 1.2.4.t
    $this->shRemoInsertCategories = $shRemoInsertCategories;
    if (isset($shRemoInsertCategoryId))	// V 1.2.4.t
    $this->shRemoInsertCategoryId = $shRemoInsertCategoryId;

    if (isset($shCBShortUserURL))	// V 1.2.4.t
    $this->shCBShortUserURL = $shCBShortUserURL;
     
    if (isset($shKeepStandardURLOnUpgrade))	// V 1.2.4.t
    $this->shKeepStandardURLOnUpgrade = $shKeepStandardURLOnUpgrade;
    if (isset($shKeepCustomURLOnUpgrade))	// V 1.2.4.t
    $this->shKeepCustomURLOnUpgrade = $shKeepCustomURLOnUpgrade;
    if (isset($shKeepMetaDataOnUpgrade))	// V 1.2.4.t
    $this->shKeepMetaDataOnUpgrade = $shKeepMetaDataOnUpgrade;
    if (isset($shKeepModulesSettingsOnUpgrade))	// V 1.2.4.t
    $this->shKeepModulesSettingsOnUpgrade = $shKeepModulesSettingsOnUpgrade;

    if (isset($shMultipagesTitle))	// V 1.2.4.t
    $this->shMultipagesTitle = $shMultipagesTitle;

    // shumisha end of new parameters
    if (isset($Enabled))		$this->Enabled		= $Enabled;
  		if (isset($replacement)) 	$this->replacement	= $replacement;
  		if (isset($pagerep)) 		$this->pagerep		= $pagerep;
  		if (isset($stripthese)) 	$this->stripthese 	= $stripthese;
  		if (isset($friendlytrim)) 	$this->friendlytrim	= $friendlytrim;
  		if (isset($suffix))			$this->suffix		= $suffix;
  		if (isset($addFile)) 		$this->addFile 		= $addFile;
  		if (isset($LowerCase))		$this->LowerCase	= $LowerCase;
  		if (isset($ShowSection)) 	$this->ShowSection	= $ShowSection;
  		if (isset($HideCat))		$this->HideCat		= $HideCat;
  		if (isset($replacement)) 	$this->UseAlias		= $UseAlias;
  		if (isset($UseAlias))		$this->page404		= $page404;
  		if (isset($predefined))		$this->predefined	= $predefined;
  		if (isset($skip))			$this->skip			= $skip;
  		if (isset($nocache))		$this->nocache		= $nocache;
  		if (isset($ShowCat)) 		$this->ShowCat 		= $ShowCat;

    // V x
    if (isset($shKeepConfigOnUpgrade))	// V 1.2.4.x
    $this->shKeepConfigOnUpgrade = $shKeepConfigOnUpgrade;
    if (isset($shSecEnableSecurity))	// V 1.2.4.x
    $this->shSecEnableSecurity = $shSecEnableSecurity;
    if (isset($shSecLogAttacks))	// V 1.2.4.x
    $this->shSecLogAttacks = $shSecLogAttacks;
    if (isset($shSecOnlyNumVars))	// V 1.2.4.x
    $this->shSecOnlyNumVars = $shSecOnlyNumVars;
    if (isset($shSecAlphaNumVars))	// V 1.2.4.x
    $this->shSecAlphaNumVars = $shSecAlphaNumVars;
    if (isset($shSecNoProtocolVars))	// V 1.2.4.x
    $this->shSecNoProtocolVars = $shSecNoProtocolVars;
    $this->ipWhiteList = shReadFile(sh404SEF_ADMIN_ABS_PATH . 'security/sh404SEF_IP_white_list.txt');
    $this->ipBlackList = shReadFile(sh404SEF_ADMIN_ABS_PATH . 'security/sh404SEF_IP_black_list.txt');
    $this->uAgentWhiteList = shReadFile(sh404SEF_ADMIN_ABS_PATH . 'security/sh404SEF_uAgent_white_list.txt');
    $this->uAgentBlackList = shReadFile(sh404SEF_ADMIN_ABS_PATH . 'security/sh404SEF_uAgent_black_list.txt');
     

    if (isset($shSecCheckHoneyPot))	// V 1.2.4.x
    $this->shSecCheckHoneyPot = $shSecCheckHoneyPot;
    if (isset($shSecDebugHoneyPot))	// V 1.2.4.x
    $this->shSecDebugHoneyPot = $shSecDebugHoneyPot;
    if (isset($shSecHoneyPotKey))	// V 1.2.4.x
    $this->shSecHoneyPotKey = $shSecHoneyPotKey;
    if (isset($shSecEntranceText))	// V 1.2.4.x
    $this->shSecEntranceText = $shSecEntranceText;
    if (isset($shSecSmellyPotText))	// V 1.2.4.x
    $this->shSecSmellyPotText = $shSecSmellyPotText;
    if (isset($monthsToKeepLogs))	// V 1.2.4.x
    $this->monthsToKeepLogs = $monthsToKeepLogs;
    if (isset($shSecActivateAntiFlood))	// V 1.2.4.x
    $this->shSecActivateAntiFlood = $shSecActivateAntiFlood;
    if (isset($shSecAntiFloodOnlyOnPOST))	// V 1.2.4.x
    $this->shSecAntiFloodOnlyOnPOST = $shSecAntiFloodOnlyOnPOST;
    if (isset($shSecAntiFloodPeriod))	// V 1.2.4.x
    $this->shSecAntiFloodPeriod = $shSecAntiFloodPeriod;
    if (isset($shSecAntiFloodCount))	// V 1.2.4.x
    $this->shSecAntiFloodCount = $shSecAntiFloodCount;
    //  if (isset($insertSectionInBlogTableLinks))	// V 1.2.4.x
    //    $this->insertSectionInBlogTableLinks = $insertSectionInBlogTableLinks;

    $this->shLangTranslateList = $this->shInitLanguageList( isset($shLangTranslateList)? $shLangTranslateList : null, 0, 0);
    $this->shLangInsertCodeList = $this->shInitLanguageList( isset($shLangInsertCodeList) ? $shLangInsertCodeList : null, 0, 0);

    if (isset($defaultComponentStringList))	// V 1.2.4.x
    $this->defaultComponentStringList = $defaultComponentStringList;

    $this->pageTexts = $this->shInitLanguageList( isset($pageTexts) ? $pageTexts : null, // V x
    isset($pagetext) ? $pagetext : 'Page-%s', isset($pagetext) ? $pagetext : 'Page-%s'); // use value from prev versions if any
     
    if (isset($shAdminInterfaceType))	// V 1.2.4.x
    $this->shAdminInterfaceType = $shAdminInterfaceType;

    // compatibility with version earlier than V x
    if (isset($shShopName))	// V 1.2.4.x
    $this->defaultComponentStringList['virtuemart'] = $shShopName;
    if (isset($shIJoomlaMagName))// V 1.2.4.x
    $this->defaultComponentStringList['magazine'] = $shIJoomlaMagName;
    if (isset($shCBName))// V 1.2.4.x
    $this->defaultComponentStringList['comprofiler'] = $shCBName;
    if (isset($shFireboardName))// V 1.2.4.x
    $this->defaultComponentStringList['fireboard'] = $shFireboardName;
    if (isset($shMyBlogName))// V 1.2.4.x
    $this->defaultComponentStringList['myblog'] = $shMyBlogName;
    if (isset($shDocmanName))// V 1.2.4.x
    $this->defaultComponentStringList['docman'] = $shDocmanName;
    if (isset($shMTreeName))// V 1.2.4.x
    $this->defaultComponentStringList['mtree'] = $shMTreeName;
    if (isset($shNewsPName))// V 1.2.4.x
    $this->defaultComponentStringList['news_portal'] = $shNewsPName;
    if (isset($shRemoName))// V 1.2.4.x
    $this->defaultComponentStringList['remository'] = $shRemoName;
    // end of compatibility code

    // V 1.3 RC
    if (isset($shInsertNoFollowPDFPrint))
    $this->shInsertNoFollowPDFPrint = $shInsertNoFollowPDFPrint;
    if (isset($shInsertReadMorePageTitle))
    $this->shInsertReadMorePageTitle = $shInsertReadMorePageTitle;
    if (isset($shMultipleH1ToH2))
    $this->shMultipleH1ToH2 = $shMultipleH1ToH2;

    // V 1.3.1 RC
    if (isset($shVmUsingItemsPerPage))
    $this->shVmUsingItemsPerPage = $shVmUsingItemsPerPage;
    if (isset($shSecCheckPOSTData))
    $this->shSecCheckPOSTData = $shSecCheckPOSTData;
    if (isset($shSecCurMonth))
    $this->shSecCurMonth = $shSecCurMonth;
    if (isset($shSecLastUpdated))
    $this->shSecLastUpdated = $shSecLastUpdated;
    if (isset($shSecTotalAttacks))
    $this->shSecTotalAttacks = $shSecTotalAttacks;
    if (isset($shSecTotalConfigVars))
    $this->shSecTotalConfigVars = $shSecTotalConfigVars;
    if (isset($shSecTotalBase64))
    $this->shSecTotalBase64 = $shSecTotalBase64;
    if (isset($shSecTotalScripts))
    $this->shSecTotalScripts = $shSecTotalScripts;
    if (isset($shSecTotalStandardVars))
    $this->shSecTotalStandardVars = $shSecTotalStandardVars;
    if (isset($shSecTotalImgTxtCmd))
    $this->shSecTotalImgTxtCmd = $shSecTotalImgTxtCmd;
    if (isset($shSecTotalIPDenied))
    $this->shSecTotalIPDenied = $shSecTotalIPDenied;
    if (isset($shSecTotalUserAgentDenied))
    $this->shSecTotalUserAgentDenied = $shSecTotalUserAgentDenied;
    if (isset($shSecTotalFlooding))
    $this->shSecTotalFlooding = $shSecTotalFlooding;
    if (isset($shSecTotalPHP))
    $this->shSecTotalPHP = $shSecTotalPHP;
    if (isset($shSecTotalPHPUserClicked))
    $this->shSecTotalPHPUserClicked = $shSecTotalPHPUserClicked;

    if (isset($shInsertSMFName))
    $this->shInsertSMFName = $shInsertSMFName;
    if (isset($shSMFItemsPerPage))
    $this->shSMFItemsPerPage = $shSMFItemsPerPage;
    if (isset($shInsertSMFBoardId))
    $this->shInsertSMFBoardId = $shInsertSMFBoardId;
    if (isset($shInsertSMFTopicId))
    $this->shInsertSMFTopicId = $shInsertSMFTopicId;
    if (isset($shinsertSMFUserName))
    $this->shinsertSMFUserName = $shinsertSMFUserName;
    if (isset($shInsertSMFUserId))
    $this->shInsertSMFUserId = $shInsertSMFUserId;

    if (isset($prependToPageTitle))
    $this->prependToPageTitle = $prependToPageTitle;
    if (isset($appendToPageTitle))
    $this->appendToPageTitle = $appendToPageTitle;

    if (isset($debugToLogFile))
    $this->debugToLogFile = $debugToLogFile;
    if (isset($debugStartedAt))
    $this->debugStartedAt = $debugStartedAt;
    if (isset($debugDuration))
    $this->debugDuration = $debugDuration;

    // V 1.3.1
    if (isset($shInsertOutboundLinksImage))
    $this->shInsertOutboundLinksImage = $shInsertOutboundLinksImage;
    if (isset($shImageForOutboundLinks))
    $this->shImageForOutboundLinks = $shImageForOutboundLinks;

    // V 1.0.12
    if (isset($useCatAlias))
    $this->useCatAlias = $useCatAlias;
    if (isset($useSecAlias))
    $this->useSecAlias = $useSecAlias;
    if (isset($useMenuAlias))
    $this->useMenuAlias = $useMenuAlias;
    if (isset($shEnableTableLessOutput))
    $this->shEnableTableLessOutput = $shEnableTableLessOutput;

    // define default values for seldom used params

    if (!defined('SH404SEF_COMPAT_SHOW_SECTION_IN_CAT_LINKS')) {
      // SECTION : GLOBAL PARAMETERS for sh404sef ---------------------------------------------------------------------

      $shDefaultParamsHelp['SH404SEF_COMPAT_SHOW_SECTION_IN_CAT_LINKS'] =
'// compatibility with past version. Set to 0 so that
// section is not added in (table) category links. This was a bug in past versions
// as sh404SEF would not insert section, even if ShowSection param was set to Yes';
      $shDefaultParams['SH404SEF_COMPAT_SHOW_SECTION_IN_CAT_LINKS'] = 1;

      $shDefaultParamsHelp['sh404SEF_USE_NON_STANDARD_PORT'] =
'// set to 1 if using other than port 80 for http';
      $shDefaultParams['sh404SEF_USE_NON_STANDARD_PORT'] = 0;

      $shDefaultParamsHelp['sh404SEF_PAGE_NOT_FOUND_FORCED_ITEMID'] =
'// if not 0, will be used instead of Homepage itemid to display 404 error page';
      $shDefaultParams['sh404SEF_PAGE_NOT_FOUND_FORCED_ITEMID'] = 0;

      $shDefaultParamsHelp['sh404SEF_PROTECT_AGAINST_DOCUMENT_TYPE_ERROR'] =
'// if not 0, urls for pdf documents and rss feeds  will be only partially turned into sef urls. 
//The query string &format=pdf or &format=feed will be still be appended.
// This will protect against malfunctions when using some plugins which makes a call
// to JFactory::getDocument() from a onAfterInitiliaze handler
// At this time, SEF urls are not decoded and thus the document type is set to html instead of pdf or feed
// resulting in the home page being displayed instead of the correct document';
      $shDefaultParams['sh404SEF_PROTECT_AGAINST_DOCUMENT_TYPE_ERROR'] = 0;

      $shDefaultParamsHelp['sh404SEF_REDIRECT_IF_INDEX_PHP'] =
'// if not 0, sh404SEF will do a 301 redirect from http://yoursite.com/index.php
// or http://yoursite.com/index.php?lang=xx to http://yoursite.com/
// this may not work on some web servers, which transform yoursite.com into
// yoursite.com/index.php, thus creating and endless loop. If your server does
// that, set this param to 0';
      $shDefaultParams['sh404SEF_REDIRECT_IF_INDEX_PHP'] = 1;

      $shDefaultParamsHelp['sh404SEF_NON_SEF_IF_SUPERADMIN'] =
'// if superadmin logged in, force non-sef, for testing and setting up purpose';
      $shDefaultParams['sh404SEF_NON_SEF_IF_SUPERADMIN'] = 0;

      $shDefaultParamsHelp['sh404SEF_DE_ACTIVATE_LANG_AUTO_REDIRECT'] =
'// set to 1 to prevent 303 auto redirect based on user language
// use with care, will prevent language switch to work for users without javascript';
      $shDefaultParams['sh404SEF_DE_ACTIVATE_LANG_AUTO_REDIRECT'] = 1;

      $shDefaultParamsHelp['sh404SEF_CHECK_COMP_IS_INSTALLED'] =
'// if 1, SEF URLs will only be built for installed components.';
      $shDefaultParams['sh404SEF_CHECK_COMP_IS_INSTALLED'] = 1;

      $shDefaultParamsHelp['sh404SEF_REDIRECT_OUTBOUND_LINKS'] =
'// if 1, all outbound links on page will be reached through a redirect
// to avoid page rank leakage';
      $shDefaultParams['sh404SEF_REDIRECT_OUTBOUND_LINKS'] = 0;

      $shDefaultParamsHelp['sh404SEF_PDF_DIR'] =
'// if not empty, urls to pdf produced by Joomla will be prefixed with this
// path. Can be : \'pdf\' or \'pdf/something\' (ie: don\'t put leading or trailing slashes)
// Allows you to store some pre-built PDF in a directory called /pdf, with the same name
// as a page. Such a pdf will be served directly by the web server instead of being built on
// the fly by Joomla. This will save CPU and RAM. (only works this way if using htaccess';
      $shDefaultParams['sh404SEF_PDF_DIR'] = 'pdf';

      $shDefaultParamsHelp['SH404SEF_URL_CACHE_TTL'] =
'// time to live for url cache in hours : default = 168h = 1 week
// Set to 0 to keep cache forever';
      $shDefaultParams['SH404SEF_URL_CACHE_TTL'] = 168;

      $shDefaultParamsHelp['SH404SEF_URL_CACHE_WRITES_TO_CHECK_TTL'] =
'// number of cache write before checking cache TTL.';
      $shDefaultParams['SH404SEF_URL_CACHE_WRITES_TO_CHECK_TTL'] = 1000;

      $shDefaultParamsHelp['sh404SEF_SEC_MAIL_ATTACKS_TO_ADMIN'] =
'// if set to 1, an email will be send to site admin when an attack is logged
// if the site is live, you could be drowning in email rapidly !!!';
      $shDefaultParams['sh404SEF_SEC_MAIL_ATTACKS_TO_ADMIN'] = 0;

      $shDefaultParams['sh404SEF_SEC_EMAIL_TO_ADMIN_SUBJECT'] = 'Your site %sh404SEF_404_SITE_NAME% was subject to an attack';
      $shDefaultParams['sh404SEF_SEC_EMAIL_TO_ADMIN_BODY'] =
'Hello !'."\n\n".'This is sh404SEF security component, running at your site (%sh404SEF_404_SITE_URL%).'
."\n\n".'I have just blocked an attack on your site. Please check details below : '
."\n".  '------------------------------------------------------------------------'
."\n".  '%sh404SEF_404_ATTACK_DETAILS%'
."\n".  '------------------------------------------------------------------------'
."\n\n".'Thanks for using sh404SEF!'
."\n\n"
;

$shDefaultParamsHelp['SH404SEF_PAGES_TO_CLEAN_LOGS'] =
'// number of pages between checks to remove old log files
// if 1, we check at every page request';
$shDefaultParams['SH404SEF_PAGES_TO_CLEAN_LOGS'] = 10000;

$shDefaultParamsHelp['SH_VM_ALLOW_PRODUCTS_IN_MULTIPLE_CATS'] =
'// SECTION : Virtuemart plugin parameters ----------------------------------------------------------------------------

// set to 1 for products to have requested category name included in url
// useful if some products are in more than one category. By default (param set to 0),
// only one category will be used. If set to 1, all categories can be used';

$shDefaultParams['SH_VM_ALLOW_PRODUCTS_IN_MULTIPLE_CATS'] = 0;

$shDefaultParamsHelp['SH404SEF_DP_INSERT_ALL_CATEGORIES'] =
'// SECTION : Deeppockets plugin parameters -----------------------------------------------------------------

// set to 0 to have no cat inserted  /234-ContentTitle/
// set to 1 to have only last cat added /123-CatTitle/234-ContentTitle/
// set to 2 to have all nested cats inserted /456-Cat1Title/123-Cat2Title/234-ContentTitle/';
$shDefaultParams['SH404SEF_DP_INSERT_ALL_CATEGORIES'] = 2;

$shDefaultParamsHelp['SH404SEF_DP_INSERT_CAT_ID'] =
'// if non-zero, id of each cat will be inserted in the url /123-CatTitle/';
$shDefaultParams['SH404SEF_DP_INSERT_CAT_ID'] = 0;

$shDefaultParamsHelp['SH404SEF_DP_INSERT_CONTENT_ID'] =
'// if non-zero, id of each content element will be inserted in url /234-ContentTitle/';
$shDefaultParams['SH404SEF_DP_INSERT_CONTENT_ID'] = 0;

$shDefaultParamsHelp['SH404SEF_DP_USE_JOOMLA_URL'] =
'// if non-zero, DP links to content element will be identical to those
// generated for Joomla regular content - usefull if this content can also be
// accessed outside of DP, to avoid duplicate content penalties';
$shDefaultParams['SH404SEF_DP_USE_JOOMLA_URL'] = 0;

$shDefaultParamsHelp['sh404SEF_SMF_PARAMS_SIMPLE_URLS'] =
'// SECTION : com_smf plugin parameters --------------------------------------------------------------------------
// set to 1 use simple URLs, without all details';
$shDefaultParams['sh404SEF_SMF_PARAMS_SIMPLE_URLS'] = 0;

$shDefaultParamsHelp['sh404SEF_SMF_PARAMS_TABLE_PREFIX'] =
'// prefix used in the DB for the SMF tables';
$shDefaultParams['sh404SEF_SMF_PARAMS_TABLE_PREFIX'] = 'smf_';

$shDefaultParamsHelp['sh404SEF_SMF_PARAMS_ENABLE_STICKY'] =
'// not used';
$shDefaultParams['sh404SEF_SMF_PARAMS_ENABLE_STICKY'] = 0;

$shDefaultParamsHelp['sh404SEF_SOBI2_PARAMS_ALWAYS_INCLUDE_CATS'] =
'// SECTION : SOBI2 plugin parameters ----------------------------------------------------------------------------

// set to 1 to always include categories in SOBI2 entries
// details pages url';
$shDefaultParams['sh404SEF_SOBI2_PARAMS_ALWAYS_INCLUDE_CATS'] = 0;

$shDefaultParamsHelp['sh404SEF_SOBI2_PARAMS_INCLUDE_ENTRY_ID'] =
'// set to 1 so that entry id is prepended to url';
$shDefaultParams['sh404SEF_SOBI2_PARAMS_INCLUDE_ENTRY_ID'] = 0;

$shDefaultParamsHelp['sh404SEF_SOBI2_PARAMS_INCLUDE_CAT_ID'] =
'// set to 1 so that category id is prepended to category name';
$shDefaultParams['sh404SEF_SOBI2_PARAMS_INCLUDE_CAT_ID'] = 0;

// end of parameters

$sef_custom_config_file = sh404SEF_ADMIN_ABS_PATH.'custom.sef.php';
// read user defined values, possibly recovered while upgrading
if (JFile::exists($sef_custom_config_file)) {
  include($sef_custom_config_file);
}

// generate string for parameter modification
if ($GLOBALS['mainframe']->isAdmin()) {  // only need to modify custom params in back-end
  $this->defaultParamList = '<?php
// custom.sef.php : custom.configuration file for sh404SEF
// 1.0.20_Beta - build_237 - Joomla 1.5.x - <a href="http://extensions.siliana.com/">extensions.Siliana.com/</a>

// DO NOT REMOVE THIS LINE :
if (!defined(\'_JEXEC\')) die(\'Direct Access to this location is not allowed.\');
// DO NOT REMOVE THIS LINE'."\n";

  foreach ($shDefaultParams as $key=>$value) {
    $this->defaultParamList .= "\n";
    if (!empty ($shDefaultParamsHelp[$key]))
    $this->defaultParamList .= $shDefaultParamsHelp[$key]."\n";  // echo help text, if any
    $this->defaultParamList .= '$shDefaultParams[\''.$key.'\'] = '
    . (is_string($value) ? "'$value'" : $value)
    .";\n";
  }
}

// read user set values for these params and create constants
if (!empty($shDefaultParams)) {
  foreach( $shDefaultParams as $key=>$value) {
    define($key, $value);
  }
}
unset($shDefaultParams);
unset($shDefaultParamsHelp);

    }

    // compatiblity variables, for sef_ext files usage from OpenSef/SEf Advance V 1.2.4.p
    $this->encode_page_suffix = '';// if using an opensef sef_ext, we don't let  them manage suffix
    $this->encode_space_char = $this->replacement;
    $this->encode_lowercase = $this->LowerCase;
    $this->encode_strip_chars = $this->stripthese;
    $this->content_page_name = str_replace('%s', '', $this->pageTexts[$GLOBALS['shMosConfig_locale']]); // V 1.2.4.r
    $this->content_page_format = '%s'.$this->replacement.'%d'; // V 1.2.4.r
    $shTemp = $this->shGetReplacements();
    foreach ($shTemp as $dest=>$source) {
      $this->spec_chars_d .= $dest.',';
      $this->spec_chars .= $source.',';
    }
    rtrim($this->spec_chars_d, ',');
    rtrim($this->spec_chars, ',');
     
  }  // end of SefConfig

  // V x
  function shCheckFileAccess($fileName) {

    $ret = is_readable( sh404SEF_ABS_PATH.$fileName) && is_writable( sh404SEF_ABS_PATH.$fileName) ?
    _COM_SEF_WRITEABLE : _COM_SEF_UNWRITEABLE;
    return $ret;
  }

  function shCheckFilesAccess() {

    shIncludeLanguageFile();  // sometimes language file may not be included yet, need it in shCheckFileAccess
    $status = array();
    $status['administrator/components/com_sh404sef/config'] = $this->shCheckFileAccess('administrator/components/com_sh404sef/config');
    $status['administrator/components/com_sh404sef'] = $this->shCheckFileAccess('administrator/components/com_sh404sef');
    $status['administrator/components/com_sh404sef/logs'] = $this->shCheckFileAccess('administrator/components/com_sh404sef/logs');
    $status['administrator/components/com_sh404sef/security'] = $this->shCheckFileAccess('administrator/components/com_sh404sef/security');
    $status['components/com_sh404sef/cache'] = $this->shCheckFileAccess('components/com_sh404sef/cache');
    $this->fileAccessStatus = $status;
  }

  function shInitLanguageList($currentList, $default, $defaultLangDefault) {
    global $mainframe;




    $ret = array();
    $shKind = shIsMultilingual();
    if (!$shKind && !$mainframe->isAdmin()) {
      if (empty($currentList) || !isset($currentList[$GLOBALS['shMosConfig_locale']])) {
        $ret[$GLOBALS['shMosConfig_locale']] = $defaultLangDefault;
      } else {
        $ret[$GLOBALS['shMosConfig_locale']] = $currentList[$GLOBALS['shMosConfig_locale']];
      }
    } else {
      $activeLanguages = shGetActiveLanguages();
      if (empty($activeLanguages)) {
        if (empty($currentList) || !isset($currentList[$GLOBALS['shMosConfig_locale']])) {
          $ret[$GLOBALS['shMosConfig_locale']] = $defaultLangDefault;
        } else {
          $ret[$GLOBALS['shMosConfig_locale']] = $currentList[$GLOBALS['shMosConfig_locale']];
        }
      } else {
        foreach ($activeLanguages as $language) {
          if (empty($currentList) || !isset($currentList[$language->code])) {
            $ret[$language->code] = $language->code == $GLOBALS['shMosConfig_locale'] ? $defaultLangDefault : $default;
          } else {
            $ret[$language->code] = $currentList[$language->code];
          }
        }
      }
    }
    return $ret;
  }

  function saveConfig($return_data=0) {

    GLOBAL $sef_config_file;

    $database =& JFactory::getDBO();
    $quoteGPC = get_magic_quotes_gpc();
    $user = JFactory::getUser();
    $userName = empty($user) ? '-' : $user->username;
    $userId = empty($user) ? '-' : $user->id;
    //build the data file
    $config_data = '<?php' . "\n"
    . '// config.sef.php : configuration file for sh404SEF for Joomla 1.5.x' . "\n"
    . '// ' . $this->version . "\n"
    . '// saved at: ' . date( 'Y-m-d H:i:s') . "\n"
    . '// by: ' . $userName . ' (id: ' . $userId . ' )' . "\n"
    . '// domain: ' . $GLOBALS['shConfigFrontLiveSite'] . "\n\n"
    . 'if (!defined(\'_JEXEC\')) die(\'Direct Access to this location is not allowed.\');' . "\n\n"
    ;

    foreach ($this as $key=>$value) {
      if ($key != "0" && $key != 'ipWhiteList' && $key != 'ipBlackList'
      && $key != 'uAgentWhiteList' && $key != 'uAgentBlackList'
      && $key != 'defaultParamList'
      ) {
        $config_data .= "\$$key = ";
        if ($key == 'shLangTranslateList' || $key == 'shLangInsertCodeList' || $key == 'defaultComponentStringList'
        || $key == 'pageTexts') {
          $datastring ='';
          foreach($value as $key2=>$data) {
            $datastring .= '"'.$key2.'"=>'.'"'.str_replace('"', '\"', $quoteGPC ? stripslashes($data):$data).'",';
          }
          $datastring = substr($datastring,0,-1);
          $config_data .= "array($datastring)";
        } else
        switch (gettype($value)) {
          case "boolean":
            $config_data .= ($value ? "true" : "false");
            break;
          case "string":
            $config_data .= "\"".str_replace('"', '\"', $quoteGPC ? stripslashes($value):$value)."\"";
            break;
          case "integer":
          case "double":
            $config_data .= strval($value);
            break;
          case "array":
            $datastring ='';
            foreach($value as $key2=>$data) {
              $datastring .= '"'.str_replace('"', '\"', $quoteGPC ? stripslashes($data):$data).'",';
            }
            $datastring = substr($datastring,0,-1);
            $config_data .= "array($datastring)";
            break;
          default:
            $config_data .= "null";
            break;
        }
        $config_data .= ";\n";
      }
    }
    $config_data .= '?'.'>';
    if ($return_data == 1) {
      return $config_data;
    }else{
      // write to disk
      //if (is_writable($sef_config_file)) {
      $trans_tbl = get_html_translation_table(HTML_ENTITIES);
      $trans_tbl = array_flip($trans_tbl);
      $config_data =strtr($config_data, $trans_tbl);
      $fd = fopen($sef_config_file, "wb");
      if (fwrite($fd, $config_data, strlen($config_data)) === FALSE) {
        $ret = 0;
      }else{
   					$ret = 1;
      }
      fclose($fd);
      // save lists
      shSaveFile(sh404SEF_ADMIN_ABS_PATH . 'security/sh404SEF_IP_white_list.txt', $this->ipWhiteList);
      shSaveFile(sh404SEF_ADMIN_ABS_PATH . 'security/sh404SEF_IP_black_list.txt', $this->ipBlackList);
      shSaveFile(sh404SEF_ADMIN_ABS_PATH . 'security/sh404SEF_uAgent_white_list.txt', $this->uAgentWhiteList);
      shSaveFile(sh404SEF_ADMIN_ABS_PATH . 'security/sh404SEF_uAgent_black_list.txt', $this->uAgentBlackList);
      shSaveFile(sh404SEF_ADMIN_ABS_PATH . 'custom.sef.php',
      $quoteGPC ? stripslashes($this->defaultParamList) : $this->defaultParamList);

      // V 1.2.4.q : save copy of config file to other location for automatic recovering of config when upgrading
      $fd = fopen(sh404SEF_ABS_PATH.'media/sh404_upgrade_conf_'
      .str_replace('/','_',str_replace('http://', '', $GLOBALS['shConfigFrontLiveSite'])).'.php', "w");
      fwrite($fd, $config_data, strlen($config_data));
      fclose($fd);
      // save lists to backup location
      if (!is_writable(sh404SEF_ABS_PATH . 'media/sh404_upgrade_conf_security')) {
        @mkdir(sh404SEF_ABS_PATH . 'media/sh404_upgrade_conf_security');
      }
      shSaveFile(sh404SEF_ABS_PATH . 'media/sh404_upgrade_conf_security/sh404SEF_IP_white_list.txt',
      $this->ipWhiteList);
      shSaveFile(sh404SEF_ABS_PATH . 'media/sh404_upgrade_conf_security/sh404SEF_IP_black_list.txt',
      $this->ipBlackList);
      shSaveFile(sh404SEF_ABS_PATH . 'media/sh404_upgrade_conf_security/sh404SEF_uAgent_white_list.txt',
      $this->uAgentWhiteList);
      shSaveFile(sh404SEF_ABS_PATH . 'media/sh404_upgrade_conf_security/sh404SEF_uAgent_black_list.txt',
      $this->uAgentBlackList);
      shSaveFile(sh404SEF_ABS_PATH . 'media/sh404_upgrade_conf_'
      .str_replace('/','_',str_replace('http://', '', $GLOBALS['shConfigFrontLiveSite'])).'.custom.php',
      $quoteGPC ? stripslashes($this->defaultParamList) : $this->defaultParamList);
      return $ret;
    }
  }

  /**
   * Return array of URL characters to be replaced.
   * Copied from Artio Joomsef V 1.4.0
   *
   * @return array
   */
   
  function shGetReplacements()

  {
    // V 1.2.4.q : initialize variable
    static $shReplacements = null;
    if (isset($shReplacements)) return $shReplacements;
    $shReplacements = array();
    $items = explode(',', $this->shReplacements);
    foreach ($items as $item) {
      if (!empty($item)) {  // V 1.2.4.q better protection. Returns null array if empty
        @list($src, $dst) = explode('|', trim($item));
        $shReplacements[trim($src)] = trim($dst);
      }
    }

    return $shReplacements;
  }

  /**
   * Return array of URL characters to be replaced.
   * Copied from Artio Joomsef V 1.4.0
   *
   * @return array
   */
   
  function shGetStripCharList()

  {
    static $shStripCharList = null;
    if (is_null($shStripCharList)) {
      $shStripCharList = array();
      $shStripCharList = explode('|', $this->stripthese);
    }
    return $shStripCharList;
  }

  function set($var, $val) {

    if (isset($this->$var)) {
      $this->$var = $val;
      return true;
    }
    return false;
  }

  function version() {

    return $this->$version;
  }
}

// set of utility functions

function shSortURL($string) {
  // URL must be like : index.php?param2=xxx&option=com_ccccc&param1=zzz
  // URL returned will be ! index.php?option=com_ccccc&param1=zzz&param2=xxx
  $ret = '';
  $st = str_replace('&amp;', '&',$string);
  $st = str_replace('index.php', '', $st);
  $st = str_replace('?', '', $st);
  parse_str( $st,$shVars);
  if (count($shVars) > 0) {
    ksort($shVars);  // sort URL array
    $shNewString = '';
    $ret = 'index.php?';
    foreach ($shVars as $key => $value) {
      if (strtolower($key) != 'option') { // option is always first parameter
        if( is_array($value) ) {
          foreach($value as $k=>$v) {  // fix for arrays, thanks doorknob
            $shNewString .= '&'.$key.'[]='.$v;
          }
        } else {
          $shNewString .= '&'.$key.'='.$value;
        }
      } else {
        $ret .= $key.'='.$value;
      }
    }
    $ret .= $ret == 'index.php?' ? ltrim( $shNewString, '&') : $shNewString;
  }
  return $ret;
}

/**
 * Disable caching of Joomfish language selection module
 *
 * Caching would otherwise new SEF urls in non-default language to
 * be created.
 *
 */
function shDisableJFModuleCaching() {

  // load module data
  $db = & JFactory::getDBO();
  $query = "select * from #__modules where module='mod_jflanguageselection'";
  $db->setQuery( $query);
  $module = $db->loadObject();
  if (empty( $module)) {
    // joomfish module not here, do nothing
    return;
  }
  $params = new JParameter( $module->params );
  $cache_href = $params->get( 'cache_href');

  // set caching to false
  if ($cache_href != 0) {
    // change setting
    $params->set( 'cache_href', 0);
    $newParam = $params->toArray();
    // save these new params
    $row =& JTable::getInstance('module');
    $row->load( $module->id);
    $row->bind( array( 'params' => $newParam));
    $row->store();
    global $mainframe;
    $mainframe->enqueueMessage( _COM_SEF_JC_MODULE_CACHING_DISABLED);
  }
}

// returns found languages, but will check request language ($_GET or $_POST)
// and use that over user lang if it exists
// returns a lnguage code : en, fr, sp
function shDecideRequestLanguage() {

  $reqLang = JRequest::getVar( 'lang', '' );
  if( $reqLang != '' )
  $finalLang = $reqLang;
  else
  $finalLang = shDiscoverUserLanguage();
  return $finalLang;
}

/** The function finds the language which is to be used for the user/session
 *
 * It is possible to choose the language based on the client browsers configuration,
 * the activated language of the configuration and the language a user has choosen in
 * the past. The decision of this order is done in the JoomFish configuration.
 *
 * This is a modified copy of what's available in Joomfish system bot.
 * Returns a language code : en, fr, sp
 */

function shDiscoverUserLanguage() {

  $shCookieLang = shGetCookieLanguage();
  $userLang = empty( $shCookieLang) ? shGetParamUserLanguage() : $shCookieLang;
  return $userLang;
}

// returns language code (en, fr, sp after lookign up Joomfish params
// probably does not work with NokKaew
function shGetParamUserLanguage() {
  global $shMosConfig_lang, $shMosConfig_locale, $shMosConfig_shortcode, $_MAMBOTS;

  if (!shIsMultilingual())
  return $shMosConfig_shortcode;

  $database =& JFactory::getDBO();
  // check if param query has previously been processed
  /*
   if ( !isset($_MAMBOTS->_system_mambot_params['jfSystembot']) ) {
   // load mambot params info
   $query = "SELECT params"
   . "\n FROM #__mambots"
   . "\n WHERE element = 'jfdatabase.systembot'"
   . "\n AND folder = 'system'"
   ;
   $database->setQuery( $query );
   $mambot = $database->loadObject();

   // save query to class variable
   $_MAMBOTS->_system_mambot_params['jfSystembot'] = $mambot;
   }
   // pull query data from class variable
   $mambot = $_MAMBOTS->_system_mambot_params['jfSystembot'];

   $botParams = new mosParameters( $mambot->params );
   $determitLanguage 		= $botParams->def( 'determitLanguage', 1 );
   $newVisitorAction		= $botParams->def( 'newVisitorAction', "browser" );
   */
  $determitLanguage 		= 1;
  $newVisitorAction		= "browser";
  if ($newVisitorAction=="browser" && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {
    // no language chooses - assume from browser configuration
    // language negotiation by Kochin Chang, June 16, 2004
    // retrieve active languages from database
    $active_lang = null;
    $activeLanguages = shGetActiveLanguages();
    if( count( $activeLanguages ) == 0 ) {
      return $shMosConfig_shortcode;
    }
    foreach ($activeLanguages as $lang) {
      $active_lang[] = $lang->iso;
    }
    // figure out which language to use
    $browserLang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach( $browserLang as $lang ) {
      $shortLang = substr( $lang, 0, 2 );
      if( in_array($lang, $active_lang) ) {
        $client_lang = $lang;
        break;
      }
      if ( in_array($shortLang, $active_lang) ) {
        $client_lang = $shortLang;
        break;
      }
    }
    // if language is still blank then use first active language!
    if (empty($client_lang)) {
      $client_lang = $activeLanguages[0]->iso;
    }
  } elseif ($newVisitorAction=="joomfish"){
    // This list is ordered already!
    $activeLanguages = shGetActiveLanguages();
    if( count( $activeLanguages ) == 0 ) {
      return $shMosConfig_shortcode;
    }
    else {
      $client_lang = $activeLanguages[0]->iso;
    }
     
  } else {// otherwise default use site default language
    $activeLanguages = shGetActiveLanguages();
    if( count( $activeLanguages ) == 0 ) {
      return $shMosConfig_shortcode;
    }
    foreach ($activeLanguages as $lang) {
      if ($lang->code == $shMosConfig_locale){
        $client_lang = $lang->iso;
        break;
      }
    }
    // if language is still blank then use first active language!
    if ($client_lang==""){
      $client_lang = $activeLanguages[0]->iso;
    }
  }
  return $client_lang;
}

function shGetCookieLanguage() {

  $mbfcookie = JRequest::getVar( 'mbfcookie', null, 'COOKIE' );
  if (isset($mbfcookie["lang"]) && $mbfcookie["lang"] != "") {
    $lang = $mbfcookie["lang"];
  } else {
    $lang = '';
  }
  return $lang;
}

/**
 * Check if user session exists. Adapted from Joomla original code
 */
function shLookupSession() {

  global $mainframe;

  return false;  // does not work in 1.5. Not needed anyway, as long as multilingual 303 redirect is not solved

  $database =& JFactory::getDBO();
  // initailize session variables
  $session 	= new mosSession( $database );
  $option = strval( strtolower( JRequest::getVar( 'option' ) ) );
  $mainframe = new mosMainFrame( $database, $option, '.' );
  // purge expired sessions
  $session->purge('core');  // can't purge as $mainframe is not initialized yet
  // Session Cookie `name`
  // WARNING : I am using the Hack from
  $sessionCookieName 	= mosMainFrame::sessionCookieName();
  // Get Session Cookie `value`
  $sessioncookie 		= strval( JRequest::getVar( $sessionCookieName, null, 'COOKIE' ) );
  // Session ID / `value`
  $sessionValueCheck 	= mosMainFrame::sessionCookieValue( $sessioncookie );
  // Check if existing session exists in db corresponding to Session cookie `value`
  // extra check added in 1.0.8 to test sessioncookie value is of correct length
  $ret = false;
  if ( $sessioncookie && strlen($sessioncookie) == 32 && $sessioncookie != '-' && $session->load($sessionValueCheck) )
  $ret = true;
  unset($mainframe);
  return $ret;
}

// redirect user according to its language preference
function shGuessLanguageAndRedirect( $queryString) {

  if (!sh404SEF_DE_ACTIVATE_LANG_AUTO_REDIRECT
  && shIsMultilingual() == 'joomfish') {
    $cookieLang = shGetCookieLanguage();
    $sessionExists = shLookupSession();
    $reqLang = JRequest::getVar( 'lang', '' );
    $targetLang = '';
    if (!$sessionExists)  {  // no session and not coming from self
      if (empty($cookieLang)) {  // this is really first visit (or visitor does not accept cookie)
        $discoveredLang = shGetParamUserLanguage();
        if ( $discoveredLang != $reqLang)
        $targetLang = $discoveredLang;
      } else {  // returning visitor, with only a cookie set
        if ($cookieLang != $reqLang)
        $targetLang = $cookieLang;
      }
    }
    if (!empty($targetLang)) { // 303 redirect to same URL in preferred language
      $queryString = shSetURLVar( 'index.php?'.$queryString, 'lang', $targetLang);
      _log('Redirecting (303) to user language |cookie = '.$cookieLang. '|session='.$sessionExists.'|req='.$reqLang.'|target='.$targetLang);
      shRedirect( $GLOBALS['shConfigLiveSite'].'/'.$queryString, '', 303);
    }
  }

}

// 1.2.4.t 10/08/2007 12:17:37 return false if not multilingual
function shIsMultilingual() {
  global $mainframe;

  static $shIsMultiLingual = null;

  if (is_null( $shIsMultiLingual)) {
    $conf =& JFactory::getConfig();
    $shIsMultiLingual = !is_null( $conf->getValue( 'multilingual_support', null)) ? 'joomfish' : false;
  }
  return $shIsMultiLingual;

}

// 1.2.4.t 10/08/2007 12:17:37 return true if param is default language
function shIsDefaultLang( $langName) {

  return $langName == shGetDefaultLang();
}

// 1.2.4.t 10/08/2007 12:17:37 return true if param is default language
function shGetDefaultLang() {

  $type = shIsMultilingual();
  switch ($type) {
    case false:
      $shDefaultLang = $GLOBALS['shMosConfig_locale'];
      break;
    case 'joomfish':
      $conf =& JFactory::getConfig();
      $shDefaultLang = $conf->getValue( 'defaultlang');
      break;
  }
  return $shDefaultLang;
}


function shAdjustToRewriteMode( $url) {
  //$sefConfig = shRouter::shGetConfig();
  return $url;
}

function shFinalizeURL( $url) {
  $sefConfig = shRouter::shGetConfig();
  if (!empty($url) && (strpos($url, '/index.php?/') === false)) {  // V w 27/08/2007 13:38:34 sh_NetURL does not work if
    $URI = new sh_Net_URL($url);                       // using this rewrite mode as the added ? fools it if there is indeed
    if (!empty($URI->path)) {                          // a query string. Better not do anything
      $url = $URI->protocol.'://'.$URI->host.(!sh404SEF_USE_NON_STANDARD_PORT || empty($URI->port) ? '' : ':'.$URI->port);
      $url .= $sefConfig->shEncodeUrl ? shUrlEncode( $URI->path) :  $URI->path;
      if (count($URI->querystring) > 0) {
        $shTemp = '';
        foreach ($URI->querystring as $key=>$value) {
          if(is_array($value)) {  // array fix, thanks doorknob
            foreach( $value as $k=>$v) {
              $shTemp .= '&'.$key.'[]='.($sefConfig->shEncodeUrl ? shUrlEncode($v) : $v);
            }
          } else {
            $shTemp .= '&'.$key.'='.($sefConfig->shEncodeUrl ? shUrlEncode($value) : $value);
          }
        }

        $shTemp = ltrim( $shTemp, '&');  // V x 02/09/2007 21:17:19
        $url .= '?'. $shTemp;  // V x 02/09/2007 21:17:24
      }
      if ($URI->anchor)
      $url .= '#'.($sefConfig->shEncodeUrl ? shUrlEncode($URI->anchor) : $URI->anchor);
    }
  }
  // V 1.2.4.s hack to workaround Virtuemart/SearchEngines issue with cookie check
  // V 1.2.4.t fixed bug, was checking for vmcchk instead of vmchk
  if (shIsSearchEngine() && (strpos( $url, 'vmchk') !== false)) {
    $url = str_replace('vmchk/', '', $url);  // remove check,
    //cookie will be forced if user agent is searchengine
  }
  $url = shAdjustToRewriteMode ($url);
  //str_replace('&', '&amp;', $url); // V 1.2.4.t XHTML validation // J 1.5 does it already
  $url = str_replace('&amp;', '&;', $url);  // when Joomla wil turn that into &amp; we are sur we won't have &amp;amp;
  return $url;
}

// V 1.2.4.p compatibility function with SEFAdvance
function sefencode( $string) {
  return titleToLocation( $string);
}

function titleToLocation(&$title)
{
  $sefConfig = shRouter::shGetConfig();
  $title = trim($title);
  $debug = 0;
  if ($debug) $t[] = $title;
  $shRep = $sefConfig->shGetReplacements();
  if (!empty($shRep))
  $title = strtr($title, $shRep);
  if ($debug) $t[] = $title;
  $shStrip = $sefConfig->shGetStripCharList();
  if (!empty($shStrip))
  $title = str_replace( $shStrip, '', $title);
  if ($debug) $t[] = $title;
  // V 1.2.4.t remove spaces
  $title = preg_replace( '/[\s]+/iU', $sefConfig->replacement, $title);
  if ($debug) $t[] = $title;
  $title = str_replace('\'', $sefConfig->replacement, $title);
  $title = str_replace('"', $sefConfig->replacement, $title);
  // V x strip # as it breaks anchor management
  $title = str_replace('#', $sefConfig->replacement, $title);
  // V u - 26/08/2007 10:26:58 remove question marks
  $title = str_replace('?', $sefConfig->replacement, $title);
  if ($debug) $t[] = $title;
  $title = str_replace('\\', $sefConfig->replacement, $title);
  if ($debug) $t[] = $title;
  // V 1.2.4.t remove duplicate replacement chars
  if (!empty($sefConfig->replacement))  // V x protect/allow empty
  $title = preg_replace('/'.preg_quote($sefConfig->replacement).'{2,}/', $sefConfig->replacement, $title);
  if ($debug) $t[] = $title;
  $title = trim( $title, str_replace('|', '', $sefConfig->friendlytrim));  // V 1.2.4.t add SEF URL trimming of user set characters
  $title = $sefConfig->LowerCase ? strtolower($title) : $title;  // V w 27/08/2007 13:11:48
  if ($debug) $t[] = $title;
  if ($debug && strpos($t[0], '\'') !== false) {
    var_dump($t);
    die();
  }
  return $title;
}

// V x utility 01/09/2007 22:18:55 function to remove mosmsg var from url
function shCleanUpMosMsg( $string) {
  return preg_replace( '/(&|\?)mosmsg=[^&]*/i', '', $string);
}

// V x utility  function to remove a variable from an URL
function shCleanUpVar( $string, $var) {
  return preg_replace( '/(&|\?)'.preg_quote($var).'=[^&]*/i', '', $string);
}

// V x utility 01/09/2007 22:18:55 function to return mosmsg var from url
function shGetMosMsg( $string) {
  $matches = array();
  $result = preg_match( '/(&|\?)mosmsg=[^&]*/i', $string, $matches);
  if (!empty($matches))
  return trim( $matches[0], '&?');
  else return '';
}

// V x utility function to return lang var from url
function shGetURLLang( $string) {
  $matches = array();
  $string = str_replace('&amp;', '&', $string); // normalize
  $result = preg_match( '/(&|\?)lang=[^&]*/i', $string, $matches);
  if (!empty($matches)) {
    $result = trim( $matches[0], '&?');
    $result = str_replace('lang=', '', $result);
    return shGetNameFromIsoCode($result);
  }
  else return '';
}

// V x utility function to return a var from url
function shGetURLVar( $string, $var) {
  $matches = array();
  $string = str_replace('&amp;', '&', $string); // normalize
  $result = preg_match( '/(&|\?)'.preg_quote($var).'=[^&]*/i', $string, $matches);
  if (!empty($matches)) {
    $result = trim( $matches[0], '&?');
    $result = str_replace($var.'=', '', $result);
    return $result;
  }
  else return '';
}

// V x utility function to set  a var in an url
function shSetURLVar( $string, $var, $value) {
  if (empty( $string) || empty($var) || empty($value)) return $string;
  $string = str_replace('&amp;', '&', $string); // normalize
  $exp = '/(&|\?)'.preg_quote($var).'=[^&]*/i';
  $result = preg_match( $exp, $string);
  if ($result)  // var already in URL
  $result = preg_replace( $exp, '$1'.$var.'='.$value, $string);
  else {  // var does not exist in URL
    $result = $string.(strpos( $string, '?') !== false ? '&':'?').$var.'='.$value;
    $result = shSortURL($result);
  }
  return $result;
}

// V 1.2.4.q utility function to clean language and pagination info from url
function shCleanUpPag( $string) {
  $shTempString = preg_replace( '/(&|\?)limit=[^&]*/i', '', $string);
  $shTempString = preg_replace( '/(&|\?)limitstart=[^&]*/i', '', $shTempString);
  return $shTempString;
}

// V 1.2.4.t utility function to clean language from url
function shCleanUpLang( $string) {
  return preg_replace( '/(&|\?)lang=[a-zA-Z]{2}/iU', '', $string);
}

// V 1.2.4.q utility function to clean language and pagination info from url
function shCleanUpLangAndPag( $string) {
  $shTempString = shCleanUpLang( $string);
  $shTempString = shCleanUpPag($shTempString);
  return $shTempString;
}

// V 1.2.4.t utility function to clean anchor from url
function shCleanUpAnchor( $string) {
  $bits = explode('#', $string);
  return $bits[0];
}


// V 1.2.4.t
function shIncludeLanguageFile() {
  if (defined( '_COM_SEF_SH_REDIR_404')) return;
  if (shFileExists(sh404SEF_ADMIN_ABS_PATH.'language/'.$GLOBALS['shMosConfig_lang'].'.php')) {
    include_once(sh404SEF_ADMIN_ABS_PATH.'language/'.$GLOBALS['shMosConfig_lang'].'.php');
  }
  else {
    include_once(sh404SEF_ADMIN_ABS_PATH.'language/english.php');
  }
}


function shGETGarbageCollect() {  // V 1.2.4.m moved to main component from plugins
  // builds up a string using all remaining GET parameters, to be appended to the URL without any sef transformation
  // those variables passed litterally must be removed from $string as well, so that they are not stored in DB
  global $shGETVars;
  $sefConfig = shRouter::shGetConfig();
  if (!$sefConfig->shAppendRemainingGETVars || empty($shGETVars)) return '';
  $ret = '';
  ksort($shGETVars);
  foreach ($shGETVars as $param => $value) {
    if( is_array($value) ) {
      foreach($value as $k=>$v) {
        $ret .= '&'.$param.'[]='.$v;
      }
    } else {
      $ret .= '&'.$param.'='.$value;
    }

  }
  return $ret;
}

function shRebuildNonSefString( $string) { // V 1.2.4.m moved to main component from plugins
  // rebuild a non-sef string, removing all GET vars that were not turned into SEF
  // as we do not want to store them in DB

  global $shRebuildNonSef;
  $sefConfig = & shRouter::shGetConfig();
  if (!$sefConfig->shAppendRemainingGETVars || empty($shRebuildNonSef)) return $string;
  $shNewString = '';
  if (!empty($shRebuildNonSef)) {
    foreach ($shRebuildNonSef as $param) {  // need to sort, and still place option in first pos.
      if (strpos($param, 'sh404SEF_title=') !== false)
      $param = str_replace('sh404SEF_title=', 'title=', $param);
      $shNewString .= $param;
    }
    $ret = shSortUrl('index.php?'.ltrim( $shNewString, '&'));
  }
  return $ret;
}


function shRemoveFromGETVarsList( $paramName) {
  global $shGETVars, $shRebuildNonSef;

  $sefConfig = shRouter::shGetConfig();
  if (!$sefConfig->shAppendRemainingGETVars) return null;
  if (!empty($paramName)) {
    if (isset($shGETVars[$paramName])) {
      $shValue = $shGETVars[$paramName];
      $shRebuildNonSef[] = '&'.$paramName.'='.$shValue;  // build up a non-sef string with the GET vars used to
      // build the SEF string. This string will be the one stored in db instead of
      // the full, original one
      unset( $shGETVars[@$paramName]);
    }
  }
}

function shAddToGETVarsList( $paramName, $paramValue) {  // V 1.2.4.m
  global $shGETVars, $shRebuildNonSef;
  if (empty( $paramName)) return;
  $shGETVars[$paramName] = $paramValue;
}

function shFinalizePlugin( $string, $title, &$shAppendString, $shItemidString,
$limit, $limitstart, $shLangName, $showall = null) { // V 1.2.4.s
  global $shGETVars;
  if (!empty($shItemidString))
  $title[] = $shItemidString; // V 1.2.4.m
  // stitch back additional parameters, not sef-ified
  $shAppendString .= shGETGarbageCollect();  // add automatically all GET variables that had not been used already
  if (!empty($shAppendString))
  $shAppendString = '?'.ltrim( $shAppendString, '&'); // don't add to $string, otherwise it will be stored in the DB
  return sef_404::sefGetLocation( shRebuildNonSefString( $string), $title, null,
  (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null),
  (isset($shLangName) ? @$shLangName : null),
  (isset($showall) ? @$showall : null)
  );
}

function shInitializePlugin($lang, &$shLangName, &$shLangIso, $option) {
  global $shMosConfig_lang, $shMosConfig_locale;

  $conf	=& JFactory::getConfig();
  $configDefaultLanguage = $conf->getValue('config.language');
   
  $shLangName = empty($lang) ? $shMosConfig_locale : shGetNameFromIsoCode( $lang);
  $shLangIso = (shTranslateUrl($option, $shLangName)) ?
  (isset($lang) ? $lang : shGetIsoCodeFromName( $shMosConfig_locale))
  : (isset($configDefaultLanguage) ? shGetIsoCodeFromName($configDefaultLanguage) : shGetIsoCodeFromName( $shMosConfig_locale));
  if (strpos($shLangIso, '_') !== false) {   //11/08/2007 14:30:16 mambo compat
    $shTemp = explode( '_', $shLangIso);
    $shLangIso = $shTemp[0];
  }
  // added protection : do not SEF if component is not installed. Do not attempt to build SEF URL
  // if component is not installed, or else plugin may try to read from comp DB tables. This will cause DB table names
  // to be displayed
  return !sh404SEF_CHECK_COMP_IS_INSTALLED
  || ( sh404SEF_CHECK_COMP_IS_INSTALLED &&
  shFileExists(sh404SEF_ABS_PATH.'components/'.$option.'/'.str_replace('com_', '',$option).'.php'));
}

function shLoadPluginLanguage ( $pluginName, $language, $defaultString) {  // V 1.2.4.m
  global $sh_LANG;
  // load the Language File
  if (shFileExists( sh404SEF_ADMIN_ABS_PATH.'language/plugins/'.$pluginName.'.php' )) {
    include_once( sh404SEF_ADMIN_ABS_PATH.'language/plugins/'.$pluginName.'.php' );
  }
  else JError::RaiseError( 500, 'sh404SEF - missing language file for plugin '.$pluginName.'. Cannot continue.');

  if (!isset($sh_LANG[$language][$defaultString]))
  return 'en';
  else return $language;
}

function shInsertIsoCodeInUrl($compName, $shLang = null) {  // V 1.2.4.m
  global $shMosConfig_lang, $shMosConfig_locale;
  $sefConfig = & shRouter::shGetConfig();

  $shLang = empty($shLang) ? $shMosConfig_locale : $shLang;  // V 1.2.4.q
  if (empty($compName) || !$sefConfig->shInsertLanguageCode  // if no compname or global param is off
  || $sefConfig->shLangInsertCodeList[$shLang] == 2  // set to not insertcode
  || ( $sefConfig->shLangInsertCodeList[$shLang] == 0 && shGetDefaultlang() == $shLang) // or set to default
  )  // but this is default language
  return false;
  $compName = str_replace('com_', '', $compName);
  return !in_array($compName, $sefConfig->notInsertIsoCodeList);
}

function shTranslateUrl ($compName, $shLang = null) {  // V 1.2.4.m  // V 1.2.4.q added $shLang param
  global $shMosConfig_lang, $shMosConfig_locale;

  $sefConfig = & shRouter::shGetConfig();

  $shLang = empty($shLang) ? $shMosConfig_locale : $shLang;
  if (empty($compName) || !$sefConfig->shTranslateURL
  || $sefConfig->shLangTranslateList[$shLang] == 2 ) // set to not translate
  return false;
  $compName = str_replace('com_', '', $compName);
  $result = !in_array($compName, $sefConfig->notTranslateURLList);
  return $result;
}

// V 1.2.4.q returns true if current page is home page.
function shIsCurrentPageHome() {
  global $option, $shHomeLink;

  $currentPage = shSortUrl( preg_replace( '/(&|\?)lang=[a-zA-Z]{2,3}/iU', '', empty($_SERVER['QUERY_STRING']) ? '' : $_SERVER['QUERY_STRING'])); // V 1.2.4.t
  $currentPage = ltrim( str_replace('index.php', '', $currentPage), '/');
  $currentPage = ltrim( $currentPage, '?');
  $shHomePage = preg_replace( '/(&|\?)lang=[a-zA-Z]{2,3}/iU', '', $shHomeLink);
  $shHomePage = ltrim( str_replace('index.php', '', $shHomePage), '/');
  $shHomePage = ltrim( $shHomePage, '?');
  return  $currentPage == $shHomePage;
}

function shUrlEncode( $path) {
  $ret = $path;
  if (!empty($path)) {
    $bits = explode('/', $path);
    $enc = array();
    if (count($bits)) {
      foreach ($bits as $key=>$value) {
        $enc[$key] = rawurlencode($value);
      }
      $ret = implode($enc,'/');
    }
  }
  return $ret;
}
function shUrlDecode( $path) {
  $ret = $path;
  if (!empty($path)) {
    $bits = explode('/', $path);
    $dec = array();
    if (count($bits)) {
      foreach ($bits as $key=>$value) {
        $dec[$key] = rawurldecode($value);
      }
      $ret = implode($dec,'/');
    }
  }
  return $ret;
}

// returns default items per page from menu items params. menu item selected by its id taken from a URL
function shGetDefaultDisplayNumFromURL($url) {
   
  $menuItemid = shGetURLVar($url, 'Itemid');
  return shGetDefaultDisplayNum($menuItemid, $url);
}

// returns default items per page from menu items params. menu item selected by its id taken from a URL
function shGetDefaultDisplayNum($menuItemid, $url) {
  global $mainframe;
  $listLimit = $mainframe->getCfg( 'list_limit', 10 );
  $ret = $listLimit; // defaults to site default items per page value
  if (empty($menuItemid) || empty( $url)) return $ret;  // no itemid
  // now handle special cases
  if ( (strpos( $url, 'option=com_content') !== false && strpos( $url, 'layout=blog') !== false)
  || (strpos( $url, 'option=com_content') !== false && strpos( $url, 'view=frontpage') !== false)
  ) {
    $menu = & shRouter::shGetMenu();
    $menuItem = $menu->getItem($menuItemid);  // load menu item from DB
    if (empty($menuItem)) return $ret;  // if none, default
    $params = new JParameter( $menuItem->params );  // get params from menu item
    $num_leading_articles = $params->get('num_leading_articles');
    $num_intro_articles = $params->get('num_intro_articles');
    $ret = $num_leading_articles + $num_intro_articles;  // calculate how many items on a page
  }
  return $ret;
}

function getSefUrlFromDatabase($url, &$sefString)  // V 1.2.4.t
{
  $database =& JFactory::getDBO();
  $query = "SELECT oldurl, dateadd FROM #__redirection WHERE newurl = '".$database->getEscaped($url)."'";
  $database->setQuery($query); // 10/08/2007 22:10:05 mambo compat
  if ($result = $database->loadObject()) {
    $sefString = $result->oldurl;
    if (empty($result->oldurl))
    return sh404SEF_URLTYPE_404;
    return $result->dateadd == '0000-00-00' ? sh404SEF_URLTYPE_AUTO : sh404SEF_URLTYPE_CUSTOM;
  } else
  return sh404SEF_URLTYPE_NONE;
}

// V 1.2.4.t check both cache and DB
function shGetSefURLFromCacheOrDB($string, &$sefString) {
  $sefConfig = shRouter::shGetConfig();
  if (empty($string)) return sh404SEF_URLTYPE_NONE;
  $sefString = '';
  $urlType = sh404SEF_URLTYPE_NONE;
  if ($sefConfig->shUseURLCache)
  $urlType = shGetSefURLFromCache($string, $sefString);
  // Check if the url is already saved in the database.
  if ($urlType == sh404SEF_URLTYPE_NONE) {
    $urlType = getSefUrlFromDatabase($string, $sefString);
    if ($urlType == sh404SEF_URLTYPE_NONE || $urlType == sh404SEF_URLTYPE_404)
    return $urlType;
    else {
      if ($sefConfig->shUseURLCache) {
        shAddSefURLToCache( $string, $sefString, $urlType);
      }
    }
  }
  return $urlType;
}

// add URL to DB and cache. URL must no exists, this is insert, not update
function shAddSefUrlToDBAndCache( $nonSefUrl, $sefString, $rank, $urlType) {

  $database =& JFactory::getDBO();
  $sefString = ltrim( $sefString, '/'); // V 1.2.4.t just in case you forgot to remove leading slash
  switch ($urlType) {
    case sh404SEF_URLTYPE_AUTO :
      $dateAdd = '0000-00-00';
      break;
    case sh404SEF_URLTYPE_CUSTOM :
      $dateAdd = date("Y-m-d");
      break;
    case sh404SEF_URLTYPE_NONE :
      return null;
      break;
  }
  $query = '';
  if ($urlType == sh404SEF_URLTYPE_AUTO) {  // before adding a full sef, we must check it does not already exists as a 404
    $query = 'SELECT id FROM #__redirection where oldurl=\''.$sefString.'\' AND newurl = \'\';';
    _log('Querying for 404 : '.$query);
    $database->setQuery($query);
    $result = $database->loadObject(); // instead of inserting, we must update this 404 record
    if (!empty($result))
    $query = 'UPDATE #__redirection SET '.  // V 1.2.4.q
    		"newurl='".addslashes(urldecode($nonSefUrl))."', rank='".$rank."', dateadd='".$dateAdd.'\' '
    		."WHERE oldurl = '".$sefString."';";
    		else $query = '';
  }
  if (empty($query)) {
    $query = "INSERT INTO #__redirection (oldurl, newurl, rank, dateadd) ".  // V 1.2.4.q
    	"VALUES ('".$sefString."', '".addslashes(urldecode($nonSefUrl))."', '".$rank."', '".$dateAdd."')";  // V 1.2.4.q
  }
  _log('Querying to insert/update sef record : '.$query);
  $database->setQuery($query);
  if (!$database->query()) {
    _log('Bad query '. $query);
  }
  // shumisha 2007-03-13 added URL caching, need to store this new URL
  shAddSefURLToCache( $nonSefUrl, $sefString, $urlType);

}

// V 1.2.4.t build up a string with a page number
function shBuildPageNumberString( $pagenum) {
  $sefConfig = shRouter::shGetConfig();

  if ($sefConfig->pagetext && (false !== strpos($sefConfig->pagetext, '%s'))){
  		return str_replace('%s', $pagenum, $sefConfig->pagetext);
  } else {
  		return $pagenum;
  }
}

function shReadFile($shFileName, $asString = false){
  $ret = array();
  if (is_readable($shFileName)) {
    $shFile = fOpen($shFileName, 'r');
    do {
      $shRead = fgets($shFile,1024);
      if (!empty($shRead) && substr($shRead, 0, 1) != '#') $ret[] = trim(stripslashes($shRead));
    }
    while (!feof($shFile));
    fclose($shFile);
  }
  if ($asString)
  $ret = implode("\n", $ret);
  return $ret;
}

function shSaveFile($shFileName, $fileData){
  if (empty($shFileName)) return;
  $fileIsThere = file_exists($shFileName);
  if (!$fileIsThere || ($fileIsThere && is_writable($shFileName))) {
  		$dataFile=fopen( $shFileName,'wb');
  		if ($dataFile) {
   			if (is_array($fileData)) {
   			  $fileData = implode("\n",$fileData); //make sure we write a string
   			}
   			fWrite( $dataFile, empty($fileData) ? '':$fileData);
   			fClose( $dataFile);
  		}
  }
}

// shumisha utility function to obtain iso code from language name
function shGetIsoCodeFromName($langName) {
  global $shIsoCodeCache, $shMosConfig_lang, $shMosConfig_locale, $shMosConfig_shortcode;

  $database =& JFactory::getDBO();
  if (!isset( $shIsoCodeCache[$langName])) {
    $type = shIsMultilingual();
    if ($type != false) {
      if ($type == 'joomfish') {
        $select = 'iso, shortcode, code';
      }
      $query = 'SELECT '.($type == 'joomfish' ? $select :'mambo,name')
      .' FROM '.($type == 'joomfish' ? '#__languages':'#__nok_language').' WHERE 1';
      $database->setQuery($query);
      $rows = $database->loadObjectList();
      foreach ($rows as $row) {
        if ($type == 'joomfish')
      		$jfIsoCode = empty($row->shortcode) ? $row->iso:$row->shortcode;
      		$shIsoCodeCache[($type == 'joomfish' ? $row->code:$row->name)] = ($type == 'joomfish' ? $jfIsoCode:$row->mambo);
      }
    } else { // no joomfish, so it has to be default language
      $langName = $shMosConfig_locale;
      $shIsoCodeCache[$shMosConfig_locale] = $shMosConfig_shortcode;
    }
  }
  return empty($shIsoCodeCache[$langName]) ? 'en' : $shIsoCodeCache[$langName];
}

// shumisha utility function to obtain language name from iso code
function shGetNameFromIsoCode($langCode) {
  global $shLangNameCache, $shMosConfig_lang, $shMosConfig_locale, $shLangNameCache;

  $database =& JFactory::getDBO();
  if (empty( $shLangNameCache)) {
    $type = shIsMultilingual();
    if ($type !== false) {
      if ($type == 'joomfish') {
        $select = 'iso, shortcode, code';
      }
      $query = 'SELECT '.($type == 'joomfish' ? $select:'mambo, name')
      .' FROM '.($type == 'joomfish' ? '#__languages':'#__nok_language').' WHERE 1';
      $database->setQuery($query);
      $rows = $database->loadObjectList();
      foreach ($rows as $row) {
        if ($type == 'joomfish')
      		$jfIsoCode = empty($row->shortcode) ? $row->iso:$row->shortcode;
        $shLangNameCache[($type == 'joomfish' ? $jfIsoCode:$row->mambo)] = ($type == 'joomfish' ? $row->code:$row->name);
      }
      return empty($shLangNameCache[$langCode]) ? $shMosConfig_locale : $shLangNameCache[$langCode];
    } else { // no joomfish, so it has to be default language
      return $shMosConfig_locale;
    }
  } else return empty($shLangNameCache[$langCode]) ? $shMosConfig_locale : $shLangNameCache[$langCode];
}

/**
 * Get list of front-end available langauges
 *
 * @return unknown
 */
function shGetFrontEndActiveLanguages() {

  $shLangs = array();
  // Initialize some variables
  $client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

  //load folder filesystem class
  jimport('joomla.filesystem.folder');
  $path = JLanguage::getLanguagePath($client->path);
  $dirs = JFolder::folders( $path );

  foreach ($dirs as $dir) {
    //$files = JFolder::files( $path.DS.$dir, '^([-_A-Za-z]*)\.xml$' );
    $files = JFolder::files( $path.DS.$dir, '^([A-Za-z]{2}-[A-Za-z]{2})\.xml$' ); // some languages may add other xml files
    // Read the file to see if it's a valid component XML file
    $xml = & JFactory::getXMLParser('simple');

    foreach ($files as $file) {
      if (!$xml->loadFile($path.DS.$dir.DS.$file)) {
        unset($xml);
        continue;
      }
      if (is_object( $xml->document) && $xml->document->name() != 'metafile') {
        unset($xml);
        continue;
      }
      $shLang = new StdClass();
      $element = & $xml->document->metadata[0];
      $subElem = $element->tag[0];
      $shTemp = explode( '-', $subElem->data());
      $shLang->iso = $shTemp[0] ? $shTemp[0] : 'en';
      if (!empty($element->backwardLang)) {
        $subLang = $element->backwardLang[0];
      } else {
        $subLang = $element->backwardlang[0];  // some language files have backwardlang instead of backwardLang
      }
      $subLang = $element->tag[0];
      $shLang->code = $subLang->data();
      $shLangs[] = $shLang;
    }
  }
  return $shLangs;
}

// utility function to return list of available languages / isolate from JFish/Nokkaew compat issues
function shGetActiveLanguages() {

  global $mainframe;

  static $shActiveLanguages = null;  // cache this, to reduce DB queries
  if (!is_null($shActiveLanguages))
  return $shActiveLanguages;
   
  $shKind = shIsMultilingual();
  if ($shKind == 'joomfish') {
    $tempList = JoomFishManager::getActiveLanguages();
    if (!empty($tempList)) {
      foreach ($tempList as $language) {
        $shLang = null;
        $shLang->code = $language->code;
        $shLang->iso = $language->shortcode;
        $shActiveLanguages[] = $shLang;
      }
    } else $shKind = '';
  }
  if (empty($shKind)) {  // not multilingual
    $shActiveLanguages = shGetFrontEndActiveLanguages();
  }
  return $shActiveLanguages;
}

// returns prefix for $option component, as per user settings
function shGetComponentPrefix( $option) {

  if (empty($option)) return '';
  $sefConfig = shRouter::shGetConfig();
  $option = str_replace('com_', '', $option);
  $prefix = '';
  $prefix = empty($sefConfig->defaultComponentStringList[@$option]) ?
		'':$sefConfig->defaultComponentStringList[@$option];
  return $prefix;
}

function shRedirect( $url, $msg='', $redirKind = '301', $msgType='message' ) {

  global $mainframe;
  $sefConfig = & shRouter::shGetConfig();

  // specific filters
  if (class_exists('InputFilter')) {
    $iFilter = new InputFilter();
    $url = $iFilter->process( $url );
    if (!empty($msg)) {
      $msg = $iFilter->process( $msg );
    }

    if ($iFilter->badAttributeValue( array( 'href', $url ))) {
      $url = $GLOBALS['shConfigLiveSite'];
    }
  }

  // If the message exists, enqueue it
  if (trim( $msg )) {
    $mainframe->enqueueMessage($msg, $msgType);
  }

  // Persist messages if they exist
  if (count($mainframe->_messageQueue))
  {
    $session =& JFactory::getSession();
    $session->set('application.queue', $mainframe->_messageQueue);
  }

  if (headers_sent()) {
    echo "<script>document.location.href='$url';</script>\n";
  } else {
    @ob_end_clean(); // clear output buffer
    switch ($redirKind) {
      case '302':
        $redirHeader ='HTTP/1.1 302 Moved Temporarily';
        break;
      case '303':
        $redirHeader ='HTTP/1.1 303 See Other';
        break;
      default:
        $redirHeader = 'HTTP/1.1 301 Moved Permanently';
        break;
    }
    header( $redirHeader );
    header( "Location: ". $url );
  }
  $mainframe->close();
}

// Net/Url.php From the PEAR Library (http://pear.php.net/package/sh_Net_URL)
// +-----------------------------------------------------------------------+
// | Copyright (c) 2002-2004, Richard Heyes                                |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.|
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Richard Heyes <richard at php net>                            |
// +-----------------------------------------------------------------------+
//
// $Id: sh404sef.class.php 941 2009-06-07 07:38:43Z silianacom-svn $
//
// sh_Net_URL Class
class sh_Net_URL
{
  /**
   * Full url
   * @var string
   */
  var $url;
  /**
   * Protocol
   * @var string
   */
  var $protocol;
  /**
   * Username
   * @var string
   */
  var $username;
  /**
   * Password
   * @var string
   */
  var $password;
  /**
   * Host
   * @var string
   */
  var $host;
  /**
   * Port
   * @var integer
   */
  var $port;
  /**
   * Path
   * @var string
   */
  var $path;
  /**
   * Query string
   * @var array
   */
  var $querystring;
  /**
   * Anchor
   * @var string
   */
  var $anchor;
  /**
   * Whether to use []
   * @var bool
   */
  var $useBrackets;
  /**
   * PHP4 Constructor
   *
   * @see __construct()
   */
  function sh_Net_URL($url = null, $useBrackets = true)
  {
    $this->__construct($url, $useBrackets);
  }
  /**
   * PHP5 Constructor
   *
   * Parses the given url and stores the various parts
   * Defaults are used in certain cases
   *
   * @param string $url         Optional URL
   * @param bool   $useBrackets Whether to use square brackets when
   *                            multiple querystrings with the same name
   *                            exist
   */
  function __construct($url = null, $useBrackets = true)
  {
    $HTTP_SERVER_VARS  = !empty($_SERVER) ? $_SERVER : $GLOBALS['HTTP_SERVER_VARS'];
    $this->useBrackets = $useBrackets;
    $this->url         = $url;
    $this->user        = '';
    $this->pass        = '';
    $this->host        = '';
    $this->port        = 80;
    $this->path        = '';
    $this->querystring = array();
    $this->anchor      = '';
    // Only use defaults if not an absolute URL given
    if (!preg_match('/^[a-z0-9]+:\/\//i', $url)) {
      $this->protocol    = (isset ($HTTP_SERVER_VARS['HTTPS']) ?
      (@$HTTP_SERVER_VARS['HTTPS'] == 'on' ? 'https' : 'http') : 'http');
      /**
       * Figure out host/port
       */
      if (!empty($HTTP_SERVER_VARS['HTTP_HOST']) AND preg_match('/^(.*)(:([0-9]+))?$/U', $HTTP_SERVER_VARS['HTTP_HOST'], $matches)) {
        $host = $matches[1];
        if (!empty($matches[3])) {
          $port = $matches[3];
        } else {
          $port = $this->getStandardPort($this->protocol);
        }
      }
      $this->user        = '';
      $this->pass        = '';
      $this->host        = !empty($host) ? $host : (isset($HTTP_SERVER_VARS['SERVER_NAME']) ? $HTTP_SERVER_VARS['SERVER_NAME'] : 'localhost');
      $this->port        = !empty($port) ? $port : (isset($HTTP_SERVER_VARS['SERVER_PORT']) ? $HTTP_SERVER_VARS['SERVER_PORT'] : $this->getStandardPort($this->protocol));
      $this->path        = !empty($HTTP_SERVER_VARS['PHP_SELF']) ? $HTTP_SERVER_VARS['PHP_SELF'] : '/';
      $this->querystring = isset($HTTP_SERVER_VARS['QUERY_STRING']) ? $this->_parseRawQuerystring($HTTP_SERVER_VARS['QUERY_STRING']) : null;
      $this->anchor      = '';
    }
    // Parse the url and store the various parts
    if (!empty($url)) {
      $urlinfo = parse_url($url);
      // Default querystring
      $this->querystring = array();
      foreach ($urlinfo as $key => $value) {
        switch ($key) {
          case 'scheme':
            $this->protocol = $value;
            $this->port     = $this->getStandardPort($value);
            break;
          case 'user':
          case 'pass':
          case 'host':
          case 'port':
            $this->$key = $value;
            break;
          case 'path':
            if ($value{0} == '/') {
              $this->path = $value;
            } else {
              $path = dirname($this->path) == DIRECTORY_SEPARATOR ? '' : dirname($this->path);
              $this->path = sprintf('%s/%s', $path, $value);
            }
            break;
          case 'query':
            $this->querystring = $this->_parseRawQueryString($value);
            break;
          case 'fragment':
            $this->anchor = $value;
            break;
        }
      }
    }
  }

  /**
   * Returns full url
   *
   * @return string Full url
   * @access public
   */
  function getURL()
  {
    $querystring = $this->getQueryString();
    $this->url = $this->protocol . '://'
    . $this->user . (!empty($this->pass) ? ':' : '')
    . $this->pass . (!empty($this->user) ? '@' : '')
    . $this->host . ($this->port == $this->getStandardPort($this->protocol) ? '' : ':' . $this->port)
    . $this->path
    . (!empty($querystring) ? '?' . $querystring : '')
    . (!empty($this->anchor) ? '#' . $this->anchor : '');
    return $this->url;
  }
  /**
   * Adds a querystring item
   *
   * @param  string $name       Name of item
   * @param  string $value      Value of item
   * @param  bool   $preencoded Whether value is urlencoded or not, default = not
   * @access public
   */
  function addQueryString($name, $value, $preencoded = false)
  {
    if ($preencoded) {
      $this->querystring[$name] = $value;
    } else {
      $this->querystring[$name] = is_array($value) ? array_map('rawurlencode', $value): rawurlencode($value);
    }
  }
  /**
   * Removes a querystring item
   *
   * @param  string $name Name of item
   * @access public
   */
  function removeQueryString($name)
  {
    if (isset($this->querystring[$name])) {
      unset($this->querystring[$name]);
    }
  }
  /**
   * Sets the querystring to literally what you supply
   *
   * @param  string $querystring The querystring data. Should be of the format foo=bar&x=y etc
   * @access public
   */
  function addRawQueryString($querystring)
  {
    $this->querystring = $this->_parseRawQueryString($querystring);
  }
  /**
   * Returns flat querystring
   *
   * @return string Querystring
   * @access public
   */
  function getQueryString()
  {
    static $argSeparator = null;

    if (is_null( $argSeparator)) {
      $argSeparator = ini_get('arg_separator.output');
    }

    if (!empty($this->querystring)) {
      foreach ($this->querystring as $name => $value) {
        if (is_array($value)) {
          foreach ($value as $k => $v) {
            $querystring[] = $this->useBrackets ? sprintf('%s[%s]=%s', $name, $k, $v) : ($name . '=' . $v);
          }
        } elseif (!is_null($value)) {
          $querystring[] = $name . '=' . $value;
        } else {
          $querystring[] = $name;
        }
      }
      $querystring = implode( $argSeparator, $querystring);
    } else {
      $querystring = '';
    }
    return $querystring;
  }
  /**
   * Parses raw querystring and returns an array of it
   *
   * @param  string  $querystring The querystring to parse
   * @return array                An array of the querystring data
   * @access private
   */
  function _parseRawQuerystring($querystring)
  {
    static $argSeparator = null;

    if (is_null( $argSeparator)) {
      $argSeparator = preg_quote(ini_get('arg_separator.input'), '/');
    }
    $parts  = preg_split('/[' . $argSeparator . ']/', $querystring, -1, PREG_SPLIT_NO_EMPTY);
    $return = array();
    foreach ($parts as $part) {
      if (strpos($part, '=') !== false) {
        $value = substr($part, strpos($part, '=') + 1);
        $key   = substr($part, 0, strpos($part, '='));
      } else {
        $value = null;
        $key   = $part;
      }
      if (substr($key, -2) == '[]') {
        $key = substr($key, 0, -2);
        if (@!is_array($return[$key])) {
          $return[$key]   = array();
          $return[$key][] = $value;
        } else {
          $return[$key][] = $value;
        }
      } elseif (!$this->useBrackets AND !empty($return[$key])) {
        $return[$key]   = (array)$return[$key];
        $return[$key][] = $value;
      } else {
        $return[$key] = $value;
      }
    }
    return $return;
  }
  /**
   * Resolves //, ../ and ./ from a path and returns
   * the result. Eg:
   *
   * /foo/bar/../boo.php    => /foo/boo.php
   * /foo/bar/../../boo.php => /boo.php
   * /foo/bar/.././/boo.php => /foo/boo.php
   *
   * This method can also be called statically.
   *
   * @param  string $url URL path to resolve
   * @return string      The result
   */
  function resolvePath($path)
  {
    $path = explode('/', str_replace('//', '/', $path));
    for ($i=0; $i<count($path); $i++) {
      if ($path[$i] == '.') {
        unset($path[$i]);
        $path = array_values($path);
        $i--;
      } elseif ($path[$i] == '..' AND ($i > 1 OR ($i == 1 AND $path[0] != '') ) ) {
        unset($path[$i]);
        unset($path[$i-1]);
        $path = array_values($path);
        $i -= 2;
      } elseif ($path[$i] == '..' AND $i == 1 AND $path[0] == '') {
        unset($path[$i]);
        $path = array_values($path);
        $i--;
      } else {
        continue;
      }
    }
    return implode('/', $path);
  }
  /**
   * Returns the standard port number for a protocol
   *
   * @param  string  $scheme The protocol to lookup
   * @return integer         Port number or NULL if no scheme matches
   *
   * @author Philippe Jausions <Philippe.Jausions@11abacus.com>
   */
  function getStandardPort($scheme)
  {
    switch (strtolower($scheme)) {
      case 'http':    return 80;
      case 'https':   return 443;
      case 'ftp':     return 21;
      case 'imap':    return 143;
      case 'imaps':   return 993;
      case 'pop3':    return 110;
      case 'pop3s':   return 995;
      default:        return null;
    }
  }
  /**
   * Forces the URL to a particular protocol
   *
   * @param string  $protocol Protocol to force the URL to
   * @param integer $port     Optional port (standard port is used by default)
   */
  function setProtocol($protocol, $port = null)
  {
    $this->protocol = $protocol;
    $this->port = is_null($port) ? $this->getStandardPort() : $port;
  }
}

function shCloseLogFile() {

  global $shLogger;
  if (!empty($shLogger)) {
    $shLogger->log('Closing log file at shutdown'."\n\n");
    if (!empty($shLogger->logFile))
    fClose( $shLogger->logFile);
  }
}

function _log($text, $data = '') {

  global $shLogger;
  $sefConfig = & shRouter::shGetConfig();
  static $shutdownRegistered = false;

  if (empty($sefConfig) || empty($sefConfig->debugToLogFile)) return;
  if (!empty($sefConfig->debugDuration) && (time()-$sefConfig->debugStartedAt) > $sefConfig->debugDuration)
  return;
  if (empty($shLogger)) {
    $shLogger = new shSimpleLogger( $GLOBALS['shConfigLiveSite'],
    sh404SEF_ADMIN_ABS_PATH.'logs/',
										'sh404SEF_debug_log',
    $sefConfig->debugToLogFile);
  }
  if (!$shutdownRegistered) {
    register_shutdown_function('shCloseLogFile');
    $shutdownRegistered = true;
  }
  $shLogger->log($text, $data);
}

class shSimpleLogger {

  var $traceFileName = '';
  var $isActive = 0;
  var $logFile = null;

  function shSimpleLogger( $siteName, $basePath, $fileName, $isActive) {
    $sefConfig = shRouter::shGetConfig();
    if (empty($isActive)) {
      $this->isActive = 0;
      return;
    } else $this->isActive = 1;
    $traceFileName = $basePath.$sefConfig->debugStartedAt.'.'.$fileName.'_'
    .str_replace('/','_',str_replace('http://', '', $siteName))
    .'.log';
    // Create file
    $fileIsThere = file_exists($traceFileName);
    $sep = "\t";
    if (!$fileIsThere) { // create file
      $fileHeader = 'sh404SEF trace file - created : '.$this->logTime()
      .' for '.$siteName."\n\n".str_repeat('-',25).' PHP Configuration '.str_repeat('-',25)."\n\n";
      $config = $this->parsePHPConfig();
      $line = str_repeat('-',69)."\n\n";
    } else $fileHeader = '';
    $file = fopen($traceFileName, 'ab');
    if ($file) {
      if (!empty($fileHeader)) {
        fWrite( $file, $fileHeader);
        fWrite( $file, print_r($config, true));
        fwrite( $file, $line);
      }
      $this->logFile = $file;
    } else {
      $this->isActive = 0;
      return;
    }
  }

  function logTime() {
    return date('Y-m-d')."\t".date('H:i:s');
  }

  function log($text, $data='') {
    if (empty($this->isActive) || empty($text)) return;
    $logData = empty($data) ? '' : ":\t".print_r($data, true);
    fWrite($this->logFile, $this->logTime()."\t".$text.$logData."\n");
  }

  function parsePHPConfig() {
    // by Andrew dot Boag at catalyst dot net dot nz
    // found on php.net doc

    ob_start();
    phpinfo(-1);
    $s = ob_get_contents();
    ob_end_clean();
    $a = $mtc = array();
    if (preg_match_all('/<tr><td class="e">(.*?)<\/td><td class="v">(.*?)<\/td>(:?<td class="v">(.*?)<\/td>)?<\/tr>/',$s,$mtc,PREG_SET_ORDER)){
      foreach($mtc as $v){
        if($v[2] == '<i>no value</i>') continue;
        $a[$v[1]] = $v[2];
      }
    }
    return $a;
  }
}

// J 1.5 : will put unused vars in uri query
function shRebuildVars( $appendString, &$uri) {
  if (empty( $uri)) return;
  $string = empty($appendString) ? '' : ltrim($appendString, '?');
  $uri->setQuery($string);
}

function shFileExists( $fileName) {
  static $files = array();

  $fileMD5 = md5( $fileName);
  if (!isset($files[$fileMD5])) {
    $files[$fileMD5] = file_exists( $fileName);
  }
  return $files[$fileMD5];
}

function shSefRelToAbs($string, $shLanguageParam, &$uri) {

  global $_SEF_SPACE, $shMosConfig_lang, $shMosConfig_locale,
  // shumisha 2007-03-13 added URL caching
  $shGETVars, $shRebuildNonSef,
  // V 1.2.4.m
  $shHomeLink,
  // V 1.2.4.q
  $shHttpsSave;

  _log('Entering shSefRelToAbs with '.$string.' | Lang = '.$shLanguageParam);

  $sefConfig = & shRouter::shGetConfig();
  
  // if superadmin, display non-sef URL, for testing/setting up purposes
  if (sh404SEF_NON_SEF_IF_SUPERADMIN) {
    $user = JFactory::getUser();
    if ($user->usertype == 'Super Administrator' ) {
      _log('Returning non-sef because superadmin said so.');
      return '/';
    }
  }
  // return unmodified anchors
  if (substr( $string, 0, 1) == '#') {  // V 1.2.4.t
    return $string;
  }
  // V 1.2.4.q quick fix for shared SSL server : if https, switch to non sef
  if (!empty($shHttpsSave) && $sefConfig->shForceNonSefIfHttps ) {
    _log('Returning shSefRelToAbs : Forced non sef if https');
    return shFinalizeURL($string);
  }

  $database =& JFactory::getDBO();

  $shOrigString = $string;
  $shMosMsg = shGetMosMsg($string); // V x 01/09/2007 22:45:52
  $string = shCleanUpMosMsg($string);// V x 01/09/2007 22:45:52

  // V x : removed shJoomfish module. Now we set $mosConfi_lang here
  $shOrigLang = $shMosConfig_locale; // save current language
  $shLanguage = shGetURLLang( $string);  // target language in URl is always first choice
  if (empty($shLanguage)) {
    $shLanguage = !empty($shLanguageParam) ? $shLanguageParam : $shMosConfig_locale;
  }

  // V 1.3.1 protect against those drop down lists
  if (strpos( $string, 'this.options[selectedIndex].value') !== false) {
    $string .= '&amp;lang='.shGetIsoCodeFromName($shLanguage);
    return $string;
  }
  $shMosConfig_locale = $shLanguage;
  _log('Language used : '.$shLanguage);

  // V 1.2.4.t workaround for old links like option=compName instead of option=com_compName
  if ( strpos(strtolower($string), 'option=login') === false && strpos(strtolower($string), 'option=logout') === false &&
  strpos(strtolower($string), 'option=&') === false && substr(strtolower($string), -7) != 'option='
  && strpos(strtolower($string), 'option=cookiecheck') === false
  && strpos(strtolower($string), 'option=') !== false && strpos(strtolower($string), 'option=com_') === false) {
    $string = str_replace('option=', 'option=com_', $string);
  }
  // V 1.2.4.k added homepage check : needed in case homepage is not com_frontpage
  if (empty($shHomeLink)) {  // first, find out about homepage link, from DB. homepage is not always /index.php or similar
    // it can be a link to anything, a page, a component,...
    $menu = & shRouter::shGetMenu();
    $shHomePage = & $menu->getDefault();

    if ($shHomePage) {
      if ( (substr( $shHomePage->link, 0, 9) == 'index.php')  // if link on homepage is a local page
      && (!preg_match( '/Itemid=[0-9]*/', $shHomePage->link))) {  // and it does not have an Itemid
        $shHomePage->link .= ($shHomePage->link == 'index.php' ? '?':'&').'Itemid='.$shHomePage->id;  // then add itemid
      }
      $shHomeLink = $shHomePage->link;
      if (!strpos($shHomeLink,'lang=')) {
        // V 1.2.4.q protect against not existing
        $shDefaultIso = shGetIsoCodeFromName(shGetDefaultLang());
        $shSepString = (substr($shHomeLink, -9) == 'index.php' ? '?':'&');
        $shHomeLink .= $shSepString.'lang='.$shDefaultIso;
      }
      $shHomeLink = shSortUrl($shHomeLink);  // $shHomeLink has lang info, whereas $homepage->link may or may not
    }
    _log('HomeLink = '. $shHomeLink);
  }

  // V 1.2.4.j string to be appended to URL, but not saved to DB
  $shAppendString = '';
  $shRebuildNonSef = array();
  $shComponentType = '';  // V w initialize var to avoid notices

  if ($shHomeLink) {  // now check URL against our homepage, so as to always return / if homepage
    $v1 = ltrim(str_replace($GLOBALS['shConfigLiveSite'], '', $string), '/');
    // V 1.2.4.m : remove anchor if any
    $v2 = explode( '#', $v1);
    $v1 = $v2[0];
    $shAnchor = isset($v2[1]) ? '#'.$v2[1] : '';
    $shSepString = (substr($v1, -9) == 'index.php' ? '?':'&');
    $shLangString = $shSepString.'lang='.shGetIsoCodeFromName($shLanguage);
    if (!strpos($v1,'lang=')) {
      $v1 .= $shLangString;
    }
    $v1 = str_replace('&amp;', '&', shSortURL($v1));
    // V 1.2.4.t check also without pagination info
    if (strpos( $v1, 'limitstart=0') !== false) {  // the page has limitstart=0
      $stringNoPag = shCleanUpPag($v1);  // remove paging info to be sure this is not homepage
    } else $stringNoPag = null;
    if ($v1 == $shHomeLink || $v1 == 'index.php'.$shLangString
    || $stringNoPag == $shHomeLink)  { // V 1.2.4.t 24/08/2007 11:07:49
      $shTemp = ($v1 == $shHomeLink || shIsDefaultLang($shLanguage)?
        	'' : shGetIsoCodeFromName($shLanguage).'/');  //10/08/2007 17:28:14
      if (!empty($shMosMsg) ) // V x 01/09/2007 22:48:01
      $shTemp .= '?'.$shMosMsg;
      if (!empty($sefConfig->shForcedHomePage)) { // V 1.2.4.t
        $shTmp = $shTemp.$shAnchor;
        $ret = shFinalizeURL($sefConfig->shForcedHomePage.(empty($shTmp) ? '' : '/'.$shTmp));
        if (empty($uri))  // if no URI, append remaining vars directly to the string
        $ret .= $shAppendString;
        else
        shRebuildVars( $shAppendString, $uri);
        $shMosConfig_locale = $shOrigLang;
        _log('Returning shSefRelToAbs 1 with '.$ret);
        return $ret;
      } else {
        $shRewriteBit = shIsDefaultLang($shLanguage)? '/': $sefConfig->shRewriteStrings[$sefConfig->shRewriteMode];
        $ret = shFinalizeURL($GLOBALS['shConfigLiveSite'].$shRewriteBit.$shTemp.$shAnchor);
        if (empty($uri))  // if no URI, append remaining vars directly to the string
        $ret .= $shAppendString;
        else
        shRebuildVars( $shAppendString, $uri);
        $shMosConfig_locale = $shOrigLang;
        _log('Returning shSefRelToAbs 2 with '.$ret);
        return $ret;
      }
    }
  }

  $newstring = str_replace($GLOBALS['shConfigLiveSite'].'/', '', $string);
  // check for url to same site, but with SSL : Joomla 1.5 does not allow it yet
  //$liveSiteSsl = str_replace('http://', 'https://', $GLOBALS['shConfigLiveSite']);
  //$newStringSsl = str_replace($liveSiteSsl.'/', '', $string);

  $letsGo = substr($newstring,0,9) == 'index.php'
  && !eregi('^(([^:/?#]+):)', $newstring)
  && !eregi('this\.options\[selectedIndex\]\.value', $newstring);
  //$letsGoSsl = substr($newstringSsl,0,9) == 'index.php'
  //	&& !eregi('^(([^:/?#]+):)', $newstringSsl)
  //	&& !eregi('this\.options\[selectedIndex\]\.value', $newstringSsl);
  $letsGoSsl = false;
  if ($letsGo || $letsGoSsl)
  {
    // Replace & character variations.
    $string = str_replace(array('&amp;', '&#38;'), array('&', '&'), $letsGo ? $newstring : $newStringSsl);
    $newstring = $string; // V 1.2.4.q
    $shSaveString = $string;
    // warning : must add &lang=xx (only if it does not exists already), so as to be able to recognize the SefURL in the db if it's there
    if (!strpos($string,'lang=')) {
      $shSepString = (substr($string, -9) == 'index.php' ? '?':'&');
      $anchorTable = explode('#', $string); // V 1.2.4.m remove anchor before adding language
      $string = $anchorTable[0];
      $string .= $shSepString.'lang='.shGetIsoCodeFromName($shLanguage)
      .(!empty($anchorTable[1])? '#'.$anchorTable[1]:''); // V 1.2.4.m then stitch back anchor
    }
    $URI = new sh_Net_URL($string);
    // V 1.2.4.l need to save unsorted URL
    if (count($URI->querystring) > 0) {
      // Import new vars here.
      $option = null;
      $task = null;
      //$sid = null;  V 1.2.4.s
      // sort GET parameters to avoid some issues when same URL is produced with options not
      // in the same order, ie index.php?option=com_virtuemart&category_id=3&Itemid=2&lang=fr
      // Vs index.php?category_id=3&option=com_virtuemart&Itemid=2&lang=fr
      ksort($URI->querystring);  // sort URL array
      $string = shSortUrl($string);
      // now we are ready to extract vars
      $shGETVars = $URI->querystring;
      extract($URI->querystring, EXTR_REFS);
    }
    if (empty($option)) {// V 1.2.4.r protect against empty $option : we won't know what to do
      $shMosConfig_locale = $shOrigLang;
      _log('Returning shSefRelToAbs 3 with '.$shOrigString);
      return $shOrigString;
    }
    $shOption = str_replace('com_', '', $option);
    switch ($shOption) {
      case (in_array($shOption, $sefConfig->skip)):
        $shComponentType = 'skip';
        break;
      case (in_array($shOption, $sefConfig->nocache)):
        $shComponentType = 'noCache';
        break;
      default:
        $shComponentType = 'sh404SEF';
        break;
    }

    // V 1.2.4.s : fallback to to JoomlaSEF if no extension available
    // V 1.2.4.t : this is too early ; it prevents manual custom redirect to be checked agains the requested non-sef URL
    if (($shComponentType == 'sh404SEF')
    && !shFileExists(sh404SEF_ABS_PATH.'components/com_sh404sef/sef_ext/'.$option.'.php')
    && !shFileExists(sh404SEF_ABS_PATH.'components/'.$option.'/sef_ext.php')
    && !shFileExists(sh404SEF_ABS_PATH.'components/'.$option.'/sef_ext/'.$option.'.php')  // V 1.2.4.s native plugin can be in comp own /sef_ext/dir - allows deliv of plugin with comp
    )
    $shComponentType = 'sh404SEFFallback';
    _log('Component type = '.$shComponentType);
    // is there a named anchor attached to $string? If so, strip it off, we'll put it back later.
    if ($URI->anchor)
    $string = str_replace('#'.$URI->anchor, '', $string);  // V 1.2.4.m
    // shumisha special homepage processing (in other than default language)
    if  ((shIsHomePage($string)) || ($string == 'index.php')  // 10/08/2007 18:13:43
    ){
      $sefstring = '';
      $urlType = shGetSefURLFromCacheOrDB($string, $sefstring);
      // J 1.5 : $limit seems not to be used anymore. Replaced by only $limitstart. However, other extensions may (and will)
      // still use it so we need it both ways
      if (($urlType == sh404SEF_URLTYPE_NONE || $urlType == sh404SEF_URLTYPE_404) && (!empty($limit) || (!isset($limit) && !empty($limitstart))) ) {
        $urlType = shGetSefURLFromCacheOrDB(shCleanUpPag($string), $sefstring); // V 1.2.4.t check also without page info
        //to be able to add pagination on custom
        //redirection or multi-page homepage
        if ($urlType != sh404SEF_URLTYPE_NONE && $urlType != sh404SEF_URLTYPE_404) {
          $sefstring = shAddPaginationInfo( @$limit, @$limitstart, @showall,1, $string, $sefstring, null);
          // that's a new URL, so let's add it to DB and cache
          shAddSefUrlToDBAndCache( $string, $sefstring, 0, $urlType);  // created url must be of same type as original
        }
        if ($urlType == sh404SEF_URLTYPE_NONE || $urlType == sh404SEF_URLTYPE_404) {
          require_once(sh404SEF_FRONT_ABS_PATH.'sef_ext.php');
          $sef_ext = new sef_404();
          // Rewrite the URL now.
          $sefstring = $sef_ext->create($string, $URI->querystring, $shAppendString, $shLanguage,
          $shOrigString); // V 1.2.4.s added original string
        }
      } else if (($urlType == sh404SEF_URLTYPE_NONE || $urlType == sh404SEF_URLTYPE_404)) {  // not found but no $limit or $limitstart
        $sefstring = shGetIsoCodeFromName($shLanguage).'/';
        shAddSefUrlToDBAndCache( $string, $sefstring, 0, sh404SEF_URLTYPE_AUTO); // create it
      }
      // V 1.2.4.j : added $shAppendString to pass non sef parameters. For use with parameters that won't be stored in DB
      $ret = $GLOBALS['shConfigLiveSite'].$sefConfig->shRewriteStrings[$sefConfig->shRewriteMode]
      .$sefstring
      //.$shAppendString  // J 1.5 already do this
      ;

      // not valid with 1.5 anymore                       ;
      //if (!empty($shMosMsg)) // V x 01/09/2007 22:48:01
      //  $ret .= (empty($shAppendString) || $sefConfig->shRewriteStrings[$sefConfig->shRewriteMode] == '/index.php?/' ? '?':'&').$shMosMsg;
      $ret = shFinalizeURL($ret);
      if (empty($uri))  // if no URI, append remaining vars directly to the string
      $ret .= $shAppendString;
      else
      shRebuildVars( $shAppendString, $uri);
      $shMosConfig_locale = $shOrigLang;
      _log('Returning shSefRelToAbs 4 with '.$ret);
      return $ret;
    }

    if (isset($option) && !($option=='com_content' && @$task == 'edit') && (strtolower($option) != 'com_sh404sef')) { // V x 29/08/2007 23:19:48
      // check also that option = com_content, otherwise, breaks some comp
      /*Beat: sometimes task is not set, e.g. when $string = "index.php?option=com_frontpage&Itemid=1" */
      switch ($shComponentType) {
        case 'skip': {
          $sefstring = $shSaveString;  // V 1.2.4.q : restore untouched URL, except anchor
          // which will be added later
          break;
        }
        case 'noCache': {
          if (isset($URI)) unset($URI);
          $sefstring = 'component/';
          $URI = new sh_Net_URL(shSortUrl($shSaveString));
          if (count($URI->querystring) > 0) {
            foreach($URI->querystring as $key => $value) {
              $sefstring .= "$key,$value/";
            }
            $sefstring = str_replace( 'option/', '', $sefstring );
          }
          break;
        }
        case 'sh404SEFFallback': // v 1.2.4.t

          // if not found then fall back to Joomla! SEF
          if (isset($URI)) unset($URI);
          $sefstring = 'component/';
          $URI = new sh_Net_URL(shSortUrl($shSaveString));
          if (count($URI->querystring) > 0) {
            foreach($URI->querystring as $key => $value) {
              $sefstring .= "$key,$value/";
            }
            $sefstring = str_replace( 'option/', '', $sefstring );
          }
          break;
        default: {
          $sefstring='';
          $urlType = shGetSefURLFromCacheOrDB($string, $sefstring); // V 1.2.4.t
          if (($urlType == sh404SEF_URLTYPE_NONE || $urlType == sh404SEF_URLTYPE_404) && (!empty($limit) || (!isset($limit) && !empty($limitstart)))) {
            $urlType = shGetSefURLFromCacheOrDB(shCleanUpPag($string), $sefstring); // search without pagination info
            if ($urlType != sh404SEF_URLTYPE_NONE && $urlType != sh404SEF_URLTYPE_404) {
              $sefstring = shAddPaginationInfo( @$limit, @$limitstart, @showall, 1, $string, $sefstring, null);
              // that's a new URL, so let's add it to DB and cache
              shAddSefUrlToDBAndCache( $string, $sefstring, 0, $urlType);
            }
          }

          if ($urlType == sh404SEF_URLTYPE_NONE) {
            // If component has its own sef_ext plug-in included.
            $shDoNotOverride = in_array( $shOption, $sefConfig->shDoNotOverrideOwnSef);
            if (shFileExists(sh404SEF_ABS_PATH.'components/'.$option.'/sef_ext.php')
            && ($shDoNotOverride                   // and param said do not override
            || (!$shDoNotOverride              // or param said override, but we don't have a plugin either in sh404SEF dir or component sef_ext dir
            && (!shFileExists(sh404SEF_ABS_PATH
            .'components/com_sh404sef/sef_ext/'.$option.'.php')
            &&
            !shFileExists(sh404SEF_ABS_PATH
            .'components/'.$option.'/sef_ext/'.$option.'.php') )
            ))) {
              // Load the plug-in file. V 1.2.4.s changed require_once to include
              include_once(sh404SEF_ABS_PATH.'components/'.$option.'/sef_ext.php');
              $_SEF_SPACE = $sefConfig->replacement;
              $comp_name = str_replace('com_', '', $option);
              eval("\$sef_ext = new sef_$comp_name;");
              // V x : added default string in params
              if (empty($sefConfig->defaultComponentStringList[$comp_name]))
              $title[] = getMenuTitle($option, null, isset($Itemid) ? @$Itemid : null, null, $shLanguage); // V 1.2.4.x
              else $title[] = $sefConfig->defaultComponentStringList[$comp_name];
              // V 1.2.4.r : clean up URL BEFORE sending it to sef_ext files, to have control on what they do
              // remove lang information, we'll put it back ourselves later
              //$shString = preg_replace( '/(&|\?)lang=[a-zA-Z]{2,3}/iU' ,'', $string);
              // V 1.2.4.t use original non-sef string. Some sef_ext files relies on order of params, which may
              // have been changed by sh404SEF
              $shString = preg_replace( '/(&|\?)lang=[a-zA-Z]{2,3}/iU' ,'', $shSaveString);
              $finalstrip = explode("|", $sefConfig->stripthese);
              $shString = str_replace('&', '&amp;', $shString);
              _log('Sending to own sef_ext.php plugin : '.$shString);
              $sefstring = $sef_ext->create($shString);
              _log('Created by sef_ext.php plugin : '.$sefstring);
              $sefstring = str_replace("%10", "%2F", $sefstring);
              $sefstring = str_replace("%11", $sefConfig->replacement, $sefstring);
              $sefstring = rawurldecode($sefstring);
              if ($sefstring == $string) {
                if (!empty($shMosMsg)) // V x 01/09/2007 22:48:01
                $string .= '?'.$shMosMsg;
                $ret = shFinalizeURL($string);
                $shMosConfig_locale = $shOrigLang;
                _log('Returning shSefRelToAbs 5 with '.$ret);
                return $ret;
              }
              else {
                // V 1.2.4.p : sef_ext extensions for opensef/SefAdvance do not always replace '
                $sefstring = str_replace( '\'', $sefConfig->replacement, $sefstring);
                // some ext. seem to html_special_chars URL ?
                $sefstring = str_replace( '&#039;', $sefConfig->replacement, $sefstring); // V w 27/08/2007 13:23:56
                $sefstring = str_replace(' ', $_SEF_SPACE, $sefstring);
                $sefstring = str_replace(' ', '',
                (shInsertIsoCodeInUrl($option, $shLanguage) ?   // V 1.2.4.q
                shGetIsoCodeFromName($shLanguage).'/' : '')
                .titleToLocation($title[0]).'/'.$sefstring.(($sefstring != '') ? $sefConfig->suffix : ''));
                if (!empty($sefConfig->suffix))
                $sefstring = str_replace('/'.$sefConfig->suffix, $sefConfig->suffix, $sefstring);

                //$finalstrip = explode("|", $sefConfig->stripthese);
                $sefstring = str_replace($finalstrip, $sefConfig->replacement, $sefstring);
                $sefstring = str_replace($sefConfig->replacement.$sefConfig->replacement.$sefConfig->replacement,
                $sefConfig->replacement, $sefstring);
                $sefstring = str_replace($sefConfig->replacement.$sefConfig->replacement,
                $sefConfig->replacement, $sefstring);
                $suffixthere = 0;
                if (!empty($sefConfig->suffix) && strpos($sefstring, $sefConfig->suffix ) !== false)  // V 1.2.4.s
                $suffixthere = strlen($sefConfig->suffix);
                $takethese = str_replace("|", "", $sefConfig->friendlytrim);
                $sefstring = trim(substr($sefstring,0,strlen($sefstring)-$suffixthere), $takethese);
                $sefstring .= $suffixthere == 0 ? '': $sefConfig->suffix;  // version u 26/08/2007 17:27:16
                // V 1.2.4.m store it in DB so as to be able to use sef_ext plugins really !
                $string = str_replace('&amp;', '&', $string);
                // V 1.2.4.r without mod_rewrite
                $shSefString = shAdjustToRewriteMode($sefstring);
                // V 1.2.4.p check for various URL for same content
                $dburl = ''; // V 1.2.4.t prevent notice error
                $urlType = sh404SEF_URLTYPE_NONE;
                if ($sefConfig->shUseURLCache)
                $urlType = shGetNonSefURLFromCache($shSefString, $dburl);
                $newMaxRank = 0; // V 1.2.4.s
                $shDuplicate = false;
                if ($sefConfig->shRecordDuplicates || $urlType == sh404SEF_URLTYPE_NONE) {  // V 1.2.4.q + V 1.2.4.s+t
                  $sql = "SELECT newurl, rank, dateadd FROM #__redirection WHERE oldurl = '"
                  .$shSefString."' ORDER BY rank ASC";
                  $database->setQuery($sql);
                  $dbUrlList = $database->loadObjectList();
                  if (count($dbUrlList) > 0) {
                    $dburl = $dbUrlList[0]->newurl;
                    $newMaxRank = $dbUrlList[count($dbUrlList)-1]->rank+1;
                    $urlType = $dbUrlList[0]->dateadd == '0000-00-00' ? sh404SEF_URLTYPE_AUTO : sh404SEF_URLTYPE_CUSTOM;
                  }
                }
                if ($urlType != sh404SEF_URLTYPE_NONE && ($dburl != $string)) $shDuplicate = true;
                $urlType = $urlType == sh404SEF_URLTYPE_NONE ? sh404SEF_URLTYPE_AUTO : $urlType;
                _log('Adding from sef_ext to DB : '.$shSefString.' | rank = '.($shDuplicate?$newMaxRank:0) );
                shAddSefUrlToDBAndCache( $string, $shSefString, ($shDuplicate?$newMaxRank:0), $urlType);
              }
            }
            // Component has no own sef extension.
            else {
              $string = trim($string, "&?");

              // V 1.2.4.q a trial in better handling homepage articles
              if (shIsCurrentPageHome() && ($option == 'com_content')    // com_content component on homepage
              && (isset($task)) && ($task == 'view')
              && $sefConfig->guessItemidOnHomepage) {
                $string = preg_replace( '/(&|\?)Itemid=[^&]*/i', '', $string);  // we remove Itemid, as com_content plugin
                $Itemid = null;                                     // will hopefully do a better job at finding the right one
                unset($URI->querystring['Itemid']);
                unset($shGETVars['Itemid']);
              }

              require_once(sh404SEF_FRONT_ABS_PATH.'sef_ext.php');
              $sef_ext = new sef_404();
              // Rewrite the URL now. // V 1.2.4.s added original string
              $sefstring = $sef_ext->create($string, $URI->querystring, $shAppendString, $shLanguage, $shOrigString);
            }
          }
        }
      } // end of cache check shumisha
      if (isset($sef_ext)) unset($sef_ext);
      // V 1.2.4.j
      // V 1.2.4.r : checked for double //
      // V 1.2.4.r try sef without mod_rewrite
      $shRewriteBit = $shComponentType == 'skip' ? '/': $sefConfig->shRewriteStrings[$sefConfig->shRewriteMode];
      if (strpos($sefstring,'index.php') === 0 ) $shRewriteBit = '/';  // V 1.2.4.t bug #119
      $string =  $GLOBALS['shConfigLiveSite'].$shRewriteBit.ltrim( $sefstring, '/')
      //.$shAppendString  // J 1.5 already do this
      //. (empty($shMosMsg) ? '' :
      //		(empty($shAppendString)
      //		|| $sefConfig->shRewriteStrings[$sefConfig->shRewriteMode] == '/index.php?/' ? '?':'&').$shMosMsg)
      .(($URI->anchor)?"#".$URI->anchor:'');
    }
    else {  // V x 03/09/2007 13:47:37 editing content
      $shComponentType = 'skip';  // will prevent turning & into &amp;
    }
    $ret = $string;
    // $ret = str_replace('itemid', 'Itemid', $ret); // V 1.2.4.t bug #125
  }
  if (!isset($ret)) $ret = $string;
  //if (!empty($shMosMsg) && strpos($ret, $shMosMsg) === false) // V x 01/09/2007 23:02:00
  //   $ret .= (strpos( $ret, '?') === false  || $sefConfig->shRewriteStrings[$sefConfig->shRewriteMode] == '/index.php?/'? '?':'&').$shMosMsg;
  $ret = ($shComponentType == 'sh404SEF') ? shFinalizeURL($ret) : $ret;  // V w 27/08/2007 13:21:28
  if (empty($uri)) {  // we don't have a uri : we must be doing a redirect from non-sef to sef or similar
    $ret .= $shAppendString;  // append directly to url
  } else {
    shRebuildVars( $shAppendString, $uri);  // instead, add to uri. Joomla will put everything together
  }
  $shMosConfig_locale = $shOrigLang;
  return $ret;
}

// V 1.2.4.t returns sef url with added pagination information
function shAddPaginationInfo( $limit, $limitstart, $showall, $iteration, $url, $location, $shSeparator = null){
  global $mainframe;

  $sefConfig = & shRouter::shGetConfig();

  $listLimit = shGetDefaultDisplayNumFromURL($url);
  $database =& JFactory::getDBO();
  if (empty($shSeparator))
  $shSeparator = (substr($location, -1) == '/') ? '':'/';
  if (!empty($limit) && is_numeric( $limit)) {
    $pagenum = intval($limitstart/$limit);
    $pagenum++;
  } else if (!isset($limit) && !empty($limitstart)) {  // only limitstart
    if (strpos( $url, 'option=com_content') !== false && strpos( $url, 'view=article') !== false) {
      $pagenum = intval($limitstart+1);   // multipage article
    }
    else {
      $pagenum = intval($limitstart/$listLimit)+1;  // blogs, tables, ...
    }
  } else {
    $pagenum = $iteration;
  }
  // Make sure we do not end in infite loop here.
  if ($pagenum < $iteration)
  $pagenum = $iteration;
  // shumisha added to handle table-category and table-section which may have variable number of items per page
  // There still will be a problem with filter, which may reduce the total number of items. Thus the item we are looking for
  if ( /*(strpos($url,'option=com_search'))
  || (strpos($url,'option=com_content') &&  // J 1.5 : no more item number drop down box ??
  (    (strpos( $url, 'view=category'))
  //|| (strpos( $url, 'view=blogcategory'))
  || (strpos( $url, 'view=section'))
  //|| (strpos( $url, 'task=blogsection'))
  ))
  || */ (strpos($url,'option=com_virtuemart') && $sefConfig->shVmUsingItemsPerPage)) {
  $shMultPageLength= $sefConfig->pagerep.(empty($limit) ? $listLimit : $limit);
  } else $shMultPageLength= '';
  // shumisha : modified to add # of items per page to URL, for table-category or section-category
   
  if (!empty($sefConfig->pageTexts[$GLOBALS['shMosConfig_locale']])
  && (false !== strpos($sefConfig->pageTexts[$GLOBALS['shMosConfig_locale']], '%s'))){
    $page = str_replace('%s', $pagenum, $sefConfig->pageTexts[$GLOBALS['shMosConfig_locale']]).$shMultPageLength;
  } else {
    $page = $sefConfig->pagerep.$pagenum.$shMultPageLength;
  }
  // V 1.2.4.t special processing to replace page number by headings
  $shPageNumberWasReplaced = false;
  if (   $sefConfig->shMultipagesTitle && strpos($url, 'option=com_content') !== false
  && strpos($url, 'view=article') !== false && !empty($limitstart) ) {  // this is multipage article - limitstart instead of limit in J1.5
    parse_str($url, $shParams);
    if (!empty($shParams['id'])) {
      $shPageTitle = '';
      $sql = 'SELECT c.id, c.fulltext, c.introtext  FROM #__content AS c WHERE id=\''.$shParams['id'].'\'';
      $database->setQuery($sql);
      $contentElement = $database->loadObject( );
      if ($database->getErrorNum()) {
        JError::RaiseError( 500, $database->stderr());
      }
      $contentText = $contentElement->introtext.$contentElement->fulltext;
      if (!empty($contentElement) && ( strpos( $contentText, 'class="system-pagebreak' ) !== false )) { // search for mospagebreak tags
        // copied over from pagebreak plugin
        // expression to search for
        $regex = '#<hr([^>]*)class=\"system-pagebreak\"([^>]*)\/>#iU';
        // find all instances of mambot and put in $matches
        $shMatches = array();
        preg_match_all( $regex, $contentText, $shMatches, PREG_SET_ORDER );
        // adds heading or title to <site> Title
        if (empty($limitstart)) {  // if first page use heading of first mospagebreak
          /* if ( $shMatches[0][2] ) {
           parse_str( html_entity_decode( $shMatches[0][2] ), $args );
           if ( @$args['heading'] ) {
           $shPageTitle = stripslashes( $args['heading'] );
           }
           }*/
        } else {  // for other pages use title of mospagebreak
          if ( $limitstart > 0 && $shMatches[$limitstart-1][1] ) {
            $args = JUtility::parseAttributes( $shMatches[$limitstart-1][1] );
            if ( @$args['title'] ) {
              $shPageTitle = $args['title'];
            } else if (@$args['alt']) {
              $shPageTitle = $args['alt'];
            } else {  // there is a page break, but no title. Use a page number
              $shPageTitle = str_replace('%s', $limitstart+1, $sefConfig->pageTexts[$GLOBALS['shMosConfig_locale']]);
            }
          }
        }
      }
      if (!empty($shPageTitle)) { // found a heading, we should use that as a Title
        $location .= $shSeparator.titleToLocation($shPageTitle);
      }
      $shPageNumberWasReplaced = true;  // always set the flag, otherwise we'll a Page-1 added
    }
  }
  // maybe this is a multipage with "showall=1"
  if ( strpos($url, 'option=com_content') !== false
  && strpos($url, 'view=article') !== false && strpos($url, 'showall=1') !== false ) {  // this is multipage article with showall
    $location .= $shSeparator.titleToLocation(JText::_( 'All Pages' ));
    $shPageNumberWasReplaced = true;  // always set the flag, otherwise we'll a Page-1 added
  }

  // V u 26/08/2007 10:35:38 trim url as per user settings
  $suffixthere = 0;
  if (strpos($url, 'option=com_content') !== false && strpos($url, 'format=pdf') !== false)
  $shSuffix = '.pdf';
  else
  $shSuffix = $sefConfig->suffix;
  if (!empty($shSuffix) && ($shSuffix != '/') && subStr( $location, -strLen($shSuffix)) == $shSuffix)
  $suffixthere = strLen($shSuffix);
  $takethese = str_replace('|', '', $sefConfig->friendlytrim);
  $location = trim(substr($location,0,strlen($location)-$suffixthere), $takethese);

  // add page number
  if (!$shPageNumberWasReplaced && isset($limitstart)
  && ($limitstart != 0 									// if not first page, add items per page
  || ($limitstart == 0 								// if first page, we may add number of items per page if the
  && ((strpos($url,'option=com_virtuemart') 		// requested number of items per page is not the default one
  && $sefConfig->shVmUsingItemsPerPage
  && (isset($limit) && $limit != $listLimit)  // // for Virtuemart, default is Joomla global default
  )
  /*|| (
   ((strpos($url,'option=com_search')) || (strpos($url,'option=com_content') &&  // for regular content, default
   ((strpos( $url, 'task=category')) || (strpos( $url, 'task=section')))))	  // can be set for each menu item
   && $limit != $listLimit 		                            // so we need to fetch the display_num param for the
   )*/														// Itemid found in the URL, if any. if none, use default
  )
  )
  )
  ) {
    $location .= $shSeparator.$page;
  }
  // add suffix
  if (!empty($shSuffix) && $location != '/' && substr($location, -1) != '/') {
    $location = $shSuffix == '/' ?
    $location.$shSuffix
    : str_replace($shSuffix, '', $location).$shSuffix;
  }

  // add default index file
  if ($sefConfig->addFile){ // V 1.2.4.t
    if ((empty($shSuffix) || (!empty($shSuffix)
    && subStr( $location, -strLen($shSuffix)) != $shSuffix) ) )
    $location .= (substr($location, -1) == '/' ? '':'/').$sefConfig->addFile;
  }
  return ltrim($location, '/');
}


// V 1.2.4.t check if this is a request for VM cookie check AND done by a search engine
// if so, this has to be an old link left over in search engine index, and  we must 301 redirectt to
// same URl without vmvhk/
function shCheckVMCookieRedirect() {

  global $shCurrentPageURL;

  if (shIsSearchEngine() && strpos($shCurrentPageURL, 'vmchk/') !== false) {
    shRedirect( str_replace('vmchk/', '', $shCurrentPageURL));
  }
}




/*
 * 404SEF SUPPORT FUNCTIONS
 */
function sef_ext_exists($this_name)
{
  $sefConfig = & shRouter::shGetConfig();

  $database =& JFactory::getDBO();
  // check for sef_ext
  $this_name = str_replace($sefConfig->replacement, " ", $this_name);
  $this_name = str_replace('\'', '', $this_name);  // V 1.2.4.t 21/08/2007 20:45:58 bug #165
  $sql = "SELECT `id`,`link` FROM #__menu  WHERE ((`name` LIKE '%".$this_name."%') AND (`published` > 0))";
  $database->setQuery($sql);
  $rows = @$database->loadObjectList();

  if ($database->getErrorNum()) {
    JError::RaiseError( 500, $database->stderr());
  }

  if (@count($rows) > 0) {
    $option = str_replace("index.php?option=","",$rows[0]->link);
    if (shFileExists(sh404SEF_ABS_PATH."components/$option/sef_ext.php")){
      return @$rows[0];
    }
    else {
      unset($rows);
    }
  }

  return null;
}

function getExt($URL_ARRAY)
{

  $sefConfig = & shRouter::shGetConfig();
  
  $database =& JFactory::getDBO();
  $ext = array();
  $row = sef_ext_exists($URL_ARRAY[0]);
  $ext['path'] = sh404SEF_FRONT_ABS_PATH.'sef_ext.php';

  if (is_object($row)) {
    $option = str_replace("index.php?option=","",$row->link);
    $ext['path'] = sh404SEF_ABS_PATH."components/$option/sef_ext.php";
  }
  elseif ((strpos($URL_ARRAY[0], "com_") !== false) or ($URL_ARRAY[0] == "component")) {
    $option = "com_component";
  }
  elseif($URL_ARRAY[0] == 'content') {
    $option = "com_content";
  }
  else{
    $option = "404";
  }
  $ext['name'] = str_replace("com_","",$option);

  return $ext;
}

function is_valid($string)
{
  global $base, $index;
  if (empty($string))
  $state = false;
  elseif (($string == $index )|($string == $base.$index )) {
    $state = true ;
  }
  else {
    $state = false;
    require_once(sh404SEF_FRONT_ABS_PATH.'sef_ext.php');
    $sef_ext = new sef_404;
    $option = (isset($_GET['option'])) ? $_GET['option'] : (isset($_REQUEST['option'])) ? $_REQUEST['option'] : null;

    $vars = array();
    if (is_null($option)) {
      parse_str($string, $vars);
      if (isset($vars['option'])) {
        $option = $vars['option'];
      }
    }
    switch ($option) {
      case is_null($option):
        break;
      case "login":		/*Beat: makes this also compatible with CommunityBuilder login module*/
      case "logout": {
        $state = true;
        break;
      }
      default: {
        if (is_valid_component($option)){
          if ((!($option == "com_content"))|(!($option == "content"))) {
            $state = true;
          }
          else {
            $title=$sef_ext->getContentTitles($_REQUEST['view'],$_REQUEST['id'], empty($_REQUEST['layout']) ? '' : $_REQUEST['layout']);
            if (count($title) > 0) {
              $state = true;
            }
          }
        }
        // shumisha check if this is homepage+lang=xx
        else {
          if (substr($string,0,5)=='lang=')
          $state = true;
        }
        // shumisha end of change
      }
    }
  }
  return $state;
}

function is_valid_component($this)
{
  $state = false;
  $path = sh404SEF_ABS_PATH .'components/';

  if (is_dir($path)) {
    if (($contents = opendir($path))) {
      while (($node = readdir($contents)) !== false) {
        if ($node != '.' && $node != '..') {
          if (is_dir($path.'/'.$node) && $this == $node) {
            $state = true;
            break;
          }
        }
      }
    }
  }
  return $state;
}

// V 1.2.4.q detect homepage, disregarding language and pagination
function shIsHomepage( $string) {

  static $pages = array();

  global $shHomeLink;

  $md5 = md5( $string);
  if( !isset( $pages[$md5])) {
    $shTempString = rtrim(str_replace($GLOBALS['shConfigLiveSite'], '', $string), '/');
    $pages[$md5] = shSortUrl(shCleanUpLangAndPag($shTempString)) == shSortUrl(shCleanUpLangAndPag($shHomeLink)); // version t added sorting
  }
  return $pages[$md5];
}

function getMenuTitle($option, $task, $id = null, $string = null, $shLanguage = null)
{
  global $shHomeLink;

  $sefConfig = & shRouter::shGetConfig();
  
  $database =& JFactory::getDBO();
  $shLanguage = empty($shLanguage) ? $GLOBALS['shMosConfig_locale'] : $shLanguage;
  // V 1.2.4.q must also check if homepage, in any language. If homepage, must return $title[]='/'
  // language info and limit/limistart pagination will be added at final stage by sefGetLocation()
  // V 1.2.4.t must also check that menu item is published !!

  $nameField = $sefConfig->useMenuAlias ? 'alias' : 'name';

  if (!empty($string)) {  // V 1.2.4.q replaced isset by empty
    $sql = "SELECT " . $nameField . ", link,id FROM #__menu WHERE link = '$string' AND published = '1'";
  }
  elseif (!empty($id)) {
    $sql = "SELECT " . $nameField . ", link,id FROM #__menu WHERE id = '".$id."' AND published='1'";
  }
  elseif (!empty($option)) {
    $sql = 'SELECT ' . $nameField . ', link,id FROM #__menu WHERE published=\'1\' AND link LIKE \'index.php?option='.$option.'%\'';
  }else {
    return '/'; // don't know what else we could do, just go home
  }
  $database->setQuery($sql);
  if (isset($shLanguage) && shIsMultilingual()) {
    $rows = @$database->loadObjectList( '', true, $shLanguage);
  }
  else {
    $rows = @$database->loadObjectList( );
  }
  if ($database->getErrorNum()) {
    die( $database->stderr() );
  } elseif(@count($rows) > 0) {
    $shLink = shSortUrl($rows[0]->link.($rows[0]->link == 'index.php' ? '?':'&').'Itemid='.$rows[0]->id);
    if (!shIsHomepage( $shLink)) {  // V1.2.4.q homepage detection
      if(!empty($rows[0]->$nameField)) {
        $title = $rows[0]->$nameField;
      }
    } else $title = '/'; // this is homepage
  } else {
    $title = str_replace('com_', '', $option);
  }
  return $title;
}

function shIsSearchEngine() {  // return true if user agant is a search engine
  static $isSearchEngine = null;
  static $searchEnginesAgents = array(
     'B-l-i-t-z-B-O-T'
     ,'Baiduspider'
     ,'BlitzBot'
     ,'btbot'
     ,'DiamondBot'
     ,'Exabot'
     ,'FAST Enterprise Crawler'
     ,'FAST-WebCrawler/'
     ,'g2Crawler'
     ,'genieBot'
     ,'Gigabot'
     ,'Girafabot'
     ,'Googlebot'
     ,'ia_archiver'
     ,'ichiro'
     ,'Mediapartners-Google'
     ,'Mnogosearch'
     ,'msnbot'
     ,'MSRBOT'
     ,'Nusearch Spider'
     ,'SearchSight'
     ,'Seekbot'
     ,'sogou spider'
     ,'Speedy Spider'
     ,'Ask Jeeves/Teoma'
     ,'VoilaBot'
     ,'Yahoo!'
     ,'Slurp'
     ,'YahooSeeker'
     );
     //return true;
     if (!is_null ($isSearchEngine)) {
       return $isSearchEngine;
     }
     else {
       $isSearchEngine = false;
       $useragent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : strtolower($_SERVER['HTTP_USER_AGENT']);
       if (!empty($useragent))
       foreach ($searchEnginesAgents as $searchEnginesAgent)
       if (strpos($useragent, strtolower($searchEnginesAgent)) !== false ) {
         $isSearchEngine = true;
         return true;
       }
       return $isSearchEngine;
     }
}

// J 1.5 specific functions

function shFetchLinkFromMenu($Itemid) {

}

function shRemoveSlugs( $vars) {  // remove slugs from a J! 1.5 non-sef style vars array
  if (!empty($vars)) {
    foreach($vars as $k => $v) {
      $m = is_string( $v) ? explode(':', $v) : null; // tracker #14107, thanks 3dentech
      if (!empty( $m) && !empty($m[1]) && is_numeric($m[0])) { // an integer followed by : followed by something
        $vars[$k]= $m[0];
      } else {
        // use the raw value, for arrays for instance
        $vars[$k] = $v;
      }
    }
    // fix some problems in incoming URLs
    if (!empty($vars['Itemid'])) {  // sometimes we get doubles : ?Itemid=xx?Itemid=xx
      $vars['Itemid'] = intval($vars['Itemid']);
    }
    if (!empty($vars['view'])) {    // some links have view=article;
      $vars['view'] = str_replace('article;', 'article', $vars['view']);
      // view is set but no option : use default controller (com_content)
      if (empty($vars['option']))
      $vars['option'] = 'com_content';
    }
    if (empty( $vars['option']) && !empty($vars['format']) && $vars['format']=='feed') {
      $vars['option'] = 'com_content';
    }
  }
  return $vars;
}

function shNormalizeNonSefUri( & $uri, $menu = null) {  // put back a J!1.5 non-sef url to J! 1.0.x format
  // Get the route
  $route = $uri->getPath();
  //Get the query vars
  $vars = $uri->getQuery(true);
  // fix some problems in incoming URLs
  if (!empty($vars['Itemid'])) {  // sometimes we get doubles : ?Itemid=xx?Itemid=xx
    $vars['Itemid'] = intval($vars['Itemid']);
    $uri->setQuery($vars);
  }

  // fix urls obtained through a single Itemid, in menus : url is option=com_xxx&Itemid=yy
  if (count($vars) == 2 && $uri->getVar('Itemid')) {
    if (empty($menu))
    $menu = & shRouter::shGetMenu();
    $shItem = $menu->getItem($vars['Itemid']);
    if (!empty($shItem)) {  // we found the menu item
      $url = $shItem->link.'&Itemid='.$shItem->id;
      $uri = new JURI($url);	// rebuild $uri based on this new url
      $uri->setPath($route);
      $vars = $uri->getQuery(true);
    }
  }

  $vars = shRemoveSlugs($vars);
  $uri->setQuery($vars);
}

function shNormalizeNonSefUrl($url){  // returns non-sef url with slugs removed + a few fixes

  $uri = new JURI($url);
  shNormalizeNonSefUri($uri);
  return $uri->toString(array('path', 'query', 'fragment'));

}

function shSetJfLanguage( $requestlang) {

  if (empty($requestlang)) return;

  // get instance of JoomFishManager to obtain active language list and config values
  $jfm =&  JoomFishManager::getInstance();
  $activeLanguages = $jfm->getActiveLanguages();
  // get the name of the language file for joomla
  $jfLang = TableJFLanguage::createByShortcode( $requestlang, true);

  // set Joomfish stuff
  // Get the global configuration object
  global $mainframe;
  $registry =& JFactory::getConfig();
  $params = $registry->getValue("jfrouter.params");
  $enableCookie			= $params->get( 'enableCookie', 1 );

  if ($enableCookie){
    setcookie( "lang", "", time() - 1800, "/" );
    setcookie( "jfcookie", "", time() - 1800, "/" );
    setcookie( "jfcookie[lang]", $jfLang->shortcode, time()+24*3600, '/' );
  }

  $GLOBALS['iso_client_lang'] = $jfLang->shortcode;
  $GLOBALS['mosConfig_lang'] = $jfLang->code;

  $mainframe->setUserState('application.lang',$jfLang->code);
  $registry->setValue("config.jflang", $jfLang->code);
  $registry->setValue("config.lang_site",$jfLang->code);
  $registry->setValue("config.language",$jfLang->code);
  $registry->setValue("joomfish.language",$jfLang);

  // Force factory static instance to be updated if necessary
  $lang =& JFactory::getLanguage();
  if ($jfLang->code != $lang->getTag()){
    $lang = JFactory::_createLanguage();
  }

  // overwrite with the valued from $jfLang
  $params = new JParameter($jfLang->params);
  $paramarray = $params->toArray();
  foreach ($paramarray as $key=>$val) {
    $registry->setValue("config.".$key,$val);

    if (defined("_JLEGACY")){
      $name = 'mosConfig_'.$key;
      $GLOBALS[$name] = $val;
    }
  }

  // set our own data
  $GLOBALS['shMosConfig_lang']   = $lang->get('backwardlang', 'english');
  $GLOBALS['shMosConfig_locale']   = $jfLang->code;
  $GLOBALS['shMosConfig_shortcode']   = $jfLang->shortcode;

}

function shCheckRedirect ($dest, $incomingUrl) {

  $sefConfig = & shRouter::shGetConfig();
  if (!empty($dest) && $dest != $incomingUrl) {  // redirect to alias
    if ($dest == sh404SEF_HOMEPAGE_CODE) {
      if (!empty($sefConfig->shForcedHomePage)) {
        $dest = shFinalizeURL($sefConfig->shForcedHomePage);
      } else {
        $dest = shFinalizeURL($GLOBALS['shConfigLiveSite']);
      }
    } else {
      $shUri = null;
      $dest = shSefRelToAbs($dest, '', $shUri);
    }
     
    if ($dest != $incomingUrl) {
      _log('Redirecting to '. $dest .' from alias '.$incomingUrl);
      shRedirect($dest);
    }
  }
}

function shUrlSafeDisplay( $url) {
  
  $url = urldecode( $url);
  return htmlentities( $url, ENT_QUOTES);
}