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

class SEFViewConfig extends JView
{

	function display($tpl = null)
	{
		JToolBarHelper::title( JText::_('JoomSEF Configuration'), 'config.png' );
		
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		
		// Get data from the model
		$lists = & $this->get('Lists');

		$this->assignRef('lists', $lists);
		
		JHTML::_('behavior.tooltip');

		parent::display($tpl);
	}

}
?>