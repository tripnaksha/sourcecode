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

require_once (JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_sef' . DS . 'tables' . DS . 'extension.php');
jimport('joomla.filesystem.file');

define('_COM_SEF_PRIORITY_DEFAULT_ITEMID', 90);
define('_COM_SEF_PRIORITY_DEFAULT', 95);

class SEFTools
{

    function getSEFVersion()
    {
        static $version;

        if (! isset($version)) {
            $xml = JFactory::getXMLParser('Simple');

            $xmlFile = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_sef' . DS . 'sef.xml';

            if (JFile::exists($xmlFile)) {
                if ($xml->loadFile($xmlFile)) {
                    $root = & $xml->document;
                    $element = & $root->getElementByPath('version');
                    $version = $element ? $element->data() : '';
                }
            }
        }

        return $version;
    }
    
    function getSEFInfo()
    {
        static $info;
        
        if( !isset($info) ) {
            $info = array();
            
            $xml = JFactory::getXMLParser('Simple');

            $xmlFile = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_sef' . DS . 'sef.xml';

            if (JFile::exists($xmlFile)) {
                if ($xml->loadFile($xmlFile)) {
                    $root = & $xml->document;
                    
                    $element = & $root->getElementByPath('version');
                    $info['version'] = $element ? $element->data() : '';
                    
                    $element = & $root->getElementByPath('creationdate');
                    $info['creationDate'] = $element ? $element->data() : '';
                    
                    $element = & $root->getElementByPath('author');
                    $info['author'] = $element ? $element->data() : '';
                    
                    $element = & $root->getElementByPath('authoremail');
                    $info['authorEmail'] = $element ? $element->data() : '';
                    
                    $element = & $root->getElementByPath('authorurl');
                    $info['authorUrl'] = $element ? $element->data() : '';
                    
                    $element = & $root->getElementByPath('copyright');
                    $info['copyright'] = $element ? $element->data() : '';
                    
                    $element = & $root->getElementByPath('license');
                    $info['license'] = $element ? $element->data() : '';
                    
                    $element = & $root->getElementByPath('description');
                    $info['description'] = $element ? $element->data() : '';
                }
            }
        }
        
        return $info;
    }

    function getExtVersion($extension)
    {
        $xml = & SEFTools::getExtXML($extension);
        $version = null;

        if ($xml) {
            $root = & $xml->document;
            $ver = $root->attributes('version');
            if (($root->name() == 'install') && version_compare($ver, '1.5', '>=') && ($root->attributes('type') == 'sef_ext')) {
                $element = & $root->getElementByPath('version');
                $version = $element ? $element->data() : '';
            }
        }

        return $version;
    }

    /**
     * Returns extension name from its XML file.
     *
     * @return string
     */
    function getExtName($extension)
    {
        $xml = & SEFTools::getExtXML($extension);
        $name = null;

        if ($xml) {
            $root = & $xml->document;
            $ver = $root->attributes('version');
            if (($root->name() == 'install') && version_compare($ver, '1.5', '>=') && ($root->attributes('type') == 'sef_ext')) {
                $element = & $root->getElementByPath('name');
                $name = $element ? $element->data() : '';
            }
        }

        return $name;
    }

    /**
     * Returns the extension XML object
     *
     * @param string $extension     Extension option
     * @return JSimpleXML           Extension XML
     */
    function &getExtXML($extension)
    {
        static $xmls;

        if (! isset($xmls)) {
            $xmls = array();
        }

        if (! isset($xmls[$extension])) {
            $xmls[$extension] = null;

            $xmlFile = JPATH_ROOT . DS . 'components' . DS . 'com_sef' . DS . 'sef_ext' . DS . $extension . '.xml';
            if (JFile::exists($xmlFile)) {
                $xmls[$extension] = JFactory::getXMLParser('Simple');
                if (! $xmls[$extension]->loadFile($xmlFile)) {
                    $xmls[$extension] = null;
                }
            }
        }

        return $xmls[$extension];
    }
    
    function &getExtAcceptVars($option)
    {
        static $acceptVars;
        
        if( !isset($acceptVars) ) {
            $acceptVars = array();
        }
        
        if( !isset($acceptVars[$option]) ) {
            $params =& SEFTools::getExtParams($option);
            $aVars = trim($params->get('acceptVars', ''));
            
            if( $aVars == '' ) {
                $acceptVars[$option] = array();
            }
            else {
                $acceptVars[$option] = explode(';', $aVars);
                $acceptVars[$option] = array_map('trim', $acceptVars[$option]);
            }
        }
        
        return $acceptVars[$option];
    }
    
    function &getExtFilters($option)
    {
        static $filters;
        
        if( !isset($filters) ) {
            $filters = array();
        }
        
        if( !isset($filters[$option]) ) {
            $filters[$option] = array();
            $filters[$option]['pos'] = array();
            $filters[$option]['neg'] = array();
            
            $db =& JFactory::getDBO();
            
            $db->setQuery("SELECT `filters` FROM `#__sefexts` WHERE `file` = '{$option}.xml' LIMIT 1");
            $row = $db->loadResult();
            
            if( $row ) {
                // Parse the filters
                $rules = explode("\n", $row);
                $rules = array_map('trim', $rules);
                
                if( count($rules) > 0 ) {
                    foreach($rules as $rule) {
                        // Is the rule positive or negative?
                        if( $rule[0] == '+' ) {
                            $type = 'pos';
                        }
                        else if( $rule[0] == '-' ) {
                            $type = 'neg';
                        }
                        else {
                            continue;
                        }
                        
                        $rule = substr($rule, 1);
                        
                        // Split the rule to regexp and variables parts
                        $pos = strrpos($rule, '=');
                        if( $pos === false ) {
                            continue;
                        }
                        
                        $re = substr($rule, 0, $pos);
                        $vars = substr($rule, $pos + 1);
                        if( $re == '' || $vars == '' ) {
                            continue;
                        }
                        
                        // Create the filter object
                        $filter = new stdClass();
                        $filter->rule = $re;
                        $filter->vars = array_map('trim', explode(',', $vars));
                        
                        // Add the filter to filters
                        $filters[$option][$type][] = $filter;
                    }
                }
            }
        }
        
        return $filters[$option];
    }
    
    function &getExtFiltersByVars($option)
    {
        static $byVars;
        
        if( !isset($byVars) ) {
            $byVars = array();
        }
        
        if( !isset($byVars[$option]) ) {
            $byVars[$option] = array();
            
            // Get filters
            $filters =& SEFTools::getExtFilters($option);
            if( count($filters) > 0 ) {
                // Loop through filter types (pos, neg)
                foreach($filters as $type => $typeFilters) {
                    if( count($typeFilters) > 0 ) {
                        // Loop through filters
                        foreach($typeFilters as $filter) {
                            if( count($filter->vars) > 0 ) {
                                // Loop through variables
                                foreach($filter->vars as $var) {
                                    // Add filter to var and type
                                    if( !isset($byVars[$option][$var]) ) {
                                        $byVars[$option][$var] = array();
                                    }
                                    if( !isset($byVars[$option][$var][$type]) ) {
                                        $byVars[$option][$var][$type] = array();
                                    }
                                    $byVars[$option][$var][$type][] = $filter->rule;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $byVars[$option];
    }

    function getLangCode($langTag = null)
    {
        // Get current language tag
        if (is_null($langTag)) {
            $lang = & JFactory::getLanguage();
            $langTag = $lang->getTag();
        }

        $jfm = & JoomFishManager::getInstance();
        $code = $jfm->getLanguageCode($langTag);

        return $code;
    }

    function getLangId($langTag = null)
    {
        // Get current language tag
        if (is_null($langTag)) {
            $lang = & JFactory::getLanguage();
            $langTag = $lang->getTag();
        }

        $jfm = & JoomFishManager::getInstance();
        $id = $jfm->getLanguageID($langTag);

        return $id;
    }

    function getLangLongCode($langCode = null)
    {
        static $codes;

        // Get current language code
        if (is_null($langCode)) {
            $lang = & JFactory::getLanguage();
            return $lang->getTag();
        }

        if (is_null($codes)) {
            $codes = array();

            $jfm = & JoomFishManager::getInstance();
            $langs = & $jfm->getLanguages(false);
            if (! empty($langs)) {
                foreach ($langs as $lang) {
                    $codes[$lang->shortcode] = $lang->code;
                }
            }
        }

        if (isset($codes[$langCode])) {
            return $codes[$langCode];
        }

        return null;
    }

    /**
     * Returns JParameter object representing extension's parameters
     *
     * @param	string          Extension name
     * @return	JParameter      Extension's parameters
     */
    function &getExtParams($option)
    {
        $db = & JFactory::getDBO();

        static $exts, $params;

        if (! isset($exts)) {
            $query = "SELECT `file`, `params` FROM `#__sefexts`";
            $db->setQuery($query);
            $exts = $db->loadObjectList('file');
        }

        if (! isset($params)) {
            $params = array();
        }
        if (! isset($params[$option])) {
            $data = '';
            if (isset($exts[$option . '.xml'])) {
                $data = $exts[$option . '.xml']->params;
            }
            $params[$option] = new JParameter($data);

            // Set the extension's parameters renderer
            $pxml = & SEFTools::getExtParamsXML($option);
            if (is_a($pxml, 'JSimpleXMLElement')) {
                $params[$option]->setXML($pxml);
            }
            else if( is_array($pxml) && count($pxml) > 0 ) {
                for( $i = 0, $n = count($pxml); $i < $n; $i++ ) {
                    if( is_a($pxml[$i], 'JSimpleXMLElement') ) {
                        $params[$option]->setXML($pxml[$i]);
                    }
                }
            }
            
            // Set the default parameters renderer
            $xml = & SEFTools::getExtsDefaultParamsXML();
            if (is_a($xml, 'JSimpleXMLElement')) {
                $p = & $xml->getElementByPath('params');
                $params[$option]->setXML($p);
            }            
        }

        return $params[$option];
    }

    /**
     * Returns the JSimpleXMLElement object representing
     * the default parameters for every extension
     * 
     * @return JSimpleXMLElement	Extensions' default parameters
     */
    function &getExtsDefaultParamsXML()
    {
        static $xml;

        if (isset($xml)) {
            return $xml;
        }

        $xml = null;
        $xmlpath = JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_sef' . DS . 'extensions_params.xml';

        if (JFile::exists($xmlpath)) {
            $parser = JFactory::getXMLParser('Simple');
            if ($parser->loadFile($xmlpath)) {
                $xml = & $parser->document;
            }
        }

        return $xml;
    }

    /**
     * Returns the JSimpleXMLElement object representing
     * the extension's parameters
     *
     * @param string $option		Extension name
     * @return JSimpleXMLElement	Extension's parameters
     */
    function &getExtParamsXML($option)
    {
        static $xmls;

        if (! isset($xmls)) {
            $xmls = array();
        }

        if (! isset($xmls[$option])) {
            $xmls[$option] = null;

            $xml = & SEFTools::getExtXML($option);

            if ($xml) {
                $document = & $xml->document;

                $xmls[$option] = array();
                
                if( isset($document->params) ) {
                    for( $i = 0, $n = count($document->params); $i < $n; $i++) {
                        if( isset($document->params[$i]->param) ) {
                            if( $i == 0 ) {
                                // Remove the parameters that are duplicate with common ones
                                $hide = array();
                                $hideNames = array('ignoreSource' , 'itemid' , 'overrideId' , 'customNonSef');
            
                                // Collect elements to remove
                                for ($j = 0, $m = count($document->params[$i]->param); $j < $m; $j++) {
                                    if (in_array($document->params[$i]->param[$j]->attributes('name'), $hideNames)) {
                                        $hide[] = & $document->params[$i]->param[$j];
                                    }
                                }
            
                                // Remove elements
                                for ($j = 0, $m = count($hide); $j < $m; $j++) {
                                    $document->params[$i]->removeChild($hide[$j]);
                                }
                            }
        
                            $xmls[$option][] =& $document->params[$i];
                        }
                    }
                }
            }
        }

        return $xmls[$option];
    }

    /** Returns the array of texts used by the extension for creating URLs
     *  in currently selected language (for JoomFish support)
     *
     * @param	string  Extension name
     * @return	array   Extension's texts
     */
    function getExtTexts($option, $lang = '')
    {
        $database = & JFactory::getDBO();

        static $extTexts;

        if ($option == '') {
            return false;
        }

        // Set the language
        if ($lang == '') {
            $lang = SEFTools::getLangLongCode();
        }
        if (! isset($extTexts)) {
            $extTexts = array();
        }
        if (! isset($extTexts[$option])) {
            $extTexts[$option] = array();
        }
        if (! isset($extTexts[$option][$lang])) {
            $extTexts[$option][$lang] = array();
            // If lang is different than current language, change it
            if ($lang != SEFTools::getLangLongCode()) {
                $language = & JFactory::getLanguage();
                $oldLang = $language->setLanguage($lang);
                $language->load();
            }
            $query = "SELECT `id`, `name`, `value` FROM `#__sefexttexts` WHERE `extension` = '$option'";
            $database->setQuery($query);
            $texts = $database->loadObjectList();
            if (is_array($texts) && (count($texts) > 0)) {
                foreach (array_keys($texts) as $i) {
                    $name = $texts[$i]->name;
                    $value = $texts[$i]->value;
                    $extTexts[$option][$lang][$name] = $value;
                }
            }
            // Set the language back to previously selected one
            if (isset($oldLang) && ($oldLang != SEFTools::getLangLongCode())) {
                $language = & JFactory::getLanguage();
                $language->setLanguage($oldLang);
                $language->load();
            }
        }
        return $extTexts[$option][$lang];
    }

    function removeVariable($url, $var, $value = '')
    {
        if ($value == '') {
            //$newurl = eregi_replace("(&|\?)$var=[^&]*", '\\1', $url);
            
            $regex = "(&|\?)$var=[^&]*";
            $regex = addcslashes($regex, '/');
            $newurl = preg_replace('/' . $regex . '/i', '$1', $url);
        } else {
            $trans = array('?' => '\\?' , '.' => '\\.' , '+' => '\\+' , '*' => '\\*' , '^' => '\\^' , '$' => '\\$');
            $value = strtr($value, $trans);
            //$newurl = eregi_replace("(&|\?)$var=$value(&|\$)", '\\1\\2', $url);
            $regex = "(&|\?)$var=$value(&|\$)";
            $regex = addcslashes($regex, '/');
            $newurl = preg_replace('/' . $regex . '/i', '$1$2', $url);
        }
        $newurl = trim($newurl, '&?');
        $trans = array('&&' => '&' , '?&' => '?');
        $newurl = strtr($newurl, $trans);

        return $newurl;
    }

    function getVariable($url, $var)
    {
        $value = null;
        $matches = array();

        if( preg_match("/[&\?]$var=([^&]*)/", $url, $matches) > 0 ) {
            $value = $matches[1];
        }

        return $value;
    }

    function extractVariable(&$url, $var)
    {
        $value = SEFTools::getVariable($url, $var);
        $url = SEFTools::removeVariable($url, $var);

        return $value;
    }

    function fixVariable(&$uri, $varName)
    {
        $value = $uri->getVar($varName);
        if (! is_null($value)) {
            $pos = strpos($value, ':');
            if ($pos !== false) {
                $value = substr($value, 0, $pos);
                $uri->setVar($varName, $value);
            }
        }
    }
    
    /**
     * Removes given variables from URI and returns a query string
     * built of them
     *
     * @param JURI $uri
     * @param array $vars Variables to remove
     */
    function RemoveVariables(&$uri, &$vars)
    {
        $query = array();
        if (is_array($vars) && count($vars) > 0) {
            foreach($vars as $var) {
                // Get the variable value
                $value = $uri->getVar($var);
                
                // Skip variables not present in URL
                if( is_null($value) ) {
                    continue;
                }
                
                // Add variable to query
                if( is_array($value) ) {
                    // Variable is an array, let's remove all its occurences
                    foreach($value as $key => $val) {
                        $query[] = $var.'['.$key.']='.urlencode($val);
                    }
                }
                else {
                    // Variable is not an array
                    $query[] = $var.'='.urlencode($value);
                }
                
                // Remove variable from URI
                $uri->delVar($var);
            }
        }
        $query = implode('&amp;', $query);
        
        return $query;
    }

    function ReplaceAll($search, $replace, $subject)
    {
        while (strpos($subject, $search) !== false) {
            $subject = str_replace($search, $replace, $subject);
        }

        return $subject;
    }

    /**
     * Checks whether JoomFish is installed
     *
     * @return boolean
     */
    function JoomFishInstalled()
    {
        static $installed;

        if (! isset($installed)) {
            $installed = JFile::exists(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_joomfish' . DS . 'joomfish.php');
        }

        return $installed;
    }

    /**
     * Checks whether to use alias from extension parameter value
     *
     * @param string $params
     * @param string $paramName
     * @return boolean
     */
    function UseAlias(&$params, $paramName)
    {
        $sefConfig =& SEFConfig::getConfig();

        $param = $params->get($paramName, 'global');
        if( ($param == 'alias') ||
            ($param == 'global' && $sefConfig->useAlias) )
        {
            return true;
        }
        
        return false;
    }
    
	/**
	 * Convert description of extensions from html to plain for metatags
	 * 
	 * @param string $text
	 * @return string
	 */
	function cleanDesc($text) {
		// Remove javascript
		$regex = "'<script[^>]*?>.*?</script>'si";
		$text = preg_replace($regex, " ", $text);
		$regex = "'<noscript[^>]*?>.*?</noscript>'si";
		$text = preg_replace($regex, " ", $text);
		
		// Strip any remaining html tags
        $text = strip_tags($text);
		
		// Remove any mambot codes
		$regex = '(\{.*?\})';
		$text = preg_replace($regex, " ", $text);
		
		// Some replacements
        $text = str_replace(array('\n', '\r', '"'), array(' ', '', '&quot;'), $text);
        $text = trim($text);
		
        return $text;
    }
	
	/**
	 * Clip text to use as meta description
	 * 
	 * @param string $text
	 * @param int $limit
	 * @return string
	 */
	function clipDesc($text, $limit) {
        if (strlen($text) > $limit) {
            $text = substr($text, 0, $limit);
            $pos = strrpos($text, ' ');
            if ($pos !== false) {
                $text = substr($text, 0, $pos - 1);
            }
            $text = trim($text);
        }
		return $text;
	}
	
	/**
	 * Generate for metatags
	 * 
	 * @param string $desc
	 * @param string $blacklist
	 * @param int $count
	 * @param int $minLength
	 * @return string
	 */
	function generateKeywords($desc, $blacklist, $count, $minLength) {
		// Remove any email addresses
		$regex = '/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)(\\.[A-Za-z0-9-]+)*)/iex';
		$desc = preg_replace($regex, '', $desc);
		// Some unwanted replaces
        $desc = preg_replace('/<[^>]*>/', ' ', $desc);	
		$desc = preg_replace('/[\.;:|\'|\"|\`|\,|\(|\)|\-]/', ' ', $desc);	
		$keysArray = explode(" ", $desc);
		// Sort words from up to down
		$keysArray = array_count_values(array_map(array('JoomSEF', '_utf8LowerCase'), $keysArray));
		
		if( is_null($blacklist) ) {
		    $blacklist = "a, able, about, above, abroad, according, accordingly, across, actually, adj, after, afterwards, again, against, ago, ahead, ain't, all, allow, allows, almost, alone, along, alongside, already, also, although, always, am, amid, amidst, among, amongst, an, and, another, any, anybody, anyhow, anyone, anything, anyway, anyways, anywhere, apart, appear, appreciate, appropriate, are, aren't, around, as, a's, aside, ask, asking, associated, at, available, away, awfully, b, back, backward, backwards, be, became, because, become, becomes, becoming, been, before, beforehand, begin, behind, being, believe, below, beside, besides, best, better, between, beyond, both, brief, but, by, c, came, can, cannot, cant, can't, caption, cause, causes, certain, certainly, changes, clearly, c'mon, co, co., com, come, comes, concerning, consequently, consider, considering, contain, containing, contains, corresponding, could, couldn't, course, c's, currently, d, dare, daren't, definitely, described, despite, did, didn't, different, directly, do, does, doesn't, doing, done, don't, down, downwards, during, e, each, edu, eg, eight, eighty, either, else, elsewhere, end, ending, enough, entirely, especially, et, etc, even, ever, evermore, every, everybody, everyone, everything, everywhere, ex, exactly, example, except, f, fairly, far, farther, few, fewer, fifth, first, five, followed, following, follows, for, forever, former, formerly, forth, forward, found, four, from, further, furthermore, g, get, gets, getting, given, gives, go, goes, going, gone, got, gotten, greetings, h, had, hadn't, half, happens, hardly, has, hasn't, have, haven't, having, he, he'd, he'll, hello, help, , hence, her, here, hereafter, hereby, herein, here's, hereupon, hers, herself, he's, hi, him, himself, his, hither, hopefully, how, howbeit, however, hundred, i, i'd, ie, if, ignored, i'll, i'm, immediate, in, inasmuch, inc, inc., indeed, indicate, indicated, indicates, inner, inside, insofar, instead, into, inward, is, isn't, it, it'd, it'll, its, it's, itself, i've, j, just, k, keep, keeps, kept, know, known, knows, l, last, lately, later, latter, latterly, least, less, lest, let, let's, like, liked, likely, likewise, little, look, looking, looks, low, lower, ltd, m, made, mainly, make, makes, many, may, maybe, mayn't, me, mean, meantime, meanwhile, merely, might, mightn't, mine, minus, miss, more, moreover, most, mostly, mr, mrs, much, must, mustn't, my, myself, n, name, namely, nd, near, nearly, necessary, need, needn't, needs, neither, never, neverf, neverless, nevertheless, new, next, nine, ninety, no, nobody, non, none, nonetheless, noone, no-one, nor, normally, not, nothing, notwithstanding, novel, now, nowhere, o, obviously, of, off, often, oh, ok, okay, old, on, once, one, ones, one's, only, onto, opposite, or, other, others, otherwise, ought, oughtn't, our, ours, ourselves, out, outside, over, overall, own, p, particular, particularly, past, per, perhaps, placed, please, plus, possible, presumably, probably, provided, provides, q, que, quite, qv, r, rather, rd, re, really, reasonably, recent, recently, regarding, regardless, regards, relatively, respectively, right, round, s, said, same, saw, say, saying, says, second, secondly, , see, seeing, seem, seemed, seeming, seems, seen, self, selves, sensible, sent, serious, seriously, seven, several, shall, shan't, she, she'd, she'll, she's, should, shouldn't, since, six, so, some, somebody, someday, somehow, someone, something, sometime, sometimes, somewhat, somewhere, soon, sorry, specified, specify, specifying, still, sub, such, sup, sure, t, take, taken, taking, tell, tends, th, than, thank, thanks, thanx, that, that'll, thats, that's, that've, the, their, theirs, them, themselves, then, thence, there, thereafter, thereby, there'd, therefore, therein, there'll, there're, theres, there's, thereupon, there've, these, they, they'd, they'll, they're, they've, thing, things, think, third, thirty, this, thorough, thoroughly, those, though, three, through, throughout, thru, thus, till, to, together, too, took, toward, towards, tried, tries, truly, try, trying, t's, twice, two, u, un, under, underneath, undoing, unfortunately, unless, unlike, unlikely, until, unto, up, upon, upwards, us, use, used, useful, uses, using, usually, v, value, various, versus, very, via, viz, vs, w, want, wants, was, wasn't, way, we, we'd, welcome, well, we'll, went, were, we're, weren't, we've, what, whatever, what'll, what's, what've, when, whence, whenever, where, whereafter, whereas, whereby, wherein, where's, whereupon, wherever, whether, which, whichever, while, whilst, whither, who, who'd, whoever, whole, who'll, whom, whomever, who's, whose, why, will, willing, wish, with, within, without, wonder, won't, would, wouldn't, x, y, yes, yet, you, you'd, you'll, your, you're, yours, yourself, yourselves, you've, z, zero";
		}
		$blackArray = explode(",", $blacklist);
		
	    foreach($blackArray as $blackWord){
		    if(isset($keysArray[trim($blackWord)]))
				unset($keysArray[trim($blackWord)]);
		}
		
		arsort($keysArray);
		
		$i = 1;
		$keywords = '';
		foreach($keysArray as $word=>$instances){
			if($i > $count)
				break;
			if(strlen(trim($word)) >= $minLength ) {
				$keywords .= $word . ", ";
				$i++;
			}
		}
		
		$keywords = rtrim($keywords, ", ");
		return $keywords;
    }
    
    function GetSEFGlobalMeta() {
        return '6953dd99d3e99ba8dbae11769e6567a0'; // sef.global.meta
    }
	
    /**
     * Sends the POST request
     *
     * @param string $url
     * @param string $referer
     * @param array $_data
     * @return object
     */
    function PostRequest($url, $referer = null, $_data = null) {
     
        // convert variables array to string:
        $data = '';
        if( is_array($_data) && count($_data) > 0 ) {
            // format --> test1=a&test2=b etc.
            $data = array();
            while( list($n, $v) = each($_data) ) {
                $data[] = "$n=$v";
            }    
            $data = implode('&', $data);
        }
        
        if( is_null($referer) ) {
            $referer = JURI::root();
        }
     
        // parse the given URL
        $url = parse_url($url);
        if( !isset($url['scheme']) || ($url['scheme'] != 'http') ) { 
            return false;
        }
     
        // extract host and path:
        $host = $url['host'];
        $path = isset($url['path']) ? $url['path'] : '/';
     
        // open a socket connection on port 80
        $fp = @fsockopen($host, 80);
        if( $fp === false ) {
            return false;
        }
     
        // send the request headers:
        fputs($fp, "POST $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
        fputs($fp, "Referer: $referer\r\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-length: ". strlen($data) ."\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);
     
        $result = ''; 
        while(!feof($fp)) {
            // receive the results of the request
            $result .= fgets($fp, 128);
        }
     
        // close the socket connection:
        fclose($fp);
     
        // split the result header from the content
        $result = explode("\r\n\r\n", $result, 2);
     
        $header = isset($result[0]) ? $result[0] : '';
        $content = isset($result[1]) ? $result[1] : '';
        
        $response = new stdClass();
        $response->header = $header;
        $response->content = $content;
        
        // Handle chunked transfer if needed
        if( strpos(strtolower($response->header), 'transfer-encoding: chunked') !== false ) {
            $parsed = '';
            $left = $response->content;
            
            while( true ) {
                $pos = strpos($left, "\r\n");
                if( $pos === false ) {
                    return $response;
                }
                
                $chunksize = substr($left, 0, $pos);
                $pos += strlen("\r\n");
                $left = substr($left, $pos);
                
                $pos = strpos($chunksize, ';');
                if( $pos !== false ) {
                    $chunksize = substr($chunksize, 0, $pos);
                }
                $chunksize = hexdec($chunksize);
                
                if( $chunksize == 0 ) {
                    break;
                }
                
                $parsed .= substr($left, 0, $chunksize);
                $left = substr($left, $chunksize + strlen("\r\n"));
            }
            
            $response->content = $parsed;
        }
        
        // Get the response code from header
        $headerLines = explode("\n", $response->header);
        $header1 = explode(' ', trim($headerLines[0]));
        $code = intval($header1[1]);
        $response->code = $code;
        
        return $response;
    }
    
    function getSEOStatus()
    {
        static $status;
        
        if( !isset($status) ) {
            $sefConfig =& SEFConfig::getConfig();
            $status = array();
            
            $config =& JFactory::getConfig();
            $status['sef'] = (bool)$config->getValue('config.sef');
            $status['mod_rewrite'] = (bool)$config->getValue('config.sef_rewrite');
            $status['joomsef'] = (bool)$sefConfig->enabled;
            $status['plugin'] = JPluginHelper::isEnabled('system', 'joomsef');
            $status['newurls'] = !$sefConfig->disableNewSEF;
        }
        
        return $status;
    }
}
?>
