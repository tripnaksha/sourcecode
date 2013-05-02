<?php
/**
 * RokNewsFlash Module
 *
 * @package RocketTheme
 * @subpackage roknewsflash
 * @version   1.3 February 4, 2010
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
jimport('joomla.utilities.date');

class comAddlInfoHelper
{

	function getList()
	{
		global $mainframe;

		$db		=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		$userId		= (int) $user->get('id');
		
		$where = "a.published = 1";
		$orderby = "type";
		
    		$query = 'SELECT a.id, a.type, a.description ' .
    			' FROM #__trailType AS a' .
    			' WHERE '. $where .
    			' ORDER BY '. $orderby;
		
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$i		= 0;
		$lists	= array();
		foreach ( $rows as $row )
		{
			$lists[$i]->id = $row->id;
			$lists[$i]->type = $row->type;
			$lists[$i]->description = $row->description;
			$i++;
		}

		return $lists;
	}

	function setInfo($date, $duration, $activity, $difficulty, $desc, $trailid, $trailname)
//	function setInfo($date, $duration, $activity, $difficulty, $desc, $trailid)
	{
		global $mainframe;
//		$enddate = "";

		if (date('Y-m-d', strtotime($date)) > date('Y-m-d')) 
		  $planned = 1;
		else
		  $planned = 0;
		  
		if (strlen($desc) == 0)
		   $desc = "--No Description--";
                else
                {
	           $order   = array("\r\n", "\n", "\r");
	           $replace = '<br />';
                   $desc = str_replace($order, $replace, $desc);
                }

		if ($duration == "# of days")
		   $duration = 0;

	        $enddate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " +".$duration." day"));
	        if ($activity == "")
	           $activity = 0;
	        if ($difficulty == "")
	           $difficulty = 3;

		$db	=& JFactory::getDBO();
    		$query = 'INSERT INTO  ' .
    			' jos_trailAddlInfo (' .
			' trail_id,' .
			' type,' .
			' descr,' .
			' difficulty,' .
			' planned,' .
			' strtdate,' .
			' enddate,' .
			' duration)' .
		        ' VALUES (' .
		         $trailid . ", " .
		         $activity . ", " .
		         $db->quote($db->getEscaped($desc)) . ", " .
		         $difficulty . ", " .
		         $planned . ", " .
		         $db->quote(date('Y-m-d', strtotime($date))) . ", " .
		         $db->quote($enddate) . ", " .
		         $duration . ");";
//	echo "<script type='text/javascript'>alert('$query');</script>"; 
		$result = mysql_query($query);

		if (mysql_errno())
		  die('Invalid query : ' . $query . '<br />' . mysql_error());
		else
		   $lastid = mysql_insert_id();

		return $trailid.':'.$trailname;
//		return $trailid;
	}
	
	function getBrowser() 
	{
		$agent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : false;
		$ie_version = false;
				
		if (eregi("msie", $agent) && !eregi("opera", $agent)){
            $val = explode(" ",stristr($agent, "msie"));
            $ver = explode(".", $val[1]);
			$ie_version = $ver[0];
			$ie_version = ereg_replace("[^0-9,.,a-z,A-Z]", "", $ie_version);
		}
		
		return $ie_version;
	}
	
}
