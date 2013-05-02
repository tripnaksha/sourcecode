<?php
/**
 * @version 1.0 $Id: sampledata.php 662 2008-05-09 22:28:53Z schlu $
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

jimport('joomla.application.component.controller');

/**
 * EventList Component Sampledata Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class EventListControllerSampledata extends EventListController
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();
	}
 	
 	/**
	 * Process sampledata
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function load()
	{
		//get model
		$model 	= $this->getModel('sampledata');
		if (!$model->loaddata()) {
			$msg 	= JText::_( 'SAMPLEDATA FAILED' );
		} else {
			$msg 	= JText::_( 'SAMPLEDATA SUCCESSFULL' );
		}
		
		$link 	= 'index.php?option=com_eventlist&view=eventlist';
		
		$this->setRedirect($link, $msg);
 	}
}
?>