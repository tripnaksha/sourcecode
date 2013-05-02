<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id: installer.php 194 2010-07-23 09:24:00Z nikosdion $
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Installer deployment
 */
class AECoreDomainInstaller extends AEAbstractPart
{

	/** @var int Installer image file offset last read */
	private $offset;

	/**
	 * Public constructor
	 * @return AECoreDomainInstaller
	 */
	public function __construct()
	{
		parent::__construct();
		AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: New instance");
	}

	/**
	 * Implements the _prepare abstract method
	 *
	 */
	function _prepare()
	{
		// Add the backup description and comment in a README.html file in the
		// installation directory. This makes it the first file in the archive.
		$data = $this->createReadme();
		$archive =& AEFactory::getArchiverEngine();
		$archive->addVirtualFile('README.html','installation', $data);

		// Set our state to prepared
		$this->setState('prepared');
	}

	/**
	 * Implements the _run() abstract method
	 */
	function _run()
	{
		if( $this->getState() == 'postrun' )
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__." :: Already finished");
			$this->setStep('');
			$this->setSubstep('');
		} else {
			$this->setState('running');
		}

		// Try to step the archiver
		$archive =& AEFactory::getArchiverEngine();
		$ret = $archive->transformJPA($this->offset);
		// Error propagation
		$this->propagateFromObject($archive);

		if( ($ret !== false) && ($archive->getError() == '') )
		{
			$this->offset = $ret['offset'];
			$this->setStep($ret['filename']);
		}

		// Check for completion
		if($ret['done'])
		{
			AEUtilLogger::WriteLog(_AE_LOG_DEBUG, __CLASS__.":: archive is initialized");
			$this->setState('finished');
		}
	}

	/**
	 * Implements the _finalize() abstract method
	 *
	 */
	function _finalize()
	{
		$this->setState('finished');
	}

	/**
	 * Creates the contents of an HTML file with the description and comment of
	 * the backup. This file will be saved as README.html in the installation
	 * directory.
	 * @return string The contents of the HTML file.
	 */
	private function createReadme()
	{
		$config = AEFactory::getConfiguration();

		$lbl_description = JText::_('BACKUP_LABEL_DESCRIPTION');
		$lbl_comment = JText::_('BACKUP_LABEL_COMMENT');
		$lbl_version = AKEEBA_VERSION.' ('.AKEEBA_DATE.')';

		$lbl_coreorpro = (AKEEBA_PRO == 1) ? 'Professional' : 'Core';
		
		$description = $config->get('volatile.core.description','');
		$comment = $config->get('volatile.core.comment','');

		$config->set('volatile.core.description',null);
		$config->set('volatile.core.comment',null);

		return <<<ENDHTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Akeeba Backup Archive Identity</title>
</head>
<body>
	<h1>$lbl_description</h1>
	<p id="description"><![CDATA[$description]]></p>
	<h1>$lbl_comment</h1>
	<div id="comment">
	$comment
	</div>
	<hr/>
	<p>
		Akeeba Backup $lbl_coreorpro $lbl_version
	</p>
</body>
</html>
ENDHTML;
	}

}