<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
require_once JPATH_SITE.DS.'components'.DS.'com_tag'.DS.'helper'.DS.'helper.php';

/**
 * Tag Component Tag Model
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class TagModelTag extends JModel
{

	function clearAll(){
		$query='delete from #__tag_term_content';
		$this->_db->setQuery($query);
		return $this->_db->query();
	}


	function getTagList(){
		global $mainframe;
		$catid				= $mainframe->getUserStateFromRequest('articleelement.catid',				'catid',			0,	'int');
		$sectionid	= $mainframe->getUserStateFromRequest('articleelement.filter_sectionid',	'filter_sectionid',	-1,	'int');
		$search				= $mainframe->getUserStateFromRequest('articleelement.search',				'search',			'',	'string');
		$search				= JString::strtolower($search);
		
		

		$where='';
		if($sectionid>0){
			$where.=' and sectionid='.$sectionid;
		}
		if($catid>0){
			$where.=' and catid='.$catid;
		}
		if(!empty($search)){
			$where.=" and ( `title` like'%".$search."%'  or `fulltext` like'%".$search."%'  or  `introtext` like'%".$search."%')";
		}

		$totalQuery="SELECT ct1+ct2 AS total FROM ";
		$totalQuery=$totalQuery."(select count(*) as ct1 from #__content where 1=1".$where.') as content,';
		$totalQuery=$totalQuery. "(select count(*) as ct2 from #__trailList where 1=1";
		if($search) 
		   $totalQuery=$totalQuery. " and name like '%".$search."%') as trails";
		else
		   $totalQuery=$totalQuery. ') as trails';
		$this->_db->setQuery($totalQuery);
		$this->_db->query();
		$total=$this->_db->loadResult();
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$params = JComponentHelper::getParams('com_tag');
		$limit=$params->get('tag_page_limit',30);
		$contentQuery='select id from #__content as c where 1=1'.$where;
		$contentQuery=$contentQuery." union all select id from #__trailList where 1=1";
		if($search) 
		   $contentQuery=$contentQuery. " and name like '%".$search."%'";

		$this->_db->setQuery($contentQuery,$limitstart,$limit);
		jimport('joomla.html.pagination');
		//$result;
		$result->page = new JPagination($total, $limitstart, $limit);
		$contentIdsArray= $this->_db->loadResultArray();

		$contentIds=implode(',',$contentIdsArray);

		$query = 'select c.id as cid,cc.title as category,s.title as section,c.title,t.name, "com_content" as component '.
			'from #__content as c '.
			'left join #__tag_term_content as tc '.
			'on c.id=tc.cid and tc.component= "com_content" '.
			'left join #__sections as s '.
			'on c.sectionid=s.id '.
			'left join #__categories as cc '.
			'on c.catid=cc.id '.
			'left join #__tag_term as t '.
			'on tc.tid=t.id '.
			'where c.id in ('.$contentIds.') ';
		$query = $query. ' UNION ALL '.
			'select c.id as cid,"Trail" as category,"Trail" as section,c.name as title,t.name,"com_traildisplay" as component '.
			'from #__trailList as c '.
			'left join #__tag_term_content as tc '.
			'on c.id=tc.cid and tc.component= "com_traildisplay" '.
			'left join #__tag_term as t '.
			'on tc.tid=t.id where c.id in('.$contentIds.') ';
		$this->_db->setQuery($query);
		$result->list= $this->_db->loadObjectList();
		return $result;
	}

	function getTagsForArticle(){
		$cid=JRequest::getString('article_id');
		if(isset($cid)){
			$query='select t.name from #__tag_term_content as tc left join #__tag_term as t on t.id=tc.tid where tc.cid='.$cid;
			$this->_db->setQuery($query);
			$tagsInArray=$this->_db->loadResultArray();
			if(isset($tagsInArray)&&!empty($tagsInArray)){
				return implode(',',$tagsInArray);
			}
			return '';
		}else{
			return '';
		}
	}

	function batchUpdate($arrayTags){
		if(count($arrayTags)){
			for( $i = 0; $i<count($arrayTags, 0); $i++){
				$cid = $arrayTags[$i][0];
				$tags = $arrayTags[$i][1];
				$comp = $arrayTags[$i][2];
				$deleteTags='delete from #__tag_term_content where cid='.$cid;
				$this->_db->setQuery($deleteTags);
				$this->_db->query();
				if(isset($tags)){
					$tagsArray=explode(',',$tags);
					if(count($tagsArray)){
						foreach($tagsArray as $tag){
							$tid=$this->storeTerm($tag);
							if($tid){
								$this->insertContentterm($tid,$cid,$comp);
							}
						}
					}
				}
			}
		}
	}
	function storeTerm($name,$description=NULL,$weight=0){
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

	function insertContentterm($tid,$cid,$comp){
		$insertQuery='insert into #__tag_term_content (tid,cid,component) values('.$tid.','.$cid.',"'.$comp.'")';
		$this->_db->setQuery($insertQuery);
		$this->_db->query();
	}

	function storeContentTerm($tid,$cid){
		$selectQuery='select * from  #__tag_term_content where tid='.$tid.' and cid='.$cid;
		$this->_db->setQuery($selectQuery);
		$this->_db->query();
		$numRows=$this->_db->getNumRows();
		if($numRows<=0){
			//Not exist, insert
			$this->insertContentterm($tid,$cid);
		}
	}


	function isContentHasTags($cid){
		$query='select count(*) as ct from #__tag_term_content where cid='.$cid;
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}
}
