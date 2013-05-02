<?php

/**
 *  A PHP class providing access to a date/time picker
 *
 *  -   provides a nice and intuitive date picker and optionally, a time picker
 *  -   complex formulas for selectable dates and times ranges can be set
 *  -   dates can be preselected and the datepicker can be instructed to show specific month/year as default
 *  -   the returned format of the date can be set to any formats supported by PHP's date() function
 *  -   customizable starting day of the week
 *  -   supports localisation (currently english, dutch, finnish, french, german, italian, romanian and slovenian translations are available)
 *  -   template driven and highly customizable
 *  -   the included template produces valid HTML 4.01 Transitional and XHTML 1.0
 *  -   code is heavily documented so you can easily understand every aspect of it
 *
 *  See the documentation for more info.
 *
 *  Read the LICENSE file, provided with the package, to find out how you can use this PHP script.
 *
 *  If you don't find this file, please write an email to noname at nivelzero dot ro and you will be sent a copy of the license file
 *
 *  For more resources visit {@link http://stefangabos.blogspot.com}
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @version    1.0.7 (last revision: November 15, 2007)
 *  @copyright  (c) 2006-2007 Stefan Gabos
 *  @package    datePicker
 *  @example    example1.php
 *  @example    example2.php
 *  @example    example3.php
 *  @example    example4.php
 *  @example    example5.php
 *  @example    example6.php
 */

error_reporting(E_ALL);

class datePicker
{

    /**
     *  An array of an unlimited number of arrays with three elements.
     *
     *  In these arrays of three elements the first one is the date representing the start of the range (inclusive), the second
     *  one is the date representing the end of the range (inclusive) and the third one represents how days should be skipped
     *  (i.e. "1" means every day in the range should be available for selection, "2" means every second day should be available
     *  for selection and so on)
     *
     *  <i>Note that dates must be represented as UNIX timestamps!</i>
     *
     *  <i>Also note that the range of dates can be anywhere in between 1/1/1970 and 31/12/2038</i>
     *
     *  <code>
     *      /**
     *      *
     *      *   Allow every second day starting from next Monday and up to next Friday
     *      *
     *      {@*}
     *
     *     $dp->selectableDatesRange = array(
     *
     *          array(strtotime("next Monday"), strtotime("next Friday"), 1),
     *
     *     )
     *
     *  </code>
     *
     *  @since 1.0.4
     *
     *  @var array
     */
    var $selectableDatesRange;
    
    /**
     *  An array of an unlimited number of arrays with three elements.
     *
     *  In these arrays of three elements the first one is the year representing the start of the range (inclusive), the second
     *  one is the year representing the end of the range (inclusive) and the third one represents how years should be skipped
     *  (i.e. "1" means every year in the range should be available for selection, "2" means every second year should be available
     *  for selection and so on)
     *
     *  <code>
     *      /**
     *      *
     *      *   Allow every second year starting with 2000 and up to the current year
     *      *   and also, every year from 1990 to 1996
     *      *
     *      {@*}
     *
     *     $dp->selectableYearsRange = array(
     *
     *          array(2000, date("Y"), 2),
     *          array(1990, 1996, 1)
     *
     *     )
     *
     *  </code>
     *
     *  Setting values to this property will make the years show in a select box (rather than static text) enabling the user
     *  to quickly select a year from the given range
     *
     *  @since 1.0.4
     *
     *  @var array
     */
    var $selectableYearsRange;
    
    /**
     *  An array of an unlimited number of arrays with six elements.
     *
     *  In these arrays of six elements the first one is the hour representing the start of the range (inclusive), the second
     *  one is the hour representing the end of the range (exclusive), the third one represents how hours should be skipped
     *  (i.e. "1" means every hour in the range should be available for selection, "2" means every second hour should be available
     *  for selection and so on), the fourth one is the minute representing the start of the range (inclusive), the fifth
     *  one is the minute representing the end of the range (exclusive) and the sixth one represents how minutes should be skipped
     *  (i.e. "15" means every 15th minute in the range should be available for selection, "30" means every 30th minute should be
     *  available for selection and so on),
     *
     *  An unlimited number of ranges can be specified.
     *
     *  Setting this property has sense only if {@link enableTimePicker} property is set to TRUE
     *
     *  <code>
     *      $dp->selectableTimesRange = array(
     *          /**
     *              from hours 10 to 12 and minute 0 to 60 allow every hour and minute
     *          {@*}
     *          array(10, 12, 1, 0, 60, 1),
     *          /**
     *              also, from hours 12 to 18 and minutes 0 to 60 allow only 12, 14 and
     *              16 hours to be selected (through a stepping of 2) and 0, 15, 30, 45
     *              minutes (through a stepping of 15)
     *          {@*}
     *          array(12, 18, 2, 0, 60, 15),
     *      )
     *  </code>
     *
     *  @since 1.0.4
     *
     *  @var array
     */
    var $selectableTimesRange;
    
    /**
     *  If set to TRUE, a button for going to the current month/year will be shown.
     *
     *  Useful when the user can wonder through the years and months and might want to get back.
     *
     *  Default is FALSE
     *
     *  @since 1.0.7
     *
     *  @var boolean
     */
    var $showGoToCurrentMonthYear;
    
    /**
     *  If set to TRUE, a time picker will also be shown
     *
     *  Default is FALSE
     *
     *  @since 1.0.4
     *
     *  @var boolean
     */
    var $enableTimePicker;
    
    /**
     *  Preselects a date in the calendar
     *
     *  <b>The date must be specified as a UNIX timestamp!</b>
     *
     *  @var timestamp
     */
    var $preselectedDate;
    
    /**
     *  Format of the returned day
     *
     *  Any combination allowed by PHP's date() function can be used
     *
     *  <b>Note</b>
     *
     *  <i>You should never use hours, minutes and seconds in your format because you will always have "00:00" appended to your returned
     *  date. If {@link enableTimePicker} is set to TRUE, selected hour and minute will be automatically appended</i>
     *
     *  default is "m d Y"
     *
     *  @var string
     */
    var $dateFormat;
    
    /**
     *  What day should be taken as the first day of week
     *
     *  Possible values range from 0 (Sunday) to 6 (Saturday)
     *
     *  default is 0 (Sunday)
     *
     *  @var    integer
     */
    var $firstDayOfWeek;
    
    /**
     *  Height of the calendar window
     *
     *  default is 250
     *
     *  @var integer
     */
    var $windowHeight;

    /**
     *  Width of the calendar window
     *
     *  default is 300
     *
     *  @var integer
     */
    var $windowWidth;

    /**
     *  Language file to use
     *
     *  The name of the php language file you wish to use from the /languages folder.
     *
     *  Without the extension! (i.e. "german" for the german language not "german.php")
     *
     *  default is "english"
     *
     *  @var   string
     */
    var $language;

    /**
     *  Template folder to use
     *
     *  Note that only the folder of the template you wish to use needs to be specified. Inside the folder
     *  you <b>must</b> have the <b>template.xtpl</b> file which will be automatically loaded
     *
     *  default is "default"
     *
     *  @var   string
     */
    var $template;

    /**
     *  Constructor of the class
     *
     *  @return void
     */
    function datePicker()
    {
    
        // default values to properties
        $this->preselectedDate = "";
        $this->selectableRange = array();
        $this->selectableYearsRange = array();
        $this->selectableTimesRange = array();
        $this->enableTimePicker = false;
        $this->dateFormat = "m d Y";
        $this->firstDayOfWeek = 0;
        $this->windowHeight = 300;
        $this->windowWidth = 250;
        $this->language = "english";
        $this->template = "default";
        $this->showClearDate = true;
        $this->showGoToCurrentMonthYear = false;

        // get the absolute path of the class. any further includes rely on this
        // and (on a windows machine) replace \ with /
        $this->absolutePath = preg_replace("/\\\/", "/", dirname(__FILE__));

        // remove $_SERVER["DOCUMENT_ROOT"] from the path
        // this path is to be used from within HTML as it is a relative path
        $this->strippedPath = preg_replace("/".preg_replace("/\//", "\/", $_SERVER["DOCUMENT_ROOT"])."/i", "", $this->absolutePath);

    }
    
    /**
     *  Returns the JavaScript code that will open the pop-up window containing the date picker
     *
     *  @param  string  $controlID      the ID of the HTML element (textbox or textarea) where to return the selected value
     *
     *  @param  mixed   $startMonth     (optional) which month to start the calendar from
     *
     *                                  Note that you can also pass a javascript statement as argument!
     *
     *  @param  mixed   $startYear      (optional) which year to start the calendar from
     *
     *                                  Note that you can also pass a javascript statement as argument!
     *
     *  @param  mixed   $startHour      (optional) what hour to show by default
     *
     *                                  Default is "0"
     *
     *                                  This argument will be processed only if the {@link enableTimePicker} property is set to TRUE!
     *
     *                                  Note that you can also pass a javascript statement as argument!
     *
     *  @param  mixed   $startMinute    (optional) what minute to show by default
     *
     *                                  Default is "0"
     *
     *                                  This argument will be processed only if the {@link enableTimePicker} property is set to TRUE!
     *
     *                                  Note that you can also pass a javascript statement as argument!
     *
     *  @return void
     */
    function show($controlID, $startMonth = "", $startYear = "", $startHour = "", $startMinute = "")
    {
    
        // make sure that selectableDatesRange property is ok
        // iterate through all the given ranges
        if (is_array($this->selectableDatesRange) && !empty($this->selectableDatesRange)) {

            foreach ($this->selectableDatesRange as $index=>$range) {

                // a range is valid if it has 3 items
                if (is_array($range) && count($range) == 3) {

                    // iterate through range's items
                    foreach ($range as $key=>$value) {

                        // and make them integers
                        $this->selectableDatesRange[$index][$key] = (int)$this->selectableDatesRange[$index][$key];

                    }

                // if range is erroneous
                } else {

                    unset($this->selectableDatesRange[$index]);

                }

            }

        // if range is erroneous
        } else {

            $this->selectableDatesRange = array();

        }

        // make sure that selectableYearsRange property is ok
        // iterate through all the given ranges
        if (is_array($this->selectableYearsRange) && !empty($this->selectableYearsRange)) {

            foreach ($this->selectableYearsRange as $index=>$range) {

                // a range is valid if it has 3 items
                if (is_array($range) && count($range) == 3) {

                    // iterate through range's items
                    foreach ($range as $key=>$value) {

                        // and make them integers
                        $this->selectableYearsRange[$index][$key] = (int)$this->selectableYearsRange[$index][$key];

                    }

                // if range is erroneous
                } else {

                    unset($this->selectableYearsRange[$index]);
                    
                }

            }

        // if range is erroneous
        } else {

            $this->selectableYearsRange = array();

        }

        // make sure that selectableTimesRange property is ok
        // iterate through all the given ranges
        if (is_array($this->selectableTimesRange) && !empty($this->selectableTimesRange)) {

            foreach ($this->selectableTimesRange as $index=>$range) {

                // a range is valid if it has 6 items
                if (is_array($range) && count($range) == 6) {

                    // iterate through range's items
                    foreach ($range as $key=>$value) {

                        // and make them integers
                        $this->selectableTimesRange[$index][$key] = (int)$this->selectableTimesRange[$index][$key];

                    }

                // if range is erroneous
                } else {

                    unset($this->selectableTimesRange[$index]);

                }

            }

        } else {

            // if range is erroneous
            $this->selectableTimesRange = array();

        }
        
        return "javascript:var cw = null; cw = window.open('" . $this->strippedPath . "/includes/datepicker.php?preselectedDate=" . $this->preselectedDate . "&selectableDatesRange=" . urlencode(htmlspecialchars(serialize($this->selectableDatesRange), ENT_COMPAT)) . "&selectableYearsRange=" . urlencode(htmlspecialchars(serialize($this->selectableYearsRange), ENT_COMPAT)) . "&selectableTimesRange=" . urlencode(htmlspecialchars(serialize($this->selectableTimesRange), ENT_COMPAT)) . "&enableTimePicker=" . $this->enableTimePicker . "&showGoToCurrentMonthYear=" . ($this->showGoToCurrentMonthYear === TRUE ? 1 : 0) . "&month=" . $startMonth . "&year=" . $startYear . "&hour=" . $startHour . "&minute=" . $startMinute . "&controlName=" . $controlID . "&dateFormat=" . $this->dateFormat . "&firstDayOfWeek=" . $this->firstDayOfWeek . "&language=" . $this->language . "&template=" . $this->template . "','datePicker','width=" . $this->windowWidth . ",height=" . $this->windowHeight . ",left='+(screen.availWidth/2-" . $this->windowWidth/2 . ")+',top='+(screen.availHeight/2-" . $this->windowHeight/2 . ")+',scrollbars=no,toolbar=no,menubar=no,location=no,alwaysraised=yes,modal=yes'); if (window.focus) { cw.focus() } return false";

    }

}

?>
