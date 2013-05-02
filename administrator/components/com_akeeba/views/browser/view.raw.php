<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: view.raw.php 51 2010-01-30 10:49:58Z nikosdion $
 * @since 2.2
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

class AkeebaViewBrowser extends JView
{
	function display()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.path');

		// Get the folder to browse
		$folder = JRequest::getString('folder', '');
		$processfolder = JRequest::getInt('processfolder', 0);

		if(empty($folder))
		{
			$folder = '';
			$folder_raw = '';
			$isFolderThere = false;
			$isInRoot = false;
			$isOpenbasedirRestricted = false;
		}
		else
		{
			$stock_dirs = AEPlatform::get_stock_directories();
			arsort($stock_dirs);

			if($processfolder == 1)
			{
				foreach($stock_dirs as $find => $replace)
				{
					$folder = str_replace($find, $replace, $folder);
				}
			}

			// Normalise name, but only if realpath() really, REALLY works...
			$old_folder = $folder;
			$folder = @realpath($folder);
			if($folder === false) $folder = $old_folder;

			if(AEUtilFilesystem::folderExists($folder))
			{
				$isFolderThere = true;
			}
			else
			{
				$isFolderThere = false;
			}
			JRequest::setVar('folder', $folder);

			// Check if it's a subdirectory of the site's root
			$isInRoot = (strpos($folder, JPATH_SITE) === 0);

			// Check open_basedir restrictions
			$isOpenbasedirRestricted = AEUtilQuirks::checkOpenBasedirs($folder);

			// -- Get the meta form of the directory name, if applicable
			$folder_raw = $folder;
			foreach($stock_dirs as $replace => $find)
			{
				$folder_raw = str_replace($find, $replace, $folder_raw);
			}

		}

		// Writable check and contents listing if it's in site root and not restricted
		if($isFolderThere && !$isOpenbasedirRestricted)
		{
			// Get writability status
			$isWritable = is_writable($folder);

			// Get contained folders
			$subfolders = JFolder::folders($folder);
		}
		else
		{
			if($isFolderThere && !$isOpenbasedirRestricted)
			{
				$isWritable = is_writable($folder);
			}
			else
			{
				$isWritable = false;
			}

			$subfolders = array();
		}

		// Get parent directory
		$pathparts = explode(DS, $folder);
		if(is_array($pathparts))
		{
			$path = '';
			foreach($pathparts as $part)
			{
				$path .= empty($path) ? $part : DS.$part;
				if(empty($part)) {
					if( DS != '\\' ) $path = DS;
					$part = DS;
				}
				$crumb['label'] = $part;
				$crumb['folder'] = $path;
				$breadcrumbs[]=$crumb;
			}

			$junk = array_pop($pathparts);
			$parent = implode(DS, $pathparts);
		}
		else
		{
			// Can't identify parent dir, use ourselves.
			$parent = $folder;
			$breadcrumbs = array();
		}

		$this->assign('folder',					$folder);
		$this->assign('folder_raw',				$folder_raw);
		$this->assign('parent',					$parent);
		$this->assign('exists',					$isFolderThere);
		$this->assign('inRoot',					$isInRoot);
		$this->assign('openbasedirRestricted',	$isOpenbasedirRestricted);
		$this->assign('writable',				$isWritable);
		$this->assign('subfolders',				$subfolders);
		$this->assign('breadcrumbs',			$breadcrumbs);

		parent::display();
	}
}
?>
