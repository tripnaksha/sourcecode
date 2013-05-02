<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2010 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: directftp.php 124 2010-04-27 16:30:56Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Direct Transfer Over FTP archiver class
 *
 * Transfers the files to a remote FTP server instead of putting them in
 * an archive
 *
 */
class AEArchiverDirectftp extends AEAbstractArchiver
{
	/** @var resource FTP resource handle */
	private $_ftphandle;

	/** @var string FTP hostname */
	private $_host;

	/** @var string FTP port */
	private $_port;

	/** @var string FTP username */
	private $_user;

	/** @var string FTP password */
	private $_pass;

	/** @var bool Should we use FTP over SSL? */
	private $_usessl;

	/** @var bool Should we use passive FTP? */
	private $_passive;

	/** @var string FTP initial directory */
	private $_initdir;

	/** @var string Current remote directory, including the remote directory string */
	private $_currentdir;

	/** @var bool Could we connect to the server? */
	public $connect_ok = false;

	// ------------------------------------------------------------------------
	// Implementation of abstract methods
	// ------------------------------------------------------------------------

	/**
	 * Initialises the archiver class, seeding the remote installation
	 * from an existent installer's JPA archive.
	 *
	 * @param string $sourceJPAPath Absolute path to an installer's JPA archive
	 * @param string $targetArchivePath Absolute path to the generated archive (ignored in this class)
	 * @param array $options A named key array of options (optional)
	 * @access public
	 */
	public function initialize( $targetArchivePath, $options = array() )
	{
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: new instance");

		$registry =& AEFactory::getConfiguration();

		$this->_host = $registry->get('engine.archiver.directftp.host','');
		$this->_port = $registry->get('engine.archiver.directftp.port','21');
		$this->_user = $registry->get('engine.archiver.directftp.user','');
		$this->_pass = $registry->get('engine.archiver.directftp.pass','');
		$this->_initdir = $registry->get('engine.archiver.directftp.initial_directory','');
		$this->_usessl = $registry->get('engine.archiver.directftp.ftps', false);
		$this->_passive = $registry->get('engine.archiver.directftp.passive_mode', true);

		if(isset($options['host'])) $this->_host = $options['host'];
		if(isset($options['port'])) $this->_port = $options['port'];
		if(isset($options['user'])) $this->_user = $options['user'];
		if(isset($options['pass'])) $this->_pass = $options['pass'];
		if(isset($options['initdir'])) $this->_initdir = $options['initdir'];
		if(isset($options['usessl'])) $this->_usessl = $options['usessl'];
		if(isset($options['passive'])) $this->_passive = $options['passive'];
		$this->connect_ok = $this->_connectFTP();
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: FTP connection status: " . ($this->connect_ok ? 'success' : 'FAIL') );
	}

	public function finalize()
	{
		// Really does nothing...
	}

	/**
	 * Returns a string with the extension (including the dot) of the files produced
	 * by this class.
	 * @return string
	 */
	public function getExtension()
	{
		return '';
	}


	/**
	 * The most basic file transaction: add a single entry (file or directory) to
	 * the archive.
	 *
	 * @param bool $isVirtual If true, the next parameter contains file data instead of a file name
	 * @param string $sourceNameOrData Absolute file name to read data from or the file data itself is $isVirtual is true
	 * @param string $targetName The (relative) file name under which to store the file in the archive
	 * @return True on success, false otherwise
	 */
	protected function _addFile( $isVirtual, &$sourceNameOrData, $targetName )
	{
		// Are we connected to a server?
		if(!is_resource($this->_ftphandle))
		{
			if(!$this->_connectFTP()) return false;
		}

		// See if it's a directory
		$isDir = $isVirtual ? false : is_dir($sourceNameOrData);

		if($isDir)
		{
			// Just try to create the remote directory
			return $this->_makeDirectory($targetName);
		}
		else
		{
			// We have a file we need to upload
			if($isVirtual)
			{
				// Create a temporary file, upload, rename it
				$tempFileName = AEUtilTempfiles::createRegisterTempFile();
				if(function_exists('file_put_contents'))
				{
					// Easy writing using file_put_contents
					if(@file_put_contents($tempFileName, $sourceNameOrData) === false)
					{
						$this->setError('Could not store virtual file '.$targetName.' to '.$tempFileName.' using file_put_contents() before uploading.');
						return false;
					}
				}
				else
				{
					// The long way, using fopen() and fwrite()
					$fp = @fopen($tempFileName, 'wb');
					if($fp === false)
					{
						$this->setError('Could not store virtual file '.$targetName.' to '.$tempFileName.' using fopen() before uploading.');
						return false;
					}
					else
					{
						$result = @fwrite($fp, $sourceNameOrData);
						if($result === false) {
							$this->setError('Could not store virtual file '.$targetName.' to '.$tempFileName.' using fwrite() before uploading.');
							return false;
						}
						@fclose($fp);
					}
				}
				// Upload the temporary file under the final name
				$res = $this->_upload($tempFileName, $targetName);
				// Remove the temporary file
				AEUtilTempfiles::unregisterAndDeleteTempFile($tempFileName, true);
				return $res;
			}
			else
			{
				// Upload a file
				return $this->_upload($sourceNameOrData, $targetName);
			}
		}
	}

	// ------------------------------------------------------------------------
	// Private class-specific methods
	// ------------------------------------------------------------------------

	/**
	 * "Magic" function called just before serialization of the object. Disconnects
	 * from the FTP server and allows PHP to serialize like normal.
	 * @return array The variables to serialize
	 */
	public function _onSerialize()
	{
		if(is_resource($this->_ftphandle))
		{
			@ftp_close($this->_ftphandle);
		}

		$this->_ftphandle = null;
		$this->_currentdir = null;

		return array_keys(get_object_vars($this));
	}

	/**
	 * Tries to connect to the remote FTP server and change into the initial directory
	 * @return bool True is connection successful, false otherwise
	 */
	private function _connectFTP()
	{
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, 'Connecting to remote FTP');
		// Connect to the FTP server
		if($this->_usessl)
		{
			if(function_exists('ftp_ssl_connect'))
			{
				$this->_ftphandle = @ftp_ssl_connect($this->_host, $this->_port);
			}
			else
			{
				$this->_ftphandle = false;
			}
		}
		else
		{
			$this->_ftphandle = @ftp_connect($this->_host, $this->_port);
		}

		if($this->_ftphandle === false)
		{
			$this->setError('Could not connect to remote FTP server');
			return false;
		}

		// Login
		if(!@ftp_login($this->_ftphandle, $this->_user, $this->_pass))
		{
			$this->setError('Invalid username/password for the remote FTP server');
			return false;
		}

		// Change to initial directory
		if(!@ftp_chdir($this->_ftphandle, $this->_initdir))
		{
			$this->setError('Invalid initial directory for the remote FTP server');
			return false;
		}

		$this->_currentdir = $this->_initdir;

		@ftp_pasv($this->_ftphandle, $this->_passive);
		return true;
	}

	/**
	 * Changes to the requested directory in the remote server. You give only the
	 * path relative to the initial directory and it does all the rest by itself,
	 * including doing nothing if the remote directory is the one we want. If the
	 * directory doesn't exist, it creates it.
	 * @param $dir string The (realtive) remote directory
	 * @return bool True if successful, false otherwise.
	 */
	private function _ftp_chdir($dir)
	{
		// Calculate "real" (absolute) FTP path
		$realdir = substr($this->_initdir, -1) == '/' ? substr($this->_initdir, 0, strlen($this->_initdir) - 1) : $this->_initdir;
		$realdir .= '/'.$dir;
		$realdir = substr($realdir, 0, 1) == '/' ? $realdir : '/'.$realdir;

		if($this->_currentdir == $realdir)
		{
			// Already there, do nothing
			return true;
		}

		$result = @ftp_chdir($this->_ftphandle, $realdir);
		if($result === false)
		{
			// The directory doesn't exist, let's try to create it...
			if(!$this->_makeDirectory($dir)) return false;
			// After creating it, change into it
			@ftp_chdir($this->_ftphandle, $realdir);
		}

		// Update the private "current remote directory" variable
		$this->_currentdir = $realdir;
		return true;
	}

	private function _makeDirectory( $dir )
	{
		$alldirs = explode('/', $dir);
		$previousDir = substr($this->_initdir, -1) == '/' ? substr($this->_initdir, 0, strlen($this->_initdir) - 1) : $this->_initdir;
		$previousDir = substr($previousDir, 0, 1) == '/' ? $previousDir : '/'.$previousDir;

		foreach($alldirs as $curdir)
		{
			$check = $previousDir.'/'.$curdir;
			if(!@ftp_chdir($this->_ftphandle, $check) )
			{
				if(@ftp_mkdir($this->_ftphandle, $check) === false)
				{
					$this->setError('Could not create directory '.$check);
					return false;
				}
				@ftp_chmod($this->_ftphandle, 0755, $check);
			}
			$previousDir = $check;
		}

		return true;
	}

	/**
	 * Uploads a file to the remote server
	 * @param $sourceName string The absolute path to the source local file
	 * @param $targetName string The relative path to the targer remote file
	 * @return bool True if successful
	 */
	private function _upload($sourceName, $targetName)
	{
		// Try to change into the remote directory, possibly creating it if it doesn't exist
		$dir = dirname($targetName);
		if(!$this->_ftp_chdir($dir))
		{
			return false;
		}

		// Upload
		$realdir = substr($this->_initdir, -1) == '/' ? substr($this->_initdir, 0, strlen($this->_initdir) - 1) : $this->_initdir;
		$realdir .= '/'.$dir;
		$realdir = substr($realdir, 0, 1) == '/' ? $realdir : '/'.$realdir;
		$realname = $realdir.'/'.basename($targetName);
		$res = @ftp_put($this->_ftphandle, $realname, $sourceName, FTP_BINARY);

		if(!$res)
		{
			// If the file was unreadable, just skip it...
			if(is_readable($sourceName))
			{
				$this->setError('Uploading '.$targetName.' has failed.');
				return false;
			} else {
				$this->setWarning( 'Uploading '.$targetName.' has failed because the file is unreadable.');
				return true;
			}
		}
		else
		{
			@ftp_chmod($this->_ftphandle, 0755, $realname);
			return true;
		}
	}
}