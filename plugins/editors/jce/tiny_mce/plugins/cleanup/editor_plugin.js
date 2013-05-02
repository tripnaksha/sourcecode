/**
* @version		$Id: editor_plugin.js 26 2009-05-25 10:21:53Z happynoodleboy $
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

	tinymce.create('tinymce.plugins.CleanupPlugin', {
		init : function(ed, url) {
			var t = this;
			this.editor = ed;
			// Format Block fix
			ed.onBeforeExecCommand.add(function(ed, cmd, ui, val, o) {
				if(cmd == 'FormatBlock'){
					if(val == '' || typeof(val) == 'undefined'){
						ed.execCommand('RemoveFormat', false);
						o.terminate = true;
					}
				}
			});	
			// Cleanup callback
			ed.onBeforeSetContent.add(function(ed, o){
				// Geshi
				o.content = o.content.replace(/<pre xml:\s*(.*?)>(.*?)<\/pre>/g, '<pre class="geshi-$1">$2</pre>');
			});
			// Cleanup callback
			ed.onPostProcess.add(function(ed, o) {
				if (o.set){
					// Geshi
					o.content = o.content.replace(/<pre xml:\s*(.*?)>(.*?)<\/pre>/g, '<pre class="geshi-$1">$2</pre>');					
				}
				if (o.get){
					// Geshi
					o.content = o.content.replace(/<pre class="geshi-(.*?)">(.*?)<\/pre>/g, '<pre xml:$1>$2</pre>');						
					// Correct b and i tags
					if(!ed.getParam('verify_html')){
						o.content = o.content.replace(/<(\/?)b\b>|<b\b( [^>]+)>/gi, '<$1strong$2>');
						o.content = o.content.replace(/<(\/?)i\b>|<i\b( [^>]+)>/gi, '<$1em$2>');
					}
					// Remove empty jceutilities anchors
					o.content = o.content.replace(/<a([^>]*)class="jce(box|popup|lightbox|tooltip|_tooltip)"([^>]*)><\/a>/gi, '');
					// Remove span elements with jceutilities classes
					o.content = o.content.replace(/<span class="jce(box|popup|lightbox|tooltip|_tooltip)">(.*?)<\/span>/gi, '$2');
					//mce stuff
					o.content = o.content.replace(/mce_(src|href|style|coords|shape)="([^"]+)"\s*?/gi, '');
					
					if(ed.getParam('cleanup_keep_nbsp', true)) {
						o.content = o.content.replace(/\u00a0/g, '&nbsp;');
					}
				}
			});
			// Save callback
			ed.onGetContent.add(function(ed, o){
				if(o.save){
					// Convert entities to characters
					if(ed.getParam('cleanup_pluginmode')){
						o.content = o.content.replace(/&#39;/gi, "'");
						o.content = o.content.replace(/&apos;/gi, "'");
						o.content = o.content.replace(/&amp;/gi, "&");
						o.content = o.content.replace(/&quot;/gi, '"');
					}	
				}
			});
		},

		getInfo : function() {
			return {
				longname : 'Cleanup',
				author : 'Ryan Demmer',
				authorurl : 'http://www.joomlacontenteditor.net',
				infourl : 'http://www.joomlacontenteditor.net',
				version : '@@version@@'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('cleanup', tinymce.plugins.CleanupPlugin);
})();