<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class SEFControllerInfo extends SEFController
{
    function __construct()
    {
        parent::__construct();
    }

    function help()
    {
        JRequest::setVar('view', 'info');
        JRequest::setVar('layout' , 'help');

        parent::display();
    }
    
    function doc()
    {
        JRequest::setVar('view', 'info');
        JRequest::setVar('layout', 'doc');
        
        parent::display();
    }
    
    function changelog()
    {
        JRequest::setVar('view', 'info');
        JRequest::setVar('layout', 'changelog');
        
        parent::display();
    }

}
?>
