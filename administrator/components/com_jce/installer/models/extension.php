<?php
/**
 * @version		$Id: extension.php 47 2009-05-26 18:06:30Z happynoodleboy $
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Import library dependencies
require_once(dirname(__FILE__).DS.'extensions.php');

/**
 * Installer Plugins Model
 *
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerModelExtension extends InstallerModel
{
	/**
	 * Extension Type
	 * @var	string
	 */
	var $_type = 'extension';

	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		global $mainframe;

		// Call the parent constructor
		parent::__construct();
	}

	function _loadItems()
	{
		global $mainframe, $option;

		// Get a database connector
		$db = & JFactory::getDBO();

		$query = 'SELECT e.id as id, e.name as name, e.extension as extension, e.folder as folder, p.name as file, p.title as plugin' .
				' FROM #__jce_extensions AS e' .
				' INNER JOIN #__jce_plugins as p ON e.pid = p.id' .
				' ORDER BY e.name';
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$numRows = count($rows);
		
		for ($i = 0; $i < $numRows; $i ++) {
			$row = & $rows[$i];
			
			$plugin = $row->plugin;
			$name 	= $row->name;
			$folder = $row->folder;
			
			// Get the plugin base path
			$baseDir = JPATH_PLUGINS.DS.'editors'.DS.'jce'.DS.'tiny_mce'.DS.'plugins';
			// Get the plugin xml file
			$xmlfile = $baseDir .DS. $row->file .DS. 'extensions' .DS. $row->folder .DS. $row->extension .".xml";
			
			if (file_exists($xmlfile)) {
				if ($data = JApplicationHelper::parseXMLInstallFile($xmlfile)) {
					foreach($data as $key => $value)
					{
						$row->$key = $value;
					}
				}
			}
			$row->name 		= $name;
			$row->plugin 	= $plugin;
		}

		$this->setState('pagination.total', $numRows);
		if($this->_state->get('pagination.limit') > 0) {
			$this->_items = array_slice( $rows, $this->_state->get('pagination.offset'), $this->_state->get('pagination.limit') );
		} else {
			$this->_items = $rows;
		}
	}
}