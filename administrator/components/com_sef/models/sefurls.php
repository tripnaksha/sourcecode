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

class SEFModelSEFUrls extends JModel
{
    /**
     * Constructor that retrieves variables from the request
     */
    function __construct()
    {
        parent::__construct();
        $this->_getVars();
    }

    function _getVars()
    {
        global $mainframe;

        $this->viewmode = $mainframe->getUserStateFromRequest('sef.sefurls.viewmode', 'viewmode', 0);
        $this->sortby = $mainframe->getUserStateFromRequest('sef.sefurls.sortby', 'sortby', 0);
        $this->filterComponent = $mainframe->getUserStateFromRequest("sef.sefurls.comFilter", 'comFilter', '');
        $this->filterSEF = $mainframe->getUserStateFromRequest("sef.sefurls.filterSEF", 'filterSEF', '');
        $this->filterReal = $mainframe->getUserStateFromRequest("sef.sefurls.filterReal", 'filterReal', '');
        $this->filterHitsCmp = $mainframe->getUserStateFromRequest("sef.sefurls.filterHitsCmp", 'filterHitsCmp', 0);
        $this->filterHitsVal = $mainframe->getUserStateFromRequest("sef.sefurls.filterHitsVal", 'filterHitsVal', '');
        $this->filterItemid = $mainframe->getUserStateFromRequest("sef.sefurls.filterItemId", 'filterItemid', '');
        $this->filterLang = $mainframe->getUserStateFromRequest('sef.sefurls.filterLang', 'filterLang', '');

        $this->limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $this->limitstart	= $mainframe->getUserStateFromRequest('sef.sefurls.limitstart', 'limitstart', 0, 'int');

        // in case limit has been changed, adjust limitstart accordingly
        $this->limitstart = ($this->limit != 0 ? (floor($this->limitstart / $this->limit) * $this->limit) : 0);

        $total = $this->getTotal();

        /*
        if (($this->limitstart + $this->limit - 1) > $total) {
        $this->limitstart = max(($total - $this->limit), 0);
        }
        */
        
        // tracking on?
        $config =& SEFConfig::getConfig();
        $this->trace = $config->trace;        
    }

    /**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
    function _buildQuery()
    {
        $limit = '';
        if (($this->limit != 0) || ($this->limitstart != 0)) {
            $limit = " LIMIT {$this->limitstart},{$this->limit}";
        }

        $query = "SELECT * FROM #__sefurls WHERE ".$this->_getWhere()." ORDER BY ".$this->_getSort().$limit;

        return $query;
    }

    function _getSort()
    {
        if( !isset($this->_sort) ) {
            switch ($this->sortby) {
                case 1: $this->_sort =  "`sefurl` DESC";    break;
                case 2: $this->_sort =  "`origurl`";         break;
                case 3: $this->_sort =  "`origurl` DESC";    break;
                case 4: $this->_sort =  "`cpt`";            break;
                case 5: $this->_sort =  "`cpt` DESC";       break;
                default: $this->_sort = "`sefurl`";         break;
            }
        }

        return $this->_sort;
    }

    function _getWhere()
    {
        if( empty($this->_where) ) {
            $db =& JFactory::getDBO();
            
            // filter ViewMode
            if ($this->viewmode == 1) {
                $where = "`dateadd` > '0000-00-00' AND `origurl` = '' ";
            } elseif ( $this->viewmode == 2 ) {
                $where = "`dateadd` > '0000-00-00' AND `origurl` != '' ";
            } elseif ( $this->viewmode == 0 ) {
                $where = "`dateadd` = '0000-00-00' ";
            } elseif ( $this->viewmode == 4 ) {
                $where = "(`origurl` != '') AND (`sefurl` = '' OR `origurl` LIKE '%option=com_frontpage%') ";
            } else {
                $where = "`origurl` != '' ";
            }

            // filter URLs
            if ($this->filterComponent != '' && $this->viewmode != 1) {
                $where .= "AND (`origurl` LIKE '%option={$this->filterComponent}&%' OR `origurl` LIKE '%option={$this->filterComponent}') ";
            }
            if ($this->filterLang != '' ) {
                $where .= "AND (`origurl` LIKE '%lang={$this->filterLang}%') ";
            }
            if ($this->filterSEF != '') {
                if( substr($this->filterSEF, 0, 4) == 'reg:' ) {
                    $val = substr($this->filterSEF, 4);
                    if( $val != '' ) {
                        // Regular expression search
                        $val = $db->Quote($val);
                        $where .= "AND `sefurl` REGEXP $val ";
                    }
                }
                else {
                    $val = $db->Quote('%'.$this->filterSEF.'%');
                    $where .= "AND `sefurl` LIKE $val ";
                }
            }
            if ($this->filterReal != '' && $this->viewmode != 1) {
                if( substr($this->filterReal, 0, 4) == 'reg:' ) {
                    $val = substr($this->filterReal, 4);
                    if( $val != '' ) {
                        // Regular expression search
                        $val = $db->Quote($val);
                        $where .= "AND `origurl` REGEXP $val ";
                    }
                }
                else {
                    $val = $db->Quote('%'.$this->filterReal.'%');
                    $where .= "AND `origurl` LIKE $val ";
                }
            }

            // filter hits
            if ($this->filterHitsVal != '') {
                $cmp = ($this->filterHitsCmp == 0) ? '=' : (($this->filterHitsCmp == 1) ? '>' : '<');
                $val = $db->Quote($this->filterHitsVal);
                $where .= "AND `cpt` $cmp $val ";
            }

            // Filter Itemid
            if ($this->filterItemid != '' && $this->viewmode != 1) {
                $val = $db->Quote($this->filterItemid);
                $where .= "AND `Itemid` = $val ";
            }

            // Filter duplicities
            if( $this->viewmode == 5 ) {
                $where .= " GROUP BY `sefurl` HAVING COUNT(`sefurl`) > 1";
                
                $where = " `sefurl` IN (SELECT `sefurl` FROM `#__sefurls` WHERE {$where})";
            }
            
            $this->_where = $where;
        }

        return $this->_where;
    }

    function _getWhereIds()
    {
        $ids = JRequest::getVar('cid', array(), 'post', 'array');

        $where = '';
        if( count($ids) > 0 ) {
            $where = '`id` IN (' . implode(', ', $ids) . ')';
        }

        return $where;
    }

    function getTotal()
    {
        if (!isset($this->_total)) {
            $this->_db->setQuery("SELECT COUNT(*) FROM `#__sefurls` WHERE ".$this->_getWhere());
            $this->_total = $this->_db->loadResult();
        }

        return $this->_total;
    }

    /**
     * Retrieves the data
     */
    function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty( $this->_data ))
        {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList( $query );
        }

        return $this->_data;
    }

    function getLists()
    {
        // Make the input boxes for hits filter
        $hitsCmp[] = JHTML::_('select.option', '0', '=');
        $hitsCmp[] = JHTML::_('select.option', '1', '>');
        $hitsCmp[] = JHTML::_('select.option', '2', '<');
        $lists['hitsCmp'] = JHTML::_('select.genericlist', $hitsCmp, 'filterHitsCmp', "class=\"inputbox\" onchange=\"document.adminForm.submit();\" size=\"1\"" , 'value', 'text', $this->filterHitsCmp);
        $lists['hitsVal'] = "<input type=\"text\" name=\"filterHitsVal\" value=\"{$this->filterHitsVal}\" size=\"5\" maxlength=\"10\" onchange=\"document.adminForm.submit();\" />";

        // Make the input box for Itemid filter
        $lists['itemid'] = "<input type=\"text\" name=\"filterItemid\" value=\"{$this->filterItemid}\" size=\"5\" maxlength=\"10\" onchange=\"document.adminForm.submit();\" />";

        // make the select list for the filter
        $viewmode[] = JHTML::_('select.option', '3', JText::_('Show All Redirects'));
        $viewmode[] = JHTML::_('select.option', '2', JText::_('Show Custom Redirects'));
        $viewmode[] = JHTML::_('select.option', '0', JText::_('Show SEF Urls'));
        $viewmode[] = JHTML::_('select.option', '4', JText::_('Show Links to Homepage'));
        $viewmode[] = JHTML::_('select.option', '1', JText::_('Show 404 Log'));
        $viewmode[] = JHTML::_('select.option', '5', JText::_('Show Duplicities'));
        $lists['viewmode'] = JHTML::_('select.genericlist', $viewmode, 'viewmode', "class=\"inputbox\" onchange=\"document.adminForm.submit();\" size=\"1\"" ,  'value', 'text', $this->viewmode);

        // make the select list for the filter
        $orderby[] = JHTML::_('select.option', '0', JText::_('SEF Url').' '.JText::_('(asc)'));
        $orderby[] = JHTML::_('select.option', '1', JText::_('SEF Url').' '.JText::_('(desc)'));
        if ($this->viewmode != 1) {
            $orderby[] = JHTML::_('select.option', '2', JText::_('Real Url').' '.JText::_('(asc)'));
            $orderby[] = JHTML::_('select.option', '3', JText::_('Real Url').' '.JText::_('(desc)'));
        }
        $orderby[] = JHTML::_('select.option', '4', JText::_('Hits').' '.JText::_('(asc)'));
        $orderby[] = JHTML::_('select.option', '5', JText::_('Hits').' '.JText::_('(desc)'));
        $lists['sortby'] = JHTML::_('select.genericlist', $orderby, 'sortby', "class=\"inputbox\" onchange=\"document.adminForm.submit();\" size=\"1\"" , 'value', 'text', $this->sortby);

        // make the select list for the component filter
        $comList[] = JHTML::_('select.option', '', JText::_('(All)'));
        //$comList[] = JHTML::_('select.option', 'com_content', 'Content');
        $this->_db->setQuery("SELECT `name`,`option` FROM `#__components` WHERE `parent` = '0' ORDER BY `name`");
        $rows = $this->_db->loadObjectList();
        if ($this->_db->getErrorNum()) {
            echo $this->_db->stderr();
            return false;
        }
        foreach(array_keys($rows) as $i) {
            $row = &$rows[$i];
            $comList[] = JHTML::_('select.option', $row->option, $row->name );
        }
        $lists['comList'] = JHTML::_( 'select.genericlist', $comList, 'comFilter', "class=\"inputbox\" onchange=\"document.adminForm.submit();\" size=\"1\"", 'value', 'text', $this->filterComponent);

        // make the filter text boxes
        $lists['filterSEF']  = "<input class=\"hasTip\" type=\"text\" name=\"filterSEF\" value=\"{$this->filterSEF}\" size=\"40\" maxlength=\"255\" onchange=\"document.adminForm.submit();\" title=\"".JText::_('TT_FILTER_SEF')."\" />";
        $lists['filterReal'] = "<input class=\"hasTip\" type=\"text\" name=\"filterReal\" value=\"{$this->filterReal}\" size=\"40\" maxlength=\"255\" onchange=\"document.adminForm.submit();\" title=\"".JText::_('TT_FILTER_REAL')."\" />";
        
        // Load the active languages
        if( SEFTools::JoomFishInstalled() ) {
            $db =& JFactory::getDBO();
            $query = "SELECT `name`, `shortcode` FROM `#__languages` WHERE `active` = '1' ORDER BY `name`";
            $db->setQuery($query);
            $langs = $db->loadObjectList();
            
            $langList = array();
            $langList[] = JHTML::_('select.option', '', JText::_('(All)'));
            foreach($langs as $lang) {
                $langList[] = JHTML::_('select.option', $lang->shortcode, $lang->name);
            }
            
            // Make the language filter
            $lists['filterLang'] = JHTML::_('select.genericlist', $langList, 'filterLang', 'class="inputbox" onchange="document.adminForm.submit();" size="1"', 'value', 'text', $this->filterLang);
        }

        return $lists;
    }

    function getPagination()
    {
        jimport('joomla.html.pagination');
        $pagination = new JPagination($this->getTotal(), $this->limitstart, $this->limit);

        return $pagination;
    }

    function deleteFiltered()
    {
        if( $this->viewmode == 5 ) {
            // We need to get the list of duplicates IDs first
            // (MySQL can't use the same table in DELETE and SELECT subquery). Can't do this:
            // $query = "DELETE FROM `#__sefurls` WHERE ".$this->_getWhere();

            $query = "SELECT `id` FROM `#__sefurls` WHERE ".$this->_getWhere();
            $this->_db->setQuery($query);
            $ids = $this->_db->loadResultArray();
            
            if( !is_array($ids) || count($ids) == 0 ) {
                return true;
            }
            
            // Now we need to use the IDs in the WHERE clause
            $query = "DELETE FROM `#__sefurls` WHERE `id` IN (" . implode(',', $ids) . ")";
        } else {
            $query = "DELETE FROM `#__sefurls` WHERE ".$this->_getWhere();
        }
        
        $this->_db->setQuery($query);
        if (!$this->_db->query()) {
            $this->setError( $this->_db->getErrorMsg() );
            return false;
        }

        return true;
    }

    function export($where = '')
    {
        $config =& JFactory::getConfig();
        $dbprefix = $config->getValue('config.dbprefix');
        $sql_data = '';
        $filename = 'joomsef_custom_urls.sql';
        $fields = array('cpt', 'sefurl', 'origurl', 'Itemid', 'metadesc', 'metakey', 'metatitle', 'metalang', 'metarobots', 'metagoogle', 'canonicallink', 'dateadd', 'priority');

        $query = "SELECT * FROM `#__sefurls`";
        if( !empty($where) ) {
            $query .= " WHERE " . $where;
        }
        $this->_db->setQuery( $query );
        $rows = $this->_db->loadObjectList();

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $values = array();
                foreach ($fields as $field) {
                    if (isset($row->$field)) {
                        $values[] = '\'' . $row->$field . '\'';
                    } else {
                        $values[] = '\'\'';
                    }
                }
                $sql_data .= "INSERT INTO `{$dbprefix}sefurls` (".implode(', ', $fields).") VALUES (".implode(', ', $values).");\n";
            }
        } else {
            return false;
        }

        if( !headers_sent() ) {
            // flush the output buffer
            while( ob_get_level() > 0 ) {
                ob_end_clean();
            }

            ob_start();
            header ('Expires: 0');
            header ('Last-Modified: '.gmdate ('D, d M Y H:i:s', time()) . ' GMT');
            header ('Pragma: public');
            header ('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header ('Accept-Ranges: bytes');
            header ('Content-Length: ' . strlen($sql_data));
            header ('Content-Type: Application/octet-stream');
            header ('Content-Disposition: attachment; filename="' . $filename . '"');
            header ('Connection: close');

            echo($sql_data);

            ob_end_flush();
            die();
            return true;
        } else {
            return false;
        }
    }
    
    function UpdateURLs()
    {
        $db =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();
        
        // Load all the URLs
        $query = "SELECT `sefurl`, `origurl`, `Itemid` FROM `#__sefurls` WHERE `dateadd` = '0000-00-00'";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        
        // Check that there's anything to update
        if( is_null($rows) || count($rows) == 0 ) {
            return 0;
        }

        // OK, we've got some data, let's update them
        // First, we need to remove all the URLs to be updated from the database
        $query = "DELETE FROM `#__sefurls` WHERE `dateadd` = '0000-00-00'";
        $db->setQuery($query);
        if (!$db->query()) {
            JError::raiseError(100, 'DB error: '.$db->getErrorMsg());
            return 0;
        }
        
        // Load the needed classes
        jimport('joomla.application.router');
        require_once(JPATH_ROOT.DS.'includes'.DS.'application.php');
        require_once(JPATH_ROOT.DS.'components'.DS.'com_sef'.DS.'sef.router.php');
        
        // Create an instance of JoomSEF router
        $router = new JRouterJoomsef();
        
        // Check if JoomFish is present
        if (SEFTools::JoomFishInstalled() ) {
            // We need to fool JoomFish to think we're running in frontend
            global $mainframe;
            $mainframe->_clientId = 0;
            
            // Load and initialize JoomFish plugin
            if( !class_exists('plgSystemJFDatabase') ) {
                require(JPATH_PLUGINS.DS.'system'.DS.'jfdatabase.php');
            }
            $params =& JPluginHelper::getPlugin('system', 'jfdatabase');
            $dispatcher = & JDispatcher::getInstance();
            $plugin = new plgSystemJFDatabase($dispatcher, (array)($params));
            $plugin->onAfterInitialise();
            
            // Set the mainframe back to its original state
            $mainframe->_clientId = 1;
        }
        
        // Loop through URLs and update them one by one
        for( $i = 0, $n = count($rows); $i < $n; $i++ ) {
            $row =& $rows[$i];
            $url = $row->origurl;
            $oldSef = $row->sefurl;
            if( !empty($row->Itemid) ) {
                if( strpos($url, '?') !== false ) {
                    $url .= '&';
                } else {
                    $url .= '?';
                }
                $url .= 'Itemid='.$row->Itemid;
            }
            
            $newSefUri = $router->build($url);
            
            // JURI::toString() returns bad results when used with some UTF characters!
            $newSef = ltrim(str_replace(JURI::root(), '', $newSefUri->_uri), '/');
            
            // If the SEF URL changed, we need to add it to 301 redirection table
            if( $oldSef != $newSef ) {
                // Check that the redirect does not already exist
                $query = "SELECT `id` FROM `#__sefmoved` WHERE `old` = '{$oldSef}' AND `new` = '{$newSef}' LIMIT 1";
                $db->setQuery($query);
                $id = $db->loadResult();
                
                if( !$id ) {
                    $query = "INSERT INTO `#__sefmoved` (`old`, `new`) VALUES ('{$oldSef}', '{$newSef}')";
                    $db->setQuery($query);
                    $db->query();
                }
            }
        }
        
        return count($rows);
    }
}
?>