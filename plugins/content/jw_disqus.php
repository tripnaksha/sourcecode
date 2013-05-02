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

$mainframe->registerEvent( 'onPrepareContent', 'jwDisqus' );

function jwDisqus(&$row,&$params){

    // JoomlaWorks reference parameters
    $plg_name               = "jw_disqus";
    $plg_copyrights_start   = "\n\n<!-- JoomlaWorks \"Disqus Comment System for Joomla!\" Plugin (v2.1) starts here -->\n";
    $plg_copyrights_end     = "\n<!-- JoomlaWorks \"Disqus Comment System for Joomla!\" Plugin (v2.1) ends here -->\n\n";
    
		// API
    global $mainframe;
		$document =& JFactory::getDocument();
		$db 			=& JFactory::getDBO();
		$user 		=& JFactory::getUser();
		$aid 			= $user->get('aid');
		
		// Assign paths
    $sitePath = JPATH_SITE;
    $siteUrl  = substr(JURI::base(), 0, -1);
    
    // Check if plugin is enabled
    if(JPluginHelper::isEnabled('content',$plg_name)==false) return;
        
    
    
		// ----------------------------------- Get plugin parameters -----------------------------------
		$plugin =& JPluginHelper::getPlugin('content', $plg_name);
		$pluginParams = new JParameter( $plugin->params );

		$selectedCategories			= $pluginParams->get('selectedCategories','');
		$selectedMenus					= $pluginParams->get('selectedMenus','');
		$disqusSubDomain				= str_replace('.disqus.com','',$pluginParams->get('disqusSubDomain','disqusforjoomla'));
		$disqusListingCounter		= $pluginParams->get('disqusListingCounter',1);
		$disqusArticleCounter		= $pluginParams->get('disqusArticleCounter',1);
		$disqusDevMode					= $pluginParams->get('disqusDevMode',0);
		$debugMode							= $pluginParams->get('debugMode',0);
		if($debugMode==0) error_reporting(0); // Turn off all error reporting
		
		// External parameter for controlling plugin layout within modules
		if(!$params) $params = new JParameter(null);
		$parsedInModule = $params->get('parsedInModule');
		
		
		
		// ----------------------------------- Before plugin render -----------------------------------

		// Simple check before parsing the plugin
		if(!$row->id) return;
		
		// Requests
		$option 		= JRequest::getCmd('option');
		$view 			= JRequest::getCmd('view');
		$layout 		= JRequest::getCmd('layout');
		$page 			= JRequest::getCmd('page');
		$secid 			= JRequest::getInt('secid');
		$catid 			= JRequest::getInt('catid');
		$itemid 		= JRequest::getInt('Itemid');
		if(!$itemid) $itemid = 999999;
		
		// Get the current category
		if(is_null($row->catslug)){
			$currectCategory = 0;
		} else {
			$currectCategory = explode(":",$row->catslug);
			$currectCategory = $currectCategory[0];	
		}

		// Define plugin category restrictions
		if (is_array($selectedCategories)){
			$categories = $selectedCategories;
		} elseif ($selectedCategories==''){
			$categories[] = $currectCategory;
		} else {
			$categories[] = $selectedCategories;
		}
		
		// Define plugin menu restrictions
		if (is_array($selectedMenus)){
			$menus = $selectedMenus;
		} elseif (is_string($selectedMenus) && $selectedMenus!=''){
			$menus[] = $selectedMenus;
		} elseif ($selectedMenus==''){
			$menus[] = $itemid;
		}


		
		// ----------------------------------- Prepare elements -----------------------------------
		
		// Includes
		require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
		require_once(dirname(__FILE__).DS.$plg_name.DS.'includes'.DS.'helper.php');
		
		// Output object
		$output = new JObject;

		// Article URLs (raw, browser, system)
		$itemURLraw = $siteUrl.'/index.php?option=com_content&view=article&id='.$row->id;
		
		$websiteURL = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['HTTP_HOST'] : "http://".$_SERVER['HTTP_HOST'];
		$itemURLbrowser = $websiteURL.$_SERVER['REQUEST_URI'];
		$itemURLbrowser = explode("#",$itemURLbrowser);
		$itemURLbrowser = $itemURLbrowser[0];
		
		if ($row->access <= $user->get('aid', 0)){
			$itemURL = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catslug, $row->sectionid));
		} else {
			$itemURL = JRoute::_("index.php?option=com_user&task=register");
		}
		
		// Article URL assignments
		$output->itemURL 					= $websiteURL.$itemURL;
		$output->itemURLrelative 	= $itemURL;
		$output->itemURLbrowser		= $itemURLbrowser;
		$output->itemURLraw				= $itemURLraw;

		// Fetch elements specific to the "article" view only
		if( in_array($currectCategory,$categories) && in_array($itemid,$menus) && $option=='com_content' && $view=='article'){
		
			// Comments (article page)
			$output->comments = '
			<div id="disqus_thread"></div>
			<script type="text/javascript">
				//<![CDATA[
			';
			if($disqusSubDomain=='disqusforjoomla' || $disqusDevMode){
				$output->comments .= '
					var disqus_developer = "1";
				';
			}
			$output->comments .= '
					var disqus_url= "'.$output->itemURL.'";
					var disqus_identifier = "'.substr(md5($disqusSubDomain),0,10).'_id'.$row->id.'";
				//]]>
			</script>
			<script type="text/javascript" src="http://disqus.com/forums/'.$disqusSubDomain.'/embed.js"></script>
			<noscript>
				<a href="http://'.$disqusSubDomain.'.disqus.com/?url=ref">'.JText::_("View the discussion thread.").'</a>
			</noscript>
			<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
			';

		} // End fetch elements specific to the "article" view only



		// ----------------------------------- Head tag includes -----------------------------------
		$output->includes = "
		{$plg_copyrights_start}
		<script type=\"text/javascript\">
			//<![CDATA[
			
			window.addEvent('domready', function(){
				// Smooth Scroll
				new SmoothScroll({
					duration: 500
				});
		    
				// Disqus Counter
				var links = document.getElementsByTagName('a');
				var query = '?';
				for(var i = 0; i < links.length; i++) {
					if(links[i].href.indexOf('#disqus_thread') >= 0) query += 'url' + i + '=' + encodeURIComponent(links[i].href) + '&';
				}
				var disqusScript = document.createElement('script');
				disqusScript.setAttribute('charset','utf-8');
				disqusScript.setAttribute('type','text/javascript');
				disqusScript.setAttribute('src','http://disqus.com/forums/{$disqusSubDomain}/get_num_replies.js' + query + '');
				var b = document.getElementsByTagName('body')[0];
				b.appendChild(disqusScript);
			});
			
			// Disqus CSS
			var disqus_iframe_css = \"".JWHelper::getTemplatePath($plg_name,'css/disqus.css')->http."\";
			
			//]]>
		</script>
		<style type=\"text/css\" media=\"all\">
			@import \"".JWHelper::getTemplatePath($plg_name,'css/template.css')->http."\";
		</style>
		{$plg_copyrights_end}
		";
		
		
		
		// ----------------------------------- Render the output -----------------------------------		
		if( in_array($currectCategory,$categories) && in_array($itemid,$menus) ){
		
				// Load the plugin language file the proper way
				if($mainframe->isAdmin()){
					JPlugin::loadLanguage( 'plg_content_'.$plg_name );
				} else {
					JPlugin::loadLanguage( 'plg_content_'.$plg_name, 'administrator' );
				}
				
				if( ($option=='com_content' && $view=='article') && $parsedInModule!=1){

					// Output head includes
					JHTML::_('behavior.mootools');
					JWHelper::loadHeadIncludes($output->includes);
					
					// Fetch the template
					ob_start();
					include(JWHelper::getTemplatePath($plg_name,'article.php')->file);
					$getArticleTemplate = $plg_copyrights_start.ob_get_contents().$plg_copyrights_end;
					ob_end_clean();
	
					// Output
					$row->text = $getArticleTemplate;
					
				} else if( $disqusListingCounter && (($option=='com_content' && ($view=='frontpage' || $view=='section' || $view=='category')) || $parsedInModule==1) ){
				
					// Output head includes
					JHTML::_('behavior.mootools');
					JWHelper::loadHeadIncludes($output->includes);
				
					// Fetch the template
					ob_start();
					include(JWHelper::getTemplatePath($plg_name,'listing.php')->file);
					$getListingTemplate = $plg_copyrights_start.ob_get_contents().$plg_copyrights_end;
					ob_end_clean();
						
					// Output
					$row->text = $getListingTemplate;
									
				}
				
		} // END IF
		  
} // END FUNCTION
