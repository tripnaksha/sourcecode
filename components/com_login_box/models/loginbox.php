<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
class LoginBoxModelLoginBox extends JModel {
   function register() {
		global $mainframe;
		//check the token before we do anything else
		$token	= JUtility::getToken();
		if(!JRequest::getInt($token, 0, 'post')) {
         return JError::raiseWarning(403, 'Request Forbidden');
		}

		// Get required system objects
		$user 		= clone(JFactory::getUser());
		$authorize	=& JFactory::getACL();

		// If user registration is not allowed, show 403 not authorized.
		$usersConfig = &JComponentHelper::getParams('com_users');
		if (!$usersConfig->get('allowUserRegistration')) {
         return JError::raiseWarning( 403, JText::_( 'Access Forbidden' ));
		}

		// Initialize new usertype setting
		$newUsertype = $usersConfig->get( 'new_usertype' );
		if (!$newUsertype) {
			$newUsertype = 'Registered';
		}

		// Bind the post array to the user object
		if (!$user->bind( JRequest::get('post'), 'usertype' )) {
			return JError::raiseWarning( 500, $user->getError());
		}

		// Set some initial user values
		$user->set('id', 0);
		$user->set('usertype', '');
		$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));
		//Ajay's hack to set username to email
		$user->set('username', JRequest::getString('email', '', 'post', JREQUEST_ALLOWRAW));

		// TODO: Should this be JDate?
		$user->set('registerDate', date('Y-m-d H:i:s'));

		// If user activation is turned on, we need to set the activation information
		$useractivation = $usersConfig->get( 'useractivation' );
		if ($useractivation == '1') {
			jimport('joomla.user.helper');
			$user->set('activation', md5( JUserHelper::genRandomPassword()) );
			$user->set('block', '1');
		}

		// If there was an error with registration, set the message and display form
		if ( !$user->save() ) {
         return JError::raiseWarning('', JText::_( $user->getError()));
		}
		//Added - Ajay - 05/03/09 - message for wrong answer to challenge question
		#if (!$this->_checkSum()) {
		#	return JError::raiseWarning('', "Wrong sum entered. Please add and enter sum again.");
		#}
		$pwd = JRequest::getString('password', '', 'post');
		if (strlen($pwd) < 6){
			return JError::raiseWarning('', "Password too short. Please enter again.");
		}
		//Added - Ajay - 05/03/09 - added captcha for resetting password request
 		$password = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);
  		$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password); //Disallow control chars in the email
 		$this->sendMail($user, $password);


		$credentials = array();
		$credentials['username'] = JRequest::getString('email', '', 'method', 'username');
		$credentials['password'] = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);
 		
		$error = $mainframe->login($credentials, $options);
		if (JError::isError($error)) {
		   JRequest::setVar('login_only', 1);
		   $view->display();
		}

		return true;
   }
   
	function sendMail(&$user, $password) {
		global $mainframe;

		$db		=& JFactory::getDBO();

		$name 		= $user->get('name');
		$email 		= $user->get('email');
		$username 	= $user->get('username');

		$usersConfig 	= &JComponentHelper::getParams( 'com_users' );
		$sitename 		= $mainframe->getCfg( 'sitename' );
		$useractivation = $usersConfig->get( 'useractivation' );
		$mailfrom 		= $mainframe->getCfg( 'mailfrom' );
		$fromname 		= $mainframe->getCfg( 'fromname' );
		$siteURL		= JURI::base();

		$subject 	= sprintf ( JText::_( 'Account details for' ), $name, $sitename);
		$subject 	= html_entity_decode($subject, ENT_QUOTES);

		if ( $useractivation == 1 ){
			$message = sprintf ( JText::_( 'SEND_MSG_ACTIVATE' ), $name, $sitename, $siteURL."index.php?option=com_user&task=activate&activation=".$user->get('activation'), $siteURL, $username, $password);
		} else {
			$message = sprintf ( JText::_( 'SEND_MSG' ), $name, $sitename, $siteURL);
		}
		
//		$message = str_replace('\n', '<br />', $message);
		$message = nl2br($message);
		$message .= '<br /><br />
		At TripNaksha, you will find <a href="http://www.tripnaksha.com/index.php?option=com_showalltrails&Itemid=44" title="All listed trails">trail routes</a> (for hiking, motorcycling, cycling, running and more) and <a href="http://www.tripnaksha.com/index.php?option=com_eventlist&view=weekend&Itemid=22" title ="Trips listed for this weekend">upcoming trips</a> being added regularly. I hope you will find the information here useful and will contribute trails, article and more too. We are always eager to hear your feedback, do let us know what you think!
		<br />
		<br />
		You can join us on our <a href="http://www.facebook.com/TripNaksha" title="Facebook Page">TripNaksha Facebook Page</a> and chat with us on <a href="http://twitter.com/TripNaksha">twitter</a>. Do join the discussion at our <a href="http://www.tripnaksha.com/blog">blog</a> and leave your comments.
		<br />';

		$message = html_entity_decode($message, ENT_QUOTES);

		//get all super administrator
		$query = 'SELECT name, email, sendEmail' .
				' FROM #__users' .
				' WHERE LOWER( usertype ) = "super administrator"';
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		// Send email to user
		if ( ! $mailfrom  || ! $fromname ) {
			$fromname = $rows[0]->name;
			$mailfrom = $rows[0]->email;
		}
		JUtility::sendMail($mailfrom, $fromname, $email, $subject, $message, $mode=1);

		// Send notification to all administrators
		$subject2 = sprintf ( JText::_('Account details for %s at %s' ), $name, $sitename);
		$subject2 = html_entity_decode($subject2, ENT_QUOTES);

		// get superadministrators id
		foreach ( $rows as $row ) {
			if ($row->sendEmail) {
				$message2 = sprintf ( JText::_( 'SEND_MSG_ADMIN' ), $row->name, $sitename, $name, $email, $username);
				$message2 = html_entity_decode($message2, ENT_QUOTES);
				JUtility::sendMail($mailfrom, $fromname, $row->email, $subject2, $message2);
			}
		}
	}
	//Added - Ajay - 24/03/09 - removed captcha, instead using random arithmetic addition question.
	function _checkSum() {
		global $mainframe;

		// load the contact details
		$return = false;

		$sum = JRequest::getVar('sum', false, '', 'CMD');
		$summing = JRequest::getVar('summing', false, '', 'CMD');
		if ($summing == $sum){
			return true;
		} else return false;
	}
	//Added - Ajay - 24/03/09 - removed captcha, instead using random arithmetic addition question.
}
?>
