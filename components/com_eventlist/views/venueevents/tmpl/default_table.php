<?php
/**
 * @version 1.0 $Id: default_table.php 662 2008-05-09 22:28:53Z schlu $
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
defined( '_JEXEC' ) or die( 'Restricted access' );
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

<form action="<?php echo $this->action; ?>" method="post" id="adminForm">

<?php if ($this->params->get('filter') || $this->params->get('display')) : ?>
<div id="el_filter" class="floattext">
		<?php if ($this->params->get('filter')) : ?>
		<div class="el_fleft">
			<?php
			echo '<label for="filter_type">'.JText::_('FILTER').'</label>&nbsp;';
			echo $this->lists['filter_types'].'&nbsp;';
			?>
			<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="text_area" onchange="document.getElementById('adminForm').submit();" />
			<button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_( 'GO' ); ?></button>
			<button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();"><?php echo JText::_( 'RESET' ); ?></button>
		</div>
		<?php endif; ?>
		<?php if ($this->params->get('display')) : ?>
		<div class="el_fright">
			<?php
			echo '<label for="limit">'.JText::_('DISPLAY NUM').'</label>&nbsp;';
			echo $this->pageNav->getLimitBox();
			?>
		</div>
		<?php endif; ?>
</div>
<?php endif; ?>

<table class="eventtable" width="<?php echo $this->elsettings->tablewidth; ?>" border="0" cellspacing="0" cellpadding="0" summary="eventlist">
	
	<colgroup>
		<col width="<?php echo $this->elsettings->datewidth; ?>" class="el_col_date" />
		<?php if ($this->elsettings->showtitle == 1) : ?>
			<col width="<?php echo $this->elsettings->titlewidth; ?>" class="el_col_title" />
		<?php endif; ?>
		<?php if ($this->elsettings->showlocate == 1) :	?>
			<col width="<?php echo $this->elsettings->locationwidth; ?>" class="el_col_venue" />
		<?php endif; ?>
		<?php if ($this->elsettings->showcity == 1) :	?>
			<col width="<?php echo $this->elsettings->citywidth; ?>" class="el_col_city" />
		<?php endif; ?>
		<?php if ($this->elsettings->showstate == 1) :	?>
			<col width="<?php echo $this->elsettings->statewidth; ?>" class="el_col_state" />
		<?php endif; ?>
		<?php if ($this->elsettings->showcat == 1) :	?>
			<col width="<?php echo $this->elsettings->catfrowidth; ?>" class="el_col_category" />
		<?php endif; ?>
	</colgroup>

	<thead>
			<tr>
				<th id="el_date" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', $this->escape($this->elsettings->datename), 'a.dates', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				if ($this->elsettings->showtitle == 1) :
				?>
				<th id="el_title" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', $this->escape($this->elsettings->titlename), 'a.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				if ($this->elsettings->showlocate == 1) :
				?>
				<th id="el_location" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', $this->escape($this->elsettings->locationname), 'l.venue', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				if ($this->elsettings->showcity == 1) :
				?>
				<th id="el_city" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', $this->escape($this->elsettings->cityname), 'l.city', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				if ($this->elsettings->showstate == 1) :
				?>
				<th id="el_state" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', $this->escape($this->elsettings->statename), 'l.state', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				if ($this->elsettings->showcat == 1) :
				?>
				<th id="el_category" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', $this->escape($this->elsettings->catfroname), 'c.catname', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				?>
			</tr>
	</thead>
	<tbody>
	<?php
	if ($this->noevents == 1) :
		?>
		<tr align="center"><td><?php echo JText::_( 'NO EVENTS' ); ?></td></tr>
		<?php
	else :

	$this->rows =& $this->getRows();

	foreach ($this->rows as $row) :
		?>
  			<tr class="sectiontableentry<?php echo ($row->odd +1 ) . $this->params->get( 'pageclass_sfx' ); ?>" >

    			<td headers="el_date" align="left">
    				<strong>
    					<?php echo ELOutput::formatdate($row->dates, $row->times); ?>
    					
    					<?php
    					if ($row->enddates) :
    						echo ' - '.ELOutput::formatdate($row->enddates, $row->endtimes);
    					endif;
    					?>
    				</strong>

					<?php
					if ($this->elsettings->showtime == 1) :
					?>
						<br />
						<?php
						echo ELOutput::formattime($row->dates, $row->times);
						
						if ($row->endtimes) :
							echo ' - '.ELOutput::formattime($row->enddates, $row->endtimes);
						endif;
					endif;
					?>
				</td>

				<?php
				//Link to details
				$detaillink = JRoute::_( 'index.php?view=details&id='. $row->slug );
				//title
				if (($this->elsettings->showtitle == 1 ) && ($this->elsettings->showdetails == 1) ) :
				?>

				<td headers="el_title" align="left" valign="top"><a href="<?php echo $detaillink ; ?>"> <?php echo $this->escape($row->title); ?></a></td>

				<?php
				endif;

				if (( $this->elsettings->showtitle == 1 ) && ($this->elsettings->showdetails == 0) ) :
				?>

				<td headers="el_title" align="left" valign="top"><?php echo $this->escape($row->title); ?></td>

				<?php
				endif;
				if ($this->elsettings->showlocate == 1) :
				?>

					<td headers="el_location" align="left" valign="top">
						<?php
						if ($this->elsettings->showlinkvenue == 1 ) :
							echo $row->locid != 0 ? "<a href='".JRoute::_('index.php?view=venueevents&id='.$row->venueslug)."'>".$this->escape($row->venue)."</a>" : '-';
						else :
							echo $row->locid ? $this->escape($row->venue) : '-';
						endif;
						?>
					</td>

				<?php
				endif;

				if ($this->elsettings->showcity == 1) :
				?>

					<td headers="el_city" align="left" valign="top"><?php echo $row->city ? $this->escape($row->city) : '-'; ?></td>

				<?php
				endif;

				if ($this->elsettings->showstate == 1) :
				?>

					<td headers="el_state" align="left" valign="top"><?php echo $row->state ? $this->escape($row->state) : '-'; ?></td>

				<?php
				endif;

				if ($this->elsettings->showcat == 1) :
					if ($this->elsettings->catlinklist == 1) :
					?>

						<td headers="el_category" align="left" valign="top">
							<a href="<?php echo JRoute::_('index.php?view=categoryevents&id='.$row->categoryslug); ?>">
								<?php echo $row->catname ? $this->escape($row->catname) : '-' ; ?>
							</a>
						</td>

					<?php else : ?>

						<td headers="el_category" align="left" valign="top">
							<?php echo $row->catname ? $this->escape($row->catname) : '-'; ?>
						</td>

				<?php
					endif;
				endif;
				?>

			</tr>

  		<?php
		endforeach;
		endif;
		?>

	</tbody>
</table>