<?php
/**
 * Element: Title
 * Displays a title with a bunch of extras, like: description, image, versioncheck
 *
 * @package    NoNumber! Elements
 * @version    1.2.0
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Title Element
 *
 * Available extra parameters:
 * title			The title
 * description		The description
 * message_type		none, message, notice, error?
 * image			Image (and path) to show on the right
 * show_apply		Show an apply tick image on the right (only if the image is not set)
 * url				The main url
 * download_url		The url of the download location
 * help_url			The url of the help page
 * version_url		The url to the new version folder (default = [url]/versions/)
 * version_path		The path to version folder
 * version_file		The filename of the current version file
 */
class JElementTitle extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Title';

	function fetchTooltip( $label, $description, &$node, $control_name, $name )
	{
		$nostyle =		$node->attributes( 'nostyle' );
		if ( $nostyle ) {
			return JElement::fetchTooltip( $label, '', $node, $control_name, $name );
		}
		return;
	}

	function fetchElement( $name, $value, &$node, $control_name )
	{
		$start =		$node->attributes( 'start' );
		$end =			$node->attributes( 'end' );

		if ( $end ) {
			return '</td></tr></table></div></div>';
		}

		$title =		$node->attributes( 'label' );
		$description =	$node->attributes( 'description' );
		$lang_folder =	$node->attributes( 'language_folder' );
		$message_type =	$node->attributes( 'message_type' );
		$image =		$node->attributes( 'image' );
		$image_w =		$node->attributes( 'image_w' );
		$image_h =		$node->attributes( 'image_h' );
		$show_apply =	$node->attributes( 'show_apply' );
		$nostyle =		$node->attributes( 'nostyle' );
		$toggle =		$node->attributes( 'toggle' );

		$file_root = str_replace( '\\', '/', str_replace( JPATH_SITE, '', dirname( __FILE__ ) ) );
		$document =& JFactory::getDocument();
		$document->addScript( JURI::root(true).$file_root.'/title.js' );

		if ( $nostyle ) {
			return JText::_( $description );
		}

		// The main url
		$url =				$node->attributes( 'url' );
		$download =			$node->attributes( 'download_url' );
		$help =				$node->attributes( 'help_url' );
		$version =			$node->attributes( 'version' );
		$version_url =		$this->def( $node->attributes( 'version_url' ), $url.'/versions/' );
		$version_file =		$node->attributes( 'version_file' );

		$msg = '';

		if ( $lang_folder ) {
			// Include extra language file
			$lang = JFactory::getLanguage();
			$lang = str_replace( '_', '-', $lang->_lang );

			if ( strpos( $lang_folder, '/administrator' ) === 0 ) {
				$lang_folder = str_replace( '/', DS, str_replace( '/administrator', JPATH_ADMINISTRATOR, $lang_folder ) );
			} else {
				$lang_folder = JPATH_SITE.str_replace( '/', DS, $lang_folder );
			}
			$lang_file = 'en-GB.inc.php';
			if ( file_exists( $lang_folder.DS.$lang_file ) ) {
				include $lang_folder.DS.$lang_file;
			}
			if ( $lang != 'en-GB' ) {
				$lang_file = $lang.'.inc.php';
				if ( !file_exists( $lang_folder.DS.$lang_file ) ) {
					$include_file = 'en-GB.inc.php';
				}
				if ( file_exists( $lang_folder.DS.$lang_file ) ) {
					include $lang_folder.DS.$lang_file;
				}
			}
		}

		if ( $title ) {
			$title = JText::_( $title );
		}

		$user = JFactory::getUser();
		if( strlen( $version ) && strlen( $version_file ) && ( $user->usertype == 'Super Administrator' || $user->usertype == 'Administrator' ) ) {
			// Import library dependencies
			require_once dirname( __FILE__ ).DS.'version_check.php';

			$msg = NoNumberVersionCheck::setMessage( $version, $version_file, $version_url, $download );
			if ( $version ) {
				if ( $title ) {
					$title .= ' v'.$version;
				} else {
					$title = JText::_( 'Version' ).' '.$version;
				}
			}
		}

		if ( $url ) {
			$url = '<a href="'.$url.'" target="_blank" title="'.$title.'">';
		}

		if ( $image ) {
			$image = str_replace( '/', "\n", str_replace( '\\', '/', $image ) );
			$image = explode( "\n", trim( $image ) );
			if ( $image['0'] == 'administrator' ) {
				$image['0'] = JURI::base(true);
			} else {
				$image['0'] = JURI::root(true).'/'.$image['0'];
			}
			$image = $url.'<img src="'.implode( '/', $image ).'" border="0" style="float:right;margin-left:10px" alt=""';
			if ( $image_w ) {
				$image .= ' width="'.$image_w.'"';
			}
			if ( $image_h ) {
				$image .= ' height="'.$image_h.'"';
			}
			$image .= ' />';
			if ( $url ) { $image .= '</a>'; }
		}

		if ( $url ) { $title = $url.$title.'</a>'; }
		if ( $description ) { $description = JText::_( $description ); }
		if ( $help ) { $help = '<a href="'.$help.'" target="_blank" title="'.JText::_( '-More info' ).'">'.JText::_( 'More info' ).'...</a>'; }

		if ( $title ) { $title = html_entity_decoder( $title ); }
		if ( $description ) { $description = html_entity_decoder( $description ); }

		$html = '';
		if ( $image ) { $html .= $image; }
		if ( $show_apply ) {
			$apply_button = '<a href="#" onclick="submitbutton( \'apply\' );" title="'.JText::_( 'Apply' ).'"><img align="right" border="0" alt="'.JText::_( 'Apply' ).'" src="images/tick.png"/></a>';
			$html .= $apply_button;
		}

		if ( $toggle && $description ) {
			$el = 'document.getElementById( \''.$control_name.$name.'description\' )';
			$onclick =
				'if( this.innerHTML == \''.JText::_( JText::_( 'Show' ).' '.$title ).'\' ){'
					.$el.'.style.display = \'block\';'
					.'this.innerHTML = \''.JText::_( JText::_( 'Hide' ).' '.$title ).'\';'
				.'}else{'
					.$el.'.style.display = \'none\';'
					.'this.innerHTML = \''.JText::_( JText::_( 'Show' ).' '.$title ).'\';'
				.'}'
				.'this.blur();return false;'
				;
			$html .= '<div class="button2-left" style="margin:0px 0px 5px 0px;"><div class="blank"><a href="javascript://;" onclick="'.$onclick.'">'.JText::_( JText::_( 'Show' ).' '.$title ).'</a></div></div>'."\n";
			$html .= '<br clear="all" />';
			$html .= '<div id="'.$control_name.$name.'description" style="display:none;">';
		} else if ( $title ) {
			$html .= '<h4 style="margin: 0px;">'.$title.'</h4>';
		}
		if ( $description ) { $html .= $description; }
		if ( $help ) { $html .= '<p>'.$help.'</p>'; }
		if ( $toggle && $description ) {
			$html .= '</div>';
		}
		if ( $message_type ) {
			$html = '<dl id="system-message"><dd class="'.$message_type.'"><ul><li>'.html_entity_decoder( $html ).'</li></ul></dd></dl>';
		} else {
			$html = '<div class="panel"><div style="padding: 2px 5px;">'.$html.'<div style="clear: both;"></div>';
			if ( $start ) {
				$html .= '<table width="100%" class="paramlist admintable" cellspacing="1">';
				$html .= '<tr><td style="padding: 0px;" colspan="2">';
			} else {
				$html .= '</div></div>';
			}
		}

		if ( $msg ) { $html = $msg.$html; }

		return $html;
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}

if( !function_exists( 'html_entity_decoder' ) ) {
	function html_entity_decoder( $given_html, $quote_style = ENT_QUOTES, $charset = 'UTF-8' )
	{
		if( phpversion() < '5.0.0' ) {
			$trans_table = array_flip( get_html_translation_table( HTML_SPECIALCHARS, $quote_style ) );
			$trans_table['&#39;'] = "'";
			return ( strtr( $given_html, $trans_table ) );
		}else {
			return html_entity_decode( $given_html, $quote_style, $charset );
		}
	}
}