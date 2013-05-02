<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2010 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: factory.php 153 2010-06-04 17:28:34Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

// Define the log levels
if(!defined('_AE_LOG_NONE'))
{
	define("_AE_LOG_NONE",		0);
	define("_AE_LOG_ERROR",		1);
	define("_AE_LOG_WARNING",	2);
	define("_AE_LOG_INFO",		3);
	define("_AE_LOG_DEBUG",		4);
}

// Load the platform helper class first
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'platform'.DIRECTORY_SEPARATOR.AKEEBAPLATFORM.DIRECTORY_SEPARATOR.'platform.php';

/**
 * The Akeeba Engine Factory class
 * This class is reposnsible for instanciating all Akeeba classes
 */
class AEFactory {
	/** @var array A list of instanciated objects */
	private $objectlist = array();

	/** Private constructor makes sure we can't directly instanciate the class */
	private function __construct() {}

	/**
	 * Gets a single, internally used instance of the Factory
	 * @param string $serialized_data [optional] Serialized data to spawn the instance from
	 * @return AEFactory A reference to the unique Factory object instance
	 */
	protected static function &getInstance( $serialized_data = null ) {
		static $myInstance;
		if(!is_object($myInstance) || !is_null($serialized_data))
			if(!is_null($serialized_data))
				$myInstance = unserialize($serialized_data);
			else
				$myInstance = new self();
		return $myInstance;
	}

	/**
	 * Internal function which instanciates a class named $class_name.
	 * The autoloader
	 * @param string $class_name
	 * @return
	 */
	protected static function &getClassInstance($class_name) {
		$self = self::getInstance();
		if(!isset($self->objectlist[$class_name]))
		{
			$self->objectlist[$class_name] = new $class_name;
		}
		return $self->objectlist[$class_name];
	}

	/**
	 * Internal function which removes a class named $class_name
	 * @param string $class_name
	 */
	protected static function &unsetClassInstance($class_name) {
		$self = self::getInstance();
		if(isset($self->objectlist[$class_name]))
		{
			$self->objectlist[$class_name] = null;
			unset($self->objectlist[$class_name]);
		}
	}

	// ========================================================================
	// Public factory interface
	// ========================================================================

	/**
	 * Gets a serialized snapshot of the Factory for safekeeping (hibernate)
	 * @return string The serialized snapshot of the Factory
	 */
	public static function serialize() {
		// Call _onSerialize in all classes known to the factory
		if(!empty($self->objectlist))
		{
			foreach($self->objectlist as $class_name => $object)
			{
				$o =& $self->objectlist[$class_name];
				if(method_exists($o, '_onSerialize'))
				{
					call_user_method('_onSerialize', $o);
				}
			}
		}
		// Serialize the factory
		return serialize(self::getInstance());
	}

	/**
	 * Regenerates the full Factory state from a serialized snapshot (resume)
	 * @param string $serialized_data The serialized snapshot to resume from
	 */
	public static function unserialize($serialized_data) {
		self::getInstance($serialized_data);
	}

	/**
	 * Reset the internal factory state, freeing all previosuly created objects
	 */
	public static function nuke()
	{
		$self = self::getInstance();
		foreach($self->objectlist as $key => $object)
		{
			$self->objectlist[$key] = null;
		}
		$self->objectlist = array();
	}
	// ========================================================================
	// Akeeba classes
	// ========================================================================

	/**
	 * Returns an Akeeba Configuration object
	 * @return AEConfiguration The Akeeba Configuration object
	 */
	public static function &getConfiguration()
	{
		return self::getClassInstance('AEConfiguration');
	}

	/**
	 * Returns a statistics object, used to track current backup's progress
	 * @return AEUtilStatistics
	 */
	public static function &getStatistics()
	{
		return self::getClassInstance('AEUtilStatistics');
	}

	/**
	 * Returns the currently configured archiver engine
	 * @return AEAbstractArchiver
	 */
	public static function &getArchiverEngine()
	{
		static $class_name;
		if( empty($class_name) )
		{
			$registry = self::getConfiguration();
			$engine = $registry->get('akeeba.advanced.archiver_engine');
			$class_name = 'AEArchiver'.ucfirst($engine);
		}
		return self::getClassInstance($class_name);
	}

	/**
	 * Returns the currently configured dump engine
	 * @return AEAbstractDump
	 */
	public static function &getDumpEngine()
	{
		static $class_name;
		if( empty($class_name) )
		{
			$registry = self::getConfiguration();
			$engine = $registry->get('akeeba.advanced.dump_engine');
			$class_name = 'AEDump'.ucfirst($engine);
		}
		return self::getClassInstance($class_name);
	}

	/**
	 * Returns the filesystem scanner engine instance
	 * @return AEAbstractScan The scanner engine
	 */
	public static function &getScanEngine()
	{
		static $class_name;
		if( empty($class_name) )
		{
			$registry = self::getConfiguration();
			$engine = $registry->get('akeeba.advanced.scan_engine');
			$class_name = 'AEScan'.ucfirst($engine);
		}
		return self::getClassInstance($class_name);
	}

	public static function &getWriterEngine()
	{
		// @todo Implement AEFactory::getWriterEngine();
	}

	/**
	 * Returns the currently post-processing engine
	 * @return AEAbstractPostproc
	 */
	public static function &getPostprocEngine()
	{
		static $class_name;
		if( empty($class_name) )
		{
			$registry = self::getConfiguration();
			$engine = $registry->get('akeeba.advanced.proc_engine');
			$class_name = 'AEPostproc'.ucfirst($engine);
		}
		return self::getClassInstance($class_name);
	}

	/**
	 * Returns an instance of the Filters feature class
	 * @return AECoreFilters	The Filters feature class' object instance
	 */
	public static function &getFilters()
	{
		return self::getClassInstance('AECoreFilters');
	}

	/**
	 * Returns an instance of the specified filter group class. Do note that it does not
	 * work with platform filter classes. They are handled internally by AECoreFilters.
	 * @param	string	$filter_name	The filter class to load, without AEFilter prefix
	 * @return	AEAbstractFilter	The filter class' object instance
	 */
	public static function &getFilterObject($filter_name)
	{
		return self::getClassInstance('AEFilter'.ucfirst($filter_name));
	}

	/**
	 * Loads an engine domain class and returns its associated object
	 * @param	string	$domain_name	The name of the domain, e.g. installer for AECoreDomainInstaller
	 * @return AEAbstractPart
	 */
	public static function &getDomainObject($domain_name)
	{
		return self::getClassInstance('AECoreDomain'.ucfirst($domain_name));
	}

	/**
	 * Returns a database connection object. It's an alias of AECoreDatabase::getDatabase()
	 * @param array $options Options to use when instanciating the database connection
	 * @return AEAbstractDriver
	 */
	public static function &getDatabase($options = null)
	{
		if(is_null($options))
		{
			$options = AEPlatform::get_platform_database_options();
		}
		return AECoreDatabase::getDatabase($options);
	}

	/**
	 * Returns a database connection object. It's an alias of AECoreDatabase::getDatabase()
	 * @param array $options Options to use when instanciating the database connection
	 * @return AEAbstractDriver
	 */
	public static function unsetDatabase($options = null)
	{
		if(is_null($options))
		{
			$options = AEPlatform::get_platform_database_options();
		}
		$db =& AECoreDatabase::getDatabase($options);
		$db->close();
		AECoreDatabase::unsetDatabase($options);
	}

	/**
	 * Get the a reference to the Akeeba Engine's timer
	 * @return AECoreTimer
	 */
	public static function &getTimer()
	{
		return self::getClassInstance('AECoreTimer');
	}

	/**
	 * Get a reference to Akeeba Engine's heart, Kettenrad
	 * @return AECoreKettenrad
	 */
	public static function &getKettenrad()
	{
		return self::getClassInstance('AECoreKettenrad');
	}

	// ========================================================================
	// Handy functions
	// ========================================================================

	public static function getAkeebaRoot()
	{
		static $root = null;
		if(empty($root)) $root = dirname(__FILE__);
		return $root;
	}
}

// Make sure the class autoloader is loaded
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'autoloader.php';

// register the autoloader
AEPlatform::register_autoloader();

// Define and register the timeout trap
function AkeebaTimeoutTrap()
{
	if( connection_status() >= 2 ) {
		AEUtilLogger::WriteLog(_AE_LOG_ERROR, 'Akeeba has timed out' );
	}
}
register_shutdown_function("AkeebaTimeoutTrap");

# = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = #
#                                          D E P R E C A T E D                                  #
# = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = # = #
/**
 * Tries to import a file from the Akeeba Joomla! back-end
 * @param string $className Class name in dot format, e.g. 'helpers.config'
 * @param bool $searchAllBackend When true, the root is the Joomla! component's administrator part
 * @deprecated
 */
function akimport( $className, $searchAllBackend = false )
{
		$parts = explode('.', $className); // Break apart at dots
		$akeebaClassName = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_akeeba'.DS.( $searchAllBackend ? '' : 'akeeba'.DS );
		$akeebaClassName .= implode(DS,$parts); // Glue the pieces with the directory separator
		$akeebaClassName .= '.php';

		if(!file_exists($akeebaClassName))
		{
			// Compatibility with pre-3.0 versions -- Will eventually go away!
			$newClassName = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_akeeba'.DS.( $searchAllBackend ? '' : 'classes'.DS );
			$newClassName .= implode(DS,$parts); // Glue the pieces with the directory separator
			$newClassName .= '.php';

			if(!file_exists($newClassName))
			{
				JError::raiseError(500,'Inexistent Akeeba Class '.$className,'Akeeba class ' . $className . ' does not exist.' );
			}
			else
			{
				require_once($newClassName);
			}
		}
		else
		{
			require_once($akeebaClassName);
		}

}