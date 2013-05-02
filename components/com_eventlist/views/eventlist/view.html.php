<?php
/**
 * @version 1.0 $Id: view.html.php 662 2008-05-09 22:28:53Z schlu $
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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the EventList View
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class EventListViewEventList extends JView
{
	/**
	 * Creates the Simple List View
	 *
	 * @since 0.9
	 */
	function display( $tpl = null )
	{
		global $mainframe;

		//initialize variables
		$document 	= & JFactory::getDocument();
		$elsettings = & ELHelper::config();
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams();
		$uri 		= & JFactory::getURI();
		$pathway 	= & $mainframe->getPathWay();

		//add css file
		$document->addStyleSheet($this->baseurl.'/components/com_eventlist/assets/css/eventlist.css');
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		// get variables
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$limit		= $mainframe->getUserStateFromRequest('com_eventlist.eventlist.limit', 'limit', $params->def('display_num', 0), 'int');
		$task 		= JRequest::getWord('task');
		$pop		= JRequest::getBool('pop');

		//get data from model
		$rows 	= & $this->get('Data');
		$total 	= & $this->get('Total');

		//are events available?
		if (!$rows) {
			$noevents = 1;
		} else {
			$noevents = 0;
		}

		//params
		$params->def( 'page_title', $item->name);

		if ( $pop ) {//If printpopup set true
			$params->set( 'popup', 1 );
		}

		//pathway
		$pathway->setItemName( 1, $item->name );
		
		if ( $task == 'archive' ) {
			$pathway->addItem(JText::_( 'ARCHIVE' ), JRoute::_('index.php?view=eventlist&task=archive') );
			$print_link = JRoute::_('index.php?view=eventlist&task=archive&tmpl=component&pop=1');
			$pagetitle = $params->get('page_title').' - '.JText::_( 'ARCHIVE' );
		} else {
			$print_link = JRoute::_('index.php?view=eventlist&tmpl=component&pop=1');
			$pagetitle = $params->get('page_title');
		}
		
		//Set Page title
		$mainframe->setPageTitle( $pagetitle );
   		$mainframe->addMetaTag( 'title' , $pagetitle );

		//Check if the user has access to the form
		$maintainer = ELUser::ismaintainer();
		$genaccess 	= ELUser::validate_user( $elsettings->evdelrec, $elsettings->delivereventsyes );

		if ($maintainer || $genaccess ) $dellink = 1;

		//add alternate feed link
		$link    = 'index.php?option=com_eventlist&view=eventlist&format=feed';
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		
		//create select lists
		$lists	= $this->_buildSortLists();
		
		if ($lists['filter']) {
			//$uri->setVar('filter', JRequest::getString('filter'));
			//$filter		= $mainframe->getUserStateFromRequest('com_eventlist.eventlist.filter', 'filter', '', 'string');
			$uri->setVar('filter', $lists['filter']);
			$uri->setVar('filter_type', JRequest::getString('filter_type'));
		} else {
			$uri->delVar('filter');
			$uri->delVar('filter_type');
		}

		// Create the pagination object
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);

		$this->assign('lists' , 					$lists);
		$this->assign('total',						$total);
		$this->assign('action', 					$uri->toString());

		$this->assignRef('rows' , 					$rows);
		$this->assignRef('task' , 					$task);
		$this->assignRef('noevents' , 				$noevents);
		$this->assignRef('print_link' , 			$print_link);
		$this->assignRef('params' , 				$params);
		$this->assignRef('dellink' , 				$dellink);
		$this->assignRef('pageNav' , 				$pageNav);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('pagetitle' , 				$pagetitle);

		parent::display($tpl);

	}

	/**
	 * Manipulate Data
	 *
	 * @access public
	 * @return object $rows
	 * @since 0.9
	 */
	function &getRows()
	{
		$count = count($this->rows);

		if (!$count) {
			return;
		}
				
		$k = 0;
		foreach($this->rows as $key => $row)
		{
			$row->odd   = $k;
			
			$this->rows[$key] = $row;
			$k = 1 - $k;
		}

		return $this->rows;
	}

	/**
	 * Method to build the sortlists
	 *
	 * @access private
	 * @return array
	 * @since 0.9
	 */
	function _buildSortLists()
	{
		$elsettings = & ELHelper::config();
		
		$filter_order		= JRequest::getCmd('filter_order', 'a.dates');
		$filter_order_Dir	= JRequest::getWord('filter_order_Dir', 'DESC');
//		$filter_order_Dir	= JRequest::getWord('filter_order_Dir', 'ASC');

		$filter				= JRequest::getString('filter');
		$filter_type		= JRequest::getString('filter_type');

		$sortselects = array();
		$sortselects[]	= JHTML::_('select.option', 'title', $elsettings->titlename );
		$sortselects[] 	= JHTML::_('select.option', 'venue', $elsettings->locationname );
		$sortselects[] 	= JHTML::_('select.option', 'city', $elsettings->cityname );
		if ($elsettings->showcat) {
			$sortselects[] 	= JHTML::_('select.option', 'type', $elsettings->catfroname );
		}
		$sortselect 	= JHTML::_('select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $filter_type );

		$lists['order_Dir'] 	= $filter_order_Dir;
		$lists['order'] 		= $filter_order;
		$lists['filter'] 		= $filter;
		$lists['filter_types'] 	= $sortselect;

		return $lists;
	}
}
?>