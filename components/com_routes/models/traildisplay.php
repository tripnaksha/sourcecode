<?php
/**
 * Hello Model for Hello World Component
 * 
 * @package    Joomla.Tutorials
 * @subpackage Components
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_2
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
/**
 * Hello Model
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class RoutesModelTraildisplay extends JModel
{
    /**
    * Gets the greeting
    * @return string The greeting to be displayed to the user
    */
    function getTrailDetail()
    {
        $db =& JFactory::getDBO();
        $trailid = JRequest::getInt( 'id' );
        $tname = JRequest::getVar( 'name' );
        $tview = JRequest::getVar( 'tview' );
        
        if ($trailid == 0)
        {
          $str = explode( ':', $tview );
          $trailid = (int) $str[0];
        }

	$db	=& JFactory::getDBO();
	$query = " SELECT a.id, a.name, a.nname, a.nemail, \n" .
		" a.userId, a.intro, a.length, DATE_FORMAT(a.createtime,'%d %b %y') as ttime, d.name as uname, upload, encodeurl, \n" .
		" CASE WHEN CHAR_LENGTH(c.descr) THEN c.descr ELSE '--No Description--' END as descr\n" .
		" FROM jos_trailList AS a LEFT JOIN jos_trailAddlInfo c  \n" .
		" ON c.trail_id = a.id \n" .
		" LEFT JOIN jos_users d \n" .
		" ON a.userId = d.id \n" .
		" WHERE a.id = " . $trailid . "\n";
/*	$query = " SELECT a.id, a.name, a.nname, a.nemail, \n" .
		" a.userId, a.intro, a.length, DATE_FORMAT(a.createtime,'%d %b %y') as ttime, d.name as uname, upload, encodeurl, descr\n" .
		" FROM jos_trailAddlInfo c, jos_trailList AS a LEFT JOIN jos_users d \n" .
		" ON a.userId = d.id \n" .
		" WHERE c.trail_id = a.id and a.id = " . $trailid . "\n";
*/
	$db->setQuery( $query );
	$details = $db->loadObject();

        return $details;
    }
}

