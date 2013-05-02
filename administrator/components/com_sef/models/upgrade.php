<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.3.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.installer.helper');

class SEFModelUpgrade extends JModel
{
    function getUpgradeExts()
    {
        if (!isset($this->_upgradeExts)) {
            $this->_loadVersions();

            $basedir = JPATH_ROOT.DS.'components'.DS.'com_sef'.DS.'sef_ext';

            $extensions = array();
            if( count($this->_extVersions) > 0 ) {
                foreach( $this->_extVersions as $ext ) {
                    $xmlfile = $basedir.DS.$ext->name.'.xml';
                    if (!JFile::exists($xmlfile)) {
                        continue;
                    }

                    $xml =& JFactory::getXMLParser('Simple');
                    if (!$xml->loadFile($xmlfile)) {
                        unset($xml);
                        continue;
                    }

                    $root =& $xml->document;
                    if (!is_object($root) ||
                        ($root->name() != 'install') ||
                        version_compare($root->attributes('version'), '1.5', '<') ||
                        ($root->attributes('type') != 'sef_ext' ))
                    {
                        unset($xml);
                        continue;
                    }

                    $extension = new stdClass();
                    $extension->new = $ext->version;

                    $element         = &$root->getElementByPath('name');
                    $extension->name = $element ? $element->data() : '';

                    $element        = &$root->getElementByPath('version');
                    $extension->old = $element ? $element->data() : '';

                    $extensions[$ext->name] = $extension;
                }
            }

            $this->_upgradeExts = $extensions;
        }

        return $this->_upgradeExts;
    }

    function getNewSEFVersion()
    {
        if( !isset($this->_newSEFVersion) ) {
            $this->_loadVersions();
        }

        return $this->_newSEFVersion;
    }
    
    function getRegisteredInfo()
    {
        if (!isset($this->_regInfo) ) {
            $regInfo = new stdClass();
            
            $sefConfig =& SEFConfig::getConfig();
            if( trim($sefConfig->artioDownloadId) != '' ) {
                // Send the request to ARTIO server to check registration
                $data = array('download_id' => trim($sefConfig->artioDownloadId));
                $response = SEFTools::PostRequest($sefConfig->serverLicenser, null, $data);
                
                if( ($response === false) || ($response->code != 200) ) {
                    JError::raiseNotice(100, JText::_('ERROR_REG_CHECK_FAIL'));
                    return null;
                }
                else {
                    // Parse the response - get individual lines
                    $lines = explode("\n", $response->content);
                    
                    // Get the code
                    $pos = strpos($lines[0], ' ');
                    if( $pos === false ) {
                        JError::raiseNotice(100, JText::_('ERROR_REG_CHECK_FAIL'));
                        return null;
                    }
                    $regInfo->code = intval(substr($lines[0], 0, $pos));
                    
                    if( ($regInfo->code == 10) || ($regInfo->code == 20) ) {
                        // Download id found
                        if (count($lines) < 3) {
                            // Wrong response
                            JError::raiseNotice(100, JText::_('ERROR_REG_CHECK_FAIL'));
                            return null;
                        }
                        
                        // Parse the date
                        $date =& JFactory::getDate(str_replace('.', '/', trim($lines[1])));
                        $regInfo->date = $date->toFormat(JText::_('DATE_FORMAT_LC3'));
                        
                        // Parse the name
                        $regInfo->name = trim($lines[2]);
                        
                        // Parse the company
                        $regInfo->company = isset($lines[3]) ? trim($lines[3]) : '';
                        
                        // Is upgrade expired?
                        if ($regInfo->code == 20) {
                            JError::raiseNotice(100, JText::_('INFO_UPGRADE_LICENSE_EXPIRED') . ' ' . JText::_('INFO_YOU_GET_FREE_VERSION'));
                        }
                        // Is upgrade inactive
						if ($regInfo->code == 30) {
                            JError::raiseNotice(100, JText::_('INFO_UPGRADE_NOT_ACTIVE') . ' ' . JText::_('INFO_YOU_GET_FREE_VERSION'));
                            $regInfo->date = JText::_('not activated yet');
                        }                        
                    }
                    else if( $regInfo->code == 90 ) {
                        // Download id not found, do nothing
                        JError::raiseNotice(100, JText::_('ERROR_DOWNLOAD_ID_NOT_FOUND') . ' ' . JText::_('INFO_YOU_GET_FREE_VERSION'));
                    }
                    else {
                        // Wrong response
                        JError::raiseNotice(100, JText::_('ERROR_REG_CHECK_FAIL'));
                        return null;
                    }
                }
            }
            else {
                // Download ID not set
                JError::raiseNotice(100, JText::_('Download ID not set.') . ' ' . JText::_('INFO_YOU_GET_FREE_VERSION'));
                return null;
            }
            
            $this->_regInfo = $regInfo;
        }
        
        return $this->_regInfo;
    }

    function _loadVersions()
    {
        if (!isset($this->_newSEFVersion) || !isset($this->_extVersions)) {
            $sefConfig =& SEFConfig::getConfig();

            // Get the response from server
    		$response = SEFTools::PostRequest($sefConfig->serverNewVersionURL);
    		
    		// Check the response
    		if( ($response === false) || ($response->code != 200) ) {
    		    JError::raiseNotice(100, JText::_('ERROR_NO_VERSION_INFO'));
    		    $versions = '?.?.?';
    		}
    		else {
    		    $versions = $response->content;
    		}

            $versions = explode("\n", trim($versions));
            $this->_newSEFVersion = trim(array_shift($versions));

            $this->_extVersions = array();
            if (count($versions) > 0) {
                foreach($versions as $version) {
                    $parts = preg_split('/[\s]+/', $version);
                    
                    $ext = new stdClass();
                    $ext->name = $parts[0];
                    $ext->version = trim($parts[1]);
                    if (isset($parts[2])) {
                        $ext->link = trim($parts[2]);
                        $ext->type = 'Paid';
                    }
                    else {
                        $ext->link = '';
                        $ext->type = 'Free';
                    }
                    
                    $this->_extVersions[$ext->name] = $ext;
                }
            }
        }
    }
    
    function &getVersions()
    {
        $this->_loadVersions();
        
        return $this->_extVersions;
    }
    
    function getIsPaidVersion()
    {
        if( !isset($this->_isPaidVersion) ) {
            $check = SEFTools::GetSEFGlobalMeta();
            $ctrl = md5(implode(file(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_sef'.DS.'sef.xml')));
            
            $this->_isPaidVersion = ($check == $ctrl);
        }
        
        return $this->_isPaidVersion;
    }

    function upgrade()
    {
        $extDir = JPATH_ROOT.DS.'components'.DS.'com_sef'.DS.'sef_ext';

        $fromServer = JRequest::getVar('fromserver');
        $extension = JRequest::getVar('ext');

        if( !empty($extension) && !JFile::exists($extDir.DS.$extension.'.php')) {
            $this->setState('message', JText::_('You are not allowed to upgrade this extension.'));
            return false;
        }

        if( is_null($fromServer) ) {
            $this->setState('message', JText::_('Upgrade source not given.'));
            return false;
        }

        if( $fromServer == 1 ) {
            $package = $this->_getPackageFromServer($extension);
        } else {
            $package = $this->_getPackageFromUpload();
        }

        // was the package unpacked?
        if (!$package) {
            $this->setState('message', 'Unable to find install package.');
            return false;
        }

        // get current version
        if (empty($extension)) {
            $curVersion = SEFTools::getSEFVersion();
        } else {
            $curVersion = SEFTools::getExtVersion($extension);
        }
        if (empty($curVersion)) {
            $this->setState('message', JText::_('Could not find current version.'));
            JFolder::delete($package['dir']);
            return false;
        }

        // create an array of upgrade files
        $upgradeDir = $package['dir'].DS.'upgrade';
        $upgradeFiles = JFolder::files($upgradeDir, '.php$');

        if (empty($upgradeFiles)) {
            $this->setState('message', JText::_('This package does not contain any upgrade informations.'));
            JFolder::delete($package['dir']);
            return false;
        }

        // check if current version is upgradeable with downloaded package
        $reinstall = false;
        if (!in_array($curVersion . '.php', $upgradeFiles)) {
            if (empty($extension)) {
                // check if current version is being manually reinstalled with the same version package
                $xmlFile = $package['dir'] . DS . 'sef.xml';
                $packVersion = $this->_getXmlText($xmlFile, 'version');
                if (($packVersion == $curVersion) && JFile::exists($upgradeDir.DS.'reinstall.php')) {
                    // initiate the reinstall
                    $reinstall = true;
                    global $mainframe;
                    $mainframe->enqueueMessage(JText::_('INFO_SEF_REINSTALL'));
                }
            }

            if (!$reinstall) {
                $this->setState('message', JText::_('ERROR_CANT_UPGRADE'));
                JFolder::delete($package['dir']);
                return false;
            }
        }

        natcasesort($upgradeFiles);

        // prepare arrays of upgrade operations and functions to manipulate them
        $this->_fileError = false;
        $this->_fileList = array();
        $this->_sqlList = array();
        $this->_scriptList = array();

        if (!$reinstall) {
            // load each upgrade file starting with current version in ascending order
            foreach ($upgradeFiles as $uFile) {
                //if (!eregi("^[0-9]+\.[0-9]+\.[0-9]+\.php$", $uFile)) {
                
                if (!preg_match("/^[0-9]+\.[0-9]+\.[0-9]+\.php$/i", $uFile)) {
                    continue;
                }
                if (strnatcasecmp($uFile, $curVersion.".php") >= 0) {
                    require_once($upgradeDir.DS.$uFile);
                }
            }
        } else {
            // create list of all files to upgrade
            require_once($upgradeDir.DS.'reinstall.php');
        }

        if ($this->_fileError == false) {
            // set errors variable
            $errors = false;

            // first of all check if all the files are writeable
            // ONLY IF FTP IS DISABLED
    		jimport('joomla.client.helper');
    		$ftpOptions = JClientHelper::getCredentials('ftp');
    		
    		if( $ftpOptions['enabled'] != 1 ) {
                foreach ($this->_fileList as $dest => $op) {
                    $file = JPath::clean(JPATH_ROOT.DS.$dest);
    
                    // check if source file is present in upgrade package
                    if ($op->operation == 'upgrade') {
                        $from = JPath::clean($package['dir'].DS.$op->packagePath);
                        if( !JFile::exists($from) ) {
                            JError::raiseWarning( 100, JText::_('File does not exist in upgrade package') . ': ' . $op->packagePath );
                            $errors = true;
                        }
                    }
    
                    if ((($op->operation == 'delete')  && (JFile::exists($file))) ||
                    (($op->operation == 'upgrade') && (!JFile::exists($file)))) {
    
                        // if the file is to be deleted or created, the file's directory must be writable
                        $dir = dirname($file);
                        if (!JFolder::exists($dir)) {
                            // we need to create the directory where the file is to be created
                            if( !JFolder::create($dir) ) {
                                JError::raiseWarning( 100, JText::_('Directory could not be created') . ': ' . $dir );
                                $errors = true;
                            }
                        }
    
                        if (!is_writable($dir)) {
                            if (!JPath::setPermissions($dir, '0755', '0777')) {
                                JError::raiseWarning( 100, JText::_('Directory not writeable') . ': ' . $dir );
                                $errors = true;
                            }
                        }
                    }
                    elseif ($op->operation == 'upgrade') {
    
                        // the file itself must be writeable
                        if (!is_writable($file)) {
                            if (!JPath::setPermissions($file, '0755', '0777')) {
                                JError::raiseWarning( 100, JText::_('File not writeable') . ': ' . $file );
                                $errors = true;
                            }
                        }
                    }
                }
    		}

    		// If there are no errors, let's upgrade
            if (!$errors) {
                $db =& JFactory::getDBO();

                // execute SQL queries
                foreach ($this->_sqlList as $sql) {
                    $db->setQuery($sql);
                    if( !$db->query() ) {
                        JError::raiseWarning(100, JText::_('Unable to execute SQL query') . ': ' . $sql);
                        $errors = true;
                    }
                }

                // perform file operations
                foreach ($this->_fileList as $dest => $op) {
                    if ($op->operation == 'delete') {
                        $file = JPath::clean(JPATH_ROOT.DS.$dest);
                        if (JFile::exists($file)) {
                            $success = JFile::delete($file);
                            if (!$success) {
                                JError::raiseWarning(100, JText::_('Could not delete file. Please, check the write permissions on').' '.$dest);
                                $errors = true;
                            }
                        }
                    }
                    elseif ($op->operation == 'upgrade') {
                        $from = JPath::clean( $package['dir'].DS.$op->packagePath );
                        $to = JPath::clean( JPATH_ROOT.DS.$dest );
                        $destDir = dirname($to);

                        // create the destination directory if needed
                        if (!JFolder::exists($destDir)) {
                            JFolder::create($destDir);
                        }

                        $success = JFile::copy($from, $to);
                        if (!$success) {
                            JError::raiseWarning(100, JText::_('Could not rewrite file. Please, check the write permissions on').' '.$dest);
                            $errors = true;
                        }
                    }
                }

                // run scripts
                foreach ($this->_scriptList as $script) {
                    $file = JPath::clean( $package['dir'].DS.$script );
                    if( !JFile::exists($file) ) {
                        JError::raiseWarning(100, JText::_('Could not find script file').': '.$script);
                        $errors = true;
                    } else {
                        include($file);
                    }
                }
            }

            if (!$errors) {
                $what = (empty($extension)) ? JText::_('JoomSEF') : JText::_('SEF Extension').' '.$extension;
                $this->setState('message', $what . ' ' . JText::_('successfully upgraded.'));
            }
            else {
                $this->setState('message', JText::_('ERROR_UPGRADE_PROBLEM'));
            }
        }

        JFolder::delete($package['dir']);
        return true;
    }

    // Adds a file operation to $fileList
    // $joomlaPath - destination file path (e.g. '/administrator/components/com_sef/admin.sef.php')
    // $operation - can be 'delete' or 'upgrade'
    // $packagePath - source file path in upgrade package if $operation is 'upgrade' (e.g. '/admin.sef.php')
    function _addFileOp($joomlaPath, $operation, $packagePath = '')
    {
        if (!in_array($operation, array('upgrade', 'delete'))) {
            $this->fileError = true;
            JError::raiseWarning(100, JText::_('Invalid upgrade operation') . ': ' . $operation);
            return false;
        }

        // Do not check if file in package exists - it may be deleted in some future version during upgrade
        // It will be checked before running file operations
        $file = new stdClass();
        $file->operation = $operation;
        $file->packagePath = $packagePath;

        $this->_fileList[$joomlaPath] = $file;
    }

    function _addSQL($sql)
    {
        $this->_sqlList[] = $sql;
    }

    function _addScript($script)
    {
        $this->_scriptList[] = $script;
    }

    function _getPackageFromUpload()
    {
        // Get the uploaded file information
        $userfile = JRequest::getVar('install_package', null, 'files', 'array' );

        // Make sure that file uploads are enabled in php
        if (!(bool) ini_get('file_uploads')) {
            JError::raiseWarning(100, JText::_('WARNINSTALLFILE'));
            return false;
        }

        // Make sure that zlib is loaded so that the package can be unpacked
        if (!extension_loaded('zlib')) {
            JError::raiseWarning(100, JText::_('WARNINSTALLZLIB'));
            return false;
        }

        // If there is no uploaded file, we have a problem...
        if (!is_array($userfile) ) {
            JError::raiseWarning(100, JText::_('No file selected'));
            return false;
        }

        // Check if there was a problem uploading the file.
        if ( $userfile['error'] || $userfile['size'] < 1 )
        {
            JError::raiseWarning(100, JText::_('WARNINSTALLUPLOADERROR'));
            return false;
        }

        // Build the appropriate paths
        $config =& JFactory::getConfig();
        $tmp_dest = $config->getValue('config.tmp_path').DS.$userfile['name'];
        $tmp_src  = $userfile['tmp_name'];

        // Move uploaded file
        jimport('joomla.filesystem.file');
        $uploaded = JFile::upload($tmp_src, $tmp_dest);

        // Unpack the downloaded package file
        $package = JInstallerHelper::unpack($tmp_dest);

        // Delete the package file
        JFile::delete($tmp_dest);

        return $package;
    }

    function _getPackageFromServer($extension)
    {
        // Make sure that zlib is loaded so that the package can be unpacked
        if (!extension_loaded('zlib')) {
            JError::raiseWarning(100, JText::_('WARNINSTALLZLIB'));
            return false;
        }

        // build the appropriate paths
        $sefConfig =& SEFConfig::getConfig();
        $config =& JFactory::getConfig();
        if( empty($extension) ) {
            $tmp_dest = $config->getValue('config.tmp_path').DS.'joomsef.zip';
        }
        else {
            $tmp_dest = $config->getValue('config.tmp_path').DS.$extension.'.zip';
        }

        // Validate the upgrade on server
        $data = array();
        $data['username'] = $sefConfig->artioUserName;
        $data['password'] = $sefConfig->artioPassword;
        if( empty($extension) ) {
            $data['download_id'] = $sefConfig->artioDownloadId;
            $data['file'] = 'com_joomsef';
        }
        else {
            $params =& SEFTools::getExtParams($extension);
            $data['download_id'] = $params->get('downloadId', '');
            $data['file'] = 'ext_joomsef3_' . substr($extension, 4);
        }
        $uri = parse_url(JURI::root());
        $url = $uri['host'].$uri['path'];
        $url = trim($url, '/');
        $data['site'] = $url;
        $data['ip'] = $_SERVER['SERVER_ADDR'];
        $lang =& JFactory::getLanguage();
        $data['lang'] = $lang->getTag();
        $data['cat'] = 'joomsef3';

        // Get the server response
        $response = SEFTools::PostRequest($sefConfig->serverAutoUpgrade, JURI::root(), $data);

        // Check the response
        if( ($response === false) || ($response->code != 200) ) {
            JError::raiseWarning(100, JText::_('Connection to server could not be established.'));
            return false;
        }
        
        // Response OK, check what we got
        if( strpos($response->header, 'Content-Type: application/zip') === false ) {
            JError::raiseWarning(100, $response->content);
            return false;
        }
        
        // Seems we got the ZIP installation package, let's save it to disk
        if (!JFile::write($tmp_dest, $response->content)) {
            JError::raiseWarning(100, JText::_('Unable to save installation file in temp directory.'));
            return false;
        }

        // Unpack the downloaded package file
        $package = JInstallerHelper::unpack($tmp_dest);

        // Delete the package file
        JFile::delete($tmp_dest);

        return $package;
    }

    function _getXmlText($file, $variable)
    {
        // try to find variable
        $value = null;
        if (JFile::exists($file)) {
            $xml =& JFactory::getXMLParser('Simple');

            if ($xml->loadFile($file)) {
                $root =& $xml->document;
                $element =& $root->getElementByPath($variable);
                $value = $element ? $element->data() : '';
            }
        }

        return $value;
    }

}
?>