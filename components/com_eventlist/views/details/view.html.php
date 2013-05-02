<?php
/**
 * @version 1.1 $Id: view.html.php 709 2008-07-01 09:44:44Z schlu $
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

jimport( 'joomla.application.component.view');

/**
 * HTML Details View class of the EventList component
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class EventListViewDetails extends JView
{
	/**
	 * Creates the output for the details view
	 *
 	 * @since 0.9
	 */
	function display($tpl = null)
	{
		global $mainframe;

		$document 	= & JFactory::getDocument();
		$user		= & JFactory::getUser();
		$dispatcher = & JDispatcher::getInstance();
		$elsettings = & ELHelper::config();

		$row		= & $this->get('Details');
		$registers	= & $this->get('Registers');
		$regcheck	= & $this->get('Usercheck');

		//get menu information
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams('com_eventlist');

		//Check if the id exists
		if ($row->did == 0)
		{
			return JError::raiseError( 404, JText::sprintf( 'Event #%d not found', $row->did ) );
		}

		//Check if user has access to the details
		if ($elsettings->showdetails == 0) {
			return JError::raiseError( 403, JText::_( 'NO ACCESS' ) );
		}

		//add css file
		$document->addStyleSheet($this->baseurl.'/components/com_eventlist/assets/css/eventlist.css');
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		//Print
		$pop	= JRequest::getBool('pop');

		$params->def( 'page_title', JText::_( 'DETAILS' ));

		if ( $pop ) {
			$params->set( 'popup', 1 );
		}

		$print_link = JRoute::_('index.php?view=details&id='.$row->slug.'&pop=1&tmpl=component');

		//pathway
		$pathway 	= & $mainframe->getPathWay();
		$pathway->addItem( JText::_( 'DETAILS' ). ' - '.$row->title, JRoute::_('index.php?view=details&id='.$row->slug));

		//Get images
		$dimage = ELImage::flyercreator($row->datimage, 'event');
		$limage = ELImage::flyercreator($row->locimage);

		//Check user if he can edit
		$allowedtoeditevent = ELUser::editaccess($elsettings->eventowner, $row->created_by, $elsettings->eventeditrec, $elsettings->eventedit);
		$allowedtoeditvenue = ELUser::editaccess($elsettings->venueowner, $row->venueowner, $elsettings->venueeditrec, $elsettings->venueedit);

		//Timecheck for registration
		$jetzt = date("Y-m-d");
		$now = strtotime($jetzt);
		$date = strtotime($row->dates);
		$timecheck = $now - $date;

		//let's build the registration handling
		$formhandler  = 0;

		//is the user allready registered at the event
		if ( $regcheck ) {
			$formhandler = 3;
		} else {
			//no, he isn't
			$formhandler = 4;
		}

		//check if it is too late to register and overwrite $formhandler
		if ( $timecheck > 0 ) {
			$formhandler = 1;
		}

		//is the user registered at joomla and overwrite $formhandler if not
		if ( !$user->get('id') ) {
			$formhandler = 2;
		}

		if ($formhandler >= 3) {
			$js = "function check(checkbox, senden) {
				if(checkbox.checked==true){
					senden.disabled = false;
				} else {
					senden.disabled = true;
				}}";
			$document->addScriptDeclaration($js);
		}

		//Generate Eventdescription
		if (($row->datdescription == '') || ($row->datdescription == '<br />')) {
			$row->datdescription = JText::_( 'NO DESCRIPTION' ) ;
		} else {
			//Execute Plugins
			$row->text	= $row->datdescription;

			JPluginHelper::importPlugin('content');
			$results = $dispatcher->trigger('onPrepareContent', array (& $row, & $params, 0));
			$row->datdescription = $row->text;
		}

		//Generate Venuedescription
		if (($row->locdescription == '') || ($row->locdescription == '<br />')) {
			$row->locdescription = JText::_( 'NO DESCRIPTION' );
		} else {
			//execute plugins
			$row->text	=	$row->locdescription;

			JPluginHelper::importPlugin('content');
			$results = $dispatcher->trigger('onPrepareContent', array (& $row, & $params, 0));
			$row->locdescription = $row->text;
		}

		// generate Metatags
		$meta_keywords_content = "";
		if (!empty($row->meta_keywords)) {
			$keywords = explode(",",$row->meta_keywords);
			foreach($keywords as $keyword) {
				if ($meta_keywords_content != "") {
					$meta_keywords_content .= ", ";
				}
				if (ereg("[/[/]",$keyword)) {
					$keyword = trim(str_replace("[","",str_replace("]","",$keyword)));
					$buffer = $this->keyword_switcher($keyword, $row, $elsettings->formattime, $elsettings->formatdate);
					if ($buffer != "") {
						$meta_keywords_content .= $buffer;
					} else {
						$meta_keywords_content = substr($meta_keywords_content,0,strlen($meta_keywords_content) - 2);	// remove the comma and the white space
					}
				} else {
					$meta_keywords_content .= $keyword;
				}

			}
		}
		if (!empty($row->meta_description)) {
			$description = explode("[",$row->meta_description);
			$description_content = "";
			foreach($description as $desc) {
					$keyword = substr($desc, 0, strpos($desc,"]",0));
					if ($keyword != "") {
						$description_content .= $this->keyword_switcher($keyword, $row, $elsettings->formattime, $elsettings->formatdate);
						$description_content .= substr($desc, strpos($desc,"]",0)+1);
					} else {
						$description_content .= $desc;
					}

			}
		} else {
			$description_content = "";
		}

		//set page title and meta stuff
		$document->setTitle( $item->name.' - '.$row->title );
        $document->setMetadata('keywords', $meta_keywords_content );
        $document->setDescription( strip_tags($description_content) );

        //build the url
        if(!empty($row->url) && strtolower(substr($row->url, 0, 7)) != "http://") {
        	$row->url = 'http://'.$row->url;
        }

        //create flag
        if ($row->country) {
        	$row->countryimg = ELOutput::getFlag( $row->country );
        }
        //Google images api
        $titlearray = explode("-", $row->title);
        if (count($titlearray)>1) 
	{
	    $titletext = "";
	    for ($i=1; $i<count($titlearray)-1; $i++) {
	      $titletext .= $titlearray[$i];
	    }
	    $titletext = trim($titletext);
	}
	else 
	     $titletext = trim($row->title);
	$picjs = <<<JSCRIPT
	      google.load('search', '1');

	      var imageSearch;

	      function addPaginationLinks() {
		// To paginate search results, use the cursor function.
		var cursor = imageSearch.cursor;
		var curPage = cursor.currentPageIndex; // check what page the app is on
		var pagesDiv = document.createElement('p');
		pagesDiv.setAttribute("style","text-align:center;");
		for (var i = 0; i < cursor.pages.length; i++) {
		  var page = cursor.pages[i];
		  if (curPage == i) { 

		  // If we are on the current page, then don't make a link.
		    var label = document.createTextNode(' ' + page.label + ' ');
		    pagesDiv.appendChild(label);
		  } else {

		    // Create links to other pages using gotoPage() on the searcher.
		    var link = document.createElement('a');
		    link.href = 'javascript:imageSearch.gotoPage('+i+');';
		    link.innerHTML = page.label;
		    link.style.marginRight = '2px';
		    pagesDiv.appendChild(link);
		  }
		}

		var contentDiv = document.getElementById('pictures');
		contentDiv.appendChild(pagesDiv);
	      }

	      function searchCompletePic(query) {

		// Check that we got results
		if (imageSearch.results && imageSearch.results.length > 0) {
		  // Grab our content div, clear it.
		  var contentDiv = document.getElementById('pictures');
		  contentDiv.innerHTML = '';

		  // Loop through our results, printing them to the page.
		  var results = imageSearch.results;
		  for (var i = 0; i < results.length; i++) {
		    // For each result write it's title and image to the screen
		    var result = results[i];
		    var imgContainer = document.createElement('div');
		    imgContainer.className = "box";
		    var newLink = document.createElement('a');
		    
		    // We use titleNoFormatting so that no HTML tags are left in the 
		    // title
		    newLink.title = query;
		    newLink.setAttribute ('rel', "{handler: 'iframe', size: {x: " + result.width + ", y: " + result.height + "}}");
	//            newLink.rel = "{handler: 'iframe'}";
		    newLink.href = "#";
//		    newLink.setAttribute ('onclick', "alert('abc'); return false;");
		    newLink.setAttribute ('onclick', "SqueezeBox.fromElement('" + result.url + "'); return false;");

		    var newImg = document.createElement('img');
		    // There is also a result.url property which has the escaped version
		    newImg.src = result.tbUrl;
		    newImg.alt = query + ' img' + (i+1);
		    newImg.title = query + ' img' + (i+1);

		    newLink.appendChild(newImg);
		    imgContainer.appendChild(newLink);

		    // Put our title + image in the content
		    contentDiv.appendChild(imgContainer);
		  }
		  var clrContainer = document.createElement('div');
		  clrContainer.setAttribute("style","clear:both;height:0px");
		  clrContainer.appendChild( document.createTextNode( '\u00A0' ) );
		  contentDiv.appendChild(clrContainer);

		  // Now add links to additional pages of search results.
		  addPaginationLinks(imageSearch);
		}
	      }

	      function OnLoad() {
	      
		// Create an Image Search instance.
		imageSearch = new google.search.ImageSearch();

		// Set searchComplete as the callback function when a search is 
		// complete.  The imageSearch object will have results in it.
		imageSearch.setSearchCompleteCallback(this, searchCompletePic, ['$titletext']);
		imageSearch.setResultSetSize(8);

		// Find me a beautiful car.
		imageSearch.execute("$titletext");
		
		// Include the required Google branding
	//        google.search.Search.getBranding('branding');
	      }
	      google.setOnLoadCallback(OnLoad);
JSCRIPT;
	$key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBR2bDFzeCT1WBspxZrlXfH7Q6YWYhSjQl2ZBZH0H0HKmSschtXmr2DxcA';
	$document->addScriptDeclaration($picjs);
	$document->addScript('https://www.google.com/jsapi?key=' . $key);



		//assign vars to jview
		$this->assignRef('row', 					$row);
		$this->assignRef('params' , 				$params);
		$this->assignRef('allowedtoeditevent' , 	$allowedtoeditevent);
		$this->assignRef('allowedtoeditvenue' , 	$allowedtoeditvenue);
		$this->assignRef('dimage' , 				$dimage);
		$this->assignRef('limage' , 				$limage);
		$this->assignRef('print_link' , 			$print_link);
		$this->assignRef('registers' , 				$registers);
		$this->assignRef('formhandler',				$formhandler);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('item' , 					$item);

		parent::display($tpl);
	}

	/**
	 * structures the keywords
	 *
 	 * @since 0.9
	 */
	function keyword_switcher($keyword, $row, $formattime, $formatdate) {
		switch ($keyword) {
			case "catsid":
				$content = $row->catname;
				break;
			case "a_name":
				$content = $row->venue;
				break;
			case "times":
			case "endtimes":
				if ($row->$keyword) {
					$content = strftime( $formattime ,strtotime( $row->$keyword ) );
				} else {
					$content = '';
				}
				break;
			case "dates":
			case "enddates":
				$content = strftime( $formatdate ,strtotime( $row->$keyword ) );
				break;
			default:
				$content = $row->$keyword;
				break;
		}
		return $content;
	}
}
?>