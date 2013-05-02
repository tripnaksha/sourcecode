<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="POST" name="adminForm">
    <div id="editcell">
        <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
            <thead>
                <tr>
                    <th width="5">#</th>
                    <th width="20"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $this->items ); ?>);" /></th>
                    <th width="100" align="left" nowrap="nowrap"><?php echo JText::_( 'Name' ); ?></th>
                    <th width="25" align="center" nowrap="nowrap"><?php echo JText::_( 'Button' ); ?></th>
                    <th width="50" align="left" nowrap="nowrap"><?php echo JText::_( 'Type' ); ?></th>
                    <th width="200" align="left" nowrap="nowrap"><?php echo JText::_( 'Feed url' ); ?></th>
                    <th width="5%" nowrap="nowrap"><?php echo JText::_( 'Published' ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="9">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <?php
            $k = 0;
            for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
                $row = &$this->items[$i];

                $checked 	= JHTML::_('grid.id',  $i, $row->id );
                $published 	= JHTML::_('grid.published', $row, $i );

                $feedurl = JURI::root() . JRoute::_( "index.php?option=com_bca-rss-syndicator&feed_id=".$row->id);
                ?>
            <tr class="<?php echo "row$k"; ?>">
                <td><?php echo $this->pagination->getRowOffset( $i ); ?></td>
                <td>
                    <?php echo $checked ;?>
                </td>
                <td>

                    <span class="editlinktip hasTip" title="<?php echo JText::_( 'Edit feed' );?>::<?php echo $this->escape($row->feed_name); ?>">
                        <a href="#" onclick="return listItemTask('cb<?php echo $i; ?>','edit')">
                    <?php echo $this->escape($row->feed_name); ?></a></span>

                </td>
                <td><img src="<?php if($row->feed_button != "") {echo (JURI::root() . "components/com_bca-rss-syndicator/assets/images/buttons/".$row->feed_button);} ?>"></td>
                <td><?php echo $row->feed_type; ?></td>
                <td><a href="<?php echo $feedurl;?>" target="_blank"><?php echo $feedurl;?></a></td>
                <td><?php echo $published ;?></td>
            <?php		$k = 1 - $k; ?>		</tr>
            <?php	}
        ?>

        </table>
    </div>
    <input type="hidden" name="option" value="com_bca-rss-syndicator" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="feed" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>