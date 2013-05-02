<?php
/**
 * @version 1.0 $Id: choosevenue.php 783 2008-09-29 14:40:13Z schlu $
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>

<script type="text/javascript">

	function tableOrdering( order, dir, view )
	{
		var form = document.getElementById("adminForm");

		form.filter_order.value 	= order;
		form.filter_order_Dir.value	= dir;
		form.submit( view );
	}
</script>

<div id="eventlist" class="el_selectvenue">

<h1 class='componentheading'>
	<?php
		echo JText::_('SELECTVENUE');
	?>
</h1>

<div class="clear"></div>

<form action="index.php?option=com_eventlist&amp;view=editevent&amp;layout=choosevenue&amp;tmpl=component" method="post" id="adminForm">

<div id="el_filter" class="floattext">
		<div class="el_fleft">
			<?php
			echo '<label for="filter_type">'.JText::_('FILTER').'</label>&nbsp;';
			echo $this->searchfilter.'&nbsp;';
			?>
			<input type="text" name="filter" id="filter" value="<?php echo $this->filter;?>" class="text_area" onchange="document.getElementById('adminForm').submit();" />
			<button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_( 'Go' ); ?></button>
			<button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();"><?php echo JText::_( 'Reset' ); ?></button>
		</div>
		<div class="el_fright">
			<?php
			echo '<label for="limit">'.JText::_('DISPLAY NUM').'</label>&nbsp;';
			echo $this->pageNav->getLimitBox();
			?>
		</div>

</div>

<table class="eventtable" width="100%" border="0" cellspacing="0" cellpadding="0" summary="eventlist">
	<thead>
		<tr>
			<th width="7" class="sectiontableheader" align="left"><?php echo JText::_( 'Num' ); ?></th>
			<th align="left" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', 'VENUE', 'l.name', $this->lists['order_Dir'], $this->lists['order'], 'selectvenue' ); ?></th>
			<th align="left" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', 'Created By', 'm.name', $this->lists['order_Dir'], $this->lists['order'], 'selectvenue' ); ?></th>
			<th align="left" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', 'Created On', 'l.createTime', $this->lists['order_Dir'], $this->lists['order'], 'selectvenue' ); ?></th>
		</tr>
	</thead>

	<tbody>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->rows ); $i < $n; $i++) {
			$row = &$this->rows[$i];
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td><?php echo $this->pageNav->getRowOffset( $i ); ?></td>
			<td align="left">
				<a style="cursor:pointer" onclick="window.parent.elSelectVenue('<?php echo $row->id; ?>', '<?php echo str_replace( array("'", "\""), array("\\'", ""), $row->venue); ?>');">
						<?php echo $this->escape($row->venue); ?>
				</a>
			</td>
			<td align="left"><?php echo $this->escape($row->city); ?></td>
			<td align="left"><?php echo $row->country; ?></td>
		</tr>
		<?php $k = 1 - $k; } ?>
	</tbody>
</table>

<p>
<input type="hidden" name="task" value="selectvenue" />
<input type="hidden" name="option" value="com_eventlist" />
<input type="hidden" name="tmpl" value="component" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</p>
</form>

<p class="pageslinks">
	<?php echo $this->pageNav->getPagesLinks(); ?>
</p>

<p class="pagescounter">
	<?php echo $this->pageNav->getPagesCounter(); ?>
</p>

<p class="copyright">
<?php echo ELOutput::footer();	?>
</p>
</div>
