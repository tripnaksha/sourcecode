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

class SEFControllerHtaccess extends JController
{
    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    function __construct()
    {
        parent::__construct();
        
        $this->registerTask('applySimple', 'saveSimple');
        $this->registerTask('applyAdvanced', 'saveAdvanced');
        $this->registerTask('add', 'edit');
    }

    function _checkWritable()
    {
        $model =& $this->getModel('htaccess');

        if( !$model->IsWritable() ) {
            JError::raiseWarning('100', JText::_('INFO_HTACCESS_NOT_WRITABLE'));
            $this->setRedirect('index.php?option=com_sef');
            return false;
        }

        return true;
    }

    function display()
    {
        if( !$this->_checkWritable() ) {
            return;
        }
        
        JRequest::setVar( 'view', 'htaccess' );
        JRequest::setVar( 'layout', 'simple' );

        parent::display();
    }
    
    function advanced()
    {
        if( !$this->_checkWritable() ) {
            return;
        }
        
        JRequest::setVar( 'view', 'htaccess' );
        JRequest::setVar( 'layout', 'advanced' );
        JRequest::setVar( 'hidemainmenu', 1 );

        parent::display();
    }
    
    function edit()
    {
        JRequest::setVar( 'view', 'htaccess' );
        JRequest::setVar( 'layout', 'redirect' );
        JRequest::setVar( 'hidemainmenu', 1 );

        parent::display();
    }
    
    function save()
    {
        if( !$this->_checkWritable() ) {
            return;
        }
        
        $model =& $this->getModel('htaccess');
        
        $newid = $model->storeOptions();
        if( $newid !== false ) {
            $msg = JText::_('.htaccess file was saved successfuly.');
        }
        else {
            $msg = JText::_('Error saving .htaccess file.');
        }
        
        $this->setRedirect('index.php?option=com_sef&controller=htaccess', $msg);
    }
    
    function cancel()
    {
        $this->setRedirect('index.php?option=com_sef&controller=htaccess');
    }
    
    function remove()
    {
        if( !$this->_checkWritable() ) {
            return;
        }
        
        $model =& $this->getModel('htaccess');
        
        if( $model->remove() ) {
            $msg = JText::_('.htaccess file was saved successfuly.');
        }
        else {
            $msg = JText::_('Error saving .htaccess file.');
        }
        
        $this->setRedirect('index.php?option=com_sef&controller=htaccess', $msg);
    }
    
    function saveAdvanced()
    {
        if( !$this->_checkWritable() ) {
            return;
        }
        
        $task = JRequest::getCmd('task');
        $model =& $this->getModel('htaccess');
        
        if( $model->storeAdvanced() ) {
            $msg = JText::_('.htaccess file was saved successfuly.');
        }
        else {
            $msg = JText::_('Error saving .htaccess file.');
        }
        
        if( $task == 'saveAdvanced' ) {
            $this->setRedirect('index.php?option=com_sef&controller=htaccess', $msg);
        }
        else {
            $this->setRedirect('index.php?option=com_sef&controller=htaccess&task=advanced', $msg);
        }
    }
    
    function saveSimple()
    {
        if( !$this->_checkWritable() ) {
            return;
        }
        
        $task = JRequest::getCmd('task');
        $model =& $this->getModel('htaccess');
        
        $newid = $model->storeSimple();
        if( $newid !== false ) {
            $msg = JText::_('.htaccess file was saved successfuly.');
        }
        else {
            $msg = JText::_('Error saving .htaccess file.');
        }
        
        if( $task == 'saveSimple' ) {
            $this->setRedirect('index.php?option=com_sef&controller=htaccess', $msg);
        }
        else {
            $this->setRedirect('index.php?option=com_sef&controller=htaccess&task=edit&cid[]='.$newid, $msg);
        }
    }
}
?>