<?php
/*
// JoomlaWorks "Disqus Comment System" Plugin for Joomla! 1.5.x - Version 2.1
// Copyright (c) 2006 - 2009 JoomlaWorks Ltd.
// Released under the GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
// More info at http://www.joomlaworks.gr
// Designed and developed by the JoomlaWorks team
// ***Last update: May 30th, 2009***
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JWHelper {

	// Load Includes
	function loadHeadIncludes($headIncludes){
		global $loadPluginIncludes;
		$document = & JFactory::getDocument();
		if(!$loadPluginIncludes){
			$loadPluginIncludes=1;
			$document->addCustomTag($headIncludes);
		}
	}
	
	// Load Module Position
	function loadModulePosition( $position, $style='' ){
		$document	= &JFactory::getDocument();
		$renderer	= $document->loadRenderer('module');
		$params		= array('style'=>$style);
	
		$contents = '';
		foreach (JModuleHelper::getModules($position) as $mod)  {
			$contents .= $renderer->render($mod, $params);
		}
		return $contents;
	}
		
	// Path overrides
	function getTemplatePath($pluginName,$file){
		global $mainframe;
		$p = new JObject;
		if(file_exists(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.$pluginName.DS.str_replace('/',DS,$file))){
			$p->file = JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.$pluginName.DS.$file;
			$p->http = JURI::base()."templates/".$mainframe->getTemplate()."/html/{$pluginName}/{$file}";
		} else {
			$p->file = JPATH_SITE.DS.'plugins'.DS.'content'.DS.$pluginName.DS.'tmpl'.DS.$file;
			$p->http = JURI::base()."plugins/content/{$pluginName}/tmpl/{$file}";
		}
		return $p;
	}

} // end class
