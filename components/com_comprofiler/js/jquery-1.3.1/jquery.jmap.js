(function($){
	/**
	 *	The global namespace for jMaps
	 *	@type Object
	 *	@cat jMapOptions
	 *	@name $.jmap
	 */
	$.jmap = $.jmap || {};

	$.jmap.store = {};
	
	/**
	 *	The global object that contains the project details
	 *	@type Object
	 *	@cat jMapOptions
	 *	@name $.jmap.JDetails
	 */
	$.jmap.JDetails = {
		name: "jMaps Google Maps Plugin",
		description: "jMaps is a jQuery plugin that makes google maps easy",
		version: "3.0",
		releaseDate: "19/04/2008",
		author: "Tane Piper <digitalspaghetti@gmail.com>",
		blog: "http://digitalspaghetti.me.uk",
		repository: "http://hg.digitalspaghetti.me.uk/jmaps",
		googleGroup: "http://groups.google.com/group/jmaps",
		licenceType: "MIT",
		licenceURL: "http://www.opensource.org/licenses/mit-license.php"
	};
	
	/**
	 *	The global object for i18n error messages
	 *	@type Object
	 *	@cat jMapOptions
	 *	@name $.jmap.JErrors
	 */
	$.jmap.JErrors = {
		en : {
			functionDoesNotExist : "jMap Error 1: The function does not exist",
			addressNotFound: "This address cannot be found.  Please modify your search.",
			browserNotCompatible: "This browser is reported as being not compatible with Google Maps.",
			cannotLoad: "Cannot load the Google Maps API at this time.  Please check your connection."
		},
		fr : {
			addressNotFound: "Cette adresse ne peut pas être trouvée. Veuillez modifier votre recherche.",
			browserNotCompatible: "Ce navigateur est rapporté en tant qu'étant non compatible avec des cartes de Google.",
			cannotLoad: "Ne peut pas charger les cartes api de Google actuellement. Veuillez vérifier votre raccordement."
		},
		de : {
			addressNotFound: "Diese Adresse kann nicht gefunden werden. Ändern Sie bitte Ihre Suche.",
			browserNotCompatible: "Diese Datenbanksuchroutine wird als seiend nicht kompatibel mit Google Diagrammen berichtet.",
			cannotLoad: "Kann nicht die Google Diagramme API diesmal laden. Überprüfen Sie bitte Ihren Anschluß."
		},
		nl : {
			addressNotFound: "Dit adres kan worden gevonden niet. Gelieve te wijzigen uw onderzoek.",
			browserNotCompatible: "Dit browser wordt gemeld zoals zijnd niet compatibel met Kaarten Google.",
			cannotLoad: "Kan de Google Kaarten API op dit moment laden niet. Gelieve te controleren uw verbinding."
		},
		es : {
			addressNotFound: "Esta dirección no puede ser encontrada. Modifique por favor su búsqueda.",
			browserNotCompatible: "Este browser se divulga como siendo no compatible con los mapas de Google.",
			cannotLoad: "No puede cargar los mapas API de Google en este tiempo. Compruebe por favor su conexión."
		},
		sv : {
			addressNotFound: "Denna adress kunde ej hittas. Var god justera din sökning",
			browserNotCompatible: "Denna webbläsare är ej kompatibel med Google Maps",
			cannotLoad: "Kan inte ladda Google Maps API för tillfället. Var god kontrollera din anslutning."
		}
	};
	
	/**
	 *	The global object for .jmap("init") calls
	 *	@type Object
	 *	@cat jMapOptions
	 *	@name $.jmap.JDefaults
	 */	$.jmap.JDefaults = {
		// Initial type of map to display
		language: "en",
		// Options: "map", "sat", "hybrid"
		mapType: "map",
		// Initial map center
		mapCenter: [55.958858,-3.162302],
		// Initial map size
		mapDimensions: [400, 400],
		// Initial zoom level
		mapZoom: 12,
		// Initial map control size
		// Options: "large", "small", "none"
		mapControlSize: "small",
		// Initialise type of map control
		mapEnableType: false,
		// Initialise small map overview
		mapEnableOverview: false,
		// Enable map dragging when left button held down
		mapEnableDragging: true,
		// Enable map info windows
		mapEnableInfoWindows: true,
		// Enable double click zooming
		mapEnableDoubleClickZoom: false,
		// Enable zooming with scroll wheel
		mapEnableScrollZoom: false,
		// Enable smooth zoom
		mapEnableSmoothZoom: false,
		// Enable Google Bar
		mapEnableGoogleBar: false,
		// Enables scale bar
		mapEnableScaleControl: false,
		// Enable the jMap icon
		mapShowjMapIcon: true,
		//Debug Mode
		debugMode: false
	};
	
	/**
	 *	The global object for the google ads manager
	 *	@type Object
	 *	@cat jMapOptions
	 *	@name $.jmap.JAdsManagerDefaults
	 */
	$.jmap.JAdsManagerDefaults = {
		// Google Adsense publisher ID
		publisherId: ""
	};
	
	/**
	 *	The global object for adding KML or OpenRSS feeds to the map
	 *	@type Object
	 *	@cat jMapOptions
	 *	@name $.jmap.JFeedDefaults
	 */
	$.jmap.JFeedDefaults = {
		// URL of the feed to pass (required)
		feedUrl: "",
		// Position to center the map on (optional)
		mapCenter: []
	};
	
	/**
	 *	The global object that contains ground overlay details
	 *	@type Object
	 *	@cat jMapOptions
	 *	@name $.jmap.JGroundOverlayDefauts
	 */

	$.jmap.JGroundOverlayDefaults = {
		// South West Boundry
		overlaySouthWestBounds: [],
		// North East Boundry
		overlayNorthEastBounds: [],
		// Image
		overlayImage: ""
	};
	
	$.jmap.JIconDefaults = {
		iconImage: "",
		iconShadow: "",
		iconSize: null,
		iconShadowSize: null,
		iconAnchor: null,
		iconInfoWindowAnchor: null,
		iconPrintImage: "",
		iconMozPrintImage: "",
		iconPrintShadow: "",
		iconTransparent: ""
	};
	
	// Marker manager default options
	$.jmap.JMarkerManagerDefaults = {
		// Border Padding in pixels
		borderPadding: 100,
		// Max zoom level 
		maxZoom: 17,
		// Track markers
		trackMarkers: false
	};
	
	// Default options for a point to be created
	$.jmap.JMarkerDefaults = {
		// Point lat & lng
		pointLatLng: [],
		// Point HTML for infoWindow
		pointHTML: null,
		// Event to open infoWindow (click, dblclick, mouseover, etc)
		pointOpenHTMLEvent: "click",
		// Point is draggable?
		pointIsDraggable: false,
		// Point is removable?
		pointIsRemovable: false,
		// Event to remove on (click, dblclick, mouseover, etc)
		pointRemoveEvent: "dblclick",
		// These two are only required if adding to the marker manager
		pointMinZoom: 4,
		pointMaxZoom: 17,
		// Optional Icon to pass in (not yet implemented)
		pointIcon: null,
		// For maximizing infoWindows (not yet implemented)
		pointMaxContent: null,
		pointMaxTitle: null
	};
	
	// Defaults for a Polygon
	$.jmap.JPolygonDefaults = {
		// An array of GLatLng objects
		polygonPoints: [],
		// The outer stroke colour
	 	polygonStrokeColor: "#000000",
	 	// Stroke thickness
	 	polygonStrokeWeight: 5,
	 	// Stroke Opacity
	 	polygonStrokeOpacity: 1,
	 	// Fill colour
	 	polygonFillColor: "#ff0000",
	 	// Fill opacity
	 	polygonFillOpacity: 1,
	 	// Optional center map
	 	mapCenter: [],
	 	// Is polygon clickable?
	 	polygonClickable: true
	};
	
	// Default options for a Polyline
	$.jmap.JPolylineDefaults = {
		// An array of GLatLng objects
		polylinePoints: [],
		// Colour of the line
		polylineStrokeColor: "#ff0000",
		// Width of the line
		polylineStrokeWidth: 10,
		// Opacity of the line
		polylineStrokeOpacity: 1,
		// Optional center map
		mapCenter: [],
		// Is line Geodesic (i.e. bends to the curve of the earth)?
		polylineGeodesic: false,
		// Is line clickable?
		polylineClickable: true
	};
	
	$.jmap.JScreenOverlayDefaults = {
		
	};
	
	$.jmap.JSearchAddressDefaults = {
		// Address to search for
		address: null,
		// Optional Cache to store Geocode Data (not implemented yet)
		cache: {},
		// Country code for localisation (not implemented yet)
		countryCode: 'uk'
	};
	
	$.jmap.JSearchDirectionsDefault = {
		// From address
		fromAddress: "",
		// To address
		toAddress: "",
		// Optional panel to show text directions
		directionsPanel: ""
	};
	
	$.jmap.JTrafficDefaults = {
		// Can pass in "create" (default) or "destroy" which will remove the layer
		method: "create",
		// Center the map on this point (optional)
		mapCenter: []
	};
	
	$.jmap.JMoveToDefaults = {
		centerMethod: 'normal',
		mapType: null,
		mapCenter: [],
		mapZoom: null
	};
	
	$.jmap.JSavePositionDefaults = {
		recall: false
	};
	
	$.jmap.variables = {
		mapType: "Unknown",
		mapCenter: []
	};
	
	$.jmap.init = function(el, options, callback) {
	
		/* Set Up Options */
		// First we create out options object by checking passed options
		// and that no defaults have been overidden
		var options = $.extend({}, $.jmap.JDefaults, options);
		// Check for metadata plugin support
		var options = $.jmap.JOptions = $.meta ? $.extend({}, options, $(this).data()) : options;
		/* End Set Up Options */
		
		// Do checks or throw errors
		$.jmap._initChecks(el);
		
		// Initialise the GMap2 object
		el.jmap = $.jmap.GMap2 = new GMap2(el);
		
		// If the user shows the jMaps icon, show it right away
		if (options.mapShowjMapIcon) {
			$.jmap.addScreenOverlay(
				{
					imageUrl:'http://hg.digitalspaghetti.me.uk/jmaps/raw-file/3228fade0b3c/docs/images/jmaps-mapicon.png',
					screenXY:[70,10],
					overlayXY:[0,0],
					size:[42,25]
				}
			);
		}
		
		
		// Set map type based on passed option
		var mapType = $.jmap._initMapType(options.mapType);
		
		// Initialise the map with the passed settings
		el.jmap.setCenter(new GLatLng(options.mapCenter[0], options.mapCenter[1]), options.mapZoom, mapType);
			
		// Attach a controller to the map view
		// Will attach a large or small.  If any other value passed (i.e. "none") it is ignored
		switch(options.mapControlSize)
		{
			case "small":
				el.jmap.addControl(new GSmallMapControl());
			break;
			case "large":
				el.jmap.addControl(new GLargeMapControl());
			break;
		}
		// Type of map Control (Map,Sat,Hyb)
		if(options.mapEnableType)
			el.jmap.addControl(new GMapTypeControl()); // Off by default
		
		// Show the small overview map
		if(options.mapEnableOverview)
			el.jmap.addControl(new GOverviewMapControl());// Off by default
		
		// GMap2 Functions (in order of the docs for clarity)
		// Enable a mouse-dragable map
		if(!options.mapEnableDragging)
			el.jmap.disableDragging(); // On by default
			
		// Enable Info Windows
		if(!options.mapEnableInfoWindows)
			el.jmap.disableInfoWindow(); // On by default
		
		// Enable double click zoom on the map
		if(options.mapEnableDoubleClickZoom)
			el.jmap.enableDoubleClickZoom(); // On by default
		
		// Enable scrollwheel on the map
		if(options.mapEnableScrollZoom)
			el.jmap.enableScrollWheelZoom(); //Off by default
		
		// Enable smooth zooming
		if (options.mapEnableSmoothZoom)
			el.jmap.enableContinuousZoom(); // Off by default

		// Enable Google Bar
		if (options.mapEnableGoogleBar)
			el.jmap.enableGoogleBar();  //Off by default
			
		// Enables Scale bar
		if (options.mapEnableScaleControl)
			el.jmap.addControl(new GScaleControl());

		// output init to console
		if (options.debugMode) {
		    console.log(el.jmap);
		}
		
		// Initialise variables
		$.jmap.getMapType();
		
		if (typeof callback == 'function') return callback(el, options);
	};
	
	/**
	 *	.addFeed(options, callback?);
	 *	This function takes a KML or GeoRSS file and
	 *	adds it to the map
	 */
	$.jmap.addFeed = function(options, callback) {
	
		var options = $.extend({}, $.jmap.JFeedDefaults, options);
		
		// Load feed
		var feed = new GGeoXml(options.feedUrl);
		// Add as overlay
		$.jmap.GMap2.addOverlay(feed);
		
		// If the user has passed the optional mapCenter,
		// then center the map on that point
		if (options.mapCenter[0] && options.mapCenter[1])
			$.jmap.GMap2.setCenter(new GLatLng(options.mapCenter[0], options.mapCenter[1]));
		
		if (typeof callback == 'function') return callback(feed, options);
	};
	
	$.jmap.addGroundOverlay = function(options, callback) {
		var options = $.extend({}, $.jmap.JGroundOverlayDefaults, options);
		var boundries = new GLatLngBounds(new GLatLng(options.overlaySouthWestBounds[0], options.overlaySouthWestBounds[1]), new GLatLng(options.overlayNorthEastBounds[0], options.overlayNorthEastBounds[1]));
		
		$.jmap.GGroundOverlay = new GGroundOverlay(options.overlayImage, boundries);
		$.jmap.GMap2.addOverlay($.jmap.GGroundOverlay);
		
		if (typeof callback == 'function') return callback();
	};
	
	$.jmap.hideGroundOverlay = function(callback) {
		$.jmap.GGroundOverlay.hide();
		if (typeof callback == 'function') return callback();
	};
	
	$.jmap.showGroundOverlay = function(callback) {
		isHidden = $.jmap.GGroundOverlay.isHidden();
		if (isHidden)
			$.jmap.GGroundOverlay.show();
		if (typeof callback == 'function') return callback();
	};
	
	/**
	 *	Create a marker and add it as a point to the map
	 */
	$.jmap.addMarker = function(options, callback) {
		// Create options
		var options = $.extend({}, $.jmap.JMarkerDefaults, options);
		var markerOptions = {};
		
		if (typeof options.pointIcon == 'object')
			$.extend(markerOptions, {icon: options.pointIcon});
		
		if (options.pointIsDraggable)
			$.extend(markerOptions, {draggable: options.pointIsDraggable});
		
		// Create marker, optional parameter to make it draggable
		var marker = new GMarker(new GLatLng(options.pointLatLng[0],options.pointLatLng[1]), markerOptions);
		
		// If it has HTML to pass in, add an event listner for a click
		if(options.pointHTML)
			GEvent.addListener(marker, options.pointOpenHTMLEvent, function(){
				marker.openInfoWindowHtml(options.pointHTML, {maxContent: options.pointMaxContent, maxTitle: options.pointMaxTitle});
			});

		// If it is removable, add dblclick event
		if(options.pointIsRemovable)
			GEvent.addListener(marker, options.pointRemoveEvent, function(){
				$.jmap.GMap2.removeOverlay(marker);
			});

		// If the marker manager exists, add it
		if($.jmap.GMarkerManager) {
			$.jmap.GMarkerManager.addMarker(marker, options.pointMinZoom, options.pointMaxZoom);	
		} else {
			// Direct rendering to map
			$.jmap.GMap2.addOverlay(marker);
		}
		
		if (typeof callback == 'function') return callback();
	};
	
	/**
	 * Create a screen overlay
	 * @param {Object} options
	 * @param function callback
	 */
	$.jmap.addScreenOverlay = function(options, callback) {
		var options = $.extend({}, $.jmap.JScreenOverlayDefaults, options);
		var overlay = new GScreenOverlay(options.imageUrl, new GScreenPoint(options.screenXY[0],options.screenXY[1]), new GScreenPoint(options.overlayXY[0],options.overlayXY[1]), new GScreenSize(options.size[0],options.size[1]));
		$.jmap.GMap2.addOverlay(overlay);
		
		if (typeof callback == 'function') return callback(overlay, options);
	};
	
	/**
	 * Create a polygon and render to the map
	 */
	 $.jmap.addPolygon = function(options, callback) {
	 	
	 	var options = $.extend({}, $.jmap.JPolygonDefaults, options);
		polygonOptions = {};
	 	if (!options.polygonClickable)
			var polygonOptions = $.extend({}, polygonOptions, {
				clickable: false
			});
	 		
	 	if(options.mapCenter[0] && options.mapCenter[1])
	 		$.jmap.GMap2.setCenter(new GLatLng(options.mapCenter[0], options.mapCenter[1]));
		
		var polygon = new GPolygon(options.polygonPoints, options.polygonStrokeColor, options.polygonStrokeWeight, options.polygonStrokeOpacity, options.polygonFillColor, options.polygonFillOpacity, polygonOptions);

		$.jmap.GMap2.addOverlay(polygon);
		
		if (typeof callback == 'function') return callback();
	 };
	
	/**
	 *	Create a polyline and render on the map
	 */
	$.jmap.addPolyline = function (options, callback) {
		var options = $.extend({}, $.jmap.JPolylineDefaults, options);
		var polyLineOptions = {};
		if (options.polylineGeodesic)
			$.extend({}, polyLineOptions, {geodesic: true});
			
		if(!options.polylineClickable)
			$.extend({}, polyLineOptions, {clickable: false});

		if (options.mapCenter[0] && options.mapCenter[1])
			$.jmap.GMap2.setCenter(new GLatLng(options.mapCenter[0], options.mapCenter[1]));

		var polyline = new GPolyline(options.polylinePoints, options.polylineStrokeColor, options.polylineStrokeWidth, options.polylineStrokeOpacity, polyLineOptions);
		$.jmap.GMap2.addOverlay(polyline);
		
		if (typeof callback == 'function') return callback();
	};
		
	/**
	 *	.trafficInfo(options?, callback?);
	 *	This function renders a traffic info
	 *	overlay for supported cities
	 *	The GTrafficOverlay also has it's own show/hide methods
	 *	that do not destory the overlay.  Can be called:
	 *	$.jmap.GTrafficOverlay.show();
	 *	$.jmap.GTrafficOverlay.hide();
	 */
	$.jmap.addTrafficInfo = function(options, callback) {
		var options = $.extend({}, $.jmap.JTrafficDefaults, options);
		
		// Does the user wants to create or destory the overlay
		switch(options.method) {
			case "create":
				$.jmap.GTrafficOverlay = new GTrafficOverlay;
				// Add overlay
				$.jmap.GMap2.addOverlay($.jmap.GTrafficOverlay);
				// If the user has passed the optional mapCenter,
				// then center the map on that point
				if (options.mapCenter[0] && options.mapCenter[1]) {
					$.jmap.GMap2.setCenter(new GLatLng(options.mapCenter[0], options.mapCenter[1]));
				}
			break;
			case "destroy":
				// Distroy overlay
				$.jmap.GMap2.removeOverlay($.jmap.GTrafficOverlay);
			break;
		
		}
		if (typeof callback == 'function') return callback();
	};
	
	$.jmap.disableTraffic = function(callback) {
		$.jmap.GTrafficOverlay.hide();
		if (typeof callback == 'function') return callback();
	};
	
	$.jmap.enableTraffic = function(callback) {
		$.jmap.GTrafficOverlay.show();
		if (typeof callback == 'function') return callback();
	};
	
	/**
	 *	Create a AdSense ad manager
	 */
	$.jmap.createAdsManager = function(options, callback) {
		var options = $.extend({}, $.jmap.JAdsManagerDefaults, options);
	
		$.jmap.GAdsManager = new GAdsManager($.jmap.GMap2, options.publisherId);
		
		if (typeof callback == 'function') return callback();
	};
	
	$.jmap.hideAds = function(callback){
		$.jmap.GAdsManager.disable();
		if (typeof callback == 'function') return callback();
	};
	
	$.jmap.showAds = function(callback){
		$.jmap.GAdsManager.enable();
		if (typeof callback == 'function') return callback();
	};
	
	// Create Geocoder cache and attach to global object
	$.jmap.createGeoCache = function(callback) {
		$.jmap.GGeocodeCache = new GGeocodeCache();
		if (typeof callback == 'function') return callback();
	};
	
	// Create a geocoder object
	$.jmap.createGeoCoder = function(cache, callback) {
		if (cache) {
			// Create with cache
			$.jmap.GClientGeocoder = new GClientGeocoder(cache);
		} else {
			// No cache
			$.jmap.GClientGeocoder = new GClientGeocoder;
		}
		if (typeof callback == 'function') return callback();
	};
	
	/**
	 * Create an icon to return to addMarker
	 */
	$.jmap.createIcon = function(options) {
		var options = $.extend({}, $.jmap.JIconDefaults, options);
		var icon = new GIcon(G_DEFAULT_ICON);
		
		if(options.iconImage)
			icon.image = options.iconImage;
		if(options.iconShadow)
			icon.shadow = options.iconShadow;
		if(options.iconSize)
			icon.iconSize = options.iconSize;
		if(options.iconShadowSize)
			icon.shadowSize = options.iconShadowSize;
		if(options.iconAnchor)
			icon.iconAnchor = options.iconAnchor;
		if(options.iconInfoWindowAnchor)
			icon.infoWindowAnchor = options.iconInfoWindowAnchor;
	
		return icon;
	};
	
	/**
	 *	Creates the marker manager and attaches it to the $.jmap namespace
	 */
	$.jmap.createMarkerManager = function(options, callback) {
		// Merge the options with the defaults
		var options = $.extend({}, $.jmap.JMarkerManagerDefaults, options);
		// Create the marker manager and attach to the global object
		$.jmap.GMarkerManager = new GMarkerManager($.jmap.GMap2, options);
		// Return the callback
		if (typeof callback == 'function') return callback();
	};
		
	// This is an alias function that allows the user to simply search for an address
	// Can be returned as a result, or as a point on the map
	$.jmap.searchAddress = function(options, callback) {
	
		var options = $.extend({}, $.jmap.JSearchAddressDefaults, options);
		
		// Add options from pass to marker object
		var pass = $.extend({}, $.jmap.JMarkerManagerDefaults);
		
		// Check to see if the Geocoder already exists in the object
		// or create a temporary locally scoped one.
		if (typeof $.jmap.GClientGeocoder == 'undefined') {
			 var geocoder = new GClientGeocoder;
		} else {
			var geocoder = $.jmap.GClientGeocoder;
		}
		
		// Geocode the address
		geocoder.getLatLng(options.address, function(point){
				if (!point) {
					// Address is not found, throw an error
					throw new Error($.jmap.JErrors[$.jmap.JOptions.language].addressNotFound);
				}
				if (typeof callback == 'function') return callback(options, point);
		});
	};
	

	/**
	 *	.searchDirections(options, callback?);
	 *	This function allows you to pass a to and from address.  If To address
	 *	is previous from address, automatically creates a GRoute object
	 */	
	$.jmap.searchDirections = function(options, callback) {	
		var options = $.extend({}, $.jmap.JSearchDirectionsDefaults, options);
		var panel = $('#' + options.directionsPanel).get(0);
		$.jmap.GDirections = new GDirections($.jmap.GMap2, panel);
		$.jmap.GDirections.load(options.fromAddress + ' to ' + options.toAddress);
		if (typeof callback == 'function') return callback();
	};
	
	$.jmap.moveTo = function(options, callback) {
		
		var options = $.extend({}, $.jmap.JMoveToDefaults, options);
		if (options.mapType)
			var mapType = $.jmap._initMapType(options.mapType);
		var point = new GLatLng(options.mapCenter[0], options.mapCenter[1]);
		switch (options.centerMethod) {
			case 'normal':
				$.jmap.GMap2.setCenter(point, options.mapZoom, mapType);
				break;
			case 'pan':
				$.jmap.GMap2.panTo(point);
				break;
		}
		if (typeof callback == 'function') return callback();
	};
	
	$.jmap.savePosition = function(options, callback) {
		var options = $.extend({}, $.jmap.JMoveToDefaults, options);
		if (options.recall) {
			$.jmap.GMap2.returnToSavedPosition();
		} else {
			$.jmap.GMap2.savePosition();
		}
		if (typeof callback == 'function') return callback();
	};
	
	$.jmap.createKeyboardHandler = function(callback){
		$.jmap.keyboardHandler = new GKeyboardHandler($.jmap.GMap2);
		if (typeof callback == 'function') return callback();
	};
	
	
	// Function currently not returning correctly
	$.jmap.getMapType = function() {
		var mapTypes = $.jmap.GMap2.getMapTypes();
		var actMap = $.jmap.GMap2.getCurrentMapType();
		if (actMap.Hz) {
			$.jmap.variables.mapType = actMap.Hz;
		}
	};
	
	$.jmap.getCenter = function(){
		var center = $.jmap.GMap2.getCenter();
		$.jmap.variables.mapCenter = center;
		if (typeof callback == 'function') return callback(center);
	};
	
	$.jmap.getBounds = function(){
		var bounds = $.jmap.GMap2.getBounds();
		$.jmap.variables.mapBounds = bounds;
		if (typeof callback == 'function') return callback(bounds);
	};

	/* Internal Functions */
	
	/**
	 *	Function: 	setMapType
	 *	Accepts: 	string maptype
	 *	Returns:	CONSTANT maptype
	 **/ 
	$.jmap._initMapType = function(option) {
		// Lets set our map type based on the options
		switch(option) {
			case "map":	// Normal Map
				var maptype = G_NORMAL_MAP;
			break;
			case "sat":	// Satallite Imagery
				var maptype = G_SATELLITE_MAP;
			break;
			case "hybrid":	//Hybrid Map
				var maptype = G_HYBRID_MAP;
			break;
		}
		return maptype;	
	};
	
	$.jmap._initChecks = function(el) {
		// Check if API can be loaded
		if (typeof GBrowserIsCompatible == 'undefined') {
			// Because map does not load, provide visual error
			$(el).text($.jmap.JErrors[$.jmap.JOptions.language].cannotLoad).css({
				color: "#f00"
			});
			// Throw exception
			throw Error($.jmap.JErrors[$.jmap.JOptions.language].cannotLoad);
		}
		// Check to see if browser is compatible, if not throw and exception
		if (!GBrowserIsCompatible()) {
			// Because map does not load, provide visual error
			$(el).text($.jmap.JErrors[$.jmap.JOptions.language].browserNotCompatible).css({color: "#f00"});
			// Throw exception
			throw Error($.jmap.JErrors[$.jmap.JOptions.language].browserNotCompatible);
		}
	};
	
	$.jmap.storePoints = function(options, callback) {
		$.jmap.store = $.extend({}, $.jmap.store, options);		
		if (typeof callback == 'function') return callback($.jmap.store);
	};
	
	$.fn.jmap = function(method, options, callback) {
		return this.each(function(){
			if (method == "init") {
				new $.jmap.init(this, options, callback);
			} else if (typeof method == 'object' || method == null) {
				new $.jmap.init(this, method, options);
			} else if (typeof options == 'function'){
				new $.jmap[method](options);
			} else {
				try {
					new $.jmap[method](options, callback);
				} catch(err) {
					throw Error($.jmap.JErrors[$.jmap.JOptions.language].functionDoesNotExist);
				}
			}
		});
	};
})(jQuery);