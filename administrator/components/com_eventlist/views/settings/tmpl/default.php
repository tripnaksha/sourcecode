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

defined('_JEXEC') or die('Restricted access');
?>

<form action="index.php" method="post" name="adminForm">

    	<div id="elconfig-document">
			<div id="page-basic">
				<?php require_once(dirname(__FILE__).DS.'el.settings_basic.html'); ?>
			</div>

			<div id="page-usercontrol">
				<?php require_once(dirname(__FILE__).DS.'el.settings_usercontrol.html'); ?>
			</div>

			<div id="page-details">
				<?php require_once(dirname(__FILE__).DS.'el.settings_detailspage.html'); ?>
			</div>

			<div id="page-layout">
				<?php require_once(dirname(__FILE__).DS.'el.settings_layout.html'); ?>
			</div>

			<div id="page-parameters">
				<?php require_once(dirname(__FILE__).DS.'el.settings_parameters.html'); ?>
			</div>
		</div>
		<div class="clr"></div>

		<?php echo JHTML::_( 'form.token' ); ?>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="id" value="1">
		<input type="hidden" name="lastupdate" value="<?php echo $this->elsettings->lastupdate; ?>">
		<input type="hidden" name="option" value="com_eventlist">
		<input type="hidden" name="controller" value="settings">
		</form>

		<p class="copyright">
			<?php echo ELAdmin::footer( ); ?>
		</p>