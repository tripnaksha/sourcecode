<?php

defined('_JEXEC') or die();

class TagControllerTerm extends TagController
{

	function __construct()
	{
		parent::__construct();

	}
	function execute( $task ){
		switch ($task){
			case 'save':
				$this->save();
				break;
			case 'edit':
			case 'add':
				$this->edit();
				break;
			case 'remove':
				$this->remove();
				break;
			case 'batchadd':
				$this->batchAdd();
				break;
			case 'batchsave':
				$this->batchSave();
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
		JRequest::setVar( 'view', 'term' );
		parent::display();
	}



	/**
	 * save categories
	 */
	function save()
	{
		$id		=  JRequest::getVar( 'cid', 0 ,'POST');
		$name 	=  JRequest::getVar(   'name', '' ,'POST');
		$description     =  JRequest::getVar(   'description', '','POST', 'string', JREQUEST_ALLOWRAW);
		$weight 	=  JRequest::getVar(   'weight', '' ,'POST');
		$model = $this->getModel('term');
		$isok=true;
		if(isset($id[0])&& $id[0]){
			$isok=$model->update($id[0],$name,$description,$weight);
		}else{
			$isok=$model->store($name,$description,$weight);
		}
		if(!$isok) {
			$msg = JText::_( 'TERM COULD NOT BE CREATED PLEASE CHECK' );
		} else {
			$msg = JText::_( 'TERM SUCCESSFULLY CREATED' );
		}
		$this->setRedirect( "index2.php?option=com_tag&controller=term",$msg );
	}
	function edit()
	{
		JRequest::setVar( 'view', 'term' );
		JRequest::setVar('layout','edit');
		parent::display();
	}
	function remove(){
		$ids		=  JRequest::getVar( 'cid', 0 ,'POST');
		$model = $this->getModel('term');
		if(!$model->remove($ids)) {
			$msg = JText::_( 'TERM COULD NOT BE REMOVED PLEASE CHECK' );
		} else {
			$msg = JText::_( 'TERM SUCCESSFULLY REMOVED' );
		}
		$this->setRedirect( "index2.php?option=com_tag&controller=term",$msg );
	}
	function batchAdd(){
		JRequest::setVar( 'view', 'term' );
		JRequest::setVar('layout','batchadd');
		parent::display();

	}

	function batchSave(){

		$terms 	=  JRequest::getVar(   'names', '' ,'POST');
		$msg;
		$terms=trim($terms);
		if(isset($terms)&&$terms){
			$model = $this->getModel('term');
			$isok=$model->insertTerms($terms);

			if(!$isok) {
				$msg = JText::_( 'TERMS COULD NOT BE CREATED PLEASE CHECK' );
			} else {
				$msg = JText::_( 'TERMS SUCCESSFULLY CREATED' );
			}
		}else{
			$msg=JText::_('TERMS CAN NOT BE BLANK');
		}
		$this->setRedirect( "index2.php?option=com_tag&controller=term",$msg );

	}
}
?>
