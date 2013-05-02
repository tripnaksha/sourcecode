<?php
/**
 * @package Akeeba
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: default.php 201 2010-08-01 21:04:43Z nikosdion $
 * @since 3.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

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

<div id="dialog" title="<?php echo JText::_('CONFIG_UI_BROWSER_TITLE') ?>">
</div>

<div style="text-align: center; margin: -1.2em auto 0">
		<p class="ui-corner-all ui-state-highlight" style="padding: 0.8em;">
			<span class="ui-icon ui-icon-info" style="display:inline-block;">&nbsp;</span>
			<?php echo JText::_('CONFIG_WHERE_ARE_THE_FILTERS'); ?>
		</p>
</div>
<div style="clear:both;"></div>

<div class="activeprofile2 ui-widget-header ui-corner-all">
		<p>
			<?php echo JText::_('CPANEL_PROFILE_TITLE'); ?>: #<?php echo $this->profileid; ?>
			<?php echo $this->profilename; ?>
		</p>
</div>
<div style="clear:both;"></div>

<!-- This is the form used to submit data back to the app -->
<form name="adminForm" method="post" action="index.php" >
	<input type="hidden" name="option" value="<?php echo JRequest::getCmd('option') ?>" />
	<input type="hidden" name="view" value="config" />
	<input type="hidden" name="task" value="" />

	<!-- This div contains user interface elements -->
	<div id="akeebagui">
	</div>
</form>
<script type="text/javascript">
	// Callback routine to close the browser dialog
	var akeeba_browser_callback = null;

	// Hook for DirectFTP connection test
	var directftp_test_connection = null;

	<?php if(defined('AKEEBA_PRO')): ?>
	// Hook for Upload to Remote FTP connection test
	var postprocftp_test_connection = null;
	<?php endif; ?>

	akeeba.jQuery(document).ready(function($){
		// Push some translations
		akeeba_translations['UI-BROWSE'] = '<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_BROWSE')) ?>';
		akeeba_translations['UI-CONFIG'] = '<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_CONFIG')) ?>';

		// Load the configuration UI data
		akeeba_ui_theme_root = '<?php echo $this->mediadir ?>';
		var data = JSON.parse("<?php echo $this->json; ?>");
		parse_config_data(data);

		// Create the dialog
		$("#dialog").dialog({
			autoOpen: false,
			closeOnEscape: false,
			height: 400,
			width: 640,
			hide: 'slide',
			modal: true,
			position: 'center',
			show: 'slide'
		});

		// Create an AJAX error trap
		akeeba_error_callback = function( message ) {
			var dialog_element = $("#dialog");
			dialog_element.html(''); // Clear the dialog's contents
			//dialog_element.addClass('ui-state-error');
			dialog_element.dialog('option', 'title', '<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_AJAXERRORDLG_TITLE')) ?>');
			$(document.createElement('p')).html('<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_AJAXERRORDLG_TEXT')) ?>').appendTo(dialog_element);
			$(document.createElement('pre')).html( message ).appendTo(dialog_element);
			dialog_element.dialog('open');
		};

		// Create the DirectFTP connection test hook
		directftp_test_connection = function()
		{
			var button = $(document.getElementById('engine.archiver.directftp.ftp_test'));
			button.addClass('ui-state-disabled');
			button.removeClass('ui-state-default');

			// Get the values the user has entered
			var data = new Object();
			data['host'] = $(document.getElementById('var[engine.archiver.directftp.host]')).val();
			data['port'] = $(document.getElementById('var[engine.archiver.directftp.port]')).val();
			data['user'] = $(document.getElementById('var[engine.archiver.directftp.user]')).val();
			data['pass'] = $(document.getElementById('var[engine.archiver.directftp.pass]')).val();
			data['initdir'] = $(document.getElementById('var[engine.archiver.directftp.initial_directory]')).val();
			data['usessl'] = $(document.getElementById('var[engine.archiver.directftp.ftps]')).is(':checked');
			data['passive'] = $(document.getElementById('var[engine.archiver.directftp.passive_mode]')).is(':checked');

			// Construct the query
			akeeba_ajax_url = '<?php echo AkeebaHelperEscape::escapeJS('index.php?option=com_akeeba&view=config&format=raw&ajax=testftp') ?>';
			doAjax(data, function(res){
				var button = $(document.getElementById('engine.archiver.directftp.ftp_test'));
				button.removeClass('ui-state-disabled');
				button.addClass('ui-state-default');

				var dialog_element = $("#dialog");
				dialog_element.html(''); // Clear the dialog's contents
				dialog_element.dialog('option', 'title', '<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_DIRECTFTP_TEST_DIALOG_TITLE')) ?>');
				dialog_element.removeClass('ui-state-error');
				if( res === true )
				{
					$(document.createElement('p')).html('<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_DIRECTFTP_TEST_OK')) ?>').appendTo(dialog_element);
				}
				else
				{
					$(document.createElement('p')).html('<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_DIRECTFTP_TEST_FAIL')) ?>').appendTo(dialog_element);
					$(document.createElement('p')).html( res ).appendTo( dialog_element );
				}
				dialog_element.dialog('open');
			});
		}

<?php if(defined('AKEEBA_PRO')): ?>
		// Create the FTP upload post-proc engine test hook
		postprocftp_test_connection = function()
		{
			var button = $(document.getElementById('engine.postproc.ftp.ftp_test'));
			button.addClass('ui-state-disabled');
			button.removeClass('ui-state-default');

			// Get the values the user has entered
			var data = new Object();
			data['host'] = $(document.getElementById('var[engine.postproc.ftp.host]')).val();
			data['port'] = $(document.getElementById('var[engine.postproc.ftp.port]')).val();
			data['user'] = $(document.getElementById('var[engine.postproc.ftp.user]')).val();
			data['pass'] = $(document.getElementById('var[engine.postproc.ftp.pass]')).val();
			data['initdir'] = $(document.getElementById('var[engine.postproc.ftp.initial_directory]')).val();
			data['usessl'] = $(document.getElementById('var[engine.postproc.ftp.ftps]')).is(':checked');
			data['passive'] = $(document.getElementById('var[engine.postproc.ftp.passive_mode]')).is(':checked');

			// Construct the query
			akeeba_ajax_url = '<?php echo AkeebaHelperEscape::escapeJS('index.php?option=com_akeeba&view=config&format=raw&ajax=testftp') ?>';
			doAjax(data, function(res){
				var button = $(document.getElementById('engine.postproc.ftp.ftp_test'));
				button.removeClass('ui-state-disabled');
				button.addClass('ui-state-default');

				var dialog_element = $("#dialog");
				dialog_element.html(''); // Clear the dialog's contents
				dialog_element.dialog('option', 'title', '<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_POSTPROCFTP_TEST_DIALOG_TITLE')) ?>');
				dialog_element.removeClass('ui-state-error');
				if( res === true )
				{
					$(document.createElement('p')).html('<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_POSTPROCFTP_TEST_OK')) ?>').appendTo(dialog_element);
				}
				else
				{
					$(document.createElement('p')).html('<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_POSTPROCFTP_TEST_FAIL')) ?>').appendTo(dialog_element);
					$(document.createElement('p')).html( res ).appendTo( dialog_element );
				}
				dialog_element.dialog('open');
			});
		}
<?php endif; ?>

		// Create the browser hook
		akeeba_browser_hook = function( folder, element )
		{
			var dialog_element = $("#dialog");
			dialog_element.html(''); // Clear the dialog's contents
			dialog_element.removeClass('ui-state-error');
			dialog_element.dialog('option', 'title', '<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_BROWSER_TITLE')) ?>');

			// URL to load the browser
			var browserSrc = '<?php echo AkeebaHelperEscape::escapeJS(JURI::base().'index.php?option=com_akeeba&view=browser&format=raw&processfolder=1&folder=') ?>';
			browserSrc = browserSrc + encodeURIComponent(folder);

			// IFrame holding the browser
			var akeeba_browser_iframe = $(document.createElement('iframe')).attr({
				'id':			'akeeba_browser_iframe',
				width:			'100%',
				height:			'98%',
				marginWidth		: 0,
				marginHeight	: 0,
				frameBorder		: 0,
				scrolling		: 'auto',
				src				: browserSrc
			});
			akeeba_browser_iframe.appendTo( dialog_element );

			// Close dialog callback (user confirmed the new folder)
			akeeba_browser_callback = function( myFolder ) {
				$(element).val( myFolder );
				dialog_element.dialog('close');
			};

			dialog_element.dialog('open');
		};
	});
</script>