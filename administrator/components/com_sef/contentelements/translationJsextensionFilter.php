<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Don't allow direct linking
defined( 'JPATH_BASE' ) or die( 'Direct Access to this location is not allowed.' );

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sef'.DS.'classes'.DS.'seftools.php');

class translationJsextensionFilter extends translationFilter
{
    function translationJsextensionFilter ($contentElement){
        $this->filterNullValue = 'all';
        $this->filterType = 'jsextension';
        $this->filterField = $contentElement->getFilter('jsextension');
        parent::translationFilter($contentElement);
    }

    function GetExts() {
        static $exts;

        if( !isset($exts) ) {
            $exts = array();

            $db =& JFactory::getDBO();
            $query = "SELECT DISTINCT `extension` FROM `#__sefexttexts`";
            $db->setQuery($query);
            $extensions = $db->loadResultArray();

            if( count($extensions) > 0 ) {
                foreach($extensions as $ext) {
                    $exts[$ext] = SEFTools::getExtName($ext);
                }
            }
        }

        return $exts;
    }

    function _createFilter(){
        if (!$this->filterField ) return "";
        
        $filter = "";
        if( $this->filter_value != $this->filterNullValue ) {
            $filter = "`extension` = '$this->filter_value'";
        }
        
        return $filter;
    }


    /**
     * Creates filter 
     *
     * @param unknown_type $filtertype
     * @param unknown_type $contentElement
     * @return unknown
     */
    function _createfilterHTML(){
        if (!$this->filterField) return "";

        // get list of extensions using texts
        $exts = $this->GetExts();

        $opts = array();
        $opts[] = JHTML::_( 'select.option', 'all', 'All' );
        if( count($exts) > 0 ) {
            foreach($exts as $ext => $name) {
                $opts[] = JHTML::_( 'select.option', $ext, $name );
            }
        }

        $list = array();
        $list['title'] = 'Extension:';
        $list['html'] = JHTML::_( 'select.genericlist', $opts, 'jsextension_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);

        return $list;

    }

}

?>
