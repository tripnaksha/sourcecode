window.addEvent('domready', function()
{
 if (GBrowserIsCompatible())
 {
  var map = new GMap2($('map_canvas'));
  map.setCenter(new GLatLng(38.89, -77.04), 12);
  window.onunload=function()
  {
   GUnload();
  };
 }
});
