<?php
/**
* yvComment module helper
* @version 1.18.0
* @package yvCommentModule
* @(c) 2008 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL
**/

// no direct access
defined('_JEXEC') or die('Restricted access');
define('yvCommentModuleVersion', '1.20.0');

class modyvcomment extends JObject {
	// static member is not compatible with PHP4 :-( 
	var $_Ok = true; // if false - error state: try to be quiet

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for
	 * plugins because func_get_args ( void ) returns a copy of all passed arguments
	 * NOT references.  This causes problems with cross-referencing necessary for the
	 * observer design pattern.
	 */
	function modyvcomment($config) {
		parent :: __construct($config);
		global $mainframe;
		global $yvComment;

		//echo 'yvcomment Module constructor' . '<br/>';
		$Ok = true;
		
		if (!$yvComment) {
			$Ok = false;
			$path = JPATH_SITE . DS . 'components' . DS . 'com_yvcomment' . DS . 'helpers.php';
			if (file_exists($path)) {
			  require_once ($path);
		  	$yvComment = new yvCommentHelper();
			}
			if ($yvComment) {
				if (!$yvComment->JoomlaCoreVersionCheck()) {
					$Ok = false;
				}
			} else {
				// No yvComment Component
			  $mainframe->enqueueMessage(
				  'yvComment Module is installed, but "<b>yvComment Component</b>" is <b>not</b> installed. Please install it to use <a href="http://yurivolkov.com/Joomla/yvComment/index_en.html" target="_blank">yvComment solution</a>.<br/>' . '(yvComment Module version="' . yvCommentModuleVersion . '")',
			  	'error');
			}
			if (!$Ok) {
				$yvComment = null;
			}
		}
		if ($Ok) {
			$Ok = $yvComment->Ok();
		}	
		if ($Ok) {
			if (strcmp(yvCommentModuleVersion, yvCommentVersion) != 0) {
				$message = 'Versions of "yvComment Module" and "yvComment Component" are not the same.<br/>' 
					. '(Module version="' . yvCommentModuleVersion . '"; Component version="' 
					. yvCommentVersion . '")<br/>' 
					. 'Please install the same versions from <a href="http://yurivolkov.com/Joomla/yvComment/index_en.html" target="_blank">yvComment home page</a>.';
				$mainframe->enqueueMessage($message, 'error');
				$Ok = false;
			}
		}
		$this->_Ok = $Ok;		
	}

	function Ok() {
		return $this->_Ok; 
	}

	// Information that should be placed immediately after the generated content
	function ShowModule(& $module, & $params, $page = 0) {
		global $yvComment;
		global $option;
		$Ok = true;
		$strOut = "";
		$task = 'viewdisplay';

  	$InstanceInd = $yvComment->BeginInstance('module', $params);
		$yvComment->setPageValue('module_title', $module->title);
		
    $viewName = $yvComment->getPageValue('view_name', 'listofcomments');
		$layoutName = $yvComment->getPageValue('layout_name', 'default');
		if ($layoutName == '0') {
			$layoutName = $yvComment->getPageValue('layout_name_custom', 'default');
		}
    JRequest::setVar('layout', $layoutName);

		// Pagination for module doesn't work... Joomla's "feature"...
		//   - e.g. Frontpage menu item causes injection of 'limit'... to the Request...
		$show_pagination = false;
		//$show_pagination = $yvComment->getPageValue('show_pagination', false);	
		if (!$show_pagination) {
		  // Next line doesn't work, because it doesn't really set parameter to 'false':
		  //   $yvComment->setPageValue('show_pagination', false);
		  // And this works:	
		  $yvComment->setPageValue('show_pagination', '0');	
		  // echo 'show_pagination=' . $yvComment->getPageValue('show_pagination', '???') . ';';	
			$limit = intval($yvComment->getPageValue('count', 0));
			if ($limit > 0) {
				$yvComment->setPageValue('yvcomment_limit', $limit);
			}
		}	  	

		if ($Ok) {
			$config = array ();
			$config['task'] = $task;
			$config['view'] = $viewName;
	
			// This is needed only because we can't 'undefine' this:
			//define( 'JPATH_COMPONENT',					JPATH_BASE.DS.'components'.DS.$name);
			$config['base_path'] = JPATH_SITE_YVCOMMENT;
	
			// Create the controller
			$controller = new yvcommentController($config);
	
			// Perform the Request task
			$controller->execute($task);
	
			$strOut .= $controller->getOutput();
			if ($params->get('module_title_is_dynamic', true)) {
				$module->title = $yvComment->getPageValue('module_title', $module->title);
			}

		}

  	$yvComment->EndInstance($InstanceInd);
		return $strOut;
	}
}
?>