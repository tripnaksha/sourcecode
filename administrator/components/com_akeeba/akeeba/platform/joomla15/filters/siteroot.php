<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2010 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: siteroot.php 51 2010-01-30 10:49:58Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Add site's root to the backup set.
 */
class AEFilterPlatformSiteroot extends AEAbstractFilter
{
	public function __construct()
	{
		// This is a directory inclusion filter.
		$this->object	= 'dir';
		$this->subtype	= 'inclusion';
		$this->method	= 'direct';
		$this->filter_name = 'PlatformSiteroot';

		// Directory inclusion format:
		// array(real_directory, add_path)
		$add_path = null; // A null add_path means that we dump this dir's contents in the archive's root

		// We take advantage of the filter class magic to inject our custom filters
		$configuration =& AEFactory::getConfiguration();

		$this->filter_data[] = array (
			'[SITEROOT]',
			$add_path
		);

		parent::__construct();
	}

	private function get_site_path()
	{
		// FIX 1.1.0 $mosConfig_absolute_path may contain trailing slashes or backslashes incompatible with exclusion filters
		// FIX 1.2.2 Some hosts yield an empty string on realpath(JPATH_SITE)
		// FIX 2.2 On Windows, realpath might fail
		// FIX 2.4: Make an assumption (wild guess...)
		if( (JPATH_BASE == '/administrator') || (JPATH_ROOT == '') )
		{
			$this->setWarning("Your site's root is an empty string. I am trying a workaround.");
			$jpath_site_real = '/';
		}
		else
		{
			// Fix 2.4: Make sure that $jpath_site_real contains something even if realpath fails
			$jpath_site_real = @realpath(trim(JPATH_SITE));
			$jpath_site_real = ($jpath_site_real === false) ? trim(JPATH_SITE) : $jpath_site_real;
			$jpath_site_real = AEUtilFilesystem::TranslateWinPath($jpath_site_real);
		}

		if( $jpath_site_real == '' )
		{
			// The JPATH_SITE is resolved to an empty string; attempt a workaround

			// Windows hosts
			if(DIRECTORY_SEPARATOR == '\\')
			{
				if( (trim(JPATH_SITE) != '') && (trim(JPATH_SITE) != '\\') && (trim(JPATH_SITE) != '/'))
				{
					$this->setWarning("The site's root couldn't be normalized on a Windows host. Attempting workaround (filters might not work)");
					$jpath_site_real = JPATH_SITE; // Forcibly use the configured JPATH_SITE
				}
				else
				{
					$this->setWarning("The normalized path to your site's root seems to be an empty string; I will attempt a workaround (Windows host)");
					$jpath_site_real = '/'; // Start scanning from filesystem root (workaround mode)
				}
			}
			// *NIX hosts
			else
			{
				$this->setWarning("The normalized path to your site's root seems to be an empty string; I will attempt a workaround (*NIX host)");
				# Fix 2.1 Since JPATH_SITE is an empty string, shouldn't I begin scanning from the FS root, for crying out loud? What was I thinking putting JPATH_SITE there?
				$jpath_site_real = '/'; // Start scanning from filesystem root (workaround mode)
			}
		}

		// Fix 2.4.b1 : Add the trailing slash
		if( (substr($jpath_site_real,-1) != '/') && !empty($jpath_site_real) )
		{
			$jpath_site_real .= '/';
		}

		return $jpath_site_real;
	}
}