<?php
/**
 * User SEF extension for Joomla!
 *
 * @author      $Author: David Jozefov $
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access.');

class SefExt_com_user extends SefExt
{

    function create(&$uri)
    {
        $vars = $uri->getQuery(true);
        extract($vars);
        
        $title = array();
        $title[] = JoomSEF::_getMenuTitle(@$option, null, @$Itemid);

        if (isset($task)) {
            if ($task == 'register') {
                $title[] = JText::_('Register');
                unset($task);
            }
        }
        
        if (!empty($view)) {
            $title[] = JText::_($view);
        }
        
        if (!empty($layout)) {
            $title[] = JText::_($layout);
        }
        
        $newUri = $uri;
        if (count($title) > 0) {
            $newUri = JoomSEF::_sefGetLocation($uri, $title, @$task, null, null, @$lang);
        }
        
        return $newUri;
    }

}
?>
