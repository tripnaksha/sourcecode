<?php
/**
* @package mod_pixsearch
* @copyright	Copyright (C) 2007 PixPro Stockholm AB. All rights reserved.
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL, see LICENSE.php
* PixSearch is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modPixsearchHelper{
	function inizialize( $css_style, $offset ){
		global $mainframe;
		// ADD MOOTOOLS
		JHTML::_('behavior.mootools');
		$tohead='';
		if($css_style == 1) $tohead='<link rel="stylesheet" href="'.JURI::Base().'modules/mod_pixsearch/css/pixsearch_default.css" type="text/css" />';
		$tohead.='
		<script type="text/javascript" language="javascript" src="'.JURI::Base().'modules/mod_pixsearch/js/pixsearch.js"></script>
		<style type="text/css">#ps_results{margin-left:'.$offset.'px;}</style>
		';
		$mainframe->addCustomHeadTag($tohead);
	}
}