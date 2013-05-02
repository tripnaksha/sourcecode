<?php
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );

class TagViewImport extends JView
{

	function display($tpl = null)
	{

		$this->defaultTpl($tpl);

	}

	function defaultTpl($tpl=null){
		JToolBarHelper::title(   JText::_( 'IMPORT TAGS FROM OTHER COMPONENTS' ), 'tag.png' );

		JToolBarHelper::spacer();
		JToolBarHelper::customX('import','default','',JText::_('IMPORT'),false);
		JToolBarHelper::spacer();
		JToolBarHelper::back(JText::_( 'CONTROL PANEL'),'index.php?option=com_tag');

		parent::display($tpl);
	}



}
