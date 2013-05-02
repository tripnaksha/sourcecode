<?php
/**
 * @package    Joomla.Tutorials
 * @subpackage Components
 * components/com_hello/hello.php
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_1
 * @license    GNU/GPL
*/
 
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
// Require the base controller
 
require_once( JPATH_COMPONENT.DS.'controller.php' );
 
// Require specific controller if requested
if($controller = JRequest::getWord('controller')) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}
 
// Create the controller
$classname    = 'HelloController'.$controller;
$controller   = new $classname( );
 
// Perform the Request task
$controller->execute( JRequest::getWord( 'task' ) );
 
// Redirect if set by the controller
$controller->redirect();
