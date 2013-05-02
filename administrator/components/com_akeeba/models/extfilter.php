<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: extfilter.php 51 2010-01-30 10:49:58Z nikosdion $
 * @since 2.1
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

/**
 * Extension Filters model
 *
 */
class AkeebaModelExtfilter extends JModel
{
	// ========================================================================
	// Raw filter interface
	// ========================================================================

	/**
	 * Gets (automatically loading and caching) filters for a given class
	 *
	 * @param string $class The filter class to return
	 * @return array
	 * @access public
	 */
	function &getFilters($class)
	{
		static $_filters;

		if( (!is_array($_filters)) || (!isset($_filters[$class])) )
		{
			// Load and cache the requested filter class
			$_filters[$class] = $this->_loadFilterClass($class);
		}

		return $_filters[$class];
	}

	/**
	 * Checks if the filtering is enabled for the $item item in the $class class
	 *
	 * @param string $class Filter class (components, modules, plugins, templates, languages)
	 * @param mixed $item Class-dependent item value, e.g. the option for the components class
	 * @return bool True if filtering is enabled, false otherwise
	 * @access public
	 */
	function isSetFor($class, $item)
	{
		$filters =& $this->getFilters($class);
		return in_array($item, $filters);
	}

	/**
	 * Returns extra DELETE statements to enforce the Extension Filters
	 *
	 */
	function &getExtraSQL()
	{
		static $_sql;

		if(!$_sql)
		{
			$_sql = '';
			// Get SQL for components filter
			$_sql .= $this->_getSQLforComponents();
			// Get SQL for modules filter
			$_sql .= $this->_getSQLforModules();
			// Get SQL for plugins filter
			$_sql .= $this->_getSQLforPlugins();
			// Get SQL for languages filter
			$_sql .= $this->_getSQLforLanguages();
			// Get SQL for templates filter
			$_sql .= $this->_getSQLforTemplates();
		}

		return $_sql;
	}

	// ========================================================================
	// Component filter interface
	// ========================================================================

	/**
	 * Gets a list of installed non-core components
	 * @param bool $reload When true, it forces reloading the list (bust cache)
	 * @return array An array holding component information
	 * @access public
	 */
	function &getComponents($reload = false)
	{
		static $_data;

		if(!$_data || $reload)
		{
			// Get a list of components
			$db =& JFactory::getDBO();

			$query = 'SELECT *' .
					' FROM #__components' .
					' WHERE parent = 0' .
					' AND iscore = 0' .
					' ORDER BY iscore, name';
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			// Get a list of applied filters
			$applied =& $this->getFilters('components');

			$_data = array();

			$numRows = count($rows);
			for($i=0;$i < $numRows; $i++)
			{
				$row =& $rows[$i];
				$activeFilter = in_array($row->option, $applied);
				$_data[] = array(
					'name'		=> $row->name,
					'option'	=> $row->option,
					'active'	=> $activeFilter
				);
			}
		}

		return $_data;
	}

	/**
	 * Toggles the filtering status for a component
	 *
	 * @param string $option Component's option, e.g. 'com_akeeba'
	 */
	function toggleComponentFilter($option)
	{
		// Toggle the filter
		$enabled = $this->_toggleFilter('components', $option);
		// Update other filters affected by this choice
		$this->_extraFiltersForComponents($option, $enabled);
	}

	/**
	 * Since the user can manually modify the derived DEF and SFF filters,
	 * this method re-applies them FOR ACTIVE FILTERS ONLY!!!
	 *
	 */
	function reapplyComponentsFilter()
	{
		$components =& $this->getComponents();
		if( count($components) > 0 )
		foreach($components as $c)
		if($c['active'])
		$this->_extraFiltersForComponents($c['option'], true);
	}

	/**
	 * Applies the extra filters for this component. Each component, when filtered,
	 * enfores DEF and SFF filters for its files.
	 *
	 * @param string $option
	 * @param bool $active
	 */
	function _extraFiltersForComponents($option, $active)
	{
		// Component directories, through DEF
		akimport('models.def', true);
		$defModel = new AkeebaModelDef;
		$dirFront = 'components'.DS.$option;
		$dirBack = 'administrator'.DS.'components'.DS.$option;
		if($active)
		{
			$defModel->enableFilter($dirFront);
			$defModel->enableFilter($dirBack);
		}
		else
		{
			$defModel->disableFilter($dirFront);
			$defModel->disableFilter($dirBack);
		}

		// Translation files, through SFF
		akimport('models.sff', true);
		$sffModel = new AkeebaModelSff;

		$feLangs =& $this->_getAllLanguages();
		if(count($feLangs) > 0)
		foreach($feLangs as $lang)
		{
			$basedir = str_replace(JPATH_SITE.DS,'',$lang['basedir']).DS.$lang['language'];
			$file1 = $basedir.DS.$lang['language'].'.'.$option.'.ini';
			$file2 = $basedir.DS.$lang['language'].'.'.$option.'.menu.ini';
			if($active)
			{
				$sffModel->enableFilter($file1);
				$sffModel->enableFilter($file2);
			}
			else
			{
				$sffModel->disableFilter($file1);
				$sffModel->disableFilter($file2);
			}
		}

		$beLangs =& $this->_getAllLanguages(false);
		if(count($beLangs) > 0)
		foreach($beLangs as $lang)
		{
			$basedir = str_replace(JPATH_SITE.DS,'',$lang['basedir']).DS.$lang['language'];
			$file1 = $basedir.DS.$lang['language'].'.'.$option.'.ini';
			$file2 = $basedir.DS.$lang['language'].'.'.$option.'.menu.ini';
			if($active)
			{
				$sffModel->enableFilter($file1);
				$sffModel->enableFilter($file2);
			}
			else
			{
				$sffModel->disableFilter($file1);
				$sffModel->disableFilter($file2);
			}
		}
	}

	/**
	 * Returns DELETE SQL statements to remove the excluded components from the
	 * Joomla! database
	 *
	 */
	function _getSQLforComponents() {
		$db =& $this->getDBO();

		$sql = '';
		$components =& $this->getComponents();

		if(count($components) > 0)
		foreach($components as $c)
		if($c['active'])
		{
			$sql .= 'DELETE FROM '.$db->nameQuote('#__components').
						' WHERE '.$db->nameQuote('option').' = '.
			$db->Quote($c['option']).";\n";
			$sql .= 'DELETE FROM '.$db->nameQuote('#__menu').
						' WHERE '.$db->nameQuote('type').' = '.
			$db->Quote('component').' AND '.
			$db->nameQuote('link').' LIKE '.
			$db->Quote('%option='.$c['option'].'%').";\n";
		}
		return $sql;
	}

	// ========================================================================
	// Modules filter interface
	// ========================================================================

	/**
	 * Gets an indexed array of all installed modules. The array index is the
	 * module's ID
	 *
	 * @param bool $reload Force reload of the list when true
	 * @return array
	 * @access public
	 */
	function &getModules($reload = false)
	{
		static $_data;

		if(!$_data || $reload)
		{
			$db = &JFactory::getDBO();

			$query = 'SELECT id, module, client_id, title' .
					' FROM #__modules' .
					' WHERE module LIKE "mod_%" ' .
					' AND iscore = 0'.
					' GROUP BY module, client_id' .
					' ORDER BY client_id, module';
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$_data = array();

			$n = count($rows);
			for ($i = 0; $i < $n; $i ++) {
				$row = & $rows[$i];
				$active = $this->isSetFor('modules', $row->id);
				$_data[$row->id] = array(
					'id'		=> $row->id,
					'module'	=> $row->module,
					'name'		=> $row->title,
					'frontend'	=> ($row->client_id == 0),
					'active'	=> $active
				);
			}
		}

		return $_data;
	}

	/**
	 * Toggles the filtering for a given module ID
	 *
	 * @param integer $moduleID The ID of the module
	 */
	function toggleModuleFilter($moduleID)
	{
		// Toggle the filter
		$enabled = $this->_toggleFilter('modules', $moduleID);
		// Update other filters affected by this choice
		$this->_extraFiltersForModules($moduleID, $enabled);
	}

	/**
	 * Enforces exclusion of module directories for a given module ID
	 *
	 * @param integer $moduleID The ID of the module
	 * @param bool $active Filter status, true means filtering is enabled
	 */
	function _extraFiltersForModules($moduleID, $active)
	{
		// If the ID is invalid (module doesn't exist?), quit
		$allModules =& $this->getModules();
		if(!isset($allModules[$moduleID])) return;
		// Get base directory, based on front- or back-end type. RELATIVE PATHS!!!!
		if($allModules[$moduleID]['frontend'])
		{
			$basedir = '';
		}
		else
		{
			$basedir = str_replace(JPATH_SITE.DS, '', JPATH_ADMINISTRATOR).DS;
		}
		$modulePath = $basedir.'modules'.DS.$allModules[$moduleID]['module'];

		// Use the DEF to apply inclusion/exclusion of module directory
		akimport('models.def', true);
		$defModel = new AkeebaModelDef;
		if($active)
		{
			$defModel->enableFilter($modulePath);
		}
		else
		{
			$defModel->disableFilter($modulePath);
		}

		// Translation files, through SFF
		akimport('models.sff', true);
		$sffModel = new AkeebaModelSff;

		if($allModules[$moduleID]['frontend'])
		{
			// Front-end modules

			$feLangs =& $this->_getAllLanguages();
			if(count($feLangs) > 0)
			foreach($feLangs as $lang)
			{
				$basedir = str_replace(JPATH_SITE.DS,'',$lang['basedir']).DS.$lang['language'];
				$file1 = $basedir.DS.$lang['language'].'.'.$allModules[$moduleID]['module'].'.ini';
				if($active)
				{
					$sffModel->enableFilter($file1);
				}
				else
				{
					$sffModel->disableFilter($file1);
				}
			}
		}
		else
		{
			// Back-end module

			$beLangs =& $this->_getAllLanguages(false);
			if(count($beLangs) > 0)
			foreach($beLangs as $lang)
			{
				$basedir = str_replace(JPATH_SITE.DS,'',$lang['basedir']).DS.$lang['language'];
				$file1 = $basedir.DS.$lang['language'].'.'.$allModules[$moduleID]['module'].'.ini';
				if($active)
				{
					$sffModel->enableFilter($file1);
				}
				else
				{
					$sffModel->disableFilter($file1);
				}
			}
		}
	}

	/**
	 * Re-apply extra filter for all modules
	 *
	 */
	function reapplyModulesFilters()
	{
		$modules =& $this->getModules();
		if( count($modules) > 0 )
		foreach($modules as $m)
		if($m['active'])
		$this->_extraFiltersForModules($m['id'], true);
	}

	/**
	 * Gets extra DELETE SQL commands for excluded modules
	 *
	 * @return string
	 */
	function _getSQLforModules()
	{
		$db =& $this->getDBO();

		$sql = '';
		$modules =& $this->getModules();

		if(count($modules) > 0)
		foreach($modules as $m)
		if($m['active'])
		{
			$sql .= 'DELETE FROM '.$db->nameQuote('#__modules').
						' WHERE '.$db->nameQuote('id').' = '.
			$db->Quote($m['id']).";\n";
			$sql .= 'DELETE FROM '.$db->nameQuote('#__modules_menu').
						' WHERE '.$db->nameQuote('moduleid').' = '.
			$db->Quote($m['id']).";\n";
		}
		return $sql;
	}

	// ========================================================================
	// Plug-ins filter interface
	// ========================================================================

	function &getPlugins($reload = false)
	{
		static $_data;

		if(!$_data || $reload)
		{
			$db = &JFactory::getDBO();

			$query = 'SELECT id, name, folder, element, client_id' .
				' FROM #__plugins' .
				' WHERE iscore = 0 '.
				' ORDER BY client_id, folder, name';
			$db->setQuery( $query );
			$rows = $db->loadObjectList();

			$_data = array();

			$n = count($rows);
			for ($i = 0; $i < $n; $i ++) {
				$row = & $rows[$i];
				$active = $this->isSetFor('plugins', $row->id);
				$_data[$row->id] = array(
					'id'		=> $row->id,
					'plugin'	=> $row->element,
					'name'		=> $row->name,
					'type'		=> $row->folder,
					'frontend'	=> ($row->client_id == 0),
					'active'	=> $active
				);
			}
		}

		return $_data;
	}

	function togglePluginFilter($pluginID)
	{
		// Toggle the filter
		$enabled = $this->_toggleFilter('plugins', $pluginID);
		// Update other filters affected by this choice
		$this->_extraFiltersForPlugins($pluginID, $enabled);
	}

	function reapplyPluginsFilters()
	{
		$plugins =& $this->getPlugins();
		if( count($plugins) > 0 )
		foreach($plugins as $p)
		if($p['active'])
		$this->_extraFiltersForPlugins($p['id'], true);
	}

	function _extraFiltersForPlugins($pluginID, $active)
	{
		// If the ID is invalid (module doesn't exist?), quit
		$allPlugins =& $this->getPlugins();
		if(!isset($allPlugins[$pluginID])) return;
		// Get base directory, based on front- or back-end type. RELATIVE PATHS!!!!
		if($allPlugins[$pluginID]['frontend'])
		{
			$basedir = '';
		}
		else
		{
			$basedir = str_replace(JPATH_SITE.DS, '', JPATH_ADMINISTRATOR).DS;
		}
		$basedir .= 'plugins'.DS.$allPlugins[$pluginID]['type'].DS;

		// Use the SFF to apply inclusion/exclusion of plugin files and DEF
		// for any (optional) plugin folder
		$file1 = $basedir.$allPlugins[$pluginID]['plugin'].'.php';
		$file2 = $basedir.$allPlugins[$pluginID]['plugin'].'.xml';
		$pluginPath = $basedir.$allPlugins[$pluginID]['plugin'];

		akimport('models.def', true);
		akimport('models.sff', true);
		$defModel = new AkeebaModelDef;
		$sffModel = new AkeebaModelSff;

		if($active)
		{
			$defModel->enableFilter($pluginPath);
			$sffModel->enableFilter($file1);
			$sffModel->enableFilter($file2);
		}
		else
		{
			$defModel->disableFilter($pluginPath);
			$sffModel->disableFilter($file1);
			$sffModel->disableFilter($file2);
		}

		// Translation files, through SFF

		if($allPlugins[$pluginID]['frontend'])
		{
			// Front-end modules

			$feLangs =& $this->_getAllLanguages();
			if(count($feLangs) > 0)
			foreach($feLangs as $lang)
			{
				$basedir = str_replace(JPATH_SITE.DS,'',$lang['basedir']).DS.$lang['language'];
				$file1 = $basedir.DS.$lang['language'].'.plg_'.$allPlugins[$pluginID]['plugin'].'.ini';
				if($active)
				{
					$sffModel->enableFilter($file1);
				}
				else
				{
					$sffModel->disableFilter($file1);
				}
			}
		}
		else
		{
			// Back-end module

			$beLangs =& $this->_getAllLanguages(false);
			if(count($beLangs) > 0)
			foreach($beLangs as $lang)
			{
				$basedir = str_replace(JPATH_SITE.DS,'',$lang['basedir']).DS.$lang['language'];
				$file1 = $basedir.DS.$lang['language'].'.plg_'.$allPlugins[$pluginID]['plugin'].'.ini';
				if($active)
				{
					$sffModel->enableFilter($file1);
				}
				else
				{
					$sffModel->disableFilter($file1);
				}
			}
		}
	}

	function _getSQLforPlugins()
	{
		$db =& $this->getDBO();

		$sql = '';
		$plugins =& $this->getPlugins();

		if(count($plugins) > 0)
		foreach($plugins as $p)
		if($p['active'])
		{
			$sql .= 'DELETE FROM '.$db->nameQuote('#__plugins').
						' WHERE '.$db->nameQuote('id').' = '.
			$db->Quote($p['id']).";\n";
		}
		return $sql;
	}

	// ========================================================================
	// Languages filter interface
	// ========================================================================

	/**
	 * Returns an annotated list of all front-end or back-end languages
	 *
	 * @param bool $frontend If true returns front-end languages, if false returns back-end languages
	 * @return array The annotated languages array
	 */
	function &_getAllLanguages($frontend = true)
	{
		static $_feLanguages;
		static $_beLanguages;

		if($frontend)
		{
			if(!$_feLanguages)
			{
				$_feLanguages = array();
				jimport( 'joomla.filesystem.folder' );

				// Get the site languages
				$langBDir = JLanguage::getLanguagePath(JPATH_SITE);
				$langDirs = JFolder::folders($langBDir);

				for ($i=0; $i < count($langDirs); $i++)
				{
					// Try to find VALID languages, by scanning and parsing their XML files
					$row = array();
					$row['language'] = $langDirs[$i];
					$row['basedir'] = $langBDir;
					$files = JFolder::files( $langBDir.DS.$langDirs[$i], '^([-_A-Za-z]*)\.xml$' );
					foreach ($files as $file)
					{
						$data = JApplicationHelper::parseXMLLangMetaFile($langBDir.DS.$langDirs[$i].DS.$file);

						// If we didn't get valid data from the xml file, move on...
						if (!is_array($data)) {
							continue;
						}

						// Populate the row from the xml meta file
						foreach($data as $key => $value)
						{
							$row[$key] = $value;
						}

						$clientVals =& JApplicationHelper::getClientInfo(0);
						$lang = JComponentHelper::getParams('com_languages');
						$row['default'] = ( $lang->get($clientVals->name, 'en-GB') == basename( $row['language'] ) );
					}
					if(isset($row['default'])) $_feLanguages[] = $row;
				}
			}

			return $_feLanguages;
		}
		else
		{
			if(!$_beLanguages)
			{
				$_beLanguages = array();
				jimport( 'joomla.filesystem.folder' );

				// Get the site languages
				$langBDir = JLanguage::getLanguagePath(JPATH_ADMINISTRATOR);
				$langDirs = JFolder::folders($langBDir);

				for ($i=0; $i < count($langDirs); $i++)
				{
					// Try to find VALID languages, by scanning and parsing their XML files
					$row = array();
					$row['language'] = $langDirs[$i];
					$row['basedir'] = $langBDir;
					$files = JFolder::files( $langBDir.DS.$langDirs[$i], '^([-_A-Za-z]*)\.xml$' );
					foreach ($files as $file)
					{
						$data = JApplicationHelper::parseXMLLangMetaFile($langBDir.DS.$langDirs[$i].DS.$file);

						// If we didn't get valid data from the xml file, move on...
						if (!is_array($data)) {
							continue;
						}

						// Populate the row from the xml meta file
						foreach($data as $key => $value)
						{
							$row[$key] = $value;
						}

						$clientVals =& JApplicationHelper::getClientInfo(1);
						$lang = JComponentHelper::getParams('com_languages');
						$row['default'] = ( $lang->get($clientVals->name, 'en-GB') == basename( $row['language'] ) );
					}
					if(isset($row['default'])) $_beLanguages[] = $row;
				}
			}

			return $_beLanguages;
		}
	}

	function &getLanguages($reload = false)
	{
		static $_data;

		if(!$_data || $reload)
		{
			$_data = array();

			// Add non-default front-end languages
			$feLang =& $this->_getAllLanguages(true);
			if(count($feLang) > 0)
			{
				foreach($feLang as $lang)
				{
					if(!$lang['default'])
					{
						$lang['id'] = '0-'.$lang['language'];
						$lang['active'] = $this->isSetFor('languages', $lang['id']);
						$lang['frontend'] = true;
						$_data[$lang['id']] = $lang;
					}
				}
			}

			// Add non-default back-end languages
			$beLang =& $this->_getAllLanguages(false);
			if(count($beLang) > 0)
			{
				foreach($beLang as $lang)
				{
					if(!$lang['default'])
					{
						$lang['id'] = '1-'.$lang['language'];
						$lang['active'] = $this->isSetFor('languages', $lang['id']);
						$lang['frontend'] = false;
						$_data[$lang['id']] = $lang;
					}
				}
			}
		}

		return $_data;
	}

	function toggleLanguageFilter($lang)
	{
		// Toggle the filter
		$enabled = $this->_toggleFilter('languages', $lang);
		// Update other filters affected by this choice
		$this->_extraFiltersForLanguages($lang, $enabled);
	}

	function reapplyLanguagesFilters()
	{
		$languages =& $this->getLanguages();
		if( count($languages) > 0 )
		foreach($languages as $l)
		if($l['active'])
		$this->_extraFiltersForLanguages($l['id'], true);
	}

	function _extraFiltersForLanguages($lang, $active)
	{
		// If the language code is invalid (language doesn't exist?), quit
		$allLanguages =& $this->getLanguages();
		if(!isset($allLanguages[$lang])) return;
		$langPath = str_replace(JPATH_SITE.DS, '', $allLanguages[$lang]['basedir']).DS.
		$allLanguages[$lang]['language'];

		// Use the DEF to apply inclusion/exclusion of language directory
		akimport('models.def', true);
		$defModel = new AkeebaModelDef;
		if($active)
		{
			$defModel->enableFilter($langPath);
		}
		else
		{
			$defModel->disableFilter($langPath);
		}
	}

	function _getSQLforLanguages()
	{
		return ''; // No information for languages is stored in the database, man!
	}

	// ========================================================================
	// Templates filter interface
	// ========================================================================

	/**
	 * Returns an annotated list of all front-end or back-end templates
	 *
	 * @param bool $frontend If true returns front-end templates, if false returns back-end templates
	 * @return array The annotated templates array
	 */
	function &_getAllTemplates($frontend = true)
	{
		static $_feTemplates;
		static $_beTemplates;

		if($frontend)
		{
			if(!$_feTemplates)
			{
				$_feTemplates = array();
				jimport( 'joomla.filesystem.folder' );

				// Get the site languages
				$tempBDir = JPATH_SITE.DS.'templates';
				$tempDirs = JFolder::folders($tempBDir);

				// Get a list of the currently active templates
				$db =& $this->getDBO();
				$query = 'SELECT template' .
						' FROM #__templates_menu' .
						' WHERE 1';
				$db->setQuery($query);
				$activeList = $db->loadResultArray();

				for ($i=0; $i < count($tempDirs); $i++)
				{
					// Try to find VALID languages, by scanning and parsing their XML files
					$row = array();
					$row['template'] = $tempDirs[$i];
					$row['basedir'] = $tempBDir;
					$files = JFolder::files( $tempBDir.DS.$tempDirs[$i], '.xml$' );
					foreach ($files as $file)
					{
						$data = JApplicationHelper::parseXMLInstallFile($tempBDir.DS.$tempDirs[$i].DS.$file);

						// If we didn't get valid data from the xml file, move on...
						if (!is_array($data)) {
							continue;
						}

						// Populate the row from the xml meta file
						foreach($data as $key => $value)
						{
							$row[$key] = $value;
						}

						$row['client_id'] = 0;
						$row['default'] = ( in_array($row['template'], $activeList) );
					}
					if(isset($row['default'])) $_feTemplates[] = $row;
				}
			}

			return $_feTemplates;
		}
		else
		{
			if(!$_beTemplates)
			{
				$_beTemplates = array();
				jimport( 'joomla.filesystem.folder' );

				// Get the site languages
				$tempBDir = JPATH_ADMINISTRATOR.DS.'templates';
				$tempDirs = JFolder::folders($tempBDir);

				// Get a list of the currently active templates
				$db =& $this->getDBO();
				$query = 'SELECT template' .
						' FROM #__templates_menu' .
						' WHERE 1';
				$db->setQuery($query);
				$activeList = $db->loadResultArray();

				for ($i=0; $i < count($tempDirs); $i++)
				{
					// Try to find VALID languages, by scanning and parsing their XML files
					$row = array();
					$row['template'] = $tempDirs[$i];
					$row['basedir'] = $tempBDir;
					$files = JFolder::files( $tempBDir.DS.$tempDirs[$i], '.xml$' );
					foreach ($files as $file)
					{
						$data = JApplicationHelper::parseXMLInstallFile($tempBDir.DS.$tempDirs[$i].DS.$file);

						// If we didn't get valid data from the xml file, move on...
						if (!is_array($data)) {
							continue;
						}

						// Populate the row from the xml meta file
						foreach($data as $key => $value)
						{
							$row[$key] = $value;
						}

						$row['client_id'] = 1;
						$row['default'] = ( in_array($row['template'], $activeList) );
					}
					if(isset($row['default'])) $_beTemplates[] = $row;
				}
			}

			return $_beTemplates;
		}
	}

	function &getTemplates($reload = false)
	{
		static $_data;

		if(!$_data || $reload)
		{
			$_data = array();

			// Add non-default front-end templates
			$feTemp =& $this->_getAllTemplates(true);
			if(count($feTemp) > 0)
			{
				foreach($feTemp as $temp)
				{
					if(!$temp['default'])
					{
						$temp['id'] = '0-'.$temp['template'];
						$temp['active'] = $this->isSetFor('templates', $temp['id']);
						$temp['frontend'] = true;
						$_data[$temp['id']] = $temp;
					}
				}
			}

			// Add non-default back-end templates
			$beTemp =& $this->_getAllTemplates(false);
			if(count($beTemp) > 0)
			{
				foreach($beTemp as $temp)
				{
					if(!$temp['default'])
					{
						$temp['id'] = '1-'.$temp['template'];
						$temp['active'] = $this->isSetFor('templates', $temp['id']);
						$temp['frontend'] = false;
						$_data[$temp['id']] = $temp;
					}
				}
			}
		}

		return $_data;
	}

	function toggleTemplateFilter($templateID)
	{
		// Toggle the filter
		$enabled = $this->_toggleFilter('templates', $templateID);
		// Update other filters affected by this choice
		$this->_extraFiltersForTemplates($templateID, $enabled);
	}

	function reapplyTemplatesFilters()
	{
		$templates =& $this->getTemplates();
		if( count($templates) > 0 )
		foreach($templates as $t)
		if($t['active'])
		$this->_extraFiltersForTemplates($t['id'], true);
	}

	function _extraFiltersForTemplates($templateID, $active)
	{
		// If the template code is invalid (template doesn't exist?), quit
		$allTemplates =& $this->getTemplates();
		if(!isset($allTemplates[$templateID])) return;
		$tempPath = str_replace(JPATH_SITE.DS, '', $allTemplates[$templateID]['basedir']).DS.
		$allTemplates[$templateID]['template'];

		// Use the DEF to apply inclusion/exclusion of template directory
		akimport('models.def', true);
		$defModel = new AkeebaModelDef;
		if($active)
		{
			$defModel->enableFilter($tempPath);
		}
		else
		{
			$defModel->disableFilter($tempPath);
		}
	}

	function _getSQLforTemplates()
	{
		$db =& $this->getDBO();

		$sql = '';
		$templates =& $this->getTemplates();

		if(count($templates) > 0)
		foreach($templates as $t)
		if($t['active'])
		{
			$sql .= 'DELETE FROM '.$db->nameQuote('#__templates_menu').
						' WHERE '.$db->nameQuote('template').' = '.$db->Quote($t['template']).
						' AND '.$db->nameQuote('client_id').' = '.$db->Quote($t['client_id']).
						";\n";
		}
		return $sql;
	}

	// ========================================================================
	// Private functions, used by all individual filter interfaces
	// ========================================================================

	/**
	 * Loads a specific filter class. Caching is the responsibility of the consumer!
	 *
	 * @param string $class The filter class to return
	 * @return array
	 * @access private
	 */
	function &_loadFilterClass($class)
	{
		$ret = array();

		// Get active profile
		$session =& JFactory::getSession();
		$profile = $session->get('profile', null, 'akeeba');

		$db =& $this->getDBO();
		$sql = "SELECT * FROM ".$db->nameQuote('#__ak_exclusion').
			' WHERE '.$db->nameQuote('profile').' = '.$db->Quote($profile).
			' AND '.$db->nameQuote('class').' = '.$db->Quote($class);
		$db->setQuery($sql);
		$temp = $db->loadAssocList();

		$this->_filters = array();
		if(is_array($temp))
		{
			foreach($temp as $entry)
			{
				$ret[] = $entry['value'];
			}
		}

		return $ret;
	}

	/**
	 * Activates the filtering for a specific class and item
	 *
	 * @param string $class Filter class (components, modules, plugins, templates, languages)
	 * @param mixed $item Class-dependent item value, e.g. the option for the components class
	 * @access private
	 */
	function _enableFilter($class, $item)
	{
		if($this->isSetFor($class, $item)) return; // Do not process already activated filter

		// Get active profile
		$session =& JFactory::getSession();
		$profile = $session->get('profile', null, 'akeeba');

		$db =& $this->getDBO();
		$sql = "INSERT INTO ".$db->nameQuote('#__ak_exclusion').
			'('.$db->nameQuote('profile').', '.$db->nameQuote('class').', '
			.$db->nameQuote('value').') VALUES ('.
			$db->Quote($profile).', '.$db->Quote($class).', '.$db->Quote($item).')';
			$db->setQuery($sql);
			$db->query();
			if(JError::isError($db))
			{
				$this->setError($db->getError());
			}
	}

	/**
	 * Deactivates the filtering for a specific class and item
	 *
	 * @param string $class Filter class (components, modules, plugins, templates, languages)
	 * @param mixed $item Class-dependent item value, e.g. the option for the components class
	 * @access private
	 */
	function _disableFilter($class, $item)
	{
		if(!$this->isSetFor($class, $item)) return; // Do not process already deactivated filter

		// Get active profile
		$session =& JFactory::getSession();
		$profile = $session->get('profile', null, 'akeeba');

		$db =& $this->getDBO();
		$sql = "DELETE FROM ".$db->nameQuote('#__ak_exclusion').
			' WHERE '.$db->nameQuote('profile').' = '.$db->Quote($profile).
			' AND '.$db->nameQuote('class').' = '.$db->Quote($class).
			' AND '.$db->nameQuote('value').' = '.$db->Quote($item);
		$db->setQuery($sql);
		$db->query();
		if(JError::isError($db))
		{
			$this->setError($db->getError());
		}
	}

	/**
	 * Toggles the filtering for a specific class and item
	 *
	 * @param string $class Filter class (components, modules, plugins, templates, languages)
	 * @param mixed $item Class-dependent item value, e.g. the option for the components class
	 * @return bool True if the filtering is enabled, false otherwise
	 * @access private
	 */
	function _toggleFilter($class, $item)
	{
		if($this->isSetFor($class, $item))
		{
			$this->_disableFilter($class, $item);
			return false;
		}
		else
		{
			$this->_enableFilter($class, $item);
			return true;
		}
	}
}