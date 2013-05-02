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

<form action="index.php" method="post" name="adminForm">
<fieldset class="adminform">
<legend><?php echo JText::_('Update Report'); ?></legend>
<table class="adminform">
<tr>
    <th colspan="2">
        <?php if( $this->success ) {
            echo '<span style="color: green">' . JText::_('Update Successful') . '</span>';
        } else {
            echo '<span style="color: red">' . JText::_('There are no URLs to be updated') . '</span>';
        }
        ?>
    </th>
</tr>
<tr>
    <td width="100"><?php echo JText::_('URLs updated'); ?>:</td>
    <td><?php echo $this->total; ?></td>
</tr>
<tr>
    <td colspan="2"><input type="button" value="<?php echo JText::_('Ok'); ?>" onclick="submitbutton('back');" /></td>
</tr>
</table>
</fieldset>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
</form>
