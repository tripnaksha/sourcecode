<?php
defined('_JEXEC') or die();
jimport('joomla.application.component.controller');
JHTML::_('behavior.mootools');
class LoginBoxController extends JController {
   function display() {
      $view = $this->getView();
      
      global $mainframe;
      $user = JFactory::getUser();
      if (JRequest::getVar('clickbtn'))
         $link = "fup";
      else
	 $link = "nop";
      if (($user->get('id') > 0 || $mainframe->getUserState($this->_namespace.'state_check') == 'logged') && $link == 'fup')
         $mainframe->redirect('index.php?option=com_content&view=article&id=207&tmpl=component');
      else if (($user->get('id') > 0 || $mainframe->getUserState($this->_namespace.'state_check') == 'logged') && $link == 'nop')
         $mainframe->redirect('index.php?option=com_login_box&task=loggedin&clickbtn='.JRequest::getVar('clickbtn'));
      else
        $view->display();

//      $view->display();
   }
   
	function login() {
		global $mainframe;
		$view = $this->getView();
      		$user = JFactory::getUser();
      		if ($user->get('id') > 0) {
      		   $view->display('logged');
      		   return;
      		}

		if ($return = JRequest::getVar('return', '', 'method', 'base64')) {
			$return = base64_decode($return);
		}

		$options = array();
		$options['remember'] = JRequest::getBool('remember', false);

		//Added - Ajay - 23/09/09 - added nickname in the credentials, pass to gmail auth plugin and enter as real name there
		$username = JRequest::getString('username', '', 'method', 'username');
		$nickname = JRequest::getString('nickname', '', 'method', 'nickname');
		$credentials = array();
		$credentials['username'] = JRequest::getVar('username', '', 'method', 'username');
		$credentials['password'] = JRequest::getString('passwd', '', 'post', JREQUEST_ALLOWRAW);
		$credentials['nickname'] = $nickname ? $nickname: substr($username, 0, strpos($username,"@") - 1);

		//Added - Ajay - 05/03/09 - added captcha for login request
		//Modified - Ajay - 24/03/09 - removed captcha, instead using random arithmetic addition question.
		   $error = $mainframe->login($credentials, $options);
		   if (JError::isError($error)) {
		      JRequest::setVar('login_only', 1);
		      $view->display();
		   }
/*		   else if (!$this->_checkSum()) {
		      JError::raiseWarning('', "Wrong sum entered. Please add and enter sum again.");
		      JRequest::setVar('login_only', 1);
		      $view->display();
		   }
*/		   else {
		      $mainframe->setUserState( $this->_namespace.'state_check', "logged" );
		      $mainframe->redirect('index.php?option=com_login_box&task=loggedin&clickbtn='.JRequest::getVar('clickbtn'));
		   }
		//Added - Ajay - 05/03/09 - added captcha for login request
	}
	
	function register() {
	   global $mainframe;
	   $model = $this->getModel();
      $view = $this->getView();
	   if (JError::isError($model->register())) {
		   JRequest::setVar('register_only', 1);
		   $view->display();
	   } else {
		   $mainframe->redirect('index.php?option=com_login_box&task=registered');
	   }
	}
	
	function registered() {
      $view = $this->getView();
	   $view->display('registered');
	}
	
	function loggedin() {
           global $mainframe;
           $user = JFactory::getUser();
           $view = $this->getView();
           if (JRequest::getVar('clickbtn'))
              $link = "fup";
           else
	      $link = "nop";
           if (($user->get('id') > 0 || $mainframe->getUserState($this->_namespace.'state_check') == 'logged') && $link == 'fup')
              $mainframe->redirect('index.php?option=com_content&view=article&id=207&tmpl=component');
           else
           {
               $view->assign("clickbtn",JRequest::getVar('clickbtn'));
	       $view->display('success');
           }
	}
	//Added - Ajay - 05/03/09 - added captcha for resetting password request
	//Modified - Ajay - 24/03/09 - removed captcha, instead using random arithmetic addition question.
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
	//Added - Ajay - 05/03/09 - added captcha for resetting password request -- End
	/**
	 * Password Reset Request Method
	 *
	 * @access	public
	 */
	function requestreset()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		// Get the input
		$email		= JRequest::getVar('email', null, 'post', 'string');

		// Get the model
		$model = &$this->getModel('Reset');

		// Request a reset
		if ($model->requestReset($email) === false)
		{
			$message = JText::sprintf('PASSWORD_RESET_REQUEST_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_login_box&reset_only=1', $message);
			return false;
		}
		$message = JText::sprintf('PASSWORD_RESET_SUCCESS', $email);
		$this->setRedirect('index.php?option=com_login_box&login_only=1', $message);
	}
	/**
	 * Username Reminder Method
	 *
	 * @access	public
	 */
	function remindusername()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		// Get the input
		$email = JRequest::getVar('email', null, 'post', 'string');

		// Get the model
		$model = $this->getModel('remind');

		// Send the reminder
		if ($model->remindUsername($email) === false)
		{
			$message = JText::sprintf('USERNAME_REMINDER_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_login_box&remind_only=1', $message);
			return false;
		}

		$message = JText::sprintf('USERNAME_REMINDER_SUCCESS', $email);
		$this->setRedirect('index.php?option=com_login_box&login_only=1', $message);
	}


}
?>