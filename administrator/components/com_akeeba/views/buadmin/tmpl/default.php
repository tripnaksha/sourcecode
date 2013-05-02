<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: default.php 232 2010-08-26 07:51:12Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

JHTML::_('behavior.mootools');

// Filesize formatting function by eregon at msn dot com
// Published at: http://www.php.net/manual/en/function.number-format.php
function format_filesize($number, $decimals = 2, $force_unit = false, $dec_char = '.', $thousands_char = '')
{
	if($number <= 0) return '-';

	$units = array('b', 'Kb', 'Mb', 'Gb', 'Tb');
	if($force_unit === false)
	$unit = floor(log($number, 2) / 10);
	else
	$unit = $force_unit;
	if($unit == 0)
	$decimals = 0;
	return number_format($number / pow(1024, $unit), $decimals, $dec_char, $thousands_char).' '.$units[$unit];
}

// Load a mapping of backup types to textual representation
$scripting = AEUtilScripting::loadScripting();
$backup_types = array();
foreach($scripting['scripts'] as $key => $data)
{
	$backup_types[$key] = JText::_($data['text']);
}

?>
<div id="jpcontainer">
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" id="option" value="com_akeeba" />
	<input type="hidden" name="view" id="view" value="buadmin" />
	<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
	<input type="hidden" name="task" id="task" value="" />
<table class="adminlist">
	<thead>
		<tr>
			<th width="20"><input type="checkbox" name="toggle" value=""
				onclick="checkAll(<?php echo count( $this->list ) + 1; ?>);" /></th>

			<th><?php echo JText::_('STATS_LABEL_DESCRIPTION'); ?></th>
			<th><?php echo JText::_('STATS_LABEL_START'); ?></th>
			<th><?php echo JText::_('STATS_LABEL_DURATION'); ?></th>
			<th><?php echo JText::_('STATS_LABEL_STATUS'); ?></th>
			<th><?php echo JText::_('STATS_LABEL_ORIGIN'); ?></th>
			<th><?php echo JText::_('STATS_LABEL_TYPE'); ?></th>
			<th><?php echo JText::_('STATS_LABEL_PROFILEID'); ?></th>
			<th><?php echo JText::_('STATS_LABEL_SIZE'); ?></th>
			<th><?php echo JText::_('STATS_LABEL_ARCHIVE'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
		</tr>
	</tfoot>
	<tbody>
	<?php if(!empty($this->list)): ?>
	<?php $id = 1; $i = 0;?>
	<?php foreach($this->list as $record): ?>
	<?php
	$id = 1 - $id;
	$check = JHTML::_('grid.id', ++$i, $record['id']);
	switch($record['meta'])
	{
		case 'ok':
			$status = JText::_('STATS_LABEL_STATUS_OK');
			break;

		case 'obsolete':
			$status = JText::_('STATS_LABEL_STATUS_OBSOLETE');
			break;

		case 'fail':
			$status = JText::_('STATS_LABEL_STATUS_FAIL');
			break;

		case 'pending':
			$status = JText::_('STATS_LABEL_STATUS_PENDING');
			break;
	}

	$origin_lbl = 'STATS_LABEL_ORIGIN_'.strtoupper($record['origin']);
	$origin = JText::_($origin_lbl);
	/*
	if($origin == $origin_lbl)
	{
		$origin = '&ndash;';
	}
	/**/

	if( array_key_exists($record['type'], $backup_types) )
	{
		$type = $backup_types[$record['type']];
	}
	else
	{
		$type = '&ndash;';
	}

	jimport('joomla.utilities.date');
	$startTime = new JDate($record['backupstart']);
	$endTime = new JDate($record['backupend']);

	$duration = $endTime->toUnix() - $startTime->toUnix();
	if($duration > 0)
	{
		$seconds = $duration % 60;
		$duration = $duration - $seconds;

		$minutes = ($duration % 3600) / 60;
		$duration = $duration - $minutes * 60;

		$hours = $duration / 3600;
		$duration = sprintf('%02d',$hours).':'.sprintf('%02d',$minutes).':'.sprintf('%02d',$seconds);
	}
	else
	{
		$duration = '-';
	}
	$user =& JFactory::getUser();
	$userTZ = $user->getParam('timezone',0);
	$startTime->setOffset($userTZ);

	if($record['meta'] == 'ok')
	{
		// Get the download links for downloads for completed, valid backups
		$filename_col = '';
		$thisPart = '';
		$thisID = urlencode($record['id']);
		if($record['multipart'] == 0)
		{
			// Single part file -- Create a simple link
			$filename_col = "<a href=\"javascript:confirmDownload('$thisID', '$thisPart');\">".$record['archivename']."</a>";
		}
		else
		{
			$filename_col = $record['archivename']."<br/>";
			for($count = 0; $count < $record['multipart']; $count++)
			{
				$thisPart = urlencode($count);
				$label = JText::sprintf('STATS_LABEL_PART', $count);
				$filename_col .= ($count > 0) ? ' &bull; ' : '';
				$filename_col .= "<a href=\"javascript:confirmDownload('$thisID', '$thisPart');\">$label</a>";
			}
		}
	}
	else
	{
		// If the backup is not complete, just show dashes
		$filename_col = '&mdash;';
	}

	// Link for Show Comments lightbox
	$info_link = "";
	if(!empty($record['comment']))
	{
		$info_link = JHTML::_('tooltip', strip_tags($record['comment']) ) . '&ensp;';
	}

	$edit_link = JURI::base() . 'index.php?option=com_akeeba&view=buadmin&task=showcomment&id='.$record['id'];

	if(empty($record['description'])) $record['description'] = JText::_('STATS_LABEL_NODESCRIPTION');
	?>
		<tr class="row<?php echo $id; ?>">
			<td><?php echo $check; ?></td>
			<td>
				<?php echo $info_link ?>
				<a href="<?php echo $edit_link; ?>"><?php echo $record['description'] ?></a>
			</td>
			<td><?php echo $startTime->toFormat(JText::_('DATE_FORMAT_LC4')); ?></td>
			<td><?php echo $duration; ?></td>
			<td class="bufa-<?php echo $record['meta']; ?>"><?php echo $status ?></td>
			<td><?php echo $origin ?></td>
			<td><?php echo $type ?></td>
			<td><?php echo $record['profile_id'] ?></td>
			<td><?php echo ($record['meta'] == 'ok') ? format_filesize($record['size']) : '-' ?></td>
			<td><?php echo $filename_col; ?></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
</form>
</div>
