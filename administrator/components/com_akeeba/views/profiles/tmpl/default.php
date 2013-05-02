<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: default.php 201 2010-08-01 21:04:43Z nikosdion $
 * @since 1.3
 */

defined('_JEXEC') or die('Restricted access');

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_akeeba" />
	<input type="hidden" name="view" value="profiles" />
	<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
	<input type="hidden" name="task" id="task" value="" />
	<table class="adminlist">
		<thead>
			<tr>
				<th width="20px">&nbsp;</th>
				<th width="20px">#</th>
				<th><?php JText::_('PROFILE_COLLABEL_DESCRIPTION'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$i = 1;
		foreach( $this->profiles as $profile ):
		$id = JHTML::_('grid.id', ++$i, $profile->id);
		$link = 'index.php?option='.JRequest::getCmd('option').'&amp;view='.JRequest::getCmd('view').'&amp;task=edit&amp;id='.$profile->id.'&amp;layout=default_edit';
		$i = 1 - $i;
		?>
			<tr class="row<?php echo $i; ?>">
				<td><?php echo $id; ?></td>
				<td><?php echo $profile->id ?></td>
				<td>
					<a href="<?php echo $link; ?>">
						<?php echo $profile->description; ?>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</form>