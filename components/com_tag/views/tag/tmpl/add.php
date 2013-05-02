<?php defined('_JEXEC') or die('Restricted access');

?>

<div align="center"><h1 ><?php if(isset($this->tags)&&!empty($this->tags)){echo( JText::_('EDIT TAGS'));}else{echo( JText::_('ADD TAGS'));}?></h1></div>
<form action="index.php" method="post"
	name="addTags" id="addTags">
<table align="center">
	<tbody>
	<tr>
	<td><div align="center"><textarea id="tags" name="tags" rows="5" cols="60"><?php echo($this->tags);?></textarea></div></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>
	<div align="center">	
	<input type="submit" value="<?php echo JText::_('SAVE');?>"  />
	 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	 <input type="button" name="cancel" value="<?php echo JText::_('CANCEL'); ?>"
                 onClick="window.parent.document.getElementById('sbox-window').close();" />
	</div>
	</td></tr>
	
	</tbody>
</table>
<input type="hidden" name="cid" value="<?php echo JRequest::getString('article_id')?>"/>
<input type="hidden" name="refresh" value="<?php echo JRequest::getString('refresh')?>"></input><input type="hidden"
	name="task" value="save"> <input type="hidden" name="option"
	value="com_tag"> <?php echo JHTML::_( 'form.token' ); ?>
</form>