<?php
/**
 * Helper class for mod_eventlistcalq module
 * 
 * @package    Eventlist CalModuleQ for Joomla 1.5
 * @subpackage Modules
 * @link http://extensions.qivva.com
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @copyright (C) 2008 Toni Smillie www.qivva.com
 * Eventlist Calendar Q by Toni Smillie www.qivva.com
 * 
  * Version 0.8
 * Changes for v0.8
 * 1. Roll over year end bug fixed
 * 2. Check for mb_substr
 * 3. Removed hard coded text align center (Now uses stylesheet)
  * Version 0.7
 * Changes for v0.7
 * 1. Removed JDate again - causing too many date/time problems
 * 2. Better Tooltips
 * Version 0.6
 * Changes for v0.6
 * 1. Use JDate for month and day languages
 * 2. XHTML validation fixes
 * 3. Tests if mbstring functions are installed before using them, defaults to ucfirst if no mb_convert_case
 * 4. Allows a calendar offset so can have multiple calendars displaying different months
 *
  * Version 0.5
 * Changes for v0.5
 * 1. Remember which month was being viewed, so doesn't revery back to "today" on a page change - controlled by Parameter
 * 2. Fix for Windows IIS servers
 * 3. Fix for SEF links
 * 4. Replace instead of concatenate month view changes
 * 5. Set $month_href = NULL; (bug fix)
 * 6. Use multibyte strings for days and months. Parameter overrides for locale and charset.
 *
 * Version 0.4
 * Changes for v0.4
 * 1. New Parameters Category ID and Venue ID to allow for filtering of calendar module events
 * 2. Removed the 2 styling parameters form the parameter list. All styling is now done in the CSS
 * 3. Enhanced styling and new stylesheet
 * 
 * Changes for v0.3
 * 1. Fixed timeoffset properly for Joomla 1.5
 * 2. Fixed problem that caused "Notice: Undefined index:" with PHP5
 * 
 * Changes for v0.2
 * 1. Added Title on Tooltips
 * 2. Fix for time offset
 * 3 Bug fix - not picking up all events when on the same day
 * 
 * Original Eventlist calendar from Christoph Lukes www.schlu.net
 * PHP Calendar (version 2.3), written by Keith Devens
 * http://keithdevens.com/software/php_calendar
 * see example at http://keithdevens.com/weblog
 * License: http://keithdevens.com/software/license
*/

class modeventlistcalqhelper
{

	function getdays ($greq_year, $greq_month, &$params)
	{
		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		
		$catid 				= trim( $params->get('catid') );
		$venid 				= trim( $params->get('venid') );
		$StraightToDetails	= $params->get( 'StraightToDetails', '1' );	
		$DisplayCat			= $params->get( 'DisplayCat', '0' );
		$DisplayVenue		= $params->get( 'DisplayVenue', '0' );
		$ArchivedEvents		= $params->get( 'ArchivedEvents', '0' );
		
		//Get eventdates
		if ($catid)
		{
			$ids = explode( ',', $catid );
			JArrayHelper::toInteger( $ids );
			$categories = ' AND (c.id=' . implode( ' OR c.id=', $ids ) . ')';
		}
		if ($venid)
		{
			$ids = explode( ',', $venid );
			JArrayHelper::toInteger( $ids );
			$venues = ' AND (l.id=' . implode( ' OR l.id=', $ids ) . ')';
		}
		 if ($ArchivedEvents==0) 
		 {
		   $publ = '1';
		 }
		 else
		 {
		   $publ = '-1';
		 }
		$query = 'SELECT a.id,a.dates, a.times, a.enddates,a.title,c.id AS mcatid,c.catname,l.id AS mlocid,l.venue, DAYOFMONTH(a.dates) AS created_day, YEAR(a.dates) AS created_year, MONTH(a.dates) AS created_month'
		. ' FROM #__eventlist_events AS a'
		. ' LEFT JOIN #__eventlist_categories AS c ON c.id = a.catsid'
		. ' LEFT JOIN #__eventlist_venues AS l ON l.id = a.locid'
		. ' WHERE a.published = '.$publ
		. ' AND c.access <= '.(int)$user->aid
		.($catid ? $categories : '')
		.($venid ? $venues : '')	
		;
		
		
		$db->setQuery( $query );
		$events = $db->loadObjectList();
		
		$days = array();
		foreach ( $events as $event )
		{
		   // Cope with no end date set i.e. set it to same as start date
			if  (($event->enddates == '0000-00-00') or (is_null($event->enddates)))
			{
				$eyear = $event->created_year;
				$emonth = $event->created_month;
				$eday = $event->created_day;
		
			}
			else
			{
				list($eyear, $emonth, $eday) = explode('-', $event->enddates);
			}
			// The two cases for roll over the year end with an event that goes across the year boundary.
			if ($greq_year < $eyear) 
			{
				$emonth = $emonth + 12; 
			}
					
			if ($event->created_year < $greq_year) 
			{
				$event->created_month = $event->created_month - 12;
			}

			if (  ($greq_year >= $event->created_year) && ($greq_year <= $eyear) 
			   && ($greq_month >= $event->created_month) && ($greq_month <= $emonth) )
		   {
			// Set end day for current month

				if ($emonth > $greq_month)
				{
					$emonth = $greq_month;

		//			$eday = cal_days_in_month(CAL_GREGORIAN, $greq_month,$greq_year);
					$eday = date('t', mktime(0,0,0, $greq_month, 1, $greq_year));
				}

			// Set start day for current month
				if ($event->created_month < $greq_month)
				{
					$event->created_month = $greq_month;
					$event->created_day = 1;
				}	
				$stod = 1;			
				for ($count = $event->created_day; $count <= $eday; $count++)
				{
		
				$uxdate = mktime(0,0,0,$greq_month,$count,$greq_year); 
				$tdate = strftime('%Y%m%d',$uxdate);// Toni change Joomla 1.5
				$created_day = $count;
		
	//			$tt = $days[$count][1];
		
	//			if (strlen($tt) == 0)

					if (empty($days[$count][1]))
					{
						$title = htmlspecialchars($event->title);
						if ($DisplayCat ==1)
						{
							$title = $title . '&nbsp;(' . htmlspecialchars($event->catname) . ')';
						}
						if ($DisplayVenue == 1)
						{
							if (isset($event->venue)) 
							{
							$title = $title . '&nbsp;@' . htmlspecialchars($event->venue);
							}
						}
						$stodid = $event->id;
						$stod = 1;
					}
					else
					{
						$tt = $days[$count][1];
						$title = $tt . '&#013 +' . htmlspecialchars($event->title);
						if ($DisplayCat ==1)
						{
							$title = $title . '&nbsp;(' . htmlspecialchars($event->catname) . ')';
						}
						if ($DisplayVenue == 1)
						{
							if (isset($event->venue)) 
							{
							$title = $title . '&nbsp;@' . htmlspecialchars($event->venue);
							}
						}
						$stod = 0;
					}	
					if (($StraightToDetails == 1) and ($stod==1))
					{
						$link			= EventListHelperRoute::getRoute( $stodid, 'details') ;
					}
					else
					{	
						$link			= EventListHelperRoute::getRoute( $tdate, 'day') ;
					}		
				$days[$count] = array($link,$title);
				}
		}
	// End of Toni modification	
	}
	return $days;
	} //End of function getdays
} //End class

?> 