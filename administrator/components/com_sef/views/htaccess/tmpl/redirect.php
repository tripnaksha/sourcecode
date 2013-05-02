<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>
<script language="javascript">
<!--
function isValidURL(url)
{ 
    var RegExp = /^(http|https|ftp):\/\/(([\d\w]|%[a-fA-F\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/;
    if( RegExp.test(url) ) {
        return true;
    } else {
        return false;
    }
}

function submitbutton(pressbutton)
{
    var form = document.adminForm;
    if (pressbutton == 'cancel') {
        submitform( pressbutton );
        return;
    }
    
    // do field validation
    if( form.from.value == '' || form.to.value == '' ) {
        alert('<?php echo JText::_('Redirect from and Redirect to fields cannot be empty.'); ?>');
        return;
    }
    if( form.from.value[0] != '/' ) {
        alert('<?php echo JText::_('Redirect from field must start with a slash.'); ?>');
        return;
    }
    if( (form.to.value[0] != '/') && !isValidURL(form.to.value) ) {
        alert('<?php echo JText::_('Redirect to field must be a valid absolute URL.'); ?>');
        return;
    }
    
    submitform(pressbutton);
}
//-->
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<table class="adminform">
    <tr><th colspan="2"><?php echo JText::_('Redirect'); ?></th></tr>
	<tr>
		<td><?php echo JText::_('Redirect from'); ?></td>
		<td><input class="inputbox" type="text" size="100" name="from" value="<?php echo $this->redirect->from; ?>">
		<?php echo JHTML::_('tooltip', JText::_('TT_HTACCESS_FROM')); ?>
		</td>
	</tr>
	<tr>
		<td><?php echo JText::_('Redirect to');?></td>
		<td align="left"><input class="inputbox" type="text" size="100" name="to" value="<?php echo $this->redirect->to; ?>">
		<?php echo JHTML::_('tooltip', JText::_('TT_HTACCESS_TO'));?>
		</td>
	</tr>
</table>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="htaccess" />
<input type="hidden" name="id" value="<?php echo $this->redirect->id; ?>" />
</form>
