<?php
/**
 * http:BL System Plugin allows you to verify IP addresses of clients connecting to your website against the Project Honey Pot database.
 * Thanks to http:BL API you can quickly check whether your visitor is an email harvester, a comment spammer or any other malicious creature.
 * Communication with verification server is done via DNS request mechanism, which makes the query and response even quicker.
 * Now, thanks to http:BL System Plugin any potentially harmful clients are denied from accessing your website and therefore abusing it.
 *
 * @author Michiel Bijland
 * @package plg_httpbl
 * @version $Revision: 191 $
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/* no direct access */
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * Http:BL System Plugin
 *
 * @package	plg_httpbl
 */
class plgSystemHttpBL extends JPlugin {
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object		$subject The object to observe
	 */
	function plgSystemHttpBL(& $subject, $params) {
		parent::__construct($subject, $params);
	}

	function onAfterInitialise(){
		global $mainframe;

		// Get Plugin info
		$pluginparams = $this->params;

		// get API key
		$key 		= $pluginparams->get( 'key' );
		
		// without key this plugins has no use.
		if(empty($key)){
			$this->log(array(0,0,0,0), 'WARNING: No key so we cannot check http:bl.');
			return;
		}
		
		$log 		= $pluginparams->get( 'log', 1 );
		$checkgroups= $pluginparams->get( 'usergroups', array());
		// Make sure we have a array
		if (!is_array($checkgroups)){
			$checkgroups = array($checkgroups);
		}
		
		// get User object
		$user		= & JFactory::getUser();
		
		// get correct gid as Joomla! guest user has 0
		if($user->guest){
			$gid = 29;
		} else {
		    $gid = $user->gid;
		}
		
		// check if user in usergroups
		if(!in_array($gid, $checkgroups)){
			if($log > 1){
				$this->log(array(0,0,0,0), 'User was not checked because of his group id.');
			}
			return;
		}

		// set adrs
		$adrs = $_SERVER['REMOTE_ADDR'];

		// get the cache
		$cache  =& JFactory::getCache('httpBL', 'output');
		// We only cache responce not the outcome. So settings can be changed without problems.
		$responce = $cache->get($adrs);
		if(!$responce){
			// Query
			$ip = implode ( '.', array_reverse( explode( '.', $adrs ) ) );
			$query = $key . '.' . $ip .'.dnsbl.httpbl.org';
			$responce = gethostbyname( $query );

			// Did the lookup fail, if so either not listed or error
			if($query == $responce){
				// rewrite responce so key isn't written to cache file and save precious space.
				$responce = '0.0.0.0';
			}

			// store data
			$cache->store($responce, $adrs);
		}

		// explode responce
		$responce = explode( '.', $responce);

		// If the response is positive,
		if ( $responce[0] == 127 ) {

			// Get thresholds
			$age 		= $pluginparams->get( 'age'		, 30 );
			$threat 	= $pluginparams->get( 'threat'	, 25 );

			// Who to block
			$block_s 	= $pluginparams->get( 'block_s'	, 1 ) ? 1 : 0;
			$block_h	= $pluginparams->get( 'block_h'	, 1 ) ? 2 : 0;
			$block_c	= $pluginparams->get( 'block_c'	, 1 ) ? 4 : 0;
			$block		= $block_s | $block_h | $block_c;

			// Redirect
			$redirect 	= $pluginparams->get( 'redirect', False );

			// lazy if so here we go
			if ( $responce[1] < $age && $responce[2] > $threat && ($responce[3] & $block > 0)){
				if($redirect){
					header( "HTTP/1.1 301 Moved Permanently ");
					header( "Location: $redirect" );
				}
				if($log){
					$this->log($responce, 'This request was blocked');
				}
				JError::raiseError( 301, JText::_("Moved"));
			} elseif ($log > 1){
				// listed but not a threat
				$this->log($responce, 'Ip listed but not a threat.');
			}
		} elseif ($log > 1) {
			// not listed or failed lookup
			$this->log($responce, 'Ip not listed');
		}
	}
	
	function log($responce, $msg){
		jimport('joomla.error.log');

		$options['format'] = "{DATE}\t{TIME}\t{C-IP}\t{AGE}\t{THREAT}\t{TYPE}\t{MSG}";
		$log = & JLog::getInstance('httpbl.php', $options);

		$entry = array();
		$entry['age'] = $responce[1];
		$entry['threat'] = $responce[2];
		$entry['type'] = $responce[3];
		$entry['msg'] = $msg;
		$log->addEntry($entry);
	}
}
?>