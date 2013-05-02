<?php
/**
 * @version 1.0 $Id: output.class.php 750 2008-07-25 09:55:23Z schlu $
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

/**
 * Holds the logic for all output related things
 *
 * @package Joomla
 * @subpackage EventList
 */
class ELOutput {

	/**
	* Writes footer. Official copyright! Do not remove!
	*
	* @author Christoph Lukes
	* @since 0.9
	*/
	function footer( )
	{
		echo 'EventList powered by <a href="http://www.schlu.net">schlu.net</a>';
	}

	/**
	* Writes Event submission button
	*
	* @author Christoph Lukes
	* @since 0.9
	*
	* @param int $dellink Access of user
	* @param array $params needed params
	* @param string $view the view the user will redirected to
	**/
	function submitbutton( $dellink, &$params )
	{
		if ($dellink == 1) {

			JHTML::_('behavior.tooltip');

			if ( $params->get('icons') ) {
				$image = JHTML::_('image.site', 'submitevent.png', 'components/com_eventlist/assets/images/', NULL, NULL, JText::_( 'DELIVER NEW EVENT' ));
			} else {
				$image = JText::_( 'DELIVER NEW EVENT' );
			}

			$link 		= 'index.php?view=editevent';
			$overlib 	= JText::_( 'SUBMIT EVENT TIP' );
			$output		= '<a href="'.JRoute::_($link).'" class="editlinktip hasTip" title="'.JText::_( 'DELIVER NEW EVENT' ).'::'.$overlib.'">'.$image.'</a>';

			return $output;
		}

		return;
	}

	/**
	* Writes Archivebutton
	*
	* @author Christoph Lukes
	* @since 0.9
	*
	* @param int $oldevent Archive used or not
	* @param array $params needed params
	* @param string $task The current task
	* @param int $categid The cat id
	*/
	function archivebutton( &$params, $task = NULL, $id = NULL )
	{

		$settings = & ELHelper::config();
		
		if ( $settings->oldevent == 2 ) {

			JHTML::_('behavior.tooltip');
			
			$view = JRequest::getWord('view');
			
			if ($task == 'archive') {
				
				if ( $params->get('icons') ) {
					$image = JHTML::_('image.site', 'eventlist.png', 'components/com_eventlist/assets/images/', NULL, NULL, JText::_( 'SHOW EVENTS' ));
				} else {
					$image = JText::_( 'SHOW EVENTS' );
				}
				$overlib 	= JText::_( 'SHOW EVENTS TIP' );
				$title 		= JText::_( 'SHOW EVENTS' );
				
				if ($id) {
						$link 		= JRoute::_( 'index.php?view='.$view.'&id='.$id );
				} else {
						$link 		= JRoute::_( 'index.php' );
				}
				
			} else {
				
				if ( $params->get('icons') ) {
					$image = JHTML::_('image.site', 'archive_front.png', 'components/com_eventlist/assets/images/', NULL, NULL, JText::_( 'SHOW ARCHIVE' ));
				} else {
					$image = JText::_( 'SHOW ARCHIVE' );
				}
				$overlib 	= JText::_( 'SHOW ARCHIVE TIP' );
				$title 		= JText::_( 'SHOW ARCHIVE' );
					
				if ($id) {
					$link 		= JRoute::_( 'index.php?view='.$view.'&id='.$id.'&task=archive' );
				} else {
					$link		= JRoute::_('index.php?view='.$view.'&task=archive');
				}
			}

			$output = '<a href="'.$link.'" class="editlinktip hasTip" title="'.$title.'::'.$overlib.'">'.$image.'</a>';

			return $output;

		}
		return;
	}

	/**
	 * Creates the edit button
	 *
	 * @param int $Itemid
	 * @param int $id
	 * @param array $params
	 * @param int $allowedtoedit
	 * @param string $view
	 * @since 0.9
	 */
	function editbutton( $Itemid, $id, &$params, $allowedtoedit, $view)
	{

		if ( $allowedtoedit ) {

			JHTML::_('behavior.tooltip');

			switch ($view)
			{
				case 'editevent':
					if ( $params->get('icons') ) {
						$image = JHTML::_('image.site', 'calendar_edit.png', 'components/com_eventlist/assets/images/', NULL, NULL, JText::_( 'EDIT EVENT' ));
					} else {
						$image = JText::_( 'EDIT EVENT' );
					}
					$overlib = JText::_( 'EDIT EVENT TIP' );
					$text = JText::_( 'EDIT EVENT' );
					break;

				case 'editvenue':
					if ( $params->get('icons') ) {
						$image = JHTML::_('image.site', 'calendar_edit.png', 'components/com_eventlist/assets/images/', NULL, NULL, JText::_( 'EDIT EVENT' ));
					} else {
						$image = JText::_( 'EDIT VENUE' );
					}
					$overlib = JText::_( 'EDIT VENUE TIP' );
					$text = JText::_( 'EDIT VENUE' );
					break;
			}

			$link 	= 'index.php?view='.$view.'&id='.$id.'&returnid='.$Itemid;
			$output	= '<a href="'.JRoute::_($link).'" class="editlinktip hasTip" title="'.$text.'::'.$overlib.'">'.$image.'</a>';

			return $output;
		}

		return;
	}

	/**
	 * Creates the print button
	 *
	 * @param string $print_link
	 * @param array $params
	 * @since 0.9
	 */	
	function printbutton( $print_link, &$params )
	{
		if ($params->get( 'show_print_icon' )) {

			JHTML::_('behavior.tooltip');

			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

			// checks template image directory for image, if non found default are loaded
			if ( $params->get( 'icons' ) ) {
				$image = JHTML::_('image.site', 'printButton.png', 'images/M_images/', NULL, NULL, JText::_( 'Print' ));
			} else {
				$image = JText::_( 'Print' );
			}

			if (JRequest::getInt('pop')) {
				//button in popup
				$output = '<a href="#" onclick="window.print();return false;">'.$image.'</a>';
			} else {
				//button in view
				$overlib = JText::_( 'PRINT TIP' );
				$text = JText::_( 'Print' );

				$output	= '<a href="'. JRoute::_($print_link) .'" class="editlinktip hasTip" onclick="window.open(this.href,\'win2\',\''.$status.'\'); return false;" title="'.$text.'::'.$overlib.'">'.$image.'</a>';
			}

			return $output;
		}
		return;
	}

	/**
	 * Creates the email button
	 *
	 * @param object $slug
	 * @param array $params
	 * @since 0.9
	 */
	function mailbutton($slug, $view, $params)
	{
		if ($params->get('show_email_icon')) {

			JHTML::_('behavior.tooltip');
			$uri    =& JURI::getInstance();
			$base  	= $uri->toString( array('scheme', 'host', 'port'));
			$link 	= $base.JRoute::_( 'index.php?view='.$view.'&id='.$slug, false );
			$url	= 'index.php?option=com_mailto&tmpl=component&link='.base64_encode( $link );
			$status = 'width=400,height=300,menubar=yes,resizable=yes';

			if ($params->get('icons')) 	{
				$image = JHTML::_('image.site', 'emailButton.png', 'images/M_images/', NULL, NULL, JText::_( 'EMAIL' ));
			} else {
				$image = JText::_( 'EMAIL' );
			}

			$overlib = JText::_( 'EMAIL TIP' );
			$text = JText::_( 'EMAIL' );

			$output	= '<a href="'. JRoute::_($url) .'" class="editlinktip hasTip" onclick="window.open(this.href,\'win2\',\''.$status.'\'); return false;" title="'.$text.'::'.$overlib.'">'.$image.'</a>';

			return $output;
		}
		return;
	}

	/**
	 * Creates the map button
	 *
	 * @param obj $data
	 * @param obj $settings
	 *
	 * @since 0.9
	 */
	function mapicon($data)
	{
		$settings = & ELHelper::config();
		
		//Link to map
		$mapimage = JHTML::_('image.site', 'mapsicon.png', 'components/com_eventlist/assets/images/', NULL, NULL, JText::_( 'MAP' ));

		//set var
		$output 	= null;
		$attributes = null;

		//stop if disabled
		if (!$data->map) {
			return $output;
		}
		
		$data->country = JString::strtoupper($data->country);

		//google or map24
		switch ($settings->showmapserv)
		{
			case 1:
			{
  				if ($settings->map24id) {

				$url		= 'http://link2.map24.com/?lid='.$settings->map24id.'&amp;maptype=JAVA&amp;width0=2000&amp;street0='.$data->street.'&amp;zip0='.$data->plz.'&amp;city0='.$data->city.'&amp;country0='.$data->country.'&amp;sym0=10280&amp;description0='.$data->venue;
				$output		= '<a class="map" title="'.JText::_( 'MAP' ).'" href="'.$url.'" target="_blank">'.$mapimage.'</a>';

  				}
			} break;

			case 2:
			{
				if($settings->gmapkey) {

					$document 	= & JFactory::getDocument();
					JHTML::_('behavior.mootools');

					//TODO: move map into squeezebox
					//TODO: temporary fix (v=2.115) for the gmaps issue caused by a bug in the gmaps api..set back when google finaly was able to fix this
					$document->addScript($this->baseurl.'/components/com_eventlist/assets/js/gmapsoverlay.js');
					$document->addScript('http://maps.google.com/maps?file=api&amp;v=2.115&amp;key='.trim($settings->gmapkey));
  					$document->addStyleSheet($this->baseurl.'/components/com_eventlist/assets/css/gmapsoverlay.css', 'text/css');

					$url		= 'http://maps.google.com/maps?q='.str_replace(" ", "+", $data->street).', '.$data->plz.' '.str_replace(" ", "+", $data->city).', '.$data->country.'&amp;venue='.$data->venue;
					$attributes = ' rel="gmapsoverlay"';
				} else {
					$url		= 'http://maps.google.com/maps?q='.str_replace(" ", "+", $data->street).', '.$data->plz.' '.str_replace(" ", "+", $data->city).', '.$data->country;
				}

				$output		= '<a class="map" title="'.JText::_( 'MAP' ).'" href="'.$url.'"'.$attributes.'>'.$mapimage.'</a>';

			} break;
		}

		return $output;
	}

	/**
	 * Creates the flyer
	 *
	 * @param obj $data
	 * @param obj $settings
	 * @param array $image
	 * @param string $type
	 *
	 * @since 0.9
	 */
	function flyer( $data, $image, $type = 'venue' )
	{
		$settings = & ELHelper::config();

		//define the environment based on the type
		if ($type == 'event') {
			$folder		= 'events';
			$imagefile	= $data->datimage;
			$info		= $data->title;
		} else {
			$folder 	= 'venues';
			$imagefile	= $data->locimage;
			$info		= $data->venue;
		}

		//do we have an image?
		if (empty($imagefile)) {

			//nothing to do
			return;

		} else {

			jimport('joomla.filesystem.file');

			//does a thumbnail exist?
			if (JFile::exists(JPATH_SITE.DS.'images'.DS.'eventlist'.DS.$folder.DS.'small'.DS.$imagefile)) {

				if ($settings->lightbox == 0) {

					$url		= '#';
					$attributes	= 'class="modal" onclick="window.open(\''.$this->baseurl.'/'.$image['original'].'\',\'Popup\',\'width='.$image['width'].',height='.$image['height'].',location=no,menubar=no,scrollbars=no,status=no,toolbar=no,resizable=no\')"';

				} else {

					JHTML::_('behavior.modal');

					$url		= $this->baseurl.'/'.$image['original'];
					$attributes	= 'class="modal" title="'.$info.'"';

				}

				$icon	= '<img src="'.$this->baseurl.'/'.$image['thumb'].'" width="'.$image['thumbwidth'].'" height="'.$image['thumbheight'].'" alt="'.$info.'" title="'.JText::_( 'CLICK TO ENLARGE' ).'" />';
				$output	= '<a href="'.$url.'" '.$attributes.'>'.$icon.'</a>';

			//No thumbnail? Then take the in the settings specified values for the original
			} else {

				$output	= '<img class="modal" src="'.$this->baseurl.'/'.$image['original'].'" width="'.$image['width'].'" height="'.$image['height'].'" alt="'.$info.'" />';

			}
		}

		return $output;
	}

	/**
	 * Creates the country flag
	 *
	 * @param string $country
	 *
	 * @since 0.9
	 */
	function getFlag($country)
	{
        $country = JString::strtolower($country);

        jimport('joomla.filesystem.file');

        if (JFile::exists(JPATH_COMPONENT_SITE.DS.'assets'.DS.'images'.DS.'flags'.DS.$country.'.gif')) {
        	$countryimg = '<img src="'.JURI::base(true).'/components/com_eventlist/assets/images/flags/'.$country.'.gif" alt="'.JText::_( 'COUNTRY' ).': '.$country.'" width="16" height="11" />';

        	return $countryimg;
        }

        return null;
	}
	
	/**
	 * Formats date
	 *
	 * @param string $date
	 * @param string $time
	 * 
	 * @return string $formatdate
	 *
	 * @since 0.9
	 */
	function formatdate($date, $time)
	{
		$settings = & ELHelper::config();
		
		if(!$date) {
			return;
		}
		
		if(!$time) {
			$time = '00:00:00';
		}
		
		//Format date
		$formatdate = strftime( $settings->formatdate, strtotime( $date.' '.$time ));
		
		return $formatdate;
	}
	
	/**
	 * Formats time
	 *
	 * @param string $date
	 * @param string $time
	 * 
	 * @return string $formattime
	 *
	 * @since 0.9
	 */
	function formattime($date, $time)
	{
		$settings = & ELHelper::config();
		
		if(!$time) {
			return;
		}
		
		//Format time
		$formattime = strftime( $settings->formattime, strtotime( $date.' '.$time ));
		$formattime .= ' '.$settings->timename;
		
		return $formattime;
	}
}
?>