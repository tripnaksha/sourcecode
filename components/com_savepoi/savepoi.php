<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

// Get instance of database object
$db =& JFactory::getDBO();

$point	= $db->quote( $db->getEscaped($_POST['point']));
$lat	= $db->quote( $db->getEscaped($_POST['lat']));
$lng	= $db->quote( $db->getEscaped($_POST['lng']));
$userid = $db->quote( $db->getEscaped($_POST['userid']));
$label	= $db->quote( $db->getEscaped($_POST['label']));
$link	= $db->quote( $db->getEscaped($_POST['link']));
$email	= $db->quote( $db->getEscaped($_POST['email']));
$desc	= $db->quote( $db->getEscaped($_POST['desc']));

// Insert the trail details in the table

$query = "INSERT INTO jos_pointInfo (\n" .
		"point,\n" .
		"lat,\n" .
		"lng,\n" .
		"userid,\n" .
		"label,\n" .
		"link,\n" .
		"descr,\n" .
		"email)\n" .
         " VALUES (\n" .
         $point . ", \n" .
         $lat . ", \n" .
         $lng . ", \n" .
         $userid . ", \n" .
         $label . ", \n" .
         $link . ", \n" .
         $desc . ", \n" .
         $email . "\n);";
$result = mysql_query($query);

if (!$result) {
  die('Invalid query : ' . $query);
}
else {
  $lastid = mysql_insert_id();
  echo "Trail Saved." . $lastid;
}
?>