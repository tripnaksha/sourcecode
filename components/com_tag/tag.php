<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Require the com_content helper library
require_once (JPATH_COMPONENT.DS.'controller.php');

// Create the controller
$controller = new TagController( );

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
// Redirect if set by the controller
$controller->redirect();