<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.installer.installer' );
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

function com_install()
{
    $db =& JFactory::getDBO();

    // Set the wrapper extension default parameters
    $db->setQuery("SELECT `id` FROM `#__sefexts` WHERE `file` = 'com_wrapper.xml' LIMIT 1");
    $id = $db->loadResult();
    if( empty($id) ) {
        $db->setQuery("INSERT INTO `#__sefexts` (`file`, `params`) VALUES ('com_wrapper.xml', 'ignoreSource=0\nitemid=1\noverrideId=')");
        $res = $db->query();
        if( !$res ) {
            JError::raiseWarning(100, JText::_('Default parameters for Wrapper extension could not be set. Please, check them manually.'));
        }
    }

    // Install JoomSEF plugin
    $src = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sef'.DS.'plugin'.DS;
    $dest = JPATH_ROOT.DS.'plugins'.DS.'system'.DS;

    $res = JFile::copy($src.'joomsef.php', $dest.'joomsef.php');
    $res = $res && JFile::copy($src.'joomsef.xml', $dest.'joomsef.xml');

    $db->setQuery("INSERT INTO #__plugins
	               (id, name, element, folder, access, ordering, published, iscore, client_id, checked_out, checked_out_time, params)
	               VALUES ('', 'System - ARTIO JoomSEF', 'joomsef', 'system', 0, 0, 1, 0, 0, 0, '0000-00-00 00:00:00', '')");
    $res = $res && $db->query();

    if (!$res) {
        JError::raiseWarning(100, JText::_('WARNING_PLUGIN_NOT_INSTALLED').': '.$src);
    } else {
        JFolder::delete($src);
    }

    // Install content elements if JoomFish is present
    $file = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'joomfish.php';

    if( JFile::exists($file) ) {
        $src = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sef'.DS.'contentelements';
        $dest = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'contentelements';

        $files = JFolder::files($src);

        $res = true;
        foreach($files as $file) {
            $res = $res && JFile::copy($src.DS.$file, $dest.DS.$file);
        }

        if( !$res ) {
            JError::raiseWarning(100, JText::_('JoomSEF content elements for JoomFish not installed. Please install them manually from the following folder').': '.$src);
        } else {
            JFolder::delete($src);
        }
    }
    
    // Install the extension installer adapter if possible
    $adapterSrc = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sef'.DS.'adapters'.DS.'sef_ext.php';
    $adapterDest = JPATH_LIBRARIES.DS.'joomla'.DS.'installer'.DS.'adapters'.DS.'sef_ext.php';
    $adapterInstalled = false;
    if( is_writable(dirname($adapterDest)) ) {
        if( @copy($adapterSrc, $adapterDest) ) {
            $adapterInstalled = true;
        }
    }

    // Check former AceSEF and sh404SEF installations
    $formerSEF = false;

    $query = "SELECT * FROM `#__acesef_urls` LIMIT 1";
    $db->setQuery($query);
    $row = $db->loadObject();
    if( !is_null($row) ) {
        $formerSEF = true;
    }

    if( !$formerSEF ) {
        $query = "SELECT * FROM `#__redirection` LIMIT 1";
        $db->setQuery($query);
        $row = $db->loadObject();
        if( !is_null($row) ) {
            if( isset($row->oldurl) && isset($row->newurl) ) {
                $formerSEF = true;
            }
        }
    }

    ob_start();

    echo '<div class="quote" style="text-align: center;">';
    echo '<h1>ARTIO JoomSEF installed succesfully!</h1>';
    
    if( $formerSEF ) {
        echo '<h3>JoomSEF detected former installation of another SEF component. You can automatically import SEF URLs from it <a href="index.php?option=com_sef&amp;controller=sefurls&amp;task=showimport">here</a>.</h3><br />';
    }
    
    if( !$adapterInstalled ) {
        ?>
        <p class="message">
        The JoomSEF extension installer adapter could not be installed, because the destination directory is not writable.
        If you want to be able to install JoomSEF extensions directly from the Joomla Installer, please manually copy this file:
        <br />
        <?php echo str_replace(JPATH_ROOT, '', $adapterSrc); ?>
        <br />
        to this directory:
        <br />
        <?php echo str_replace(jPATH_ROOT, '', dirname($adapterDest)); ?>
        </p>
        <?php
    }
    
    echo '<h3>You must first edit the configuration, enable it and save before it will become active.</h3>';
    $readdocs = '<p class="message">Please scroll down and read the documentation.<br/>There is still extra configuration that you need to complete for ';
    if (!(strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') === false)) {
        echo $readdocs.'IIS.</p>';
    }
    else {
        // Get the correct rewrite base
        $base = JURI::root(true);
        if( $base == '' ) {
            $base = '/';
        }

        // Create htaccess content
        $htaccess = "
DirectoryIndex index.php
RewriteEngine On
RewriteBase {$base}

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/index.php
RewriteCond %{REQUEST_URI} (/|\.php|\.html|\.htm|\.feed|\.pdf|\.raw|/[^.]*)$  [NC]
RewriteRule (.*) index.php
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]

########## Begin - Rewrite rules to block out some common exploits
## If you experience problems on your site block out the operations listed below
## This attempts to block the most common type of exploit `attempts` to Joomla! 
#                              
# Block out any script trying to set a mosConfig value through the URL
RewriteCond %{QUERY_STRING} mosConfig_[a-zA-Z_]{1,21}(=|\%3D) [OR]
# Block out any script trying to base64_encode crap to send via URL
RewriteCond %{QUERY_STRING} base64_encode.*\(.*\) [OR]
# Block out any script that includes a <script> tag in URL
RewriteCond %{QUERY_STRING} (\<|%3C).*script.*(\>|%3E) [NC,OR]
# Block out any script trying to set a PHP GLOBALS variable via URL
RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]
# Block out any script trying to modify a _REQUEST variable via URL
RewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2})
# Send all blocked request to homepage with 403 Forbidden error!
RewriteRule ^(.*)$ index.php [F,L]
# 
########## End - Rewrite rules to block out some common exploits
";

        //if (substr(PHP_OS, 0, 3) == 'WIN') {
        //	echo '<p class="error">Found apache on windows, .htaccess is an illegal filename for this system.<br/>You must complete the rest of the configuration manually.</p>';
        //	echo $readdocs."the apache .htaccess file.</p>";
        //}
        //else{
        echo '<p style="text-align: center;">Checking for .htaccess in Joomla! root...<br />';
        $file = JPATH_ROOT.DS.'.htaccess';
        if( !JFile::exists($file) ) {
            echo 'not found.</p>';

            if( !JFile::write($file, $htaccess) ) {
                echo '<p style="text-align: center;" class="error">Unable to create .htaccess file in your Joomla! root. Please create this file yourself and add the following lines:<br/><pre>'.htmlspecialchars(nl2br($htaccess)).'</pre>';
            }
            else{
                echo "Successfully created .htaccess file in your Joomla! root with the following content:<br/><pre>".htmlspecialchars(nl2br($htaccess))."</pre>";
            }
            echo "Please check that the RewriteBase directive path is set correctly and matches your configuration.";
        }
        else {
            echo '<span class="error">Found existing .htaccess in Joomla! root.</span></p>';
            echo $readdocs.'the apache .htaccess file</p>';
        }
        echo '</div>';
    }

    include( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sef'.DS.'views'.DS.'info'.DS.'tmpl'.DS.'readme.inc.html' );

    $output = ob_get_contents();
    ob_end_clean();

    echo $output;
}
?>
