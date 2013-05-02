<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: json.php 239 2010-08-29 17:18:01Z nikosdion $
 * @since 3.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// JSON API version number
define('AKEEBA_JSON_API_VERSION', '300');

// Load framework base classes
jimport('joomla.application.component.model');

include_once JPATH_COMPONENT_ADMINISTRATOR.DS.'plugins'.DS.'helpers'.DS.'encrypt.php';

class AkeebaModelJson extends JModel
{
	const	STATUS_OK					= 200;	// Normal reply
	const	STATUS_NOT_AUTH				= 401;	// Invalid credentials
	const	STATUS_NOT_ALLOWED			= 403;	// Not enough privileges
	const	STATUS_NOT_FOUND			= 404;  // Requested resource not found
	const	STATUS_INVALID_METHOD		= 405;	// Unknown JSON method
	const	STATUS_ERROR				= 500;	// An error occured
	const	STATUS_NOT_IMPLEMENTED		= 501;	// Not implemented feature
	const	STATUS_NOT_AVAILABLE		= 503;	// Remote service not activated

	const	ENCAPSULATION_RAW			= 1;	// Data in plain-text JSON
	const	ENCAPSULATION_AESCTR128		= 2;	// Data in AES-128 stream (CTR) mode encrypted JSON
	const	ENCAPSULATION_AESCTR256		= 3;	// Data in AES-256 stream (CTR) mode encrypted JSON
	const	ENCAPSULATION_AESCBC128		= 4;	// Data in AES-128 standard (CBC) mode encrypted JSON
	const	ENCAPSULATION_AESCBC256		= 5;	// Data in AES-256 standard (CBC) mode encrypted JSON

	private	$json_errors = array(
			JSON_ERROR_NONE => 'No error has occurred (probably emtpy data passed)',
			JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
			JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
			JSON_ERROR_SYNTAX => 'Syntax error'
			);

	/** @var int The status code */
	private	$status = 200;
	/** @var int Data encapsulation format */
	private $encapsulation = 1;
	/** @var mixed Any data to be returned to the caller */
	private $data = '';
	/** @var string A password passed to us by the caller */
	private $password = null;

	public function execute($json)
	{
		// Check if we're activated
		$enabled = AEPlatform::get_platform_configuration_option('frontend_enable', 0);
		if(!$enabled)
		{
			$this->data = 'Access denied';
			$this->status = self::STATUS_NOT_AVAILABLE;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return $this->getResponse();
		}

		// Try to JSON-decode the request's input first
		$request = @json_decode($json, false);
		if(is_null($request))
		{
			// Could not decode JSON
			$this->data = 'JSON decoding error: '.$this->json_errors[json_last_error()];
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return $this->getResponse();
		}

		// Decode the request body
		// Request format: {encapsulation, body{ [key], [challenge], method, [data] }} or {[challenge], method, [data]}
		if( isset($request->encapsulation) && isset($request->body) )
		{
			if(!class_exists('AkeebaHelperEncrypt') && !($request->encapsulation == self::ENCAPSULATION_RAW))
			{
				// Encrypted request found, but there is no encryption class available!
				$this->data = 'This server does not support encrypted requests';
				$this->status = self::STATUS_NOT_AVAILABLE;
				$this->encapsulation = self::ENCAPSULATION_RAW;
				return $this->getResponse();
			}

			// Fully specified request
			switch( $request->encapsulation )
			{
				case self::ENCAPSULATION_AESCBC128:
					if(!isset($body))
					{
						$request->body = base64_decode($request->body);
						$body = AkeebaHelperEncrypt::AESDecryptCBC($request->body, $this->serverKey(), 128);
					}

				case self::ENCAPSULATION_AESCBC256:
					if(!isset($body))
					{
						$request->body = base64_decode($request->body);
						$body = AkeebaHelperEncrypt::AESDecryptCBC($request->body, $this->serverKey(), 256);
					}

				case self::ENCAPSULATION_AESCTR128:
					if(!isset($body))
					{
						$body = AkeebaHelperEncrypt::AESDecryptCtr($request->body, $this->serverKey(), 128);
					}

				case self::ENCAPSULATION_AESCTR256:
					if(!isset($body))
					{
						$body = AkeebaHelperEncrypt::AESDecryptCtr($request->body, $this->serverKey(), 256);
					}

					$request->body = json_decode($body, false);
					if(is_null($request->body))
					{
						// Decryption failed. The user is an imposter! Go away, hacker!
						$this->data = 'Authentication failed';
						$this->status = self::STATUS_NOT_AUTH;
						$this->encapsulation = self::ENCAPSULATION_RAW;
						return $this->getResponse();
					}
					break;

				case self::ENCAPSULATION_RAW:
					$request->body = json_decode($request->body, false);
					break;
			}
		}
		elseif( isset($request->body) )
		{
			// Partially specified request, assume RAW encapsulation
			$request->encapsulation = self::ENCAPSULATION_RAW;
			$request->body = json_decode($request->body);
		}
		else
		{
			// Legacy request
			$legacyRequest = clone $request;
			$request = (object) array( 'encapsulation' => self::ENCAPSULATION_RAW, 'body' => null );
			$request->body = json_decode($legacyRequest);
			unset($legacyRequest);
		}

		// Authenticate the user. Do note that if an encrypted request was made, we can safely assume that
		// the user is authenticated (he already knows the server key!)
		if($request->encapsulation == self::ENCAPSULATION_RAW)
		{
			$authenticated = false;
			if(isset($request->body->challenge))
			{
				list($challenge, $check) = explode(':', $request->body->challenge);
				$crosscheck = strtolower(md5($challenge.$this->serverKey()));
				$authenticated = ($crosscheck == $check);
			}
			if(!$authenticated)
			{
				// If the challenge was missing or it was wrong, don't let him go any further
				$this->data = 'Invalid login credentials';
				$this->status = self::STATUS_NOT_AUTH;
				$this->encapsulation = self::ENCAPSULATION_RAW;
				return $this->getResponse();
			}
		}

		// Replicate the encapsulation preferences of the client for our own output
		$this->encapsulation = $request->encapsulation;

		// Store the client-specified key, or use the server key if none specified and the request
		// came encrypted.
		$this->password = isset($request->body->key) ? $request->body->key : null;
		if(is_null($request->body->key) && ($request->encapsulation != self::ENCAPSULATION_RAW) )
		{
			$this->password = $this->serverKey();
		}

		// Does the specified method exist?
		$method_exists = false;
		$method_name = '';
		if(isset($request->body->method))
		{
			$method_name = ucfirst($request->body->method);
			$method_exists = method_exists($this, '_api'.$method_name );
		}
		if(!$method_exists)
		{
			// The requested method doesn't exist. Oops!
			$this->data = "Invalid method $method_name";
			$this->status = self::STATUS_INVALID_METHOD;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return $this->getResponse();
		}

		// Run the method
		$params = array();
		if(isset($request->body->data)) $params = (array)$request->body->data;
		$this->data = call_user_func( array($this, '_api'.$method_name) , $params);

		return $this->getResponse();
	}

	/**
	 * Packages the response to a JSON-encoded object, optionally encrypting the
	 * data part with a caller-supplied password.
	 * @return string The JSON-encoded response
	 */
	private function getResponse()
	{
		// Initialize the response
		$response = array(
			'encapsulation'	=> $this->encapsulation,
			'body'		=> array(
				'status'		=> $this->status,
				'data'			=> null
			)
		);

		$data = json_encode($this->data);

		if(empty($this->password)) $this->encapsulation = self::ENCAPSULATION_RAW;

		switch($this->encapsulation)
		{
			case self::ENCAPSULATION_RAW:
				break;

			case self::ENCAPSULATION_AESCTR128:
				$data = AkeebaHelperEncrypt::AESEncryptCtr($data, $this->password, 128);
				break;

			case self::ENCAPSULATION_AESCTR128:
				$data = AkeebaHelperEncrypt::AESEncryptCtr($data, $this->password, 256);
				break;

			case self::ENCAPSULATION_AESCBC128:
				$data = base64_encode(AkeebaHelperEncrypt::AESEncryptCBC($data, $this->password, 128));
				break;

			case self::ENCAPSULATION_AESCBC256:
				$data = base64_encode(AkeebaHelperEncrypt::AESEncryptCBC($data, $this->password, 256));
				break;
		}

		$response['body']['data'] = $data;

		return '###' . json_encode($response) . '###';
	}

	private function serverKey()
	{
		static $key = null;

		if(is_null($key))
		{
			$key = AEPlatform::get_platform_configuration_option('frontend_secret_word', '');
		}

		return $key;
	}

	private function _apiGetVersion()
	{
		return (object)array(
			'api'		=> AKEEBA_JSON_API_VERSION,
			'component'	=> AKEEBA_VERSION,
			'date'		=> AKEEBA_DATE
		);
	}

	private function _apiGetProfiles()
	{
		require_once JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_akeeba'.DS.'models'.DS.'profiles.php';
		$model = new AkeebaModelProfiles();
		$profiles = $model->getProfilesList(true);
		$ret = array();

		if(count($profiles))
		{
			foreach($profiles as $profile)
			{
				$temp = new stdClass();
				$temp->id = $profile->id;
				$temp->name = $profile->description;
				$ret[] = $temp;
			}
		}

		return $ret;
	}

	private function _apiStartBackup($config)
	{
		$defConfig = array(
			'profile'		=> 1,
			'description'	=> '',
			'comment'		=> '',
			'tag'			=> 'remote'
		);
		$config = array_merge($defConfig, $config);
		extract($config);

		// Set the profile
		$profile = JRequest::getInt('profile',1);
		if(!is_numeric($profile)) $profile = 1;
		$session =& JFactory::getSession();
		$session->set('profile', $profile, 'akeeba');

		// Use the default description if none specified
		if(empty($description))
		{
			jimport('joomla.utilities.date');
			$user =& JFactory::getUser();
			$userTZ = $user->getParam('timezone',0);
			$dateNow = new JDate();
			$dateNow->setOffset($userTZ);
			$description = JText::_('BACKUP_DEFAULT_DESCRIPTION').' '.$dateNow->toFormat(JText::_('DATE_FORMAT_LC2'));
		}


		// Start the backup
		AEPlatform::load_configuration($profile);
		AECoreKettenrad::reset();
		$kettenrad =& AECoreKettenrad::load($tag);

		$options = array(
			'description'	=> $description,
			'comment'		=> $comment,
			'tag'			=> $tag
		);
		$kettenrad->setup($options);
		AECoreKettenrad::save();

		$array = $kettenrad->getStatusArray();
		if($array['Error'] != '')
		{
			// A backup error had occured. Why are we here?!
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return 'A backup error had occured: '.$array['Error'];
		}
		else
		{
			$array = $kettenrad->tick();
			if($array['Error'] != '')
			{
				// A backup error had occured. Why are we here?!
				$this->status = self::STATUS_ERROR;
				$this->encapsulation = self::ENCAPSULATION_RAW;
				return 'A backup error had occured: '.$array['Error'];
			}
			else
			{
				$statistics =& AEFactory::getStatistics();
				$array['BackupID'] = $statistics->getId();
				return $array;
			}
		}
	}

	private function _apiStepBackup($config)
	{
		$defConfig = array(
			'tag'			=> 'remote'
		);
		$config = array_merge($defConfig, $config);
		extract($config);

		$kettenrad =& AECoreKettenrad::load($tag);
		$array = $kettenrad->getStatusArray();

		if($array['Error'] != '')
		{
			// A backup error had occured. Why are we here?!
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return 'A backup error had occured: '.$array['Error'];
		}
		elseif($array['HasRun'] == 1)
		{
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return 'The backup is already finalized!';
		}
		else
		{
			$array = $kettenrad->tick();
			AECoreKettenrad::save();

			if($array['Error'] != '')
			{
				// A backup error had occured. Why are we here?!
				$this->status = self::STATUS_ERROR;
				$this->encapsulation = self::ENCAPSULATION_RAW;
				return 'A backup error had occured: '.$array['Error'];
			}
			else
			{
				return $array;
			}
		}
	}

	private function _apiListBackups($config)
	{
		$defConfig = array(
			'from'			=> 0,
			'limit'			=> 50
		);
		$config = array_merge($defConfig, $config);
		extract($config);

		require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'statistics.php';

		$model = new AkeebaModelStatistics();
		return $model->getStatisticsListWithMeta(true);
	}

	private function _apiGetBackupInfo($config)
	{
		$defConfig = array(
			'backup_id'			=> '0'
		);
		$config = array_merge($defConfig, $config);
		extract($config);

		// Get the basic statistics
		$record = AEPlatform::get_statistics($backup_id);

		// Get a list of filenames
		$backup_stats = AEPlatform::get_statistics($backup_id);

		// Backup record doesn't exist
		if(empty($backup_stats))
		{
			$this->status = self::STATUS_NOT_FOUND;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return 'Invalid backup record identifier';
		}

		$filenames = AEUtilStatistics::get_all_filenames($stat);

		if(empty($filenames))
		{
			// Archives are not stored on the server or no files produced
			$record['filenames'] = array();
		}
		else
		{
			$filedata = array();
			$i = 0;

			// Get file sizes per part
			foreach($filenames as $file)
			{
				$i++;
				$size = @filesize($file);
				$size = is_numeric($size) ? $size : 0;
				$filedata[] = array(
					'part'			=> $i,
					'name'			=> basename($file),
					'size'			=> $size
				);
			}

			// Add the file info to $record['filenames']
			$record['filenames'] = $filedata;
		}

		return $record;

	}

	private function _apiDownload($config)
	{
		$defConfig = array(
			'backup_id'			=> 0,
			'part_id'			=> 1,
			'segment'			=> 1
		);
		$config = array_merge($defConfig, $config);
		extract($config);

		$backup_stats = AEPlatform::get_statistics($backup_id);
		if(empty($backup_stats))
		{
			// Backup record doesn't exist
			$this->status = self::STATUS_NOT_FOUND;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return 'Invalid backup record identifier';
		}
		$files = AEUtilStatistics::get_all_filenames($backup_stats);

		if( (count($files) < $part_id) || ($part_id <= 0) )
		{
			// Invalid part
			$this->status = self::STATUS_NOT_FOUND;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return 'Invalid backup part';
		}

		$file = $files[$segment-1];
		$fp = fopen($file, 'rb');

		if($fp === false)
		{
			// Could not read file
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return 'Error reading backup archive';
		}

		$seekPos = 1048576 * ($segment - 1);
		if($seekPos>0) if(!fseek($fp, $seekPos))
		{
			// Could not seek to position
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return 'Error reading specified segment';
		}

		$buffer = fread($fp, 1048756);

		if($buffer === false)
		{
			// Could not read
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return 'Error reading specified segment';
		}

		fclose($fp);

		switch($this->encapsulation)
		{
			case self::ENCAPSULATION_RAW:
				return base64_encode($buffer);
				break;

			case self::ENCAPSULATION_AESCTR128:
				$this->encapsulation = self::ENCAPSULATION_AESCBC128;
				return $buffer;
				break;

			case self::ENCAPSULATION_AESCTR256:
				$this->encapsulation = self::ENCAPSULATION_AESCBC256;
				return $buffer;
				break;

			default:
				// On encrypted comms the encryption will take care of transport encoding
				return $buffer;
				break;
		}
	}

	private function _apiDelete($config)
	{
		$defConfig = array(
			'backup_id'			=> 0
		);
		$config = array_merge($defConfig, $config);
		extract($config);

		require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'statistics.php';

		$model = new AkeebaModelStatistics();
		$result = $model->delete((int)$backup_id);
		if(!$result)
		{
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return $model->getError();
		}
		else
		{
			return true;
		}
	}

	private function _apiDeleteFiles($config)
	{
		$defConfig = array(
			'backup_id'			=> 0
		);
		$config = array_merge($defConfig, $config);
		extract($config);

		require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'statistics.php';

		$model = new AkeebaModelStatistics();
		$result = $model->deleteFile((int)$backup_id);
		if(!$result)
		{
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return $model->getError();
		}
		else
		{
			return true;
		}
	}

	private function _apiGetLog($config)
	{
		$defConfig = array(
			'tag'			=> 'remote'
		);
		$config = array_merge($defConfig, $config);
		extract($config);

		$filename = AEUtilLogger::logName($tag);
		$buffer = file_get_contents($filename);

		switch($this->encapsulation)
		{
			case self::ENCAPSULATION_RAW:
				return base64_encode($buffer);
				break;

			case self::ENCAPSULATION_AESCTR128:
				$this->encapsulation = self::ENCAPSULATION_AESCBC128;
				return $buffer;
				break;

			case self::ENCAPSULATION_AESCTR256:
				$this->encapsulation = self::ENCAPSULATION_AESCBC256;
				return $buffer;
				break;

			default:
				// On encrypted comms the encryption will take care of transport encoding
				return $buffer;
				break;
		}

	}
}