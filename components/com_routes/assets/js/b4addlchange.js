//----------------------------------------Global declarations---------------------------------------------------------------//
var startLng = 82.353516;
var startLat = 23.362429;
var startZoom = 5;
var marker = null;
var count = 0;
var draw = 0;
var route = [];
var recording = false;
var map = null;
var rtImageLocation = null;
var rtLocation = null;
var markerList = [];
var polyList = [];
var labelMarker = [];
var dirpoints = [];
var routeTrack = [];
var mapContainer;
var userName = "";
var userID = "";
var userMode = 0;
var rtShow = 0;
var searchText = "";
var sText = "";
var avFlag = 0;
var wrFlag = 0;
var trailName;
var searchResult;
var currentURL;
var disControl, saveControl, mesControl, noneControl, insControl, creControl, infoControl, roadControl, screenControl;
var geocoder;
var srchFlag = 0;
var strtFlag = 0;
var tidFlag = 0;
var trailID = 0;
var trailind = 0;
var roadind = 0;
var limit = 100;
var gdir, mgr = null, poly = [], polycnt = 0, drDistance = 0, disTrack = [], gdircomp = 0, errorRet = 0, contextmenu;
// ====== Create a Client Geocoder ======
var geo = new GClientGeocoder(new GGeocodeCache()); 
// ====== Array for decoding the failure codes ======
var reasons=[];
reasons[G_GEO_SUCCESS]            = "Success";
reasons[G_GEO_MISSING_ADDRESS]    = "Missing Address: The address was either missing or had no value.";
reasons[G_GEO_UNKNOWN_ADDRESS]    = "Unknown Address:  No corresponding geographic location could be found for the specified address.";
reasons[G_GEO_UNAVAILABLE_ADDRESS]= "Unavailable Address:  The geocode for the given address cannot be returned due to legal or contractual reasons.";
reasons[G_GEO_BAD_KEY]            = "Bad Key: The API key is either invalid or does not match the domain for which it was given";
reasons[G_GEO_TOO_MANY_QUERIES]   = "Too Many Geocoding Queries Too Soon. Do not close this page yet, click the next point again in a couple of seconds, it should work then.";
reasons[G_GEO_SERVER_ERROR]       = "Server error: The geocoding request could not be successfully processed.";
reasons[G_GEO_BAD_REQUEST]        = "A directions request could not be successfully parsed.";
reasons[G_GEO_MISSING_QUERY]      = "No query was specified in the input.";
reasons[G_GEO_UNKNOWN_DIRECTIONS] = "Could not compute directions between these points. Pick a different end point.";
//------------------------------------End Global declarations---------------------------------------------------------------//

/**
* Helper function to hide the given DOM element
* @param {Object} element The DOM element that should be hidden
*/
function hideel(element){
	element.style.display = "none";
	element.style.position = "absolute";
}
/**
* Helper function to show the given DOM element
* @param {Object} element The DOM element that should be displayed
*/
function show(element){
	element.style.display = "block";
	element.style.position = "relative";
}
//get the location of this script
var scripts = document.getElementsByTagName("script") ;
for(var i = 0 ; i < scripts.length ; i++)
{
	var scriptSource = scripts[i].src ;
	var temp = scriptSource.indexOf("map.js") ;
	if(temp >= 0)
	{
	   jsLocation = scriptSource.slice(0, temp);
	   //below will work only if 'images' and 'js' files are in separate folders at the same level.
	   //rtLocation is the root component folder and rtImageLocation is the images folder
	   rtLocation = jsLocation.substring(0, jsLocation.lastIndexOf('/')-3);
	   rtImageLocation = jsLocation.substring(0, jsLocation.lastIndexOf('/')-3) + "/images/" ;
	}
}
//----------------------------------------Custom marker icons-----------------------------------------------------------------//
var turn = new GIcon();
turn.image = rtImageLocation + "turn.png";
turn.iconSize = new GSize(1, 1);
turn.iconAnchor = new GPoint(1, 1);
turn.infoWindowAnchor = new GPoint(5, 1);

var start = new GIcon();
start.image = rtImageLocation + "start.gif";
start.iconSize = new GSize(20, 22);
start.iconAnchor = new GPoint(5, 22);
start.infoWindowAnchor = new GPoint(5, 1);

var end = new GIcon();
end.image = rtImageLocation + "end.gif";
end.iconSize = new GSize(20, 22);
end.iconAnchor = new GPoint(3, 22);
end.infoWindowAnchor = new GPoint(5, 1);

var ind = new GIcon();
ind.image = rtImageLocation + "trail-marker.png";
ind.iconSize = new GSize(14, 14);
ind.iconAnchor = new GPoint(7, 7);
ind.infoWindowAnchor = new GPoint(7, 7);

var burn = new GIcon();
burn.image = rtImageLocation + "burn.png";
burn.iconSize = new GSize(5, 5);
burn.iconAnchor = new GPoint(2, 2);
burn.infoWindowAnchor = new GPoint(5, 1);

var blustar = new GIcon();
blustar.image = rtImageLocation + "star-blue.png";
blustar.iconSize = new GSize(16, 15);
blustar.iconAnchor = new GPoint(8, 8);
blustar.infoWindowAnchor = new GPoint(8, 8);

var redstar = new GIcon();
redstar.image = rtImageLocation + "star-red.png";
redstar.iconSize = new GSize(16, 15);
redstar.iconAnchor = new GPoint(8, 8);
redstar.infoWindowAnchor = new GPoint(8, 8);

var star2 = new GIcon();
star2.image = rtImageLocation + "turnmark.png";
star2.iconSize = new GSize(10, 10);
star2.iconAnchor = new GPoint(5, 5);
star2.infoWindowAnchor = new GPoint(5, 5);

var n5 = new GIcon();
n5.image = rtImageLocation + "nos/1.png";
n5.iconSize = new GSize(24, 24);
n5.iconAnchor = new GPoint(12, 12);
n5.infoWindowAnchor = new GPoint(12, 12);

var customIcons = [];
customIcons["start"] = start;
customIcons["end"] = end;
customIcons["turn"] = turn;
customIcons["ind"] = ind;
customIcons["burn"] = burn;
customIcons["blustar"] = blustar;
customIcons["redstar"] = redstar;
customIcons["star2"] = star2;
customIcons["n5"] = n5;

//----------------------------------------------------------------------------------------------------------------------------//
//----------------------------------------------------------------------------------------------------------------------------//
/* Cookie Functions courtesy of Quirksmode
	http://www.quirksmode.org/js/cookies.html
*/
function createCookie( name, value, days) {
	var expires = "";
	if (days)
	{
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		expires = "; expires="+date.toGMTString();
	}
	document.cookie = name+"="+value+expires+"; path=/";
}
function readCookie( name ) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++)
	{
		var c = ca[i];
		while (c.charAt(0)==' ') {c = c.substring(1,c.length);}
		if (c.indexOf(nameEQ) === 0) {return c.substring(nameEQ.length,c.length);}
	}
	return null;
}
function setSTextCookie() {
	createCookie("searchBoxCookie",searchText,0);
	createCookie("searchPageCookie",searchText,-1);
}
function getSTextCookie() {
	var userSText = readCookie("searchBoxCookie");
	if(userSText){
		return userSText;
	}
}
/************************************************************* www.sean.co.uk ************************************************/
/**
* Helper function to show pause execution for the passed no of milliseconds
* @param number of milliseconds for which to stop execution
*/
function pausecomp(millis){
	var date = new Date();
	var curDate = null;

	do { curDate = new Date(); }
	while(curDate-date < millis);
}
/**
* Helper function to calculate height of all the controls on the top right of the map - not used currently
* @param -none-
*/
function calcHeight() {
	var totalheight = 0;
	if (document.getElementById("controlsDiv"))
	   totalheight = totalheight + document.getElementById("controlsDiv").offsetHeight;
	if (document.getElementById("roadDiv"))
	   totalheight = totalheight + document.getElementById("roadDiv").offsetHeight;
	if (document.getElementById("screenDiv"))
	   totalheight = totalheight + document.getElementById("screenDiv").offsetHeight;
	if (document.getElementById("infoDiv"))
	   totalheight = totalheight + document.getElementById("infoDiv").offsetHeight;

	return (totalheight);
}

/**
* Function to send AJAX query to and redirect returned data to the appropriate function
* @param url - URL to send query to
* @param queryString - POST parameters for URL
* @param returnVar - not used currently
* @param rerFunction -function to which data returned from query will be sent
*/
function ajaxFunction(url, queryString, returnVar, retFunction) {
	var ajaxRequest;
	try{
		// Opera 8.0+, Firefox, Safari
		ajaxRequest = new XMLHttpRequest();
	} catch (e){
		// Internet Explorer Browsers
		try{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				// Something went wrong
				alert("Your browser broke!");
				return false;
			}
		}
	}
	try{
	   ajaxRequest.open("POST", url, true);
	   ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	   ajaxRequest.send(queryString);

	   //upon a change of status of the request for the lookup page, call the javascript handler
	   ajaxRequest.onreadystatechange = function() {
		//readystate of 4 means the request is complete
		if (ajaxRequest.readyState == 4) {
			//status code of 200 means OK (regular status codes)
			if (ajaxRequest.status != 200) {
				alert('Page not found');
//				alert(ajaxRequest.responseText);
				return false;
			}
			else {
//			alert(ajaxRequest.responseText);
			   retFunction(ajaxRequest,returnVar);
			}
		}
	   };
	} catch (error3) {
		alert('Page not found');
		return false;
	}
}

//----------------------------------------------------------------------------------------------------------------------------//
//----------------------------------------------------------------------------------------------------------------------------//
//----------------------------------------------------------------------------------------------------------------------------//
/**
 * @author Marco Alionso Ramirez, marco@onemarco.com
 * @url http://onemarco.com
 * @version 1.0
 * This code is public domain
 */

/**
* The Tooltip class is an addon designed for the Google Maps GMarker class. 
* @constructor
* @param {GMarker} marker
* @param {String} text
* @param {Number} padding
*/
/*function Tooltip(marker, text, padding){
	this.marker_ = marker;
	this.text_ = text;
	this.padding_ = padding;
}

Tooltip.prototype = new GOverlay();

Tooltip.prototype.initialize = function(map){
	var div = document.createElement("div");
	div.appendChild(document.createTextNode(this.text_));
	div.className = 'tooltip';
	div.style.position = 'absolute';
	div.style.visibility = 'hidden';
	map.getPane(G_MAP_FLOAT_PANE).appendChild(div);
	this.map_ = map;
	this.div_ = div;
}

Tooltip.prototype.remove = function(){
	this.div_.parentNode.removeChild(this.div_);
}

Tooltip.prototype.copy = function(){
	return new Tooltip(this.marker_,this.text_,this.padding_);
}

Tooltip.prototype.redraw = function(force){
	if (!force) return;
	var markerPos = this.map_.fromLatLngToDivPixel(this.marker_.getPoint());
	var iconAnchor = this.marker_.getIcon().iconAnchor;
	var xPos = Math.round(markerPos.x - this.div_.clientWidth / 2);
	var yPos = markerPos.y - iconAnchor.y - this.div_.clientHeight - this.padding_;
	this.div_.style.top = yPos + 'px';
	this.div_.style.left = xPos + 'px';
}

Tooltip.prototype.show = function(){
	this.div_.style.visibility = 'visible';
}

Tooltip.prototype.hide = function(){
	this.div_.style.visibility = 'hidden';
}
*/
/**
* Function to draw the waypoint markers to denote distances on the polyline sent as parameer.
* @param polyline
*/
function drawWayMarker(polyline) {
	   var length = polyline.getLength()/1000, km=0;
	   var div = 1000, batch500 = [], batch100 = [], batch50 = [], batch20 = [], batch10 = [], batch5 = [], batch1 = [];
	   var km_point = polyline.GetPointsAtDistance(div);

	   for (i = 0; i < km_point.length; i++) {
	     km = (i+1)*div/1000;
	     var n5 = new GIcon();
	     n5.image = rtImageLocation + "nos/" + km + ".png";
	     n5.iconSize = new GSize(24, 24);
	     n5.iconAnchor = new GPoint(12, 12);
	     n5.infoWindowAnchor = new GPoint(12, 12);

//	    var infoWindowContent = "Total in km: " +(polyline.getLength()/1000).toFixed(4)+ "<br>Riverkilometer "+Math.abs(153.4-i/1000).toFixed(2);
             var landmark = new GMarker(km_point[i], {draggable: false, icon: n5});

             batch1.push (landmark);
             if (km>0)
             {
                if (km%500 == 0)
                   batch500.push(landmark);
                if (km%100 == 0)
                   batch100.push(landmark);
                if (km%50 == 0)
                   batch50.push(landmark);
                if (km%20 == 0)
                   batch20.push(landmark);
                if (km%10 == 0)
                   batch10.push(landmark);
                if (km%5 == 0)
                   batch5.push(landmark);
             }
           }
           mgr.addMarkers(batch1,14);
           mgr.addMarkers(batch5,13);
           mgr.addMarkers(batch10,12);
           mgr.addMarkers(batch20,10);
           mgr.addMarkers(batch50,8);
           mgr.addMarkers(batch100,7);
           mgr.addMarkers(batch500,5);

           mgr.refresh();
}

/**
* Function to draw the waypoint markers to denote distances on the polyline array sent as parameter - this is for auto routes.
* @param polyline
*/
function drawWayMarkerauto (poly) {
	var nos = poly.length, div = 1000, total = 0, batch500 = [], batch100 = [], batch50 = [], batch20 = [], batch10 = [], batch5 = [], batch1 = [];
	trailLength = calcDistance();
	var buf = 0, total = 0, totaldis = 0;

	for (i=0; i<nos; i++)
	{
	  var smalltotal = 0;
	  polylen = poly[i].getLength();
	  if ((polylen+buf) >= div)
	  {
	     var intbuf = div - buf;
	     while (intbuf <= polylen)
	     {
	       smalltotal += div;
	       total += div;
	       var point = poly[i].GetPointAtDistance(intbuf); //get point at 1km for first iteration
	       var km = total/1000;
	       var n5 = new GIcon();
	       n5.image = rtImageLocation + "nos/" + km + ".png";
	       n5.iconSize = new GSize(24, 24);
	       n5.iconAnchor = new GPoint(12, 12);
	       n5.infoWindowAnchor = new GPoint(12, 12);
	       var landmark = new GMarker(point, {draggable: false, icon: n5});

               batch1.push (landmark);
               if (km>0)
               {
                  if (km%500 == 0)
                     batch500.push(landmark);
                  if (km%100 == 0)
                     batch100.push(landmark);
                  if (km%50 == 0)
                     batch50.push(landmark);
                  if (km%20 == 0)
                     batch20.push(landmark);
                  if (km%10 == 0)
                     batch10.push(landmark);
                  if (km%5 == 0)
                     batch5.push(landmark);
               }
	       intbuf += div;
	     }
	  }
	  totaldis += polylen;
	  buf = totaldis - total;
	}
        mgr.addMarkers(batch1,14);
        mgr.addMarkers(batch5,13);
        mgr.addMarkers(batch10,12);
        mgr.addMarkers(batch20,10);
        mgr.addMarkers(batch50,8);
        mgr.addMarkers(batch100,7);
        mgr.addMarkers(batch500,5);

        mgr.refresh();
}

/**
* Function to draw the offroad point to point polyline. Each point stored in route[] is used for this.
* @param -none-
*/
function drawPolyline(upload, roadtype, no, markers) {
	var line = [], div=0;
	if (upload != 2)
	{
	   for (i = route.length; i > route.length - 2 ; i--)
	     {line.push(route[i - 1]); }
           disTrack[disTrack.length] = route[routeTrack.length - 1].distanceFrom(route[routeTrack.length - 2]);
	   polyline = new GPolyline(line, "#ff0033", 4);
	   if (no == markers - 1)
	   {
	      polyline2 = new GPolyline(route, "#ff0033", 1);
	      drawWayMarker(polyline2);
	   }
	}
	else
	{
	  polyline = new GPolyline(route, "#ff0033", 4);
	  var div, length = polyline.getLength()/1000;
	  drawWayMarker(polyline);
	}

	map.addOverlay(polyline);
	polyList.push(polyline);
}

/**
* Function to draw the offroad point to point polyline. Each point stored in route[] is used for this. Not used currently
* @param -none-
*/
function drawPolydirs() {
	var x;
	
/*	for (var i = 0; i < dirpoints.length - 2; i++)
	{
	   if (i%2 == 0 )
	   {
	     x = "from: " + dirpoints[i].toUrlValue() + " to: " + dirpoints[i+1].toUrlValue();
	     gdircomp = 1;
	     gdir.load(x, {"getSteps": false,"getPolyline":true});
	   }
	   else
	     {continue;}
	}
*/
	for (var i = 0; i < dirpoints.length - 2; i++)
	{
	   if (i == 0)
	     x = "from: " + dirpoints[i].toUrlValue();
	   if (i%2 == 0 && i>0)
	   {
	     x = x + " to: " + dirpoints[i+1].toUrlValue();
	     gdircomp = i;
	   }
	   else
	     {
		continue;
	     }
	   gdir.load(x, {getPolyline:true});
	}
}

/**
* Function to draw the directions for onroad points when stored trail is displayed. Each point stored in dirpoints[] is used for this.
* @param -none-
*/
function getDirections(dirpoints, num, mode) {
	var dir=new GDirections();
	if (num ==0) poly =[];
	
	GEvent.addListener(dir, "load", function() {
	   poly[polycnt] = dir.getPolyline();
	   disTrack[disTrack.length] = dir.getDistance().meters;
	   map.addOverlay(poly[polycnt]);
	   polycnt++;
           if ((num+2) < dirpoints.length) getDirections(dirpoints, (num+2), mode);
           if ((num+2) == dirpoints.length && mode == 0) drawWayMarkerauto (poly);
	});

	dir.load("from: " + dirpoints[num].toUrlValue() + " to: " + dirpoints[num+1].toUrlValue(), {"getSteps":false, "getPolyline":true, preserveViewport:true});
}

/**
* Function to draw the directions for onroad points when trail is being marked. Each point stored in dirpoints[] is used for this.
* @param -none-
*/
function drawPolyRec(i) {
	var x;
	x = "from: " + dirpoints[i].toUrlValue() + " to: " + dirpoints[i+1].toUrlValue();
	gdircomp = i;
	gdir.load(x, {getPolyline:true});
}

/**
* Function to create a link (create trail, save trail, etc) with associated icon inside controls on the map
* @param href - not used currently
* @param clck - function to be called on clicking the link
* @param title - anchor text to be shown for link
* @param imgSrc - path of icon associated
* @param linkid - id for the html element
* @param linkClass - css class for styling the link
*/
function createIconDOM (href,clck,title,imgSrc,linkid,linkClass) {
	var iconLink = document.createElement("span");
	iconLink.id = linkid;
	if (linkClass) {
		iconLink.className = linkClass;
	}
	var beginRecordingIcon = document.createElement("img");
	beginRecordingIcon.src = imgSrc;
	beginRecordingIcon.align = "absbottom";
	iconLink.appendChild(beginRecordingIcon);
	iconLink.appendChild(document.createTextNode(title));

	GEvent.addDomListener(iconLink, "click", clck);
	return iconLink;
}

/**
* Function to calculate total distance of the marked trail. Distance b/w consecutive points on the trail is stored in disTrack[] which is added up here.
* @param none
*/
function calcDistance(){
	var x = 0, y = 0;
	var distance = 0;
	for (i=0; i < disTrack.length; i++)
	{  
	      x += disTrack[i];
	}
	y = x/1000;
	distance = y.toFixed(1);
//	distance = Math.round(x/1000);
	return distance;
}
//----------------------------------------------------------------------------------------------------------------------------//
//----------------------------------------------------------------------------------------------------------------------------//
function ScreenControls() {}

ScreenControls.prototype = new GControl();

ScreenControls.prototype.initialize = function(map) {
	var container = document.createElement("div");
	container.id="ScreenTypeConDiv";

	var headText = document.createTextNode("Full Screen");

	var ScreenTypeDiv = document.createElement("div");
	ScreenTypeDiv.id = 'ScreenTypeDiv';
	this.setButtonStyle_(ScreenTypeDiv);

	ScreenTypeDiv.appendChild(headText);
	container.appendChild(ScreenTypeDiv);

	GEvent.addDomListener(ScreenTypeDiv, "click", screenType);
	var headText = document.createTextNode("Full Screen");

	map.getContainer().appendChild(container);

	return container;
};

ScreenControls.prototype.getDefaultPosition = function() {
	return new GControlPosition(G_ANCHOR_TOP_RIGHT);
};

ScreenControls.prototype.setButtonStyle_ = function(button) {
};

/**
* The SaveControls class creates a HTML form which appears when user choose to save the trail
* Collects details of trailname, description, email and nickname (for guests), options to write review/tip (if logged in user)
*/
function SaveControls() {}

SaveControls.prototype = new GControl();

SaveControls.prototype.initialize = function(map) {
/*	savediv
	  - headLabel		- h1
	  - nameLabelSpan	- span
	    - nameLabel		- label
	      - labelText	- text for the label
	  - nameInputSpan	- span
	    - trailName		- input
	  - nameMsg		- div
	  - descLabelSpan	- span
	    - descLabel		- label
	      - descText	- text for the label
	  - descTextSpan	- span
	    - trailDesc		- textarea
	  - chkBoxSpan		- span
	    - chkSpan		- label
	      - chkText		- text for the label
	      - chkPrivate	- checkbox to mark a trail private
	  - cbMsg		- div
	  - buttonSpan		- span
	    - saveButton	- button
	      - butText		- text for the button
*/

	var container = document.createElement("div");
/*	container.style.width="50%";
	container.style.height="60%";
*/	container.style.width="300";
	container.style.height="360";
	container.id="savecontainer";

	var saveDiv = document.createElement("div");
	saveDiv.style.width = "100%";
	saveDiv.style.height = "100%";
	saveDiv.id = 'saveDiv';
	this.setButtonStyle_(saveDiv);
	container.appendChild(saveDiv);

	var headLabel = document.createElement("h1");
	headLabel.id = 'h1';
	var headText = document.createTextNode("Save Trail");
	headLabel.appendChild(headText);

	var nameLabel = document.createElement("label");
	nameLabel.htmlFor = 'trailName';
	nameLabel.id = 'label';

	var nameLabelSpan = document.createElement("span");
	nameLabelSpan.id = "span";

	var labelText = document.createTextNode("Name this trail - ");
	nameLabel.appendChild(labelText);
	nameLabelSpan.appendChild(nameLabel);

	var nameInputSpan = document.createElement("span");
	nameInputSpan.id = "span";

	var trailName = document.createElement("input");
	trailName.id = "trailName";
	trailName.type = "text";
	trailName.maxLength = 60;
	nameInputSpan.appendChild(trailName);
	trailName.onkeyup = checkAval;

	var nameMsg = document.createElement("div");
	nameMsg.id = 'nameMsg';

	var descLabelSpan = document.createElement("span");
	descLabelSpan.id = "span";

	var descLabel = document.createElement("label");
	descLabel.htmlFor = 'descName';
	descLabel.id = 'label';

/*	var descText = document.createTextNode("Enter a brief introduction for this trail -");
	descLabel.appendChild(descText);
	descLabelSpan.appendChild(descLabel);

	var descTextSpan = document.createElement("span");
	descTextSpan.id = "span";

	var trailDesc = document.createElement("textarea");
	trailDesc.id = "trailDesc";
	trailDesc.cols = 30;
	descTextSpan.appendChild(trailDesc);
*/
	var msgBoxSpan = document.createElement("div");
	msgBoxSpan.id = "msgBoxSpan";
	var msgText = document.createTextNode("You can claim the trail in your name by referring this email.");
//	msgBoxSpan.appendChild(msgText);

	var chkBoxSpan = document.createElement("div");
	chkBoxSpan.id = "chkboxspan";

	var chkLabel = document.createElement("label");
	chkLabel.htmlFor = 'chkLabel';
	chkLabel.id = 'chklabel';

	if (userID != 0)
	   {var chkText = document.createTextNode("Mark as Private");}
	else
	{
	   var chkText = document.createTextNode("You can also create reviews and trips for this trail if you log in.");

	   var nicknameLabelSpan = document.createElement("div");
	   nicknameLabelSpan.id = "nicklspan";

	   var nicknameLabel = document.createElement("label");
	   nicknameLabel.htmlFor = 'nicklName';
	   nicknameLabel.id = 'label';

	   var nicklabelText = document.createTextNode("Your Name *");
	   nicknameLabel.appendChild(nicklabelText);
	   nicknameLabelSpan.appendChild(nicknameLabel);

	   var nicknameInputSpan = document.createElement("div");
	   nicknameInputSpan.id = "nicklspan";
	   nicknameInputSpan.style.padding = "0px";

	   var nickName = document.createElement("input");
	   nickName.id = "nicklName";
	   nickName.type = "text";
	   nickName.maxLength = 30;
	   nicknameInputSpan.appendChild(nickName);
	   nickName.onkeyup = countChar;
	   nicknameLabelSpan.appendChild(nicknameInputSpan);

	   var nickemailLabelSpan = document.createElement("div");
	   nickemailLabelSpan.id = "nickrspan";

	   var nickemailLabel = document.createElement("label");
	   nickemailLabel.htmlFor = 'nickEmail';
	   nickemailLabel.id = 'label';

	   var nickemaillabelText = document.createTextNode("Valid Email *");
	   nickemailLabel.appendChild(nickemaillabelText);
	   var nickemaillabelBreak = document.createElement('br');
	   nickemailLabelSpan.appendChild(nickemailLabel);
	   nickemailLabelSpan.appendChild(nickemaillabelBreak);

	   var nickemailInputSpan = document.createElement("div");
	   nickemailInputSpan.id = "nickrspan";
	   nickemailInputSpan.style.padding = "0px";

	   var nickEmail = document.createElement("input");
	   nickEmail.id = "nickEmail";
	   nickEmail.type = "text";
	   nickEmail.maxLength = 30;
	   nickemailInputSpan.appendChild(nickEmail);
	   nickEmail.onkeyup = countChar;
	   nickemailLabelSpan.appendChild(nickemailInputSpan);
	   
	   var nicknameClearSpan = document.createElement("div");
	   nicknameClearSpan.id = "nickclrspan";
	   
	   chkBoxSpan.appendChild(nicknameLabelSpan);
	   chkBoxSpan.appendChild(nickemailLabelSpan);
	   chkBoxSpan.appendChild(msgBoxSpan);
	   chkBoxSpan.appendChild(nicknameClearSpan);
	}
//	chkLabel.appendChild(chkText);
	chkBoxSpan.appendChild(chkLabel);
	
	var buttonSpan = document.createElement("span");
	buttonSpan.id = "span";

	var saveButton = document.createElement("button");
	saveButton.id = "sbutton";

	var butText = document.createTextNode("Save");
	saveButton.appendChild(butText);
	saveButton.onclick = saveRoute;

	var cancelButton = document.createElement("button");
	cancelButton.id = "cbutton";

	var cbutText = document.createTextNode("Cancel");
	cancelButton.appendChild(cbutText);
	cancelButton.onclick = function abc(){ savediv = document.getElementById('savecontainer'); savediv.parentNode.removeChild(savediv); if (saveControl){map.removeControl(saveControl);}};

	var chkPrivate = document.createElement("input");
	chkPrivate.id = "chkPrivate";
	chkPrivate.type = "checkbox";
	if (userID != 0)
	   {chkBoxSpan.appendChild(chkPrivate);}
	chkPrivate.onclick = cbfMsg;

	var cbMsg = document.createElement("div");
	cbMsg.id = 'cbMsg';

	buttonSpan.appendChild(saveButton);
	buttonSpan.appendChild(cancelButton);
	buttonSpan.appendChild(cbMsg);

	var iconSpan = document.createElement("span");
	iconSpan.id = "iconspan";
	iconSpan.display = "hidden";

	var wIconA = document.createElement("a");
	wIconA.setAttribute('href','#');

	var wIcon = document.createElement("img");
	wIcon.src = rtImageLocation + "write.png";
	wIcon.id = "wIcon";
	wIcon.onclick = saveWrite;
	wIcon.alt = "Write a review for this trail";
	wIcon.title = "Write a review for this trail (will also save this trail)";
	wIconA.appendChild(wIcon);
	iconSpan.appendChild(wIconA);

	var tIconA = document.createElement("a");
	tIconA.setAttribute('href','#');

	var tIcon = document.createElement("img");
	tIcon.src = rtImageLocation + "tip.png";
	tIcon.id = "tIcon";
	tIcon.onclick = saveTip;
	tIcon.alt = "Write a tip for this trail";
	tIcon.title = "Write a tip for this trail (will also save this trail)";
	tIconA.appendChild(tIcon);
	iconSpan.appendChild(tIconA);

	var cIconA = document.createElement("a");
	cIconA.setAttribute('href','#');

	var cIcon = document.createElement("img");
	cIcon.src = rtImageLocation + "create.png";
	cIcon.id = "cIcon";
	cIcon.onclick = saveTrip;
	cIcon.alt = "Create a trip for this trail";
	cIcon.title = "Create a trip for this trail (will also save this trail)";
	cIconA.appendChild(cIcon);
	iconSpan.appendChild(cIconA);

	saveDiv.appendChild(headLabel);
	saveDiv.appendChild(nameLabelSpan);
	saveDiv.appendChild(nameInputSpan);
	saveDiv.appendChild(nameMsg);
//	saveDiv.appendChild(descLabelSpan);
//	saveDiv.appendChild(descTextSpan);
	saveDiv.appendChild(chkBoxSpan);
	saveDiv.appendChild(buttonSpan);
	saveDiv.appendChild(iconSpan);

	map.getContainer().appendChild(container);
	return container;
};

SaveControls.prototype.getDefaultPosition = function() {
	var bounds = map.getBounds();
	var xPos = bounds.getNorthEast().lat() - ((bounds.toSpan().lat()) * 1/4);
	var yPos = bounds.getNorthEast().lng() - ((bounds.toSpan().lng()) * 3/4);
	point= new GLatLng(xPos, yPos);
	var x = map.fromLatLngToContainerPixel(point);
	return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(x.x, x.y));
};

SaveControls.prototype.setButtonStyle_ = function(button) {
	button.style.border = "2px solid black";
};

/**
* Function to length of nick name and email (for guests).
* @param none
*/
function countChar() {
	var nickName = document.getElementById('nicklName');
	var nickEmail = document.getElementById('nickEmail');
	var chklabel = document.getElementById('chklabel');
	var x;
	if (nickName.value.length < 6) {
	  chklabel.innerHTML = "Nick name should be longer than 6 characters.<br/>";
	  chklabel.style.color = 'yellow';
	}
	else
	{
	  x = chklabel.innerHTML;
	  x = x.replace("Nick name should be longer than 6 characters.","");
	  chklabel.innerHTML = x;
	}
	if (nickEmail.value.length >= 1 && echeck(nickEmail.value) == false)
	{
	  x = chklabel.innerHTML;
	  if (x.indexOf("Please enter a valid email i.d.") == -1)
	  {
	    chklabel.innerHTML = x + "Please enter a valid email i.d.";
	  }
	  chklabel.style.color = 'yellow';
	}
	else
	{
	  x = chklabel.innerHTML;
	  x = x.replace("Please enter a valid email i.d.","");
	  chklabel.innerHTML = x;
	}
}

/**
* DHTML email validation script. Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
* @param email string
*/
function echeck(str) {
	var at="@";
	var dot=".";
	var lat=str.indexOf(at);
	var lstr=str.length;
	var ldot=str.indexOf(dot);
	if (str.indexOf(at)==-1)
	   {return false;}
	if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr)
	   {return false;}
	if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr)
	   {return false;}
	if (str.indexOf(at,(lat+1))!=-1)
	   {return false;}
	if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot)
	   {return false;}
	if (str.indexOf(dot,(lat+2))==-1)
	   {return false;}
	if (str.indexOf(" ")!=-1)
	   {return false;}
	return true;
}

/**
* This class is for creating the container which holds the create, search map, etc links on the right side of map.
* Also used to create the Full screen, follow road container
* @constructor
* @param -none-
*/
function RouteControls() {}

RouteControls.prototype = new GControl();

RouteControls.prototype.initialize = function(map) {
	var container = document.createElement("div");

	var controlsDiv = document.createElement("div");
	controlsDiv.id = 'controlsDiv';
	this.setButtonStyle_(controlsDiv);
	container.appendChild(controlsDiv);

	var createRouteLink = createIconDOM ('',beginRecording,'Create a trail',rtImageLocation + 'walker.png','createRouteLink','controlEnabled');
	var findRouteLink  = createIconDOM ('',showRouteList,'Search Map',rtImageLocation + 'tracker.png','findRouteLink','controlEnabled');

	//order of appending the links is important. keep it consistent with order in resetmap() function
	controlsDiv.appendChild(createRouteLink);

	if (userID && userID!= 0)
	{
	   var myTrailsLink = createIconDOM ('',showMyTrail,'My trails',rtImageLocation + 'walker.png','myTrailsLink','controlEnabled');
	   controlsDiv.appendChild(myTrailsLink);
	}
	controlsDiv.appendChild(findRouteLink);
	map.getContainer().appendChild(container);
	return container;
};

RouteControls.prototype.getDefaultPosition = function() {
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 45));
};

RouteControls.prototype.setButtonStyle_ = function(button) {
	button.style.border = "2px solid black";
};

/**
* This class is for creating the container which holds the distance information at the top of the map.
* @constructor
* @param -none-
*/
function ShoDistance() {}

ShoDistance.prototype = new GControl();

ShoDistance.prototype.initialize = function(map) {

	var container = document.createElement("div");
	container.style.width="auto";
	container.style.height="auto";

	var mesDiv = document.createElement("div");
	mesDiv.style.width = "100%";
	mesDiv.style.height = "100%";
	mesDiv.id = 'mesDiv';
	container.appendChild(mesDiv);

	var disDiv = document.createElement("div");
	disDiv.style.width = "100%";
	disDiv.id = 'disDiv';
	container.appendChild(disDiv);

	var disStat = document.createElement("h3");
	disStat.id = 'disStat';
	disDiv.appendChild(disStat);

	map.getContainer().appendChild(container);
	return container;
};

ShoDistance.prototype.getDefaultPosition = function() {
	var bounds = map.getBounds();
	var xPos = bounds.getNorthEast().lat() - ((bounds.toSpan().lat()) * 1/3);
	var yPos = bounds.getNorthEast().lng() - ((bounds.toSpan().lng()) * .75);
	point= new GLatLng(xPos, yPos);
	var x = map.fromLatLngToContainerPixel(point);
	return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(x.x, 0));
};

//----------------------------------------------------------------------------------------------------------------------------//
/**
* Calculates and returns the position of the Gcontrol container with distance and instruction at the top of the map.
* @param int type 
*/
function newPosition(type) {
	var bounds = map.getBounds();
	var xPos, yPos;
	if (type == 1)
	{
	   xPos = bounds.getNorthEast().lat() - ((bounds.toSpan().lat()) * 1/20);
	   yPos = bounds.getNorthEast().lng() - ((bounds.toSpan().lng()) * 2/3);
	}
	else if (type == 2)
	{
	   xPos = bounds.getNorthEast().lat() - ((bounds.toSpan().lat()) * 1/3);
	   yPos = bounds.getNorthEast().lng() - ((bounds.toSpan().lng()) * 4/5);
	}
	point= new GLatLng(xPos, yPos);
	var x = map.fromLatLngToContainerPixel(point);
	return x;
}

/**
* Called when user chooses to create trail. Enables all the controls needed while creating trail after removing other controls if existing.
* 
* @param -none-
*/
function beginRecording() {
	var contDiv = new Element( 'div', {'styles': {'margin': '10px', 'font' : 'bold 13px Arial, Helvetica, sans-serif'}}).setText('You can do more!');
	var line1 = new Element( 'li', {'styles': {'margin': '20px', 'font' : 'normal 12px Arial, Helvetica, sans-serif'}}).setText('To draw on-road trails easily, click on the "Follow road" button on the right.');
	var line2 = new Element( 'li', {'styles': {'margin': '20px', 'font' : 'normal 12px Arial, Helvetica, sans-serif'}}).setText('You can change the map size by toggling the Restore/Full Screen button at the right top corner.');
	var line3 = new Element( 'li', {'styles': {'margin': '20px', 'font' : 'normal 12px Arial, Helvetica, sans-serif'}}).setText('To search for locations when you are creating a trail, right click on the map and select the search option.');
	var line4 = new Element( 'li', {'styles': {'margin': '20px', 'font' : 'normal 12px Arial, Helvetica, sans-serif'}}).setText('Know an interesting/useful place near a trail? Right click on the map and add a comment!');

	line1.inject(contDiv);
	line2.inject(contDiv);
	line3.inject(contDiv);
	line4.inject(contDiv);

	SqueezeBox.setOptions(SqueezeBox.presets,{'handler': 'iframe','size': {'x': 350, 'y': 225},'closeWithOverlay': 1});
	SqueezeBox.setContent( 'adopt', contDiv );

	draw = 1;
	recording = true;
	editmode = 0;
	route = [];
	routeTrack = [];
	disTrack = [];
	dirpoints = [];
	routeDistance = 0;
	currentRouteId = null;

	map.removeControl(creControl);
	map.removeControl(infoControl);
	mgr.clearMarkers();
	var controlsDiv = document.getElementById('controlsDiv');
	var editRouteLink = document.getElementById('editRouteLink');
	if (editRouteLink)
	   controlsDiv.removeChild(editRouteLink);
	map.clearOverlays();
	if (typeof(trMarkerCluster) != "undefined") trMarkerCluster.clearMarkers();
	if (typeof(poiMarkerCluster) != "undefined") poiMarkerCluster.clearMarkers();
	map.enableScrollWheelZoom();
	map.closeExtInfoWindow();
	var mapcon = document.getElementById('map_container');
	var mapdiv = document.getElementById('map_canvas');
	var trailbot = document.getElementById('map_description');
	map_addon = document.getElementById("map_addon");

/*	map_addon.style.width = '0px';
	map_addon.style.height = '0px';
	map_addon.style.border = '0px';
	map_addon.style.visibility = 'hidden';
*/	
	if (trailbot)
	{
	  trailbot.style.visibility = "hidden";
	  trailbot.style.height = "0px";
	}

	insControl = new ShoDistance();

	map.addControl(insControl);
	var disdiv = document.getElementById('disDiv');
	disdiv.parentNode.removeChild(disdiv);
	var mesdiv = document.getElementById('mesDiv');

	mesdiv.innerHTML = "Click anywhere on the map to start creating a trail.";
	var mesdiv = document.getElementById('mesDiv');

	disControl = new ShoDistance();
	var x = newPosition(1);
	if (mesdiv)
	   {position = new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(x.x, mesdiv.offsetHeight));}

	map.addControl(disControl, position);
	var disStat = document.getElementById('disStat');
	disStat.innerHTML = "Total distance - 0km";
	if (document.getElementById('removeMarkerLink')== null)
	   {setRecordMode();}
	screenType(1);
   	return false;
}

function editRecording() {
	draw = 1;	
	recording = true;
	editmode = 1;
	currentRouteId = null;
	map.removeControl(infoControl);
	disControl = new ShoDistance();
	var x = newPosition(1);
	position = new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(x.x,0));
	map.addControl(disControl, position);
	var disStat = document.getElementById('disStat');
	trailLength = calcDistance();
	disStat.innerHTML = "Total distance - " + trailLength + "km";
	setRecordMode();
}

function roadType() {
	roadind = 1;
	var roaddiv = document.getElementById('roadDiv');
	var onroadLink = document.getElementById('onroadType');
	var offroadLink = document.getElementById('offroadType');
	if (onroadLink)
	{
	   roaddiv.removeChild(onroadLink);
	   var offroadLink = createIconDOM('', roadType, 'Go off-road', rtImageLocation + 'hike.png', 'offroadType', 'controlEnabled');
	   roaddiv.appendChild(offroadLink);
	   roadind = 1;
	}
	else if (offroadLink)
	{
	   roaddiv.removeChild(offroadLink);
	   var onroadLink = createIconDOM('', roadType, 'Go on road', rtImageLocation + 'cycle.png', 'onroadType', 'controlEnabled');
	   roaddiv.appendChild(onroadLink);
	   roadind = 0;
	}
}

function screenType(drawStage) {
	var ScreenTypeDiv = document.getElementById('ScreenTypeDiv');
	var mapcon = document.getElementById('map_container');
	var mapdiv = document.getElementById('map_canvas');
	var mapdesc = document.getElementById('map_description');
	curText = ScreenTypeDiv.innerHTML;

	if (curText == 'Full Screen')
	{
	   scroll(0,0);
	   mapcon.style.position = 'absolute';
   	   mapcon.style.left = 0;
   	   mapcon.style.top = 0;
   	   mapcon.style.height = '100%';
   	   mapcon.style.width = '100%';
   	   mapdiv.style.zIndex = '10000';
   
	   mapdiv.style.width = '100%';
	   mapdiv.style.height = '700px';
	   map.checkResize();
	   document.getElementsByTagName("body")[0].scroll = 'yes';
	   document.getElementsByTagName("body")[0].style.overflow = 'hidden';
	   ScreenTypeDiv.innerHTML = 'Restore';
	}
	else if (curText == 'Restore' && drawStage != 1)
	{
	   ScreenTypeDiv.innerHTML = 'Full Screen';
	   mapcon.style.position = 'relative';
   	   mapcon.style.left = 'auto';
   	   mapcon.style.top = 'auto';
   	   mapcon.style.height = 'auto';
   	   mapcon.style.width = 'auto';
   	   mapdiv.style.zIndex = '9000';
	   if (trailID != 0)
	   {
	      mapdiv.style.height = '400px';
	      mapdiv.style.width = '450px';
map.getContainer().style.overflow="hidden"; 
	   }
	   else
	   {
	      mapdiv.style.height = '600px';
	      mapdiv.style.width = '750px';
	   }
	   if (mapdesc)
	   {
	      mapdesc.style.height = '100%';
	      mapdesc.style.top = '400px';
	      mapdesc.style.position = 'relative';
	      document.getElementsByTagName("body")[0].scroll = 'yes';
	   }

	   var IE = /*@cc_on!@*/false;
	   if(!IE)
	      document.getElementsByTagName('body')[0].style.overflow = 'visible';
	   map.checkResize();
	}
}

function setRecordMode() {
	if (srchFlag == 1 && strtFlag != 1)
	   if (confirm ('Reset map?'))
	     map.setCenter(new GLatLng(startLat, startLng), startZoom.toInt());

	srchFlag = 0;
	strtFlag = 0;

	for (i = 0; i < labelMarker.length; i++)
	{
	  map.removeOverlay(labelMarker[i]);
	}
	labelMarker = [];

	var controlsDiv = document.getElementById('controlsDiv');
	var createRouteLink = document.getElementById('createRouteLink');
	controlsDiv.removeChild(createRouteLink);
	var findRouteLink = document.getElementById('findRouteLink');
	controlsDiv.removeChild(findRouteLink);
	var editRouteLink = document.getElementById('editRouteLink');
	if (editRouteLink)
	   controlsDiv.removeChild(editRouteLink);
//	var dropSelect = document.getElementById('dropSelect');
//	controlsDiv.removeChild(dropSelect);
	var myTrailsLink = document.getElementById('myTrailsLink');
	if (myTrailsLink)
	   controlsDiv.removeChild(myTrailsLink);
	var trailHead = document.getElementById('trailheading');
	if (trailHead)
	   trailHead.innerHTML = "";

	var searchBox = document.getElementById('searchBox');
	var searchButton = document.getElementById('searchButton');
	var searchContainer = document.getElementById('searchContainer');

	if (searchBox && searchButton)
	{
	   searchContainer.removeChild(searchBox);
	   searchContainer.removeChild(searchButton);
	   searchContainer.parentNode.removeChild(searchContainer);
	}
	var cancelLink = createIconDOM('', cancelRoute, 'Cancel', rtImageLocation + 'cancel.png', 'cancelLink', currentRouteId ? 'controlEnabled' : 'controlDisabled');
	var saveRouteLink = createIconDOM('', saveCurrentRoute, 'Save this trail', rtImageLocation + 'save.png', 'saveRouteLink', currentRouteId ? 'controlEnabled' : 'controlDisabled');
	var removeMarkerLink = createIconDOM('', removeMarker, 'Undo last point', rtImageLocation + 'undo.png', 'removeMarkerLink', currentRouteId ? 'controlEnabled' : 'controlDisabled');
	cancelLink.className = 'controlEnabled';
	removeMarkerLink.className = 'controlEnabled';
	saveRouteLink.className = 'controlEnabled';

	controlsDiv.appendChild(cancelLink);
	controlsDiv.appendChild(saveRouteLink);
	controlsDiv.appendChild(removeMarkerLink);

	var condiv = document.getElementById('controlsDiv');
	var rheight = 45 + condiv.offsetHeight + 5;
	    var y=calcHeight() + 50;
//	alert (rheight + "-" + y)
//	var position = new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, rheight));
	var position = new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, y));
	condiv.id = "temp";

	roadControl = new RouteControls();
	map.addControl(roadControl, position);
	condiv = document.getElementById('controlsDiv');
	if ( condiv.hasChildNodes() )
	{
	   while ( condiv.childNodes.length >= 1 )
	   {
	      condiv.removeChild( condiv.firstChild );
	   }
	}
	condiv.id = "roadDiv";
	condiv.style.backgroundImage = 'url(' + rtImageLocation + 'bg.png' + ')';
	var temp = document.getElementById('temp');
	var onroadLink = createIconDOM('', roadType, 'Follow road', rtImageLocation + 'cycle.png', 'onroadType', 'controlEnabled');
	condiv.appendChild(onroadLink);
	temp.id = "controlsDiv";

};

function saveRoute() {
   if (avFlag == 1)
   {
	var XML = new XMLWriter(), trackXML = new XMLWriter();
	var prvte, nickname, nickemail, ind = 0;

     if (editmode == 0)
     {
	trailName = document.getElementById('trailName').value;
	var txt = document.getElementById('trailDesc');
	if(userID == 0)
	{
	  nickname = document.getElementById('nicklName').value;
	  nickemail = document.getElementById('nickEmail').value;
	}

	if (trailName == '')
	{
	   alert ("Please enter a name for this trail!");
	   document.getElementById('trailName').focus();
	   ind = 1;
	}
	else if (trailName.length < 6)
	{
	   alert ("Please enter a name with at least 6 characters!");
	   document.getElementById('trailName').focus();
	   ind = 1;
	}
	else if (userID == 0 && nickname == '')
	{
	   alert ("Please enter your nickname!");
	   document.getElementById('nicklName').focus();
	   ind = 1;
	}
	else if (userID == 0 && nickname.length < 6)
	{
	   alert ("Please enter a nickname with at least 6 characters!");
	   document.getElementById('nicklName').focus();
	   ind = 1;
	}
	else if (userID == 0 && nickemail == '')
	{
	   alert ("Please enter your email address! \n(You will be contacted only if there is a genuine \nrequest for information about this trail.)");
	   document.getElementById('nickEmail').focus();
	   ind = 1;
	}
	if (document.getElementById('chkPrivate'))
	{
	   if (document.getElementById('chkPrivate').checked == true)
	      prvte = 1;
	   else
	      prvte = 0;
	}
	var intro;
/*	if (txt.value.length == 0)*/
	  intro = "--No Description--";
/*	else
	  intro = txt.value;
*/
     }

	   if (ind == 1) return;
	
//	   intro = intro.toString().replace(/\n/g,"<br />");
	   XML.BeginNode("route");
	   for (i = 0; i < route.length ; i++)
	   {
	      XML.BeginNode("marker");
	      XML.WriteString((String(route[i])));
	      XML.EndNode();
	   }
	   XML.Close(); // Takes care of unended tags.
	   trackXML.BeginNode("routedetails");
	   for (i = 0; i < routeTrack.length ; i++)
	   {
	      trackXML.BeginNode("mode");
	      trackXML.WriteString((String(routeTrack[i])));
	      trackXML.EndNode();
	   }
	   trackXML.Close();

	   distance = calcDistance();
	   document.getElementById('sbutton').disabled = true;
	   document.getElementById('sbutton').childNodes[0].nodeValue = "Saving";
	   document.getElementById('cbutton').disabled = true;

	   if (editmode == 0)
	      var queryString = "mode=save&XML=" + XML.ToString() + "&start=" + String(route[0]) + "&zoom=" + (parseInt(map.getZoom()) - 1) + "&center=" + map.getCenter() + "&uid=" + userID + "&uname=" + userName + "&name=" + trailName + "&intro=" + intro  + "&length=" + distance + "&private=" + prvte + "&nickname=" + nickname + "&nickemail=" + nickemail + "&detailXML=" + trackXML.ToString() + "&upload=1";
	   else
	      var queryString = "mode=update&XML=" + XML.ToString() + "&length=" + distance + "&detailXML=" + trackXML.ToString() + "&trailid=" + trailID;
	   ajaxFunction("index.php?option=com_savetrail&format=raw", queryString, '', saveMessage);
   }
   else
   {
	var nameMsg = document.getElementById('nameMsg');
	nameMsg.innerHTML = "Trail name yet to be verified!";
	nameMsg.style.color = 'yellow';
	document.getElementById('trailName').focus();
   }
};

function saveMessage(text)
{
	if (text.responseText.indexOf("Trail Saved.") != -1)
	{
	   if (disControl)
	   {
	      map.removeControl(disControl);
	   }
	   if (noneControl)
	   {
	      map.removeControl(noneControl);
	   }

	   var controlsDiv = document.getElementById('controlsDiv');
	   var editRouteLink = document.getElementById('editRouteLink');
	   if (editRouteLink)
	      controlsDiv.removeChild(editRouteLink);
//	   alert ("Trail saved!");
//	   fadeOut("mesControl","disDiv","50","2000");
   	   document.getElementById('sbutton').childNodes[0].nodeValue = "Saved!";
	   var trailid = text.responseText.substring(text.responseText.indexOf(".")+1);
	   SqueezeBox.fromElement('index.php?option=com_addltrailinfo&tmpl=component&trailid='+trailid, {handler: 'iframe', size: {x: 600, y: 500}});

	   switch (wrFlag)
	   {
	     case 1:
	     {
	       wrFlag = 0;
	       createCookie("trailid", trailid , 0);
	       parent.location.href = "index.php?option=com_contentsubmit&view=article&layout=bysection&id=2&Itemid=16&ctgid=4&trailId=" + trailid;
	       break;
	     }
	     case 2:
	     {
	       wrFlag = 0;
	       parent.location.href = "index.php?option=com_contentsubmit&view=article&layout=bysection&id=1&Itemid=17";
	       break;
	     }
	     case 3:
	     {
	       wrFlag = 0;
	       parent.location.href = "index.php?option=com_eventlist&view=editevent&Itemid=23&tsk=" + trailid + "&name=" + trailName;
	       break;
	     }
	     default:
	     {
	       resetMap(0);
	       tidText = "searchMode=tid&rid=" + trailid;
	       tidFlag = 1;
	       ajaxFunction("index.php?option=com_searchtrails&format=raw", tidText, '', getTrailList);
	       map.enableScrollWheelZoom();
	       map.enableDragging();
	     }
	   }
	}
	else
	{
	   mesControl = new ShoDistance();

   	   x = newPosition(1);
	   if (!document.getElementById('mesDiv'))
	      position = new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(x.x, 0));
	   map.addControl(mesControl, position);
	   var mesStat = document.getElementById('disStat');
	   mesStat.innerHTML = "Trail could not be saved - Technical problem!";
	   fadeOut("mesControl","disDiv","50","2000");
	}
};

function cancelRoute() {
	if (confirm ('Definitely cancel this trail?'))
	{
	   resetMap();
	}
};

function resetMap(saveState) {

	if('undefined' == typeof saveState) {saveState = 1;}
	map.clearOverlays();
	route = [];
	markerList = [];
	routeTrack = [];
	disTrack = [];
	dirpoints = [];
	roadind = 0;
	
	draw = 0;
	recording = false;
	var controlsDiv = document.getElementById('controlsDiv');
	var searchContainer = document.getElementById('searchContainer');
	var cancelLink = document.getElementById('cancelLink');
	var saveRouteLink = document.getElementById('saveRouteLink');
	var removeMarkerLink = document.getElementById('removeMarkerLink');

	map.setCenter(new GLatLng(startLat, startLng), startZoom.toInt());
	map.disableScrollWheelZoom();

	if (cancelLink)
	{
	   controlsDiv.removeChild(cancelLink);
	}
	if (saveRouteLink)
	{
	   controlsDiv.removeChild(saveRouteLink);
	}
	if (removeMarkerLink)
	{
	   controlsDiv.removeChild(removeMarkerLink);
	}
	if (insControl)
	{
	   map.removeControl(insControl);
	}
	if (disControl)
	{
	   map.removeControl(disControl);
	}
	if (noneControl)
	{
	   map.removeControl(noneControl);
	}
	if (saveControl)
	{
	   map.removeControl(saveControl);
	}
	if (searchContainer)
	{
	   map.removeControl(searchContainer);
	}
	if (creControl)
	{
	   map.removeControl(creControl);
	}
	if (roadControl)
	{
	   map.removeControl(roadControl);
	}
	var createRouteLink = createIconDOM ('',beginRecording,'Create a trail',rtImageLocation + 'walker.png','createRouteLink','controlEnabled');
	var findRouteLink  = createIconDOM ('',showRouteList,'Search Map',rtImageLocation + 'find.png','findRouteLink','controlEnabled');

	//order of appending the links is important. keep it consistent with routecontrols() custom control
	controlsDiv.appendChild(createRouteLink);
	if (userID != 0)
	{
	   var myTrailsLink = createIconDOM ('',showMyTrail,'My trails',rtImageLocation + 'walker.png','myTrailsLink','controlEnabled');
	   controlsDiv.appendChild(myTrailsLink);
	}

	controlsDiv.appendChild(findRouteLink);
};

function saveCurrentRoute() {
	draw = 0;
	if (route.length <= 1)
	{
	   alert ("There should at least be an origin and a destination for a trail.");
	   draw = 1;
	}
	else
	{
	   //Below code is to position the map coordinates and zoom level so that all the points in the
	   //trail are shown. These settings will be captured when the trail is saved.
	   var bounds = new GLatLngBounds();
	   for (var i = 0; i < route.length; i++) {
	      bounds.extend(route[i]);
           }
	   map.setZoom(map.getBoundsZoomLevel(bounds));
	   map.setCenter(bounds.getCenter());
	   map.disableScrollWheelZoom();
	   map.disableDragging();
	   if (!document.getElementById('saveDiv') && editmode == 0)
	   {
	      if (disControl)
	      {
	         map.removeControl(disControl);
	      }
	      if (insControl)
	      {
	         map.removeControl(insControl);
	      }
	      saveControl = new SaveControls();
	      map.addControl(saveControl);
	      // remove icons for writing a trail, tip and creating a trip
	      if (userID == 0)
	      {
		var wIcon = document.getElementById("wIcon");
		var tIcon = document.getElementById("tIcon");
		var cIcon = document.getElementById("cIcon");

		wIcon.parentNode.removeChild(wIcon);
		tIcon.parentNode.removeChild(tIcon);
		cIcon.parentNode.removeChild(cIcon);
	      }
	      document.getElementById('trailName').focus();
	   }
	   else
	   {
	      avFlag = 1;
	      saveRoute();
	   }
	}
};

function showMyTrail() {
	trailind = 0;
	for (i=0; i < labelMarker.length; i++)
	   map.removeOverlay(labelMarker[i]);
	map.removeControl(creControl);
	map.removeControl(infoControl);
	var controlsDiv = document.getElementById('controlsDiv');
	var editRouteLink = document.getElementById('editRouteLink');
	if (editRouteLink)
	   controlsDiv.removeChild(editRouteLink);
	var trailHead = document.getElementById('trailheading');
	if (trailHead)
	  trailHead.innerHTML = "";
	var searchBox = document.getElementById('searchBox');
	var searchButton = document.getElementById('searchButton');
	var searchContainer = document.getElementById('searchContainer');

	if (searchBox && searchButton)
	{
	   searchContainer.removeChild(searchBox);
	   searchContainer.removeChild(searchButton);
	   searchContainer.parentNode.removeChild(searchContainer);
	}
	labelMarker = [];
	route = [];
	markerList = [];
	polyList = [];
	routeTrack = [];
//	disTrack = 0;
	srchFlag = 0;
	map.clearOverlays();
	var uidText = "";
	uidText = "searchMode=min&uid=" + userID;
	ajaxFunction("index.php?option=com_searchtrails&format=raw", uidText, '', getTrailList);
	//ajaxFunction(url, queryString, returnVar, retFunction) -- function definition
};

function showRouteList() {
	while (route.length > 0)
	{	   
	   route = [];
	   markerList = [];
	   polyList = [];
	   routeTrack = [];
//	   disTrack = 0;
	}
	map.clearOverlays();
	if (creControl)
	   map.removeControl(creControl);
	var controlsDiv = document.getElementById('controlsDiv');
	var editRouteLink = document.getElementById('editRouteLink');
	if (editRouteLink)
	   controlsDiv.removeChild(editRouteLink);
	var trailHead = document.getElementById('trailheading');
	if (trailHead)
	   trailHead.innerHTML = "";
	map.removeControl(infoControl);
//	map.closeExtInfoWindow();

	map.enableScrollWheelZoom();

	var controlsDiv = document.getElementById('controlsDiv');
	if (!document.getElementById('searchBox'))
	{
	   var iconLink = document.createElement("span");
	   var searchBox = document.createElement("input");
/*	   var dropSelect = document.getElementById('dropSelect');
	   if (dropSelect)
	   {
	      controlsDiv.removeChild(dropSelect);
	   }
*/
	   iconLink.id = "searchContainer";
	   searchBox.id = "searchBox";
	   searchBox.type = "text";
	   if(window.addEventListener)
	      searchBox.addEventListener('keydown', handleEnter, false);
	   else
	      searchBox.attachEvent('onkeydown', handleEnter);
	   iconLink.appendChild(searchBox);

	   var searchButton = document.createElement("input");
	   searchButton.id = "searchButton";
	   searchButton.type = "submit";
	   searchButton.value = "go";

	   if(window.addEventListener)
	      searchButton.addEventListener('click', showAddress, false);
	   else
	      searchButton.attachEvent('onclick', showAddress);
	   var spEle2 = document.createElement("div");
	   spEle2.id = "dropSpan2";
	   var myZoom = document.createElement("SELECT");
	   myZoom.id = "dropZSelect";
	   var myOps = document.createElement("option") ;
	   myOps.setAttribute('value',"0");
	   txt = document.createTextNode("Zoom level");
	   myOps.appendChild(txt);
	   myZoom.appendChild(myOps);
	   for ( x = 1 ; x < 18  ; x++ ) {
	     myOps = document.createElement("option");
	     myOps.setAttribute('value',x);
	     txt = document.createTextNode(x);
	     myOps.appendChild(txt);
	     myZoom.appendChild(myOps);
	   }

	   spEle2.appendChild(myZoom);

	   iconLink.appendChild(searchButton);
//	   iconLink.appendChild(spEle2);
	   controlsDiv.appendChild(iconLink);
	   searchBox.focus();
	}
	else
	{
	   map.clearOverlays();
	   document.getElementById('searchBox').value ="";
	   document.getElementById('searchBox').focus();

	   while (route.length > 0)
	   {
	      map.clearOverlays();
	      route = [];
	      markerList = [];
	      polyList = [];
	      routeTrack = [];
//	      disTrack = 0;
	   }
	   for (i = 0; i < labelMarker.length; i++)
	   {
	      map.removeOverlay(labelMarker[i]);
	   }

	   labelMarker = [];
	   map.setCenter(new GLatLng(startLat, startLng), startZoom.toInt());
	}
};

function handleEnter (e) {
	var key = e.keyCode || e.which;
	if (key == 13)
	{
	    showAddress(); //Search only for location, also bring up nearby trails
	}
};


function handleEnter2 (e) {
	var key = e.keyCode || e.which;
	if (key == 13)
	{
	    checkAddress(); //Search only for location, don't bring up nearby trails
	}
};

function callSearchMod() {
	while (route.length > 0)
	{
	   map.clearOverlays();
	   route = [];
	   markerList = [];
	   routeTrack = [];
//	   disTrack = 0;
	   polyList = [];
	}
	for (i = 0; i < labelMarker.length; i++)
	{
	   map.removeOverlay(labelMarker[i]);
	}

	labelMarker = [];
	map.setCenter(new GLatLng(startLat, startLng), startZoom.toInt());

//	For some strange reason, the code below is causing an error in IE - solved - searchBox was not declared as variable
	var searchBox = document.getElementById('searchBox');

	if (searchBox.value.length == 0)
	{
	   alert ("Please enter all or part of the trail name which you want to find.");
	   searchBox.focus();
	}
	else
	{
	   for (i = 0; i < labelMarker.length; i++)
	   {
	      map.removeOverlay(labelMarker[i]);
      	   }
	   labelMarker = [];
	   setSTextCookie();
	   searchText = "searchMode=name&searchName=" + searchBox.value + "&uid=" + userID;
	   ajaxFunction("index.php?option=com_searchtrails&format=raw", searchText, '', getTrailList);
	   //ajaxFunction(url, queryString, returnVar, retFunction) -- function definition
	}
};

function createMarker(pt, id, markmode) {
	//Marker will be created at 'pt'. If the no of points in the trail is more than 2, then the next
	//point will be considered a turn and the icon changed for this point.
	//This function is called when removing a point from the trail too - that's because the icons
	//have to be changed and all.
	//'markmode' will be	0 - when new point is during trail creation
	//			1 - when start pts of trails are shown - trail on road get auto directions
	//			2 - when last point on trail is being undone.
	//'id' is to signify which icon to set for the new marker.
	var cIcon;

	// 0 is for the start of the route, 1 for all other points on the route.
	if (id == 0)
	   cIcon = customIcons["start"];
	else if (id == 1)
	   cIcon = customIcons["end"];
	else if (id == 2)
	   cIcon = customIcons["redstar"];

	//If user clicks on a point near a road but not exactly on a road, then save the point nearest to the road instead of the clicked point.
	if (markmode == 1)
	{
	  var dirn=new GDirections();
	  dirn.loadFromWaypoints([pt,pt],{getPolyline:true});
	  GEvent.addListener(dirn, "load", function() {
	     var p = dirn.getPolyline().getVertex(0);
	     route.splice(route.length - 1, 1);
	     route.push(p);
	  });
	}

	marker = new GMarker(pt, {draggable: false, icon: cIcon});
	map.addOverlay(marker);
	markerList.push(marker);

	if (markmode == 1)
	{
	   if (markerList.length > 1)
	   {
	     if ('undefined' != typeof route[route.length - 2])
	       dirpoints[dirpoints.length] = route[route.length - 2];
	     if (markerList.length > 2)
	     {
	       map.removeOverlay(markerList[markerList.length -2]);
	       marker = new GMarker(route[route.length - 2], {draggable: false, icon: customIcons["turn"]});
	       map.addOverlay(marker);
	       markerList.splice(markerList.length - 2, 1, marker);
	     }
     	     dirpoints[dirpoints.length] = route[route.length - 1];
	   }
	}
	else
	{
	   if (markerList.length > 2)
	   {
	      map.removeOverlay(markerList[markerList.length -2]);
	      marker = new GMarker(route[route.length - 2], {draggable: false, icon: customIcons["turn"]});
	      map.addOverlay(marker);
	      markerList.splice(markerList.length - 2, 1, marker);
	   }

	   //The condition below is for removing the turn icon when the destination of the trail is removed
	   //and the new destination is at the point where earlier a turn was.
	   if (markmode == 2)
	   {
	      map.removeOverlay(markerList[markerList.length -2]);
	      markerList.splice(markerList.length - 2, 1);
	   }
	}
};

function removeMarker(errorRet) {
if('undefined' == typeof errorRet) {errorRet = 0;}
	if (route.length == 0)
	{
	   alert ("There is no marker to remove.");
	}
	else if (route.length == 1)
	{
	   map.removeOverlay(markerList[markerList.length - 1]);
	   route = [];
	   markerList = [];
	   routeTrack = [];
//	   disTrack = 0;
	   polyList = [];
	   dirpoints = [];
	   map.clearOverlays();
	   var disLabel = document.getElementById("disStat");
	   disLabel.innerHTML = "Total distance - 0km";
	}
	else
	{
	   if (routeTrack[route.length - 1] == 0)
	   {
	     map.removeOverlay(polyList[polyList.length -1]);
	     polyList.splice(polyList.length - 1, 1);
	   }
	   else 
	   {
	     if (errorRet == 1)
	     {}
	     else
	     {
	       map.removeOverlay(poly[poly.length-1]);
	       poly.splice(poly.length - 1, 1);
	       dirpoints.splice(dirpoints.length - 2, 2);
	       polycnt--;
	     }
	   }
	   route.splice(route.length - 1, 1);
	   routeTrack.splice(routeTrack.length - 1, 1);
	   disTrack.splice(disTrack.length - 1, 1);
	   map.removeOverlay(markerList[markerList.length -1]);
	   markerList.splice(markerList.length - 1, 1);
	   if (route.length > 1)
	      createMarker(route[route.length - 1], 1, 2); // Have to call create marker function so that the
	      						   // icons for turn and last point can be taken care of.
	   distance = calcDistance();
	   var disLabel = document.getElementById("disStat");
	   disLabel.innerHTML = "Total distance - " + distance + "km";
	}
};

//Check if said trail name is already saved or if it is still available.
function cbfMsg () {
	var cbMsg = document.getElementById('cbMsg');
	var wIcon = document.getElementById("wIcon");
	var tIcon = document.getElementById("tIcon");
	var cIcon = document.getElementById("cIcon");
	var iconSpan = document.getElementById("iconspan");

	if (document.getElementById('chkPrivate').checked == true)
	{
	  cbMsg.innerHTML = "You will not be able to create reviews, trips for private trails.";
	  // remove icons for writing a trail, tip and creating a trip
	  wIcon.parentNode.removeChild(wIcon);
	  tIcon.parentNode.removeChild(tIcon);
	  cIcon.parentNode.removeChild(cIcon);
	}
	else
	{
	  cbMsg.innerHTML = "";
	  if (!wIcon)
	  {
		wIconA = document.createElement("a");
		wIconA.setAttribute('href','#');

		wIcon = document.createElement("img");
		wIcon.src = rtImageLocation + "write.png";
		wIcon.id = "wIcon";
		wIcon.onclick = saveWrite;
		wIcon.alt = "Write a review for this trail";
		wIcon.title = "Write a review for this trail (will also save this trail)";
		wIconA.appendChild(wIcon);
		iconSpan.appendChild(wIconA);
	  }
	  if (!tIcon)
	  {
		tIconA = document.createElement("a");
		tIconA.setAttribute('href','#');

		tIcon = document.createElement("img");
		tIcon.src = rtImageLocation + "tip.png";
		tIcon.id = "tIcon";
		tIcon.onclick = saveTip;
		tIcon.alt = "Write a tip for this trail";
		tIcon.title = "Write a tip for this trail (will also save this trail)";
		tIconA.appendChild(tIcon);
		iconSpan.appendChild(tIconA);
	  }
	  if (!cIcon)
	  {
		cIconA = document.createElement("a");
		cIconA.setAttribute('href','#');

		cIcon = document.createElement("img");
		cIcon.src = rtImageLocation + "create.png";
		cIcon.id = "cIcon";
		cIcon.onclick = saveTrip;
		cIcon.alt = "Create a trip for this trail";
		cIcon.title = "Create a trip for this trail (will also save this trail)";
		cIconA.appendChild(cIcon);
		iconSpan.appendChild(cIconA);
	  }
	}
};

//Set flag, after saving, control routed to creating review.
function saveWrite () {
	wrFlag = 1;
	saveRoute();
};

//Set flag, after saving, control routed to creating review.
function saveTip () {
	wrFlag = 2;
	saveRoute();
};

//Set flag, after saving, control routed to creating trip.
function saveTrip () {
	wrFlag = 3;
	saveRoute();
};

//Check if said trail name is already saved or if it is still available.
function checkAval () {
	var trailName = document.getElementById('trailName');
	var nameMsg = document.getElementById('nameMsg');
	if (trailName.value.length >= 6) {
//	  nameMsg.innerHTML = "Checking availability of trail name...";
//	  nameMsg.style.color = 'yellow';
	  var searchTrailname = "searchMode=all&searchName=" + trailName.value + "&uid=" + userID;
	  ajaxFunction("index.php?option=com_searchtrails&format=raw", searchTrailname, '', getAval);
	  //ajaxFunction(url, queryString, returnVar, retFunction) -- function definition
	}
	else
	{
	  nameMsg.innerHTML = "";
//	  nameMsg.innerHTML = "Please enter a name longer than 6 characters.";
//	  nameMsg.style.color = 'red';
	  avFlag = 0;
	}
};

//Check if said trail name is already saved or if it is still available.
function getAval (jsonText) {
	var nameMsg = document.getElementById('nameMsg');
	if (eval( jsonText.responseText ) == null)
	{
	  nameMsg.innerHTML = "Trail name available!";
	  nameMsg.style.color = '#73d670';
	  avFlag = 1;
	}
	else
	{
	  nameMsg.innerHTML = "Please choose a different name for the trail.";
	  nameMsg.style.color = 'red';
	  avFlag = 0;
	}
};

// Create labeledMarkers at the start points of all trails resulting from query in the textbox or in the routesearch module
function getTrailList (jsonText) {
//alert(jsonText.responseText);
    if (draw == 1 && recording ==1)
    {
        return;
    }
    if (eval( jsonText.responseText ) == null)
    {
	if (disControl)
	   map.removeControl(disControl);
    }
    else if (eval( jsonText ))
    {
        if (eval("(" + jsonText.responseText + ")"))
        {
           searchResult = eval("(" + jsonText.responseText + ")");
        }
        else
        {
           return;
        }
        var cnt = 0;
        var templist = [];
	var icon = customIcons["ind"];
	var bounds = new GLatLngBounds();
	var uname, intro, zoom, tname, username;
//        while (cnt < searchResult.length)
        for (cnt=0; cnt < parseInt(searchResult.length); cnt++)
        {
          opts = {
                "icon": icon,
                "clickable": true,
                "labelText": cnt + 1,
                "labelOffset": new GSize(-16, -16)
          };
          tempvar = searchResult[cnt].center;
          tempvar = tempvar.replace("(","");
          tempvar = tempvar.replace(")","");
          var newt = tempvar.split(",");
          ltd = newt[0];
          lng = newt[1];
//          ltd = tempvar.substring(1,tempvar.indexOf(","));
//          lng = tempvar.substring(tempvar.indexOf(",") + 1, tempvar.indexOf(")"));

          if (!searchResult[cnt].uname)
          {
             if (searchResult[cnt].nname)
               uname = searchResult[cnt].nname;
             else
               uname = 'Guest';
             username = uname;
          }
          else
          {
             uname = "<a href='index.php?option=com_comprofiler&task=userProfile&user=" + searchResult[cnt].uid + "&Itemid=34'>" + searchResult[cnt].uname + "</a>";
             uname = uname.toString().replace(/&/g,"&amp;");
             username = searchResult[cnt].uname;
          }

          var j = 0;
          var str = "";
          var cur = "";
          var vir = "";
          intro = searchResult[cnt].intro;
          zoom = searchResult[cnt].zoom.toInt();
          tname = searchResult[cnt].name;
          intro = intro.toString().replace(/\\n/g,"<br />");
	  var XML = new XMLWriter();
	   XML.BeginNode("trail");
	   XML.BeginNode("data");
	   XML.WriteString(searchResult[cnt].name);
	   XML.EndNode();
	   XML.BeginNode("data");
	   XML.WriteString(searchResult[cnt].xml);
	   XML.EndNode();
	   XML.BeginNode("data");
	   XML.WriteString(searchResult[cnt].detailxml);
	   XML.EndNode();
	   XML.BeginNode("data");
	   XML.WriteString(searchResult[cnt].center);
	   XML.EndNode();
	   XML.BeginNode("data");
	   XML.WriteString(searchResult[cnt].zoom);
	   XML.EndNode();
	   XML.BeginNode("data");
	   XML.WriteString(intro);
	   XML.EndNode();
	   XML.BeginNode("data");
	   XML.WriteString(searchResult[cnt].length);
	   XML.EndNode();
	   XML.BeginNode("data");
	   XML.WriteString(searchResult[cnt].uid);
	   XML.EndNode();
	   XML.BeginNode("data");
	   XML.WriteString(searchResult[cnt].createtime);
	   XML.EndNode();
	   XML.BeginNode("data");
	   XML.WriteString(uname);
	   XML.EndNode();
	   XML.BeginNode("data");
	   XML.WriteString(searchResult[cnt].nname);
	   XML.EndNode();
/*	   XML.BeginNode("data");
	   XML.WriteString(searchResult[cnt].nemail);
	   XML.EndNode();
*/	   XML.BeginNode("data");
	   XML.WriteString(searchResult[cnt].upload);
	   XML.EndNode();
	   XML.BeginNode("data");
	   XML.WriteString(searchResult[cnt].encodeurl);
	   XML.EndNode();
	  XML.Close(); // Takes care of unended tags.

	  var trailid = searchResult[cnt].id;
	  trailID = trailid;
	  var createrId = searchResult[cnt].uid;
	  var trailName = searchResult[cnt].name;
	  var trailTime = searchResult[cnt].createtime;
	  var trailLength = searchResult[cnt].length;
	  var encodeurl = searchResult[cnt].encodeurl;
	  var revUrl = 'index.php?option=com_contentsubmit&view=article&layout=bysection&id=2&Itemid=16&ctgid=4&trailId=' + trailid;
	  var tipUrl = "index.php?option=com_contentsubmit&view=article&layout=bysection&id=1&Itemid=17";
	  var tripUrl = "index.php?option=com_eventlist&view=editevent&Itemid=23&tsk=" + trailid + "&name=" + trailName;
	  var searchUrl = 'index.php?option=com_searchtrailreviews&rid=' + trailid + '&searchName=' + searchResult[cnt].name;

	  var html = "<div id='pwrapper' class='wrapper' style='height:auto'>";
	  html += "<div id='pheader' class='windowheader'><b>" + searchResult[cnt].name + "</b><br /><div class='headertext'>Created " + trailTime + " by " + uname + "</div></div>";
//	  html += "<div class='header'><div class='leftcol'><b>" + searchResult[cnt].name + "</b><br />- " + uname + "</div></div>";
//	  html += "<div class='rightcol2'>" + uname + "</div></div>";
	  html += "<div id='pcont' class='cont'><div class='leftcol'>Length of trail - <b>" + trailLength + " km</b></div> <br />";
	  html += "<div id='pcontleft' class='leftcol'>" + intro + "</div></div>";

	  html += "<div id='picons' class='icons'><div class='rightcol'>";
	  html += "<a href='#' onClick=\"getTrailDetails(&quot;" + encodeURI(XML.ToString()) + "&quot;," + trailid + ")\"><img alt='View Trail' title='View Trail' src=" + rtImageLocation + "view.png /></a></div>";
	  html += "<div class='rightcol'><a href='#' onClick=\"alert('Please use the URL below the map and paste it in your website.');document.getElementById('embedUrl').select();\")";
	  html += "<img alt='Copy Trail' title='Copy Trail' src=" + rtImageLocation + "copy.png /></a></div>";

	  //Can read or write a review for a trail. While writing the review, first set a cookie with the trail i.d first, then open the create review page.
	  if (userID > 0)
	  {
	     html += "<div class='rightcol'><a href='#' onClick=\"window.location='" + searchUrl + "'\">";
	     html += "<img alt='Read Review' title='Read Review' src=" + rtImageLocation + "read.png /></a></div>";
	     html += "<div class='rightcol'><a href='#' onClick=\"javascript:createCookie('trailid'," + trailid + ",0);window.location='" + revUrl + "'\">";
	     html += "<img alt='Write a Review' title='Write a Review' src=" + rtImageLocation + "write.png /></a></div>";
//	     html += "<div class='rightcol'><a href='#' onClick=\"window.location='" + tipUrl + "'\">";
//	     html += "<img alt='Write a Tip' title='Write a Tip' src=" + rtImageLocation + "tip.png /></a></div>";
	     html += "<div class='rightcol'><a href='#' onClick=\"window.location='" + tripUrl + "'\">";
	     html += "<img alt='Plan a Trip' title='Plan a Trip' src=" + rtImageLocation + "create.png /></a>" + "</div></div>";
	  }
	  else
	     html += "<div class='rightcol'><a href='#' onClick=\"window.location='" + searchUrl + "'\"><img alt='Read Review' title='Read Review' src=" + rtImageLocation + "read.png /></a>" + "</div></div>";

	  html += "</div>";

          var newma = new GMarker(new GLatLng(ltd, lng), customIcons["blustar"]);
	  newma.html = html;
	  labelMarker.push(newma);
          bounds.extend(new GLatLng(ltd, lng));
        }
        if (srchFlag == 1)
          trMarkerCluster = new MarkerClusterer(map, labelMarker);
        if (cnt == 1 && srchFlag != 1)
        {
          map.setZoom(zoom);
          if (trailID != 0) {
/*            var trailhead = document.getElementById("trailheading");
            if (trailhead)
              trailhead.innerHTML = tname;
*/            getTrailDetails(encodeURI(XML.ToString()), trailid );
          }
        }
        else if (cnt > 1 && srchFlag != 1)
	  map.setZoom(map.getBoundsZoomLevel(bounds));

        if (srchFlag != 1)
	  map.setCenter(bounds.getCenter());

	if (tidFlag == 1) {
	   tidFlag = 0;
	   var condiv = document.getElementById('controlsDiv');
	   var position = new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 45 + condiv.offsetHeight + 10));
	   condiv.id = "temp";

	   infoControl = new RouteControls();
	   map.addControl(infoControl, position);
	   map.setMapType(G_HYBRID_MAP);
	   condiv = document.getElementById('controlsDiv');
	   if ( condiv.hasChildNodes() )
	   {
	       while ( condiv.childNodes.length >= 1 )
	       {
	   	condiv.removeChild( condiv.firstChild );
	       }
	   }
	   condiv.id = "infoDiv";
	   var temp = document.getElementById('temp');
	   temp.id = "controlsDiv";
	   var lenLabel = document.createElement("div");
	   lenLabel.id = 'traillen';
	   if (trailLength == 0)// && upload == 2)
	     trailLength = calcDistance();
	   var lenText = document.createTextNode("Length of Trail - " + trailLength + " km.");
	   var ownLabel = document.createElement("div");
	   ownLabel.id = 'trailown';
	   var ownText = document.createTextNode("Created by " + username);
	   var dtLabel = document.createElement("div");
	   dtLabel.id = 'traildt';
	   var dtText = document.createTextNode("on " + trailTime);
	   lenLabel.appendChild(lenText);
	   ownLabel.appendChild(ownText);
	   dtLabel.appendChild(dtText);
	   condiv.appendChild(lenLabel);
	   condiv.appendChild(ownLabel);
	   condiv.appendChild(dtLabel);
	}
    }
};

function test(){
alert ('x');
}

/***************************http://www.codeproject.com/KB/ajax/Flash_user_confirmation.aspx********************************/
function fadeOut (control, id, steps, duration) {
	var fadeOutComplete;
	var element = document.getElementById(id);
	for (i = 0; i <= 1; i += (1 / steps)) {
	   setTimeout("setOpacity('" + id + "', "  + (1 - i) + ")", i * duration);
	   fadeOutComplete = i * duration;
	}
	setTimeout("map.removeControl(" + control + ")", fadeOutComplete);
}

function setOpacity(id, level) {
	var element = document.getElementById(id);
	element.style.zoom = 1;
	element.style.opacity = level;
	element.style.MozOpacity = level;
	element.style.KhtmlOpacity = level;
	element.style.filter = "alpha(opacity=" + (level * 100) + ");";
	if (level == 1)
	   pausecomp(750);
}
/***************************http://www.codeproject.com/KB/ajax/Flash_user_confirmation.aspx********************************/

// When search is done, the search component is called first with raw o/p. It returns the list of trails and their details.
// The below function reads the details and overlays a marker at the start point of each trail.
function getTrailDetails(XML, trailid) {
	if (typeof(trMarkerCluster) != "undefined") trMarkerCluster.clearMarkers();
	var roadtype=0, trlmrk=0, rdmrk=0;
	xml2 = decodeURI(XML).replace(/\&lt;/g,'<');
	xml2 = decodeURI(xml2).replace(/\&gt;/g,'>');
	map.clearOverlays();
	var xmlDoc = GXml.parse(xml2);

	var data = xmlDoc.documentElement.getElementsByTagName("data");
	var name = data[0].childNodes[0].nodeValue;
	var markers = xmlDoc.documentElement.getElementsByTagName("marker");
	var details = xmlDoc.documentElement.getElementsByTagName("mode");
	var trackXML2 = new XMLWriter();;

	var center = data[3].childNodes[0].nodeValue;
	var zoom = data[4].childNodes[0].nodeValue;
	var intro = data[5].childNodes[0].nodeValue;
	trailLength = data[6].childNodes[0].nodeValue;
	createrId = data[7].childNodes[0].nodeValue;
	var upload = data[11].childNodes[0].nodeValue;
	var encodeurl = data[12].childNodes[0].nodeValue;
	disTrack = [];
	var y=0;
	var bounds = new GLatLngBounds();
	trailind = 1;
	trailID = trailid;
	var embedurl = document.getElementById("embedurl");
	var imageurl = document.getElementById("imageurl");
	
	var addhtml = "<iframe width=\"475\" height=\"475\" frameborder=\"0\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" src=\"http://www.tripnaksha.com/index.php?option=com_trailembed&tview="+trailID+"&trailname="+name+"&tmpl=component&theight=475&twidth=475\"></iframe>";
	if (embedurl) {embedurl.value = addhtml;}
	addhtml = "<img src=\"http://maps.google.com/maps/api/staticmap?size=475x475&amp;path=weight:3|color:red|enc:"+encodeurl+"&amp;sensor=false&amp;maptype=hybrid\" alt=\""+name+"\"/>";
	if (imageurl) {imageurl.value = addhtml;}

          if (userID == createrId && userID != 0 && upload == 1)
          {
             var controlsDiv = document.getElementById('controlsDiv');
             var myTrailsLink = document.getElementById('myTrailsLink');
//             var editRouteLink = createIconDOM ('',editRecording,'Edit this trail',rtImageLocation + 'walker.png','editRouteLink','controlEnabled');
//             controlsDiv.insertBefore(editRouteLink, myTrailsLink);
             var editRouteLink = document.getElementById('editRouteLink');
             if (!editRouteLink)
             {
                editRouteLink = createIconDOM ('',editRecording,'Edit this trail',rtImageLocation + 'walker.png','editRouteLink','controlEnabled');
                controlsDiv.insertBefore(editRouteLink, myTrailsLink);
             }
          }

	for (var i = 0; i < markers.length; i++) {
	   point = markers[i].childNodes[0].nodeValue;
	   var tempvar = point.replace("(","");
	   tempvar = tempvar.replace(")","");
	   var newt = tempvar.split(",");
	   var lat = newt[0];
           var lng = newt[1];
	   latlng = new GLatLng(lat, lng);
	   if (details.length > 0)
	   {
	      routeTrack.push(details[i].childNodes[0].nodeValue);
	      if (i>0 && details[i].childNodes[0].nodeValue == 1)
	         rdmrk = 1;
	      else if (i>0 && details[i].childNodes[0].nodeValue == 0)
	         trlmrk = 1;
	   }
	   else
	   {
	      routeTrack.push(0);
	      trlmrk = 1;
	      rdmrk = 0;
	   }
	  if (trlmrk==1 && rdmrk==1)
	     roadtype = 1;
	  else
	     roadtype = 0;

	   if (i == 0)
	   {
	      route.push(latlng);
	      map.clearOverlays();
	      createMarker(latlng, 0, 0);
	   }
	   else
	   {
	      route.push(latlng);
	      if (route.length > 1 && upload == 2)
                disTrack[disTrack.length] = route[route.length - 1].distanceFrom(route[route.length - 2]);

	      if (upload == 1)
	         createMarker(latlng, 1, 0);
	      if (upload == 1 && details.length == 0)
	        drawPolyline(0, roadtype, i, markers.length);
	      if (upload == 1 && details.length > 0 && details[i].childNodes[0].nodeValue == 0)
	        drawPolyline(0, roadtype, i, markers.length);
      	      if (upload == 1 && details.length > 0 && details[i].childNodes[0].nodeValue == 1)
	      {
	        dirpoints[y+1] = latlng;
	        point = markers[i-1].childNodes[0].nodeValue;
	        tempvar = point.replace("(","");
	        tempvar = tempvar.replace(")","");
	        newt = tempvar.split(",");
	        lat = newt[0];
	        lng = newt[1];
	        latlng = new GLatLng(lat, lng);
	        dirpoints[y] = latlng;
	        y=y+2;
	      }

	   }
	   if (upload == 2 && i+1 == markers.length)
	     createMarker(latlng, 1, 0);
	   bounds.extend(route[i]);
          }
	  if (y>0)
	    getDirections(dirpoints, 0, roadtype);
	  else if (upload == 2)
	    drawPolyline(2, roadtype);
//GLog.write (roadtype+"-"+trlmrk+"-"+rdmrk);

          var trailhead = document.getElementById("trailheading");
          if (trailhead)
            trailhead.innerHTML = name;

	  map.setZoom(map.getBoundsZoomLevel(bounds));
	  map.setCenter(bounds.getCenter());
	  var bounds = map.getBounds();
	  var southWest = bounds.getSouthWest();
	  var northEast = bounds.getNorthEast();
	  srchText = "uid=" + userID + "&southLng=" + bounds.getSouthWest().lng() + "&southLtd=" + bounds.getSouthWest().lat() + "&northLng=" + bounds.getNorthEast().lng() + "&northLtd=" + bounds.getNorthEast().lat();
//	  ajaxFunction("index.php?option=com_locsearchpois&format=raw&mode=0", srchText, '', getPOIList);
};
function showAddress() {
	while (route.length > 0)
	{
	   map.clearOverlays();
	   route = [];
	   markerList = [];
	   polyList = [];
	   routeTrack = [];
	}
	for (i = 0; i < labelMarker.length; i++)
	{
	   map.removeOverlay(labelMarker[i]);
	}

	labelMarker = [];
	trailind = 0;
	var searchBox = document.getElementById('searchBox');
	address = searchBox.value;

	if (searchBox.value.length == 0)
	{
	   alert ("Please enter all or part of the trail name which you want to find.");
	   searchBox.focus();
	}
	else {
		geocoder.getLocations(address,function(point) {
		      if (!point || point.Status.code != 200) {
			alert(address + " not found");
		      }
		      else {
			var latLngBox = point.Placemark[0].ExtendedData.LatLonBox;
			var latLngBound = new GLatLngBounds(new GLatLng(latLngBox.south, latLngBox.west), new GLatLng(latLngBox.north, latLngBox.east));
			zoomLevel = map.getBoundsZoomLevel(latLngBound);
			var latlng;
			latlng = new GLatLng(point.Placemark[0].Point.coordinates[1], point.Placemark[0].Point.coordinates[0]);

			srchFlag = 1;
			map.setCenter(latlng, zoomLevel);
			var bounds = map.getBounds();
			var southWest = bounds.getSouthWest();
			var northEast = bounds.getNorthEast();
			var srchText = "";
			srchText = "uid=" + userID + "&southLng=" + bounds.getSouthWest().lng() + "&southLtd=" + bounds.getSouthWest().lat() + "&northLng=" + bounds.getNorthEast().lng() + "&northLtd=" + bounds.getNorthEast().lat();
			ajaxFunction("index.php?option=com_locsearchtrails&format=raw", srchText, '', getTrailList);
			ajaxFunction("index.php?option=com_locsearchpois&format=raw&mode=0", srchText, '', getPOIList);
		      }
		});
      	}
}

//--------------------------------------------------------------------------------------------------------------------------//
//--------------------------------------------------------------------------------------------------------------------------//
function getPOIList (jsonText) {
//alert (jsonText.responseText);
	var searchResult;
	var labelPOI = [];
	if (eval( jsonText ))
    	{
    	   if (eval("(" + jsonText.responseText + ")"))
    	   {
    	      searchResult = eval("(" + jsonText.responseText + ")");
    	   }
    	   else
    	   {
    	      return;
    	   }
    	   var cnt = 0;
    	   while (cnt < searchResult.length)
    	   {
    	      var point = new GLatLng(searchResult[cnt].lat, searchResult[cnt].lng);
    	      querystr = "index.php?option=com_locsearchpois&format=raw&uid=" + userID + "&mode=2&point=" + point;
    	      marker = CreateExtMarker(point, querystr, '2');
    	      labelPOI.push(marker);
    	      cnt ++;
    	   }
    	   poiMarkerCluster = new MarkerClusterer(map, labelPOI);
    	}
}
function poiSuccess(jsonText) {
	if (jsonText.responseText.indexOf("Trail Saved.") != -1)
	{
	  document.getElementById('message').innerHTML = "Waypoint info saved!";
	  document.getElementById('poidesc').value = "";
	  document.getElementById('poilabel').value = "";
	  document.getElementById('poiemail').value = "";
	}
	else
	{
	  document.getElementById('message').innerHTML = "Could not save data - Technical problem!";
	}
}
function poiSave(latlng, lat, lng) {
	if (document.getElementById('poilabel').value.length == 0) // && document.getElementById('poilabel') == null )
	{
	  document.getElementById('message').innerHTML = "Please label this point.";
	}
	if (userID == 0)
	  if (document.getElementById('poiemail').value.length == 0 || echeck(document.getElementById('poiemail').value) == false)
	    document.getElementById('message').innerHTML = "Please enter a valid email i.d.";
	  else
	    queryString = "point=" + latlng + "&lat=" + lat + "&lng=" + lng + "&userid=" + userID + "&label=" + document.getElementById('poilabel').value+ "&email=" + document.getElementById('poiemail').value + "&desc=" + document.getElementById('poidesc').value;
	else
	  queryString = "point=" + latlng + "&lat=" + lat + "&lng=" + lng + "&userid=" + userID + "&label=" + document.getElementById('poilabel').value+ "&email=" + "&desc=" + document.getElementById('poidesc').value;
	
	ajaxFunction('index.php?option=com_savepoi&format=raw', queryString, '', poiSuccess);
}
function addPOI(lng, lat) {
	var latlng = new GLatLng(lat, lng);
	contextmenu.style.visibility = "hidden";
	querystr = "index.php?option=com_locsearchpois&format=raw&uid=" + userID +"&mode=1&point='" + latlng + "'";
	marker = CreateExtMarker(latlng, querystr, '1');
	map.addOverlay(marker);
	GEvent.trigger(marker,"click");
}
function CreateExtMarker(point, querystr, mode) {
        var marker = new GMarker(point, {draggable: false, icon: customIcons["redstar"]});
          GEvent.addListener(marker, 'click', 
            function(){
              marker.openExtInfoWindow(
                map,
                "custom_info_window_red",
                "<p>Loading Tabs...</p>",
                {beakOffset: 3, ajaxUrl: querystr}
              ); 
            }
          );

         GEvent.addDomListener(map, 'extinfowindowupdate',function(){
            var tabs = new Array(document.getElementById("tab0"),document.getElementById("tab1"));
            if( tabs.length > 0 ){
              var tabContentsArray = new Array(tabs.length);
              for( i=0; i < tabs.length; i++){
                tabContentsArray[i] = document.getElementById("tab" + i + "_content");
                if( i > 0){
                  hideel(tabContentsArray[i]);
                }
                else
                  show(tabContentsArray[i]);
                tabs[i].setAttribute("name", i.toString());
              
                GEvent.addDomListener(tabs[i],"click",function(){
                  var tabIndex = this.getAttribute("name");
                
                  for(tabContentIndex=0; tabContentIndex < tabs.length; tabContentIndex++){
                    if( tabContentIndex == tabIndex ){
                      show(tabContentsArray[tabContentIndex]);
                    }else{
                      hideel(tabContentsArray[tabContentIndex]);
                    }
                  }
                  map.getExtInfoWindow().resize();
                });
              }
            }
          });
        return marker;
}
function searchLoc() {
	contextmenu.innerHTML = contextmenu.innerHTML + "<div class='context'><input type='text' id='searchtext' name='searchtext' size='10' maxlength='30'/><input type='submit' id='searchbut' name='enviar' value='Go'><\/div>";
	
	var searchBox = document.getElementById('searchtext');
	var searchBut = document.getElementById('searchbut');
	searchBox.focus();
	if(window.addEventListener)
	   searchBox.addEventListener('keydown', handleEnter2, false);
	else
	   searchBox.attachEvent('onkeydown', handleEnter2);
	if(window.addEventListener)
	   searchBut.addEventListener('click', checkAddress, false);
	else
	   searchBut.attachEvent('onclick', checkAddress);

}

function checkAddress(passedcountry){
//alert(passedcountry);
	contextmenu.style.visibility = "hidden";
	var searchtext = document.getElementById('searchtext');
	if (passedcountry)
	  address = passedcountry;
	else
	  address = searchtext.value;

	if (address.length == 0)
	{
	   alert ("Please enter all or part of the location name which you want to find.");
	   searchtext.focus();
	}
	else {
		geocoder.getLocations(address,function(point) {
		      if (!point || point.Status.code != 200) {
			alert(address + " not found");
		      }
		      else {
			var latLngBox = point.Placemark[0].ExtendedData.LatLonBox;
			var latLngBound = new GLatLngBounds(new GLatLng(latLngBox.south, latLngBox.west), new GLatLng(latLngBox.north, latLngBox.east));
			zoomLevel = map.getBoundsZoomLevel(latLngBound);
			var latlng = new GLatLng(point.Placemark[0].Point.coordinates[1], point.Placemark[0].Point.coordinates[0]);
			map.setCenter(latlng, zoomLevel);
			var bounds = map.getBounds();
			var center = map.getCenter();
			startLng = center.lng();
			startLat = center.lat();
			var southWest = bounds.getSouthWest();
			var northEast = bounds.getNorthEast();
			srchText = "uid=" + userID + "&southLng=" + bounds.getSouthWest().lng() + "&southLtd=" + bounds.getSouthWest().lat() + "&northLng=" + bounds.getNorthEast().lng() + "&northLtd=" + bounds.getNorthEast().lat();
			ajaxFunction("index.php?option=com_locsearchtrails&format=raw", srchText, '', getTrailList);
			ajaxFunction("index.php?option=com_locsearchpois&format=raw&mode=0", srchText, '', getPOIList);
		      }
		});
	}
}
//--------------------------------------------------------------------------------------------------------------------------//
//--------------------------------------------------------------------------------------------------------------------------//
function load(uname, uid, tid, city){
	if (GBrowserIsCompatible())
	{
	    createCookie("testCookie","1",0);
	    if (!readCookie("testCookie"))
	      alert("Cookies are disabled in your browser.\nPlease enable them so that TripNaksha works well for you.\nWe do not store any personal information!");
	    userName = uname;
	    userID = uid;
	    currentURL = parent.location.href;

            contextmenu = document.createElement("div");
            contextmenu.id = "contextmenu";
            contextmenu.style.visibility="hidden";
            contextmenu.style.backgroundImage = 'url(' + rtImageLocation + 'yellow.png' + ')';
            contextmenu.style.border="1px solid #000";
            contextmenu.style.color="#000";

	    var map_div = document.getElementById("map_canvas");
	    if (tid != 0)
	    {
	       map = new GMap2(map_div,{size:new GSize(450,400)});
	       map.addControl(new GSmallZoomControl3D());
	       map.getContainer().style.overflow = "hidden";
	    }
	    else
	    {
	       map = new GMap2(document.getElementById("map_canvas"),{size:new GSize(750,600)});
	       map.addControl(new GLargeMapControl3D());
	    }
	    // Add Google map controls
	    map.addControl(new GMapTypeControl(), new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(73,0)));
	    map.setCenter(new GLatLng(startLat, startLng), startZoom.toInt());
	    // Add custom controls
	    map.addControl(new ScreenControls());
	    map.addControl(new RouteControls());
	    map.getContainer().appendChild(contextmenu);
	    geocoder = new GClientGeocoder();
	    // do not pass map_canvas as that makes the directions/route/steps show up on the map
	    gdir=new GDirections(null);
//	    gdir=new GDirections(null, document.getElementById("map_canvas"));
	    mgr = new MarkerManager(map);

	    if (city && tid == 0) {
	       srchFlag = 1;
	       strtFlag = 1;
	       checkAddress (city);
	    }
	    else {
	       map.setCenter(new GLatLng(startLat, startLng), startZoom.toInt());
	    }
	    if (tid != 0)
	    {
		var tidText = "";
		trailID = tid;
		tidText = "searchMode=tid&rid=" + tid;
		ajaxFunction("index.php?option=com_searchtrails&format=raw", tidText, '', getTrailList);
		tidFlag = 1;
	    }
	    else if (currentURL.indexOf("Itemid") != -1 && currentURL.indexOf("41") != -1)
	    {
		var uidText = "";
		uidText = "searchMode=min&uid=" + userID;
		ajaxFunction("index.php?option=com_searchtrails&format=raw", uidText, '', getTrailList);
	    }
	    else if (location.href.indexOf("tmpl") != -1)
	    {
	        if (location.href.indexOf("find") != -1)
	          showRouteList();
	        else
	          beginRecording();
	    }

	    GEvent.addListener(gdir, "load", function() {
	       if (gdircomp == 1)
	       {
		   poly[polycnt] = gdir.getPolyline();
		   map.addOverlay(poly[polycnt]);
		   polycnt++;
		   gdircomp = 1;
		   distance = calcDistance();
		   var disLabel = document.getElementById("disStat");
		   if (disLabel)
		      disLabel.innerHTML = "Total distance - " + distance + "km";
	       }
	       else
	           if (gdircomp+2 <= dirpoints.length)
	           {
		      poly[polycnt] = gdir.getPolyline();
		      map.addOverlay(poly[polycnt]);
		      disTrack[routeTrack.length - 2] = gdir.getDistance().meters;
		      polycnt++;
		      distance = calcDistance();
		      var disLabel = document.getElementById("disStat");
		      if (disLabel)
		         disLabel.innerHTML = "Total distance - " + distance + "km";
	           }
	    });

	    GEvent.addListener(gdir,"error", function() {
		var code = gdir.getStatus().code;
		var reason="Code "+code;
		if (reasons[code]) {
		  reason = "Code "+code +" : "+reasons[code];
		  if (code == 620)
		     limit = limit + 100;
		} 
		alert("Failed to obtain directions, "+reason);
		removeMarker(1);
	    });

	    GEvent.addListener(map, 'singlerightclick', function(pixel,tile) {
		// store the "pixel" info in case we need it later
		// adjust the context menu location if near an egde
		// create a GControlPosition
		// apply it to the context menu, and make the context menu visible
		clickedPixel = pixel;
		var latlng = map.fromContainerPixelToLatLng(pixel);
		var x=pixel.x;
		var y=pixel.y;
		if (x > map.getSize().width - 120) { x = map.getSize().width - 120 }
		if (y > map.getSize().height - 100) { y = map.getSize().height - 100 }
		var pos = new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(x,y));  
		pos.apply(contextmenu);
//		contextmenu.innerHTML = '<a href="javascript:zoomIn()"><div class="context">&nbsp;&nbsp;Zoom in&nbsp;&nbsp;<\/div><\/a>'
//		                    + '<a href="javascript:zoomOut()"><div class="context">&nbsp;&nbsp;Zoom out&nbsp;&nbsp;<\/div><\/a>'
//		                    + '<a href="javascript:addPOI(\'' + latlng.x + "','" + latlng.y + '\')"><div class="context">&nbsp;&nbsp;Add //Comment&nbsp;&nbsp;<\/div><\/a>'
//		                    + '<a href="javascript:searchLoc()"><div class="context">&nbsp;&nbsp;Search next waypoint&nbsp;&nbsp;<\/div><\/a>';
		contextmenu.innerHTML = '<a href="javascript:addPOI(\'' + latlng.x + "','" + latlng.y + '\')"><div class="context">&nbsp;&nbsp;Add Comment&nbsp;&nbsp;<\/div><\/a>'
		                    + '<a href="javascript:searchLoc()"><div class="context">&nbsp;&nbsp;Search next waypoint&nbsp;&nbsp;<\/div><\/a>';
		contextmenu.style.visibility = "visible";
	    });
	    
	    GEvent.addListener(map, "click", function(overlay, point, opoint) {
	        contextmenu.style.visibility = "hidden";
	        if (overlay instanceof GMarker && trailind == 1) map.closeInfoWindow();//test ("4");
	        if(overlay && draw == 0 && trailind == 0)// && mode == 0)
	        {
    	           var infoWindow = map.getInfoWindow();
    	           if (overlay instanceof GMarker)
    	           {
			overlay.openExtInfoWindow(
			   map,
			   "custom_info_window_red",
			   overlay.html,
			   {beakOffset: 3}
			); 
/*
			   map.openInfoWindowHtml(overlay.getPoint(), overlay.html, {
			       onOpenFn: function(){
			       	  var pcont = document.getElementById('pcont').offsetHeight;
			       	  var picon = document.getElementById('picons').offsetHeight;
			          var pheight = document.getElementById('pheader').offsetHeight + (pcont >= picon ? pcont: picon);
			          var pwidth  = document.getElementById('pcontleft').offsetWidth + document.getElementById('picons').offsetWidth;
			          infoWindow.reset(overlay.getPoint(),infoWindow.getTabs(),new GSize(pwidth,pheight),null,null);
			          }
			       }
			   );
*/
	           }
	        }
	        else if (point && recording && draw == 1)// && mode == 0)
	        {
	            if (route.length == 0)
	            {
	               //for the first point
	               route.push(point);
		       map.clearOverlays();
		       markerList = [];
		       if (roadind == 0 )
		       {
	                 routeTrack.push('0');
//	                 if (dirn.loadFromWaypoints([point.toUrlValue(6),point.toUrlValue(6)],{getPolyline:true});) alert("x");
		         createMarker(point, 0, 0);
		       }
		       else
		       {
	                 routeTrack.push('1');
		         createMarker(point, 0, 1);
		       }
	            }
	            else //if (route[route.length-1].x != point.x && route[route.length-1].y != point.y)
	            {
	               //for all points after the first one.
	               route.push(point);
		       if (roadind == 0 )
		       {
	                 createMarker(point, 1, 0);
	                 routeTrack.push('0');
	                 drawPolyline();
	                 distance = calcDistance();
		       }
	               else
		       {
	                 createMarker(point, 1, 1);
	                 routeTrack.push('1');
			 if (dirpoints.length >= 2)
			   drawPolyRec(dirpoints.length-2);
			 else
			   drawPolyRec(0);
	                 distance = calcDistance();
		       }
	               var disStat = document.getElementById('disStat');
	               if (disStat)
	                  disStat.innerHTML = "Total distance - " + distance + "km";
	            }
	        }
	    });
	}
	else {
	    alert("Sorry, the Google Maps API is not compatible with this browser");
	}
}
