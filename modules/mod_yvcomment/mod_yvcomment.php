<?php
/**
* yvComment module
* @version 1.18.0
* @package yvCommentModule
* @(c) 2008 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL
**/

// no direct access
defined('_JEXEC') or die('Restricted access');

// FYI: the file is 'require'd from JModuleHelper::renderModule() 
if (strtoupper( $_SERVER['REQUEST_METHOD'] ) == 'HEAD') {
	// hide, cause something works wrong in this case	
} else {
	
	require_once (dirname(__FILE__).DS.'helpers.php');

  //echo '<div>'; 	
  //echo 'Module "' . $module->title . '"<hr />'; 	
  //echo print_r($module, true) . '<hr />';
  //echo print_r($module->params, true) . '<hr />';
  //echo print_r($params, true) . '<hr />';
  //echo '</div>'; 	

  $yvComment_mod1 = new modyvcomment( null );
  if ($yvComment_mod1->Ok()) {
	  echo $yvComment_mod1->ShowModule($module, $params);
  }  
}
?>