 <?php
// no direct access
defined('_JEXEC') or die('Restricted access');

// Get request ID from query string variable
$southlng = JRequest::getVar( 'southLng', null, 'POST' );
$southltd = JRequest::getVar( 'southLtd', null, 'POST' );
$northlng = JRequest::getVar( 'northLng', null, 'POST' );
$northltd = JRequest::getVar( 'northLtd', null, 'POST' );
$userid = JRequest::getInt( 'uid' );

// Get instance of database object
$db =& JFactory::getDBO();

		if ($userid == 0)
		{
			$part1 = "";
		}
		else
		{
			$part1 = "SELECT DISTINCT b.id, b.name, b.nname, b.routeXML, b.detailXML, b.routeStart, b.mapCenter, \n" .
				" b.zoomLevel, b.userId,b.length, DATE_FORMAT(b.createtime,'%d %b %y') as ttime, c.name as uname, b.upload, b.encodeurl, d.descr" .
				" FROM jos_trailDetail AS a, jos_trailList AS b, jos_users AS c, jos_trailAddlInfo d\n" .
				" WHERE a.Lng >= " . $southlng .
				" AND a.Lng <= " . $northlng .
				" AND a.Lat >= " . $southltd .
				" AND a.Lat <= " . $northltd .
				" AND a.Trail_ID = b.id" .
				" AND b.Private = 1" .
				" AND b.userId = " . $userid .
				" AND c.id = b.userId" .
				" AND d.trail_id = b.id" .
				" UNION ALL ";
		}

		$part2 = "SELECT DISTINCT b.id, b.name, b.nname, b.nemail, b.routeXML, b.detailXML, b.routeStart, b.mapCenter, \n" .
			" b.zoomLevel, b.userId, b.length, DATE_FORMAT(b.createtime,'%d %b %y') as ttime, c.name as uname, b.upload, b.encodeurl, d.descr"  .
			" FROM jos_trailDetail AS a, jos_trailAddlInfo d, jos_trailList AS b LEFT JOIN jos_users c \n" .
			" ON b.userId = c.id" .
			" WHERE a.Lng >= " . $southlng .
			" AND a.Lng <= " . $northlng .
			" AND a.Lat >= " . $southltd .
			" AND a.Lat <= " . $northltd .
			" AND a.Trail_ID = b.id" .
			" AND d.trail_id = b.id" .
			" AND b.Private = 0";
		$query = $part1 . $part2;
//echo $query;
$result = mysql_query($query);
$i = 0;

if(!$result){
	die(mysql_error());
}
// In the case of 'search by i.d' there should be only 1 iteration here.
while($row = mysql_fetch_assoc($result))
{
	$route [$i][id] = $row['id'];
	$route [$i][name] = $row['name'];
	$route [$i][nname] = $row['nname'];
	$route [$i][nemail] = $row['nemail'];
	$route [$i][start] = $row['routeStart'];
	$route [$i][xml] = $row['routeXML'];
	$route [$i][detailxml] = $row['detailXML'];
	$route [$i][center] = $row['mapCenter'];
	$route [$i][zoom] = $row['zoomLevel'];
	$route [$i][uid] = $row['userId'];
	$route [$i][intro] = $row['descr'];
	$route [$i][length] = $row['length'];
	$route [$i][createtime] = $row['ttime'];
	$route [$i][uname] = $row['uname'];
	$route [$i][upload] = $row['upload'];
	$route [$i][encodeurl] = $row['encodeurl'];

	$i += 1;
}

// Send as JSON object instead of XML to avoid routeXML playing havoc.
echo json_encode($route);
//echo $route;
//echo $query;
//echo $searchmode;
?>