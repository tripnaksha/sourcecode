/**
* @version		$Id: editor_plugin.js 83 2009-06-08 12:59:29Z happynoodleboy $
* @package      JCE
* @copyright    Copyright (C) 2005 - 2009 Ryan Demmer. All rights reserved.
* @author		Ryan Demmer
* @license      GNU/GPL
* JCE is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/
(function() {
	var each = tinymce.each;

	tinymce.create('tinymce.plugins.CodePlugin', {
		init : function(ed, url) {
			var t = this;
			
			t.editor = ed;
			t.url = url;
			
			ed.onPreInit.add(function() {
				// Add iframe to valid elements
				if(ed.getParam('code_javascript')){
					ed.serializer.addRules('script[src|charset|defer|type|xml::space]');
				}
				if(ed.getParam('code_css')){
					ed.serializer.addRules('style[type|media|dir|lang|xml::lang]');
				}
				// Add a global '_php' attribute
				ed.serializer.addRules('@[_php]');
			});
			
			ed.onInit.add(function() {
				ed.dom.loadCSS(url + "/css/content.css");
			});
			
			ed.onBeforeSetContent.add(function(ed, o) {							   
				// test for PHP, Script or Style
				if (/<(\?|script|style)/.test(o.content)) {
					// Remove javascript if not enabled
					if(!ed.getParam('code_javascript')){
						o.content = o.content.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, '');
					}
					// Remove style if not enabled
					if(!ed.getParam('code_css')){
						o.content = o.content.replace(/<style[^>]*>([\s\S]*?)<\/style>/gi, '');
					}
					// Remove PHP if not enabled
					if(!ed.getParam('code_php')){
						o.content = o.content.replace(/<\?(php)?([\s\S]*?)\?>/gi, '');
					}
					// PHP code within an attribute
					o.content = o.content.replace(/="([^"]+?)"/g, function(a, b){
						if(/<\?(php)?/.test(b)){
							b = ed.dom.encode(b);
						}
						return '="'+ b +'"';
					});
					// PHP code within a textarea
					if(/<textarea/.test(o.content)){
						o.content = o.content.replace(/<textarea([^>]*)>([\s\S]*?)<\/textarea>/gi, function(a, b, c){
							if(/<\?(php)?/.test(c)){
								c = ed.dom.encode(c);
							}
							return '<textarea' + b + '>' + c + '</textarea>';															 
						});
					}
					// Preserve script elements
					o.content = o.content.replace(/<(script|style)([^>]+)>([\s\S]*?)<\/(script|style)>/gi, function(v, a, b, c) {
						a = a.toUpperCase();
						
						// Remove prefix and suffix code for script element
						c = t._trim(c);
						// Convert php
						c = c.replace(/<\?(php)?([\s\S]+?)\?>/gi, '<span class="mcePHP">$2</span>');
						// Remove deprecated language attribute
						b = b.replace(/(language="[a-z]+")/gi, '');
						// Output fake element
						return '<span '+b+'class="mce'+ a +'"><!--'+ a + c + a +'--></span>';
					});
					// PHP code within an element
					o.content = o.content.replace(/<([^>]+)<\?(php)?(.+?)\?>([^>]*?)>/gi, function(a, b, c, d, e){
						if(b.charAt(b.length) != ' '){
							b += ' ';
						}
						return '<'+ b + '_php="' + d + '" '+ e +'>';
					});
					// PHP code other				
					o.content = o.content.replace(/<\?(php)?([\s\S]+?)\?>/gi, '<span class="mcePHP"><!--PHP$2PHP--></span>');
				}
			});
			
			ed.onSetContent.add(function(ed, o) {
				var dom = ed.dom, v;
				each(dom.select('span.mceSCRIPT, span.mceSTYLE', ed.getBody()), function(n) {
					if (!n.title) {
						t._serializeSpan(n);
					}
				});
			});
			
			ed.onPreProcess.add(function(ed, o) {
				var dom = ed.dom, v;
			
				if (o.set) {
					each(dom.select('span.mceSCRIPT, span.mceSTYLE', o.node), function(n) {
						if (!n.title) {
							t._serializeSpan(n);
						}
					});
				}
				
				if (o.get) {
					each(dom.select('span.mceSCRIPT', o.node), function(n) {
						dom.replace(t._buildScript(n), n);
					});
					each(dom.select('span.mceSTYLE', o.node), function(n) {
						dom.replace(t._buildStyle(n), n);
					});		
				}
			});
			
			ed.onPostProcess.add(function(ed, o) {
				if (o.get){
					// Process converted php
					if(/mcePHP/.test(o.content) || /&lt;\?(php)?/.test(o.content)){
						o.content = o.content.replace(/&lt;span class="mcePHP"&gt;([^"]+)&lt;\/span&gt;/g, function(a, b, c, d, e){
							return t._decode(a);
						});
						o.content = o.content.replace(/"(.*?)&lt;\?(php)?([^"]+)\?&gt;(.*?)"/g, function(a, b, c, d, e){
							return '"' + b + '<?php' + t._decode(d) + '?>' + e + '"';
						});
						o.content = o.content.replace(/<textarea([^>]*)>([\s\S]*?)<\/textarea>/gi, function(a, b, c){
							if(/&lt;\?php/.test(c)){
								c = t._decode(c);	
							}
							return '<textarea' + b + '>' + c + '</textarea>';
						});
						o.content = o.content.replace(/_php="([^"]+)"/g, function(a, b){
							return '<?php' + t._decode(b) + '?>';
						});
						o.content = o.content.replace(/<span class="mcePHP">(<!--PHP)?([\s\S]*?)(PHP-->)?<\/span>/g, function(a, b, c, d){
							return '<?php' + t._decode(c) + '?>';																				   
						});
					}
					if(/<(script|style)/.test(o.content)){
						o.content = o.content.replace(/<(script|style)([^>]+)>([\s\S]*?)<\/(script|style)>/g, function(a, b, c, d){
							// Remove all CDATA etc.
							d = t._trim(d);
							
							// Replace <br /> elements
							d = d.replace(/<br([^>]*?)>/gi, '\n');
							d = d.replace(/&nbsp;/g, ' ');
							d = t._decode(d);
													
							if(d && /\S*/.test(d)) {
								// Add CDATA if required
								if(d && ed.getParam('code_cdata', false)){
									if(b == 'style') {
										d = '\n<!--\n'+ d +'\n-->\n';
									} else {
										d = '\n// <![CDATA[\n'+ d +'\n// ]]>\n';
									}
								} else {
									d = '\n' + d + '\n';
								}	
							}							
							return '<' + b + c + '>'+ d +'</' + b + '>';	
						});	
					}
				}
			});
		},
		
		_buildScript : function(n){
			var t = this, ed = this.editor, dom = ed.dom, ob, h,  p = this._parse(n.title);		
			
			p.type 	= 'text/javascript';
			// Decode to avoid double encoding
			if (p.src) {
				p.src 	= p.src.replace(/&amp;/g, '&'); 
			}

			h = n.innerHTML.replace(/<!--SCRIPT([\s\S]*?)SCRIPT-->/, function(a, b){
				return b;
			});
			
			// decode contents
			h = t._decode(h);
			
			// Create element
			if (tinymce.isIE) {
				ob = dom.create('span', tinymce.extend({
					mce_name: 'script'
				}, p));
			} else {
				ob = dom.create('script', p);
			}

			if(tinymce.isIE) {
				ob.innerText = h;
			} else {
				ob.appendChild(document.createTextNode(h));
			}
	
			return ob;
		},
		
		_buildStyle : function(n){
			var t = this, ed = this.editor, dom = ed.dom, ob, p = this._parse(n.title), h;			

			p.type = 'text/css';

			h = n.innerHTML.replace(/<!--STYLE([\s\S]*?)STYLE-->/, function(a, b){
				return b;
			});
			// decode contents
			h = t._decode(h);	
			
			// Create element
			if (tinymce.isIE) {
				ob = dom.create('span', tinymce.extend({
					mce_name: 'style'
				}, p));
			} else {
				ob = dom.create('style', p);
			}

			if(tinymce.isIE) {
				ob.innerText = h;
			} else {
				ob.appendChild(document.createTextNode(h));
			}
			
			return ob;
		},
		
		_serializeSpan : function(n){
			var t = this, ed = this.editor, dom = ed.dom, v, k, p = {};
			
			each(['src','charset','defer','type','xml:space','media'], function(k){
				var v = dom.getAttrib(n, k);
				if (v){
					p[k] = t._encode(v);
				}
				n.removeAttribute(k);
			});
			n.removeAttribute('mce_src');
			n.title = this._serialize(p);
		},
		
		_parse : function(s) {
			return tinymce.util.JSON.parse('{' + s + '}');
		},

		_serialize : function(o) {
			return tinymce.util.JSON.serialize(o).replace(/[{}]/g, '').replace(/"/g, "'");
		},
		
		_decode : function(s){
			return s.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&').replace(/&quot;/g, '"');	
		},
		
		_encode : function(s){
			s = s.replace(/&amp;/g, '&');
			return this.editor.dom.encode(s);
		},
		
		// Private internal function
		 _trim : function(s) {
			// Remove prefix and suffix code for element
			s = s.replace(/(\/\/\s+<!\[CDATA\[)/gi, '\n');
			s = s.replace(/(<!--\[CDATA\[|\]\]-->)/gi, '\n');
			s = s.replace(/^[\r\n]*|[\r\n]*$/g, '');
			s = s.replace(/^\s*(\/\/\s*<!--|\/\/\s*<!\[CDATA\[|<!--|<!\[CDATA\[)[\r\n]*/gi, '');
			s = s.replace(/\s*(\/\/\s*\]\]>|\/\/\s*-->|\]\]>|-->|\]\]-->)\s*$/g, '');
			return s;
		},

		getInfo : function() {
			return {
				longname : 'Code',
				author : 'Ryan Demmer',
				authorurl : 'http://www.joomlacontenteditor.net',
				infourl : 'http://www.joomlacontenteditor.net',
				version : '@@version@@'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('code', tinymce.plugins.CodePlugin);
})();