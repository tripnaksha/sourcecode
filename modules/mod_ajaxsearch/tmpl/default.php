<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<form class="ajaxsearch" method=post action="index.php">

	<input type="text" size="30" id="inputString" name="searchword" class="inputbox" onkeyup="lookup(this.value,<?php echo $module->id ?>,'<?php echo $order_by_ajax ?>');" value="<?php echo $text_ajax ?>" onblur="if (this.value=='') {this.value='<?php echo $text_ajax ?>'}" onfocus="if (this.value=='<?php echo $text_ajax ?>') {this.value=''}" autocomplete="off" />
    <div id="loading" style="display: none;"></div>
    <input type="submit" class="btn" value="Go">

    <input id="loading-not" value="" type="reset" onclick="hide()"/>
	<div id="suggestions"></div>
<input type="hidden" name="option" value="com_search" />
</form>