<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<?php if(isset($list)&&!empty($list)) {?>
<div class="tagCloud"><?php	foreach ($list as $item) {?> <a
	href="<?php echo $item->link; ?>" rel="tag" class="<?php echo $item->class; ?>">
<?php echo $item->name; ?></a> <?php }?>
</div><?php } ?>
