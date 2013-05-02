<?php
class JoomlaTagsHelper{
	function param($name,$default=''){
		static $params;
		if (!isset( $params )){
			$params = JComponentHelper::getParams('com_tag');
		}

		return $params->get($name,$default);
	}
	function tag_alphasort($tag1, $tag2)
	{
		if($tag1->name == $tag2->name)
		{
			return 0;
		}
		return ($tag1->name < $tag2->name) ? -1 : 1;
	}
	function tag_popularasort($tag1, $tag2)
	{
		if($tag1->ct == $tag2->ct)
		{
			return 0;
		}
		return ($tag1->ct < $tag2->ct) ? -1 : 1;
	}
	function tag_latestasort($tag1, $tag2)
	{
		if($tag1->created == $tag2->created)
		{
			return 0;
		}
		return ($tag1->created < $tag2->created) ? -1 : 1;
	}
	function hitsasort($tag1, $tag2)
	{
		if($tag1->hits == $tag2->hits)
		{
			return 0;
		}
		return ($tag1->hits < $tag2->hits) ? -1 : 1;
	}
	function getComponentVersion()
	{
		static $version;

		if( !isset($version) ) {
			$xml = JFactory::getXMLParser('Simple');

			$xmlFile = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tag'.DS.'manifest.xml';

			if( file_exists($xmlFile) ) {
				if( $xml->loadFile($xmlFile) ) {
					$root =& $xml->document;
					$element =& $root->getElementByPath('version');
					$version = $element ? $element->data() : '';
				}
			}
		}

		return $version;
	}
	function preHandle($tag){
		$tag=JString::trim($tag);
		$toLowcase=JoomlaTagsHelper::param('lowcase',1);
		if($toLowcase){
			$tag=JString::strtolower($tag);
		}
		return $tag;
	}

	function ucwords($word){
		if(JoomlaTagsHelper::param('capitalize')){
			return JString::ucwords($word);
		}else{
			return $word;
		}
	}
}
?>