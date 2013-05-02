<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
class jsCssCompressor {

	function makeJs($basUrl,$joomlaRoot,$pathes,$to,$isGZ,$jqueryNoConflict,$jquery) {
		if(file_exists($to)) unlink($to);
		$fp=fopen($to,"wb");
		$data='';
		$header='';
		if($isGZ){
			$header='<?php '
			.'ob_start("ob_gzhandler");'
			.'header("Cache-Control: public");'
			.'header("Pragma: cache");'
			.'$offset = 60*60*24*60;'
			.'$ExpStr = "Expires: ".gmdate("D, d M Y H:i:s",time() + $offset)." GMT";'
			.'$LmStr = "Last-Modified: ".gmdate("D, d M Y H:i:s",filemtime(__FILE__))." GMT";'
			.'header($ExpStr);'
			.'header($LmStr);'
			.'header("Content-Type: text/javascript; charset: UTF-8");'
			.'?>'
			.' ';
		}
		foreach($pathes as $v) {
			if(file_exists($v)){
				$content=file_get_contents($v);
				if($jqueryNoConflict&&$jquery==$v){
					$content.="\n jQuery.noConflict();\n";
				}
				$data .=$this->clear_js($content);
			}else{
				echo('File not exist'.$v);
			}
		}
		fwrite($fp,$data );
		fclose($fp);
		if(file_exists($to)) {return true;}else{return false;}
	}

	function makeJsold($basUrl,$joomlaRoot,$pathes,$to,$isGZ,$jqueryNoConflict,$jquery) {
		if(file_exists($to)) unlink($to);
		$fp=fopen($to,"wb");
		$data='';
		foreach($pathes as $v) {
			if(file_exists($v)){
				$content=file_get_contents($v);
				if($jqueryNoConflict&&$jquery==$v){
					$content.="\n jQuery.noConflict();\n";
				}
				$data .=$this->clear_js($content);
			}else{
				echo('File not exist'.$v);
			}
		}
		if($isGZ){
			fwrite($fp,gzencode($data));
		}else{
			fwrite($fp,$data );
		}
		fclose($fp);
		if(file_exists($to)) {return true;}else{return false;}
	}


	function makeCss($basUrl,$joomlaRoot,$pathes,$to,$isGZ) {
		if(file_exists($to)) unlink($to);
		$fp=fopen($to,"wb");
		$data='';
		if($isGZ){
			$data='<?php '
			.'ob_start("ob_gzhandler");'
			.'header("Cache-Control: public");'
			.'header("Pragma: cache");'
			.'$offset = 60*60*24*60;'
			.'$ExpStr = "Expires: ".gmdate("D, d M Y H:i:s",time() + $offset)." GMT";'
			.'$LmStr = "Last-Modified: ".gmdate("D, d M Y H:i:s",filemtime(__FILE__))." GMT";'
			.'header($ExpStr);'
			.'header($LmStr);'
			.'header("Content-Type: text/css; charset: UTF-8");'
			.'?>'
			.' ';
		}
		foreach($pathes as $v) {
			if(file_exists($v)){
				$data.=$this->loadcss($basUrl,$joomlaRoot,$v);
			}else{
				echo('File not exist'.$v);
			}
		}

		fwrite($fp,$data );

		fclose($fp);
		if(file_exists($to)) {return true;}else{return false;}
	}

	function makeCssOld($basUrl,$joomlaRoot,$pathes,$to,$isGZ) {
		if(file_exists($to)) unlink($to);
		$fp=fopen($to,"wb");
		$data='';
		foreach($pathes as $v) {
			if(file_exists($v)){
				$data.=$this->loadcss($basUrl,$joomlaRoot,$v);

			}else{
				echo('File not exist'.$v);
			}
		}
		if($isGZ){
			fwrite($fp,gzencode($data));
		}else{
			fwrite($fp,$data );
		}
		fclose($fp);
		if(file_exists($to)) {return true;}else{return false;}
	}

	function clear_js($data) {
		return $data;
	}
	function loadcss($basUrl,$joomlaRoot,$file) {
		$data=file_get_contents($file);
		if(false){
		 $data = preg_replace('<
        \s*([@{}:;,]|\)\s|\s\()\s* |  
        /\*([^*\\\\]|\*(?!/))+\*/ |   
        [\n\r]                        
        >x', '\1', $data);
		}

		$base=basePathForFile($joomlaRoot,$basUrl,$file);
		echo($base);
		build_css_path(NULL, $base);
		//handle urls in css
		$data = preg_replace_callback('/url\([\'"]?(?![a-z]+:|\/+)([^\'")]+)[\'"]?\)/i', 'build_css_path', $data);
		return $data;
	}

}
//end class
function basePathForFile($joomlaRoot,$basUrl,$file=NULL){
	static $_joomlaRoot;
	static $_basUrl;
	if(isset($joomlaRoot)){
		$_joomlaRoot=$joomlaRoot;
		$_basUrl=$basUrl;

	}
	$base=str_replace($_joomlaRoot,$_basUrl,dirname($file)).'/';
	$base=str_replace("\\\\\\",'/',$base);
	$base=str_replace("///",'/',$base);
	$base=str_replace("/\\/",DS,$base);
	$base=str_replace("\\/\\",DS,$base);
	$base=str_replace("\\",'/',$base);
	$base=str_replace("\\\\",'/',$base);
	$base=str_replace("\\/",'/',$base);
	$base=str_replace("/\\",'/',$base);
	$base=str_replace("//",'/',$base);
	$base=str_replace("/",'/',$base);
	$base=str_replace('http:/','http://',$base);
	$base=str_replace('https:/','https://',$base);
	return $base;
}
function  build_css_path($matches, $base = NULL) {
	static $_base;
	// Store base path for preg_replace_callback.
	if (isset($base)) {
		$_base = $base;
	}
	// Prefix with base and remove '../' segments where possible.
	$path = $_base . $matches[1];
	$last = '';
	while ($path != $last) {
		$last = $path;
		$path = preg_replace('`(^|/)(?!\.\./)([^/]+)/\.\./`', '$1', $path);
	}
	return 'url('. $path .')';
}

/**
 * Loads the stylesheet and resolves all @import commands.
 *
 * Loads a stylesheet and replaces @import commands with the contents of the
 * imported file. Use this instead of file_get_contents when processing
 * stylesheets.
 *
 * The returned contents are compressed removing white space and comments only
 * when CSS aggregation is enabled. This optimization will not apply for
 * color.module enabled themes with CSS aggregation turned off.
 *
 * @param $file
 *   Name of the stylesheet to be processed.
 * @param $optimize
 *   Defines if CSS contents should be compressed or not.
 * @return
 *   Contents of the stylesheet including the imported stylesheets.
 */
function joomla_load_stylesheet($file, $optimize = NULL) {
	static $_optimize;
	// Store optimization parameter for preg_replace_callback with nested @import loops.
	if (isset($optimize)) {
		$_optimize = $optimize;
	}

	$contents = '';
	if (file_exists($file)) {
		// Load the local CSS stylesheet.
		$contents = file_get_contents($file);

		// Change to the current stylesheet's directory.
		$cwd = getcwd();
		chdir(dirname($file));

		// Replaces @import commands with the actual stylesheet content.
		// This happens recursively but omits external files.
		$contents = preg_replace_callback('/@import\s*(?:url\()?[\'"]?(?![a-z]+:)([^\'"\()]+)[\'"]?\)?;/', '_joomla_load_stylesheet', $contents);
		// Remove multiple charset declarations for standards compliance (and fixing Safari problems).
		$contents = preg_replace('/^@charset\s+[\'"](\S*)\b[\'"];/i', '', $contents);

		if ($_optimize) {
			// Perform some safe CSS optimizations.
			$contents = preg_replace('<
        \s*([@{}:;,]|\)\s|\s\()\s* |  # Remove whitespace around separators, but keep space around parentheses.
        /\*([^*\\\\]|\*(?!/))+\*/ |   # Remove comments that are not CSS hacks.
        [\n\r]                        # Remove line breaks.
        >x', '\1', $contents);
		}

		// Change back directory.
		chdir($cwd);
	}

	return $contents;
}

/**
 * Loads stylesheets recursively and returns contents with corrected paths.
 *
 * This function is used for recursive loading of stylesheets and
 * returns the stylesheet content with all url() paths corrected.
 */
function _joomla_load_stylesheet($matches) {
	$filename = $matches[1];
	$base = dirname(realpath($filename)) .'/';
	_joomla_build_css_path(NULL, $base);
	// Load the imported stylesheet and replace @import commands in there as well.
	$file = joomla_load_stylesheet($filename);

	// Alter all url() paths, but not external.
	return preg_replace('/url\(([\'"]?)(?![a-z]+:)([^\'")]+)[\'"]?\)?;/i', 'url(\1'. dirname($filename) .'/', $file);

}

function joomla_build_css_cache($basUrl,$joomlaRoot,$pathes,$to,$isGZ) {
	$data = '';

	// Build aggregate CSS file.

	foreach ($pathes as $file) {
		//$base=basePathForFile($joomlaRoot,$basUrl,$file);
		$contents = joomla_load_stylesheet($file, TRUE);
		// Return the path to where this CSS file originated from.
		//$base = dirname($file) .'/';
		$base=basePathForFile($joomlaRoot,$basUrl,$file);
		_joomla_build_css_path(NULL, $base);
		// Prefix all paths within this CSS file, ignoring external and absolute paths.
		$data .= preg_replace_callback('/url\([\'"]?(?![a-z]+:|\/+)([^\'")]+)[\'"]?\)/i', '_joomla_build_css_path', $contents);

		// Per the W3C specification at http://www.w3.org/TR/REC-CSS2/cascade.html#at-import,
		// @import rules must proceed any other style, so we move those to the top.
		$regexp = '/@import[^;]+;/i';
		preg_match_all($regexp, $data, $matches);
		$data = preg_replace($regexp, '', $data);
		$data = implode('', $matches[0]) . $data;

		$data=str_replace($joomlaRoot.DS,$basUrl,$data);

	}
	$fp=fopen($to,"wb");
	// Create the CSS file.
	if($isGZ){
		fwrite($fp,gzencode($data));
	}else{
		fwrite($fp,$data );
	}
	fclose($fp);
	return true;
}

/**
 * Helper function for joomla_build_css_cache().
 *
 * This function will prefix all paths within a CSS file.
 */
function _joomla_build_css_path($matches, $base = NULL) {
	static $_base;
	// Store base path for preg_replace_callback.
	if (isset($base)) {
		$_base = $base;
	}

	// Prefix with base and remove '../' segments where possible.
	$path = $_base . $matches[1];
	$last = '';
	while ($path != $last) {
		$last = $path;
		$path = preg_replace('`(^|/)(?!\.\./)([^/]+)/\.\./`', '$1', $path);
	}
	return 'url('. $path .')';
}
