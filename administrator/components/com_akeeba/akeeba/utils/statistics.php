<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: statistics.php 179 2010-07-03 10:13:24Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

class AEUtilStatistics extends AEAbstractObject
{
	/** @var bool used to block multipart updating initializing the backup */
	private $multipart_lock = true;

	/** @var int The statistics record number of the current backup attempt */
	private $statistics_id = null;

	/** @var array Local cache of the stat record data */
	private $cached_data = array();

	/**
	 * Releases the initial multipart lock
	 */
	public function release_multipart_lock()
	{
		$this->multipart_lock = false;
	}

	/**
	 * Updates the multipart status of the current backup attempt's statistics record
	 * @param int $multipart The new multipart status
	 */
	public function updateMultipart( $multipart )
	{
		if( $this->multipart_lock ) return;

		AEUtilLogger::WriteLog(_AE_LOG_DEBUG,'Updating multipart status to '.$multipart);

		// Cache this change and commit to db only after the backup is done, or failed
		$registry =& AEFactory::getConfiguration();
		$registry->set('volatile.statistics.multipart', $multipart);
	}

	/**
	 * Sets or updates the statistics record of the current backup attempt
	 * @param array $data
	 */
	public function setStatistics($data)
	{
		$ret = AEPlatform::set_or_update_statistics($this->statistics_id, $data, $this);
		if(!is_null($ret))
		{
			$this->statistics_id = $ret;
		}
		$this->cached_data = array_merge($this->cached_data, $data);
	}

	/**
	 * Returns the statistics record ID (used in DB backup classes)
	 * @return int
	 */
	public function getId()
	{
		return $this->statistics_id;
	}

	/**
	 * Returns a copy of the cached data
	 * @return array
	 */
	public function getRecord()
	{
		return $this->cached_data;
	}



	/**
	 * Returns all the filenames of the backup archives for the specified stat record,
	 * or null if the backup type is wrong or the file doesn't exist. It takes into
	 * account the multipart nature of Split Backup Archives.
	 *
	 * @param array $stat The backup statistics record
	 * @param bool $skipNonComplete Skips over backups with no files produced
	 * @return array|null The filenames or null if it's not applicable
	 */
	public static function get_all_filenames( $stat, $skipNonComplete = true )
	{
		// Shortcut for database entries marked as having no files
		if($stat['filesexist'] == 0) { return array(); }

		// Initialize
		$base_directory = @dirname( $stat['absolute_path'] );
		$base_filename = $stat['archivename'];
		$filenames = array( $base_filename );

		if(empty($base_filename))
		{
			// This is a backup with a writer which doesn't store files on the server
			return null;
		}

		// Calculate all the filenames for this backup
		if($stat['multipart'] > 0)
		{
			// Find the base filename and extension
			$dotpos = strrpos($base_filename, '.');
			$extension = substr($base_filename, $dotpos);
			$basefile = substr($base_filename, 0, $dotpos);

			// Calculate the multiple names
			$multipart = $stat['multipart'];
			for($i = 1; $i <= $multipart; $i++ )
			{
				// Note: For $multipart = 10, it will produce i.e. .z01 through .z10
				// This is intentional. If the backup aborts and multipart=1, we
				// might be stuck with a .z01 file instead of a .zip. So do not
				// change the less than or equal with a straight less than.
				$filenames[] = $basefile.substr($extension,0,2).sprintf('%02d', $i);
			}
		}

		// Check if the files exist, otherwise attempt to provide relocated filename
		$ret = array();

		$ds = DIRECTORY_SEPARATOR;
		// $test_file is the first file which must have been created
		$test_file = count($filenames) == 1 ? $filenames[0] : $filenames[1];
		if (
			(!@file_exists($base_directory.$ds.$test_file)) ||
			(!is_dir($base_directory))
		)
		{
			// The test file wasn't detected. Use the configured output directory.
			$registry =& AEFactory::getConfiguration();
			$base_directory = $registry->get('akeeba.basic.output_directory');
		}

		foreach($filenames as $filename)
		{
			// Trun relative path to absolute
			$filename = $base_directory.$ds.$filename;

			// Return the new filename IF IT EXISTS!
			if(!@file_exists($filename)) $filename = '';

			// Do not return filename for invalid backups
			if( !empty($filename) )
			{
				$ret[] = $filename;
			}
		}

		if((count($ret) == 0) && $skipNonComplete) $ret = null;

		return $ret;
	}
}