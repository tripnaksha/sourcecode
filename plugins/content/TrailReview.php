<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

$mainframe->registerEvent( 'onAfterContentSave', 'TrailReview' );

class TrailReview extends JPlugin
{
	function onAfterContentSave( &$article, $isNew )
	{
		global $mainframe;
		$db	=& JFactory::getDBO();
		$user	=& JFactory::getUser();
	 	$uri 	=& JFactory::getURI();

		if (strpos ($uri->toString(), 'trailId') || isset($_COOKIE['trailrev']))
		{
			// Get plugin info
			$plugin =& JPluginHelper::getPlugin('content', 'TrailReview');
		 	$pluginParams = new JParameter( $plugin->params );

			$catname = $pluginParams->get('catname', 1);
			$secname = $pluginParams->get('secname', 1);

			$query = "SELECT MAX(A.id) AS latest " .
				"FROM #__content A, #__categories B, #__sections C " .
				"WHERE A.catid = B.id " .
				"AND A.sectionid = C.id " .
				"AND INSTR( B.title, '" . $catname . "' ) " .
				"AND INSTR( C.title, '" . $secname . "' )";
			$db->setQuery( $query );
			$rows = $db->loadObjectList();

			$row = $rows[0];

			if(!$rows){
				echo $query;
				die(mysql_error());
			}

			if(isset($_COOKIE['trailid']))
				$trail = $_COOKIE['trailid'];
			if(isset($_COOKIE['difficulty']))
				$diffi = $_COOKIE['difficulty'];
			else $diffi = 2;
			if(isset($_COOKIE['time']))
				$time = $_COOKIE['time'];
			else $time = 2;
			if(isset($_COOKIE['equipment']))
				$equipment = $_COOKIE['equipment'];

			$query1 = "INSERT INTO #__trailReview (Trail_ID, Content_ID, difficulty, time, equipment) " .
				"VALUES (" . $trail . ", " . $row->latest . ", " . $diffi . ", " . $time . ", \"" . $equipment . "\")";
			//Delete the cookies after every use
			unset($_COOKIE['trailrev']);
			unset($_COOKIE['time']);
			unset($_COOKIE['equipment']);
			unset($_COOKIE['difficulty']);

			$db->setQuery( $query1 );
			$rows = $db->query();

			if(!$rows){
				echo $query1;
				die(mysql_error());
			}
		}
	}

/*  Don't think it is a good idea to force a name, let the users choose.
        function onBeforeContentSave( &$article, $isNew )
        {
                global $mainframe;
                $user =& JFactory::getUser(); // get the user
                $article->title = "goobley dook " . $article->title;

                return true;
        }
*/
}
?>
