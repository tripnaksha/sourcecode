<?php defined('_JEXEC') or die('Restricted access');
$term=$this->term;
$editor =& JFactory::getEditor();
?>

<form action="index2.php" method="post" name="adminForm" id="adminForm"
	class="adminForm">
<table>
	<tr>
		<td><?php echo JText::_('TERM NAME');?>:</td>
		<td><input class="inputbox" type="text" size="30" maxlength="100"
			name="name" value="<?php echo $term->name;?>"></td>
		<td width="20">&nbsp;</td>

		<td><?php echo JText::_('WEIGHT');?>:</td>
		<td><input class="inputbox" type="text" size="10" maxlength="10"
			name="weight" value="<?php echo $term->weight;?>"></td>
	</tr>
	<tr>
		<td colspan="5"><?php echo JText::_('TERM DESCRIPTION');?>:</td>
	</tr>
	<tr>
		<td colspan="5"><textarea id="description" name="description" rows="5"
			cols="60"><?php echo $term->description;?></textarea></td>
	</tr>

</table>

<input type="hidden" name="controller" value="term" /> <input
	type="hidden" name="task" value="save"> <input type="hidden" name="id"
	value="<?php  echo  $term->id;?>"><input type="hidden" name="cid[]"
	value="<?php  echo  $term->id;?>"> <input type="hidden" name="option"
	value="com_tag"> <?php echo JHTML::_( 'form.token' ); ?></form>
