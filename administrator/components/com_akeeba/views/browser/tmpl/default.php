<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: default.php 163 2010-06-13 14:42:35Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo JText::_('CONFIG_UI_BROWSER_TITLE'); ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="-1" />
<link rel="stylesheet" type="text/css"
	href="<?php echo JURI::base(); ?>../media/com_akeeba/theme/browser.css" />
	<script type="text/javascript">
		function akeeba_browser_useThis()
		{
			var rawFolder = document.forms.adminForm.folderraw.value;
			if( rawFolder == '[SITEROOT]' )
			{
				alert('<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_ROOTDIR')); ?>');
				rawFolder = '[SITETMP]';
			}
			window.parent.akeeba_browser_callback( rawFolder );
		}
	</script>
</head>
<body>

<?php if(empty($this->folder)): ?>
<form action="index.php" method="post" name="adminForm">
	<input type="hidden" name="option" value="com_akeeba" />
	<input type="hidden" name="view" value="browser" />
	<input type="hidden" name="format" value="raw" />
	<input type="hidden" name="folder" id="folder" value="" />
	<input type="hidden" name="processfolder" id="processfolder" value="0" />
</form>
	<?php else: ?>
<div id="controls">
<?php
$image = JURI::base().'../media/com_akeeba/icons/';
$image .= $this->writable ? 'ok_small.png' : 'error_small.png';
?>
<img src="<?php echo $image; ?>"
	style="float: right; position: relative; right: 3px; top: 6px;"
	alt="<?php echo $this->writable ? JText::_('WRITABLE') : JText::_('UNWRITABLE'); ?>"
	title="<?php echo $this->writable ? JText::_('WRITABLE') : JText::_('UNWRITABLE'); ?>" />
	<form action="index.php" method="post" name="adminForm">
		<input type="hidden" name="option" value="com_akeeba" />
		<input type="hidden" name="view" value="browser" />
		<input type="hidden" name="format" value="raw" />
		<input type="text" name="folder" id="folder" value="<?php echo $this->folder; ?>" />
		<input type="hidden" name="folderraw" id="folderraw" value="<?php echo $this->folder_raw ?>"/>
		<input type="submit" class="button"	value="<?php echo JText::_('BROWSER_LBL_GO'); ?>" />
		<input type="button" class="button"	value="<?php echo JText::_('BROWSER_LBL_USE'); ?>" onclick="akeeba_browser_useThis();" />
	</form>
</div>

<div id="breadcrumbs">
<?php if(count($this->breadcrumbs) > 0): ?>
	<?php $i = 0 ?>
	<?php foreach($this->breadcrumbs as $crumb):
		$link = JURI::base()."index.php?option=com_akeeba&view=browser&format=raw&folder=".urlencode($crumb['folder']);
		$label = htmlentities($crumb['label']);
		$i++;
		$bull = $i < count($this->breadcrumbs) ? '&bull;' : '';
	?>
	<a href="<?php echo $link ?>"><?php echo $label ?></a><?php echo $bull ?>
	<?php endforeach; ?>
<?php endif; ?>
</div>

<div id="browser">
<?php if(count($this->subfolders) > 0): ?>
	<?php $linkbase = JURI::base()."index.php?option=com_akeeba&view=browser&format=raw&folder="; ?>
	<a href="<?php echo $linkbase.urlencode($this->parent); ?>"><?php echo JText::_('BROWSER_LBL_GOPARENT') ?></a>
	<?php foreach($this->subfolders as $subfolder): ?>
	<a href="<?php echo $linkbase.urlencode($this->folder.DS.$subfolder); ?>"><?php echo htmlentities($subfolder) ?></a>
	<?php endforeach; ?>
<?php else: ?>

<?php
if(!$this->exists) {
	echo JText::_('BROWSER_ERR_NOTEXISTS');
} else if(!$this->inRoot) {
	echo JText::_('BROWSER_ERR_NONROOT');
} else if($this->openbasedirRestricted) {
	echo JText::_('BROWSER_ERR_BASEDIR');
} else {
?>
	<?php $linkbase = JURI::base()."index.php?option=com_akeeba&view=browser&format=raw&folder="; ?>
	<a href="<?php echo $linkbase.urlencode($this->parent); ?>"><?php echo JText::_('BROWSER_LBL_GOPARENT') ?></a>
	<?php
}
?>
<?php endif; ?>
</div>

<?php endif; ?>
</body>
</html>