<?php defined('_JEXEC') or die('Restricted access');
?>

<form action="index2.php?controller=css&option=com_tag" method="post"
	name="adminForm" id="adminForm" class="adminForm">
<table border="0" cellpadding="1" cellspacing="1" width="100%">
	<tr>
		<td width="220"><span class="componentheading"><?php echo JText::_('TAG CSS FILE');?>: <?php if($this->isCssWritable){?>
		<strong style="color: green;"><?php echo JText::_('WRITEABLE');?></strong> <?php }else{?> <strong
			style="color: red"><?php echo JText::_('UNWRITEABLE');?></strong> <?php };?> </span></td>
	</tr>
	<tr>
		<td>
		<table class="adminform">
			<tbody>
				<tr>
					<th><?php echo($this->cssFileName);?></th>
				</tr>
				<tr>
					<td><textarea <?php if(!$this->isCssWritable){ echo('readonly');};?> style="width: 100%; height: 600px;" cols="80"
						rows="25" name="csscontent"  class="inputbox">
<?php echo($this->cssFileContent);?>
				</textarea>
				
				</tr>
				<tr>
		
		</table>

		</td>
	</tr>

</table>

<input type="hidden" name="task" value="save"> <input type="hidden"
	name="controller" value="css"> <input type="hidden" name="option"
	value="<?php echo $option; ?>"> <?php echo JHTML::_( 'form.token' ); ?>

</form>
