 <?php
// no direct access
defined('_JEXEC') or die('Restricted access');

// Get request ID from query string variable
$searchmode = JRequest::getVar( 'searchMode', null, 'POST' );
$trailname = JRequest::getVar( 'searchName', null, 'POST' );
$trailid = JRequest::getInt( 'rid' );
$userid = JRequest::getInt( 'uid' );

// Get instance of database object
$db =& JFactory::getDBO();

//echo $searchmode . $trailname . $trailid. $userid;
// Currently using the first, third and the fourth modes
switch ($searchmode)
{
	case "name":
	{
		//If logged in, then have to search for user's private trails.
		if ($userid != 0)
		{
		   $part1 = " SELECT a.id, a.name, a.nname, a.nemail, a.routeXML, a.routeStart, a.mapCenter, \n" .
		   	    " a.zoomLevel, a.userId, a.intro, a.length, DATE_FORMAT(a.createtime,'%d %b %y') as ttime, d.name as uname\n" .
		   	    " FROM jos_trailList AS a, jos_users d \n" .
		   	    " WHERE a.userId = " . $userid . "\n" .
		   	    " AND a.name LIKE " . $db->Quote( '%'. $db->getEscaped( $trailname, true ) .'%', false ) . "\n" .
		   	    " AND a.private = 1 \n" .
		   	    " AND a.userId = d.id \n" .
		   	    " UNION ALL \n";
		}
		//If the user is not logged in, then no need to search for private trails stored by user.
		else
		{
		   $part1 = "";
		}

		//Show all public trails to both logged in and guest users.
		$part2 = " SELECT b.id, b.name, b.nname, b.nemail, b.routeXML, b.routeStart, b.mapCenter, \n" .
			 " b.zoomLevel, b.userId, b.intro, b.length, DATE_FORMAT(b.createtime,'%d %b %y') as ttime, c.name as uname\n" .
			 " FROM jos_trailList AS b LEFT JOIN jos_users c \n" .
			 " ON b.userId = c.id \n" .
			 " WHERE b.private = 0 \n" .
			 " AND b.name LIKE " . $db->Quote( '%'. $db->getEscaped( $trailname, true ) .'%', false ) . "\n";

		$query = $part1 . $part2;
		break;
	}
	case "tid":
	{
		//Show trail matching the trail id sent.
		$query = " SELECT a.id, a.name, a.nname, a.nemail, a.routeXML, a.routeStart, a.mapCenter, \n" .
			" a.zoomLevel, a.userId, a.intro, a.length, DATE_FORMAT(a.createtime,'%d %b %y') as ttime, d.name as uname\n" .
			" FROM jos_trailList AS a LEFT JOIN jos_users d \n" .
			" ON a.userId = d.id \n" .
			" WHERE a.id = " . $trailid . "\n";
		break;
	}
	case "min":
	{
		//Show logged in user's private and public trails.
		$query = " SELECT a.id, a.name, a.nname, a.nemail, a.routeXML, a.routeStart, a.mapCenter, \n" .
			" a.zoomLevel, a.userId, a.intro, a.length, DATE_FORMAT(a.createtime,'%d %b %y') as ttime, d.name as uname\n" .
			" FROM jos_trailList AS a, jos_users d \n" .
			" WHERE a.userId = " . $userid . "\n" .
			" AND a.userId = d.id \n";
		break;
	}
	case "all":
	{
		//Search for trails with specific name.
		$query = " SELECT a.id, a.name, a.nname, a.nemail, a.routeXML, a.routeStart, a.mapCenter, \n" .
			" a.zoomLevel, a.userId, a.intro, a.length, DATE_FORMAT(a.createtime,'%d %b %y') as ttime, 'uname'\n" .
			" FROM jos_trailList AS a \n" .
			" WHERE a.name = " . $db->Quote( $db->getEscaped( $trailname, true ), false ) . "\n";
		break;
	}
}
//die ($query);

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
	$route [$i][center] = $row['mapCenter'];
	$route [$i][zoom] = $row['zoomLevel'];
	$route [$i][uid] = $row['userId'];
	$route [$i][intro] = $row['intro'];
	$route [$i][length] = $row['length'];
	$route [$i][createtime] = $row['ttime'];
	$route [$i][uname] = $row['uname'];

	$i += 1;
}

// Send as JSON object instead of XML to avoid routeXML playing havoc.
echo json_encode($route);
//echo $route;
//echo $query;
//echo $searchmode;
?>