<?php
/**
 * Element Include: VersionCheck
 * Methods to check if current version is the latest
 *
 * @package    NoNumber! Elements
 * @version    1.1.0
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Version Check Class (Include file)
 */
class NoNumberVersionCheck
{
	function setMessage( $current_version = '0', $version_file = '', $version_url = '', $download_url = '' )
	{
		$mainframe =& JFactory::getApplication();

		$messageQueue = $mainframe->getMessageQueue();

		if ( $version_file ) {
			$new_version = NoNumberVersionCheck::getVersion( $version_file, $version_url );
			$has_newer = NoNumberVersionCheck::checkVersion( $current_version, $new_version );
			if ( $has_newer ) {
				// set message
				$msg = JText::sprintf( '-A newer version is available', $download_url, $new_version, $current_version );
				$message_set = 0;
				foreach ( $messageQueue as $queue_message ) {
					if ( $queue_message['type'] == 'notice' && $queue_message['message'] == $msg ) {
						$message_set = 1;
						break;
					}
				}
				if ( !$message_set ) {
					$mainframe->enqueueMessage( $msg, 'notice' );
				}
			}
		}
	}

	function getVersion( $version_file = '', $version_url = '' )
	{
		$version = '0';

		if ( !$version_file ) {
			return $version;
		}

		$cookieName = JUtility::getHash( $version_file.'_version' );

		$cookie = JRequest::getString( $cookieName, '', 'COOKIE' );

		if ( $cookie ) {
			$version = NoNumberVersionCheck::cleanString( $cookie );
			return $version;
		}

		// the url of the new version file
		$url =	$version_url.'/'.$version_file;

		$timeout = 1;

		//Version Checker
		if( function_exists( 'curl_init' ) ){
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, $timeout );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 1 );
			$version = curl_exec( $ch );
			curl_close( $ch );
		} else {
			// Set timeout
			// Doesn't work in SAFE_MODE ON
			$old_timeout = ini_set( 'default_socket_timeout', $timeout );
			$file = @fopen( $url, 'r' );
			if ( $file ) {
				ini_set( 'default_socket_timeout', $old_timeout );
				stream_set_timeout( $file, $timeout );
				stream_set_blocking( $file, 0 );
				$version = fread( $file, 10 );
			}
		}

		if ( $version ) {
			$version = NoNumberVersionCheck::cleanString( $version );
		}

		if ( $version ) {
			$lifetime = time() + 60*60; // 1 hour
			setcookie( $cookieName, $version, $lifetime );
		}

		return $version;
	}

	function checkVersion( $current_version = 0, $new_version = 0 )
	{
		$has_newer = 0;

		$v_cur = NoNumberVersionCheck::convertToNumberArray( $current_version );
		$v_new = NoNumberVersionCheck::convertToNumberArray( $new_version );

		$count = count( $v_cur );
		for ( $i = 0; $i < $count; $i++ ) {
			 if ( $v_cur[$i] != $v_new[$i] ) {
			 	if ( $v_cur[$i] < $v_new[$i] ) {
					$has_newer = 1;
				}
				break;
			}
		}

		return $has_newer;
	}

	function convertToNumberArray( $nr )
	{
		/*
		 * v1.2.1 is newer that v1.2.1a
		 * because v1.2.1a is the first development version of v1.2.1
		 */
		$nr_array = array( 0, 0, 0, 0, 0 );
		$nr = explode( '.', $nr );
		$count = count( $nr_array );
		for( $i = 0; $i < $count; $i++ ) {
			if ( !isset( $nr[$i] ) || $nr[$i] == 0 ) {
				$nr_part = 0.1;
			} else {
				$nr_part = $nr[$i];
				if ( is_numeric( $nr[$i] ) ) {
						$nr_part += 0.1;
				} else {
					$nr_part = preg_replace( '#^([0-9]*)#', '\1.', $nr_part );
					$nr_part_array = explode( '.', $nr_part );
					$nr_part = intval( $nr_part_array['0'] );
					if ( isset( $nr_part_array['1'] ) && $nr_part_array['1'] ) {
						// if letter is set, convert it to a number and ad it as a tenthousandth
						$nr_part += ( ord( $nr_part_array['1'] ) ) / 100000;
					} else {
						// if no letter is set, ad a tenth
						$nr_part += 0.1;
					}
				}
			}
			$nr_array[$i] = $nr_part;
		}
		return $nr_array;
	}

	function cleanString( $str = '' )
	{
		$str = preg_replace( '#[^0-9a-z\.]#', '', strtolower( $str ) );
		return $str;
	}
}