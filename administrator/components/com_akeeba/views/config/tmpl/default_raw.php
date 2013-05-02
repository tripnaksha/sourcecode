<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: default_raw.php 51 2010-01-30 10:49:58Z nikosdion $
 * @since 1.3
 *
 * The main page of the Akeeba Backup component is where all the fun takes place :)
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

echo '###'.json_encode($this->result).'###';