<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
require_once JPATH_COMPONENT_SITE.DS.'helper'.DS.'helper.php';
/**
 * Content Component Category Model
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class TagModelTerm extends JModel
{
	function remove($ids){
		$where;
		if(count($ids)>1){
			$where= ' id in('.implode(',',$ids).')';
		}else if(count($ids)==1){
			$where=' id='.$ids[0];
		}else{
			return false;
		}
		$query='delete from #__tag_term where '.$where;
		//echo($query);
		$this->_db->setQuery($query);
		return $this->_db->query();
	}
	function update($id,$name,$description,$weight){
		$updateQuery='update #__tag_term set name="'.$name.'", weight="'.$weight.'", description="'.$description.'" where id='.$id;
		$this->_db->setQuery($updateQuery);
		return $this->_db->query();
	}

	function store($name,$description=NULL,$weight=0){
		$name=JoomlaTagsHelper::preHandle($name);
		if(empty($name)){
			return 0;
		}
		$query="SELECT * FROM #__tag_term Where name='".$name."'";
		$this->_db->setQuery($query, 0, 1);
		$tagInDB= $this->_db->loadObject();
		if(isset($tagInDB)&isset($tagInDB->id)){
			$needUpdate=false;
			$updateQuery='update #__tag_term set ';
			if(isset($description)&&!empty($description)){
				$needUpdate=true;
				$updateQuery.="description='".$description."'";
			}
			if(isset($weight)){
				if($needUpdate){
					$updateQuery.=', weight='.$weight;
				}else{
					$updateQuery.=' weight='.$weight;
					$needUpdate=true;
				}
			}
			if($needUpdate){
				$updateQuery.=' where id='.$tagInDB->id;
				$this->_db->setQuery($updateQuery);
				$this->_db->query();
			}
			return $tagInDB->id;
		}else{
			$insertQuery="insert into #__tag_term (name";
			$valuePart=" values('".$name."'";
			if(isset($description)&&!empty($description)){
				$insertQuery.=",description";
				$valuePart.=",'".$description."'";
			}
			if(isset($weight)){
				$insertQuery.=",weight";
				$valuePart.=",".weight;
			}
			$date =& JFactory::getDate();
			$now = $date->toMySQL();
			$insertQuery.=',created) ';
			$valuePart.=','.$this->_db->Quote($now).')';
			$this->_db->setQuery($insertQuery.$valuePart);
			$this->_db->query();
			return $this->_db->insertid();
		}
	}
	function insertTerms($terms){
		$terms=JoomlaTagsHelper::preHandle($terms);
		$termsInArray=explode(',',$terms);
		if(empty($termsInArray)){
			return false;
		}
		$isok=true;
		foreach($termsInArray as $term){
			$this->store($term);
		}
		return $isok;
	}
	function deleteContentTerms($cid){
		$deleteQuery='delete from #__tag_term_content where cid='.$cid;
		$this->_db->setQuery($deleteQuery);
		$this->_db->query();
	}
	function insertContentTerms($cid,$tids){
		foreach($tids as $tid){
			insertContentterm($tid,$cid);
		}
	}
	function insertContentterm($tid,$cid){
		$insertQuery='insert into #__tag_term_content (tid,cid) values('.$tid.','.$cid.')';
		$this->_db->setQuery($insertQuery);
		$this->_db->query();
	}
	function termsForContent($cid){
		$query='select t.id as tid,t.name from #__tag_term as t left join #__tag_term_content as c  on c.tid=t.id where c.cid='.$cid.' order by t.weight desc,t.name';
		$this->_db->setQuery($query);
		return $this->_db->loadResultArray();
	}
	function getTermList(){
		global $mainframe;
		$search				= $mainframe->getUserStateFromRequest('articleelement.search',				'search',			'',	'string');
		$search				= JString::strtolower($search);
		$where;
		if(!is_null($search)){
			$where=" where name like'%".$search."%' ";
		}
		$query="select count(*) as ct from #__tag_term ".$where;
		$this->_db->setQuery($query);
		$this->_db->query();
		$total=$this->_db->loadResult();
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$params = JComponentHelper::getParams('com_tag');
		$limit=$params->get('tag_page_limit',30);

		$query='select t.id,t.name,t.description,t.weight,t.created,t.hits,count(c.cid)as count from #__tag_term  as t  left join  #__tag_term_content as c  on c.tid=t.id '.$where.' group by(t.id) order by t.name';
		$this->_db->setQuery($query,$limitstart,$limit);
		jimport('joomla.html.pagination');
		//$result;
		$result->page = new JPagination($total, $limitstart, $limit);
		$result->list= $this->_db->loadObjectList();
		return $result;


	}

	function getTerm(){
		$id = JRequest::getVar( 'cid', array(0), '', 'array' );
		$query='select * from #__tag_term  where id='.$id[0];
		$this->_db->setQuery($query);
		return $this->_db->loadObject();
	}

}
