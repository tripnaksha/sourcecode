<table width="100%" cellpadding="0" cellspacing="0" border="0"  height="300">
   <tr valign="middle">
      <td>
	<h3 style="text-align: center;">
	<?php echo JText::_('TEXT_SUCCESS_LOGGEDIN')?>
	</h3>
      </td>
   </tr>
</table>
<script type="text/javascript">
window.addEvent('domready', function() {
   if('<?php echo $this->clickbtn;?>'=='fup')
   {
	window.location.href = 'index.php?option=com_content&view=article&id=207&tmpl=component';
   }
   else
     setTimeout(function(){
//      window.parent.location.href = 'index.php?option=com_traildisplay&Itemid=1';
        var currentURL = window.parent.location.href;
        if (currentURL.indexOf("view=article&id=162") != -1)
           window.parent.location.href = 'index.php?option=com_traildisplay&Itemid=1';
        else
           window.parent.location.reload(true);
     }, 50);
});
</script>
