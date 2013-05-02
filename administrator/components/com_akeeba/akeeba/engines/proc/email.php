<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: email.php 170 2010-06-28 18:33:42Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

class AEPostprocEmail extends AEAbstractPostproc
{
	public function processPart($absolute_filename)
	{
		// Retrieve engine configuration data
		$config =& AEFactory::getConfiguration();

		$address	= trim( $config->get('engine.postproc.email.address', '') );
		$subject	= $config->get('engine.postproc.email.subject', '0');

		// Sanity checks
		if(empty($address))
		{
			$this->setError('You have not set up a recipient\'s email address for the backup files');
			return false;
		}

		// Send the file
		$basename = basename($absolute_filename);
		AEUtilLogger::WriteLog(_AE_LOG_INFO, "Preparing to email $basename to $address");
		if(empty($subject)) $subject = JText::_('AKEEBA_DEFAULT_EMAIL_SUBJECT');
		$body = "Emailing $basename";

		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Subject: $subject");
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, "Body: $body");

		$result = AEPlatform::send_email($address, $subject, $body, $absolute_filename);

		// Return the result
		if($result !== true)
		{
			// An error occured
			$this->setError( $result );
			// Notify that we failed
			return false;
		}
		else
		{
			// Return success
			AEUtilLogger::WriteLog(_AE_LOG_INFO, "Email sent successfully");
			return true;
		}
	}
}