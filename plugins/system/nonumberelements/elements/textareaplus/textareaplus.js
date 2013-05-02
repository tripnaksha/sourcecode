/**
 * JavaScript file for Element: Text Area Plus
 *
 * @package    NoNumber! Elements
 * @version    1.1.0
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

TextAreaResizer = new Class({
	initialize: function(id, options)
	{
		this.options = Object.extend(
			options || {}
		);
		this.options.min_x = this.options.min_x || 160;
		this.options.max_x = this.options.max_x || 800;
		this.options.min_y = this.options.min_y || 80;
		this.options.max_y = this.options.max_y || 400;
		this.element = $(id);
		this.handleid = id+'_handle';
		this.build();
	},
	build: function()
	{
		var wrapper = new Element( 'div', { 'class':'textarea_wrapper' } );
		var handle = new Element( 'div', { 'class':'textarea_handle', 'id':this.handleid } );
		wrapper.injectAfter( this.element );
		wrapper.adopt( this.element );
		wrapper.adopt( handle );
		autoHeightParents( handle );
		$(handle).setStyle( 'width', this.element.getStyle( 'width' ) );
		this.element.makeResizable({
			handle: handle.id,
			grid: 20,
			modifiers:{ x: 'width', y:'height' },
			limit: { x: [this.options.min_x, this.options.max_x], y: [this.options.min_y, this.options.max_y] },
			onStart: function(el){
				autoHeightParents( handle );
				$(document.body).addClass('dragging');
		    },
			onComplete: function(el){
		    	$(document.body).removeClass('dragging');
		    },
		    onDrag: function(el){
				$(handle).setStyle( 'width', this.element.getStyle( 'width' ) );
		    }
		});
	}
});

if( typeof this.window['autoHeightParents'] != 'function' )
{
	function autoHeightParents( element )
	{
		var parent = element.parentNode;
		for ( i = 0; i < 50; i++) {
			if ( parent ) {
				if ( parent.name == 'adminForm' ) {
					break;
				}
				if ( parent.tagName == 'DIV' ) {
					parent.style.height = 'auto';
				}
				parent = parent.parentNode;
			}
		}
	}
}