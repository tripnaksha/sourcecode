<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
jimport( 'joomla.application.pathway');

class TagViewTag extends JView
{
	function display($tpl = null)
	{		$layout=JRequest::getCmd("layout","default");
	switch ($layout){
		case 'add':
			$this->add($tpl);
			break;
		case 'warning':
			$this->warning($tpl);
			break;
		default:
			$this->defaultTpl($tpl);
	}

	}

	function defaultTpl($tpl=null){

		$tag=JRequest::getString('tag', null);
		$tag=URLDecode($tag);
		JRequest::setVar('tag',$tag);

		$results= & $this->get( 'Data' );
		$total= & $this->get('Total');
		$pagination= & $this->get( 'Pagination' );
		$tagDescription=&$this->get('TagDescription');
		$isTermExist=$this->get('TermExist');
		if(!$isTermExist){
			//$layout=JRequest::setVar("layout","warning");
			JRequest::setVar('tagsWarning','REQUEST_TAG_NOT_EXIST_WARNING');
			$this->setLayout('warning');
		}else{
			$this->assignRef('pagination',  $pagination);
			$this->assignRef('results',		$results);
			$this->assign('total',			$total);
			$this->assign('tagDescription',			$tagDescription);
            
			
			$params = JComponentHelper::getParams('com_tag');
			$layout=JRequest::getCmd("layout",$params->get('layout','default'));
			
			$this->setLayout($layout);
			$showMeta=$params->get('contentMeta','1');
			$description=$params->get('description','1');
			$ads=&$params->get('ads','');
			$this->assign('showMeta',  $showMeta);
			$this->assign('showDescription',$description);
			$this->assignRef('ads',	$ads);		
			
		}
		parent::display($tpl);	
		
	}

	function add($tpl=null){
		$tags=&$this->getTagsForArticle();
		$this->assignRef('tags',$tags);
		parent::display($tpl);
	}
	function warning($tpl=null){
		parent::display($tpl);
	}

	function getTagsForArticle(){
		$cid=JRequest::getString('article_id');
		if(isset($cid)){
			$db			=& JFactory::getDBO();
			$query='select t.name from #__tag_term_content as tc left join #__tag_term as t on t.id=tc.tid where tc.cid='.$cid;
			$db->setQuery($query);
			$tagsInArray=$db->loadResultArray();
			if(isset($tagsInArray)&&!empty($tagsInArray)){
				return implode(',',$tagsInArray);
			}
			return '';
		}else{
			return '';
		}
	}


}
