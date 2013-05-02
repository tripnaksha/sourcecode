<?php
/**
* Blog Tab Class for handling the CB tab api
* @version $Id: cb.mamblogtab.php 461 2006-10-30 15:07:03Z beat $
* @package Community Builder
* @subpackage cb.mamblog.php
* @author JoomlaJoe - Thanks to Jeffrey Hill for pagination and search additions
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


class getBlogTab extends cbTabHandler {
	
	function getBlogTab() {
		$this->cbTabHandler();
	}

	function getDisplayTab($tab,$user,$ui) {
		global $_CB_database, $_CB_framework, $mainframe;
		if(!file_exists( $_CB_framework->getCfg('absolute_path') . '/components/com_mamblog/configuration.php' )){
			$return = _UE_MAMBLOGNOTINSTALLED;
		} else {
			include_once ( $_CB_framework->getCfg('absolute_path') . '/components/com_mamblog/configuration.php' );
			$return="";
	
			$return .= $this->_writeTabDescription( $tab, $user );

			$params = $this->params;
	        $entriesNumber	= $params->get('entriesNumber', '10');
			$pagingEnabled	= $params->get('pagingEnabled', 0);
			$searchEnabled	= $params->get('searchEnabled', 0);
			$pagingParams = $this->_getPaging(array(),array("entries_"));
			if (!$searchEnabled) $pagingParams["entries_search"]=null;

            $sectid="";
            $catid="";
            if(ISSET($cfg_mamblog['sectionid']))  $sectid="\n AND a.sectionid=" . (int) $cfg_mamblog['sectionid'];
            if(ISSET($cfg_mamblog['categoryid'])) $catid="\n AND a.categoryid=" . (int) $cfg_mamblog['categoryid'];

            $where = "\n WHERE a.created_by = ". (int) $user->id .""
			. "\n AND a.state = 1"
			. $sectid
			. $catid
			. ($pagingParams["entries_search"]?  "\n AND (a.title LIKE '%".cbEscapeSQLsearch($pagingParams["entries_search"])."%'"
												  ." OR a.introtext LIKE '%".cbEscapeSQLsearch($pagingParams["entries_search"])."%'"
												  ." OR a.fulltext LIKE '%".cbEscapeSQLsearch($pagingParams["entries_search"])."%')"
												  : "");

            if ($pagingEnabled) {
	            $query="SELECT COUNT(*)"
	            		. "\n FROM #__content AS a"
	         		    . $where;
				$_CB_database->setQuery($query);
	            $total = $_CB_database->loadResult();
	            if (!is_numeric($total)) $total = 0;
	            $userHasPosts = ($total > 0 || ($pagingParams["entries_search"]));
	            if ($pagingParams["entries_limitstart"] === null) $pagingParams["entries_limitstart"] = "0";
	            if ($entriesNumber > $total) $pagingParams["entries_limitstart"] = "0";
	        } else {
	            $pagingParams["entries_limitstart"] = "0";
	            $pagingParams["entries_search"] = null;
	        }
	        switch ($pagingParams["entries_sortby"]) {
	    	case "title":
				$order = "a.title ASC, a.created DESC";
				break;
			case "hits":
				$order = "a.hits DESC, a.created DESC";
				break;
			case "date":
			default:
				$order = "a.created DESC";
				break;
	        }
			$query = "SELECT a.id, a.title, a.hits, a.created"
			// For the article plugin?
	        //. "\n ROUND( r.rating_sum / r.rating_count ) AS rating,r.rating_count"
			. "\n FROM #__content AS a"
			//. "\n LEFT JOIN #__content_rating AS r ON r.content_id = a.id"
			. $where
			. "\n ORDER BY ".$order
			. "\n LIMIT " . (int) ( $pagingParams["entries_limitstart"] ? $pagingParams["entries_limitstart"] : 0 ) . "," . (int) $entriesNumber;
	        $_CB_database->setQuery( $query );
			$items = $_CB_database->loadObjectList();

			if ($searchEnabled) {
	            $searchForm = $this->_writeSearchBox($pagingParams,"entries_", "style=\"float:right;\"", "class=\"inputbox\"");
			}

			if(count($items) > 0) {
				if ($pagingParams["entries_search"]) $title = sprintf(_UE_BLOG_FOUNDENTRIES,$total);
				elseif ($pagingEnabled) $title = sprintf(_UE_BLOG_ENTRIES,$entriesNumber);
				else $title = sprintf(_UE_BLOG_LASTENTRIES,$entriesNumber);
				$return .= "<br /><div class=\"sectiontableheader\" style=\"text-align:left;padding-left:0px;padding-right:0px;margin:0px 0px 10px 0px;height:auto;width:100%;\">";
				$return .= "<div class=\"sectiontableheader\" style=\"float:left;\">".$title."</div> ";
				
	            $artURL="index.php?option=com_content&amp;task=view&amp;id=";
	            if ($searchEnabled) $return .= $searchForm;
				$return .= "<br /><div style=\"clear:both;\">&nbsp;</div>";
	            $return .= "<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" style=\"margin:0px;padding:0px;width:100%;\">";
	            $return .= "<tr class=\"sectiontableheader\">";
	            $return .= "<th>".$this->_writeSortByLink($pagingParams,"entries_","date",_UE_ARTICLEDATE,true)."</th>";
	            $return .= "<th>".$this->_writeSortByLink($pagingParams,"entries_","title",_UE_ARTICLETITLE)."</th>";
	            if($_CB_framework->getCfg( 'hits' )) {
	            	$return .= "<th>".$this->_writeSortByLink($pagingParams,"entries_","hits",_UE_ARTICLEHITS)."</th>";
	            }
	            $return .= "</tr>";
	            $i = 2;
	            foreach($items as $item) {
	            	if ( isset( $mainframe ) && is_callable( array( $mainframe, "getItemid" ) ) ) {
		            	$itemid	= $mainframe->getItemid( $item->id );
        			} elseif (is_callable( "JApplicationHelper::getItemid" ) ) {
	            		$itemid	= JApplicationHelper::getItemid( $item->id );
	            	} else {
	            		$itemid = null;
	            	}
	            	$itemidtxt	= $itemid ? "&amp;Itemid=" . (int) $itemid : "";
	                $i = ($i==1) ? 2 : 1;
	                $return .= "<tr class=\"sectiontableentry$i\"><td>" . cbFormatDate( $item->created ) . "</td>"
	                		. "<td><a href=\"".$artURL.$item->id.$itemidtxt."\">".$item->title."</a></td>";
	                if($_CB_framework->getCfg( 'hits' )) $return.= "<td>".$item->hits."</td>\n";
	                $return .= "</tr>\n";
		        }
	            $return .= "</table></div>";
	            if ($pagingEnabled && ($entriesNumber < $total)) {
	                $return .= "<div style='width:95%;text-align:center;'>"
	                .$this->_writePaging($pagingParams,"entries_",$entriesNumber,$total)
	                ."</div>";
	            }
	        }
            else {
                if ($pagingEnabled && $userHasPosts && $searchEnabled && $pagingParams["entries_search"]) {
					 $return .= "<br /><div class=\"sectiontableheader\" style=\"text-align:left;width:95%;\">";
					 $return .= $searchForm;
		             $return .= "</div>";
					 $return .= "<br />".sprintf(_UE_BLOG_FOUNDENTRIES, 0);
                } else {
		 			 $return .= "<br /><br /><div class=\"sectiontableheader\" style=\"text-align:left;width:95%;\">";
					 $return .= _UE_NOBLOGS;
					 $return .= "</div>";
               }
            }
		}
		return $return;
    }
}	// end class getBlogTab
?>
