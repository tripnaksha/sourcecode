<?php
/**
 * @param	array
 * @return	array
 */
function TagBuildRoute( &$query )
{
	//print_r($query);

	$segments = array();

	if (isset($query['tag'])) {
		$segments[] = $query['tag'];
		unset($query['tag']);
	}

	if (isset($query['view'])) {
		unset($query['view']);
	}
	if(isset($query['task'])&&$query['task']=='tag'){
		unset($query['task']);
	}

	if(isset($query['layout'])){
		unset($query['layout']);
	}
	return $segments;
}

/**
 * @param	array
 * @return	array
 */
function TagParseRoute( $segments )
{
	//print_r($segments);
	
	$vars = array();
	$tag	= array_shift($segments);
	$vars['tag'] = $tag;
	$vars['view'] = 'tag';
	return $vars;
}