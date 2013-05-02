<table width="100%" cellpadding="0" cellspacing="0" border="0"  height="300">
   <tr valign="middle">
      <td>
	<h3 style="text-align: center;">
	<?php echo JText::_('TEXT_ALREADY_LOGGEDIN')?>
	</h3>
      </td>
   </tr>
</table>
<script type="text/javascript">
window.addEvent('domready', function() {
window.location.href = 'index.php?option=com_content&view=article&id=207&tmpl=component';
   setTimeout(function(){
      window.parent.location.reload(true);
   }, 50);
});
</script>
