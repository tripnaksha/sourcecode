<?php defined('_JEXEC') or die('Restricted access');

$editor =& JFactory::getEditor();
$createdate =& JFactory::getDate();
$limitstart =
JRequest::getVar('limitstart', '0', '', 'int');


global $mainframe;

// Initialize variables
$db		= &JFactory::getDBO();

// Get some variables from the request
$sectionid			= JRequest::getVar( 'sectionid', -1, '', 'int' );
$option				= JRequest::getCmd( 'option' );
$catid				= $mainframe->getUserStateFromRequest('articleelement.catid',				'catid',			0,	'int');
$filter_sectionid	= $mainframe->getUserStateFromRequest('articleelement.filter_sectionid',	'filter_sectionid',	-1,	'int');
$search				= $mainframe->getUserStateFromRequest('articleelement.search',				'search',			'',	'string');
$search				= JString::strtolower($search);

// get list of categories for dropdown filter
$filter = ($filter_sectionid >= 0) ? ' WHERE cc.section = '.$db->Quote($filter_sectionid) : '';
$categoryQuery = 'SELECT cc.id AS value, cc.title AS text, section' .
				' FROM #__categories AS cc' .
				' INNER JOIN #__sections AS s ON s.id = cc.section' .$filter ;
$categories[] = JHTML::_('select.option', '0', '- '.JText::_('Select Category').' -');
$db->setQuery($categoryQuery);
$categories = array_merge($categories, $db->loadObjectList());
$catidFilter = JHTML::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $catid);
// get list of sections for dropdown filter
$javascript = 'onchange="document.adminForm.submit();"';
$sectionidFilter = JHTML::_('list.section', 'filter_sectionid', $filter_sectionid, $javascript);

?>
<script type="text/javascript">
function autofill(tag){
      //alert(tag.style);
}
</script>
<form action="index2.php?controller=tag&option=com_tag" method="post"
	name="adminForm" id="adminForm" class="adminForm" autocomplete="off">


<table>
	<tr>
		<td width="100%"><?php echo JText::_( 'Filter' ); ?>: <input
			type="text" name="search" id="search" value="<?php echo($search);?>"
			class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
		<button
			onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
		</td>
		<td nowrap="nowrap"><?php
		echo $sectionidFilter;
		echo $catidFilter;
		?></td>
	</tr>
</table>
<table class="adminlist">
	<thead>
		<tr>
			<th width="10" align="left"><?php echo JText::_( 'Num' ); ?></th>
			<th class="title" width="20%"><?php echo JText::_('ARTICLE');?></th>
			<th class="title" width="10%"><?php echo JText::_('SECTION');?></th>
			<th class="title" width="10%"><?php echo JText::_('CATEGORY');?></th>
			<th><?php echo JText::_('TAGS');?></th>
			<th class="title" width="10%"><?php echo JText::_('Component');?></th>

		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="13"><?php echo $this->tagList->page->getPagesLinks(); ?></td>
		</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;
	$rows=$this->tagList->list;
	if( count( $rows ) ) {
		$combined=array();
		for($i=0,$n=count($rows);$i<$n;$i++){
			$row 	= &$rows[$i];
			if(isset($combined[$row->cid])){
				$combined[$row->cid]->tag.=','.$row->name;
			}else{
				if(isset($row->name)){
					$obj->tag=$row->name;
				}else{
					$obj->tag='';
				}
				$obj->title=$row->title;
				$obj->id=$row->cid;
				$obj->category=$row->category;
				$obj->section=$row->section;
				$obj->component=$row->component;
				$combined[$row->cid]=$obj;
			}
			unset($obj);
		}
		unset($rows);
		$rows=array_values($combined);
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row 	= &$rows[$i];
			JFilterOutput::objectHtmlSafe($row);
			?>
		<tr class="<?php echo 'row'.$i; ?>">
			<td><?php echo  $limitstart+$i+1  ?></td>
			<td><?php echo $row->title; ?></td>
			<td><?php echo $row->section; ?></td>
			<td><?php echo $row->category; ?></td>
			<td class="order">
			  <span>
			    <input type="hidden" name="id[]" value="<?php echo $row->id;?>" />
			    <input type="hidden" name="compo[]" value="<?php echo $row->component;?>" />
			    <input name="tag[]" value="<?php echo $row->tag; ?>" id="<?php echo 'tag_'.$row->id;?>" type="text" size="80" onclick="autofill(<?php echo 'tag_'.$row->id;?>)" />
			  </span>
			</td>
			<td><?php echo $row->component;?></td>
				<?php
		}
		$k = 1 - $k;
		?>
		</tr>

		<?php
	} else {
		?>
		<tr>
			<td colspan="8"><?php echo JText::_('There are no Articles'); ?></td>
		</tr>
		<?php
	}

	?>
	</tbody>
</table>
<input type="hidden" name="currenttag" id="currenttag" />
<input type="hidden" name="boxchecked" value="0" /> 
<input type="hidden" name="task" value=""> 
<input type="hidden" name="controller" value="tag"> 
<input type="hidden" name="option" value="<?php echo $option; ?>"> <?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="limitstart" />
</form>
