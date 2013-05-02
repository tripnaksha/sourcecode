<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 2, or later
 * @version $Id: view.raw.php 92 2010-03-18 10:33:11Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

class AkeebaViewBackup extends JView
{
	public function display($tpl = null)
	{
		parent::display($tpl);
	}
}
?>