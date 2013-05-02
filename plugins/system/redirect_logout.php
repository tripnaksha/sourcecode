<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.event.plugin' );

/**
 * Joomla! Redirect Logout
 * Version 1.51
 * @author		Ajay Reddy
 * @package		Joomla
 * @subpackage	System
 */
class  plgSystemRedirect_Logout extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgSystemRedirect_Logout(& $subject, $config)
	{
		parent::__construct($subject, $config);
		     // load plugin parameters
            $this->_plugin = & JPluginHelper::getPlugin( 'system', 'Redirect_Logout' );
            $this->_params = new JParameter( $this->_plugin->params );
	}

	function onLogoutUser()
	{
		global $mainframe;

		$redirect_destination	= $this->params->get('redirect_destination', 1);
		$redirect_message	= $this->params->get('redirect_message', '');
		$time_delay		= $this->params->get('time_delay', '');
		$clear_cache		= $this->params->get('clear_cache', '');

		// Destroy the php session for this user
		$session =& JFactory::getSession();
		$session->destroy();
		$mainframe->redirect( $redirect_destination, $redirect_message );
		return true;
	}
}
?>
