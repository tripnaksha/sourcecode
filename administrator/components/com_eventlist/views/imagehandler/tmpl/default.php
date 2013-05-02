<?php
/**
 * @version 1.0 $Id: default.php 689 2008-06-21 03:49:43Z schlu $
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');
?>
<form action="index.php" method="post" name="adminForm">
<div class="imghead">

	<?php echo JText::_( 'SEARCH' ).' '; ?>
	<input type="text" name="search" id="search" value="<?php echo $this->search; ?>" class="text_area" onChange="document.adminForm.submit();" />
	<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
	<button onclick="this.form.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>

</div>

<div class="imglist">

		<?php
		for ($i = 0, $n = count($this->images); $i < $n; $i++) :
			$this->setImage($i);
			echo $this->loadTemplate('image');
		endfor;
		?>
</div>

<div class="clear"></div>
		
<div class="pnav"><?php echo $this->pageNav->getListFooter(); ?></div>

	<input type="hidden" name="option" value="com_eventlist" />
	<input type="hidden" name="view" value="imagehandler" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
</form>