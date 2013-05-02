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


class TableMovedUrl extends JTable
{
    var $id;
    var $old;
    var $new;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function TableMovedUrl(& $db) {
        parent::__construct('#__sefmoved', 'id', $db);
    }

}
?>
