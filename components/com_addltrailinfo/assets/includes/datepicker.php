<?php

    error_reporting(E_ALL);
    
    // the very first time, all properties are set through GET and so we need
    // to convert them to POSTs as we'll be using that from that moment on
    if (
    
        isset($_GET["preselectedDate"]) &&
        isset($_GET["selectableDatesRange"]) &&
        isset($_GET["selectableYearsRange"]) &&
        isset($_GET["selectableTimesRange"]) &&
        isset($_GET["enableTimePicker"]) &&
        isset($_GET["showGoToCurrentMonthYear"]) &&
        isset($_GET["month"]) &&
        isset($_GET["year"]) &&
        isset($_GET["hour"]) &&
        isset($_GET["minute"]) &&
        isset($_GET["controlName"]) &&
        isset($_GET["dateFormat"]) &&
        isset($_GET["firstDayOfWeek"]) &&
        isset($_GET["language"]) &&
        isset($_GET["template"])
        
    ) {
    
        $_POST["preselectedDate"] = $_GET["preselectedDate"];
        $_POST["selectableDatesRange"] = $_GET["selectableDatesRange"];
        $_POST["selectableYearsRange"] = $_GET["selectableYearsRange"];
        $_POST["selectableTimesRange"] = $_GET["selectableTimesRange"];
        $_POST["enableTimePicker"] = $_GET["enableTimePicker"];
        $_POST["showGoToCurrentMonthYear"] = $_GET["showGoToCurrentMonthYear"];
        $_POST["month"] = $_GET["month"];
        $_POST["year"] = $_GET["year"];
        $_POST["hour"] = $_GET["hour"];
        $_POST["minute"] = $_GET["minute"];
        $_POST["controlName"] = $_GET["controlName"];
        $_POST["dateFormat"] = $_GET["dateFormat"];
        $_POST["firstDayOfWeek"] = $_GET["firstDayOfWeek"];
        $_POST["language"] = $_GET["language"];
        $_POST["template"] = $_GET["template"];

    }
    
    if (

        isset($_POST["preselectedDate"]) &&
        isset($_POST["selectableDatesRange"]) &&
        isset($_POST["selectableYearsRange"]) &&
        isset($_POST["selectableTimesRange"]) &&
        isset($_POST["enableTimePicker"]) &&
        isset($_POST["showGoToCurrentMonthYear"]) &&
        isset($_POST["month"]) &&
        isset($_POST["year"]) &&
        ($_POST["enableTimePicker"] === true ? isset($_POST["hour"]) : true) &&
        ($_POST["enableTimePicker"] === true ? isset($_POST["minute"]) : true) &&
        isset($_POST["controlName"]) &&
        isset($_POST["dateFormat"]) &&
        isset($_POST["firstDayOfWeek"]) &&
        isset($_POST["language"]) &&
        isset($_POST["template"])

    ) {
    
        // decode the selectable date ranges
        $selectableDatesRange = unserialize(html_entity_decode(urldecode($_POST["selectableDatesRange"]), ENT_COMPAT));
        
        // decode the selectable year ranges
        $selectableYearsRange = unserialize(html_entity_decode(urldecode($_POST["selectableYearsRange"]), ENT_COMPAT));
        
        // decode the selectable time ranges
        $selectableTimesRange = unserialize(html_entity_decode(urldecode($_POST["selectableTimesRange"]), ENT_COMPAT));

        $selectableDates = array();
        
        // if date ranges were set
        if (!empty($selectableDatesRange)) {
        
            // iterate through the ranges
            foreach ($selectableDatesRange as $range) {

                // a range is considered valid is it's an array of length 3
                if (is_array($range) && count($range) == 3) {
                
                    // assign to $selectableDate the first value of the array (the lower limit of the range)
                    $selectableDate = (int)$range[0];
                    
                    // loop while $selectableDate is lower than the second value of the array (the higher limit of the range)
                    while ($selectableDate <= $range[1]) {
                    
                        // if $selectableDate is not already in the list of selectable dates
                        if (!in_array($selectableDate, $selectableDates)) {

                            // add it to the list, using the format provided by user
                            $selectableDates[] = date($_POST["dateFormat"], $selectableDate);

                        }
                        
                        // add as many days to $selectableDate as specified by the third value of the array
                        $selectableDate = strtotime("+" . $range[2] . " days", $selectableDate);

                    }

                }

            }

        }
        
        $selectableYears = array();
        
        // if year ranges were set
        if (!empty($selectableYearsRange)) {

            // iterate through the ranges
            foreach ($selectableYearsRange as $range) {

                // a range is considered valid is it's an array of length 3
                if (is_array($range) && count($range) == 3) {

                    // iterate through all the years in the range, skipping as requested
                    for ($i = $range[0]; $i <= $range[1]; $i += $range[2]) {

                        // make sure that year is valid and also not already in the array
                        if ($i > 1969 && $i < 2039 && !in_array($i, $selectableYears)) {

                            // construct the selectable years array
                            $selectableYears[] = $i;

                        }

                    }

                }

            }
        
            // sort years ascendingly
            sort($selectableYears);
            
        }
        
        $selectableHours = array();
        $selectableMinutes = array();

        // if time ranges were not
        if (empty($selectableTimesRange)) {
        
            // set the default time range (hours 0 to 23, minutes 0 to 59, all hours and minutes)
            $selectableTimesRange = array(array(0, 24, 1, 0, 60, 1));
        
        }
        
        // iterate through the ranges
        foreach ($selectableTimesRange as $timesRange) {

            // a range is considered valid is it's an array of length 3
            if (is_array($timesRange) && count($timesRange) == 6) {

                // hours range
                $hoursLow = $timesRange[0];
                $hoursHigh = $timesRange[1];

                // minutes range
                $minutesLow = $timesRange[3];
                $minutesHigh = $timesRange[4];

                // stepping
                $hoursStep = $timesRange[2];
                $minutesStep = $timesRange[5];

                // iterate through all the hours in range, stepping as requested
                for ($i = $hoursLow; $i < $hoursHigh; $i += $hoursStep) {

                    // make sure that hour is valid and also not already in the array
                    if ($i >= 0 && $i < 24 && !in_array($i, $selectableHours)) {

                        // construct the selectable hours array
                        $selectableHours[] = $i;

                    }

                }

                // iterate through all the minutes in range, stepping as requested
                for ($i = $minutesLow; $i < $minutesHigh; $i += $minutesStep) {

                    // make sure that minutes is valid and also not already in the array
                    if ($i >= 0 && $i < 60 && !in_array($i, $selectableMinutes)) {

                        // construct the selectable minutes array
                        $selectableMinutes[] = $i;

                    }

                }

            }

        }
        
        // sort ascendingly both hours and minutes array
        sort($selectableHours);
        sort($selectableMinutes);

        // if no default month is selected
        if ($_POST["month"] == "") {
        
            // set as default the current month
            $_POST["month"] = date("m");
            
        }

        // if no default year is selected
        if ($_POST["year"] == "") {
        
            // set as default the current year
            $_POST["year"] = date("Y");
            
        }
        
        // if month is smaller than 1
        if ($_POST["month"] < 1) {
        
            // set the year one year backward
            $_POST["year"] = $_POST["year"] - 1;

            // and make the month the 12th
            $_POST["month"] = 12;
            
        // if month is greater than 12
        } elseif ($_POST["month"] > 12) {
        
            // set the year one year forward
            $_POST["year"] = $_POST["year"] + 1;
            
            // and make the month the 1st
            $_POST["month"] = 1;
            
        }
        
        // if year is not in the selectable range
        if (!in_array($_POST["year"], $selectableYears)) {
        
            // find if user is ascending or descending on years (by reading $_POST["action"])
            foreach ($selectableYears as $key=>$year) {

                // if user is descending and we've found the year he's descending from
                if ($_POST["action"] == "dec" && ($year - 1) == $_POST["year"]) {
                
                    // if a lower year exists
                    if (isset($selectableYears[$key - 1])) {
                    
                        // make that one the selected one
                        $_POST["year"] = $selectableYears[$key - 1];

                    // if no lower year exists than the one we left from
                    } else {

                        // revert back to where we left from
                        $_POST["year"] = $year;

                    }
                    
                    // don't look further
                    break;

                // if user is ascending and we've found the year he's ascending from
                } elseif ($_POST["action"] == "inc" && ($year + 1) == $_POST["year"]) {

                    // if a higher year exists
                    if (isset($selectableYears[$key + 1])) {

                        // make that one the selected one
                        $_POST["year"] = $selectableYears[$key + 1];

                    // if no higher year exists than the one we left from
                    } else {

                        // revert back to where we left from
                        $_POST["year"] = $year;

                    }
                    
                    // don't look further
                    break;

                }

            }

        }

        // compute the number of days in the selected month
        $daysInCurrentMonth = date("t", mktime(0, 0, 0, $_POST["month"], 1, $_POST["year"]));

        // what weekday is the first day of the month?
        $firstWeekDayInCurrentMonth = date("w", mktime(0, 0, 0, $_POST["month"], 1, $_POST["year"]));

        // how many days to display from previous month?
        $cmp = $firstWeekDayInCurrentMonth - $_POST["firstDayOfWeek"];
        $daysFromPreviousMonth = $cmp < 0 ? 5 - $cmp : $cmp;
        
        // include the XTemplate class
        require_once "../includes/class.xtemplate.php";
        
        // check if the template file exists
        if (@file_exists("../templates/" . htmlentities($_POST["template"]) . "/template.xtpl")) {

            // instantiate a new template object with the given template
            $xtpl = new XTemplate("../templates/" . htmlentities($_POST["template"]) . "/template.xtpl");
            
        }
        
        // include the language file
        require_once "../languages/" . htmlentities($_POST["language"]) . ".php";

        // assign the language array
        $xtpl->assign("languageStrings", $languageStrings);

        // if time picker is to be shown
        if ($_POST["enableTimePicker"]) {

            $hours = "";

            // generate the hours range
            foreach ($selectableHours as $hour) {

                $hours .= "<option value='" . $hour . "' " . (($_POST["preselectedDate"] != "" && !isset($_POST["action"]) ? date("H", $_POST["preselectedDate"]) == $hour : $_POST["hour"] == $hour) ? "selected" : "") . ">" . str_pad($hour, 2, "0", STR_PAD_LEFT) . "</option>";

            }

            $minutes = "";

            // generate the minutes range
            foreach ($selectableMinutes as $minute) {

                $minutes .= "<option value='" . $minute . "' " . (($_POST["preselectedDate"] != "" && !isset($_POST["action"]) ? date("i", $_POST["preselectedDate"]) == $minute : $_POST["minute"] == $minute) ? "selected" : "") . ">" . str_pad($minute, 2, "0", STR_PAD_LEFT) . "</option>";

            }

            $xtpl->assign("hours", $hours);

            $xtpl->assign("minutes", $minutes);

            $xtpl->parse("main.timepicker");

        }

        // if a selectable year range is not set
        if (empty($selectableYearsRange)) {

            // parse designated block
            $xtpl->parse("main.year");

        // if a selectable year range was set
        } else {
        
            $years = "";

            // iterate through the selectable years
            foreach ($selectableYears as $year) {

                // construct the selectable years list
                $years .= "<option value='" . $year . "'" . ($_POST["year"] == $year ? "selected" : "") . ">" . $year . "</option>";

            }
            
            // assign the options
            $xtpl->assign("years", $years);
            
            // parse the designated block
            $xtpl->parse("main.years");

        }

        // parse month names
        for ($i = 1; $i < 13; $i++) {
        
            $xtpl->assign("monthLiteral", $languageStrings["strLang_abbrMonthsNames"][$i - 1]);
            $xtpl->assign("monthNumeric", $i);
            
            // if this is the selected month
            if ($i == $_POST["month"]) {
            
                $xtpl->parse("main.months_row.item.month_selected");
            
            // if this is any other month
            } else {

                $xtpl->parse("main.months_row.item.month");

            }
            
            $xtpl->parse("main.months_row.item");

            // 6 months in a row
            if ($i % 6 == 0) {

                $xtpl->parse("main.months_row");
            }
            
        }

        // parse day names
        for ($i = 0; $i < 7; $i++) {
        
            $xtpl->assign("dayName", $languageStrings["strLang_abbrDaysNames"][($i + $_POST["firstDayOfWeek"] > 6 ? $i + $_POST["firstDayOfWeek"] - 7 : $i + $_POST["firstDayOfWeek"])]);
            $xtpl->parse("main.day_names");
            
        }

        // if a date was preselected
        if ($_POST["preselectedDate"] != "") {

            // calculate it's timestamp
            $preselectedTimestamp = mktime(0, 0, 0, date("m", $_POST["preselectedDate"]), date("d", $_POST["preselectedDate"]), date("Y", $_POST["preselectedDate"]));

            $xtpl->assign("preselectedDateFormatted", date($_POST["dateFormat"], $preselectedTimestamp));

        // if no date was preselected
        } else {

            // just make it's timestamp 0
            $preselectedTimestamp = 0;

        }
        
        // the calendar shows 42 days
        for ($i = 1; $i < 43; $i++) {
        
            // days from previous month
            if ($i <= $daysFromPreviousMonth) {

                $timestamp = mktime(0, 0, 0, $_POST["month"], 0 - ($daysFromPreviousMonth - $i), $_POST["year"]);
                $xtpl->assign("day", date("d", $timestamp));
                
                // switch days
                switch (date("w", $timestamp)) {
                
                    // if the day to show is a weekend
                    case 0:
                    case 6:
                    
                        $xtpl->parse("main.days_row.day.previousMonth_weekend" . ($timestamp == $preselectedTimestamp ? "_preselected" : ""));

                        break;

                    // if the day to show is a weekday
                    default:
                    
                        $xtpl->parse("main.days_row.day.previousMonth_weekday" . ($timestamp == $preselectedTimestamp ? "_preselected" : ""));
                        
                }
                
            // days in current month
            } elseif ($i > $daysFromPreviousMonth && $i <= ($daysInCurrentMonth + $daysFromPreviousMonth)) {
            
                $timestamp = mktime(0, 0, 0, $_POST["month"], $i - $daysFromPreviousMonth, $_POST["year"]);
                $xtpl->assign("returnValue", date($_POST["dateFormat"], $timestamp));
                $xtpl->assign("day", date("d", $timestamp));
                
                // by default, consider date to be out of the selectable range
                $selectable = false;
                
                // if no ranges specified or date is in selectable range
                if (empty($selectableDates) || in_array(date($_POST["dateFormat"], $timestamp), $selectableDates)) {
                
                    // means that any date is selectable
                    $selectable = true;

                }
                
                // switch days
                switch (date("w", $timestamp)) {
                
                    // if the day to show is a weekend
                    case 0:
                    case 6:
                    
                        // if this date is not selectable
                        if (!$selectable) {
                        
                            $xtpl->parse("main.days_row.day.currentMonth_weekend_disabled");
                                
                        // if date is selectable
                        } else {
                        
                            // if this both the preselected date and current date
                            if (

                                $timestamp == $preselectedTimestamp &&
                                $timestamp == mktime(0, 0, 0, date("m"), date("d"), date("Y"))

                            ) {
                            
                                $xtpl->parse("main.days_row.day.currentMonth_currentDay_weekend_preselected");

                            // if this is just the preselected date
                            } elseif (

                                $timestamp == $preselectedTimestamp

                            ) {

                                $xtpl->parse("main.days_row.day.currentMonth_weekend_preselected");

                            // if this the current date
                            } elseif ($timestamp == mktime(0, 0, 0, date("m"), date("d"), date("Y"))) {
                            
                                $xtpl->parse("main.days_row.day.currentMonth_currentDay_weekend");

                            } else {

                                $xtpl->parse("main.days_row.day.currentMonth_weekend");

                            }
                            
                        }
                        
                        break;
                        
                    // if the day to show is a weekday
                    default:

                        // if this date is not selectable
                        if (!$selectable) {

                            $xtpl->parse("main.days_row.day.currentMonth_weekday_disabled");

                        // if date is selectable
                        } else {

                            // if this both the preselected date and current date
                            if (

                                $timestamp == $preselectedTimestamp &&
                                $timestamp == mktime(0, 0, 0, date("m"), date("d"), date("Y"))

                            ) {

                                $xtpl->parse("main.days_row.day.currentMonth_currentDay_weekday_preselected");

                            // if this is just the preselected date
                            } elseif (

                                $timestamp == $preselectedTimestamp

                            ) {

                                $xtpl->parse("main.days_row.day.currentMonth_weekday_preselected");

                            // if this the current date
                            } elseif ($timestamp == mktime(0, 0, 0, date("m"), date("d"), date("Y"))) {

                                $xtpl->parse("main.days_row.day.currentMonth_currentDay_weekday");

                            } else {

                                $xtpl->parse("main.days_row.day.currentMonth_weekday");

                            }
                            
                        }
                        
                }
                
            // days in next month
            } else {
            
                $timestamp = mktime(0, 0, 0, $_POST["month"] + 1, $i - $daysFromPreviousMonth - $daysInCurrentMonth, $_POST["year"]);
                $xtpl->assign("day", date("d", $timestamp));
                
                // switch days
                switch (date("w", $timestamp)) {
                
                    // if the day to show is a weekend
                    case 0:
                    case 6:
                    
                        $xtpl->parse("main.days_row.day.nextMonth_weekend" . ($timestamp == $preselectedTimestamp ? "_preselected" : ""));

                        break;
                        
                    // if the day to show is a weekday
                    default:
                    
                        $xtpl->parse("main.days_row.day.nextMonth_weekday" . ($timestamp == $preselectedTimestamp ? "_preselected" : ""));
                        
                }
                
            }
            
            $xtpl->parse("main.days_row.day");

            // 7 days in a row
            if ($i % 7 == 0) {

                $xtpl->parse("main.days_row");

            }

        }

        // parse the buttons
        $xtpl->parse("main.clear_date_button");
        
        if (!isset($_POST["action"])) {

            $xtpl->parse("main.defaults");

        }

        // if the button for going back to current month/year is to be shown
        if ($_POST["showGoToCurrentMonthYear"] == 1) {

            // pass on the current month and year
            $xtpl->assign("currentMonth", date("m"));

            $xtpl->assign("currentYear", date("Y"));
            
            // show the button
            $xtpl->parse("main.gotocurrent");
            
        }

        // assign the form action
        // previous to 1.0.7 this was used directly in the template file as {PHP._SERVER.SCRIPT_NAME} but
        // for some reason, on some installations, this would produce an empty string and therefore users
        // would not be able to change months
        // i suppose that's because XTemplate gets it's PHP variables from the GLOBAL array and it might be
        // that it is not always available...
        $xtpl->assign("formAction", $_SERVER["SCRIPT_NAME"]);

        // wrap up output generation
        $xtpl->parse("main");

        // output the date picker
        $xtpl->out("main");
        
    }

?>
