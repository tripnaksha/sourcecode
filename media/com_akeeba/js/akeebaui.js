/**
 * Akeeba Backup
 * The modular PHP5 site backup software solution
 * This file contains the jQuery-based client-side user interface logic
 * @package akeebaui
 * @copyright Copyright (c)2009-2010 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @version $Id: akeebaui.js 247 2010-09-08 10:05:36Z nikosdion $
 **/

/** @var Root URI for theme files */
var akeeba_ui_theme_root = "";

/** @var The AJAX proxy URL */
var akeeba_ajax_url = "";

/** @var Current backup job's tag */
var akeeba_backup_tag = 'backend';

/** @var The callback function to call on error */
var akeeba_error_callback = dummy_error_handler;

/** @var A URL to return to upon successful backup */
var akeeba_return_url = '';

/** @var The translation strings used in the GUI */
var akeeba_translations = new Array();
akeeba_translations['UI-BROWSE'] = 'Browse...';
akeeba_translations['UI-CONFIG'] = 'Configure...';
akeeba_translations['UI-LASTRESPONSE'] = 'Last server response %ss ago';
akeeba_translations['UI-ROOT'] = '&lt;root&gt;';
akeeba_translations['UI-ERROR-FILTER'] = 'An error occured while applying the filter for "%s"';

/** @var Engine definitions array */
var akeeba_engines = new Array();

/** @var Installers definitions array */
var akeeba_installers = new Array();

/** @var The function used to show the directory browser. Takes two params: starting_directory, input_element */
var akeeba_browser_hook = null;

/** @var An array of domains and descriptions, used during backup */
var akeeba_domains = null;

/** @var A function which causes the visual comment editor to save its contents */
var akeeba_comment_editor_save = null;

/** @var Maximum execution time per step (in msec) */
var akeeba_max_execution_time = 14000;

/** @var Maximum execution time per step bias (in percentage units, 0 to 100) */
var akeeba_time_bias = 75;

/** @var Used for filter reset operations */
var akeeba_current_root = '';

/** @var iFrame pseudo-AJAX success callback */
var akeeba_iframecb_success = null;

/** @var iFrame pseudo-AJAX error callback */
var akeeba_iframecb_error = null;

/** @var iFrame pseudo-AJAX IFRAME element */
var akeeba_iframe = null;

/** @var Should I use IFRAME instead of regular AJAX calls? */
var akeeba_use_iframe = false;

//=============================================================================
//Akeeba Backup -- Common functions
//=============================================================================

/**
 * An extremely simple error handler, dumping error messages to screen
 * @param error The error message string
 */
function dummy_error_handler(error)
{
	alert("An error has occured\n"+error);
}

/**
 * Poor man's AJAX, using IFRAME elements
 * @param data An object with the query data, e.g. a serialized form
 * @param successCallback A function accepting a single object parameter, called on success
 */
function doIframeCall(data, successCallback, errorCallback)
{
	(function($) {
		akeeba_iframecb_success = successCallback;
		akeeba_iframecb_error = errorCallback;
		akeeba_iframe = document.createElement('iframe');
		$(akeeba_iframe)
			.css({
				'display'		: 'none',
				'visibility'	: 'hidden',
				'height'		: '1px'
			})
			.attr('onload','cbIframeCall()')
			.insertAfter('#response-timer');
		var url = akeeba_ajax_url + '&' + $.param(data);
		$(akeeba_iframe).attr('src',url);
	})(akeeba.jQuery);
}

/**
 * Poor man's AJAX, using IFRAME elements: the callback function
 */
function cbIframeCall()
{
	(function($) {
		// Get the contents of the iFrame
		var iframeDoc = null;
		if (akeeba_iframe.contentDocument) {
			iframeDoc = akeeba_iframe.contentDocument; // The rest of the world
		} else {
			iframeDoc = akeeba_iframe.contentWindow.document; // IE on Windows
		}
		var msg = iframeDoc.body.innerHTML;
		
		// Dispose of the iframe
		$(akeeba_iframe).remove();
		akeeba_iframe = null;
		
		// Start processing the message
		var junk = null;
		var message = "";
		
		// Get rid of junk before the data
		var valid_pos = msg.indexOf('###');
		if( valid_pos == -1 ) {
			// Valid data not found in the response
			msg = 'Invalid AJAX data: ' + msg;
			if(akeeba_iframecb_error == null)
			{
				if(akeeba_error_callback != null)
				{
					akeeba_error_callback(msg);
				}
			}
			else
			{
				akeeba_iframecb_error(msg);
			}
			return;
		} else if( valid_pos != 0 ) {
			// Data is prefixed with junk
			junk = msg.substr(0, valid_pos);
			message = msg.substr(valid_pos);
		}
		else
		{
			message = msg;
		}
		message = message.substr(3); // Remove triple hash in the beginning
		
		// Get of rid of junk after the data
		var valid_pos = message.lastIndexOf('###');
		message = message.substr(0, valid_pos); // Remove triple hash in the end
		
		try {
			var data = JSON.parse(message);
		} catch(err) {
			var msg = err.message + "\n<br/>\n<pre>\n" + message + "\n</pre>";
			if(akeeba_iframecb_error == null)
			{
				if(akeeba_error_callback != null)
				{
					akeeba_error_callback(msg);
				}
			}
			else
			{
				akeeba_iframecb_error(msg);
			}
			return;
		}
		
		// Call the callback function
		akeeba_iframecb_success(data);		
	})(akeeba.jQuery);
}

/**
 * Performs an AJAX request and returns the parsed JSON output.
 * The global akeeba_ajax_url is used as the AJAX proxy URL.
 * If there is no errorCallback, the global akeeba_error_callback is used.
 * @param data An object with the query data, e.g. a serialized form
 * @param successCallback A function accepting a single object parameter, called on success
 * @param errorCallback A function accepting a single string parameter, called on failure
 */
function doAjax(data, successCallback, errorCallback, useCaching)
{
	if(akeeba_use_iframe) {
		doIframeCall(data, successCallback, errorCallback)
		return;
	}

	if(useCaching == null) useCaching = true;
	(function($) {
		var structure =
		{
			type: "POST",
			url: akeeba_ajax_url,
			cache: false,
			data: data,
			timeout: 600000,
			success: function(msg) {
				// Initialize
				var junk = null;
				var message = "";
				
				// Get rid of junk before the data
				var valid_pos = msg.indexOf('###');
				if( valid_pos == -1 ) {
					// Valid data not found in the response
					msg = 'Invalid AJAX data: ' + msg;
					if(errorCallback == null)
					{
						if(akeeba_error_callback != null)
						{
							akeeba_error_callback(msg);
						}
					}
					else
					{
						errorCallback(msg);
					}
					return;
				} else if( valid_pos != 0 ) {
					// Data is prefixed with junk
					junk = msg.substr(0, valid_pos);
					message = msg.substr(valid_pos);
				}
				else
				{
					message = msg;
				}
				message = message.substr(3); // Remove triple hash in the beginning
				
				// Get of rid of junk after the data
				var valid_pos = message.lastIndexOf('###');
				message = message.substr(0, valid_pos); // Remove triple hash in the end
				
				try {
					var data = JSON.parse(message);
				} catch(err) {
					var msg = err.message + "\n<br/>\n<pre>\n" + message + "\n</pre>";
					if(errorCallback == null)
					{
						if(akeeba_error_callback != null)
						{
							akeeba_error_callback(msg);
						}
					}
					else
					{
						errorCallback(msg);
					}
					return;
				}
				
				// Call the callback function
				successCallback(data);
			},
			error: function(Request, textStatus, errorThrown) {
				var message = '<strong>AJAX Loading Error</strong><br/>HTTP Status: '+Request.status+' ('+Request.statusText+')<br/>';
				message = message + 'Internal status: '+textStatus+'<br/>';
				message = message + 'XHR ReadyState: ' + Request.readyState + '<br/>';
				message = message + 'Raw server response:<br/>'+Request.responseText;

				if(errorCallback == null)
				{
					if(akeeba_error_callback != null)
					{
						akeeba_error_callback(message);
					}
				}
				else
				{
					errorCallback(message);
				}
			}
		};
		if(useCaching)
		{
			$.manageAjax.add('akeeba-ajax-profile', structure);
		}
		else
		{
			$.ajax( structure );
		}
	})(akeeba.jQuery);
}

//=============================================================================
//Akeeba Backup -- Configuration page
//=============================================================================

/**
 * Parses the JSON decoded data object defining engine and GUI parameters for the
 * configuration page
 * @param data The nested objects of engine and GUI definitions
 */
function parse_config_data(data)
{
	parse_config_engine_data(data.engines);
	parse_config_installer_data(data.installers);
	parse_config_gui_data(data.gui);
}

/**
 * Parses the engine definition data passed from Akeeba Engine to the UI via JSON
 * @param data Nested objects of engine definitions
 */
function parse_config_engine_data(data)
{
	// As simple as it can possibly be!
	akeeba_engines = data;
}

/**
 * Parses the installer definition data passed from Akeeba Engine to the UI via JSON
 * @param data Nested objects of installer definitions
 */
function parse_config_installer_data(data)
{
	akeeba_installers = data;
}

/**
 * Parses the main configuration GUI definition, generating the on-page widgets
 * @param data The nested objects of the GUI definition ('gui' key of JSON data)
 * @param rootnode The jQuery extended root DOM element in which to create the widgets
 */
function parse_config_gui_data(data, rootnode)
{
	(function($) {
		if(rootnode == null)
		{
			// The default root node is the form itself
			rootnode = $('#akeebagui');
		}
		
		// Begin by slashing contents of the akeebagui DIV
		rootnode.empty();
		
		// This is the workhorse, looping through groupdefs and creating HTML elements
		var group_id = 0;
		$.each(data,function(headertext, groupdef) {
			// Loop for each group definition
			group_id++;
			
			// Create a fieldset container
			var container = $( document.createElement('fieldset') );
			container.addClass('ui-corner-all');
			container.appendTo( rootnode );

			// Create a group header
			var header = $( document.createElement('div') );
			header.attr('id', 'auigrp_'+rootnode.attr('id')+'_'+group_id);
			header.addClass('ui-widget-header').addClass('akeeba-ui-config-header');
			header.html(headertext);
			header.appendTo(container);
			
			// Loop each element
			$.each(groupdef, function(config_key, defdata){
				// Parameter ID
				var current_id = 'var['+config_key+']';
				
				// Option row DIV
				var row_div = $(document.createElement('div')).addClass('akeeba-ui-optionrow');
				row_div.appendTo(container);
				
				// Create label
				var label = $(document.createElement('label'));
				label.attr('for', current_id);
				label.html( defdata['title'] );
				label.tooltip({
					track: true,
					delay: 0,
					showURL: false,
					opacity: 1,
					fixPNG: true,
					fade: 0,
					extraClass: 'ui-dialog ui-corner-all',
					bodyHandler: function() {
						var title = $(this).html();
						var description = defdata['description'];
						var html = '<h3><div class="ui-icon ui-icon-info"></div><span>'+title+'</span></h3>';
						html += '<div>'+description+'</div>';
						return html;
					}
				});
				label.appendTo( row_div );
				
				// Create GUI representation based on type
				switch( defdata['type'] )
				{
					// An installer selection
					case 'installer':
						// Create the select element
						var editor = $(document.createElement('select')).attr({
							id:			current_id,
							name:		current_id
						});
						$.each(akeeba_installers, function(key, element){
							var option = $(document.createElement('option')).attr('value', key).html(element.name);
							if( defdata['default'] == key ) option.attr('selected',1);
							option.appendTo( editor );
						});

						editor.appendTo( row_div );

						break;				
				
					// An engine selection
					case 'engine':
						var engine_type = defdata['subtype'];
						if( akeeba_engines[engine_type] == null ) break;

						// Container for engine parameters, initially hidden
						var engine_config_container = $(document.createElement('div')).attr({
							id:			config_key+'_config'
						}).addClass('ui-helper-hidden').appendTo( container );
						
						// Container for selection & button
						var span = $(document.createElement('span'));
						span.appendTo( row_div );
						
						// Create the select element
						var editor = $(document.createElement('select')).attr({
							id:			current_id,
							name:		current_id
						});
						$.each(akeeba_engines[engine_type], function(key, element){
							var option = $(document.createElement('option')).attr('value', key).html(element.information.title);
							if( defdata['default'] == key ) option.attr('selected',1);
							option.appendTo( editor );
						});
						editor.bind("change",function(e){
							// When the selection changes, we have to repopulate the config container
							// First, save any changed values
							var old_values = new Object;
							$(engine_config_container).find('input').each(function(i){
								if( $(this).attr('type') == 'checkbox' )
								{
									old_values[$(this).attr('id')] = $(this).is(':checked');
								}
								else
								{
									old_values[$(this).attr('id')] = $(this).val();
								}
							});
							// Create the new interface
							var new_engine = $(this).val();
							var enginedef = akeeba_engines[engine_type][new_engine];
							var enginetitle = enginedef.information.title;
							var new_data = new Object;
							var engine_params = enginedef.parameters;
							new_data[enginetitle] = engine_params;
							parse_config_gui_data(new_data, engine_config_container);
							$(engine_config_container)
								.find('.akeeba-ui-config-header:first')
								.after(
									$(document.createElement('p'))
									.html(enginedef.information.description)
								);
							// Reapply changed values
							engine_config_container.find('input').each(function(i){
								var old = old_values[$(this).attr('id')];
								if( (old != null) && (old != undefined) )
								{
									if( $(this).attr('type') == 'checkbox' )
									{ $(this).attr('checked', old); }
									else if ( $(this).attr('type') == 'hidden' )
									{
										$(this).next().next().slider( 'value' , old );
									}
									else
									{ $(this).val(old); }
								}
							});
						});
						editor.appendTo( span );
						
						// Add a configuration show/hide button
						var button = $(document.createElement('button')).addClass('ui-state-default').html(akeeba_translations['UI-CONFIG']);
						button.bind('click', function(e){
							engine_config_container.toggleClass('ui-helper-hidden');
							e.preventDefault();
						});
						button.appendTo( span );
						
						// Populate config container with the default engine data
						if(akeeba_engines[engine_type][defdata['default']] != null)
						{
							var new_engine = defdata['default'];
							var enginedef = akeeba_engines[engine_type][new_engine];
							var enginetitle = enginedef.information.title;
							var new_data = new Object;
							var engine_params = enginedef.parameters;
							new_data[enginetitle] = engine_params;
							parse_config_gui_data(new_data, engine_config_container);
							$(engine_config_container)
							.find('.akeeba-ui-config-header:first')
							.after(
								$(document.createElement('p'))
								.html(enginedef.information.description)
							);							
						}
						break;
					
					// A text box with an option to launch a browser
					case 'browsedir':
						var editor = $(document.createElement('input')).attr({
							type:		'text',
							id:			current_id,
							name:		current_id,
							size:		'30',
							value:		defdata['default']
						});
						
						var button = $(document.createElement('button')).addClass('ui-state-default').html(akeeba_translations['UI-BROWSE']);
						button.bind('click',function(event){
							event.preventDefault();
							if( akeeba_browser_hook != null ) akeeba_browser_hook( editor.val(), editor );
						});
						
						var span = $(document.createElement('span'));
						editor.appendTo( span );
						button.appendTo( span );
						span.appendTo(row_div);
						break;
						
					// A drop-down list
					case 'enum':
						var editor = $(document.createElement('select')).attr({
							id:			current_id,
							name:		current_id
						});
						// Create and append options
						var enumvalues = defdata['enumvalues'].split("|");
						var enumkeys = defdata['enumkeys'].split("|");
						
						$.each(enumvalues, function(counter, value){
							var item_description = enumkeys[counter];
							var option = $(document.createElement('option')).attr('value', value).html(item_description);
							if(value == defdata['default']) option.attr('selected',1);
							option.appendTo( editor );
						});
						
						editor.appendTo( row_div );
						break;
						
					// A simple single-line, unvalidated text box
					case 'string':
						var editor = $(document.createElement('input')).attr({
							type:		'text',
							id:			current_id,
							name:		current_id,
							size:		'40',
							value:		defdata['default']
						});
						editor.appendTo( row_div );
						break;
						
					// An integer slider
					case 'integer':
						// Hidden form element which echoes slider's value
						var hidden_input = $(document.createElement('input')).attr({
							id:		current_id,
							name:	current_id,
							type:	'hidden'
						}).val(defdata['default']);
						hidden_input.appendTo( row_div );
						// A label to display the current setting
						var notify_label = $(document.createElement('div')).attr({
							id:		config_key+'_ticker'
						}).addClass('ui-widget-content').addClass('akeeba-ui-slider-label').addClass('ui-corner-all');
						notify_label.appendTo( row_div );
						// Slider widget
						var wrapper_div = $(document.createElement('div'));
						var slider_div = $(document.createElement('div')).attr('id',config_key+'_slider');
						function fix_slider() {
							slider_div.parent()
							.stop()
							.css({
								padding: '0',
								top: '0',
								bottom: '0',
								overflow: 'visible',
								height: '24px'
							});
							slider_div
							.stop()
							.css({
								display: 'block',
								margin: '0',
								padding: '0',
								top: '0',
								bottom: '0',
								overflow: 'visible',
								height: '0.8em'								
							});							
						}
						function update_gui_from_slider(event, ui, hack)
						{
							var display_value = null;
							if(hack == null)
							{
								display_value = ui.value;
							}
							else
							{
								display_value = ui.slider('option', 'value'); 
							}
							hidden_input.val(display_value);
							var uom = defdata['uom'];
							if( typeof(uom) != 'string' ) {
								uom = '';
							} else {
								uom = ' '+uom;
							}
							if( Math.floor(defdata['scale']) == defdata['scale'] ) display_value = display_value / defdata['scale'];
							notify_label.html(display_value.toFixed(2) + uom);
							
							fix_slider();
						}
						slider_div.slider({
							animate:		'fast',
							max:			Number(defdata['max']),
							min:			Number(defdata['min']),
							orientation:	'horizontal',
							step:			Number(defdata['every']),
							change:			update_gui_from_slider,
							slide:			update_gui_from_slider,
							value:			Number(defdata['default'])
						});
						// Thank you MooTools 2.4 for screwing me. I really appreciate it... NOT!
						slider_div.hover(fix_slider, fix_slider);
						slider_div.mousemove(fix_slider);
						slider_div.mouseover(fix_slider);
						slider_div.keyup(fix_slider);
						slider_div.blur(fix_slider);
						
						slider_div.appendTo(wrapper_div);
						wrapper_div.addClass('akeeba-ui-slider');
						wrapper_div.appendTo( row_div );
						update_gui_from_slider(null, slider_div, true);
						break;
					
					// A toggle button (stylized checkbox)
					case 'bool':
						var wrap_div = $(document.createElement('div')).addClass('akeeba-ui-checkbox');
						// This hack is required in order to "submit" unchecked checkboxes with a value of 0... 
						$(document.createElement('input')).attr({
							name:			current_id,
							type:			'hidden',
							value:			0
						}).appendTo( wrap_div );
						// ...and let checked checkboxes post a value equal to 1.
						var editor = $(document.createElement('input')).attr({
							id:				current_id,
							name:			current_id,
							type:			'checkbox',
							value:			1
						});
						if( defdata['default'] != 0 ) editor.attr('checked','checked');
						editor.appendTo( wrap_div );
						editor.checkbox({
							cls: 'jquery-safari-checkbox',
							empty: akeeba_ui_theme_root+'images/empty.png'
						});
						wrap_div.appendTo( row_div );
						break;
						
					// Button with a custom hook function
					case 'button':
						// Create the button
						var hook = defdata['hook'];
						var editor = $(document.createElement('button')).addClass('ui-state-default').attr('id', current_id).html(label.html());
						label.html('&nbsp;');
						editor.hover(
							function(){$(this).addClass('ui-state-hover');}, 
							function(){$(this).removeClass('ui-state-hover');}
						);
						editor.bind('click', function(e){
							e.preventDefault();
							try {
								eval(hook+'()');
							} catch(err) {}
						});
						editor.appendTo( row_div );
						break;
				}
			});
			
		});
	})(akeeba.jQuery);
}

//=============================================================================
//Akeeba Backup -- Backup Now page
//=============================================================================

function set_ajax_timer()
{
	setTimeout('akeeba_ajax_timer_tick()', 10);
}

function akeeba_ajax_timer_tick()
{
	(function($){
		doAjax({
			// Data to send to AJAX
			'ajax'	: 'step',
			'tag'	: akeeba_backup_tag
		}, backup_step, backup_error, false );
	})(akeeba.jQuery);	
}

function start_timeout_bar(max_allowance, bias)
{
	(function($) {
		var lastResponseSeconds = 0;
		$('#response-timer div.text').everyTime(1000, 'lastReponse', function(){
			lastResponseSeconds++;
			var lastText = akeeba_translations['UI-LASTRESPONSE'].replace('%s', lastResponseSeconds.toFixed(0));
			$('#response-timer div.text').html(lastText);
		});
		
		var maximum_time = 180000;
		var green_time = Number(max_allowance) * (Number(bias) / 100);
		var yellow_time = Number(max_allowance) - green_time;
		var green_percentage = Number((green_time / maximum_time) * 100).toFixed(2)+'%';
		var yellow_percentage = Number((Number(max_allowance) / maximum_time ) * 100).toFixed(2)+'%';
		$('div.color-overlay')
		.css({
			backgroundColor: '#00cc00'
		})
		.animate({
			width: green_percentage
		}, green_time, 'none', function() {
			$('div.color-overlay').animate({
				backgroundColor: '#cccc00'
			},{easing: 'none', duration: 1000, queue: false});
		})
		.animate({
			width: yellow_percentage
		}, yellow_time, 'none', function() {
			$('div.color-overlay').animate({
				backgroundColor: '#ff9999'
			},{easing: 'none', duration: 1000, queue: false});			
		})
		.animate({
			width: '100%'
		}, maximum_time - Number(max_allowance), 'none', function() {
			$('#response-timer div.text').stopTime('lastReponse');
			// TODO Issue a backup failure (more than 3min have passed)
		})
		.animate({
			backgroundColor: '#cc0000'
		}, {easing: 'none', duration: 2000});
	})(akeeba.jQuery);
}

function reset_timeout_bar()
{
	(function($){
		$('#response-timer div.text').stopTime();
		$('div.color-overlay').stop(true);
		$('div.color-overlay')
		.css({
			backgroundColor: '#00cc00',
			width: '1px'
		});
		var lastText = akeeba_translations['UI-LASTRESPONSE'].replace('%s', '0');
		$('#response-timer div.text').html(lastText);
	})(akeeba.jQuery);
}

function render_backup_steps(active_step)
{
	(function($){
		var normal_class = 'step-complete';
		if( active_step == '' ) normal_class = 'step-pending';
	
		$('#backup-steps').html('');
		$.each(akeeba_domains, function(counter, element){
			var step = $(document.createElement('div'))
				.html(element[1])
				.data('domain',element[0])
				.appendTo('#backup-steps');
	
			if(step.data('domain') == active_step )
			{
				normal_class = 'step-pending';
				this_class = 'step-active';
			}
			else
			{
				this_class = normal_class;
			}
			step.attr({
				'class': this_class
			});
		});			
	})(akeeba.jQuery);
}

function backup_start()
{
	(function($){
		// Save the editor contents
		try {
			if( akeeba_comment_editor_save != null ) akeeba_comment_editor_save();
		} catch(err) {
			// If the editor failed to save its content, just move on and ignore the error
			$('#comment').val("");
		}
		// Get encryption key (if applicable)
		var jpskey = '';
		try {
			jpskey = $('#jpskey').val();
		} catch(err) {
			jpskey = '';
		}
		// Hide the backup setup
		$('#backup-setup').hide("fast");
		// Show the backup progress
		$('#backup-progress-pane').show("fast");
		// Initialize steps
		render_backup_steps('');
		// Start the response timer
		start_timeout_bar(akeeba_max_execution_time, akeeba_time_bias);
		// Perform Ajax request
		akeeba_backup_tag = 'backend';
		doAjax({
			// Data to send to AJAX
			'ajax': 'start',
			description: $('#backup-description').val(),
			comment: $('#comment').val(),
			jpskey: jpskey
		}, backup_step, backup_error, false );
	})(akeeba.jQuery);
}

function backup_step(data)
{
	// Update visual step progress from active domain data
	reset_timeout_bar();
	render_backup_steps(data.Domain);
	(function($){
		// Update step/substep display
		$('#backup-step').html(data.Step);
		$('#backup-substep').html(data.Substep);
		// Do we have warnings?
		if( data.Warnings.length > 0 )
		{
			$.each(data.Warnings, function(i, warning){
				var newDiv = $(document.createElement('div'))
					.html(warning)
					.appendTo( $('#warnings-list') );
			});
			if( $('#backup-warnings-panel').is(":hidden") )
			{
				$('#backup-warnings-panel').show('fast');
			}
		}
		// Do we have errors?
		var error_message = data.Error;
		if(error_message != '')
		{
			// Uh-oh! An error has occurred.
			backup_error(error_message);
			return;
		}
		else
		{
			// No errors. Good! Are we finished yet?
			if(data["HasRun"] == 1)
			{
				// Yes. Show backup completion page.
				backup_complete();
			}
			else
			{
				// No. Set the backup tag
				akeeba_backup_tag = data.tag;
				if(empty(akeeba_backup_tag)) akeeba_backup_tag = 'backend';
				// Start the response timer...
				start_timeout_bar(akeeba_max_execution_time, akeeba_time_bias);
				// ...and send an AJAX command
				set_ajax_timer();
			}
		}
	})(akeeba.jQuery);
}

function backup_error(message)
{
	(function($){
		// Make sure the timer is stopped
		reset_timeout_bar();
		// Hide progress and warnings
		$('#backup-progress-pane').hide("fast");
		$('#backup-warnings-panel').hide("fast");
		// Setup and show error pane
		$('#backup-error-message').html(message);
		$('#error-panel').show();
	})(akeeba.jQuery);
}

function backup_complete()
{
	(function($){
		// Make sure the timer is stopped
		reset_timeout_bar();
		// Hide progress
		$('#backup-progress-pane').hide("fast");
		// Show finished pane
		$('#backup-complete').show();
		$('#backup-warnings-panel').width('100%');

		// Proceed to the return URL if it is set
		if(akeeba_return_url != '')
		{
			window.location = akeeba_return_url;
		}
	})(akeeba.jQuery);
}

//=============================================================================
//Akeeba Backup -- Filesystem Filters (direct)
//=============================================================================

/**
 * Loads the contents of a directory
 * @param data
 * @return
 */
function fsfilter_load(data)
{
	// Add the verb to the data
	data.verb = 'list';
	// Convert to JSON
	var json = JSON.stringify(data);
	// Assemble the data array and send the AJAX request
	var new_data = new Object;
	new_data.action = json;
	doAjax(new_data, function(response){
		fsfilter_render(response);
	});
}

/**
 * Toggles a filesystem filter
 * @param data
 * @param caller
 * @return
 */
function fsfilter_toggle(data, caller, callback, use_inner_child)
{
	if(use_inner_child == null) use_inner_child = true;
	(function($){
		// Make the icon spin
		if(caller != null)
		{
			// Do not allow multiple simultaneous AJAX requests on the same object 
			if( caller.data('loading') == true ) return;
			
			caller.data('loading', true);
			if(use_inner_child) {
				var icon_span = caller.children('span:first');
			} else {
				var icon_span = caller;
			}
			caller.data('icon', icon_span.attr('class') );
			icon_span.removeClass(caller.data('icon'));
			icon_span.addClass('ui-icon');
			icon_span.addClass('ui-icon-arrowrefresh-1-w');
			icon_span.everyTime(100, 'spinner', function(){
				if(icon_span.hasClass('ui-icon-arrowrefresh-1-w'))
				{
					icon_span.removeClass('ui-icon-arrowrefresh-1-w');
					icon_span.addClass('ui-icon-arrowrefresh-1-n');
				} else
				if(icon_span.hasClass('ui-icon-arrowrefresh-1-n'))
				{
					icon_span.removeClass('ui-icon-arrowrefresh-1-n');
					icon_span.addClass('ui-icon-arrowrefresh-1-e');
				} else
				if(icon_span.hasClass('ui-icon-arrowrefresh-1-e'))
				{
					icon_span.removeClass('ui-icon-arrowrefresh-1-e');
					icon_span.addClass('ui-icon-arrowrefresh-1-s');
				} else
				{
					icon_span.removeClass('ui-icon-arrowrefresh-1-s');
					icon_span.addClass('ui-icon-arrowrefresh-1-w');
				}
			});
		}
	
		
		// Convert to JSON
		var json = JSON.stringify(data);
		// Assemble the data array and send the AJAX request
		var new_data = new Object;
		new_data.action = json;
		doAjax(new_data, function(response){
			if(caller != null)
			{
				icon_span.stopTime();
				icon_span.attr('class', caller.data('icon'));
				caller.removeData('icon');
				caller.removeData('loading');
			}
			if( response.success == true )
			{
				if(caller != null)
				{
					if(use_inner_child)
					{
						// Update the on-screen filter state
						if(response.newstate == true)
						{
							caller.removeClass('ui-state-normal');
							caller.addClass('ui-state-highlight');
						}
						else
						{
							caller.addClass('ui-state-normal');
							caller.removeClass('ui-state-highlight');
						}
					}
				}
				if(!(callback == null)) callback(response, caller);
			}
			else
			{
				if(!(callback == null)) callback(response, caller);
				// An error occured
				var dialog_element = $("#dialog");
				dialog_element.html(''); // Clear the dialog's contents
				$(document.createElement('p')).html(akeeba_translations['UI-ERROR-FILTER'].replace('%s', data.node)).appendTo(dialog_element);
				dialog_element.dialog('open');
			}
		}, function(msg){
			// Error handler
			if(caller != null)
			{
				icon_span.stopTime();
				icon_span.attr('class', caller.data('icon'));
				caller.removeData('icon');
				caller.removeData('loading');
			}

			akeeba_error_callback(msg);
		});
	})(akeeba.jQuery);
}

/**
 * Renders the Filesystem Filters page 
 * @param data
 * @return
 */
function fsfilter_render(data)
{
	akeeba_current_root = data.root;
	(function($){
		// ----- Render the crumbs bar
		// Create a new crumbs data array
		var crumbsdata = new Array;
		// Push the "navigate to root" element
		var newCrumb = new Array;
		newCrumb[0] = akeeba_translations['UI-ROOT'];	// [0] : UI Label
		newCrumb[1] = data.root;						// [1] : Root node
		newCrumb[2] = new Array;						// [2] : Crumbs to current directory
		newCrumb[3] = '';								// [3] : Node element
		crumbsdata.push(newCrumb);
		// Iterate existing crumbs
		if(data.crumbs.length > 0)
		{
			var crumbs = new Array;
			$.each(data.crumbs,function(counter, crumb) {
				var newCrumb = new Array;
				newCrumb[0] = crumb;
				newCrumb[1] = data.root;
				newCrumb[2] = crumbs.slice(0); // Otherwise it is copied by reference
				newCrumb[3] = crumb;
				crumbsdata.push(newCrumb);
				crumbs.push(crumb); // Push this dir into the crumb list
			});
		}
		// Render the UI crumbs elements
		var akcrumbs = $('#ak_crumbs');
		akcrumbs.html('');
		$.each(crumbsdata, function(counter, def){
			$(document.createElement('span'))
				.html(def[0])
				.attr('class', 'ui-state-default')
				.hover(
						   function(){$(this).addClass('ui-state-hover');}, 
						   function(){$(this).removeClass('ui-state-hover');}
					)
				.click(function(){
					$(this).append(
						$(document.createElement('img'))
						.attr('src', akeeba_ui_theme_root+'../icons/loading.gif')
						.attr({
							width: 16,
							height: 11,
							border: 0,
							alt: 'Loading...'
						})
						.css({
							marginTop: '5px',
							marginLeft: '5px'
						})
					);
					
					var new_data = new Object;
					new_data.root = def[1];
					new_data.crumbs = def[2];
					new_data.node = def[3];
					fsfilter_load(new_data);
				})
				.appendTo(akcrumbs);
			if(counter < (crumbsdata.length-1) ) akcrumbs.append(' &bull; ');
		});
		
		// ----- Render the subdirectories
		var akfolders = $('#folders');
		akfolders.html('');
		if(data.crumbs.length > 0)
		{
			// The parent directory element
			var uielement = $(document.createElement('div'))
			.addClass('folder-container');
			uielement
			.append($(document.createElement('span')).addClass('folder-padding'))
			.append($(document.createElement('span')).addClass('folder-padding'))
			.append($(document.createElement('span')).addClass('folder-padding'))
			.append(
				$(document.createElement('span'))
				.addClass('folder-name folder-up')
				.html('('+akcrumbs.find('span:last').prev().html()+')')
				.prepend(
					$(document.createElement('span'))
					.addClass('ui-icon ui-icon-arrowreturnthick-1-w')
				)
				.click(function(){
					akcrumbs.find('span:last').prev().click();
				})
			)
			.appendTo(akfolders);
		}
		$.each(data.folders, function(folder, def){
			var uielement = $(document.createElement('div'))
				.addClass('folder-container');
			
			var available_filters = new Array;
			available_filters.push('directories');
			available_filters.push('skipdirs');
			available_filters.push('skipfiles');
			$.each(available_filters, function(counter, filter){
				var ui_icon = $(document.createElement('span')).addClass('folder-icon-container');
				switch(filter)
				{
					case 'directories':
						ui_icon.append('<span class="ui-icon ui-icon-cancel"></span>');
						break;
					case 'skipdirs':
						ui_icon.append('<span class="ui-icon ui-icon-folder-open"></span>');
						break;
					case 'skipfiles':
						ui_icon.append('<span class="ui-icon ui-icon-document"></span>');
						break;
				}
				ui_icon.tooltip({
					track: false,
					delay: 0,
					showURL: false,
					opacity: 1,
					fixPNG: true,
					fade: 0,
					extraClass: 'ui-dialog ui-corner-all',
					bodyHandler: function() {
						html = '<div>'+akeeba_translations['UI-FILTERTYPE-'+filter.toUpperCase()]+'</div>';
						return html;
					}
				});
				
				switch(def[filter])
				{
					case 2:
						ui_icon.addClass('ui-state-error');
						break;
						
					case 1:
						ui_icon.addClass('ui-state-highlight');
						// Don't break; we have to add the handler!
						
					case 0:
						ui_icon.click(function(){
							var new_data = new Object;
							new_data.root = data.root;
							new_data.crumbs = crumbs;
							new_data.node = folder;
							new_data.filter = filter;
							new_data.verb = 'toggle';
							fsfilter_toggle(new_data, ui_icon);
						});
				}
				ui_icon.appendTo(uielement);
			}); // filter loop
			// Add the folder label and make clicking on it load its listing
			$(document.createElement('span'))
				.html(folder)
				.addClass('folder-name')
				.click(function(){
					// Show "loading" animation
					$(this).append(
						$(document.createElement('img'))
						.attr('src', akeeba_ui_theme_root+'../icons/loading.gif')
						.attr({
							width: 16,
							height: 11,
							border: 0,
							alt: 'Loading...'
						})
						.css({
							marginTop: '3px',
							marginLeft: '5px'
						})
					);
					
					var new_data = new Object;
					new_data.root = data.root;
					new_data.crumbs = crumbs;
					new_data.node = folder;
					fsfilter_load(new_data);
				})
				.appendTo(uielement);
			// Render
			uielement.appendTo(akfolders);
		});
		
		// ----- Render the files
		var akfiles = $('#files');
		akfiles.html('');
		$.each(data.files, function(file, def){
			var uielement = $(document.createElement('div'))
				.addClass('file-container');
			
			var available_filters = new Array;
			available_filters.push('files');
			$.each(available_filters, function(counter, filter){
				var ui_icon = $(document.createElement('span')).addClass('file-icon-container');
				switch(filter)
				{
					case 'files':
						ui_icon.append('<span class="ui-icon ui-icon-cancel"></span>');
						break;
				}
				ui_icon.tooltip({
					track: false,
					delay: 0,
					showURL: false,
					opacity: 1,
					fixPNG: true,
					fade: 0,
					extraClass: 'ui-dialog ui-corner-all',
					bodyHandler: function() {
						html = '<div>'+akeeba_translations['UI-FILTERTYPE-'+filter.toUpperCase()]+'</div>';
						return html;
					}
				});
				switch(def[filter])
				{
					case 2:
						ui_icon.addClass('ui-state-error');
						break;
						
					case 1:
						ui_icon.addClass('ui-state-highlight');
						// Don't break; we have to add the handler!
						
					case 0:
						ui_icon.click(function(){
							var new_data = new Object;
							new_data.root = data.root;
							new_data.crumbs = crumbs;
							new_data.node = file;
							new_data.filter = filter;
							new_data.verb = 'toggle';
							fsfilter_toggle(new_data, ui_icon);
						});
				}
				ui_icon.appendTo(uielement);
			}); // filter loop
			// Add the file label
			uielement
			.append(
				$(document.createElement('span'))
				.addClass('file-name')
				.html(file)
			)
			.append(
				$(document.createElement('span'))
				.addClass('file-size')
				.html(size_format(def['size']))
			);
			// Render
			uielement.appendTo(akfiles);
		});
	})(akeeba.jQuery);
}

/**
 * Loads the tabular view of the Filesystems Filter for a given root
 * @param root
 * @return
 */
function fsfilter_load_tab(root)
{
	var data = new Object;
	data.verb = 'tab';
	data.root = root;
	// Convert to JSON
	var json = JSON.stringify(data);
	// Assemble the data array and send the AJAX request
	var new_data = new Object;
	new_data.action = json;
	doAjax(new_data, function(response){
		fsfilter_render_tab(response);
	});
}

/**
 * Add a row in the tabular view of the Filesystems Filter 
 * @param def
 * @param append_to_here
 * @return
 */
function fsfilter_add_row(def, append_to_here)
{
	(function($){
		// Turn def.type into something human readable
		var type_text = akeeba_translations['UI-FILTERTYPE-'+def.type.toUpperCase()];
		if(type_text == null) type_text = def.type;
		
		$(document.createElement('tr'))
		.addClass('ak_filter_row')
		.append(
			// Filter title
			$(document.createElement('td'))
			.addClass('ak_filter_type')
			.append(
				$(document.createElement('span'))
				.addClass('ui-icon ui-icon-circle-plus addnew')
				.click(function(){
					// Add a row below ourselves
					var new_def = new Object;
					new_def.type = def.type;
					new_def.node = '';
					fsfilter_add_row(new_def, $(this).parent().parent().parent() );
					$(this).parent().parent().parent().children('tr:last').children('td:last').children('span.ak_filter_tab_icon_container:last').click();
				})
			)
			.append(type_text)
		)
		.append(
			$(document.createElement('td'))
			.addClass('ak_filter_item')
			.append(
				$(document.createElement('span'))
				.addClass('ak_filter_tab_icon_container')
				.click(function(){
					if( def.node == '' )
					{
						// An empty filter is normally not saved to the database; it's a new record row which has to be removed...
						$(this).parent().parent().remove();
						return;
					}
					
					var new_data = new Object;
					new_data.root = $('#active_root').val();
					new_data.crumbs = new Array();
					new_data.node = def.node;
					new_data.filter = def.type;
					new_data.verb = 'toggle';
					fsfilter_toggle(new_data, $(this), function(response, caller){
						if(response.success)
						{
							caller.parent().parent().remove();
						}
					});
				})
				.append(
						$(document.createElement('span'))
						.addClass('ui-icon ui-icon-trash deletebutton')
				)
			)
			.append(
				$(document.createElement('span'))
				.addClass('ak_filter_tab_icon_container')
				.click(function(){
					if( $(this).siblings('span.ak_filter_tab_icon_container:first').next().data('editing') ) return;
					$(this).siblings('span.ak_filter_tab_icon_container:first').next().data('editing',true);
					$(this).next().hide();
					$(document.createElement('input'))
					.attr({
						type: 'text',
						size: 60
					})
					.val( $(this).next().html() )
					.appendTo( $(this).parent() )
					.blur(function(){
						var new_value = $(this).val();
						if(new_value == '')
						{
							// Well, if the user meant to remove the filter, let's help him!
							$(this).parent().children('span.ak_filter_name').show();
							$(this).siblings('span.ak_filter_tab_icon_container').find('span.deletebutton').click();
							$(this).remove();
							return;
						}
						
						// First, remove the old filter
						var new_data = new Object;
						new_data.root = $('#active_root').val();
						new_data.crumbs = new Array();
						new_data.old_node = def.node;
						new_data.new_node = new_value;
						new_data.filter = def.type;
						new_data.verb = 'swap';
						
						var input_box = $(this);
						
						fsfilter_toggle(new_data,
							input_box.siblings('span.ak_filter_tab_icon_container:first').next(),
							function(response, caller){
								// Remove the editor
								input_box.siblings('span.ak_filter_tab_icon_container:first').next().removeData('editing');
								input_box.parent().find('span.ak_filter_name').show();
								input_box.siblings('span.ak_filter_tab_icon_container:first').next().removeClass('ui-state-highlight');
								input_box.parent().find('span.ak_filter_name').html( new_value );
								input_box.remove();
							}
						);
					})
					.focus();
				})					
				.append(
					$(document.createElement('span'))
					.addClass('ui-icon ui-icon-pencil editbutton')
				)
			)
			.append(
				$(document.createElement('span'))
				.addClass('ak_filter_name')
				.html(def.node)
			)
		)
		.appendTo( $(append_to_here) );
	})(akeeba.jQuery);
}

/**
 * Renders the tabular view of the Filesystems Filter
 * @param data
 * @return
 */
function fsfilter_render_tab(data)
{
	(function($){
		var tbody = $('#ak_list_contents');
		tbody.html('');
		$.each(data, function(counter, def){
			fsfilter_add_row(def, tbody);
		});
	})(akeeba.jQuery);
}

/**
 * Wipes out the filesystem filters
 * @return
 */
function fsfilter_nuke()
{
	var data = new Object;
	data.root = akeeba_current_root;
	data.verb = 'reset';
	// Convert to JSON
	var json = JSON.stringify(data);
	// Assemble the data array and send the AJAX request
	var new_data = new Object;
	new_data.action = json;
	doAjax(new_data, function(response){
		fsfilter_render(response);
	});
}

//=============================================================================
//Akeeba Backup -- Database Filters (direct)
//=============================================================================

/**
 * Loads the contents of a database
 * @param data
 * @return
 */
function dbfilter_load(data)
{
	// Add the verb to the data
	data.verb = 'list';
	// Convert to JSON
	var json = JSON.stringify(data);
	// Assemble the data array and send the AJAX request
	var new_data = new Object;
	new_data.action = json;
	doAjax(new_data, function(response){
		dbfilter_render(response);
	});
}

/**
 * Toggles a database filter
 * @param data
 * @param caller
 * @return
 */
function dbfilter_toggle(data, caller, callback)
{
	fsfilter_toggle(data, caller, callback);
}

/**
 * Renders the Database Filters page 
 * @param data
 * @return
 */
function dbfilter_render(data)
{
	akeeba_current_root = data.root;
	(function($){
		// ----- Render the tables
		var aktables = $('#tables');
		aktables.html('');
		$.each(data.tables, function(table, dbef){
			var uielement = $(document.createElement('div'))
				.addClass('table-container');
			
			var available_filters = new Array;
			available_filters.push('tables');
			available_filters.push('tabledata');
			$.each(available_filters, function(counter, filter){
				var ui_icon = $(document.createElement('span')).addClass('table-icon-container');
				switch(filter)
				{
					case 'tables':
						ui_icon.append('<span class="ui-icon ui-icon-cancel"></span>');
						break;
					case 'tabledata':
						ui_icon.append('<span class="ui-icon ui-icon-contact"></span>');
						break;
				}
				ui_icon.tooltip({
					track: false,
					delay: 0,
					showURL: false,
					opacity: 1,
					fixPNG: true,
					fade: 0,
					extraClass: 'ui-dialog ui-corner-all',
					bodyHandler: function() {
						html = '<div>'+akeeba_translations['UI-FILTERTYPE-'+filter.toUpperCase()]+'</div>';
						return html;
					}
				});
				
				switch(dbef[filter])
				{
					case 2:
						ui_icon.addClass('ui-state-error');
						break;
						
					case 1:
						ui_icon.addClass('ui-state-highlight');
						// Don't break; we have to add the handler!
						
					case 0:
						ui_icon.click(function(){
							var new_data = new Object;
							new_data.root = data.root;
							new_data.node = table;
							new_data.filter = filter;
							new_data.verb = 'toggle';
							dbfilter_toggle(new_data, ui_icon);
						});
				}
				ui_icon.appendTo(uielement);
			}); // filter loop
			// Add the table label
			var iconclass = 'ui-icon-link';
			var icontip = 'UI-TABLETYPE-MISC';
			switch(dbef.type)
			{
				case 'table':
					iconclass = 'ui-icon-calculator';
					icontip = 'UI-TABLETYPE-TABLE';
					break;
				case 'view':
					iconclass = 'ui-icon-copy';
					icontip = 'UI-TABLETYPE-VIEW';
					break;
				case 'procedure':
					iconclass = 'ui-icon-script';
					icontip = 'UI-TABLETYPE-PROCEDURE';
					break;
				case 'function':
					iconclass = 'ui-icon-gear';
					icontip = 'UI-TABLETYPE-FUNCTION';
					break;
				case 'trigger':
					iconclass = 'ui-icon-video';
					icontip = 'UI-TABLETYPE-TRIGGER';
					break;
			}
			$(document.createElement('span'))
				.addClass('table-name')
				.html(table)
				.append(
					$(document.createElement('span'))
					.addClass('table-icon-container')
					.addClass('table-icon-noclick')
					.addClass('table-icon-small')
					.append(
						$(document.createElement('span'))
						.addClass('ui-icon')
						.addClass('ui-icon-grip-dotted-vertical')
					)
				)
				.append(
					$(document.createElement('span'))
					.addClass('table-icon-container')
					.addClass('table-icon-noclick')
					.addClass('table-icon-small')
					.append(
						$(document.createElement('span'))
						.addClass('ui-icon')
						.addClass(iconclass)
					)
					.tooltip({
						track: false,
						delay: 0,
						showURL: false,
						opacity: 1,
						fixPNG: true,
						fade: 0,
						extraClass: 'ui-dialog ui-corner-all',
						bodyHandler: function() {
							html = '<div>'+akeeba_translations[icontip]+'</div>';
							return html;
						}
					})					
				)
				.appendTo(uielement);
			// Render
			uielement.appendTo(aktables);
		});
	})(akeeba.jQuery);
}

/**
 * Loads the tabular view of the Database Filter for a given root
 * @param root
 * @return
 */
function dbfilter_load_tab(root)
{
	var data = new Object;
	data.verb = 'tab';
	data.root = root;
	// Convert to JSON
	var json = JSON.stringify(data);
	// Assemble the data array and send the AJAX request
	var new_data = new Object;
	new_data.action = json;
	doAjax(new_data, function(response){
		dbfilter_render_tab(response);
	});
}

/**
 * Add a row in the tabular view of the Filesystems Filter 
 * @param def
 * @param append_to_here
 * @return
 */
function dbfilter_add_row(def, append_to_here)
{
	(function($){
		// Turn def.type into something human readable
		var type_text = akeeba_translations['UI-FILTERTYPE-'+def.type.toUpperCase()];
		if(type_text == null) type_text = def.type;
		
		$(document.createElement('tr'))
		.addClass('ak_filter_row')
		.append(
			// Filter title
			$(document.createElement('td'))
			.addClass('ak_filter_type')
			.append(
				$(document.createElement('span'))
				.addClass('ui-icon ui-icon-circle-plus addnew')
				.click(function(){
					// Add a row below ourselves
					var new_def = new Object;
					new_def.type = def.type;
					new_def.node = '';
					dbfilter_add_row(new_def, $(this).parent().parent().parent() );
					$(this).parent().parent().parent().children('tr:last').children('td:last').children('span.ak_filter_tab_icon_container:last').click();
				})
			)
			.append(type_text)
		)
		.append(
			$(document.createElement('td'))
			.addClass('ak_filter_item')
			.append(
				$(document.createElement('span'))
				.addClass('ak_filter_tab_icon_container')
				.click(function(){
					if( def.node == '' )
					{
						// An empty filter is normally not saved to the database; it's a new record row which has to be removed...
						$(this).parent().parent().remove();
						return;
					}
					
					var new_data = new Object;
					new_data.root = $('#active_root').val();
					new_data.node = def.node;
					new_data.filter = def.type;
					new_data.verb = 'remove';
					dbfilter_toggle(new_data, $(this), function(response, caller){
						if(response.success)
						{
							caller.parent().parent().remove();
						}
					});
				})
				.append(
						$(document.createElement('span'))
						.addClass('ui-icon ui-icon-trash deletebutton')
				)
			)
			.append(
				$(document.createElement('span'))
				.addClass('ak_filter_tab_icon_container')
				.click(function(){
					if( $(this).siblings('span.ak_filter_tab_icon_container:first').next().data('editing') ) return;
					$(this).siblings('span.ak_filter_tab_icon_container:first').next().data('editing',true);
					$(this).next().hide();
					$(document.createElement('input'))
					.attr({
						type: 'text',
						size: 60
					})
					.val( $(this).next().html() )
					.appendTo( $(this).parent() )
					.blur(function(){
						var new_value = $(this).val();
						if(new_value == '')
						{
							// Well, if the user meant to remove the filter, let's help him!
							$(this).parent().children('span.ak_filter_name').show();
							$(this).siblings('span.ak_filter_tab_icon_container').find('span.deletebutton').click();
							$(this).remove();
							return;
						}
						
						// First, remove the old filter
						var new_data = new Object;
						new_data.root = $('#active_root').val();
						new_data.old_node = def.node;
						new_data.new_node = new_value;
						new_data.filter = def.type;
						new_data.verb = 'swap';
						
						var input_box = $(this);
						
						dbfilter_toggle(new_data,
							input_box.siblings('span.ak_filter_tab_icon_container:first').next(),
							function(response, caller){
								// Remove the editor
								input_box.siblings('span.ak_filter_tab_icon_container:first').next().removeData('editing');
								input_box.parent().find('span.ak_filter_name').show();
								input_box.siblings('span.ak_filter_tab_icon_container:first').next().removeClass('ui-state-highlight');
								input_box.parent().find('span.ak_filter_name').html( new_value );
								input_box.remove();
							}
						);
					})
					.focus();
				})					
				.append(
					$(document.createElement('span'))
					.addClass('ui-icon ui-icon-pencil editbutton')
				)
			)
			.append(
				$(document.createElement('span'))
				.addClass('ak_filter_name')
				.html(def.node)
			)
		)
		.appendTo( $(append_to_here) );
	})(akeeba.jQuery);
}

/**
 * Renders the tabular view of the Database Filter
 * @param data
 * @return
 */
function dbfilter_render_tab(data)
{
	(function($){
		var tbody = $('#ak_list_contents');
		tbody.html('');
		$.each(data, function(counter, def){
			dbfilter_add_row(def, tbody);
		});
	})(akeeba.jQuery);
}

/**
 * Activates the exclusion filters for non-CMS tables
 */
function dbfilter_exclude_noncms()
{
	(function($){
		$('#tables div').each(function(i, element){
			// Get the table name
			var tablename = $(element).find('span.table-name:first').text();
			var prefix = tablename.substr(0,3);
			// If the prefix is #__ it's a CMS table and I have to skip it
			if( prefix != '#__' )
			{
				var icon = $(element).find('span.table-icon-container span.ui-icon:first');
				if ( !($(icon).parent().hasClass('ui-state-highlight')) )
				{
					$(icon).click();
				}
			}
		});
	})(akeeba.jQuery);
}

/**
 * Wipes out the database filters
 * @return
 */
function dbfilter_nuke()
{
	var data = new Object;
	data.root = akeeba_current_root;
	data.verb = 'reset';
	// Convert to JSON
	var json = JSON.stringify(data);
	// Assemble the data array and send the AJAX request
	var new_data = new Object;
	new_data.action = json;
	doAjax(new_data, function(response){
		dbfilter_render(response);
	});
}

//=============================================================================
// Akeeba's jQuery extensions
//=============================================================================
//Custom no easing plug-in
akeeba.jQuery.extend(akeeba.jQuery.easing, {
	none: function(fraction, elapsed, attrStart, attrDelta, duration) {
		return attrStart + attrDelta * fraction;
	}
});

//=============================================================================
// 							I N I T I A L I Z A T I O N
//=============================================================================
akeeba.jQuery(document).ready(function($){
	// Create an AJAX manager
	var akeeba_ajax_manager = $.manageAjax.create('akeeba_ajax_profile', { 
		queue: true,  
		abortOld: false,
		maxRequests: 1,
		preventDoubbleRequests: false,
		cacheResponse: false
	}); 	
	// Add hover state to buttons and other non jQuery UI elements
	$('.ui-state-default').hover(
	   function(){$(this).addClass('ui-state-hover');}, 
	   function(){$(this).removeClass('ui-state-hover');}
	);
});
