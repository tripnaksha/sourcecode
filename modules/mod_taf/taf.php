<?php
$siteRoot= JURI::root();
?>

<div align="<?php echo $params->get('position'); ?>">
<div id="st<?php echo $params->get('accountNumber'); ?>" class="st-taf"> 
<script type="text/javascript" src="http://cdn.socialtwist.com/<?php echo $params->get('accountNumber'); ?>/script.js">
</script>
<a class="st-taf" href="http://tellafriend.socialtwist.com:80" onclick="return false;" style="border:0; padding:0; margin:0;" > 
<img alt="SocialTwist Tell-a-Friend" src="<?php echo $siteRoot;?><?php echo $params->get('image'); ?>" onmouseout="STTAFFUNC.hideHoverMap(this)"  onmouseover="STTAFFUNC.showHoverMap(this, '<?php echo $params->get('accountNumber'); ?>', window.location, document.title)" onclick="STTAFFUNC.cw(this, {id:'<?php echo $params->get('accountNumber'); ?>', link: window.location, title: document.title });" />
</a>
</div>
</div>
