/**
* @copyright	Copyright (C) 2007 PixPro Stockholm AB. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PixSearch is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* PixSearch javascript
*
* Used to process Ajax searches on a Joomla database.
*
* @author		Henrik Hussfelt <henrik@pixpro.net>
* @package		mod_pixsearch
* @since		1.5
* @version		0.4.0
*/

/*
Global language vars, to be set from languagefiles through mod_pixsearch.
*/
var _txtResults	= null;
var _txtClose	= null;
var _txtSearch	= null;
var _txtReadmore= null;
var _txtNoResults=null;
var _txtAdvSearch=null;
var _txtSearchLink=null;
var _txtURIBase	= null;
var _optLimit	= null;
var _optOrdering= null;
var _optPhrase	= null;
var _optHideDivs = null;
var _optIncludeLink=null;
var _txtViewAll	=null;
var _optShowCategory=null;
var _optShowReadmore=null;
var _optShowDescription=null;

/*
Set global language vars
*/
function setSpecifiedLanguage(a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q){
	_txtResults	= a;
	_txtClose	= b;
	_txtSearch	= c;
	_txtReadmore= d;
	_txtNoResults=e;
	_txtAdvSearch=f;
	_txtSearchLink=g;
	_txtURIBase	= h;
	_optLimit	= i;
	_optOrdering= j;
	_optPhrase	= k;
	_optHideDivs = l;
	_optIncludeLink = m;
	_txtViewAll	= n;
	_optShowCategory = o;
	_optShowReadmore = p;
	_optShowDescription = q;
}

/*
When DOMReady, execute js_code.
*/
window.addEvent('domready', function() {
	var addSearchResult = function() {
		var result_div = $('ps_results');
		var row="ps_row_2";
		var x=0;
		var res_header = new Element('div', {'class': 'ps_header' }).setHTML(_txtResults).injectInside(result_div);
		var link = new Element('a', {'id': 'ps_link' }).setProperty('href','#').setHTML(_txtClose).injectBefore(res_header);
		var splitDivs=_optHideDivs.split(" ");
		$('ps_link').addEvent('click', function(e) {
			e = new Event(e).stop();
			$('ps_search_str').value = _txtSearch;
			$('ps_results').empty().removeClass('ps_results').setStyle('visibility', 'hidden');
			// SHOW DIVS
			if(splitDivs.length > 0 && splitDivs != '') splitDivs.each(function(r){
				$(r).setStyle('visibility', 'visible');
			});
		});
		// HIDE DIVS
		if(splitDivs.length > 0 && splitDivs != '') splitDivs.each(function(r){
			$(r).setStyle('visibility', 'hidden');
		});
		result_div.addClass('ps_results');
		search_res = $$('#pixsearch_tmpdiv fieldset');
		if(search_res.length > 0) search_res.each(function(res) {
			x +=1;
			var res_data='';
			res_data=res.getChildren();
			if(res_data.length > 0){
				res_data.each(function(r) {
					// MAKE A CHECK THAT OBJECT EXIST AND THAT THE LENGTH OF THE TITLE IS GREATER THEN 2
					if(r.getTag() == "div"){
						if(r.getChildren().length > 2){
							var suri=r.getFirst().getNext().getProperty('href'); // not working in JS .replace(/&/g,"&amp;");.replace(/&/g,"&amp;");
							if(row == "ps_row_2") row = "ps_row_1";
							else row = "ps_row_2";
							var el = new Element('div', {'class': row });
							var link = new Element('a').setProperty('href',suri).injectInside(el);
							var name = new Element('h3').setHTML(r.getFirst().getNext().getText()).injectInside(link);
							if(_optShowDescription) var description=r.getNext().getText();
							else var description = '';
							var desc = new Element('span').setHTML(description).injectAfter(link);
							if(_optShowCategory){
								var cat = new Element('span', {'class': 'small'}).setHTML(r.getChildren().getLast().getText()).injectAfter(link);
								var br = new Element('br').injectAfter(cat);
							}
							if(_optShowReadmore){
								var link = new Element('a',{'class': 'clearboth'}).setProperty('href',suri).setHTML(_txtReadmore).injectAfter(desc);
								if(_optShowDescription) var br = new Element('br').injectAfter(desc);
							}
							el.inject(result_div);
						}
					}
				});
			}
		});
		if(x < 1){
			var el = new Element('div', {'class': "ps_row_1" });
			var name = new Element('h3').setHTML(_txtNoResults).injectInside(el);
			var link = new Element('a').setProperty('href',_txtSearchLink).injectAfter(name);
			var name = new Element('span').setHTML(_txtAdvSearch).injectInside(link);
			el.inject(result_div);
		}else{
			if(_optIncludeLink == 1){
				var el = new Element('div', {'class': "ps_row_btm" });
				var link2 = new Element('a').setProperty('href',"javascript:document.pp_search.limit.value='';document.pp_search.submit();").injectInside(el);
				var name = new Element('span').setHTML(_txtViewAll).injectInside(link2);
				el.inject(result_div);
			}
		}
	}
	$('ps_search_str').addEvent('click', function(e) {
		if($('ps_search_str').value == _txtSearch) $('ps_search_str').value = '';
	});

	$('ps_search_str').onkeyup = function(){
		var curtime = new Date();
		var url = _txtURIBase+'/index2.php';
		if($('ps_search_str').value == ''){
			var splitDivs=_optHideDivs.split(" ");
			$('ps_results').empty().removeClass('ps_results').setStyle('visibility', 'hidden');
			// SHOW DIVS
			if(splitDivs.length > 0 && splitDivs != '') splitDivs.each(function(r){
				$(r).setStyle('visibility', 'visible');
			});
		}
		else{
			var req = new Ajax(url, {
				method: 'get',
				delay : 200,
				data: {
				'option' : 'com_search',
				'view' : 'search',
				'searchphrase' : _optPhrase, //any,all,exact
				'ordering' : _optOrdering, // popular,oldest,newest,alpha,category
				'limit' : _optLimit, // 10
				'searchword' : $('ps_search_str').value,
				'r' : curtime.getTime()
				},
				onComplete: function(results) {
					var theNewEle = new Element('div').setHTML(results);
					theNewEle.getChildren().each(function(r) {
						if(r.getProperty('class') == 'contentpaneopen'){
							$('pixsearch_tmpdiv').setHTML(r.innerHTML);
						}
					});
					$('ps_results').empty().removeClass('ps_results').setStyle('visibility', 'visible');
					addSearchResult();
					$('pixsearch_tmpdiv').empty().setStyle('visibility','visible');
				}
			});
			var rq = req.request.delay(200,req);
			$('ps_search_str').onkeydown = function(){$clear(rq);};
		}
	}
});