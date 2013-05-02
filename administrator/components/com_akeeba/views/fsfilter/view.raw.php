<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: view.raw.php 91 2010-03-16 20:34:37Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

/**
 * The RAW mode view class for the Filesystem Filters page
 *
 */
class AkeebaViewFsfilter extends JView
{
	function display()
	{
		$dummy = new stdClass();
		$action = JRequest::getVar('action', $dummy);
		$verb = array_key_exists('verb', get_object_vars($action)) ? $action->verb : null;

		$ret_array = array();
		$model = $this->getModel();

		switch($verb)
		{
			// Return a listing for the normal view
			case 'list':
				$ret_array =& $model->make_listing($action->root, $action->crumbs, $action->node);
				break;

			// Toggle a filter's state
			case 'toggle':
				$ret_array = $model->toggle($action->root, $action->crumbs, $action->node, $action->filter);
				break;

			// Set a filter (used by the editor)
			case 'set':
				$ret_array = $model->set($action->root, $action->crumbs, $action->node, $action->filter);
				break;

			// Swap a filter (used by the editor)
			case 'swap':
				$ret_array = $model->swap($action->root, $action->crumbs, $action->old_node, $action->new_node, $action->filter);
				break;

			case 'tab':
				$ret_array = $model->get_filters($action->root);
				break;

			// Reset filters
			case 'reset':
				$ret_array = $model->reset($action->root);
				break;
		}

		$json = json_encode($ret_array);
		$this->assign('json', $json);

		parent::display('raw');
	}
}
?>