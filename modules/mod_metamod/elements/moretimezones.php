<?php
/**
* @version		1.5g
* @copyright	Copyright (C) 2007-2008 Stephen Brandon
* @license		GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElementMoretimezones extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Moretimezones';


	function get_tz_options($name, $selectedzone, $control_name) {
		$r = '';
		$r .= '<select name="'.htmlentities($control_name).'['.htmlentities($name).']">';
		$r .= $this->timezonechoice($selectedzone);
		$r .= '</select>';
		return $r;
	}

	function static_zones() {
		return $timezones = array('Africa/Abidjan' => 0, 'Africa/Accra' => 0, 'Africa/Addis_Ababa' => 10800, 'Africa/Algiers' => 3600, 'Africa/Asmera' => 10800, 'Africa/Bamako' => 0, 'Africa/Bangui' => 3600, 'Africa/Banjul' => 0, 'Africa/Bissau' => 0, 'Africa/Blantyre' => 7200, 'Africa/Brazzaville' => 3600, 'Africa/Bujumbura' => 7200, 'Africa/Cairo' => 7200, 'Africa/Casablanca' => 0, 'Africa/Ceuta' => 3600, 'Africa/Conakry' => 0, 'Africa/Dakar' => 0, 'Africa/Dar_es_Salaam' => 10800, 'Africa/Djibouti' => 10800, 'Africa/Douala' => 3600, 'Africa/El_Aaiun' => 0, 'Africa/Freetown' => 0, 'Africa/Gaborone' => 7200, 'Africa/Harare' => 7200, 'Africa/Johannesburg' => 7200, 'Africa/Kampala' => 10800, 'Africa/Khartoum' => 10800, 'Africa/Kigali' => 7200, 'Africa/Kinshasa' => 3600, 'Africa/Lagos' => 3600, 'Africa/Libreville' => 3600, 'Africa/Lome' => 0, 'Africa/Luanda' => 3600, 'Africa/Lubumbashi' => 7200, 'Africa/Lusaka' => 7200, 'Africa/Malabo' => 3600, 'Africa/Maputo' => 7200, 'Africa/Maseru' => 7200, 'Africa/Mbabane' => 7200, 'Africa/Mogadishu' => 10800, 'Africa/Monrovia' => 0, 'Africa/Nairobi' => 10800, 'Africa/Ndjamena' => 3600, 'Africa/Niamey' => 3600, 'Africa/Nouakchott' => 0, 'Africa/Ouagadougou' => 0, 'Africa/Porto-Novo' => 3600, 'Africa/Sao_Tome' => 0, 'Africa/Timbuktu' => 0, 'Africa/Tripoli' => 7200, 'Africa/Tunis' => 3600, 'Africa/Windhoek' => 3600, 'America/Adak' => -36000, 'America/Anchorage' => -32400, 'America/Anguilla' => -14400, 'America/Antigua' => -14400, 'America/Araguaina' => -10800, 'America/Argentina/Buenos_Aires' => 0, 'America/Argentina/Catamarca' => 0, 'America/Argentina/ComodRivadavia' => 0, 'America/Argentina/Cordoba' => 0, 'America/Argentina/Jujuy' => 0, 'America/Argentina/La_Rioja' => 0, 'America/Argentina/Mendoza' => 0, 'America/Argentina/Rio_Gallegos' => 0, 'America/Argentina/San_Juan' => 0, 'America/Argentina/Tucuman' => 0, 'America/Argentina/Ushuaia' => 0, 'America/Aruba' => -14400, 'America/Asuncion' => -14400, 'America/Atikokan' => 0, 'America/Atka' => -36000, 'America/Bahia' => 0, 'America/Barbados' => -14400, 'America/Belem' => -10800, 'America/Belize' => -21600, 'America/Blanc-Sablon' => 0, 'America/Boa_Vista' => -14400, 'America/Bogota' => -18000, 'America/Boise' => -25200, 'America/Buenos_Aires' => -10800, 'America/Cambridge_Bay' => -25200, 'America/Campo_Grande' => 0, 'America/Cancun' => -21600, 'America/Caracas' => -14400, 'America/Catamarca' => -10800, 'America/Cayenne' => -10800, 'America/Cayman' => -18000, 'America/Chicago' => -21600, 'America/Chihuahua' => -25200, 'America/Coral_Harbour' => 0, 'America/Cordoba' => -10800, 'America/Costa_Rica' => -21600, 'America/Cuiaba' => -14400, 'America/Curacao' => -14400, 'America/Danmarkshavn' => 0, 'America/Dawson' => -28800, 'America/Dawson_Creek' => -25200, 'America/Denver' => -25200, 'America/Detroit' => -18000, 'America/Dominica' => -14400, 'America/Edmonton' => -25200, 'America/Eirunepe' => -18000, 'America/El_Salvador' => -21600, 'America/Ensenada' => -28800, 'America/Fort_Wayne' => -18000, 'America/Fortaleza' => -10800, 'America/Glace_Bay' => -14400, 'America/Godthab' => -10800, 'America/Goose_Bay' => -14400, 'America/Grand_Turk' => -18000, 'America/Grenada' => -14400, 'America/Guadeloupe' => -14400, 'America/Guatemala' => -21600, 'America/Guayaquil' => -18000, 'America/Guyana' => -14400, 'America/Halifax' => -14400, 'America/Havana' => -18000, 'America/Hermosillo' => -25200, 'America/Indiana/Indianapolis' => -18000, 'America/Indiana/Knox' => -18000, 'America/Indiana/Marengo' => -18000, 'America/Indiana/Petersburg' => 0, 'America/Indiana/Vevay' => -18000, 'America/Indiana/Vincennes' => 0, 'America/Indianapolis' => -18000, 'America/Inuvik' => -25200, 'America/Iqaluit' => -18000, 'America/Jamaica' => -18000, 'America/Jujuy' => -10800, 'America/Juneau' => -32400, 'America/Kentucky/Louisville' => -18000, 'America/Kentucky/Monticello' => -18000, 'America/Knox_IN' => -18000, 'America/La_Paz' => -14400, 'America/Lima' => -18000, 'America/Los_Angeles' => -28800, 'America/Louisville' => -18000, 'America/Maceio' => -10800, 'America/Managua' => -21600, 'America/Manaus' => -14400, 'America/Martinique' => -14400, 'America/Mazatlan' => -25200, 'America/Mendoza' => -10800, 'America/Menominee' => -21600, 'America/Merida' => -21600, 'America/Mexico_City' => -21600, 'America/Miquelon' => -10800, 'America/Moncton' => 0, 'America/Monterrey' => -21600, 'America/Montevideo' => -10800, 'America/Montreal' => -18000, 'America/Montserrat' => -14400, 'America/Nassau' => -18000, 'America/New_York' => -18000, 'America/Nipigon' => -18000, 'America/Nome' => -32400, 'America/Noronha' => -7200, 'America/North_Dakota/Center' => -21600, 'America/North_Dakota/New_Salem' => 0, 'America/Panama' => -18000, 'America/Pangnirtung' => -18000, 'America/Paramaribo' => -10800, 'America/Phoenix' => -25200, 'America/Port-au-Prince' => -18000, 'America/Port_of_Spain' => -14400, 'America/Porto_Acre' => -18000, 'America/Porto_Velho' => -14400, 'America/Puerto_Rico' => -14400, 'America/Rainy_River' => -21600, 'America/Rankin_Inlet' => -21600, 'America/Recife' => -10800, 'America/Regina' => -21600, 'America/Rio_Branco' => -18000, 'America/Rosario' => -10800, 'America/Santiago' => -14400, 'America/Santo_Domingo' => -14400, 'America/Sao_Paulo' => -10800, 'America/Scoresbysund' => -3600, 'America/Shiprock' => -25200, 'America/St_Johns' => -12600, 'America/St_Kitts' => -14400, 'America/St_Lucia' => -14400, 'America/St_Thomas' => -14400, 'America/St_Vincent' => -14400, 'America/Swift_Current' => -21600, 'America/Tegucigalpa' => -21600, 'America/Thule' => -14400, 'America/Thunder_Bay' => -18000, 'America/Tijuana' => -28800, 'America/Toronto' => 0, 'America/Tortola' => -14400, 'America/Vancouver' => -28800, 'America/Virgin' => -14400, 'America/Whitehorse' => -28800, 'America/Winnipeg' => -21600, 'America/Yakutat' => -32400, 'America/Yellowknife' => -25200, 'Antarctica/Casey' => 28800, 'Antarctica/Davis' => 25200, 'Antarctica/DumontDUrville' => 36000, 'Antarctica/Mawson' => 21600, 'Antarctica/McMurdo' => 43200, 'Antarctica/Palmer' => -14400, 'Antarctica/Rothera' => 0, 'Antarctica/South_Pole' => 43200, 'Antarctica/Syowa' => 10800, 'Antarctica/VostokArctic/Longyearbyen' => 0, 'Asia/Aden' => 10800, 'Asia/Almaty' => 21600, 'Asia/Amman' => 7200, 'Asia/Anadyr' => 43200, 'Asia/Aqtau' => 14400, 'Asia/Aqtobe' => 18000, 'Asia/Ashgabat' => 18000, 'Asia/Ashkhabad' => 18000, 'Asia/Baghdad' => 10800, 'Asia/Bahrain' => 10800, 'Asia/Baku' => 14400, 'Asia/Bangkok' => 25200, 'Asia/Beirut' => 7200, 'Asia/Bishkek' => 18000, 'Asia/Brunei' => 28800, 'Asia/Calcutta' => 19800, 'Asia/Choibalsan' => 32400, 'Asia/Chongqing' => 28800, 'Asia/Chungking' => 28800, 'Asia/Colombo' => 21600, 'Asia/Dacca' => 21600, 'Asia/Damascus' => 7200, 'Asia/Dhaka' => 21600, 'Asia/Dili' => 32400, 'Asia/Dubai' => 14400, 'Asia/Dushanbe' => 18000, 'Asia/Gaza' => 7200, 'Asia/Harbin' => 28800, 'Asia/Hong_Kong' => 28800, 'Asia/Hovd' => 25200, 'Asia/Irkutsk' => 28800, 'Asia/Istanbul' => 7200, 'Asia/Jakarta' => 25200, 'Asia/Jayapura' => 32400, 'Asia/Jerusalem' => 7200, 'Asia/Kabul' => 16200, 'Asia/Kamchatka' => 43200, 'Asia/Karachi' => 18000, 'Asia/Kashgar' => 28800, 'Asia/Katmandu' => 20700, 'Asia/Krasnoyarsk' => 25200, 'Asia/Kuala_Lumpur' => 28800, 'Asia/Kuching' => 28800, 'Asia/Kuwait' => 10800, 'Asia/Macao' => 28800, 'Asia/Macau' => 0, 'Asia/Magadan' => 39600, 'Asia/Makassar' => 0, 'Asia/Manila' => 28800, 'Asia/Muscat' => 14400, 'Asia/Nicosia' => 7200, 'Asia/Novosibirsk' => 21600, 'Asia/Omsk' => 21600, 'Asia/Oral' => 0, 'Asia/Phnom_Penh' => 25200, 'Asia/Pontianak' => 25200, 'Asia/Pyongyang' => 32400, 'Asia/Qatar' => 10800, 'Asia/Qyzylorda' => 0, 'Asia/Rangoon' => 23400, 'Asia/Riyadh' => 10800, 'Asia/Saigon' => 25200, 'Asia/Sakhalin' => 36000, 'Asia/Samarkand' => 18000, 'Asia/Seoul' => 32400, 'Asia/Shanghai' => 28800, 'Asia/Singapore' => 28800, 'Asia/Taipei' => 28800, 'Asia/Tashkent' => 18000, 'Asia/Tbilisi' => 14400, 'Asia/Tehran' => 12600, 'Asia/Tel_Aviv' => 7200, 'Asia/Thimbu' => 21600, 'Asia/Thimphu' => 21600, 'Asia/Tokyo' => 32400, 'Asia/Ujung_Pandang' => 28800, 'Asia/Ulaanbaatar' => 28800, 'Asia/Ulan_Bator' => 28800, 'Asia/Urumqi' => 28800, 'Asia/Vientiane' => 25200, 'Asia/Vladivostok' => 36000, 'Asia/Yakutsk' => 32400, 'Asia/Yekaterinburg' => 18000, 'Asia/YerevanAtlantic/Azores' => 0, 'Atlantic/Bermuda' => -14400, 'Atlantic/Canary' => 0, 'Atlantic/Cape_Verde' => -3600, 'Atlantic/Faeroe' => 0, 'Atlantic/Jan_Mayen' => 3600, 'Atlantic/Madeira' => 0, 'Atlantic/Reykjavik' => 0, 'Atlantic/South_Georgia' => -7200, 'Atlantic/St_Helena' => 0, 'Atlantic/Stanley' => -14400, 'Australia/ACT' => 36000, 'Australia/Adelaide' => 34200, 'Australia/Brisbane' => 36000, 'Australia/Broken_Hill' => 34200, 'Australia/Canberra' => 36000, 'Australia/Currie' => 0, 'Australia/Darwin' => 34200, 'Australia/Hobart' => 36000, 'Australia/LHI' => 37800, 'Australia/Lindeman' => 36000, 'Australia/Lord_Howe' => 37800, 'Australia/Melbourne' => 36000, 'Australia/NSW' => 36000, 'Australia/North' => 34200, 'Australia/Perth' => 28800, 'Australia/Queensland' => 36000, 'Australia/South' => 34200, 'Australia/Sydney' => 36000, 'Australia/Tasmania' => 36000, 'Australia/Victoria' => 36000, 'Australia/West' => 28800, 'Australia/Yancowinna' => 34200, 'Europe/Amsterdam' => 3600, 'Europe/Andorra' => 3600, 'Europe/Athens' => 7200, 'Europe/Belfast' => 0, 'Europe/Belgrade' => 3600, 'Europe/Berlin' => 3600, 'Europe/Bratislava' => 3600, 'Europe/Brussels' => 3600, 'Europe/Bucharest' => 7200, 'Europe/Budapest' => 3600, 'Europe/Chisinau' => 7200, 'Europe/Copenhagen' => 3600, 'Europe/Dublin' => 0, 'Europe/Gibraltar' => 3600, 'Europe/Guernsey' => 0, 'Europe/Helsinki' => 7200, 'Europe/Isle_of_Man' => 0, 'Europe/Istanbul' => 7200, 'Europe/Jersey' => 0, 'Europe/Kaliningrad' => 7200, 'Europe/Kiev' => 7200, 'Europe/Lisbon' => 0, 'Europe/Ljubljana' => 3600, 'Europe/London' => 0, 'Europe/Luxembourg' => 3600, 'Europe/Madrid' => 3600, 'Europe/Malta' => 3600, 'Europe/Mariehamn' => 0, 'Europe/Minsk' => 7200, 'Europe/Monaco' => 3600, 'Europe/Moscow' => 10800, 'Europe/Nicosia' => 7200, 'Europe/Oslo' => 3600, 'Europe/Paris' => 3600, 'Europe/Prague' => 3600, 'Europe/Riga' => 7200, 'Europe/Rome' => 3600, 'Europe/Samara' => 14400, 'Europe/San_Marino' => 3600, 'Europe/Sarajevo' => 3600, 'Europe/Simferopol' => 7200, 'Europe/Skopje' => 3600, 'Europe/Sofia' => 7200, 'Europe/Stockholm' => 3600, 'Europe/Tallinn' => 7200, 'Europe/Tirane' => 3600, 'Europe/Tiraspol' => 7200, 'Europe/Uzhgorod' => 7200, 'Europe/Vaduz' => 3600, 'Europe/Vatican' => 3600, 'Europe/Vienna' => 3600, 'Europe/Vilnius' => 7200, 'Europe/Volgograd' => 0, 'Europe/Warsaw' => 3600, 'Europe/Zagreb' => 3600, 'Europe/Zaporozhye' => 7200, 'Europe/Zurich' => 3600, 'Indian/Antananarivo' => 10800, 'Indian/Chagos' => 21600, 'Indian/Christmas' => 25200, 'Indian/Cocos' => 23400, 'Indian/Comoro' => 10800, 'Indian/Kerguelen' => 18000, 'Indian/Mahe' => 14400, 'Indian/Maldives' => 18000, 'Indian/Mauritius' => 14400, 'Indian/Mayotte' => 10800, 'Indian/Reunion' => 14400, 'Pacific/Apia' => -39600, 'Pacific/Auckland' => 43200, 'Pacific/Chatham' => 45900, 'Pacific/Easter' => -21600, 'Pacific/Efate' => 39600, 'Pacific/Enderbury' => 46800, 'Pacific/Fakaofo' => -36000, 'Pacific/Fiji' => 43200, 'Pacific/Funafuti' => 43200, 'Pacific/Galapagos' => -21600, 'Pacific/Gambier' => -32400, 'Pacific/Guadalcanal' => 39600, 'Pacific/Guam' => 36000, 'Pacific/Honolulu' => -36000, 'Pacific/Johnston' => -36000, 'Pacific/Kiritimati' => 50400, 'Pacific/Kosrae' => 39600, 'Pacific/Kwajalein' => 43200, 'Pacific/Majuro' => 43200, 'Pacific/Marquesas' => -34200, 'Pacific/Midway' => -39600, 'Pacific/Nauru' => 43200, 'Pacific/Niue' => -39600, 'Pacific/Norfolk' => 41400, 'Pacific/Noumea' => 39600, 'Pacific/Pago_Pago' => -39600, 'Pacific/Palau' => 32400, 'Pacific/Pitcairn' => -28800, 'Pacific/Ponape' => 39600, 'Pacific/Port_Moresby' => 36000, 'Pacific/Rarotonga' => -36000, 'Pacific/Saipan' => 36000, 'Pacific/Samoa' => -39600, 'Pacific/Tahiti' => -36000, 'Pacific/Tarawa' => 43200, 'Pacific/Tongatapu' => 46800, 'Pacific/Truk' => 36000, 'Pacific/Wake' => 43200, 'Pacific/Wallis' => 43200, 'Pacific/Yap' => 36000 );
	}
	function timezonechoice($selectedzone) {
		$structure = '';

		$structure .= '<option '.( ($selectedzone == "default") ?' selected="selected"':'').'value="default">Joomla! default</option>';
		
		if (function_exists("timezone_identifiers_list")) {
			$all = timezone_identifiers_list(); //php5 only
		} else {
			$all = array_keys($this->static_zones()); // I would rather get them from the OS, but this will do at a pinch.
		}
		$i = 0;
		foreach($all AS $zone) {
			$zone = explode('/',$zone);
			$zonen[$i]['continent'] = isset($zone[0]) ? $zone[0] : '';
			$zonen[$i]['city'] = isset($zone[1]) ? $zone[1] : '';
			$zonen[$i]['subcity'] = isset($zone[2]) ? $zone[2] : '';
			$i++;
		}

		asort($zonen);
		foreach($zonen AS $zone) {
			extract($zone);
			if($continent == 'Africa' || $continent == 'America' || $continent == 'Antarctica' 
			|| $continent == 'Arctic' || $continent == 'Asia' || $continent == 'Atlantic' 
			|| $continent == 'Australia' || $continent == 'Europe' || $continent == 'Indian' 
			|| $continent == 'Pacific') {
				if(!isset($selectcontinent)) {
					$structure .= '<optgroup label="'.$continent.'">'; // continent
				} elseif($selectcontinent != $continent) {
					$structure .= '</optgroup><optgroup label="'.$continent.'">'; // continent
				}

				if(isset($city) != ''){
					if (!empty($subcity) != ''){
						$city = $city . '/'. $subcity;
					}
					$structure .= "<option ".((($continent.'/'.$city)==$selectedzone)?'selected="selected "':'')." value=\"".($continent.'/'.$city)."\">".str_replace('_',' ',$city)."</option>"; //Timezone
				} else {
					if (!empty($subcity) != ''){
						$city = $city . '/'. $subcity;
					}
					$structure .= "<option ".(($continent==$selectedzone)?'selected="selected "':'')." value=\"".$continent."\">".$continent."</option>"; //Timezone
				}

				$selectcontinent = $continent;
			}
		}
		$structure .= '</optgroup>';
		return $structure;
	}

	function fetchElement($name, $value, &$node, $control_name) {
		return $this->get_tz_options($name, $value, $control_name);
	}
}