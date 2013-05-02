<?php

defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class bcarsssyndicatorModelFeed extends JModel
{

	var $_data = null;
	var $_sdata = null;
	var $_total = null;
	var $_pagination = null;
	var $_id = null;	
	var $_sections;
	var $_exCategories;
	var $_seccatlist;
	
	function __construct()
	{
		parent::__construct();

		global $mainframe, $option;

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		
		//edit feed
		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}
	
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_sdata	= null;
	}
	/**
	 * Method to get the total number of feeds items
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}		
		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the feeds
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
			
		}
		
		return $this->_pagination;
	}
	
	

	function _buildQuery()
	{
		$query = "SELECT * FROM `#__bcarsssyndicator_feeds`";
		return $query;
	}

	function getData()
	{
		// Load the data
		if (empty( $this->_data )) {
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));;
		}
		if (!$this->_data) {
			$this->_data = null;
		}
		
		return $this->_data;
	}

	
//Feed
	
	/**
	 * Method to remove a feed
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 * lvh
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM `#__bcarsssyndicator_feeds`'
				. ' WHERE id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to (un)publish a feed
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 * lvh
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user 	=& JFactory::getUser();

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			
			$query = "UPDATE `#__bcarsssyndicator_feeds` SET published='$publish'"
	. "\nWHERE id IN ($cids)";
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}
	
	function getSData()
	{
		// Load the data
		if (empty( $this->_sdata )) {
			$query = "SELECT * FROM `#__bcarsssyndicator_feeds` WHERE id = $this->_id";
			$this->_db->setQuery( $query );
			$this->_sdata = $this->_db->loadObject();
		}
		if (!$this->_sdata) {
			$this->_sdata = new stdClass();
			$this->_sdata->id = 0;			
		}
		return $this->_sdata;
	}
	
	function getSections()
	{
		if (empty( $this->_sections ))
		{
			$query = "SELECT id, title"
					. "\n FROM #__sections"
					. "\n WHERE published = 1"
					. "\n AND scope = 'content'"
					. "\n ORDER BY ordering"
					;
			$this->_sections = $this->_getList( $query );
		}
		return $this->_sections;
	}
	
	function getExCategories()
	{
		if (empty( $this->_exCategories ))
		{
			$query = "SELECT c.id, CONCAT( 'Exclude: ', s.title, ' | ', c.title ) AS title"
					. "\n FROM #__categories AS c"
					. "\n INNER JOIN #__sections AS s ON s.id=c.section"
					. "\n WHERE c.published = 1"
					. "\n AND s.published = 1"
					. "\n AND s.scope = 'content'"
					. "\n ORDER BY s.ordering, c.ordering"
					;
			$this->_exCategories = $this->_getList( $query );
		}
		return $this->_exCategories;
	}
	
	function getSectionCategoryList()
	{
		if (empty( $this->_seccatlist ))
		{
			$query = "SELECT id AS value, title AS text FROM #__sections ORDER BY title";
			$secs = $this->_getList( $query );
			foreach($secs as $sec)
			{
				$subquery = "SELECT id AS value, title FROM #__categories WHERE section = " . $sec->value . " ORDER BY title";
				$cats = $this->_getList( $subquery );
				
				foreach($cats as $cat)
				{
					$this->_seccatlist[] = $cat->title;
					
				}
			}
		}
		return $this->_seccatlist;
	}
	
	function save()
	{
		$id = JRequest::getVar('id', '0', 'post', 'int');
		$a_msg_sectlist = JRequest::getVar('msg_sectlist', array(), 'post', 'array');
		$a_msg_excatlist = JRequest::getVar('msg_excatlist', array(), 'post', 'array');
		
		$msg_sectlist  = implode(',', $a_msg_sectlist);
		$msg_excatlist  = implode(',', $a_msg_excatlist);

		$feed_name = JRequest::getVar('feed_name', '', 'post', 'string');
        $feed_name = $this->_db->Quote($this->_db->getEscaped($feed_name), false);

		$feed_description = JRequest::getVar('feed_description', '', 'post', 'string');
        $feed_description = $this->_db->Quote($this->_db->getEscaped($feed_description), false);

		$feed_type = JRequest::getVar('feed_type', '', 'post', 'string');
		$feed_cache = JRequest::getVar('feed_cache', '', 'post', 'string');

		$feed_imgUrl = JRequest::getVar('feed_imgUrl', '', 'post', 'string');
        $feed_imgUrl = $this->_db->Quote($this->_db->getEscaped($feed_imgUrl), false);

		$feed_button = JRequest::getVar('feed_button', '', 'post', 'string');
        $feed_button = $this->_db->Quote($this->_db->getEscaped($feed_button), false);
        
		$feed_renderAuthorFormat = JRequest::getVar('feed_renderAuthorFormat', '', 'post', 'string');
		$feed_renderHTML   = JRequest::getVar('feed_renderHTML', '0', 'post', 'int');
		$feed_renderImages = JRequest::getVar('feed_renderImages', '0', 'post', 'int');
		$msg_count = JRequest::getVar('msg_count', '', 'post', 'string');
		$msg_orderby=JRequest::getVar('msg_orderby', '', 'post', 'string');
		$msg_numWords = JRequest::getVar('msg_numWords', '0', 'post', 'int');
		$msg_FPItemsOnly = JRequest::getVar('msg_FPItemsOnly', '0', 'post', 'int');
		$msg_fulltext = JRequest::getVar('msg_fulltext', '0', 'post', 'int');
		$published = JRequest::getVar('published', '0', 'post', 'int');
		//VH Oct 27 2008		
		$msg_exitems = JRequest::getVar('msg_exitems', '', 'post', 'string');;	
		
		$isNew = ($id<1);
		if($isNew)
			$query = "INSERT INTO #__bcarsssyndicator_feeds (`feed_name`,`feed_description`, `feed_type`, `feed_cache` ,`feed_imgUrl`,
					  `feed_button`, `feed_renderAuthorFormat`,  `feed_renderHTML`, `feed_renderImages` , `msg_count` , `msg_orderby`,
					  `msg_numWords` , `msg_FPItemsOnly`, `msg_sectlist` , `msg_excatlist` , `msg_fulltext` , `msg_exitems` ,
					  `published`) 
						VALUES 
						(
							$feed_name,
							$feed_description,
							'$feed_type',
							'$feed_cache',
							$feed_imgUrl,
							$feed_button,
							'$feed_renderAuthorFormat',
							'$feed_renderHTML',
							'$feed_renderImages',
							'$msg_count',
							'$msg_orderby',
							'$msg_numWords',
							'$msg_FPItemsOnly',
							'$msg_sectlist',
							'$msg_excatlist',
							'$msg_fulltext',
							'$msg_exitems',
							'$published'
						)
				";
			else
				$query = "UPDATE #__bcarsssyndicator_feeds SET
							`feed_name` = $feed_name,
							`feed_description` = $feed_description,
							`feed_type` = '$feed_type',
							`feed_cache` = '$feed_cache',
							`feed_imgUrl` = $feed_imgUrl,
							`feed_button` = $feed_button,
							`feed_renderAuthorFormat` = '$feed_renderAuthorFormat',
							`feed_renderHTML` = '$feed_renderHTML',
							`feed_renderImages` = '$feed_renderImages',
							`msg_count` = '$msg_count',
							`msg_orderby` = '$msg_orderby',
							`msg_numWords` = '$msg_numWords',
							`msg_FPItemsOnly` = '$msg_FPItemsOnly',
							`msg_sectlist` = '$msg_sectlist',
							`msg_excatlist` = '$msg_excatlist',
							`msg_fulltext` = '$msg_fulltext',
							`msg_exitems` = '$msg_exitems',
							`published` = '$published'
						WHERE id = $id
				";			
		$this->_db->setQuery($query);
		$this->_data = $this->_db->query();
		if($this->_data)
			return true;
		else
			return false;
	}
	
	function getDefaultData()
	{
		$config =& $this->getInstance('config','bcarsssyndicatorModel');
		return $config->getData();
	}

}
