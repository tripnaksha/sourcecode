<?php
/**
 * @copyright	Copyright (C) Filz-and-more.de.
 * @license		Linkware
 * If you use this template or parts of it, please setup a backlink to www.filz-and-more.de
 * or leave the link in the footer-part untouched.
 * Otherwise you must buy a link-free version.
 * Please be fair - thanks. Juergen
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<jdoc:include type="head" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/template.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/general.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/ja-sosdmenu.css" type="text/css" />
<link rel="alternate" type="application/rss+xml"   title="Latest routes on tripnaksha.com"   href="http://feeds.feedburner.com/LatestRoutes" />
<link rel="alternate" type="application/rss+xml"   title="Upcoming trips on tripnaksha.com"   href="http://feeds.feedburner.com/UpcomingTrips" />
<link rel="alternate" type="application/rss+xml"   title="Latest reviews on tripnaksha.com"   href="http://feeds.feedburner.com/TripnakshaReviews" />
<link rel="alternate" type="application/rss+xml"   title="Recently added trips on tripnaksha.com"   href="http://feeds.feedburner.com/tripnakshatrips" />
<!--[if lte IE 6]>
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/ie6.css" type="text/css" />
<![endif]-->

<script type="text/javascript">
if (screen.width > 1280 || screen.height >= 800)
{
//	document.getElementById("himg").src="templates/<?php echo $this->template ?>/images/ht.png";
}
// Internet Explorer 6
var IE6 = false /*@cc_on || @_jscript_version < 5.7 @*/;
if (IE6 ) {
	window.onload = function(){
		if (document.all&&document.getElementById) {
			navParent = document.getElementById("ja-mainnav");
			navRoot = navParent.childNodes[0];

			for (i=0; i<navRoot.childNodes.length; i++) {
				node = navRoot.childNodes[i];
				if (node.nodeName=="LI") {
					node.onmouseover=function() {this.className+=" over"; }
					node.onmouseout=function() {this.className=this.className.replace(" over", "");}
				}
			}
		}
	}
}
//<!--Start Kampyle Exit-Popup Code-->
var k_push_vars = {
     "display_after": 30,
	"view_percentage": 30,
	"popup_font_color": "#000000",
	"popup_background": "#ffffff",
	"popup_separator": "#D4E2F0",
	"header": "Your feedback is important to us!",
	"question": "Would you be willing to give us a short (1 minute) feedback?",
	"footer": "Thank you for helping us improve our website",
	"remind": "Remind me later",
	"remind_font_color": "#3882C3",
	"yes": "Yes",
	"no": "No",
	"text_direction": "ltr",
	"images_dir": "http://cf.kampyle.com/",
	"yes_background": "#76AC78",
	"no_background": "#8D9B86",
	"site_code": 2407904
}
</script>
<!--End Kampyle Exit-Popup Code-->

<!-- Start Kampyle Css -->
<link rel="stylesheet" type="text/css" media="screen" href="http://cf.kampyle.com/k_button.css" />
<!-- End Kampyle Css -->
<link rel="stylesheet" href="templates/<?php echo $this->template ?>/css/template.css" type="text/css" />
<!--link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="templates/<?php echo $this->template; ?>/css/ja-sosdmenu.css" type="text/css" /-->


<meta name="verify-v1" content="1Un5z0g7VR6Dt6cqpqDb5cLtNv76zbqA0N61G/2o3jI=" />
</head>

<body>

<div id="main">
	<div class="header">
		<div class="title">
			<?php //echo $mosConfig_sitename; ?>
			<h1 ><a href="."><img id="himg" alt="Trip Naksha" height="115px" src="templates/<?php echo $this->template ?>/images/dreaming2.jpg" /></a></h1>
		</div>

		<div id="headertop">
			<jdoc:include type="modules" name="headertop" style="table" />
			<p><a href="http://www.tripnaksha.com/index.php?option=com_content&view=article&id=224" title="Subscribe to TripNaksha feeds"><img src="media/system/images/feed-icon16x16.png" alt="Tripnaksha feed" style="height:16px;width:16px;vertical-align:middle;border:0" alt="subscribe"/>Subscribe</a><!--a href="http://feeds.feedburner.com/TripnakshaReviews" rel="alternate" type="application/rss+xml"><img src="media/system/images/feed-icon16x16.png" alt="Tripnaksha feed" style="height:16px;width:16px;vertical-align:middle;border:0" alt="subscribe"/></a>&nbsp;<a href="http://feeds.feedburner.com/TripnakshaReviews" rel="alternate" type="application/rss+xml">Subscribe</a--></p>
		</div>
		<div style="clear:both"></div>
		<div id="headerbot">
			<jdoc:include type="modules" name="headerbot" style="clearfix" />
		</div>
	</div>
	<div class="navbar">
			<div id="ja-mainnavwrap">
				<div id="ja-mainnav">
				<jdoc:include type="modules" name="hornav" />
				</div>
				<div id="ja-mainnav-right">
				<jdoc:include type="modules" name="hornav-right" />
				</div>
			</div>
			<div id="navright">
			<table cellspacing="0" cellpadding="0" style="margin: 0 auto;">
				<tr><td>
				<!--jdoc:include type="modules" name="user3" style="table" /-->
				</td></tr>
			</table>
			</div>
	</div>

	<div class="mainColumn">
			<div id="leftColumn" class="column">
				<?php if($this->countModules('left')) : ?>
					<jdoc:include type="modules" name="left" style="rounded" />
				<?php endif; ?>
			</div>
			<div id="contentColumn" class="contentColumn">
					<?php if($this->countModules('right') and JRequest::getCmd('layout') != 'form') : ?>
					<div id="rightColumn" class="column">

							<jdoc:include type="modules" name="top" style="rounded" />


							<jdoc:include type="modules" name="right" style="rounded" />
					</div>
						<div id="inner_contentColumn" class="mc">
					<?php else: ?>
						<div id="inner_contentColumn_full" class="mc">
					<?php endif; ?>
						<div><div><div>
							<jdoc:include type="modules" name="breadcrumbs" style="rounded" />
							<div>
							<!--div id="trailheading"></div>
							<div id="trailtail" style="visibility:hidden; height:0px;"></div-->
							<?php if($this->countModules('user1 or user2')) : ?>
								<table class="nopad user1user2">
									<tr class="latest" valign="top">
										<?php if($this->countModules('user1')) : ?>
											<td>
                                            		<div class="latest">
                                                    	<jdoc:include type="modules" name="user1" style="rounded" />
                                                    </div>

											</td>
										<?php endif; ?>
										<?php if($this->countModules('user1 and user2')) : ?>
											<td class="greyline">&nbsp;</td>
										<?php endif; ?>
										<?php if($this->countModules('user2')) : ?>
											<td>
                                            	<div class="latest">
                                                    <jdoc:include type="modules" name="user2" style="rounded" />
                                                </div>
											</td>
										<?php endif; ?>
									</tr>
								</table>

								<div id="maindivider"></div>
							<?php endif; ?>

							<table class="nopad" id="nopad">
								<tr valign="top">
									<td>
										<jdoc:include type="component" />
									</td>
								</tr>
							</table>
							<!--div id="trailbot" style="visibility:hidden; height:0px;"==>
							</div>
						</div></div></div></div>

					</div>
			</div>
			<div style="clear:both"></div>
	</div>
</div>
<div id="footer">
	<!--p align="right"><small ><a href="http://www.filz-and-more.de" title="Filz and More - Schmuck und Filz Onlineshop" style="color:#fff;">Template von Filz und Trend-Onlineshop</a></small></p-->
			<div id="links">
				<div style="text-align: center; padding: 24px 0 0;">
					<div >
					|&nbsp;<a class="footer123" href="index.php?option=com_content&view=article&id=164&Itemid=21" target="_blank">Privacy</a> &nbsp;|&nbsp;
					<a class="footer123" href="index.php?option=com_content&view=article&id=165&Itemid=21" target="_blank">Terms</a>&nbsp;|&nbsp;
					<a class="footer123" href="index.php?option=com_content&view=article&id=166&Itemid=21" target="_blank">Contact</a>&nbsp;|
					<a class="footer123" href="http://blog.tripnaksha.com" target="_blank">Blog</a>&nbsp;|&nbsp;
					<a class="footer123" href="http://www.facebook.com/TripNaksha" target="_blank">Facebook</a>&nbsp;|&nbsp;
					<a class="footer123" href="http://twitter.com/tripnaksha" target="_blank">Twitter</a>&nbsp;|&nbsp;
					<!--a class="footer123" href="index.php?option=com_content&view=article&id=168&Itemid=21">Coming Soon&nbsp;|&nbsp;</a-->
					<a class="footer123" href="index.php?option=com_content&view=article&id=163&Itemid=21">FAAQs</a>&nbsp;|&nbsp;
					</div>
				</div>
				<div style="text-align: center; padding: 24px 0 0;">
					<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/"><img alt="Creative Commons License" style="height:31px;border-width:0" src="media/system/images/88x31.png" /></a>
				</div>
			</div>
	<div style="color:#698F48;"><center><jdoc:include type="modules" name="footer" style="xhtml"/></center></div>
</div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-4899076-4");
pageTracker._trackPageview();
} catch(err) {}</script>

<!--Start Kampyle Feedback Form Button--><div id="k_close_button" class="k_float kc_bottom kc_right"></div><div><a href="http://www.kampyle.com/solutions/website-feedback-form/"  target="kampyleWindow" id="kampylink" class="k_float k_bottom_sl k_right" onclick="javascript:k_button.open_ff('site_code=2407904&amp;form_id=30713&lang=en');return false;">
<img src="./templates/daydream/images/en-orange-corner-low-right.png" alt="Website Feedback" border="0"/></a></div><!--div id="k_slogan" class="k_float k_bottom k_right"><a href="http://www.kampyle.com/" target="_blank">Feedback</a> Analytics</div-->
<script src="http://cf.kampyle.com/k_button.js" type="text/javascript"></script>
<script type="text/javascript" src="http://cf.kampyle.com/k_push.js"></script><!--End Kampyle Feedback Form Button-->
<!--End Kampyle Feedback Form Button-->
</script>
</body>
</html>
