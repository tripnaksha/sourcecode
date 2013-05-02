<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: none.php 92 2010-03-18 10:33:11Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

class AEPostprocNone extends AEAbstractPostproc
{

	public function __construct()
	{
		// No point in breaking the step; we simply do nothing :)
		$this->break_after = false;
		$this->break_before = false;
		$this->allow_deletes = false;
	}

	public function processPart($absolute_filename)
	{
		// Really nothing to do!!
		return true;
	}
}