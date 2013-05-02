<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

// Get instance of database object
$db =& JFactory::getDBO();

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

// Insert the trail details in the table

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
		"nemail)" .
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
	 $nemail . ");";

$result = mysql_query($query);

if (!$result) {
  die('Invalid query : ' . $query);
}

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
  echo "Trail Saved." . $lastid;
}

?>