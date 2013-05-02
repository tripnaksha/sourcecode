<?php
/**
 * @version		$Id: search.php 10752 2008-08-23 01:53:31Z eddieajau $
 * @package		Joomla
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * Search Component Search Model
 *
 * @package		Joomla
 * @subpackage	Search
 * @since 1.5
 */
class SearchModelAjax extends JModel
{

	var $_data = null;


	function __construct()
	{
		parent::__construct();

		global $mainframe;

		// Set the search parameters
		$keyword		= JRequest::getVar('queryString');
		$match			= JRequest::getWord('searchphrase', 'all');
		$ordering		= JRequest::getWord('ordering', 'newest');
		$this->setSearch($keyword, $match, $ordering);
	

	}

	
	function setSearch($keyword, $match = 'all', $ordering = 'newest')
	{
		if(isset($keyword)) {
			$this->setState('keyword', $keyword);
		}

		if(isset($match)) {
			$this->setState('match', $match);
		}

		if(isset($ordering)) {
			$this->setState('ordering', $ordering);
		}
	}

	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			JPluginHelper::importPlugin( 'search');
			$dispatcher =& JDispatcher::getInstance();
			$results = $dispatcher->trigger( 'onSearch', array(
			$this->getState('keyword'),
			$this->getState('match'),
			$this->getState('ordering'),
			"") );

			$rows = array();
			foreach($results AS $result) {
				$rows = array_merge( (array) $rows, (array) $result);
			}

			$this->_data = $rows;
		
		}

		return $this->_data;
	}
	
	function getParams()
	{
		//id for parameters
		$searchId = JRequest::getVar('id');
	
		$db =& JFactory::getDBO(); 
		$query = 'SELECT params'
			. ' FROM #__modules'
			. ' WHERE published=1 AND id='. (int) $searchId
		;
		$db->setQuery($query);
	    $result=$db->loadResult();

		return $result;
	}

}

?>