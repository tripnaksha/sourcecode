<?php
/*
// JoomlaWorks "Disqus Comment System" Plugin for Joomla! 1.5.x - Version 2.1
// Copyright (c) 2006 - 2009 JoomlaWorks Ltd.
// Released under the GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
// More info at http://www.joomlaworks.gr
// Designed and developed by the JoomlaWorks team
// ***Last update: May 30th, 2009***
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

?>

<span id="startOfPage"></span>

<?php if($disqusArticleCounter): ?>
<!-- Disqus comments counter and anchor link -->
<div class="jwDisqusArticleCounter">
	<a class="jwDisqusArticleCounterLink" href="#disqus_thread"><?php echo JText::_('View Comments'); ?></a>
	<div class="clr"></div>
</div>
<?php endif; ?>

<?php echo $row->text; ?>

<hr />

<!-- Disqus comments form -->
<div class="jwDisqusForm">
	<?php echo $output->comments; ?>
</div>

<div class="jwDisqusBackToTop">
	<a href="#startOfPage"><?php echo JText::_("back to top"); ?></a>
	<div class="clr"></div>
</div>
	
<div class="clr"></div>
