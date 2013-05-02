<?php
/**
 * $Id: install.xmap.php 43 2009-08-01 19:21:35Z guilleva $
 * $LastChangedDate: 2009-08-01 13:21:35 -0600 (Sat, 01 Aug 2009) $
 * $LastChangedBy: guilleva $
 * Xmap by Guillermo Vargas
 * a sitemap component for Joomla! CMS (http://www.joomla.org)
 * Author Website: http://joomla.vargas.co.cr
 * Project License: GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// load language file
$pathLangFile	= JPATH_ADMINISTRATOR.DS.'components'.DS.'com_xmap'.DS.'language'.DS;
$lang =& JFactory::getLanguage();
$tmp_lng = strtolower($lang->getBackwardLang());

if ( file_exists( $pathLangFile . $tmp_lng . '.php' )){
    include_once( $pathLangFile . $tmp_lng . '.php' );
}else{
    include_once( $pathLangFile . $tmp_lng . '.php' );
}

$live_site = substr_replace(JURI::root(), "", -1, 1);

include( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_xmap'.DS.'classes'.DS.'XmapConfig.php' );

echo '<table>';
echo '<tr><td><img src="',$live_site,'/administrator/components/com_xmap/images/logo.jpg" /></td>';
echo '<td>';
echo '<table class="adminlist" style="width:auto"><tr class="row0"><td>&rarr;</td><td>'."\n";

XmapConfig::create();

echo '</td></tr>'."\n";

echo "</table></td>\n";
echo "</tr>";
echo '<tr><td colspan="2"><h3 style="padding:0;margin:0">Xmap is a sitemap component for Joomla!</h3>
	Settings can be configured in the <a href="index2.php?option=com_xmap">&rarr; component menu</a>!<br />
	Author website: <a href="http://joomla.vargas.co.cr" target="_blank">joomla.vargas.co.cr</a><br />
	Based on <a href="http://www.ko-ca.com" target="_blank">Joomap</a> by  Daniel Grothe<br />
	<br /></td></tr>';
echo "</table>";