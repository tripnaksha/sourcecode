<?php
/**
 * @version 1.0 $Id: default.php 662 2008-05-09 22:28:53Z schlu $
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

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div id="eventlist" class="el_eventlist">
<!--p class="buttons">
	<?php
		echo ELOutput::printbutton( $this->print_link, $this->params );
	?>
</p-->

<h1 class="componentheading">
	<?php echo "All Trips This Weekend";?>
</h1>

<!--table-->

<?php echo $this->loadTemplate('table'); ?>

<p>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</p>
</form>

<!--footer-->

<?php if (( $this->page > 0 ) && ( !$this->params->get( 'popup' ) )) : ?>
<div class="pageslinks">
	<?php echo $this->pageNav->getPagesLinks(); ?>
</div>

<p class="pagescounter">
	<?php echo $this->pageNav->getPagesCounter(); ?>
</p>
<?php endif; ?>

<p class="copyright">
	<?php echo ELOutput::footer( ); ?>
</p>

</div>
