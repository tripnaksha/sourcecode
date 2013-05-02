<?php
/**
* @package mod_pixsearch
* @copyright	Copyright (C) 2007 PixPro Stockholm AB. All rights reserved.
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL, see LICENSE.php
* PixSearch is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<form name="pp_search" id="pp_search" action="<?php echo JURI::Base()?>" method="get">
<div class="pixsearch<?php echo $params->get('moduleclass_sfx'); ?>">
	<div class="ps_pretext"><?php echo $params->get('pretext'); ?></div>
	<input id="ps_search_btn" name="searchbtn" type="submit" value="<?php echo JText::_('GO'); ?>" />
	<input id="ps_search_str" name="searchword" type="text" value="<?php echo JText::_('SEARCH'); ?>" />
	<input type="hidden" name="searchphrase" value="<?php echo $params->get("searchphrase")?>"/>
	<input type="hidden" name="limit" value="" />
	<input type="hidden" name="ordering" value="<?php echo $params->get("ordering")?>" />
	<input type="hidden" name="view" value="search" />
	<input type="hidden" name="Itemid" value="99999999" />
	<input type="hidden" name="option" value="com_search" />
	<div class="ps_posttext"><?php echo $params->get('posttext'); ?></div>
	<div id="ps_filler">&nbsp;</div>
	<div id="ps_results" style="float:right"></div>
	<script type="text/javascript">
	setSpecifiedLanguage(
		'<?php echo JText::_('RESULTS'); ?>',
		'<?php echo JText::_('CLOSE'); ?>',
		'<?php echo JText::_('SEARCH'); ?>',
		'<?php echo JText::_('READMORE'); ?>',
		'<?php echo JText::_('NORESULTS'); ?>',
		'<?php echo JText::_('ADVSEARCH'); ?>',
		'<?php echo JURI::Base().htmlentities($params->get('search_page')); ?>',
		'<?php echo JURI::Base()?>',
		'<?php echo $params->get('limit', '10'); ?>',
		'<?php echo $params->get('ordering', 'newest'); ?>',
		'<?php echo $params->get('searchphrase', 'any'); ?>',
		'<?php echo $params->get('hide_divs', ''); ?>',
		<?php echo $params->get('include_link', 1); ?>,
		'<?php echo JText::_('VIEWALL'); ?>',
		<?php echo $params->get('include_category', 1); ?>,
		<?php echo $params->get('show_readmore', 1); ?>,
		<?php echo $params->get('show_description', 1); ?>
	);
	</script>
</div>
<div id="pixsearch_tmpdiv" style="visibility:hidden;display:none;float:right"></div>
</form>
