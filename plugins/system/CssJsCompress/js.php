<?php
header('Content-type: text/javascript; charset: UTF-8');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 1728000) . ' GMT');

header('Cache-control: max-age=30');
$jsFileName=$_GET["js"];

if(!preg_match("/^[a-z0-9]+\.js(?:\.gz)?$/", $jsFileName)){
  exit("Access denied.");
}
  
if(strpos($jsFileName,"js.gz")){
   header("Content-Encoding: gzip");
}
define('DS', DIRECTORY_SEPARATOR);
define('PATH_ROOT', dirname(__FILE__) . DS);
$file=PATH_ROOT.'..'.DS.'..'.DS.'..'.DS.'cache'.DS.'js'.DS.$jsFileName;
if(file_exists($file)){
 header('Last-Modified: '.gmdate('D, d M Y H:i:s',filemtime($file)).' GMT');
    $content=file_get_contents($file);
    echo($content);
}

?>
