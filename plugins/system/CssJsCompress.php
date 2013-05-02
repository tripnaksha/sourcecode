<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
require_once ( dirname(__FILE__).DS.'CssJsCompress'.DS.'js_merge_php4.php' );

/**
 * Joomla! Css and JS aggregation and compression Plugin
 *
 * @author		Joe <joomlatags@gmail.com>
 * @package		JoomlaTag
 * @subpackage	System
 */
class  plgSystemCssJsCompress extends JPlugin
{

	function plgSystemCssJsCompress(& $subject, $config)
	{
		parent::__construct($subject, $config);

	}

	function onAfterRender()
	{

		$isDebug=false;
		$css=$this->param('css');
		$javascript=$this->param('javascript');
		if(!$css&&!$javascript){
			return true;
		}
		// Only render for the front site
	 $app =& JFactory::getApplication();
	 if($app->getName() != 'site') {
	 	return true;
	 }		$document	=& JFactory::getDocument();		$doctype	= $document->getType();		// Only render for HTML output		if ( $doctype != 'html' ) { return; }	 //excludeComponents
		$excludeComponents=$this->param('excludeComponents');		if(isset($excludeComponents)&&$excludeComponents){			$components=@explode(',',$excludeComponents);			$option=JRequest::getVar('option');			if(isset($option)&&in_array($option,$components)){				return true;			}		}
		//gzip checking
	 $isGZ=$this->param('gzip');
	 if($isGZ){
	 	$encoding = JResponse::_clientEncoding();
			if (!$encoding){
				$isGZ=false;
			}			if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
				$isGZ=false;
			}
	 }
	 $body = JResponse::getBody();	 $isok=true;	 $compressor=new jsCssCompressor();	 $baseUrl=JURI::base(true).'/';	 if($javascript){	 	$scriptRegex="/<script [^>]+(\/>|><\/script>)/i";	 	$jsRegex="/([^\"\'=]+\.(js))[\"\']/i";	 	preg_match_all($scriptRegex, $body, $matches);	 	$scripts=@implode('',$matches[0]);	 	preg_match_all($jsRegex,$scripts,$matches);	 	$scriptFiles= array();	 	foreach($matches[1] as $script){	 		if(isInternal($script)){	 			//if $baseurl=='/'; may need specail handle	 			$parts=@explode( JURI::base(),$script);	 			if(count($parts)>1&&endwith($parts[1],'.js')){	 				$script=JPATH_ROOT.DS.$parts[1];	 				$script=replaceSperator($script);	 				$scriptFiles[]=$script;	 			}else if(endwith($script,'.js')){	 						 				$script=$_SERVER['DOCUMENT_ROOT'].DS.$script;	 				$script=replaceSperator($script);	 				if(file_exists($script)){	 					$scriptFiles[]=$script;	 				}else{	 					$script=JPATH_ROOT.DS.$script;	 					$script=replaceSperator($script);	 					if(file_exists($script)){	 						$scriptFiles[]=$script;	 					}	 				}	 			}	 		}	 	}	 	$scriptFiles=array_unique($scriptFiles);	 	$customOrder=$this->param('customOrder');	 	$jqueryNoConflict=$this->param('jqueryNoConflict');	 	$jquery=$this->param('jquery');	 	$exclude=$this->param('excludeJs');	 	$excludeJs=array();	 	if(isset($exclude)&&$exclude){	 		$excludeJs=@explode(',',$exclude);	 	}	 	$predefinedExcludeJs=array('xajax.js','script.js','tiny_mce.js','com_community/assets/toolbar.js');	 	$excludeJs=@array_merge($excludeJs,$predefinedExcludeJs);	 	$mappedScripts=array();	 	foreach($scriptFiles as $sf){	 		$file=strrchr($sf,DS);	 		if(isset($file)){	 			$file=substr($file,1);	 			$mappedScripts[trim($file)]=$sf;	 		}	 	}	 	$firstScripts=explode(',',$customOrder);	 	$orderedScripts=array();	 	foreach($firstScripts as $fs){	 		$fs=trim($fs);	 		if(array_key_exists($fs,$mappedScripts)){	 			$orderedScripts[]=$mappedScripts[$fs];	 		}	 	}	 	//append all js, ignore exluded files and customorded files	 	foreach($scriptFiles as $sf){	 		if(!in_array($sf,$orderedScripts)){	 			$shouldIgnore=false;	 			if(isset($excludeJs)&&count($excludeJs)){	 				foreach($excludeJs as $exd){	 					if($exd&&endwith($sf,$exd)){	 						$shouldIgnore=true;	 						break;	 					}	 				}	 			}	 			if(!$shouldIgnore){	 				$orderedScripts[]=$sf;	 			}	 		}	 	}	 	if(isset($jquery)&&isset($mappedScripts[$jquery])){	 		$jquery=$mappedScripts[$jquery];	 	}	 	$scriptFiles=$orderedScripts;	 	if(!empty($scriptFiles)){	 		// print_r($orderedScripts);	 		$singleJsFileName=md5(JURI::base().@implode('',$scriptFiles)).'.js';	 		if($isGZ){	 			$singleJsFileName.='.gz';	 		}	 		$jsBaseDir=JPATH_CACHE.DS.'js';	 		if(!file_exists($jsBaseDir)){	 			mkdir($jsBaseDir);	 			file_put_contents($jsBaseDir.DS.'index.html',$this->indexContent());	 		}	 		$jsSingle=$jsBaseDir.DS.$singleJsFileName;	 		if($isDebug||!file_exists($jsSingle)){	 			$isok=$compressor->makeJsOld($baseUrl,JPATH_ROOT,$scriptFiles, $jsSingle,$isGZ,$jqueryNoConflict,$jquery);	 		}	 		if($isok){	 			replaceJs(NULL,$excludeJs);	 			$body=preg_replace_callback($scriptRegex,"replaceJs",$body);	 			$newImportJs='</title><script type="text/javascript" src="'. $baseUrl.'plugins/system/CssJsCompress/js.php?js='.$singleJsFileName.'"></script>';	 			//only match once	 			$body = preg_replace('/<\/title>/i',$newImportJs , $body,1);	 			JResponse::setBody($body);	 		}	 	}//end if($javascript)	 }	 if($css){	 	$predefinedExcludeCss=array('template_ie7.css','ie.css','ie6.css',	 	'ie7.css','ie8.css','ie6_css.css','ie7_css.css','ie6.php','default-ie6.php',	 	'cb_superthumb/style.css','ieonly.css','ie7only.css','print.css',	 	'dashboard.IE.css','style.IE6.css','style.IE7.css');	 	//handle conditional css and javascript files	 	$conditionRegex="/<\!--\[if.*?\[endif\]-->/is";	 	preg_match_all($conditionRegex,$body,$conditonMatches);	 	$linksRegex="|<link[^>]+[/]?>((.*)</[^>]+>)?|U";	 	if(!empty($conditonMatches)){	 		preg_match_all($linksRegex,@implode('',$conditonMatches[0]),$conditionCss);	 		if(!empty($conditionCss[0])){	 			$cssRegex="/([^\"\'=]+\.(css))[\"\']/i";	 			preg_match_all($cssRegex,@implode('',$conditionCss[0]),$conditionCssFiles);	 			if(!empty($conditionCssFiles[1])){	 				foreach($conditionCssFiles[1] as $conditionalCss){	 					$conditionalCss=fileName($conditionalCss);	 					$predefinedExcludeCss[]=trim($conditionalCss);	 				}	 				//print_r($predefinedExcludeCss);	 			}	 		}	 	}	 	//end of conditional css and javascript files

	 	$cssRegex="/([^\"\'=]+\.(css))[\"\']/i";

	 	preg_match_all($linksRegex, $body, $matches);

	 	$links=@implode('',$matches[0]);

	 	preg_match_all($cssRegex,$links,$matches);


	 	$cssLinks= array();
	 	//$uri =& JURI::getInstance();

	 	foreach($matches[1] as $link){
	 		if(isInternal($link)){
	 			$parts=@explode( JURI::base(),$link);

	 			if(count($parts)>1&&strpos($parts[1],'.css')){
	 				$link=JPATH_ROOT.DS.$parts[1];
	 				$link=replaceSperator($link);
	 				$cssLinks[]=$link;
	 			}else if(strpos($link,'.css')){

	 				$link=$_SERVER['DOCUMENT_ROOT'].DS.$link;
	 				$link=replaceSperator($link);	 				if(file_exists($link)){
	 					$cssLinks[]=$link;	 				}else{	 					$link=JPATH_ROOT.DS.$link;	 					$link=replaceSperator($link);	 					if(file_exists($link)){	 						$cssLinks[]=$link;	 					}	 				}
	 			}
	 		}
	 	}
	 	$cssLinks=array_unique($cssLinks);
	 	// print_r($cssLinks);

	 	$excludeCss=array();
	 	$exclude=$this->param('excludeCss');
	 	if(isset($exclude)&& $exclude){
	 		$excludeCss=@explode(',',$exclude);	 	}	 	$excludeCss=@array_merge($excludeCss,$predefinedExcludeCss);	 	$orderedCss = array();
	 	foreach($cssLinks as $css){
	 		$shouldIgnore=false;
	 		foreach($excludeCss as $exd){
	 			if(endwith($css,$exd)){
	 				$shouldIgnore=true;
	 				break;
	 			}
	 		}
	 		if(!$shouldIgnore){
	 			$orderedCss[]=$css;
	 		}
	 	}
	 	$cssLinks=$orderedCss;

	 	//print_r($cssLinks);	 	if(!empty($cssLinks)){
	 		$singlecssFileName=md5(JURI::base().@implode('',$cssLinks)).'.css';
	 		if($isGZ){
	 			$singlecssFileName.='.gz';
	 		}
	 		$cssBaseDir=JPATH_CACHE.DS.'css';
	 		if(!file_exists($cssBaseDir)){
	 			mkdir($cssBaseDir);
	 			file_put_contents($cssBaseDir.DS.'index.html',$this->indexContent());
	 		}

	 		$cssSingle=$cssBaseDir.DS.$singlecssFileName;

	 		//print_r(JURI::base().'</br>');
	 		// print_r(JPATH_ROOT);
	 		if($isDebug||!file_exists($cssSingle)){
	 			//$isok=$compressor->makeCssOld($baseUrl,JPATH_ROOT,$cssLinks, $cssSingle,$isGZ);	 			$isok=joomla_build_css_cache($baseUrl,JPATH_ROOT,$cssLinks, $cssSingle,$isGZ);
	 		}


	 		if($isok){
	 			replaceCss(NULL,$excludeCss);
	 			$body=preg_replace_callback($linksRegex,'replaceCss',$body);
	 			$newImportCss='</title><link  rel="stylesheet" type="text/css" href="'. $baseUrl .'plugins/system/CssJsCompress/css.php?css='.$singlecssFileName.'"/>';
	 			$body = preg_replace('/<\/title>/i',$newImportCss , $body,1);
	 			JResponse::setBody($body);
	 		}	 	}
	 }//if($css)

	 //done css
	  


	 //done js
	}



	function param($name){
		static $plugin,$pluginParams;
		if (!isset( $plugin )){
			$plugin =& JPluginHelper::getPlugin('system', 'CssJsCompress');
			$pluginParams = new JParameter( $plugin->params );
		}
		return $pluginParams->get($name);
	}

	function indexContent(){
		return "<html><body bgcolor='#FFFFFF'></body></html>";
	}


}
//end class

function isInternal($url) {
	$uri =& JURI::getInstance($url);
	$base = $uri->toString(array('scheme', 'host', 'port', 'path'));
	$host = $uri->toString(array('scheme', 'host', 'port'));
	if(stripos($base, JURI::base()) !== 0 && !empty($host)) {
		return false;
	}
	return true;
}

//end class
function replaceCss($matches,$exclude = NULL){

	static $_exclude;
	// Store exclude css for preg_replace_callback.
	if (isset($exclude)) {
		$_exclude = $exclude;
	}else if(isset($_exclude)){
		$cssRegex="/([^\"\'=]+\.(css))[\"\']/i";
		preg_match_all($cssRegex, $matches[0], $m);
		if(isset($m[1])&&count($m[1])){
			$cssFile=$m[1][0];
			if(count($_exclude)){
				foreach($_exclude as $exd){
					if($exd&&endwith($cssFile, $exd)){
						return $matches[0];
					}
				}
			}
			$ignore= count($m[0])&&endwith( $cssFile,'.css')&&!endwith( $cssFile,'.css.php')&&isInternal( $cssFile);
			if($ignore){
				return ' ';
			} else{
				return $matches[0];
			}
		}else{
			return $matches[0];
		}
	}

}

function replaceJs($matches,$exclude = NULL){
	static $_exclude;
	// Store exclude javascripts for preg_replace_callback.
	if (isset($exclude)) {
		$_exclude = $exclude;
	}else if(isset($_exclude)){
		$jsRegex="/src=[\"\']([^\"\']+)[\"\']/i";
		preg_match_all($jsRegex, $matches[0], $m);
		if(isset($m[1])&&count($m[1])){
			$scriptFile=$m[1][0];
			if(count($_exclude)){
				foreach($_exclude as $exd){
					if($exd&&endwith($scriptFile, $exd)){
						return $matches[0];
					}
				}
			}

			$ignore= count($m[0])&&endwith( $scriptFile,'.js')&&!endwith( $scriptFile,'.js.php')&&isInternal( $scriptFile);
			if($ignore){
				return ' ';
			} else{
				return $matches[0];
			}
		}else{
			return $matches[0];
		}
	}
}

function endwith($FullStr, $EndStr)  {
	$StrLen = strlen($EndStr);
	$FullStrEnd = substr($FullStr, strlen($FullStr) - $StrLen);
	if($FullStrEnd == $EndStr){
		return true;
	}
	return false;
}

function fileName($whole){
	$file=strrchr($whole,'/');
	$file=substr($file,1);
	if(isset($file)){
		return trim($file);
	}else{
		return $whole;
	}
}

function replaceSperator($link){
	$link=str_replace("\\\\",DS,$link);
	$link=str_replace("/\\/",DS,$link);
	$link=str_replace("\\/\\",DS,$link);
	$link=str_replace("\\",DS,$link);
	$link=str_replace("//",DS,$link);
	$link=str_replace("/",DS,$link);
	return $link;
}
