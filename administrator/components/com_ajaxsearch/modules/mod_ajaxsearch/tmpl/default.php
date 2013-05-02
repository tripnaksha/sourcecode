<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<form class="ajaxsearch">

	<input type="text" size="30" id="inputString" class="inputbox" onkeyup="lookup(this.value,<?php echo $module->id ?>,'<?php echo $order_by_ajax ?>');" value="<?php echo $text_ajax ?>" onblur="if (this.value=='') {this.value='<?php echo $text_ajax ?>'}" onfocus="if (this.value=='<?php echo $text_ajax ?>') {this.value=''}" autocomplete="off" />
    <div id="loading" style="display: none;"></div>
    <input id="loading-not" value="" type="reset" onclick="hide()"/>
	<div id="suggestions"></div>
</form>