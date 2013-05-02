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

class SEFModelSEFUrl extends JModel
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
            $query = "SELECT * FROM `#__sefurls` WHERE `id` = '{$this->_id}'";
            $this->_db->setQuery( $query );
            $this->_data = $this->_db->loadObject();
        }
        if (!$this->_data) {
            $this->_data = new stdClass();
            $this->_data->id = 0;
            $this->_data->cpt = null;
            $this->_data->sefurl = null;
            $this->_data->origurl = null;
            $this->_data->Itemid = null;
            $this->_data->metadesc = null;
            $this->_data->metakey = null;
            $this->_data->metatitle = null;
            $this->_data->metalang = null;
            $this->_data->metarobots = null;
            $this->_data->metagoogle = null;
            $this->_data->canonicallink = null;
            $this->_data->dateadd = null;
        }

        return $this->_data;
    }

    function store()
    {
        $row =& $this->getTable();

        $data = JRequest::get('post');

        // Bind the form fields to the table
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Make sure the record is valid
        if (!$row->check()) {
            $this->setError($row->_error);
            return false;
        }
        
        // Set the priority according to Itemid
        if ($row->Itemid != '') {
            $row->priority = _COM_SEF_PRIORITY_DEFAULT_ITEMID;
        }
        else {
            $row->priority = _COM_SEF_PRIORITY_DEFAULT;
        }

        // Store the table to the database
        if (!$row->store()) {
            $this->setError( $row->getError() );
            return false;
        }

        // check if there's old url to save to Moved Permanently table
        $unchanged = JRequest::getVar('unchanged');
        if (!empty($unchanged)) {
            $row =& $this->getTable('MovedUrl');
            $row->old = $unchanged;
            $row->new = JRequest::getVar('sefurl');

            // pre-save checks
            if (!$row->check()) {
                $this->setError($row->getError());
                return false;
            }

            // save the changes
            if (!$row->store()) {
                $this->setError($row->getError());
                return false;
            }
        }

        return true;
    }

    function delete()
    {
        $cids = JRequest::getVar('cid', array(0), 'post', 'array');

        if (count($cids)) {
            $ids = implode(',', $cids);
            $query = "DELETE FROM `#__sefurls` WHERE `id` IN ($ids)";
            $this->_db->setQuery($query);
            if (!$this->_db->query()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }
        return true;
    }

    function setActive()
    {
        if( $this->_id == 0 ) {
            return false;
        }

        // Get the SEF URL for given id
        $row =& $this->getData();

        // Set priority to 0 for given id
        $query = "UPDATE `#__sefurls` SET `priority` = '0' WHERE `id` = '{$this->_id}' LIMIT 1";
        $this->_db->setQuery( $query );
        if( !$this->_db->query() ) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        
        // Set priority to 100 for every other same SEF URL
        $query = "UPDATE `#__sefurls` SET `priority` = '100' WHERE (`sefurl` = '{$row->sefurl}') AND (`id` != '{$this->_id}')";
        $this->_db->setQuery( $query );
        if( !$this->_db->query() ) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        
        return true;
    }
}
?>