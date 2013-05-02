<?php
/**
 * @version		$Id: gmail.php 10709 2008-08-21 09:58:52Z eddieajau $
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * GMail Authentication Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 1.5
 */
class plgAuthenticationStudioAMKGMail extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgAuthenticationStudioAMKGMail(& $subject, $config) {
		parent::__construct($subject, $config);
	}

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param   array 	$credentials Array holding the user credentials
	 * @param 	array   $options     Array of extra options
	 * @param	object	$response	Authentication response object
	 * @return	boolean
	 * @since 1.5
	 */
	function onAuthenticate( $credentials, $options, &$response )
	{
		$message = '';
		$success = 0;
		if(function_exists('curl_init'))
		{
			if(strlen($credentials['username']) && strlen($credentials['password']))
			{
				// Bug Fixed by StudioAMK. Username used for gmail authentication must contain @gmail.com or @googlemail.com.
				if(substr_count($credentials['username'], '@gmail.com') || substr_count($credentials['username'], '@googlemail.com'))
				{
					// Get instance of database object
					$db =& JFactory::getDBO();
					$query = "select id from jos_users where email = '" . $credentials['username']."'";
					$result = mysql_query($query);
					if(!$result)
						die(mysql_error());
					else
						$row = mysql_fetch_row($result);

					//If email address already exists in db, old user. Welcome email should be sent only to new users.
					if ($row)
					   $isnew = 0;
					else
					   $isnew = 1;

					$mailfrom = 'ajay@TripNaksha.com';
					$fromname = 'ajay@TripNaksha.com';
//					$email = 'ajay@TripNaksha.com';
					$email = $credentials['username'];
					$subject = 'Welcome to TripNaksha!';
					$mailmessage = 'Hi,
						<br />
						<br />
						Thanks for checking out TripNaksha, an enabler for adventure travel in India.
						<br />
						<br />
						Here, you will find <a href="http://www.tripnaksha.com/index.php?option=com_showalltrails&Itemid=44" title="All listed trails">trail routes</a> (for hiking, motorcycling, cycling, running and more) and <a href="http://www.tripnaksha.com/index.php?option=com_eventlist&view=weekend&Itemid=22" title ="Trips listed for this weekend">upcoming trips</a> being added regularly. I hope you will find the information here useful and will contribute trails, article and more too. We are always eager to hear your feedback, do let us know what you think!
						<br />
						<br />
						You can join us on our <a href="http://www.facebook.com/TripNaksha" title="TripNaksha Facebook Page">Facebook Page</a> and chat with us on <a href="http://twitter.com/TripNaksha">twitter</a>. Do join the discussion at our <a href="http://www.tripnaksha.com/blog">blog</a> and leave your comments.
						<br />
						<br />
						Regards,
						<br />
						Ajay R';

					$curl = curl_init('https://mail.google.com/mail/feed/atom');
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					//curl_setopt($curl, CURLOPT_HEADER, 1);
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt($curl, CURLOPT_USERPWD, $credentials['username'].':'.$credentials['password']);
					$result = curl_exec($curl);
					$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);

					switch($code)
					{
						case 200:
					 		$message = 'Access Granted';
					 		$success = 1;
					 		if ($isnew == 1)
					 		  JUtility::sendMail($mailfrom, $fromname, $email, $subject, $mailmessage, $mode=1);
						break;
						case 401:
							$message = 'Access Denied';
						break;
						default:
							$message = 'Result unknown, access denied.';
							break;
					}
				}
				else {
					$message = 'Username must contain @gmail.com or @googlemail.com';
				}
			}
			else  {
				$message = 'Username or password blank';
			}
		}
		else {
			$message = 'curl isn\'t installed';
		}

		if ($success)
		{
			$response->status 	     = JAUTHENTICATE_STATUS_SUCCESS;
			$response->error_message = '';
			$response->email 	= $credentials['username'];
			$response->fullname = $credentials['nickname'];
			$response->username = $credentials['nickname'];
		}
		else
		{
			$response->status 		= JAUTHENTICATE_STATUS_FAILURE;
			$response->error_message	= 'Failed to authenticate: ' . $message;
		}
	}
}
