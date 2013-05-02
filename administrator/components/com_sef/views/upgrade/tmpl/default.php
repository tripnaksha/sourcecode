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

$sefConfig =& SEFConfig::getConfig();

if( (trim($sefConfig->artioDownloadId) != '') && (is_null($this->regInfo) || ($this->regInfo->code != 10)) ) {
    $needConfirm = true;
}
else {
    $needConfirm = false;
}

if( (trim($sefConfig->artioDownloadId) == '') || is_null($this->regInfo) || ($this->regInfo->code != 10) ) {
    $downloadPaid = false;
}
else {
    $downloadPaid = true;
}
?>

<script language="javascript" type="text/javascript">
<!--
	function submitbutton3(pressbutton) {
		var form = document.adminForm;

		var sendOk = true;
		
		<?php
		if( $needConfirm ) {
		    ?>
		    sendOk = confirm('<?php echo JText::_('You will obtain the non-paid version of JoomSEF. Are you sure you want to use the automatic upgrade from server?'); ?>');
		    <?php
		}
		?>
		if( sendOk ) {
    		form.fromserver.value = '1';
    		form.submit();
		}
	}
	
	function submitbuttonext(extension) {
		var form = document.adminForm;

		form.fromserver.value = '1';
		form.ext.value = extension;
		form.submit();	    
	}

//-->
</script>

<fieldset class="adminform">
<legend><?php echo JText::_('JoomSEF'); ?></legend>
<table class="adminform">
<tr>
    <th colspan="2"><?php echo JText::_('Version Info'); ?></th>
</tr>
<tr>
    <td width="20%"><?php echo JText::_('Installed version').':'; ?></td>
    <td><?php echo $this->oldVer; ?></td>
</tr>
<tr>
    <td><?php echo JText::_('Newest version').':'; ?></td>
    <td><?php echo $this->newVer; ?></td>
</tr>
</table>

<?php
if( trim($sefConfig->artioDownloadId) != '' ) {
    ?>
    <table class="adminform">
    <tr>
        <th colspan="2"><?php echo JText::_('Registration Info'); ?></th>
    </tr>
    <?php
    if( is_null($this->regInfo) ) {
        ?>
        <tr>
            <td colspan="2"><?php echo JText::_('Could not retrieve registration information.'); ?></td>
        </tr>
        <?php
    }
    else if( $this->regInfo->code == 90 ) {
        ?>
        <tr>
            <td colspan="2"><?php echo JText::_('Download ID was not found in our database.'); ?></td>
        </tr>
        <?php
    }
    else {
        $regTo = $this->regInfo->name;
        if( !empty($this->regInfo->company) ) {
            $regTo .= ', ' . $this->regInfo->company;
        }
        ?>
        <tr>
            <td width="20%""><?php echo JText::_('Registered to'); ?>:</td>
            <td><?php echo $regTo; ?></td>
        </tr>
        <?php
        if ($this->regInfo->code == 10 || $this->regInfo->code == 30) {
            $dateText = JText::_('Free upgrades available until');
        }
        elseif ($this->regInfo->code == 20) {
            $dateText = JText::_('Free upgrades expired');
        }
        ?>
        <tr>
            <td><?php echo $dateText; ?>:</td>
            <td><?php echo $this->regInfo->date; ?></td>
        </tr>
        <?php
    }
    ?>
    </table>
    <?php
} // Download ID set
?>

<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm">
<?php
$available = false;
if ((strnatcasecmp($this->newVer, $this->oldVer) > 0) ||
(strnatcasecmp($this->newVer, substr($this->oldVer, 0, strpos($this->oldVer, '-'))) == 0) ||
($this->newVer == "?.?.?") )
{
    $available = true;

    if (!$this->isPaidVersion && $downloadPaid) {
        $btnText = JText::_('Online Upgrade to Paid Version');
    } else {
        $btnText = JText::_('Upgrade from ARTIO Server');
    }
}
elseif (($this->newVer == $this->oldVer)) {
    $available = true;
    if (!$this->isPaidVersion && $downloadPaid) {
    	$btnText = JText::_('Online Migrate to Paid Version');
    } else {
    	$btnText = JText::_('Reinstall from ARTIO Server');
    }
}

if( $available )
{
?>
    <table class="adminform">
        <tr>
            <th><?php echo $btnText; ?></th>
        </tr>
        <tr>
            <td>
                   <?php
                   if( $this->newVer == '?.?.?' ) {
                       echo JText::_('Server not available.');
                   }
                   else
                   {
                       ?>
                       <input class="button" type="button" value="<?php echo $btnText; ?>" onclick="submitbutton3()" />
                       <?php
                   }
                   ?>
            </td>
        </tr>
    </table>
<?php
} else {
?>
    <table class="adminform">
        <tr>
            <th><?php echo JText::_('Your JoomSEF is up to date.'); ?></th>
        </tr>
    </table>
<?php } ?>

<table class="adminform">
<tr>
    <th colspan="2"><?php echo JText::_( 'Upload Package File' ); ?></th>
</tr>
<tr>
    <td width="120">
        <label for="install_package"><?php echo JText::_( 'Package File' ); ?>:</label>
    </td>
    <td>
        <input class="input_box" id="install_package" name="install_package" type="file" size="57" />
        <input class="button" type="submit" value="<?php echo JText::_( 'Upload File' ); ?> &amp; <?php echo JText::_( 'Install' ); ?>" />
    </td>
</tr>
</table>
</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('SEF Extensions'); ?></legend>
<table class="adminform">
    <tr>
        <th><?php echo JText::_('SEF Extension'); ?></th>
        <th><?php echo JText::_('Installed version'); ?></th>
        <th><?php echo JText::_('Newest version'); ?></th>
        <th><?php echo JText::_('Upgrade'); ?></th>
    </tr>
    <?php
    $k = 0;
    if( (count($this->extensions) > 0) ) {
        foreach(array_keys($this->extensions) as $i) {
            $row = &$this->extensions[$i];
        ?>
        <tr class="<?php echo 'row'.$k; ?>">
            <td><?php echo $row->name; ?></td>
            <td><?php echo $row->old; ?></td>
            <td><?php echo $row->new; ?></td>
            <td>
            <?php
            if( (strnatcasecmp($row->new, $row->old) > 0) ||
                (strnatcasecmp($row->new, substr($row->old, 0, strpos($row->old, '-'))) == 0) )
            {
                ?>
                <input class="button" type="button" value="<?php echo JText::_('Upgrade'); ?>" onclick="submitbuttonext('<?php echo $i; ?>')" />
                <?php
            } else {
                echo JText::_('Not available');
            }
            ?>
            </td>
        </tr>
        <?php
        $k = 1 - $k;
        }
    }
    ?>
</table>
</fieldset>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="doUpgrade" />
<input type="hidden" name="controller" value="" />
<input type="hidden" name="fromserver" value="0" />
<input type="hidden" name="ext" value="" />
<?php echo JHTML::_('form.token'); ?>
</form>
