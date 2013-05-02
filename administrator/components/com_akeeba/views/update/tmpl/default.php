<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: default.php 251 2010-09-17 09:51:27Z nikosdion $
 * @since 2.2
 */

defined('_JEXEC') or die('Restricted access');

if(!$this->updates->supported)
{
	$icon_class = 'ak-icon-warning';
	$overview_class = 'notok';
	$mode = 'unsupported';
}
elseif( $this->updates->update_available )
{
	$icon_class = 'ak-icon-update';
	$overview_class = 'statuswarning';
	$mode = 'update';
}
else
{
	$icon_class = 'ak-icon-ok';
	$overview_class = 'ok';
	$mode = 'ok';
}

?>

	<?php if( (AKEEBA_PRO) && empty($this->updates->package_url_suffix) && $this->updates->supported ): ?>
	<div class="ui-state-error ui-corner-all notok updaterwarning">
		<?php echo JText::_('UPDATE_ERROR_USERNAMEPASSREQUIRED'); ?>
	</div>
	<?php endif; ?>

	<div id="akeeba-update-results" class="<?php echo $overview_class; ?> ui-corner-all">
		<div id="update-image" class="ak-icon <?php echo $icon_class; ?>"></div>
		<h2>
		<?php switch($mode):
				case 'ok': ?>
			<?php echo JText::_('UPDATE_LABEL_NOUPGRADESFOUND') ?>
		<?php	break;
				case 'update': ?>
			<?php echo JText::_('UPDATE_LABEL_UPGRADEFOUND') ?>
		<?php	break;
				default: ?>
			<?php echo JText::_('UPDATE_LABEL_NOTAVAILABLE') ?>
		<?php endswitch; ?>
		</h2>
	</div>

	<?php if($mode != 'unsupported'): ?>
	<table id="version_info_table" class="ui-corner-all">
		<tr>
			<td class="label"><?php echo JText::_('UPDATE_LABEL_EDITION') ?></td>
			<td colspan="3">
				<?php if(AKEEBA_PRO): ?>
					Akeeba Backup Professional for Joomla!
				<?php else: ?>
					Akeeba Backup Core for Joomla!
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td class="label"><?php echo JText::_('UPDATE_LABEL_YOURVERSION') ?></td>
			<td>
				<span class="version"><?php echo $this->updates->current_version ?></span>
				<span class="version-status">
					(<?php echo JText::_('UPDATE_STATUS_'.strtoupper($this->updates->current_status)); ?>)
				</span>
			</td>
			<td colspan="2">
				<?php echo JText::_('UPDATE_LABEL_RELEASEDON') ?>
				<span class="reldate"><?php echo $this->updates->current_date ?></span>
			</td>
		</tr>
		<tr>
			<td class="label"><?php echo JText::_('UPDATE_LABEL_LATESTVERSION') ?></td>
			<td>
				<span class="version"><?php echo $this->updates->latest_version ?></span>
				<span class="version-status">
					(<?php echo JText::_('UPDATE_STATUS_'.strtoupper($this->updates->status)); ?>)
				</span>
			</td>
			<td colspan="2">
				<?php echo JText::_('UPDATE_LABEL_RELEASEDON') ?>
				<span class="reldate"><?php echo $this->updates->latest_date ?></span>
			</td>
		</tr>
		<tr>
			<td class="label"><?php echo JText::_('UPDATE_LABEL_PACKAGELOCATION') ?></td>
			<td colspan="3">
				<a href="<?php echo htmlentities($this->updates->package_url.$this->updates->package_url_suffix) ?>">
					<?php echo htmlentities($this->updates->package_url); ?>
				</a>
			</td>
		</tr>
	</table>

	<div id="updater-buttons">

	<?php if($mode == 'update'): ?>
	<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="updateform">
		<input type="hidden" name="option" value="com_akeeba" />
		<input type="hidden" name="view" value="update" />
		<input type="hidden" name="task" value="update" />
		<span class="ui-state-default akupdatebutton ui-corner-all" onclick="forms.updateform.submit();">
			<span class="updategfx">
				<?php echo JText::_('UPDATE_LABEL_UPDATENOW'); ?>
			</span>
		</span>
	</form>
	<?php endif; ?>

	<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="requeryform">
		<input type="hidden" name="option" value="com_akeeba" />
		<input type="hidden" name="view" value="update" />
		<input type="hidden" name="task" value="force" />
		<span class="ui-state-default akupdatebutton ui-corner-all" onclick="forms.requeryform.submit();">
			<span class="requerygfx">
				<?php echo JText::_('UPDATE_LABEL_FORCE'); ?>
			</span>
		</span>
	</form>

	</div>

	<?php else: ?>
	<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="requeryform">
		<input type="hidden" name="option" value="com_akeeba" />
		<input type="hidden" name="view" value="update" />
		<input type="hidden" name="task" value="force" />
		<span class="ui-state-default akupdatebutton ui-corner-all" onclick="forms.requeryform.submit();">
			<span class="requerygfx">
				<?php echo JText::_('UPDATE_LABEL_FORCE'); ?>
			</span>
		</span>
	</form>
	
	<?php endif; ?>