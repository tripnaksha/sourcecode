<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$state      = &$this->get('State');
$result     = $state->get('result');
$message    = $state->get('message');
?>
<table class="adminform">
<tr>
	<td align="left">
	<strong><?php echo $message; ?></strong>
	</td>
</tr>
<tr>
	<td colspan="2" align="center">
	[&nbsp;<a href="<?php echo $this->url; ?>" style="font-size: 16px; font-weight: bold">Continue ...</a>&nbsp;]
	</td>
</tr>
</table>
