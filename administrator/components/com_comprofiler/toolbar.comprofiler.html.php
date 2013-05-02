<?php
/**
* User Menu Button Bar HTML
* @version $Id: toolbar.comprofiler.html.php 344 2006-08-05 10:25:39Z beat $
* @package Community Builder
* @subpackage toolbar.comprofiler.html.php
* @author JoomlaJoe and Beat
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
* Utility class for the button bar
* @author Mambo Foundation Inc http://www.mambo-foundation.org
* @copyright 2005-2007 Mambo Foundation Inc.
* @license GNU/GPL Version 2, see LICENSE.php
* Mambo is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; version 2 of the License.
*/
class cbMenuBarBase {

    /**
	* Writes the start of the button bar table
	*/
    function startTable() {
		echo '<div class="cbtoolbarbar">';
    }
    function _output( $onClick, $icon, $alt, $link = '#' ) {
    	$translated	=	CBTxt::T( $alt );
		$html	=	'<a href="' . $link . '"'
				.		( $onClick ? ' onclick="' . $onClick . '" ' : '' )
				.		' class="cbtoolbar">'
				.	'<span class="cbicon-32-' . $icon . '" title="' . htmlspecialchars( $translated ) . '"></span>'
				.	htmlspecialchars( $translated )
				.	"</a>\n";
		return $html;
    }
    /**
	* Writes a custom option and task button for the button bar
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	*/
    function custom( $task='', $icon='', $iconOver='', $alt='', $listSelect=true, $prefix='' ) {
        if ($listSelect) {
            $onClick = "if (document.adminForm.boxchecked.value == 0){
				alert('".sprintf(CBTxt::T('Please make a selection from the list to %s'),$alt)."');
			}else {
				".$prefix."submitbutton('$task');
			}";
        } else {
            $onClick	=	$prefix . "submitbutton('$task')";
        }
     //   if ( $icon ) {
		$icon			=	preg_replace( '/\.[^.]*$/', '', $icon );
        echo cbMenuBarBase::_output( $onClick, $icon, $alt );
     //   }
    }

    /**
	* Writes a custom option and task button for the button bar.
	* Extended version of custom() calling hideMainMenu() before submitbutton().
	* @param string The task to perform (picked up by the switch($task) blocks
	* @param string The image to display
	* @param string The image to display when moused over
	* @param string The alt text for the icon image
	* @param boolean True if required to check that a standard list item is checked
	*/
    function customX( $task='', $icon='', $iconOver='', $alt='', $listSelect=true ) {
        CBtoolmenuBar::custom ($task, $icon, $iconOver, $alt, $listSelect, 'hideMainMenu();');
    }

    /**
	* Standard routine for displaying toolbar icon
	* @param string An override for the task
	* @param string An override for the alt text
	* @param string The name to be used as a legend and as the image name
	* @param
	*/
    function addToToolBar( $task, $alt, $name, $imagename, $extended = false, $listprompt = '', $confirmMsg = '', $inlineJs = true ) {
        if ( is_null( $alt ) ) {
        	$alt	=	CBTxt::T($name);
        }
        echo CBtoolmenuBar::_output( $inlineJs ? CBtoolmenuBar::makeJavaScript( $task, $extended, $listprompt, $confirmMsg ) : null, $imagename, $alt, '#' . $task );
    }

    function makeJavaScript ($task, $extended, $listprompt='', $confirmMsg = '' ) {
        $script = '';
        if ( $listprompt ) {
        	$script .= "if (document.adminForm.boxchecked.value == 0){ alert('$listprompt'); } else {";
        }
        if ( $confirmMsg ) {
        	$script	.=	"if (confirm('" . addslashes( $confirmMsg ) . "')) { ";
        }
        if ( $extended ) {
        	$script .= 'hideMainMenu();';
        }
        $script .= "submitbutton('$task')";
        if ( $confirmMsg ) {
        	$script	.=	'}';
        }
        if ( $listprompt ) {
        	$script	.=	'}';
        }
        return $script;
    }

    function getTemplate( ) {
        global $database;
        $sql = "SELECT template FROM #__templates_menu WHERE client_id='1' AND menuid='0'";
        $database->setQuery( $sql );
        return $database->loadResult();
    }

    /**
	* Writes the common 'new' icon for the button bar
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function addNew( $task='new', $alt=null ) {
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('New'), 'new');
    }

    /**
	* Writes the common 'new' icon for the button bar.
	* Extended version of addNew() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function addNewX( $task='new', $alt=null ) {
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('New'), 'new', true);
    }

    /**
	* Writes a common 'publish' button
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function publish( $task='publish', $alt=null ) {
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Publish'), 'publish');
    }

    /**
	* Writes a common 'publish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function publishList( $task='publish', $alt=null ) {
        $listprompt = CBTxt::T('Please make a selection from the list to publish');
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Publish'), 'publish', false, $listprompt);
    }

    /**
	* Writes a common 'default' button for a record
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function makeDefault( $task='default', $alt=null ) {
        $listprompt = CBTxt::T('Please select an item to make default');
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Default'), 'publish', false, $listprompt);
    }

    /**
	* Writes a common 'assign' button for a record
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function assign( $task='assign', $alt=null ) {
        $listprompt = CBTxt::T('Please select an item to assign');
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Assign'), 'publish', false, $listprompt);
    }

    /**
	* Writes a common 'unpublish' button
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function unpublish( $task='unpublish', $alt=null ) {
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Unpublish'), 'unpublish');
    }

    /**
	* Writes a common 'unpublish' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function unpublishList( $task='unpublish', $alt=null ) {
        $listprompt = CBTxt::T('Please make a selection from the list to unpublish');
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Unpublish'), 'unpublish', false, $listprompt);
    }

    /**
	* Writes a common 'archive' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function archiveList( $task='archive', $alt=null ) {
        $listprompt = CBTxt::T('Please make a selection from the list to archive');
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Archive'), 'archive', false, $listprompt);
    }

    /**
	* Writes an unarchive button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function unarchiveList( $task='unarchive', $alt=null ) {
        $listprompt = CBTxt::T('Please select a news story to unarchive');
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Unarchive'), 'unarchive', false, $listprompt);
    }

    /**
	* Writes a common 'edit' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function editList( $task='edit', $alt=null ) {
        $listprompt = CBTxt::T('Please select an item from the list to edit');
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Edit'), 'edit', false, $listprompt);
    }

    /**
	* Writes a common 'edit' button for a list of records.
	* Extended version of editList() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function editListX( $task='edit', $alt=null ) {
        $listprompt = CBTxt::T('Please select an item from the list to edit');
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Edit'), 'edit', true, $listprompt);
    }

    /**
	* Writes a common 'edit' button for a template html
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function editHtml( $task='edit_source', $alt=null ) {
        $listprompt = CBTxt::T('Please select an item from the list to edit');
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Edit HTML'), 'html', false, $listprompt);
    }

    /**
	* Writes a common 'edit' button for a template html.
	* Extended version of editHtml() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function editHtmlX( $task='edit_source', $alt=null ) {
        $listprompt = CBTxt::T('Please select an item from the list to edit');
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Edit HTML'), 'html', true, $listprompt);
    }

    /**
	* Writes a common 'edit' button for a template css
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function editCss( $task='edit_css', $alt=null ) {
        $listprompt = CBTxt::T('Please select an item from the list to edit');
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Edit CSS'), 'css', false, $listprompt);
    }

    /**
	* Writes a common 'edit' button for a template css.
	* Extended version of editCss() calling hideMainMenu() before submitbutton().
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function editCssX( $task='edit_css', $alt=null ) {
        $listprompt = CBTxt::T('Please select an item from the list to edit');
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Edit CSS'), 'css', true, $listprompt);
    }

    /**
	* Writes a common 'delete' button for a list of records
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function deleteList( $msg='', $task='remove', $alt=null ) {
        $listprompt = CBTxt::T('Please make a selection from the list to delete');
        $msgIntro	=	'Are you sure you want to delete the selected items ?  ';
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Delete'), 'delete', false, $listprompt, $msgIntro . $msg );
    }

    /**
	* Writes a common 'delete' button for a list of records.
	* Extended version of deleteList() calling hideMainMenu() before submitbutton().
	* @param string  Postscript for the 'are you sure' message
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function deleteListX( $msg='', $task='remove', $alt=null ) {
        $listprompt = CBTxt::T('Please make a selection from the list to delete');
        $msgIntro	=	'Are you sure you want to delete the selected items ?  ';
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Delete'), 'delete', true, $listprompt, $msgIntro . $msg );
    }

    /**
	* Write a trash button that will move items to Trash Manager
	*/
    function trash( $task='remove', $alt=null ) {
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Trash'), 'delete');
    }

    /**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension)
	*/
    function preview( $popup='', $updateEditors=false ) {
    	global $_CB_framework;
        $image = cbMenuBarBase::ImageCheckAdmin( 'preview.png', '/administrator/images/', NULL, NULL, CBTxt::T('Preview'), 'preview' );
        $image2 = cbMenuBarBase::ImageCheckAdmin( 'preview_f2.png', '/administrator/images/', NULL, NULL, CBTxt::T('Preview'), 'preview', 0 );
        $cur_template = CBtoolmenuBar::getTemplate();

        ob_start();
		?>
		function popup() {
		    <?php
		    if ($popup == 'contentwindow') {
		        echo $_CB_framework->saveCmsEditorJS( 'introtext' );
		        echo $_CB_framework->saveCmsEditorJS( 'fulltext' );
		    } elseif ($popup == 'modulewindow') {
		    	$_CB_framework->saveCmsEditorJS( 'content' );
		    }
		    ?>
		    window.open('index3.php?pop=/<?php echo $popup;?>.php&amp;t=<?php echo $cur_template; ?>', 'win1', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
		}
		<?php
		$cbjavascript	=	ob_get_contents();
		ob_end_clean();
		$_CB_framework->document->addHeadScriptDeclaration( $cbjavascript );
		
		echo CBtoolmenuBar::_output( 'popup();', 'preview', CBTxt::T('Preview') );
    }

    /**
	* Writes a preview button for a given option (opens a popup window)
	* @param string The name of the popup file (excluding the file extension for an xml file)
	* @param boolean Use the help file in the component directory
	*/
    function help( $ref, $com=false ) {
    	global $_CB_framework;

        $image		=	cbMenuBarBase::ImageCheckAdmin( 'help.png', '/administrator/images/', NULL, NULL, CBTxt::T('Help'), 'help' );
        $image2		=	cbMenuBarBase::ImageCheckAdmin( 'help_f2.png', '/administrator/images/', NULL, NULL, CBTxt::T('Help'), 'help', 0 );
        $live_site	=	$_CB_framework->getCfg( 'live_site' );
        $rootpath	=	$_CB_framework->getCfg( 'absolute_path' );
        /*$helpUrl = mosGetParam( $GLOBALS, 'mosConfig_helpurl', '' );
        if ($helpUrl) {
        $url = $helpUrl . '/index2.php?option=com_content&amp;task=findkey&pop=1&keyref=' . urlencode( $ref );
        } else {*/
        $option = $GLOBALS['option'];
        if (substr($option,0,4) != 'com_') $option = "com_$option";
        $component = substr($option, 4);
        if ($com) {
            $url = '/administrator/components/' . $option . '/help/';
        }else{
            $url = '/help/';
        }
        $ref = $component.'.'.$ref . '.html';
        $url .= $ref;

        if (!file_exists($rootpath.'/help/'.$ref)) return false;
        $url = $live_site . $url;

        $onClickJs	=	"window.open('$url', 'mambo_help_win', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');";
        echo CBtoolmenuBar::_output( $onClickJs, 'help', CBTxt::T('Help') );
        /*}*/
    }

    /**
	* Writes a save button for a given option
	* Apply operation leads to a save action only (does not leave edit mode)
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function apply( $task='apply', $alt=null, $inlineJs=true  ) {
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Apply'), 'apply', false, '', '', $inlineJs );
    }

    /**
	* Writes a save button for a given option
	* Save operation leads to a save and then close action
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function save( $task='save', $alt=null, $inlineJs=true ) {
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Save'), 'save', false, '', '', $inlineJs );
    }

    /**
	* Writes a save button for a given option (NOTE this is being deprecated)
	*/
    function savenew() {
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Save'), 'savenew');		//should be save image
    }

    /**
	* Writes a save button for a given option (NOTE this is being deprecated)
	*/
    function saveedit() {
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Save'), 'saveedit');		//should be save image
    }

    /**
	* Writes a cancel button and invokes a cancel operation (eg a checkin)
	* @param string An override for the task
	* @param string An override for the alt text
	*/
    function cancel( $task='cancel', $alt=null, $inlineJs=true ) {
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Cancel'), 'cancel', false, '', '', $inlineJs );
    }

    /**
	* Writes a cancel button that will go back to the previous page without doing
	* any other operation
	*/
    function back( $alt = null, $href = '' ) {
        if ( is_null( $alt ) ) {
        	$alt = CBTxt::T('Back');
        }
        if ( $href ) {
            $link		=	$href;
            $onClickJs	=	null;
        } else {
        	$link		=	'#';
            $onClickJs	=	'window.history.go(-1);return false;';
        }
        echo CBtoolmenuBar::_output( $onClickJs, 'back', $alt, $link );
    }

    /**
	* Write a divider between menu buttons
	*/
    function divider() {
        $image = cbMenuBarBase::ImageCheckAdmin( 'menu_divider.png', '/administrator/images/' );
		?>
		<span class="cbtoolbardivider">
		<?php echo $image; ?>
		</span>
		<?php
    }

    /**
	* Writes a media_manager button
	* @param string The sub-drectory to upload the media to
	*/
    function media_manager( $directory = '', $alt=null ) {
        if ( is_null( $alt ) ) {
        	$alt = CBTxt::T('Upload');
        }
        $cur_template = CBtoolmenuBar::getTemplate();
        $image = cbMenuBarBase::ImageCheckAdmin( 'upload.png', '/administrator/images/', NULL, NULL, CBTxt::T('Upload Image'), 'uploadPic' );
        // $image2 = cbMenuBarBase::ImageCheckAdmin( 'upload_f2.png', '/administrator/images/', NULL, NULL, CBTxt::T('Upload Image'), 'uploadPic', 0 );
        
        $onClickJs	=	"popupWindow('index3.php?pop=uploadimage.php&amp;directory=$directory&amp;t=$cur_template','win1',350,100,'no');";
      	echo CBtoolmenuBar::_output( $onClickJs, $image, $alt );
    }

    /**
	* Writes a spacer cell
	* @param string The width for the cell
	*/
    function spacer( $width='' )
    {
        if ($width != '') {
?>
			<span class="cbtoolbarspacer" style="width:<?php echo $width;?>;">&nbsp;</span>
<?php
        } else {
?>
			<span class="cbtoolbarspacer">&nbsp;</span>
<?php
        }
    }

    /**
	* Writes the end of the menu bar table
	*/
    function endTable() {
		echo '</div>';
    }
	/**
	* Checks to see if an image exists in the current templates image directory
 	* if it does it loads this image.  Otherwise the default image is loaded.
	* Also can be used in conjunction with the menulist param to create the chosen image
	* load the default or use no image
	*/
	function ImageCheckAdmin( $file, $directory='/administrator/images/', $param=NULL, $param_directory='/administrator/images/', $alt=NULL, $name=NULL, $type=1, $align='middle' ) {
		global $_CB_framework;

		$live_site		=	$_CB_framework->getCfg( 'live_site' );
		$mainframe		=&	$_CB_framework->_baseFramework;
		$cur_template 	=	$mainframe->getTemplate();
// ECHO $_CB_framework->getCfg( 'absolute_path' ) . '/administrator/templates/' . $cur_template . '/images/' . $file;
		if ( $param ) {
			$image		=	$live_site . $param_directory . $param;
		} else {
			if ( file_exists($_CB_framework->getCfg( 'absolute_path' ) . '/administrator/templates/' . $cur_template . '/images/' . $file ) ) {
				$image	=	$live_site . '/administrator/templates/' . $cur_template . '/images/' . $file;
			}
			else $image	=	$live_site . $directory . $file;
		}
		// outputs actual html <img> tag
		if ( $type ) {
			$image		=	'<img src="'. $image .'" alt="'. $alt .'" align="'. $align .'" name="'. $name .'" border="0" />';
		}
		return $image;
	}
}
class CBtoolmenuBar extends cbMenuBarBase {
	/**
	* Writes the common $action icon for the button bar
	* @param string url link
	* @param string action (for displaying correct icon))
	* @param string An override for the alt text
	*/
	function linkAction( $action='new', $link='', $alt='New' ) {
		if ( cbStartOfStringMatch( $link, 'javascript:' ) ) {
			$href	=	'#';
			$onClickJs	=	substr( $link, 11 );
		} else {
			$href		=	$link;
			$onClickJs	=	null;
		}
		echo CBtoolmenuBar::_output( $onClickJs, $action, $alt, $href );
	}
	/**
	* Writes a common 'edit' button for a list of records
	* @param string An override for the task
	* @param string An override for the alt text
	*/
	function editListNoSelect( $task='edit', $alt='Edit' ) {
        // $listprompt = CBTxt::T('Please select an item from the list to edit');
        $listprompt		=	'';
        CBtoolmenuBar::addToToolBar ($task, $alt, CBTxt::T('Edit'), 'edit', false, $listprompt);
    }
}

class TOOLBAR_usersextras {
	/**
	* Draws the menu for a New users
	*/
	function _NEW() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::save();
		CBtoolmenuBar::cancel('showusers');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}
	/** Edit user */
	function _EDIT() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::save( 'save', null, false );
		CBtoolmenuBar::cancel('showusers', null, false );
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}

	function _NEW_TAB() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::save('saveTab');
		CBtoolmenuBar::cancel('showTab');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}
	
	function _EDIT_TAB() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::save('saveTab');
		CBtoolmenuBar::cancel('showTab');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}

	function _DEFAULT_TAB() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::custom( 'newTab', 'new.png', 'new_f2.png', 'New Tab', false );
		CBtoolmenuBar::editList('editTab');
		CBtoolmenuBar::deleteList('The tab will be deleted and cannot be undone!','removeTab');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}

	function _NEW_FIELD() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::save('saveField');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::apply('applyField');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::cancel('showField');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}
	
	function _EDIT_FIELD() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::save('saveField');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::apply('applyField');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::cancel('showField');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}

	function _DEFAULT_FIELD() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::custom( 'newField', 'new.png', 'new_f2.png', 'New Field', false );
		CBtoolmenuBar::editList('editField');
		CBtoolmenuBar::deleteList('The Field and all user data associated to this field will be lost and cannot be undone!','removeField');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}

	function _NEW_LIST() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::save('saveList');
		CBtoolmenuBar::cancel('showLists');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}
	
	function _EDIT_LIST() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::save('saveList');
		CBtoolmenuBar::cancel('showLists');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}

	function _DEFAULT_LIST() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::custom( 'newList', 'new.png', 'new_f2.png', 'New List', false );
		CBtoolmenuBar::editList('editList');
		CBtoolmenuBar::deleteList('The selected List(s) will be deleted and cannot be undone!','removeList');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}

	function _EDIT_CONFIG() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::save('saveconfig');
		CBtoolmenuBar::cancel();
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}

	function _DEFAULT() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::addNew();
		CBtoolmenuBar::editList();
		CBtoolmenuBar::deleteList();
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}

	function _EDIT_PLUGIN() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::save('savePlugin');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::apply('applyPlugin');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::cancel( 'cancelPlugin', 'Close' );
		/*
		if ( $id ) {
			// for existing content items the button is renamed `close`
			CBtoolmenuBar::cancel( 'cancelPlugin', 'Close' );
		} else {
			CBtoolmenuBar::cancel('showPlugins');
		}
		*/
		CBtoolmenuBar::endTable();
	}

	function _PLUGIN_ACTION_SHOW() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::cancel( 'cancelPluginAction', 'Close' );
		CBtoolmenuBar::endTable();
	}

	function _PLUGIN_ACTION_EDIT() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::save('savePlugin');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::apply('applyPlugin');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::cancel( 'cancelPluginAction', 'Close' );
		/*
		if ( $id ) {
			// for existing content items the button is renamed `close`
			CBtoolmenuBar::cancel( 'cancelPlugin', 'Close' );
		} else {
			CBtoolmenuBar::cancel('showPlugins');
		}
		*/
		CBtoolmenuBar::endTable();
	}

	function _PLUGIN_MENU( &$xmlToolbarMenuArray ) {
		if ( $xmlToolbarMenuArray && ( count( $xmlToolbarMenuArray ) > 0 ) ) {
			CBtoolmenuBar::startTable();
			foreach ( $xmlToolbarMenuArray as $xmlTBmenu ) {
				if ( $xmlTBmenu && ( count( $xmlTBmenu->children() ) > 0 ) ) {
					foreach ( $xmlTBmenu->children() as $menu ) {
						if ( $menu->name() == 'menu' ) {
							// $name			=	$menu->attributes( 'name' );
							$action			=	$menu->attributes( 'action' );
							$task			=	$menu->attributes( 'task' );
							$label			=	$menu->attributes( 'label' );
							// $description	=	$menu->attributes( 'description' );
							
							if ( in_array( $action, get_class_methods( 'CBtoolmenuBar' ) ) || in_array( strtolower( $action ), get_class_methods( 'CBtoolmenuBar' ) ) ) {		// PHP 5 || PHP 4
								switch ( $action ) {
									case 'custom':
									case 'customX':
										$icon		=	$menu->attributes( 'icon' );
										$iconOver	=	$menu->attributes( 'iconover' );
										CBtoolmenuBar::$action( $task, $icon, $iconOver, $label, false );
										break;
									case 'editList':
										CBtoolmenuBar::editListNoSelect( $task, $label );
										break;
									case 'deleteList':
									case 'deleteListX':
										$message	=	$menu->attributes( 'message' );
										CBtoolmenuBar::$action( $message, $task, $label );
										break;
									case 'trash':
										CBtoolmenuBar::$action( $task, $label, false );
										break;
									case 'preview':
										$popup	=	$menu->attributes( 'popup' );
										CBtoolmenuBar::$action( $popup, true );
										break;
									case 'help':
										$ref	=	$menu->attributes( 'ref' );
										CBtoolmenuBar::$action( $ref, true );
										break;
									case 'savenew':
									case 'saveedit':
									case 'divider':
									case 'spacer':
										CBtoolmenuBar::$action();
										break;
									case 'back':
										$href	=	$menu->attributes( 'href' );
										CBtoolmenuBar::$action( $label, $href );
										break;
									case 'media_manager':
										$directory	=	$menu->attributes( 'directory' );
										CBtoolmenuBar::$action( $directory, $label );
										break;
									case 'linkAction':
										$urllink	=	$menu->attributes( 'urllink' );
										CBtoolmenuBar::$action( $task, $urllink, $label );
										break;
									default:
										CBtoolmenuBar::$action( $task, $label );
										break;
								}
	
							}
							// if ( in_array( $action, array(	'customX', 'addNew', 'addNewX', 'publish', 'publishList', 'makeDefault', 'assign', 'unpublish', 'unpublishList', 
							//								'archiveList', 'unarchiveList', ) ) ) {
								// nothing
							// }
						}
					}
				}
			}
			CBtoolmenuBar::endTable();
		}
	}

	function _DEFAULT_PLUGIN_MENU() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::linkAction( 'cancel', 'index2.php?option=comprofiler&task=showPlugins', 'Close' );
		CBtoolmenuBar::endTable();
	}

	function _DEFAULT_PLUGIN() {
		CBtoolmenuBar::startTable();
		CBtoolmenuBar::publishList('publishPlugin');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::unpublishList('unpublishPlugin');
		// CBtoolmenuBar::spacer();
		// CBtoolmenuBar::   "addInstall" link ('newPlugin');
/*
		CBtoolmenuBar::spacer();
		if (is_callable(array("CBtoolmenuBar","addNewX"))) {		// Mambo 4.5.0 support:
			CBtoolmenuBar::addNewX('newPlugin');
		} else {
			CBtoolmenuBar::addNew('newPlugin');
		}
*/
		CBtoolmenuBar::spacer();
		if (is_callable(array("CBtoolmenuBar","editListX"))) {		// Mambo 4.5.0 support:
			CBtoolmenuBar::editListX('editPlugin');
		} else {
			CBtoolmenuBar::editList('editPlugin');
		}
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::deleteList('','deletePlugin');
		CBtoolmenuBar::spacer();
		CBtoolmenuBar::endTable();
	}
}
?>