<?php
$usersConfig = &JComponentHelper::getParams('com_users');
$useractivation = $usersConfig->get('useractivation');
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0"  height="300">
   <tr valign="middle">
      <td>
	<h3 style="font-size: 12px; text-align: center;">
	<?php if ($useractivation):?>
	<?php echo JText::_('TEXT_SUCCESS_REGISTERED')?>
	<br>
	<a href="javascript: void(0);" onclick="window.parent.location.reload(true); return false;"><?php echo JText::_('TEXT_CLOSE_BOX')?></a>
	   <?php else:?>
	<?php echo JText::_('TEXT_REGISTRATION_COMPLETE')?>
	<br>
	<a href="<?php echo JRoute::_('index.php?option=com_login_box&login_only=1')?>"><?php echo JText::_('TEXT_CLIK_TO_LOGIN')?></a>
	<?php endif;?>
	</h3>
      </td>
   </tr>
</table>
<script type="text/javascript">
window.addEvent('domready', function() {
   setTimeout(function(){
      window.parent.location.reload(true);
   }, 50);
});
</script>
