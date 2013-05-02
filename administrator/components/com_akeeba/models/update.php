<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: update.php 209 2010-08-11 21:43:26Z nikosdion $
 * @since 2.2
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

/**
 * The Live Update model
 *
 */
class AkeebaModelUpdate extends JModel
{
	private $update_url = '';

	/**
	 * Public constructor
	 * @param unknown_type $config
	 */
	public function __construct( $config = array() )
	{
		parent::__construct($config);

		// Determine the appropriate update URL based on whether we're on Core or Professional edition
		AEPlatform::load_version_defines();
		if(AKEEBA_PRO == 1)
		{
			$this->update_url = 'https://www.akeebabackup.com/updates/abpro.ini';
		}
		else
		{
			$this->update_url = 'https://www.akeebabackup.com/updates/abcore.ini';
		}
	}

	/**
	 * Does the server support URL fopen() wrappers?
	 * @return bool
	 */
	private function hasURLfopen()
	{
		// If we are not allowed to use ini_get, we assume that URL fopen is
		// disabled.
		if(!function_exists('ini_get'))
		return false;

		if( !ini_get('allow_url_fopen') )
		return false;

		return true;
	}

	/**
	 * Does the server support the cURL extension?
	 * @return bool
	 */
	private function hascURL()
	{
		if(!function_exists('curl_exec'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns the date and time when the last update check was made.
	 * @return JDate
	 */
	private function lastUpdateCheck()
	{
		// Get a reference to component's parameters
		$component =& JComponentHelper::getComponent( 'com_akeeba' );
		$params = new JParameter($component->params);
		$lastdate = $params->get('lastupdatecheck', '2009-04-02');

		jimport('joomla.utilities.date');
		$date = new JDate($lastdate);
		return $date;
	}

	/**
	 * Gets an object with the latest version information, taken from the update.ini data
	 * @return JObject|bool An object holding the data, or false on failure
	 */
	private function getLatestVersion($force = false)
	{
		$inidata = false;
		jimport('joomla.utilities.date');
		$curdate = new JDate();
		$lastdate = $this->lastUpdateCheck();
		$difference = ($curdate->toUnix(false) - $lastdate->toUnix(false)) / 3600;

		$inidata = $this->getUpdateINIcached();
		$cached = false;

		// Make sure we ask the server at most every 24 hrs (unless $force is true)
		if( ($difference < 24) && (!empty($inidata)) && (!$force) )
		{
			$cached = true;
			// Cached INI data is valid
		}
		// Prefer to use cURL if it exists and we don't have cached data
		elseif( $this->hascURL() )
		{
			$inidata = $this->getUpdateINIcURL();
		}
		// If cURL doesn't exist, or if it returned an error, try URL fopen() wrappers
		elseif( $this->hasURLfopen() )
		{
			$inidata = $this->getUpdateINIfopen();
		}

		// Make sure we do have INI data and not junk...
		if($inidata != false)
		{
			if( strpos($inidata, '; Live Update provision file') !== 0 )
			{
				$inidata = false;
			}
		}

		// If we have a valid update.ini, update the cache and read the version information
		if($inidata != false)
		{
			if(!$cached) $this->setUpdateINIcached($inidata);

			$parsed=AEUtilIni::parse_ini_file($inidata, false, true);

			// Determine status by parsing the version
			$version = $parsed['version'];
			if( preg_match('#^[0-9\.]*a[0-9\.]*#', $version) == 1 )
			{
				$status = 'alpha';
			} elseif( preg_match('#^[0-9\.]*b[0-9\.]*#', $version) == 1 )
			{
				$status = 'beta';
			} elseif( preg_match('#^[0-9\.]*$#', $version) == 1 )
			{
				$status = 'stable';
			} else {
				$status = 'svn';
			}

			// Special processing for the link in Akeeba Backup Professional
			$suffix = '';
			if(AKEEBA_PRO)
			{
				$component =& JComponentHelper::getComponent( 'com_akeeba' );
				$params = new JParameter($component->params);
				$username = $params->get('update_username', '');
				$password = $params->get('update_password', '');
				if( !empty($username) && !empty($password) )
				{
					$suffix = '?username='.urlencode($username).'&password='.urlencode($password).'&format=raw';
				}
			}

			$ret = new JObject;
			$ret->version	= $parsed['version'];
			$ret->status	= $status;
			$ret->reldate	= $parsed['date'];
			$ret->url		= $parsed['link'];
			$ret->urlsuffix	= $suffix;
			return $ret;
		}

		return false;
	}

	/**
	 * Retrieves the update.ini data using URL fopen() wrappers
	 * @return string|bool The update.ini contents, or FALSE on failure
	 */
	private function getUpdateINIfopen()
	{
		return @file_get_contents($this->update_url);
	}

	/**
	 * Retrieves the update.ini data using cURL extention calls
	 * @return string|bool The update.ini contents, or FALSE on failure
	 */
	private function getUpdateINIcURL()
	{
		$process = curl_init($this->update_url);
		curl_setopt($process, CURLOPT_HEADER, 0);
		// Pretend we are IE7, so that webservers play nice with us
		curl_setopt($process, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
		curl_setopt($process,CURLOPT_ENCODING , 'gzip');
		curl_setopt($process, CURLOPT_TIMEOUT, 5);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
		// The @ sign allows the next line to fail if open_basedir is set or if safe mode is enabled
		@curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
		@curl_setopt($process, CURLOPT_MAXREDIRS, 20);
		$inidata = curl_exec($process);
		curl_close($process);
		return $inidata;
	}

	private function getUpdateINIcached()
	{
		$component =& JComponentHelper::getComponent( 'com_akeeba' );
		$params = new JParameter($component->params);
		$inidata =  $params->get('updateini', "");
		return json_decode($inidata);
	}

	/**
	 * Caches the update.ini contents to database
	 * @param $inidata string The update.ini data
	 */
	private function setUpdateINIcached($inidata)
	{
		$component =& JComponentHelper::getComponent( 'com_akeeba' );
		$params = new JParameter($component->params);
		jimport('joomla.utilities.date');
		$date = new JDate();
		$params->set('updateini', json_encode($inidata) );
		$params->set('lastupdatecheck', $date->toUnix(false));

		$db =& JFactory::getDBO();
		$data = $params->toString();

		global $mainframe;
		if( !is_object($mainframe) )
		{
			// Joomla! 1.6
			$sql = 'UPDATE `#__extensions` SET `params` = '.$db->Quote($data).' WHERE '.
				"`element` = 'com_akeeba' AND `type` = 'component'";
		}
		else
		{
			// Joomla! 1.5
			$sql = 'UPDATE `#__components` SET `params` = '.$db->Quote($data).' WHERE '.
				"`option` = 'com_akeeba' AND `parent` = 0 AND `menuid` = 0";
		}

		$db->setQuery($sql);
		$db->query();
	}

	/**
	 * Is the Live Update supported on this server?
	 * @return bool
	 */
	public function isLiveUpdateSupported()
	{
		return $this->hasURLfopen() || $this->hascURL();
	}

	/**
	 * Searches for updates and returns an object containing update information
	 * @return JObject An object with members: supported, update_available,
	 * 				   current_version, current_date, latest_version, latest_date,
	 * 				   package_url
	 */
	public function &getUpdates($force = false)
	{
		jimport('joomla.utilities.date');
		$ret = new JObject();
		if(!$this->isLiveUpdateSupported())
		{
			$ret->supported = false;
			$ret->update_available = false;
			return $ret;
		}
		else
		{
			$ret->supported = true;
			$update = $this->getLatestVersion($force);

			// FIX 2.3: Fail gracefully if the update data couldn't be retrieved
			if(!is_object($update) || ($update === false))
			{
				$ret->supported = false;
				$ret->update_available = false;
				return $ret;
			}

			// Check if we need to upgrade, by release date
			jimport('joomla.utilities.date');
			AEPlatform::load_version_defines();
			$curdate = new JDate(AKEEBA_DATE);
			$curdate = $curdate->toUnix(false);

			$relobject = new JDate($update->reldate);
			$reldate = $relobject->toUnix(false);
			$ret->latest_date = $relobject->toFormat('%Y-%m-%d');

			$version = AKEEBA_VERSION;
			if( preg_match('#^[0-9\.]*a[0-9\.]*#', $version) == 1 )
			{
				$status = 'alpha';
			} elseif( preg_match('#^[0-9\.]*b[0-9\.]*#', $version) == 1 )
			{
				$status = 'beta';
			} elseif( preg_match('#^[0-9\.]*$#', $version) == 1 )
			{
				$status = 'stable';
			} else {
				$status = 'svn';
			}


			$ret->update_available = ($reldate > $curdate);
			$ret->current_version = AKEEBA_VERSION;
			$ret->current_date = AKEEBA_DATE;
			$ret->current_status = $status;
			$ret->latest_version = $update->version;
			$ret->status = $update->status;
			$ret->package_url = $update->url;
			$ret->package_url_suffix = $update->urlsuffix;
			return $ret;
		}
	}

	function downloadPackage($url, $target)
	{
		if(function_exists('curl_exec'))
		{
			// By default, try using cURL
			$process = curl_init($url);
			curl_setopt($process, CURLOPT_HEADER, 0);
			// Pretend we are IE7, so that webservers play nice with us
			curl_setopt($process, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
			curl_setopt($process, CURLOPT_TIMEOUT, 5);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
			// The @ sign allows the next line to fail if open_basedir is set or if safe mode is enabled
			@curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
			@curl_setopt($process, CURLOPT_MAXREDIRS, 20);
			$data = curl_exec($process);
			curl_close($process);

			if($data !== false)
			{
				jimport('joomla.filesystem.file');
				$result = JFile::write($target, $data);
				if($result !== false) {
					return basename($target);
				} else {
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			// Use Joomla!'s download helper
			jimport('joomla.installer.helper');
			return JInstallerHelper::downloadPackage($url, $target);
		}
	}
}