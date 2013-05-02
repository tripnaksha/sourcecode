<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: files.php 141 2010-05-20 18:33:40Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Subdirectories exclusion filter. Excludes temporary, cache and backup output
 * directories' contents from being backed up.
 */
class AEFilterPlatformFiles extends AEAbstractFilter
{
	public function __construct()
	{
		$this->object	= 'file';
		$this->subtype	= 'all';
		$this->method	= 'direct';
		$this->filter_name = 'PlatformFiles';

		// We take advantage of the filter class magic to inject our custom filters
		$this->filter_data['[SITEROOT]'] = array (
			'kickstart.php'
		);

		parent::__construct();
	}

}