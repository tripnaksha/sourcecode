<?php defined('_JEXEC') or die('Restricted access');
$firstWarning=JRequest::getVar('FirstWarning',true);
$warning=JRequest::getVar('tagsWarning','FIRST_SAVE_WARNING');
if($firstWarning){
	$document =& JFactory::getDocument();
	$document->addStyleSheet(JURI::base() . 'components/com_tag/css/tagcloud.css');

	?>

<div class="warning">
<h1><?php echo JText::_('WARNING');?></h1>
<h2><?php echo JText::_($warning);?></h2>

</div>
<!--div class="joomlatags">Powered by <a href="http://www.joomlatags.org"
	title="Tags for Joomla">Tags for Joomla</a></div-->

	<?php };
	JRequest::setVar('FirstWarning',false);
	?>
