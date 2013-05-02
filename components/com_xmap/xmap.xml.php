<?php 
/**
 * $Id: xmap.xml.php 42 2009-08-01 19:09:42Z guilleva $
 * $LastChangedDate: 2009-08-01 13:09:42 -0600 (Sat, 01 Aug 2009) $
 * $LastChangedBy: guilleva $
 * Xmap by Guillermo Vargas
 * A Sitemap component for Joomla! CMS (http://www.joomla.org)
 * Author Website: http://joomla.vargas.co.cr
 * Project License: GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Direct Access to this location is not allowed.'); 

/** Wraps XML Sitemaps output */
class XmapXML extends Xmap {
	var $_links;
	var $doCompression=1;
	var $isNews=0;

	function XmapXML (&$config, &$sitemap) {
		$this->view = 'xml';
		$this->uids = array();
		Xmap::Xmap($config, $sitemap);
	}

	/** Convert sitemap tree to a XML Sitemap list */
	function printNode( &$node ) {
		global $Itemid;

		if ($this->isNews && (!isset($node->newsItem) || !$node->newsItem) ) {
			return true;
		}

		static $live_site,$len_live_site;
		if ( !isset($live_site) ) {
			$live_site = substr_replace(JURI::root(), "", -1, 1);
			$len_live_site = strlen( $live_site );
		}

		$out = '';

		$link = Xmap::getItemLink($node);

		$is_extern = ( 0 != strcasecmp( substr($link, 0, $len_live_site), $live_site ) );

		if ( !isset($node->browserNav) )
			$node->browserNav = 0;

		if ( $node->browserNav != 3			// ignore "no link"
		 && !$is_extern					// ignore external links
		 && empty($this->_links[$link]) ) {	// ignore links that have been added already

			$this->count++;
		 	$this->_links[$link] = 1;


			if( !isset($node->priority) )
				$node->priority = "0.5";

			if( !isset($node->changefreq) )
				$node->changefreq = 'daily';

			$changefreq = $this->sitemap->getProperty('changefreq',$node->changefreq,$node->id,'xml',$node->uid);
			$priority   = $this->sitemap->getProperty('priority',$node->priority,$node->id,'xml',$node->uid);

			echo '<url>'."\n";
			# Removed escapeURL until a better solution for UTF-8 is found
			# echo '<loc>', $this->escapeURL($link) ,'</loc>'."\n";
			echo '<loc>', $link ,'</loc>'."\n";
			if ($this->_isAdmin) {
				echo '<uid>', $node->uid ,'</uid>'."\n";
				echo '<itemid>', $node->id ,'</itemid>'."\n";
			}
			$timestamp = (isset($node->modified) && $node->modified != FALSE && $node->modified != -1) ? $node->modified : time();
			$modified = gmdate('Y-m-d\TH:i:s\Z', $timestamp);
			if ( !$this->isNews ) {
				echo '<lastmod>',$modified,'</lastmod>'."\n";
   				echo  '<changefreq>',$changefreq,'</changefreq>'."\n";
				echo '<priority>',$priority,'</priority>'."\n";
			} else {
				if ( isset($node->keywords) ) {
					# $keywords = str_replace(array('&amp;','&'),array('&','&amp;'),$node->keywords);
					# $keywords = str_replace('&','&amp;',$node->keywords);
					$keywords =  htmlspecialchars($node->keywords);
				} else {
					$keywords = '';
				}

				echo "<news:news>\n";
   				echo '<news:publication_date>',$modified,'</news:publication_date>'."\n";
				if ( $keywords ) {
					echo '<news:keywords>',$keywords,'</news:keywords>'."\n";
				}
				echo "</news:news>\n";
			}
 			echo '</url>',"\n";
		}
		return true;
	}

        function escapeURL($str) {
            static $xTrans;
            if (!isset($xTrans)) {
                $xTrans = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
                foreach ($xTrans as $key => $value)
                    $xTrans[$key] = '&#'.ord($key).';';
                // dont translate the '&' in case it is part of &xxx;
                $xTrans[chr(38)] = '&';
            }   
            return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,4};)/","&amp;" , strtr($str, $xTrans));
        }

        function changeLevel($level) {
                return true;
        }

	function startOutput( &$menus, &$config ) {
		$live_site = substr_replace(JURI::root(), "", -1, 1);
		
		$this->isNews = JRequest::getInt('news',0);
		// Don't compress something if the server is going todo it anyway. Waste of time.
		$this->doCompression = ($this->sitemap->compress_xml && !ini_get('zlib.output_compression') && ini_get('output_handler')!='ob_gzhandler');

		@ob_end_clean();
		if ($this->doCompression ) {
			$encoding = JResponse::_clientEncoding();
			header('Content-Encoding: ' . $encoding);
			header('X-Content-Encoded-By: Joomla! 1.5');
			ob_start();
			
		}
		header('Content-type: application/xml; charset=utf-8');
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		if (!$config->exclude_xsl && !$this->isNews ) {
			$user =& JFactory::getUser();
			if ($user->get('gid') == "25") {	
				echo '<?xml-stylesheet type="text/xsl" href="'. $live_site.'/index2.php?option=com_xmap&amp;view=xsladminfile&amp;tmpl=component"?>'."\n";
			} else {
				echo '<?xml-stylesheet type="text/xsl" href="'. $live_site.'/index2.php?option=com_xmap&amp;view=xslfile&amp;tmpl=component"?>'."\n";
			}
		}
		echo '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
                     'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 '.
                     'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" '.
                     'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.
                     ($this->isNews? ' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"':'').
                     ">\n";
		
	}

	function endOutput( &$menus ) {
		echo "</urlset>\n";
		if ($this->doCompression) {
			$data = ob_get_contents();
			@ob_end_clean();
			echo JResponse::_compress($data);
		}
	}

	function startMenu(&$menu) {
		return true;
	}

	function endMenu(&$menu) {
		return true;
	}

}
