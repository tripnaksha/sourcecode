<?php
/**
 * @version		$Id: extension.php 97 2009-06-21 19:18:07Z happynoodleboy $
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

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jce'.DS.'plugins'.DS.'extension.php');

/**
 * Extension installer
 *
 * @package		JCE
 * @subpackage	Installer
 * @since		1.5
 */
class JCEInstallerExtension extends JObject
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

        // Set the component name
        $name = & $this->manifest->getElementByPath('name');
        $this->set('name', $name->data());

        // Get the component description
        $description = & $this->manifest->getElementByPath('description');
        if (is_a($description, 'JSimpleXMLElement')) {
            $this->parent->set('message', $description->data());
        }
        else {
            $this->parent->set('message', '');
        }

        $element = & $this->manifest->getElementByPath('files');

        $extension = $this->manifest->attributes('extension');
        $plugin = $this->manifest->attributes('plugin');
        $folder = $this->manifest->attributes('folder');

        if (! empty($plugin)) {
            $this->parent->setPath('extension_root', JPATH_PLUGINS.DS.'editors'.DS.'jce'.DS.'tiny_mce'.DS.'plugins'.DS.$plugin.DS.'extensions'.DS.$folder);
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

        // Set overwrite flag if not set by Manifest
        $this->parent->setOverwrite(true);

        // If the extension directory does not exist, lets create it
        $created = false;
        if (!file_exists($this->parent->getPath('extension_root'))) {
            if (!$created = JFolder::create($this->parent->getPath('extension_root'))) {
                $this->parent->abort('Extension Install: '.JText::_('Failed to create directory').': "'.$this->parent->getPath('extension_root').'"');
                return false;
            }
        }

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

        /**
         * ---------------------------------------------------------------------------------------------
         * Database Processing Section
         * ---------------------------------------------------------------------------------------------
         */

        // Check to see if an extension by the same name is already installed
        $query = 'SELECT id'
        .' FROM #__jce_extensions'
        .' WHERE extension = '.$db->Quote($extension)
        ;
        $db->setQuery($query);


        $row = & JTable::getInstance('extension', 'JCETable');

        if (!$db->Query()) {
            // Install failed, roll back changes
            $this->parent->abort('Extension Install: '.$db->stderr(true));
            return false;
        }
        $id = $db->loadResult();

        // Was there an extension already installed with the same name?
        if ($id) {

            if (!$this->parent->getOverwrite())
            {
                // Install failed, roll back changes
                $this->parent->abort('Extension Install: '.JText::_('Extension').' "'.$ename.'" '.JText::_('already exists!'));
                return false;
            }
            else {
                $row->load($id);
            }
        }
        // Get the associated plugin
        $query = 'SELECT id'
        .' FROM #__jce_plugins'
        .' WHERE name = '.$db->Quote($plugin);
        $db->setQuery($query);

        if (!$db->Query()) {
            // Install failed, roll back changes
            $this->parent->abort('Plugin not installed: '.$db->stderr(true));
            return false;
        }
        $id = $db->loadResult();

        $row->pid = $id;
        $row->name = $this->get('name');
        $row->extension = $this->manifest->attributes('extension');
        $row->folder = $this->manifest->attributes('folder');
        $row->published = 1;

        if (!$row->store()) {
            // Install failed, roll back changes
            $this->parent->abort('Extension Install: '.$db->stderr(true));
            return false;
        }

        // Since we have created an extension item, we add it to the installation step stack
        // so that if we have to rollback the changes we can undo it.
        $this->parent->pushStep( array ('type'=>'extension', 'id'=>$row->id));

        /**
         * ---------------------------------------------------------------------------------------------
         * Finalization and Cleanup Section
         * ---------------------------------------------------------------------------------------------
         */

        // Lastly, we will copy the manifest file to its appropriate place.
        if (!$this->parent->copyManifest(-1)) {
            // Install failed, rollback changes
            $this->parent->abort('Extension Install: '.JText::_('Could not copy setup file'));
            return false;
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

        $row = & JTable::getInstance('extension', 'JCETable');
        $row->load((int)$id);

        $query = 'SELECT name'
        .' FROM #__jce_plugins'
        .' WHERE id = '.$row->pid
        ;

        $db->setQuery($query);
        if (!$db->Query()) {
            // Install failed, roll back changes
            $this->parent->abort('Plugin not installed: '.$db->stderr(true));
            return false;
        }
        $file = $db->loadResult();

        // Get the extension folder so we can properly build the plugin path
        if (trim($row->extension) == '') {
            JError::raiseWarning(100, 'Extension Uninstall: '.JText::_('Extension field empty, cannot remove files'));
            return false;
        }

        // Set the plugin root path
        $this->parent->setPath('extension_root', JPATH_PLUGINS.DS.'editors'.DS.'jce'.DS.'tiny_mce'.DS.'plugins'.DS.$file.DS.'extensions'.DS.$row->folder);

        $manifestFile = $this->parent->getPath('extension_root').DS.$row->extension.'.xml';

        if (file_exists($manifestFile))
        {
            $xml = & JFactory::getXMLParser('Simple');

            // If we cannot load the xml file return null
            if (!$xml->loadFile($manifestFile)) {
                JError::raiseWarning(100, 'Extension Uninstall: '.JText::_('Could not load manifest file'));
                return false;
            }

            /*
             * Check for a valid XML root tag.
             */
            $root = & $xml->document;
            if ($root->name() != 'install') {
                JError::raiseWarning(100, 'Extension Uninstall: '.JText::_('Invalid manifest file'));
                return false;
            }

            // Remove the extension files
            $this->parent->removeFiles($root->getElementByPath('files'), -1);

            // Remove all media and languages as well
            $this->parent->removeFiles($root->getElementByPath('languages'), 0);


            JFile::delete($manifestFile);
        }
        else {
            JError::raiseWarning(100, 'Extension Uninstall: Manifest File invalid or not found. Extension entry removed from database.');

            $row->delete($row->id);
            unset ($row);
            return false;
        }

        // Now we will no longer need the extension object, so lets delete it
        $row->delete($row->id);
        unset ($row);

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

        // Remove the entry from the #__jce_extensions table
        $query = 'DELETE'
        .' FROM #__jce_extensions'
        .' WHERE id='.(int)$arg['id'];

        $db->setQuery($query);

        return ($db->query() !== false);
    }
}
