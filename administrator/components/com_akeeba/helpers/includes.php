<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: includes.php 126 2010-04-28 23:00:40Z nikosdion $
 * @since 1.3
 */

defined('_JEXEC') or die('Restricted access');

/**
 * A centralized place to include Akeeba Backup's CSS and JS files to the rendered page, as well as
 * GUI-related helper functions
 * @author Nicholas
 */
class AkeebaHelperIncludes
{
	/** @var bool Should I use Akeeba plugins? */
	static $usePlugins = false;

	/** @var array The URLs of external scripts I've got to load*/
	public static $scriptURLs = array();

	/** @var array script definitions I want to inject right after the external scripts */
	public static $scriptDefs = array();

	static $viewHelpMap = array(
		'backup'		=> 'backup-now.html',
		'buadmin'		=> 'adminsiter-backup-files.html',
		'config'		=> 'configuration.html',
		'cpanel'		=> 'ch03.html#control-panel',
		'dbef'			=> 'database-tables-exclusion.html',
		'fsfilter'		=> 'exclude-data-from-backup.html#files-and-directories-exclusion',
		'log'			=> 'view-log.html',
		'profiles'		=> 'using-basic-operations.html#id4812849',
		'update'		=> '',
		'eff'			=> 'off-site-directories-inclusion.html',
		'extfilter'		=> 'extension-filters.html',
		'multidb'		=> 'include-data-to-archive.html#multiple-db-definitions',
		'regexdbfilter'	=> 'regex-database-tables-exclusion.html',
		'regexfsfilter'	=> 'regex-files-directories-exclusion.html',
		'restore'		=> ''
	);

	static function getScriptDefs()
	{
		$media_folder = JURI::base().'../media/com_akeeba/';
		$scriptDefs = array(
			$media_folder.'js/gui-helpers.js',
			$media_folder.'js/akeebaui.js'
		);
		if(self::$usePlugins)
		{
			$scriptDefs[] = $media_folder.'plugins/js/akeebaui.js';
		}
		return $scriptDefs;
	}

	/**
	 * Includes Akeeba Backup's Javascript files
	 * @param $plugins bool Should I also include the files from the plugins directory?
	 */
	static function includeJS($plugins = false)
	{
		// Load jQuery
		self::jQueryLoad();
		self::jQueryUILoad();

		$document =& JFactory::getDocument();

		// In Joomla! 1.6 we have to load jQuery and jQuery UI without the hackish onAfterRender method :(
		global $mainframe;
		if(!is_object($mainframe))
		{
			foreach(self::$scriptURLs as $url)
			{
				$document->addScript($url);
			}
			foreach(self::$scriptDefs as $script)
			{
				$document->addScriptDeclaration($script);
			}
		}

		// Joomla! 1.5 method
		self::$usePlugins = $plugins;
		$scriptDefs = self::getScriptDefs();
		foreach($scriptDefs as $scriptURI)
		{
			$document->addScript($scriptURI);
		}
	}

	/**
	 * Includes Akeeba Backup's CSS files
	 * @param $plugins bool Should I also include the files from the plugins directory?
	 */
	static function includeCSS($plugins=false)
	{
		$media_folder = JURI::base().'../media/com_akeeba/';
		$document =& JFactory::getDocument();
		$document->addStyleSheet($media_folder.'theme/jquery-ui.css');
		$document->addStyleSheet($media_folder.'theme/akeebaui.css');
		if($plugins)
		{
			$document->addStyleSheet($media_folder.'plugins/theme/akeebaui.css');
		}
	}

	/**
	 * Includes Akeeba Backup's media (CSS & JS) files. It's a shorthand to the other two functions.
	 * @param $plugins bool Should I also include the files from the plugins directory?
	 */
	static function includeMedia($plugins=false)
	{
		self::includeJS($plugins);
		self::includeCSS($plugins);
	}

	/**
	 * Loads jQuery from its respective source
	 */
	static function jQueryLoad()
	{
		$source = AEPlatform::get_platform_configuration_option('backend_jquery_source', 0);
		$js = null;
		switch($source)
		{
			case 0:
				// Local copy
				$js = JURI::base().'../media/com_akeeba/js/jquery.js';
				break;
			case 1:
				// Google AJAX APIs copy -- Conditionally loads it if it's not already present :)
				$js = 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js';
			case 2:
				// Do not load
				break;
		}

		if(!is_null($js))
		{
			self::$scriptURLs[] = $js;
		}

		if($source != 0)
		{
			self::$scriptDefs[] = <<<ENDJS
var akeeba = {};
akeeba.jQuery = jQuery.noConflict();
ENDJS;
		}
	}

	/**
	 * Loads jQuery UI from its respective source
	 */
	static function jQueryUILoad()
	{
		$source = AEPlatform::get_platform_configuration_option('backend_jqueryui_source', 0);
		$js = null;

		switch($source)
		{
			case 0:
				// Local copy
				$js = JURI::base().'../media/com_akeeba/js/jquery-ui.js';
				break;
			case 1:
				// Google AJAX APIs copy -- Conditionally loads it if it's not already present :)
				$js = 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js';
			case 2:
				// Do not load
				break;
		}

		if(!is_null($js))
		{
			self::$scriptURLs[] = $js;
		}
	}

	static public function addHelp()
	{
		$view = JRequest::getCmd('view','cpanel');
		if( array_key_exists($view, self::$viewHelpMap) )
		{
			$page = self::$viewHelpMap[$view];
			if(empty($page)) return;
			self::addLiveHelpButton($page);
		}
	}

	static public function addLiveHelpButton( $page )
	{
		if(strpos($page, '.html') === false) $page .= '.html';
		if(strpos($page, '#') === false) $page .= '#maincol';
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Popup', 'help', 'help', 'http://www.akeebabackup.com/akeeba-backup-documentation/'.$page, 900, 500 );
	}
}

/**
 * This is an Akeeba hack to make sure that its own JS is going to be loaded before the one loaded by any
 * funky system plug-in. For example, many stupid plugins default to loading jQuery 1.2.6 in the backend.
 * WTF?! This is an ancient version! And why the hell load it in the backend anyway?! So, instead of having
 * to educate webmasters that the plugins work in a stupid way and plugin authors how not to write stupid
 * scripts (can't really blame newbies for being ignorant), I work around this issue by writing my hidden
 * system plug-in. Yeap! This is actually a system plugin :p It will grab the HTML and drop its own JS in
 * the head of the script, before anything else has the chance to run.
 *
 * Peace.
 */
function AkeebaScriptHook()
{
	global $mainframe;
	// Joomla! 1.6 compatibility. Do not touch!
	if(!is_object($mainframe))
	{
		$app = &JFactory::getApplication('administrator');
		if(!$app->isAdmin()) return;
	}
	else
	{
		// Not in back-end? Why are we here then?!
		if(!$mainframe->isAdmin()) return;
	}

	// If there are no script defs, just go to sleep
	if(empty(AkeebaHelperIncludes::$scriptURLs) && empty(AkeebaHelperIncludes::$scriptDefs) ) return;

	$myscripts = '';
	if(!empty(AkeebaHelperIncludes::$scriptURLs)) foreach(AkeebaHelperIncludes::$scriptURLs as $url)
	{
		$myscripts .= '<script type="text/javascript" src="'.$url.'"></script>'."\n";
	}
	if(!empty(AkeebaHelperIncludes::$scriptDefs))
	{
		$myscripts .= '<script type="text/javascript">'."\n";
		foreach(AkeebaHelperIncludes::$scriptDefs as $def)
		{
			$myscripts .= $def."\n";
		}
		$myscripts .= '</script>'."\n";
	}


	$buffer = JResponse::getBody();
	$pos = strpos($buffer, "<head>");
	if($pos > 0)
	{
		$buffer = substr($buffer, 0, $pos + 6).$myscripts.substr($buffer, $pos + 6);
		JResponse::setBody($buffer);
	}
}

$app = &JFactory::getApplication();
$app->registerEvent('onAfterRender', 'AkeebaScriptHook');