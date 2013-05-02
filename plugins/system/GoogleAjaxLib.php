<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );


class plgSystemGoogleAjaxLib extends JPlugin
{


	function plgSystemGoogleAjaxLib( &$subject, $params )
	{
		parent::__construct( $subject, $params );
	}




	function onAfterRender( )
	{
		$done=JRequest :: getVar('GoogleAjaxLib');

		if($done){
			return "";
		}
		$body = JResponse::getBody();
		$scriptRegex="/<script [^>]+(\/>|><\/script>)/i";
		$body=preg_replace_callback($scriptRegex,array( &$this, '_replaceJs'), $body);
		JResponse::setBody($body);

		JRequest :: setVar('GoogleAjaxLib','1');
		return '';
	}

	function _replaceJs($matches){

		$replaceMootools=$this->param('ReplaceMootools',1);
		$mootoolsVersion=$this->param('MootoolsVersion');
		$mootoolsName=$this->param('MootoolsName','mootools.js');
			
		$replaceJQuery=$this->param('ReplaceJQuery',0);
		$JQueryVersion=$this->param('JQueryVersion','1.3.2');
		$jqueryName=$this->param('JQueryName','jquery.js');
			
		$replacePrototype=$this->param('ReplacePrototype',0);
		$prototypeVersion=$this->param('PrototypeVersion','1.6.0.3');
		$prototypeName = $this->param('PrototypeName','prototype.js');

		$replaceYUI=$this->param('ReplaceYUI',0);
		$yuiVersion=$this->param('YUIVersion','2.7.0');
		$yuiName = $this->param('YUIName','yuiloader.js');

	    $replaceSWF=$this->param('ReplaceSWFObject',0);
		$swfVersion=$this->param('SWFObjectVersion','2.2');
		$swfName = $this->param('SWFObjectName','swfobject.js');
		
		$scriptStart='<script type="text/javascript" src="';
		$scriptEnd='"></script>';
		//print_r($matches);

			
		if($replaceMootools){			
			if(strpos($matches[0],$mootoolsName)!==false){
				$googleMootools='http://ajax.googleapis.com/ajax/libs/mootools/'.$mootoolsVersion.'/mootools-yui-compressed.js';
			    return $scriptStart.$googleMootools.$scriptEnd;
			}
		}

		if($replaceJQuery){
			if(strpos($matches[0],$jqueryName)!==false){
				$googleJquery='http://ajax.googleapis.com/ajax/libs/jquery/'.$JQueryVersion.'/jquery.min.js';
				return $scriptStart.$googleJquery.$scriptEnd;
			}
		}
		if($replacePrototype){
			if(strpos($matches[0],$prototypeName)!==false){
				$googlePrototype='http://ajax.googleapis.com/ajax/libs/prototype/'.$prototypeVersion.'/prototype.js';
				return $scriptStart.$googlePrototype.$scriptEnd;
			}
		}

		if($replaceYUI){
			if(strpos($matches[0],$yuiName)!==false){
				$googleYUI='http://ajax.googleapis.com/ajax/libs/yui/'.$yuiVersion.'/build/yuiloader/yuiloader.js';
				return $scriptStart.$googleYUI.$scriptEnd;
			}
		}
		if($replaceSWF){
			if(strpos($matches[0],$swfName)!==false){
				$googleSWF='http://ajax.googleapis.com/ajax/libs/swfobject/'.$swfVersion.'/swfobject.js';
				return $scriptStart.$googleSWF.$scriptEnd;
			}
		}
		return $matches[0];

	}

	function param($name,$default=NULL){
		static $plugin,$pluginParams;
		if (!isset( $plugin )){
			$plugin =& JPluginHelper::getPlugin('system', 'GoogleAjaxLib');
			$pluginParams = new JParameter( $plugin->params );
		}
		return $pluginParams->get($name,$default);
	}
}
