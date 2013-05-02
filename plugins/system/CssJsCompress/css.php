<?php
header('Content-type: text/css; charset: UTF-8');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 1728000) . ' GMT');
$cssFileName=$_REQUEST["css"];

if(!preg_match("/^[a-z0-9]+\.css(?:\.gz)?$/", $cssFileName)){
  exit("Access denied.");
}

if(strpos($cssFileName,"css.gz")){
   header("Content-Encoding: gzip");
}
define('DS', DIRECTORY_SEPARATOR);
define('PATH_ROOT', dirname(__FILE__) . DS);
$file=PATH_ROOT.'..'.DS.'..'.DS.'..'.DS.'cache'.DS.'css'.DS.$cssFileName;
if(file_exists($file)){   
   header('Last-Modified: '.gmdate('D, d M Y H:i:s',filemtime($file)).' GMT');
   $content=file_get_contents($file);
   echo($content);

}
?>
