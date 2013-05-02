<?php
/**
 * shCustomTags Joomla module : php module
 * Copyright (C) 2006-2007 Yannick Gaultier (shumisha). All rights reserved.
 * Released under the http://www.gnu.org/copyleft/gpl.html GNU/GPL, doc/license and credits.txt
 * This is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// V 1.2.4.t  check if sh404SEF is running
if (function_exists('shLoadPluginlanguage')) {

	// support for improved TITLE, DESCRIPTION, KEYWORDS and ROBOTS head tag
	global $shCustomTitleTag, $shCustomDescriptionTag, $shCustomKeywordsTag,
	$shCustomRobotsTag, $shCustomLangTag;
	// these variables can be set throughout your php code in components, bots or other modules
	// the last one wins !


	function shCleanUpTitle( $title) {
		return trim(trim(stripslashes(html_entity_decode($title))), '"');
	}

	function shCleanUpDesc( $desc) {
		$desc = stripslashes(html_entity_decode(strip_tags($desc, '<br><br /><p></p>'), ENT_NOQUOTES));
		$desc = str_replace('<br>',' - ', $desc);  // otherwise, one word<br >another becomes onewordanother
		$desc = str_replace('<br />',' - ', $desc);
		$desc = str_replace('<p>',' - ', $desc);
		$desc = str_replace('</p>',' - ', $desc);
		while (strpos($desc, ' -  - ') !== false) {
			$desc = str_replace(' -  - ', ' - ', $desc);
		}
		$desc = str_replace("&#39;",'\'', $desc);
		$desc = str_replace("&#039;",'\'', $desc);
		$desc = str_replace('"', '\'', $desc);
		$desc = str_replace("\r",'', $desc);
		$desc = str_replace("\n",'', $desc);
		return substr( trim($desc), 0, 512);
	}

	// utility function to insert data into an html buffer, after, instead or before
	// one or more instances of a tag. If last parameter is 'first', then only the
	// first occurence of the tag is replaced, or the new value is inserted only
	// after or before the first occurence of the tag

	function shInsertCustomTagInBuffer( $buffer, $tag, $where, $value, $firstOnly) {
		if (!$buffer || !$tag || !$value) return $buffer;
		$bits = explode($tag, $buffer);
		if (count($bits) < 2) return $buffer;
		$result = $bits[0];
		$maxCount = count($bits)-1;
		switch ($where) {
			case 'instead' :
				for ($i=0; $i < $maxCount; $i++) {
					$result .= ($firstOnly == 'first' ? ($i==0 ? $value:$tag):$value).$bits[$i+1];
				}
				break;
			case 'after' :
				for ($i=0; $i < $maxCount; $i++) {
					$result .= $tag.($firstOnly == 'first' ? ($i==0 ? $value:$tag):$value).$bits[$i+1];
				}
				break;
			default :
				for ($i=0; $i < $maxCount; $i++) {
					$result .= ($firstOnly == 'first' ? ($i==0 ? $value:$tag):$value).$tag.$bits[$i+1];
				}
				break;
		}
		return $result;
	}

	function shDoLinkReadMoreCallback($matches) {
		if (count($matches) != 3) return empty($matches) ? '' : $matches[0];
		$mask = '<td class="contentheading" width="100%">%%shM1%%title="%%shTitle%%" class="readon">%%shM2%%&nbsp;[%%shTitle%%]</a>';
		$result = str_replace('%%shM2%%', $matches[2], $mask);
		// we may have captured more than we want, if there are several articles, but only the last one has
		// a Read more link (first ones may be intro-only articles). Need to make sure we are fetching the right title
		$otherArticles = explode( '<td class="contentheading" width="100%">', $matches[1]);
		$articlesCount = count ($otherArticles);
		$matches[1] = $otherArticles[$articlesCount-1];
		unset($otherArticles[$articlesCount-1]);

		$bits = explode ('class="contentpagetitle">', $matches[1]);
		if (count ($bits) > 1) {  // there is a linked title
			$titleBits = array();
			preg_match('/(.*)(<script|<\/a>)/isU', $bits[1], $titleBits); // extract title-may still have <h1> tags
			$title = shCleanUpTitle( trim($titleBits[1]));
		} else {  // title is not linked
			$titleBits = array();
			preg_match('/(.*)(<script|<a\s*href=|<\/td>)/isU', $matches[1], $titleBits); // extract title-may still have <h1> tags
			$title = str_replace('<h1>', '', $titleBits[1]);
			$title = str_replace('</h1>', '', $title);
			$title = shCleanUpTitle( trim($title));
		}
		$result = str_replace('%%shTitle%%', $title, $result);
		// restore possible additionnal articles
		$articles = implode( '<td class="contentheading" width="100%">', $otherArticles);
		$matches[1] = (empty($articles) ? '': $articles . '<td class="contentheading" width="100%">') . $matches[1];
		$result = str_replace('%%shM1%%', $matches[1], $result);
		$result = str_replace('%%shM2%%', $matches[2], $result);
		$result = str_replace( 'class="contentpagetitle">', 'title="'.$title.'" class="contentpagetitle">', $result);
		return $result;
	}

	function shDoRedirectOutboundLinksCallback($matches) {
		if (count($matches) != 2) return empty($matches) ? '' : $matches[0];
		if (strpos($matches[1], $GLOBALS['shConfigLiveSite']) === false){
			$mask = '<a href="'.$GLOBALS['shConfigLiveSite'].'/index.php?option=com_sh404sef&shtask=redirect&shtarget=%%shM1%%"';
			$result = str_replace('%%shM1%%', $matches[1], $mask);
		} else $result = $matches[0];
		return $result;
	}


	function shDoInsertOutboundLinksImageCallback($matches) {
		//if (count($matches) != 2 && count($matches) != 3) return empty($matches) ? '' : $matches[0];
		$orig = $matches[0];
		$bits = explode('href=', $orig);
		$part2 = $bits[1];  // 2nd part, after the href=
		$sep = substr($part2, 0, 1);  // " or ' ?
		$link = trim($part2, $sep);  // remove first " or '
		if (empty($sep)) { // this should not happen, but it happens (Fireboard)
			$result = $matches[0];
			return $result;
		}
		$link = explode($sep, $link);
		$link = $link[0]; // keep only the link
			
		$shPageInfo = & shRouter::shPageInfo();
		$sefConfig = & shRouter::shGetConfig();
			
		if ( substr($link, 0, strlen($GLOBALS['shConfigLiveSite'])) != $GLOBALS['shConfigLiveSite']
		&& substr($link, 0, 7) == 'http://'
		&& substr($link, 0, strlen($shPageInfo->base)) != $shPageInfo->base){

			$mask = '%%shM1%%href="%%shM2%%" %%shM3%% >%%shM4%%<img border="0" alt="%%shM5%%" src="'
			.$GLOBALS['shConfigLiveSite'].'/components/com_sh404sef/images/'
			.$sefConfig->shImageForOutboundLinks
			.'"/></a>';

			$result = str_replace('%%shM1%%', $bits[0], $mask);
			$result = str_replace('%%shM2%%', $link, $result);

			$m3 = str_replace($sep.$link.$sep, '', str_replace('</a>', '', $part2)); // remove link from part 2
			$bits2 = explode('>', $m3);
			$m3 = $bits2[0];
			$result = str_replace('%%shM3%%', $m3, $result);

			array_shift($bits2); // remove first bit
			$m4 = implode($bits2, '>');
			$result = str_replace('%%shM4%%', $m4, $result);

			$m5 = strip_tags($m4);
			$result = str_replace('%%shM5%%', $m5, $result);

		} else $result = $matches[0];
		return $result;
	}

	function shDoTitleTags( &$buffer) {
		// Replace TITLE and DESCRIPTION and KEYWORDS
		if (empty($buffer)) return;
		global $shCustomTitleTag, $shCustomDescriptionTag, $shCustomKeywordsTag,
		$shCustomRobotsTag, $shCustomLangTag, $shHomeLink,
		$shMosConfig_lang, $shMosConfig_locale;

		$database	= & JFactory::getDBO();
		$sefConfig = & shRouter::shGetConfig();
		$shPageInfo = & shRouter::shPageInfo();  // get page details gathered by system plugin

		// V 1.2.4.t protect against error if using shCustomtags without sh404SEF activated
		// this should not happen, so we simply do nothing
		if (!isset($sefConfig) || empty( $shPageInfo->shCurrentPageNonSef)) {
			return;
		}

		// fix bug in Joomla search
		$shUri = null;
		$buffer = str_replace( 'action="index.php"', 'action="'.shSefRelToabs('index.php', '', $shUri).'"', $buffer);

		// check if there is a manually created set of tags from tags file
		// need to get them from DB
		if ($sefConfig->shMetaManagementActivated) {

			// get header data set through Joomla
			$document = & JFactory::getDocument();
			$joomlaHead = $document->getHeadData();
			$joomlaMetaData = $joomlaHead['metaTags']['standard'];
				
			//  plugin system to automatically build title and description tags on a component per component basis
			$option = JRequest::getVar( 'option');
			$shDoNotOverride = in_array( str_replace('com_', '', $option), $sefConfig->shDoNotOverrideOwnSef);

			if ((file_exists(sh404SEF_ABS_PATH.'components/'.$option.'/meta_ext/'.$option.'.php'))
			&& ($shDoNotOverride                   // and param said do not override
			|| (!$shDoNotOverride              // or param said override, but we don't have a plugin
			&& !file_exists(sh404SEF_ABS_PATH.'components/com_sh404sef/meta_ext/'.$option.'.php'))  )) {
				_log('Loading component own meta plugin');  // Load the plug-in file
				include(sh404SEF_ABS_PATH.'components/'.$option.'/meta_ext/'.$option.'.php');
			} // then look for sh404SEF own plugin
			else if (file_exists(sh404SEF_ABS_PATH.'components/com_sh404sef/meta_ext/'.$option.'.php')) {
				_log('Loading built-in meta plugin');
				include(sh404SEF_ABS_PATH.'components/com_sh404sef/meta_ext/'.$option.'.php');
			} else {  // no plugin, just use URI
				$shUri = JURI::getInstance();
				$path = $shUri->toString( array('scheme', 'host', 'port', 'path'));
				$path = str_replace($GLOBALS['shConfigLiveSite'], '', $path);
				if ($sefConfig->shRewriteMode != 0)
				$path = str_replace( $sefConfig->shRewriteStrings[$sefConfig->shRewriteMode],'', $path);
				$path = str_replace( $sefConfig->suffix, '', $path);
				$path = trim($path, '/');
				if ($path != 'index.php' && strpos( $path, 'component/option,') === false) {
					$title = explode('/', $path);
					$title = array_reverse( $title);
					$shCustomTitleTag = ltrim(implode( ' | ', $title), '/ | ');
				}
			}

			// now read manually setup tags
			if (shSortUrl($shPageInfo->shCurrentPageNonSef) == shCleanUpAnchor($shHomeLink)) { // V 1.2.4.t homepage custom tags
				$sql = 'SELECT id, metadesc, metakey, metatitle, metalang, metarobots FROM #__sh404SEF_meta WHERE newurl = \''.sh404SEF_HOMEPAGE_CODE.'\'';
			} else {
				// V 1.2.4.t make sure we have lang info and properly sorted params
				if (!preg_match( '/(&|\?)lang=[a-zA-Z]{2,3}/iU', $shPageInfo->shCurrentPageNonSef)) {  // no lang string, let's add default
					$shTemp = explode( '_', $GLOBALS['shMosConfig_locale']);
					$shLangTemp = $shTemp[0] ? $shTemp[0] : 'en';
					$shPageInfo->shCurrentPageNonSef .= '&lang='.$shLangTemp;
				}
				$shPageInfo->shCurrentPageNonSef = shSortUrl($shPageInfo->shCurrentPageNonSef);
				$sql = 'SELECT id, metadesc, metakey, metatitle, metalang, metarobots FROM #__sh404SEF_meta WHERE newurl = \''.ltrim($shPageInfo->shCurrentPageNonSef, '/').'\'';
			}
			$shCustomTags = null;
			$database->setQuery($sql);
			$shCustomTags = $database->loadObject();
			if ( !empty($shCustomTags)) {
				$shCustomTitleTag = !empty($shCustomTags->metatitle) ? $shCustomTags->metatitle : $shCustomTitleTag;
				$shCustomDescriptionTag = !empty($shCustomTags->metadesc) ? $shCustomTags->metadesc : $shCustomDescriptionTag;
				$shCustomKeywordsTag = !empty($shCustomTags->metakey) ? $shCustomTags->metakey : $shCustomKeywordsTag;
				$shCustomRobotsTag = !empty($shCustomTags->metarobots) ? $shCustomTags->metarobots : $shCustomRobotsTag;
				$shCustomLangTag = !empty($shCustomTags->metalang) ? $shCustomTags->metalang : $shCustomLangTag;
			}

			// then insert them in page
			if (!is_null($shCustomTitleTag)) {
				$buffer = preg_replace( '/\<\s*title\s*\>.*\<\s*\/title\s*\>/isU', '<title>'
				.shCleanUpTitle($sefConfig->prependToPageTitle.$shCustomTitleTag.$sefConfig->appendToPageTitle).'</title>', $buffer);
				$buffer = preg_replace( '/\<\s*meta\s+name\s*=\s*"title.*\/\>/isU', '', $buffer); // remove Joomla title meta
			}
			if (!is_null($shCustomDescriptionTag)) {
				$buffer = preg_replace( '/\<\s*meta\s+name\s*=\s*"description.*\/\>/isU', '<meta name="description" content="'
				.shCleanUpDesc($shCustomDescriptionTag).'" />', $buffer);
			}
			if (!is_null($shCustomKeywordsTag)) {
				$buffer = preg_replace( '/\<\s*meta\s+name\s*=\s*"keywords.*\/\>/isU', '<meta name="keywords" content="'
				.shCleanUpDesc($shCustomKeywordsTag).'" />', $buffer);
			}
			if (!is_null($shCustomRobotsTag)) {
				if (strpos($buffer, '<meta name="robots" content="') !== false) {
					$buffer = preg_replace( '/\<\s*meta\s+name\s*=\s*"robots.*\/\>/isU', '<meta name="robots" content="'
					.$shCustomRobotsTag.'" />', $buffer);
				}
				else if (!empty($shCustomRobotsTag)) {
					$buffer = shInsertCustomTagInBuffer( $buffer, '</head>', 'before', '<meta name="robots" content="'
					.$shCustomRobotsTag.'" />', 'first');
				}
			}
			if (!is_null($shCustomLangTag)) {
				$shLang = $shCustomLangTag;
			}
			else {
				$shLang = $shMosConfig_locale;
			}
			if (strpos($buffer, '<meta http-equiv="Content-Language"') !== false) {
				$buffer = preg_replace( '/\<\s*meta\s+http-equiv\s*=\s*"Content-Language".*\/\>/isU', '<meta http-equiv="Content-Language" content="'.$shLang.'" />', $buffer);
			}
			else if (!empty($shCustomLangTag)) {
				$buffer = shInsertCustomTagInBuffer( $buffer, '</head>', 'before', '<meta http-equiv="Content-Language" content="'.$shLang.'" />', 'first');
			}

			// remove Generator tag
			if ($sefConfig->shRemoveGeneratorTag) {
				$buffer = preg_replace( '/<meta\s*name="Generator"\s*content=".*\/>/isU','', $buffer);
			}

				
			// version x : add title to read on link
			if ($sefConfig->shInsertReadMorePageTitle) {
				if (strpos( $buffer, 'class="readon"') !== false) {
					$buffer = preg_replace_callback( '/<td class="contentheading" width="100%">(.*)class="readon">(.*)<\/a>/isU',
									'shDoLinkReadMoreCallback', $buffer);						
				}
			}
			// put <h1> tags around content elements titles
			if ($sefConfig->shPutH1Tags) {
				if (strpos($buffer, 'class="componentheading') !== false) {
					$buffer = preg_replace( '/<div class="componentheading([^>]*)>\s*(.*)\s*<\/div>/isU',
                              '<div class="componentheading$1><h1>$2</h1></div>', $buffer);    	
					$buffer = preg_replace( '/<td class="contentheading([^>]*)>\s*(.*)\s*<\/td>/isU',
                              '<td class="contentheading$1><h2>$2</h2></td>', $buffer);
				} else {  // replace contentheading by h1
					$buffer = preg_replace( '/<td class="contentheading([^>]*)>\s*(.*)\s*<\/td>/isU',
                              '<td class="contentheading$1><h1>$2</h1></td>', $buffer);
				}
			}

			// version x : if multiple h1 headings, replace them by h2
			if ($sefConfig->shMultipleH1ToH2 && substr_count( strtolower($buffer), '<h1>') > 1) {
				$buffer = str_replace( '<h1>', '<h2>', $buffer);
				$buffer = str_replace( '<H1>', '<h2>', $buffer);
				$buffer = str_replace( '</h1>', '</h2>', $buffer);
				$buffer = str_replace( '</H1>', '</h2>', $buffer);
			}

			// V 1.3.1 : replace outbounds links by internal redirects
			if (sh404SEF_REDIRECT_OUTBOUND_LINKS) {
				$buffer = preg_replace_callback( '/<\s*a\s*href\s*=\s*"(.*)"/isU',
									'shDoRedirectOutboundLinksCallback', $buffer);
			}

			// V 1.3.1 : add symbol to outbounds links
			if ($sefConfig->shInsertOutboundLinksImage) {
				$buffer = preg_replace_callback( "/<\s*a\s*href\s*=\s*(\"|').*(\"|')\s*>.*<\/a>/isU",
									'shDoInsertOutboundLinksImageCallback', $buffer);
			}

			// all done
			return $buffer;
		}
	}

	// begin main output --------------------------------------------------------

	// check we are outputting document for real
	$document = &JFactory::getDocument();
	if ($document->getType() == 'html') {
		$shPage = JResponse::getBody();

		// do TITLE and DESCRIPTION and KEYWORDS and ROBOTS tags replacement
		shDoTitleTags( $shPage);

		if (SH_SHOW_CACHE_STATS)
		$shPage .= shShowCacheStats();
			
		JResponse::setBody($shPage);
	}
	//	}
}

?>
