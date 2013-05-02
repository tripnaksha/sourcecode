<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');

require_once JPATH_COMPONENT_SITE.DS.'helper'.DS.'helper.php';

class TagModelTag extends JModel
{
	/**
	 * tag data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Search total
	 *
	 * @var integer
	 */
	var $_total = null;

	var $_termExist=false;


	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	var $_defaultLimit = 10;
	var $_tagDescription=null;


	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		$this->_defaultLimit=JoomlaTagsHelper::param('page_limit',10);
		$this->_loadData();
	}

	function getTermExist(){
		return $this->_termExist;
	}

	function getData()
	{
		return $this->_data;
	}

	function getTagDescription(){
		return $this->_tagDescription;
	}

	function _loadData(){
		$query= $this->_buildQuery();
		if($this->_termExist){
			$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

			$this->_data=$this->_getList($query,$limitstart,$this->_defaultLimit);
		}
	}

	function getTotal(){
		return $this->_total;
	}

	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $limitstart,$this->_defaultLimit);
		}

		return $this->_pagination;
	}

	function _buildQuery(){
		$tag=JRequest::getString('tag', null);
		
		$tag=URLDecode($tag);
        $tag=explode("?start=",$tag);
        $tag=JoomlaTagsHelper::preHandle($tag[0]);
	
		JRequest::setVar('tag',$tag);
		$tag=trim($tag);
		$db=& JFactory::getDBO();


		$tagObj;
		$ids;
		if(!isset($this->_tagDescription)){

			$tagDescriptionQuery="select id,description from #__tag_term where name='".$tag."'";
			
			$db->setQuery($tagDescriptionQuery);
			$db->query();
			$this->_tagDescription=$db->loadResult();
			$tagObj=$db->loadObject();
			if(isset($tagObj)&&$tagObj->id){
				$this->_termExist=true;
			}else{
				$this->_termExist=false;
				return '';
			}
			$updateHitsQuery="update #__tag_term set hits=hits+1 where id=".$tagObj->id;

			$db->setQuery($updateHitsQuery);

			$db->query();

			$this->_tagDescription=$tagObj->description;

			$totalQuery="select count(c.cid) from #__tag_term_content as c where c.tid=".$tagObj->id;

			$db->setQuery($totalQuery);

			$db->query();



			$this->_total=$db->loadResult();



			//get content items with this tag
			$tagQuery="select  c.cid from #__tag_term_content as c  where c.component='com_content' and c.tid=".$tagObj->id;

			

			$db->setQuery($tagQuery);

			$contentIds = $db->loadResultArray();



			$cids=implode(',',$contentIds);


			//get trail items with this tag
			$tagQuery="select  c.cid from #__tag_term_content as c  where c.component='com_traildisplay' and c.tid=".$tagObj->id;

			

			$db->setQuery($tagQuery);

			$contentIds = $db->loadResultArray();



			$tids=implode(',',$contentIds);

		}



		$nullDate	= $db->getNullDate();
		jimport('joomla.utilities.date');
		$date = new JDate();
		$now  = $date->toMySQL();
		$order=JoomlaTagsHelper::param('Order');

		$ShowArchiveArticles=JoomlaTagsHelper::param('ShowArchiveArticles');

		$state=' a.state = 1 ';

		if($ShowArchiveArticles){

			$state.=' or a.state = -1';

		}

		if ($cids.length>0)
		$query = 'SELECT '.
		' a.id, a.title, a.created,u.name as author,a.created_by_alias as created_by_alias ,a.sectionid,COUNT(a.id) as total,a.introtext, a.fulltext, a.access, cc.title as section,' .
		' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
		' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug,'.

		' CHAR_LENGTH( a.`fulltext` ) AS readmore, "content" as source'.
		' FROM #__content AS a' .		
		' INNER JOIN #__categories AS cc ON cc.id = a.catid' .	

        ' INNER JOIN #__sections AS s ON s.id=a.sectionid'.  

		' INNER JOIN #__users AS u ON u.id=a.created_by'.   
		' WHERE (a.id in ('.$cids.') AND '.

		'('.$state.'))' .
		' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )' .
		' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'.			
		' AND cc.published = 1'.
		' GROUP BY(a.id) UNION ALL ';//  ORDER BY  '.$this->_buildOrderBy($order);
		else $query = '';
		$query .= ' SELECT a.id, a.name as title, a.createtime as created, u.name as author, u.name as created_by_alias , "0", COUNT(a.id) as total, b.descr AS introtext, b.descr AS fulltxt, a.private, "Trail" as section, '.
		' CASE WHEN CHAR_LENGTH(replace(a.name, " ", "-")) THEN CONCAT_WS(":", a.id, replace(a.name, " ", "-")) ELSE a.id END as slug, '.
		' "Trails" as catslug, '.
		' CHAR_LENGTH( b.descr ) AS readmore , "trail" as source'.
		' FROM #__trailAddlInfo AS b, #__trailList AS a '.
		' LEFT JOIN #__users AS u ON u.id = a.userId '.
		' WHERE a.id '.
		' IN ('.$tids.') '.
		' AND b.trail_id = a.id '.
		' AND a.private = 0 '.
		' GROUP BY a.id ';
//		echo($query);
		
		return $query;

	}

	function _buildOrderBy($order){
		$orderby='a.ordering';
		switch ($order)
		{
			case 'date' :
				$orderby = 'a.created';
				break;

			case 'rdate' :
				$orderby = 'a.created DESC';
				break;

			case 'alpha' :
				$orderby = 'a.title';
				break;

			case 'ralpha' :
				$orderby = 'a.title DESC';
				break;

			case 'hits' :
				$orderby = 'a.hits DESC';
				break;

			case 'rhits' :
				$orderby = 'a.hits';
				break;

			case 'order' :
				$orderby = 'a.ordering';
				break;

			case 'author' :
				$orderby = 'a.created_by_alias, u.name';
				break;

			case 'rauthor' :
				$orderby = 'a.created_by_alias DESC, u.name DESC';
				break;

			case 'front' :
				$orderby = 'f.ordering';
				break;

			default :
				$orderby = 'a.ordering';
				break;
			
		}
		return $orderby;
	}

}
