<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 2, or later
 * @version $Id: default_raw.php 166 2010-06-22 17:35:59Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');
$task = JRequest::getCmd('task','');

echo $this->json;