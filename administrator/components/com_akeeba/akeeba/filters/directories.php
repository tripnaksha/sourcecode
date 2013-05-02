<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: directories.php 51 2010-01-30 10:49:58Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Directory exclusion filter
 */
class AEFilterDirectories extends AEAbstractFilter
{
	function __construct()
	{
		$this->object	= 'dir';
		$this->subtype	= 'all';
		$this->method	= 'direct';

		if(empty($this->filter_name)) $this->filter_name = strtolower(basename(__FILE__,'.php'));
		parent::__construct();
	}
}