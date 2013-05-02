<?php
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );

class TagViewFrontpage extends JView
{

	function display($tpl = null)
	{

		$this->defaultTpl($tpl);

	}

	function defaultTpl($tpl=null){
		JToolBarHelper::title(   JText::_( 'JOOMLA TAGS' ), 'tag.png' );
		parent::display($tpl);
	}



}
