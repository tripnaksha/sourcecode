<?php defined('_JEXEC') or die('Restricted access');
$firstWarning=JRequest::getVar('FirstWarning',true);
$warning=JRequest::getVar('tagsWarning','FIRST_SAVE_WARNING');
if($firstWarning){
	$document =& JFactory::getDocument();
	$document->addStyleSheet(JURI::base() . 'components/com_tag/css/tag.css');

	?>

<div class="warning">
<h1><?php echo JText::_('WARNING');?></h1>
<h2>
<?php echo JText::_('FIRST_SAVE_WARNING');?>
</h2>
</div>
	<?php };
	JRequest::setVar('FirstWarning',false);
	?>

