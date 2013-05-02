// any Internet Explorer (thanks to Dean)
var isMSIE = /*@cc_on!@*/false;

if (!isMSIE) window.onload=pageload();

function pageload(){
	var contentColumn = document.getElementById('contentColumn');
	var leftcolumn = document.getElementById('leftColumn');
	var rightcolumn = document.getElementById('rightColumn');
	var fullcontent = document.getElementById('inner_contentColumn_full');
	var nopad = document.getElementById('nopad');
	var first = document.getElementById('first');
	getElementByClass('navbar');

	if (leftcolumn) {leftcolumn.parentNode.removeChild(leftcolumn); }
	if (rightcolumn) rightcolumn.parentNode.removeChild(rightcolumn);
	if (contentColumn){
		contentColumn.style.margin = "0px";
		contentColumn.style.width = "100%";
	}
	if (fullcontent) fullcontent.style.width = "100%";
	if (nopad) nopad.style.height = "300px";
	
	//show first 5 items on page load
	showInitial('routelist');
	showInitial('triplist');
};

function ieload(){
	pageload();
};

/* getElementByClass - http://www.actiononline.biz/web/code/how-to-getelementsbyclass-in-javascript-the-code/
/**********************/
var allHTMLTags = new Array();
function getElementByClass(theClass) {
	//Create Array of All HTML Tags
	var allHTMLTags=document.getElementsByTagName("*");
	//Loop through all tags using a for loop
	for (i=0; i<allHTMLTags.length; i++) {
		//Get all tags with the specified class name.
		if (allHTMLTags[i].className==theClass) {
			//Place any code you want to apply to all pages with the class specified.
			//In this example is to “display:none;” them
			//Making them all dissapear on the page.
			allHTMLTags[i].style.display='none';
		}
	}
}

function showHide(ulname){
	var ulist = document.getElementById(ulname);
	if (ulist)
	{
		var navItem = ulist.getElementsByTagName("li");
		var shoitems = 5, cnt = 0;
		// modified from http://snipplr.com/view/243/show--hide-an-unordered-list/
		for (var i = 0; i < navItem.length; i++){
			if (navItem[i].className == 'more' && cnt < shoitems)
			{
			   navItem[i].className = '';
			   cnt ++;
			}
		}
	}
}
function showInitial(ulname){
	var ulist = document.getElementById(ulname);
	if (ulist)
	{
	   var navItem = ulist.getElementsByTagName("li");
	   for (var i = 0; i < navItem.length; i++){
		if (navItem[i].className == 'more' && i < 5)
		{
		   navItem[i].className = '';
		}
	   }
	}
}
