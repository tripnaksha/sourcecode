<?php


defined('_JEXEC') or die();

class TagControllerImport extends TagController
{

	function __construct()
	{
		parent::__construct();
	}
	function execute( $task ){
		switch ($task){
			case 'import':
				$this->import();
				break;
			default:
				$this->display();
		}

	}

	/**
	 * display the form
	 * @return void
	 */
	function display()
	{
		JRequest::setVar( 'view', 'import' );
		parent::display();
	}




	function import(){
		$model = $this->getModel('import');
		$msg="";
		$ok=false;
		$source=JRequest::getVar('source','meta-keys');
		if($source=='meta-keys'){
			$ok=$model->importTagsFromMetaKeys();
		}else if($source=='jtags'){
			$ok=$model->importTagsFromJTags();
		}
		//need specail handle on the msg and method calls when add more sources.
		if($ok){
			$msg = JText::_( 'We met some problems while importing tags, please check!' );
		} else {
			$msg = JText::_( 'Tags are successfully imported!');
		}
		//parent::display();
		$this->setRedirect( "index2.php?option=com_tag&controller=import",$msg );
	}

}
?>
