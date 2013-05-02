<?php

defined('_JEXEC') or die('Restricted access');

require_once (JPATH_COMPONENT.DS.'controller.php');
require_once (JPATH_COMPONENT.DS.'controllers'.DS.'tag.php');
require_once (JPATH_COMPONENT.DS.'controllers'.DS.'term.php');
require_once (JPATH_COMPONENT.DS.'controllers'.DS.'css.php');
require_once (JPATH_COMPONENT.DS.'controllers'.DS.'import.php');
$document = & JFactory::getDocument();
$document->addStyleSheet(JURI::root(true).'/administrator/components/com_tag/css/tag.css' );

$controller = JRequest::getVar('controller');
// Require specific controller if requested
// Create the controller
$classname	= 'TagController'.$controller;

$controller = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();

?>
