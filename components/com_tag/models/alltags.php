<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');

require_once JPATH_COMPONENT_SITE.DS.'helper'.DS.'helper.php';

class TagModelAllTags extends JModel
{	function getAllTags(){
	$order=JoomlaTagsHelper::param('tagOrder');
	$orderby=$this->_buildOrderBy($order);
	$query='select count(*) as ct,name from #__tag_term_content as tc inner join #__tag_term as t on t.id=tc.tid  group by(tid) order by '.$orderby;
	return $this->_getList($query);
}
function _buildOrderBy($order){
	$orderby='RAND()';
	switch ($order)
	{
		case 'random':
			$orderby = 'RAND()';
			break;
		case 'date' :
			$orderby = 't.created';
			break;

		case 'rdate' :
			$orderby = 't.created DESC';
			break;

		case 'alpha' :
			$orderby = 't.name';
			break;

		case 'ralpha' :
			$orderby = 't.name DESC';
			break;

		case 'hits' :
			$orderby = 't.hits DESC';
			break;

		case 'rhits' :
			$orderby = 't.hits';
			break;

		default :
			$orderby = 'RAND()';
			break;

	}
	return $orderby;
}
}
