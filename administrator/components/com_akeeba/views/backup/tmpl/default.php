<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: default.php 247 2010-09-08 10:05:36Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load the editor
$editor =& JFactory::getEditor();

// Apply error container chrome if there are errors detected
$quirks_style = $this->haserrors ? 'class="ui-state-error"' : "";
?>
<!-- jQuery & jQuery UI detection. Also shows a big, fat warning if they're missing -->
<div id="nojquerywarning">
	<p class="akwarningtitle">WARNING</p>
	<p>jQuery and/or jQuery UI have not been loaded. This usually means that you have to change the permissions
	of media/com_akeeba and <u>all of its contents</u> to a least 0644. Alternatively, click on the
	&quot;Parameters&quot; icon, located in the toolbar of Akeeba Backup's Control Panel page and set the source
	for both of them to &quot;Google AJAX API&quot;.</p>
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

<script type="text/javascript">
// Initialization
akeeba.jQuery(document).ready(function($){
	// The return URL
	akeeba_return_url = '<?php AkeebaHelperEscape::escapeJS($this->returnurl) ?>'

	// Used as parameters to start_timeout_bar()
	akeeba_max_execution_time = <?php echo $this->maxexec; ?>;
	akeeba_time_bias = <?php echo $this->bias; ?>;

	// Create a function for saving the editor's contents
	akeeba_comment_editor_save = function() {
		<?php echo $editor->save('comment'); ?>
	}

	// Push some translations
	akeeba_translations['UI-LASTRESPONSE'] = '<?php echo AkeebaHelperEscape::escapeJS(JText::_('BACKUP_TEXT_LASTRESPONSE')) ?>';

	//Parse the domain keys
	akeeba_domains = JSON.parse("<?php echo $this->domains ?>");

	// Setup AJAX proxy URL
	akeeba_ajax_url = 'index.php?option=com_akeeba&view=backup&format=raw';

	// Setup the IFRAME mode
	akeeba_use_iframe = <?php echo $this->useiframe ?>;

	// Bind start button's click event
	$('#backup-start').bind("click", function(e){
		backup_start();
	});
});
</script>

<div id="backup-setup">
	<h1><?php echo JText::_('BACKUP_HEADER_STARTNEW') ?></h1>

	<div class="activeprofile ui-widget-header ui-corner-all">
		<form action="index.php" method="post" name="flipForm" id="flipForm">
			<input type="hidden" name="option" value="com_akeeba" />
			<input type="hidden" name="view" value="backup" />
			<input type="hidden" name="returnurl" value="<?php htmlentities($this->returnurl, ENT_COMPAT, 'UTF-8', false) ?>" />
			<?php echo JText::_('CPANEL_PROFILE_TITLE'); ?>: #<?php echo $this->profileid; ?>
			<?php echo JHTML::_('select.genericlist', $this->profilelist, 'profileid', 'onchange="flipProfile();"', 'value', 'text', $this->profileid); ?>
			<input type="hidden" name="description" id="flipDescription" value="" />
			<input type="hidden" name="comment" id="flipComment" value="" />
			<input type="button" class="ui-state-default" value="<?php echo JText::_('CPANEL_PROFILE_BUTTON'); ?>" onclick="flipProfile();" />
		</form>
	</div>
	<div class="clr"></div>
	<script type="text/javascript">
	function flipProfile()
	{
		(function($) {
			// Save the editor contents
			<?php echo $editor->save('comment'); ?>
			$('#flipDescription').val(  $('#backup-description').val() );
			$('#flipComment').val( $('#comment').val() );
			document.forms.flipForm.submit();
		})(akeeba.jQuery);
	}
	</script>

	<?php if ($this->hasquirks): ?>
	<div id="quirks" <?php echo $quirks_style ?>>
		<h3><?php echo JText::_('BACKUP_LABEL_DETECTEDQUIRKS') ?></h3>
		<p><?php echo JText::_('BACKUP_LABEL_QUIRKSLIST') ?></p>
		<?php echo $this->quirks; ?>
	</div>
	<?php endif; ?>

	<form id="dummyForm">
	<table id="backup-setup-parameters" class="adminlist" width="100%">
		<tr>
			<td valign="top"><?php echo JText::_('BACKUP_LABEL_DESCRIPTION'); ?></td>
			<td valign="top">
				<input type="text" name="description" value="<?php echo $this->description; ?>"
				maxlength="255" size="80" id="backup-description" />
			</td>
			<td rowspan="3" valign="middle" width="100%">
				<div id="backup-start" class="ui-corner-all ui-state-default">
					<table class="backup-start-text" border="0" width="100%">
						<tr valign="middle">
							<td align="center">
								<?php echo JText::_('BACKUP_LABEL_START') ?>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
		<?php if($this->showjpskey): ?>
		<tr>
			<td>
				<?php echo JText::_('CONFIG_JPS_KEY_TITLE'); ?>
			</td>
			<td>
				<input type="password" name="jpskey" value="<?php echo htmlentities($this->jpskey, ENT_COMPAT, 'UTF-8', false) ?>"
				size="50" id="jpskey" />
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td><?php echo JText::_('BACKUP_LABEL_COMMENT'); ?></td>
			<td><?php echo $editor->display( 'comment',  $this->comment, '480', '300', '60', '20', array() ) ; ?>
			</td>
		</tr>
	</table>
	</form>
</div>

<div id="backup-progress-pane" class="ui-widget" style="display: none">
	<div class="ui-state-highlight" style="padding: 0.3em; margin: 0.3em 0.2em; font-weight: bold;">
			<span class="ui-icon ui-icon-notice" style="float: left;"></span>
			<?php echo JText::_('BACKUP_TEXT_BACKINGUP'); ?>
	</div>
	<div id="backup-progress-header" class="ui-corner-tl ui-corner-tr ui-widget-header">
		<?php echo JText::_('BACKUP_LABEL_PROGRESS') ?>
	</div>
	<div id="backup-progress-content" class="ui-corner-bl ui-corner-br ui-widget-content">
		<div id="backup-steps" class="ui-corner-all">
		</div>
		<div id="backup-status" class="ui-corner-all">
			<div id="backup-step"></div>
			<div id="backup-substep"></div>
		</div>
		<div id="response-timer" class="ui-corner-all">
			<div class="color-overlay"></div>
			<div class="text"></div>
		</div>
	</div>
	<span id="ajax-worker"></span>
</div>

<div id="backup-complete" class="ui-widget" style="display: none">
	<h1 class="ui-widget-header">
		<?php echo JText::_('BACKUP_HEADER_BACKUPFINISHED'); ?>
	</h1>
	<div id="finishedframe" class="ui-widget-content">
		<div style="min-height: 32px">
			<div class="ak-icon ak-icon-ok" style="float: left; margin: 0 1em 0 0 !important;"></div>
			<p>
				<?php echo JText::_('BACKUP_TEXT_CONGRATS') ?>
			</p>
		</div>

		<a href="<?php echo JURI::base() ?>index.php?option=com_akeeba&view=buadmin" class="akbutton ui-state-default ui-corner-all">
			<div class="ak-icon ak-icon-adminfiles" style="margin: 0 1em 0 0 !important"></div>
			<div class="text">
				<?php echo JText::_('BUADMIN'); ?>
			</div>
		</a>
		<a href="<?php echo JURI::base() ?>index.php?option=com_akeeba&view=log" class="akbutton ui-state-default ui-corner-all">
			<div class="ak-icon ak-icon-viewlog" style="margin: 0 1em 0 0 !important"></div>
			<div class="text">
				<?php echo JText::_('VIEWLOG'); ?>
			</div>
		</a>
	</div>
</div>

<div id="backup-warnings-panel" class="ui-widget" style="display:none">
	<h2 class="warnings-header ui-widget-header ui-corner-tl ui-corner-tr"><?php echo JText::_('BACKUP_LABEL_WARNINGS') ?></h2>
	<div id="warnings-list" class="ui-widget-content ui-corner-bl ui-corner-br">
	</div>
</div>

<div id="error-panel" class="ui-widget" style="display:none">
	<h1 class="ui-widget-header ui-state-error">
		<?php echo JText::_('BACKUP_HEADER_BACKUPFAILED'); ?>
	</h1>
	<div id="errorframe" class="ui-widget-content">
		<p><?php echo JText::_('BACKUP_TEXT_BACKUPFAILED') ?></p>
		<p id="backup-error-message" class="ui-state-error ui-corner-tl ui-corner-tr">
		</p>
		<p>
			<?php echo JText::_('BACKUP_TEXT_READLOGFAIL') ?>
		</p>
		<p>
			<?php echo JText::sprintf('BACKUP_TEXT_RTFMFIRST', 'http://www.akeebabackup.com/forum') ?>
		</p>
		<a href="<?php echo JURI::base() ?>index.php?option=com_akeeba&view=log" class="akbutton ui-state-default ui-corner-all">
			<div class="ak-icon ak-icon-viewlog" style="margin: 0 1em 0 0 !important"></div>
			<div class="text">
				<?php echo JText::_('VIEWLOG'); ?>
			</div>
		</a>
	</div>
</div>