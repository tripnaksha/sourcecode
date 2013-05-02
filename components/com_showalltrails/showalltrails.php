<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

	// Get instance of database object
	$db =& JFactory::getDBO();
	$document =& JFactory::getDocument();
	$path = JPATH_COMPONENT_SITE;
	$comp = substr($path, strpos($path, 'components'));

	//require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'search.php' );
	require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_search'.DS.'helpers'.DS.'search.php');
	require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
	require_once(JPATH_SITE.DS.'components'.DS.'com_showalltrails'.DS.'encoder.php');
	require_once(JPATH_SITE.DS.'components'.DS.'com_showalltrails'.DS.'rating/rating.php');
//	<!-- Core Files.  Change the hyperlink references to reflect your site structure.  Note, this must also be updated in the ratings.js file. -->
	$document->addStyleSheet($comp . '/showall.css');
	$document->addStyleSheet($comp . '/rating/rating.css');
	$document->addScript('/modules'.DS.'mod_ajaxsearch'.DS."js/jquery.js");
	$document->addScript($comp . '/showall.js');
	$document->addScript($comp . "/rating/rating.js");

	$url = JFactory::getURI()->toString();
	if (strpos($url, ".com") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQkUZHNJdizg5ywABG1vcOZLnlKKRQiK1QyIYbC7QJYSAvZi_ftqMywEg';
	}
	else if (strpos($url, ".in") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBTGk7r6UUG2tv7pCXD49pEILMut2BSK1KyluFXiSlHDmPfgxKEcQu31zA';
	}
	else if (strpos($url, ".net") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQVUtw86IlLILJmmHf_nHtc38TT4xQEwo2T9X3LZpg2rnfZGcOvR7jrgA';
	}
	else if (strpos($url, ".org") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBRfh_bXew_S5fvS_f8On4C7bLoFWxSxwel7vDUce97Zyj7kn9hhDqcEhQ';
	}
	else if (strpos($url, "192.168.2.3") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQrYQ538GaVx7Y5oWUNieOY1BuluhTxxahJrgPkopr_wUZiigcgAainqA';
	}
	else if (strpos($url, "192.168.2.2") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBR2bDFzeCT1WBspxZrlXfH7Q6YWYhSjQl2ZBZH0H0HKmSschtXmr2DxcA';
	}
	else if (strpos($url, "192.168.1.10") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBTmuanj1bmj0Kwra7_b0ad5OHlDlBRDXWIgvx0w3LRNw0hBx3Uyx9NgQg';
	}
	else if (strpos($url, "192.168") !== false) {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBSQvaeunbmSpov2FeFgtN9Ft_-duxQeLyrqgG0pLspna6L5_tzgXRcCrA';
	}
	else {
	   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQWsjmFdvDaunQqy7Hy_5WmcWUnQxQ2hYp5C2KhnePdi9AbknQb10H_Rw';
	}
	
	$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', 10, 'int');
        $limitstart = JRequest::getVar('limitstart', 0, '', 'int');
        $params = new JParameter( $paramsdata, $paramsdefs );

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

	$query = " SELECT 1 \n" .
		 " FROM #__trailList AS b LEFT JOIN #__users c \n" .
		 " ON b.userId = c.id \n" .
		 " WHERE b.private = 0 \n";
	$db->setQuery($query);
	$rows = $db->loadObjectList();
	$total = count($rows);

	// Create the pagination object
	jimport('joomla.html.pagination');
	$pagination = new JPagination($total, $limitstart, $limit);

/*	$query = " SELECT d.id, d.userid, a.rating, a.fav, d.tname, d.routeXML, d.uname, d.ttime, d.nname, d.intro, d.createtime FROM \n" .
		 " (SELECT b.id, b.name AS tname, b.nname, b.nemail, b.routeStart, b.mapCenter, b.userId, b.length, \n" .
		 " DATE_FORMAT( b.createtime, '%d %b %y' ) AS ttime, b.intro, c.name AS uname, b.routeXML, b.createtime \n" .
		 " FROM jos_trailList AS b \n" .
		 " LEFT JOIN jos_users c ON b.userId = c.id \n" .
		 " WHERE b.private = 0) d \n" .
		 " LEFT JOIN jos_trail_rating a ON trail_id = d.id \n";
	if (JFactory::getUser()->get('id') != 0)
	  $query = $query . " AND a.uid = " . JFactory::getUser()->get('id');
	$query = $query . " ORDER BY d.createtime DESC";
	$query = " SELECT d.id, d.userid, a.rating, a.fav, d.tname, d.routeXML, d.uname, d.ttime, d.nname, d.intro, d.createtime, d.encodeurl FROM \n" .
		 " (SELECT b.id, b.name AS tname, b.nname, b.nemail, b.routeStart, b.mapCenter, b.userId, b.length, \n" .
		 " DATE_FORMAT( b.createtime, '%d %b %y' ) AS ttime, b.intro, c.name AS uname, b.routeXML, b.createtime, b.encodeurl \n" .
		 " FROM jos_trailList AS b \n" .
		 " LEFT JOIN jos_users c ON b.userId = c.id \n" .
		 " WHERE b.private = 0) d \n" .
		 " LEFT JOIN jos_trail_rating a ON trail_id = d.id \n";
*/	$query = " SELECT d.id, d.userid, a.rating, a.fav, d.tname, d.routeXML, d.uname, d.ttime, d.nname, d.descr, d.createtime, d.encodeurl FROM \n" .
		 " (SELECT b.id, b.name AS tname, b.nname, b.nemail, b.routeStart, b.mapCenter, b.userId, b.length, \n" .
		 " CASE WHEN CHAR_LENGTH(e.descr) THEN e.descr ELSE '--No Description--' END as descr, \n" .
		 " DATE_FORMAT( b.createtime, '%d %b %y' ) AS ttime, c.name AS uname, b.routeXML, b.createtime, b.encodeurl \n" .
		 " FROM jos_trailList AS b \n" .
		 " LEFT JOIN jos_trailAddlInfo e ON e.trail_id = b.id \n" .
		 " LEFT JOIN jos_users c ON b.userId = c.id \n" .
		 " WHERE b.private = 0) d \n" .
		 " LEFT JOIN jos_trail_rating a ON trail_id = d.id \n";
	if (JFactory::getUser()->get('id') != 0)
	  $query = $query . " AND a.uid = " . JFactory::getUser()->get('id');
	$query = $query . " ORDER BY d.createtime DESC";

	$db->setQuery($query, $pagination->limitstart, $pagination->limit);
	$rows = $db->loadObjectList();
?>
<div >
<div class="trailcomponent">
<?php echo 'All Trails'; ?>
</div>
<table cellspacing="0" cellpadding="0" border="0" width="100%" class="trailtable<?php echo $params->get( 'pageclass_sfx' ); ?>">
	<form action="<?php echo $this->action; ?>" method="post" name="adminForm">
	<div style="float:right">
	<?php
		echo JText::_('Display Num');
		echo $pagination->getLimitBox();
	?>
	</div>
	<div style="clear:right">&nbsp</div>
<tbody>
<?php
	$i = $limitstart;
	foreach ( $rows as $row )
	{
	  $i++;
/*	  $string = str_replace("<route><marker>(", "", $row->routeXML);
	  $string = str_replace(")</marker></route>", "", $string);
	  $points = explode(")</marker><marker>(", $string);
	  for ( $i = 0; $i < count($points); $i++)
	  {
	     $temp = explode (",",$points[$i]);
	     $polymod[$i][0] = $temp[0];
	     $polymod[$i][1] = $temp[1];
	  }
	  $polylineEncoder = new PolylineEncoder(); 
	  $polyline = $polylineEncoder->dpEncode($polymod);
*/
?>
	<tr class="trailtableentry1">
		<td>
		<a class="imga" href="<?php echo JRoute::_('index.php?option=com_routes&view=traildisplay&Itemid=1&tview=' . $row->id . '&trailname=' . str_replace(' ', '-', $row->tname));?>" title="Map for the trail <?php echo $row->tname;?>"><img alt="<?php echo $row->tname;?>" src="http://maps.google.com/maps/api/staticmap?size=80x80&amp;path=weight:3|color:red|enc:<?php echo $row->encodeurl;//$polyline[0]; ?>&amp;key=<?php echo $key; ?>&amp;sensor=false&amp;maptype=hybrid"/></a>
		<!--a class="imga" href="<?php //echo JRoute::_('index.php?option=com_traildisplay&Itemid=1&tview=' . $row->id . '&trailname=' . str_replace(' ', '-', $row->tname));?>" title="Map for the trail <?php echo $row->tname;?>"><img alt="<?php //echo $row->tname;?>" src="http://maps.google.com/maps/api/staticmap?size=80x80&amp;path=weight:3|color:red|enc:<?php //echo $row->encodeurl;//$polyline[0]; ?>&amp;key=<?php //echo $key; ?>&amp;sensor=false&amp;maptype=hybrid"/></a-->
		</td>
		<td class="detailcol">
		  <?php echo  "<h3><a href=\"" . JRoute::_("index.php?option=com_routes&view=traildisplay&Itemid=1&tview=" . $row->id . '&trailname=' . str_replace(' ', '-', $row->tname)) . "\" title=\"Map for the trail " . $row->tname ."\">" . strtoupper($row->tname) . "</a></h3>" . " created on " . $row->ttime . " by " . ($row->uname?"<a href=\"index.php?option=com_comprofiler&task=userProfile&user=".$row->userid."\">".$row->uname."</a>":($row->nname?$row->nname:"Guest"));?>
		  <?php //echo  "<h3><a href=\"" . JRoute::_("index.php?option=com_traildisplay&Itemid=1&tview=" . $row->id . '&trailname=' . str_replace(' ', '-', $row->tname)) . "\" title=\"Map for the trail " . $row->tname ."\">" . strtoupper($row->tname) . "</a></h3>" . " created on " . $row->ttime . " by " . ($row->uname?"<a href=\"index.php?option=com_comprofiler&task=userProfile&user=".$row->userid."\">".$row->uname."</a>":($row->nname?$row->nname:"Guest"));?>
		  <br />
		  <h4>Description</h4>
		  <?php echo  $row->descr;?>
		</td>
		<td>
		  <a id="fav<?php echo $row->id;?>" class="imga" href="javascript:void(0)" title="Favorite it!" onclick="javascript:favit('<?php echo JFactory::getUser()->get('id');?>','<?php echo $row->id;?>');"><img id="favimg<?php echo $row->id;?>" width=25px height=25px src="<?php echo JURI::base().'components/com_showalltrails/'; if ($row->fav == 1) echo 'fav.png'; else echo 'favs.png';?>"/></a>
		  <?php rating_form("jos_trail_rating", $row->id, JFactory::getUser()->get('id')); ?>
		  <br />
		  <div id="ratingmsg<?php echo $row->id;?>">&nbsp;</div>
		</td>
	</tr>
<?php
		}
?>
<tr>
	<td colspan="5">&nbsp;</td>
</tr>
<tr>
	<td align="center" colspan="4" class="trailtablefooter<?php //echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo $pagination->getPagesLinks(); ?>
	</td>
</tr>
<tr>
	<td colspan="5" align="right">
		<?php echo $pagination->getPagesCounter(); ?>
	</td>
</tr>
</tbody>
</form>
</table>
