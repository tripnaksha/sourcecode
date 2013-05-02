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

jimport('joomla.application.component.model');

class SEFModelExtensions extends JModel
{
    var $_extensions;
    var $_components;
    var $_componentsNoExt;
    var $_newVersions;
    
    function __construct()
    {
        parent::__construct();
    }

    function getExtensions()
    {
        if( !isset($this->_extensions) ) {
            // Try to get the newest versions information from upgrade server
            $this->_loadNewVersions();
            
            $path = JPATH_ROOT.DS.'components'.DS.'com_sef'.DS.'sef_ext';
            $xmlfiles = JFolder::files($path, '.xml$');

            $exts = array();
            if( is_array($xmlfiles) && (count($xmlfiles) > 0) ) {
                foreach($xmlfiles as $file) {
                    $manifest = $this->_isManifest($path.DS.$file);
                    if( !is_null($manifest) ) {
                        $ext = new stdClass();
                        $ext->id = $file;
                        
                        // Remove the .xml extension
                        $ext->option = substr($file, 0, -4);
                        
                        $ext->component = $this->_getComponent($ext->option);

                        $root =& $manifest->document;

                        $element            = &$root->getElementByPath('name');
                        $ext->name          = $element ? $element->data() : '';

                        $element 			 = &$root->getElementByPath('creationdate');
                        $ext->creationdate   = $element ? $element->data() : '';

                        $element 			= &$root->getElementByPath('author');
                        $ext->author		= $element ? $element->data() : '';

                        $element 			= &$root->getElementByPath('copyright');
                        $ext->copyright	    = $element ? $element->data() : '';

                        $element 			= &$root->getElementByPath('authoremail');
                        $ext->authorEmail	= $element ? $element->data() : '';

                        $element 			= &$root->getElementByPath('authorurl');
                        $ext->authorUrl	    = $element ? $element->data() : '';

                        $element 			= &$root->getElementByPath('version');
                        $ext->version		= $element ? $element->data() : '';
                        
                        if( isset($this->_newVersions[$ext->option]) ) {
                            $ext->newestVersion = $this->_newVersions[$ext->option]->version;
                            $ext->type = $this->_newVersions[$ext->option]->type;
                        }
                        else {
                            $ext->newestVersion = null;
                            $ext->type = null;
                        }
                        
                        // Load parameters
                        $ext->params =& SEFTools::getExtParams($ext->option);
                        
                        // Active handler
                        $ext->handler = $this->_getActiveHandler($ext->option);

                        $exts[$ext->option] = $ext;
                    }
                }
            }

            $this->_extensions = $exts;
        }

        return $this->_extensions;
    }
    
    function getComponentsWithoutExtension()
    {
        if( !isset($this->_componentsNoExt) ) {
            $this->_loadComponents();
            $this->getExtensions();
            $this->_loadNewVersions();
            
            $this->_componentsNoExt = array();
            
            // Loop through the components and find those without installed extension
            if( is_array($this->_components) && (count($this->_components) > 0) ) {
                foreach($this->_components as $component) {
                    if( isset($this->_extensions[$component->option]) ) {
                        continue;
                    }
                    
                    $cmp = new stdClass();
                    $cmp = $component;
                    
                    if( isset($this->_newVersions[$cmp->option]) ) {
                        $cmp->extType = $this->_newVersions[$cmp->option]->type;
                        $cmp->extVersion = $this->_newVersions[$cmp->option]->version;
                        $cmp->extLink = $this->_newVersions[$cmp->option]->link;
                    }
                    else {
                        $cmp->extType = null;
                        $cmp->extVersion = null;
                        $cmp->extLink = null;
                    }
                    
                    // Load component parameters
                    $cmp->params =& SEFTools::getExtParams($cmp->option);
                    
                    // Active handler
                    $cmp->handler = $this->_getActiveHandler($cmp->option);
                    
                    $this->_componentsNoExt[$cmp->option] = $cmp;
                }
            }
        }
        
        return $this->_componentsNoExt;
    }

    function &_isManifest($file)
    {
        // Initialize variables
        $null	= null;
        $xml	=& JFactory::getXMLParser('Simple');

        // If we cannot load the xml file return null
        if (!$xml->loadFile($file)) {
            // Free up xml parser memory and return null
            unset ($xml);
            return $null;
        }

        /*
         * Check for a valid XML root tag.
         */
        $root =& $xml->document;
        if( !is_object($root) ||
            ($root->name() != 'install') ||
            version_compare($root->attributes('version'), '1.5', '<') ||
            ($root->attributes('type') != 'sef_ext') )
        {
            // Free up xml parser memory and return null
            unset ($xml);
            return $null;
        }

        // Valid manifest file return the object
        return $xml;
    }

    function _loadComponents()
    {
        if( isset($this->_components) ) {
            return;
        }
        
        $db =& JFactory::getDBO();
        $query = "SELECT `name`, `option` FROM `#__components` WHERE (`parent` = '0') AND (`option` != '') ORDER BY `name`";
        $db->setQuery($query);
        $this->_components = $db->loadObjectList('option');
        
        // Remove the standard Joomla components
        $remove = array('com_sef', 'com_cache', 'com_config', 'com_cpanel', 'com_installer', 'com_joomfish', 'com_languages', 'com_massmail', 'com_media', 'com_menus', 'com_messages', 'com_modules', 'com_plugins', 'com_templates');
        foreach($remove as $r)
        {
            if( isset($this->_components[$r]) ) {
                unset($this->_components[$r]);
            }
        }
    }
    
    function _getComponent($option)
    {
        $this->_loadComponents();
        
        if( isset($this->_components[$option]) ) {
            return $this->_components[$option];
        }
        
        return null;
    }
    
    function _loadNewVersions()
    {
        if( isset($this->_newVersions) ) {
            return;
        }
        
        $upgradeModel =& JModel::getInstance('Upgrade', 'SEFModel');
        $this->_newVersions =& $upgradeModel->getVersions();
    }
    
    function _getActiveHandler($option)
    {
        $params =& SEFTools::getExtParams($option);
        
        $handler = $params->get('handling', '0');
        switch($handler)
        {
            case '0':
                $compExt = JFile::exists(JPATH_ROOT.DS.'components'.DS.$option.DS.'router.php');
                $ownExt = JFile::exists(JPATH_ROOT.DS.'components'.DS.'com_sef'.DS.'sef_ext'.DS.$option.'.php');
                
                if( $compExt && !$ownExt ) {
                    return JText::_('Component\'s router');
                }
                else if( $ownExt ) {
                    return JText::_('JoomSEF extension');
                }
                else {
                    return JText::_('JoomSEF default handler');
                }
                break;
                
            case '1':
                return JText::_('Default Joomla! router');
                break;
                
            case '2':
                return JText::_('Not using SEF');
                break;
        }
    }
}
?>
