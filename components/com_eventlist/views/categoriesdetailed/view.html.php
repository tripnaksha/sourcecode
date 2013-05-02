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
 * HTML View class for the Categoriesdetailed View
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class EventListViewCategoriesdetailed extends JView
{
	/**
	 * Creates the Categoriesdetailed View
	 *
	 * @since 0.9
	 */
	function display( $tpl = null )
	{
		global $mainframe;

		//initialise variables
		$document 	= & JFactory::getDocument();
		$elsettings = & ELHelper::config();
		$model 		= $this->getModel();
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams();

		//get vars
		$limitstart		= JRequest::getInt('limitstart');
		$limit			= JRequest::getInt('limit', $params->get('cat_num'));
		$pathway 		= & $mainframe->getPathWay();
		$pop			= JRequest::getBool('pop');
		$task 			= JRequest::getWord('task');

		//Get data from the model
		$categories	= & $this->get('Data');
		$total 		= & $this->get('Total');

		//add css file
		$document->addStyleSheet($this->baseurl.'/components/com_eventlist/assets/css/eventlist.css');
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		$params->def( 'page_title', $item->name);

		//pathway
		$pathway->setItemName(1, $item->name);
		
		if ( $task == 'archive' ) {
			$pathway->addItem(JText::_( 'ARCHIVE' ), JRoute::_('index.php?view=categoriesdetailed&task=archive') );
			$print_link = JRoute::_( 'index.php?option=com_eventlist&view=categoriesdetailed&task=archive&pop=1&tmpl=component' );
			$pagetitle = $params->get('page_title').' - '.JText::_( 'ARCHIVE' );
		} else {
			$print_link = JRoute::_( 'index.php?option=com_eventlist&view=categoriesdetailed&pop=1&tmpl=component' );
			$pagetitle = $params->get('page_title');
		}
		//set Page title
		$mainframe->setPageTitle( $pagetitle );
		$mainframe->addMetaTag( 'title' , $pagetitle );
		$document->setMetadata( 'keywords' , $pagetitle );

		//Print
		$params->def( 'print', !$mainframe->getCfg( 'hidePrint' ) );
		$params->def( 'icons', $mainframe->getCfg( 'icons' ) );

		if ( $pop ) {
			$params->set( 'popup', 1 );
		}

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


		// Create the pagination object
		jimport('joomla.html.pagination');

		$pageNav = new JPagination($total, $limitstart, $limit);

		$this->assignRef('categories' , 			$categories);
		$this->assignRef('print_link' , 			$print_link);
		$this->assignRef('params' , 				$params);
		$this->assignRef('dellink' , 				$dellink);
		$this->assignRef('item' , 					$item);
		$this->assignRef('model' , 					$model);
		$this->assignRef('pageNav' , 				$pageNav);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('task' , 					$task);
		$this->assignRef('pagetitle' , 				$pagetitle);
		

		parent::display($tpl);

	}//function end

	/**
	 * Manipulate Data
	 *
	 * @since 0.9
	 */
	function getRows()
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
}
?>