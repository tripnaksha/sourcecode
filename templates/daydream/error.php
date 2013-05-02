<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

JPlugin::loadLanguage( 'tpl_SG1' );

if (($this->error->code) == '404') {
header('Location: index.php?option=com_content&view=article&id=175');
exit;
} 
?>

