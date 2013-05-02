<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: view.html.php 204 2010-08-04 15:47:10Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

/**
 * Akeeba Backup Configuration view class
 *
 */
class AkeebaViewConfig extends JView
{
	function display()
	{
		// Toolbar buttons
		JToolBarHelper::title(JText::_('AKEEBA').':: <small>'.JText::_('CONFIGURATION').'</small>','akeeba');
		JToolBarHelper::apply();
		JToolBarHelper::save();
		JToolBarHelper::cancel();

		// Add references to scripts and CSS
		AkeebaHelperIncludes::includeMedia(false);
		$media_folder = JURI::base().'../media/com_akeeba/';

		// Get a JSON representation of GUI data
		$json = AkeebaHelperEscape::escapeJS(AEUtilInihelper::getJsonGuiDefinition(),'"\\');
		$this->assignRef( 'json', $json );

		// Get profile ID
		$profileid = AEPlatform::get_active_profile();
		$this->assign('profileid', $profileid);

		// Get profile name
		akimport('models.profiles',true);
		$model = new AkeebaModelProfiles();
		$model->setId($profileid);
		$profile_data = $model->getProfile();
		$this->assign('profilename', $profile_data->description);

		// Get the root URI for media files
		$this->assign( 'mediadir', AkeebaHelperEscape::escapeJS($media_folder.'theme/') );

		// Add live help
		AkeebaHelperIncludes::addHelp();

		parent::display();
	}
}