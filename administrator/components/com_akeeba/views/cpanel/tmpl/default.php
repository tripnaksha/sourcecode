<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: default.php 201 2010-08-01 21:04:43Z nikosdion $
 * @since 1.3
 *
 * The main page of the Akeeba Backup component is where all the fun takes place :)
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

AEPlatform::load_version_defines();
$lang =& JFactory::getLanguage();
$icons_root = JURI::base().'components/com_akeeba/assets/images/';

?>
<!-- jQuery & jQuery UI detection. Also shows a big, fat warning if they're missing -->
<div id="nojquerywarning">
	<p class="akwarningtitle">WARNING</p>
	<p>jQuery and/or jQuery UI have not been loaded. This usually means that you have to change the permissions
	of media/com_akeeba and <u>all of its contents</u> to a least 0644. Alternatively, click on
	&quot;Parameters&quot; and set the source for both of them to &quot;Google AJAX API&quot;.</p>
	<p>If you do not do that, the component <strong><u>will not work</u></strong>.</p>
</div>
<script type="text/javascript">
	if(typeof akeeba.jQuery == 'function')
	{
		if(typeof akeeba.jQuery.ui == 'object')
		{
			akeeba.jQuery('#nojquerywarning').css('display','none');
		}
	}
</script>

<div class="toprowcontainer">
	<div class="feedrotator">
		<a target="_blank" href="http://feeds2.feedburner.com/~r/joomlapack/news/~6/1">
			<img src="http://feeds2.feedburner.com/joomlapack/news.1.gif" alt="The JoomlaPack News" style="border: 0">
		</a>
	</div>
	<div class="activeprofile ui-widget-header ui-corner-all">
		<form action="index.php" method="post" name="adminForm" id="adminForm">
			<input type="hidden" name="option" value="com_akeeba" />
			<input type="hidden" name="view" value="cpanel" />
			<input type="hidden" name="task" value="switchprofile" />
			<p>
				<?php echo JText::_('CPANEL_PROFILE_TITLE'); ?>: #<?php echo $this->profileid; ?>
				<?php echo JHTML::_('select.genericlist', $this->profilelist, 'profileid', 'onchange="document.forms.adminForm.submit()"', 'value', 'text', $this->profileid); ?>
				<input type="submit" value="<?php echo JText::_('CPANEL_PROFILE_BUTTON'); ?>" class="ui-state-default" />
			</p>
		</form>
	</div>
	<?php if($this->supports_update):?>
	<div class="updateservice ui-state-default ui-corner-all">
		<?php $class = $this->update ? 'update' : 'ok' ?>
		<?php $text = $this->update ? 'CPANEL_UPGRADE_NOW' : 'CPANEL_UPGRADE_UPTODATE'; ?>
			<div class="update-icon">
				<a href="index.php?option=com_akeeba&view=update">
					<div class="ak-icon ak-icon-<?php echo $class ?>"></div>
					<span><?php echo JText::_($text); ?></span>
				</a>
			</div>
	</div>
	<?php endif; ?>
</div>

<div id="cpanel">
	<div class="ak_cpanel_modules" id="ak_cpanel_modules">
		<h3><?php echo JText::_('CPANEL_LABEL_STATUSSUMMARY'); ?></h3>
		<div class="ak_cpanel_status_cell">
			<?php echo $this->statuscell ?>

			<?php $quirks = AEUtilQuirks::get_quirks(); ?>
			<?php if(!empty($quirks)): ?>
			<h4 class="ui-widget-header ui-corner-tl">
				<?php echo JText::_('CPANEL_LABEL_STATUSDETAILS'); ?>
			</h4>
			<div class="ui-widget-content ui-corner-br">
				<?php echo $this->detailscell ?>
			</div>
			<?php endif; ?>

			<?php if(!defined('AKEEBA_PRO')) { $show_donation = 1; } else { $show_donation = (AKEEBA_PRO != 1); } ?>
			<p class="ak_version"><?php echo JText::_('AKEEBA').' '.($show_donation?'':'Professional ').AKEEBA_VERSION.' ('.AKEEBA_DATE.')' ?></p>
			<?php if($show_donation): ?>
			<div style="text-align: center;">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="10903325">
					<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online." style="border: none !important;">
					<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
			</div>
			<?php endif; ?>
		</div>

		<h3><?php echo JText::_('BACKUP_STATS'); ?></h3>
		<div><?php echo $this->statscell ?></div>

		<h3><?php echo JText::_('CPANEL_LABEL_NEWSTITLE'); ?></h3>
		<div><?php echo $this->newscell ?></div>

		<h3><?php echo JText::_('TRANSLATION_CREDITS'); ?></h3>
		<div>
			<p>
				<strong><?php echo JText::_('TRANSLATION_LANGUAGE') ?></strong>
				<br />
				<a href="<?php echo JText::_('TRANSLATION_AUTHOR_URL') ?>"><?php echo JText::_('TRANSLATION_AUTHOR') ?></a>
			</p>
		</div>
	</div>

	<div class="ak_cpanel_main_container">
		<div class="ak_cpanel_header ui-widget-header ui-corner-tl ui-corner-tr">
			<?php echo JText::_('CPANEL_HEADER_BASICOPS'); ?>
		</div>
		<div class="ak_cpanel_icons ui-widget-content ui-corner-br ui-corner-bl">
			<?php foreach($this->icondefs['operations'] as $icon): ?>
			<div class="icon">
				<a href="<?php echo 'index.php?option=com_akeeba'.
					(is_null($icon['view']) ? '' : '&amp;view='.$icon['view']).
					(is_null($icon['task']) ? '' : '&amp;task='.$icon['task']); ?>">
				<div class="ak-icon ak-icon-<?php echo $icon['icon'] ?>">&nbsp;</div>
				<span><?php echo $icon['label']; ?></span>
				</a>
			</div>
			<?php endforeach; ?>
			<div class="ak_clr_left"></div>
		</div>

		<?php if(!empty($this->icondefs['inclusion'])): ?>
		<div class="ak_cpanel_header ui-widget-header ui-corner-tl ui-corner-tr">
			<?php echo JText::_('CPANEL_HEADER_INCLUSION'); ?>
		</div>
		<div class="ak_cpanel_icons ui-widget-content ui-corner-br ui-corner-bl">
			<?php foreach($this->icondefs['inclusion'] as $icon): ?>
			<div class="icon">
				<a href="<?php echo 'index.php?option=com_akeeba'.
					(is_null($icon['view']) ? '' : '&amp;view='.$icon['view']).
					(is_null($icon['task']) ? '' : '&amp;task='.$icon['task']); ?>">
				<div class="ak-icon ak-icon-<?php echo $icon['icon'] ?>">&nbsp;</div>
				<span><?php echo $icon['label']; ?></span>
				</a>
			</div>
			<?php endforeach; ?>
			<div class="ak_clr_left"></div>
		</div>
		<?php endif; ?>

		<div class="ak_cpanel_header ui-widget-header ui-corner-tl ui-corner-tr">
			<?php echo JText::_('CPANEL_HEADER_EXCLUSION'); ?>
		</div>
		<div class="ak_cpanel_icons ui-widget-content ui-corner-br ui-corner-bl">
			<?php foreach($this->icondefs['exclusion'] as $icon): ?>
			<div class="icon">
				<a href="<?php echo 'index.php?option=com_akeeba'.
					(is_null($icon['view']) ? '' : '&amp;view='.$icon['view']).
					(is_null($icon['task']) ? '' : '&amp;task='.$icon['task']); ?>">
				<div class="ak-icon ak-icon-<?php echo $icon['icon'] ?>">&nbsp;</div>
				<span><?php echo $icon['label']; ?></span>
				</a>
			</div>
			<?php endforeach; ?>
			<div class="ak_clr_left"></div>
		</div>

	</div>
</div>

<div class="ak_clr"></div>

<p>
	<?php echo JText::sprintf('COPYRIGHT', date('y')); ?><br/>
	<?php echo JText::_('LICENSE'); ?>
</p>

<script type="text/javascript">
akeeba.jQuery(document).ready(function($){
	$('#ak_cpanel_modules').accordion();
});
</script>
