/**
 * $Id$
 *
 * @author Moxiecode
<<<<<<< .mine
 * @author Ryan Demmer
 * @copyright Copyright (c) 2004-2008, Moxiecode Systems AB, All rights reserved.
=======
 * @copyright Copyright � 2004-2008, Moxiecode Systems AB, All rights reserved.
 *
 * $Id: editor_plugin.js 26 2009-05-25 10:21:53Z happynoodleboy $
 * Modifications to support expansion - divx, pdf, object.
>>>>>>> .r227
 */

(function() {
	var each = tinymce.each;

	tinymce.create('tinymce.plugins.MediaPlugin', {
		init : function(ed, url) {
			var t = this;
			
			t.editor = ed;
			t.url = url;

			function isMediaElm(n) {
				return /^(mceItemFlash|mceItemShockWave|mceItemWindowsMedia|mceItemQuickTime|mceItemRealMedia)$/.test(n.className);
			};

			ed.onPreInit.add(function() {
				// Force in _value parameter this extra parameter is required for older Opera versions
				ed.serializer.addRules('param[name|value|_mce_value]');
			});

			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('media', n.nodeName == 'IMG' && isMediaElm(n));
			});

			ed.onInit.add(function() {
				var lo = {
					mceItemFlash : 'flash',
					mceItemShockWave : 'shockwave',
					mceItemWindowsMedia : 'windowsmedia',
					mceItemQuickTime : 'quicktime',
					mceItemRealMedia : 'realmedia',
					// Added DivX
					mceItemDivX : 'divx'
				};

				ed.selection.onSetContent.add(function() {
					t._spansToImgs(ed.getBody());
				});

				ed.selection.onBeforeSetContent.add(t._objectsToSpans, t);

				if (ed.settings.content_css !== false)
					ed.dom.loadCSS(url + "/css/content.css");

				if (ed.theme.onResolveName) {
					ed.theme.onResolveName.add(function(th, o) {
						if (o.name == 'img') {
							each(lo, function(v, k) {
								if (ed.dom.hasClass(o.node, k)) {
									o.name = v;
									o.title = ed.dom.getAttrib(o.node, 'title');
									return false;
								}
							});
						}
					});
				}

				// Removed ContextMenu
			});

			ed.onBeforeSetContent.add(t._objectsToSpans, t);

			ed.onSetContent.add(function() {
				t._spansToImgs(ed.getBody());
			});

			ed.onPreProcess.add(function(ed, o) {
				var dom = ed.dom;

				if (o.set) {
					t._spansToImgs(o.node);

					each(dom.select('IMG', o.node), function(n) {
						var p;

						if (isMediaElm(n)) {
							p = t._parse(n.title);
							dom.setAttrib(n, 'width', dom.getAttrib(n, 'width', p.width || 100));
							dom.setAttrib(n, 'height', dom.getAttrib(n, 'height', p.height || 100));
						}
					});
				}

				if (o.get) {
					each(dom.select('IMG', o.node), function(n) {
						var ci, cb, mt;

						if (ed.getParam('media_use_script')) {
							if (isMediaElm(n))
								n.className = n.className.replace(/mceItem/g, 'mceTemp');

							return;
						}

						switch (n.className) {
							case 'mceItemFlash':
								ci = 'd27cdb6e-ae6d-11cf-96b8-444553540000';
								cb = 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=' + ed.getParam('media_version_flash', '10,0,32,18');
								mt = 'application/x-shockwave-flash';
								break;

							case 'mceItemShockWave':
								ci = '166b1bca-3f9c-11cf-8075-444553540000';
								cb = 'http://download.macromedia.com/pub/shockwave/cabs/director/sw.cab#version=' + ed.getParam('media_version_shockwave', '11,0,0,458');
								mt = 'application/x-director';
								break;

							case 'mceItemWindowsMedia':
								ci = '6bf52a52-394a-11d3-b153-00c04f79faa6';
								cb = 'http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=' + ed.getParam('media_version_windowsmedia', '5,1,52,701');
								mt = 'application/x-mplayer2';
								break;

							case 'mceItemQuickTime':
								ci = '02bf25d5-8c17-4b23-bc80-d3488abddc6b';
								cb = 'http://www.apple.com/qtactivex/qtplugin.cab#version=' + ed.getParam('media_version_quicktime', '6,0,2,0');
								mt = 'video/quicktime';
								break;

							case 'mceItemRealMedia':
								ci = 'cfcdaa03-8be4-11cf-b84b-0020afbbccfa';
								cb = 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=' + ed.getParam('media_version_realplayer', '7,0,0,0');
								mt = 'audio/x-pn-realaudio-plugin';
								break;

							// +Added DivX
							case 'mceItemDivX':
								ci = '67dabfbf-d0ab-41fa-9c46-cc0f21721616';
								cb = 'http://go.divx.com/plugin/DivXBrowserPlugin.cab';
								mt = 'video/divx';
								break;
							
							// +Added PDF
							case 'mceItemPDF':
								ci = 'ca8a9780-280d-11cf-a24d-444553540000';
								cb = '';
								mt = 'application/pdf';
								break;
						}

						if (ci) {
							dom.replace(t._buildObj({
								classid : ci,
								codebase : cb,
								type : mt
							}, n), n);
						}
					});
				}
			});

			ed.onPostProcess.add(function(ed, o) {
				o.content = o.content.replace(/_mce_value=/g, 'value=');
			});

			function getAttr(s, n) {
				n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);

				return n ? ed.dom.decode(n[1]) : '';
			};

			ed.onPostProcess.add(function(ed, o) {
				if (ed.getParam('media_use_script')) {
					o.content = o.content.replace(/<img[^>]+>/g, function(im) {
						var cl = getAttr(im, 'class');

						if (/^(mceTempFlash|mceTempShockWave|mceTempWindowsMedia|mceTempQuickTime|mceTempRealMedia|mceTempDivX)$/.test(cl)) {
							at = t._parse(getAttr(im, 'title'));
							at.width = getAttr(im, 'width');
							at.height = getAttr(im, 'height');
							im = '<script type="text/javascript">write' + cl.substring(7) + '({' + t._serialize(at) + '});</script>';
						}

						return im;
					});
				}
			});
		},

		getInfo : function() {
			return {
				longname : 'Media',
				author : 'Moxiecode Systems AB / Ryan Demmer',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/media',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		// Private methods
		_objectsToSpans : function(ed, o) {
			var t = this, h = o.content;

			h = h.replace(/<script[^>]*>\s*write(Flash|ShockWave|WindowsMedia|QuickTime|RealMedia|DivX)\(\{([^\)]*)\}\);\s*<\/script>/gi, function(a, b, c) {
				var o = t._parse(c);

				return '<img class="mceItem' + b + '" title="' + ed.dom.encode(c) + '" src="' + t.url + '/img/trans.gif" width="' + o.width + '" height="' + o.height + '" />'
			});

			h = h.replace(/<object([^>]*)>/gi, '<span class="mceItemObject" $1>');
			h = h.replace(/<embed([^>]*)\/?>/gi, '<span class="mceItemEmbed" $1></span>');
			h = h.replace(/<embed([^>]*)>/gi, '<span class="mceItemEmbed" $1>');
			h = h.replace(/<\/(object)([^>]*)>/gi, '</span>');
			h = h.replace(/<\/embed>/gi, '');
			h = h.replace(/<param([^>]*)>/gi, function(a, b) {return '<span ' + b.replace(/value=/gi, '_mce_value=') + ' class="mceItemParam"></span>'});
			h = h.replace(/\/ class=\"mceItemParam\"><\/span>/gi, 'class="mceItemParam"></span>');

			o.content = h;
		},

		_buildObj : function(o, n) {
			var ob, ed = this.editor, dom = ed.dom, p = this._parse(n.title), stc, oh;
			
			stc = ed.getParam('media_strict') && o.type == 'application/x-shockwave-flash';

			p.width = o.width = dom.getAttrib(n, 'width') || 100;
			p.height = o.height = dom.getAttrib(n, 'height') || 100;

			if (p.src)
				p.src = ed.convertURL(p.src, 'src', n);
				
			if (p.flashvars)
				p.flashvars = this._unescape(p.flashvars);
				
			if(p.objecthtml) {
				oh = this._unescape(p.objecthtml);
				delete p.objecthtml;
			}

			if (stc) {
				ob = dom.create('span', {
					id : p.id,
					mce_name : 'object',
					type : 'application/x-shockwave-flash',
					data : p.src,
					style : dom.getAttrib(n, 'style'),
					width : o.width,
					height : o.height
				});
				p.movie = p.src;
				delete(p.src);
			} else {
				ob = dom.create('span', {
					id : p.id,
					mce_name : 'object',
					classid : "clsid:" + o.classid,
					style : dom.getAttrib(n, 'style'),
					codebase : o.codebase,
					width : o.width,
					height : o.height
				});
			}

			each (p, function(v, k) {
				if (!/^(width|height|codebase|classid|id)$/.test(k)) {
					// Use url instead of src in IE for Windows media
					if (o.type == 'application/x-mplayer2' && k == 'src' && !p.url)
						k = 'url';

					if (v)
						dom.add(ob, 'span', {mce_name : 'param', name : k, '_mce_value' : v});
				}
			});

			if (!stc)
				dom.add(ob, 'span', tinymce.extend({mce_name : 'embed', type : o.type, style : dom.getAttrib(n, 'style')}, p));
				
			if(oh)
				ob.innerHTML = ob.innerHTML + oh;

			return ob;
		},

		_spansToImgs : function(p) {
			var t = this, dom = t.editor.dom, ci;
			
			each(dom.select('span.mceItemObject, span.mceItemEmbed', p), function(n) {
				ci = dom.getAttrib(n, "classid") || dom.getAttrib(n, 'type');
				
				each(dom.select('*:not(span.mceItemEmbed, span.mceItemParam)', n), function(el){
					dom.add(n, 'span', {
						'name'		:'objecthtml', 
						'_mce_value': t._escape(dom.getOuterHTML(el)), 
						'class'		: 'mceItemParam'
					})
				});
				
				if(!ci && n.className == 'mceItemObject') {
					// Find embed
					each(n.childNodes, function(c){
						if(dom.hasClass(c, 'mceItemEmbed'))
							ci = dom.getAttrib(c, 'type');
					});
				}
				if(ci)
					ci = ci.toLowerCase().replace(/\s+/g, '');
					
				switch (ci) {
					case 'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000':
					case 'application/x-shockwave-flash':
						dom.replace(t._createImg('mceItemFlash', n), n);
						break;

					case 'clsid:166b1bca-3f9c-11cf-8075-444553540000':
					case 'application/x-director':
						dom.replace(t._createImg('mceItemShockWave', n), n);
						break;

					case 'clsid:6bf52a52-394a-11d3-b153-00c04f79faa6':
					case 'clsid:22d6f312-b0f6-11d0-94ab-0080c74c7e95':
					case 'clsid:05589fa1-c356-11ce-bf01-00aa0055595a':
					case 'application/x-mplayer2':
						dom.replace(t._createImg('mceItemWindowsMedia', n), n);
						break;

					case 'clsid:02bf25d5-8c17-4b23-bc80-d3488abddc6b':
					case 'video/quicktime':
						dom.replace(t._createImg('mceItemQuickTime', n), n);
						break;

					case 'clsid:cfcdaa03-8be4-11cf-b84b-0020afbbccfa':
					case 'audio/x-pn-realaudio-plugin':
						dom.replace(t._createImg('mceItemRealMedia', n), n);
						break;
					// +Added DivX	
					case 'clsid:67dabfbf-d0ab-41fa-9c46-cc0f21721616':
					case 'video/divx':
						dom.replace(t._createImg('mceItemDivX', n), n);
						break;
					// Added PDF
					case 'clsid:ca8a9780-280d-11cf-a24d-444553540000':
					case 'application/pdf':
						dom.replace(t._createImg('mceItemPDF', n), n);
						break;

					default:
						dom.replace(t._createImg('mceItemGeneric', n), n);
						break;
				}
				return;	
			});
		},

		_createImg : function(cl, n) {
			var t = this, im, dom = this.editor.dom, pa = {}, ti = '', args = ['title', 'flashvars', 'src', 'wmode', 'allowfullscreen', 'quality'];	

			// Create image
			im = dom.create('img', {
				src 	: this.url + '/img/trans.gif',
				width 	: dom.getAttrib(n, 'width') || 100,
				height 	: dom.getAttrib(n, 'height') || 100,
				style 	: dom.getAttrib(n, 'style'),
				'class' : cl
			});
			
			each(['bgcolor', 'align', 'border', 'vspace', 'hspace'], function(na){
				var v = dom.getAttrib(n, na);
				if (v) {
					switch(na) {
						case 'bgcolor':
							dom.setStyle(im, 'background-color', v);
							break;
						case 'align':
							if(/^(left|right)$/.test(v)){
								dom.setStyle(im, 'float', v);
							} else {
								dom.setStyle(im, 'vertical-align', v);
							}
							break;
						case 'vspace':
							dom.setStyle(im, 'margin-top', v);
							dom.setStyle(im, 'margin-bottom', v);
							break;
						case 'hspace':
							dom.setStyle(im, 'margin-left', v);
							dom.setStyle(im, 'margin-right', v);
							break;
					}
				}
			});
			
			each(['id', 'name'], function(na){
				var v = dom.getAttrib(n, na);
				if (v) {					
					im[na] = v;
				}
			});
			
			if(cl == 'mceItemGeneric'){
				each(['type', 'classid', 'codebase'], function(na){														   
					var v = dom.getAttrib(n, na);				
					if (v){
						pa[na] = v;
					}
				});
			}

			// Add optional parameters
			each(dom.select('span', n), function(n) {
				if (dom.hasClass(n, 'mceItemParam')) {
					var k = dom.getAttrib(n, 'name'), v = dom.getAttrib(n, '_mce_value');
					if (k == 'flashvars') {
						v = t._escape(v);
					}
					pa[k] = v;
				}
			});

			// Use src not movie
			if (pa.movie) {
				pa.src = pa.movie;
				delete pa.movie;
			}

			// Merge with embed args
			each(args, function(na) {
				var v = dom.getAttrib(n, na);

				if (v && !pa[na]) {
					if (na == 'flashvars') {
						v = t._escape(v);
					}
					pa[na] = v;
				}
			});

			delete pa.width;
			delete pa.height;

			im.title = this._serialize(pa);

			return im;
		},
		
		_unescape : function(s) {
			return decodeURIComponent(s).replace(/%27/g, "'");
		},
		
		_escape : function(s) {
			return encodeURIComponent(s.replace(/'/g, '%27'));
		},

		_parse : function(s) {
			//return tinymce.util.JSON.parse('{' + s + '}');
			var o = {}
			each(s.replace(/[{}"]/g, '').split(','), function(kv) {
				kv = kv.replace(/([\w]+):([\w%-_\.!~\*\(\)]+)/g, function(a, b, c){
					o[b] = c.replace(/^'|'$/g, '');
				});
			});
			return o;
		},

		_serialize : function(o) {
			return tinymce.util.JSON.serialize(o).replace(/[{}]/g, '');
		}
	});

	// Register plugin
	tinymce.PluginManager.add('media', tinymce.plugins.MediaPlugin);
})();