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

function MarkerManager(map,opt_opts){var me=this;me.map_=map;me.mapZoom_=map.getZoom();me.projection_=map.getCurrentMapType().getProjection();opt_opts=opt_opts||{};me.tileSize_=MarkerManager.DEFAULT_TILE_SIZE_;var maxZoom=MarkerManager.DEFAULT_MAX_ZOOM_;if(opt_opts.maxZoom!=undefined){maxZoom=opt_opts.maxZoom}me.maxZoom_=maxZoom;me.trackMarkers_=opt_opts.trackMarkers;var padding;if(typeof opt_opts.borderPadding=="number"){padding=opt_opts.borderPadding}else{padding=MarkerManager.DEFAULT_BORDER_PADDING_}me.swPadding_=new GSize(-padding,padding);me.nePadding_=new GSize(padding,-padding);me.borderPadding_=padding;me.gridWidth_=[];me.grid_=[];me.grid_[maxZoom]=[];me.numMarkers_=[];me.numMarkers_[maxZoom]=0;GEvent.bind(map,"moveend",me,me.onMapMoveEnd_);me.removeOverlay_=function(marker){map.removeOverlay(marker);me.shownMarkers_--};me.addOverlay_=function(marker){map.addOverlay(marker);me.shownMarkers_++};me.resetManager_();me.shownMarkers_=0;me.shownBounds_=me.getMapGridBounds_()};MarkerManager.DEFAULT_TILE_SIZE_=1024;MarkerManager.DEFAULT_MAX_ZOOM_=17;MarkerManager.DEFAULT_BORDER_PADDING_=100;MarkerManager.MERCATOR_ZOOM_LEVEL_ZERO_RANGE=256;MarkerManager.prototype.resetManager_=function(){var me=this;var mapWidth=MarkerManager.MERCATOR_ZOOM_LEVEL_ZERO_RANGE;for(var zoom=0;zoom<=me.maxZoom_;++zoom){me.grid_[zoom]=[];me.numMarkers_[zoom]=0;me.gridWidth_[zoom]=Math.ceil(mapWidth/me.tileSize_);mapWidth<<=1}};MarkerManager.prototype.clearMarkers=function(){var me=this;me.processAll_(me.shownBounds_,me.removeOverlay_);me.resetManager_()};MarkerManager.prototype.getTilePoint_=function(latlng,zoom,padding){var pixelPoint=this.projection_.fromLatLngToPixel(latlng,zoom);return new GPoint(Math.floor((pixelPoint.x+padding.width)/this.tileSize_),Math.floor((pixelPoint.y+padding.height)/this.tileSize_))};MarkerManager.prototype.addMarkerBatch_=function(marker,minZoom,maxZoom){var mPoint=marker.getPoint();if(this.trackMarkers_){GEvent.bind(marker,"changed",this,this.onMarkerMoved_)}var gridPoint=this.getTilePoint_(mPoint,maxZoom,GSize.ZERO);for(var zoom=maxZoom;zoom>=minZoom;zoom--){var cell=this.getGridCellCreate_(gridPoint.x,gridPoint.y,zoom);cell.push(marker);gridPoint.x=gridPoint.x>>1;gridPoint.y=gridPoint.y>>1}};MarkerManager.prototype.isGridPointVisible_=function(point){var me=this;var vertical=me.shownBounds_.minY<=point.y&&point.y<=me.shownBounds_.maxY;var minX=me.shownBounds_.minX;var horizontal=minX<=point.x&&point.x<=me.shownBounds_.maxX;if(!horizontal&&minX<0){var width=me.gridWidth_[me.shownBounds_.z];horizontal=minX+width<=point.x&&point.x<=width-1}return vertical&&horizontal};MarkerManager.prototype.onMarkerMoved_=function(marker,oldPoint,newPoint){var me=this;var zoom=me.maxZoom_;var changed=false;var oldGrid=me.getTilePoint_(oldPoint,zoom,GSize.ZERO);var newGrid=me.getTilePoint_(newPoint,zoom,GSize.ZERO);while(zoom>=0&&(oldGrid.x!=newGrid.x||oldGrid.y!=newGrid.y)){var cell=me.getGridCellNoCreate_(oldGrid.x,oldGrid.y,zoom);if(cell){if(me.removeFromArray(cell,marker)){me.getGridCellCreate_(newGrid.x,newGrid.y,zoom).push(marker)}}if(zoom==me.mapZoom_){if(me.isGridPointVisible_(oldGrid)){if(!me.isGridPointVisible_(newGrid)){me.removeOverlay_(marker);changed=true}}else{if(me.isGridPointVisible_(newGrid)){me.addOverlay_(marker);changed=true}}}oldGrid.x=oldGrid.x>>1;oldGrid.y=oldGrid.y>>1;newGrid.x=newGrid.x>>1;newGrid.y=newGrid.y>>1;--zoom}if(changed){me.notifyListeners_()}};MarkerManager.prototype.removeMarker=function(marker){var me=this;var zoom=me.maxZoom_;var changed=false;var point=marker.getPoint();var grid=me.getTilePoint_(point,zoom,GSize.ZERO);while(zoom>=0){var cell=me.getGridCellNoCreate_(grid.x,grid.y,zoom);if(cell){me.removeFromArray(cell,marker)}if(zoom==me.mapZoom_){if(me.isGridPointVisible_(grid)){me.removeOverlay_(marker);changed=true}}grid.x=grid.x>>1;grid.y=grid.y>>1;--zoom}if(changed){me.notifyListeners_()}};MarkerManager.prototype.addMarkers=function(markers,minZoom,opt_maxZoom){var maxZoom=this.getOptMaxZoom_(opt_maxZoom);for(var i=markers.length-1;i>=0;i--){this.addMarkerBatch_(markers[i],minZoom,maxZoom)}this.numMarkers_[minZoom]+=markers.length};MarkerManager.prototype.getOptMaxZoom_=function(opt_maxZoom){return opt_maxZoom!=undefined?opt_maxZoom:this.maxZoom_};MarkerManager.prototype.getMarkerCount=function(zoom){var total=0;for(var z=0;z<=zoom;z++){total+=this.numMarkers_[z]}return total};MarkerManager.prototype.addMarker=function(marker,minZoom,opt_maxZoom){var me=this;var maxZoom=this.getOptMaxZoom_(opt_maxZoom);me.addMarkerBatch_(marker,minZoom,maxZoom);var gridPoint=me.getTilePoint_(marker.getPoint(),me.mapZoom_,GSize.ZERO);if(me.isGridPointVisible_(gridPoint)&&minZoom<=me.shownBounds_.z&&me.shownBounds_.z<=maxZoom){me.addOverlay_(marker);me.notifyListeners_()}this.numMarkers_[minZoom]++};GBounds.prototype.containsPoint=function(point){var outer=this;return(outer.minX<=point.x&&outer.maxX>=point.x&&outer.minY<=point.y&&outer.maxY>=point.y)};MarkerManager.prototype.getGridCellCreate_=function(x,y,z){var grid=this.grid_[z];if(x<0){x+=this.gridWidth_[z]}var gridCol=grid[x];if(!gridCol){gridCol=grid[x]=[];return gridCol[y]=[]}var gridCell=gridCol[y];if(!gridCell){return gridCol[y]=[]}return gridCell};MarkerManager.prototype.getGridCellNoCreate_=function(x,y,z){var grid=this.grid_[z];if(x<0){x+=this.gridWidth_[z]}var gridCol=grid[x];return gridCol?gridCol[y]:undefined};MarkerManager.prototype.getGridBounds_=function(bounds,zoom,swPadding,nePadding){zoom=Math.min(zoom,this.maxZoom_);var bl=bounds.getSouthWest();var tr=bounds.getNorthEast();var sw=this.getTilePoint_(bl,zoom,swPadding);var ne=this.getTilePoint_(tr,zoom,nePadding);var gw=this.gridWidth_[zoom];if(tr.lng()<bl.lng()||ne.x<sw.x){sw.x-=gw}if(ne.x-sw.x+1>=gw){sw.x=0;ne.x=gw-1}var gridBounds=new GBounds([sw,ne]);gridBounds.z=zoom;return gridBounds};MarkerManager.prototype.getMapGridBounds_=function(){var me=this;return me.getGridBounds_(me.map_.getBounds(),me.mapZoom_,me.swPadding_,me.nePadding_)};MarkerManager.prototype.onMapMoveEnd_=function(){var me=this;me.objectSetTimeout_(this,this.updateMarkers_,0)};MarkerManager.prototype.objectSetTimeout_=function(object,command,milliseconds){return window.setTimeout(function(){command.call(object)},milliseconds)};MarkerManager.prototype.refresh=function(){var me=this;if(me.shownMarkers_>0){me.processAll_(me.shownBounds_,me.removeOverlay_)}me.processAll_(me.shownBounds_,me.addOverlay_);me.notifyListeners_()};MarkerManager.prototype.updateMarkers_=function(){var me=this;me.mapZoom_=this.map_.getZoom();var newBounds=me.getMapGridBounds_();if(newBounds.equals(me.shownBounds_)&&newBounds.z==me.shownBounds_.z){return}if(newBounds.z!=me.shownBounds_.z){me.processAll_(me.shownBounds_,me.removeOverlay_);me.processAll_(newBounds,me.addOverlay_)}else{me.rectangleDiff_(me.shownBounds_,newBounds,me.removeCellMarkers_);me.rectangleDiff_(newBounds,me.shownBounds_,me.addCellMarkers_)}me.shownBounds_=newBounds;me.notifyListeners_()};MarkerManager.prototype.notifyListeners_=function(){GEvent.trigger(this,"changed",this.shownBounds_,this.shownMarkers_)};MarkerManager.prototype.processAll_=function(bounds,callback){for(var x=bounds.minX;x<=bounds.maxX;x++){for(var y=bounds.minY;y<=bounds.maxY;y++){this.processCellMarkers_(x,y,bounds.z,callback)}}};MarkerManager.prototype.processCellMarkers_=function(x,y,z,callback){var cell=this.getGridCellNoCreate_(x,y,z);if(cell){for(var i=cell.length-1;i>=0;i--){callback(cell[i])}}};MarkerManager.prototype.removeCellMarkers_=function(x,y,z){this.processCellMarkers_(x,y,z,this.removeOverlay_)};MarkerManager.prototype.addCellMarkers_=function(x,y,z){this.processCellMarkers_(x,y,z,this.addOverlay_)};MarkerManager.prototype.rectangleDiff_=function(bounds1,bounds2,callback){var me=this;me.rectangleDiffCoords(bounds1,bounds2,function(x,y){callback.apply(me,[x,y,bounds1.z])})};MarkerManager.prototype.rectangleDiffCoords=function(bounds1,bounds2,callback){var minX1=bounds1.minX;var minY1=bounds1.minY;var maxX1=bounds1.maxX;var maxY1=bounds1.maxY;var minX2=bounds2.minX;var minY2=bounds2.minY;var maxX2=bounds2.maxX;var maxY2=bounds2.maxY;for(var x=minX1;x<=maxX1;x++){for(var y=minY1;y<=maxY1&&y<minY2;y++){callback(x,y)}for(var y=Math.max(maxY2+1,minY1);y<=maxY1;y++){callback(x,y)}}for(var y=Math.max(minY1,minY2);y<=Math.min(maxY1,maxY2);y++){for(var x=Math.min(maxX1+1,minX2)-1;x>=minX1;x--){callback(x,y)}for(var x=Math.max(minX1,maxX2+1);x<=maxX1;x++){callback(x,y)}}};MarkerManager.prototype.removeFromArray=function(array,value,opt_notype){var shift=0;for(var i=0;i<array.length;++i){if(array[i]===value||(opt_notype&&array[i]==value)){array.splice(i--,1);shift++}}return shift};
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('7 37(n,v,w){4 o=[];4 m=n;4 t=z;4 q=3;4 r=20;4 x=[36,30,2R,2E,2z];4 s=[];4 u=[];4 p=z;4 i=0;A(i=1;i<=5;++i){s.O({\'18\':"1V://35-31-2Z.2W.2Q/2K/2C/2B/2y/m"+i+".2u",\'S\':x[i-1],\'Z\':x[i-1]})}6(F w==="X"&&w!==z){6(F w.1f==="13"&&w.1f>0){r=w.1f}6(F w.1y==="13"){t=w.1y}6(F w.14==="X"&&w.14!==z&&w.14.9!==0){s=w.14}}7 1t(){6(u.9===0){8}4 a=[];A(i=0;i<u.9;++i){q.Q(u[i],G,z,z,G)}u=a}3.1s=7(){8 s};3.12=7(){A(4 i=0;i<o.9;++i){6(F o[i]!=="1Y"&&o[i]!==z){o[i].12()}}o=[];u=[];17.1W(p)};7 1p(a){8 m.1b().34(a.1o())}7 1S(a){4 c=a.9;4 b=[];A(4 i=c-1;i>=0;--i){q.Q(a[i].C,G,a[i].I,b,G)}1t()}3.Q=7(g,j,b,h,a){6(a!==G){6(!1p(g)){u.O(g);8}}4 f=b;4 d=h;4 e=m.M(g.1o());6(F f!=="2A"){f=T}6(F d!=="X"||d===z){d=o}4 k=d.9;4 c=z;A(4 i=k-1;i>=0;i--){c=d[i];4 l=c.1L();6(l===z){1I}l=m.M(l);6(e.x>=l.x-r&&e.x<=l.x+r&&e.y>=l.y-r&&e.y<=l.y+r){c.Q({\'I\':f,\'C\':g});6(!j){c.L()}8}}c=R 1J(3,n);c.Q({\'I\':f,\'C\':g});6(!j){c.L()}d.O(c);6(d!==o){o.O(c)}};3.1C=7(a){A(4 i=0;i<o.9;++i){6(o[i].1K(a)){o[i].L();8}}};3.L=7(){4 a=3.1j();A(4 i=0;i<a.9;++i){a[i].L(G)}};3.1j=7(){4 b=[];4 a=m.1b();A(4 i=0;i<o.9;i++){6(o[i].1n(a)){b.O(o[i])}}8 b};3.1N=7(){8 t};3.1M=7(){8 m};3.1e=7(){8 r};3.Y=7(){4 a=0;A(4 i=0;i<o.9;++i){a+=o[i].Y()}8 a};3.29=7(){8 o.9};3.1A=7(){4 d=3.1j();4 e=[];4 f=0;A(4 i=0;i<d.9;++i){4 c=d[i];4 b=c.1x();6(b===z){1I}4 a=m.W();6(a!==b){4 h=c.1w();A(4 j=0;j<h.9;++j){4 g={\'I\':T,\'C\':h[j].C};e.O(g)}c.12();f++;A(j=0;j<o.9;++j){6(c===o[j]){o.1v(j,1)}}}}1S(e);3.L()};3.1u=7(a){A(4 i=0;i<a.9;++i){3.Q(a[i],G)}3.L()};6(F v==="X"&&v!==z){3.1u(v)}p=17.27(m,"26",7(){q.1A()})}7 1J(h){4 o=z;4 n=[];4 m=h;4 j=h.1M();4 l=z;4 k=j.W();3.1w=7(){8 n};3.1n=7(c){6(o===z){8 T}6(!c){c=j.1b()}4 g=j.M(c.25());4 a=j.M(c.24());4 b=j.M(o);4 e=G;4 f=h.1e();6(k!==j.W()){4 d=j.W()-k;f=23.22(2,d)*f}6(a.x!==g.x&&(b.x+f<g.x||b.x-f>a.x)){e=T}6(e&&(b.y+f<a.y||b.y-f>g.y)){e=T}8 e};3.1L=7(){8 o};3.Q=7(a){6(o===z){o=a.C.1o()}n.O(a)};3.1C=7(a){A(4 i=0;i<n.9;++i){6(a===n[i].C){6(n[i].I){j.1c(n[i].C)}n.1v(i,1);8 G}}8 T};3.1x=7(){8 k};3.L=7(b){6(!b&&!3.1n()){8}k=j.W();4 i=0;4 a=h.1N();6(a===z){a=j.21().1Z()}6(k>=a||3.Y()===1){A(i=0;i<n.9;++i){6(n[i].I){6(n[i].C.11()){n[i].C.1a()}}N{j.1r(n[i].C);n[i].I=G}}6(l!==z){l.1k()}}N{A(i=0;i<n.9;++i){6(n[i].I&&(!n[i].C.11())){n[i].C.1k()}}6(l===z){l=R E(o,3.Y(),m.1s(),m.1e());j.1r(l)}N{6(l.11()){l.1a()}l.1q(G)}}};3.12=7(){6(l!==z){j.1c(l)}A(4 i=0;i<n.9;++i){6(n[i].I){j.1c(n[i].C)}}n=[]};3.Y=7(){8 n.9}}7 E(a,c,d,b){4 f=0;4 e=c;1X(e!==0){e=V(e/10,10);f++}6(d.9<f){f=d.9}3.16=d[f-1].18;3.H=d[f-1].S;3.P=d[f-1].Z;3.19=d[f-1].1U;3.D=d[f-1].32;3.15=a;3.1T=f;3.1R=d;3.1m=c;3.1l=b}E.J=R 2Y();E.J.2X=7(i){3.1P=i;4 j=1O.2V("2U");4 h=3.15;4 f=i.M(h);f.x-=V(3.P/2,10);f.y-=V(3.H/2,10);4 g="";6(1O.2T){g=\'2S:2P:2O.2M.2L(2J=2I,2H="\'+3.16+\'");\'}N{g="2G:18("+3.16+");"}6(F 3.D==="X"){6(F 3.D[0]==="13"&&3.D[0]>0&&3.D[0]<3.H){g+=\'S:\'+(3.H-3.D[0])+\'B;1H-1g:\'+3.D[0]+\'B;\'}N{g+=\'S:\'+3.H+\'B;1G-S:\'+3.H+\'B;\'}6(F 3.D[1]==="13"&&3.D[1]>0&&3.D[1]<3.P){g+=\'Z:\'+(3.P-3.D[1])+\'B;1H-1i:\'+3.D[1]+\'B;\'}N{g+=\'Z:\'+3.P+\'B;1F-1E:1D;\'}}N{g+=\'S:\'+3.H+\'B;1G-S:\'+3.H+\'B;\';g+=\'Z:\'+3.P+\'B;1F-1E:1D;\'}4 k=3.19?3.19:\'2x\';j.U.2w=g+\'2v:2t;1g:\'+f.y+"B;1i:"+f.x+"B;2D:"+k+";2s:2F;1h-2r:2q;"+\'1h-2p:2o,2n-2m;1h-2N:2l\';j.2k=3.1m;i.2j(2i).2h(j);4 e=3.1l;17.2g(j,"2f",7(){4 a=i.M(h);4 d=R 1Q(a.x-e,a.y+e);d=i.1B(d);4 b=R 1Q(a.x+e,a.y-e);b=i.1B(b);4 c=i.2e(R 2d(d,b),i.2c());i.2b(h,c)});3.K=j};E.J.1K=7(){3.K.2a.33(3.K)};E.J.28=7(){8 R E(3.15,3.1T,3.1m,3.1R,3.1l)};E.J.1q=7(a){6(!a){8}4 b=3.1P.M(3.15);b.x-=V(3.P/2,10);b.y-=V(3.H/2,10);3.K.U.1g=b.y+"B";3.K.U.1i=b.x+"B"};E.J.1k=7(){3.K.U.1d="1z"};E.J.1a=7(){3.K.U.1d=""};E.J.11=7(){8 3.K.U.1d==="1z"};',62,194,'|||this|var||if|function|return|length||||||||||||||||||||||||||null|for|px|marker|anchor_|ClusterMarker_|typeof|true|height_|isAdded|prototype|div_|redraw_|fromLatLngToDivPixel|else|push|width_|addMarker|new|height|false|style|parseInt|getZoom|object|getTotalMarkers|width||isHidden|clearMarkers|number|styles|latlng_|url_|GEvent|url|textColor_|show|getBounds|removeOverlay|display|getGridSize_|gridSize|top|font|left|getClustersInViewport_|hide|padding_|text_|isInBounds|getLatLng|isMarkerInViewport_|redraw|addOverlay|getStyles_|addLeftMarkers_|addMarkers|splice|getMarkers|getCurrentZoom|maxZoom|none|resetViewport|fromDivPixelToLatLng|removeMarker|center|align|text|line|padding|continue|Cluster|remove|getCenter|getMap_|getMaxZoom_|document|map_|GPoint|styles_|reAddMarkers_|index_|opt_textColor|http|removeListener|while|undefined|getMaximumResolution|60|getCurrentMapType|pow|Math|getNorthEast|getSouthWest|moveend|addListener|copy|getTotalClusters|parentNode|setCenter|getSize|GLatLngBounds|getBoundsZoomLevel|click|addDomListener|appendChild|G_MAP_MAP_PANE|getPane|innerHTML|bold|serif|sans|Arial|family|11px|size|position|pointer|png|cursor|cssText|black|images|90|boolean|markerclusterer|trunk|color|78|absolute|background|src|scale|sizingMethod|svn|AlphaImageLoader|Microsoft|weight|DXImageTransform|progid|com|66|filter|all|div|createElement|googlecode|initialize|GOverlay|library|56|utility|opt_anchor|removeChild|containsLatLng|gmaps|53|MarkerClusterer'.split('|'),0,{}))
