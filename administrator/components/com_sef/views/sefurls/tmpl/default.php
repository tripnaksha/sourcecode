<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">

<script type="text/javascript">
<!--
function useRE(el1, el2)
{
    if( !el1 || !el2 ) {
        return;
    }
    
    if( el1.checked && el2.value.substr(0, 4) != 'reg:' ) {
        el2.value = 'reg:' + el2.value;
    }
    else if( !el1.checked && el2.value.substr(0,4) == 'reg:' ) {
        el2.value = el2.value.substr(4);
    }
}
-->
</script>

<table>
    <tr>
        <td width="100%" valign="bottom">
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('ViewMode') . ':';
            ?>
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Sort by') . ':';
            ?>
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Hits') . ':';
            ?>
        </td>
        <?php if( $this->viewmode != 1 ) { ?>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('ItemID') . ':';
            ?>
        </td>
        <?php } ?>
        <td nowrap="nowrap">
            Use RE&nbsp;<input type="checkbox" onclick="useRE(this, document.adminForm.filterSEF);" />
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo (($this->viewmode == 1) ? JText::_('Filter Urls') : JText::_('Filter SEF Urls')) . ':';
            ?>
        </td>
        <?php if( $this->viewmode != 1 ) { ?>
        <td nowrap="nowrap">
            Use RE&nbsp;<input type="checkbox" onclick="useRE(this, document.adminForm.filterReal);" />
        </td>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Filter Real Urls') . ':';
            ?>
        </td>
        <?php } ?>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Component') . ':';
            ?>
        </td>
        <?php if( SEFTools::JoomFishInstalled() ) { ?>
        <td nowrap="nowrap" align="right">
            <?php
            echo JText::_('Language') . ':';
            ?>
        </td>
        <?php } ?>
    </tr>
    <tr>
        <td></td>
        <td>
            <?php echo $this->lists['viewmode']; ?>
        </td>
        <td>
            <?php echo $this->lists['sortby']; ?>
        </td>
        <td nowrap="nowrap">
            <?php echo $this->lists['hitsCmp'] . $this->lists['hitsVal']; ?>
        </td>
        <?php if ($this->viewmode != 1) { ?>
        <td>
            <?php echo $this->lists['itemid']; ?>
        </td>
        <?php } ?>
        <td colspan="2">
            <?php echo $this->lists['filterSEF']; ?>
        </td>
        <?php if ($this->viewmode != 1) { ?>
        <td colspan="2">
            <?php echo $this->lists['filterReal']; ?>
        </td>
        <?php } ?>
        <td>
            <?php echo $this->lists['comList']; ?>
        </td>
        <?php if (SEFTools::JoomFishInstalled()) { ?>
        <td>
            <?php echo $this->lists['filterLang']; ?>
        </td>
        <?php } ?>
    </tr>
</table>

<table class="adminlist">
<thead>
    <tr>
        <th width="5">
            <?php echo JText::_('Num'); ?>
        </th>
        <th width="20">
            <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
        </th>
        <th class="title" width="40px">
            <?php echo JText::_('Hits'); ?>
        </th>
        <th class="title">
            <?php echo (($this->viewmode == 1) ? JText::_('Date Added') : JText::_('SEF Url')); ?>
        </th>
        <th class="title">
            <?php echo (($this->viewmode == 1) ? JText::_('Url') : JText::_('Real Url')); ?>
        </th>
		<?php if ($this->trace) : ?>
        <th class="title" width="40px">
	        <?php echo JText::_('Trace'); ?>
        </th>
		<?php endif; ?>        
		<?php if ($this->viewmode != 1) : ?>
		<th class="title" width="50px">
        	<?php echo JText::_('Active'); ?>
        </th>
		<?php endif; ?>
    </tr>
</thead>
<?php
	$colspan = 5;
	if ($this->viewmode != 1) $colspan++;
	if ($this->trace )$colspan++;
?>
<tfoot>
    <tr>
        <td colspan="<?php echo $colspan; ?>">
            <?php echo $this->pagination->getListFooter(); ?>
        </td>
    </tr>
</tfoot>
<tbody>
    <?php
    $k = 0;
    //for ($i=0, $n=count( $rows ); $i < $n; $i++) {
    foreach (array_keys($this->items) as $i) {
        $row = &$this->items[$i];
        ?>
        <tr class="<?php echo 'row'. $k; ?>">
            <td align="center">
                <?php echo $this->pagination->getRowOffset($i); ?>
            </td>
            <td>
                <?php echo JHTML::_('grid.id', $i, $row->id ); ?>
            </td>
            <td>
                <?php echo $row->cpt; ?>
            </td>
            <td style="text-align:left;">
                <?php if ($this->viewmode == 1 ) {
                    echo $row->dateadd;
                } else { ?>
                    <a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>', 'edit')">
                    <?php echo $row->sefurl;?>
                    </a>
                <?php } ?>
            </td>
            <td style="text-align:left;">
                <?php if ($this->viewmode == 1 ) { ?>
                    <a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>', 'edit')">
                    <?php echo $row->sefurl; ?>
                    </a>
                <?php } else {
                    echo htmlentities($row->origurl . ($row->Itemid == '' ? '' : (strpos($row->origurl, '?') ? '&' : '?') . 'Itemid='.$row->Itemid ) );
                } ?>
            </td>
            <?php if ($this->trace) : ?>
            <td style="text-align: center;">
            	<?php echo JHTML::_('tooltip', nl2br($row->trace), JText::_('Trace Information'));?></td>
            </td>
            <?php endif; ?>
            <?php
            if( $this->viewmode != 1 ) {
                ?>
                <td style="text-align: center;">
                    <?php
                    if( $row->priority == 0 ) {
                        ?>
                        <span class="hasTip" title="<?php echo JText::_('This is the active link for SEF URL'); ?>">
                        <img src="images/tick.png" border="0" alt="Active" />
                        </span>
                        <?php
                    }
                    else {
                        $img = ($row->priority == 100 ? 'publish_r.png' : 'publish_g.png');
                        ?>
                        <span class="editlinktip hasTip" title="<?php echo JText::_('Make this link active for SEF URL'); ?>">
                        <a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i; ?>', 'setActive')">
                        <img src="images/<?php echo $img; ?>" border="0" alt="Not active" />
                        </a>
                        </span>
                        <?php
                    }
                    ?>
                </td>
                <?php
            }
            ?>
        </tr>
        <?php
        $k = 1 - $k;
    }
    ?>
</tbody>
</table>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="sefurls" />
</form>
