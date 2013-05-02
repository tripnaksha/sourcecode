<?php
/**
 * Module default template
 *
 * @package    Cache Cleaner
 * @version    1.1.1
 * @since      File available since Release v1.0.0
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl/cachecleaner
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

echo modCacheCleaner::render( $params );