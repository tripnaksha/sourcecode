/**
 * @author Moxiecode
 * @author Ryan Demmer
 * @copyright Copyright (c) 2004-2008, Moxiecode Systems AB, All rights reserved.
 * $Id: editor_plugin.js 173 2009-07-11 13:26:46Z happynoodleboy $
 */

(function() {
	var each = tinymce.each;

	tinymce.create('tinymce.plugins.PastePlugin', {
		init : function(ed, url) {
			var t = this, cb;

			t.editor = ed;
			t.url = url;

			// Setup plugin events
			t.onPreProcess 		= new tinymce.util.Dispatcher(t);
			t.onPostProcess 	= new tinymce.util.Dispatcher(t);

			// Register default handlers
			t.onPreProcess.add(t._preProcess);
			t.onPostProcess.add(t._postProcess);

			// Register optional preprocess handler
			t.onPreProcess.add(function(pl, o) {
				ed.execCallback('paste_preprocess', pl, o);
			});

			// Register optional postprocess
			t.onPostProcess.add(function(pl, o) {
				ed.execCallback('paste_postprocess', pl, o);
			});
			
			t.pasteText 	= ed.getParam('paste_text', 1);
			t.pasteHtml		= ed.getParam('paste_html', 1);

			// This function executes the process handlers and inserts the contents
			function process(o) {
				var dom = ed.dom;

				// Execute pre process handlers
				t.onPreProcess.dispatch(t, o);

				// Create DOM structure
				o.node = dom.create('div', 0, o.content);

				// Execute post process handlers
				t.onPostProcess.dispatch(t, o);

				// Serialize content
				o.content = ed.serializer.serialize(o.node, {getInner : 1});

				//  Insert cleaned content. We need to handle insertion of contents containing block elements separately
				if (/<(p|h[1-6]|ul|ol)/.test(o.content))
					t._insertBlockContent(ed, dom, o.content);
				else
					t._insert(o.content);
			};

			// Add command for external usage
			ed.addCommand('mceInsertClipboardContent', function(u, o) {
				process(o);
			});
			
			ed.onInit.add(function(){
				if (ed.plugins.contextmenu) {
					ed.plugins.contextmenu.onContextMenu.add(function(th, m, e) {
						var c = ed.selection.isCollapsed();
						m.add({title : 'advanced.cut_desc', icon : 'cut', cmd : 'Cut'}).setDisabled(c);
						m.add({title : 'advanced.copy_desc', icon : 'copy', cmd : 'Copy'}).setDisabled(c);
						if (t.pasteHtml) {
							m.add({title : 'paste.paste_desc', icon : 'paste', cmd : 'mcePaste'});
						}
						if (t.pasteText) {
							m.add({title : 'paste.paste_text_desc', icon : 'pastetext', cmd : 'mcePasteText'});
						}
					});
				}				
			});

			// This function grabs the contents from the clipboard by adding a
			// hidden div and placing the caret inside it and after the browser paste
			// is done it grabs that contents and processes that
			function grabContent(e) {
				var n, or, rng, sel = ed.selection, dom = ed.dom, body = ed.getBody(), posY;

				if (dom.get('_mcePaste'))
					return;

				// Create container to paste into
				n = dom.add(body, 'div', {id : '_mcePaste'}, '\uFEFF');

				// If contentEditable mode we need to find out the position of the closest element
				if (body != ed.getDoc().body)
					posY = dom.getPos(ed.selection.getStart(), body).y;
				else
					posY = body.scrollTop;

				// Styles needs to be applied after the element is added to the document since WebKit will otherwise remove all styles
				dom.setStyles(n, {
					position : 'absolute',
					left : -10000,
					top : posY,
					width : 1,
					height : 1,
					overflow : 'hidden'
				});

				if (tinymce.isIE) {
					// Select the container
					rng = dom.doc.body.createTextRange();
					rng.moveToElementText(n);
					rng.execCommand('Paste');

					// Remove container
					dom.remove(n);

					// Check if the contents was changed, if it wasn't then clipboard extraction failed probably due
					// to IE security settings so we pass the junk though better than nothing right
					if (n.innerHTML === '\uFEFF') {
						ed.execCommand('mcePaste');
						e.preventDefault();
						return;
					}

					// Process contents
					process({
						content: n.innerHTML
					});
					
					// Block the real paste event
					return tinymce.dom.Event.cancel(e);
				} else {
					or = ed.selection.getRng();

					// Move caret into hidden div
					n = n.firstChild;
					rng = ed.getDoc().createRange();
					rng.setStart(n, 0);
					rng.setEnd(n, 1);
					sel.setRng(rng);

					// Wait a while and grab the pasted contents
					window.setTimeout(function() {
						var h = '', nl = dom.select('div[id=_mcePaste]');

						// WebKit will split the div into multiple ones so this will loop through then all and join them to get the whole HTML string
						each(nl, function(n) {
							h += (dom.select('> span.Apple-style-span div', n)[0] || dom.select('> span.Apple-style-span', n)[0] || n).innerHTML;
						});

						// Remove the nodes
						each(nl, function(n) {
							dom.remove(n);
						});

						// Restore the old selection
						if (or)
							sel.setRng(or);

						process({content : h});
					}, 0);
				}
			};

			// Check if we should use the new auto process method			
			if (ed.getParam('paste_auto_cleanup_on_paste', true)) {
				// Is it's Opera or older FF use key handler
				if (tinymce.isOpera || /Firefox\/2/.test(navigator.userAgent)) {
					ed.onKeyDown.add(function(ed, e) {
						if (((tinymce.isMac ? e.metaKey : e.ctrlKey) && e.keyCode == 86) || (e.shiftKey && e.keyCode == 45))
							grabContent(e);
					});
				} else {
					// Grab contents on paste event on Gecko and WebKit
					ed.onPaste.addToTop(function(ed, e) {
						return grabContent(e);
					});
				}
			}

			// Block all drag/drop events
			if (ed.getParam('paste_block_drop', true)) {
				ed.onInit.add(function() {
					ed.dom.bind(ed.getBody(), ['dragend', 'dragover', 'draggesture', 'dragdrop', 'drop', 'drag'], function(e) {
						e.preventDefault();
						e.stopPropagation();

						return false;
					});
				});
			}
			// Add commands
			each(['mcePasteText', 'mcePaste'], function(cmd) {			
				ed.addCommand(cmd, function() {
					t.command = cmd;		
					try {
						if(tinymce.isWebKit) {
							// On WebKit the command will just be ignored if it's not enabled
							if (!ed.getDoc().queryCommandSupported('Paste')){
								t._openWin(cmd);
							}
						} else {
							ed.getDoc().execCommand('Paste', false, null);
						}						
					} catch (ex) {
						t._openWin(cmd);
					}	
				});
			});
			
			if(t.pasteHtml && !t.pasteText) {
				ed.addButton('paste', {title : 'paste.paste_desc', cmd : 'mcePaste', ui : true});
			}
			if(!t.pasteHtml && t.pasteText) {
				ed.addButton('paste', {title : 'paste.paste_text_desc', cmd : 'mcePasteText', ui : true, image : t.url + '/img/pastetext.gif'});	
			}
			
			// Add legacy support
			t._legacySupport();
		},

		createControl: function(n, cm) {
			var t = this, ed = t.editor, doc = ed.getDoc();
			
			switch (n) {	 
				case 'paste':
					if(t.pasteHtml && t.pasteText) {
						var c = cm.createSplitButton('paste', {
							title : 'paste.paste_desc',
							image : t.url + '../../../themes/advanced/img/paste.gif',
							onclick : function() {
								ed.execCommand('mcePaste');
							}
						});
		 
						c.onRenderMenu.add(function(c, m) {
							m.add({
								title : 'paste.paste_desc',
								icon : 'paste',
								onclick : function() {
									ed.execCommand('mcePaste');
								}
							});
		 					
							m.add({
								title : 'paste.paste_text_desc',
								icon : 'pastetext',
								onclick : function() {
									ed.execCommand('mcePasteText');
								}
							});
						});
						// Return the new splitbutton instance
						return c;	
					}
				break;
			}
	 
			return null;
		},

		getInfo : function() {
			return {
				longname : 'Paste text/word',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/paste',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},
		
		_openWin : function(cmd){
			var t = this, ed = this.editor;
			
			ed.windowManager.open({
				file 	: t.url + '/paste.htm',
				width 	: parseInt(ed.getParam("paste_dialog_width", "450")),
				height 	: parseInt(ed.getParam("paste_dialog_height", "400")),
				inline 	: 1
			}, {
				cmd : cmd
			});	
		},
		
		_processRe : function(items, h) {
			each(items, function(v) {
				// Remove or replace
				if (v.constructor == RegExp)
					h = h.replace(v, '');
				else
					h = h.replace(v[0], v[1]);
			});
			
			return h;
		},

		_preProcess : function(pl, o) {
			var ed = this.editor, h = o.content;

			if (this.command == 'mcePasteText' || (this.pasteText && !this.pasteHtml)) {
				o.wordContent = false;
				h = this._processPlainText(h);
			} else {
				// Process away some basic content
				h = this._processRe([
					/^\s*(&nbsp;)+/g, // nbsp entities at the start of contents
				 	/(&nbsp;|<br[^>]*>)+\s*$/g // nbsp entities at the end of contents
				], h);

				// Open Office
				if (/(content=\"OpenOffice.org[^\"]+\")/i.test(h)) {
					o.wordContent = true; // Mark the pasted contents as word specific content
					h = this._processRe([
				 		/[\s\S]+?<meta[^>]*>/, // Remove everything before meta element
						/<!--[\s\S]+?-->/gi, // Comments
						/<style[^>]*>[\s\S]+?<\/style>/gi // Remove styles
					], h);
				}
				// Word
				if (/(class=\"?Mso|style=\"[^\"]*\bmso\-|w:WordDocument)/.test(h)) {
					o.wordContent = true; // Mark the pasted contents as word specific content
					//console.log('Word contents detected.');
					h = this._processWordContent(h);
				}
				
				h = this._processRe([
					/<\/?(font|meta|link|style|title)[^>]*>/gi, // Remove some tags
				 	[/&nbsp;/g, '\u00a0'] // Replace nsbp entites to char since it's easier to handle
				], h);
				
				// Remove all spans if no styles is to be retained
				if (!ed.getParam('paste_retain_style_properties') || ed.getParam('paste_remove_spans')) {
					h = this._processRe([/<\/?(span)[^>]*>/gi], h);
				}
			}

			// Remove empty paragraphs
			if (ed.getParam('paste_remove_empty_paragraphs')) {
				if(ed.getParam('force_br_newlines')){
					h = this._processRe([
						/<p>\s*<\/p>/gi, '<br />'
					], h);
				} else {
					h = this._processRe([
						/<p>\s*<\/p>/gi
					], h);
				}
			}

			//console.log('After preprocess:' + h);

			o.content = h;
		},
		
		_processWordContent : function(h) {
			var ed = this.editor, stripClass;
		
			if (ed.getParam('paste_convert_middot_lists', true)) {
				h = this._processRe([
					[/<!--\[if !supportLists\]-->/gi, '$&__MCE_ITEM__'], // Convert supportLists to a list item marker
					[/(<span[^>]+:\s*symbol[^>]+>)/gi, '$1__MCE_ITEM__'], // Convert symbol spans to list items
				 	[/(<span[^>]+mso-list:[^>]+>)/gi, '$1__MCE_ITEM__'] // Convert mso-list to item marker
				], h);
			}
			
			h = this._processRe([
				/<!--[\s\S]+?-->/gi, // Word comments
			 	/<\/?((v|w):\w+)[^>]*>/gi, // Remove some tags including VML content
			 	/<\\?\?xml[^>]*>/gi, // XML namespace declarations
			 	/<\/?o:[^>]*>/gi, // MS namespaced elements <o:tag>
			 	/ (id|name|language|type|on\w+|(v|w):\w+)=\"([^\"]*)\"/gi, // on.., class, style and language attributes with quotes
			 	/ (id|name|language|type|on\w+|(v|w):\w+)=(\w+)/gi, // on.., class, style and language attributes without quotes (IE)
			 	[/<(\/?)s>/gi, '<$1strike>'], // Convert <s> into <strike> for line-though
			 	/<script[^>]+>[\s\S]*?<\/script>/gi // All scripts elements for msoShowComment for example
			 	
			], h);
			
			// Allow for class names to be retained if desired; either all, or just the ones from Word
			// Note that the paste_strip_class_attributes: 'none, verify_css_classes: true is also a good variation.
			stripClass = ed.getParam('paste_strip_class_attributes', 'all');
			if (stripClass !== "none") {
				h = h.replace(/(<[a-z](?:"(?:[^"]|\\")*"|'(?:[^']|\\')*'|[^"'>])*)\sclass=("(?:[^"]|\\")*"|'(?:[^']|\\')*'|[-\w]+)/gi, function(match, g1, g2){
				
					if (stripClass === "all") {
						return g1;
					}
					
					var cls = tinymce.grep(tinymce.explode(g2.replace(/^(["'])(.*)\1$/, "$2"), " "), function(v){
						return (/^(?!mso)/i.test(v));
					});
					
					return g1 + (cls.length ? 'class="' + cls.join(" ") + '"' : '');
				});
			}

			return h;
		},
		/*
		_processPlainText : function (h) {
			var ed = this.editor, lines;
			if (ed.getParam('paste_keep_linebreaks', true)) {
				h = this._processRe([/<\/p>/g, '\n'], h);
			}
			// remove all html
			h = this._processRe([/(<\/?[^>]+?>)/g], h);
			
			lines = h.split(/\r?\n/);
			if (lines.length > 1) {
				h = '';
				tinymce.each(lines, function(row){
					if (ed.getParam('force_p_newlines')) {
						h += '<p>' + row + '</p>';
					}
					else {
						h += row + '<br />';
					}
				});
			}
			return h;
		},
		*/
		_processPlainText : function (h) {
			var ed = this.editor, dom = ed.dom, i, len, pos, rpos, node, breakElms, before, after, w = ed.getWin(), d = ed.getDoc(), sel = ed.selection, entities = null;
			var linebr = ed.getParam(ed, "force_p_newlines") ? 'p' : 'br';
			var rl = [[/\u2026/g, "..."],[/[\x93\x94\u201c\u201d]/g, '"'],[/[\x60\x91\x92\u2018\u2019]/g, "'"]];

			if ((typeof(h) === "string") && (h.length > 0)) {
			
				if (!entities) {
					entities = ("34,quot,38,amp,39,apos,60,lt,62,gt," + ed.serializer.settings.entities).split(",");
				}
				
				// If HTML content with line-breaking tags, then remove all cr/lf chars because only tags will break a line
				if (/<(?:p|br|h[1-6]|ul|ol|dl|table|t[rdh]|div|blockquote|fieldset|pre|address|center)[^>]*>/i.test(h)) {
					h = this._processRe([
						/[\n\r]+/g
					], h);
				}
				// Otherwise just get rid of carriage returns (only need linefeeds)
				else {
					h = this._processRe([
						/\r+/g
					], h);
				}

				h = this._processRe([
					[/<\/(?:p|h[1-6]|ul|ol|dl|table|div|blockquote|fieldset|pre|address|center)>/gi, "\n\n"],		// Block tags get a blank line after them
					[/<br[^>]*>|<\/tr>/gi, "\n"],				// Single linebreak for <br /> tags and table rows
					[/<\/t[dh]>\s*<t[dh][^>]*>/gi, "\t"],		// Table cells get tabs betweem them
					/<[a-z!\/?][^>]*>/gi,						// Delete all remaining tags
					[/&nbsp;/gi, " "],							// Convert non-break spaces to regular spaces (remember, *plain text*)
					[
						// HTML entity
						/&(#\d+|[a-z0-9]{1,10});/gi,
						
						// Replace with actual character
						function (e, s) {
							if (s.charAt(0) === "#") {
								return String.fromCharCode(s.slice(1));
							}
							else {
								return ((e = tinymce.inArray(entities, s)) > 0)? String.fromCharCode(entities[e-1]) : " ";
							}
						}
					],
					[/(?:(?!\n)\s)*(\n+)(?:(?!\n)\s)*/gi, "$1"],	// Cool little RegExp deletes whitespace around linebreak chars.
					[/\n{3,}/g, "\n\n"],							// Max. 2 consecutive linebreaks
					/^\s+|\s+$/g									// Trim the front & back
				], h);

				h = ed.dom.encode(h);
				
				// Delete any highlighted text before pasting
				if (!sel.isCollapsed()) {
					d.execCommand("Delete", false, null);
				}
				
				// Perform default or custom replacements
				if (tinymce.is(rl, "array") || (tinymce.is(rl, "array"))) {
					h = this._processRe(rl, h);
				}
				else if (tinymce.is(rl, "string")) {
					h = this._processRe(new RegExp(rl, "gi"), h);
				}
				
				// Treat paragraphs as specified in the config
				if (linebr == "none") {
					h = this._processRe([
						[/\n+/g, " "]
					], h);
				}
				else if (linebr == "br") {
					h = this._processRe([
						[/\n/g, "<br />"]
					], h);
				}
				else {
					h = this._processRe([
						/^\s+|\s+$/g,
						[/\n\n/g, "</p><p>"],
						[/\n/g, "<br />"]
					], h);
				}

				// This next piece of code handles the situation where we're pasting more than one paragraph of plain
				// text, and we are pasting the content into the middle of a block node in the editor.  The block
				// node gets split at the selection point into "Para A" and "Para B" (for the purposes of explaining).
				// The first paragraph of the pasted text is appended to "Para A", and the last paragraph of the
				// pasted text is prepended to "Para B".  Any other paragraphs of pasted text are placed between
				// "Para A" and "Para B".  This code solves a host of problems with the original plain text plugin and
				// now handles styles correctly.  (Pasting plain text into a styled paragraph is supposed to make the
				// plain text take the same style as the existing paragraph.)
				if ((pos = h.indexOf("</p><p>")) != -1) {
					rpos = h.lastIndexOf("</p><p>");
					node = sel.getNode(); 
					breakElms = [];		// Get list of elements to break 

					do {
						if (node.nodeType == 1) {
							// Don't break tables and break at body
							if (node.nodeName == "TD" || node.nodeName == "BODY") {
								break;
							}
							
							breakElms[breakElms.length] = node;
						}
					} while (node = node.parentNode);
					
					// Are we in the middle of a block node?
					if (breakElms.length > 0) {
						before = h.substring(0, pos);
						after = "";
						
						for (i=0, len=breakElms.length; i<len; i++) {
							before += "</" + breakElms[i].nodeName.toLowerCase() + ">";
							after += "<" + breakElms[breakElms.length-i-1].nodeName.toLowerCase() + ">";
						}
						
						if (pos == rpos) {
							h = before + after + h.substring(pos+7);
						}
						else {
							h = before + h.substring(pos+4, rpos+4) + after + h.substring(rpos+7);
						}
					}
				}

				return h;
			}
		},

		/**
		 * Various post process items.
		 */
		_postProcess : function(pl, o) {
			var t = this, ed = t.editor, dom = ed.dom, styleProps;

			if (o.wordContent) {
				// Remove named anchors or TOC links
				each(dom.select('a', o.node), function(a) {
					if (!a.href || a.href.indexOf('#_Toc') != -1)
						dom.remove(a, 1);
				});
				
				if (t.editor.getParam('paste_convert_middot_lists', true))
					t._convertLists(pl, o);

				// Process styles
				styleProps = ed.getParam('paste_retain_style_properties'); // retained properties

				// If string property then split it
				if (tinymce.is(styleProps, 'string'))
					styleProps = tinymce.explode(styleProps);

				// Retains some style properties
				each(dom.select('*', o.node), function(el) {
					var newStyle = {}, npc = 0, i, sp, sv;

					// Store a subset of the existing styles
					if (styleProps) {
						for (i = 0; i < styleProps.length; i++) {
							sp = styleProps[i];
							sv = dom.getStyle(el, sp);

							if (sv) {
								newStyle[sp] = sv;
								npc++;
							}
						}
					}

					// Remove all of the existing styles
					dom.setAttrib(el, 'style', '');

					if (styleProps && npc > 0)
						dom.setStyles(el, newStyle); // Add back the stored subset of styles
					else // Remove empty span tags that do not have class attributes
						if (el.nodeName == 'SPAN' && !el.className)
							dom.remove(el, true);
				});
				
				// Process images - remove local
				each(dom.select('img', o.node), function(el){
					if (!/^http(s)?:\/\//g.test(el.src)) {
						dom.remove(el);
					}
				});
			}
			
			if (ed.getParam('paste_convert_urls', true)) {		
				var ex = '([-!#$%&\'\*\+\\./0-9=?A-Z^_`a-z{|}~]+@[-!#$%&\'\*\+\\/0-9=?A-Z^_`a-z{|}~]+\.[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+)';
				var ux = '((news|telnet|nttp|file|http|ftp|https)://[-!#$%&\'\*\+\\/0-9=?A-Z^_`a-z{|}~]+\.[-!#$%&\'\*\+\\./0-9=?A-Z^_`a-z{|}~]+)';
				
				function processRe(h){
					h = h.replace(new RegExp(ex, 'g'), '<a href="mailto:$1">$1</a>');				
					h = h.replace(new RegExp(ux, 'g'), '<a href="$1">$1</a>');
					
					return h;
				}
				// Process e-mail and urls
				each(dom.select('*:not(a)', o.node), function(el){
					each(el.childNodes, function(n){
						if (n.nodeType == 3) {
							var s = n.innerText || n.textContent || n.data || null;
							if(s && /(@|:\/\/)/.test(s)) {
								if (s = processRe(s)) {
									n.parentNode.innerHTML = s;
								}
							}
						}
					});
				});
			}

			// Remove all style information or only specifically on WebKit to avoid the style bug on that browser
			if (ed.getParam("paste_remove_styles") || (ed.getParam("paste_remove_styles_if_webkit") && tinymce.isWebKit)) {
				each(dom.select('*[style]', o.node), function(el) {
					el.removeAttribute('style');
					el.removeAttribute('mce_style');
				});
			} else {
				if (tinymce.isWebKit) {
					// We need to compress the styles on WebKit since if you paste <img border="0" /> it will become <img border="0" style="... lots of junk ..." />
					// Removing the mce_style that contains the real value will force the Serializer engine to compress the styles
					each(dom.select('*', o.node), function(el) {
						el.removeAttribute('mce_style');
					});
				}
			}
		},

		/**
		 * Converts the most common bullet and number formats in Office into a real semantic UL/LI list.
		 */
		_convertLists : function(pl, o) {
			var dom = pl.editor.dom, listElm, li, lastMargin = -1, margin, levels = [], lastType;

			// Convert middot lists into real semantic lists
			each(dom.select('p', o.node), function(p) {
				var sib, val = '', type, html, idx, parents, s, st;

				// Get text node value at beginning of paragraph
				for (sib = p.firstChild; sib && sib.nodeType == 3; sib = sib.nextSibling)
					val += sib.nodeValue;

				val = p.innerHTML.replace(/<\/?\w+[^>]*>/gi, '').replace(/&nbsp;/g, '\u00a0');

				// Detect unordered lists look for bullets
				if (/^(__MCE_ITEM__)+[\u2022\u00b7\u00a7\u00d8o]\s*\u00a0*/.test(val))
					type = 'ul';

				// Detect ordered lists 1., a. or ixv.
				if(s = val.match(/^__MCE_ITEM__\s*\(*(\w+)\.*\)*\s*\u00a0{2,}/)){
					type = 'ol';
					// Find list style - will return later
					s = tinymce.trim(s[1]);
					if(s){	
						if(/0[1-9]/.test(s)){
							st = 'decimal-leading-zero';	
						}
						if(/[a-z+?]/.test(s)){
							st = 'lower-alpha';	
						}
						if(/[A-Z+?]/.test(s)){
							st = 'upper-alpha';	
						}
						if(/[ivx+]/.test(s)){
							st = 'lower-roman';	
						}
						if(/[IVX+]/.test(s)){
							st = 'upper-roman';	
						}
					}
				} 

				// Check if node value matches the list pattern: o&nbsp;&nbsp;
				if (type) {
					margin = parseFloat(p.style.marginLeft || 0);

					if (margin > lastMargin)
						levels.push(margin);

					if (!listElm || type != lastType) {
						listElm = dom.create(type);
						dom.insertAfter(listElm, p);
					} else {
						// Nested list element
						if (margin > lastMargin) {
							listElm = li.appendChild(dom.create(type));
						} else if (margin < lastMargin) {
							// Find parent level based on margin value
							idx = tinymce.inArray(levels, margin);
							parents = dom.getParents(listElm.parentNode, type);
							listElm = parents[parents.length - 1 - idx] || listElm;
						}
					}

					// Remove middot or number spans if they exists
					each(dom.select('span', p), function(span) {
						var html = span.innerHTML.replace(/<\/?\w+[^>]*>/gi, '');

						// Remove span with the middot or the number
						if (type == 'ul' && /^[\u2022\u00b7\u00a7\u00d8o]/.test(html))
							dom.remove(span);
						else if (/^[\s\S]*\w+\.(&nbsp;|\u00a0)*\s*/.test(html))
							dom.remove(span);
					});

					html = p.innerHTML;

					// Remove middot/list items
					if (type == 'ul')
						html = p.innerHTML.replace(/__MCE_ITEM__/g, '').replace(/^[\u2022\u00b7\u00a7\u00d8o]\s*(&nbsp;|\u00a0)+\s*/, '');
					else
						html = p.innerHTML.replace(/__MCE_ITEM__/g, '').replace(/^\s*\(*\w+\.*\)*(&nbsp;|\u00a0)+\s*/, '');

					// Create li and add paragraph data into the new li
					li = listElm.appendChild(dom.create('li', 0, html));
					dom.remove(p);
					
					// Set list styling if any
					if(st){
						dom.setStyle(listElm, 'list-style-type', st);
					}

					lastMargin = margin;
					lastType = type;				
				} else
					listElm = lastMargin = 0; // End list element
			});
			
			// Remove any left over makers
			html = o.node.innerHTML;
			if (html.indexOf('__MCE_ITEM__') != -1)
				o.node.innerHTML = html.replace(/__MCE_ITEM__/g, '');
		},

		/**
		 * This method will split the current block parent and insert the contents inside the split position.
		 * This logic can be improved so text nodes at the start/end remain in the start/end block elements
		 */
		_insertBlockContent : function(ed, dom, content) {
			var parentBlock, marker, sel = ed.selection, last, elm, vp, y, elmHeight;

			function select(n) {
				var r;

				if (tinymce.isIE) {
					r = ed.getDoc().body.createTextRange();
					r.moveToElementText(n);
					r.collapse(false);
					r.select();
				} else {
					sel.select(n, 1);
					sel.collapse(false);
				}
			};

			// Insert a marker for the caret position
			this._insert('<span id="_marker">&nbsp;</span>', 1);
			marker = dom.get('_marker');
			parentBlock = dom.getParent(marker, 'p,h1,h2,h3,h4,h5,h6,ul,ol,th,td');

			// If it's a parent block but not a table cell
			if (parentBlock && !/TD|TH/.test(parentBlock.nodeName)) {
				// Split parent block
				marker = dom.split(parentBlock, marker);

				// Insert nodes before the marker
				each(dom.create('div', 0, content).childNodes, function(n) {
					last = marker.parentNode.insertBefore(n.cloneNode(true), marker);
				});

				// Move caret after marker
				select(last);
			} else {
				dom.setOuterHTML(marker, content);
				sel.select(ed.getBody(), 1);
				sel.collapse(0);
			}

			dom.remove('_marker'); // Remove marker if it's left

			// Get element, position and height
			elm = sel.getStart();
			vp = dom.getViewPort(ed.getWin());
			y = ed.dom.getPos(elm).y;
			elmHeight = elm.clientHeight;

			// Is element within viewport if not then scroll it into view
			if (y < vp.y || y + elmHeight > vp.y + vp.h)
				ed.getDoc().body.scrollTop = y < vp.y ? y : y - vp.h + 25;
		},

		/**
		 * Inserts the specified contents at the caret position.
		 */
		_insert : function(h, skip_undo) {
			var ed = this.editor;

			// First delete the contents seems to work better on WebKit
			if (!ed.selection.isCollapsed())
				ed.getDoc().execCommand('Delete', false, null);

			// It's better to use the insertHTML method on Gecko since it will combine paragraphs correctly before inserting the contents
			ed.execCommand(tinymce.isGecko ? 'insertHTML' : 'mceInsertContent', false, h, {skip_undo : skip_undo});
		},
		
		/**
		 * This method will open the old style paste dialogs. Some users might want the old behavior but still use the new cleanup engine.
		 */
		_legacySupport : function() {
			var t = this, ed = t.editor;

			// Register buttons for backwards compatibility
			ed.addButton('pastetext', {title : 'paste.paste_text_desc', cmd : 'mcePasteText'});
			ed.addButton('pasteword', {title : 'paste.paste_word_desc', cmd : 'mcePaste'});
		}
	});

	// Register plugin
	tinymce.PluginManager.add('paste', tinymce.plugins.PastePlugin);
})();