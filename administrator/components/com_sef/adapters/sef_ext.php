<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Component installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerSef_Ext extends JObject
{
    /**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	$parent	Parent object [JInstaller instance]
	 * @return	void
	 * @since	1.5
	 */
    function __construct(&$parent)
    {
        $this->parent =& $parent;
    }

    function install()
    {
        $extDir = JPATH_ROOT.DS.'components'.DS.'com_sef'.DS.'sef_ext';
        
		// Check that the sef_ext directory is writable
		if( !is_writable($extDir) ) {
		    JError::raiseWarning(100, JText::_('The JoomSEF Extensions directory /components/com_sef/sef_ext is not writable, so you are not able to install any extensions.'));
		    return false;
		}
        
        // Get a database connector object
        $db =& $this->parent->getDBO();

        // Get the extension manifest object
        $manifest =& $this->parent->getManifest();
        $this->manifest =& $manifest->document;

        // Set the extensions name
        $name =& $this->manifest->getElementByPath('name');
        $name = JFilterInput::clean($name->data(), 'string');
        $this->parent->set('name', $name);

        // Get the component description
        $description = & $this->manifest->getElementByPath('description');
        if (is_a($description, 'JSimpleXMLElement')) {
            $this->parent->set('message', $description->data());
        } else {
            $this->parent->set('message', '' );
        }

        // Set the installation path
        $this->parent->setPath('extension_root', $extDir);

        /**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

        // If the plugin directory does not exist, lets create it
        $created = false;
        if (!file_exists($this->parent->getPath('extension_root'))) {
            if (!$created = JFolder::create($this->parent->getPath('extension_root'))) {
                $this->parent->abort(JText::_('SEF Extension').' '.JText::_('Install').': '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_root').'"');
                return false;
            }
        }

        /*
        * If we created the plugin directory and will want to remove it if we
        * have to roll back the installation, lets add it to the installation
        * step stack
        */
        if ($created) {
            $this->parent->pushStep(array ('type' => 'folder', 'path' => $this->parent->getPath('extension_root')));
        }

        // Copy all necessary files
        $element =& $this->manifest->getElementByPath('files');
        if ($this->parent->parseFiles($element, -1) === false) {
            // Install failed, roll back changes
            $this->parent->abort();
            return false;
        }

        // If there is an install file, lets copy it.
        $installScriptElement =& $this->manifest->getElementByPath('installfile');
        if (is_a($installScriptElement, 'JSimpleXMLElement')) {
            // Make sure it hasn't already been copied (this would be an error in the xml install file)
            if (!file_exists($this->parent->getPath('extension_root').DS.$installScriptElement->data()))
            {
                $path['src']	= $this->parent->getPath('source').DS.$installScriptElement->data();
                $path['dest']	= $this->parent->getPath('extension_root').DS.$installScriptElement->data();
                if (!$this->parent->copyFiles(array ($path))) {
                    // Install failed, rollback changes
                    $this->parent->abort(JText::_('SEF Extension').' '.JText::_('Install').': '.JText::_('Could not copy PHP install file.'));
                    return false;
                }
            }
            $this->set('install.script', $installScriptElement->data());
        }

        // If there is an uninstall file, lets copy it.
        $uninstallScriptElement =& $this->manifest->getElementByPath('uninstallfile');
        if (is_a($uninstallScriptElement, 'JSimpleXMLElement')) {
            // Make sure it hasn't already been copied (this would be an error in the xml install file)
            if (!file_exists($this->parent->getPath('extension_root').DS.$uninstallScriptElement->data()))
            {
                $path['src']	= $this->parent->getPath('source').DS.$uninstallScriptElement->data();
                $path['dest']	= $this->parent->getPath('extension_root').DS.$uninstallScriptElement->data();
                if (!$this->parent->copyFiles(array ($path))) {
                    // Install failed, rollback changes
                    $this->parent->abort(JText::_('SEF Extension').' '.JText::_('Install').': '.JText::_('Could not copy PHP uninstall file.'));
                    return false;
                }
            }
        }

        /**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

        /*
        * Let's run the install queries for the component
        *	If backward compatibility is required - run queries in xml file
        *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
        *	file for utf-8 support or non-utf-8 support
        */
        $result = $this->parent->parseQueries($this->manifest->getElementByPath('install/queries'));
        if ($result === false) {
            // Install failed, rollback changes
            $this->parent->abort(JText::_('SEF Extension').' '.JText::_('Install').': '.JText::_('SQL Error')." ".$db->stderr(true));
            return false;
        } elseif ($result === 0) {
            // no backward compatibility queries found - try for Joomla 1.5 type queries
            // second argument is the utf compatible version attribute
            $utfresult = $this->parent->parseSQLFiles($this->manifest->getElementByPath('install/sql'));
            if ($utfresult === false) {
                // Install failed, rollback changes
                $this->parent->abort(JText::_('SEF Extension').' '.JText::_('Install').': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
                return false;
            }
        }

        // Insert extension into database
        $query = "SELECT `file` FROM `#__sefexts` WHERE `file` = " . $db->Quote( basename($this->parent->getPath('manifest')) );
        $db->setQuery( $query );
        $id = $db->loadResult();

        if(!$id) {
            $params = $this->_getDefaultParams();
            $filters = $this->_getDefaultFilters();
            
            $fields = array('`file`');
            $values = array($db->Quote(basename($this->parent->getPath('manifest'))));
            
            if( !empty($params) ) {
                $fields[] = '`params`';
                $values[] = $db->Quote($params);
            }
            if( !empty($filters) ) {
                $fields[] = '`filters`';
                $values[] = $db->Quote($filters);
            }
            
            $query = "INSERT INTO `#__sefexts` (".implode(',', $fields).") VALUES (".implode(',', $values).")";
            $db->setQuery($query);
            if (!$db->query()) {
                $this->parent->abort( JText::_('SEF Extension').' '.JText::_('Install').': '.JText::_('SQL Error')." ".$db->stderr(true) );
                return false;
            }
        }

        // Remove already created URLs for this extension from database
        $component = preg_replace('/.xml$/', '', basename($this->parent->getPath('manifest')));
        $query = "DELETE FROM `#__sefurls` WHERE (`origurl` LIKE '%option=$component&%' OR `origurl` LIKE '%option=$component')";
        $db->setQuery($query);
        if (!$db->query()) {
            $this->parent->abort( JText::_('SEF Extension').' '.JText::_('Install').': '.JText::_('SQL Error')." ".$db->stderr(true) );
            return false;
        }

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Custom Installation Script Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * If we have an install script, lets include it, execute the custom
		 * install method, and append the return value from the custom install
		 * method to the installation message.
		 */
		if ($this->get('install.script')) {
			if (is_file($this->parent->getPath('extension_root').DS.$this->get('install.script'))) {
				ob_start();
				ob_implicit_flush(false);
				require_once ($this->parent->getPath('extension_root').DS.$this->get('install.script'));
				if (function_exists('com_install')) {
					if (com_install() === false) {
						$this->parent->abort(JText::_('SEF Extension').' '.JText::_('Install').': '.JText::_('Custom install routine failure'));
						return false;
					}
				}
				$msg = ob_get_contents();
				ob_end_clean();
				if ($msg != '') {
					$this->parent->set('extension.message', $msg);
				}
			}
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Finalization and Cleanup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Lastly, we will copy the manifest file to its appropriate place.
		if (!$this->parent->copyManifest(-1)) {
			// Install failed, rollback changes
			$this->parent->abort(JText::_('SEF Extension').' '.JText::_('Install').': '.JText::_('Could not copy setup file'));
			return false;
		}
		return true;
    }
    
    function _getDefaultParams()
    {
        $element = $this->manifest->getElementByPath('install/defaultparams');
        
        if( !is_a($element, 'JSimpleXMLElement') || !count($element->children()) ) {
            return '';
        }
        
		$defaultParams = $element->children();
		if( count($defaultParams) == 0 ) {
			return '';
		}
        
		$params = array();
		foreach($defaultParams as $param) {
		    if( $param->name() != 'defaultparam' ) {
		        continue;
		    }
		    
		    $name = $param->attributes('name');
		    $value = $param->attributes('value');
		    
		    $params[] = $name . '=' . $value;
		}
		
		if( count($params) > 0 ) {
		    return implode("\n", $params);
		}
		else {
		    return '';
		}
    }
    
    function _getDefaultFilters()
    {
        $element = $this->manifest->getElementByPath('install/defaultfilters');
        
        if( !is_a($element, 'JSimpleXMLElement') || !count($element->children()) ) {
            return '';
        }
        
		$defaultFilters = $element->children();
		if( count($defaultFilters) == 0 ) {
			return '';
		}
        
		$filters = array();
		foreach($defaultFilters as $filter) {
		    if( $filter->name() != 'defaultfilter' ) {
		        continue;
		    }
		    
		    $filters[] = $filter->data();
		}
		
		if( count($filters) > 0 ) {
		    return implode("\n", $filters);
		}
		else {
		    return '';
		}
    }

    function uninstall($id, $clientId)
    {
        // Initialize variables
        $db =& $this->parent->getDBO();
        $basepath = JPATH_ROOT.DS.'components'.DS.'com_sef'.DS.'sef_ext';
        $xmlFile = $basepath.DS.$id;
        $ext = str_replace('.xml', '', $id);
        $retval = true;

        $xml =& JFactory::getXMLParser('Simple');
        if( !$xml->loadFile($xmlFile) ) {
            unset($xml);
            JError::raiseWarning(100, JText::_('Unable to load XML file.'));
            return false;
        }

        $root =& $xml->document;

        // Now lets load the uninstall file if there is one and execute the uninstall function if it exists.
        $uninstallfileElement =& $root->getElementByPath('uninstallfile');
        if( is_a($uninstallfileElement, 'JSimpleXMLElement') ) {
            // Element exists, does the file exist?
            if( is_file($basepath.DS.$uninstallfileElement->data()) ) {
                ob_start();
                ob_implicit_flush(false);
                require_once( $basepath.DS.$uninstallfileElement->data() );
                if( function_exists('com_uninstall') ) {
                    if( com_uninstall() === false ) {
                        JError::raiseWarning(100, JText::_('SEF Extension').' '.JText::_('Uninstall').': '.JText::_('Custom Uninstall script unsuccessful'));
                        $retval = false;
                    }
                }
                $msg = ob_get_contents();
                ob_end_clean();
                if ($msg != '') {
                    $this->parent->set('extension.message', $msg);
                }
            }
        }

        /*
        * Let's run the uninstall queries for the component
        *	If backward compatibility is required - run queries in xml file
        *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
        *	file for utf-8 support or non-utf support
        */
        $result = $this->parent->parseQueries($root->getElementByPath('uninstall/queries'));
        if ($result === false) {
            // Install failed, rollback changes
            JError::raiseWarning(100, JText::_('SEF Extension').' '.JText::_('Uninstall').': '.JText::_('SQL Error')." ".$db->stderr(true));
            $retval = false;
        } elseif ($result === 0) {
            // no backward compatibility queries found - try for Joomla 1.5 type queries
            // second argument is the utf compatible version attribute
            $utfresult = $this->parent->parseSQLFiles($root->getElementByPath('uninstall/sql'));
            if ($utfresult === false) {
                // Install failed, rollback changes
                JError::raiseWarning(100, JText::_('SEF Extension').' '.JText::_('Uninstall').': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
                $retval = false;
            }
        }

        // Now remove files
        $fileselement = $root->getElementByPath('files');
        if( is_a($fileselement, 'JSimpleXMLElement') ) {
            $files = $fileselement->children();
            if( count($files) > 0 ) {
                foreach($files as $file) {
                    $filename = $file->data();
                    if( file_exists($basepath.DS.$filename) ) {
                        if( !JFile::delete($basepath.DS.$filename) ) {
                            JError::raiseWarning(100, JText::_('SEF Extension').' '.JText::_('Uninstall').': '.JText::_('Could not delete file') . ': ' . $filename);
                            $retval = false;
                        }
                    }
                }
            }
        }

        // Remove the XML file
        if( !JFile::delete($xmlFile) ) {
            JError::raiseWarning(100, JText::_('SEF Extension').' '.JText::_('Uninstall').': '.JText::_('Could not delete file') . ': ' . $id);
            $retval = false;
        }

        // Remove the extension's texts
        $query = "DELETE FROM `#__sefexttexts` WHERE `extension` = '$ext'";
        $db->setQuery($query);
        if( !$db->query() ) {
            JError::raiseWarning(100, JText::_('SEF Extension').' '.JText::_('Uninstall').': '.JText::_('SQL Error')." ".$db->stderr(true));
            $retval = false;
        }

        return $retval;
    }
}
