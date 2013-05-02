<?
//  Begin Function
function createRSSFile($post_title,$post_description,$post_link)
{
	$returnITEM = "<item>\n";
	// this will return the Title of the Article.
	$returnITEM .= "<title>".iconv("UTF-8","cp1252//TRANSLIT//IGNORE",$post_title)."</title>\n";
	// this will return the Description of the Article.
	$returnITEM .= "<description>".iconv("UTF-8","cp1252//TRANSLIT//IGNORE",$post_description)."</description>\n";
	// this will return the guid to the post.
	//$returnITEM .= "<link>".iconv("UTF-8","cp1252//TRANSLIT//IGNORE",$post_guid)."</link>\n";
	// this will return the URL to the post.
	
	$returnITEM .= "<link>".iconv("UTF-8","cp1252//TRANSLIT//IGNORE",$post_link)."</link>\n";
	$returnITEM .= "</item>\n";
	return $returnITEM;
}
function createListFile($post_title,$post_description,$post_link)
{
	$returnITEM = "<li class='more'>\n";
	// this will return the Title of the Article.
	$returnITEM .= "<a href = '".$post_link."' title='".$post_description."'>".$post_title."</a>\n";
	$returnITEM .= "</li>\n";
	return $returnITEM;
}
$db = mysql_connect('localhost', 'tripnaks_dbwrite', '?w;h%EqvalQx');
mysql_select_db('tripnaks_joomTrips');
// Lets build the page
$filename = "/var/www/tripnaksha.com/htdocs/feeds/outputfeeds/upcomingtrips.xml";
unlink($filename);
$listfilename = "/var/www/tripnaksha.com/htdocs/components/com_firstpage/latesttrips.txt";
$rootURL = "http://www.tripnaksha.com";
$latestBuild = date("r");
// Lets define the the type of doc we're creating.
$createXML = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$createXML .= "<rss version=\"0.92\">\n";
$createXML .= "<channel>
	<title>Upcoming trips listed on TripNaksha</title>
	<link>$rootURL</link>
	<description>List of trips this week. This feed only contains the titles of each trip. $latestBuild</description>
	<lastBuildDate>$latestBuild</lastBuildDate>
	<docs>http://backend.userland.com/rss092</docs>
	<language>en</language>
";
$createList = "<ul class='listexpander' id='triplist'>";

/************************************get weekdays set************************************/
$nulldate 	= '0000-00-00';

$lowEnd=date("w");
$lowEnd=-$lowEnd;
$highEnd=$lowEnd + 7;
$weekday=0;
for ($i=$lowEnd; $i<=$highEnd; $i++) {
$WeekDate[$weekday]=date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")+$i, date("Y")));
$weekday++;
}

// First thing we need to do is to select only published events
$where = ' WHERE a.published = 1';
// Third is to only select events of the specified day
//$where .= ' AND ((a.dates BETWEEN \''.$WeekDate[1].'\' AND \''.$WeekDate[7].'\') OR (a.enddates BETWEEN \''.$WeekDate[1].'\' AND \''.$WeekDate[7].'\'))';
$where2 = $where . ' AND a.dates < "'.date("Y-m-d").'"';
//$where .= ' AND a.dates >= "'.date("Y-m-d").'"';
$orderby = ' ORDER BY a.id desc';
$orderby2 = ' ORDER BY a.dates desc';
// Lets Get the News Articles
$content_search = '(SELECT a.id, a.dates, a.enddates, a.title, DATE_FORMAT(a.created,"%d %M %Y") as created,'
				. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug'
				. ' FROM jos_eventlist_events AS a'
				. $where
				. $orderby
				. ') ';
//$content_search .= ' UNION ALL ';
//$content_search .= '(SELECT a.id, a.dates, a.enddates, a.title, DATE_FORMAT(a.created,"%d %M %Y") as created,'
//                                . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug'
//                                . ' FROM jos_eventlist_events AS a'
//                                . $where2
//                                . $orderby2
//                                . ') ';

//echo $content_search;
$content_results = mysql_query($content_search) or die ("Query failed: " . mysql_error() . " Actual query: " . $content_search);;

// Lets get the results
while ($articleInfo = mysql_fetch_object($content_results))
{
	$titlearray = explode("-", str_replace("&", "", $articleInfo->title));
	$text = str_replace("&", "", $articleInfo->slug);
	$text = preg_replace('/[\[\]]/', '', $text);
	$text = preg_replace('[\s]', '', $text);
	$text = preg_replace('[`\(]', '', $text);
	$page = $rootURL."/index.php?view=details&amp;id=".$text."&amp;option=com_eventlist&amp;Itemid=46";
	$description = "Starting from: ".$titlearray[0]." Dates: ".$articleInfo->dates." to ".$articleInfo->enddates;
	if (count($titlearray)>1) 
	{
	    $titletext = "";
	    for ($i=1; $i<count($titlearray)-1; $i++) {
	      $titletext .= $titlearray[$i];
	    }
	    $title = $titletext;
	     //$title = trim($titlearray[1])." from ".trim($titlearray[0]) ;
	}
	else 
	     $title = trim($articleInfo->title);
	$createXML .= createRSSFile($title,$description,$page);
	$createList .= createListFile($title,$description,$page);
}
$createList .= "</ul>";

$createXML .= "</channel>\n </rss>";
// Finish it up
$filehandle = fopen($filename,"w") or die("Can't open the xml file");
fwrite($filehandle,$createXML);
fclose($filehandle);

$filehandle = fopen($listfilename,"w") or die("Can't open the txt file");
fwrite($filehandle,$createList);
fclose($filehandle);
//echo "XML Sitemap updated!";
?>
