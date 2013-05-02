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
?>

	<script language="javascript">
	<!--
	function submitbutton(pressbutton)
	{
	    var form = document.adminForm;
	    if (pressbutton == 'cancel') {
	        submitform( pressbutton );
	        return;
	    }
	    // do field validation
	    if (form.customurl.checked == true ) {
	        form.dateadd.value = "<?php echo date('Y-m-d'); ?>"
	    } else {
	        form.dateadd.value = "0000-00-00"
	    }
	    if (form.origurl.value == "") {
	        alert( "<?php echo JText::_('You must provide a URL for the redirection.'); ?>" );
	    } else {
	        if (form.origurl.value.match(/^index.php/)) {
	            <?php if( $sefConfig->useMoved ) { ?>
	            // Ask to save the changed url to Moved Permanently table
	            if( (form.sefurl.value != form.unchanged.value) && (form.id.value != "0" && form.id.value != "") ) {
	                <?php if( $sefConfig->useMovedAsk ) { ?>
	                if( !confirm("<?php echo JText::_('CONFIRM_AUTO_301'); ?>") ) {
	                    form.unchanged.value = "";
	                }
	                <?php } ?>
	            } else {
	                form.unchanged.value = "";
	            }
	            <?php } else { echo 'form.unchanged.value="";'; } ?>
	            submitform( pressbutton );
	        } else {
	            alert( "<?php echo JText::_('The Old Non-SEF Url must begin with index.php'); ?>" );
	        }
	    }
	}
	//-->
	</script>
	<form action="index2.php" method="post" name="adminForm" id="adminForm">
	<table class="adminform">
	    <tr><th colspan="2"><?php echo JText::_('URL'); ?></th></tr>
		<tr>
			<td><?php echo JText::_('New SEF URL'); ?></td>
			<td><input class="inputbox" type="text" size="100" name="sefurl" value="<?php echo $this->sef->sefurl; ?>">
			<?php echo JHTML::_('tooltip', JText::_('TT_SEF_URL'), JText::_('New SEF URL')); ?>
			</td>
		</tr>
		<tr>
			<td><?php echo JText::_('Old Non-SEF Url');?></td>
			<td align="left"><input class="inputbox" type="text" size="100" name="origurl" value="<?php echo $this->sef->origurl; ?>">
			<?php echo JHTML::_('tooltip', JText::_('TT_ORIG_URL'), JText::_('Old Non-SEF Url'));?>
			</td>
		</tr>
		<tr>
			<td><?php echo JText::_('Itemid');?></td>
			<td align="left"><input class="inputbox" type="text" size="30" name="Itemid" value="<?php echo $this->sef->Itemid; ?>">
			<?php echo JHTML::_('tooltip', JText::_('TT_ITEMID'), JText::_('Itemid'));?>
			</td>
		</tr>		
		<tr>
  		<td></td>
  		<td>
  			<?php echo JText::_('Save as Custom Redirect'); ?><input type="checkbox" name="customurl" value="0" checked="checked" />
  		</td>
		</tr>
		<tr><th colspan="2"><?php echo JText::_('Meta Tags (optional)'); ?> <?php echo  JHTML::_('tooltip', JText::_('INFO_JOOMSEF_PLUGIN'), JText::_('JoomSEF Plugin Notice')); ?></th></tr>
		<tr>
		  <td><?php echo JText::_('Title'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="255" name="metatitle" value="<?php echo htmlspecialchars($this->sef->metatitle); ?>">
		  </td>
		</tr>
		<tr>
		  <td><?php echo JText::_('Meta Descrition'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="255" name="metadesc" value="<?php echo htmlspecialchars($this->sef->metadesc); ?>">
		  </td>
		</tr>
		<tr>
		  <td><?php echo JText::_('Meta Keywords'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="255" name="metakey" value="<?php echo htmlspecialchars($this->sef->metakey); ?>">
		  </td>
		</tr>
		<tr>
		  <td><?php echo JText::_('Meta Content-Language'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="30" name="metalang" value="<?php echo htmlspecialchars($this->sef->metalang); ?>">
		  </td>
		</tr>
		<tr>
		  <td><?php echo JText::_('Meta Robots'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="30" name="metarobots" value="<?php echo htmlspecialchars($this->sef->metarobots); ?>">
		  </td>
		</tr>
		<tr>
		  <td><?php echo JText::_('Meta Googlebot'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="30" name="metagoogle" value="<?php echo htmlspecialchars($this->sef->metagoogle); ?>">
		  </td>
		</tr>
		<tr>
		  <td><?php echo JText::_('Canonical Link'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="255" name="canonicallink" value="<?php echo htmlspecialchars($this->sef->canonicallink); ?>">
		  </td>
		</tr>

		<?php $config =& SEFConfig::getConfig(); ?>
		<?php if ($config->trace) : ?>		
		<tr><th colspan="2"><?php echo JText::_('URL Source Tracing'); ?></th></tr>
		<tr>
		  <td valign="top"><?php echo JText::_('Trace Information'); ?>:</td>
		  <td align="left"><?php echo nl2br(htmlspecialchars($this->sef->trace)); ?>
		  </td>
		</tr>
		<?php endif; ?>
	</table>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="sefurls" />
<input type="hidden" name="unchanged" value="<?php echo $this->sef->sefurl; ?>" />
<input type="hidden" name="dateadd" value="<?php echo $this->sef->dateadd; ?>" />
<input type="hidden" name="id" value="<?php echo $this->sef->id; ?>" />
</form>
