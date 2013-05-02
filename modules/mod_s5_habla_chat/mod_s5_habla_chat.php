<?php

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

$hablaid		= $params->get( 'hablaid', '' );
$pretext_s5_hc		= $params->get( 'pretext', "" );
$posttext_s5_hc		= $params->get( 'posttext', "" );
$height		= $params->get( 'height', '' );
$width		= $params->get( 'width', '' );

$LiveSite 	= JURI::base();

?>

<?php if ($pretext_s5_hc != "") { ?>
<?php echo $pretext_s5_hc ?>
<br /><br />
<?php } ?>

<div id="habla_available_div" onclick="javascript:habla_window.show();javascript:habla_window.expand();" style="display:none; background-image:url(<?php echo $LiveSite ?>/modules/mod_s5_habla_chat/s5_habla_chat/online.png); background-repeat:no-repeat; background-position:top left; cursor:pointer; height:<?php echo $height ?>px; width:<?php echo $width ?>px"></div>

<div id="habla_unavailable_div" style="background-image:url(<?php echo $LiveSite ?>/modules/mod_s5_habla_chat/s5_habla_chat/offline.png); background-repeat:no-repeat; background-position:top left; cursor:pointer; height:<?php echo $height ?>px; width:<?php echo $width ?>px"></div>

<?php if ($posttext_s5_hc != "") { ?>
<br />
<?php echo $posttext_s5_hc ?>
<?php } ?>

<script type="text/javascript" src="http://static.hab.la/js/wc.js"></script>
<script type="text/javascript">

function s5_close_habla_timer() {
window.setTimeout('s5_close_habla()',300);
}
function s5_close_habla() {
document.getElementById("habla_window_div").style.display = "none";
}
function s5_enable_habla_close() {
document.getElementById("habla_closebutton_a").onclick = s5_close_habla_timer;
}

config = wc_config();
config.vars["start_hidden"] = 1;
config.vars["expandOnMessageReceived"] = 1;
wc_init("<?php echo $hablaid ?>",config); 

window.setTimeout('s5_enable_habla_close()',3000);

</script>
