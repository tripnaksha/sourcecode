 <?php
// no direct access
defined('_JEXEC') or die('Restricted access');

	// Get instance of database object
	$db =& JFactory::getDBO();
	// Get request ID from query string variable
	$southlng = JRequest::getVar( 'southLng', null, 'POST' );
	$southltd = JRequest::getVar( 'southLtd', null, 'POST' );
	$northlng = JRequest::getVar( 'northLng', null, 'POST' );
	$northltd = JRequest::getVar( 'northLtd', null, 'POST' );
	$userid = JRequest::getInt( 'uid' );
	$mode = JRequest::getInt( 'mode' );
	$point = JRequest::getVar( 'point');
//echo $point;
	if (strlen($point) > 0)
	{
	   $var = explode (",", str_replace (")", "", str_replace ("(", "", $point)));
	   $lat = trim($var[0]);
	   $lng = trim($var[1]);
	}

	if ($mode == 0)
	{
		$query = "SELECT b.id, b.name, a.label, a.link, a.email, a.point, a.lat, a.lng, a.descr \n" .
			" FROM	jos_pointInfo AS a LEFT JOIN jos_users b \n"  .
			" ON a.userId = b.id \n" .
			" WHERE a.Lng >= " . $southlng .  "\n" .
			" AND a.Lng <= " . $northlng .  "\n" .
			" AND a.Lat >= " . $southltd .  "\n" .
			" AND a.Lat <= " . $northltd .  "\n" .
			" ORDER BY a.point";
	}
	else if ($mode == 2)
	{
		$query = "SELECT distinct b.id, b.name, a.label, a.link, a.email, a.point, a.lat, a.lng, a.descr \n" .
			" FROM	jos_pointInfo AS a LEFT JOIN jos_users b \n"  .
			" ON a.userId = b.id \n" .
			" WHERE round(a.lat, 5) = " . $db->quote( round($lat, 5)) . "\n" .
			" AND round(a.lng, 5) = " . $db->quote( round($lng, 5));
	}

	if ($mode != 1)
	{
	   $result = mysql_query($query);
	   if(!$result){
	   	die(mysql_error());
	   }
	   $i = 0;

	   if ($mode == 2)
	      	$insert = "<div id='infowrap'>";

	   // In the case of 'search by i.d' there should be only 1 iteration here.
	   while($row = mysql_fetch_assoc($result))
	   {
	   	if ($mode == 0)
		{
			$route [$i][id] = $row['id'];
			$route [$i][name] = $row['name'];
			$route [$i][label] = $row['label'];
			$route [$i][link] = $row['link'];
			$route [$i][point] = $row['point'];
			$route [$i][lat] = $row['lat'];
			$route [$i][lng] = $row['lng'];

			$i += 1;
		}
		if ($mode == 2)
		{
			$insert = $insert . "<div class='leftcol'><b>" . $row['label'] . "</b></div>";
			if (trim(strlen($row['name'])) > 0)
			   $insert = $insert . "<div class='rightcol'>" . $row['name'] . "</div>";
			else
			{
			   $arr = explode("@", $row['email']);
			   $email = substr($arr[0], 0 , strlen($arr[0]-3));
			   $insert = $insert . "<div class='rightcol'>" . $email . "</div>";
			}
			$insert = $insert . "<div class='labelwrap'>".$row['descr']."</div>";
		}
	   }
	   if ($mode == 2)
	        $insert = $insert . "<div class=\"footer\">Click on the 'Add' tab above to add info about this waypoint!</div></div>";
	}

	if ($mode == 0)
	{
		// Send as JSON object instead of XML to avoid routeXML playing havoc.
		echo json_encode($route);
	}
	else if ($mode == 1)
	{
		$html = "<div id=\"all\" class=\"tabs\">";
		$html = $html . " <div class=\"tabs_header\">";
		$html = $html . " <div id=\"tab0\" class=\"tab\"><p class=\"contents\">Info</p></div>";
		$html = $html . " <div id=\"tab1\" class=\"tab\"><p class=\"contents\">Add</p></div>";
		$html = $html . " </div>";
		$html = $html . " <div class=\"tab_contents\">";
		$html = $html . " <div id=\"tab0_content\">";
		$html = $html . " <div class=\"title\">Waypoint Info</div>";
		$html = $html . " <div id='infowrap'><p>";
		$html = $html . " You're almost done! <br />Add a little more info about this waypoint";
		$html = $html . " so that others can benefit. Is it a landmark? A viewpoint? A nice";
		$html = $html . " hotel you'd recommend?<br />";
		$html = $html . " <div class=\"footer\">Click on the 'Add' tab above and write about it!</div>";
		$html = $html . " </p>";
		$html = $html . " </div></div>";
		$html = $html . " <div id=\"tab1_content\">";
		$html = $html . " <div class=\"title\">Add Waypoint Info</div>";
		$html = $html . " <p>";
		$html = $html . " <div>Comment *&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <input type='text' id='poilabel' size=10 maxlength=40 name='poilabel' value='' /></div>";
//		$html = $html . " <div>URL (pictures,etc) - &nbsp;<input type='text' id='poilink' size=10 maxlength=80 name='poilink' value='' />&nbsp;</div>";
		$html = $html . " <div>Description &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;<input type='textarea' id='poidesc' rows=4 cols=20 name='poidesc' value='' />&nbsp;</div>";
		if ($userid == 0)
		   $html = $html . " <div>Your email i.d * &nbsp;&nbsp;&nbsp;&nbsp;- <input type='text' id='poiemail' size=10 maxlength=80 name='poilink' value='' />&nbsp;</div>";
		$html = $html . " <div><input value='Save' type='button' onclick=\"poiSave(" . $point . "," . $lat . "','" . $lng . ");\" /></div>";
		$html = $html . " <div id='message'>&nbsp;</div>";
		$html = $html . " </p>";
		$html = $html . " </div>";
		$html = $html . " </div>";
		$html = $html . " </div>";
		echo $html;
	}
	else if ($mode == 2)
	{
		$html = "<div id=\"all\" class=\"tabs\">";
		$html = $html . " <div class=\"tabs_header\">";
		$html = $html . " <div id=\"tab0\" class=\"tab\"><p class=\"contents\">Info</p></div>";
		$html = $html . " <div id=\"tab1\" class=\"tab\"><p class=\"contents\">Add</p></div>";
		$html = $html . " </div>";
		$html = $html . " <div class=\"tab_contents\">";
		$html = $html . " <div id=\"tab0_content\">";
		$html = $html . " <div class=\"title\">Waypoint Info</div>";
		$html = $html . $insert;
		$html = $html . " </div>";
		$html = $html . " <div id=\"tab1_content\">";
		$html = $html . " <div class=\"title\">Add Waypoint Info</div>";
		$html = $html . " <p>";
		$html = $html . " <div>Comment *&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <input type='text' id='poilabel' size=10 maxlength=40 name='poilabel' value='' /></div>";
//		$html = $html . " <div>URL (pictures,etc) - &nbsp;<input type='text' id='poilink' size=10 maxlength=80 name='poilink' value='' />&nbsp;</div>";
		$html = $html . " <div>Description &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;<input type='textarea' id='poidesc' rows=4 cols=20 name='poidesc' value='' />&nbsp;</div>";
		if ($userid == 0)
		   $html = $html . " <div>Your email i.d * &nbsp;&nbsp;&nbsp;&nbsp;- <input type='text' id='poiemail' size=10 maxlength=80 name='poilink' value='' />&nbsp;</div>";
		$html = $html . " <div><input value='Save' type='button' onclick=\"poiSave(" . $db->quote($point) . "," . $db->quote($lat) . "," . $db->quote($lng) . ");\" /></div>";
		$html = $html . " <div id='message'>&nbsp;</div>";
		$html = $html . " </p>";
		$html = $html . " </div>";
		$html = $html . " </div>";
		$html = $html . " </div>";
		echo $html;
	}
	//echo $route;
	//echo $query;
	//echo $searchmode;
?>
