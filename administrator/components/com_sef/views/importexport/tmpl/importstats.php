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
<legend><?php echo JText::_('Import Report'); ?></legend>
<table class="adminform">
<tr>
    <th colspan="2">
        <?php if( $this->success ) {
            echo '<span style="color: green">' . JText::_('Import Successful') . '</span>';
        } else {
            echo '<span style="color: red">' . JText::_('There were some errors during import') . '</span>';
        }
        ?>
    </th>
</tr>
<tr>
    <td width="200"><?php echo JText::_('File format'); ?>:</td>
    <td><?php echo $this->filetype; ?></td>
</tr>
<tr>
    <td><?php echo JText::_('Parsed lines'); ?>:</td>
    <td><?php echo $this->total; ?></td>
</tr>
<tr>
    <td><?php echo JText::_('Successfully Imported'); ?>:</td>
    <td><?php echo $this->imported; ?></td>
</tr>
<tr>
    <td><?php echo JText::_('Not Imported'); ?>:</td>
    <td><?php echo $this->notImported; ?></td>
</tr>
<tr>
    <td colspan="2"><input type="button" value="<?php echo JText::_('Ok'); ?>" onclick="submitbutton('back');" /></td>
</tr>
</table>
</fieldset>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="sefurls" />
</form>
