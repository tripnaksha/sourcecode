<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: view.html.php 247 2010-09-08 10:05:36Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

class AkeebaViewBackup extends JView
{
	function display()
	{
		// Set the toolbar title
		JToolBarHelper::title(JText::_('AKEEBA').':: <small>'.JText::_('BACKUP').'</small>','akeeba');

		// Add some buttons
		JToolBarHelper::back('Back', 'index.php?option='.JRequest::getCmd('option'));
		JToolBarHelper::spacer();

		// Load the Status Helper
		akimport('helpers.status', true);
		$helper =& AkeebaHelperStatus::getInstance();

		// Determine default description
		jimport('joomla.utilities.date');
		$jregistry =& JFactory::getConfig();
		$tzDefault = $jregistry->getValue('config.offset');
		$user =& JFactory::getUser();
		$tz = $user->getParam('timezone', $tzDefault);
		$dateNow = new JDate();
		$dateNow->setOffset($tz);
		$backup_description = JText::_('BACKUP_DEFAULT_DESCRIPTION').' '.$dateNow->toFormat(JText::_('DATE_FORMAT_LC2')) ;
		$backup_description = AkeebaHelperEscape::escapeJS($backup_description,"'");

		$default_description = $backup_description;
		$backup_description = JRequest::getVar('description', $default_description);

		$comment = JRequest::getVar('comment', '', 'default', 'none', 2);

		// Get the domain details from scripting facility
		$registry =& AEFactory::getConfiguration();
		$script = $registry->get('akeeba.basic.backup_type','full');
		$scripting = AEUtilScripting::loadScripting();
		$domains = array();
		if(!empty($scripting)) foreach( $scripting['scripts'][$script]['chain'] as $domain )
		{
			$description = JText::_($scripting['domains'][$domain]['text']);
			$domain_key = $scripting['domains'][$domain]['domain'];
			$domains[] = array($domain_key, $description);
		}
		$json_domains = AkeebaHelperEscape::escapeJS(json_encode($domains),'"\\');

		// Get the maximum execution time and bias
		$maxexec = $registry->get('akeeba.tuning.max_exec_time',14) * 1000;
		$bias = $registry->get('akeeba.tuning.run_time_bias',75);

		// Get a potential return URL
		$returnurl = JRequest::getString('returnurl',null);
		if(empty($returnurl)) $returnurl = '';

		// Pass on data
		$this->assign('haserrors', !$helper->status);
		$this->assign('hasquirks', $helper->hasQuirks());
		$this->assign('quirks', $helper->getQuirksCell(!$helper->status));
		$this->assign('description', $backup_description);
		$this->assign('comment', $comment);
		$this->assign('domains', $json_domains);
		$this->assign('maxexec', $maxexec);
		$this->assign('bias', $bias);
		$this->assign('useiframe', $registry->get('akeeba.basic.useiframe',0) ? 'true' : 'false');
		$this->assign('returnurl', $returnurl);
		if($registry->get('akeeba.advanced.archiver_engine','jpa') == 'jps')
		{
			$this->assign('showjpskey', 1);
			$this->assign('jpskey', $registry->get('engine.archiver.jps.key',''));
		}
		else
		{
			$this->assign('showjpskey', 0);
		}

		// Pass on profile info
		akimport('models.cpanel', true);
		$cpanelmodel = new AkeebaModelCpanel();
		$this->assign('profileid', $cpanelmodel->getProfileID()); // Active profile ID
		$this->assign('profilelist', $cpanelmodel->getProfilesList()); // List of available profiles

		// Add references to CSS and JS files
		AkeebaHelperIncludes::includeMedia(false);

		// Add live help
		AkeebaHelperIncludes::addHelp();

		parent::display(JRequest::getCmd('tpl',null));
	}
}