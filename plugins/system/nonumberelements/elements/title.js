/**
 * JavaScript file for Element: Title
 * Corrects some style problems
 *
 * @package    NoNumber! Elements
 * @version    1.2.0
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

//alert('c');
if ( typeof( nn_title_version ) == 'undefined' || nn_title_version < 1001000 ) {

	// version number of the toggler.
	// to prevent this from overwriting newer versions if other extensions include the toggler too
	var nn_title_version = 1001000;

	// prevent init from running more than once
	if ( typeof( window['nnTitleSet'] ) == "undefined" ) {
		var nnTitleSet = null;
	}

	window.addEvent( 'domready', function() {
		if ( !nnTitleSet ) {
			nnTitleSet = new nnTitle();
		}
	});

	var nnTitle = new Class({
		initialize: function()
		{
			var table;
			$$('.paramlist_value').each( function( td ) {
				if ( td.getTag() == 'td' ) {
					table = td.getParent().getParent().getParent();
					td.setStyle( 'width', table.offsetWidth-140 );
				}
			});
		}
	});
}