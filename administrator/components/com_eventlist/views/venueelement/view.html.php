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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the EventList venueselect screen
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class EventListViewVenueelement extends JView {

	function display($tpl = null)
	{
		global $mainframe, $option;

		//initialise variables
		$db			= & JFactory::getDBO();
		$document	= & JFactory::getDocument();
		
		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.modal');

		//get vars
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.venueelement.filter_order', 'filter_order', 'l.ordering', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.venueelement.filter_order_Dir', 'filter_order_Dir', '', 'word' );
		$filter 			= $mainframe->getUserStateFromRequest( $option.'.venueelement.filter', 'filter', '', 'int' );
		$filter_state 		= $mainframe->getUserStateFromRequest( $option.'.venueelement.filter_state', 'filter_state', '*', 'word' );
		$search 			= $mainframe->getUserStateFromRequest( $option.'.venueelement.search', 'search', '', 'string' );
		$search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );
		$template 			= $mainframe->getTemplate();

		//prepare document
		$document->setTitle(JText::_( 'SELECTVENUE' ));
		$document->addStyleSheet("templates/$template/css/general.css");

		// Get data from the model
		$rows      	= & $this->get( 'Data');
//		$total      = & $this->get( 'Total');
		$pageNav 	= & $this->get( 'Pagination' );

		//publish unpublished filter
		$lists['state']	= JHTML::_('grid.state', $filter_state );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		//Build search filter
		$filters = array();
		$filters[] = JHTML::_('select.option', '1', JText::_( 'VENUE' ) );
		$filters[] = JHTML::_('select.option', '2', JText::_( 'CITY' ) );
		$lists['filter'] = JHTML::_('select.genericlist', $filters, 'filter', 'size="1" class="inputbox"', 'value', 'text', $filter );

		// search filter
		$lists['search']= $search;

		//assign data to template
		$this->assignRef('lists'      	, $lists);
		$this->assignRef('rows'      	, $rows);
		$this->assignRef('pageNav' 		, $pageNav);

		parent::display($tpl);
	}
}
?>