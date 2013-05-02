<?php
/**
 * Banners SEF extension for Joomla!
 *
 * @author      $Author: David Jozefov $
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access.');

class SefExt_com_banners extends SefExt
{
    var $params;
    
    function GetBannerName($id) {
        $database =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();
        
        $field = 'name';
        if( SEFTools::UseAlias($this->params, 'banner_alias') ) {
            $field = 'alias';
        }
        
        $jfTranslate = $sefConfig->translateNames ? ', `bid`' : '';
        $query = "SELECT `$field` AS `name` $jfTranslate FROM `#__banner` WHERE `bid` = '$id'";
        $database->setQuery($query);
        $row = $database->loadObject();
        
        $name = isset($row->name) ? $row->name : '';
        if( $this->params->get('banner_id', '0') ) {
            $name = $id . '-' . $name;
        }
        
        return $name;
    }
    
    function create(&$uri) {
        $sefConfig =& SEFConfig::getConfig();
        $this->params =& SEFTools::getExtParams('com_banners');
        
        $vars = $uri->getQuery(true);
        extract($vars);
        
        //$title[] = 'banners';
        //$title[] = '/';
        //$title[] = $task.$bid.$sefConfig->suffix;
        $title[] = JoomSEF::_getMenuTitle(@$option, @$task, @$Itemid);
        
        switch(@$task) {
            case 'click':
                $title[] = $this->GetBannerName($bid);
                unset($task);
                break;
        }

        $newUri = $uri;
        if (count($title) > 0) $newUri = JoomSEF::_sefGetLocation($uri, $title, @$task, null, null, @$vars['lang']);
        
        return $newUri;
    }
}
?>
