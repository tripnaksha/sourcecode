<?php
/**
 * @version 1.0 $Id: default.php 784 2008-09-29 14:44:42Z schlu $
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

<script type="text/javascript">
	Window.onDomReady(function(){
		var form = document.getElementById('adminForm');
		var map = form.getElementById('map1');
		
		if(map.checked) {
			addrequired();
		}
	});
	
	function addrequired() {
		
		var form = document.getElementById('adminForm');
		
		$(form.street).addClass('required');
		$(form.plz).addClass('required');
		$(form.city).addClass('required');
		$(form.country).addClass('required');
	}
	
	function removerequired() {
		
		var form = document.getElementById('adminForm');
		
		$(form.street).removeClass('required');
		$(form.plz).removeClass('required');
		$(form.city).removeClass('required');
		$(form.country).removeClass('required');
	}

	function submitbutton( pressbutton ) {

		if (pressbutton == 'cancelvenue') {
			elsubmitform( pressbutton );
			return;
		}

		var form = document.getElementById('adminForm');
		var validator = document.formvalidator;
		var venue = $(form.venue).getValue();
		venue.replace(/\s/g,'');
		
		var map = form.getElementById('map1');
		var streetcheck = $(form.street).hasClass('required');
	
		//workaround cause validate strict doesn't allow and operator
		//and ie doesn't understand CDATA properly
		if (map.checked) {
			if(!streetcheck) {  
				addrequired();
			}
		}

		if (!map.checked) {
			if(streetcheck) {  
				removerequired();
			}
		}

		if ( venue.length==0 ) {
   			alert("<?php echo JText::_( 'ERROR ADD VENUE', true ); ?>");
   			validator.handleResponse(false,form.venue);
   			form.venue.focus();
   			return false;
   		} else if ( validator.validate(form.street) === false) {
   			alert("<?php echo JText::_( 'ERROR ADD STREET', true ); ?>");
   			validator.handleResponse(false,form.street);
   			form.street.focus();
   			return false;
		} else if ( validator.validate(form.plz) === false) {
   			alert("<?php echo JText::_( 'ERROR ADD ZIP', true ); ?>");
   			validator.handleResponse(false,form.plz);
   			form.plz.focus();
   			return false;
  		} else if ( validator.validate(form.city) === false) {
  			alert("<?php echo JText::_( 'ERROR ADD CITY', true ); ?>");
  			validator.handleResponse(false,form.city);
  			form.city.focus();
  			return false;
		} else if ( validator.validate(form.country) === false) {
   			alert("<?php echo JText::_( 'ERROR ADD COUNTRY', true ); ?>");
   			validator.handleResponse(false,form.country);
   			form.country.focus();
   			return false;
  		} else {
  			<?php
			// JavaScript for extracting editor text
			echo $this->editor->save( 'locdescription' );
			?>
			elsubmitform(pressbutton);

			return true;
		}
	}
	
	//joomla submitform needs form name
	function elsubmitform(pressbutton){
			
			var form = document.getElementById('adminForm');
			if (pressbutton) {
				form.task.value=pressbutton;
			}
			if (typeof form.onsubmit == "function") {
				form.onsubmit();
			}
			form.submit();
		}


	var tastendruck = false
	function rechne(restzeichen)
	{
		maximum = <?php echo $this->elsettings->datdesclimit; ?>

		if (restzeichen.locdescription.value.length > maximum) {
          	restzeichen.locdescription.value = restzeichen.locdescription.value.substring(0, maximum)
          	links = 0
		} else {
        	links = maximum - restzeichen.locdescription.value.length
        }
 		restzeichen.zeige.value = links
  	}

  	function berechne(restzeichen)
   	{
  		tastendruck = true
  		rechne(restzeichen)
  	}
</script>


<div id="eventlist" class="el_editvenue">

    <?php if ($this->params->def( 'show_page_title', 1 )) : ?>
    <h1 class="componentheading">
        <?php echo $this->params->get('page_title'); ?>
    </h1>
    <?php endif; ?>

    <form enctype="multipart/form-data" id="adminForm" action="<?php echo JRoute::_('index.php') ?>" method="post" class="form-validate">

		 <p class="clear"></p>

      	<fieldset class="el_fldst_address">

            <legend><?php echo JText::_('ADDRESS'); ?></legend>

            <div class="el_venue floattext">
                <label for="venue"><?php echo JText::_( 'VENUE' ).':'; ?></label>
                <input class="inputbox required" type="text" name="venue" id="venue" value="<?php echo $this->escape($this->row->venue); ?>" size="55" maxlength="50" />
            </div>

            <div class="el_street floattext">
                <label for="street"><?php echo JText::_( 'STREET' ).':'; ?></label>
                <input class="inputbox" type="text" name="street" id="street" value="<?php echo $this->escape($this->row->street); ?>" size="55" maxlength="50" />
            </div>

            <div class="el_plz floattext">
                <label for="plz"><?php echo JText::_( 'ZIP' ).':'; ?></label>
                <input class="inputbox" type="text" name="plz" id="plz" value="<?php echo $this->escape($this->row->plz); ?>" size="15" maxlength="10" />
            </div>

            <div class="el_city floattext">
                <label for="city"><?php echo JText::_( 'CITY' ).':'; ?></label>
                <input class="inputbox" type="text" name="city" id="city" value="<?php echo $this->escape($this->row->city); ?>" size="55" maxlength="50" />
            </div>

            <div class="el_state floattext">
                <label for="state"><?php echo JText::_( 'STATE' ).':'; ?></label>
                <input class="inputbox" type="text" name="state" id="state" value="<?php echo $this->escape($this->row->state); ?>" size="55" maxlength="50" />
            </div>

            <div class="el_country floattext">
                <label for="country"><?php echo JText::_( 'COUNTRY' ).':'; ?></label>
                <input class="inputbox" type="text" name="country" id="country" value="<?php echo $this->row->country; ?>" size="3" maxlength="2" />
                <span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTES' ); ?>::<?php echo JText::_('COUNTRY HINT'); ?>">
                		<?php echo $this->infoimage; ?>
                </span>
            </div>

            <div class="el_url floattext">
                <label for="url"><?php echo JText::_( 'WEBSITE' ).':'; ?></label>
                <input class="inputbox" name="url" id="url" type="text" value="<?php echo $this->escape($this->row->url); ?>" size="55" maxlength="199" />&nbsp;
                <span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTES' ); ?>::<?php echo JText::_('WEBSITE HINT'); ?>">
                		<?php echo $this->infoimage; ?>
                </span>
            </div>

            <?php if ( $this->elsettings->showmapserv != 0 ) : ?>
            <div class="el_map floattext">
                <p>
                    <br /><strong><?php echo JText::_( 'ENABLE MAP' ).':'; ?></strong>
                    <span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTES' ); ?>::<?php echo JText::_('ADDRESS NOTICE'); ?>">
                        <?php echo $this->infoimage; ?>
                    </span>
                </p>

                <label for="map0"><?php echo JText::_( 'no' ); ?></label>
                <input type="radio" name="map" id="map0" onchange="removerequired();" value="0" <?php echo $this->row->map == 0 ? 'checked="checked"' : ''; ?> class="inputbox" />
                <br class="clear" />
              	<label for="map1"><?php echo JText::_( 'yes' ); ?></label>
              	<input type="radio" name="map" id="map1" onchange="addrequired();" value="1" <?php echo $this->row->map == 1 ? 'checked="checked"' : ''; ?> class="inputbox" />
            </div>
            <?php endif; ?>

        </fieldset>

      	<?php	if (( $this->elsettings->imageenabled == 2 ) || ($this->elsettings->imageenabled == 1)) :	?>
      	<fieldset class="el_fldst_image">

            <legend><?php echo JText::_('IMAGE'); ?></legend>

    		<?php
            if ($this->row->locimage) :
    				echo ELOutput::flyer( $this->row, $this->limage );
    		else :
      		    echo JHTML::_('image', 'components/com_eventlist/assets/images/noimage.png', JText::_('NO IMAGE'), array('class' => 'modal'));
    		endif;
      		?>

            <label for="userfile"><?php echo JText::_('IMAGE'); ?></label>
      			<input class="inputbox <?php echo $this->elsettings->imageenabled == 2 ? 'required' : ''; ?>" name="userfile" id="userfile" type="file" />
      			<span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTES' ); ?>::<?php echo JText::_('MAX IMAGE FILE SIZE').' '.$this->elsettings->sizelimit.' kb'; ?>">
      				<?php echo $this->infoimage; ?>
      			</span>

      			<!--<?php echo JText::_( 'CURRENT IMAGE' );	?>
      			<?php echo JText::_( 'SELECTED IMAGE' ); ?>-->

      	</fieldset>
      	<?php endif; ?>

      	<fieldset class="el_fldst_description">

          	<legend><?php echo JText::_('DESCRIPTION'); ?></legend>

        		<?php
        		//wenn usertyp min editor wird editor ausgegeben ansonsten textfeld
        		if ( $this->editoruser ) :
        			echo $this->editor->display('locdescription', $this->row->locdescription, '655', '400', '70', '15', array('pagebreak', 'readmore') );
        		else :
        		?>
      			<textarea style="width:100%;" rows="10" name="locdescription" class="inputbox" wrap="virtual" onkeyup="berechne(this.form)"></textarea><br />
      			<?php echo JText::_('NO HTML'); ?><br />
      			<input disabled="disabled" value="<?php echo $this->elsettings->datdesclimit; ?>" size="4" name="zeige" /><?php echo JText::_('AVAILABLE')." "; ?><br />
      			<a href="javascript:rechne(document.adminForm);"><?php echo JText::_('REFRESH'); ?></a>

        		<?php	endif; ?>

      	</fieldset>

      	<fieldset class="el_fldst_meta">

          	<legend><?php echo JText::_('METADATA INFORMATION'); ?></legend>

            <div class="el_box_left">
              	<label for="metadesc"><?php echo JText::_( 'META DESCRIPTION' ); ?></label>
          		<textarea class="inputbox" cols="40" rows="5" name="meta_description" id="metadesc" style="width:250px;"></textarea>
            </div>

            <div class="el_box_right">
        		<label for="metakey"><?php echo JText::_( 'META KEYWORDS' ); ?></label>
        		<textarea class="inputbox" cols="40" rows="5" name="meta_keywords" id="metakey" style="width:250px;"></textarea>
            </div>

            <br class="clear" />
            
    		<input type="button" class="button el_fright" value="<?php echo JText::_( 'ADD VENUE CITY' ); ?>" onclick="f=document.getElementById('adminForm');f.metakey.value=f.venue.value+', '+f.city.value+f.metakey.value;" />

      	</fieldset>
<!--  removed to avoid double posts in ie7
      	<div class="el_save_buttons floattext">
    		<button type="button" onclick="return submitbutton('savevenue')">
    			<?php echo JText::_('SAVE') ?>
    		</button>
    		<button type="reset" onclick="return submitbutton('cancelvenue')">
    			<?php echo JText::_('CANCEL') ?>
    		</button>
		</div>
-->	

        <div class="el_save_buttons floattext">
  			<button type="button" onclick="return submitbutton('savevenue')">
  				<?php echo JText::_('SAVE') ?>
  			</button>
  			<button type="reset" onclick="return submitbutton('cancelvenue')">
  				<?php echo JText::_('CANCEL') ?>
  			</button>
	</div>
	<p class="clear">
      	<input type="hidden" name="option" value="com_eventlist" />
      	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
      	<input type="hidden" name="referer" value="<?php echo @$_SERVER['HTTP_REFERER']; ?>" />
      	<input type="hidden" name="created" value="<?php echo $this->row->created; ?>" />
      	<input type="hidden" name="curimage" value="<?php echo $this->row->locimage; ?>" />
      	<?php echo JHTML::_( 'form.token' ); ?>
      	<input type="hidden" name="task" value="" />
      	</p>

    </form>

    <p class="copyright">
        <?php echo ELOutput::footer( ); ?>
    </p>

</div>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>
