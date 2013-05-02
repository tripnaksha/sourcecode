<?php
// config.sef.php : configuration file for sh404SEF for Joomla 1.5.x
// 1.0.20_Beta - build_237 - Joomla 1.5.x - <a href="http://extensions.siliana.com/">extensions.Siliana.com</a>
// saved at: 2010-03-24 12:43:03
// by: ajyrds (id: 62 )
// domain: http://www.tripnaksha.com

if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

$version = "1.0.20_Beta - build_237 - Joomla 1.5.x - <a href=\"http://extensions.siliana.com/\">extensions.Siliana.com</a>";
$Enabled = "0";
$replacement = "-";
$pagerep = "-";
$stripthese = ",|~|!|@|%|^|(|)|<|>|:|;|{|}|[|]|&|`|â€ž|â€¹|â€™|â€˜|â€œ|â€�|â€¢|â€º|Â«|Â´|Â»|Â°";
$shReplacements = "Å |S, Å’|O, Å½|Z, Å¡|s, Å“|oe, Å¾|z, Å¸|Y, Â¥|Y, Âµ|u, Ã€|A, Ã�|A, Ã‚|A, Ãƒ|A, Ã„|A, Ã…|A, Ã†|A, Ã‡|C, Ãˆ|E, Ã‰|E, ÃŠ|E, Ã‹|E, ÃŒ|I, Ã�|I, ÃŽ|I, Ã�|I, Ã�|D, Ã‘|N, Ã’|O, Ã“|O, Ã”|O, Ã•|O, Ã–|O, Ã˜|O, Ã™|U, Ãš|U, Ã›|U, Ãœ|U, Ã�|Y, ÃŸ|s, Ã |a, Ã¡|a, Ã¢|a, Ã£|a, Ã¤|a, Ã¥|a, Ã¦|a, Ã§|c, Ã¨|e, Ã©|e, Ãª|e, Ã«|e, Ã¬|i, Ã­|i, Ã®|i, Ã¯|i, Ã°|o, Ã±|n, Ã²|o, Ã³|o, Ã´|o, Ãµ|o, Ã¶|o, Ã¸|o, Ã¹|u, Ãº|u, Ã»|u, Ã¼|u, Ã½|y, Ã¿|y, ÃŸ|ss";
$suffix = ".html";
$addFile = "";
$friendlytrim = "-|.";
$LowerCase = "0";
$ShowSection = "0";
$ShowCat = "1";
$UseAlias = "1";
$page404 = "175";
$predefined = array("frontpage","sh404sef");
$skip = array("bca-rss-syndicator","eventlist","firstpage","savetrail","searchtrest","showalltrails","trailembed");
$nocache = array();
$shDoNotOverrideOwnSef = array();
$shLog404Errors = "1";
$shUseURLCache = "0";
$shMaxURLInCache = "10000";
$shTranslateURL = "1";
$shInsertLanguageCode = "1";
$notTranslateURLList = array();
$notInsertIsoCodeList = array();
$shInsertGlobalItemidIfNone = "0";
$shInsertTitleIfNoItemid = "0";
$shAlwaysInsertMenuTitle = "0";
$shAlwaysInsertItemid = "0";
$shDefaultMenuItemName = "";
$shAppendRemainingGETVars = true;
$shVmInsertShopName = "0";
$shInsertProductId = "0";
$shVmUseProductSKU = "0";
$shVmInsertManufacturerName = "0";
$shInsertManufacturerId = "0";
$shVMInsertCategories = "1";
$shVmAdditionalText = "1";
$shVmInsertFlypage = "1";
$shInsertCategoryId = "0";
$shInsertNumericalId = "0";
$shInsertNumericalIdCatList = array("1","3","4","5","6","8","9","17","2","7");
$shRedirectNonSefToSef = "1";
$shRedirectJoomlaSefToSef = "1";
$shConfig_live_secure_site = "";
$shActivateIJoomlaMagInContent = "1";
$shInsertIJoomlaMagIssueId = "0";
$shInsertIJoomlaMagName = "0";
$shInsertIJoomlaMagMagazineId = "0";
$shInsertIJoomlaMagArticleId = "0";
$shInsertCBName = "0";
$shCBInsertUserName = "0";
$shCBInsertUserId = "1";
$shCBUseUserPseudo = "1";
$shLMDefaultItemid = "0";
$shInsertFireboardName = "0";
$shFbInsertCategoryName = "1";
$shFbInsertCategoryId = "0";
$shFbInsertMessageSubject = "1";
$shFbInsertMessageId = "1";
$shInsertMyBlogName = "0";
$shMyBlogInsertPostId = "1";
$shMyBlogInsertTagId = "0";
$shMyBlogInsertBloggerId = "1";
$shInsertDocmanName = "0";
$shDocmanInsertDocId = "1";
$shDocmanInsertDocName = "1";
$shDMInsertCategories = "1";
$shDMInsertCategoryId = "0";
$shEncodeUrl = "0";
$guessItemidOnHomepage = "0";
$shForceNonSefIfHttps = "0";
$shRewriteMode = "0";
$shRewriteStrings = array("/","/index.php/","/index.php?/");
$shRecordDuplicates = "1";
$shRemoveGeneratorTag = "1";
$shPutH1Tags = "0";
$shMetaManagementActivated = "1";
$shInsertContentTableName = "1";
$shContentTableName = "Table";
$shAutoRedirectWww = "1";
$shVmInsertProductName = "1";
$shForcedHomePage = "";
$shInsertContentBlogName = "0";
$shContentBlogName = "";
$shInsertMTreeName = "0";
$shMTreeInsertListingName = "1";
$shMTreeInsertListingId = "1";
$shMTreePrependListingId = "1";
$shMTreeInsertCategories = "1";
$shMTreeInsertCategoryId = "0";
$shMTreeInsertUserName = "1";
$shMTreeInsertUserId = "1";
$shInsertNewsPName = "0";
$shNewsPInsertCatId = "0";
$shNewsPInsertSecId = "0";
$shInsertRemoName = "0";
$shRemoInsertDocId = "1";
$shRemoInsertDocName = "1";
$shRemoInsertCategories = "1";
$shRemoInsertCategoryId = "0";
$shCBShortUserURL = "0";
$shKeepStandardURLOnUpgrade = "1";
$shKeepCustomURLOnUpgrade = "1";
$shKeepMetaDataOnUpgrade = "1";
$shKeepModulesSettingsOnUpgrade = true;
$shMultipagesTitle = "1";
$encode_page_suffix = "";
$encode_space_char = "-";
$encode_lowercase = "0";
$encode_strip_chars = ",|~|!|@|%|^|(|)|<|>|:|;|{|}|[|]|&|`|â€ž|â€¹|â€™|â€˜|â€œ|â€�|â€¢|â€º|Â«|Â´|Â»|Â°";
$spec_chars_d = "Å ,Å’,Å½,Å¡,Å“,Å¾,Å¸,Â¥,Âµ,Ã€,Ã�,Ã‚,Ãƒ,Ã„,Ã…,Ã†,Ã‡,Ãˆ,Ã‰,ÃŠ,Ã‹,ÃŒ,ÃŽ,Ã‘,Ã’,Ã“,Ã”,Ã•,Ã–,Ã˜,Ã™,Ãš,Ã›,Ãœ,ÃŸ,Ã ,Ã¡,Ã¢,Ã£,Ã¤,Ã¥,Ã¦,Ã§,Ã¨,Ã©,Ãª,Ã«,Ã¬,Ã­,Ã®,Ã¯,Ã°,Ã±,Ã²,Ã³,Ã´,Ãµ,Ã¶,Ã¸,Ã¹,Ãº,Ã»,Ã¼,Ã½,Ã¿,";
$spec_chars = "S,O,Z,s,oe,z,Y,Y,u,A,Y,A,A,A,A,A,C,E,E,E,E,I,I,N,O,O,O,O,O,O,U,U,U,U,ss,a,a,a,a,a,a,a,c,e,e,e,e,i,i,i,i,o,n,o,o,o,o,o,o,u,u,u,u,y,y,";
$content_page_format = "%s-%d";
$content_page_name = "Page-";
$shKeepConfigOnUpgrade = "1";
$shSecEnableSecurity = "1";
$shSecLogAttacks = "1";
$shSecOnlyNumVars = array();
$shSecAlphaNumVars = array();
$shSecNoProtocolVars = array();
$shSecCheckHoneyPot = "0";
$shSecHoneyPotKey = "";
$shSecEntranceText = "<p>Sorry. You are visiting this site from a suspicious IP address, which triggered our protection system.</p>
    <p>If you <strong>ARE NOT</strong> a malware robot of any kind, please accept our apologies for the unconvenience. You can access the page by clicking here : ";
$shSecSmellyPotText = "The following link is here to further trap malicious internet robots, so please don't click on it : ";
$monthsToKeepLogs = "1";
$shSecActivateAntiFlood = "1";
$shSecAntiFloodOnlyOnPOST = "0";
$shSecAntiFloodPeriod = "10";
$shSecAntiFloodCount = "10";
$shLangTranslateList = array("en-GB"=>"0");
$shLangInsertCodeList = array("en-GB"=>"0");
$defaultComponentStringList = array("bca-rss-syndicator"=>"","comprofiler"=>"","contact"=>"","content"=>"","contentsubmit"=>"","eventlist"=>"","firstpage"=>"","jce"=>"","newsfeeds"=>"","poll"=>"","savetrail"=>"","search"=>"","searchtrest"=>"","showalltrails"=>"","tag"=>"","traildisplay"=>"","trailembed"=>"","user"=>"","weblinks"=>"","wrapper"=>"","xmap"=>"","yvcomment"=>"");
$pageTexts = array("en-GB"=>"Page-%s");
$shAdminInterfaceType = 2;
$shInsertNoFollowPDFPrint = true;
$shInsertReadMorePageTitle = "1";
$shMultipleH1ToH2 = "1";
$shVmUsingItemsPerPage = "0";
$shSecCheckPOSTData = "1";
$shSecCurMonth = 0;
$shSecLastUpdated = 0;
$shSecTotalAttacks = 0;
$shSecTotalConfigVars = 0;
$shSecTotalBase64 = 0;
$shSecTotalScripts = 0;
$shSecTotalStandardVars = 0;
$shSecTotalImgTxtCmd = 0;
$shSecTotalIPDenied = 0;
$shSecTotalUserAgentDenied = 0;
$shSecTotalFlooding = 0;
$shSecTotalPHP = 0;
$shSecTotalPHPUserClicked = 0;
$shInsertSMFName = "1";
$shSMFItemsPerPage = "20";
$shInsertSMFBoardId = "1";
$shInsertSMFTopicId = "1";
$shinsertSMFUserName = "0";
$shInsertSMFUserId = "1";
$appendToPageTitle = "";
$prependToPageTitle = "";
$debugToLogFile = "0";
$debugStartedAt = 0;
$debugDuration = 3600;
$shInsertOutboundLinksImage = "0";
$shImageForOutboundLinks = "external-black.png";
$useCatAlias = "0";
$useSecAlias = "0";
$useMenuAlias = "0";
$shEnableTableLessOutput = "0";
$fileAccessStatus = array(" <b><font color=\"green\">Writeable</font></b>"," <b><font color=\"green\">Writeable</font></b>"," <b><font color=\"green\">Writeable</font></b>"," <b><font color=\"green\">Writeable</font></b>"," <b><font color=\"green\">Writeable</font></b>");
?>