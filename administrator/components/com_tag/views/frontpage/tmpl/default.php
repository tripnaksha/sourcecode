<?php defined('_JEXEC') or die('Restricted access');
$document = & JFactory::getDocument();
$document->addScript( JURI::root(true).'/media/system/js/modal.js' );
$document->addStyleSheet(JURI::root(true).'/media/system/css/modal.css' );
//$document->addStyleSheet(JURI::root(true).'/administrator/components/com_tag/css/tag.css' );
JHTML::_('behavior.modal', 'a.modal');

require_once JPATH_COMPONENT_SITE.DS.'helper'.DS.'helper.php';
?>

<table class="adminform" width="100%">
	<tr>
		<td width="52%" valign="top">
		<div id="tagpanel">

		<div style="float: left;">
		<div class="icon"><a href="index.php?option=com_tag&controller=tag"
			title="<?php echo JText::_('TAG　MANAGER');?>"> <img
			src="components/com_tag/images/tag.png"
			alt="<?php echo JText::_('TAG　MANAGER');?>" /> <span><?php echo JText::_('TAG　MANAGER');?></span></a></div>
		</div>
		<div style="float: left;">
		<div class="icon"><a href="index.php?option=com_tag&controller=term"
			title="<?php echo JText::_('TERM MANAGER');?>"> <img
			src="components/com_tag/images/term.png"
			alt="<?php echo JText::_('TERM MANAGER');?>" /> <span><?php echo JText::_('TERM MANAGER');?></span></a></div>
		</div>

		<div style="float: left;">
		<div class="icon"><a class="modal"
			rel="{handler: 'iframe', size: {x: 600, y: 600}}"
			href="index.php?option=com_config&controller=component&component=com_tag&path="
			title="<?php echo JText::_('CONFIGURATION FOR JOOMLA TAGS');?>"> <img
			src="components/com_tag/images/config.png"
			alt="<?php echo JText::_('CONFIGURATION');?>" /> <span><?php echo JText::_('CONFIGURATION');?></span></a></div>
		</div>

		<div style="float: left;">
		<div class="icon"><a href="index.php?option=com_tag&controller=css"
			title="<?php echo JText::_('TEMPLATE MANAGER');?>"> <img
			src="components/com_tag/images/template.png"
			alt="<?php echo JText::_('TEMPLATE MANAGER');?>" /> <span><?php echo JText::_('TEMPLATE MANAGER');?></span></a></div>
		</div>


		<div style="float: left;">
		<div class="icon"><a href="index.php?option=com_tag&controller=import"
			title="<?php echo JText::_('IMPORT TAGS FROM OTHER COMPONENTS');?>">
		<img src="components/com_tag/images/import.png" /> <span><?php echo JText::_('IMPORT TAGS');?></span></a></div>
		</div>
		<div style="float: left;">
		<div class="icon"><a href="http://www.joomlatags.org" target="_blank" 
			title="<?php echo JText::_('JOOMLA TAGS HOME PAGE');?>"> <img
			src="components/com_tag/images/frontpage.png" /> <span><?php echo JText::_('HOME PAGE');?></span></a></div>
		</div>

		</div>
		<!-- end of div tagpanel --></td>
		<td width="48%" valign="top" align="left">
		<table border="1" width="100%" id="tagversion">
			<tr>
				<th colspan="2"><a
					href="http://extensions.joomla.org/extensions/search-&-indexing/tags-&-clouds/7718/details"
					target="_blank"> <font color="blue">JOomla Tags </font></a><?php echo JText::_('JOOMLA TAGS SLOGAN');?>
				</th>

			</tr>

			<tr>
				<td width="240px"><?php echo JText::_('VERSION');?></td>
				<td width="72%"><a href="http://www.joomlatags.org" target="_blank">JOomla
				Tags</a> v<?php echo(JoomlaTagsHelper::getComponentVersion());?></td>

			</tr>

			<tr>
				<td><?php echo JText::_('DONATE');?></td>
				<td>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input name="cmd" value="_donations" type="hidden"> <input
					name="business" value="guohongqiao@gmail.com" type="hidden"> <input
					name="item_name"
					value="Donate to support free Joomla extensions development. Thanks."
					type="hidden"> <input name="no_shipping" value="0" type="hidden"> <input
					name="no_note" value="1" type="hidden"> <input name="currency_code"
					value="USD" type="hidden"> <input name="tax" value="0"
					type="hidden"> <input name="bn" value="PP-DonationsBF"
					type="hidden"> <input
					src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif"
					name="submit" alt="PayPal - The safer, easier way to pay online!"
					border="0" type="image"> <img alt=""
					src="https://www.paypal.com/en_US/i/scr/pixel.gif" border="0"
					width="1" height="1"></form>
				</td>
			</tr>

			<tr>
				<td><?php echo JText::_('VOTE');?></td>

				<td><a target="_blank"
					href="http://extensions.joomla.org/extensions/search-&-indexing/tags-&-clouds/7718/details">Joomla
				Extensions Directory</a></td>
			</tr>
<!-- 
			<tr>
				<td><?php echo JText::_('CUSTOMER DEVELOPMENT');?></td>

				<td></td>
			</tr>
 -->
			<tr>
				<td><?php echo JText::_('LICENSE');?></td>

				<td><a target="_blank"
					href="http://www.gnu.org/licenses/gpl-2.0.html">GNU/GPL License</a></td>
			</tr>
			<!-- 
			<tr>
				<td><?php echo JText::_('COPYRIGHT');?></td>
				<td>&copy; 2009 joomlatags.org</td>
			</tr>
			 -->
		</table>
		</td>
	</tr>
</table>


