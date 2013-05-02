<?php
/**
 * Element: Author
 * Displays a selectbox of authors
 *
 * @package    NoNumber! Elements
 * @version    1.0.0
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Author Element
 */
class JElementAuthor extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Author';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		return JHTML::_( 'list.users', $control_name.'['.$name.']', $value, 1 );
	}
}