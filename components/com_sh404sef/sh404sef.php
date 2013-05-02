<?php
/**
 * SEF module for Joomla!
 *
 * @author      $Author: shumisha $
 * @copyright   Yannick Gaultier - 2007
 * @package     sh404SEF-15
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     $Id: sh404sef.php 939 2009-06-07 06:52:23Z silianacom-svn $
 *
 */

// Security check to ensure this file is being included by a parent file.
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

// no direct acccess
shRedirect($GLOBALS['shConfigLiveSite']);