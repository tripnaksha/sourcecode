<?php
/**
 * @package CONTENTSUBMIT
 * @link 	http://www.dioscouri.com
 * @license GNU/GPLv2
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Component Definitions
DEFINE("_CONTENTSUBMIT_DSCVERSION", '1.0');
DEFINE("_THISCOMPONENT", $option);
DEFINE("_THISCOMPONENTNAME", substr ( _THISCOMPONENT, 4 ) );

// include the language file
if (file_exists(JPATH_COMPONENT_SITE.DS.'languages'.DS.$lang->getBackwardLang().'.php')) {
      include_once(JPATH_COMPONENT_SITE.DS.'languages'.DS.$lang->getBackwardLang().'.php');
} else {
      include_once(JPATH_COMPONENT_SITE.DS.'languages'.DS.'english.php');
}

// Require the base controller
require_once( JPATH_COMPONENT_SITE.DS.'controller.php' );

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
$classname    = 'ContentsubmitController'.$controller;
$controller   = new $classname( );

$user 	= &JFactory::getUser();
if ($user->id != 0) {
	// Perform the requested task
	$controller->execute( JRequest::getVar( 'task' ) );
} else {
//	$link = 'index.php';
	printf("You need to either %sLogin%s (you can use Gmail i.d and password too!) or %sRegister%s for this..","<a href='".htmlspecialchars('index.php?option=com_login_box&login_only=1')."' onclick=\"SqueezeBox.fromElement(this); return false;\" rel=\"{handler: 'iframe', size: {x: 400, y: 320}}\"> ","</a>","<a href='".htmlspecialchars('index.php?option=com_login_box&register_only=1')."' onclick=\"SqueezeBox.fromElement(this); return false;\" rel=\"{handler: 'iframe', size: {x: 400, y: 390}}\"> ","</a>") ;
	printf("<script type='text/javascript'>SqueezeBox.fromElement('index.php?option=com_login_box&login_only=1', {handler: 'iframe', size: {x: 400, y: 320}});</script>");
//	$msg = JText::_( _CONTENTSUBMIT_PLEASELOGIN );
//	$controller->setRedirect( $link, $msg, 'notice' );
}

// Redirect if set by the controller
$controller->redirect();

?>
