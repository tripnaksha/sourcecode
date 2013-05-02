<?php

jimport('joomla.application.component.controller');

class BcaRssSyndicatorControllerButtonMaker extends JController
{

	var $_link = null;
	function __construct()
	{
		
		parent::__construct();
		$this->_link = 'index.php?option=com_bca-rss-syndicator&task=buttonmaker';
	}
	
	function save()
	{		
		$model = $this->getModel('buttonmaker');
		$msg = $model->save($post);
		$is_ajaxed = isset($_SERVER["HTTP_X_REQUESTED_WITH"]) ? ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") : false;
		if($is_ajaxed)
			exit($msg);		
		$this->setRedirect( $this->_link, $msg );
	}	
}
?>
