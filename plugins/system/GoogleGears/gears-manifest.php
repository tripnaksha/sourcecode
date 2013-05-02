<?php
/**
 * @copyright	Copyright (C) 2008 Blue Flame IT (Jersey) Ltd. All rights reserved.
 * @license		GNU/GPL v2,
 * @link 		http://www.phil-taylor.com
 * @author 		Phil Taylor <me@phil-taylor.com> 
 * @version    $Id: gears-manifest.php 190 2008-10-02 15:34:18Z  $
 */
@set_time_limit ( 0 );
@ini_set ( 'memory_limit', '32M' );
define ( 'DS', DIRECTORY_SEPARATOR );
define ( 'JPATH_ROOT', realpath ( dirname ( __FILE__ ) . DS.'..'.DS.'..'.DS.'..'.DS ) );
define ( '_JGEARS_DEV', FALSE );
define ( 'JPATH_BASE' , '1');

class JGearsManifest {

    private $revision = '$Id: gears-manifest.php 191 2009-11-21 15:34:18Z  $';
	
	private $folders = array ();
	
	private $JVersion = '';
	
	private $output = '';
	
	private $manifest = '{
				"betaManifestVersion" : 1,
				"version" : "%s",
				"entries" : [
	%s
				{ "url" : "../../../administrator/images/tick.png" }
				]}';
	
	private $fileTypes = array ('png', 'css', 'gif', 'jpg', 'js' );
	
	private $customFiles = array();
	
	public function __construct() {
		$this->_setJVersion ();
		$this->_setFolders ();
		$this->_parseFolders ();
	}
	
	private function _parseFolders() {
		
		if (count ( $this->customFiles )) {
			foreach ( $this->customFiles as $file ) {
				$this->output .= sprintf ( '{ "url" : "../../../%s" },' . "\n\t", $file );
			}
		
		}
		
		foreach ( $this->folders as $folder ) {
			
			foreach ( $this->fileTypes as $type ) {
				$this->_parseFiles ( $folder, $type );
			}
		
		}
	
	}
	
	private function _getManifestVersion() {
		return md5 ( $this->output . $this->JVersion . $this->revision);
	}
	
	private function _parseFiles($folder, $suffix) {
		
		$dir = $this->mosReadDirectory ( JPATH_ROOT . DS . $folder, '.' . $suffix, true, true );
		foreach ( $dir as $file ) {
			
			if (! is_dir ( $file )) {
				$this->output .= sprintf ( '{ "url" : "../../../%s" },' . "\n\t", str_replace ( str_replace('\\','/',JPATH_ROOT . DS), '', $file ) );
			}
		}
	
	}
	
	private function _setJVersion() {
		require (JPATH_ROOT . DS . 'libraries/joomla/version.php');
		$JVersion = new JVersion ( );
		$this->JVersion = $JVersion->RELEASE . '.' . $JVersion->DEV_LEVEL;
	}
	
	private function _setFolders() {
		if (is_dir ( JPATH_ROOT . DS . 'plugins/system/GoogleGears/' ))
			$this->folders [] = 'plugins/system/GoogleGears/';
			
		/* additional BlueFlame files */
		if (file_exists ( JPATH_ROOT . DS . 'plugins/system/blueflame/bfCombine.php' )) {
			if (is_dir ( JPATH_ROOT . DS . 'plugins/system/blueflame/' ))
				$this->folders [] = 'plugins/system/blueflame/';
			if (is_dir ( JPATH_ROOT . DS . 'components/com_mailinglist/' ))
				$this->folders [] = 'components/com_mailinglist/';
			if (is_dir ( JPATH_ROOT . DS . 'components/com_form/' ))
				$this->folders [] = 'components/com_form/';
			if (is_dir ( JPATH_ROOT . DS . 'components/com_kb/' ))
				$this->folders [] = 'components/com_kb/';
			if (is_dir ( JPATH_ROOT . DS . 'components/com_tag/' ))
				$this->folders [] = 'components/com_tag/';
			
			if (file_exists ( JPATH_ROOT . DS . 'components/com_tag/xajax.tag.php' )) {
				$this->customFiles [] = 'plugins/system/blueflame/bfCombine.php?type=css&amp;c=tag&amp;f=bfadmin_css,admin_css';
				$this->customFiles [] = 'plugins/system/blueflame/bfCombine.php?type=js&c=tag&f=mootools,jquery,jquery.tabs,jquery.thickbox_js,bfadmin_js,admin_js,jquery.accordion';
			}
			if (file_exists ( JPATH_ROOT . DS . 'components/com_form/xajax.form.php' )) {
				$this->customFiles [] = 'plugins/system/blueflame/bfCombine.php?type=css&amp;c=form&amp;f=bfadmin_css,admin_css';
				$this->customFiles [] = 'plugins/system/blueflame/bfCombine.php?type=js&c=form&f=mootools,jquery,jquery.tabs,jquery.thickbox_js,bfadmin_js,admin_js,jquery.accordion';
			}
			if (file_exists ( JPATH_ROOT . DS . 'components/com_kb/xajax.kb.php' )) {
				$this->customFiles [] = 'plugins/system/blueflame/bfCombine.php?type=css&amp;c=kb&amp;f=bfadmin_css,admin_css';
				$this->customFiles [] = 'plugins/system/blueflame/bfCombine.php?type=js&c=kb&f=mootools,jquery,jquery.tabs,jquery.thickbox_js,bfadmin_js,admin_js,jquery.accordion';
			}
		}
		/* Standard Joomla 1.5 folders */
		$this->folders [] = 'administrator/images/';
		$this->folders [] = 'administrator/templates/khepri/images/';
		$this->folders [] = 'administrator/templates/khepri/css/';
		$this->folders [] = 'administrator/templates/khepri/js/';
		$this->folders [] = 'administrator/templates/khepri/images/toolbar/';
		$this->folders [] = 'includes/js/';
		$this->folders [] = 'media/system/';
		$this->folders [] = 'images/';
		$this->folders [] = 'plugins/editors/';
		$this->folders [] = 'templates/system/';
	
	}
	
	/**
	 * Utility function to read the files in a directory
	 * @param string The file system path
	 * @param string A filter for the names
	 * @param boolean Recurse search into sub-directories
	 * @param boolean True if to prepend the full path to the file name
	 */
	function mosReadDirectory($path, $filter = '.', $recurse = false, $fullpath = false) {
		
		$arr = array ();
		if (! @is_dir ( $path )) {
			return $arr;
		}
		$handle = opendir ( $path );
		
		while ( $file = readdir ( $handle ) ) {
			$dir = $this->mosPathName ( $path . DS . $file, false );
			$isDir = is_dir ( $dir );
			if (($file != ".") && ($file != "..") && ($file != ".svn")) {
				if (preg_match ( "/$filter/", $file )) {
					if ($fullpath) {
						$arr [] = trim ( $this->mosPathName ( $path . DS . $file, false ) );
					} else {
						$arr [] = trim ( $file );
					}
				}
				if ($recurse && $isDir) {
					$arr2 = $this->mosReadDirectory ( $dir, $filter, $recurse, $fullpath );
					$arr = array_merge ( $arr, $arr2 );
				}
			}
		}
		closedir ( $handle );
		asort ( $arr );
		return $arr;
	}
	
	/**
	 * Function to strip additional / or \ in a path name
	 * @param string The path
	 * @param boolean Add trailing slash
	 */
	function mosPathName($p_path, $p_addtrailingslash = true) {
		$retval = "";
		
		$retval = str_replace ( '\\', '/', $p_path );
		if ($p_addtrailingslash) {
			if (substr ( $retval, - 1 ) != '/') {
				$retval .= '/';
			}
		}
		// Remove double //
		$retval = str_replace ( '//', '/', $retval );
		
		return $retval;
	}
	
	public function __toString() {
		return sprintf ( $this->manifest, $this->_getManifestVersion (), $this->output );
	
	}

}

echo new JGearsManifest ( );