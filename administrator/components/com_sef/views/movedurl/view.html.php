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

class SEFViewMovedUrl extends JView
{
    function display($tpl = null)
    {
        //get the data
        $url      =& $this->get('Data');
        $isNew    = ($url->id < 1);

        $text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
        JToolBarHelper::title(   JText::_( 'URL' ).': <small>[ ' . $text.' ]</small>', '301-redirects.png' );
        JToolBarHelper::save();
        if ($isNew)  {
            JToolBarHelper::cancel();
        } else {
            // for existing items the button is renamed `close`
            JToolBarHelper::cancel( 'cancel', 'Close' );
        }

        $this->assignRef('url', $url);
        
        JHTML::_('behavior.tooltip');

        parent::display($tpl);
    }
    
}
