<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
JHTML::_('behavior.mootools');
require_once('geoplugin.class.php');
$geoplugin = new geoPlugin();
// If we wanted to change the base currency, we would uncomment the following line
// $geoplugin->currency = 'EUR';
$geoplugin->locate();

switch (JRequest::getCmd('task'))
{
	default:
		basicMap();
		break;
}
 
function basicMap()
{
	$url = JFactory::getURI()->toString();
	if (strpos($url, ".com") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQkUZHNJdizg5ywABG1vcOZLnlKKRQiK1QyIYbC7QJYSAvZi_ftqMywEg';
	}
	else if (strpos($url, ".in") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBTGk7r6UUG2tv7pCXD49pEILMut2BSK1KyluFXiSlHDmPfgxKEcQu31zA';
	}
	else if (strpos($url, ".net") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQVUtw86IlLILJmmHf_nHtc38TT4xQEwo2T9X3LZpg2rnfZGcOvR7jrgA';
	}
	else if (strpos($url, ".org") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBRfh_bXew_S5fvS_f8On4C7bLoFWxSxwel7vDUce97Zyj7kn9hhDqcEhQ';
	}
	else if (strpos($url, "staging") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBS-qS4eptdPHG1UjzLnJ6fGUkWm4RSMdUj6_csNpypmF6YY5VjHYm5jdQ';
	}
	
	$document =& JFactory::getDocument();
	if (! (JFactory::getUser()->get('id')==0 && strrpos ($url, 'Itemid') && strrpos ($url, 'Itemid=41')))
	{
		$document->addScript('http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $key);
		$document->addScript(JURI::base() . 'components/com_traildisplay/js/map.js');
		$document->addScript(JURI::base() . 'components/com_traildisplay/js/allpack.js');
//		$document->addScript($baseurl.'components/com_traildisplay/js/markerlib_pack.js');
//		$document->addScript($baseurl.'components/com_traildisplay/js/epoly.js');
	}
		$document->addScript(JURI::base() . 'media/system/js/modal.js');
		$document->addStyleSheet(JURI::base() . 'media/system/css/modal.css');
	$document->addStyleSheet(JURI::base() . 'components/com_traildisplay/css/map.css');
}
?>
<!--to install firebug lite-->
<!--script type='text/javascript' src='http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js'></script-->
<?php

	$document =& JFactory::getDocument();
	$tview = JRequest::getInt( 'tview' );
	$tname = JRequest::getVar( 'trailname' );
	$baseurl = JURI::base();
	
	if ($tview)
	{
		require_once(JPATH_SITE.DS.'components'.DS.'com_showalltrails'.DS.'rating/rating.php');
		$document->addStyleSheet($baseurl.'components/com_showalltrails/rating/rating.css');
		$document->addStyleSheet($baseurl.'components/com_showalltrails/rating/showall.css');
		$document->addScript($baseurl."modules/mod_ajaxsearch/js/jquery.js");
//		$document->addScript($baseurl.'components/com_showalltrails/rating/rating.js');
		$document->addScript($baseurl.'components/com_showalltrails/showall.js');

		$html = '';
		$html = "<br /><a id=\"fav$tview\" class=\"imga\" href=\"javascript:void(0)\" title=\"Favorite it!\" onclick=\"javascript:favit('".JFactory::getUser()->get('id')."',".$tview.");\"><img id=\"favimg$tview\" width=25px height=25px src=\"".$baseurl."components/com_showalltrails/favs.png\"/></a>&nbsp;<br /><a href=\"javascript:favit('".JFactory::getUser()->get('id')."',".$tview.");\">Mark as favorite</a>";
		
		$html = $html . "<div id='ratingmsg".$tview."'>&nbsp;</div>";
	        $html = $html . "<br /><div class='panellink'><a class='rightpanel' href='index.php?option=com_searchtrailreviews&rid=$tview&searchName=$tname' title='Read reviews'>Read reviews</a></div><br />";
	        $html = $html . "<div class='panellink'><a class='rightpanel' href='index.php?option=com_eventlist&view=editevent&Itemid=23&tsk=$tview&name=$tname' title='Plan a trip'>Plan a trip</a></div><br />";
//	        $html = $html . "<div class='panellink'><a class='rightpanel' href='#' title='View profile of'>View 's profile</a></div><br />";
	        $html = $html . "<div class='panellink'><a class='rightpanel' href='javascript:document.getElementById(\"embedurl\").focus();document.getElementById(\"embedurl\").select();' title='Copy the HTML code below and paste to your website'>Copy trail <b>map</b> to your website</a><br />";
	        $html = $html . "abcdefg<div class='panellink'><img onclick=\"STTAFFUNC.cw(this, {id:'2009022410967', link: window.location, title: document.title });\" onmouseover=\"STTAFFUNC.showHoverMap(this, '2009022410967', window.location, document.title)\" onmouseout=\"STTAFFUNC.hideHoverMap(this)\" src=\"http://www.tripnaksha.com/modules/mod_taf/images/tafbuttontxt_green24.png\" alt=\"Tell a Friend about this Trail\"/></div><br />";
        }

	if (JFactory::getUser()->get('id')==0 && strrpos (JFactory::getURI()->toString(), 'Itemid') && strrpos (JFactory::getURI()->toString(), 'Itemid=41'))
	{
		printf("You need to either %sLogin%s (you can use Gmail i.d and password too!) or %sRegister%s for this..","<a href='".htmlspecialchars('index.php?option=com_login_box&login_only=1')."' onclick=\"SqueezeBox.fromElement(this); return false;\" rel=\"{handler: 'iframe', size: {x: 400, y: 320}}\"> ","</a>","<a href='".htmlspecialchars('index.php?option=com_login_box&register_only=1')."' onclick=\"SqueezeBox.fromElement(this); return false;\" rel=\"{handler: 'iframe', size: {x: 400, y: 390}}\"> ","</a>") ;
		printf("<script type='text/javascript'>SqueezeBox.fromElement('index.php?option=com_login_box&login_only=1', {handler: 'iframe', size: {x: 400, y: 320}});</script>");
	}
	else
	{
		$js = "load(";
		if (JFactory::getUser()->get('username'))
		  $js .= '\'' . JFactory::getUser()->get('username') . '\'';
		else
		  $js .= '\'' . 'Guest' . '\'';
		$js .= ', ';
		if (JFactory::getUser()->get('id'))
		  $js .= '\'' . JFactory::getUser()->get('id') . '\'';
		else
		  $js .= "'0'";
		if ($tview)
		  $js .= ", " . $tview;
		else
		  $js .= ", " . '\'0\'';
		$js .= ",'".$geoplugin->city."');";

		$njs = '
		  window.addEvent(\'onunload\', \'GUnload()\');
		';

		$document =& JFactory::getDocument();
		$document->addScriptDeclaration($njs);

//		if(JFactory::getUser()->get('id') == 0)
		   echo ("<div id=\"upload\" style=\"color: red; text-align: right; font-size: '14px';width: 750px;\"><a rel=\"{handler: 'iframe', size: {x: 400, y: 320}}\" onclick=\"SqueezeBox.fromElement(this); return false;\" href=\"index.php?option=com_login_box&login_only=1&clickbtn=fup\">Upload KML or KMZ files</a></div>");
//		else
//		   echo ("<div id=\"upload\" style=\"text-align: right; font-size: '14px';width: 750px;\"><a rel=\"{handler: 'iframe', size: {x: 400, y: 320}}\" onclick=\"SqueezeBox.fromElement(this); return false;\" href=\"index.php?option=com_content&view=article&id=207&tmpl=component\">Upload KML</a></div>");

		echo ("<div id=\"map_container\" style=\"position: absolute;\">");
		echo ("<div id=\"map_canvas\" style=\"width: 750px; height: 600px; position: relative; float: left;\"></div>");
		echo ("<div id=\"map_addon\" style=\"width: 0px; height: 600px; position: relative; float: left; visibility: hidden;\">");
		if ($tview)
		{
			JPluginHelper::importPlugin( 'trails' );
			$dispatcher =& JDispatcher::getInstance();
			if ($tview)
			   $results = $dispatcher->trigger( 'onDisplayTrails');
			echo $results[0] . '<br />';
		}
		echo ($html."</div></div>");
//		echo ("<div id=\"map_footer\" style=\"width: 750px; height: 100px; clear:both; position: relative;\"></div>");
//		echo ("<div id=\"map_altmap\" style=\"width: 0px; height: 0px; clear: both; visibility: hidden;\"></div>");
//		echo ("<div id=\"map_canvas\" style=\"width: 750px; height: 600px\"></div>");
		echo "<script type='text/javascript'>" . $js . "</script>";
	}
?>
