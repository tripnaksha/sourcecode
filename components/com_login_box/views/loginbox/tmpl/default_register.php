<script type="text/javascript">
window.onload = function () {
	document.getElementById('name').focus();
}
function passCheck(){
	var password = document.getElementById('password');
	var pmessage = document.getElementById('pmessage');
	if (password.value.length < 6)
	{
//	   pmessage.innerHTML = "<div style='color: red'>Password should have at </div>";
	   pmessage.innerHTML = "<div style='color: red'>At least 6 characters please</div>";	   
	}
	else
	   pmessage.innerHTML = "";
}
</script>
<?php
if (JRequest::getVar('go')) {
   $data = JRequest::get('post');
} else {
   $data = array();
   $data['name'] = '';
   $data['username'] = '';
   $data['email'] = '';
}
?>
<h3><?php echo JText::_('TEXT_REGISTER')?></h3>
<form method="POST" action="<?php echo JRoute::_( 'index.php?option=com_login_box&task=register' ); ?>" target="_self">
   
   <div class="fields">
      <label><?php echo JText::_('TEXT_SURENAME')?></label>
<br>
      <input id="name" name="name" value="<?php echo htmlspecialchars($data['name'])?>">      
   </div>
   
   <!--div class="fields">
      <label><?php echo JText::_('TEXT_USERNAME')?></label>
<br>
      <input name="username"  value="<?php echo htmlspecialchars($data['username'])?>">
   </div-->
   
   <div class="fields">
      <label><?php echo JText::_('TEXT_EMAIL') . " (this will be your username)"?></label>
<br>
      <input name="email"  value="<?php echo htmlspecialchars($data['email'])?>">      
   </div>

   <div class="fields">
      <label><?php echo JText::_('TEXT_PASS')?></label>
<br>
      <input id="password" name="password" type="password" onKeyDown="passCheck()">
   </div>
   <div id="pmessage">
   </div>

   <div class="fields">
      <label><?php echo JText::_('TEXT_CONFIRMPASS')?></label>
<br>
      <input name="password2" type="password">
   </div>

   <?php global $mainframe; 
     //set the argument below to true if you need to show vertically( 3 cells one below the other) 
     $mainframe->triggerEvent('onShowOSOLCaptcha', array(true));
   ?>
   <!--div class="fields"-->
      <!--label><?php echo "Sum of the numbers "; echo $this->nums['num1'] . " and " . $this->nums['num2'] . " is :"; ?></label>
      <br>
      <input name="sum" type="text">
   </div-->

	<input type="hidden" name="summing" value="<?php echo $this->nums['num1'] + $this->nums['num2']; ?>" />
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
	<input type="submit" class="submit" value="<?php echo JText::_('TEXT_REGISTER')?>" name="go">
</form>
