<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<script language="javascript" type="text/javascript">
<!--
function setgood() {
	// TODO: Put setGood back
	return true;
}

var sectioncategories = new Array;
<?php
$i = 0;
//foreach ($this->lists['sectioncategories'] as $k=>$items) {
//	foreach ($items as $v) {
//		echo "sectioncategories[".$i++."] = new Array( '$k','".addslashes( $v->id )."','".addslashes( $v->title )."' );\n\t\t";
//	}
//}
?>


function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}
/*	try {
		form.onsubmit();
	} catch(e) {
		alert(e);
	}
*/
	// do field validation
	var text = <?php echo $this->editor->getContent( 'text' ); ?>
	if (form.title.value == '') {
		return alert ( "<?php echo JText::_( 'Article must have a title', true ); ?>" );
	} else if (text == '') {
		return alert ( "<?php echo JText::_( 'Article must have some text', true ); ?>");
	} else if (parseInt('<?php echo $this->article->sectionid;?>')) {
		// for articles
		if (form.catid && getSelectedValue('adminForm','catid') < 1) {
			return alert ( "<?php echo JText::_( 'Please select a category', true ); ?>" );
		}
	}
	<?php echo $this->editor->save( 'text' ); ?>
	submitform(pressbutton);
}
//-->
</script>
<form action="<?php echo $this->action ?>" method="post" name="adminForm">
<fieldset>
<legend><?php echo JText::_('Editor'); ?></legend>
<table>
<tr>
	<td class="key">
		<label for="sectionid">
			<?php echo JText::_( 'Section' ); ?>:
		</label>
	</td>
	<td>
		<?php echo $this->lists['sectionid']; ?>
	</td>
	<td class="key">
		<label for="catid">
			<?php echo JText::_( 'Category' ); ?>:
		</label>
	</td>
	<td>
		<?php echo $this->lists['catid']; ?>
	</td>
	<td class="key">
		<label id="ltrail" for="listid">
			<?php if ($this->lists['listid']) echo JText::_( 'Trail:' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->lists['listid']; ?>
	</td>
</tr>
<?php if ($this->lists['listid']) {?>
<tr>
	<td class="key">
		<label id="ldifficulty" for="difficulty">
			<?php echo JText::_( 'Difficulty:' ); ?>
		</label>
	</td>
	<td>
		<select id="difficulty" name="difficulty" class="inputbox">
			<option value="1">Easy</option>
			<option selected value="2">Moderate</option>
			<option value="3">Challenging</option>
		</select>
	</td>
	<td class="key">
		<label id="ltime" for="time">
			<?php echo JText::_( 'Time taken:' ); ?>
		</label>
	</td>
	<td>
		<select id="time" name="time" class="inputbox">
			<option value="1">< 5hrs</option>
			<option selected value="2">5 to 10hrs</option>
			<option value="3">> 10hrs</option>
		</select>
	</td>
	<td class="key">
		<label id="lequipment" for="equipment">
			<?php echo JText::_( 'Gear used:' ); ?>
		</label>
	</td>
	<td>
		<input type="text" id="equipment" rows="1" cols="200" style="width:225px; height:20px" class="inputbox" id="metakey" name="metakey"><?php echo str_replace('&','&amp;',$this->article->metakey); ?></input>
	</td>
</tr>
<?php } ?>
</table>
<table class="adminform" width="100%">
<tr>
	<td>
		<div style="float: left;">
			<label for="title">
				<?php echo JText::_( 'Title' ); ?>:
			</label>
			<input class="inputbox" type="text" id="title" name="title" size="50" maxlength="100" value="<?php echo $this->escape($this->article->title); ?>" />
		</div>
	</td>
</tr>
</table>

<?php
echo $this->editor->display('text', $this->article->text, '100%', '400', '70', '15');
?>
</fieldset>
<!--fieldset-->
<legend><?php //echo JText::_('Publishing'); ?></legend>
<table class="adminform">
<?php if ($this->user->authorize('com_content', 'publish', 'content', 'all')) : ?>
<tr>
	<td class="key">
		<label for="state">
			<?php echo JText::_( 'Published' ); ?>:
		</label>
	</td>
	<td>
		<?php echo $this->lists['state']; ?>
	</td>
</tr>
<?php endif; ?>
<!--Commented - Ajay - 02/02/09 - do not show front page option and author alias>
<tr>
	<td width="120" class="key">
		<label for="frontpage">
			<?php echo JText::_( 'Show on Front Page' ); ?>:
		</label>
	</td>
	<td>
		<?php echo $this->lists['frontpage']; ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="created_by_alias">
			<?php echo JText::_( 'Author Alias' ); ?>:
		</label>
	</td>
	<td>
		<input type="text" id="created_by_alias" name="created_by_alias" size="50" maxlength="100" value="<?php echo $this->article->created_by_alias; ?>" class="inputbox" />
	</td>
</tr>
-->
<!--Commented - Ajay - 02/02/09 - Only hiding the labels and input boxes, as complete commenting is throwing an error>
<tr>
	<td class="key">
		<label for="publish_up">
			<?php //echo JText::_( 'Start Publishing' ); ?>:
		</label>
	</td>
	<td>
	    <?php //echo JHTML::_('calendar', $this->article->publish_up, 'publish_up', 'publish_up', '%Y-%m-%d %H:%M:%S', array('class'=>'inputbox', 'size'=>'25',  'maxlength'=>'19')); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="publish_down">
			<?php echo JText::_( 'Finish Publishing' ); ?>:
		</label>
	</td>
	<td>
	    <?php //echo JHTML::_('calendar', $this->article->publish_down, 'publish_down', 'publish_down', '%Y-%m-%d %H:%M:%S', array('class'=>'inputbox', 'size'=>'25',  'maxlength'=>'19')); ?>
	</td>
</tr>
<!--Commented - Ajay - 20/01/09 - do not give option to select access level>
<tr>
	<td valign="top" class="key">
		<label for="access">
			<?php //echo JText::_( 'Access Level' ); ?>
		</label>
	</td>
	<td>
		<?php //echo $this->lists['access']; ?>
	</td>
</tr>

<!--Commented - Ajay - 02/02/09 - do not show ordering detail
<tr>
	<td class="key">
		<label for="ordering">
			<?php echo JText::_( 'Ordering' ); ?>:
		</label>
	</td>
	<td>
		<?php echo $this->lists['ordering']; ?>
	</td>
</tr>
-->
</table>
<!--/fieldset-->

<fieldset>
<legend><?php //echo JText::_('Metadata'); ?></legend>
<table class="adminform">
<tr>
	<td  valign="top" class="key">
		<label for="metakey">
			<?php //echo JText::_( 'Keywords' ); ?><b>Tag this article </b>:
		</label>
	</td>
	<td>
		<textarea rows="5" cols="50" style="width:500px; height:25px" class="inputbox" id="metakey" name="metakey"><?php echo str_replace('&','&amp;',$this->article->metakey); ?></textarea>
	</td>
</tr>
</table>
</fieldset>
<div>
	<button type="button" onclick="submitbutton('save')">
		<?php echo JText::_('Save') ?>
	</button>
	<button type="button" onclick="submitbutton('cancel')">
		<?php echo JText::_('Cancel') ?>
	</button>
</div>

<input type="hidden" name="option" value="com_content" />
<input type="hidden" name="id" value="<?php echo $this->article->id; ?>" />
<input type="hidden" name="version" value="<?php echo $this->article->version; ?>" />
<input type="hidden" name="created_by" value="<?php echo $this->article->created_by; ?>" />
<input type="hidden" name="referer" value="<?php echo @$_SERVER['HTTP_REFERER']; ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="task" value="" />
</form>
<?php echo JHTML::_('behavior.keepalive'); ?>
