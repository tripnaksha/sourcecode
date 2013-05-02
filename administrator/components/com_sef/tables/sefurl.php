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


class TableSEFUrl extends JTable
{
    /** @var int */
    var $id = null;
    /** @var int */
    var $cpt = null;
    /** @var string */
    var $sefurl = null;
    /** @var string */
    var $origurl = null;
    /** @var int */
    var $Itemid = null;
    /** @var string */
    var $metadesc = null;
    /** @var string */
    var $metakey = null;
    /** @var string */
    var $metatitle = null;
    /** @var string */
    var $metalang = null;
    /** @var string */
    var $metarobots = null;
    /** @var string */
    var $metagoogle = null;
    /** @var string */
    var $canonicallink = null;
    /** @var date */
    var $dateadd = null;
    /** @var priority */
    var $priority = null;

    /**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
    function TableSEFUrl(& $db)
    {
        parent::__construct('#__sefurls', 'id', $db);
    }

    function check()
    {
        //initialize
        $this->_error = null;
        $this->sefurl = trim($this->sefurl);
        $this->origurl = trim($this->origurl);
        $this->metadesc = trim($this->metadesc);
        $this->metakey = trim($this->metakey);
        // check for valid URLs
        if ($this->origurl == '') {
            $this->_error .= JText::_('ERROR_EMPTY_URL');
            return false;
        }
        //if (eregi("^\\/", $this->sefurl)) {
        if (preg_match("|^\\/|i", $this->sefurl)) {
            $this->_error .= JText::_('There should be NO LEADING SLASH on the New SEF URL.');
        }
        //if ((eregi("^index.php", $this->origurl)) === false) {
        if (!preg_match("/^index.php/i", $this->origurl)) {
            $this->_error .= JText::_('The Old Non-SEF Url must begin with index.php');
        }
        if (is_null($this->_error)) {
            // check for existing URLS
            $this->_db->setQuery("SELECT id FROM #__sefurls WHERE `sefurl` LIKE " . $this->_db->Quote($this->sefurl) . " AND `origurl` LIKE " . $this->_db->Quote($this->origurl));
            $xid = intval($this->_db->loadResult());
            if ($xid && $xid != intval($this->id)) {
                $this->_error = JText::_('This URL already exists in the database!');
                return false;
            }
            
            return true;
        } else {
            return false;
        }
    }
}
?>