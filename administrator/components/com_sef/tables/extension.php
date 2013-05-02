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


class TableExtension extends JTable
{
    var $id;
    var $file;
    var $title;
    var $filters;
    var $params;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function TableExtension(& $db) {
        parent::__construct('#__sefexts', 'file', $db);
    }
    
    function store( $updateNulls = false ) {
        $k = $this->_tbl_key;
        
        $query = "SELECT `id` FROM `#__sefexts` WHERE `file` = '{$this->$k}'";
        $this->_db->setQuery($query);
        $this->id = $this->_db->loadResult();
        
        if( $this->id ) {
            $ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
        } else {
            $ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
        }
        if( !$ret ) {
			$this->setError(get_class( $this ).'::store failed - '.$this->_db->getErrorMsg());
			return false;
		} else {
			return true;
		}
    }

}
?>
