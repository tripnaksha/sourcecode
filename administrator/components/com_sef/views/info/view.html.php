<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class SEFViewInfo extends JView
{

	function display($tpl = null)
	{
		$task = JRequest::getVar('task');
	
		if ($task == 'help') {
		    $title = 'JoomSEF Support';
		    $icon = 'help.png';
		}
		elseif ($task == 'doc') {
		    $title = 'JoomSEF Documentation';
		    $icon = 'docs.png';
		}
		elseif ($task == 'changelog') {
		    $title = 'JoomSEF Changelog';
		    $icon = 'info.png';
		}
		else {
		    $title = 'JoomSEF';
		    $icon = 'artio.png';
		}
		
		JToolBarHelper::title(JText::_($title), $icon);		
		JToolBarHelper::back(JText::_('Back'), 'index.php?option=com_sef');

		parent::display($tpl);
	}

}
