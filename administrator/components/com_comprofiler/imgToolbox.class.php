<?php
/**
 * $Id: imgToolbox.class.php 480 2006-11-01 01:14:51Z beat $
-----------------------------------------------------------------------
|                                                                     |
| Date: March, 2005						      |
| Author: MamboJoe, <http://www.mambojoe.com>                         |
| Original Author: Mike de Boer, <http://www.mikedeboer.nl>           |
| Copyright: copyright (C) 2004 by Mike de Boer                       |
| Description: Abstracted Image Class			              |
| License: GPL                                                        |
| Filename: imgToolbox.class.php                                      |
| Version: 3.0                                                        |
|                                                                     |
-----------------------------------------------------------------------
-----------------------------------------------------------------------
|                                                                     |
| What is the toolbox? --> well, it's an object that bundles all      |
| medium-manipulation tools into one convenient class.                |
| These tools would include:                                          |
|                                                                     |
| - Image resizing                                                    |
| - Image rotating                                                    |
| - Image watermarking with custom TrueType fonts                     |
| - ALL tools have implementations for the following manipulation     |
|   software: ImageMagick, NetPBM, GD1.x and GD2.x.                   |
|                                                                     |
-----------------------------------------------------------------------
**/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class imgToolbox{
	var $_platform = null;
	var $_isBackend = null;
	var $_conversiontype = null;
	var $_IM_path = null;
	var $_NETPBM_path = null;
	var $_JPEGquality = null;
	var $_err_num = null;
    	var $_err_names = array();
    	var $_err_types = array();
	var $_wmtext = null;
	var $_wmdatfmt = null;
	var $_wmfont = null;
	var $_wmfont_size = null;
	var $_wmrgbtext = null;
	var $_wmrgbtsdw = null;
	var $_wmhotspot = null;
	var $_wmtxp = null;
	var $_wmtyp = null;
	var $_wmsxp = null;
	var $_wmsyp = null;
    	var $_buffer = null;
	var $_filepath= null;
	var $_rotate = null;
	var $_maxsize = null;
	var $_maxwidth = null;
	var $_maxheight = null;
	var $_thumbwidth = null;
	var $_thumbheight = null;
	var $_errMSG = null;
	var $_debug = null;
	
	function imgToolBox(){
		// constructor of the toolbox - primary init...
			$this->_conversiontype=1;
			$this->_rotate=0;
			$this->_IM_path = "auto";
			$this->_NETPBM_path = "auto";
			$this->_maxsize = 102400;
			$this->_maxwidth = 200;
			$this->_maxheight = 500;
			$this->_thumbwidth = 86;
			$this->_thumbheight = 60;
			$this->_JPEGquality = 85;
		// load watermark settings...
			$this->_wmtext = "[date]";
			$this->_wmfont = "ARIAL.TTF";
			$this->_wmfont_size = 12;
			$this->_wmrgbtext = "FFFFFF";
			$this->_wmrgbtsdw = "000000";
			$this->_wmhotspot = 8;
			$this->_wmdatfmt = "Y-m-d";
		// watermark offset coordinates...t = top and s = side.
			$this->_wmtxp = 0;
			$this->_wmtyp = 0;
			$this->_wmsxp = 1;
			$this->_wmsyp = 1;
		
		// toolbox ready for use...
	}
    function processImage($image, $filename,$filepath, $rotate, $degrees = 0, $copyMethod = 1, $allwaysResize = 1){
		// reset script execution time limit (as set in MAX_EXECUTION_TIME ini directive)...
		// requires SAFE MODE to be OFF!
		$this->_filepath=$filepath;
		if( ( !($this->cbIsFunctionDisabled( 'set_time_limit' ) ) ) && ( ini_get( 'safe_mode' ) != 1 ) ) {	//CB_FIXED
			@set_time_limit(0);
		}
		$error=0;
		$errorMSG=null;
		switch ($this->_conversiontype){
			//Imagemagick
			case 1:
				if($this->_IM_path == 'auto'){
					$this->_IM_path		=	$this->autodetectExec( 'convert' );
				}else{
					if($this->_IM_path){
						if(!is_dir($this->_IM_path)){
								$error=1;
								$errorMSG = "Error: your ImageMagick path is not correct! Please (re)specify it in the Admin-system under 'Settings'";
							}
					}
				}
				break;
			//NetPBM
			case 2:
				if($this->_NETPBM_path == 'auto'){
					$this->_NETPBM_path	=	$this->autodetectExec( 'jpegtopnm' );
				}else{
					if($this->_NETPBM_path){
						if(!is_dir($this->_NETPBM_path)){
								$error=1;
								$errorMSG = "Error: your NetPBM path is not correct! Please (re)specify it in the Admin-system under 'Settings'";
							}
					}
				}
				break;
			//GD1
			case 3:
				if (!function_exists('imagecreatefromjpeg')) {
				    $error=1;
				    $errorMSG = "PHP running on your server does not support the GD image library, check with your webhost if ImageMagick is installed";
				}
				break;
			//GD2
			case 4:
				if (!function_exists('imagecreatefromjpeg')) {
				    $error=1;
				    $errorMSG = "Error: PHP running on your server does not support the GD image library, check with your webhost if ImageMagick is installed";
				}
				if (!function_exists('imagecreatetruecolor')) {
				    $error=1;
				    $errorMSG = "Error: PHP running on your server does not support GD graphics library version 2.x, please install GD version 2.x or switch to another images library in Community Builder Configuration.";
				}
				break;
		}
		if($error) {
			$this->raiseError($errorMSG);
                    	return false;
		}
		if(!$this->checkFilesize($image['tmp_name'],$this->_maxsize*1024)) {
			$this->raiseError("The file exceeds the maximum size of ".$this->_maxsize." kilobytes");
                    	return false;
		}
			
		$filepath		=	$this->_filepath;  
		$filename		=	urldecode($filename);
    	// replace every space-character with a single "_"
    	$filename		=	preg_replace( "/ /", "_",	 $filename );
 		// Get rid of extra underscores
 		$filename		=	preg_replace( "/_+/", "_",	 $filename );
 		$filename		=	preg_replace( "/(^_|_$)/", "", $filename );
		$tag			=	preg_replace( "/^.*\\.([^\\.]*)$/", "\\1", $image['name'] );
		$tag			=	strtolower( $tag );
		$filename		=	$filename . "." . $tag;
		$image			=	$image['tmp_name'];
        if ( $this->acceptableFormat( $tag ) ) {
            // File is an image/ movie/ document...
            $file		=	$this->_filepath . $filename;
            $thumbfile	=	$this->_filepath . "tn" . $filename;
            if ( $copyMethod == 1 ) {
                if ( ! @move_uploaded_file( $image, $file ) ) {
                    // some error occured while moving file, register this...
					$this->raiseError("Error occurred during the moving of the uploaded file.");
                   	return false;
                }
            } elseif ( $copyMethod == 2 ) {
                //disabled: if ( ! @fs_copy( $image, $file ) ){
                    // some error occured while moving file, register this...
                    $this->raiseError("Error occurred during the moving of the uploaded file.");
                    return false;
                //}
            }
            @chmod( $file, 0644 );

            if( $this->isImage( $tag ) ){
           	   $imginfo	=	getimagesize( $file );
           	   if ( $imginfo !== false ) {
	               if( $this->_rotate ){
	                   if( ! $this->rotateImage( $file, $file, $filename, $degrees ) ) {
       	           	   	   @unlink( $file );
	                       $this->raiseError("Error rotating image");
	                       return false;
	                   }
	               }
	               // if the image size is greater than the given maximum: resize it!               
	               if ( $imginfo[0] > $this->_maxwidth || $imginfo[1] > $this->_maxheight ){
	               		if( ! $this->resizeImage( $file, $file, $this->_maxwidth, $this->_maxheight, $filename ) ) {
		           	   	   @unlink( $file );
	               		   $this->raiseError( "Error: resizing image failed." );
		                   return false;
	               		}
	               } elseif ( $allwaysResize ) {
	               		if( ! $this->resizeImage( $file, $file, $imginfo[0], $imginfo[1], $filename ) ) {
		           	   	   @unlink( $file );
	               		   $this->raiseError( "Error: resizing image failed." );
		                   return false;
	               		}
	               }
	               // resize to thumbnail...
	            	if( ! $this->resizeImage( $file, $thumbfile, $this->_thumbwidth, $this->_thumbheight, $filename ) ) {
	           	   	   @unlink( $file );
	                   $this->raiseError("Error: resizing thumbnail image failed.");
	                   return false;
	               }
	               @chmod( $thumbfile, 0644 );
           	   } else {
           	   	   @unlink( $file );
           	   	   $this->raiseError( "Error: image format is not supported." );
      			   return false;
           	   }
           }
        } else {
            //Not the right format, register this...
            $this->raiseError( "Error: " . $tag . " is not a supported format." );
            return false;
        }
        return $filename;
    }
	function acceptableFormat($tag){
		return ($this->isImage($tag));
	}
	function isImage($tag){
	    return in_array($tag, $this->acceptableImageList());
	}
	function acceptableImageList(){
	    return array('jpg', 'jpeg', 'gif', 'png');
	}
	function resizeImage($file, $desfile, $maxWidth, $maxHeight, $filename = ""){
		list($width, $height) = @getimagesize($file);

		if( $width > $maxWidth & $height <= $maxHeight )
		{
			$ratio = $maxWidth / $width;
		}
		elseif( $height > $maxHeight & $width <= $maxWidth )
		{
			$ratio = $maxHeight / $height;
		}
		elseif( $width > $maxWidth & $height > $maxHeight )
		{
			$ratio1 = $maxWidth / $width;
			$ratio2 = $maxHeight / $height;
			$ratio = ($ratio1 < $ratio2)? $ratio1:$ratio2;
		}
		else
		{
			$ratio = 1;
		}

		$nWidth = floor($width*$ratio);
		$nHeight = floor($height*$ratio);

		switch ($this->_conversiontype){
			//Imagemagick
			case 1:
				if($this->resizeImageIM($file, $desfile, $nWidth,$nHeight))
					return true;
				else
					return false;
				break;
			//NetPBM
			case 2:
				if($this->resizeImageNETPBM($file,$desfile,$nWidth,$nHeight,$filename))
					return true;
				else
					return false;
				break;
			//GD1
			case 3:
				if($this->resizeImageGD1($file, $desfile, $nWidth,$nHeight))
					return true;
				else
					return false;
				break;
			//GD2
			case 4:
				if($this->resizeImageGD2($file, $desfile, $nWidth,$nHeight))
					return true;
				else
					return false;
				break;
		}
		return true;
	}
	function _escapeshellcmd( $command ) {
		if ( substr( PHP_OS, 0, 3 ) == 'WIN' )	 {
			return $command;
		} else {
			return escapeshellcmd( $command );
		}
		
	}	
	function resizeImageIM($src_file, $dest_file, $destWidth,$destHeight){
		//$cmd = $this->_IM_path."convert -resize $new_size \"$src_file\" \"$dest_file\"";
   		//$cmd = "'".$this->_IM_path."convert' -geometry $destWidth x $destHeight '$src_file' '$dest_file'"; 
   		$cmd = $this->_escapeshellcmd( $this->_IM_path . 'convert' ) . ' -geometry ' . escapeshellarg( $destWidth ) . ' x ' . escapeshellarg( $destHeight ) . ' ' . escapeshellarg( $src_file ) . ' ' . escapeshellarg( $dest_file ); 
		$output=array(); $retval = null;
		exec($cmd, $output, $retval);
		if($this->debug()) {
			echo "<div>cmd=$cmd<br/>output=". join("\n", $output)."</div>";
		}
		return true;
/* ???	//TBD:
		if ( ( substr( PHP_OS, 0, 3 ) != 'WIN' ) && $retval ) {
			return false;
		} else {
			return true;
		}
*/
	}
	function resizeImageNETPBM($src_file,$des_file,$destWidth,$destHeight,$orig_name){
		$quality = $this->_JPEGquality;
		$imginfo = getimagesize($src_file);
		if ($imginfo == null) {
			$this->raiseError("Error: Unable to execute getimagesize function");
			return false;
		}
		if (preg_match("/\\.png\$/i", $orig_name)){
			$cmd = $this->_escapeshellcmd( $this->_NETPBM_path . 'pngtopnm' )  . ' ' . escapeshellarg( $src_file ) . ' | ' . $this->_escapeshellcmd( $this->_NETPBM_path . 'pnmscale' ) . ' -xysize ' . escapeshellarg( $destWidth ) . ' ' . escapeshellarg( $destHeight ) . ' | ' . $this->_escapeshellcmd( $this->_NETPBM_path . 'pnmtopng' ) . ' > ' . escapeshellarg( $des_file ); 
		}
		else if (preg_match("/\\.(jpg|jpeg)\$/i", $orig_name)){
			$cmd = $this->_escapeshellcmd( $this->_NETPBM_path . 'jpegtopnm' ) . ' ' . escapeshellarg( $src_file ) . ' | ' . $this->_escapeshellcmd( $this->_NETPBM_path . 'pnmscale' ) . ' -xysize ' . escapeshellarg( $destWidth ) . ' ' . escapeshellarg( $destHeight ) . ' | ' . $this->_escapeshellcmd( $this->_NETPBM_path . 'ppmtojpeg' ) . ' ' . escapeshellarg( "-quality=$quality" ) . ' > ' . escapeshellarg( $des_file );
	}
		else if (preg_match("/\\.gif\$/i", $orig_name)){
			$cmd = $this->_escapeshellcmd( $this->_NETPBM_path . 'giftopnm' )  . ' ' . escapeshellarg( $src_file ) . ' | ' . $this->_escapeshellcmd( $this->_NETPBM_path . 'pnmscale' ) . ' -xysize ' . escapeshellarg( $destWidth ) . ' ' . escapeshellarg( $destHeight ) . ' | ' . $this->_escapeshellcmd( $this->_NETPBM_path . 'ppmquant' ) . ' 256 | ' . $this->_escapeshellcmd( $this->_NETPBM_path . 'ppmtogif' ) . ' > ' . escapeshellarg( $des_file );
		}else{
			$this->raiseError("Error: NetPBM doesn't support this file type.");
			return false;
		}
		$output = array();
		$retval = null;
		exec($cmd, $output, $retval);
		if($this->debug()) {
			echo "<div>cmd=$cmd<br/>output=". join("\n", $output)."</div>";
		}
			return true;
/* ???	//TBD:
		if ( ( substr( PHP_OS, 0, 3 ) != 'WIN' ) && $retval ) {
			return false;
		} else {
			return true;
		}
*/
	}
	function resizeImageGD1($src_file, $dest_file, $destWidth,$destHeight){
		$imginfo = getimagesize($src_file);
		if ($imginfo == null) {
			$this->raiseError("Error: Unable to execute getimagesize function");
			return false;
		}
		// GD can only handle JPG & PNG images
		if ($imginfo[2] != 2 && $imginfo[2] != 3 && ($imginfo[2] == 1 && !function_exists( 'imagecreatefromgif' ))){
			$this->raiseError("Error: GD1 doesn't support this file type.");
			return false;
		}
		if ($imginfo[2] == 2)
			$src_img = imagecreatefromjpeg($src_file);
		elseif ($imginfo[2] == 1)
			$src_img = imagecreatefromgif($src_file);
		else
			$src_img = imagecreatefrompng($src_file);
		if (!$src_img) {
			$this->raiseError("Error: GD1 Unable to create image from imagetype function");
			return false;
		}
		$dst_img = imagecreate($destWidth, $destHeight);
		imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, $destWidth, $destHeight, $imginfo[0], $imginfo[1]);
		if ($imginfo[2] == 2)
			imagejpeg($dst_img, $dest_file, $this->_JPEGquality);
		elseif ($imginfo[2] == 1)
			imagegif($dst_img, $dest_file);
		else
			imagepng($dst_img, $dest_file);
		imagedestroy($src_img);
		imagedestroy($dst_img);
		return true; 
	}
	function resizeImageGD2($src_file, $dest_file, $destWidth,$destHeight){
		$imginfo = getimagesize($src_file);
		if ($imginfo == null) {
			$this->raiseError("Error: Unable to execute getimagesize function");
			return false;
		}
		// GD can only handle JPG & PNG images
		if ($imginfo[2] != 2 && $imginfo[2] != 3 && ($imginfo[2] == 1 && !function_exists( 'imagecreatefromgif' ))){
			$this->raiseError("Error: GD2 Unable to create image from imagetype function");
			return false;
		}
		if ($imginfo[2] == 2)
			$src_img = imagecreatefromjpeg($src_file);
		elseif ($imginfo[2] == 1)
			$src_img = imagecreatefromgif($src_file);
		else
			$src_img = imagecreatefrompng($src_file);
		if (!$src_img) {
			$this->raiseError("Error: GD2 Unable to create image from imagetype function");
			return false;
		}
		$dst_img = imagecreatetruecolor($destWidth, $destHeight);
		$background_color	=	imagecolorallocate( $dst_img, 255, 255, 255 );		// white background for images with transparency
		ImageFilledRectangle($dst_img, 0, 0, $destWidth, $destHeight, $background_color);
		imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $destWidth, $destHeight, $imginfo[0], $imginfo[1]);
		if ($imginfo[2] == 2) {
			imagejpeg($dst_img, $dest_file, $this->_JPEGquality);
		} elseif ($imginfo[2] == 1) {
			if(function_exists('imagegif')) {
				imagegif($dst_img, $dest_file);
			} else {
				$this->raiseError("Error: GIF Uploads are not supported by this version of GD");
				return false;
			}
		} else {
			imagepng($dst_img, $dest_file);
		}
		imagedestroy($src_img);
		imagedestroy($dst_img);
		return true;
	}
	function rotateImage($file, $desfile, $filename, $degrees){
		$degrees = intval($degrees);
		switch ($this->_conversiontype){
			//Imagemagick
			case 1:
				if($this->rotateImageIM($file, $desfile, $degrees))
					return true;
				else
					return false;
				break;
			//NetPBM
			case 2:
				if($this->rotateImageNETPBM($file,$desfile, $filename, $degrees))
					return true;
				else
					return false;
				break;
			//GD1
			case 3:
				if($this->rotateImageGD1($file, $desfile, $degrees))
					return true;
				else
					return false;
				break;
			//GD2
			case 4:
				if($this->rotateImageGD2($file, $desfile, $degrees))
					return true;
				else
					return false;
				break;
		}
		return true;
	}
	function checkFilesize($file,$maxSize) {
	   $size = filesize($file);
	   if($size <= $maxSize) {
	      return true;
	   }
	   return false;
	}

	function rotateImageIM($file, $desfile, $degrees){
		$cmd = $this->_escapeshellcmd( $this->_IM_path . 'convert' ) . ' -rotate ' . escapeshellarg( $degrees ) . ' ' . escapeshellarg( $file ) . ' ' . escapeshellarg( $desfile );
		$retval = null;
		$output = null;
		exec($cmd, $output, $retval);
		if ( ( substr( PHP_OS, 0, 3 ) != 'WIN' ) && $retval ) {
			return false;
		} else
			return true;
	}
	function rotateImageNETPBM($file, $desfile, $filename, $degrees){
		$quality = $this->_JPEGquality;
		$fileOut = "$file.1";
		fs_copy($file,$fileOut); 
		if (preg_match("/\\.png\$/i", $filename)){
			$cmd = $this->_escapeshellcmd( $this->_NETPBM_path . 'pngtopnm' )  . ' ' . escapeshellarg( $file ) . ' | ' . $this->_escapeshellcmd( $this->_NETPBM_path . 'pnmrotate' ) . ' ' . escapeshellarg( $degrees ) . ' | ' . $this->_escapeshellcmd( $this->_NETPBM_path . 'ppmtopng' )  . ' > ' . escapeshellarg( $fileOut ); 
		}
		else if (preg_match("/\\.(jpg|jpeg)\$/i", $filename)){
			$cmd = $this->_escapeshellcmd( $this->_NETPBM_path . 'jpegtopnm' ) . ' ' . escapeshellarg( $file ) . ' | ' . $this->_escapeshellcmd( $this->_NETPBM_path . "pnmrotate" ) . ' ' . escapeshellarg( $degrees ) . ' | ' . $this->_escapeshellcmd( $this->_NETPBM_path . 'ppmtojpeg' ) . ' ' . escapeshellarg( '-quality=' . $quality ). ' > ' . escapeshellarg( $fileOut );
		}
		else if (preg_match("/\\.gif\$/i", $filename)){
			$cmd = $this->_escapeshellcmd( $this->_NETPBM_path . 'giftopnm' )  . ' ' . escapeshellarg( $file ) . ' | ' . $this->_escapeshellcmd( $this->_NETPBM_path . "pnmrotate" ) . ' ' . escapeshellarg( $degrees ) . ' | ' . $this->_escapeshellcmd( $this->_NETPBM_path . 'ppmquant' )  . ' 256 | ' . $this->_escapeshellcmd( $this->_NETPBM_path . 'ppmtogif' ) . ' > ' . escapeshellarg( $fileOut ); 
		}else{
			return false;
		}
		$output = null;
		$retval = null;
		exec($cmd, $output, $retval);
		if ( ( substr( PHP_OS, 0, 3 ) != 'WIN' ) && $retval ) {
			return false;
		}else{
			fs_rename($fileOut, $desfile); 
			return true;
		}
	}
	function rotateImageGD1($file, $desfile, $degrees){
		$imginfo = getimagesize($file);
		if ($imginfo == null)
			return false;
		// GD can only handle JPG & PNG images
		if ($imginfo[2] != 2 && $imginfo[2] != 3){
			return false;
		}
		if ($imginfo[2] == 2)
			$src_img = imagecreatefromjpeg($file);
		else
			$src_img = imagecreatefrompng($file);
		if (!$src_img)
			return false;
		// The rotation routine...
		$src_img	= imagerotate($src_img, $degrees, 0);
		$dst_img	= imagecreate($imginfo[0], $imginfo[1]);
		$destWidth 	= $imginfo[0];
		$destHeight	= $imginfo[1];
		imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, (int) $destWidth, (int) $destHeight, imagesx( $src_img ), imagesy( $src_img ) );
		if ($imginfo[2] == 2)
			imagejpeg($dst_img, $desfile, $this->_JPEGquality);
		else
			imagepng($dst_img, $desfile);
		imagedestroy($src_img);
		imagedestroy($dst_img);
		return true; 
	}
	function rotateImageGD2($file, $desfile, $degrees){
		$imginfo = getimagesize($file);
		if ($imginfo == null)
			return false;
		// GD can only handle JPG & PNG images
		if ($imginfo[2] != 2 && $imginfo[2] != 3){
			return false;
		}
		if ($imginfo[2] == 2)
			$src_img = imagecreatefromjpeg($file);
		else
			$src_img = imagecreatefrompng($file);
		if (!$src_img)
			return false;
		// The rotation routine...
		$src_img = imagerotate($src_img, $degrees, 0);
		$dst_img = imagecreatetruecolor($imginfo[0], $imginfo[1]);
		$destWidth 	= $imginfo[0];
		$destHeight	= $imginfo[1];
		imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, (int) $destWidth, (int) $destHeight, imagesx( $src_img ), imagesy( $src_img ) );
		if ($imginfo[2] == 2)
			imagejpeg($dst_img, $desfile, $this->_JPEGquality);
		else
			imagepng($dst_img, $desfile);
		imagedestroy($src_img);
		imagedestroy($dst_img);
		return true;
	}
	// watermark by Elf Qrin ( http://www.ElfQrin.com/ ) - modified for use with zOOm.
	function watermark($file, $desfile) {
		$suffx = substr($file,strlen($file)-4,4);
		if ($suffx == ".jpg" || $suffx == "jpeg" || $suffx == ".png") {
			$text = str_replace("[date]",date($this->_wmdatfmt),$this->_wmtext);
			if ($suffx == ".jpg" || $suffx == "jpeg") {
				$image = imagecreatefromjpeg($file);
			}
			if ($suffx == ".png") {
				$image = imagecreatefrompng($file);
			}
			$rgbtext = HexDec($this->_wmrgbtext);
			$txtr = floor($rgbtext/pow(256,2));
			$txtg = floor(($rgbtext%pow(256,2))/pow(256,1));
			$txtb = floor((($rgbtext%pow(256,2))%pow(256,1))/pow(256,0));
			
			$rgbtsdw = HexDec($this->_wmrgbtsdw);
			$tsdr = floor($rgbtsdw/pow(256,2));
			$tsdg = floor(($rgbtsdw%pow(256,2))/pow(256,1));
			$tsdb = floor((($rgbtsdw%pow(256,2))%pow(256,1))/pow(256,0));
			
			$coltext = imagecolorallocate($image,$txtr,$txtg,$txtb);
			$coltsdw = imagecolorallocate($image,$tsdr,$tsdg,$tsdb);
			
			if ($this->_wmhotspot != 0) {
				$ix = imagesx($image);
				$iy = imagesy($image);
				$tsw = strlen($text)*$this->_wmfont_size/imagefontwidth($this->_wmfont)*3;
				$tsh = $this->_wmfont_size/imagefontheight($this->_wmfont);
				switch ($this->_wmhotspot) {
					case 1:
						$txp = $this->_wmtxp;
						$typ = $tsh*$tsh+imagefontheight($this->_wmfont)*2+$this->_wmtyp;
						break;
					case 2:
						$txp = floor(($ix-$tsw)/2);
						$typ = $tsh*$tsh+imagefontheight($this->_wmfont)*2+$this->_wmtyp;
						break;
					case 3:
						$txp = $ix-$tsw-$txp;
						$typ = $tsh*$tsh+imagefontheight($this->_wmfont)*2+$this->_wmtyp;
						break;
					case 4:
						$txp = $this->_wmtxp;
						$typ = floor(($iy-$tsh)/2);
						break;
					case 5:
						$txp = floor(($ix-$tsw)/2);
						$typ = floor(($iy-$tsh)/2);
						break;
					case 6:
						$txp = $ix-$tsw-$this->_wmtxp;
						$typ = floor(($iy-$tsh)/2);
						break;
					case 7:
						$txp = $this->_wmtxp;
						$typ = $iy-$tsh-$this->_wmtyp;
						break;
					case 8:
						$txp = floor(($ix-$tsw)/2);
						$typ = $iy-$tsh-$this->_wmtyp;
						break;
					case 9:
						$txp = $ix-$tsw-$this->_wmtxp;
						$typ = $iy-$tsh-$this->_wmtyp;
						break;
				}
			}
			imagettftext($image, $this->_wmfont_size, 0, $txp + 1, $typ + 1, $coltsdw, $this->_wmfont,$text);
			imagettftext($image, $this->_wmfont_size, 0, $txp, $typ, $coltext, $this->_wmfont, $text);	
			if ($suffx == ".jpg" || $suffx == "jpeg") {
				imagejpeg($image, $desfile, $this->_JPEGquality);
			}elseif($suffx == ".png"){
				imgepng($image, $desfile);
			}
			imagedestroy($image);
			return true;
		}else{
			return false;
		}
	}
/**
* Copy and resample an image with rounded corners.
* source:  public code, license: public
* ----------------------------------------------------------- */
	function imageRoundedCopyResampled(&$dstimg, &$srcimg, $dstx, $dsty, $srcx, $srcy, $dstw, $dsth, $srcw, $srch, $radius) {
		# Resize the Source Image
		$srcResized = imagecreatetruecolor($dstw, $dsth);
		imagecopyresampled($srcResized, $srcimg, 0, 0, $srcx, $srcy,
		$dstw, $dsth, $srcw, $srch);
		# Copy the Body without corners
		imagecopy($dstimg, $srcResized, $dstx+$radius, $dsty,
		$radius, 0, $dstw-($radius*2), $dsth);
		imagecopy($dstimg, $srcResized, $dstx, $dsty+$radius,
		0, $radius, $dstw, $dsth-($radius*2));
		# Create a list of iterations; array(array(X1, X2, CenterX, CenterY), ...)
		# Iterations in order are: Top-Left, Top-Right, Bottom-Left, Bottom-Right
		$iterations = array(
		array(0, 0, $radius, $radius),
		array($dstw-$radius, 0, $dstw-$radius, $radius),
		array(0, $dsth-$radius, $radius, $dsth-$radius),
		array($dstw-$radius, $dsth-$radius, $dstw-$radius, $dsth-$radius)
		);
		# Loop through each corner 'iteration'
		foreach($iterations as $iteration) {
			list($x1,$y1,$cx,$cy) = $iteration;
			for ($y=$y1; $y<=$y1+$radius; $y++) {
				for ($x=$x1; $x<=$x1+$radius; $x++) {
					# If length (X,Y)->(CX,CY) is less then radius draw the point
					$length = sqrt(pow(($cx - $x), 2) + pow(($cy - $y), 2));
					if ($length < $radius) {
						imagecopy($dstimg, $srcResized, $x+$dstx, $y+$dsty,
						$x, $y, 1, 1);
					}
				}
			}
		}
	}
	function parseDir($dir){
		global $zoom;
		// start the scan...(open the local dir)
		$images = array();
		$handle = fs_opendir($dir);
		while (($file = readdir($handle)) != false) {
			if ($file != "." && $file != "..") {
				$tag = preg_replace("/.*\\.([^\\.]*)\$/", "\\1", $file);
				$tag = strtolower($tag);
				if ($zoom->acceptableFormat($tag)) {
					// Tack it onto images...
					$images[] = $file;
				}
			}
		}
		closedir($handle);
		return $images;
	}
	function getImageLibs(){
		// do auto-detection on the available graphics libraries
		// This assumes the executables are within the shell's path
		$imageLibs= array();
		// do various tests:
		if ( true == ( $testIM = $this->testIM() ) ) {
			$imageLibs['imagemagick'] = $testIM;
		}
		if (true == ( $testNetPBM = $this->testNetPBM() ) ) {
			$imageLibs['netpbm'] = $testNetPBM;
		}			
		$imageLibs['gd'] = $this->testGD();		
		return $imageLibs;
	}

//CB_FIXES:
	function cbIsFunctionDisabled( $function ) {
		if (is_callable("ini_get")) {
			$funcsString		=	@ini_get( 'disable_functions' );
			if ( extension_loaded( 'suhosin' ) ) {
				$funcsString	.=	',' . @ini_get( 'suhosin.executor.func.blacklist' );
			}
			$funcs				=	explode( ',', $funcsString );
			for ( $i=0, $n=count($funcs); $i<$n; $i++ ) {
				$funcs[$i] = trim($funcs[$i]);
			}
			return in_array( $function, $funcs );
		} else {
			return true;
		}
	}
	
	function cbIsExecDisabled() {
		return $this->cbIsFunctionDisabled( 'exec' );
	}
//END OF CB_FIXES.
	function testIM(){
		if($this->cbIsExecDisabled()){		//CB_FIXES:
			return false;     // exec() is disabled, so give up
		}
		if ( $this->_IM_path == 'auto' ) {
			$this->_IM_path		=	$this->autodetectExec( 'convert' );
		}
		$output					=	null;
		$status					=	null;
		$matches				=	null;
		@exec( $this->_escapeshellcmd( $this->_IM_path . 'convert' ) . ' -version', $output, $status );
		if ( ( ( substr( PHP_OS, 0, 3 ) == 'WIN' ) || ! $status ) && isset( $output[0] ) ) {
			if (preg_match("/imagemagick[ \t]+([0-9\\.]+)/i",$output[0],$matches))
			   return $matches[0];
		}
		unset($output, $status);
		return null;
	}
	function testNetPBM(){
		if($this->cbIsExecDisabled()){		//CB_FIXES:
			return false;     // exec() is disabled, so give up
		}
		if($this->_NETPBM_path == 'auto'){
			$this->_NETPBM_path	=	$this->autodetectExec( 'jpegtopnm' );
		}
		$output		=	null;
		$status		=	null;
		$matches	=	null;
		@exec( $this->_escapeshellcmd( $this->_NETPBM_path . 'jpegtopnm' ) . ' -version 2>&1',  $output, $status );
		if ( ( ( substr( PHP_OS, 0, 3 ) == 'WIN' ) || ! $status ) && isset( $output[0] ) ) {
			if (preg_match("/netpbm[ \t]+([0-9\\.]+)/i",$output[0],$matches))
			   return $matches[0];
		}
		unset($output, $status);
		return null;
	}
	function testGD(){
		$gd = array();
		// $GDfuncList = get_extension_funcs('gd');
		ob_start();
		@phpinfo(INFO_MODULES);
		$output=ob_get_contents();
		ob_end_clean();
		$matches[1]='';
		if(preg_match("/GD Version[ \t]*(<[^>]+>[ \t]*)+([^<>]+)/s",$output,$matches)){
			$gdversion = $matches[2];

			if (function_exists('imagecreatetruecolor') && function_exists('imagecreatefromjpeg')) {
				$gd['gd2'] = $gdversion;
			} elseif (function_exists('imagecreatefromjpeg')) {
				$gd['gd1'] = $gdversion;
			}
		}
		/*
		

		*/
		return $gd;
	}
	function autodetectExec( $program ) {
		$path					=	null;
		if( function_exists( 'is_executable' ) ) {
			$paths = array( '/usr/bin/', '/usr/local/bin/', '' );
			foreach ($paths as $path) {
				if ( @is_executable( $path . $program ) ) {
					break;
				}
			}
		}
		return $path;
	}
	function strip_nulls( $str ){
		$res = explode( chr(0), $str );
		return chop( $res[0] );
	}
	//--------------------Error handling functions-------------------------//
	function debug() {
		if($this->_debug) return true;
		else return false;

	}
	function raiseError($errorMSG) {
		$this->_errMSG=$errorMSG;
		return true;
	}


	function displayErrors(){
		if ($this->_err_num <> 0){
			echo '<center><table border="0" cellpadding="3" cellspacing="0" width="70%">';
			echo '<tr class="sectiontableheader"><td width="100" align="left">Image Name</td><td align="left">Error type</td></tr>';
			$tabcnt = 0;
			for ($x = 0; $x <= $this->_err_num; $x++){
				echo '<tr align="left"><td>'.$this->_err_names[$x].'</td><td align="left">'.$this->_err_types[$x].'</td></tr>';
				if ($tabcnt == 1){
	    			$tabcnt = 0;
				} else {
					$tabcnt++;
	    		}
			}
			echo "</table></center>";
		}
	}
	//--------------------END error handling functions----------------------//
}
