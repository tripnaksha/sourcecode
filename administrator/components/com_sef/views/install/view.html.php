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

class SEFViewInstall extends JView
{
	function __construct($config = null)
	{
		parent::__construct($config);
		$this->_addPath('template', $this->_basePath.DS.'views'.DS.'templates');
	}

	function display($tpl = null)
	{
		JToolBarHelper::title( JText::_( 'Install' ).' '.JText::_('SEF Extension'), 'plugin.png' );
		
		$bar = & JToolBar::getInstance();
		$bar->appendButton('Confirm', 'Are you sure you want to uninstall selected extension?', 'uninstall', 'Uninstall', 'uninstallext', true, false);
		JToolBarHelper::spacer();
		JToolBarHelper::back('Back', 'index.php?option=com_sef&controller=extension');
		
		$exts = $this->get('extensions', 'extensions');
		$this->assignRef('extensions', $exts);
		
		// Check that the sef_ext directory is writable
		if( !is_writable(JPATH_ROOT.DS.'components'.DS.'com_sef'.DS.'sef_ext') ) {
		    JError::raiseWarning(100, JText::_('The JoomSEF Extensions directory /components/com_sef/sef_ext is not writable, so you will not be able to install any extensions.'));
		}

		parent::display($tpl);
	}
	
	function showMessage()
	{
	    JToolBarHelper::title( JText::_( 'Install' ).' '.JText::_('SEF Extension'), 'plugin.png' );
	    
        $url = 'index.php?option=com_sef&task=installext';
        $redir = JRequest::getVar('redirto', null, 'post');
        if( !is_null($redir) ) {
            $url = 'index.php?option=com_sef&'.$redir;
        }
	    JToolBarHelper::back('Continue', $url);
	    
	    $this->assign('url', $url);
	    
	    $this->setLayout('message');
	    parent::display();
	}
}
