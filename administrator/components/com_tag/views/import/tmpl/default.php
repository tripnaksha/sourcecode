<?php defined('_JEXEC') or die('Restricted access');
?>

<form action="index2.php?controller=import&option=com_tag" method="post"
	name="adminForm" id="adminForm" class="adminForm">
<table class="admintable">
	<tr>
		<td class="key"><label for="source"><?php echo JText::_('SOURCE');?>:</label></td>

		<td ><select name="source" id="section" class="inputbox"
			size="1">
			<option value="meta-keys"><?php echo JText::_('META KEYWORDS');?></option>
			<option value="jtags">JTags</option>		
		</select></td>
		
	</tr>
	<tr><td>&nbsp;</td><td><input
	type="submit" name="Import" value="<?php echo JText::_('IMPORT');?>" class="inputbox"></td></tr>

</table>
<input type="hidden" name="task" value="import"> <input
	type="hidden" name="controller" value="import"> <input type="hidden"
	name="option" value="<?php echo $option; ?>"> <?php echo JHTML::_( 'form.token' ); ?>

</form>
