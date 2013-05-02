<?php
jimport('joomla.application.component.controller');

/**
 * Joomla Tag component Controller
 *
 */
class TagController extends JController
{
	function __construct()
	{
		parent::__construct();

	}

	function display()
	{
		$view=JRequest::getVar('view');
		if(!isset($view)){
			JRequest::setVar( 'view', 'frontpage' );
		}
		parent::display();
	}

}
?>
