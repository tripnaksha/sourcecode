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

<fieldset class="adminform">
<legend><?php echo JText::_('301 Redirects'); ?></legend>
<table class="adminlist">
<thead>
    <tr>
        <th width="5">
            <?php echo JText::_('Num'); ?>
        </th>
        <th width="20">
            <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
        </th>
        <th class="title">
            <?php echo JText::_('Redirect from'); ?>
        </th>
        <th class="title">
            <?php echo JText::_('Redirect to'); ?>
        </th>
    </tr>
</thead>
<tbody>
    <?php
    $k = 0;
    $keys = array_keys($this->items);
    
    for( $i = 0, $n = count($keys); $i < $n; $i++ ) {
        $id = $i + 1;
        $row =& $this->items[$keys[$i]];
        ?>
        <tr class="<?php echo 'row'. $k; ?>">
            <td align="center">
                <?php echo $i+1; ?>
            </td>
            <td>
                <?php echo JHTML::_('grid.id', $i, $id ); ?>
            </td>
            <td>
                <a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>', 'edit')">
                <?php echo $row->from; ?>
                </a>
            </td>
            <td>
                <a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>', 'edit')">
                <?php echo $row->to; ?>
                </a>
            </td>
        </tr>
        <?php
        $k = 1 - $k;
    }
    ?>
</tbody>
</table>
</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('RewriteBase'); ?></legend>
<table class="adminform">
    <tr>
        <th colspan="2"><?php echo JText::_('INFO_REWRITE_BASE'); ?></th>
    </tr>
    <tr>
        <td width="100"><?php echo JText::_('Enabled'); ?>:</td>
        <td><?php echo $this->lists['baseEnable']; ?></td>
    </tr>
    <tr>
        <td><?php echo JText::_('Value'); ?>:</td>
        <td><?php echo $this->lists['baseValue']; ?></td>
    </tr>
</table>
</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('FollowSymLinks'); ?></legend>
<table class="adminform">
    <tr>
        <th colspan="2"><?php echo JText::_('INFO_FOLLOW_SYMLINKS'); ?></th>
    </tr>
    <tr>
        <td width="100"><?php echo JText::_('Enabled'); ?>:</td>
        <td><?php echo $this->lists['symLinksEnable']; ?></td>
    </tr>
</table>
</fieldset>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="htaccess" />
</form>
