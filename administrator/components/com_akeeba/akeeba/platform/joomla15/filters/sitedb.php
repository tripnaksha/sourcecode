<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2010 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: sitedb.php 158 2010-06-10 08:46:49Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Add site's main database to the backup set.
 */
class AEFilterPlatformSitedb extends AEAbstractFilter
{
	public function __construct()
	{
		// This is a directory inclusion filter.
		$this->object	= 'db';
		$this->subtype	= 'inclusion';
		$this->method	= 'direct';
		$this->filter_name = 'PlatformSitedb';

		// Add a new record for the core Joomla! database
		// Get core database options
		$options = AEPlatform::get_platform_database_options();

		$host = $options['host'];
		$port	= NULL;
		$socket	= NULL;
		$targetSlot = substr( strstr( $host, ":" ), 1 );
		if (!empty( $targetSlot )) {
			// Get the port number or socket name
			if (is_numeric( $targetSlot ))
				$port	= $targetSlot;
			else
				$socket	= $targetSlot;

			// Extract the host name only
			$host = substr( $host, 0, strlen( $host ) - (strlen( $targetSlot ) + 1) );
			// This will take care of the following notation: ":3306"
			if($host == '')
				$host = 'localhost';
		}

		// This is the format of the database inclusion filters
		$entry = array(
			'host' => $host,
			'port' => is_null($socket) ? (is_null($port) ? '' : $port) : $socket,
			'username' => $options['user'],
			'password' => $options['password'],
			'database' => $options['database'],
			'prefix' => $options['prefix'],
			'dumpFile' => 'joomla.sql',
			'driver' => AEPlatform::get_default_database_driver(true)
		);


		// We take advantage of the filter class magic to inject our custom filters
		$configuration =& AEFactory::getConfiguration();

		$this->filter_data['[SITEDB]'] = $entry;

		parent::__construct();
	}
}