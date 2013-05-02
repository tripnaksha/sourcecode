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

class SEFControllerMovedUrls extends SEFController
{
    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    function __construct()
    {
        parent::__construct();
        
        $this->registerTask('add', 'edit');
    }

    function display()
    {
        JRequest::setVar( 'view', 'movedurls' );
        
        parent::display();
    }
    
    function edit()
    {
        JRequest::setVar( 'view', 'movedurl' );
        JRequest::setVar( 'hidemainmenu', 1 );
        
        parent::display();
    }
    
    function save()
    {
        $model = $this->getModel('movedurl');

        if ($model->store()) {
            $msg = JText::_( 'URL Saved' );
        } else {
            $msg = JText::_( 'Error Saving URL' );
        }

        $this->setRedirect('index.php?option=com_sef&controller=movedurls', $msg);
    }
    
    function remove()
    {
		$model = $this->getModel('movedurl');
		
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More URLs Could not be Deleted' );
		} else {
			$msg = JText::_( 'URL(s) Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_sef&controller=movedurls', $msg );
    }
    
    function deleteFiltered()
    {
        $model = $this->getModel('movedurls');
        
		if(!$model->deleteFiltered()) {
			$msg = JText::_( 'Error: One or More URLs Could not be Deleted' );
		} else {
			$msg = JText::_( 'URL(s) Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_sef&controller=movedurls', $msg );
    }
    
    function cancel()
    {
        $this->setRedirect( 'index.php?option=com_sef&controller=movedurls' );
    }
}
?>
