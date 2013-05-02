<?php
/**
 * $Id: com_content.php 48 2009-08-02 02:56:12Z guilleva $
 * $LastChangedDate: 2009-08-01 20:56:12 -0600 (Sat, 01 Aug 2009) $
 * $LastChangedBy: guilleva $
 * Xmap by Guillermo Vargas
 * a sitemap component for Joomla! CMS (http://www.joomla.org)
 * Author Website: http://joomla.vargas.co.cr
 * Project License: GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/


defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' ); 

require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

/** Handles standard Joomla Content */
class xmap_com_content {

	/*
	* This function is called before a menu item is printed. We use it to set the
	* proper uniqueid for the item
	*/
	function prepareMenuItem(&$node) {
		$view = $id = '';
		$link = str_replace('&amp;','&',$node->link);
		if (preg_match("/.*view=([^&]+).*/",$link,$matches)) {
			$view=$matches[1];
		}
		if (preg_match("/.*[&\?]id=([0-9]+).*/",$link,$matches)) {
			$id=$matches[1];
		}
		
		switch( $view ) {
			case 'blog_category':
			case 'category':
				$node->uid = 'com_contentc'.$id;
				$node->expandible=true;
				break;
			case 'section':
			case 'blog_section':
				$node->uid = 'com_contents'.$id;
				$node->expandible=true;
				break;
			case 'article':
				$node->uid = 'com_contenta'.$id;
				$node->expandible=false;
		}
	}

	/** return a node-tree */
	function &getTree(&$xmap, &$parent, &$params) {
		$result = null;
		if ( $parent->type === 'component' ) {
			$task = preg_replace("/.*view=([^&]+).*/",'$1',$parent->link);
			$id = preg_replace("/.*[&\?]id=([0-9]+).*/",'$1',$parent->link);
			$type = "content_$task";
		} else {
			$type = $parent->type;
			$id = $parent->componentid;
		}

		/***
		* Parameters Initialitation
		**/
		//----- Set expand_categories param
		$expand_categories = xmap_com_content::getParam($params,'expand_categories',1);
		$expand_categories = ( $expand_categories == 1
				  || ( $expand_categories == 2 && $xmap->view == 'xml')
				  || ( $expand_categories == 3 && $xmap->view == 'html')
								  ||   $xmap->view == 'navigator');
		$params['expand_categories'] = $expand_categories;

		//----- Set expand_sections param
		$expand_sections = xmap_com_content::getParam($params,'expand_sections',1);
		$expand_sections = ( $expand_sections == 1
				  || ( $expand_sections == 2 && $xmap->view == 'xml')
				  || ( $expand_sections == 3 && $xmap->view == 'html')
								  ||   $xmap->view == 'navigator');
		$params['expand_sections'] = $expand_sections;

		//----- Set show_unauth param
		$show_unauth = xmap_com_content::getParam($params,'show_unauth',1);
		$show_unauth = ( $show_unauth == 1
				  || ( $show_unauth == 2 && $xmap->view == 'xml')
				  || ( $show_unauth == 3 && $xmap->view == 'html'));
		$params['show_unauth'] = $show_unauth;

		//----- Set cat_priority and cat_changefreq params
		$priority = xmap_com_content::getParam($params,'cat_priority',$parent->priority);
		$changefreq = xmap_com_content::getParam($params,'cat_changefreq',$parent->changefreq);
		if ($priority  == '-1')
			$priority = $parent->priority;
		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['cat_priority'] = $priority;
		$params['cat_changefreq'] = $changefreq;

		//----- Set art_priority and art_changefreq params
		$priority = xmap_com_content::getParam($params,'art_priority',$parent->priority);
		$changefreq = xmap_com_content::getParam($params,'art_changefreq',$parent->changefreq);
		if ($priority  == '-1')
			$priority = $parent->priority;
		if ($changefreq  == '-1')
			$changefreq = $parent->changefreq;

		$params['art_priority'] = $priority;
		$params['art_changefreq'] = $changefreq;
		
		$params['max_art'] = intval(xmap_com_content::getParam($params,'max_art',0));
		$params['max_art_age'] = intval(xmap_com_content::getParam($params,'max_art_age',0));

		switch( $type ) {
			case 'content_blog_category':
				if ( $params['expand_categories'] ) {
					$menuparams = xmap_com_content::paramsToArray( $parent->params );
					if ( $id == 0 )  // Multi category
						$id = xmap_com_content::getParam($menuparams,'categoryid',$id);
					$result = xmap_com_content::getContentCategory($xmap, $parent, $id, $params, $menuparams);
				}
			break;
			case 'content_category':
				if( $params['expand_categories'] ) {
					$menuparams = xmap_com_content::paramsToArray( $parent->params );
					$result = xmap_com_content::getContentCategory( $xmap, $parent, $id, $params, $menuparams );
				}
			break;
			case 'content_section':
				if( $params['expand_sections'] ) {
					$menuparams = xmap_com_content::paramsToArray( $parent->params );
					$result = xmap_com_content::getContentSection($xmap, $parent, $id, $params, $menuparams);
				}
			break;
			case 'content_blog_section':
				if( $params['expand_sections'] ) {
					$menuparams = xmap_com_content::paramsToArray( $parent->params );
					$result = xmap_com_content::getContentBlogSection($xmap, $parent, $id, $param, $menuparams);
				}
			break;
			case 'content_typed':
				$database = & JFactory::getDBO();
				$database->setQuery("SELECT modified, created FROM #__content WHERE id=". $id);
				$database->loadObject( $item );
				if( $item->modified == '0000-00-00 00:00:00' )
					$item->modified = $item->created;
				$parent->modified = xmap_com_content::toTimestamp( $item->modified );
			break;
		}
		return $result;
	}

	/** Get all content items within a content category.
	 * Returns an array of all contained content items. */
	function getContentCategory(&$xmap, &$parent, $catid, &$params, &$menuparams) {
		$database = & JFactory::getDBO();
		$orderby = !empty($menuparams['orderby']) ?  $menuparams['orderby'] : (!empty($menuparams['orderby_sec'])? $menuparams['orderby_sec'] : 'rdate' );
		$orderby = xmap_com_content::orderby_sec( $orderby );

		$query =
		  "SELECT a.id, a.title, a.metakey, a.modified, CASE WHEN strcmp(a.created,a.publish_up)<0 THEN a.publish_up ELSE a.created END as `created`, a.sectionid"
		. ',CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug' .
		  ',CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as catslug'
		. "\n FROM #__content AS a,#__categories AS c"
		. "\n WHERE a.catid=(".$catid.")"
		. "\n AND a.catid=c.id"
		. "\n AND a.state='1'"
		. "\n AND ( a.publish_up = '0000-00-00 00:00:00' OR a.publish_up <= '". date('Y-m-d H:i:s',$xmap->now) ."' )"
		. "\n AND ( a.publish_down = '0000-00-00 00:00:00' OR a.publish_down >= '". date('Y-m-d H:i:s',$xmap->now) ."' )"
		. ( ($params['max_art_age'] || $xmap->isNews) ? "\n AND ( a.created >= '".date('Y-m-d H:i:s',time() - (($xmap->isNews && $params['max_art_age'] > 3)? 3 : $params['max_art_age']) *86400)."' ) " : '')
		. ( $xmap->noauth ? '' : "\n AND a.access<='". $xmap->gid ."'" )	// authentication required ?
		. ( $xmap->view != 'xml'?"\n ORDER BY ". $orderby ."": '' )
		. ( $params['max_art'] ? "\n LIMIT {$params['max_art']}" : '');
		;
		$database->setQuery( $query );
		$database->getQuery(  );
		$items = $database->loadObjectList();

		if ( count($items) > 0 ) {
			$xmap->changeLevel(1);
			foreach($items as $item) {
				$node = new stdclass();
				$node->id = $parent->id;
				$node->uid = $parent->uid.'a'.$item->id;
				$node->browserNav = $parent->browserNav;
				$node->priority = $params['art_priority'];
				$node->changefreq = $params['art_changefreq'];
				$node->name = $item->title;
				$node->expandible = false;
				// TODO: Should we include category name or metakey here?
				// $node->keywords = $item->metakey;
				$node->newsItem = 1;

				// For the google news we should use te publication date instead
				// the last modification date. See 
				if ( $xmap->isNews || $item->modified == '0000-00-00 00:00:00' )
					$item->modified = $item->created;

				$node->modified = xmap_com_content::toTimestamp( $item->modified );
				// $node->link = 'index.php?option=com_content&amp;view=article&amp;catid='.$item->catslug.'&amp;id='.$item->slug;
				$node->link = ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid);
				$xmap->printNode($node);
	    		}
			$xmap->changeLevel(-1);
	    	}
	    	return true;
	}

	/** Get all Categories within a Section.
	 * Also call getCategory() for each Category to include it's items */
	function getContentSection(&$xmap, &$parent, $secid, &$params, &$menuparams ) {
		$database = & JFactory::getDBO();

		$orderby = isset($menuparams['orderby']) ? $menuparams['orderby'] : '';
		$orderby = xmap_com_content::orderby_sec( $orderby );

		$query =
		  'SELECT a.id, a.title, a.name, a.params,a.section,a.alias'
		. ',CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug'
		. "\n FROM #__categories AS a"
		. "\n LEFT JOIN #__content AS b ON b.catid = a.id "
		. "\n AND b.state = '1'"
		. "\n AND ( b.publish_up = '0000-00-00 00:00:00' OR b.publish_up <= '". date('Y-m-d H:i:s',$xmap->now) ."' )"
		. "\n AND ( b.publish_down = '0000-00-00 00:00:00' OR b.publish_down >= '". date('Y-m-d H:i:s',$xmap->now) ."' )"
		. ( $xmap->noauth ? '' : "\n AND b.access <= ". $xmap->gid )		// authentication required ?
		. "\n WHERE a.section = '". $secid ."'"
		. "\n AND a.published = '1'"
		. ( $xmap->noauth ? '' : "\n AND a.access <= ". $xmap->gid )		// authentication required ?
		. "\n GROUP BY a.id"
		. ( @$menuparams['empty_cat'] ? '' : "\n HAVING COUNT( b.id ) > 0" )	// hide empty categories ?
		. ( $xmap->view != 'xml'? "\n ORDER BY ". $orderby: '');
		$database->setQuery( $query );
		$items = $database->loadObjectList();

		$layout = '';

		$xmap->changeLevel(1);
		foreach($items as $item) {
			$node = new stdclass();
			$node->id = $parent->id;
			$node->uid = $parent->uid.'c'.$item->id;
			$node->name = $item->title;
			$node->browserNav = $parent->browserNav;
			$node->priority = $params['cat_priority'];
			$node->changefreq = $params['cat_changefreq'];
			$node->expandible = true;
			$node->link = ContentHelperRoute::getCategoryRoute($item->slug, $item->section);
			# $node->link = 'index.php?option=com_content&amp;view=category'.$layout.'&amp;id='.$item->slug;
			if( ($xmap->printNode($node) !== FALSE) && $params['expand_categories'] ) {
				xmap_com_content::getContentCategory($xmap, $parent, $item->id, $params, $menuparams);
			}
				
		}
		$xmap->changeLevel(-1);
		return true;
	}

	/** Return an array with all Items in a Section */
	function getContentBlogSection(&$xmap, &$parent, $secid, &$params, &$menuparams ) {
		$database = & JFactory::getDBO();

		$order_pri = isset($menuparams['orderby_pri']) ? $menuparams['orderby_pri'] : '';
		$order_sec = isset($menuparams['orderby_sec']) && !empty($menuparams['orderby_sec']) ? $menuparams['orderby_sec'] : 'rdate';
		$order_pri	= xmap_com_content::orderby_pri( $order_pri );
		$order_sec	= xmap_com_content::orderby_sec( $order_sec );
		if ( $secid == 0 )  // Multi section
			$secid 	= xmap_com_content::getParam($menuparams,'sectionid',$secid);
		$where		= xmap_com_content::where( 1, $xmap->access, $xmap->noauth, $xmap->gid, $secid, date('Y-m-d H:i:s',$xmap->now) );

		$query =
		  "SELECT a.id, a.title, a.modified, a.created,a.sectionid"
		. ',CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug'
		. ',CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as catslug'
		. "\n FROM #__content AS a"
		. "\n INNER JOIN #__categories AS cc ON cc.id = a.catid"
		. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
		. "\n LEFT JOIN #__content_rating AS v ON a.id = v.content_id"
		. "\n LEFT JOIN #__sections AS s ON a.sectionid = s.id"
		. "\n LEFT JOIN #__groups AS g ON a.access = g.id"
		. "\n WHERE ". implode( "\n AND ", $where )
		. "\n AND s.access <= ".$xmap->gid
		. "\n AND cc.access <= ".$xmap->gid
		. "\n AND s.published = 1"
		. "\n AND cc.published = 1"
		. ($xmap->view!='xmal'?"\n ORDER BY $order_pri $order_sec":'')
		;

		$database->setQuery( $query );
		$items = $database->loadObjectList();

		$xmap->changeLevel(1);
		foreach($items as $item) {
			$node = new stdclass();
			$node->id = $parent->id;
			$node->uid = $parent->uid.'a'.$item->id;
			$node->browserNav = $parent->browserNav;
			$node->priority = $params['art_priority'];
			$node->changefreq = $params['art_changefreq'];
			$node->name = $item->title;
			$node->expandible = false;

			if( $item->modified == '0000-00-00 00:00:00' )
				$item->modified = $item->created;
			$node->modified = xmap_com_content::toTimestamp( $item->modified );

			$node->link = ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid);
			# $node->link = 'index.php?option=com_content&amp;task=view&amp;id='.$item->slug;
			$xmap->printNode($node);
	    }
	    $xmap->changeLevel(-1);
	    return true;
	}

	/***************************************************/
	/* copied from /components/com_content/content.php */
	/***************************************************/

	/** convert a menuitem's params field to an array */
	function paramsToArray( &$menuparams ) {
		$tmp = explode("\n", $menuparams);
		$res = array();
		foreach($tmp AS $a) {
			@list($key, $val) = explode('=', $a, 2);
			$res[$key] = $val;
		}
		return $res;
	}
	/** Translate Joomla datestring to timestamp */
	function toTimestamp( &$date ) {
		if ( $date && ereg( "([0-9]{4})-([0-9]{2})-([0-9]{2})[ ]([0-9]{2}):([0-9]{2}):([0-9]{2})", $date, $regs ) ) {
			return mktime( $regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1] );
		}
		return FALSE;
	}

	/** translate primary order parameter to sort field */
	function orderby_pri( $orderby ) {
		switch ( $orderby ) {
			case 'alpha':
				$orderby = 'cc.title, ';
				break;
	
			case 'ralpha':
				$orderby = 'cc.title DESC, ';
				break;
	
			case 'order':
				$orderby = 'cc.ordering, ';
				break;
	
			default:
				$orderby = '';
				break;
		}

		return $orderby;
	}

	/** translate secondary order parameter to sort field */
	function orderby_sec( $orderby ) {
		switch ( $orderby ) {
			case 'date':
				$orderby = 'a.created';
				break;
	
			case 'rdate':
				$orderby = 'a.created DESC';
				break;
	
			case 'alpha':
				$orderby = 'a.title';
				break;
	
			case 'ralpha':
				$orderby = 'a.title DESC';
				break;
	
			case 'hits':
				$orderby = 'a.hits';
				break;
	
			case 'rhits':
				$orderby = 'a.hits DESC';
				break;
	
			case 'order':
				$orderby = 'a.ordering';
				break;
	
			case 'author':
				$orderby = 'a.created_by_alias, u.name';
				break;
	
			case 'rauthor':
				$orderby = 'a.created_by_alias DESC, u.name DESC';
				break;
	
			case 'front':
				$orderby = 'f.ordering';
				break;
	
			default:
				$orderby = 'a.ordering';
				break;
		}

		return $orderby;
	}
	/** @param int 0 = Archives, 1 = Section, 2 = Category */
	function where( $type=1, &$access, &$noauth, $gid, $id, $now=NULL, $year=NULL, $month=NULL ) {
		$database = & JFactory::getDBO();
		
		$nullDate = $database->getNullDate();
		$where = array();
	
		// normal
		if ( $type > 0) {
			$where[] = "a.state = '1'";
			if ( !$access->canEdit ) {
				$where[] = "( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )";
				$where[] = "( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )";
			}
			if ( $noauth ) {
				$where[] = "a.access <= $gid";
			}
			if ( $id > 0 ) {
				if ( $type == 1 ) {
					$where[] = "a.sectionid IN ( $id ) ";
				} else if ( $type == 2 ) {
					$where[] = "a.catid IN ( $id ) ";
				}
			}
		}

		// archive
		if ( $type < 0 ) {
			$where[] = "a.state='-1'";
			if ( $year ) {
				$where[] = "YEAR( a.created ) = '$year'";
			}
			if ( $month ) {
				$where[] = "MONTH( a.created ) = '$month'";
			}
			if ( $noauth ) {
				$where[] = "a.access <= $gid";
			}
			if ( $id > 0 ) {
				if ( $type == -1 ) {
					$where[] = "a.sectionid = $id";
				} else if ( $type == -2) {
					$where[] = "a.catid = $id";
				}
			}
		}

		return $where;
	}
	function &getParam($arr, $name, $def) {
		$var = JArrayHelper::getValue( $arr, $name, $def, '' );
		return $var;
	}
}
