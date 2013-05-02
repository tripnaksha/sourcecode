<?php

function com_install () {
	global $mainframe;
	global $dirs,$save,$copy;
	
	$db=JFactory::getDBO();
	$db->setQuery("INSERT INTO `#__modules` ( `id` , `title` , `content` , `ordering` , `position` , `checked_out` , `checked_out_time` , `published` , `module` , `numnews` , `access` , `showtitle` , `params` , `iscore` , `client_id` , `control` ) VALUES ('', 'ajaxSearch', '', '7', 'left', '0', '0000-00-00 00:00:00', '1', 'mod_ajaxsearch', '0', '0', '0', '', '0', '0', '')");
	$db->query();
	
	// Files Definition List
	$dirs=Array(
		'modules/mod_ajaxsearch',
		'modules/mod_ajaxsearch/img',
		'modules/mod_ajaxsearch/tmpl',
		'modules/mod_ajaxsearch/js',
		'modules/mod_ajaxsearch/css',
		'components/com_search/views/ajax',
		'components/com_search/views/ajax/tmpl'
	);
	
	// Unhacked files to save / backup
	$save=Array(
		'components/com_search/controller.php',				
	);
	
	// Packed files to copy
	$copy=Array(
		'components/com_search/controller.php',
		'components/com_search/models/ajax.php',
		'components/com_search/views/ajax/view.html.php',
		'components/com_search/views/ajax/index.html',
		'components/com_search/views/ajax/tmpl/default.php',
		'components/com_search/views/ajax/tmpl/index.html',
		'modules/mod_ajaxsearch/helper.php',
		'modules/mod_ajaxsearch/mod_ajaxsearch.php',
		'modules/mod_ajaxsearch/mod_ajaxsearch.xml',
		'modules/mod_ajaxsearch/index.html',
		'modules/mod_ajaxsearch/img/ajax-loader.gif',
		'modules/mod_ajaxsearch/img/close.gif',
		'modules/mod_ajaxsearch/img/shortcuts_arrow.gif',
		'modules/mod_ajaxsearch/img/search_loop.gif',
		'modules/mod_ajaxsearch/js/jquery-1.3.2.min.js',
		'modules/mod_ajaxsearch/js/script.js',
		'modules/mod_ajaxsearch/css/search.css',
		'modules/mod_ajaxsearch/tmpl/default.php',
		'modules/mod_ajaxsearch/tmpl/index.html',
		
	);
	
	$rootPath=JPATH_ROOT;
	
	$allCopied=copyFiles($rootPath);
	if($allCopied){
		//clearFiles($rootPath);
		$module_msg="Successfully Installed ajaxSearch";
	}else{
		$db=JFactory::getDBO();
		$db->setQuery("DELETE FROM `#__modules` WHERE title='ajaxSearch' AND module='mod_ajaxsearch'");
		$db->query();
		
		uncopyFiles($rootPath);
		//clearFiles($rootPath);
		
		$module_msg="Unable to Install ajaxSearch.";
	}
	
	echo "<p>$module_msg</p>";
	
}

// Function to copy the files (backup originals, create dirs, copy files)
function copyFiles($basePath){
	
	global $dirs,$save,$copy;
	
	if(subStr($basePath,-1)!='/')$basePath.='/';
	
	$basePath=str_replace("\\","/",$basePath);
	
	$fromPath=$basePath.'administrator/components/com_ajaxsearch/';
	
	
	
	$fsdirs=new JFolder();
	$fslink=new JFile();
	
	foreach($save as $bak){
		$new=str_replace(Array('.php','.xml','.html'),Array('.bak.php','.bak.xml','.bak.html'),$bak);
		$oldPath=$basePath.$bak;
		$newPath=$basePath.$new;
		
		if(!$fslink->copy($bak,$new,$basePath)){
			return false;
		}
	}	
	
	foreach($dirs as $dir){
		$folder=$basePath.$dir;
		
		if(!is_dir($folder)) $fsdirs->create($folder,0755);
		else return false;
	}
	
	
	foreach($copy as $src){
		$from=$fromPath.$src;
		$dest=$basePath.$src;

		if(!$fslink->copy($from,$dest)){
			return false;
		}
	}
	
	echo "<br />Package Files Installed";
	return true;
}

// Function to remove the files (remove files, remove dirs, restore originals)
function uncopyFiles($basePath){
	global $dirs,$save,$copy;
	
	if(subStr($basePath,-1)!='/')$basePath.='/';
	
	$basePath=str_replace("\\","/",$basePath);
	
	$fromPath=$basePath.'administrator/components/com_ajaxsearch/';
	
	$fsdirs=new JFolder();
	$fslink=new JFile();
	
	foreach($copy as $src){
		// Removes installed file
		$dest=$basePath.$src;
		$fslink->delete($dest);
		// Removes package file
		$dest=$fromPath.$src;
		$fslink->delete($dest);
	}
	
	foreach($dirs as $dir){
		$folder=$basePath.$dir;
		if(is_dir($folder)) $fsdirs->delete($folder);
	}
	
	foreach($save as $bak){
		$new=str_replace(Array('.php','.xml','.html'),Array('.bak.php','.bak.xml','.bak.html'),$bak);
		$oldPath=$basePath.$bak;
		$newPath=$basePath.$new;
		$fslink->copy($newPath,$oldPath);
	}
	echo "<br />Package files removed after error occured";
}

?>
