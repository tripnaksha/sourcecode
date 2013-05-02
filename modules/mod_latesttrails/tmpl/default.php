<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<ul class="latesttrails<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($list as $item) :  ?>
	<li class="latesttrails<?php echo $params->get('moduleclass_sfx'); ?>">
		<a href="<?php echo $item->link; ?>" class="latesttrails<?php echo $params->get('moduleclass_sfx'); ?>">
			<?php echo $item->text; ?></a>
	</li>
<?php endforeach; ?>
</ul>