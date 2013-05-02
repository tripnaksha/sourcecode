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
 * HTML View class for the Categoryevents View
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class EventListViewCategoryevents extends JView
{
	/**
	 * Creates the Categoryevents View
	 *
	 * @since 0.9
	 */
	function display( $tpl=null )
	{
		global $mainframe, $option;

		//initialize variables
		$document 	= & JFactory::getDocument();
		$menu		= & JSite::getMenu();
		$elsettings = & ELHelper::config();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams();
		$uri 		= & JFactory::getURI();
		$pathway 	= & $mainframe->getPathWay();

		//add css file
		$document->addStyleSheet($this->baseurl.'/components/com_eventlist/assets/css/eventlist.css');
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		// Request variables
		$limitstart		= JRequest::getInt('limitstart');
		$limit       	= $mainframe->getUserStateFromRequest('com_eventlist.categoryevents.limit', 'limit', $params->def('display_num', 0), 'int');
		$task 			= JRequest::getWord('task');
		$pop			= JRequest::getBool('pop');

		//get data from model
		$rows 		= & $this->get('Data');
		$category 	= & $this->get('Category');
		$total 		= & $this->get('Total');

		//are events available?
		if (!$rows) {
			$noevents = 1;
		} else {
			$noevents = 0;
		}

		//does the category exist
		if ($category->id == 0)
		{
			return JError::raiseError( 404, JText::sprintf( 'Category #%d not found', $category->id ) );
		}

		//Set Meta data
		$document->setTitle( $item->name.' - '.$category->catname );
    	$document->setMetadata( 'keywords', $category->meta_keywords );
    	$document->setDescription( strip_tags($category->meta_description) );

    	//Print function
		$params->def( 'print', !$mainframe->getCfg( 'hidePrint' ) );
		$params->def( 'icons', $mainframe->getCfg( 'icons' ) );

		if ( $pop ) {
			$params->set( 'popup', 1 );
		}

		//add alternate feed link
		$link    = 'index.php?option=com_eventlist&view=categoryevents&format=feed&id='.$category->id;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom', 'alternate', 'rel'), $attribs);

		//create the pathway
		$pathway->setItemName(1, $item->name);
		
		if ($task == 'archive') {
			$pathway->addItem( JText::_( 'ARCHIVE' ).' - '.$category->catname, JRoute::_('index.php?option='.$option.'&view=categoryevents&task=archive&id='.$category->slug));
			$link = JRoute::_( 'index.php?option=com_eventlist&view=categoryevents&task=archive&id='.$category->slug );
			$print_link = JRoute::_( 'index.php?option=com_eventlist&view=categoryevents&id='. $category->id .'&task=archive&pop=1&tmpl=component');
		} else {
			$pathway->addItem( $category->catname, JRoute::_('index.php?option='.$option.'&view=categoryevents&id='.$category->slug));
			$link = JRoute::_( 'index.php?option=com_eventlist&view=categoryevents&id='.$category->slug );
			$print_link = JRoute::_( 'index.php?option=com_eventlist&view=categoryevents&id='. $category->id .'&pop=1&tmpl=component');
		}

		//Check if the user has access to the form
		$maintainer = ELUser::ismaintainer();
		$genaccess 	= ELUser::validate_user( $elsettings->evdelrec, $elsettings->delivereventsyes );

		if ($maintainer || $genaccess ) $dellink = 1;

		// Create the pagination object
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);

		//Generate Categorydescription
		if (empty ($category->catdescription)) {
			$catdescription = JText::_( 'NO DESCRIPTION' );
		} else {
			//execute plugins
			$category->text	= $category->catdescription;
			$category->title 	= $category->catname;
			JPluginHelper::importPlugin('content');
			$results = $mainframe->triggerEvent( 'onPrepareContent', array( &$category, &$params, 0 ));
			$catdescription = $category->text;
		}

		if ($category->image != '') {

			$imgattribs['width'] = $elsettings->imagewidth;
			$imgattribs['height'] = $elsettings->imagehight;

			$category->image = JHTML::image('images/stories/'.$category->image, $category->catname, $imgattribs);
		} else {
			$category->image = JHTML::image('components/com_eventlist/assets/images/noimage.png', $category->catname);
		}

		//create select lists
		$lists	= $this->_buildSortLists($elsettings);
		$this->assign('lists', 						$lists);
		$this->assign('action', 					$uri->toString());

		$this->assignRef('rows' , 					$rows);
		$this->assignRef('noevents' , 				$noevents);
		$this->assignRef('category' , 				$category);
		$this->assignRef('print_link' , 			$print_link);
		$this->assignRef('params' , 				$params);
		$this->assignRef('dellink' , 				$dellink);
		$this->assignRef('task' , 					$task);
		$this->assignRef('catdescription' , 		$catdescription);
		$this->assignRef('pageNav' , 				$pageNav);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('item' , 					$item);

		parent::display($tpl);
	}

	/**
	 * Manipulate Data
	 *
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

	function _buildSortLists($elsettings)
	{
		// Table ordering values
		$filter_order		= JRequest::getCmd('filter_order', 'a.dates');
		$filter_order_Dir	= JRequest::getCmd('filter_order_Dir', 'DESC');
//		$filter_order_Dir	= JRequest::getCmd('filter_order_Dir', 'ASC');

		$filter				= JRequest::getString('filter');
		$filter_type		= JRequest::getString('filter_type');

		$sortselects = array();
		$sortselects[]	= JHTML::_('select.option', 'title', $elsettings->titlename );
		$sortselects[] 	= JHTML::_('select.option', 'venue', $elsettings->locationname );
		$sortselects[] 	= JHTML::_('select.option', 'city', $elsettings->cityname );
		$sortselect 	= JHTML::_('select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $filter_type );

		$lists['order_Dir'] 	= $filter_order_Dir;
		$lists['order'] 		= $filter_order;
		$lists['filter'] 		= $filter;
		$lists['filter_type'] 	= $sortselect;

		return $lists;
	}
}
?>