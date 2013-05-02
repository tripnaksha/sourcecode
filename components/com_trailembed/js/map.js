//----------------------------------------Global declarations---------------------------------------------------------------//
var startLng = 82.353516;
var startLat = 23.362429;
var startZoom = 5;
var marker = null;
var count = 0;
var route = [];
var map = null;
var rtImageLocation = null;
var rtLocation = null;
var markerList = [];
var polyList = [];
var dirpoints = [];
var routeTrack = [];
var mapContainer;
var userName = "";
var userID = "";
var userMode = 0;
var trailName;
var searchResult;
var disControl;
var infoControl;
var geocoder;
var tidFlag = 0;
var trailID = 0;
var trailind = 0;
var gdir, mgr = null, poly = [], polycnt = 0, drDistance = 0, disTrack = [], gdircomp = 0, errorRet = 0, limit = 100;
//------------------------------------End Global declarations---------------------------------------------------------------//
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
};
//----------------------------------------Custom marker icons-----------------------------------------------------------------//
var turn = new GIcon();
turn.image = rtImageLocation + "turn.png";
turn.iconSize = new GSize(1, 1);
turn.iconAnchor = new GPoint(1, 1);
turn.infoWindowAnchor = new GPoint(1, 1);

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
ind.image = rtImageLocation + "ind.png";
ind.iconSize = new GSize(32, 32);
ind.iconAnchor = new GPoint(14, 13);
ind.infoWindowAnchor = new GPoint(25, 7);

var customIcons = [];
customIcons["start"] = start;
customIcons["end"] = end;
customIcons["turn"] = turn;
customIcons["ind"] = ind;
//----------------------------------------------------------------------------------------------------------------------------//
/************************************************************* www.sean.co.uk ************************************************/
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
	   }
	} catch (error3) {
		alert('Page not found');
		return false;
	}
};

//----------------------------------------------------------------------------------------------------------------------------//
//----------------------------------------------------------------------------------------------------------------------------//
//----------------------------------------------------------------------------------------------------------------------------//

/**
* Function to draw the offroad point to point polyline. Each point stored in route[] is used for this.
* @param -none-
*/
function drawPolyline(upload, roadtype, no, markers) {
	var line = [], div=0;
	if (upload != 2)
	{
	   for (i = route.length; i > route.length - 2 ; i--)
	     {line.push(route[i - 1]); }//alert (route[i-1]);
           disTrack[disTrack.length] = route[routeTrack.length - 1].distanceFrom(route[routeTrack.length - 2]);
	   var polyline = new GPolyline(line, "#ff0033", 4);
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
* Function to draw the directions for onroad points when stored trail is displayed. Each point stored in dirpoints[] is used for this.
* @param -none-
*/
function getDirections(dirpoints, num, mode) {
	var dir=new GDirections();
	if (num ==0) poly =[];
	
	GEvent.addListener(dir, "load", function() {
//var date = new Date(milliseconds);
//GLog.write(date);
	   poly[polycnt] = dir.getPolyline();
	   disTrack[disTrack.length] = dir.getDistance().meters;
	   map.addOverlay(poly[polycnt]);
	   polycnt++;
           if ((num+2) < dirpoints.length) getDirections(dirpoints, (num+2), mode);
//           if ((num+2) == dirpoints.length && mode == 0) drawWayMarkerauto (poly);
	});

	dir.load("from: " + dirpoints[num].toUrlValue() + " to: " + dirpoints[num+1].toUrlValue(), {"getSteps":false, "getPolyline":true, preserveViewport:true});
}

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
};

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
function creditControls() {}

creditControls.prototype = new GControl();

creditControls.prototype.initialize = function(map) {
	var container = document.createElement("div");
	container.id="CreditConDiv";

	var headText = document.createTextNode("Map created at ");

	var CreditDiv = document.createElement("div");
	CreditDiv.id = 'CreditDiv';
	var creditLink = document.createElement("a");
//	creditLink.setAttribute('href','http://localhost/staging/index.php?option=com_traildisplay&tview='+trailID);
	creditLink.setAttribute('href','http://www.TripNaksha.com/index.php?option=com_routes&view=traildisplay&tview='+trailID);
	creditLink.setAttribute("target", "_blank");
	var creditLinkText = document.createTextNode("TripNaksha.com");
	creditLink.appendChild(creditLinkText);

	CreditDiv.appendChild(headText);
	CreditDiv.appendChild(creditLink);
	container.appendChild(CreditDiv);

	GEvent.addDomListener(CreditDiv, "click", screenType);
	var headText = document.createTextNode("Full Screen");

	map.getContainer().appendChild(container);
	return container;
};

creditControls.prototype.getDefaultPosition = function() {
	return new GControlPosition(G_ANCHOR_BOTTOM_RIGHT, new GSize(7, 15));
};

creditControls.prototype.setButtonStyle_ = function(button) {
};

function ScreenControls() {}

ScreenControls.prototype = new GControl();

ScreenControls.prototype.initialize = function(map) {
	var container = document.createElement("div");
	container.id="ScreenTypeConDiv";

	var headText = document.createTextNode("Full Screen");

	var ScreenTypeDiv = document.createElement("div");
	ScreenTypeDiv.id = 'ScreenTypeDiv';
	this.setButtonStyle_(ScreenTypeDiv);

//	ScreenTypeDiv.appendChild(headText);
	container.appendChild(ScreenTypeDiv);

//	GEvent.addDomListener(ScreenTypeDiv, "click", screenType);
//	var headText = document.createTextNode("Full Screen");

	map.getContainer().appendChild(container);

	return container;
};

ScreenControls.prototype.getDefaultPosition = function() {
	return new GControlPosition(G_ANCHOR_TOP_RIGHT);
};

ScreenControls.prototype.setButtonStyle_ = function(button) {
};

function RouteControls() {
};

RouteControls.prototype = new GControl();

RouteControls.prototype.initialize = function(map) {
	var txt = "";
	var container = document.createElement("div");

	var controlsDiv = document.createElement("div");
	controlsDiv.id = 'controlsDiv';
	this.setButtonStyle_(controlsDiv);
	container.appendChild(controlsDiv);

	map.getContainer().appendChild(container);
	return container;
};

RouteControls.prototype.getDefaultPosition = function() {
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 45));
};

RouteControls.prototype.setButtonStyle_ = function(button) {
	button.style.border = "2px solid black";
};

function ShoDistance() {
};

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
	var xPos = bounds.getNorthEast().lat() - ((bounds.toSpan().lat()) * 2/5);
	var yPos = bounds.getNorthEast().lng() - ((bounds.toSpan().lng()) * .7);
	point= new GLatLng(xPos, yPos);
	var x = map.fromLatLngToContainerPixel(point);
	return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(x.x, 0));
};

//----------------------------------------------------------------------------------------------------------------------------//

function createMarker(pt, id, markmode) {
	//Marker will be created at 'pt'. If the no of points in the trail is more than 2, then the next
	//point will be considered a turn and the icon changed for this point.
	//This function is called when removing a point from the trail too - that's because the icons
	//have to be changed and all.
	//'markmode' will be	0 - when new point is during trail creation
	//			1 - when start pts of trails are shown
	//			2 - when last point on trail is being undone.
	//'id' is to signify which icon to set for the new marker.
	var cIcon;

	// 0 is for the start of the route, 1 for all other points on the route.
	if (id == 0)
	   cIcon = customIcons["start"];
	else
	   cIcon = customIcons["end"];

	marker = new GMarker(pt, {draggable: false, icon: cIcon});
	map.addOverlay(marker);
	markerList.push(marker);

	if (markerList.length > 2 && markmode != 1)
	{
	   map.removeOverlay(markerList[markerList.length -2]);
	   marker = new GMarker(route[route.length - 2], {draggable: false, icon: customIcons["turn"]});
//	   map.addOverlay(marker);
	   markerList.splice(markerList.length - 2, 1, marker);
	}

	//The condition below is for removing the turn icon when the destination of the trail is removed
	//and the new destination is at the point where earlier a turn was.
	if (markmode == 2)
	{
	   map.removeOverlay(markerList[markerList.length -2]);
	   markerList.splice(markerList.length - 2, 1);
	}
};

function screenType() {
	var ScreenTypeDiv = document.getElementById('ScreenTypeDiv');
	var mapcon = document.getElementById('map_container');
	var mapdiv = document.getElementById('map_canvas');
	curText = ScreenTypeDiv.innerHTML;

	if (curText == 'Full Screen')
	{
	   scroll(0,0);
/*	   mapcon.style.position = 'absolute';
   	   mapcon.style.left = 0;
   	   mapcon.style.top = 0;
   	   mapcon.style.height = '100%';
   	   mapcon.style.width = '100%';
   	   mapcon.style.zIndex = '10000';
*/	   
	   mapdiv.style.width = '100%';
	   mapdiv.style.height = '700px';
	   mapdiv.style.position = 'absolute';
   	   mapdiv.style.left = 0;
   	   mapdiv.style.top = 0;
   	   mapdiv.style.height = '100%';
   	   mapdiv.style.width = '100%';
   	   mapdiv.style.zIndex = '10000';
	   map.checkResize();
	   document.getElementsByTagName("body")[0].scroll = 'no';
	   document.getElementsByTagName("body")[0].style.overflow = 'hidden';
	   ScreenTypeDiv.innerHTML = 'Restore';
	}
	else if (curText == 'Restore')
	{
	   ScreenTypeDiv.innerHTML = 'Full Screen';
/*	   mapcon.style.position = 'relative';
   	   mapcon.style.left = 'auto';
   	   mapcon.style.top = 'auto';
   	   mapcon.style.height = 'auto';
   	   mapcon.style.width = 'auto';
*/
	   mapdiv.style.height = '600px';
	   mapdiv.style.width = '750px';
	   mapdiv.style.position = 'relative';
   	   mapdiv.style.left = 'auto';
   	   mapdiv.style.top = 'auto';
   	   mapdiv.style.height = 'auto';
   	   mapdiv.style.width = 'auto';
	   document.getElementsByTagName("body")[0].scroll = 'yes';

	   var IE = /*@cc_on!@*/false;
	   if(!IE)
	      document.getElementsByTagName('body')[0].style.overflow = 'visible';
	   map.checkResize();
	}
}

// Create labeledMarkers at the start points of all trails resulting from query in the textbox or in the routesearch module
function getTrailList (jsonText) {
//alert('jsonText '+jsonText.responseText);
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

        while (cnt < searchResult.length)
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
          for (i=0; i< intro.length; i++)
          {
            cur = intro.substring(i, i+1);
            vir = cur + intro.substring(i+1, i+2);
            if (vir == "\\n") // && intro.substring(i+1, i+2) == "n")
              j = 0;
            if (j == 50)
            {
              if (intro.substring(i).indexOf(" ") < 3 && intro.substring(i).indexOf(" ") != -1)
              {
                 intro = str + intro.substring(i).replace(/^\s*|\s*$/,'\\n');
              }
              else
              {
                 intro = str + "\\n" + intro.substring(i);
              }
              str += intro.substring(i, i+1);
              j = 0;
            }
            else
              str += intro.substring(i, i+1);
            j++;
          }
//          intro = str.toString().replace(/\\n/g,"<br />");
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
	  var trailId = searchResult[cnt].id;
	  var trailName = searchResult[cnt].name;
	  var trailTime = searchResult[cnt].createtime;
	  var trailLength = searchResult[cnt].length;
	  var revUrl = 'index.php?option=com_contentsubmit&view=article&layout=bysection&id=2&Itemid=16&ctgid=4&trailId=' + trailid;
	  var tipUrl = "index.php?option=com_contentsubmit&view=article&layout=bysection&id=1&Itemid=17";
	  var tripUrl = "index.php?option=com_eventlist&view=editevent&Itemid=23&tsk=" + trailId + "&name=" + trailName;
	  var searchUrl = 'index.php?option=com_searchtrailreviews&rid=' + trailid + '&searchName=' + searchResult[cnt].name;

	  var html = "<div id='pwrapper' class='wrapper' style='height:auto'>";
	  html += "<div id='pheader' class='header'><b>" + searchResult[cnt].name + "</b><br /><div class='headertext'>Created " + trailTime + " by " + uname + "</div></div>";
	  html += "<div id='pcont' class='cont'><div class='leftcol'>Length of route - <b>" + trailLength + " km</b></div> <br />";
	  html += "<div id='pcontleft' class='leftcol'>" + intro + "</div></div>";

	  html += "<div id='picons' class='icons'><div class='rightcol'>";
	  html += "<a id='vtrail' href='#' onClick=\"getTrailDetails(&quot;" + encodeURI(XML.ToString()) + "&quot;)\"><img alt='View Trail' title='View Trail' src=" + rtImageLocation + "view.png /></a></div>";
	     html += "<div class='rightcol'><a href='#' onClick=\"window.location='" + searchUrl + "'\"><img alt='Read Review' title='Read Review' src=" + rtImageLocation + "read.png /></a>" + "</div></div>";

	  html += "</div>";

          cnt ++;
          bounds.extend(new GLatLng(ltd, lng));
        }
        if (cnt == 1)
        {
          map.setZoom(zoom);
          getTrailDetails(encodeURI(XML.ToString()), trailid );
        }
        else
	  map.setZoom(map.getBoundsZoomLevel(bounds));
	map.setCenter(bounds.getCenter());
	if (tidFlag == 1)
	{
	   tidFlag = 0;
	   var position = new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 45));
	   infoControl = new RouteControls();
	   map.addControl(infoControl, position);
	   condiv = document.getElementById('controlsDiv');
	   if ( condiv.hasChildNodes() )
	   {
	       while ( condiv.childNodes.length >= 1 )
	       {
	   	condiv.removeChild( condiv.firstChild );
	       }
	   }
	   condiv.id = "infoDiv";
	   var lenLabel = document.createElement("div");
	   lenLabel.id = 'traillen';
	   var lenText = document.createTextNode("Length of Route - " + trailLength + " km.");
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

// When search is done, the search component is called first with raw o/p. It returns the list of trails and their details.
// The below function reads the details and overlays a marker at the start point of each trail.
function getTrailDetails(XML) {
	var roadtype=0, trlmrk=0, rdmrk=0;
	map.clearOverlays();
	xml2 = decodeURI(XML).replace(/\&lt;/g,'<');
	xml2 = decodeURI(xml2).replace(/\&gt;/g,'>');
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

	disTrack = [];
	var y =0 ;
	var bounds = new GLatLngBounds();
	trailind = 1;

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
/*	      if (upload == 1 && details.length == 0)
	        drawPolyline(0, roadtype, i, markers.length);
*/	      if (upload == 1 && details.length > 0 && details[i].childNodes[0].nodeValue == 0)
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

//	    drawPolydirs();
//	    drawPolyRec(0);
          var trailhead = document.getElementById("trailheading");
          if (trailhead)
            trailhead.innerHTML = name;
            var trailtail = document.getElementById("trailtail");
            if (trailtail)
            {
              trailtail.innerHTML = "Embed URL - ";
              trailtail.style.height ="auto";
              trailtail.style.visibility = "visible";
              if (!document.getElementById('embedUrl'))
              {
                var iconLink = document.createElement("span");
                var embedUrl = document.createElement("input");
                embedUrl.id = "embedUrl";
                embedUrl.type = "text";
                embedUrl.style.width = "500px";
                embedUrl.value = "<iframe width=\"475\" height=\"475\" frameborder=\"0\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" src=\"http://www.tripnaksha.com/index.php?option=com_trailembed&tview=" + trailid + "&tmpl=component&theight=475&twidth=475\"></iframe>";
                trailtail.appendChild(embedUrl);
              }
           }
	  map.setZoom(map.getBoundsZoomLevel(bounds));
	  map.setCenter(bounds.getCenter());
	  var bounds = map.getBounds();
	  var southWest = bounds.getSouthWest();
	  var northEast = bounds.getNorthEast();
	  srchText = "uid=" + userID + "&southLng=" + bounds.getSouthWest().lng() + "&southLtd=" + bounds.getSouthWest().lat() + "&northLng=" + bounds.getNorthEast().lng() + "&northLtd=" + bounds.getNorthEast().lat();
//	  ajaxFunction("index.php?option=com_locsearchpois&format=raw&mode=0", srchText, '', getPOIList);
};

//--------------------------------------------------------------------------------------------------------------------------//
//--------------------------------------------------------------------------------------------------------------------------//
function redirect(x){
	window.parent.location.href="http://www.tripnaksha.com";
}
function load(uname, uid, tid){
	if (GBrowserIsCompatible())
	{
	    userName = uname;
	    userID = uid;

            var head = document.getElementsByTagName("head")[0];

	    map = new GMap2(document.getElementById("map_canvas"));
	    map.setMapType(G_HYBRID_MAP);
	    map.setCenter(new GLatLng(startLat, startLng), startZoom.toInt());
	    map.addControl(new GLargeMapControl());
	    geocoder = new GClientGeocoder();
	    map.setCenter(new GLatLng(startLat, startLng), startZoom.toInt());
//	    map.addControl(new ShoDistance());
	    map.addControl(new GMapTypeControl(), new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7,0)));
//	    map.addControl(new ScreenControls());
	    map.addControl(new ScreenControls(), new GControlPosition(G_ANCHOR_BOTTOM_RIGHT, new GSize(7,15)));
	    map.enableScrollWheelZoom();
	    geocoder = new GClientGeocoder();
	    gdir=new GDirections(document.getElementById("map_canvas"));
	    mgr = new MarkerManager(map);

	    if (tid != 0)
	    {
		var tidText = "";
		trailID = tid;
		tidText = "searchMode=tid&rid=" + tid;
		ajaxFunction("index.php?option=com_searchtrails&format=raw", tidText, '', getTrailList);
		tidFlag = 1;
	    }
	    map.addControl(new creditControls());

	    GEvent.addListener(map, "dblclick", function(overlay, point, opoint) {
		    map.ZoomIn();
	    });

	}
	else {
	    alert("Sorry, the Google Maps API is not compatible with this browser");
	}
}
