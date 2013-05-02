<?php
/**
* Joomla Community Builder : Plugin Handler
* @version $Id: library/cb/cb.params.php 610 2006-12-13 17:33:44Z beat $
* @package Community Builder
* @subpackage cb.params.php
* @author various, JoomlaJoe and Beat
* @copyright (C) Beat and JoomlaJoe, www.joomlapolis.com and various
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
* Installer class
* @package Community Builder
* @subpackage Installer
* @abstract
*/
class cbInstaller {
	// name of the XML file with installation information
	var $i_installfilename	= "";
	var $i_installarchive	= "";
	var $i_installdir		= "";
	var $i_iswin			= false;
	var $i_errno			= 0;
	var $i_error			= "";
	var $i_installtype		= "";
	var $i_unpackdir		= "";
	var $i_docleanup		= true;

	/** @var string The directory where the element is to be installed */
	var $i_elementdir = '';
	/** @var string The name of the Mambo element */
	var $i_elementname = '';
	/** @var string The name of a special atttibute in a tag */
	var $i_elementspecial = '';
	/** @var object A DOMIT XML document */
	var $i_xmldocument		= null;

	var $i_hasinstallfile = null;
	var $i_installfile = null;

	/**
	* Constructor
	*/
	function cbInstaller() {
		cbimport( 'cb.adminfilesystem' );
		$this->i_iswin = (substr(PHP_OS, 0, 3) == 'WIN');
	}
	/**
	* Uploads and unpacks a file
	* @param string The uploaded package filename or install directory
	* @param boolean True if the file is an archive file
	* @return boolean True on success, False on error
	*/
	function upload($p_filename = null, $p_unpack = true, $p_findinstallfile = true ) {
		$this->i_iswin = (substr(PHP_OS, 0, 3) == 'WIN');
		$this->installArchive( $p_filename );

		if ($p_unpack) {
			if ($this->extractArchive()) {
				if ( $p_findinstallfile ) {
					return $this->findInstallFile();
				} else {
					return true;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/**
	* Extracts the package archive file
	* @return boolean True on success, False on error
	*/
	function extractArchive() {
		global $_CB_framework;

		$base_Dir			=	_cbPathName( $_CB_framework->getCfg('tmp_path') );

		$archivename		=	$this->installArchive();
		$tmpdir				=	uniqid( 'install_' );

		$extractdir			=	_cbPathName( $base_Dir . $tmpdir );
		$archivename		=	_cbPathName( $archivename, false );

		$this->unpackDir( $extractdir );

		if ( preg_match( "/\\.zip\$/i", $archivename ) ) {
			// Extract functions
			cbimport( 'pcl.pclziplib' );
			$zipfile		=	new PclZip( $archivename );
			if($this->isWindows()) {
				define('OS_WINDOWS',1);
			} else {
				define('OS_WINDOWS',0);
			}

			$ret			=	$zipfile->extract( PCLZIP_OPT_PATH, $extractdir );
			if($ret == 0) {
				$this->setError( 1, 'Unrecoverable error "'.$zipfile->errorName(true).'"' );
				return false;
			}
		} else {
			cbimport( 'pcl.tar' );	// includes/Archive/Tar.php' );
			$archive		=&	new Archive_Tar( $archivename );
			$archive->setErrorHandling( PEAR_ERROR_PRINT );

			if ( ! $archive->extractModify( $extractdir, '' ) ) {
				$this->setError( 1, 'Extract Error' );
				return false;
			}
		}

		$this->installDir( $extractdir );

		// Try to find the correct install dir. in case that the package have subdirs
		// Save the install dir for later cleanup
		$filesindir			=	cbReadDirectory( $this->installDir(), '' );

		if ( count( $filesindir ) == 1 ) {
			if ( is_dir( $extractdir . $filesindir[0] ) ) {
				$this->installDir( _cbPathName( $extractdir . $filesindir[0] ) );
			}
		}
		return true;
	}
	/**
	* Tries to find the package XML file
	* @return boolean True on success, False on error
	*/
	function findInstallFile() {
		$found = false;
		// Search the install dir for an xml file
		$files = cbReadDirectory( $this->installDir(), '.xml$', true, false );

		if (count( $files ) > 0) {
			foreach ($files as $file) {
				$packagefile	=&	$this->isPackageFile( $this->installDir() . $file );
				if (!is_null( $packagefile ) && !$found ) {
					$this->i_xmldocument =& $packagefile;
					return true;
				}
			}
			$this->setError( 1, 'ERROR: Could not find a CB XML setup file in the package.' );
			return false;
		} else {
			$this->setError( 1, 'ERROR: Could not find an XML setup file in the package.' );
			return false;
		}
	}
	/**
	* @param string A file path
	* @return object A DOMIT XML document, or null if the file failed to parse
	*/
	function & isPackageFile( $p_file ) {
		$null		=	null;
		if ( ! file_exists( $p_file ) ) {
			return $null;
		}
		cbimport('cb.xml.simplexml');
		$xmlString	=	trim( file_get_contents( $p_file ) );

		$element	=&	new CBSimpleXMLElement( $xmlString );
		if ( count( $element->children() ) == 0 ) {
			return $null;
		}

		if ( $element->name() != 'cbinstall' ) {
			//echo "didn't find cbinstall";
			return $null;
		}
		// Set the type
		//echo "<br />element->attributes( 'type' )=".$element->attributes( 'type' );
		$this->installType( $element->attributes( 'type' ) );
		$this->installFilename( $p_file );
		return $element;
	}
	/**
	* Loads and parses the XML setup file
	* @return boolean True on success, False on error
	*/
	function readInstallFile() {

		if ($this->installFilename() == "") {
			$this->setError( 1, 'No filename specified' );
			return false;
		}

		cbimport('cb.xml.simplexml');

		if ( file_exists( $this->installFilename() ) ) {
			$xmlString = trim( file_get_contents( $this->installFilename() ) );

			$this->i_xmldocument	=&	new CBSimpleXMLElement( $xmlString );
			if ( count( $this->i_xmldocument->children() ) == 0 ) {
				return false;
			}
		}
		$main_element	=&	$this->i_xmldocument;

		// Check that it's am installation file
		if ($main_element->name() != 'cbinstall') {
			$this->setError( 1, 'File :"' . $this->installFilename() . '" is not a valid Joomla installation file' );
			return false;
		}
		//echo "<br />main_element->attributes( 'type' )=".$main_element->attributes( 'type' );
		$this->installType( $main_element->attributes( 'type' ) );
		return true;
	}
	/**
	* Abstract install method
	*/
	function install() {
		die( 'Method "install" cannot be called by class ' . strtolower(get_class( $this )) );
	}
	/**
	* Abstract uninstall method
	*/
	function uninstall() {
		die( 'Method "uninstall" cannot be called by class ' . strtolower(get_class( $this )) );
	}
	/**
	* return to method
	*/
	function returnTo( $option, $task ) {
		return "index2.php?option=$option&task=$task";
	}
	/**
	* @param string Install from directory
	* @param string The install type
	* @return boolean
	*/
	function preInstallCheck( $p_fromdir, $type='plugin' ) {

		if (!is_null($p_fromdir)) {
			$this->installDir($p_fromdir);
		}

		if (!$this->installfile()) {
			$this->findInstallFile();
		}

		if (!$this->readInstallFile()) {
			$this->setError( 1, 'Installation file not found:<br />' . $this->installDir() );
			return false;
		}
		
		//echo "<br />type=".$type." this->installType()=".$this->installType();
		if (trim($this->installType()) != trim($type)) {
			//echo "<br />failing here<br />";
			$this->setError( 1, 'XML setup file is not for a "'.$type.'".' );
			return false;
		}
		

		// In case there where an error doring reading or extracting the archive
		if ($this->errno()) {
			return false;
		}

		return true;
	}
	/**
	* @param string The tag name to parse
	* @param string An attribute to search for in a filename element
	* @param string The value of the 'special' element if found
	* @param boolean True for Administrator components
	* @return mixed Number of file or False on error
	*/
	function parseFiles( $tagName='files', $special='', $specialError='', $adminFiles=0 ) {
		global $_CB_framework;
		// Find files to copy
		$cbInstallXML	=&	$this->i_xmldocument;

		$files_element	=&	$cbInstallXML->getElementByPath( $tagName );
		if ( ! ( $files_element )) {
			return 0;
		}

		if ( count( $files_element->children() ) == 0 ) {
			// no files
			return 0;
		}
		$copyfiles = array();

		$folder					=	$files_element->attributes( 'folder' );
		if ( $folder ) {
			$temp 				= _cbPathName( $this->unpackDir() . $folder );
			if ($temp == $this->installDir()) {
				// this must be only an admin component
				$installFrom	=	$this->installDir();
			} else {
				$installFrom	=	_cbPathName( $this->installDir() . $folder );
			}
		} else {
			$installFrom		=	$this->installDir();
		}

		foreach ( $files_element->children() as $file ) {
			if ( basename( $file->data() ) != $file->data() ) {
				$newdir			=	dirname( $file->data() );

				if ( $adminFiles ) {
					if ( ! $this->mosMakePath( $this->componentAdminDir(), $newdir ) ) {
						$this->setError( 1, 'Failed to create directory "' . ($this->componentAdminDir()) . $newdir . '"' );
						return false;
					}
				} else {
					if ( ! $this->mosMakePath( $this->elementDir(), $newdir ) ) {
						$this->setError( 1, 'Failed to create directory "' . ($this->elementDir()) . $newdir . '"' );
						return false;
					}
				}
			}
			$copyfiles[]		=	$file->data();

			// check special for attribute
			if ( $file->attributes( $special ) ) {
				$this->elementSpecial( $file->attributes( $special ) );
			}
		}

		if ( $specialError ) {
			if ( $this->elementSpecial() == '' ) {
				$this->setError( 1, $specialError );
				return false;
			}
		}

		if ( $tagName == 'media' ) {
			// media is a special tag
			$installTo			=	_cbPathName( $_CB_framework->getCfg('absolute_path') . '/images/stories' );
		} else if ($adminFiles) {
			$installTo			=	$this->componentAdminDir();
		} else {
			$installTo			=	$this->elementDir();
		}
		$result					=	$this->copyFiles( $installFrom, $installTo, $copyfiles );

		return $result;
	}

	/**
	* @param string Source directory
	* @param string Destination directory
	* @param array array with filenames
	* @param boolean True is existing files can be replaced
	* @return boolean True on success, False on error
	*/
	function copyFiles( $p_sourcedir, $p_destdir, $p_files, $overwrite=false ) {
		global $_CB_framework;
		if (is_array( $p_files ) && count( $p_files ) > 0) {
			$adminFS			=&	cbAdminFileSystem::getInstance();
			$filePerms			=	$_CB_framework->getCfg( 'fileperms' );
			$dirPerms			=	$_CB_framework->getCfg( 'dirperms' );
			if ( $filePerms ) {
				$filePerms		=	octdec( $filePerms );
			} else {
				$filePerms		=	null;
			}
			if ( $dirPerms ) {
				$dirPerms		=	octdec( $dirPerms );
			} else {
				$dirPerms		=	null;
			}
			foreach($p_files as $_file) {
				$filesource		=	_cbPathName( _cbPathName( $p_sourcedir ) . $_file, false );
				$filedest		=	_cbPathName( _cbPathName( $p_destdir ) . $_file, false );

				if ( ! file_exists( $filesource ) ) {
					$this->setError( 1, "File $filesource does not exist!" );
					return false;
				} else if ( file_exists( $filedest ) && ! $overwrite ) {
					$this->setError( 1, "There is already a file called $filedest - Are you trying to install the same Plugin twice?" );
					return false;
				} else if ( ! $adminFS->copy( $filesource, $filedest ) ) {
					$this->setError( 1, "Failed to copy file: $filesource to $filedest" );
					return false;
				} else {
					if ( is_dir( $filesource ) && $dirPerms ) {
						$perms	=	$dirPerms;
					} elseif ( is_file( $filesource ) && $filePerms ) {
						$perms	=	$filePerms;
					} else {
						$perms	=	null;
					}
					if ( $perms && ! $adminFS->chmod( $filedest, $perms ) ) {
						$this->setError( 1, "Failed to chmod file: $filedest" );
						return false;
					}
				}
			}
		} else {
			return false;
		}
		return count( $p_files );
	}
	/**
	* Copies the XML setup file to the element Admin directory
	* Used by Plugin Installer
	* @return boolean True on success, False on error
	*/
	function copySetupFile( $where='admin' ) {
		if ($where == 'admin') {
			return $this->copyFiles( $this->installDir(), $this->componentAdminDir(), array( basename( $this->installFilename() ) ), true );
		} else if ($where == 'front') {
			return $this->copyFiles( $this->installDir(), $this->elementDir(), array( basename( $this->installFilename() ) ), true );
		}
		return false;
	}

	/**
	* @param int The error number
	* @param string The error message
	*/
	function setError( $p_errno, $p_error ) {
		$this->errno( $p_errno );
		$this->error( $p_error );
	}
	/**
	* @param boolean True to display both number and message
	* @param string The error message
	* @return string
	*/
	function getError($p_full = false) {
		if ($p_full) {
			return $this->errno() . " " . $this->error();
		} else {
			return $this->error();
		}
	}
	/**
	* @param string The name of the property to set/get
	* @param mixed The value of the property to set
	* @return The value of the property
	*/
	function setVar( $name, $value=null ) {
		if (!is_null( $value )) {
			$this->$name = $value;
		}
		return $this->$name;
	}

	function installFilename( $p_filename = null ) {
		if(!is_null($p_filename)) {
			if($this->isWindows()) {
				$this->i_installfilename = str_replace('/','\\',$p_filename);
			} else {
				$this->i_installfilename = str_replace('\\','/',$p_filename);
			}
		}
		return $this->i_installfilename;
	}

	function installType( $p_installtype = null ) {
		return $this->setVar( 'i_installtype', $p_installtype );
	}

	function error( $p_error = null ) {
		return $this->setVar( 'i_error', $p_error );
	}

	function installArchive( $p_filename = null ) {
		return $this->setVar( 'i_installarchive', $p_filename );
	}

	function installDir( $p_dirname = null ) {
		return $this->setVar( 'i_installdir', $p_dirname );
	}

	function unpackDir( $p_dirname = null ) {
		return $this->setVar( 'i_unpackdir', $p_dirname );
	}

	function isWindows() {
		return $this->i_iswin;
	}

	function errno( $p_errno = null ) {
		return $this->setVar( 'i_errno', $p_errno );
	}

	function hasInstallfile( $p_hasinstallfile = null ) {
		return $this->setVar( 'i_hasinstallfile', $p_hasinstallfile );
	}

	function installfile( $p_installfile = null ) {
		return $this->setVar( 'i_installfile', $p_installfile );
	}

	function elementDir( $p_dirname = null )	{
		return $this->setVar( 'i_elementdir', $p_dirname );
	}

	function elementName( $p_name = null )	{
		return $this->setVar( 'i_elementname', $p_name );
	}
	function elementSpecial( $p_name = null )	{
		return $this->setVar( 'i_elementspecial', $p_name );
	}
	/**
	* Warning: needs cbAdminFileSystem  File-system loaded to use
	* 
	* @param  string  $base  An existing base path
	* @param  string  $path  A path to create from the base path
	* @param  int     $mode  Directory permissions
	* @return boolean         True if successful
	*/
	function mosMakePath( $base, $path='', $mode = null ) {
		global $_CB_framework;

		// convert windows paths
		$path					=	preg_replace( "/(\\/){2,}|(\\\\){1,}/",'/', $path );

		// check if dir exists
		if ( file_exists( $base . $path ) ) {
			return true;
		}

		// set mode
		$origmask				=	null;
		if ( isset( $mode ) ) {
			$origmask			=	@umask(0);
		} else {
			if ( $_CB_framework->getCfg( 'dirperms' ) == '' ) {
				// rely on umask
				$mode			=	0755;		// 0777;
			} else {
				$origmask		=	@umask( 0 );
				$mode			=	octdec( $_CB_framework->getCfg( 'dirperms' ) );
			}
		}

		$ret					=	true;
		if ( $path == '' ) {
			while ( substr( $base, -1, 1 ) == '/' ) {
				$base			=	substr( $base, 0, -1 );
			}
			$adminFS			=&	cbAdminFileSystem::getInstance();
			$ret				=	$adminFS->mkdir( $base, $mode );
		} else {
			$parts				=	explode( '/', $path );
			$n					=	count( $parts );

			$path				=	$base;
			for ( $i = 0 ; $i < $n ; $i++ ) {
				$path			.=	$parts[$i];
				if ( ! file_exists( $path ) ) {
					$adminFS	=&	cbAdminFileSystem::getInstance();
					if ( ! $adminFS->mkdir( $path, $mode ) ) {
						$ret	=	false;
						break;
					}
				}
				$path			.=	'/';
			}
		}
		if ( isset( $origmask ) ) {
			@umask( $origmask );
		}
		return $ret;
	}

}	// end class cbInstaller

function cleanupInstall( $userfile_name, $resultdir) {
	if ( file_exists( $resultdir ) ) {
		$adminFS		=&	cbAdminFileSystem::getInstance();
		$adminFS->deldir( $resultdir );
		if ( $userfile_name ) {
			$adminFS->unlink( _cbPathName( $userfile_name, false ) );
		}
	}
}

class cbInstallerPlugin extends cbInstaller {
	/** @var string The element type */
	var $elementType			=	'plugin';
	var $checkdbErrors			=	null;
	var $checkdbLogs			=	null;

	/**
	* Constructor
	*/
	function cbInstallerPlugin() {
		$this->cbInstaller();
	}

	/**
	* Custom install method
	* @param boolean True if installing from directory
	*/
	function install( $p_fromdir = null ) {
		global $_CB_framework, $_CB_database, $ueConfig, $_PLUGINS;
        
		if (!$this->preInstallCheck( $p_fromdir,$this->elementType )) {
			return false;
		}

		$cbInstallXML			=&	$this->i_xmldocument;

		// Get name
		$e						=	&$cbInstallXML->getElementByPath( 'name' );
		$this->elementName( $e->data() );
		$cleanedElementName		=	strtolower(str_replace(array(" ","."),array("","_"),$this->elementName()));

		// Get plugin filename
		$files_element			=	&$cbInstallXML->getElementByPath( 'files' );
		foreach ( $files_element->children() as $file ) {
			if ($file->attributes( "plugin" )) {
				$this->elementSpecial( $file->attributes( "plugin" ) );
			}
		}
		$fileNopathNoext		=	null;
		$matches			=	array();
		if ( preg_match("/^.*[\\/\\\\](.*)\\..*$/", $this->installFilename(), $matches ) ) {
			$fileNopathNoext	=	$matches[1];
		}
		if ( ! ( $fileNopathNoext && ( $this->elementSpecial() == $fileNopathNoext ) ) ) {
			$this->setError( 1, 'Installation filename `' . $fileNopathNoext . '` (with .xml) does not match main php file plugin attribute `'  . $this->elementSpecial() . '` in the plugin xml file<br />' );
			return false;
		}
		$cleanedMainFileName	=	strtolower(str_replace(array(" ","."),array("","_"),$this->elementSpecial()));

		// check version
		$v						=	&$cbInstallXML->getElementByPath( 'version' );
		$version				=	$v->data();
		if (($version == $ueConfig['version']) || ( $version=="1.2" || $version=="1.2 RC 4" || $version=="1.2 RC 3" || $version=="1.2 RC 2" || $version=="1.2 RC" || $version=="1.0 RC 2" || $version=="1.0" || $version=="1.0.1" || $version=="1.0.2" || $version=="1.1")) {
			;
		} else {
      		$this->setError( 1, 'Plugin version ('.$version.') different from Community Builder version ('.$ueConfig['version'].')' );
			return false;
    	}

    	$backendMenu			=	"";
    	$adminmenusnode			=&	$cbInstallXML->getElementByPath( 'adminmenus' );
		if ( $adminmenusnode ) {
			$menusArr			=	array();
			//cycle through each menu
			foreach( $adminmenusnode->children() AS $menu ) {
				if ( $menu->name() == "menu" ) {
					$action		=	$menu->attributes('action');
					$text		=	getLangDefinition($menu->data());
					$menusArr[]	=	$text . ":" . $action;
				}
			}
			$backendMenu		=	implode( ",", $menusArr );
		}

		$folder					=	strtolower($cbInstallXML->attributes( 'group' ));
		if ( cbStartOfStringMatch( $folder, '/' ) ) {
			$this->elementDir( $_CB_framework->getCfg('absolute_path') . $folder . '/' );
			$subFolder			=	$folder;
		} else {
			$subFolder			=	( ( $folder == 'user' ) ? 'plug_' : '' ) . $cleanedElementName;
			$this->elementDir( $_CB_framework->getCfg('absolute_path') . '/components/com_comprofiler/plugin/' . $folder . '/' . $subFolder . '/' );
		}
		
		if (file_exists($this->elementDir())) {
      		$this->setError( 1, 'Another plugin is already using directory: "' . $this->elementDir() . '"' );
			return false;
    	}

    	$parentFolder			=	preg_replace( '/\/[^\/]*\/?$/', '/', $this->elementDir() );
		if ( ! file_exists( $parentFolder ) ) {
      		$this->setError( 1, sprintf( 'The directory in which the plugin should install does not exist: probably the parent extension is not installed. Install parent extension first. Plugin parent directory missing: "%s" and plugin directory specified by installer for installation "%s"', $parentFolder, $this->elementDir() ) );
			return false;
    	}

		if(!file_exists($this->elementDir()) && !$this->mosMakePath($this->elementDir())) {
			$this->setError( 1, 'Failed to create directory' .' "' . $this->elementDir() . '"' );
			return false;
		}

		// Copy files from package:
		if ($this->parseFiles( 'files', 'plugin', 'No file is marked as plugin file' ) === false) {
			cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
			return false;
		}

		// Copy XML file from package (needed for creating fields of new types and so on):
		if ($this->copySetupFile('front') === false) {
			cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
			return false;
		}

		// Check to see if plugin already exists in db
		$_CB_database->setQuery( "SELECT id FROM #__comprofiler_plugin WHERE element = '" . $this->elementSpecial() . "' AND folder = '" . $subFolder . "'" );
		if (!$_CB_database->query()) {
			$this->setError( 1, 'SQL error' .': ' . $_CB_database->stderr( true ) );
			cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
			return false;
		}

		$pluginid 				=	$_CB_database->loadResult();

		$pluginRowWasNotExisting	=	( ! $pluginid );

		$row					=	new moscomprofilerPlugin( $_CB_database );
		$row->id				=	$pluginid;
		if ( ! $pluginid ) {
			$row->name = $this->elementName();
			$row->ordering		=	99;
		}
		$row->type				=	$folder;
		if ( $row->type == 'language' ) {
			$row->published		=	1;
		}
		$row->folder			=	$subFolder;
		$row->backend_menu		=	$backendMenu;
		$row->iscore			=	0;
		$row->access			=	0;
		$row->client_id			=	0;
		$row->element			=	$this->elementSpecial();

		if (!$row->store()) {
			$this->setError( 1, 'SQL error' .': ' . $row->getError() );
			cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
			return false;
		}
		if ( ! $pluginid ) {
			$pluginid								=	$_CB_database->insertid();
		}
		$_PLUGINS->_setLoading( $row, true );

		// Are there any Database statements ??
		$db										=&	$cbInstallXML->getElementByPath( 'database' );
		if ( ( $db !== false ) && ( count( $db->children() ) > 0 ) ) {
			cbimport( 'cb.sql.upgrader' );
			$sqlUpgrader						=	new CBSQLupgrader( $_CB_database, false );
//$sqlUpgrader->setDryRun( true );
			$success							=	$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedElementName, true, null );
/*
var_dump( $success );
echo "<br>\nERRORS: " . $sqlUpgrader->getErrors( "<br /><br />\n\n", "<br />\n" );
echo "<br>\nLOGS: " . $sqlUpgrader->getLogs( "<br /><br />\n\n", "<br />\n" );
exit;
*/
			if ( ! $success ) {
				$this->setError( 1, "Plugin database XML SQL Error " . 	$sqlUpgrader->getErrors() );
				if ( $pluginRowWasNotExisting ) {
					$this->deleteTabAndFieldsOfPlugin( $row->id );	// delete tabs and private fields of plugin
					$row->delete();
				}
				cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
				return false;
			}
		}

		$e											=&	$cbInstallXML->getElementByPath( 'description' );
		if ( $e !== false ) {
			$desc									=	$this->elementName() . '<div>' . $e->data() . '</div>';
			$this->setError( 0, $desc );
		}
		//If type equals user then check for tabs and fields
		if ( $folder == 'user' ) {
			$tabsnode								=&	$cbInstallXML->getElementByPath( 'tabs' );
			if( $tabsnode ) {
				//cycle through each tab
				foreach( $tabsnode->children() AS $tab ) {
					if ( $tab->name() == 'tab' ) {
						//install each tab
						$tabid						=	$this->installTab($pluginid,$tab);
						if ( $tabid ) {
							//get all fields in the tab
							$fieldsnode				=	$tab->getElementByPath( 'fields' );
							if ( $fieldsnode ) {
								//cycle through each field
								foreach( $fieldsnode->children() AS $field ) {
									if ($field->name() == "field") {
										//install each field
										//echo "installing field...";
										$fieldid	=	$this->installField($pluginid,$tabid,$field);
										//get all fieldvalues for the field
										//cycle through each fieldValue
										foreach( $field->children() AS $fieldValue) {	
											if ( $fieldValue->name() == "fieldvalue" ) {
												$this->installFieldValue($fieldid,$fieldValue);
											}
										}
									}
								}
							}
						} else {
							if ( $pluginRowWasNotExisting ) {
								if ( $db ) {
									$success			=	$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedElementName, 'drop', null );
								}
								$this->deleteTabAndFieldsOfPlugin( $row->id );	// delete tabs and private fields of plugin
								$row->delete();
							}
							cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
							return false;
						}
					}
				}
			}
			// (re)install field types of plugin:
			$fieldtypes							=&	$cbInstallXML->getElementByPath( 'fieldtypes' );
			if( $fieldtypes ) {
				foreach ( $fieldtypes->children() as $typ ) {
					if ( $typ->name() == 'field' ) {
						$this->installFieldType( $pluginid, $typ->attributes( 'type' ) );
					}
				}
			}
		}

		// Are there any SQL queries??
		$query_element							=&	$cbInstallXML->getElementByPath( 'install/queries' );
		if ( $query_element ) {
			foreach( $query_element->children() as $query ) {
				$_CB_database->setQuery( trim( $query->data() ) );
				if ( ! $_CB_database->query() )
				{
					$this->setError( 1, "SQL Error " . $_CB_database->stderr( true ) );
					if ( $pluginRowWasNotExisting ) {
						if ( $db ) {
							$success			=	$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedElementName, 'drop', null );
						}
						$this->deleteTabAndFieldsOfPlugin( $row->id );	// delete tabs and private fields of plugin
						$row->delete();
					}
					cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
					return false;
				}
			}
		}
		
		// Is there an installfile
		$installfile_elemet						=&	$cbInstallXML->getElementByPath( 'installfile' );

		if ( $installfile_elemet ) {
			// check if parse files has already copied the install.component.php file (error in 3rd party xml's!)
			if ( ! file_exists( $this->elementDir() . $installfile_elemet->data() ) ) {
				if( ! $this->copyFiles( $this->installDir(), $this->elementDir(), array( $installfile_elemet->data() ) ) ) {
					$this->setError( 1, 'Could not copy PHP install file.' );
					if ( $pluginRowWasNotExisting ) {
						if ( $db ) {
							$success			=	$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedElementName, 'drop', null );
						}
						$this->deleteTabAndFieldsOfPlugin( $row->id );	// delete tabs and private fields of plugin
						$row->delete();
					}
					cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
					return false;
				}
			}
			$this->hasInstallfile( true );
			$this->installFile( $installfile_elemet->data() );
		}
		// Is there an uninstallfile
		$uninstallfile_elemet					=&	$cbInstallXML->getElementByPath( 'uninstallfile' );
		if( $uninstallfile_elemet ) {
			if ( ! file_exists( $this->elementDir() . $uninstallfile_elemet->data() ) ) {
				if( ! $this->copyFiles( $this->installDir(), $this->elementDir(), array( $uninstallfile_elemet->data() ) ) ) {
					$this->setError( 1, 'Could not copy PHP uninstall file' );
					if ( $pluginRowWasNotExisting ) {
						if ( $db ) {
							$success			=	$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedElementName, 'drop', null );
						}
						$this->deleteTabAndFieldsOfPlugin( $row->id );	// delete tabs and private fields of plugin
						$row->delete();
					}
					cleanupInstall( null, $this->elementDir() );	// try removing directory and content just created successfully
					return false;
				}
			}
		}
		
		if ( $this->hasInstallfile() ) {
			if ( is_file( $this->elementDir() . '/' . $this->installFile() ) ) {
				require_once( $this->elementDir() . '/' . $this->installFile() );
				$ret							=	call_user_func_array( 'plug_' . $cleanedMainFileName . '_install', array() );
				if ( $ret != '' ) {
					$this->setError( 0, $desc . $ret );
				}
			}
		}

		if ( ( $db !== false ) && ( count( $db->children() ) > 0 ) ) {
			HTML_comprofiler::fixcbdbShowResults( $sqlUpgrader, true, false, $success, array(), array(), $this->elementName(), 1, false );
		}
		return true;
	}
	
	/**
	 * Installs a tab into database, finding already existing one if needed.
	 *
	 * @param  int                 $pluginid
	 * @param  CBSimpleXMLElement  $tab
	 * @return int|boolean         id of tab or FALSE in case of error (error saved with $this->setError() ).
	 */
	function installTab( $pluginid, &$tab ) {
		global $_CB_database, $_CB_framework;

		// Check to see if plugin tab already exists in db
		if ( $tab->attributes( 'class' ) ) {
			$query		=	"SELECT tabid FROM #__comprofiler_tabs WHERE " /* . "pluginid = " . (int) $pluginid . " AND " */ . "pluginclass = " . $_CB_database->Quote( $tab->attributes('class') );
		} else {
			$query		=	"SELECT tabid FROM #__comprofiler_tabs WHERE pluginid = " . (int) $pluginid . " AND pluginclass = ''";
		}
		$_CB_database->setQuery( $query );
		$tabid			=	$_CB_database->loadResult();

		if ( $tab->attributes( 'type' ) == 'existingSytemTab' ) {
			if ( $tabid == null ) {
				$this->setError( 1, 'installTab error: existingSystemTab' . ': ' . $tab->attributes( 'class' ) . ' ' . 'not found' . '.' );
				return false;
			}
		} else {
			$row			=	new moscomprofilerTabs( $_CB_database );
			if ( ! $tabid ) {
				$row->title							=	$tab->attributes('name');
				$row->description					=	trim( $tab->attributes('description') );
				$row->ordering						=	99;
				$row->position						=	$tab->attributes('position');
				$row->displaytype					=	$tab->attributes('displaytype');
				$row->ordering_register			=	$tab->attributes('ordering_register');
			}
			$row->width								=	$tab->attributes('width');
			$row->pluginclass						=	$tab->attributes('class');
			$row->pluginid							=	$pluginid;
			$row->fields							=	$tab->attributes('fields');
			$row->sys								=	$tab->attributes('sys');
	
			$userGroupName							=	$tab->attributes( 'useraccessgroup' );
			switch ( $userGroupName ) {
				case 'All Registered Users':
					$row->useraccessgroupid			=	-1;
					break;
				case 'Everybody':
				default:
					if ( $userGroupName && ( $userGroupName != 'Everybody' ) ) {
						$groupId					=	$_CB_framework->acl->get_group_id( $userGroupName, 'ARO' );
						if ( $groupId ) {
							$row->useraccessgroupid	=	$groupId;
					} else {
						$row->useraccessgroupid		=	-2;
					}
					break;
					}
			}
	
			if ( ! $row->store( $tabid ) ) {
				$this->setError( 1, 'SQL error' .': ' . $row->getError() );
				return false;
			}
	
			if ( ! $tabid ) {
				$tabid								=	$_CB_database->insertid();
			}
		}
		return $tabid;
	}
	
	/**
	* installs a field for plugin
	*
	* @param  int                 $pluginid    id of the plugin creating the field
	* @param  CBSimpleXMLElement  $field
	* @return int|false     fieldid or False on error
	*/
	function installField( $pluginid,$tabid,$field) {
		global $_CB_database, $_PLUGINS;

		// Check to see if plugin tab already exists in db
		$_CB_database->setQuery( "SELECT fieldid FROM #__comprofiler_fields WHERE name = '".$field->attributes('name')."'" );
		$fieldid				=	$_CB_database->loadResult();

		$row					=	new moscomprofilerFields( $_CB_database );
		$row->name				=	$field->attributes('name');
		$row->pluginid			=	$pluginid;
		$row->tabid				=	$tabid;
		$row->type				=	$field->attributes('type');
		$row->calculated		=	(int) $field->attributes('calculated');
		if (!$fieldid) {
			$row->title			=	$field->attributes('title');
			$row->description	=	trim( $field->attributes('description') );
			$row->ordering		=	99;
			$row->registration	=	$field->attributes('registration');
			$row->profile		=	$field->attributes('profile');
			$row->readonly		=	$field->attributes('readonly');
			$row->params		=	$field->attributes('params');
		}
		$dbTable				=&	$field->getElementByPath( 'database/table' );
		if ( $dbTable ) {
			$table				=	$dbTable->attributes( 'name' );
		} else {
			$table				=	$field->attributes('table');
		}
		if ( $table ) {
			$row->table			=	$table;
		} else {
			$row->table			=	'#__comprofiler';
		}

		// if the field type is unknown, suppose it's a field type of the plugin:
		$fieldTypePluginId		=	$_PLUGINS->getUserFieldPluginId( $row->type );
		if ( ! $fieldTypePluginId ) {
			// and register it so that the XML file for custom type can be found for store:
			$_PLUGINS->registerUserFieldTypes( array( $row->type => 'CBfield_' . $row->type ), $pluginid );
		}

		if (!$row->store($fieldid)) {
			$this->setError( 1, 'SQL error on field store2' .': ' . $row->getError() );
			return false;
		}
		
		if (!$fieldid) {
			$fieldid			=	$_CB_database->insertid();
		}
		return $fieldid;
	}

	function installFieldValue($fieldid,$fieldvalue) {
		global $_CB_database;		
		$row = new moscomprofilerFieldValues($_CB_database);
		$row->fieldid = $fieldid;
		$row->fieldtitle = $fieldvalue->attributes('title');
		$row->ordering = $fieldvalue->attributes('ordering');
		$row->sys = $fieldvalue->attributes('sys');
		
		$_CB_database->setQuery("SELECT fieldvalueid FROM #__comprofiler_field_values WHERE fieldid = ". (int) $fieldid . " AND fieldtitle = '".$row->fieldtitle."'");
		$fieldvalueid = $_CB_database->loadResult();
		
		if (!$row->store($fieldvalueid)) {
			$this->setError( 1, 'SQL error on field store' .': ' . $row->getError() );
			return false;
		}
		
		return true;
	}
	/**
	 * Installs field type (for now just updates pluginid of existing entries)
	 *
	 * @param int     $pluginid
	 * @param string  $fieldType
	 */
	function installFieldType( $pluginid, $fieldType ) {
		global $_CB_database;

		// Update already existing fields of this type in db
		$_CB_database->setQuery( "UPDATE #__comprofiler_fields SET pluginid = " . ( $pluginid === null ? "NULL" : (int) $pluginid ) . " WHERE type = '" . $_CB_database->getEscaped( $fieldType ) . "'" );
		$_CB_database->query();
	}
	/**
	* Gets XML of plugin
	* @param  int  $pluginId
	* @return CBSimpleXMLElement  or string if error
	*/
	function getXml( $id ) {
		global $_CB_database, $_CB_framework;

		$_CB_database->setQuery( "SELECT `name`, `folder`, `element`, `type`, `iscore` FROM #__comprofiler_plugin WHERE `id` = " . (int) $id );
		$row			=	null;
		$_CB_database->loadObject( $row );
		if ( $_CB_database->getErrorNum() ) {
			return $_CB_database->stderr();
		}
		if ( $row == null ) {
			return 'Invalid object id';
		}

		if ( trim( $row->folder ) == '' ) {
			return 'Folder field empty';
		} elseif ( cbStartOfStringMatch( $row->folder, '/' ) ) {
			$this->elementDir( $_CB_framework->getCfg('absolute_path') . $row->folder . '/' );
		} else {
			$this->elementDir( $_CB_framework->getCfg('absolute_path') . '/components/com_comprofiler/plugin/' . $row->type . '/' . $row->folder . '/' );
		}
		$this->installFilename( $this->elementDir() . $row->element . '.xml' );

		if ( ! ( file_exists( $this->installFilename() ) && is_readable( $this->installFilename() ) ) ) {
			return $row->name .' '. "has no readable xml file " . $this->i_installfilename;
		}

		cbimport('cb.xml.simplexml');
		return new CBSimpleXMLElement( trim( file_get_contents( $this->installFilename() ) ) );
	}
	/**
	 * Checks the plugin's database tables and upgrades if needed
	 * Backend-use only.
	 *
	 * Sets for $this->getErrors() $this->checkdbErrors and for $this->getLogs() $this->checkdbLogs
	 *
	 * @param  int             $pluginId
	 * @param  boolean         $upgrade    False: only check table, True: upgrades table (depending on $dryRun)
	 * @param  boolean         $dryRun     True: doesn't do the modifying queries, but lists them, False: does the job
	 * @return boolean|string              True: success: see logs, False: error, see errors, string: error
	 */
	function checkDatabase( $pluginId, $upgrade = false, $dryRun = false ) {
		global $_CB_database;

		$success									=	null;

		$cbInstallXML								=	$this->getXml( $pluginId );
		if ( is_object( $cbInstallXML ) ) {
			$db										=&	$cbInstallXML->getElementByPath( 'database' );
			if ( $db ) {
				// get the element name:
				$e									=&	$cbInstallXML->getElementByPath( 'name' );
				$this->elementName( $e->data() );
				$cleanedElementName					=	strtolower(str_replace(array(" ","."),array("","_"),$this->elementName()));
	
				cbimport( 'cb.sql.upgrader' );
				$sqlUpgrader						=	new CBSQLupgrader( $_CB_database, false );
				$sqlUpgrader->setDryRun( $dryRun );
				$success							=	$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedElementName, $upgrade, null );
/*
var_dump( $success );
echo "<br>\nERRORS: " . $sqlUpgrader->getErrors( "<br /><br />\n\n", "<br />\n" );
echo "<br>\nLOGS: " . $sqlUpgrader->getLogs( "<br /><br />\n\n", "<br />\n" );
exit;
*/
				$this->checkdbErrors				=	$sqlUpgrader->getErrors( false );
				$this->checkdbLogs					=	$sqlUpgrader->getLogs( false );
			}
		} else {
			$success								=	$cbInstallXML;
		}
		return $success;
	}
	function getErrors( ) {
		return $this->checkdbErrors;
	}
	function getLogs( ) {
		return $this->checkdbLogs;
	}
	/**
	 * Checks that plugin is properly installed and sets, if returned true:
	 * $this->i_elementdir   To the directory of the plugin (with final / )
	 * $this->i_xmldocument  To a CBSimpleXMLElement of the XML file
	 *
	 * @param  int     $id
	 * @param  string  $option
	 * @param  int     $client
	 * @param  string  $action
	 * @return boolean
	 */
	function checkPluginGetXml( $id, $option, $client = 0, $action = 'Uninstall' ) {
		global $_CB_database, $_CB_framework;

		$_CB_database->setQuery( "SELECT `name`, `folder`, `element`, `type`, `iscore` FROM #__comprofiler_plugin WHERE `id` = " . (int) $id );

		$row			=	null;
		$_CB_database->loadObject( $row );
		if ( $_CB_database->getErrorNum() ) {
			HTML_comprofiler::showInstallMessage( $_CB_database->stderr(), $action . ' -  error' ,
			$this->returnTo( $option, 'showPlugins') );
			return false;
		}
		if ($row == null) {
			HTML_comprofiler::showInstallMessage( 'Invalid object id', $action . ' -  error' ,
			$this->returnTo( $option, 'showPlugins') );
			return false;
		}

		if (trim( $row->folder ) == '') {
			HTML_comprofiler::showInstallMessage( 'Folder field empty, cannot remove files', $action . ' -  error',
			$this->returnTo( $option, 'showPlugins') );
			return false;
		}
		
		if ($row->iscore) {
			HTML_comprofiler::showInstallMessage( $row->name .' '. "is a core element, and cannot be uninstalled.<br />You need to unpublish it if you don't want to use it" ,
			'Uninstall -  error', $this->returnTo( $option, 'showPlugins') );
			return false;
		}

	if ( trim( $row->folder ) == '' ) {
			return 'Folder field empty';
		} elseif ( cbStartOfStringMatch( $row->folder, '/' ) ) {
			$this->elementDir( $_CB_framework->getCfg('absolute_path') . $row->folder . '/' );
		} else {
			$this->elementDir( $_CB_framework->getCfg('absolute_path') . '/components/com_comprofiler/plugin/' . $row->type . '/' . $row->folder . '/' );
		}
		$this->installFilename( $this->elementDir() . $row->element . '.xml' );

		if ( ! ( file_exists( $this->i_installfilename ) && is_readable( $this->i_installfilename ) ) ) {
			HTML_comprofiler::showInstallMessage( $row->name .' '. "has no readable xml file " . $this->i_installfilename . ", and might not be uninstalled completely." ,
			$action . ' -  warning', $this->returnTo( $option, 'showPlugins') );
		}

		// see if there is an xml install file, must be same name as element
		if ( file_exists( $this->i_installfilename ) && is_readable( $this->i_installfilename ) ) {
			cbimport('cb.xml.simplexml');
			$this->i_xmldocument	=&	new CBSimpleXMLElement( trim( file_get_contents( $this->i_installfilename ) ) );
		} else {
			$this->i_xmldocument	=	null;
		}
		return true;
	}
	/**
	 * plugin uninstaller with best effort depending on what it finds.
	 *
	 * @param  int     $id
	 * @param  string  $option
	 * @param  int     $client
	 * @param  string  $action
	 * @return boolean
	 */
	function uninstall( $id, $option, $client = 0 ) {
		global $_CB_database;

		$db						=	false;

		if ( $this->checkPluginGetXml( $id, $option, $client ) ) {
			if ( ( $this->i_xmldocument !== null ) && count( $this->i_xmldocument->children() ) > 0 ) {
				$cbInstallXML	=&	$this->i_xmldocument;
				
				// get the element name:
				$e =& $cbInstallXML->getElementByPath( 'name' );
				$this->elementName( $e->data() );
				// $cleanedElementName = strtolower(str_replace(array(" ","."),array("","_"),$this->elementName()));
				
				// get the files element
				$files_element =& $cbInstallXML->getElementByPath( 'files' );
				if ( $files_element ) {

					if ( count( $files_element->children() ) ) {
						foreach ( $files_element->children() as $file) {
							if ($file->attributes( "plugin" )) {
								$this->elementSpecial( $file->attributes( "plugin" ) );
								break;
							}
						}
						$cleanedMainFileName = strtolower(str_replace(array(" ","."),array("","_"),$this->elementSpecial()));
					}

					// Is there an uninstallfile
					$uninstallfile_elemet = &$cbInstallXML->getElementByPath( 'uninstallfile' );
					if ( $uninstallfile_elemet !== false ) {
						if (is_file( $this->i_elementdir . $uninstallfile_elemet->data())) {
							global $_PLUGINS;		// needed for the require_once below !
							require_once( $this->i_elementdir . $uninstallfile_elemet->data());
							$ret = call_user_func_array("plug_".$cleanedMainFileName."_uninstall", array());

							if ($ret != '') {
								$this->setError( 0, $ret );
							}
						}
					}

					$adminFS					=&	cbAdminFileSystem::getInstance();

					foreach ( $files_element->children() as $file ) {
						// delete the files
						$filename				=	$file->data();
						if ( file_exists( $this->i_elementdir . $filename ) ) {
							$parts				=	pathinfo( $filename );
							$subpath			=	$parts['dirname'];
							if ( $subpath <> '' && $subpath <> '.' && $subpath <> '..' ) {
								//echo '<br />'. 'Deleting'  .': '. $this->i_elementdir . $subpath;
								$result			=	$adminFS->deldir( _cbPathName( $this->i_elementdir . $subpath . '/' ) );
							} else {
								//echo '<br />'. 'Deleting'  .': '. $this->i_elementdir . $filename;
								$result			=	$adminFS->unlink( _cbPathName( $this->i_elementdir . $filename, false ) );
							}
							//echo intval( $result );
						}
					}
					
					// Are there any SQL queries??
					$query_element = &$cbInstallXML->getElementByPath( 'uninstall/queries' );
					if ( $query_element !== false ) {
						foreach ( $query_element->children() as $query )
						{
							$_CB_database->setQuery( trim( $query->data() ) );
							if ( ! $_CB_database->query() )
							{
								$this->setError( 1, "SQL Error " . $_CB_database->stderr( true ) );
								return false;
							}
						}
					}

					// Are there any Database statements ??
					$db										=&	$cbInstallXML->getElementByPath( 'database' );
					if ( ( $db !== false ) && ( count( $db->children() ) > 0 ) ) {
						cbimport( 'cb.sql.upgrader' );
						$sqlUpgrader						=	new CBSQLupgrader( $_CB_database, false );
//$sqlUpgrader->setDryRun( true );
						$success							=	$sqlUpgrader->checkXmlDatabaseDescription( $db, $cleanedMainFileName, 'drop', null );
/*
var_dump( $success );
echo "<br>\nERRORS: " . $sqlUpgrader->getErrors( "<br /><br />\n\n", "<br />\n" );
echo "<br>\nLOGS: " . $sqlUpgrader->getLogs( "<br /><br />\n\n", "<br />\n" );
exit;
*/
						if ( ! $success ) {
							$this->setError( 1, "Plugin database XML SQL Error " . 	$sqlUpgrader->getErrors() );
							return false;
						}
					}

					// Delete tabs and private fields of plugin:
					$this->deleteTabAndFieldsOfPlugin( $id );

					// remove XML file from front
					$xmlRemoveResult	=	$adminFS->unlink(  _cbPathName( $this->i_installfilename, false ) );
					$filesRemoveResult	=	true;

/*					// define folders that should not be removed
					$sysFolders = array(
					'content',
					'search'
					);
					if ( ! in_array( $row->folder, $sysFolders ) ) {
*/						// delete the non-system folders if empty
						if ( count( cbReadDirectory( $this->i_elementdir ) ) < 1 ) {
							$filesRemoveResult	=	$adminFS->deldir( $this->i_elementdir );
						}
/*					}
*/
					if ( ! $xmlRemoveResult ) {
						HTML_comprofiler::showInstallMessage( 'Could not delete XML file: ' . _cbPathName( $this->i_installfilename, false ) . ' due to permission error. Please remove manually.', 'Uninstall -  warning',
						$this->returnTo( $option, 'showPlugins') );
					}
					if ( ! $filesRemoveResult ) {
						HTML_comprofiler::showInstallMessage( 'Could not delete directory: ' . $this->i_elementdir . ' due to permission error. Please remove manually.', 'Uninstall -  warning',
						$this->returnTo( $option, 'showPlugins') );
					}
				}
			}

			$_CB_database->setQuery( "DELETE FROM #__comprofiler_plugin WHERE id = " . (int) $id );
			if (!$_CB_database->query()) {
				$msg = $_CB_database->stderr;
				HTML_comprofiler::showInstallMessage( 'Cannot delete plugin database entry due to error: ' . $msg, 'Uninstall -  error',
				$this->returnTo( $option, 'showPlugins') );
				return false;
			}
			if ( ( $this->i_xmldocument !== null ) && ( $db !== false ) && ( count( $db->children() ) > 0 ) ) {
				HTML_comprofiler::fixcbdbShowResults( $sqlUpgrader, true, false, $success, array(), array(), $this->elementName(), 1, false );
			}
			return true;
		}
		return false;
	}
	/**
	 * Deletes tabs and private fields of plugin id
	 *
	 * @param int $id   id of plugin
	 */
	function deleteTabAndFieldsOfPlugin( $id ) {
		global $_CB_database;

		//Find all tabs related to this plugin
		$_CB_database->setQuery( "SELECT `tabid`, `fields` FROM #__comprofiler_tabs WHERE pluginid=" . (int) $id );
		$tabs				=	$_CB_database->loadObjectList();
		if ( count( $tabs ) > 0 ) {
			$rowTab			=	new moscomprofilerTabs( $_CB_database );
			foreach( $tabs AS $tab ) {
				//Find all fields related to the tab
				$_CB_database->setQuery( "SELECT `fieldid`, `name` FROM #__comprofiler_fields WHERE `tabid`=" . (int) $tab->tabid . " AND `pluginid`=" . (int) $id );
				$fields		=	$_CB_database->loadObjectList();
				$rowField	=	new moscomprofilerFields( $_CB_database );
				
				//Delete fields and fieldValues, but not data content itself in the comprofilier table so they stay on reinstall
				if ( count( $fields ) > 0 ) {
					//delete each field related to a tab and all field value related to a field, but not the content
					foreach( $fields AS $field ) {
						//Now delete the field itself without deleting the user data, preserving it for reinstall
						//$rowField->deleteColumn('#__comprofiler',$field->name);	// this would delete the user data
						$rowField->delete( $field->fieldid );
					}
				}
				$fcount		=	0;
				if( $tab->fields ) {
					$_CB_database->setQuery( "SELECT COUNT(*) FROM #__comprofiler_fields WHERE tabid=" . (int) $tab->tabid );
					$fcount	=	$_CB_database->loadResult();
					if( $fcount > 0 ) {
						$_CB_database->setQuery( "UPDATE #__comprofiler_tabs SET `pluginclass`=null, `pluginid`=null WHERE `tabid`=" . (int) $tab->tabid );
						$_CB_database->query();
					} else {
						//delete each tab
						$rowTab->delete( $tab->tabid );
					}
				} else {
					//delete each tab
					$rowTab->delete( $tab->tabid );
				}
			}	
		}
		//Find all fields related to this plugin which are in other tabs, are calculated and delete them as they are of no use anymore:
		$_CB_database->setQuery( "SELECT `fieldid`, `name` FROM #__comprofiler_fields WHERE `calculated`=1 AND `sys`=0 AND `pluginid`=" . (int) $id );
		$fields		=	$_CB_database->loadObjectList();
		$rowField	=	new moscomprofilerFields( $_CB_database );
		if ( count( $fields ) > 0 ) {
			foreach( $fields AS $field ) {
				//Now delete the field itself:
				$rowField->delete( $field->fieldid );
			}
		}
		//Find all fields related to this plugin and set to NULL the now uninstalled plugin.
		$_CB_database->setQuery( "SELECT COUNT(*) FROM #__comprofiler_fields WHERE pluginid=" . (int) $id );
		$fieldsNumber		=	$_CB_database->loadResult();
		if ( $fieldsNumber > 0 ) {
			$_CB_database->setQuery( "UPDATE #__comprofiler_fields SET pluginid = NULL WHERE pluginid=" . (int) $id );
			$_CB_database->query();
		}
	}
}

?>
