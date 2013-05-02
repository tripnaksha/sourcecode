<?php
/**
* @version		$Id: popup.php 74 2009-06-04 12:28:43Z happynoodleboy $
* @package		Joomla Content Editor (JCE)
* @subpackage	Components
* @copyright	Copyright (C) 2005 - 2009 Ryan Demmer. All rights reserved.
* @license		GNU/GPL
* JCE is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Get variables
$img 	= JRequest::getVar( 'img' );
$title 	= str_replace( '_', ' ', JRequest::getWord( 'title', 'Image' ) );
$mode 	= JRequest::getInt( 'mode', '0' );
$click 	= JRequest::getInt( 'click', '0' );
$print 	= JRequest::getInt( 'print', '0' );

$width 	= JRequest::getInt( 'w' );
$height = JRequest::getInt( 'h' );

// Make image relative
$img 	= str_replace( JURI::base(), '', $img );

JHTML::script('popup.js', 'components/com_jce/js/');
JHTML::stylesheet('popup.css', 'components/com_jce/css/');
?>
<script type="text/javascript">
	// Mootools required!
	if(typeof window.addEvent != 'undefined'){
		window.addEvent('load', function(){
			jcePopupWindow.init(<?php echo $width;?>, <?php echo $height;?>, <?php echo $click;?>);
		});
	}else{
		jcePopupWindow.init(<?php echo $width;?>, <?php echo $height;?>, <?php echo $click;?>);
	}
</script>
<style type="text/css">
	/* Reset template style sheet */
	body{margin:0;padding:0;}
	div{margin:0;padding:0;}
	img{margin:0;padding:0;}
</style>
<div id="jce_popup">
    <?php if( $mode ){?>
    <div class="contentheading"><?php echo $title;?></div>
    <?php }?>
    <?php if( $mode && $print ){?>
    <div class="buttonheading"><a href="javascript:;" onClick="window.print(); return false"><img src="<?php echo JURI::base(); ?>images/M_images/printButton.png" width="16" height="16" alt="<?php echo JText::_('Print');?>" title="<?php echo JText::_('Print');?>" /></a></div>
    <?php }?>
    <div><img src="<?php echo $img;?>" width="<?php echo $width;?>" height="<?php echo $height;?>" title="<?php echo $title;?>" alt="<?php echo $title;?>" onclick="window.close();" /></div>
</div>