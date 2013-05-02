<?php
/**
 * @package    Joomla.Tutorials
 * @subpackage Components
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_1
 * @license    GNU/GPL
*/
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
$document = JFactory::getDocument();
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
$document->addScript('http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $key);
$document->addScript(JURI::base() . 'components/com_routes/assets/js/map.js');
$document->addScript(JURI::base() . 'components/com_routes/assets/js/markerlib_pack.js');
$document->addScript(JURI::base() . 'components/com_routes/assets/js/epoly.js');
$document->addStyleSheet(JURI::base() . 'components/com_routes/assets/css/map.css');

$njs = 'window.addEvent(\'onunload\', \'GUnload()\');';
$document->addScriptDeclaration($njs);

/**
 * HTML View class for the HelloWorld Component
 *
 * @package    HelloWorld
 */
class RoutesViewMap extends JView
{
    function display($tpl = null)
    {
        $model = &$this->getModel();
	$js = "load(";
	if (JFactory::getUser()->get('username'))
	  $js .= '\'' . JFactory::getUser()->get('username') . '\'';
	else
	  $js .= '\'' . 'Guest' . '\'';
	$js .= ', ';
	if (JFactory::getUser()->get('id'))
	  $js .= '\'' . JFactory::getUser()->get('id') . '\'';
	else
	  $js .= "'0'";
	if ($tview)
	  $js .= ", " . $tview;
	else
	  $js .= ", " . '\'0\'';
	$js .= ",'".$geoplugin->city."');";
       $this->assignRef( 'js', $js );
 
        parent::display($tpl);
    }
}
