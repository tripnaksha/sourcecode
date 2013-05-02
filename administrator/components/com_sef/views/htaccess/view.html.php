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

class SEFViewHtaccess extends JView
{
	function display($tpl = null)
	{
	    switch($this->_layout) {
	        case 'advanced':
	            $this->_displayAdvanced();
	            break;
	            
	        case 'redirect':
	            $this->_displayRedirect();
	            break;
	            
	        default:
	            $this->_displaySimple();
	            break;
	    }
	}
	
	function _displaySimple()
	{
	    JToolBarHelper::title('JoomSEF - ' . JText::_('.htaccess Editor'), 'edit.png');
	    
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList(JText::_('CONFIRM_DEL_REDIRECTS'));
		JToolBarHelper::divider();
		JToolBarHelper::save('save', JText::_('Save Options'));
		JToolBarHelper::divider();
		JToolBarHelper::custom('advanced', 'move', 'move', JText::_('Advanced Edit'), false);
		JToolBarHelper::divider();
	    JToolBarHelper::back('Back', 'index.php?option=com_sef');
	    
	    $this->assignRef('items', $this->get('Redirects'));
	    $this->assignRef('lists', $this->get('Lists'));
	    
	    parent::display();
	}
	
	function _displayAdvanced()
	{
	    JError::raiseNotice('100', JText::_('WARNING_HTACCESS_EDIT'));
	    
	    JToolBarHelper::title('JoomSEF - '. JText::_('.htaccess Editor').' - '.JText::_('Advanced Edit'), 'edit.png');

	    JToolBarHelper::save('saveAdvanced');
	    JToolBarHelper::apply('applyAdvanced');
	    JToolBarHelper::cancel();
	    
	    $this->assignRef('file', $this->get('File'));
	    
	    parent::display();
	}
	
	function _displayRedirect()
	{
	    $redirect = $this->get('Redirect');
	    $isNew = ($redirect->id < 1);
	    
	    $text = $isNew ?  'New'  : 'Edit';
	    JToolBarHelper::title(JText::_('JoomSEF .htaccess Editor').' - '.JText::_($text . ' Redirect'), 'edit.png');
	    
	    JToolBarHelper::save('saveSimple');
	    JToolBarHelper::apply('applySimple');
        if( $isNew ) {
            JToolBarHelper::cancel();
        } else {
            // for existing items the button is renamed `close`
            JToolBarHelper::cancel('cancel', 'Close');
        }
        
        $this->assignRef('redirect', $redirect);
	    
	    JHTML::_('behavior.tooltip');
	    
	    parent::display();
	}
}
?>