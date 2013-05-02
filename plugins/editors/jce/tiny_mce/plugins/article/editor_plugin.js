/**
* @version		$Id: editor_plugin.js 153 2009-07-11 13:17:26Z happynoodleboy $
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
	tinymce.create('tinymce.plugins.ArticlePlugin', {
		init : function(ed, url) {		
			var t = this;		
			
			t.editor = ed; 
			t.url = url;

			function isReadMore(n) {
				return n.nodeName == 'IMG' && ed.dom.hasClass(n, 'mceItemReadMore');
			};
			
			function isPageBreak(n) {
				return n.nodeName == 'IMG' && ed.dom.hasClass(n, 'mceItemPageBreak');
			};
			
			// Add alt element..grrrrr :(
			ed.onPreInit.add(function() {
				ed.serializer.addRules('hr[alt|id|class|style|title|dir<ltr?rtl|lang|xml::lang|onclick|ondblclick|onmousedown|onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|onkeydown|onkeyup]');
			});
			// Register commands
			ed.addCommand('mceReadMore', function() {
				if(ed.dom.get('system-readmore')){
					alert(ed.getLang('article.readmore_alert', 'There is already a Read More break inserted in this article. Only one such break is permitted. Use a Pagebreak to split the page up further.'))
					return false;
				}
				t._insertBreak('readmore', {id: 'system-readmore'});
			});
			ed.addCommand('mcePageBreak', function(ui, v) {
				if (ui) {
					ed.windowManager.open({
						file : url + '/pagebreak.htm',
						width : 400,
						height : 100,
						inline : 1
					}, {
						plugin_url : url
					});
				} else {
					tinymce.extend(v, {'id': 'system-pagebreak'});
					t._insertBreak('pagebreak', v);
				}
			});
			// Register buttons
			if (ed.getParam('article_readmore', true)) {
				ed.addButton('readmore', {
					title : 'article.readmore', 
					cmd : 'mceReadMore', 
					image : url + '/img/readmore.gif'
				});
			}
			if (ed.getParam('article_pagebreak', true)) {
				ed.addButton('pagebreak', {
					title: 'article.pagebreak',
					cmd: 'mcePageBreak',
					image: url + '/img/pagebreak.gif',
					ui: true
				});
			}	
			
			ed.onInit.add(function() {
				ed.dom.loadCSS(url + "/css/content.css");
				
				if(ed.getParam('article_hide_xtd_btns', false)){
					// Remove Joomla! Readmore / PageBreak buttons
				tinymce.DOM.hide(ed.dom.select('div.readmore, div.pagebreak', tinymce.DOM.get('editor-xtd-buttons')));
				}				
			});
			
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('readmore',  isReadMore(n));
				cm.setActive('pagebreak', isPageBreak(n));
			});
			// preserve alt element	
			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = o.content.replace(/<hr([^>]*)alt="([^"]+?)"([^>]*)\/>/gi, function(a, b, c, d){
					if(/system-pagebreak/.test(a)){
						return a;
					}else{
						return '<hr'+ b + d +'/>';
					}
				});	
			});	
				
			ed.onSetContent.add(function() {
				var dom = ed.dom, p = ed.getBody();
				each(dom.select('HR', p), function(n) {
					if(ed.dom.getAttrib(n, 'id') == 'system-readmore' || ed.dom.hasClass(n, 'system-pagebreak')){
						dom.replace(t._createImg(n), n);
					}
				});
			});

			ed.onPreProcess.add(function(ed, o) {
				var dom = ed.dom;				
				if (o.get) {
					each(dom.select('IMG', o.node), function(n) {
						if(isReadMore(n) || isPageBreak(n)){
							dom.replace(t._buildHR(n), n);
						}
					});
				}
			});
			
			ed.onPostProcess.add(function(ed, o) {			
				if (o.get && ed.getParam('forced_root_block')) {
					o.content = o.content.replace(/<(p|div)>[\s\n]*?<hr([\s\S]+)(id|class)="system-(readmore|pagebreak)"([^>]+?)>[\s\n]*?<\/(p|div)>/gi, '<hr$2$3="system-$4"$5>');
				}
				o.content = o.content.replace(/<hr([^>]*)alt="([^"]+?)"([^>]*)\/>/gi, function(a, b, c, d){
					if(/system-pagebreak/.test(a)){
						return a;
					}else{
						return '<hr'+ b + d +'/>';
					}
				});	
			});
		},
		_insertBreak : function(s, args){
			var t = this, ed = this.editor, n = ed.selection.getNode(), h, c, re, isBlock = false, desc = '';
			var bElm = 'P,DIV,ADDRESS,PRE,FORM,TABLE,OL,UL,CAPTION,BLOCKQUOTE,CENTER,DL,DIR,FIELDSET,NOSCRIPT,NOFRAMES,MENU,ISINDEX,SAMP';

			n = ed.dom.getParent(n, bElm, 'BODY') || n;
			
			tinymce.extend(args, {src: t.url + '/img/trans.gif', 'class': s == 'pagebreak' ? 'mceItemPageBreak mceItemNoResize' : 'mceItemReadMore mceItemNoResize'});
			
			// Insert initial node
			ed.selection.setContent(ed.dom.createHTML('img', args));
			
			if(ed.dom.isBlock(n)){			
				// Create new img
				r = ed.dom.create('img', args);
				p = ed.dom.getParent(r, bElm, 'BODY');
				
				if(/^(P|DIV)$/.test(n.nodeName)) {
					// Split node html
					h = n.innerHTML.split(/<img[\s\S]+id="?system-(readmore|pagebreak)"?[^>]+>/i);	
														
					// Re-assign innerHTML
					n.innerHTML = h[0] || '';
					
					if (!n.nextSibling) {
						var el = ed.getParam('forced_root_block') || 'p';
						ed.dom.insertAfter(ed.dom.create(el), n);
					}
					
					// Insert second block element and img
					if(h[0] === '' || typeof h[0] == 'undefined') {
						if(p){
							//ed.dom.insertAfter(r, p);	
							p.insertBefore(r, n);
						}else{
							n.parentNode.insertBefore(r, n);	
						}
					} else {
						ed.dom.insertAfter(r, n);
					}
					
					if(h[2] && h[2] != '<br>'){
						c = n.cloneNode(true);							
						c.innerHTML = h[2];
						ed.dom.insertAfter(c, r);
					}
				} else {
					// Remove old img
					ed.dom.remove('system-' + s);
					// If in block
					if(p){
						p.insertBefore(r, n);
					}else{
						n.parentNode.insertBefore(r, n);	
					}
					p = r.parentNode;
					
					while(/^(ADDRESS|PRE|FORM|TABLE|OL|UL|CAPTION|BLOCKQUOTE|CENTER|DL|DIR|FIELDSET|NOSCRIPT|NOFRAMES|MENU|ISINDEX|SAMP)$/.test(p.nodeName)) {
						p.parentNode.insertBefore(r, p);
						p = r.parentNode;
					}
				}
			}
			ed.dom.setAttrib(ed.dom.get('system-pagebreak'), 'id', '');
		},
		// Private methods

		_buildHR : function(n) {
			var ed = this.editor, dom = ed.dom, args;

			if(dom.hasClass(n, 'mceItemPageBreak')){
				args = {
					title 	: dom.getAttrib(n, 'title', ''),
					alt		: dom.getAttrib(n, 'alt', ''),
					'class'	: 'system-pagebreak'
				};	
			}else{
				args = {
					id	 : 'system-readmore'
				};	
			}
			return dom.create('hr', args);		
		},

		_createImg : function(n) {
			var ed = this.editor, dom = ed.dom, t = this, args;
			
			args = {
				src		: t.url + '/img/trans.gif',
				'class' : 'mceItemReadMore'
			};
			
			if(dom.hasClass(n, 'system-pagebreak')){
				tinymce.extend(args, {'class' : 'mceItemPageBreak', title : dom.getAttrib(n, 'title', ''), alt : dom.getAttrib(n, 'alt', '')});
			}else{
				tinymce.extend(args, {id : 'system-readmore', alt : ed.getLang('article.readmore_title', 'Read More'), title : ed.getLang('article.readmore_title', 'Read More')});	
			}
			return dom.create('img', args);
		},
						
		getInfo : function() {
			return {
				longname : 'Article',
				author : 'Ryan Demmer',
				authorurl : 'http://www.joomlacontenteditor.net',
				infourl : 'http://www.joomlacontenteditor.net',
				version : '1.5.2'
			};
		}
	});
	// Register plugin
	tinymce.PluginManager.add('article', tinymce.plugins.ArticlePlugin);
})();