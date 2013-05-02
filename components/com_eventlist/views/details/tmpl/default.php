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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<script language="javascript" type="text/javascript">
	window.addEvent('domready',function(){
		if ($('mapview')) {
		$('mapview').addEvent('click',function(){
			 $('mapframe').style.height = '500px';
			 $('mapframe').style.width = '500px';
			 $('inner_contentColumn_full').style.height = $('inner_contentColumn_full').offsetHeight + 100 + 'px';
		})}
	});
</script>

<div id="eventlist" class="event_id<?php echo $this->row->did; ?> el_details">
	<!--p class="buttons">
			<?php echo ELOutput::mailbutton( $this->row->slug, 'details', $this->params ); ?>
			<?php echo ELOutput::printbutton( $this->print_link, $this->params ); ?>
	</p-->

<?php if ($this->params->def( 'show_page_title', 1 )) : ?>
	<h1 class="componentheading">
		<?php //echo $this->params->get('page_title'); ?>
		<?php echo $this->escape($this->row->title); ?>
	</h1>
<?php endif; ?>

<!-- Details EVENT -->
	<h2 class="eventlist">
		<?php
    	echo JText::_( 'EVENT' );
//    	echo '&nbsp;'.ELOutput::editbutton($this->item->id, $this->row->did, $this->params, $this->allowedtoeditevent, 'editevent' );
    	?>
	</h2>

	<?php //flyer
	echo ELOutput::flyer( $this->row, $this->dimage, 'event' );
	?>

	<dl class="event_info " style="width:70%;float:left;">

		<?php if ($this->elsettings->showdetailstitle == 1) : ?>
			<dt class="title"><?php echo JText::_( 'TITLE' ).':'; ?></dt>
    		<dd class="title"><?php echo $this->escape($this->row->title); ?>
    		&nbsp;&nbsp;&nbsp;
    		</dd>
</a>
		<?php
  		endif;
  		?>
  		<dt class="when"><?php echo JText::_( 'WHEN' ).':'; ?></dt>
		<dd class="when">
			<?php
			echo ELOutput::formatdate($this->row->dates, $this->row->times);

    		if ($this->row->enddates) :
    			echo ' - '.ELOutput::formatdate($this->row->enddates, $this->row->endtimes);
    		endif;

    		if ($this->elsettings->showtimedetails == 1) :

				echo '&nbsp;'.ELOutput::formattime($this->row->dates, $this->row->times);

				if ($this->row->endtimes) :
					echo ' - '.ELOutput::formattime($this->row->enddates, $this->row->endtimes);
				endif;
			endif;
			?>
		</dd>
  		<?php
  		if ($this->row->locid != 0) :
  		?>
		    <dt class="where"><?php echo JText::_( 'WHERE' ).':'; ?></dt>
		    <dd class="where">
    		<?php if (($this->elsettings->showdetlinkvenue == 1) && (!empty($this->row->url))) : ?>

			    <a href="<?php echo $this->row->url; ?>"><?php echo $this->escape($this->row->venue); ?></a> -

			<?php elseif ($this->elsettings->showdetlinkvenue == 2) : ?>

			    <!--a href="<?php echo JRoute::_( 'index.php?view=venueevents&id='.$this->row->venueslug ); ?>"><?php echo $this->row->venue; ?></a-->
			    <?php echo $this->row->venue . " ( " ; ?>
			    <a id="mapview" href="#">View this trail</a>
			    <a href="<?php //echo JURI::base() . 'index.php?tview=' . $this->row->locid ; ?>"><?php //echo "View this trail" ?></a> 
			    <?php echo " )" ; ?>

			<?php //elseif ($this->elsettings->showdetlinkvenue == 0) :

				//echo $this->escape($this->row->venue).' - ';

			endif;

//			echo $this->escape($this->row->city); ?>

			</dd>

		<?php endif; ?>

		<dt class="category"><?php echo JText::_( 'CATEGORY' ).':'; ?></dt>
    		<dd class="category">
				<?php echo "<a href='".JRoute::_( 'index.php?view=categoryevents&id='.$this->row->categoryslug )."'>".$this->escape($this->row->catname)."</a>";?>
			</dd>
	</dl>
	<div style="width:25%;padding-left:20px;float:left">
            <a href="http://twitter.com/share" style="margin-left:15px" class="twitter-share-button" data-count="none" data-via="tripnaksha">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
             <iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo "http://".urlencode($_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);?>&layout=button_count&show_faces=false&width=80&action=like&font=verdana&colorscheme=light" scrolling="no" frameborder="0" style="margin-left:15px; margin-bottom:-2px; border:none; overflow:hidden; width:80px; height:22px" allowTransparency="true"></iframe>
            <a href="index.php?option=com_eventlist&view=eventlist&Itemid=46" class="button classy business-plan"><span>View Upcoming Trips</span></a>
	</div>
	<div style="clear:both;">&nbsp;</div>
			<div class="trailview" style="text-align:center;">
				<iframe id="mapframe" src ="index.php?option=com_trailembed&amp;tmpl=component&amp;tview=<?php echo $this->row->locid;?>&amp;theight=475&amp;twidth=475&amp;tkey=ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQkUZHNJdizg5ywABG1vcOZLnlKKRQiK1QyIYbC7QJYSAvZi_ftqMywEg" width="0" height="0">
				<p>Your browser does not support iframes.</p>
				</iframe>
			</div>

			<div id="pictures"></div> 
			<div style="text-align:center">Powered by <img src="https://www.google.com/uds/css/small-logo.png" class="gsc-branding-img-noclear"></div>

  	<?php if ($this->elsettings->showevdescription == 1) : ?>

  	    <h2 class="description"><?php echo JText::_( 'DESCRIPTION' ); ?></h2>
  		<div class="description event_desc">
  			<?php echo $this->row->datdescription; ?>
  		</div>

  	<?php endif; ?>

<!--  	Venue  -->

	<?php if ($this->row->locid != 0) : ?>

		<h2 class="location">
			<?php echo JText::_( 'VENUE' ) ; ?>
  			<?php //echo ELOutput::editbutton($this->item->id, $this->row->locid, $this->params, $this->allowedtoeditvenue, 'editvenue' ); ?>
		</h2>

		<?php //flyer
		echo ELOutput::flyer( $this->row, $this->limage );
		echo ELOutput::mapicon( $this->row );
		?>

		<dl class="location floattext">
			 <dt class="venue"><?php echo $this->elsettings->locationname.':'; ?></dt>
				<dd class="venue">
				<?php echo "<a href='".JRoute::_( 'index.php?view=venueevents&id='.$this->row->venueslug )."'>".$this->escape($this->row->venue)."</a>"; ?>

				<?php if (!empty($this->row->url)) : ?>
					&nbsp; - &nbsp;
					<a href="<?php echo $this->row->url; ?>"> <?php echo JText::_( 'WEBSITE' ); ?></a>
				<?php
				endif;
				?>
				</dd>

			<?php
  			if ( $this->elsettings->showdetailsadress == 1 ) :
  			?>

  				<?php if ( $this->row->street ) : ?>
  				<dt class="venue_street"><?php echo JText::_( 'STREET' ).':'; ?></dt>
				<dd class="venue_street">
    				<?php echo $this->escape($this->row->street); ?>
				</dd>
				<?php endif; ?>

				<?php if ( $this->row->plz ) : ?>
  				<dt class="venue_plz"><?php echo JText::_( 'ZIP' ).':'; ?></dt>
				<dd class="venue_plz">
    				<?php echo $this->escape($this->row->plz); ?>
				</dd>
				<?php endif; ?>

				<?php if ( $this->row->city ) : ?>
    			<dt class="venue_city"><?php echo JText::_( 'CITY' ).':'; ?></dt>
    			<dd class="venue_city">
    				<?php echo $this->escape($this->row->city); ?>
    			</dd>
    			<?php endif; ?>

    			<?php if ( $this->row->state ) : ?>
    			<dt class="venue_state"><?php echo JText::_( 'STATE' ).':'; ?></dt>
    			<dd class="venue_state">
    				<?php echo $this->escape($this->row->state); ?>
    			</dd>
				<?php endif; ?>

				<?php if ( $this->row->country ) : ?>
				<dt class="venue_country"><?php echo JText::_( 'COUNTRY' ).':'; ?></dt>
    			<dd class="venue_country">
    				<?php echo $this->row->countryimg ? $this->row->countryimg : $this->row->country; ?>
    			</dd>
    			<?php endif; ?>
			<?php
			endif;
			?>
		</dl>

		<?php if ($this->elsettings->showlocdescription == 1) :	?>

			<h2 class="location_desc"><?php echo JText::_( 'DESCRIPTION' ); ?></h2>
  			<div class="description location_desc">
  				<?php echo $this->row->locdescription;	?>
  			</div>

		<?php endif; ?>

	<?php
	//row->locid !=0 end
	endif;
	?>

	<?php if ($this->row->registra == 1) : ?>

		<!-- Registration -->
		<?php echo $this->loadTemplate('attendees'); ?>

	<?php endif; ?>

	<?php if ($this->elsettings->commentsystem != 0) :	?>

		<!-- Comments -->
		<?php echo $this->loadTemplate('comments'); ?>

  	<?php endif; ?>

<p class="copyright">
	<?php echo ELOutput::footer( ); ?>
</p>
</div>
