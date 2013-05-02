<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
jimport( 'joomla.application.pathway');

class TagViewAllTags extends JView
{
	function display($tpl = null)
	{
		$allTags=$this->get('AllTags');
        $this->assignRef('allTags',$allTags);
		$this->defaultTpl($tpl);


	}

	function defaultTpl($tpl=null){

		parent::display($tpl);

	}

}
