<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: view.html.php 204 2010-08-04 15:47:10Z nikosdion $
 * @since 3.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

/**
 * View class for the Filesystem Filters
 *
 */
class AkeebaViewFsfilter extends JView
{
	public function display()
	{
		$task = JRequest::getCmd('task','normal');

		// Add toolbar buttons
		JToolBarHelper::title(JText::_('AKEEBA').': <small>'.JText::_('FSFILTERS').'</small>','akeeba');
		JToolBarHelper::back('Back', 'index.php?option='.JRequest::getCmd('option'));

		// Add custom submenus
		JSubMenuHelper::addEntry(
			JText::_('FILTERS_LABEL_NORMALVIEW'),
			JURI::base().'index.php?option=com_akeeba&view='.JRequest::getCmd('view').'&task=normal',
			($task == 'normal')
		);
		JSubMenuHelper::addEntry(
			JText::_('FILTERS_LABEL_TABULARVIEW'),
			JURI::base().'index.php?option=com_akeeba&view='.JRequest::getCmd('view').'&task=tabular',
			($task == 'tabular')
		);

		// Add references to scripts and CSS
		AkeebaHelperIncludes::includeMedia(false);
		$media_folder = JURI::base().'../media/com_akeeba/';

		// Get the root URI for media files
		$this->assign( 'mediadir', AkeebaHelperEscape::escapeJS($media_folder.'theme/') );

		// Get a JSON representation of the available roots
		$filters =& AEFactory::getFilters();
		$root_info = $filters->getInclusions('dir');
		$roots = array();
		$options = array();
		if(!empty($root_info))
		{
			// Loop all dir definitions
			foreach($root_info as $dir_definition)
			{
				if(is_null($dir_definition[1]))
				{
					// Site root definition has a null element 1. It is always pushed on top of the stack.
					array_unshift($roots, $dir_definition[0]);
				}
				else
				{
					$roots[] = $dir_definition[0];
				}

				$options[] = JHTML::_('select.option', $dir_definition[0], $dir_definition[0] );
			}
		}
		$site_root = $roots[0];
		$attribs = 'onchange="akeeba_active_root_changed();"';
		$this->assign('root_select', JHTML::_('select.genericlist', $options, 'root', $attribs, 'value', 'text', $site_root, 'active_root') );
		$this->assign('roots', $roots);

		switch($task)
		{
			case 'normal':
			default:
				$tpl = null;

				// Get a JSON representation of the directory data
				$model = $this->getModel();
				$json = json_encode($model->make_listing($site_root, array(), ''));
				$this->assignRef( 'json', $json );
				break;

			case 'tabular':
				$tpl = 'tab';

				// Get a JSON representation of the tabular filter data
				$model = $this->getModel();
				$json = json_encode( $model->get_filters($site_root) );
				$this->assignRef( 'json', $json );

				break;
		}

		// Add live help
		AkeebaHelperIncludes::addHelp();

		// Get profile ID
		$profileid = AEPlatform::get_active_profile();
		$this->assign('profileid', $profileid);

		// Get profile name
		akimport('models.profiles',true);
		$model = new AkeebaModelProfiles();
		$model->setId($profileid);
		$profile_data = $model->getProfile();
		$this->assign('profilename', $profile_data->description);

		parent::display($tpl);
	}
}
