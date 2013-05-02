<?php
/**
 * NewsFeeds SEF extension for Joomla!
 *
 * @author      $Author: David Jozefov $
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access.');

define( '_COM_SEF_PRIORITY_NEWSFEEDS_FEED_ITEMID',      15 );
define( '_COM_SEF_PRIORITY_NEWSFEEDS_FEED',             20 );
define( '_COM_SEF_PRIORITY_NEWSFEEDS_CATEGORY_ITEMID',  25 );
define( '_COM_SEF_PRIORITY_NEWSFEEDS_CATEGORY',         30 );

class SefExt_com_newsfeeds extends SefExt
{
    function fixFeedId(&$uri, $varName)
    {
        $value = $uri->getVar($varName);
        if (! is_null($value)) {
            $pos = strpos($value, '-');
            if ($pos !== false) {
                $value = substr($value, 0, $pos);
                $uri->setVar($varName, $value);
            }
        }
    }
    
    function beforeCreate(&$uri) {
        // Remove the part after ':' from variables
        if( !is_null($uri->getVar('id')) ) {
            SEFTools::fixVariable($uri, 'id');
            $this->fixFeedId($uri, 'id');
        }
        if( !is_null($uri->getVar('catid')) )    SEFTools::fixVariable($uri, 'catid');

        return;
    }
    
    function getCategoryTitle($catid, $useAlias)
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
            $query = "SELECT `$field` AS `title`, `description` $jfTranslate FROM `$cat_table` WHERE `id` = '$catid'";
            $database->setQuery($query);
            $row = $database->loadObject();

            if ($database->getErrorNum()) die($database->stderr());
            elseif( $row ) {
                $this->metadesc = $row->description;
                $title = $row->title;
                if( $this->params->get('categoryid', '0') ) {
                    $title = $catid . '-' . $title;
                }
            }
        }
        
        return $title;
    }
    
    function create(&$uri) {
        $sefConfig =& SEFConfig::getConfig();
        $database =& JFactory::getDBO();
        
        $this->params =& SEFTools::getExtParams('com_newsfeeds');
        
        $vars = $uri->getQuery(true);
        extract($vars);
        
        // JF translate extension.
        $jfTranslate = $sefConfig->translateNames ? ', `id`' : '';

        $title = array();
        
        $title[] = JoomSEF::_getMenuTitle($option, @$this_task);
        
        if( @$view == 'category' && isset($id) ) {
            $title[] = $this->getCategoryTitle($id, SEFTools::UseAlias($this->params, 'category_alias'));
        }

        if (@$view == "newsfeed") {
            if( !empty($catid) ) {
                if( $this->params->get('show_category', '1') ) {
                    $title[] = $this->getCategoryTitle($catid, SEFTools::UseAlias($this->params, 'category_alias'));
                }
            }
            
            if( empty($feedid) ) {
                $feedid = $id;
            }
            
            $field = 'name';
            if( SEFTools::UseAlias($this->params, 'feed_alias') ) {
                $field = 'alias';
            }
            
            $database->setQuery("SELECT `$field` AS `name` $jfTranslate FROM `#__newsfeeds` WHERE `id` = '$feedid'");
            $rows = $database->loadObjectList();

            if ($database->getErrorNum()) {
                die($database->stderr());
            }
            elseif (@count($rows) > 0 && !empty($rows[0]->name)) {
                $name = $rows[0]->name;
                if( $this->params->get('feedid', '0') ) {
                    $name = $feedid . '-' . $name;
                }
                $title[] = $name;
            }
        }

        $newUri = $uri;
        if (count($title) > 0) {
            // Generate meta tags
            $metatags = $this->getMetaTags();
        
            $priority = $this->getPriority($uri);
            $newUri = JoomSEF::_sefGetLocation($uri, $title, null, null, null, @$vars['lang'], null, null, $metatags, $priority);
        }
        
        return $newUri;
    }
    
    function getPriority(&$uri)
    {
        $itemid = $uri->getVar('Itemid');
        $view = $uri->getVar('view');
        
        switch($view)
        {
            case 'newsfeed':
                if( is_null($itemid) ) {
                    return _COM_SEF_PRIORITY_NEWSFEEDS_FEED;
                } else {
                    return _COM_SEF_PRIORITY_NEWSFEEDS_FEED_ITEMID;
                }
                break;
                
            case 'category':
                if( is_null($itemid) ) {
                    return _COM_SEF_PRIORITY_NEWSFEEDS_CATEGORY;
                } else {
                    return _COM_SEF_PRIORITY_NEWSFEEDS_CATEGORY_ITEMID;
                }
                break;
                
            default:
                return null;
        }
    }
}
?>
