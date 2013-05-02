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
defined('_JEXEC') or die('Restricted access');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">

<label for="filetext"><?php echo JText::_('Edit your .htaccess file'); ?></label>
<br />
<textarea name="filetext" id="filetext" class="inputbox" cols="120" rows="40"><?php echo $this->file; ?></textarea>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="htaccess" />
</form>
