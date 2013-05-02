<?php
require_once(JPATH_COMPONENT.DS.'assets/geoplugin.class.php');
$document =& JFactory::getDocument();
$geoplugin = new geoPlugin();
$geoplugin->locate();

$titletext =  str_replace(' ','-',$this->trailname).'-route';
$titletext = str_replace('---','-',$titletext);
$document->setTitle($titletext);
if (strlen($this->description)>0)
   $document->setDescription(substr($this->description, 0, 200));
else
   $document->setDescription($titletext);
// No direct access
 
defined('_JEXEC') or die('Restricted access'); ?>
<h1><div id="trailheading"><? echo $this->trailname; ?></div></h1>
<!--div id="upload" style="color: red; text-align: right; font-size: '14px';width: 750px;"><a rel="{handler: 'iframe', size: {x: 400, y: 320}}" onclick="SqueezeBox.fromElement(this); return false;" href="index.php?option=com_login_box&login_only=1&clickbtn=fup">Upload KML</a></div-->
<div id="map_container">
	<div id="map_canvas" style="width: 450px; height: 400px;"></div>
	<div id="map_addon" style="width: 275px; height: 400px; visibility: visible; ">
	    <div class="panelbox">
	    	Embed to your blog/website:
	    	<br />
		<div class="panellink">
			<div class="leftlink">
			<a title="Copy the HTML code in the box and paste to your website" href="javascript:document.getElementById(&quot;embedurl&quot;).focus();document.getElementById(&quot;embedurl&quot;).select();" class="rightpanel"><b>Map</b></a>
			</div>
			<div class="rightlink">
			<input name="embedurl" id="embedurl" class="embedurl" type="text" value="&lt;iframe width=&quot;475&quot; height=&quot;475&quot; frameborder=&quot;0&quot; scrolling=&quot;no&quot; marginheight=&quot;0&quot; marginwidth=&quot;0&quot; src=&quot;http://www.tripnaksha.com/index.php?option=com_trailembed&amp;tview=<? echo $this->trailid; ?>&amp;trailname=<? echo $this->trailname; ?>&amp;tmpl=component&amp;theight=475&amp;twidth=475&quot;&gt;&lt;/iframe&gt;&lt;br /&gt;" onclick="javascript:document.getElementById(&quot;embedurl&quot;).focus();document.getElementById(&quot;embedurl&quot;).select();">
			</div>
		</div>
		<br />
		<div class="panellink">
			<div class="leftlink">
			<a title="Copy the HTML code in the box and paste to your website" href="javascript:document.getElementById(&quot;imageurl&quot;).focus();document.getElementById(&quot;imageurl&quot;).select();" class="rightpanel"><b>Image</b></a>
			</div>
			<div class="rightlink">
			<input name="imageurl" id="imageurl" class="embedurl" type="text" value="&lt;img src=&quot;http://maps.google.com/maps/api/staticmap?size=475x475&amp;path=weight:3|color:red|enc:<? echo $this->encodeurl; ?>&amp;sensor=false&amp;maptype=hybrid&quot; alt=&quot;<? echo $this->trailname; ?>&quot;/&gt;" onclick="javascript:document.getElementById(&quot;imageurl&quot;).focus();document.getElementById(&quot;imageurl&quot;).select();">
			</div>
			<br>
		</div>
                <div class="panellink">
                        <div class="leftlink">
                        <a title="Share this route on your social networks" class="rightpanel"><b>Share</b></a>
                        </div>
                        <div class="rightlink">
                        <a href="http://twitter.com/share" style="margin-left:15px" class="twitter-share-button" data-count="none" data-via="tripnaksha">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
                        <iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo "http://".urlencode($_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);?>&layout=button_count&show_faces=false&width=80&action=like&font=verdana&colorscheme=light" scrolling="no" frameborder="0" style="margin-left:15px; margin-bottom:-2px; border:none; overflow:hidden; width:80px; height:22px" allowTransparency="true"></iframe>
                        </div>
                </div>
	    </div>
	    <div class="panelbox">
		<div><? echo $this->html; ?>
		</div>
	    </div>
	    <div id="pictures" class="panelbox"></div>
	    Powered by <img src="https://www.google.com/uds/css/small-logo.png" class="gsc-branding-img-noclear">
	</div>
</div>
<div id="map_description">
	<h2>Trip Story:</h2>
	<p>Route created by: <? if ($this->username) echo mysql_real_escape_string($this->username); else echo mysql_real_escape_string($this->nname);?>
	<br />
	Length of route: <? echo $this->traillength; ?>km</p>
	<p><? 
	      $order   = array("\r\n", "\n", "\r");
	      $replace = '<br />';
	      echo '<strong>'.str_replace($order, $replace, $this->description).'</strong><br /><hr>';
	   ?>
	</p>
</div>
<? echo '<script type="text/javascript">'. $this->js . '</script>'; ?>
<div id="disqus_thread"></div>
<script type="text/javascript">
  /**
    * var disqus_identifier; [Optional but recommended: Define a unique identifier (e.g. post id or slug) for this thread] 
    */
  (function() {
   var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
   dsq.src = 'http://tripnaksha.disqus.com/embed.js';
   (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
  })();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript=tripnaksha">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>

