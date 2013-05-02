<?php
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_COMPONENT.DS.'controller.php');
$controller = new LoginBoxController();
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
JRequest::setVar('tmpl', 'component');
?>