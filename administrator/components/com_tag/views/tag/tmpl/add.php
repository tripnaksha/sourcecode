<?php defined('_JEXEC') or die('Restricted access');

?>

<h1>Joomla tags</h1>
<form action="index2.php" method="post"
	name="adminForm" id="adminForm" class="adminForm">
<table class="adminlist">
	<thead>
		<tr>			
			<th ><?php echo JText::_('TAGS');?></th>
		</tr>
	</thead>

	<tbody>
	<tr>
	<td><textarea id="tags" name="tags" rows="5" cols="60"><?php echo($this->tags);?></textarea></td>
	</tr>
	<tr><td><input type="submit" value="<?php echo JText::_('SAVE');?>"  />
	 <input type="button" name="cancel" value="<?php echo JText::_('CANCEL'); ?>"
                 onClick="window.parent.document.getElementById('sbox-window').close();" />
	
	</td></tr>
	
	</tbody>
</table>
<input type="hidden" name="cid" value="<?php echo JRequest::getString('article_id')?>"/>
<input type="hidden" name="boxchecked" value="0" /> 
<input type="hidden" name="task" value="save"> 
<input type="hidden" name="controller" value="tag"> 
<input type="hidden" name="option" value="com_tag"> <?php echo JHTML::_( 'form.token' ); ?>
</form>