/*********************************************************************\
*                                                                     *
* epolys.js                                          by Mike Williams *
*                                                                     *
* A Google Maps API Extension                                         *
*                                                                     *
* Adds various Methods to GPolygon and GPolyline                      *
*                                                                     *
* .Contains(latlng) returns true is the poly contains the specified   *
*                   GLatLng                                           *
*                                                                     *
* .Area()           returns the approximate area of a poly that is    *
*                   not self-intersecting                             *
*                                                                     *
* .Distance()       returns the length of the poly path               *
*                                                                     *
* .Bounds()         returns a GLatLngBounds that bounds the poly      *
*                                                                     *
* .GetPointAtDistance() returns a GLatLng at the specified distance   *
*                   along the path.                                   *
*                   The distance is specified in metres               *
*                   Reurns null if the path is shorter than that      *
*                                                                     *
* .GetPointsAtDistance() returns an array of GLatLngs at the          *
*                   specified interval along the path.                *
*                   The distance is specified in metres               *
*                                                                     *
* .GetIndexAtDistance() returns the vertex number at the specified    *
*                   distance along the path.                          *
*                   The distance is specified in metres               *
*                   Reurns null if the path is shorter than that      *
*                                                                     *
* .Bearing(v1?,v2?) returns the bearing between two vertices          *
*                   if v1 is null, returns bearing from first to last *
*                   if v2 is null, returns bearing from v1 to next    *
*                                                                     *
*                                                                     *
***********************************************************************
*                                                                     *
*   This Javascript is provided by Mike Williams                      *
*   Community Church Javascript Team                                  *
*   http://www.bisphamchurch.org.uk/                                  *
*   http://econym.org.uk/gmap/                                        *
*                                                                     *
*   This work is licenced under a Creative Commons Licence            *
*   http://creativecommons.org/licenses/by/2.0/uk/                    *
*                                                                     *
***********************************************************************
*                                                                     *
* Version 1.1       6-Jun-2007                                        *
* Version 1.2       1-Jul-2007 - fix: Bounds was omitting vertex zero *
*                                add: Bearing                         *
* Version 1.3       28-Nov-2008  add: GetPointsAtDistance()           *
* Version 1.4       12-Jan-2009  fix: GetPointsAtDistance()           *
*                                                                     *
\*********************************************************************/


// === A method for testing if a point is inside a polygon
// === Returns true if poly contains point
// === Algorithm shamelessly stolen from http://alienryderflex.com/polygon/ 
GPolygon.prototype.Contains = function(point) {
  var j=0;
  var oddNodes = false;
  var x = point.lng();
  var y = point.lat();
  for (var i=0; i < this.getVertexCount(); i++) {
    j++;
    if (j == this.getVertexCount()) {j = 0;}
    if (((this.getVertex(i).lat() < y) && (this.getVertex(j).lat() >= y))
    || ((this.getVertex(j).lat() < y) && (this.getVertex(i).lat() >= y))) {
      if ( this.getVertex(i).lng() + (y - this.getVertex(i).lat())
      /  (this.getVertex(j).lat()-this.getVertex(i).lat())
      *  (this.getVertex(j).lng() - this.getVertex(i).lng())<x ) {
        oddNodes = !oddNodes
      }
    }
  }
  return oddNodes;
}

// === A method which returns the approximate area of a non-intersecting polygon in square metres ===
// === It doesn't fully account for spechical geometry, so will be inaccurate for large polygons ===
// === The polygon must not intersect itself ===
GPolygon.prototype.Area = function() {
  var a = 0;
  var j = 0;
  var b = this.Bounds();
  var x0 = b.getSouthWest().lng();
  var y0 = b.getSouthWest().lat();
  for (var i=0; i < this.getVertexCount(); i++) {
    j++;
    if (j == this.getVertexCount()) {j = 0;}
    var x1 = this.getVertex(i).distanceFrom(new GLatLng(this.getVertex(i).lat(),x0));
    var x2 = this.getVertex(j).distanceFrom(new GLatLng(this.getVertex(j).lat(),x0));
    var y1 = this.getVertex(i).distanceFrom(new GLatLng(y0,this.getVertex(i).lng()));
    var y2 = this.getVertex(j).distanceFrom(new GLatLng(y0,this.getVertex(j).lng()));
    a += x1*y2 - x2*y1;
  }
  return Math.abs(a * 0.5);
}

// === A method which returns the length of a path in metres ===
GPolygon.prototype.Distance = function() {
  var dist = 0;
  for (var i=1; i < this.getVertexCount(); i++) {
    dist += this.getVertex(i).distanceFrom(this.getVertex(i-1));
  }
  return dist;
}

// === A method which returns the bounds as a GLatLngBounds ===
GPolygon.prototype.Bounds = function() {
  var bounds = new GLatLngBounds();
  for (var i=0; i < this.getVertexCount(); i++) {
    bounds.extend(this.getVertex(i));
  }
  return bounds;
}

// === A method which returns a GLatLng of a point a given distance along the path ===
// === Returns null if the path is shorter than the specified distance ===
GPolygon.prototype.GetPointAtDistance = function(metres) {
  // some awkward special cases
  if (metres == 0) return this.getVertex(0);
  if (metres < 0) return null;
  var dist=0;
  var olddist=0;
  for (var i=1; (i < this.getVertexCount() && dist < metres); i++) {
    olddist = dist;
    dist += this.getVertex(i).distanceFrom(this.getVertex(i-1));
  }
  if (dist < metres) {return null;}
  var p1= this.getVertex(i-2);
  var p2= this.getVertex(i-1);
  var m = (metres-olddist)/(dist-olddist);
  return new GLatLng( p1.lat() + (p2.lat()-p1.lat())*m, p1.lng() + (p2.lng()-p1.lng())*m);
}

// === A method which returns an array of GLatLngs of points a given interval along the path ===
GPolygon.prototype.GetPointsAtDistance = function(metres) {
  var next = metres;
  var points = [];
  // some awkward special cases
  if (metres <= 0) return points;
  var dist=0;
  var olddist=0;
  for (var i=1; (i < this.getVertexCount()); i++) {
    olddist = dist;
    dist += this.getVertex(i).distanceFrom(this.getVertex(i-1));
    while (dist > next) {
      var p1= this.getVertex(i-1);
      var p2= this.getVertex(i);
      var m = (next-olddist)/(dist-olddist);
      points.push(new GLatLng( p1.lat() + (p2.lat()-p1.lat())*m, p1.lng() + (p2.lng()-p1.lng())*m));
      next += metres;    
    }
  }
  return points;
}

// === A method which returns the Vertex number at a given distance along the path ===
// === Returns null if the path is shorter than the specified distance ===
GPolygon.prototype.GetIndexAtDistance = function(metres) {
  // some awkward special cases
  if (metres == 0) return this.getVertex(0);
  if (metres < 0) return null;
  var dist=0;
  var olddist=0;
  for (var i=1; (i < this.getVertexCount() && dist < metres); i++) {
    olddist = dist;
    dist += this.getVertex(i).distanceFrom(this.getVertex(i-1));
  }
  if (dist < metres) {return null;}
  return i;
}

// === A function which returns the bearing between two vertices in decgrees from 0 to 360===
// === If v1 is null, it returns the bearing between the first and last vertex ===
// === If v1 is present but v2 is null, returns the bearing from v1 to the next vertex ===
// === If either vertex is out of range, returns void ===
GPolygon.prototype.Bearing = function(v1,v2) {
  if (v1 == null) {
    v1 = 0;
    v2 = this.getVertexCount()-1;
  } else if (v2 ==  null) {
    v2 = v1+1;
  }
  if ((v1 < 0) || (v1 >= this.getVertexCount()) || (v2 < 0) || (v2 >= this.getVertexCount())) {
    return;
  }
  var from = this.getVertex(v1);
  var to = this.getVertex(v2);
  if (from.equals(to)) {
    return 0;
  }
  var lat1 = from.latRadians();
  var lon1 = from.lngRadians();
  var lat2 = to.latRadians();
  var lon2 = to.lngRadians();
  var angle = - Math.atan2( Math.sin( lon1 - lon2 ) * Math.cos( lat2 ), Math.cos( lat1 ) * Math.sin( lat2 ) - Math.sin( lat1 ) * Math.cos( lat2 ) * Math.cos( lon1 - lon2 ) );
  if ( angle < 0.0 ) angle  += Math.PI * 2.0;
  angle = angle * 180.0 / Math.PI;
  return parseFloat(angle.toFixed(1));
}




// === Copy all the above functions to GPolyline ===
GPolyline.prototype.Contains             = GPolygon.prototype.Contains;
GPolyline.prototype.Area                 = GPolygon.prototype.Area;
GPolyline.prototype.Distance             = GPolygon.prototype.Distance;
GPolyline.prototype.Bounds               = GPolygon.prototype.Bounds;
GPolyline.prototype.GetPointAtDistance   = GPolygon.prototype.GetPointAtDistance;
GPolyline.prototype.GetPointsAtDistance  = GPolygon.prototype.GetPointsAtDistance;
GPolyline.prototype.GetIndexAtDistance   = GPolygon.prototype.GetIndexAtDistance;
GPolyline.prototype.Bearing              = GPolygon.prototype.Bearing;
//--------------------------------from http://www.codeproject.com/KB/ajax/XMLWriter.aspx--------------------------------------//
function JSettings()
{
	this.IE=document.all?true:false;
	this.MouseX=_JSettings_MouseX;
	this.MouseY=_JSettings_MouseY;
	this.SrcElement=_JSettings_SrcElement;
	this.Parent=_JSettings_Parent;
	this.RunOnLoad=_JSettings_RunOnLoad;
	this.FindParent=_JSettings_FindParent;
	this.FindChild=_JSettings_FindChild;
	this.FindSibling=_JSettings_FindSibling;
	this.FindParentTag=_JSettings_FindParentTag;
}
function _JSettings_MouseX(e)
{return this.IE?event.clientX:e.clientX;}
function _JSettings_MouseY(e)
{return this.IE?event.clientY:e.clientY;}
function _JSettings_SrcElement(e)
{return this.IE?event.srcElement:e.target;}
function _JSettings_Parent(Node)
{return this.IE?Node.parentNode:Node.parentElement;}
function _JSettings_RunOnLoad(Meth){var Prev=(window.onload)?window.onload:function(){};window.onload=function(){Prev();Meth();};}
function _JSettings_FindParent(Node, Attrib, Value)
{var Root = document.getElementsByTagName("BODY")[0];
Node = Node.parentNode;	while (Node != Root && Node.getAttribute(Attrib) != Value){Node=Node.parentNode;}
if (Node.getAttribute(Attrib) == Value)	{return Node;} else	{return null;}}
function _JSettings_FindParentTag(Node, TagName)
{var Root = document.getElementsByTagName("BODY")[0];
TagName=TagName.toLowerCase();
Node = Node.parentNode;	while (Node != Root && Node.tagName.toLowerCase() != TagName){Node=Node.parentNode;}
if (Node.tagName.toLowerCase() == TagName) {return Node;} else {return null;}}
function _JSettings_FindChild(Node, Attrib, Value)
{
	if (Node.getAttribute)
		if (Node.getAttribute(Attrib) == Value) return Node;

	var I=0;
	var Ret = null;
	for (I=0;I<Node.childNodes.length;I++)
	{
		Ret = FindChildByAttrib(Node.childNodes[I]);
		if (Ret) return Ret;
	}
	return null;
}
function _JSettings_FindSibling(Node, Attrib, Value)
{
	var Nodes=Node.parentNode.childNodes;
	var I=0;
	for (I=0;I<Nodes.length;I++)
	{
		if (Nodes[I].getAttribute)
		{
			if (Nodes[I].getAttribute(Attrib) == Value)
			{return Nodes[I];}
		}
	}
	return null;
}

var Settings = new JSettings();

function XMLWriter()
{
    this.XML=[];
    this.Nodes=[];
    this.State="";
    this.FormatXML = function(Str)
    {
        if (Str)
            return Str.replace(/&/g, "&amp;").replace(/\"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        return ""
    }
    this.BeginNode = function(Name)
    {
        if (!Name) return;
        if (this.State=="beg") this.XML.push(">");
        this.State="beg";
        this.Nodes.push(Name);
        this.XML.push("<"+Name);
    }
    this.EndNode = function()
    {
        if (this.State=="beg")
        {
            this.XML.push("/>");
            this.Nodes.pop();
        }
        else if (this.Nodes.length>0)
            this.XML.push("</"+this.Nodes.pop()+">");
        this.State="";
    }
    this.Attrib = function(Name, Value)
    {
        if (this.State!="beg" || !Name) return;
        this.XML.push(" "+Name+"=\""+this.FormatXML(Value)+"\"");
    }
    this.WriteString = function(Value)
    {
        if (this.State=="beg") this.XML.push(">");
        this.XML.push(this.FormatXML(Value));
        this.State="";
    }
    this.Node = function(Name, Value)
    {
        if (!Name) return;
        if (this.State=="beg") this.XML.push(">");
        this.XML.push((Value=="" || !Value)?"<"+Name+"/>":"<"+Name+">"+this.FormatXML(Value)+"</"+Name+">");
        this.State="";
    }
    this.Close = function()
    {
        while (this.Nodes.length>0)
            this.EndNode();
        this.State="closed";
    }
    this.ToString = function(){return this.XML.join("");}
}
/* ExtInfoWindow, v1.1: See code.google.com/p/gmaps-utility-library for license and info */
function ExtInfoWindow(a,b,c,d){this.html_=c;this.marker_=a;this.infoWindowId_=b;this.options_=d==null?{}:d;this.ajaxUrl_=this.options_.ajaxUrl==null?null:this.options_.ajaxUrl;this.callback_=this.options_.ajaxCallback==null?null:this.options_.ajaxCallback;this.borderSize_=this.options_.beakOffset==null?0:this.options_.beakOffset;this.paddingX_=this.options_.paddingX==null?0+this.borderSize_:this.options_.paddingX+this.borderSize_;this.paddingY_=this.options_.paddingY==null?0+this.borderSize_:this.options_.paddingY+this.borderSize_;this.map_=null;this.container_=document.createElement('div');this.container_.style.position='relative';this.container_.style.display='none';this.contentDiv_=document.createElement('div');this.contentDiv_.id=this.infoWindowId_+'_contents';this.contentDiv_.innerHTML=this.html_;this.contentDiv_.style.display='block';this.contentDiv_.style.visibility='hidden';this.wrapperDiv_=document.createElement('div')};
ExtInfoWindow.prototype=new GOverlay();
ExtInfoWindow.prototype.initialize=function(a){this.map_=a;this.defaultStyles={containerWidth:this.map_.getSize().width/2,borderSize:1};this.wrapperParts={tl:{t:0,l:0,w:0,h:0,domElement:null},t:{t:0,l:0,w:0,h:0,domElement:null},tr:{t:0,l:0,w:0,h:0,domElement:null},l:{t:0,l:0,w:0,h:0,domElement:null},r:{t:0,l:0,w:0,h:0,domElement:null},bl:{t:0,l:0,w:0,h:0,domElement:null},b:{t:0,l:0,w:0,h:0,domElement:null},br:{t:0,l:0,w:0,h:0,domElement:null},beak:{t:0,l:0,w:0,h:0,domElement:null},close:{t:0,l:0,w:0,h:0,domElement:null}};for(var i in this.wrapperParts){var b=document.createElement('div');b.id=this.infoWindowId_+'_'+i;b.style.visibility='hidden';document.body.appendChild(b);b=document.getElementById(this.infoWindowId_+'_'+i);var c=eval('this.wrapperParts.'+i);c.w=parseInt(this.getStyle_(b,'width'));c.h=parseInt(this.getStyle_(b,'height'));document.body.removeChild(b)}for(var i in this.wrapperParts){if(i=='close'){this.wrapperDiv_.appendChild(this.contentDiv_)}var d=null;if(this.wrapperParts[i].domElement==null){d=document.createElement('div');this.wrapperDiv_.appendChild(d)}else{d=this.wrapperParts[i].domElement}d.id=this.infoWindowId_+'_'+i;d.style.position='absolute';d.style.width=this.wrapperParts[i].w+'px';d.style.height=this.wrapperParts[i].h+'px';d.style.top=this.wrapperParts[i].t+'px';d.style.left=this.wrapperParts[i].l+'px';this.wrapperParts[i].domElement=d}this.map_.getPane(G_MAP_FLOAT_PANE).appendChild(this.container_);this.container_.id=this.infoWindowId_;var e=this.getStyle_(document.getElementById(this.infoWindowId_),'width');this.container_.style.width=(e==null?this.defaultStyles.containerWidth:e);this.map_.getContainer().appendChild(this.contentDiv_);this.contentWidth=this.getDimensions_(this.container_).width;this.contentDiv_.style.width=this.contentWidth+'px';this.contentDiv_.style.position='absolute';this.container_.appendChild(this.wrapperDiv_);GEvent.bindDom(this.container_,'mousedown',this,this.onClick_);GEvent.bindDom(this.container_,'dblclick',this,this.onClick_);GEvent.bindDom(this.container_,'DOMMouseScroll',this,this.onClick_);GEvent.trigger(this.map_,'extinfowindowopen');if(this.ajaxUrl_!=null){this.ajaxRequest_(this.ajaxUrl_)}};
ExtInfoWindow.prototype.onClick_=function(e){if(navigator.userAgent.toLowerCase().indexOf('msie')!=-1&&document.all){window.event.cancelBubble=true;window.event.returnValue=false}else{e.stopPropagation()}};
ExtInfoWindow.prototype.remove=function(){if(this.map_.getExtInfoWindow()!=null){GEvent.trigger(this.map_,'extinfowindowbeforeclose');GEvent.clearInstanceListeners(this.container_);if(this.container_.outerHTML){this.container_.outerHTML=''}if(this.container_.parentNode){this.container_.parentNode.removeChild(this.container_)}this.container_=null;GEvent.trigger(this.map_,'extinfowindowclose');this.map_.setExtInfoWindow_(null)}};
ExtInfoWindow.prototype.copy=function(){return new ExtInfoWindow(this.marker_,this.infoWindowId_,this.html_,this.options_)};
ExtInfoWindow.prototype.redraw=function(a){if(!a||this.container_==null)return;var b=this.contentDiv_.offsetHeight;this.contentDiv_.style.height=b+'px';this.contentDiv_.style.left=this.wrapperParts.l.w+'px';this.contentDiv_.style.top=this.wrapperParts.tl.h+'px';this.contentDiv_.style.visibility='visible';this.wrapperParts.tl.t=0;this.wrapperParts.tl.l=0;this.wrapperParts.t.l=this.wrapperParts.tl.w;this.wrapperParts.t.w=(this.wrapperParts.l.w+this.contentWidth+this.wrapperParts.r.w)-this.wrapperParts.tl.w-this.wrapperParts.tr.w;this.wrapperParts.t.h=this.wrapperParts.tl.h;this.wrapperParts.tr.l=this.wrapperParts.t.w+this.wrapperParts.tl.w;this.wrapperParts.l.t=this.wrapperParts.tl.h;this.wrapperParts.l.h=b;this.wrapperParts.r.l=this.contentWidth+this.wrapperParts.l.w;this.wrapperParts.r.t=this.wrapperParts.tr.h;this.wrapperParts.r.h=b;this.wrapperParts.bl.t=b+this.wrapperParts.tl.h;this.wrapperParts.b.l=this.wrapperParts.bl.w;this.wrapperParts.b.t=b+this.wrapperParts.tl.h;this.wrapperParts.b.w=(this.wrapperParts.l.w+this.contentWidth+this.wrapperParts.r.w)-this.wrapperParts.bl.w-this.wrapperParts.br.w;this.wrapperParts.b.h=this.wrapperParts.bl.h;this.wrapperParts.br.l=this.wrapperParts.b.w+this.wrapperParts.bl.w;this.wrapperParts.br.t=b+this.wrapperParts.tr.h;this.wrapperParts.close.l=this.wrapperParts.tr.l+this.wrapperParts.tr.w-this.wrapperParts.close.w-this.borderSize_;this.wrapperParts.close.t=this.borderSize_;this.wrapperParts.beak.l=this.borderSize_+(this.contentWidth/2)-(this.wrapperParts.beak.w/2);this.wrapperParts.beak.t=this.wrapperParts.bl.t+this.wrapperParts.bl.h-this.borderSize_;for(var i in this.wrapperParts){if(i=='close'){this.wrapperDiv_.insertBefore(this.contentDiv_,this.wrapperParts[i].domElement)}var c=null;if(this.wrapperParts[i].domElement==null){c=document.createElement('div');this.wrapperDiv_.appendChild(c)}else{c=this.wrapperParts[i].domElement}c.id=this.infoWindowId_+'_'+i;c.style.position='absolute';c.style.width=this.wrapperParts[i].w+'px';c.style.height=this.wrapperParts[i].h+'px';c.style.top=this.wrapperParts[i].t+'px';c.style.left=this.wrapperParts[i].l+'px';this.wrapperParts[i].domElement=c}var d=this.marker_;var e=this.map_;GEvent.addDomListener(this.wrapperParts.close.domElement,'click',function(){e.closeExtInfoWindow()});var f=this.map_.fromLatLngToDivPixel(this.marker_.getPoint());this.container_.style.position='absolute';var g=this.marker_.getIcon();this.container_.style.left=(f.x-(this.contentWidth/2)-g.iconAnchor.x+g.infoWindowAnchor.x)+'px';this.container_.style.top=(f.y-this.wrapperParts.bl.h-b-this.wrapperParts.tl.h-this.wrapperParts.beak.h-g.iconAnchor.y+g.infoWindowAnchor.y+this.borderSize_)+'px';this.container_.style.display='block';if(this.map_.getExtInfoWindow()!=null){this.repositionMap_()}};
ExtInfoWindow.prototype.resize=function(){var a=this.contentDiv_.cloneNode(true);a.id=this.infoWindowId_+'_tempContents';a.style.visibility='hidden';a.style.height='auto';document.body.appendChild(a);a=document.getElementById(this.infoWindowId_+'_tempContents');var b=a.offsetHeight;document.body.removeChild(a);this.contentDiv_.style.height=b+'px';var c=this.contentDiv_.offsetWidth;var d=this.map_.fromLatLngToDivPixel(this.marker_.getPoint());var e=this.wrapperParts.t.domElement.offsetHeight+this.wrapperParts.l.domElement.offsetHeight+this.wrapperParts.b.domElement.offsetHeight;var f=this.wrapperParts.t.domElement.offsetTop;this.wrapperParts.l.domElement.style.height=b+'px';this.wrapperParts.r.domElement.style.height=b+'px';var g=this.wrapperParts.b.domElement.offsetTop-b;this.wrapperParts.l.domElement.style.top=g+'px';this.wrapperParts.r.domElement.style.top=g+'px';this.contentDiv_.style.top=g+'px';windowTHeight=parseInt(this.wrapperParts.t.domElement.style.height);g-=windowTHeight;this.wrapperParts.close.domElement.style.top=g+this.borderSize_+'px';this.wrapperParts.tl.domElement.style.top=g+'px';this.wrapperParts.t.domElement.style.top=g+'px';this.wrapperParts.tr.domElement.style.top=g+'px';this.repositionMap_()};
ExtInfoWindow.prototype.repositionMap_=function(){var a=this.map_.fromLatLngToDivPixel(this.map_.getBounds().getNorthEast());var b=this.map_.fromLatLngToDivPixel(this.map_.getBounds().getSouthWest());var c=this.map_.fromLatLngToDivPixel(this.marker_.getPoint());var d=0;var e=0;var f=this.paddingX_;var g=this.paddingY_;var h=this.marker_.getIcon().infoWindowAnchor;var i=this.marker_.getIcon().iconAnchor;var j=this.wrapperParts.t.domElement;var k=this.wrapperParts.l.domElement;var l=this.wrapperParts.b.domElement;var m=this.wrapperParts.r.domElement;var n=this.wrapperParts.beak.domElement;var o=c.y-(-h.y+i.y+this.getDimensions_(n).height+this.getDimensions_(l).height+this.getDimensions_(k).height+this.getDimensions_(j).height+this.paddingY_);if(o<a.y){e=a.y-o}else{var p=c.y+this.paddingY_;if(p>=b.y){e=-(p-b.y)}}var q=Math.round(c.x+this.getDimensions_(this.container_).width/2+this.getDimensions_(m).width+this.paddingX_+h.x-i.x);if(q>a.x){d=-(q-a.x)}else{var r=-(Math.round((this.getDimensions_(this.container_).width/2-this.marker_.getIcon().iconSize.width/2)+this.getDimensions_(k).width+this.borderSize_+this.paddingX_)-c.x-h.x+i.x);if(r<b.x){d=b.x-r}}if(d!=0||e!=0&&this.map_.getExtInfoWindow()!=null){this.map_.panBy(new GSize(d,e))}};
ExtInfoWindow.prototype.ajaxRequest_=function(d){var e=this.map_;var f=this.callback_;GDownloadUrl(d,function(a,b){var c=document.getElementById(e.getExtInfoWindow().infoWindowId_+'_contents');if(a==null||b==-1){c.innerHTML='<span class="error">ERROR: The Ajax request failed to get HTML content from "'+d+'"</span>'}else{c.innerHTML=a}if(f!=null){f()}e.getExtInfoWindow().resize();GEvent.trigger(e,'extinfowindowupdate')})};
ExtInfoWindow.prototype.getDimensions_=function(a){var b=this.getStyle_(a,'display');if(b!='none'&&b!=null){return{width:a.offsetWidth,height:a.offsetHeight}}var c=a.style;var d=c.visibility;var e=c.position;var f=c.display;c.visibility='hidden';c.position='absolute';c.display='block';var g=a.clientWidth;var h=a.clientHeight;c.display=f;c.position=e;c.visibility=d;return{width:g,height:h}};
ExtInfoWindow.prototype.getStyle_=function(a,b){var c=false;b=this.camelize_(b);var d=a.style[b];if(!d){if(document.defaultView&&document.defaultView.getComputedStyle){var e=document.defaultView.getComputedStyle(a,null);d=e?e[b]:null}else if(a.currentStyle){d=a.currentStyle[b]}}if((d=='auto')&&(b=='width'||b=='height')&&(this.getStyle_(a,'display')!='none')){if(b=='width'){d=a.offsetWidth}else{d=a.offsetHeight}}return(d=='auto')?null:d};
ExtInfoWindow.prototype.camelize_=function(a){var b=a.split('-'),len=b.length;if(len==1)return b[0];var c=a.charAt(0)=='-'?b[0].charAt(0).toUpperCase()+b[0].substring(1):b[0];for(var i=1;i<len;i++){c+=b[i].charAt(0).toUpperCase()+b[i].substring(1)}return c};GMap.prototype.ExtInfoWindowInstance_=null;GMap.prototype.ClickListener_=null;GMap.prototype.InfoWindowListener_=null;GMarker.prototype.openExtInfoWindow=function(b,c,d,e){if(b==null){throw'Error in GMarker.openExtInfoWindow: map cannot be null';return false}if(c==null||c==''){throw'Error in GMarker.openExtInfoWindow: must specify a cssId';return false}b.closeInfoWindow();if(b.getExtInfoWindow()!=null){b.closeExtInfoWindow()}if(b.getExtInfoWindow()==null){b.setExtInfoWindow_(new ExtInfoWindow(this,c,d,e));if(b.ClickListener_==null){b.ClickListener_=GEvent.addListener(b,'click',function(a){if(!a&&b.getExtInfoWindow()!=null){b.closeExtInfoWindow()}})}if(b.InfoWindowListener_==null){b.InfoWindowListener_=GEvent.addListener(b,'infowindowopen',function(a){if(b.getExtInfoWindow()!=null){b.closeExtInfoWindow()}})}b.addOverlay(b.getExtInfoWindow())}};GMarker.prototype.closeExtInfoWindow=function(a){if(a.getExtInfWindow()!=null){a.closeExtInfoWindow()}};
GMap2.prototype.getExtInfoWindow=function(){return this.ExtInfoWindowInstance_};
GMap2.prototype.setExtInfoWindow_=function(a){this.ExtInfoWindowInstance_=a}
GMap2.prototype.closeExtInfoWindow=function(){if(this.getExtInfoWindow()!=null){this.ExtInfoWindowInstance_.remove()}};
