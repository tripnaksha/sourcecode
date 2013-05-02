<script type="text/javascript">
window.onload = function () {
	document.getElementById('username').focus();
}
function checkGmail() {
	var text = document.getElementById('username').value;
	if(text.indexOf("gmail") != -1 || text.indexOf("googlemail") != -1 )
	{
	   document.getElementById('nickdiv').style.visibility = "visible";
	   document.getElementById('nickdiv').style.height = "auto";
	}
	else
	{
	   document.getElementById('nickdiv').style.visibility = "hidden";
	   document.getElementById('nickdiv').style.height = "0px";
	}
}
</script>
<?php # Modifications for multi language Marek T.Purzy&#241;ski 2008.03.10?>
<h3><?php echo JText::_('TEXT_LOGIN')?></h3>
<form method="POST" action="<?php echo JRoute::_( 'index.php?option=com_login_box&task=login' ); ?>" target="_self">
   
   <label style="font-size:10px;text-decoration:bold;"><?php if (JRequest::getVar('clickbtn')) {echo "You need to login before uploading files.<br />";} echo "<font color='red'>Tip:</font> You can use your regular Gmail email/pwd!";
   ?><br /><br /></label>
   <div class="fields">
      <label><?php echo JText::_('TEXT_USERNAME')?></label>
<br>
      <input id="username" name="username" onKeyUp="checkGmail();">
   </div>

   <div class="fields">
      <label><?php echo JText::_('TEXT_PASS')?></label>
<br>
      <input name="passwd" type="password">      
   </div>

   <div id="nickdiv" class="fields" style="visibility:hidden; height:0px;">
      <label style="font-size:10px"><?php echo "Choose a nickname which can be <br />shown instead of your email address";?></label>
      <br>
      <input id="nickname" name="nickname" type="text">
   </div>

   <!--div class="fields">
      <label><?php echo "Sum of the numbers "; echo $this->nums['num1'] . " and " . $this->nums['num2'] . " is :"; ?></label>
      <br>
      <input name="sum" type="text">
   </div-->
   <?php global $mainframe; 
     //set the argument below to true if you need to show vertically( 3 cells one below the other) 
     $mainframe->triggerEvent('onShowOSOLCaptcha', array(true));
   ?>

   <div class="fields">
<input  name="remember" type="checkbox" value="yes">      <label><?php echo JText::_('TEXT_REMEMBERME')?></label>
   </div>

	<input type="hidden" name="summing" value="<?php echo $this->nums['num1'] + $this->nums['num2']; ?>" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
	<input type="hidden" name="clickbtn" value="<?php echo JRequest::getVar('clickbtn');?>" />
	<input type="submit" class="submit" value="<?php echo JText::_('TEXT_LOGIN')?>!">
</form>
<a style="font-size: 11px;" href="<?php echo JRoute::_( 'index.php?option=com_user&view=reset' ); ?>"  target="_top">
   <?php echo JText::_('TEXT_FORGOT_PASS'); ?>
</a>
<br>
<a style="font-size: 11px;"  href="<?php echo JRoute::_( 'index.php?option=com_user&view=remind' ); ?>" target="_top">
   <?php echo JText::_('TEXT_FORGOT_USERNAME'); ?>
</a>
