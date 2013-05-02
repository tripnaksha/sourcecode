<?php

jimport('joomla.application.component.controller');

class BcaRssSyndicatorControllerConfig extends JController
{

	function __construct()
	{
		parent::__construct();
	}

	function save()
	{
		$model = $this->getModel('config');
		if ($model->saveConfig($post)) {
			$msg = JText::_( 'Settings Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Settings' );
		}
		
		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_bca-rss-syndicator&task=config';
		$this->setRedirect($link, $msg);
	}
}
?>
