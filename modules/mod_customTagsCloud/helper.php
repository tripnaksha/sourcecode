<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';
require_once JPATH_BASE.DS.'components'.DS.'com_tag'.DS.'helper'.DS.'helper.php';
class modCustomTagsCloudHelper
{
	function getList(&$params)
	{
		global $mainframe;
		$db			=& JFactory::getDBO();
		$termIds=$params->get("tagIds");
		
		$idsArray=@explode(',',$termIds);
		if(empty($idsArray)){
			return array();
		}
		$query = 'select id,name, 1 as sequence from #__tag_term where id in('.@implode(',',$idsArray).')';

		$db->setQuery($query);
		$rows = $db->loadObjectList();
	
    
		if(isset($rows)&&!empty($rows)){
			$rowsMap=array();
			$total_tags=count($rows);
			foreach($rows as $row){
				$rowsMap[$row->id]=$row;
			}
			$sortedRows=array();
			
			for($index=0;$index<$total_tags;$index++){
				$id=$idsArray[$index];	
				$rowsMap[$id]->	sequence=$total_tags-$index;	
				$sortedRows[]=$rowsMap[$id];
			}
			
			$rows=array_reverse($sortedRows);
			unset($sortedRows);
			unset($rowsMap);	
			$document =& JFactory::getDocument();
			$document->addStyleSheet(JURI::base() . 'components/com_tag/css/tagcloud.css');
			$tag_sizes = 7;
		
			$min_tags = $total_tags / $tag_sizes;
			$bucket_count = 1;
			$bucket_items = 0;
			$tags_set = 0;
			for($index=0;$index<$total_tags;$index++){
				$row=&$rows[$index];
				$row->link=JRoute::_('index.php?option=com_tag&task=tag&tag='.urlencode($row->name));
					
				$tag_count = $row->sequence;
				if(($bucket_items >= $min_tags) and $last_count != $tag_count and $bucket_count < $tag_sizes)
				{
					$bucket_count++;
					$bucket_items = 0;
					// Calculate a new minimum number of tags for the remaining classes.
					$remaining_tags = $total_tags - $tags_set;
					$min_tags = $remaining_tags / $bucket_count;
				}
				$row->class = 'tag'.$bucket_count;
				$bucket_items++;
				$tags_set++;
				$last_count = $tag_count;
				$row->name=JoomlaTagsHelper::ucwords($row->name);

			}
			usort($rows, array('JoomlaTagsHelper','tag_alphasort'));
		}
		return $rows;
	}


}



