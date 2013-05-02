<?php
/**
 * Contacts SEF extension for Joomla!
 *
 * @author      $Author: David Jozefov $
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access.');

define( '_COM_SEF_PRIORITY_CONTACT_CONTACT_ITEMID',     15 );
define( '_COM_SEF_PRIORITY_CONTACT_CONTACT',            20 );
define( '_COM_SEF_PRIORITY_CONTACT_CATEGORY_ITEMID',    25 );
define( '_COM_SEF_PRIORITY_CONTACT_CATEGORY',           30 );

class SefExt_com_contact extends SefExt
{
    function getCategoryTitle($id) {
        $database =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();

        $field = 'title';
        if( SEFTools::UseAlias($this->params, 'category_alias') ) {
            $field = 'alias';
        }
        
        $jfTranslate = $sefConfig->translateNames ? ', `id`' : '';
        $database->setQuery("SELECT `$field` AS `title`, `description` $jfTranslate FROM `#__categories` WHERE `id` = $id");
        $cat = $database->loadObject();
        if($cat) {
            $this->metadesc = $cat->description;
            $name = ( ($this->params->get('categoryid', '0') != '0') ? $id.'-' : '' ).$cat->title;
            return $name;
        } else {
            return null;
        }
    }

    function getContactName($id) {
        $database =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();

        $field = 'name';
        if( SEFTools::UseAlias($this->params, 'contact_alias') ) {
            $field = 'alias';
        }
        
        $jfTranslate = $sefConfig->translateNames ? ', `id`' : '';
        $database->setQuery("SELECT `$field` AS `name`, `catid` $jfTranslate FROM `#__contact_details` WHERE `id` = $id");
        $contact = $database->loadObject();
        if($contact) {

            $name = ( ($this->params->get('contactid', '0') != '0') ? $id.'-' : '' ).$contact->name;

            if( $this->params->get('category', '1') != '1' ) {
                return array( $name );
            } else {
                return array( $this->getCategoryTitle($contact->catid), $name );
            }
        }
        else {
            return array();
        }
    }

    function beforeCreate(&$uri) {
        // Remove the part after ':' from variables
        if( !is_null($uri->getVar('id')) )       SEFTools::fixVariable($uri, 'id');
        if( !is_null($uri->getVar('catid')) )    SEFTools::fixVariable($uri, 'catid');
        
        // Remove view and catid if they point to empty category
        if( !is_null($uri->getVar('view')) && ($uri->getVar('view') == 'category') ) {
            if( is_null($uri->getVar('catid')) || ($uri->getVar('catid') == 0) ) {
                $uri->delVar('view');
                $uri->delVar('catid');
            }
        }

        return;
    }

    function create(&$uri) {
        // Extract variables
        $vars = $uri->getQuery(true);
        extract($vars);
        $title = array();

        $this->params = SEFTools::getExtParams('com_contact');

        $title[] = JoomSEF::_getMenuTitle(@$option, @$task, @$Itemid);

        if( isset($view) ) {
            switch($view) {
                case 'contact':
                    $title = array_merge( $title, $this->getContactName($id) );
                    unset($view);
                    break;

                case 'category':
                    if( isset($catid) ) {
                        $title[] = $this->getCategoryTitle($catid);
                    }
                    unset($view);
                    break;
            }
        }

        if( !empty($format) && ($format == 'feed') ) {
            if( !empty($type) ) $title[] = $type;
        }

        $newUri = $uri;
        if (count($title) > 0) {
            // Generate meta tags
            $metatags = $this->getMetaTags();
        
            $priority = $this->getPriority($uri);
            $newUri = JoomSEF::_sefGetLocation($uri, $title, @$view, null, null, @$lang, null, null, $metatags, $priority);
        }

        return $newUri;
    }
    
    function getPriority(&$uri)
    {
        $itemid = $uri->getVar('Itemid');
        $view = $uri->getVar('view');
        
        switch($view)
        {
            case 'contact':
                if( is_null($itemid) ) {
                    return _COM_SEF_PRIORITY_CONTACT_CONTACT;
                } else {
                    return _COM_SEF_PRIORITY_CONTACT_CONTACT_ITEMID;
                }
                break;
                
            case 'category':
                if( is_null($itemid) ) {
                    return _COM_SEF_PRIORITY_CONTACT_CATEGORY;
                } else {
                    return _COM_SEF_PRIORITY_CONTACT_CATEGORY_ITEMID;
                }
                break;
                
            default:
                return null;
                break;
        }
    }
}
?>