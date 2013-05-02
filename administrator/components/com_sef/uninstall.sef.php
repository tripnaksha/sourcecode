<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.installer.installer' );
jimport( 'joomla.filesystem.file' );

function com_uninstall()
{
    // uninstall JoomSEF plugin
    $path = JPATH_ROOT.DS.'plugins'.DS.'system'.DS;
    
    $res = JFile::delete($path.'joomsef.php');
    $res = $res && JFile::delete($path.'joomsef.xml');
    
    $db =& JFactory::getDBO();
    $db->setQuery("DELETE FROM `#__plugins` WHERE `folder` = 'system' AND `element` = 'joomsef' LIMIT 1");
    $res = $res && $db->query();
    
    if (!$res) {
        JError::raiseWarning(100, JText::_('WARNING_PLUGIN_NOT_UNINSTALLED'));
    }
    
    // uninstall JoomSEF extension installer adapter
    $path = JPATH_LIBRARIES.DS.'joomla'.DS.'installer'.DS.'adapters'.DS.'sef_ext.php';
    if( JFile::exists($path) ) {
        JFile::delete($path);
    }

    echo '<h3>ARTIO JoomSEF succesfully uninstalled.</h3>';
}
?>
