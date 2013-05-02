<?php
/**
* Author Tab Class for handling the CB tab api
* @version $Id: cb.authortab.php 605 2006-11-23 14:41:46Z beat $
* @package Community Builder
* @subpackage cb.authortab.php
* @author JoomlaJoe
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


class getMapperTab extends cbTabHandler {

	function getMapperTab() {
		$this->cbTabHandler();
	}

	function getDisplayTab($tab,$user,$ui) {
		global $_CB_framework, $_CB_database, $mainframe;
		$db =& JFactory::getDBO();
		$jVer		=	checkJversion();

		$return		=	'';

		$now		=	date( 'Y-m-d H:i:s', $_CB_framework->now() + $_CB_framework->getCfg( 'offset' ) * 60 * 60 );
		$query		=	"SELECT a.id, a.createTime, a.name, count( b.Trail_ID)  noreviews" .
					"\n FROM `jos_trailList` a" .
					"\n LEFT JOIN `jos_trailReview` b" .
					"\n ON a.id = b.Trail_ID" .
					"\n WHERE a.userId = " . (int) $user->id ."" .
					"\n GROUP BY a.id" .
					"\n ORDER BY a.createTime DESC";
		$db->setQuery( $query );
		//print $_CB_database->getQuery();
		$items = $db->loadObjectList();
		if(!count($items)>0) {
			$return .= "<br /><br /><div class=\"sectiontableheader\" style=\"text-align:left;width:95%;\">";
			$return .= "This user has not marked any routes yet.";
			$return .= "</div>";
			return $return;
		}

		$return .= $this->_writeTabDescription( $tab, $user );

		$return .= "<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" width=\"95%\">";
		$return .= "<tr class=\"sectiontableheader\">";
		$return .= "<th>"._UE_ARTICLEDATE."</th>";
		$return .= "<th>"._UE_ARTICLETITLE."</th>";
		$return .= "<th>"._UE_ARTICLEREVIEWS."</th>";
		$return .= "</tr>";
		$i=1;
		$hits="";
		$rating="";
		foreach($items AS $item) {
			$i= ($i==1) ? 2 : 1;

			$url	=	cbSef( 'index.php?option=com_traildisplay&amp;Itemid=1&amp;tview=' . $item->id .'&amp;trailname=' . $item->name );
			$return .= "<tr class=\"sectiontableentry$i\"><td>".cbFormatDate( $item->createTime )."</td><td><a href=\""
					. $url . "\">"
					.$item->name."</a></td><td>".$item->noreviews."</td></tr>\n";

		}
		$return .= "</table>";

		return $return;
	}
}	// end class getAuthorTab.
?>