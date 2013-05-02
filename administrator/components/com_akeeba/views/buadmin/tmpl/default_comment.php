<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: default_comment.php 201 2010-08-01 21:04:43Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

$editor =& JFactory::getEditor();
$getText = $editor->getContent('comment');

?>
<div id="jpcontainer">

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="option" value="com_akeeba" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="buadmin" />
	<input type="hidden" name="id" value="<?php echo $this->record['id'] ?>" />

	<table class="adminlist">
		<tr>
			<td><?php echo JText::_('STATS_LABEL_DESCRIPTION'); ?></td>
			<td><input type="text" name="description" maxlength="255" size="50"
				value="<?php echo $this->record['description'] ?>" />
				</td>
		</tr>
		<tr>
			<td><?php echo JText::_('STATS_LABEL_COMMENT'); ?></td>
			<td><?php echo $editor->display( 'comment',  $this->record['comment'], '550', '200', '60', '20', array() ) ; ?>
		</tr>
	</table>
</form>
</div>
