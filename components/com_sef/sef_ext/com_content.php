<?php
/**
 * Content SEF extension for Joomla!
 *
 * @author      $Author: David Jozefov $
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access.');

define( '_COM_SEF_PRIORITY_CONTENT_ARTICLE_ITEMID',         15 );
define( '_COM_SEF_PRIORITY_CONTENT_ARTICLE',                20 );
define( '_COM_SEF_PRIORITY_CONTENT_SECTIONLIST_ITEMID',     25 );
define( '_COM_SEF_PRIORITY_CONTENT_SECTIONLIST',            30 );
define( '_COM_SEF_PRIORITY_CONTENT_CATEGORYLIST_ITEMID',    35 );
define( '_COM_SEF_PRIORITY_CONTENT_CATEGORYLIST',           40 );
define( '_COM_SEF_PRIORITY_CONTENT_SECTIONBLOG_ITEMID',     45 );
define( '_COM_SEF_PRIORITY_CONTENT_SECTIONBLOG',            50 );
define( '_COM_SEF_PRIORITY_CONTENT_CATEGORYBLOG_ITEMID',    55 );
define( '_COM_SEF_PRIORITY_CONTENT_CATEGORYBLOG',           60 );

class SefExt_com_content extends SefExt
{
    /**
     * Get SEF titles of content items.
     *
     * @param  string $task
     * @param  int $id
     * @return string
     */
    function _getContentTitles($task, $id)
    {
        $database =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();

        $title = array();
        // JF translate extension.
        $jfTranslate = $sefConfig->translateNames ? ', `id`' : '';
        
        // Fields
        $title_field = $category_field = $section_field = 'title';
        if( SEFTools::UseAlias($this->params, 'title_alias') ) {
            $title_field = 'alias';
        }
        if( SEFTools::UseAlias($this->params, 'category_alias') ) {
            $category_field = 'alias';
        }
        if( SEFTools::UseAlias($this->params, 'section_alias') ) {
            $section_field = 'alias';
        }
        
        $showSection = $this->params->get('show_section', '0');
        $showCategory = $this->params->get('show_category', '1');
        $addCatToTitle = $this->params->get('meta_titlecat', '0');

        $descField = null;
        
        switch ($task) {
            case 'section':
            case 'blogsection': {
                if (isset($id)) {
                    $sql = "SELECT `$section_field` AS `section`, `description` AS `sec_desc`$jfTranslate FROM `#__sections` WHERE `id` = '$id'";
                }
                $descField = 'sec_desc';
                break;
            }
            case 'category':
            case 'blogcategory':
                if (isset($id)) {
                    if ($showSection || !$showCategory) {
                        $sql = 'SELECT s.'.$section_field.' AS section, s.description AS sec_desc'.($jfTranslate ? ', s.id AS section_id' : '')
                        .($showCategory ? ', c.'.$category_field.' AS category, c.description AS cat_desc'.($jfTranslate ? ', c.id' : '') : '')
                        .' FROM #__categories as c '
                        .'LEFT JOIN #__sections AS s ON c.section = s.id '
                        .'WHERE c.id = '.$id;
                    }
                    else {
                        $sql = "SELECT `$category_field` AS `category`, `description` AS `cat_desc`$jfTranslate FROM #__categories WHERE `id` = $id";
                    }
                    if( $showCategory ) {
                        $descField = 'cat_desc';
                    } else {
                        $descField = 'sec_desc';
                    }
                }
                break;
            case 'article':
                if (isset($id)) {
                    /*
                    Alias should not be empty, Joomla 1.5 ensures that when saving content
                    if ($sefConfig->useAlias) {
                        // verify title alias is not empty
                        $database->setQuery("SELECT alias$jfTranslate FROM #__content WHERE id = $id");
                        $title_field = $database->loadResult() ? 'alias' : 'title';
                    }
                    */
                    $selects = array();
                    $joins = array();
                    if ($showSection) {
                        $selects[] = 's.'.$section_field.' AS section'.($jfTranslate ? ', s.id AS section_id' : '').', ';
                        $joins[] = 'LEFT JOIN #__sections AS s ON a.sectionid = s.id';
                    }
                    if ($showCategory || $addCatToTitle) {
                        $selects[] = 'c.'.$category_field.' AS category'.($jfTranslate ? ', c.id AS category_id' : '').', ';
                        $joins[] = 'LEFT JOIN #__categories AS c ON a.catid = c.id';
                    }
                    $sql = 'SELECT '.implode('', $selects).
                        'a.'.$title_field.' AS title, a.introtext AS item_desc'.($jfTranslate ? ', a.id' : '').' FROM #__content as a '.
                        implode(' ', $joins).
                        ' WHERE a.id = '.$id;
                    $descField = 'item_desc';
                }
                break;
            default:
                $sql = '';
        }

        if ($sql) {
            $database->setQuery($sql);
            $row = $database->loadObject();

            if (isset($row->section)) {
                $title[] = $row->section;
                if ($sefConfig->contentUseIndex && ($task == 'section')) {
                    $title[] = '/';
                }
            }
            if (isset($row->category) && $showCategory) {
                $title[] = $row->category;
                if ($sefConfig->contentUseIndex && ($task == 'category')) {
                    $title[] = '/';
                }
            }
            if (isset($row->title)) $title[] = $row->title;
            
            if ($addCatToTitle && isset($row->category) && isset($row->title)) {
                $this->metatitle = $row->title . ' - ' . $row->category;
            }
            
            if( isset($row->$descField) ) {
                $this->metadesc = $row->$descField;
            }
        }
        return $title;
    }

    function beforeCreate(&$uri)
    {
        $db =& JFactory::getDBO();

        $params = SEFTools::GetExtParams('com_content');

        // Compatibility mode
        $comp = $params->get('compatibility', '0');
        
        // Change task=view to view=article for old urls
        if( !is_null($uri->getVar('task')) && ($uri->getVar('task') == 'view') ) {
            if( $comp == '0' ) {
                $uri->delVar('task');
            }
            $uri->setVar('view', 'article');
        }
        
        // Add the task=view in compatibility mode
        if ($comp != '0') {
            if (is_null($uri->getVar('task')) && !is_null($uri->getVar('view')) && ($uri->getVar('view') == 'article')) {
                $uri->setVar('task', 'view');
            }
        }

        // remove the limitstart and limit variables if they point to the first page
        if (!is_null($uri->getVar('limitstart')) && ($uri->getVar('limitstart') == '0')) {
            $uri->delVar('limitstart');
            $uri->delVar('limit');
        }

        // Try to guess the correct Itemid if set to
        if ($params->get('guessId', '0') != '0') {
            if (!is_null($uri->getVar('Itemid')) && !is_null($uri->getVar('id'))) {
                global $mainframe;
                $i = $mainframe->getItemid($uri->getVar('id'));
                $uri->setVar('Itemid', $i);
            }
        }

        // Remove the part after ':' from variables
        if (!is_null($uri->getVar('id')))    SEFTools::fixVariable($uri, 'id');
        if (!is_null($uri->getVar('catid'))) SEFTools::fixVariable($uri, 'catid');

        // If catid not given, try to find it
        $catid = $uri->getVar('catid');
        if (!is_null($uri->getVar('view')) && ($uri->getVar('view') == 'article') && !is_null($uri->getVar('id')) && empty($catid)) {
            $id = $uri->getVar('id');
            $query = "SELECT `catid` FROM `#__content` WHERE `id` = '{$id}'";
            $db->setQuery($query);
            $catid = $db->loadResult();

            if (!empty($catid)) {
                $uri->setVar('catid', $catid);
            }
        }

        // add the view variable if it's not set
        if (is_null($uri->getVar('view'))) {
            if (is_null($uri->getVar('id'))) {
                $uri->setVar('view', 'frontpage');
            } else {
                $uri->setVar('view', 'article');
            }
        }

        return;
    }

    function GoogleNews($title, $id)
    {
        $db =& JFactory::getDBO();

        $num = '';
        $add = $this->params->get('googlenewsnum', '0');

        if ($add == '1' || $add == '3') {
            // Article ID
            $digits = trim($this->params->get('digits', '3'));
            if (!is_numeric($digits)) {
                $digits = '3';
            }

            $num1 = sprintf('%0'.$digits.'d', $id);
        }
        if ($add == '2' || $add == '3') {
            // Publish date
            $query = "SELECT `publish_up` FROM `#__content` WHERE `id` = '$id'";
            $db->setQuery($query);
            $time = $db->loadResult();

            $time = strtotime($time);

            $date = $this->params->get('dateformat', 'ddmm');

            $search = array('dd', 'd', 'mm', 'm', 'yyyy', 'yy');
            $replace = array(date('d', $time),
            date('j', $time),
            date('m', $time),
            date('n', $time),
            date('Y', $time),
            date('y', $time) );
            $num2 = str_replace($search, $replace, $date);
        }

        if ($add == '1') {
            $num = $num1;
        }
        else if ($add == '2') {
            $num = $num2;
        }
        else if ($add == '3') {
            $sep = $this->params->get('iddatesep', '');
            if ($this->params->get('iddateorder', '0') == '0') {
                $num = $num2.$sep.$num1;
            }
            else {
                $num = $num1.$sep.$num2;
            }
        }
        
        if (!empty($num)) {
            $onlyNum = ($this->params->get('title_alias', 'global') == 'googlenews');
            
            if ($onlyNum) {
                return $num;
            }
            
            $sefConfig =& SEFConfig::getConfig();
            $sep = $sefConfig->replacement;

            $where = $this->params->get('numberpos', '1');

            if( $where == '1' ) {
                $title = $title.$sep.$num;
            } else {
                $title = $num.$sep.$title;
            }
        }

        return $title;
    }

    function create(&$uri)
    {
        $sefConfig =& SEFConfig::getConfig();

        $this->params =& SEFTools::GetExtParams('com_content');

        $vars = $uri->getQuery(true);
        extract($vars);

        // Do not SEF URLs with exturl variable
        //if( !empty($exturl) )   return $string;

        // Do not SEF edit urls
        if (isset($task) && ($task == 'edit')) {
            return $uri;
        }

        // Set title.
        $title = array();

        switch (@$view) {
            case 'new':
            case 'edit': {
                /*
                $title[] = getMenuTitle($option, $task, $Itemid, $string);
                $title[] = 'new' . $sefConfig->suffix;
                */
                break;
            }
            case 'archive': {
                $title[] = JText::_($view);
                
                if( !empty($year) ) {
                    $title[] = $year;
                }
                if( !empty($month) ) {
                    $title[] = $month;
                }
                
                break;
            }
            /*
            case 'archivecategory':
            case 'archivesection': {
            if (eregi($task.".*id=".$id, $_SERVER['REQUEST_URI'])) break;
            }
            */            
            default: {
                if (isset($format)) {
                    if ($format == 'pdf') {
                        // wrong ID
                        if (intval($id) == 0) return $uri;
                        
                        // create PDF
                        $title = $this->_getContentTitles(!empty($view) ? $view : 'article', $id);
                        if (count($title) === 0) $title[] = JoomSEF::_getMenuTitle(@$option, @$task, @$Itemid);

                        // Add Google News number if set to
                        if ((@$view == 'article') && ($this->params->get('googlenewsnum', '0') != '0')) {
                            $i = count($title) - 1;
                            $title[$i] = $this->GoogleNews($title[$i], $id);
                        }
                        
                        $title[] = JText::_('PDF');
                    } elseif ($format == 'feed') {
                        // Create feed
                        if (@$view != 'frontpage') {
                            // wrong ID
                            if (intval($id) == 0) return $uri;
                            
                            $title = $this->_getContentTitles(!empty($view) ? $view : 'article', $id);

                            // Add Google News number if set to
                            if ((@$view == 'article') && ($this->params->get('googlenewsnum', '0') != '0')) {
                                $i = count($title) - 1;
                                $title[$i] = $this->GoogleNews($title[$i], $id);
                            }
                        }
                        if ((count($title) === 0) && empty($type)) {
                            $title[] = JoomSEF::_getMenuTitle(@$option, @$task, @$Itemid);
                        }

                        if (!empty($type)) $title[] = $type;
                    }
                } else {
                    if (isset($id)) {
                        // wrong ID
                        if (intval($id) == 0) return $uri;
                        
                        $title = $this->_getContentTitles(@$view, @$id);
                        if (count($title) === 0) $title[] = JoomSEF::_getMenuTitle(@$option, @$task, @$Itemid);

                        // Add Google News number if set to
                        if ((@$view == 'article') && ($this->params->get('googlenewsnum', '0') != '0')) {
                            $i = count($title) - 1;
                            $title[$i] = $this->GoogleNews($title[$i], $id);
                        }
                    } else {
                        $title[] = JoomSEF::_getMenuTitle(@$option, @$task, @$Itemid);
                        //$title[] = JText::_('Submit');
                    }
                    
                    // Layout
                    $addLayout = $this->params->get('add_layout', '2');
                    if (isset($layout) && !empty($layout) && ($addLayout != '0')) {
                        if ($addLayout == '2') {
                            $defLayout = $this->params->get('def_layout', 'default');
                            if ($layout != $defLayout) {
                                $title[] = $layout;
                            }
                        }
                        else {
                            $title[] = $layout;
                        }
                    }
                    
                    if (isset($limitstart) && (!$sefConfig->appendNonSef || ($this->params->get('pagination', '0') == '0'))) {
                        if( @$view == 'article' ) {
                            // Multipage article
                            $page = $limitstart + 1;
                        }
                        else {
                            // Is limit set?
                            if( !isset($limit) ) {
                                // Try to get limit from menu parameters
                                $menu =& JSite::getMenu();
                                
                                if( !isset($Itemid) ) {
                                    // We need to find Itemid first
                                    $active =& $menu->getActive();
                                    $Itemid = $active->id;
                                }
                                
                                $menuParams =& $menu->getParams($Itemid);
                                $leading = $menuParams->get('num_leading_articles', 1);
                                $intro   = $menuParams->get('num_intro_articles', 4);
                                $limit = $leading + $intro;
                            }
                            $page = intval($limitstart / $limit)  + 1;
                        }
                        
                        $pagetext = strval($page);
                        if (($cnfPageText = $sefConfig->getPageText())) {
                            $pagetext = str_replace('%s', $page, $cnfPageText);
                        }
                        $title[] = $pagetext;
                    }

                    // show all
                    if (isset($showall) && ($showall == 1)) {
                        $title[] = JText::_('All Pages');
                    }

                    // print article
                    if (isset($print) && ($print == 1)) {                        
                        $title[] = JText::_('Print') . (!empty($page) ? '-'.($page+1) : '');
                    }
                }
            }
        }

        $newUri = $uri;
        if (count($title) > 0) {
            // Generate meta tags
            $metatags = $this->getMetaTags();
            if (isset($this->metatitle)) {
                $metatags['metatitle'] = $this->metatitle;
            }
            
            $nonSefVars = array();
            if ($sefConfig->appendNonSef && ($this->params->get('pagination', '0') != '0')) {
                if (isset($limit))      $nonSefVars['limit'] = $limit;
                if (isset($limitstart)) $nonSefVars['limitstart'] = $limitstart;
            }
            if( isset($filter) )    $nonSefVars['filter'] = $filter;

            $priority = $this->getPriority($uri);
            $newUri = JoomSEF::_sefGetLocation($uri, $title, null, null, null, @$lang, $nonSefVars, null, $metatags, $priority, true);
        }

        return $newUri;
    }

    function getPriority(&$uri)
    {
        $itemid = $uri->getVar('Itemid');
        $view = $uri->getVar('view');
        $layout = $uri->getVar('layout');
        
        switch($view)
        {
            case 'article':
                if( is_null($itemid) ) {
                    return _COM_SEF_PRIORITY_CONTENT_ARTICLE;
                } else {
                    return _COM_SEF_PRIORITY_CONTENT_ARTICLE_ITEMID;
                }
                break;
                
            case 'section':
                if( $layout == 'blog' ) {
                    if( is_null($itemid) ) {
                        return _COM_SEF_PRIORITY_CONTENT_SECTIONBLOG;
                    } else {
                        return _COM_SEF_PRIORITY_CONTENT_SECTIONBLOG_ITEMID;
                    }
                } else {
                    if( is_null($itemid) ) {
                        return _COM_SEF_PRIORITY_CONTENT_SECTIONLIST;
                    } else {
                        return _COM_SEF_PRIORITY_CONTENT_SECTIONLIST_ITEMID;
                    }
                }
                break;
                
            case 'category':
                if( $layout == 'blog' ) {
                    if( is_null($itemid) ) {
                        return _COM_SEF_PRIORITY_CONTENT_CATEGORYBLOG;
                    } else {
                        return _COM_SEF_PRIORITY_CONTENT_CATEGORYBLOG_ITEMID;
                    }
                } else {
                    if( is_null($itemid) ) {
                        return _COM_SEF_PRIORITY_CONTENT_CATEGORYLIST;
                    } else {
                        return _COM_SEF_PRIORITY_CONTENT_CATEGORYLIST_ITEMID;
                    }
                }
                break;
                
            default:
                return null;
                break;
        }
    }
}
?>
