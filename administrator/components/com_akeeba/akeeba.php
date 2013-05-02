<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: akeeba.php 234 2010-08-27 11:27:45Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Check for PHP4
if(defined('PHP_VERSION')) {
	$version = PHP_VERSION;
} elseif(function_exists('phpversion')) {
	$version = phpversion();
} else {
	// No version info. I'll lie and hope for the best.
	$version = '5.0.0';
}

// Old PHP version detected. EJECT! EJECT! EJECT!
if(!version_compare($version, '5.0.0', '>='))
{
	return JError::raise(E_ERROR, 500, 'PHP 4 is not supported by Akeeba Backup');
}

// Joomla! 1.6 detection
jimport('joomla.filesystem.file');
if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_redirect'.DS.'redirect.xml')) {
	define('AKEEBA_JVERSION','15');
} else {
	define('AKEEBA_JVERSION','16');
}

if(!defined('AKEEBAENGINE')) {
	define('AKEEBAENGINE', 1); // Required for accessing Akeeba Engine's factory class
	define('AKEEBAPLATFORM', 'joomla15'); // So that platform-specific stuff can get done!
}

// Setup Akeeba's ACLs, honoring laxed permissions in component's parameters, if set
if(AKEEBA_JVERSION == '15')
{
	$component =& JComponentHelper::getComponent( 'com_akeeba' );
	$params = new JParameter($component->params);
	$acl =& JFactory::getACL();
	if(method_exists($acl, 'addACL'))
	{
		$min_acl = $params->get('minimum_acl_group','super administrator');
		$acl->addACL('com_akeeba', 'manage', 'users', 'super administrator' );
		switch($min_acl)
		{
			case 'administrator':
				$acl->addACL('com_akeeba', 'manage', 'users', 'administrator' );
				break;

			case 'manager':
				$acl->addACL('com_akeeba', 'manage', 'users', 'administrator' );
				$acl->addACL('com_akeeba', 'manage', 'users', 'manager' );
				break;
		}
	}
}
else
{
	// Access check, Joomla! 1.6 style.
	if (!JFactory::getUser()->authorise('core.manage', 'com_akeeba')) {
		return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
	}
}
// Make sure we have a profile set throughout the component's lifetime
$session =& JFactory::getSession();
$profile_id = $session->get('profile', null, 'akeeba');
if(is_null($profile_id))
{
	// No profile is set in the session; use default profile
	$session->set('profile', 1, 'akeeba');
}

// Get the view and controller from the request, or set to default if they weren't set
JRequest::setVar('view', JRequest::getCmd('view','cpanel'));
JRequest::setVar('c', JRequest::getCmd('view','cpanel')); // Black magic: Get controller based on the selected view

// Load the factory
require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'akeeba'.DS.'factory.php';
// Load the Akeeba Backup configuration and check user access permission
$registry =& AEFactory::getConfiguration();
AEPlatform::load_configuration();
unset($registry);

require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'includes.php';
require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'escape.php';

// Merge the default translation with the current translation
$jlang =& JFactory::getLanguage();
// Front-end translation
$jlang->load('com_akeeba', JPATH_SITE, 'en-GB', true);
$jlang->load('com_akeeba', JPATH_SITE, $jlang->getDefault(), true);
$jlang->load('com_akeeba', JPATH_SITE, null, true);
// Back-end translation
$jlang->load('com_akeeba', JPATH_ADMINISTRATOR, 'en-GB', true);
$jlang->load('com_akeeba', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
$jlang->load('com_akeeba', JPATH_ADMINISTRATOR, null, true);

// Load the utils helper library
AEPlatform::load_version_defines();

// If JSON functions don't exist, load our compatibility layer
if( (!function_exists('json_encode')) || (!function_exists('json_decode')) )
{
	require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'jsonlib.php';
}

// Load the appropriate controller
$c = JRequest::getCmd('c','cpanel');
$path = JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.$c.'.php';
$alt_path = JPATH_COMPONENT_ADMINISTRATOR.DS.'plugins'.DS.'controllers'.DS.$c.'.php';
if(JFile::exists($path))
{
	// The requested controller exists and there you load it...
	require_once($path);
}
elseif(JFile::exists($alt_path))
{
	require_once($alt_path);
}
else
{
	// Hmm... an invalid controller was passed
	JError::raiseError('500',JText::_('Unknown controller').' '.$c);
}

// Instanciate and execute the controller
jimport('joomla.utilities.string');
$c = 'AkeebaController'.ucfirst($c);
$controller = new $c();
if(AKEEBA_JVERSION=='j15')
{
	$controller->setAccessControl('com_akeeba','manage'); // Enforce Joomla!'s ACL
}
$controller->execute(JRequest::getCmd('task','display'));

// Redirect
$controller->redirect();