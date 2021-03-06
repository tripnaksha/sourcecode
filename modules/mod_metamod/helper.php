<?php
/**
* @version		1.5g
* @copyright	Copyright (C) 2007-2008 Stephen Brandon
* @license		GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modMetaModHelper
{
	
	function locateGeoIPInclude($use_geoip) {

		// determine which file we are going to import, based on MetaMod parameter
		$geoIPDataFiles = array('','GeoIP.dat','GeoLiteCity.dat','GeoIPCity.dat');

		$geoip_folders = //MetaModParameters::geoIPFolders();
			array(
				"geoip".DS,
				"GeoIP".DS,
				"geoIP".DS,
				"GEOIP".DS,
				"GEO IP".DS,
				'',
				"geo_ip".DS,
				"geo_IP".DS,
				"Geo_IP".DS
				);
		$file = '';
		// find the file in any of the standard locations
		foreach ($geoip_folders as $folder) {
			$target = $geoIPDataFiles[$use_geoip];
			$proposed_file = JPATH_SITE.DS.$folder.DS.$target;
			if (file_exists($proposed_file)) {
				$file = $proposed_file;
				break;
			}
		}
		return $file;		
	}
	
	function languages(&$params)
	{
		$debug 			= trim( $params->get( 'debug', '0' ) );
		$opt_option		= trim( $params->get( 'language_option', '0' ) );
		$opt_compare_strict	= trim( $params->get( 'language_compare_strict', '1' ) );
		$opt_preferred		= trim( $params->get( 'language_preferred', '' ) );
		// for $language_preferred, order doesn't matter. MetaMod will just
		// look through the browser's list of languages one at a time, and
		// use the first one that also appears in $language_preferred.

		$language = '';
		$language_code = '';
		$language_region = '';
		$languages = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);


		if ($opt_option == 0) {
			// main browser lang
			if (count($languages)) {
				$language = strtolower($languages[0]);
			}
		}

		if ($opt_option == 1) { // preferred language from list
			// want to go through each of the languages in the browser string, from the 1st one,
			// and see if any of these are in the list that the admin supplied.
			$preferred_languages = explode(",",$opt_preferred);
			for ($i = 0 ; $i < count($preferred_languages); $i++) {
				$preferred_languages[$i] = trim(strtolower( $preferred_languages[$i] ));
			}

			for ($i = 0 ; $i < count($languages); $i++) {
				// first get rid of ";q=XX" suffixes 
				$l = explode( ";", trim(strtolower( $languages[$i] )));
				$try_this = $l[0];
				preg_match("/([a-z]+)-/",$try_this,$try_this_matches);
				if (
					// strict or not strict, both need this
					in_array($try_this, $preferred_languages)
					||
					// if not strict, try the browser "major" type against "preferred" ones
					( !$opt_compare_strict && count($try_this_matches) > 0 && in_array($try_this_matches[1], $preferred_languages) ) 

				) {
					$language = $try_this;
					break;
				}
			}
		}

		if ($opt_option == 2) {
			// The language of the Joomla front-end
			global $mainframe;
			$language = strtolower($mainframe->getCfg('language'));
		}

		if ($language != '') {
			preg_match("/([a-z]+)(-([a-z]+))?/",$language,$language_matches);
			$language_code = $language_matches[1];
			if (count($language_matches) > 3) $language_region = $language_matches[3];
		}
		if ($debug) {
			echo '<i>$language:</i> '.htmlentities($language).'<br>';
			echo '<i>$language_code:</i> '.htmlentities($language_code).'<br>';
			echo '<i>$language_region:</i> '.htmlentities($language_region).'<br>';
		}
		
		return array($language,$language_code,$language_region);
	}

	function timezone_offset($timestamp,$zone) {
		$old_tz = getenv('TZ');
		putenv("TZ=$zone");
		$offset = date("Z",$timestamp);
		putenv("TZ=$old_tz");
    		return $offset;
	}

	function moduleIds(&$params)
	{
		global $Itemid,$option;
		$id = array_key_exists('id',$_REQUEST) ? $_REQUEST['id'] : '';
		$view = array_key_exists('view',$_REQUEST) ? $_REQUEST['view'] : '';
		
		// Retrieve parameters
		$php 			= trim( $params->get( 'php', '' ) );
		$start_datetime 	= trim( $params->get( 'start_datetime', '' ) );
		$end_datetime 		= trim( $params->get( 'end_datetime', '' ) );
		$timezone 		= trim( $params->get( 'timezone', 'default' ) );
		$use_geoip 		= intval( $params->get( 'use_geoip', '0' ) );
		$module_ids 		= trim( $params->get( 'module_ids', '0' ) );
		$debug 			= trim( $params->get( 'debug', '0' ) );

		$now 			= &JFactory::getDate();
		if ($timezone == "default") {
			$config	=& JFactory::getConfig();
			$offset = $config->getValue('config.offset') * 3600;
		} else {
			$offset = modMetaModHelper::timezone_offset(time(),$timezone);
		}
		$now_time		= $now->toUnix() + $offset; // can now be compared directly with getData($start_datetime)

		// if you were to print this date, it should print in the locale set up in Joomla

		if ($debug) {
			echo '<b>MetaMod debug info:</b><br />';
			echo '<i>$option:</i> '.htmlentities($option).'<br />';
			echo '<i>$view:</i> '.htmlentities($view).'<br />';
			echo '<i>$id:</i> '.htmlentities($id).'<br />';
			echo '<i>$Itemid:</i> '.htmlentities($Itemid).'<br />';
			echo '<i>$timezone:</i> '.htmlentities($timezone).'<br />';
		}
		
		// LANGUAGE HANDLING
		list($language,$language_code,$language_region) = modMetaModHelper::languages($params);

		// START AND END TIMES
		// quit if we're not to start yet
		$zone = date("Z");
		if ($start_datetime != '') {
			$then = &JFactory::getDate($start_datetime);
		 	if ($then->toUnix() > $now_time) {
				if ($debug) {
					echo 'Start date/time has not been reached.<br />';
				}
				return;
			}
			if ($debug) {
				echo 'Start date/time has been reached.<br />';
			}
		}

		// quit if we are too late to display this item
		if ($end_datetime != '') {
			$then = &JFactory::getDate($end_datetime);
			if ($then->toUnix() <= $now_time) {
				if ($debug) {
					echo 'End date/time has already passed.<br />';
				}
				return;
			}
			if ($debug) {
				echo 'End date/time has not passed.<br />';
			}
		}
		
		// INCLUDE AND EXCLUDE COUNTRIES, & GEOIP FUNCTIONALITY
		$include_countries	= strtoupper(trim( $params->get( 'include_countries', '' ) ));
		$exclude_countries	= strtoupper(trim( $params->get( 'exclude_countries', '' ) ));

		static $geoip = null;

		$fromCountryId = defined('_GEOIP_FROM_COUNTRY_ID') ? _GEOIP_FROM_COUNTRY_ID : '';
		$fromCountryName = defined('_GEOIP_FROM_COUNTRY_NAME') ? _GEOIP_FROM_COUNTRY_NAME : '';
		$ip = $_SERVER['REMOTE_ADDR'];
		
		if ($use_geoip) {
			if ($fromCountryId == '' && $fromCountryName == '' && $geoip == null) {

				// find the location of the geoip data file, depending on which we are to use
				$include_file = modMetaModHelper::locateGeoIPInclude($use_geoip);
				if ($include_file == null) {
					if ($debug) {
						echo "<b>ERROR: cannot locate GeoIP data file in any standard location. Disabing GeoIP.</b><br />";
					}
				} else {
					$geoIPFiles = array('','geoip.inc','geoipcity.inc','geoipcity.inc');
				
					// grab the appropriate code for whichever geoip stuff we have 
					if (!function_exists("geoip_open")) {
						include_once(JPATH_SITE.DS."modules".DS."mod_metamod".DS."mod_metamod".DS."geoip-php4".DS.$geoIPFiles[$use_geoip]);
					}
				
					// open the data file
					$gi = geoip_open($include_file,GEOIP_STANDARD);

					if ($use_geoip == 1) { // GeoIP Country (free or commercial)
						$fromCountryId = geoip_country_code_by_addr($gi, $ip);
						$fromCountryName = geoip_country_name_by_addr($gi, $ip);
					} else { // GeoCity or GeoLiteCity
						$geoip = geoip_record_by_addr($gi, $ip);
						$fromCountryId = $geoip->country_code;
						$fromCountryName = $geoip->country_name;
						if ($geoip == null && $debug) {
							echo "No GeoCity info found for $ip. Using default country.<br />";
						}
					}
					geoip_close($gi);

					// set some defaults if necessary (probably can't happen)
					if ($fromCountryId == '') $fromCountryId = "GB";
					if ($fromCountryName == '') $fromCountryName = "United Kingdom";
					// save these away for next time
					define ("_GEOIP_FROM_COUNTRY_ID",$fromCountryId);
					define ("_GEOIP_FROM_COUNTRY_NAME",$fromCountryName);
				} // we found the geoip data file
			} // we needed to cache the geoip for the current request
		} // we were told to use a particular variant of GeoIP

		if (($fromCountryId || $fromCountryName) && $debug) {
			echo "<i>Country:</i> " .$fromCountryId . "<br />";
			echo "<i>Country Name:</i> " . $fromCountryName . "<br />";
		}

		if($geoip && $debug)
		{
			echo "<i>Country Code 2:</i> " . $geoip->country_code3 . "<br />";
			echo "<i>Region:</i> " .$geoip->region . "<br />";
			echo "<i>City:</i> " .$geoip->city . "<br />";
			echo "<i>Postal Code:</i> " .$geoip->postal_code . "<br />";
			echo "<i>Latitude:</i> " .$geoip->latitude . "<br />";
			echo "<i>Longitude:</i> " .$geoip->longitude . "<br />";
			echo "<i>DMA Code:</i> " .$geoip->dma_code . "<br />";
			echo "<i>Area Code:</i> " .$geoip->area_code . "<br />";
		}						

		if ($use_geoip) {
			if (strlen($include_countries)) {
				// reject if fromCountryId is not in the list
				if (strpos($include_countries,$fromCountryId) === false) {
					if ($debug) {
						echo 'Rejecting: '.$fromCountryId.' is not in include list<br />';
					}
					return;
				}
				if ($debug) {
					echo 'Accepting: '.$fromCountryId.' is in include list<br />';
				}
			}
			if (strlen($exclude_countries)) {
				// reject if fromCountryId is in the list
				if (! (strpos($exclude_countries,$fromCountryId) === false)) {
					if ($debug) {
						echo 'Rejecting: '.$fromCountryId.' is in exclude list<br />';
					}
					return;
				}
				if ($debug) {
					echo 'Accepting: '.$fromCountryId.' is not in exclude list<br />';
				}
			}
		}

		// for access by eval'ed script
		$db 	=& JFactory::getDBO();
		$user 	=& JFactory::getUser();

		// get results from php script
		$mods = strlen($php) ? eval(str_replace("\r<br />","\n",str_replace("\n<br />","\n",$php))) : '';

		// convert comma-separated list of module ids (specified statically) to an array of integers
		$static_ids = strlen($module_ids) ? array_map("intval",explode(",",$module_ids)) : array();

		// convert comma-separated list of module ids (returned from PHP code) to an array of integers
		if (!is_array($mods)) {
			if (is_numeric($mods)) $mods = array($mods);
			if (is_string($mods))  $mods = array_map("intval",explode(",",$mods));
			if (!is_array($mods))  $mods = array();
		}

		// combine the 2 sets of numbers
		$mods = array_filter(array_merge($static_ids,$mods)); // remove everything equating to "0" / false
		if ($debug) {
			echo 'Including modules: '.implode(', ',$mods).'<br />';
		}
		return $mods; 
	}
	
	function displayModules(&$all_mod_array, &$params) {

		if (is_array($all_mod_array) && count($all_mod_array)) {

			// Get various things incl. user permissions obj
			global $mainframe, $Itemid;
			global $option;
			$id = array_key_exists('id',$_REQUEST) ? $_REQUEST['id'] : '';
			$view = array_key_exists('view',$_REQUEST) ? $_REQUEST['view'] : '';

			$db 	=& JFactory::getDBO();
			$user 	=& JFactory::getUser();
			$aid	= $user->get('aid', 0);

			$all_mod_numbers = "('".implode("','",array_map("intval", $all_mod_array ))."')";
			$query = "SELECT id, title, module, position, content, showtitle, control, params"
			. "\n FROM #__modules AS m"
			. "\n WHERE m.published = 1"
			. "\n AND m.access <= ". (int)$aid
			. "\n AND m.client_id = ". (int)$mainframe->getClientId()
			. "\n AND m.id in $all_mod_numbers"
			. "\n ORDER BY ordering";

			$db->setQuery($query);
			$modules = array();

			if (!($modules = $db->loadObjectList())) {
				JError::raiseWarning( 'SOME_ERROR_CODE', "Error loading Modules: " . $db->getErrorMsg());
				return false; // FIXME - what to do here? Ignore?
			}

			// do some stuff that is found in libraries/joomla/application/module/helper.php
			$total = count($modules);
			for($i = 0; $i < $total; $i++)
			{
				//determine if this is a custom module
				$file					= $modules[$i]->module;
				$custom 				= substr( $file, 0, 4 ) == 'mod_' ?  0 : 1;
				$modules[$i]->user  	= $custom;
				// CHECK: custom module name is given by the title field, otherwise it's just 'om' ??
				$modules[$i]->name		= $custom ? $modules[$i]->title : substr( $file, 4 );
				$modules[$i]->style		= null;
				$modules[$i]->position	= strtolower($modules[$i]->position);
			}


			$document	= &JFactory::getDocument();
			$renderer	= $document->loadRenderer('module');
			$style 		= trim( $params->get( 'force_style', trim( $params->get( 'style', 'table' ) ) ) );

			$contents = '';
			foreach ($modules as $mod)  {
				$attribs = array();
				$attribs['style'] = $style;
				$contents .= $renderer->render($mod, $attribs);
			}
			return $contents;

		}
	}
}