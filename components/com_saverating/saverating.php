<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

// Get instance of database object
$db =& JFactory::getDBO();

$uid	= JRequest::getInt( 'uid' );
$trailid	= JRequest::getInt( 'trailid' );
$rating	= JRequest::getInt( 'rating' );
$mode	= JRequest::getVar( 'mode' );

	$db->setQuery('SELECT * FROM #__trail_rating WHERE trail_id='. $trailid . ' AND uid=' . $uid);
	$vote = $db->loadObject();

if ($mode === "rating")
{
//die ("rating");
	if($vote)
	{
		$query = "UPDATE #__trail_rating \n" .
						"SET rating = " . $rating . ", \n" .
						"createtime = NOW()" . " \n" .
						"WHERE trail_id=" . (int)$trailid . " \n" .
						"AND uid=" . $uid . ";";
	}
	else
	{
		$query = "INSERT INTO #__trail_rating \n" .
						"(rating, trail_id, uid) \n" .
						"VALUES (" . $rating . ", " . $trailid . ", " . $uid . "); "; 
	}
}
else if ($mode === "fav")
{
//	$db->setQuery('SELECT * FROM #__trail_rating WHERE trail_id='. $trailid . ' AND uid=' . $uid);
//	$vote = $db->loadObject();
if ($vote->fav)
{
  if ($vote->fav == 0)
    $rating = 1;
}
else
  $rating = 1;
  if($vote)
  {
	$query = "UPDATE #__trail_rating \n" .
		"SET fav = " . $rating . ", \n" .
		"createtime = NOW()" . " \n" .
		"WHERE trail_id=" . (int)$trailid . " \n" .
		"AND uid=" . $uid . ";";
  }
  else
  {
	$query = "INSERT INTO #__trail_rating \n" .
		"(fav, trail_id, uid) \n" .
		"VALUES (" . $rating . ", " . $trailid . ", " . $uid . "); "; 
  }
}

// Insert the trail details in the table
$db->setQuery($query);
$result = $db->query();

if (!$result) {
die (mysql_error());
  die('Invalid query : ' . $query);
}
else {
  if ($mode === "rating")
    echo "Saverate";
  else
    echo "Savefav".$rating;
}
?>