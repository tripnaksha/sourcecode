<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

class SEFModelHtaccess extends JModel
{
    var $_parsed;
    var $_file;
    var $_writable;
    var $_lines;
    var $_redirects;
    var $_linesNoRedirs;
    var $_symLinksEnabled;
    var $_baseEnabled;
    var $_baseValue;
    var $_symLinksLine;
    var $_baseLine;

    /**
     * Constructor that retrieves variables from the request
     */
    function __construct()
    {
        parent::__construct();

        // Load the needed data
        $this->_init();
    }

    function _init()
    {
        $this->_parsed = false;
        $this->_file = JPATH_ROOT.DS.'.htaccess';
        $this->_writable = is_writable($this->_file);
        $this->_lines = file($this->_file);
        $this->_redirects = array();
        $this->_linesNoRedirs = array();
        $this->_symLinksEnabled = false;
        $this->_baseEnabled = false;
        $this->_baseValue = '';
        $this->_symLinksLine = -1;
        $this->_baseLine = -1;
    }

    function IsWritable()
    {
        return $this->_writable;
    }

    function getLines()
    {
        return $this->_lines;
    }

    function getFile()
    {
        return implode('', $this->_lines);
    }

    function getRedirects()
    {
        if( !$this->_parsed ) {
            $this->_parseLines();
        }

        return $this->_redirects;
    }
    
    function getLists()
    {
        $lists = array();
        
        if( !$this->_parsed ) {
            $this->_parseLines();
        }

        $lists['symLinksEnable'] = JHTML::_('select.booleanlist', 'symLinksEnable', 'class="inputbox"', $this->_symLinksEnabled);
        $lists['baseEnable'] = JHTML::_('select.booleanlist', 'baseEnable', 'class="inputbox"', $this->_baseEnabled);
        $lists['baseValue'] = '<input type="text" size="40" class="inputbox" name="baseValue" value="'.$this->_baseValue.'" />';
        
        return $lists;
    }

    function _parseLines()
    {
        $this->_redirects = array();

        foreach($this->_lines as $line) {
            if( strpos($line, 'redirect 301 ') === 0 ) {
                // This is the redirect line
                $redir = substr(trim($line), 13);

                $redirect = $this->_parseRedirect($redir);
                if( $redirect === false ) {
                    // Add line to no-redirs lines
                    $this->_linesNoRedirs[] = $line;
                    continue;
                }

                $this->_redirects[] = $redirect;
                continue;
            }
            else if( ($pos = strpos($line, 'Options +FollowSymLinks')) !== false ) {
                // FollowSymLinks line - check if it is commented out
                if( $pos > 0 ) {
                    $pref = substr($line, 0, $pos);
                    if( strpos($pref, '#') !== false ) {
                        $this->_symLinksEnabled = false;
                    }
                    else {
                        $this->_symLinksEnabled = true;
                    }
                }
                else {
                    $this->_symLinksEnabled = true;
                }
                
                // Save the line number
                $this->_symLinksLine = count($this->_linesNoRedirs);
            }
            else if( ($pos = strpos($line, 'RewriteBase')) !== false ) {
                // RewriteBase line - check if it is commented out
                if( $pos > 0 ) {
                    $pref = substr($line, 0, $pos);
                    if( strpos($pref, '#') !== false ) {
                        $this->_baseEnabled = false;
                    }
                    else {
                        $this->_baseEnabled = true;
                    }
                }
                else {
                    $this->_baseEnabled = true;
                }
                
                // Get the base value
                $pos += strlen('RewriteBase');
                $str = trim(substr($line, $pos));
                $pos = strpos($str, '#');
                if( $pos !== false ) {
                    $str = substr($str, 0, $pos);
                }
                $this->_baseValue = $str;
                
                // Save the line number
                $this->_baseLine = count($this->_linesNoRedirs);
            }
            
            // Add line to no-redirs lines
            $this->_linesNoRedirs[] = $line;
        }

        $this->_parsed = true;
    }

    function _parseRedirect($str)
    {
        $from = $this->_parseString($str);
        $to = $this->_parseString($str);

        if( $from === false || $to === false ) {
            return false;
        }

        $redirect = new stdClass();
        $redirect->from = $from;
        $redirect->to = $to;

        return $redirect;
    }

    function _parseString(&$str) {
        // Skip spaces
        $str = ltrim($str);

        if( strlen($str) == 0 ) {
            // Nothing to parse
            return false;
        }

        if( $str[0] == '"' ) {
            // First character is quote, we need to find the ending one
            $pos = strpos($str, '"', 1);
            if( $pos === false ) {
                // Error
                return false;
            }

            $newstr = substr($str, 1, $pos-1);
            $str = substr($str, $pos+1);

            return $newstr;
        }
        else {
            // Just find the space
            $pos = strpos($str, ' ');
            if( $pos === false ) {
                $newstr = $str;
                $str = '';
                return $newstr;
            }

            $newstr = substr($str, 0, $pos);
            $str = substr($str, $pos);

            return $newstr;
        }
    }

    function storeAdvanced()
    {
        $filetext = JRequest::getString('filetext');

        return $this->_storeFile($filetext);
    }
    
    function storeSimple()
    {
        if( !$this->_parsed ) {
            $this->_parseLines();
        }

        $redirect = new stdClass();
        $redirect->from = trim(JRequest::getString('from'));
        $redirect->to = trim(JRequest::getString('to'));
        $redirect->id = JRequest::getInt('id');
        
        if( $redirect->from == '' || $redirect->to == '' ) {
            return false;
        }
        if( $redirect->from[0] != '/' ) {
            return false;
        }
        
        $regexp = '/^(http|https|ftp):\/\/(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/';
        $result = preg_match($regexp, $redirect->to);
        if( ($redirect->to[0] != '/') && ($result === false || $result === 0) ) {
            return false;
        }
        
        if( $redirect->id > 0 && $redirect->id <= count($this->_redirects) ) {
            // Replace existing redirect
            $this->_redirects[$redirect->id - 1] = $redirect;
            $newid = $redirect->id;
        }
        else {
            $this->_redirects[] = $redirect;
            $newid = count($this->_redirects);
        }
        
        if( $this->_storeRedirects() ) {
            return $newid;
        }
        else {
            return false;
        }
    }

    function remove()
    {
        if( !$this->_parsed ) {
            $this->_parseLines();
        }

        $cids = JRequest::getVar('cid', array(0), 'post', 'array');
        
        if( count($cids) > 0 ) {
            foreach($cids as $id) {
                unset($this->_redirects[$id - 1]);
            }
        }
        
        return $this->_storeRedirects();
    }
    
    function storeOptions()
    {
        if( !$this->_parsed ) {
            $this->_parseLines();
        }

        $symLinksEnable = JRequest::getBool('symLinksEnable');
        $baseEnable = JRequest::getBool('baseEnable');
        $baseValue = JRequest::getString('baseValue');
        
        // Edit the SymLinks line
        if( $this->_symLinksLine >= 0 ) {
            $str = '#';
            if( $symLinksEnable ) {
                $str = '';
            }
            
            $this->_linesNoRedirs[$this->_symLinksLine] = $str . 'Options +FollowSymLinks' . "\n";
        }
        else if( $symLinksEnable ) {
            // Add the SymLinks line if needed
            array_unshift($this->_linesNoRedirs, 'Options +FollowSymLinks'."\n");
            $this->_symLinksLine = 0;
            if( $this->_baseLine >= 0 ) {
                $this->_baseLine++;
            }
        }
        
        // Edit the RewriteBase line
        if( $this->_baseLine >= 0 ) {
            $str = '#';
            if( $baseEnable ) {
                $str = '';
            }
            
            $this->_linesNoRedirs[$this->_baseLine] = $str . 'RewriteBase ' . $baseValue . "\n";
        }
        else if( $baseEnable ) {
            // Add the RewriteBase line if needed
            array_unshift($this->_linesNoRedirs, 'RewriteBase ' . $baseValue . "\n");
            $this->_baseLine = 0;
            if( $this->_symLinksLine >= 0 ) {
                $this->_symLinksLine++;
            }
        }
        
        // Save the file
        return $this->_storeRedirects();
    }
    
    function _storeRedirects()
    {
        $file = '';
        foreach( $this->_redirects as $redir )
        {
            $file .= 'redirect 301 ' . $this->_quote($redir->from) . ' ' . $this->_quote($redir->to) . "\n";
        }
        $file .= implode($this->_linesNoRedirs);
        
        return $this->_storeFile($file);
    }
    
    function _storeFile($str)
    {
        $f = fopen($this->_file, 'w');
        if( $f === false ) {
            return false;
        }

        $result = fwrite($f, $str);
        fclose($f);

        if( $result === false ) {
            return false;
        }

        return true;
    }
    
    function getRedirect()
    {
        $array = JRequest::getVar('cid',  0, '', 'array');
        $id = (int)$array[0];
        
        if( !$this->_parsed ) {
            $this->_parseLines();
        }

        if( $id > 0 && $id <= count($this->_redirects) ) {
            $redirect = $this->_redirects[$id - 1];
            $redirect->id = $id;
        }
        else {
            $redirect = new stdClass();
            $redirect->from = '';
            $redirect->to = '';
            $redirect->id = 0;
        }
        
        return $redirect;
    }
    
    // Adds quotes if needed
    function _quote($str)
    {
        $pos = strpos($str, ' ');
        if( $pos === false ) {
            return $str;
        }
        
        return '"'.$str.'"';
    }
}
?>