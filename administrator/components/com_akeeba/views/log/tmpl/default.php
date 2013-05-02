<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: default.php 201 2010-08-01 21:04:43Z nikosdion $
 * @since 1.3
 */

defined('_JEXEC') or die('Restricted access');
if(empty($this->tag)) $this->tag = null;
?>

<div class="activeprofile2 ui-widget-header ui-corner-all">
		<p>
			<?php echo JText::_('CPANEL_PROFILE_TITLE'); ?>: #<?php echo $this->profileid; ?>
			<?php echo $this->profilename; ?>
		</p>
</div>
<div style="clear:both;">&nbsp;</div>

<?php if(count($this->logs)): ?>
<form name="adminForm" action="index.php" method="post">
	<input name="option" value="com_akeeba" type="hidden" />
	<input name="view" value="log" type="hidden" />
	<fieldset>
		<label for="tag"><?php echo JText::_('LOG_CHOOSE_FILE_TITLE'); ?></label>
		<?php echo JHTML::_('select.genericlist', $this->logs, 'tag', 'onchange=submitform()', 'value', 'text', $this->tag, 'tag') ?>
	</fieldset>
</form>
<?php else: ?>
	<fieldset>
		<h2><?php echo JText::_('LOG_NONE_FOUND') ?></h2>
	</fieldset>
<?php endif; ?>

<?php if(!empty($this->tag)): ?>
<p style="font-size: large;">
	<blink style="color: red; font-weight: bold; font-size: x-large;">&rArr;</blink>
	<a href="<?php echo JURI::base(); ?>index.php?option=com_akeeba&view=log&task=download&format=raw&tag=<?php echo urlencode($this->tag); ?>" >
		<?php echo JText::_('LOG_LABEL_DOWNLOAD'); ?>
	</a>
	<blink style="color: red; font-weight: bold; font-size: x-large;">&lArr;</blink>
</p>
<iframe
	src="<?php echo JURI::base(); ?>index.php?option=com_akeeba&view=log&task=iframe&format=raw&tag=<?php echo urlencode($this->tag); ?>"
	width="90%" height="400px">
</iframe>
<?php endif; ?>