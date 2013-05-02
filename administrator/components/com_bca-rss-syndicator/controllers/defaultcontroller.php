<?php

jimport('joomla.application.component.controller');

class BcaRssSyndicatorController extends JController
{

	function __construct()
	{
		parent::__construct();
			
		$this->registerTask('info', 'info');
		$this->registerTask('buttonmaker', 'buttonmaker');
        $this->registerTask('genbtn', 'gen_button');
		$this->registerTask('config', 'config');
		$this->registerTask('feeds', 'feeds');
		$this->registerDefaultTask('feeds');
	}

	function display()
	{
		parent::display();
	}
	
	function info()
	{
		require_once(JPATH_COMPONENT . DS . 'views' . DS . 'about.php');
		new BcaRssSyndicatorViewAbout();
	}
	
	function buttonmaker()
	{
	
		JRequest::setVar('view','buttonmaker');
		JRequest::setVar('layout','form');
		
		$this->display();
	}
	
	function config()
	{
		JRequest::setVar('view','config');
		JRequest::setVar('layout','config');
		
		$this->display();
	}
	
	function feeds()
	{
		$document = &JFactory::getDocument();
		$viewType = $document->getType();
		$view = &$this->getView('feeds',$viewType,'BcaRssSyndicatorView');
		$model = &$this->getModel('feed');
		if(!JError::isError($model))
		{
			$view->setModel($model,true);
		}
		$view->setLayout('feeds');
		$view->display();
			
	}

    function gen_button()
    {
        JRequest::setVar( 'view', 'buttonmaker' );
		JRequest::setVar( 'layout', 'button'  );

		parent::display();
    }
	
	


}
?>
