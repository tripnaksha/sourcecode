<?php
/**
* @version $Id: cb.session.php 444 2008-04-24 18:25:39Z beat $
* @package Community Builder
* @subpackage cb.session.php
* @author Beat
* @copyright (C) 2008-2009 Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// no direct access
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


/**
 * CB 1.x SESSIONS functions:
 */

/**
 * Checks that all globals are safe to known PHP and Zend bugs:
 */
function cbCheckSafeGlobals() {
	static $banned	=	array( '_files', '_env', '_get', '_post', '_cookie', '_server', '_session', 'globals' );
	$check			=	array( &$_FILES, &$_ENV, &$_GET, &$_POST, &$_COOKIE, &$_SERVER );
	for ( $i = 0, $n = count( $check ) ; $i < $n ; $i++ ) {
		foreach ( array_keys( $check[$i] ) as $k ) {
			// check for PHP globals injection bug and for PHP Zend_Hash_Del_Key_Or_Index bug:
			if ( in_array( strtolower( $k ), $banned ) || is_numeric( $k ) ) {
				die( sprintf( 'Illegal variable %s.', addslashes( htmlspecialchars( $k ) ) ) );
			}
		}
	}
}
/**
 * Unregister super-globals if register_globals is set
 */
function cbUnregisterGlobals() {
	if ( ini_get( 'register_globals' ) ) {
		$check		=	array( &$_SERVER, &$_ENV, &$_FILES, &$_COOKIE, &$_POST, &$_GET );
		if ( isset( $_SESSION ) ) {
			array_unshift ( $check, $_SESSION );
		}
		for ( $i = 0, $n = count( $check ) ; $i < $n ; $i++ ) {
			foreach ( array_keys( $check[$i] ) as $key ) {
				if ( $key != 'GLOBALS' ) {
					unset( $GLOBALS[$key] );
				}
			}
		}
	}
}


class CBCookie {
	/**
	 * PHP setcookie but smarter and more secure:
	 * //TBD: add domain info in cookie-name
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  int     $expire
	 * @param  string  $path
	 * @param  string  $domain
	 * @param  boolean $secure
	 * @param  boolean $httponly
	 * @return boolean
	 */
	function setcookie( $name, $value = '', $expire = false, $path = null, $domain = null, $secure = false,  $httponly = false ) {
		global $_CB_framework, $_SERVER;
	
		static $PrivacyHeaderSent		=	false;
	
		if ( ! $PrivacyHeaderSent ) {
			header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');		// needed for IE6 to accept this cookie in higher security setting.
			$PrivacyHeaderSent			=	true;
		}
	
		$sp								=	session_get_cookie_params();
	
		if ( ( $domain === null ) || ( $path === null ) ) {
			$matches					=	null;
			if ( $_CB_framework ) {
				$live_ok				=	( preg_match( '#^https?://([^/]+)(.*)#i', $_CB_framework->getCfg( 'live_site' ), $matches ) );
			} else {
				$live_ok				=	false;
			}
			
		}
		if ( $domain === null ) {
			// handles www and non-www domains: e.g. live_site = 'site.com' but on 'www.site.com' (or the other way around):
			// in that case, cookie-domain needs to be '.site.com':
			if ( $live_ok ) {
				$pageDomain				=	$_SERVER['HTTP_HOST'];
				$liveDomain				=	$matches[1];
				if ( $liveDomain === $pageDomain ) {
					$domain				=	$liveDomain;
				} else {
					$live_len			=	strlen( $liveDomain );
					$page_len			=	strlen( $pageDomain );
					if ( ( $live_len < $page_len )
						&& ( $liveDomain === substr( $pageDomain, $page_len - $live_len ) )
						&& ( substr( $pageDomain, $page_len - $live_len - 1, 1 ) === '.' ) )
					{
						// ends of domains match, but live_site domain is shorter (e.g. no 'www.'):
						$domain			=	'.' . $liveDomain;		// '.' in front needed for 2-3 dots security-rule of browsers ( '.site.com' )
					} elseif ( ( $live_len > $page_len )
						&& ( $pageDomain === substr( $liveDomain, $live_len - $page_len ) )
						&& ( substr( $liveDomain, $live_len - $page_len - 1, 1 ) === '.' ) )
					{
						$domain			=	'.' . $pageDomain;
					}
				}
			}
			if ( $domain === null ) {
				$domain					=	$sp['domain'];
			}
		}
		if ( substr_count( $domain, '.' ) < 2 ) {
			$domain						=	null;
		}
		if ( $path === null ) {
			$directory_len				=	strlen( $matches[2] );
			if ( $live_ok && ( $directory_len > 1 ) ) {
				// get the query string:
				if ( ! empty( $_SERVER['PHP_SELF'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ) {
					$queryString		=	urldecode( $_SERVER['REQUEST_URI'] );	// Apache
				} else {
					$queryString		=	urldecode( $_SERVER['SCRIPT_NAME'] );	// IIS
					// that part is not needed in this case:
					//	if (isset($_SERVER['QUERY_STRING']) && ! empty($_SERVER['QUERY_STRING'])) {
					//		$return	.=	'?' . $_SERVER['QUERY_STRING'];
					//	}
				}
				if ( substr( $queryString, 0, $directory_len ) === $matches[2] ) {
					$path				=	$matches[2];
				}
			}
			if ( $path === null ) {
				$path					=	'/';		// $sp['path']
			}
		}
		if ( isset( $sp['secure'] ) ) {
			if ( $secure === null ) {
				$secure					=	$sp['secure'];
			}
			if ( isset( $sp['httponly'] ) ) {
				// php >= 5.2.0:
				return setcookie( $name, $value, $expire, $path, $domain, $secure, $httponly );
			} else {
				// php < 5.2.0, but > 4.0.4:
				return setcookie( $name, $value, $expire, $path, $domain, $secure );
			}
		} else {
			return setcookie( $name, $value, $expire, $path, $domain );
		}
	}
	/**
	 * gets cookie set by cbSetcookie ! WARNING: always unescaped
	 * //TBD: add domain info in cookie-name
	 *
	 * @param  string            $name
	 * @param  string|array      $defaultValue
	 * @return string|array|null
	 */
	function getcookie( $name, $defaultValue = null ) {
		global $_COOKIE;
	
		return cbStripslashes( cbGetParam( $_COOKIE, $name, $defaultValue ) );
	}
}	// class CBCookie

/**
 * CLASS implements a minimal database connection working with CB database when present
 * and with MySql directly if not.
 */
class CBSessionStorage {
	var $_db;
	var $_table_prefix;
	var $_resource;
	var $_cursor;
	var $_sql;
	/**
	 * Constructor
	 *
	 * @return CBSessionStorage
	 */
	function CBSessionStorage( ) {
	}
	/**
	 * Connects to database layer or to mysql database
	 *
	 * @return CBSessionStorage   or $_CB_database
	 */
	function & connect() {
		if ( ! $this->_db ) {
			global $_CB_database;
			if ( $_CB_database ) {
				$this->_db					=&	$_CB_database;
			} else {
				$absolute_path				=	preg_replace( '%(/[^/]+){5}$%', '', str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$config						=	file_get_contents( $absolute_path . '/configuration.php' );
				$db_host					=	$this->_parseConfig( $config, 'host' );
				$db_user					=	$this->_parseConfig( $config, 'user' );
				$db_password				=	$this->_parseConfig( $config, 'password' );
				$db_db						=	$this->_parseConfig( $config, 'db' );
				$this->_db					=	new CBSessionStorage();
				$this->_db->_resource		=	mysql_connect( $db_host, $db_user, $db_password );
				if ( $this->_db->_resource === false ) {
					die( 'Session connect error!' );
				}
				if ( ! mysql_select_db( $db_db, $this->_db->_resource ) ) {
					die( 'Session database error!' );
				}
				$this->_db->_table_prefix	=	$this->_parseConfig( $config, 'dbprefix' );
			}
		}
		return $this->_db;
	}
	/**
	 * Parses a mambo/joomla/compatibles configuration file content
	 * @access private
	 *
	 * @param  string  $config   Content of config file
	 * @param  string  $varName  Name of variable to fetch
	 * @return string            Content of variable or NULL
	 */
	function _parseConfig( $config, $varName ) {
		$matches		=	null;
		preg_match( '/\$(?:mosConfig_)?' . $varName . '\s*=\s*\'([^\']*)\'/', $config, $matches );
        if ( isset($matches[1]) ) {
        	return $matches[1];
        } else {
        	return null;
        }
	}
	/**
	* Sets the SQL query string for later execution.
	*
	* This function replaces a string identifier $prefix with the
	* string held is the _table_prefix class variable.
	*
	* @param string $sql     The SQL query
	* @param int    $offset  The offset to start selection
	* @param int    $limit   The number of results to return
	* @param string $prefix  The common table prefix search for replacement string
	*/
	function setQuery( $sql, $offset = 0, $limit = 0, $prefix='#__' ) {
		if ( $offset || $limit ) {
			$sql		.=	" LIMIT ";
			if ( $offset ) {
				$sql	.=	( (int) $offset ) . ', ';
			}
			$sql		.=	( (int) $limit );
		}
		$this->_sql		=	$this->replacePrefix( $sql, $prefix );
	}
	/**
	 * Replace $prefix with $this->_table_prefix (simplified method)
	 * @access private
	 *
	 * @param  string  $sql      SQL query
	 * @param  string  $prefix   Common table prefix
	 */
	function replacePrefix( $sql, $prefix = '#__' ) {
		return str_replace( $prefix, $this->_table_prefix, $sql );
	}
	/**
	* Execute the query
	* 
	* @param  string  the query (optional, it will use the setQuery one otherwise)
	* @return mixed A database resource if successful, FALSE if not.
	*/
	function query( $sql = null ) {
		if ( $sql ) {
			$this->setQuery( $sql );
		}
		$this->_cursor	=	mysql_query( $this->_sql, $this->_resource );
		return $this->_cursor;
	}
	/**
	* Fetch a result row as an associative array
	*
	* @return array
	*/
	function loadAssoc( ) {
		if ( ! ( $cur = $this->query() ) ) {
			$result		=	null;
		} else {
			$result		=	mysql_fetch_assoc( $cur );
			if ( ! $result ) {
				$result	=	null;
			}
			mysql_free_result( $cur );
		}
		return $result;
	}
	/**
	* Get a database escaped string
	*
	* @param  string  $text
	* @return string
	*/
	function getEscaped( $text ) {
		return mysql_real_escape_string( $text, $this->_resource );
	}
	/**
	* Get a quoted database escaped string
	*
	* @param  string  $text
	* @return string
	*/
	function Quote( $text ) {
		return '\'' . $this->getEscaped( $text ) . '\'';
	}
	/**
	* Quote an identifier name (field, table, etc)
	*
	* @param  string  $s  The name
	* @return string      The quoted name
	*/
	function NameQuote( $s ) {
		return '`' . $s . '`';
	}
}

/**
 * This class implements CB independant database-based scalable cookies-only-based secure sessions.
 *
 */
class CBSession {
	var $_session_id		=	null;
	var $_session_var		=	null;
	var $_cookie_name		=	'cb_web_session';
	var $_cookie_verify		=	'cb_web_session_verify';
	var $_life_time			=	null;
	var $_sessionRecord		=	null;
	var $_mode;
	/**
	 * Mini-db
	 * @var CBSessionStorage
	 */
	var $_db				=	null;
	/**
	 * Constructor
	 * @access private
	 * 
	 * @param  string     $mode  'cookie' for most secure way (requires cookies enabled), 'sessionid': set id by session
	 * @return CBSession
	 */
	function CBSession( $mode = 'cookie' ) {
		$this->_mode		=	$mode;
		// Read the maxlifetime setting from PHP:
		$this->_life_time	=	max( get_cfg_var( 'session.gc_maxlifetime' ), 43200 );	// 12 hours minimum
		$dbConnect			=	new CBSessionStorage();
		$this->_db			=	$dbConnect->connect();
	}
	/**
	 * Gets singleton
	 *
	 * @param  string     $mode  'cookie' for most secure way (requires cookies enabled), 'sessionid': set id by session
	 * @return CBSession
	 */
	function & getInstance( $mode = 'cookie' ) {
		static $session					=	array();
		if ( ! isset( $session[$mode] ) ) {
			$session[$mode]				=	new CBSession( $mode );
		}
		return $session[$mode];
	}
	/**
	 * session_start() creates a session or resumes the current one based on the current session id
	 * that's being passed via a cookie.
	 *
	 * If you want to use a named session, you must call session_name() before calling session_start().
	 *
	 * session_start() will register internal output handler for URL rewriting when trans-sid is enabled.
	 * If a user uses ob_gzhandler or like with ob_start(), the order of output handler is important for proper output.
	 * For example, user must register ob_gzhandler before session start.
	 *
	 * @param  string  $mode   'cookie' for most secure way (requires cookies enabled), 'sessionid': set id by session
	 * @return bool      True: ok, False: already started
	 */
	function session_start( $_noNewSession = false ) {

		if ( $this->_session_var !== null ) {
			// session already started:
			return false;
		}
		if ( $this->_mode == 'cookie' ) {
			$cookie							=	CBCookie::getcookie( $this->_cookie_name, null );
			if ( $cookie !== null ) {
				// session existing in browser:
				$session_id					=	substr( $cookie, 0, 32 );
			} else {
				$session_id					=	null;
			}
		} elseif ( $this->_mode == 'sessionid' ) {
			$session_id						=	substr( $this->_session_id, 0, 32 );
		} else {
			return false;
		}

		if ( $session_id ) {
				$session_data				=	$this->read( $session_id );
				if ( $session_data ) {
					// session found in database:
					$session_var			=	unserialize( $session_data );
					if ( ( $session_var !== false ) && ( $this->_validateSession( $session_id, $session_data ) ) ) {
						// valid session has been retrieved:
						$this->_session_id	=	$session_id;
						$this->_session_var	=	$session_var;
						return true;
					}
				}
			}
		if ( $_noNewSession ) {
			return false;
		}
		// no valid session has been found: create a new one:
		$this->_session_id				=	$this->generateRandSessionid( 32 );
		$this->_session_var				=	array( 'cbsessions.verify' => $this->generateRandSessionid( 32 ) );
		$this->_validateSession();		// set the session
		if ( $this->_mode == 'cookie' ) {
			$this->_sendSessionCookies();
		}
		return true;
	}
	/**
	 * Sends out the session cookies
	 * @access private
	 *
	 * @return boolean  FALSE if headers already sent.
	 */
	function _sendSessionCookies() {
		global $_SERVER;

		$isHttps			=	(isset($_SERVER['HTTPS']) && ( !empty( $_SERVER['HTTPS'] ) ) && ($_SERVER['HTTPS'] != 'off') );
		return CBCookie::setcookie( $this->_cookie_name, $this->_session_id, false, null, null, $isHttps, true );
	}
	/**
	 * Regenerates a new session id, keeping session data
	 *
	 */
	function session_regenerate( ) {
		if ( ! $this->_session_id ) {
			// tries to load existing session:
			$this->session_start( true );
		}
		if ( $this->_session_id ) {
			$this->destroy( $this->_session_id );
		}
		$this->_session_id		=	$this->generateRandSessionid( 32 );
		return $this->_sendSessionCookies();
	}
	/**
	 * End the current session and store session data.
	 *
	 * @return bool
	 */
	function session_write_close( ) {
		// store:
		if ( ! $this->write( $this->_session_id, serialize( $this->_session_var ) ) ) {
			die( 'Session write error!' );
		}
		// timeout old sessions:
		$this->gc();
		return true;
	}
	/**
	 * Gets value of the session variable, change it with $this->set()
	 * (not a reference, use $this->get_reference() for reference)
	 *
	 * @param  string  $name
	 * @return mixed          NULL if not set
	 */
	function get( $name ) {
		if ( isset( $this->_session_var[$name] ) ) {
			return $this->_session_var[$name];
		} else {
			$null						=	null;
			return $null;
		}
	}
	/**
	 * Sets a value to the session variable $name (Not a reference)
	 *
	 * @param  string  $name
	 * @param  mixed   $value
	 */
	function set( $name, $value ) {
		$this->_session_var[$name]		=	$value;
	}
	/**
	 * Gets a reference to the session variable (which can be changed)
	 *
	 * @param  string  $name
	 * @return mixed          If empty/new: NULL
	 */
	function & getReference( $name ) {
		if ( ! isset( $this->_session_var[$name] ) ) {
			$this->_session_var[$name]	=	null;
		}
		return $this->_session_var[$name];
	}
	/**
	 * Unset current session
	 *
	 * @return bool           True success, False failed
	 */
	function session_unset() {
		if ( $this->_session_id ) {
			$this->destroy( $this->_session_id );
			$this->_session_id	=	null;
			$this->_session_var	=	null;
		}
	}
	/**
	 * Sets/Gets current session id for get (warning: lower security)
	 *
	 * @param  string  $id  new     if change
	 * @return string       current if no change ( $id = null ) if session started already
	 */
	function session_id( $id = null ) {
		if ( $id == null ) {
			if ( $this->_session_var !== null ) {
				// session started, can return id:
				return $this->_session_id;
			} else {
				return '';
			}
		} elseif ( strlen( $id ) == 32 ) {
			$current				=	$this->_session_id;
			if ( $id ) {
				$this->_session_id	=	$id;
			}
			return $current;
		} else {
			return false;
		}
	}
	/**
	 * Generates a random session_id of chars and numbers
	 * (Similar to cbMakeRandomString)
	 * @access private
	 *
	 * @param  int    $stringLength
	 * @return string
	 */
	function generateRandSessionid( $stringLength = 32 ) {
		$chars			=	'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$len			=	strlen( $chars );
		$rndString		=	'';

		$stat			=	@stat( __FILE__ );
		if ( ! is_array( $stat ) ) {
			$stat		=	array();
		}
		$stat[]			=	php_uname();
		$stat[]			=	uniqid( '', true );
		$stat[]			=	microtime();
		//$stat[]		=	$_CB_framework->getCfg( 'secret' );
		$stat[]			=	mt_rand( 0, mt_getrandmax() );
		mt_srand( crc32( implode( ' ', $stat ) ) );

		for ( $i = 0; $i < $stringLength; $i++ ) {
			$rndString	.=	$chars[mt_rand( 0, $len - 1 )];
		}
		return $rndString;
	}
	/**
	 * Validate the session id with internal records of the browser and check values.
	 * @access private
	 *
	 * @return bool
	 */
	function _validateSession( ) {
		// check if browser user agent has changed:
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) )	{
			$browser	=	$this->get( 'cbsession.agent' );
			if ( $browser === null ) {
				$this->set( 'cbsession.agent', $_SERVER['HTTP_USER_AGENT']);
			} elseif ( $_SERVER['HTTP_USER_AGENT'] !== $browser ) {
				return false;
			}
		}
/* PROBLEM: COMMENTED OUT FOR NOW:
		// check if client IP received (could be fake through proxy) matches:
		if ( $this->_getClientIp() != $this->_sessionRecord['client_ip'] ) {
			return false;
		}

		// check if initial session connection had no proxy and now suddenly we have one:
		$incoming_ip			=	$this->_getIcomingIp();
		if ( ( $incoming_ip != $this->_sessionRecord['client_ip'] )
			&& ( $this->_sessionRecord['incoming_ip'] == $this->_sessionRecord['client_ip'] ) ) 
		{
			return false;
		}
*/
		// Things seem to match, check the validation cookie:	//TBD later
		return true;
	}
	function _getIcomingIp() {
		global $_SERVER;
		
		return $_SERVER['REMOTE_ADDR'];
	}
	function _getClientIp() {
		global $_SERVER;
		
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$forwarded_ip_array	=	explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
			$client_ip			=	$forwarded_ip_array[count($forwarded_ip_array) - 1];
		} else {
			$client_ip			=	$_SERVER['REMOTE_ADDR'];
		}
		return $client_ip;
	}
	/**
	 * Reads session record from database
	 *
	 * @param  string  $id
	 * @return string       or NULL if record innexistant or expired
	 */
	function read( $id ) {
		if ( $this->_mode == 'cookie' ) {
			$id					.=	'/';			// 33rd character in case of cookies
		}
		// Fetch session data from the selected database
		$sql					=	'SELECT * FROM `#__comprofiler_sessions`'
								.	' WHERE `session_id` = ' . $this->_db->Quote( $id )
								.	' AND `expiry_time` >= UNIX_TIMESTAMP()'
								;
		$this->_db->setQuery( $sql );
		$this->_sessionRecord	=	$this->_db->loadAssoc();
		if ( $this->_sessionRecord !== null ) {
			return $this->_sessionRecord['session_data'];
		}
		return null;
	}
	/**
	 * Writes session record to database
	 *
	 * @param  string  $id
	 * @param  string  $data
	 * @return bool
	 */
	function write( $id, $data ) {
		global $_CB_framework;

		if ( $this->_mode == 'cookie' ) {
			$id					.=	'/';			// 33rd character in case of cookies
		}
		// Prepare new values:
		$v						=	array();
		$v['session_id']		=	$this->_db->Quote( $id );
		$v['session_data']		=	$this->_db->Quote( $data );
		$v['expiry_time']		=	'UNIX_TIMESTAMP()+' . (int) $this->_life_time;
		if ( $_CB_framework ) {
			$v['ui']			=	(int) $_CB_framework->getUi();
			if ( $_CB_framework->myId() ) {
				$v['username']	=	$this->_db->Quote( $_CB_framework->myUsername() );
				$v['userid']	=	(int) $_CB_framework->myId();
			}
		}

		if ( $this->_sessionRecord !== null ) {
			// UPDATE existing:
			$sets				=	array();
			foreach ( $v as $col => $escapedVal ) {
				$sets[]			=	$this->_db->NameQuote( $col ) . ' = ' . $escapedVal;
			}
			$where				=	array_shift( $sets );
			$sql				=	'UPDATE `#__comprofiler_sessions` SET ' . implode( ', ', $sets )
								.	' WHERE ' . $where;
			$this->_db->setQuery( $sql );
			$okUpdate			=	$this->_db->query();
			if ( $okUpdate ) {
				return $okUpdate;
			}
		}
		// INSERT new: add IP address for first record:
		$v['client_ip']		=	$this->_db->Quote( $this->_getClientIp() );
		$v['incoming_ip']	=	$this->_db->Quote( $this->_getIcomingIp() );

		$columns			=	array();
		$escValues			=	array();
		foreach ( $v as $col => $escapedVal ) {
			$columns[]		=	$this->_db->NameQuote( $col );
			$escValues[]		=	$escapedVal;
		}
		$sql				=	'INSERT INTO `#__comprofiler_sessions`'
							.	' (' . implode( ',', $columns ) . ')'
							.	' VALUES(' . implode( ',', $escValues ) . ')'
							;
		$this->_db->setQuery( $sql );
		return $this->_db->query();
	}
	/**
	 * Removes session $id from database
	 *
	 * @param  string $id
	 * @return bool
	 */
	function destroy( $id ) {
		if ( $this->_mode == 'cookie' ) {
			$id					.=	'/';			// 33rd character in case of cookies
		}
		$sql					=	'DELETE FROM `#__comprofiler_sessions`'
								.	' WHERE `session_id` = ' . $this->_db->Quote( $id )
								;
		$this->_db->setQuery( $sql );
		return $this->_db->query();
	}
	/**
	 * Garbage Collection
	 * Delete all records who have passed the expiration time
	 *
	 * @return bool
	 */
	function gc() {
		$sql					=	'DELETE FROM `#__comprofiler_sessions` WHERE `expiry_time` < UNIX_TIMESTAMP();';
		$this->_db->setQuery( $sql );
		return $this->_db->query();
	}
}

// ----- NO MORE CLASSES OR FUNCTIONS PASSED THIS POINT -----
// Post class declaration initialisations
// some version of PHP don't allow the instantiation of classes
// before they are defined

?>
