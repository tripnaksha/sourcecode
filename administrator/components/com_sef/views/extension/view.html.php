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
jimport( 'joomla.html.pane' );

class SEFViewExtension extends JView
{
	function display($tpl = null)
	{
		// Get data from the model
		$extension =& $this->get('extension');
		$this->assignRef('extension', $extension);
		
		$filters =& SEFTools::getExtFilters($extension->option);
		$this->assignRef('filters', $filters);
		
		$acceptVars =& SEFTools::getExtAcceptVars($extension->option);
		sort($acceptVars, SORT_STRING);
		$this->assignRef('acceptVars', $acceptVars);
		
		JToolBarHelper::title( JText::_( 'SEF Extension' ).' <small>'.JText::_( 'Edit' ).' [ ' . $extension->name . ' ]</small>', 'plugin.png' );
		
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		
		JHTML::_('behavior.tooltip');
		
		$redir = JRequest::getVar('redirto', '');
		$this->assignRef('redirto', $redir);
		
		// Sliding pane
		$pane = &JPane::getInstance('sliders', array('allowAllClose' => true));
		$this->assignRef('pane', $pane);
		
		parent::display($tpl);
	}
}
