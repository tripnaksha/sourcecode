<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: status.php 71 2010-02-22 22:17:01Z nikosdion $
 * @since 1.3
 */

defined('_JEXEC') or die('Restricted access');

/**
 * A class with utility functions to get the backup readiness status,
 * as well as "quirks" information. In contrast with most helper functions,
 * it has to be instanciated as an object with the getInstance() method in
 * order to work as expected.
 *
 */
class AkeebaHelperStatus extends JObject
{
	/** @var boolean Backup readiness status, true indicates ok */
	public $status = false;
	/** @var boolean Is output folder writable? */
	public $outputWritable = false;
	/** @var boolean Is temporary folder writable? */
	public $tempWritable = false;
	/** @var array Quirks detected (each entry contains code, severity, title, help_url keys) */
	public $quirks = array();

	/**
	 * Singleton pattern
	 *
	 * @return AkeebaHelperStatus
	 */
	public function &getInstance()
	{
		static $instance;

		if( empty($instance) )
		{
			$instance = new AkeebaHelperStatus();
		}

		return $instance;
	}

	/**
	 * Public contructor. Automatically initializes the object with the status and quirks.
	 *
	 * @access public
	 * @return AkeebaHelperStatus
	 */
	public function __construct()
	{
		parent::__construct();

		$status = AEUtilQuirks::get_folder_status();
		$this->outputWritable = $this->status['output'];
		$this->tempWritable = $this->status['temporary'];
		$this->status = AEUtilQuirks::get_status();
		$this->quirks = AEUtilQuirks::get_quirks();
	}

	/**
	 * Returns the HTML for the backup status cell
	 *
	 * @return string HTML
	 */
	public function getStatusCell()
	{
		$status = AEUtilQuirks::get_status();
		$quirks = AEUtilQuirks::get_quirks();

		if($status && empty($quirks))
		{
			$html = '<p class="ok ui-state-normal">'.JText::_('STATUS_OK').'</p>';
		}
		elseif($status && !empty($quirks))
		{
			$html = '<p class="statuswarning ui-state-highlight">'.JText::_('STATUS_WARNING').'</p>';
		}
		else
		{
			$html = '<p class="notok ui-state-error">'.JText::_('STATUS_ERROR').'</p>';
		}
		return $html;
	}

	/**
	 * render news feed from Akeeba Backup site
	 */
	public function getNewsCell() {
		// Permanent Fix 2.2: Display feed button, powered by FeedBurner :) Ah, at last!
		$output = '<table class="adminlist">';
		$output .= '<tr><td>'.JText::_('NEWS_INTRODUCTION').'</td></tr>';
		$output .= '<tr><td>';
		$output .= <<<ENDCODE
<script src="http://feeds2.feedburner.com/joomlapack/news?format=sigpro" type="text/javascript" ></script><noscript><p>Subscribe to RSS headline updates from: <a href="http://feeds2.feedburner.com/joomlapack/news"></a><br/>Powered by FeedBurner</p> </noscript>
ENDCODE;
		$output .= '</td></tr></table>';

		return $output;
	}

	/**
	 * Returns HTML for the warnings (status details)
	 *
	 * @return string HTML
	 */
	public function getQuirksCell($onlyErrors = false)
	{
		$html = '';
		$quirks = AEUtilQuirks::get_quirks();

		if(!empty($quirks))
		{
			$html = "<ul>\n";
			foreach($quirks as $quirk)
			{
				$html .= $this->_renderQuirk($quirk, $onlyErrors);
			}
			$html .= "</ul>\n";
		}
		else
		{
			$html = '<p>'.JText::_('QNONE').'</p>';
		}

		return $html;
	}

	/**
	 * Returns a boolean value, indicating if quirks have been detected
	 * @return bool True if there is at least one quirk detected
	 */
	public function hasQuirks()
	{
		$quirks = AEUtilQuirks::get_quirks();
		return !empty($quirks);
	}

	/**
	 * Gets the HTML for a single line of the quirks cell, based on quirks settings
	 *
	 * @param array $quirk A quirk definition array
	 */
	public function _renderQuirk($quirk, $onlyErrors = false)
	{
		if( $onlyErrors && ($quirk['severity'] != 'critical') ) return '';
		$quirk['severity'] = $quirk['severity'] == 'critical' ? 'high' : $quirk['severity'];
		$out = '<li><a class="severity-'.$quirk['severity'].'" href="'.$quirk['help_url'].'" target="_blank">'.$quirk['description'].'</a>'."\n";
		return $out;
	}

}