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
defined('_JEXEC') or die('Restricted access.');

/**
 * Class representing one cached record
 *
 */
class SEFCacheItem
{
    var $sefurl;
    var $origurl;
    var $cpt;
    var $Itemid;
    var $metatitle;
    var $metadesc;
    var $metakey;
    var $metalang;
    var $metarobots;
    var $metagoogle;
    var $canonicallink;
    
    function SEFCacheItem($nonsef, $sef, $hits, $Itemid = '', $metatitle = '', $metadesc = '', $metakey = '', $metalang = '', $metarobots = '', $metagoogle = '', $canonicallink = '')
    {
        $this->sefurl = $sef;
        $this->origurl = $nonsef;
        $this->cpt = $hits;
        $this->Itemid = $Itemid;
        $this->metatitle = $metatitle;
        $this->metadesc = $metadesc;
        $this->metakey = $metakey;
        $this->metalang = $metalang;
        $this->metarobots = $metarobots;
        $this->metagoogle = $metagoogle;
        $this->canonicallink = $canonicallink;
    }
}

/**
 * Main class handling JoomSEF's cache
 *
 */
class SEFCache
{
    var $cacheLoaded = false;
    var $loadCacheCalled = false;
    var $cacheObject = null;
    var $cache = array();
    var $maxSize;
    var $minHits;

    /**
     * Sets the main variables and loads the cache from disk
     *
     * @param int $maxSize
     * @param int $minHits
     * @return sefCache
     */
    function sefCache($maxSize, $minHits)
    {
        $this->maxSize = $maxSize;
        $this->minHits = $minHits;
        //$this->cacheFile = JPATH_ROOT.DS.'components'.DS.'com_sef'.DS.'cache'.DS.'cache.php';
        $this->cacheFile = JPATH_CACHE.DS.'joomsef.cache';

        $this->loadCache();
    }

    function &getInstance()
    {
        static $instance;
        if( !isset($instance) ) {
            $sefConfig =& SEFConfig::getConfig();
            $instance = new sefCache($sefConfig->cacheSize, $sefConfig->cacheMinHits);
        }
        return $instance;
    }
    
    /**
     * Creates the joomla cache object
     *
     */
    function createCacheObject()
    {
        if (!is_null($this->cacheObject)) {
            return;
        }
        
        $conf =& JFactory::getConfig();
		$storage = $conf->getValue('config.cache_handler', 'file');

		$options = array(
			'defaultgroup' 	=> 'joomsef',
			'cachebase' 	=> $conf->getValue('config.cache_path'),
			'lifetime' 		=> 315360000,                               // since Joomla doesn't support no-expire cache,
			'checkTime'		=> false,                                   // we'll set expire to approx 10 years - should be enough :)
			'language' 		=> 'en-GB',                                 // we want our cache mutual for all languages
			'storage'		=> $storage
		);

		jimport('joomla.cache.cache');

		$this->cacheObject =& JCache::getInstance( 'output', $options );
		
		if ($this->cacheObject && ($storage == 'memcache')) {
		    // Set the lifetime to 0 for memcache storage
		    $handler =& $this->cacheObject->_getStorage();
		    $handler->_lifetime = 0;
		}
    }

    /**
     * Loads the cache from disk to memory
     *
     */
    function loadCache()
    {
        // Was this function already called?
        if ($this->loadCacheCalled) {
            return;
        }
        $this->loadCacheCalled = true;
        
        // Is cache already loaded?
        if ($this->cacheLoaded) {
            return;
        }

        // If cache is disabled, don't load anything
        $sefConfig =& SEFConfig::getConfig();
        if (!$sefConfig->useCache) {
            $this->cacheLoaded = true;
            return;
        }
        
        // Create the cache object if needed
        $this->createCacheObject();
        if (is_null($this->cacheObject)) {
            return;
        }
        
        // Load the cache string
        $cacheString = $this->cacheObject->get('cache');
        
        if ($cacheString === false) {
            // Cache is not created yet
            $this->cacheLoaded = true;
            return;
        }
        
        // Unserialize it to the object
        $this->cache = @unserialize($cacheString);
        
        if ($this->cache === false || !is_array($this->cache)) {
            // Error loading cache
            JError::raiseWarning(100, JText::_('JoomSEF').': '.JText::_('Cache file is corrupted.'));
            return;
        }
        
        $this->cacheLoaded = true;
    }

    /**
     * Saves the cache arrays to disk
     */
    function saveCache()
    {
        // Create the cache object if needed
        $this->createCacheObject();
        if (is_null($this->cacheObject)) {
            return;
        }
        
        // Create the cache string
        $cacheString = serialize($this->cache);
        
        // Store the cache string
        // use 5 retries (in case of file locking problems), otherwise clear the cache
        for ($i = 0; $i < 5; $i++) {
            if ($this->cacheObject->store($cacheString, 'cache')) {
                return;
            }
        }
        
        // Cache could not be stored
        $this->cleanCache();
    }
    
    /**
     * Clears the cache
     *
     */
    function cleanCache()
    {
        // Create the cache object if needed
        $this->createCacheObject();
        if (is_null($this->cacheObject)) {
            return;
        }
        
        $this->cacheObject->remove('cache');
    }

    /**
     * Tries to find a nonSEF URL corresponding with given SEF URL
     * updateHits is deprecated and is not used anymore
     *
     * @param string $sef
     * @param boolean $updateHits
     * @return object
     */
    function getNonSefUrl($sef, $updateHits = true)
    {
        // Load the cache if needed
        if (!$this->cacheLoaded) {
            $this->loadCache();
        }
        
        // Check if the cache was loaded successfully
        if (!$this->cacheLoaded) {
            return false;
        }

        $sefConfig =& SEFConfig::getConfig();

        // If we are tolerant for trailing slash
        if ($sefConfig->transitSlash) {
            // Remove trailing slash
            $sef = rtrim($sef, '/');
            if( !isset($this->cache[$sef]) ) {
                // If there isn't URL without trailing slash, add the slash
                $sef .= '/';
            }
        }
        
        // Does the item exist in cache?
        if (isset($this->cache[$sef])) {
            // Return the object
            return $this->cache[$sef];
        } else {
            // Cache record not found
            return false;
        }
    }

    /**
     * Tries to find a SEF URL corresponding with given nonSEF URL
     *
     * @param string $nonsef
     * @param string $Itemid
     * @return string
     */
    function getSefUrl($nonsef, $Itemid = null)
    {
        $sefConfig =& SEFConfig::getConfig();

        // Load the cache if needed
        if (!$this->cacheLoaded) {
            $this->LoadCache();
        }

        // Check if the cache was loaded successfully
        if (!$this->cacheLoaded) {
            return false;
        }

        // Check if non-sef url doesn't contain Itemid
        $vars = array();
        parse_str(str_replace('index.php?', '', $nonsef), $vars);
        if (is_null($Itemid) && strpos($nonsef, 'Itemid=')) {
            if (isset($vars['Itemid'])) $Itemid = $vars['Itemid'];
            $nonsef = SEFTools::removeVariable($nonsef, 'Itemid');
        }

        // Get the ignoreSource parameter
        if (isset($vars['option'])) {
            $params = SEFTools::getExtParams($vars['option']);
            $extIgnore = $params->get('ignoreSource', 2);
        } else {
            $extIgnore = 2;
        }
        $ignoreSource = ($extIgnore == 2 ? $sefConfig->ignoreSource : $extIgnore);

        // Get all sef urls matching non-sef url
        if (isset($this->cache[$nonsef]) && is_array($this->cache[$nonsef]) && (count($this->cache[$nonsef]) > 0)) {
            // First search with Itemid
            foreach($this->cache[$nonsef] as $row) {
                if (isset($row->Itemid) && ($row->Itemid == $Itemid)) {
                    return $row->sefurl;
                }
            }
            
            // otherwise, return first result found
            if ($ignoreSource || is_null($Itemid)) {
                return $this->cache[$nonsef][0]->sefurl;
            }
        }
        
        // URL does not exist in the cache
        return false;
    }

    /**
     * Adds the URL to cache
     *
     * @param string $nonsef
     * @param string $sef
     * @param int $hits
     * @param string $Itemid
     * @param string $metatitle
     * @param string $metadesc
     * @param string $metakey
     * @param string $metalang
     * @param string $metarobots
     * @param string $metagoogle
     * @param string $canonicalLink
     */
    function addUrl($nonsef, $sef, $hits, $Itemid = '', $metatitle = '', $metadesc = '', $metakey = '', $metalang = '', $metarobots = '', $metagoogle = '', $canonicallink = '')
    {
        // check if URL's hits count is enough to be stored
        if ($hits < $this->minHits) {
            return;
        }

        // check the cache size
        if (count($this->cache) > $this->maxSize) {
            // Sorry, our cache is full
            return;
        }
        
        // OK, we can add the URL to the cache
        // let's create the cache record
        $cacheItem = new SEFCacheItem($nonsef, $sef, $hits, $Itemid, $metatitle, $metadesc, $metakey, $metalang, $metarobots, $metagoogle, $canonicallink);
        
        // Add it to our cache array indexing it both by SEF and nonSEF URLs
        $this->cache[$sef] = $cacheItem;
        
        // We can have the same nonSEF URLs with different Itemids
        if (!isset($this->cache[$nonsef])) {
            $this->cache[$nonsef] = array();
        }
        $this->cache[$nonsef][] =& $this->cache[$sef];
        
        // Save the cache
        $this->saveCache();
    }
}
?>