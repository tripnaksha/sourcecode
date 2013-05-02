<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

switch (JRequest::getCmd('task'))
{
	default:
		basicMap();
		break;
}

function basicMap()
{
//	echo "<link rel='stylesheet' type='text/css' href='" . $url . "media/system/css/modal.css' />";
	echo "<link rel='stylesheet' type='text/css' href='" . $url . "components/com_trailembed/css/map.css' />";
//	if (isset($_GET['tkey']) && $_GET['tkey'] != "")
//	  $tkey = $_GET['tkey'];
	$url = "http://www.tripnaksha.com/";
	$key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQkUZHNJdizg5ywABG1vcOZLnlKKRQiK1QyIYbC7QJYSAvZi_ftqMywEg';
	$document =& JFactory::getDocument();
	$document->addScript('http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $key);
	echo "<script type='text/javascript' src='" . $url . "components/com_trailembed/js/markermanager.js'></script>";
	echo "<script type='text/javascript' src='" . $url . "components/com_trailembed/js/xmlWriter.js'></script>";
//	echo("<script type='text/javascript' src='http://maps.google.com/maps?file=api&amp;v=2&amp;key='" . $key . "></script>");
	echo "<script type='text/javascript' src='" . $url . "components/com_trailembed/js/map.js'></script>";
	echo "<script type='text/javascript' src='" . $url . "media/system/js/mootools.js'></script>";
}
?>
<?php
		if (isset($_GET['tview']) && $_GET['tview'] != "")
		  $tview = $_GET['tview'];
		if (isset($_GET['theight']) && $_GET['theight'] != "")
		  $theight = $_GET['theight'];
		if (isset($_GET['twidth']) && $_GET['twidth'] != "")
		  $twidth = $_GET['twidth'];

		$js = "load(";
		$js .= '\'' . 'Guest' . '\'';
		$js .= ', ';
		$js .= "'0'";
		if ($tview)
		  $js .= ", " . $tview;
		else
		  $js .= ", " . '0';
		$js .= ");";

		$njs = '
		  window.addEvent(\'onunload\', \'GUnload()\');
		';

		echo ("<div id=\"map_canvas\" style=\"width: " . $twidth . "px; height: " . $theight . "px\"></div>");
//		echo ("<div id=\"map_canvas\" style=\"width: 100%; height: 400px\"></div>");
		echo "<script type='text/javascript'>" . $js . "</script>";
?>
