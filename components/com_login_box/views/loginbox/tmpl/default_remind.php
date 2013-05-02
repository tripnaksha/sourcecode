<?php # Modifications for multi language Marek T.Purzyñski 2008.03.10?>
<h3><?php echo JText::_('TEXT_REMIND')?></h3>
<form method="POST" action="<?php echo JRoute::_( 'index.php?option=com_login_box&task=remindusername' ); ?>" target="_self">

	<div class="fields">
	      <?php echo ('Please enter the e-mail address<br />');echo JText::_(' associated with your User<br />'); 
	      echo JText::_(' account. Your username will <br />');echo JText::_('be e-mailed to the e-mail address<br />');
	      echo JText::_(' on file.');?>
	</div>

	<div class="fields">
	    <label><?php echo JText::_('Email Address')?></label>
	    <br>
	    <input name="email" class="required validate-email" value="<?php echo htmlspecialchars($data['email'])?>" >
	</div>

	<div class="fields">
	    <label><?php echo "Sum of the numbers "; echo $this->nums['num1'] . " and " . $this->nums['num2'] . " is :";?></label>
	    <br>
	    <input name="sum" type="text">
	</div>

	<input type="hidden" name="summing" value="<?php echo $this->nums['num1'] + $this->nums['num2']; ?>" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
	<input type="submit" class="submit" value="<?php echo JText::_('TEXT_SUBMIT')?>">
</form>
<br>
