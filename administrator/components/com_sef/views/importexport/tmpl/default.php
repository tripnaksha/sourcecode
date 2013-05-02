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
	function submitimport() {
		var form = document.adminForm;

		form.task.value = 'import';
		form.submit();
	}

	function submitdbace() {
		var form = document.adminForm;

		form.task.value = 'importdbace';
		form.submit();
	}

	function submitdbsh() {
		var form = document.adminForm;

		form.task.value = 'importdbsh';
		form.submit();
	}
	
//-->
</script>

<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm">
<fieldset class="adminform">
<legend><?php echo JText::_('Import URLs From File'); ?></legend>
<table class="adminform">
<tr>
    <th colspan="2">
        <?php echo JText::_( 'You can select exported file from JoomSEF, sh404SEF or AceSEF - the format will be recognized automatically and imported correctly.' ); ?>
        <br />
        <?php echo JText::_( 'When importing from sh404SEF, please, first import the URLs and then the meta tags.' ); ?>
    </th>
</tr>
<tr>
    <td width="120">
        <label for="install_package"><?php echo JText::_( 'Import File' ); ?>:</label>
    </td>
    <td>
        <input class="input_box" id="importfile" name="importfile" type="file" size="57" />
        <input class="button" type="button" value="<?php echo JText::_( 'Import URLs' ); ?>" onclick="submitimport()" />
    </td>
</tr>
</table>
</fieldset>

<?php
if( $this->aceSefPresent || $this->shSefPresent )
{
?>

<fieldset class="adminform">
<legend><?php echo JText::_('Import URLs From Database'); ?></legend>
<table class="adminform">
<tr>
    <th>
        <?php echo JText::_( 'JoomSEF has detected former installation of another SEF component. You can automatically import SEF URLs from it using the following button.' ); ?>
    </th>
</tr>
<tr>
    <td>
    <?php
    if( $this->aceSefPresent )
    {
        ?>
        <input class="button" type="button" value="<?php echo JText::_( 'Import URLs from AceSEF table' ); ?>" onclick="submitdbace()" />
        <?php
    }
    ?>
    <?php
    if( $this->shSefPresent )
    {
        ?>
        <input class="button" type="button" value="<?php echo JText::_( 'Import URLs from sh404SEF table' ); ?>" onclick="submitdbsh()" />
        <?php
    }
    ?>
    </td>
</tr>
</table>
</fieldset>

<?php
}
?>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="sefurls" />
</form>
