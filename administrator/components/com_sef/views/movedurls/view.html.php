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

class SEFViewMovedUrls extends JView
{
	function display($tpl = null)
	{
		JToolBarHelper::title( JText::_('JoomSEF URL Manager'), '301-redirects.png' );
		
		$bar =& JToolBar::getInstance();
		
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList(JText::_('Are you sure you want to delete selected URLs?'));
		JToolBarHelper::spacer();
		$bar->appendButton( 'Confirm', JText::_('CONFIRM_DEL_FILTER'), 'delete_f2', JText::_('Delete All Filtered'), 'deletefiltered', false, false );
		JToolBarHelper::spacer();
		JToolBarHelper::back('Back', 'index.php?option=com_sef');
		
		// Get data from the model
        $this->assignRef('items', $this->get('Data'));
        $this->assign($this->getModel());
        $this->assignRef('total', $this->get('Total'));
        $this->assignRef('lists', $this->get('Lists'));
        $this->assignRef('pagination', $this->get('Pagination'));
        
		parent::display($tpl);
	}
	
}
