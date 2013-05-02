<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

function RoutesBuildRoute(&$query)
{
  $segments = array();

/*  if(isset($query['trailname']))
  {
    $segments[] = $query['trailname'];
    unset($query['trailname']);
  };
*/
  if(isset($query['tview']))
  {
    $segments[] = $query['tview'];
    unset($query['tview']);
  };
	
  if(isset($query['view']))
  {
    $segments[] = $query['view'];
    unset($query['view']);
  };
	
  return $segments;
}
function RoutesParseRoute($segments)
{
	$vars = array();
	switch($segments[1])
	{
		case 'map':
		{
			$id = explode( ':', $segments[0] );
			$vars['id'] = (int) $id[0];
			$vars['name'] = $id[1];
			$vars['view'] = $segments[1];
		} break;

		case 'traildisplay':
		{
			$id = explode( ':', $segments[0] );
			$vars['id'] = (int) $id[0];
			$vars['name'] = $id[1];
			$vars['view'] = 'traildisplay';
		} break;
	}

	return $vars;

}
?>
