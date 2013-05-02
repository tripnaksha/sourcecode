<?php
// no direct access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_SITE.DS.'components'.DS.'com_savetrail'.DS.'phpencoder.php');

// Get instance of database object
$db =& JFactory::getDBO();

$mode	= $db->quote( $db->getEscaped($_POST['mode']));
$trailid= $db->quote( $db->getEscaped($_POST['trailid']));
$XML	= $db->quote( $db->getEscaped($_POST['XML']));
$start	= $db->quote( $db->getEscaped($_POST['start']));
$zoom	= $db->quote( $db->getEscaped($_POST['zoom']));
$center	= $db->quote( $db->getEscaped($_POST['center']));
$uid	= $db->quote( $db->getEscaped($_POST['uid']));
$uname	= $db->quote( $db->getEscaped($_POST['uname']));
$name	= $db->quote( $db->getEscaped($_POST['name']));
$intro	= $db->quote( $db->getEscaped($_POST['intro']));
$length	= $db->quote( $db->getEscaped($_POST['length']));
$private= $db->quote( $db->getEscaped($_POST['private']));
$nname	= $db->quote( $db->getEscaped($_POST['nickname']));
$nemail  = $db->quote( $db->getEscaped($_POST['nickemail']));
$detailXML  = $db->quote( $db->getEscaped($_POST['detailXML']));

$polymod = array ();
$polyline = array();
$string = str_replace("'<route><marker>(", "", $XML);
$string = str_replace(")</marker></route>'", "", $string);
$points = explode(")</marker><marker>(", $string);

for ( $i = 0; $i < count($points); $i++)
{
  $temp = explode (",",$points[$i]);
  $polymod[$i][0] = trim($temp[0]);
  $polymod[$i][1] = trim($temp[1]);
}
$polyline = dpEncode($polymod);

// Insert the trail details in the table
if (strpos($mode, "update"))
{
	$query = "UPDATE jos_trailList " .
	 " SET routeXML = " . $XML . "," .
	 " detailXML = " . $detailXML . "," .
	 " length = " . $length .
	 " WHERE jos_trailList.id = " . $trailid . ";";
}
else
{
	$query = "INSERT INTO jos_trailList (" .
		"name," .
		"routeXML," .
		"routeStart," .
		"mapCenter," .
		"zoomLevel," .
		"userId," .
		"intro," .
		"length," .
		"private," .
		"createTime," .
		"nname," .
		"nemail," .
		"detailXML," .
		"upload," .
		"encodeurl)" .
         "VALUES (" .
         $name . ", " .
         $XML . ", " .
         $start . ", " .
         $center . ", " .
         $zoom . ", " .
         $uid . ", " .
         $intro . ", " .
         $length . ", " .
         $private . ", " .
         "NOW() , " .
         $nname . ", " .
         $nemail . ", " .
//	 $detailXML . ");";
	 $detailXML . ", " .
	 "1, " .
	 $db->quote( $polyline[0]) . ");";
}

$result = mysql_query($query);

if (!$result) {
  die('Invalid query : ' . $query);
}

if ($mode == 'update')
{
   $lastid = $trailid;
   $delquery = "DELETE FROM jos_trailDetail WHERE id = " . $lastid . "; \n";
   mysql_query($delquery);
}
else
   $lastid = mysql_insert_id();

//$lastid = 1;
$resUrl = new DOMDocument();
$XML = "<?xml version=\"1.0\"?>" . substr($XML, 1, strlen ($XML) - 2);
$resUrl->loadXML($XML);

$webResults = $resUrl->getElementsByTagName('marker');

$query = "INSERT INTO jos_trailDetail (Trail_ID , Lat , Lng) VALUES ";

foreach($webResults as $value){
   $marker =  $value->childNodes->item($child1)->nodeValue;
   $pos = strpos ($marker, ',');
   $end = strlen ($marker) - $pos - 3;
   $lat = substr( $marker, 1, $pos - 1);
   $lng = substr( $marker, $pos + 2, $end);
   $query = $query . "\n(" . $lastid . ", " . $lat . ", " . $lng . "), ";
}

$result = mysql_query(substr ($query , 0, strlen ($query) - 2));

if (!$result) {
  $query2 = "DELETE FROM jos_trailDetail WHERE id = " . $lastid . "; \n";
  $query2 = $query2 . "DELETE FROM jos_trailList WHERE id = " . $lastid;
  mysql_query($query2);
  die('Invalid query : ' . $query);
}
else {
//  echo "Trail Saved." . $lastid;
  echo "Trail Saved." . $lastid.':'.trim($name,"\x22\x27");
}

?>