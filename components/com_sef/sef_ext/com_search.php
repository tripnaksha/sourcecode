<?php
/**
 * Search SEF extension for Joomla!
 *
 * @author      $Author: David Jozefov $
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access.');

class SefExt_com_search extends SefExt
{

    function beforeCreate(&$uri)
    {
        $ord = $uri->getVar('ordering', null);
        if ($ord == '') {
            $uri->delVar('ordering');
        }
        
        $ph = $uri->getVar('searchphrase', null);
        if ($ph == 'all') {
            $uri->delVar('searchphrase');
        }
    }
    
    function create(&$uri)
    {
        $vars = $uri->getQuery(true);
        extract($vars);
        
        $this->params =& SEFTools::getExtParams('com_search');
        
        $newUri = $uri;
        if (!(isset($task) ? @$task : null)) {
            $title[] = JoomSEF::_getMenuTitle($option, (isset($task) ? $task : null));
            
            if( isset($searchword) && ($this->params->get('nonsefphrase', '1') != '1') ) {
                $title[] = $searchword;
            }
            
            if (count($title) > 0) {
                $nonSefVars = array();
                if (isset($ordering))       $nonSefVars['ordering']     = $ordering;
                if (isset($searchphrase))   $nonSefVars['searchphrase'] = $searchphrase;
                if (isset($submit))         $nonSefVars['submit']       = $submit;
                if (isset($limit))          $nonSefVars['limit']        = $limit;
                if (isset($limitstart))     $nonSefVars['limitstart']   = $limitstart;
                if (isset($areas))          $nonSefVars['areas']        = $areas;
                
                if (isset($searchword) && ($this->params->get('nonsefphrase', '1') == '1') ) {
                    $nonSefVars['searchword']   = $searchword;
                }
                else {
                    // Generate meta tags
                    $desc = array();
                    if( isset($searchword) ) {
                        $desc[] = $searchword;
                    }
                    if( isset($searchphrase) ) {
                        $desc[] = $searchphrase;
                    }
                    $this->metadesc = implode(', ',$desc);
                    unset($desc);
                }
                $metatags = $this->getMetaTags();

                $newUri = JoomSEF::_sefGetLocation($uri, $title, null, null, null, @$vars['lang'], $nonSefVars, null, $metatags);
            }
        }
        
        return $newUri;
    }
}
?>
