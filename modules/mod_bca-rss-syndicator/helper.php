<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class modBcaRssSyndicatorHelper{	

	function getFeeds($ids)
	{

		$db =& JFactory::getDBO();		
		
		$data = null;
		
		if ($ids && $ids != "*") {
			$query = "SELECT * FROM #__bcarsssyndicator_feeds WHERE published = 1 AND id IN ( $ids ) ORDER BY feed_name";			
		} else {
			$query = "SELECT * FROM #__bcarsssyndicator_feeds WHERE  published = 1 ORDER BY feed_name";
		}
		
		if(empty($this->data))
		{
			$db->setQuery($query);
			$this->data = $db->loadObjectList();
		}
		if(!$this->data)
		{
			$this->data = array();
		}
		return $this->data;
	}
}
?>
