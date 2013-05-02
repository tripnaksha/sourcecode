<?php
/**
 * @version		$Id: jce.php 110 2009-06-21 19:25:09Z happynoodleboy $
 * @package      JCE
 * @copyright    Copyright (C) 2005 - 2009 Ryan Demmer. All rights reserved.
 * @author		Ryan Demmer
 * @license      GNU/GPL
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Do not allow direct access
defined('_JEXEC') or die ('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * JCE WYSIWYG Editor Plugin
 *
 * @author Ryan Demmer <ryandemmer@gmail.com>
 * @package Editor - JCE
 * @since 1.5
 */
class plgEditorJCE extends JPlugin
{
    /**
     * Constructor
     *
     * For php4 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args (void) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @vars 	object $subject The object to observe
     * @vars 	array  $config  An array that holds the plugin configuration
     * @since 1.5
     */
    function plgEditorJCE( & $subject, $config)
    {
        parent::__construct($subject, $config);
    }
    /**
     * Method to handle the onInit event.
     *  - Initializes the JCE WYSIWYG Editor
     *
     * @access public
     * @return string JavaScript Initialization string
     * @since 1.5
     */
    function onInit()
    {
        global $mainframe;

        // Editor gets loaded twice in Legacy mode???
        if (JPluginHelper::isEnabled('system', 'legacy')) {
            if (defined('_JCE_ISLOADED')) {
                return false;
            }
            define('_JCE_ISLOADED', 1);
        }
        // Check for existence of Admin Component
        if (!is_dir(JPATH_SITE.DS.'components'.DS.'com_jce') || !is_dir(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jce')) {
            JError::raiseWarning('404', 'The JCE Administration Component is not installed! The Editor cannot function without it!');
        }
        // Load base Editor class
        require_once (dirname( __FILE__ ).DS.'jce'.DS.'libraries'.DS.'classes'.DS.'editor.php');

        // Create instance
        $jce = & JContentEditor::getInstance();
        $jce->addPlugins( array ('code', 'cleanup', 'tabfocus'));

        $document 	= & JFactory::getDocument();
        $params 	= $jce->getEditorParams();
        $gzip 		= $jce->getParam($params, 'editor_gzip', '0', '0')?'_gzip':'';

        $version = $jce->getVersion();
		$stamp = preg_match('/\d/', $version) ? '?version='. $version : '';
	    // TinyMCE url must be absolute!
        $document->addScript(JURI::root().'plugins/editors/jce/tiny_mce/tiny_mce'.$gzip.'.js'.$stamp);
        // Utility functions for saving
        $document->addScript(JURI::root(true).'/plugins/editors/jce/libraries/js/editor.js'.$stamp);

        // Set parameter array
        $vars = array ();

        $vars['doctype'] 								= '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">';
        $vars['mode'] 									= 'textareas';
        $vars['editor_selector'] 						= 'mceEditor';

        //Languages
        $vars['language'] 								= $jce->getLanguage();
        $vars['directionality'] 						= $jce->getLanguageDir();

        $settings = '';

        // Defaults
        $vars['theme'] 									= 'none';
        $vars['invalid_elements'] 						= 'applet,iframe,object,embed,script,style';
        $vars['plugins'] 								= '';

        if ($jce->checkUser()) {
            $vars['theme'] = 'advanced';
            // Url
            $vars['document_base_url'] 					= JURI::root();
            $vars['site_url'] 							= JURI::base(true).'/';
            
			// Theme
            $vars['theme_advanced_toolbar_location'] 	= $jce->getParam($params, 'editor_theme_advanced_toolbar_location', 'top', 'bottom');
            $vars['theme_advanced_toolbar_align'] 		= $jce->getParam($params, 'editor_theme_advanced_toolbar_align', 'left', 'center');
            $vars['theme_advanced_path'] 				= '1';
            $vars['theme_advanced_statusbar_location'] 	= $jce->getParam($params, 'editor_theme_advanced_statusbar_location', 'bottom', 'none');

            $vars['theme_advanced_resizing'] 			= $jce->getParam($params, 'editor_theme_advanced_resizing', '1', '0');
            $vars['theme_advanced_resize_horizontal'] 	= $jce->getParam($params, 'editor_theme_advanced_resize_horizontal', '1', '0');
            $vars['theme_advanced_resizing_use_cookie'] = $jce->getParam($params, 'editor_theme_advanced_resizing_use_cookie', '1', '0');

            $vars['theme_advanced_disable'] 			= $jce->getRemovePlugins();
            $vars['theme_advanced_blockformats'] 		= $jce->getParam($params, 'editor_theme_advanced_blockformats', 'p,div,h1,h2,h3,h4,h5,h6,blockquote,dt,dd,code,samp,pre', 'p,address,pre,h1,h2,h3,h4,h5,h6');
            $vars['removeformat_selector'] 				= '*';

			// Fonts
            $vars['theme_advanced_fonts'] 				= $jce->getEditorFonts($jce->getParam($params, 'editor_theme_advanced_fonts_add', ''), $jce->getParam($params, 'editor_theme_advanced_fonts_remove', ''));
            $vars['theme_advanced_font_sizes'] 			= $jce->getParam($params, 'editor_theme_advanced_font_sizes', '8pt,10pt,12pt,14pt,18pt,24pt,36pt');

            // Defaults
            $vars['theme_advanced_buttons1'] 			= '';
            $vars['theme_advanced_buttons2'] 			= '';
            $vars['theme_advanced_buttons3'] 			= '';
			
			// Cleanup
            $vars['verify_html'] 						= $jce->getParam($params, 'editor_verify_html', '0', '1');
            $vars['event_elements'] 					= $jce->getParam($params, 'editor_event_elements', 'a,img', 'a,img');
			
			// Editor Dimensions
            $vars['width'] 								= $jce->getParam($params, 'editor_width', '');
            $vars['height'] 							= $jce->getParam($params, 'editor_height', '');
			
			// Preview
            $vars['plugin_preview_width'] 				= $jce->getParam($params, 'preview_width', '750', '550');
            $vars['plugin_preview_height'] 				= $jce->getParam($params, 'preview_height', '550', '600');

            $vars['custom_colors'] 						= $jce->getParam($params, 'custom_colors', '', '');
			
			// Tables & Lists
            $vars['table_inline_editing'] 				= '1';
            $vars['fix_list_elements'] 					= '1';
            $vars['fix_table_elements'] 				= '1';

            // Encoding
            $vars['entity_encoding'] 					= $jce->getParam($params, 'editor_entity_encoding', 'raw', 'named');

            // Template CSS
            $template_path 	= JPATH_SITE.DS.'templates'.DS.$jce->getSiteTemplate().DS.'css';
            $template_url 	= JURI::root(true)."/templates/".$jce->getSiteTemplate()."/css/";
            // Joomla! 1.5 standard
            $template_file = 'template.css';
            // check for legacy template
            if (file_exists($template_path.DS.'template_css.css')) {
                $template_file = 'template_css.css';
            }
            $vars['content_css'] = $template_url.$template_file;

            //Custom template url
            if ($params->get('editor_content_css', '1') == '0') {
                $custom = $params->get('editor_content_css_custom', '');
                $vars['content_css'] = JURI::root(true).'/'.str_replace('$template', $jce->getSiteTemplate(), $custom);
            }
            // Get Extended elements
            $vars['extended_valid_elements'] = $jce->getParam($params, 'editor_extended_elements', '', '');
            // Configuration list of invalid elements as array
            $vars['invalid_elements'] = explode(',', $jce->getParam($params, 'editor_invalid_elements', '', ''));

            // Add elements to invalid list (removed by plugin)
            $jce->addKeys($vars['invalid_elements'], array ('applet', 'iframe', 'object', 'param', 'embed', 'script', 'style'));

            /* End Plugin functions */

            // Paragraph handling
            $vars['forced_root_block'] = $jce->getParam($params, 'editor_forced_root_block', '0', 'p');

            if ($params->get('editor_newlines', '0') == '1') {
                $vars['force_br_newlines'] 	= '1';
                $vars['force_p_newlines'] 	= '0';
            }
            else {
                $vars['force_br_newlines'] 	= '0';
                $vars['force_p_newlines'] 	= '1';
            }

            // 'Look & Feel'
            $vars['skin'] 				= $jce->getParam($params, 'editor_skin', 'default', 'default');
            $vars['skin_variant'] 		= $jce->getParam($params, 'editor_skin_variant', 'default', 'default');
            $vars['inlinepopups_skin'] 	= $jce->getParam($params, 'editor_inlinepopups_skin', 'clearlooks2');
            $vars['body_class'] 		= $jce->getParam($params, 'editor_body_class_type', 'custom') == 'contrast'?'mceForceColors':$jce->getParam($params, 'editor_body_class_custom', '');

            //Other - user specified
            $userParams = $params->get('editor_custom_config', '');
            $baseParams = array (
            'mode',
            'cleanup_callback',
            'save_callback',
            'file_browser_callback',
            'onpageload',
            'editor_selector'
            );
            if ($userParams) {
                $userParams = explode(';', $userParams);
                foreach ($userParams as $userParam) {
                	$keys = explode(':', $userParam);
                	if (!in_array(trim($keys[0]), $baseParams)) {
                    	$vars[trim($keys[0])] = count($keys) > 1?trim($keys[1]):'';
                    }
                }
            }
	        $callbackFile = $params->get('editor_callback_file', '');
	        // Relative urls?
	        $vars['relative_urls'] = $jce->getParam($params, 'editor_relative_urls', '1', '1');
	        if ($vars['relative_urls'] == '0') {
	            $vars['remove_script_host'] = '0';
	        }
	        // Get rows
	        $rows = $jce->getRows();
	        for ($i = 1; $i <= count($rows); $i++) {
	            $vars['theme_advanced_buttons'.$i] = $rows[$i];
	        }
	        // Add Paste plugin if clipboard loaded. TODO Remove in 1.6
	        if ($jce->isLoaded('clipboard') && !$jce->isLoaded('paste')) {
	            $jce->addPlugins('paste');
	        }
	        // Plugins
	        $vars['plugins'] = $jce->getPlugins();
	        // Get all optional plugin configuration options
			$jce->getPluginConfig($vars);
		}
        $i = 1;
        foreach ($vars as $k=>$v) {
            // If the value is an array, implode!
            if (is_array($v)) {
                $v = implode(',', $v);
                if ($v[0] == ',') {
                	$v = substr($v, 1);
                }
            }
            // Value must be set
            if ($v != '') {
				// objects or arrays or functions or regular expression
                if (preg_match('/(\[[^\]*]\]|\{[^\}]*\}|function\([^\}]*\}|^#(.*)#$)/', $v)) {
                    // replace hash delimiters with / for javascript regular expression
					$v = preg_replace('@^#(.*)#$@', '/$1/', $v);            
                }
				// anything that is not solely an integer
                else if (!is_numeric($v)) {
                    $v = '"'.$v.'"';                  
                }
				// 1 or 0 become true/false
                else if ($v == '1' || $v == '0') {
                    $v = intval($v)?'true':'false';
                }
                $settings .= "\t\t\t".$k.": ".$v."";
                if ($i < count($vars)) {
                    $settings .= ",\n";
                }
            }
            if (preg_match('/theme_advanced_buttons([1-3])/', $k) && $v == '') {
                $settings .= "\t\t\t".$k.": \"\"";
                if ($i < count($vars)) {
                    $settings .= ",\n";
                }
            }
            $i++;
        }
        $init = "
		tinyMCE.init({\n";
            $init .= preg_replace('/,?\n?$/', '', $settings)."
		});";
		if ($gzip) {
		    $plugins = is_array($vars['plugins'])?implode(',', $vars['plugins']):$vars['plugins'];
			$gz = "
			tinyMCE_GZ.init({
				plugins : '".$plugins."',
				themes : 'none,advanced',
				languages : '".$vars['language']."',
				disk_cache : false
			});";
			$document->addScriptDeclaration($gz);
		}
		$document->addScriptDeclaration($init);
		if ($params->get('editor_callback_file')) {
		    $document->addScript(JURI::root(true).'/'.$callbackFile);
		}
	}
	/**
	 * JCE WYSIWYG Editor - get the editor content
	 *
	 * @vars string 	The name of the editor
	 */
	function onGetContent($editor)
	{
	    return "JContentEditor.getContent('".$editor."');";
	}	
	/**
	 * JCE WYSIWYG Editor - set the editor content
	 *
	 * @vars string 	The name of the editor
	 */
	function onSetContent($editor, $html)
	{
	    return "JContentEditor.setContent('".$editor."','".$html."');";
	}	
	/**
	 * JCE WYSIWYG Editor - copy editor content to form field
	 *
	 * @vars string 	The name of the editor
	 */
	function onSave($editor)
	{
	    return "JContentEditor.save('".$editor."');";
	}	
	/**
	 * JCE WYSIWYG Editor - display the editor
	 *
	 * @vars string The name of the editor area
	 * @vars string The content of the field
	 * @vars string The width of the editor area
	 * @vars string The height of the editor area
	 * @vars int The number of columns for the editor area
	 * @vars int The number of rows for the editor area
	 * @vars mixed Can be boolean or array.
	 */
	function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true)
	{
	    // Only add "px" to width and height if they are not given as a percentage
	    if (is_numeric($width)) {
	        $width .= 'px';
	    }
	    if (is_numeric($height)) {
	        $height .= 'px';
	    }
	
	    $buttons = $this->_displayButtons($name, $buttons);
	
	    $editor = "<textarea id=\"$name\" name=\"$name\" cols=\"$col\" rows=\"$row\" style=\"width:{$width};height:{$height};\" class=\"mceEditor\">$content</textarea>\n" . $buttons;
	    return $editor;
	}	
	function onGetInsertMethod($name)
	{
	    $doc =& JFactory::getDocument();
	
	    $js = "function jInsertEditorText(text,editor){JContentEditor.insert(editor,text);}";
	    $doc->addScriptDeclaration($js);
	
	    return true;
	}	
    function _displayButtons($name, $buttons)
    {
        // Load modal popup behavior
        JHTML::_('behavior.modal', 'a.modal-button');
    
        $args['name'] = $name;
        $args['event'] = 'onGetInsertMethod';
    
        $return = '';
        $results[] = $this->update($args);
        foreach ($results as $result) {
            if (is_string($result) && trim($result)) {
                $return .= $result;
            }
        }  
        if (! empty($buttons)) {
            $results = $this->_subject->getButtons($name, $buttons);   
            /*
             * This will allow plugins to attach buttons or change the behavior on the fly using AJAX
             */
            $return .= "\n<div id=\"editor-xtd-buttons\">\n";
            foreach ($results as $button) {
	            /*
	             * Results should be an object
	             */
	            if ($button->get('name')) {
	                $modal = ($button->get('modal'))?'class="modal-button"':null;
	                $href = ($button->get('link'))?'href="'.JURI::base().$button->get('link').'"':null;
	                $onclick = ($button->get('onclick'))?'onclick="'.$button->get('onclick').'"':null;
	                $return .= "<div class=\"button2-left\"><div class=\"".$button->get('name')."\"><a ".$modal." title=\"".$button->get('text')."\" ".$href." ".$onclick." rel=\"".$button->get('options')."\">".$button->get('text')."</a></div></div>\n";
	           }
	    	}
	    	$return .= "</div>\n";
		}
        return $return;
	}
}
?>