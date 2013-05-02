<?php
require_once(JPATH_COMPONENT.DS.'assets/geoplugin.class.php');
$geoplugin = new geoPlugin();
$geoplugin->locate();
 
// No direct access
 
defined('_JEXEC') or die('Restricted access'); ?>
<div id="upload" style="color: red; text-align: right; font-size: '14px';width: 750px;"><a rel="{handler: 'iframe', size: {x: 400, y: 400}}" onclick="SqueezeBox.fromElement(this); return false;" href="index.php?option=com_login_box&login_only=1&clickbtn=fup">Upload KML</a></div>
<div id="map_container" style="position: absolute; height: auto;">
	<div id="map_canvas" style="width: 450px; height: 600px;"></div>
	<div id="map_addon" style="width: 0px; height: 600px; visibility: hidden;"></div>
</div>
<? echo '<script type="text/javascript">'. $this->js . '</script>'; ?>

