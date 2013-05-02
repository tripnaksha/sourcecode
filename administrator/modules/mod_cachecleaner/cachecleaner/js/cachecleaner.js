/**
 * Add to Menu editor button - JavaScript
 *
 * @package    Cache Cleaner
 * @version    1.1.1
 * @since      File available since Release v1.0.0
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl/cachecleaner
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

window.addEvent( 'domready', function()
{
	new Element( 'span', {
		'id': 'cachecleaner_msg',
	    'styles': { 'opacity': 0 }
	} )
	.injectInside( document.body )
	.addEvent( 'click', function(){ cachecleaner_show_end() } );
	cachecleaner_fx = new Fx.Styles( $( 'cachecleaner_msg' ), {wait: false} );
	cachecleaner_delay = false;
} );

var cachecleaner_load = function( id, editorname )
{
	cachecleaner_show_start();
	var myXHR = new XHR( {
			method: 'get',
			onSuccess: function( data ) {
				classname = 'warning';
				if ( data == cachecleaner_msg_success ) {
					classname = 'success';
				}
				$( 'cachecleaner_msg' ).setText( data ).addClass( classname );
				cachecleaner_show_end( 2000 );
			},
			onFailure: function() {
				classname = 'failure';
				$( 'cachecleaner_msg' ).setText( cachecleaner_msg_failure ).addClass( classname );
				cachecleaner_show_end( 2000 );
			}
		} );
	myXHR.send( cachecleaner_root+'/index.php?cleancache=1&break=1' );
}

var cachecleaner_show_start = function()
{
	$( 'cachecleaner_msg' )
	.setHTML( '<img src="'+cachecleaner_root+'/modules/mod_cachecleaner/cachecleaner/images/loading.gif" alt=\"\" /> '+cachecleaner_msg )
	.removeClass( 'success' ).removeClass( 'failure' )
	.addClass( 'visible' );
	
	$clear( cachecleaner_delay );
	cachecleaner_fx.stop();
	cachecleaner_fx.start({
		'opacity': 0.8,
		'duration': 400
	});
};

var cachecleaner_show_end = function( delay )
{
	if ( delay ) {
		cachecleaner_delay = ( function(){ cachecleaner_show_end(); } ).delay( delay );
	} else {
		$clear( cachecleaner_delay );
		cachecleaner_fx.stop();
		cachecleaner_fx.start({
			'opacity': 0,
			'duration': 1600
		});
	}
};