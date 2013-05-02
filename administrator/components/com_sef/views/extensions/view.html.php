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

class SEFViewExtensions extends JView
{
	function __construct($config = null)
	{
		parent::__construct($config);
		$this->_addPath('template', $this->_basePath.DS.'views'.DS.'templates');
	}

	function display($tpl = null)
	{
		JToolBarHelper::title('JoomSEF - '. JText::_('Extensions Management'), 'plugin.png' );
		
		$bar = & JToolBar::getInstance();
		JToolBarHelper::custom('installext', 'install', '', 'Install', false);
		$bar->appendButton('Confirm', 'Are you sure you want to uninstall selected extension?', 'uninstall', 'Uninstall', 'uninstallext', true, false);
		JToolBarHelper::editList('editext');
		JToolBarHelper::spacer();
		JToolBarHelper::back('Back', 'index.php?option=com_sef');
		
		$exts = $this->get('extensions', 'extensions');
		$this->assignRef('extensions', $exts);
		
		$noExts = $this->get('ComponentsWithoutExtension', 'extensions');
		$this->assignRef('components', $noExts);
        
        JHTML::_('behavior.tooltip');
        
		parent::display($tpl);
	}

}
