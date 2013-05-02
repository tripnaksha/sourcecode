<?php
/**
 * Element: Templates
 * Displays a select box of templates
 *
 * @package    NoNumber! Elements
 * @version    1.0.1
 *
 * @author     Peter van Westen <peter@nonumber.nl>
 * @link       http://www.nonumber.nl
 * @copyright  Copyright (C) 2010 NoNumber! All Rights Reserved
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Templates Element
 */
class JElementTemplates extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Templates';

	function fetchElement( $name, $value, &$node, $control_name)
	{

		$multiple =		$node->attributes( 'multiple' );

		$control = $control_name.'['.$name.']';
		$attribs = 'class="inputbox"';
		if ( $multiple ) {
			if( !is_array( $value ) ) { $value = explode( ',', $value ); }
			$attribs .= ' multiple="multiple"';
			$control .= '[]';
		}

		require_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_templates'.DS.'helpers'.DS.'template.php';
		$rows = array();
		$rows = TemplatesHelper::parseXMLTemplateFiles( JPATH_ROOT.DS.'templates' );
		$options = $this->createList( $rows, JPATH_ROOT.DS.'templates' );

		$attribs .= ' size="'.count( $options).'"';

		$list =	JHTML::_( 'select.genericlist', $options, $control, $attribs, 'value', 'text', $value, $control_name.$name );

		return $list;
	}
	function createList( $rows, $templateBaseDir )
	{
		$options = array();

		$option = '';
		$option->value =	'system:component';
		$option->text =		JText::_( 'None' ).' (System - component)';
		$option->disable =	null;
		$options[] = $option;

		$option = '';
		$option->value =	'0';
		$option->text =		'&nbsp;';
		$option->disable =	1;
		$options[] = $option;

		foreach ( $rows as $row ) {

			$option = '';
			$option->value =	$row->directory;
			$option->text =		$row->name;
			$option->disable =	null;
			$options[] = $option;

			$options_sub = $this->getSubTemplates( $row, $templateBaseDir );
			$options = array_merge( $options, $options_sub );
		}
		return $options;
	}

	function getSubTemplates( $row, $templateBaseDir )
	{
		$options = array();
		$templateDir = dir( $templateBaseDir.DS.$row->directory );
		while ( false !== ( $file = $templateDir->read() ) ) {
		  	if ( is_file( $templateDir->path.DS.$file ) ) {
				if ( !( strpos( $file, '.php' ) === false ) && $file != 'index.php' ) {
					$file_name = str_replace( '.php', '', $file );
					if ( $file_name != 'index' && $file_name != 'editor' && $file_name != 'error' ) {
						$option = '';
						$option->value =	$row->directory.':'.$file_name;
						$option->text =		$row->name.' - '.$file_name;
						$option->disable =	null;
						$options[] = $option;
					}
				}
			}
		}
		$templateDir->close();

		return $options;
	}

	function def( $val, $default )
	{
		return ( $val == '' ) ? $default : $val;
	}
}