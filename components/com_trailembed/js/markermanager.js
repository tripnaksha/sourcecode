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


// marker manager
function MarkerManager(map,opt_opts){var me=this;me.map_=map;me.mapZoom_=map.getZoom();me.projection_=map.getCurrentMapType().getProjection();opt_opts=opt_opts||{};me.tileSize_=MarkerManager.DEFAULT_TILE_SIZE_;var maxZoom=MarkerManager.DEFAULT_MAX_ZOOM_;if(opt_opts.maxZoom!=undefined){maxZoom=opt_opts.maxZoom}me.maxZoom_=maxZoom;me.trackMarkers_=opt_opts.trackMarkers;var padding;if(typeof opt_opts.borderPadding=="number"){padding=opt_opts.borderPadding}else{padding=MarkerManager.DEFAULT_BORDER_PADDING_}me.swPadding_=new GSize(-padding,padding);me.nePadding_=new GSize(padding,-padding);me.borderPadding_=padding;me.gridWidth_=[];me.grid_=[];me.grid_[maxZoom]=[];me.numMarkers_=[];me.numMarkers_[maxZoom]=0;GEvent.bind(map,"moveend",me,me.onMapMoveEnd_);me.removeOverlay_=function(marker){map.removeOverlay(marker);me.shownMarkers_--};me.addOverlay_=function(marker){map.addOverlay(marker);me.shownMarkers_++};me.resetManager_();me.shownMarkers_=0;me.shownBounds_=me.getMapGridBounds_()};MarkerManager.DEFAULT_TILE_SIZE_=1024;MarkerManager.DEFAULT_MAX_ZOOM_=17;MarkerManager.DEFAULT_BORDER_PADDING_=100;MarkerManager.MERCATOR_ZOOM_LEVEL_ZERO_RANGE=256;MarkerManager.prototype.resetManager_=function(){var me=this;var mapWidth=MarkerManager.MERCATOR_ZOOM_LEVEL_ZERO_RANGE;for(var zoom=0;zoom<=me.maxZoom_;++zoom){me.grid_[zoom]=[];me.numMarkers_[zoom]=0;me.gridWidth_[zoom]=Math.ceil(mapWidth/me.tileSize_);mapWidth<<=1}};MarkerManager.prototype.clearMarkers=function(){var me=this;me.processAll_(me.shownBounds_,me.removeOverlay_);me.resetManager_()};MarkerManager.prototype.getTilePoint_=function(latlng,zoom,padding){var pixelPoint=this.projection_.fromLatLngToPixel(latlng,zoom);return new GPoint(Math.floor((pixelPoint.x+padding.width)/this.tileSize_),Math.floor((pixelPoint.y+padding.height)/this.tileSize_))};MarkerManager.prototype.addMarkerBatch_=function(marker,minZoom,maxZoom){var mPoint=marker.getPoint();if(this.trackMarkers_){GEvent.bind(marker,"changed",this,this.onMarkerMoved_)}var gridPoint=this.getTilePoint_(mPoint,maxZoom,GSize.ZERO);for(var zoom=maxZoom;zoom>=minZoom;zoom--){var cell=this.getGridCellCreate_(gridPoint.x,gridPoint.y,zoom);cell.push(marker);gridPoint.x=gridPoint.x>>1;gridPoint.y=gridPoint.y>>1}};MarkerManager.prototype.isGridPointVisible_=function(point){var me=this;var vertical=me.shownBounds_.minY<=point.y&&point.y<=me.shownBounds_.maxY;var minX=me.shownBounds_.minX;var horizontal=minX<=point.x&&point.x<=me.shownBounds_.maxX;if(!horizontal&&minX<0){var width=me.gridWidth_[me.shownBounds_.z];horizontal=minX+width<=point.x&&point.x<=width-1}return vertical&&horizontal};MarkerManager.prototype.onMarkerMoved_=function(marker,oldPoint,newPoint){var me=this;var zoom=me.maxZoom_;var changed=false;var oldGrid=me.getTilePoint_(oldPoint,zoom,GSize.ZERO);var newGrid=me.getTilePoint_(newPoint,zoom,GSize.ZERO);while(zoom>=0&&(oldGrid.x!=newGrid.x||oldGrid.y!=newGrid.y)){var cell=me.getGridCellNoCreate_(oldGrid.x,oldGrid.y,zoom);if(cell){if(me.removeFromArray(cell,marker)){me.getGridCellCreate_(newGrid.x,newGrid.y,zoom).push(marker)}}if(zoom==me.mapZoom_){if(me.isGridPointVisible_(oldGrid)){if(!me.isGridPointVisible_(newGrid)){me.removeOverlay_(marker);changed=true}}else{if(me.isGridPointVisible_(newGrid)){me.addOverlay_(marker);changed=true}}}oldGrid.x=oldGrid.x>>1;oldGrid.y=oldGrid.y>>1;newGrid.x=newGrid.x>>1;newGrid.y=newGrid.y>>1;--zoom}if(changed){me.notifyListeners_()}};MarkerManager.prototype.removeMarker=function(marker){var me=this;var zoom=me.maxZoom_;var changed=false;var point=marker.getPoint();var grid=me.getTilePoint_(point,zoom,GSize.ZERO);while(zoom>=0){var cell=me.getGridCellNoCreate_(grid.x,grid.y,zoom);if(cell){me.removeFromArray(cell,marker)}if(zoom==me.mapZoom_){if(me.isGridPointVisible_(grid)){me.removeOverlay_(marker);changed=true}}grid.x=grid.x>>1;grid.y=grid.y>>1;--zoom}if(changed){me.notifyListeners_()}};MarkerManager.prototype.addMarkers=function(markers,minZoom,opt_maxZoom){var maxZoom=this.getOptMaxZoom_(opt_maxZoom);for(var i=markers.length-1;i>=0;i--){this.addMarkerBatch_(markers[i],minZoom,maxZoom)}this.numMarkers_[minZoom]+=markers.length};MarkerManager.prototype.getOptMaxZoom_=function(opt_maxZoom){return opt_maxZoom!=undefined?opt_maxZoom:this.maxZoom_};MarkerManager.prototype.getMarkerCount=function(zoom){var total=0;for(var z=0;z<=zoom;z++){total+=this.numMarkers_[z]}return total};MarkerManager.prototype.addMarker=function(marker,minZoom,opt_maxZoom){var me=this;var maxZoom=this.getOptMaxZoom_(opt_maxZoom);me.addMarkerBatch_(marker,minZoom,maxZoom);var gridPoint=me.getTilePoint_(marker.getPoint(),me.mapZoom_,GSize.ZERO);if(me.isGridPointVisible_(gridPoint)&&minZoom<=me.shownBounds_.z&&me.shownBounds_.z<=maxZoom){me.addOverlay_(marker);me.notifyListeners_()}this.numMarkers_[minZoom]++};GBounds.prototype.containsPoint=function(point){var outer=this;return(outer.minX<=point.x&&outer.maxX>=point.x&&outer.minY<=point.y&&outer.maxY>=point.y)};MarkerManager.prototype.getGridCellCreate_=function(x,y,z){var grid=this.grid_[z];if(x<0){x+=this.gridWidth_[z]}var gridCol=grid[x];if(!gridCol){gridCol=grid[x]=[];return gridCol[y]=[]}var gridCell=gridCol[y];if(!gridCell){return gridCol[y]=[]}return gridCell};MarkerManager.prototype.getGridCellNoCreate_=function(x,y,z){var grid=this.grid_[z];if(x<0){x+=this.gridWidth_[z]}var gridCol=grid[x];return gridCol?gridCol[y]:undefined};MarkerManager.prototype.getGridBounds_=function(bounds,zoom,swPadding,nePadding){zoom=Math.min(zoom,this.maxZoom_);var bl=bounds.getSouthWest();var tr=bounds.getNorthEast();var sw=this.getTilePoint_(bl,zoom,swPadding);var ne=this.getTilePoint_(tr,zoom,nePadding);var gw=this.gridWidth_[zoom];if(tr.lng()<bl.lng()||ne.x<sw.x){sw.x-=gw}if(ne.x-sw.x+1>=gw){sw.x=0;ne.x=gw-1}var gridBounds=new GBounds([sw,ne]);gridBounds.z=zoom;return gridBounds};MarkerManager.prototype.getMapGridBounds_=function(){var me=this;return me.getGridBounds_(me.map_.getBounds(),me.mapZoom_,me.swPadding_,me.nePadding_)};MarkerManager.prototype.onMapMoveEnd_=function(){var me=this;me.objectSetTimeout_(this,this.updateMarkers_,0)};MarkerManager.prototype.objectSetTimeout_=function(object,command,milliseconds){return window.setTimeout(function(){command.call(object)},milliseconds)};MarkerManager.prototype.refresh=function(){var me=this;if(me.shownMarkers_>0){me.processAll_(me.shownBounds_,me.removeOverlay_)}me.processAll_(me.shownBounds_,me.addOverlay_);me.notifyListeners_()};MarkerManager.prototype.updateMarkers_=function(){var me=this;me.mapZoom_=this.map_.getZoom();var newBounds=me.getMapGridBounds_();if(newBounds.equals(me.shownBounds_)&&newBounds.z==me.shownBounds_.z){return}if(newBounds.z!=me.shownBounds_.z){me.processAll_(me.shownBounds_,me.removeOverlay_);me.processAll_(newBounds,me.addOverlay_)}else{me.rectangleDiff_(me.shownBounds_,newBounds,me.removeCellMarkers_);me.rectangleDiff_(newBounds,me.shownBounds_,me.addCellMarkers_)}me.shownBounds_=newBounds;me.notifyListeners_()};MarkerManager.prototype.notifyListeners_=function(){GEvent.trigger(this,"changed",this.shownBounds_,this.shownMarkers_)};MarkerManager.prototype.processAll_=function(bounds,callback){for(var x=bounds.minX;x<=bounds.maxX;x++){for(var y=bounds.minY;y<=bounds.maxY;y++){this.processCellMarkers_(x,y,bounds.z,callback)}}};MarkerManager.prototype.processCellMarkers_=function(x,y,z,callback){var cell=this.getGridCellNoCreate_(x,y,z);if(cell){for(var i=cell.length-1;i>=0;i--){callback(cell[i])}}};MarkerManager.prototype.removeCellMarkers_=function(x,y,z){this.processCellMarkers_(x,y,z,this.removeOverlay_)};MarkerManager.prototype.addCellMarkers_=function(x,y,z){this.processCellMarkers_(x,y,z,this.addOverlay_)};MarkerManager.prototype.rectangleDiff_=function(bounds1,bounds2,callback){var me=this;me.rectangleDiffCoords(bounds1,bounds2,function(x,y){callback.apply(me,[x,y,bounds1.z])})};MarkerManager.prototype.rectangleDiffCoords=function(bounds1,bounds2,callback){var minX1=bounds1.minX;var minY1=bounds1.minY;var maxX1=bounds1.maxX;var maxY1=bounds1.maxY;var minX2=bounds2.minX;var minY2=bounds2.minY;var maxX2=bounds2.maxX;var maxY2=bounds2.maxY;for(var x=minX1;x<=maxX1;x++){for(var y=minY1;y<=maxY1&&y<minY2;y++){callback(x,y)}for(var y=Math.max(maxY2+1,minY1);y<=maxY1;y++){callback(x,y)}}for(var y=Math.max(minY1,minY2);y<=Math.min(maxY1,maxY2);y++){for(var x=Math.min(maxX1+1,minX2)-1;x>=minX1;x--){callback(x,y)}for(var x=Math.max(minX1,maxX2+1);x<=maxX1;x++){callback(x,y)}}};MarkerManager.prototype.removeFromArray=function(array,value,opt_notype){var shift=0;for(var i=0;i<array.length;++i){if(array[i]===value||(opt_notype&&array[i]==value)){array.splice(i--,1);shift++}}return shift};

