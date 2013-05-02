<?php
/**
 * @version 1.0 $Id: categories.php 662 2008-05-09 22:28:53Z schlu $
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * EventList Component Categories Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class EventListModelCategories extends JModel
{
	/**
	 * Categories data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Categories total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		global $mainframe;

		// Get the paramaters of the active menu item
		$params = & $mainframe->getParams();

		//get the number of events from database
		$limit			= JRequest::getInt('limit', $params->get('cat_num'));
		$limitstart		= JRequest::getInt('limitstart');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to get the Categories
	 *
	 * @access public
	 * @return array
	 */
	function &getData( )
	{
		$elsettings = & ELHelper::config();

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );

			$k = 0;
			$count = count($this->_data);
			for($i = 0; $i < $count; $i++)
			{
				$category =& $this->_data[$i];

				if ($category->image != '') {

					$attribs['width'] = $elsettings->imagewidth;
					$attribs['height'] = $elsettings->imagehight;

					$category->image = JHTML::image('images/stories/'.$category->image, $category->catname, $attribs);
				} else {
					$category->image = JHTML::image('components/com_eventlist/assets/images/noimage.png', $category->catname);
				}
				
				//create target link
				$task 	= JRequest::getWord('task');
				
				$category->linktext = $task == 'archive' ? JText::_( 'SHOW ARCHIVE' ) : JText::_( 'SHOW EVENTS' );

				if ($task == 'archive') {
					$category->linktarget = JRoute::_('index.php?view=categoryevents&id='.$category->slug.'&task=archive');
				} else {
					$category->linktarget = JRoute::_('index.php?view=categoryevents&id='.$category->slug);
				}

				$k = 1 - $k;
			}

		}

		return $this->_data;
	}

	/**
	 * Total nr of Venues
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to load the Categories
	 *
	 * @access private
	 * @return array
	 */
	function _buildQuery()
	{
		//initialize some vars
		$user		= & JFactory::getUser();
		$gid		= (int) $user->get('aid');

		//check archive task and ensure that only categories get selected if they contain a published/archived event
		$task 	= JRequest::getVar('task', '', '', 'string');
		if($task == 'archive') {
			$eventstate = ' AND a.published = -1';
		} else {
			$eventstate = ' AND a.published = 1';
		}
				
		//get categories
		$query = 'SELECT c.*, c.id AS catid, COUNT( a.id ) AS assignedevents,'
				. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug'
				. ' FROM #__eventlist_categories AS c'
				. ' LEFT JOIN #__eventlist_events AS a ON a.catsid = c.id'
				. ' WHERE c.published = 1'
				. ' AND c.access <= '.$gid
				. $eventstate
				. ' GROUP BY c.id'
				. ' ORDER BY c.ordering'
				;

		return $query;
	}
}
?>