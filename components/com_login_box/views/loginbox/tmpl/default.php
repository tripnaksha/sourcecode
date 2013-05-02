<?php
$usersConfig = &JComponentHelper::getParams('com_users');
$return = '';
?>

<table cellpadding="5" cellspacing="0" border="0" align="center" height="300">
   <tr valign="top">
<?php if (!JRequest::getVar('register_only')) : ?>
      <td>
<?php $this->display('login')?>
      </td>
<?php endif; ?>
<?php if (JRequest::getVar('register_only')) : ?>
      <td>
      <?php $this->display('register');?>
      </td>
<?php endif; ?>
<?php if (JRequest::getVar('remind_only')) : ?>
      <td>
      <?php $this->display('remind');?>
      </td>
<?php endif; ?>
<?php if (JRequest::getVar('reset_only')) : ?>
      <td>
      <?php $this->display('reset');?>
      </td>
<?php endif; ?>
   </tr>
</table>
<script type="text/javascript">

if ($('system-message')) {
   var LB_opa = 1;
   setTimeout(function() {
      var LB_timer = setInterval(function(){
         $('system-message').setOpacity(LB_opa);
         LB_opa -= 0.01;
         if (LB_opa <= 0) {
            clearInterval(LB_timer);
            $('system-message').remove();
         }
      }, 3);
   }, 1500);
}
</script>
