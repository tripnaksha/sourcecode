<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: postproc.php 92 2010-03-18 10:33:11Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Akeeba Engine post processing abstract class
 * @author Nicholas
 *
 */
abstract class AEAbstractPostproc extends AEAbstractObject
{
	/** @var bool Should we break the step before post-processing? */
	public $break_before = true;

	/** @var bool Should we break the step after post-processing? */
	public $break_after = true;

	/** @var bool Does this engine processes the files in a way that makes deleting the originals safe? */
	public $allow_deletes = true;

	/**
	 * This function takes care of post-processing a backup archive's part, or the
	 * whole backup archive if it's not a split archive type. If the process fails
	 * it should return false. If it succeeds and the entirety of the file has been
	 * processed, it should return true. If only a part of the file has been uploaded,
	 * it must return 1.
	 * @param string $absolute_filename Absolute path to the part we'll have to process
	 * @return bool|int False on failure, true on success, 1 if more work is required
	 */
	public abstract function processPart($absolute_filename);
}