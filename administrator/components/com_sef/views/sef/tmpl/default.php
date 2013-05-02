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

<script type="text/javascript">
<!--
function updateWarning()
{
    var res = confirm('<?php echo JText::_('CONFIRM_URL_UPDATE'); ?>');
    if( res ) {
        alert('<?php echo JText::_('Please, DO NOT interrupt the next step, it may take some time to complete!'); ?>');
    }
    return res;
}
function cacheClearWarning()
{
    var res = confirm('<?php echo JText::_('CONFIRM_CACHE_CLEAR'); ?>');
    return res;
}
function purgeWarning()
{
    var res = confirm('<?php echo sprintf(JText::_('CONFIRM_URL_PURGE'), $this->purgeCount); ?>');
    return res;
}
function enableStatus(type)
{
    var form = document.adminForm;
    if( !form ) {
        return;
    }
    
    form.statusType.value = type;
    submitbutton('enableStatus');
}
-->
</script>

<div class="col width-60">
	<div class="icons" id="cpanel">
	    
		<div class="config">
			<h2><?php echo JText::_('JoomSEF Configuration'); ?></h2>
	    	<!-- Global Configuration -->
	    	<div class="icon">
	    		<a href="index.php?option=com_sef&amp;controller=config&amp;task=edit" title="Configure all ARTIO JoomSEF functionality">
	       		<img src="templates/khepri/images/header/icon-48-config.png" alt="" width="48" height="48" border="0"/>
	       		<span><?php echo JText::_('Global Configuration'); ?></span>
	       	</a>
	       </div>
	       <!--  Extensions Management -->
	       <div class="icon">
	    		<a href="index.php?option=com_sef&amp;controller=extension" title="Extensions Management">
	       		<img src="templates/khepri/images/header/icon-48-plugin.png" alt="" width="48" height="48" border="0"/>
	       		<span><?php echo JText::_('Extensions Management'); ?></span>
	       	</a>
	       </div>
	       <!--  Edit .htaccess -->
	      	<div class="icon">
	       	<a href="index.php?option=com_sef&amp;controller=htaccess" title="Edit .htaccess file">
	      			<img src="components/com_sef/assets/images/icon-48-edit.png" alt="" width="48" height="48" border="0"/>
	      			<span><?php echo JText::_('Edit') . ' .htaccess'; ?></span>
	      		</a>
	      	</div>
	       <!--  Updates -->
	      	<div class="icon">
	       	<a href="index.php?option=com_sef&amp;task=showUpgrade" title="Component and plugin online and local upgrades">
	      			<img src="components/com_sef/assets/images/icon-48-update.png" alt="" width="48" height="48" border="0"/>
	      			<span><?php echo JText::_('Check Component and Extension Updates'); ?></span>
	      		</a>
	      	</div>            	
	
	      	<div style="clear: both;"></div>
	    </div>
	                  
		<div class="urls">
	   		<h2><?php echo JText::_('URLs Management'); ?></h2>
	   		<!-- URLs Edit -->
	   		<div class="icon">
	   			<a href="index.php?option=com_sef&amp;controller=sefurls&amp;viewmode=3" title="View/Edit SEF Urls">
	      			<img src="components/com_sef/assets/images/icon-48-url-edit.png" alt="" width="48" height="48" border="0"/>
	      			<span><?php echo JText::_('Manage') . ' ' . JText::_('SEF Urls'); ?></span>
	      		</a>
	   		</div>
	   		<!--  Custom URLs -->
	    	<div class="icon">
				<a href="index.php?option=com_sef&amp;controller=sefurls&amp;viewmode=2" title="View/Edit Custom Redirects">
	       			<img src="components/com_sef/assets/images/icon-48-url-user.png" alt="" width="48" height="48" border="0"/>
	       			<span><?php echo JText::_('Manage Custom URLs'); ?></span>
	      		</a>
	      	</div>	        	
	      	<!--  Update URLs -->
	   		<div class="icon">
	   			<a href="index.php?option=com_sef&amp;task=updateurls" onclick="return updateWarning();" title="Update stored URLs after configuration change">
	         		<img src="components/com_sef/assets/images/icon-48-url-update.png" alt="" width="48" height="48" align="middle" border="0"/>
	      			<span><?php echo JText::_('Update URLs'); ?></span>
	      		</a>
	      	</div>       	
	   		<!-- URLs Purge -->
	    	<div class="icon">
				<a href="index.php?option=com_sef&amp;controller=urls&amp;task=purge&amp;type=0&amp;confirmed=1" onclick="return purgeWarning();" title="Purge auto-generated SEF Urls">
	       			<img src="components/com_sef/assets/images/icon-48-url-delete.png" alt="" width="48" height="48" border="0"/>
	       			<span><?php echo JText::_('Purge') . ' ' . JText::_('SEF Urls'); ?></span>
	      		</a>
	      	</div>
	      	<div style="clear: both;"></div>
	      	
	      	<!--  404 Logs -->
	      	<div class="icon">
	      		<a href="index.php?option=com_sef&amp;controller=sefurls&amp;viewmode=1" title="View/Edit 404 Logs">
	      			<img src="components/com_sef/assets/images/icon-48-404-logs.png" alt="" width="48" height="48" border="0"/>
	      			<span><?php echo JText::_('View') . ' ' . JText::_('404 Logs'); ?></span>
	     		</a>
	     	</div>
	      	<!--  Edit Internal Redirects -->
	      	<div class="icon">
	      		<a href="index.php?option=com_sef&amp;controller=movedurls" title="View/Edit Moved Permanently Redirects">
	      			<img src="components/com_sef/assets/images/icon-48-301-redirects.png" alt="" width="48" height="48" border="0"/>
	      			<span><?php echo JText::_('Manage') . ' ' . JText::_('Internal 301 Redirects'); ?></span>
	      		</a>
	      	</div>
	   		<!--  Clear Cache -->
	   		<div class="icon">
	   			<a href="index.php?option=com_sef&amp;task=cleancache" onclick="return cacheClearWarning();" title="Clear URLs included in JoomSEF cache">
	        		 <img src="components/com_sef/assets/images/icon-48-clear.png" alt="" width="48" height="48" align="middle" border="0"/>
	      			<span><?php echo JText::_('Clear Cache'); ?></span>
	      		</a>
	      	</div>
	   	
	   		<div style="clear: both;"></div>         
	   	</div>
	   	
	   	<div class="help">
	   		<h2><?php echo JText::_('Help and Support'); ?></h2>
	   		<!--  Documentation -->
	   		<div class="icon">
				<a href="index.php?option=com_sef&amp;controller=info&amp;task=doc" title="View ARTIO JoomSEF Documentation">
	        		<img src="components/com_sef/assets/images/icon-48-docs.png" alt="" width="48" height="48" align="middle" border="0"/>
	      			<span><?php echo JText::_('Documentation'); ?></span>
	      		</a>
	      	</div>
	      	<!--  Changelog -->
	      	<div class="icon">
	   			<a href="index.php?option=com_sef&amp;controller=info&amp;task=changelog" title="View ARTIO JoomSEF Changelog">
	        		<img src="components/com_sef/assets/images/icon-48-info.png" alt="" width="48" height="48" align="middle" border="0"/>
	      			<span><?php echo JText::_('Changelog'); ?></span>
	      		</a>
	      	</div>
	      	<!--  Support -->
	      	<div class="icon">
	   			<a href="index.php?option=com_sef&amp;controller=info&amp;task=help" title="Need help with ARTIO JoomSEF?">
	         		<img src="components/com_sef/assets/images/icon-48-help.png" alt="" width="48" height="48" align="middle" border="0"/>
	      			<span><?php echo JText::_('Support'); ?></span>
	      		</a>
	      	</div>
	
	      	<div style="clear: both;"></div>
	   	</div>
	
    </div>
</div>

<div class="col width-40">
	<?php
	$sefInfo = SEFTools::getSEFInfo();
	?>
	
	<fieldset class="adminform">
	<legend>ARTIO JoomSEF</legend>
	<table class="admintable">
	   <tr>
			<td class="key"></td>
			<td>
	      		<a href="http://www.artio.net/en/joomla-extensions/artio-joomsef" target="_blank">
	          		<img src="components/com_sef/assets/images/box.png" align="middle" alt="JoomSEF logo" style="border: none; margin: 8px;" />
	        	</a>
			</td>
		</tr>
	   <tr>
	      <td class="key" width="120"></td>
	      <td><a href="<?php echo $sefInfo['authorUrl']; ?>" target="_blank">ARTIO</a> JoomSEF</td>
	   </tr>	
	   <tr>
	      <td class="key"><?php echo JText::_('Version'); ?>:</td>
	      <td><?php echo $sefInfo['version']; ?></td>
	   </tr>
	   <tr>
	      <td class="key"><?php echo JText::_('Date'); ?>:</td>
	      <td><?php echo $sefInfo['creationDate']; ?></td>
	   </tr>
	   <tr>
	      <td class="key" valign="top"><?php echo JText::_('Copyright'); ?>:</td>
	      <td>&copy; 2006 - <?php echo date('Y', strtotime($sefInfo['creationDate'])); ?>, <?php echo $sefInfo['copyright']; ?></td>
	   </tr>
	   <tr>
	      <td class="key"><?php echo JText::_('Author'); ?>:</td>
	      <td><a href="<?php echo $sefInfo['authorUrl']; ?>" target="_blank"><?php echo $sefInfo['author']; ?></a>,
	      <a href="mailto:<?php echo $sefInfo['authorEmail']; ?>"><?php echo $sefInfo['authorEmail']; ?></a></td>
	   </tr>
	   <tr>
	      <td class="key" valign="top"><?php echo JText::_('Description'); ?>:</td>
	      <td><?php echo $sefInfo['description']; ?></td>
	   </tr>
	   <tr>
	      <td class="key"><?php echo JText::_('License'); ?>:</td>
	      <td><a href="<?php echo $sefInfo['license']; ?>" target="_blank"><?php echo JText::_('Combined license') ?></a></td>
	   </tr>
	   <tr>
	      <td class="key"><?php echo JText::_('Support us'); ?>:</td>
	      <td>
	          <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	          <input name="cmd" type="hidden" value="_s-xclick"></input>
	          <input name="submit" type="image" style="border: none;" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" title="Support JoomSEF"></input>
	          <img src="https://www.paypal.com/en_US/i/scr/pixel.gif" border="0" alt="" width="1" height="1" />
	          <input name="encrypted" type="hidden" value="-----BEGIN PKCS7-----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-----END PKCS7-----"></input>
	          </form>
	      </td>
	   </tr>
	</table>
	</fieldset>

	<?php
	function showStatus($type)
	{
	    static $status;
	    if( !isset($status) ) {
	        $status = SEFTools::getSEOStatus();
	    }
	    
	    if( isset($status[$type]) ) {
	        if( $status[$type] ) {
	            echo '<span style="font-weight: bold; color: green;">' . JText::_('Enabled') . '</span>';
	        }
	        else {
	            echo '<span style="font-weight: bold; color: red;">' . JText::_('Disabled') . '</span>';
	            echo ' <input type="button" onclick="enableStatus(\'' . $type . '\');" value="' . JText::_('Enable') . '" />';
	        }
	    }
	}
	?>
	<fieldset class="adminform">
	   <legend><?php echo JText::_('SEF Status'); ?></legend>
	   <table class="admintable">
	       <tr>
	           <td class="key"><?php echo JText::_('Global SEF URLs'); ?></td>
	           <td><?php showStatus('sef'); ?></td>
	       </tr>
	       <tr>
	           <td class="key"><?php echo JText::_('Apache mod_rewrite'); ?></td>
	           <td><?php showStatus('mod_rewrite'); ?></td>
	       </tr>
	       <tr>
	           <td class="key"><?php echo JText::_('JoomSEF'); ?></td>
	           <td><?php showStatus('joomsef'); ?></td>
	       </tr>
	       <tr>
	           <td class="key"><?php echo JText::_('JoomSEF plugin'); ?></td>
	           <td><?php showStatus('plugin'); ?></td>
	       </tr>
	       <tr>
	           <td class="key"><?php echo JText::_('Creation of new URLs'); ?></td>
	           <td><?php showStatus('newurls'); ?></td>
	       </tr>
	   </table>
	</fieldset>
</div>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="statusType" value="" />
<input type="hidden" name="controller" value="" />
<?php echo JHTML::_('form.token'); ?>
</form>
