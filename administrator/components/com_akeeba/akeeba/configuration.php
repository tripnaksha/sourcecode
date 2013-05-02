<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: configuration.php 188 2010-07-18 14:15:12Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * The Akeeba Engine configuration registry class
 */
class AEConfiguration {
	/** @var string Default NameSpace */
	private $defaultNameSpace = 'global';

	/** @var array Array keys which may contain stock directory definitions */
	private $directory_containing_keys = array(
		'akeeba.basic.output_directory',
		'akeeba.basic.temporary_directory'
	);

	/** @var array The registry data */
	private $registry = array();
	
	/** @var int The currently loaded profile */
	public $activeProfile = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Assisted Singleton pattern
		if(function_exists('debug_backtrace'))
		{
			$caller=debug_backtrace();
			$caller = $caller[1];
			if($caller['class'] != 'AEFactory') trigger_error("You can't create a direct descendant of ".__CLASS__, E_USER_ERROR);
		}

		// Create the default namespace
		$this->makeNameSpace($this->defaultNameSpace);

		// Create a default configuration
		$this->reset();
	}

	/**
	 * Create a namespace
	 * @param	string	$namespace	Name of the namespace to create
	 */
	public function makeNameSpace($namespace)
	{
		$this->registry[$namespace] = array('data' => new stdClass());
	}

	/**
	 * Get the list of namespaces
	 * @return	array	List of namespaces
	 */
	public function getNameSpaces()
	{
		return array_keys($this->registry);
	}

	/**
	 * Get a registry value
	 * @param	string	$regpath	Registry path (e.g. global.directory.temporary)
	 * @param	mixed	$default	Optional default value
	 * @param	bool	$process_special_vars Optional. If true (default), it processes special variables, e.g. [SITEROOT] in folder names
	 * @return	mixed	Value of entry or null
	 */
	public function get($regpath, $default=null, $process_special_vars = true)
	{
		// Cache the platform-specific stock directories
		static $stock_directories = array();
		if(empty($stock_directories))
		{
			$stock_directories = AEPlatform::get_stock_directories();
		}

		$result = $default;

		// Explode the registry path into an array
		if ($nodes = explode('.', $regpath))
		{
			// Get the namespace
			$count = count($nodes);
			if ($count < 2) {
				$namespace	= $this->defaultNameSpace;
				$nodes[1]	= $nodes[0];
			} else {
				$namespace = $nodes[0];
			}

			if (isset($this->registry[$namespace])) {
				$ns = $this->registry[$namespace]['data'];
				$pathNodes = $count - 1;

				for ($i = 1; $i < $pathNodes; $i ++) {
					if((isset($ns->$nodes[$i]))) $ns =& $ns->$nodes[$i];
				}

				if(isset($ns->$nodes[$i])) {
					$result = $ns->$nodes[$i];
				}
			}
		}

		// Post-process certain directory-containing variables
		if( $process_special_vars && in_array($regpath, $this->directory_containing_keys) )
		{
			if(!empty($stock_directories))
			{
				foreach($stock_directories as $tag => $content)
				{
					$result = str_replace($tag, $content, $result);
				}
			}
		}

		return $result;
	}

	/**
	 * Set a registry value
	 * @param	string	$regpath 	Registry Path (e.g. global.directory.temporary)
	 * @param 	mixed	$value		Value of entry
	 * @param	bool	$process_special_vars Optional. If true (default), it processes special variables, e.g. [SITEROOT] in folder names
	 * @return 	mixed	Value of old value or boolean false if operation failed
	 */
	public function set($regpath, $value, $process_special_vars = true)
	{
		// Cache the platform-specific stock directories
		static $stock_directories = array();
		if(empty($stock_directories))
		{
			$stock_directories = AEPlatform::get_stock_directories();
		}

		// Explode the registry path into an array
		$nodes = explode('.', $regpath);

		// Get the namespace
		$count = count($nodes);

		if ($count < 2) {
			$namespace = $this->defaultNameSpace;
		} else {
			$namespace = array_shift($nodes);
			$count--;
		}

		if (!isset($this->registry[$namespace])) {
			$this->makeNameSpace($namespace);
		}

		$ns = $this->registry[$namespace]['data'];

		$pathNodes = $count - 1;

		if ($pathNodes < 0) {
			$pathNodes = 0;
		}

		for ($i = 0; $i < $pathNodes; $i ++)
		{
			// If any node along the registry path does not exist, create it
			if (!isset($ns->$nodes[$i])) {
				$ns->$nodes[$i] = new stdClass();
			}
			$ns = $ns->$nodes[$i];
		}

		// Set the new values
		if(is_string($value))
		{
			if(substr($value,0,10) == '###json###')
			{
				$value = json_decode(substr($value,10));
			}
		}

		// Post-process certain directory-containing variables
		if( $process_special_vars && in_array($regpath, $this->directory_containing_keys) )
		{
			if(!empty($stock_directories))
			{
				$data = $value;
				foreach($stock_directories as $tag => $content)
				{
					$data = str_replace($tag, $content, $data);
				}
				$ns->$nodes[$i] = $data;
				return $ns->$nodes[$i];
			}
		}

		// This is executed if any of the previous two if's is false

		$ns->$nodes[$i] = $value;
		return $ns->$nodes[$i];
	}

	/**
	 * Resets the registry to the default values
	 */
	public function reset()
	{
		// Load the Akeeba Engine INI files
		$ds = DIRECTORY_SEPARATOR;
		$root_path = dirname(__FILE__);
		$plugin_path = $root_path.$ds.'plugins';
		$paths = array(
			$root_path.$ds.'core',
			$root_path.$ds.'engines'.$ds.'archiver',
			$root_path.$ds.'engines'.$ds.'dump',
			$root_path.$ds.'engines'.$ds.'scan',
			$root_path.$ds.'engines'.$ds.'writer',
			$root_path.$ds.'engines'.$ds.'proc',
			$plugin_path.$ds.'engines'.$ds.'archiver',
			$plugin_path.$ds.'engines'.$ds.'dump',
			$plugin_path.$ds.'engines'.$ds.'scan',
			$plugin_path.$ds.'engines'.$ds.'writer',
			$plugin_path.$ds.'engines'.$ds.'proc'
		);

		foreach($paths as $root)
		{
			$handle = @opendir($root);
			if($handle !== false)
			{
				while( false !== ($file = @readdir($handle)) )
				{
					if(substr($file,-4) == '.ini')
					{
						$this->mergeEngineINI($root.DIRECTORY_SEPARATOR.$file);
					}
				}
				closedir($handle);
			}
		}

	}

	/**
	 * Merges an associative array of key/value pairs into the registry.
	 * If noOverride is set, only non set or null values will be applied.
	 * @param	array	$array	An associative array. Its keys are registry paths.
	 * @param	bool	$noOverride	[optional] Do not override pre-set values.
	 * @param	bool	$process_special_vars Optional. If true (default), it processes special variables, e.g. [SITEROOT] in folder names
	 */
	public function mergeArray($array, $noOverride = false, $process_special_vars = true)
	{
		if(!$noOverride)
		{
			foreach($array as $key => $value)
			{
				$this->set($key, $value, $process_special_vars);
			}
		}
		else
		{
			foreach($array as $key => $value)
			{
				if( is_null($this->get($key, null)) )
					$this->set($key, $value, $process_special_vars);
			}
		}
	}

	/**
	 * Merges an INI-style file into the registry. Its sections are registry paths,
	 * keys are appended to the section-defined paths and then set equal to the
	 * values. If noOverride is set, only non set or null values will be applied.
	 * Sections beginning with an underscore will be ignored.
	 * @param	string	$inifile	The full path to the INI file to load
	 * @param	bool	$noOverride	[optional] Do not override pre-set values.
	 * @return	bool	True on success
	 */
	public function mergeINI($inifile, $noOverride = false)
	{
		if(!file_exists($inifile)) return false;
		$inidata = AEUtilINI::parse_ini_file($inifile, true);
		foreach($inidata as $rootkey => $rootvalue)
		{
			if(!is_array($rootvalue))
			{
				if(!$noOverride)
				{
					$this->set($rootkey, $rootvalue);
				}
				elseif( is_null($this->get($rootkey, null)) )
				{
					$this->set($rootkey, $rootvalue);
				}
			}
			elseif( substr($rootkey,0,1) != '_' )
			{
				foreach($rootvalue as $key => $value)
				{
					if(!$noOverride)
					{
						$this->set($rootkey.'.'.$key, $rootvalue);
					}
					elseif( is_null($this->get($rootkey.'.'.$key, null)) )
					{
						$this->set($rootkey.'.'.$key, $rootvalue);
					}
				}
			}
		}
		return true;
	}

	/**
	 * Merges an engine INI file to the configuration. Each section defines a full
	 * registry path (section.subsection.key). It searches each section for the
	 * key named "default" and merges its value to the configuration. The other keys
	 * are simply ignored.
	 * @param string $inifile The absolute path to an INI file
	 * @param bool $noOverride [optional] If true, values from the INI will not override the configuration
	 * @return bool True on success
	 */
	public function mergeEngineINI($inifile, $noOverride = false)
	{
		if(!file_exists($inifile)) return false;
		$inidata = AEUtilINI::parse_ini_file($inifile, true);
		foreach($inidata as $section => $nodes)
		{
			if(is_array($nodes))
			{
				if( substr($section,0,1) != '_' )
				{
					if(isset($nodes['default']))
					{
						if(!$noOverride)
						{
							$this->set($section, $nodes['default']);
						}
						elseif( is_null($this->get($section, null)) )
						{
							$this->set($section, $nodes['default']);
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * Exports the current registry snapshot as an INI file. Each namespace is
	 * placed in a section of its own.
	 * @param	bool	$dump_global Set to true to dump the "global" namespace, false to dump everything EXCEPT the [global] namespace
	 * @return	string	INI representation of the registry
	 */
	public function exportAsINI()
	{
		$inidata = '';
		$namespaces = $this->getNameSpaces();
		foreach($namespaces as $namespace)
		{
			$inidata .= "[$namespace]\n";
			$ns = $this->registry[$namespace]['data'];
			$inidata .= $this->dumpObject($ns);
		}
		return $inidata;
	}

	/**
	 * Internal function to dump an object as INI-formatted data
	 * @param object $object
	 * @param object $prefix [optional]
	 * @return
	 */
	private function dumpObject($object, $prefix = '')
	{
		$data = '';
		$vars = get_object_vars($object);
		foreach( $vars as $key => $value )
		{
			if(!is_object($value))
			{
				if(is_array($value))
				{
					$value = '###json###'.json_encode($value);
				}
				$data .= (empty($prefix) ? '' : $prefix.'.').$key.
					'="'.addcslashes($value,"\n\r\t\"")."\"\n";
			}
			else
			{
				$data .= $this->dumpObject($value, (empty($prefix) ? '' : $prefix.'.').$key );
			}
		}
		return $data;
	}
}
?>