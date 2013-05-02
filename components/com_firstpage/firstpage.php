 <?php
// no direct access
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.mootools');

$path = JPATH_COMPONENT_SITE;
$comp = substr($path, strpos($path, 'components'));
$document =& JFactory::getDocument();
$document->addStyleSheet($comp . '/css/page.css');
$document->addScript($comp . '/js/page.js');

?>
<div id="feedcon">
   <div id="routefeed" class="latest">
        <h3>Recent Trails</h3>
        <? include ("latestlist.txt");?>
        <a href="#" id="example-show" class="showLink" onclick="showHide('routelist');return false;">See more.</a>
   </div>
   <div id="tripfeed" class="latest">
        <h3>Upcoming trips</h3>
        <? include ("latesttrips.txt");?>
        <a href="#" id="example-show" class="showLink" onclick="showHide('triplist');return false;">See more.</a>
   </div>
</div>
<div style="clear:both">&nbsp;</div>
<div class="plans-row">
	<div class="plan business leftmost" onclick="location.href='index.php?option=com_routes&view=map&Itemid=1'">
		<h3>1. Map your trips!</h3>
		<div class="child">
			<ul>
			  <li><p>Want to show your friends where you went on a trip recently?</p></li>
			  <li><p>Put the trip route up on an interactive map so they can see.</p></li>
			  <li><p>Embed this route map in your blog/website and share!</p></li>
			</ul>
		</div>
	</div>
	<div class="plan business middleleft" onclick="location.href='index.php?option=com_content&view=section&layout=blog&id=2&Itemid=4'">
		<h3>2. Write about them! </h3>
		<div class="child">
			<ul>
			  <li><p>Review the trip you went on.</p></li>
			  <li><p>Used specialized gear on a trek or ride and found it good? Let others know!</p></li>
			  <li><p>Submit tips and share your expertise tips with other trekking enthusiasts.</p></li><br>
			</ul>
		</div>
	</div>
	<div class="plan business middleright" onclick="location.href='index.php?option=com_eventlist&view=categoriesdetailed&Itemid=25'">
		<h3>3. Plan your next trip!</h3>
		<div class="child">
			<ul>
			  <li><p>Plot your route on the map or choose from over 500 routes and schedule a trip for it!</p></li>
			  <li><p>Invite your friends and others and get them to register for the trip.</p></li>
			  <li><p>What's more, embed this route along with the map in your blog!</p></li>
			</ul>
		</div>
	</div>
</div>
<div id="start">
<a href="<?php echo JRoute::_('index.php?option=com_routes&Itemid=1');?>" class="button classy business-plan">
   <span>Start now!</span>
</a>
</div>
<script type="text/javascript">ieload();</script>
