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

jimport('joomla.application.component.model');

class SEFModelMovedUrl extends JModel
{
    /**
     * Constructor that retrieves the ID from the request
     *
     * @access    public
     * @return    void
     */
    function __construct()
    {
        parent::__construct();

        $array = JRequest::getVar('cid',  0, '', 'array');
        $this->setId((int)$array[0]);
    }

    function setId($id)
    {
        // Set id and wipe data
        $this->_id      = $id;
        $this->_data    = null;
    }

    function &getData()
    {
        // Load the data
        if (empty( $this->_data )) {
            $query = "SELECT * FROM `#__sefmoved` WHERE `id` = '{$this->_id}'";
            $this->_db->setQuery( $query );
            $this->_data = $this->_db->loadObject();
        }
        if (!$this->_data) {
            $this->_data = new stdClass();
            $this->_data->id = 0;
            $this->_data->old = null;
            $this->_data->new = null;
            
            // Preset old url
            $presetOld = JRequest::getVar('sefurl');
            if( !empty($presetOld) ) {
                $this->_data->old = $presetOld;
            }
        }

        return $this->_data;
    }

    function store()
    {
        $row =& $this->getTable();

        $data = JRequest::get( 'post' );

        // Bind the form fields to the table
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Remove the starting slash if used
        $row->old = ltrim($row->old, '/');
        
        // Make sure the record is valid
        if (!$row->check()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Store the table to the database
        if (!$row->store()) {
            $this->setError( $row->getErrorMsg() );
            return false;
        }

        return true;
    }

    function delete()
    {
        $cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

        $row =& $this->getTable();

        if( count($cids) )
        {
            $ids = implode(',', $cids);
            $query = "DELETE FROM `#__sefmoved` WHERE `id` IN ($ids)";
            $this->_db->setQuery($query);
            if (!$this->_db->query()) {
                $this->setError( $this->_db->getErrorMsg() );
                return false;
            }
        }
        return true;
    }

}
?>
