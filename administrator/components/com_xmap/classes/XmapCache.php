<?php
/**
 * $Id: XmapCache.php 10 2008-09-20 22:36:33Z guilleva $
 * $LastChangedDate: 2008-09-20 16:36:33 -0600 (Sat, 20 Sep 2008) $
 * $LastChangedBy: guilleva $
 * Xmap by Guillermo Vargas
 * a sitemap component for Joomla! CMS (http://www.joomla.org)
 * Author Website: http://joomla.vargas.co.cr
 * Project License: GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

class XmapCache {
	/**
	* @return object A function cache object
	*/
	function &getCache( &$sitemap ) {
		global $mosConfig_absolute_path, $mosConfig_cachepath, $mosConfig_cachetime;

		$cache = &JFactory::getCache('com_xmap_'.$sitemap->id);
		$cache->setCaching($sitemap->usecache);
		$cache->setLifeTime($sitemap->cachelifetime);
		return $cache;
	}
	/**
	* Cleans the cache
	*/
	function cleanCache( &$sitemap ) {
		$cache =&XmapCache::getCache( $sitemap );
		if (class_exists('JFactory')) {
			return $cache->clean();
		} else {
			return $cache->clean( $cache->_group );
		}
	}
}
