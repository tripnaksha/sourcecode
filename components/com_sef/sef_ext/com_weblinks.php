<?php
/**
 * Weblinks SEF extension for Joomla!
 *
 * @author      $Author: David Jozefov $
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access.');

define( '_COM_SEF_PRIORITY_WEBLINKS_LINK_ITEMID',       15 );
define( '_COM_SEF_PRIORITY_WEBLINKS_LINK',              20 );
define( '_COM_SEF_PRIORITY_WEBLINKS_CATEGORY_ITEMID',   25 );
define( '_COM_SEF_PRIORITY_WEBLINKS_CATEGORY',          30 );

class SefExt_com_weblinks extends SefExt
{
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
    
    function beforeCreate(&$uri) {
        // Remove the part after ':' from variables
        if( !is_null($uri->getVar('id')) )       SEFTools::fixVariable($uri, 'id');
        if( !is_null($uri->getVar('catid')) )    SEFTools::fixVariable($uri, 'catid');

        return;
    }

    function create(&$uri) {
        $sefConfig =& SEFConfig::getConfig();
        $database =& JFactory::getDBO();
        
        $this->params =& SEFTools::getExtParams('com_weblinks');
        
        // JF translate extension.
        $jfTranslate = $sefConfig->translateNames ? ', `id`' : '';

        $vars = $uri->getQuery(true);
        extract($vars);

        $title = array();
        $title[] = JoomSEF::_getMenuTitle($option, @$this_task);

        if( @$view == 'category' ) {
            $title[] = $this->getCategoryTitle($id, SEFTools::UseAlias($this->params, 'category_alias'));
        }
        elseif ((empty($this_task)) && (@$view == 'weblink')) {
            if( isset($catid) ) {
                if( $this->params->get('show_category', '1') ) {
                    $title[] = $this->getCategoryTitle($catid, SEFTools::UseAlias($this->params, 'category_alias'));
                }
            }

            if( !empty($id) ) {
                $field = 'title';
                if( SEFTools::UseAlias($this->params, 'weblink_alias') ) {
                    $field = 'alias';
                }
                
                $database->setQuery("SELECT `$field` AS `title` $jfTranslate FROM `#__weblinks` WHERE `id` = '$id'");
                $rows = $database->loadObjectList();

                if ($database->getErrorNum()) die( $database->stderr());
                elseif (@count($rows) > 0 && !empty($rows[0]->title)) {
                    $name = $rows[0]->title;
                    if( $this->params->get('weblink_id', '0') ) {
                        $name = $id . '-' . $name;
                    }
                    $title[] = $name;
                }
            } else {
                $title[] = JText::_('Submit');
            }
        }

        if (isset($task) && $task == 'new') {
            $title[] = 'new'.$sefConfig->suffix;
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
            case 'weblink':
                if( is_null($itemid) ) {
                    return _COM_SEF_PRIORITY_WEBLINKS_LINK;
                } else {
                    return _COM_SEF_PRIORITY_WEBLINKS_LINK_ITEMID;
                }
                break;
                
            case 'category':
                if( is_null($itemid) ) {
                    return _COM_SEF_PRIORITY_WEBLINKS_CATEGORY;
                } else {
                    return _COM_SEF_PRIORITY_WEBLINKS_CATEGORY_ITEMID;
                }
                break;
                
            default:
                return null;
        }
    }
}
?>
