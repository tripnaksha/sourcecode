<?
//  Begin Function
function createRSSFile($post_title,$post_pubdate,$post_description,$post_guid,$post_link)
{
	$returnITEM = "<item>\n";
	// this will return the Title of the Article.
	$returnITEM .= "<title>".iconv("UTF-8","cp1252//TRANSLIT//IGNORE",$post_title)."</title>\n";
	// this will return the Description of the Article.
	$returnITEM .= "<description>".iconv("UTF-8","cp1252//TRANSLIT//IGNORE",$post_description)."</description>\n";
	// this will return the pubDate of the Article.
	$returnITEM .= "<pubDate>".$post_pubdate."</pubDate>\n";
	// this will return the guid of the Article.
	$returnITEM .= "<guid>".iconv("UTF-8","cp1252//TRANSLIT//IGNORE",$post_guid)."</guid>\n";
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
//usleep(5000000);
// Lets build the page
$filename = "/var/www/tripnaksha.com/htdocs/feeds/outputfeeds/latesttrails.xml";
$listfilename = "/var/www/tripnaksha.com/htdocs/components/com_firstpage/latestlist.txt";
unlink($filename);
//die();
$rootURL = "http://www.TripNaksha.com";
$latestBuild = date("r");
// Lets define the the type of doc we're creating.
$createXML = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$createXML .= "<rss version=\"0.92\">\n";
$createXML .= "<channel>
	<title>Routes marked on TripNaksha</title>
	<link>$rootURL</link>
	<description>Latest routes marked on the map on TripNaksha. $latestBuild</description>
	<lastBuildDate>$latestBuild</lastBuildDate>
	<docs>http://backend.userland.com/rss092</docs>
	<language>en</language>
";
$createList = "<ul class='listexpander' id='routelist'>";

// Lets Get the News Articles
//$content_search = "SELECT id, name as title, intro as description, length, createTime FROM jos_trailList WHERE private = 0 ORDER BY createTime DESC";
$content_search = "SELECT a.id, a.name as title, descr as description, length, DATE_FORMAT(createTime,'%a, %d %b %Y %T') as createUnformat, DATE_FORMAT(createTime,'%d %M %Y') as createdat, CASE WHEN LENGTH(c.name) > 0 THEN c.name ELSE a.nname END as name FROM jos_trailList a LEFT JOIN jos_trailAddlInfo b on b.trail_id = a.id LEFT JOIN jos_users c on a.userId = c.id WHERE private = 0 AND a.id <> 104 ORDER BY createTime DESC";
$content_results = mysql_query($content_search);
// Lets get the results
while ($articleInfo = mysql_fetch_object($content_results))
{
	$alias = str_replace (" ", "-", $articleInfo->title);
	$alias = str_replace ("'", "", $alias);
	$alias = str_replace ("\\", "", $alias);
	$page = iconv("ISO-8859-9","UTF-8//TRANSLIT//IGNORE",$rootURL."/index.php?option=com_routes&amp;view=traildisplay&amp;tview=".$articleInfo->id.":trailname=".$alias);
	$page = iconv("UTF-8","UTF-8//TRANSLIT//IGNORE",$page);
	//$page = iconv("UTF-8","UTF-8//IGNORE",$rootURL."/index.php?option=com_routes&amp;view=traildisplay&amp;tview=".$articleInfo->id.":trailname=".str_replace (" ", "-", $articleInfo->title));
	$description = " Route length: ".$articleInfo->length."km Marked by: ".$articleInfo->name;
	$pubdate = $articleInfo->createUnformat." +0530";
	$guid = $page;
	//$guid = $articleInfo->id.'-'.$articleInfo->createUnformat;
	$title = $articleInfo->title;
	$createXML .= createRSSFile($title,$pubdate,$description,$guid,$page);
	$createList .= createListFile($title,$description,$page);
}
$createList .= "</ul>";
$createXML .= "</channel>\n </rss>";
// Finish it up
$filehandle = fopen($filename,"w") or die("Can't open the xml file");
fwrite($filehandle,$createXML);
fclose($filehandle);

$filehandle = fopen($listfilename,"w") or die("Can't open the text file");
fwrite($filehandle,$createList);
fclose($filehandle);
//echo "XML Sitemap updated!";
?>
