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

class SEFModelURLs extends JModel
{
    function __construct()
    {
        parent::__construct();
    }
    
    function purge()
    {
        if( $this->_getTableWhere($table, $where) === false ) {
            return false;
        }
        
        $db =& JFactory::getDBO();
        $sql = "DELETE FROM $table" . (!empty($where) ? " WHERE $where" : '');
        $db->setQuery($sql);
        
        return $db->query();
    }
    
    function getCount($type = null)
    {
        if( $this->_getTableWhere($table, $where, $type) === false ) {
            return 0;
        }
        
        $db =& JFactory::getDBO();
        $sql = "SELECT COUNT(*) FROM $table" . (!empty($where) ? " WHERE $where" : '');
        $db->setQuery($sql);
        $this->_count = $db->loadResult();
        
        return $this->_count;
    }
    
    function _getTableWhere(&$table, &$where, $type = null)
    {
        if (is_null($type)) {
            $type = JRequest::getVar('type');
        }
        if( is_null($type) ) {
            return false;
        }
        
        if( ($type >= 0) && ($type <= 2) ) {
            $table = '`#__sefurls`';
            if( $type == 0 ) {
                $where = "`dateadd` = '0000-00-00'";
            }
            elseif( $type == 1 ) {
                $where = "`dateadd` > '0000-00-00' and `origurl` = '' ";
            }
            elseif( $type == 2 ) {
                $where = "`dateadd` > '0000-00-00' and `origurl` != '' ";
            }
        } elseif ( $type == 3 ) {
            $table = '`#__sefmoved`';
            $where = '';
        } else {
            return false;
        }
        
        return true;
    }

}
?>
