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

class SEFViewSEFUrls extends JView
{
	function display($tpl = null)
	{
	    global $mainframe;
	    $viewmode = $mainframe->getUserStateFromRequest('sef.sefurls.viewmode', 'viewmode', 0);
	    if ($viewmode == 2) {
	        $icon = 'url-user.png';
	    }
	    else if( $viewmode == 1 ) {
	        $icon = '404-logs.png';
	    }
	    else {
	        $icon = 'url-edit.png';
	    }
		JToolBarHelper::title(JText::_('JoomSEF URL Manager'), $icon);
		
        $this->assign($this->getModel());
        
		$bar =& JToolBar::getInstance();
		
		JToolBarHelper::addNew();
		if ($this->viewmode == 1) {
		    // 404 log
		    JToolBarHelper::addNew('create301', JText::_('Create 301'));
		}
		JToolBarHelper::editList();
		JToolBarHelper::deleteList(JText::_('Are you sure you want to delete selected URLs?'));
		//JToolBarHelper::spacer();
		$bar->appendButton( 'Confirm', JText::_('CONFIRM_DEL_FILTER'), 'delete_f2', JText::_('Delete All Filtered'), 'deletefiltered', false, false );
		JToolBarHelper::spacer();
		JToolBarHelper::custom('showimport', 'import', '', 'Import', false);
		JToolBarHelper::custom('exportsel', 'export', '', 'Export Selected', true);
		JToolBarHelper::custom('exportall', 'export', '', 'Export All Filtered', false);
		JToolBarHelper::spacer();
		JToolBarHelper::back('Back', 'index.php?option=com_sef');
		
		// Get data from the model
        $this->assignRef('items', $this->get('Data'));
        $this->assignRef('total', $this->get('Total'));
        $this->assignRef('lists', $this->get('Lists'));
        $this->assignRef('pagination', $this->get('Pagination'));
        
        JHTML::_('behavior.tooltip');
        
		parent::display($tpl);
	}

	function showUpdated()
	{
	    JToolBarHelper::title( JText::_('JoomSEF URLs Update'), 'url-update.png' );
	    JToolBarHelper::back('Back', 'index.php?option=com_sef');
	    
	    $this->setLayout('urlsupdated');
	    
        $total = intval(JRequest::getVar('result', 0));
	    $this->assign('success', ($total > 0));
        $this->assignRef('total', $total);
	    
	    parent::display();
	}
}
