<?php
/**
 * @version		$Id: plugin.php 117 2009-06-23 11:32:36Z happynoodleboy $
 * @package		JCE
 * @copyright	Copyright (C) 2009 Ryan Demmer. All rights reserved.
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die ();

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jce'.DS.'plugins'.DS.'plugin.php');

/**
 * Plugin installer
 *
 * @package		JCE
 * @subpackage	Installer
 * @since		1.5
 */
class JCEInstallerPlugin extends JObject
{
    /**
     * Constructor
     *
     * @access	protected
     * @param	object	$parent	Parent object [JInstaller instance]
     * @return	void
     * @since	1.5
     */
    function __construct( & $parent)
    {
        $this->parent = & $parent;
    }

    /**
     * Custom install method
     *
     * @access	public
     * @return	boolean	True on success
     * @since	1.5
     * Minor alteration - see below
     */
    function install()
    {
        // Get a database connector object
        $db = & $this->parent->getDBO();

        // Get the extension manifest object
        $manifest = & $this->parent->getManifest();
        $this->manifest = & $manifest->document;

        /**
         * ---------------------------------------------------------------------------------------------
         * Manifest Document Setup Section
         * ---------------------------------------------------------------------------------------------
         */

        // Set the plugin name
        $name = & $this->manifest->getElementByPath('name');
        $this->set('name', $name->data());

        // Get the plugin description
        $description = & $this->manifest->getElementByPath('description');
        if (is_a($description, 'JSimpleXMLElement')) {
            $this->parent->set('message', $description->data());
        }
        else {
            $this->parent->set('message', '');
        }

        $element = & $this->manifest->getElementByPath('files');

        // Plugin name is specified
        $plugin = $this->manifest->attributes('plugin');

        if (! empty($plugin)) {
            $this->parent->setPath('extension_root', JPATH_PLUGINS.DS.'editors'.DS.'jce'.DS.'tiny_mce'.DS.'plugins'.DS.$plugin);
        }
        else {
            $this->parent->abort('Extension Install: '.JText::_('No JCE Plugin file specified'));
            return false;
        }

        /**
         * ---------------------------------------------------------------------------------------------
         * Filesystem Processing Section
         * ---------------------------------------------------------------------------------------------
         */

        // If the extension directory does not exist, lets create it
        $created = false;
        if (!file_exists($this->parent->getPath('extension_root'))) {
            if (!$created = JFolder::create($this->parent->getPath('extension_root'))) {
                $this->parent->abort('Plugin Install: '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_root').'"');
                return false;
            }
        }
        // Set overwrite flag if not set by Manifest
        $this->parent->setOverwrite(true);

        /*
         * If we created the extension directory and will want to remove it if we
         * have to roll back the installation, lets add it to the installation
         * step stack
         */
        if ($created) {
            $this->parent->pushStep( array ('type'=>'folder', 'path'=>$this->parent->getPath('extension_root')));
        }

        // Copy all necessary files
        if ($this->parent->parseFiles($element, -1) === false) {
            // Install failed, roll back changes
            $this->parent->abort();
            return false;
        }

        // Parse optional tags -- language files for plugins
        $this->parent->parseLanguages($this->manifest->getElementByPath('languages'), 0);

        // If there is an install file, lets copy it.
        $installScriptElement = & $this->manifest->getElementByPath('installfile');
        if (is_a($installScriptElement, 'JSimpleXMLElement')) {
            // Make sure it hasn't already been copied (this would be an error in the xml install file)
            if (!file_exists($this->parent->getPath('extension_root').DS.$installScriptElement->data()))
            {
                $path['src'] = $this->parent->getPath('source').DS.$installScriptElement->data();
                $path['dest'] = $this->parent->getPath('extension_root').DS.$installScriptElement->data();
                if (!$this->parent->copyFiles( array ($path))) {
                    // Install failed, rollback changes
                    $this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Could not copy PHP install file.'));
                    return false;
                }
            }
            $this->set('install.script', $installScriptElement->data());
        }

        // If there is an uninstall file, lets copy it.
        $uninstallScriptElement = & $this->manifest->getElementByPath('uninstallfile');
        if (is_a($uninstallScriptElement, 'JSimpleXMLElement')) {
            // Make sure it hasn't already been copied (this would be an error in the xml install file)
            if (!file_exists($this->parent->getPath('extension_root').DS.$uninstallScriptElement->data()))
            {
                $path['src'] = $this->parent->getPath('source').DS.$uninstallScriptElement->data();
                $path['dest'] = $this->parent->getPath('extension_root').DS.$uninstallScriptElement->data();
                if (!$this->parent->copyFiles( array ($path))) {
                    // Install failed, rollback changes
                    $this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Could not copy PHP uninstall file.'));
                    return false;
                }
            }
        }

        /**
         * ---------------------------------------------------------------------------------------------
         * Database Processing Section
         * ---------------------------------------------------------------------------------------------
         */

        // Check to see if a plugin by the same name is already installed
        $query = 'SELECT id'.
        ' FROM #__jce_plugins'.
        ' WHERE name = '.$db->Quote($plugin);
        $db->setQuery($query);
        if (!$db->Query()) {
            // Install failed, roll back changes
            $this->parent->abort('Plugin Install: '.$db->stderr(true));
            return false;
        }
        $id = $db->loadResult();

        $row = & JTable::getInstance('plugin', 'JCETable');

        if ($id) {

            if (!$this->parent->getOverwrite()) {
                // Install failed, roll back changes
                $this->parent->abort('Plugin Install: '.JText::_('Plugin').' "'.$plugin.'" '.JText::_('already exists!'));
                return false;
            } else {
                $row->load($id);
            }

        }
        $icon = $this->manifest->getElementByPath('icon');
        $layout = $this->manifest->getElementByPath('layout');

        $row->title = $this->get('name');
        $row->name = $this->manifest->attributes('plugin');
        $row->type = 'plugin';
        $row->row = 4;
        $row->ordering = 1;
        $row->published = 1;
        $row->editable = 1;
        $row->icon = $icon->data();
        $row->layout = $layout->data();
        $row->iscore = 0;

        if (!$row->store()) {
            // Install failed, roll back changes
            $this->parent->abort('Plugin Install: '.$db->stderr(true));
            return false;
        }

        // Process default extension installation (files are assumed to have been copied!)
        $element = & $this->manifest->getElementByPath('extensions');
        if (is_a($element, 'JSimpleXMLElement') && count($element->children())) {
            $extensions = & $element->children();
            foreach ($extensions as $extension) {
                if ($extension->attributes('name')) {
                    $query = 'INSERT INTO `#__jce_extensions` '.
                    ' VALUES ("", '.(int)$row->id.', '.$db->Quote($extension->attributes('title')).', '.$db->Quote($extension->attributes('name')).', '.$db->Quote($extension->attributes('folder')).', 1 )';

                    $db->setQuery($query);
                    if (!$db->query()) {
                        // Install failed, raise error
                        JError::raiseWarning(100, 'Plugin Install: Unable to install default extension '.$extension->attributes('title'));
                        return false;
                    }
                }
            }
        }

        // Since we have created a plugin item, we add it to the installation step stack
        // so that if we have to rollback the changes we can undo it.
        $this->parent->pushStep( array ('type'=>'plugin', 'id'=>$row->id));
         
         /**
         * ---------------------------------------------------------------------------------------------
         * Install plugin into Default Group
         * ---------------------------------------------------------------------------------------------
         */
        // Add to Default Group
        if ($row->type == 'plugin') {
            JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jce'.DS.'groups');
            $group = & JTable::getInstance('groups', 'JCETable');

            $query = 'SELECT id'
            .' FROM #__jce_groups'
            .' WHERE name = '.$db->Quote('Default')
            ;
            $db->setQuery($query);
            $gid = $db->loadResult();

            $group->load($gid);
            // Add to plugins list
			$plugins = explode(',', $group->plugins);
			if(!in_array($row->id, explode(',', $group->plugins))){
				$group->plugins .= ','.$row->id;
			}
            // Add to last row if plugin has a layout icon
            if ($row->layout) {
            	if (!in_array($row->id, preg_split('/[;,]+/', $group->rows))) {
            		$group->rows .= ','.$row->id;
            	}
			}
            if (!$group->store()) {
                JError::raiseWarning(100, 'Plugin Install: Unable to add plugin to Default group');
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
            $this->parent->abort('Plugin Install: '.JText::_('Could not copy setup file'));
            return false;
        }

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
                if (function_exists('jce_install')) {
                    if (jce_install() === false) {
                        $this->parent->abort(JText::_('Plugin').' '.JText::_('Install').': '.JText::_('Custom install routine failure'));
                        return false;
                    }
                }else if (function_exists('com_install')) {
                    if (com_install() === false) {
                        $this->parent->abort(JText::_('Plugin').' '.JText::_('Install').': '.JText::_('Custom install routine failure'));
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
        return true;
    }

    /**
     * Custom uninstall method
     *
     * @access	public
     * @param	int		$cid	The id of the plugin to uninstall
     * @param	int		$clientId	The id of the client (unused)
     * @return	boolean	True on success
     * @since	1.5
     */
    function uninstall($id, $clientId)
    {
        // Initialize variables
        $row = null;
        $retval = true;
        $db = & $this->parent->getDBO();

        // First order of business will be to load the module object table from the database.
        // This should give us the necessary information to proceed.

        // ^ Changes to plugin parameters. Use JCEPluginsTable class.
        $row = & JTable::getInstance('plugin', 'JCETable');
        $row->load((int)$id);

        // Is the plugin we are trying to uninstall a core one?
        // Because that is not a good idea...
        if ($row->iscore) {
            JError::raiseWarning(100, 'Plugin Uninstall: '.JText::sprintf('WARNCOREPLUGIN', $row->title)."<br />".JText::_('WARNCOREPLUGIN2'));
            return false;
        }

        // Get the plugin folder so we can properly build the plugin path
        if (trim($row->name) == '') {
            JError::raiseWarning(100, 'Plugin Uninstall: '.JText::_('Plugin field empty, cannot remove files'));
            return false;
        }

        // Set the plugin root path
        $this->parent->setPath('extension_root', JPATH_PLUGINS.DS.'editors'.DS.'jce'.DS.'tiny_mce'.DS.'plugins'.DS.$row->name);

        $manifestFile = $this->parent->getPath('extension_root').DS.$row->name.'.xml';

        if (file_exists($manifestFile))
        {
            $xml = & JFactory::getXMLParser('Simple');

            // If we cannot load the xml file return null
            if (!$xml->loadFile($manifestFile)) {
                JError::raiseWarning(100, 'Plugin Uninstall: '.JText::_('Could not load manifest file'));
                return false;
            }

            /*
             * Check for a valid XML root tag.
             * @todo: Remove backwards compatability in a future version
             * Should be 'install', but for backward compatability we will accept 'mosinstall'.
             */
            $root = & $xml->document;
            if ($root->name() != 'install' && $root->name() != 'mosinstall') {
                JError::raiseWarning(100, 'Plugin Uninstall: '.JText::_('Invalid manifest file'));
                return false;
            }

            // Remove the plugin files
            $this->parent->removeFiles($root->getElementByPath('files'), -1);
            JFile::delete($manifestFile);

            // Remove all media and languages as well
            $this->parent->removeFiles($root->getElementByPath('languages'), 0);

            /**
             * ---------------------------------------------------------------------------------------------
             * Custom Uninstallation Script Section
             * ---------------------------------------------------------------------------------------------
             */

            // Now lets load the uninstall file if there is one and execute the uninstall function if it exists.
            $uninstallfileElement = & $root->getElementByPath('uninstallfile');
            if (is_a($uninstallfileElement, 'JSimpleXMLElement')) {
                // Element exists, does the file exist?
                if (is_file($this->parent->getPath('extension_root').DS.$uninstallfileElement->data())) {
                    ob_start();
                    ob_implicit_flush(false);
                    require_once ($this->parent->getPath('extension_root').DS.$uninstallfileElement->data());
                    if (function_exists('com_uninstall')) {
                        if (com_uninstall() === false) {
                            JError::raiseWarning(100, JText::_('Plugin').' '.JText::_('Uninstall').': '.JText::_('Custom Uninstall script unsuccessful'));
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

            // Remove extension installations from #__jce_extensions
            $query = 'DELETE'.
            ' FROM `#__jce_extensions`'.
            ' WHERE pid='.(int)$id;
            $db->setQuery($query);
            if (!$db->query()) {
                JError::raiseWarning(100, 'Plugin Uninstall: Unable to remove extension records.');
            }

            // Remove from Groups
            if ($row->type == 'plugin') {
                JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jce'.DS.'groups');
                $grow = & JTable::getInstance('groups', 'JCETable');

                $query = 'SELECT id, name, plugins, rows'
                .' FROM #__jce_groups'
                ;
                $db->setQuery($query);
                $groups = $db->loadObjectList();

                foreach ($groups as $group) {
                    $plugins = explode(',', $group->plugins);
                    // Existence check
                    if (in_array($row->id, $plugins)) {
                        // Load tables
                        $grow->load($group->id);
                        // Remove from plugins list
                        foreach ($plugins as $k=>$v) {
                            if ($row->id == $v) {
                                unset ($plugins[$k]);
                            }
                        }
                        $grow->plugins = implode(',', $plugins);
                        // Remove from rows
                        if ($row->layout) {
                            $lists = array ();
                            foreach (explode(';', $group->rows) as $list) {
                                $icons = explode(',', $list);
                                foreach ($icons as $k=>$v) {
                                    if ($row->id == $v) {
                                        unset ($icons[$k]);
                                    }
                                }
                                $lists[] = implode(',', $icons);
                            }
                            $grow->rows = implode(';', $lists);
                        }
                        if (!$grow->store()) {
                            JError::raiseWarning(100, 'Plugin Install: Unable to remove plugin from Group: '.$grow->name);
                        }
                    }
                }
            }

            // Now we will no longer need the plugin object, so lets delete it
            $row->delete($row->id);
            unset ($row);

        }
        else {
            JError::raiseWarning(100, 'Plugin Uninstall: Manifest File invalid or not found. Plugin entry removed from database.');

            $row->delete($row->id);
            unset ($row);
            $retval = false;
        }
        // If the folder is empty, let's delete it
        $files = JFolder::files($this->parent->getPath('extension_root'));
        if (!count($files)) {
            JFolder::delete($this->parent->getPath('extension_root'));
        }

        return $retval;
    }

    /**
     * Custom rollback method
     * 	- Roll back the plugin item
     *
     * @access	public
     * @param	array	$arg	Installation step to rollback
     * @return	boolean	True on success
     * @since	1.5
     * Minor changes to the db query
     */
    function _rollback_plugin($arg)
    {
        // Get database connector object
        $db = & $this->parent->getDBO();

        // Remove the entry from the #__jce_plugins table
        $query = 'DELETE'.
        ' FROM `#__jce_plugins`'.
        ' WHERE id='.(int)$arg['id'];
        $db->setQuery($query);
        return ($db->query() !== false);
    }
}
