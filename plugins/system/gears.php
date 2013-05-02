<?php
/**
* @copyright	Copyright (C) 2008 Blue Flame IT (Jersey) Ltd. All rights reserved.
* @license		GNU/GPL v2,
* @link 		http://www.phil-taylor.com
* @author 		Phil Taylor <me@phil-taylor.com> 
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgSystemGears extends JPlugin
{
	function plgSystemGears(& $subject, $config) {
		parent::__construct($subject, $config);
	}

	function onAfterInitialise()
	{
		global $mainframe;
		$user = JFactory::getUser();

		// No gears for frontend
		if (!$mainframe->isAdmin() || !$user->get('id')) {
			return;
		}

		$live_site_with_administrator = JURI::base();
		$live_site = str_replace('administrator/','',$live_site_with_administrator);
		
		/* @var $doc JDocumentHTML */
		$doc =& JFactory::getDocument();
		$doc->addStyleSheet($live_site.'plugins/system/GoogleGears/gears.css');
		$doc->addScript($live_site.'plugins/system/GoogleGears/gears_init.js');
		$doc->addScript($live_site.'plugins/system/GoogleGears/joomlaGears.js');
		$doc->addScriptDeclaration('
		/* <![CDATA[ */
				JGearsL10n = {
					updateCompleted: "Update completed.",
					error: "Error:"
				}
		/* ]]> */
		');
		
		
	}
	
	function onAfterRender(){
		$app =& JFactory::getApplication();
				/* Not frontend */
		if($app->getName() == 'site') {
			return true;
		}
				//Replace src links
	  	$base   = JURI::base(true).'/';
		$buffer = JResponse::getBody();
	    ob_start();
			
		?> 
				<div id="gears-info-box" class="info-box" style="display:none;">
				<img src="../plugins/system/GoogleGears/gear.png" title="Gear" alt="" class="gears-img" />
				<div id="gears-msg1">
				<h3 class="info-box-title">Speed up Joomla</h3>
				<p>Joomla now has support for Gears, which adds new features to your web browser.<br />
				<a href="http://gears.google.com/" target="_blank" style="font-weight:normal;">More information...</a></p>
			
				<p>After you install and enable Gears most of Joomla&#8217;s images, scripts, and CSS files will be stored locally on your computer. This speeds up page load time.</p>
				<p><strong>Don&#8217;t install on a public or shared computer.</strong></p>	<div class="submit"><button onclick="window.location = 'http://gears.google.com/?action=install&amp;return=http%3A%2F%2Fwww.phil-taylor.com%2F';" class="button">Install Now</button>
				<button class="button" style="margin-left:10px;" onclick="document.getElementById('gears-info-box').style.display='none';">Cancel</button></div>
				</div>
			
				<div id="gears-msg2" style="display:none;">
				<h3 class="info-box-title">Gears Status</h3>
				<p>Gears is installed on this computer but is not enabled for use with Joomla.</p> 
				<p>To enable it, make sure this web site is not on the denied list in Gears Settings under your browser Tools menu, then click the button below.</p>
				<p><strong>However if this is a public or shared computer, Gears should not be enabled.</strong></p>
				<div class="submit"><button class="button" onclick="JGears.getPermission();">Enable Gears</button>
				<button class="button" style="margin-left:10px;" onclick="document.getElementById('gears-info-box').style.display='none';">Cancel</button></div>
			
				</div>
			
				<div id="gears-msg3" style="display:none;">
				<h3 class="info-box-title">Gears Status</h3>
				<p>Gears is installed and enabled on this computer. You can disable it from your browser Tools menu.</p>
				<p>If there are any errors, try disabling Gears, then reload the page and enable it again.</p>
				<p>Local storage status: <span id="gears-wait"><span style="color:#f00;">Please wait! Updating files:</span> <span id="gears-upd-number"></span></span></p>
				<div class="submit"><button class="button" onclick="document.getElementById('gears-info-box').style.display='none';">Close</button></div>
				</div>
				</div>
		<?php 
			
		$contents = ob_get_contents();
		ob_end_clean();
			
		JResponse::setBody(str_replace('</body>',$contents.'</body>',$buffer));
		return true;
	}
}
?>