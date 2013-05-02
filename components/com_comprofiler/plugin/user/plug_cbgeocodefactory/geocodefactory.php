<?php

/**
 * @name		Geocode Factory plugin
 * @package		geocodefactory
 * @copyright	Copyright © 2009 - All rights reserved.
 * @license		GNU/GPL
 * @author		Cédric Pelloquin
 * @author mail	joomla@pelloquin.com
 * @website		www.pelloquin.com
 */

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class getgeocodefactoryTab extends cbTabHandler {

	function getgeocodefactoryTab() {
		$this->cbTabHandler();
	}
	
	function getDb(){
		$db = null ;
		if (class_exists('JFactory')) {
			$db = & JFactory::getDBO();
		}
		else{
			global $database ;
			$db = & $database ;
		}

		return $db;
	}
	
	function getDisplayTab($tab,$user,$ui) {
		$f_lat = $user->cb_plug_lat;
		$f_lng = $user->cb_plug_lng;

		if (!$f_lat OR !$f_lng)
			return "User has not mapped location yet!";
	
		if (!strlen($this->params->get('apiKey')))
			return "No google Apikey!";

		$return = $this->getMap($f_lat, $f_lng, false);
		return $return;
	}
	
	function getParam($p){
		$res = $this->params->get($p);
		
		switch($p){
			case 'mapsZoom':{
				if ($res<1)
					$res=10;
				break;
			}
			case 'height':
			case 'width':{
				
				if (!strlen($res))
					$res="200px";
				break;
			}
		}
		
		return $res;
	}
	
	function getEditTab($tab,$user,$ui) {
		$return = "";
		
		$f_lat = $user->cb_plug_lat;
		$f_lng = $user->cb_plug_lng;

		$return.= $this->getMap($f_lat, $f_lng, $this->params->get('pickPoint')?true:false) ;
		
		if ($this->params->get('geocodeBtn'))
			$return.= $this->getGeocodeButton() ;

		return $return ;
	}
	
	// recherche les coordonnées moyennes
	function getDefLatLong(&$lat, &$lng){
		$lat = 46.94;
		$lng = 7.42;

		$db = $this->getDb();
		$db->setQuery("SELECT AVG(`cb_pl ug_lat`) AS moyLat, AVG(`cb_plug_lng`) AS moyLng FROM `jos_comprofiler` WHERE (`cb_plug_lat` <> '' AND `cb_plug_lng` <> '') LIMIT 1");
		$voRes = $db->loadObjectList();

		if (!count($voRes))
			return ;
		
		$lat=$voRes[0]->moyLat ;
		$lng=$voRes[0]->moyLng ;
	}
	
	function getMap($f_lat, $f_lng, $draggable){
		if ($f_lat=="" OR $f_lng=="")
			$this->getDefLatLong($f_lat, $f_lng);
		
		$return = "" ;
		$return.= '<script src="http://maps.google.com/maps?file=api&v=2&key='.$this->params->get('apiKey').'" type="text/javascript"></script>';
		$return.= '<div id="ggmap" style="width:'.$this->getParam('width').'px; height:'.$this->getParam('height').'px;" class="pecCbMaps"></div>'."\n";
		$return.= '<script type="text/javascript">';
		$return.= '//<![CDATA['."\n";
		$return.= 'function loadUserMap() {'."\n";
		$return.= '	if (GBrowserIsCompatible()) {'."\n";
		$return.= '		var map = new GMap2(document.getElementById("ggmap"), {size:new GSize('.$this->getParam('width').','.$this->getParam('height').')});'."\n";
		
		if ($draggable){
			// dans ce cas on prends les coordonnées du form
			$return.= ' 	var u_lat = document.getElementById("cb_plug_lat").value ; ';
			$return.= ' 	var u_lng = document.getElementById("cb_plug_lng").value ; ';
		}else{
			// sinon celles de la base
			$return.= ' 	var u_lat = "'.$f_lat.'" ; ';
			$return.= ' 	var u_lng = "'.$f_lng.'" ; ';
		}

		$return.= '		var centerPoint = new GLatLng(u_lat,u_lng) ; '."\n";
		$return.= '		map.setCenter(centerPoint,'.$this->getParam('mapsZoom').') ; '."\n";
		
		if($this->params->get('mapControl') == 1)		$return.= ' map.addControl(new GSmallMapControl());';
		if($this->params->get('mapControl') == 2)		$return.= ' map.addControl(new GLargeMapControl());';
		if($this->params->get('mapTypeControl')) 		$return.= ' map.addControl(new GMapTypeControl());';
		if($this->params->get('overviewMapControl'))	$return.= ' map.addControl(new GOverviewMapControl());';
		if($this->params->get('doubleClickZoom')) 		$return.= ' map.enableDoubleClickZoom();';
			
		switch ($this->params->get('mapTypeOnStart')) {
			case 'G_SATELLITE_MAP':						$return.= ' map.setMapType(G_SATELLITE_MAP);'; break;
			case 'G_HYBRID_MAP':						$return.= ' map.setMapType(G_HYBRID_MAP);'; break;
			case 'G_PHYSICAL_MAP':						$return.= ' map.setMapType(G_PHYSICAL_MAP);'; break;
			case 'G_NORMAL_MAP':	
			default:									$return.= ' map.setMapType(G_NORMAL_MAP);'; break;
		}

		if ($draggable){ 
			$return.= ' 		var marker = new GMarker(centerPoint, {draggable: true}); ' ;
			$return.= ' 		map.addOverlay(marker); '."\n";
	
			$return.= ' 		GEvent.addListener(marker, "dragend", function() { ';
			$return.= ' 			ll = marker.getLatLng() ; ';
			$return.= ' 			document.getElementById("cb_plug_lat").value = ll.y; ';
			$return.= ' 			document.getElementById("cb_plug_lng").value = ll.x; ';
			$return.= ' 		}); '."\n";
			$return.= ' 		GEvent.addListener(map, "click", function(overlay, point) { ';
			$return.= ' 			if (point) { ';
			$return.= ' 				document.getElementById("cb_plug_lat").value = point.y; ';
			$return.= ' 				document.getElementById("cb_plug_lng").value = point.x; ';
			$return.= ' 				loadUserMap(); ';
			$return.= ' 			} ';
			$return.= ' 		}); '."\n";  
		} else {
			$return.= ' 			map.addOverlay(new GMarker(centerPoint));'."\n";
		}
		$return.= '		}'; 
		$return.= '	}';
		$return.= "\n if(window.attachEvent) { window.attachEvent('onload', loadUserMap); } "; 
		$return.= " else if(window.addEventListener) { window.addEventListener('load', loadUserMap, false); } ";
		$return.= '	//]]>';
		$return.= ' </script>';
		
		return $return;
	}
	
	function getGeocodeButton(){
		$ret = "" ;
		
		$ret.= ' <script type="text/javascript" language="JavaScript"> //<![CDATA[ '."\n";
		$ret.= ' function fetchCoordinates() { '."\n";
		$ret.= ' 	var gRequest = null ; '."\n";
		$ret.= ' 	var postalcode = document.getElementById("'.$this->params->get('gfZip').	'").value; ';
		$ret.= ' 	var city = document.getElementById("'.		$this->params->get('gfCity').	'").value; ';
		$ret.= ' 	var street = document.getElementById("'.	$this->params->get('gfAddress').'").value; ';
		$ret.= ' 	var country = document.getElementById("'.	$this->params->get('gfCountry').'").value; '."\n";
		$ret.= ' 	if((postalcode != "") || (city != "") || (country != "") || (street != "")) { '."\n";
		$ret.= ' 		var gRequest = "http://maps.google.com/maps/geo?q=" +street+ "+" +postalcode+ "+" +city+ "+" +country+ "&callback=getCoordinates&output=JSON&key="+"'.$this->params->get('apiKey').'"; '."\n";
		$ret.= ' 		var scriptObj = document.createElement("script"); ';
		$ret.= ' 		scriptObj.setAttribute("type", "text/javascript"); ';
		$ret.= ' 		scriptObj.setAttribute("src", gRequest); ';
		$ret.= ' 		document.getElementsByTagName("head").item(0).appendChild(scriptObj); '."\n";
		$ret.= ' 	} ';
		$ret.= ' } '."\n";
		$ret.= ' function getCoordinates(data) { '."\n";
		$ret.= ' 	switch(data.Status.code) { '."\n";
		$ret.= ' 		case 610: ';
		$ret.= ' 			alert("Invalid Google apikey."); ';
		$ret.= ' 			break; '."\n";
		$ret.= ' 		case 603: ';
		$ret.= ' 		case 602: ';
		$ret.= ' 		case 601: ';
		$ret.= ' 		case 500: ';
		$ret.= ' 			alert("Unknow address."); ';
		$ret.= ' 			break; '."\n";
		$ret.= ' 		case 200: ';
		$ret.= ' 			document.getElementById("cb_plug_lat").value = data.Placemark[0].Point.coordinates[1]; ';
		$ret.= ' 			document.getElementById("cb_plug_lng").value = data.Placemark[0].Point.coordinates[0]; ';
		$ret.= ' 			loadUserMap(); ';
		$ret.= ' 			break; '."\n";
		$ret.= ' 	} ';
		$ret.= ' } '."\n";
		$ret.= ' //]]> </script> '."\n";
		$ret.= ' <input type="button" class="button" onclick="fetchCoordinates();" value="Fetch coordinates" /> '."\n";

		return $ret ;
	}
	
	function getProductInfos(){
		echo '<p>This plugin is a free part of the <a href="http://www.pelloquin.com" target="_blank">Geocode Factory for Community Builder Component</a>  and exist in a <span style="color:green; font-weight:bold;">pro</span> version, containing a silent geocode process during registration, and during profile update: <a href="http://www.pelloquin.com" target="_blank">Update to the pro version</a>.</p>';
		echo '<p>The Geocode Factory CB plugin allows you to display a map in a user tab, and allows the user and admin to geocode the user position from the entered address (city, street, country, zipcode). Another feature from the PRO version is to geocode the user in silent mode, during registration process.</p>';
		echo '<p><img src="../components/com_comprofiler/plugin/user/plug_cbgeocodefactory/pec_logo.png"></p>';
	}

	function getFieldId($name){
		$db = $this->getDb();
		$db->setQuery("SELECT `fieldid` FROM `#__comprofiler_fields` WHERE `table`='#__comprofiler' AND `name`='{$name}' LIMIT 1");
		return $db->loadResult();
	}

	function checkFieldsExisting(){
		$gfOldLat = $this->params->get('gfOldLat');
		$gfOldLng = $this->params->get('gfOldLng');

		$db = $this->getDb();
		$db->setQuery("SELECT `name` FROM `#__comprofiler_fields` WHERE ((`table`='#__comprofiler' OR `table`='#__users') AND (`type`='text' OR `type`='predefined')) ORDER BY name");
		$voNames =  $db->loadObjectList();
		
		if (!count($voNames))
			return false;

		$iCheck = 0 ;
		foreach($voNames as $name){
			
			if ($iCheck>=2)
				return true ;

			if (($name->name == $gfOldLat) OR ($name->name == $gfOldLng))
				$iCheck++;
		}

		if ($iCheck>=2)
			return true ;

		return false;
	}

	function migrationTool(){
		$msg_pre = '<div class="cbWarning">';
		$msg_post = 'Dont forget to set the migration param to NO. </div>' ;

		return $msg_pre."This feature is only avaible in PRO version. ".$msg_post ;
	}

}


?>