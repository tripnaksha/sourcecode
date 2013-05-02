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
<script language="javascript" type="text/javascript">
<!--
	function upgradeExt(extension) {
		var form = document.adminForm;

		form.fromserver.value = '1';
		form.task.value = 'doUpgrade';
		form.ext.value = extension;
		form.submit();
	}

	function extParams(option) {
	    var form = document.adminFormCmp;
	    
	    form.task.value = 'editExt';
	    $('hiddenCid').value = option + '.xml';
	    form.submit();
	}
	
	function getExt(option) {
	    var form = document.adminFormCmp;
	    
	    form.task.value = 'doInstall';
	    form.installtype.value = 'server';
	    form.extension.value = option;
	    form.submit();
	}
	
//-->
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<?php echo $this->loadTemplate('extslist'); ?>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="" />
<input type="hidden" name="redirto" value="controller=extension" />
<input type="hidden" name="fromserver" value="0" />
<input type="hidden" name="ext" value="" />
<?php echo JHTML::_('form.token'); ?>
</form>

<form action="index.php" method="post" name="adminFormCmp" id="adminFormCmp">
<fieldset>
<legend><?php echo JText::_('Components without SEF Extension installed'); ?></legend>

<table class="adminlist">
<thead>
    <tr>
        <th width="50%" class="title">
            <?php echo JText::_('Component'); ?>
        </th>
        <!-- <th width="20%" class="title">
            <?php echo JText::_('Option'); ?>
        </th> -->
        <th class="title">
            <?php echo JText::_('Extension Availability'); ?>
        </th>
        <th class="title">
            <?php echo JText::_('Installation'); ?>
        </th>
        <th class="title">
            <?php echo JText::_('Active Handler'); ?>
        </th>
        <th>
            <?php echo JText::_('Parameters'); ?>
        </th>
    </tr>
</thead>
<tbody>
    <?php
    $k = 0;
    $i = count($this->extensions);
    foreach (array_keys($this->components) as $key) {
        $row =& $this->components[$key];
        ?>
        <tr class="<?php echo 'row'. $k; ?>">
            <td>
                <span class="editlinktip hasTip" title="<?php echo JText::_('Click to open parameters for this component'); ?>">
                <a href="javascript:void(0);" onclick="return extParams('<?php echo $row->option;?>');">
                <?php echo $row->name; ?>
                </a>
                </span>
            </td>
            <!-- <td>
                <?php echo $row->option; ?>
            </td> -->
            <td>
                <?php
                if( is_null($row->extType) ) {
                    echo JText::_('-');
                }
                else {
                    echo JText::_($row->extType);
                }
                ?>
            </td>
            <td>
                <?php
                if( is_null($row->extType) ) {
                    echo JText::_('-');
                }
                else {
                    if( ($row->extType == 'Free') || ($row->params->get('downloadId', '') != '') ) {
                        $fn = 'getExt(\'' . $row->option . '\');';
                    }
                    else {
                        $fn = 'window.open(\'' . $row->extLink . '\');';
                    }
                    ?>
                    <input type="button" class="button hasTip" value="<?php echo JText::_('Get Extension'); ?>" onclick="<?php echo $fn; ?>" title="<?php echo JText::_('Click to get the extension for this component from ARTIO server'); ?>" />
                    <?php
                }
                ?>
            </td>
            <td>
                <?php echo $row->handler; ?>
            </td>
            <td align="center">
                <span class="editlinktip hasTip" title="<?php echo JText::_('Click to open parameters for this component'); ?>">
                <a href="javascript:void(0);" onclick="return extParams('<?php echo $row->option;?>');">
                <img src="<?php echo JURI::root(); ?>administrator/templates/khepri/images/menu/icon-16-config.png" border="0" alt="Open parameters" />
                </a>
                </span>
            </td>
        </tr>
        <?php
        $k = 1 - $k;
        $i++;
    }
    ?>
</tbody>
</table>

</fieldset>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="" />
<input type="hidden" name="redirto" value="controller=extension" />
<input type="hidden" name="fromserver" value="0" />
<input type="hidden" name="cid[]" id="hiddenCid" value="" />
<input type="hidden" name="installtype" value="" />
<input type="hidden" name="extension" value="" />
<?php echo JHTML::_('form.token'); ?>
</form>
