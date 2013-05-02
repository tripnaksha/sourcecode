/*
 * Media Object 1.5.0
 *
 * Copyright (c) 2007 - 2008 Ryan Demmer (www.joomlacontenteditor.net)
 * Licensed under the GPL (http://www.gnu.org/licenses/licenses.html#GPL)license.
 * Based on the Moxiecode Embed script
 */
var MediaObject = {
	version : {
		'flash' 		: '9,0,124,0',
		'windowsmedia'	: '5,1,52,701',
		'quicktime'		: '6,0,2,0',
		'realmedia'		: '7,0,0,0',
		'shockwave'		: '8,5,1,0'
	},
	init : function(v){
		var t = this;
		for(n in v){
			t.version[n] = v[n];	
		}
	},
	getSite : function(){
		var x, s = document.getElementsByTagName('script');
		for(x=0; x<s.length; x++){
			if(/jceutilities\/js\/mediaobject.js/i.test(s[x].src)){
				site = s[x].src;
			}
		}
		return site.substring(0, site.lastIndexOf('plugins/system/jceutilities/js')).replace(/http:\/\/([^\/]+)/, '');
	},
	writeObject : function(cls, cb, mt, p){
		var h = '', n;
		var msie 	= /msie/i.test(navigator.userAgent);
		var webkit 	= /webkit/i.test(navigator.userAgent);
		
		if(!/:\/\//.test(p.src)){
			p.src = this.getSite() + p.src;	
			if(mt == 'video/x-ms-wmv'){
				p.url = p.src;
			}
			/*if(mt == 'application/x-shockwave-flash'){
				p.movie = p.src;
				delete p.src;	
			}*/
		}
		h += '<object ';
		if(mt == 'application/x-shockwave-flash'){
			h += 'type="'+ mt +'" data="'+ p.src +'" ';	
		}else{
			h += 'codebase="' + cb + '" classid="clsid:' + cls + '" ';
		}
		for (n in p){
			if(p[n] !== ''){
				if (/(id|name|width|height|style)$/.test(n)){
					h += n + '="' + p[n] + '"';	
				}
			}
		}
		h += '>';
		for (n in p){
			if(p[n] !== ''){
				if (!/(id|name|width|height|style)$/.test(n)){
					h += '<param name="' + n + '" value="' + p[n] + '">';
				}
			}
		}
		if(!msie && mt != 'application/x-shockwave-flash'){
			h += '<object type="'+ mt +'" data="'+ p.src +'"';
			for (n in p){
				if(p[n] !== ''){
					h += n + '="' + p[n] + '"';
				}
			}
			h += '></object>';	
		}
		h += '</object>';
		document.write(h);		
	},
	flash : function(p) {
		if(typeof p.wmode == 'undefined'){
			p['wmode'] = 'opaque';
		}
		this.writeObject(
			'D27CDB6E-AE6D-11cf-96B8-444553540000',
			'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=' + this.version['flash'],
			'application/x-shockwave-flash',
			p
		);
	},
	shockwave : function(p) {
		this.writeObject(
		'166B1BCA-3F9C-11CF-8075-444553540000',
		'http://download.macromedia.com/pub/shockwave/cabs/director/sw.cab#version='  + this.version['shockwave'],
		'application/x-director',
			p
		);
	},
	quicktime : function(p) {
		this.writeObject(
			'02BF25D5-8C17-4B23-BC80-D3488ABDDC6B',
			'http://www.apple.com/qtactivex/qtplugin.cab#version=' + this.version['quicktime'],
			'video/quicktime',
			p
		);
	},
	realmedia : function(p) {
		this.writeObject(
			'CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA',
			'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=' + this.version['realmedia'],
			'audio/x-pn-realaudio-plugin',
			p
		);
	},
	windowsmedia : function(p) {
		p.url = p.src;
		this.writeObject(
			'6BF52A52-394A-11D3-B153-00C04F79FAA6',
			'http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=' + this.version['windowsmedia'],
			'video/x-ms-wmv',
			p
		);
	},
	divx : function(p) {
		this.writeObject(
			'67DABFBF-D0AB-41FA-9C46-CC0F21721616',
			'http://go.divx.com/plugin/DivXBrowserPlugin.cab',
			'video/divx',
			p
		);
	}
}
function writeFlash(p) {
	MediaObject.flash(p);
}
function writeShockWave(p) {
	MediaObject.shockwave(p);
}
function writeQuickTime(p) {
	MediaObject.quicktime(p);
}
function writeRealMedia(p) {
	MediaObject.realmedia(p);
}
function writeWindowsMedia(p) {
	MediaObject.windowsmedia(p);
}
function writeDivX(p) {
	MediaObject.divx(p);
}