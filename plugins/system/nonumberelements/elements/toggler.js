/**
 * JavaScript file for Element: Toggler
 * Adds slide in and out functionality to elements based on an elements value
 *
 * @package    NoNumber! Elements
 * @version    1.2.0
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

if ( typeof( nn_toggler_version ) == 'undefined' || nn_toggler_version < 1001007 ) {

	// version number of the toggler.
	// to prevent this from overwriting newer versions if other extensions include the toggler too
	var nn_toggler_version = 1001007;

	// prevent init from running more than once
	if ( typeof( window['nnTogglerSet'] ) == "undefined" ) {
		var nnTogglerSet = null;
	}

	var nnToggler = new Class({
		togglers: {}, // holds all the toggle areas
		elements: {}, // holds all the elements with the toggle areas they effect
		form_elements: null, // holds the admin form elements
		div_elements: null, // holds the div elements

		initialize: function()
		{
			this.togglers = $$('.nntoggler');
			if ( this.togglers.length ) {
				this.form_elements = $$( 'input', 'select' );
				this.initTogglers();
			}
		},

		initTogglers: function( id )
		{
			var self = this;

			// make parent tds have no padding
			this.togglers.each( function( element ) {
				if ( element.getParent().getTag() == 'td' ) {
					element.getParent().setStyle( 'padding', '0' );
				}
			});

			// add effects
			this.togglers.each( function( toggler ) {
				toggler.nofx = toggler.hasClass( 'nntoggler_nofx' );
				if ( toggler.nofx ) {
					toggler.fx_slide = new Fx.Slide( toggler, { 'duration' : 1, onComplete: function() { self.autoHeightDivs(); } } );
				} else {
					toggler.fx_slide = new Fx.Slide( toggler, { 'duration' : 500, onStart: function() { self.autoHeightDivs(); }, onComplete: function() { self.autoHeightDivs(); } } );
					toggler.fx_fade = new Fx.Styles( toggler, { 'duration' : 500 } );
				}
			});

			// set actions on elements
			this.togglers.each( function( toggler ) {
				toggler.elements = {};
				ids = toggler.id.split( ' ' );
				for ( var i = 0; i < ids.length; i++ ) {
					keyval = ids[i].split( '.' );
					if ( keyval.length < 2 ) {
						keyval[1] = 1;
					}
					if ( typeof( toggler.elements[keyval[0]] ) == "undefined" ) {
						toggler.elements[keyval[0]] = new Array();
					}
					toggler.elements[keyval[0]].push( keyval[1] )
					self.setElementAction( keyval[0], toggler );
				}
			});

			// open togglers by value
			this.togglers.each( function( toggler ) {
				var show = 0;
				for ( element in toggler.elements ) {
					var values = self.get_values( element );
					if ( values != null && values.length && self.in_array( toggler.elements[element], values ) ) {
						show = 1;
						break;
					}
				}
				if ( !show ) {
					toggler.fx_slide.hide();
					if ( !toggler.nofx ) {
						toggler.setStyle( 'opacity', 0 );
					}
				}
				toggler.setStyle( 'visibility', 'visible' );
			});

			this.div_elements = $$( 'form div' );
			// set all divs in the form to auto height
			this.autoHeightDivs();
		},

		autoHeightDivs: function()
		{
			// set all divs in the form to auto height
			this.div_elements.each( function( el) {
				if ( el.getStyle( 'height' ) != '0px' ) {
					el.setStyle( 'height', 'auto' );
				}
			} );
		},

		toggleAction: function( toggler, show, nofx )
		{
			toggler.fx_slide.stop();
			if ( toggler.nofx || nofx ) {
				if( show ) {
					toggler.fx_slide.show();
				} else {
					toggler.fx_slide.hide();
				}
				this.autoHeightDivs();
			} else {
				toggler.fx_fade.stop();
				if( show ) {
					toggler.fx_slide.slideIn();
					(function(){ toggler.fx_fade.start( { 'opacity' : 1 } ) }).delay( 250 );
				} else {
					toggler.fx_slide.slideOut();
					toggler.fx_fade.start( { 'opacity' : 0 } );
				}
			}
		},

		toggle: function( toggler )
		{
			var show = 0;
			for ( element in toggler.elements ) {
				var values = this.get_values( element );
				if ( values != null && values.length && this.in_array( toggler.elements[element], values ) ) {
					show = 1;
					break;
				}
			}
			this.toggleAction( toggler, show );
		},

		get_values: function( element_name )
		{
			if ( this.elements[element_name] == undefined ) {
				return null;
			}

			var element = this.elements[element_name];

			var values = new Array();
			// get value
			switch ( element.type ) {
				case 'radio':
				case 'checkbox':
					for ( var i = 0; i < element.elements.length; i++ ) {
						if ( element.elements[i].checked ) {
							values.push( element.elements[i].value );
						}
					}
					break;
				default:
					if ( element.elements.length > 1 ) {
						for ( var i = 0; i < element.elements.length; i++ ) {
							if ( element.elements[i].checked ) {
								values.push( element.elements[i].value );
							}
						}
					} else {
						values.push( element.elements[0].value );
					}
					break;
			}
			return values;
		},

		setElementAction : function( element_name, toggler )
		{
			var self = this;
			var element = {};
			element.elements = new Array();
			this.form_elements.each( function( el ) {
				if (
						el.name == 'params['+element_name+']' || el.name == 'params['+element_name+'][]'
					||	el.name == element_name || el.name == element_name+'[]'

				) {
					if ( element.type == undefined ) {
						if ( el.getTag() == 'select' ) {
							element.type = 'select';
						} else {
							element.type = el.type;
						}
					}

					if ( element.type == 'radio' || element.type == 'checkbox' ) {
						el.addEvent( 'click', function(event) { self.toggle( toggler ); });
						el.addEvent( 'keyup', function(event) { self.toggle( toggler ); });
					} else {
						el.addEvent( 'change', function(event) { self.toggle( toggler ); });
					}

					if (
							element.type == 'select'
						||	element.type == 'text'
					) {
						el.addEvent( 'keyup', function(event) { self.toggle( toggler ); });
					}

					element.elements.push( el );
				}
			});
			if ( element.type != undefined ) {
				this.elements[element_name] = element;
			}
		},

		in_array : function( needle, haystack )
		{
			if( {}.toString.call(needle).slice(8, -1) != 'Array' ) {
				arr = new Array();
				arr[0] = needle;
				needle = arr;
			}
			if( {}.toString.call(haystack).slice(8, -1) != 'Array' ) {
				arr = new Array();
				arr[0] = haystack;
				haystack = arr;
			}

			for ( var h = 0; h < haystack.length; h++ ) {
				for ( var n = 0; n < needle.length; n++ ) {
			        if ( haystack[h] == needle[n] ) {
			            return true;
				    }
				}
			}
		    return false;
		}
	});
}