<?php
/**
 * @version 1.0 $Id: default_attendees.php 662 2008-05-09 22:28:53Z schlu $
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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<h2 class="register"><?php echo JText::_( 'REGISTERED USERS' ).':'; ?></h2>

<div class="register">
<?php
//only set style info if users allready have registered
if ($this->registers) :
?>
	<ul class="user floattext">

<?php
//loop through attendees
foreach ($this->registers as $register) :

	//if CB
	if ($this->elsettings->comunsolution == 1) :

		$thumb_path = 'images/comprofiler/tn';
		$no_photo 	= ' alt="'.$register->name.'"';

		if ($this->elsettings->comunoption == 1) :
			//User has avatar
			if(!empty($register->avatar)) :
				echo "<li><a href='".JRoute::_('index.php?option=com_comprofiler&task=userProfile&user='.$register->uid )."'><img src=".$thumb_path.$register->avatar.$no_photo." alt='no photo' /><span class='username'>".$register->name."</span></a></li>";

			//User has no avatar
			else :
				echo "<li><a href='".JRoute::_( 'index.php?option=com_comprofiler&task=userProfile&user='.$register->uid )."'><img src=\"components/com_comprofiler/images/english/tnnophoto.jpg\" alt=\"no photo\" /><span class='username'>".$register->name."</span></a></li>";
			endif;
		endif;

		//only show the username with link to profile
		if ($this->elsettings->comunoption == 0) :
			echo "<li><span class='username'><a href='".JRoute::_( 'index.php?option=com_comprofiler&amp;task=userProfile&amp;user='.$register->uid )."'>".$register->name." </a></span></li>";
		endif;

	//if CB end - if not CB than only name
	endif;

	//no communitycomponent is set so only show the username
	if ($this->elsettings->comunsolution == 0) :
		echo "<li><span class='username'>".$register->name."</span></li>";
	endif;

//end loop through attendees
endforeach;
?>

	</ul>
<?php endif; ?>

<?php
switch ($this->formhandler) {

	case 1:
		echo JText::_( 'TOO LATE REGISTER' );
	break;

	case 2:
		echo JText::_( 'LOGIN FOR REGISTER' );
	break;

	case 3:

		//the user is allready registered. Let's check if he can unregister from the event
		if ($this->row->unregistra == 0) :

			//no he is not allowed to unregister
			echo JText::_( 'ALLREADY REGISTERED' );

		else:

			//he is allowed to unregister -> display form
			?>
			<form id="Eventlist" action="<?php echo JRoute::_('index.php'); ?>" method="post">
				<p>
					<?php echo JText::_( 'UNREGISTER BOX' ).': '; ?>
					<input type="checkbox" name="reg_check" onclick="check(this, document.getElementById('el_send_attend'))" />
				</p>
				<p>
					<input type="submit" id="el_send_attend" name="el_send_attend" value="<?php echo JText::_( 'UNREGISTER' ); ?>" disabled="disabled" />
				</p>
				<p>
					<input type="hidden" name="rdid" value="<?php echo $this->row->did; ?>" />
					<?php echo JHTML::_( 'form.token' ); ?>
					<input type="hidden" name="task" value="delreguser" />
				</p>
			</form>
			<?php
		endif;

	break;

	case 4:

		//the user is not registered allready -> display registration form
		?>
		<form id="Eventlist" action="<?php echo JRoute::_('index.php'); ?>" method="post">
			<p>
				<?php echo JText::_( 'I WILL GO' ).': '; ?>
				<input type="checkbox" name="reg_check" onclick="check(this, document.getElementById('el_send_attend'))" />
			</p>
		<p>
			<input type="submit" id="el_send_attend" name="el_send_attend" value="<?php echo JText::_( 'REGISTER' ); ?>" disabled="disabled" />
		</p>
		<p>
			<input type="hidden" name="rdid" value="<?php echo $this->row->did; ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
			<input type="hidden" name="task" value="userregister" />
		</p>
		</form>
		<?php
	break;
}
?>
</div>