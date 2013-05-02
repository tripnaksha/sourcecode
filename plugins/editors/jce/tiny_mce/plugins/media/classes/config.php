<?php
/**
* @version		$Id: config.php 48 2009-05-27 10:46:36Z happynoodleboy $
* @package      JCE
* @copyright    Copyright (C) 2005 - 2009 Ryan Demmer. All rights reserved.
* @author		Ryan Demmer
* @license      GNU/GPL
* JCE is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/
class MediaConfig 
{
	function getConfig(&$vars)
	{
		$jce 	=& JContentEditor::getInstance();
		$params = $jce->getPluginParams('media');
		
		if ($params->get('media_use_script', '0') == '1') {
			$vars['media_use_script']	= '1';
			$vars['code_javascript'] 	= '1';
			$jce->removeKeys($vars['invalid_elements'], array('script'));
		} else {
			$jce->removeKeys($vars['invalid_elements'], array('object', 'param', 'embed'));
		}
		$vars['media_version_flash'] 			= $jce->getParam($params, 'media_version_flash', '9,0,124,0', '9,0,124,0');
		$vars['media_version_shockwave'] 		= $jce->getParam($params, 'media_version_shockwave', '11,0,0,458', '11,0,0,458');
		$vars['media_version_windowsmedia'] 	= $jce->getParam($params, 'media_version_windowsmedia', '5,1,52,701', '5,1,52,701');
		$vars['media_version_quicktime'] 		= $jce->getParam($params, 'media_version_quicktime', '6,0,2,0', '6,0,2,0');
		$vars['media_version_reallpayer'] 		= $jce->getParam($params, 'media_version_reallpayer', '7,0,0,0', '7,0,0,0');
	}
}
?>