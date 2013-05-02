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

class SefExt
{
    
    var $params;
    var $metadesc;
    
    function SefExt()
    {
        // get extension params
        $className = get_class($this);
        if (substr($className, 0, 7) == 'SefExt_') $className = substr($className, 7);    
        $this->params = SEFTools::getExtParams($className);        
    }    

    function beforeCreate(&$uri)
    {
        return;
    }

    function afterCreate(&$uri)
    {
        return;
    }

    function getSefUrlFromDatabase(&$uri)
    {
        $db =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();

        // David (284): ignore Itemid if set to
        //$where = '';

        // Get the extension's ignoreSource parameter
        $option = $uri->getVar('option');
        if( !is_null($option) ) {
            $params = SEFTools::getExtParams($option);
            $extIgnore = $params->get('ignoreSource', 2);
        } else {
            $extIgnore = 2;
        }
        $ignoreSource = ($extIgnore == 2 ? $sefConfig->ignoreSource : $extIgnore);
        $Itemid = $uri->getVar('Itemid');
        /*if (!$ignoreSource && !is_null($uri->getVar('Itemid'))) {
            $where = " AND `Itemid` = '".$uri->getVar('Itemid')."'";
        }*/

        $origurl = addslashes(html_entity_decode(urldecode(JoomSEF::_uriToUrl($uri, 'Itemid'))));
        $query = "SELECT `sefurl`, `Itemid` FROM `#__sefurls` WHERE `origurl` = '" . $origurl . "'" /*. $where*/;
        $db->setQuery($query);
        //$result = $db->loadresult();
        $sefurls = $db->loadAssocList('Itemid');
        // test if current Itemid record exists, if YES, use it, if NO, use first found
        if (isset($sefurls[$Itemid])) $active = $sefurls[$Itemid]; 
        elseif ($ignoreSource && is_array($sefurls)) $active = reset($sefurls);
        if (isset($active)) $result = $active['sefurl'];

        return !empty($result) ? $result : false;
    }

    function create(&$uri)
    {
        $vars = $uri->getQuery(true);
        extract($vars);
        
        $title = array();
        $title[] = JoomSEF::_getMenuTitle(@$option, null, @$Itemid);

        $newUri = $uri;
        if (count($title) > 0) {
            $newUri = JoomSEF::_sefGetLocation($uri, $title, null, null, null, @$lang);
        }
        
        return $newUri;
    }
    
    function revert($route)
    {
        $db =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();
        $cache =& SEFCache::getInstance();
        $vars = array();

        $routeNoSlash = rtrim($route, '/');
        
        // try to use cache
        if ($sefConfig->useCache) {
            $row = $cache->getNonSefUrl($route);
        }
        else $row = null;

        // cache worked
        if ($row) $fromCache = true;
        else {
            // URL isn't in cache or cache disabled
            $fromCache = false;
            
            if ($sefConfig->transitSlash) {
                $where = "(`sefurl` = '$routeNoSlash') OR (`sefurl` = '".$routeNoSlash.'/'."')";
            } else {
                $where = "`sefurl` = '$route'";
            }
            $sql = "SELECT * FROM #__sefurls WHERE ($where) AND (`origurl` != '') ORDER BY `priority` LIMIT 1";
            $db->setQuery($sql);
            $row = $db->loadObject();
        }

        if ($row) {
            // Use the already created URL
            $string = $row->origurl;
            if (isset($row->Itemid) && ($row->Itemid != '')) {
                $string .= (strpos($string, '?') ? '&' : '?') . 'Itemid=' . $row->Itemid;
            }

            $where = '';
            if (!empty($row->id)) {
                $where = " WHERE `id` = '{$row->id}'";
            } else {
                $where = " WHERE `sefurl` = '{$row->sefurl}' AND `origurl` != ''";
            }
            
            // update the count if needed
            if (!$fromCache || $sefConfig->cacheRecordHits) {
                $db->setQuery("UPDATE #__sefurls SET cpt=(cpt+1)".$where);
                $db->query();
            }
            $string = str_replace( '&amp;', '&', $string );
            $QUERY_STRING = str_replace('index.php?', '', $string);
            parse_str($QUERY_STRING, $vars);
            if ($sefConfig->setQueryString) {
                $_SERVER['QUERY_STRING'] = $QUERY_STRING;
            }

            // prepare the meta tags array for MetaBot
            global $mainframe;
            if (!empty($row->metatitle))  $mainframe->set('sef.meta.title',  $row->metatitle);
            if (!empty($row->metadesc))   $mainframe->set('sef.meta.desc',   $row->metadesc);
            if (!empty($row->metakey))    $mainframe->set('sef.meta.key',    $row->metakey);
            if (!empty($row->metalang))   $mainframe->set('sef.meta.lang',   $row->metalang);
            if (!empty($row->metarobots)) $mainframe->set('sef.meta.robots', $row->metarobots);
            if (!empty($row->metagoogle)) $mainframe->set('sef.meta.google', $row->metagoogle);
            if (!empty($row->canonicallink)) $mainframe->set('sef.link.canonical', $row->canonicallink);

            // If cache is enabled but URL isn't in cache yet, add it
            if ($sefConfig->useCache && !$fromCache) {
                $cache->addUrl($row->origurl, $row->sefurl, $row->cpt + 1, $row->Itemid, $row->metatitle, $row->metadesc, $row->metakey, $row->metalang, $row->metarobots, $row->metagoogle, $row->canonicallink);
            }
        } elseif ($sefConfig->useMoved) {
            // URL not found, let's try the Moved Permanently table
            $where = '';
            if( $sefConfig->transitSlash ) {
                $where = "(`old` = '{$routeNoSlash}') OR (`old` = '{$routeNoSlash}/')";
            }
            else {
                $where = "`old` = '{$route}'";
            }
            $db->setQuery("SELECT * FROM `#__sefmoved` WHERE {$where}");
            $row = $db->loadObject();

            if($row) {
                // URL found, let's update the lastHit in table and redirect
                $db->setQuery("UPDATE `#__sefmoved` SET `lastHit` = NOW() WHERE `id` = '$row->id'");
                $db->query();

                $root = JURI::root();
                $f = $l = '';
                if (!headers_sent($f, $l)) {
                    // Let's build absolute URL from our link
                    if( strstr($row->new, $root) === false ) {
                        $url = $root;
                        if (substr($url, -1) != '/') $url .= '/';
                        if (substr($row->new, 0, 1) == '/') $row->new = substr($row->new, 1);
                        $url .= $row->new;
                    } else {
                        $url = $row->new;
                    }

                    // Use the link to redirect
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: ' . $url);
                    header('Connection: close');
                    exit();
                } else {
                    JoomSEF::_headers_sent_error($f, $l, __FILE__, __LINE__);
                }
            }
        }

        return $vars;
    }

    function getMetaTags()
    {
        $metatags = array();
        if( $this->params->get('meta_desc', '1') && !empty($this->metadesc)) {
            $maxLen = $this->params->get('desc_len', '250');
            $metatags['metadesc'] = SEFTools::cleanDesc(SEFTools::clipDesc($this->metadesc, $maxLen));
        }
        if( $this->params->get('meta_keys', '1') && !empty($this->metadesc) ) {
            $minLen = $this->params->get('keys_minlen', '3');
            $count = $this->params->get('keys_count', '15');
            $blacklist = $this->params->get('blacklist', null);
            $metatags['metakey'] = SEFTools::generateKeywords(SEFTools::cleanDesc($this->metadesc), $blacklist, $count, $minLen);
        }
        
        return $metatags;
    }
}
?>