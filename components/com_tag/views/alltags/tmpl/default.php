<?php defined('_JEXEC') or die('Restricted access');
require_once JPATH_COMPONENT_SITE.DS.'helper'.DS.'helper.php';
$document	   =& JFactory::getDocument();
$description=JoomlaTagsHelper::param('metaDescription');
$document->setDescription( $description );
$keywords=JoomlaTagsHelper::param('metaKeywords');

$document->setMetadata('keywords', $keywords);

$title=JoomlaTagsHelper::param('title');
$document->setTitle($title);

$document->addStyleSheet(JURI::base() . 'components/com_tag/css/tagcloud.css');
?>
<div class="componentheading"><?php echo($title);?></div>
<?php $rows=&$this->allTags;

if(isset($rows)&&!empty($rows)){
	//we will store the order of the $rows here
	$total_tags = count($rows);
	$index=0;
	$rowIndexArray= array();
	while($index<$total_tags){
		$rowIndexArray[$rows[$index]->name]=$index;
		$index++;
	}
	//done store
	usort($rows, array('JoomlaTagsHelper','tag_popularasort'));
	$tag_sizes = 7;

	$min_tags = $total_tags / $tag_sizes;
	$bucket_count = 1;
	$bucket_items = 0;
	$tags_set = 0;
	foreach($rows as $row){
		//$row=&$rows[$index];
		$row->link=JRoute::_('index.php?option=com_tag&task=tag&tag='.$row->name);
			
		$tag_count = $row->ct;
		if(($bucket_items >= $min_tags) and $last_count != $tag_count and $bucket_count < $tag_sizes)
		{
			$bucket_count++;
			$bucket_items = 0;
			// Calculate a new minimum number of tags for the remaining classes.
			$remaining_tags = $total_tags - $tags_set;
			$min_tags = $remaining_tags / $bucket_count;
		}
		$row->class = 'tag'.$bucket_count;
		$bucket_items++;
		$tags_set++;
		$last_count = $tag_count;
		

	}
	//restore to the orignal order
	$orderedRows=array();

	foreach($rows as $row){
		$origalOrder=$rowIndexArray[$row->name];		
		$row->name=JoomlaTagsHelper::ucwords($row->name);
		$orderedRows[$origalOrder]=$row;
	}

	ksort($orderedRows);
	//done restore
	//usort($rows, array('JoomlaTagsHelper','tag_alphasort'));
}
?>
<table class="contentpaneopen" border="0" cellpadding="0"
	cellspacing="0" width="100%">
	<tr>
		<td><?php if(isset($orderedRows)&&!empty($orderedRows)) {?>
		<div class="tagCloud"><?php	foreach ($orderedRows as $order=>$item) {?> <a
			href="<?php echo $item->link; ?>" rel="tag"
			class="<?php echo $item->class; ?>"> <?php echo $item->name; ?></a> <?php }?>

		</div>
		<?php }	 ?></td>
	</tr>
	<tr>
		<td>
		<!--div class="joomlatags">Powered by <a href="http://www.joomlatags.org"
			title="Tags for Joomla">Tags for Joomla</a></div-->

		</td>
	</tr>
</table>

