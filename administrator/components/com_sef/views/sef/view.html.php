<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

//require_once(JPATH_COMPONENT.DS.'classes'.DS.'button.php');

class SEFViewSEF extends JView
{
	function display($tpl = null)
	{
		JToolBarHelper::title(JText::_('JoomSEF'), 'artio.png');
		
		// Get number of URLs for purge warning
		$model =& JModel::getInstance('URLs', 'SEFModel');
		$this->assign('purgeCount', $model->getCount(0));

		parent::display($tpl);
	}
}
