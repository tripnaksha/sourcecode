<?php
/**
 * Community Builder SEF extension for Joomla!
 *
 * @author      $Author: David Jozefov $
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access.');

if( !defined('_CB_SHOWFORM') )  define('_CB_SHOWFORM', 'showform');

class SefExt_com_comprofiler extends SefExt
{
    // Returns user name for given id
    function GetUser($id) {
        $database =& JFactory::getDBO();

        $database->setQuery("SELECT `username` FROM `#__users` WHERE `id` = $id");
        return $database->loadResult();
    }
    
    // Returns user list title for given list id
    function GetUserList($id = null) {
        $sefConfig =& SEFConfig::getConfig();
        $database =& JFactory::getDBO();
        
        $jfTranslate = ($sefConfig->translateNames ? ', `listid`' : '');
        if( !is_null($id) ) {
            $database->setQuery("SELECT `title`$jfTranslate FROM `#__comprofiler_lists` WHERE `listid` = '$id'");
        } else {
            $database->setQuery("SELECT `title`$jfTranslate FROM `#__comprofiler_lists` WHERE `default` = '1' AND `published` = '1'");
        }
        
        $row = $database->loadObject();
        
        return (isset($row->title) ? $row->title : '');
    }

    function create(&$uri) {
        $sefConfig =& SEFConfig::getConfig();
        $language =& JFactory::getLanguage();
        $curLang = $language->getBackwardLang();

        $params = SEFTools::getExtParams('com_comprofiler');

        // Include community builder language file
        $path = JPATH_ROOT.DS.'components'.DS.'com_comprofiler'.DS.'plugin'.DS.'language';
        if( file_exists($path.DS.$curLang.DS.$curLang.'.php') ) {
            include_once( $path.DS.$curLang.DS.$curLang.'.php' );
        } else {
            include_once( $path.DS.'default_language'.DS.'default_language.php' );
        }

        // Extract variables
        $vars = $uri->getQuery(true);
        extract($vars);
        $title = array();

        if( $params->get('addtitle', '2') != '0' ) {
            $title[] = JoomSEF::_getMenuTitle(@$option, @$task, @$Itemid);
        }

        if( isset($task) ) {
            switch($task) {
                case 'userProfile':
                    //$title[] = _UE_PROFILE;

                    // Remove menu title if set to
                    if( $params->get('addtitle', '2') == '1' ) {
                        $title = array();
                    }

                    // Don't add suffix if set to
                    if( ($params->get('addsuffix', '1') != '1') && ($sefConfig->suffix != '') ) {
                        $suffix = $sefConfig->suffix;
                        $sefConfig->suffix = '';
                    }

                    $title[] = $this->GetUser($user);
                    unset($task);
                    if( isset($act) ) {
                        $title[] = $act;
                    }

                    if( isset($profilebookshowform) && ($profilebookshowform == 1) ) {
                        $title[] = _CB_SHOWFORM;
                    }

                    break;

                case 'emailUser':
                case 'banProfile':
                case 'reportUser':
                    $tasks = array( 'emailUser' => _UE_EMAIL,
                    'banProfile' => _UE_BANPROFILE,
                    'reportUser' => _UE_REPORTUSER );
                    $title[] = $tasks[$task];
                    $title[] = $this->GetUser($uid);
                    unset($task);
                    break;

                case 'acceptConnection':
                case 'addConnection':
                case 'removeConnection':
                    $tasks = array( 'acceptConnection' => _UE_ACCEPTCONNECTION,
                    'addConnection' => _UE_ADDCONNECTION,
                    'removeConnection' => _UE_REMOVECONNECTION );
                    $title[] = $tasks[$task];
                    $title[] = $this->GetUser($connectionid);
                    unset($task);
                    break;

                case 'userAvatar':
                    if( isset($do) && ($do == 'deleteavatar') )
                    $title[] = _UE_DELETE_AVATAR;
                    else
                    $title[] = _UE_AVATAR;
                    unset($task);
                    break;

                case 'manageConnections':
                case 'saveConnections':
                case 'teamCredits':
                case 'userDetails':
                    $tasks = array( 'manageConnections' => _UE_MANAGECONNECTIONS,
                    'saveConnections' => _UE_UPDATE,
                    'teamCredits' => _UE_MENU_ABOUT_CB,
                    'userDetails' => _UE_USERPROFILE );
                    $title[] = $tasks[$task];
                    unset($task);
                    break;
                    
                case 'usersList':
                    $title[] = $this->GetUserList( (isset($listid) ? $listid : null) );
                    
                    if( isset($action) && ($action == 'search') ) {
                        $title[] = _UE_SEARCH;
                    }
                    
                    unset($task);
                    break;
            }

            if( !$sefConfig->appendNonSef ) {
                if( isset($tab) )           $title[] = $tab;
                if( isset($limitstart) )    $title[] = $limitstart;
            }
        }

        if( count($title) == 0 ) {
            $title[] = JoomSEF::_getMenuTitle(@$option, @$task, @$Itemid);
        }

        $newUri = $uri;
        if (count($title) > 0) {
            $nonSefVars = array();
            if( isset($tab) )           $nonSefVars['tab'] = $tab;
            if( isset($limitstart) )    $nonSefVars['limitstart'] = $limitstart;

            $ignoreVars = array();
            if( isset($cbsecurityg1) )  $ignoreVars['cbsecurityg1'] = $cbsecurityg1;

            $newUri = JoomSEF::_sefGetLocation($uri, $title, @$task, @$limit, @$limitstart, @$lang, $nonSefVars, $ignoreVars);
        }

        if( ($params->get('addsuffix', '1') != '1') && isset($suffix) ) {
            $sefConfig->suffix = $suffix;
        }

        return $newUri;
    }
}
?>
