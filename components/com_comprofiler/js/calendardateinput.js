/***********************************************
 Fool-Proof Date Input Script with DHTML Calendar
 by Jason Moon - calendar@moonscript.com
 ************************************************/
/***********************************************
* Jason's Date Input Calendar- By Jason Moon http://calendar.moonscript.com/dateinput.cfm
* Script featured on and available at http://www.dynamicdrive.com
* Keep this notice intact for use.
***********************************************/

// CB: changed a few customizations below and search for 'CB' for changes below.
// Customizable variables
var cbcalDefaultDateFormat = 'MM/DD/YYYY'; // If no date format is supplied, this will be used instead
var cbcalHideWait = 4; // Number of seconds before the calendar will disappear
var cbcalY2kPivotPoint = 76; // 2-digit years before this point will be created in the 21st century
var cbcalUnselectedMonthText = ''; // Text to display in the 1st month list item when the date isn't required
var cbcalFontSize = 11; // In pixels
var cbcalFontSizeDay = 14; // In pixels
var cbcalFontFamily = 'Tahoma';
var cbcalCellWidth = 26;	//18
var cbcalCellHeight = 24; //16
var cbcalImageURL = cbTemplateDir + 'calendar_icon.jpg';
var cbcalNextURL = cbTemplateDir + 'calendar_next.gif';
var cbcalPrevURL = cbTemplateDir + 'calendar_prev.gif';
var cbcalCalBGColor = '#F4F4F4';
var cbcalTopRowBGColor = '#DDD';
var cbcalDayBGColor = '#CCCCFF';

// Global variables
var cbcalZCounter = 100;
var cbcalToday = new Date();
// var cbcalWeekDays = new Array('S','M','T','W','T','F','S');
var cbcalWeekDays = Calendar._SDN;
var cbcalMonthDays = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
// var cbcalMonthNames = new Array('January','February','March','April','May','June','July','August','September','October','November','December');
var cbcalMonthNames = Calendar._MN;

/* Write out the stylesheet definition for the calendar: moved to CB templates.
with (document) {
   writeln('<style>');
   writeln('td.calendarDateInput {letter-spacing:normal;line-height:normal;font-family:' + cbcalFontFamily + ',Sans-Serif;font-size:' + cbcalFontSize + 'px;text-align:center;vertical-align:middle;margin:0px;padding:0px;}');
   writeln('td.calendarDayInput {letter-spacing:normal;line-height:normal;font-family:' + cbcalFontFamily + ',Sans-Serif;font-size:' + cbcalFontSizeDay + 'px;text-align:center;vertical-align:middle;}');
   writeln('select.calendarDateInput {letter-spacing:.06em;font-family:Verdana,Sans-Serif;font-size:11px;}');
   writeln('input.calendarDateInput {letter-spacing:.06em;font-family:Verdana,Sans-Serif;font-size:11px;}');
   writeln('#cb_datetestb_Current_ID {text-align:center;}');
   writeln('</style>');
}
*/
// Only allows certain keys to be used in the date field
function cbcalYearDigitsOnly(e) {
   var KeyCode = (e.keyCode) ? e.keyCode : e.which;
   return ((KeyCode == 8) // backspace
        || (KeyCode == 9) // tab
        || (KeyCode == 37) // left arrow
        || (KeyCode == 39) // right arrow
        || (KeyCode == 46) // delete
        || ((KeyCode > 47) && (KeyCode < 58)) // 0 - 9
   );
}

// Gets the absolute pixel position of the supplied element
function cbcalGetTagPixels(StartTag, Direction) {
   var PixelAmt = (Direction == 'LEFT') ? StartTag.offsetLeft : StartTag.offsetTop;
   while ((StartTag.tagName != 'BODY') && (StartTag.tagName != 'HTML')) {
      StartTag = StartTag.offsetParent;
      PixelAmt += (Direction == 'LEFT') ? StartTag.offsetLeft : StartTag.offsetTop;
   }
   return PixelAmt;
}

// Is the specified select-list behind the calendar?
function cbcalBehindCal(SelectList, CalLeftX, CalRightX, CalTopY, CalBottomY, ListTopY) {
   var ListLeftX = cbcalGetTagPixels(SelectList, 'LEFT');
   var ListRightX = ListLeftX + SelectList.offsetWidth;
   var ListBottomY = ListTopY + SelectList.offsetHeight;
   return (((ListTopY < CalBottomY) && (ListBottomY > CalTopY)) && ((ListLeftX < CalRightX) && (ListRightX > CalLeftX)));
}

// For IE, hides any select-lists that are behind the calendar
function cbcalFixSelectLists(Over) {
   if (navigator.appName == 'Microsoft Internet Explorer') {
      var CalDiv = this.getCalendar();
      var CalLeftX = CalDiv.offsetLeft;
      var CalRightX = CalLeftX + CalDiv.offsetWidth;
      var CalTopY = CalDiv.offsetTop;
      var CalBottomY = CalTopY + (cbcalCellHeight * 9);
      var FoundCalInput = false;
      formLoop :
      for (var j=this.formNumber;j<document.forms.length;j++) {
         for (var i=0;i<document.forms[j].elements.length;i++) {
            if (typeof document.forms[j].elements[i].type == 'string') {
               if ((document.forms[j].elements[i].type == 'hidden') && (document.forms[j].elements[i].name == this.hiddenFieldName)) {
                  FoundCalInput = true;
                  i += 3; // 3 elements between the 1st hidden field and the last year input field
               }
               if (FoundCalInput) {
                  if (document.forms[j].elements[i].type.substr(0,6) == 'select') {
                     ListTopY = cbcalGetTagPixels(document.forms[j].elements[i], 'TOP');
                     if (ListTopY < CalBottomY) {
                        if (cbcalBehindCal(document.forms[j].elements[i], CalLeftX, CalRightX, CalTopY, CalBottomY, ListTopY)) {
                           document.forms[j].elements[i].style.visibility = (Over) ? 'hidden' : 'visible';
                        }
                     }
                     else break formLoop;
                  }
               }
            }
         }
      }
   }
}

// Displays a message in the status bar when hovering over the calendar days
function cbcalDayCellHover(Cell, Over, Color, HoveredDay) {
   Cell.style.backgroundColor = (Over) ? cbcalDayBGColor : Color;
   if (Over) {
      if ((this.yearValue == cbcalToday.getFullYear()) && (this.monthIndex == cbcalToday.getMonth()) && (HoveredDay == cbcalToday.getDate())) self.status = 'Click to select today';
      else {
         var Suffix = HoveredDay.toString();
         switch (Suffix.substr(Suffix.length - 1, 1)) {
            case '1' : Suffix += (HoveredDay == 11) ? 'th' : 'st'; break;
            case '2' : Suffix += (HoveredDay == 12) ? 'th' : 'nd'; break;
            case '3' : Suffix += (HoveredDay == 13) ? 'th' : 'rd'; break;
            default : Suffix += 'th'; break;
         }
         self.status = 'Click to select ' + this.monthName + ' ' + Suffix;
      }
   }
   else self.status = '';
   return true;
}

// Sets the form elements after a day has been picked from the calendar
function cbcalPickDisplayDay(ClickedDay) {
   this.show();
   var MonthList = this.getMonthList();
   var DayList = this.getDayList();
   var YearField = this.getYearField();
   var SpaceForBlank = ( ( DayList.options[0].value == '' ) ? 1 : 0 );
   cbcalFixDayList(DayList, cbcalGetDayCount(this.displayed.yearValue, this.displayed.monthIndex));
   // Select the month and day in the lists
   for (var i=0;i<MonthList.length;i++) {
      if (MonthList.options[i].value == this.displayed.monthIndex) MonthList.options[i].selected = true;
   }
   for (var j=1;j<=(DayList.length-SpaceForBlank);j++) {
      if (j == ClickedDay) DayList.options[j-1+SpaceForBlank].selected = true;
   }
   this.setPicked(this.displayed.yearValue, this.displayed.monthIndex, ClickedDay);
   // Change the year, if necessary
   YearField.value = this.picked.yearPad;
   YearField.defaultValue = YearField.value;
   this.hideElements(false);	//CBB added
}

// Builds the HTML for the calendar days
function cbcalBuildCalendarDays() {
   var Rows = 5;
   if (((this.displayed.dayCount == 31) && (this.displayed.firstDay > 4)) || ((this.displayed.dayCount == 30) && (this.displayed.firstDay == 6))) Rows = 6;
   else if ((this.displayed.dayCount == 28) && (this.displayed.firstDay == 0)) Rows = 4;
   var HTML = '<table width="' + (cbcalCellWidth * 7) + '" cellspacing="0" cellpadding="1" style="cursor:default">';
   for (var j=0;j<Rows;j++) {
      HTML += '<tr>';
      for (var i=1;i<=7;i++) {
         Day = (j * 7) + (i - this.displayed.firstDay);
         if ((Day >= 1) && (Day <= this.displayed.dayCount)) {
            if ((this.displayed.yearValue == this.picked.yearValue) && (this.displayed.monthIndex == this.picked.monthIndex) && (Day == this.picked.day)) {
               TextStyle = 'color:white;font-weight:bold;';
               BackColor = cbcalDayBGColor;
            }
            else {
               TextStyle = 'color:black;';
               BackColor = cbcalCalBGColor;
            }
            if ((this.displayed.yearValue == cbcalToday.getFullYear()) && (this.displayed.monthIndex == cbcalToday.getMonth()) && (Day == cbcalToday.getDate())) TextStyle += 'border:1px solid darkred;padding:0px;';
            HTML += '<td align="center" class="calendarDayInput" style="cursor:default;height:' + cbcalCellHeight + 'px;width:' + cbcalCellWidth + 'px;' + TextStyle + ';background-color:' + BackColor + '" onClick="' + this.objName + '.pickDay(' + Day + ')" onMouseOver="return ' + this.objName + '.displayed.dayHover(this,true,\'' + BackColor + '\',' + Day + ')" onMouseOut="return ' + this.objName + '.displayed.dayHover(this,false,\'' + BackColor + '\')">' + Day + '</td>';
         }
         else HTML += '<td class="calendarDateInput" style="height:' + cbcalCellHeight + '">&nbsp;</td>';
      }
      HTML += '</tr>';
   }
   return HTML += '</table>';
}

// Determines which century to use (20th or 21st) when dealing with 2-digit years
function cbcalGetGoodYear(YearDigits) {
   if (YearDigits > 100 ) {
   	return YearDigits;
   } else {
   	  var YearLastDigits = parseInt(YearDigits,10) % 100;
      var Millennium = (YearLastDigits < cbcalY2kPivotPoint) ? 2000 : 1900;
      return Millennium + YearLastDigits;
   }
}

// Returns the number of days in a month (handles leap-years)
function cbcalGetDayCount(SomeYear, SomeMonth) {
   return ((SomeMonth == 1) && ((SomeYear % 400 == 0) || ((SomeYear % 4 == 0) && (SomeYear % 100 != 0)))) ? 29 : cbcalMonthDays[SomeMonth];
}

// Highlights the buttons
function cbcalVirtualButton(Cell, ButtonDown) {
   if (ButtonDown) {
      Cell.style.borderLeft = 'buttonshadow 1px solid';
      Cell.style.borderTop = 'buttonshadow 1px solid';
      Cell.style.borderBottom = 'buttonhighlight 1px solid';
      Cell.style.borderRight = 'buttonhighlight 1px solid';
   }
   else {
      Cell.style.borderLeft = 'buttonhighlight 1px solid';
      Cell.style.borderTop = 'buttonhighlight 1px solid';
      Cell.style.borderBottom = 'buttonshadow 1px solid';
      Cell.style.borderRight = 'buttonshadow 1px solid';
   }
}

// Mouse-over for the previous/next month buttons
function cbcalNeighborHover(Cell, Over, DateObj) {
   if (Over) {
      cbcalVirtualButton(Cell, false);
      self.status = 'Click to view ' + DateObj.fullName;
   }
   else {
      Cell.style.border = 'buttonface 1px solid';
      self.status = '';
   }
   return true;
}

// Adds/removes days from the day list, depending on the month/year
function cbcalFixDayList(DayList, NewDays) {
   var SpaceForBlank = ( ( DayList.options[0].value == '' ) ? 1 : 0 );
   var DayPick = DayList.selectedIndex + 1 - SpaceForBlank;
   if (DayPick == 0) {
      DayPick = 1;
      DayList.options[DayPick-1+SpaceForBlank].selected = true;
   }
   if (NewDays != ( DayList.length - SpaceForBlank )) {
      var OldSize = DayList.length - SpaceForBlank;
      for (var k=Math.min(NewDays,OldSize);k<Math.max(NewDays,OldSize);k++) {
         (k >= NewDays) ? DayList.options[NewDays+SpaceForBlank] = null : DayList.options[k+SpaceForBlank] = new Option(k+1, k+1);
      }
      DayPick = Math.min(DayPick, NewDays);
      DayList.options[DayPick-1+SpaceForBlank].selected = true;
   }
   return DayPick;
}

// Adds/removes days from the day list, depending on the month/year
function cbcalFixYearList(YearList, pickedYearValue) {
	var SpaceForBlank = ( ( YearList.options[0].value == '' ) ? 1 : 0 );
	if ( YearList.options[1].value < 100 ) {
		pickedYearValue = pickedYearValue % 100;
	}
	var YearPick = YearList.options[YearList.selectedIndex].value;
	for (var k=SpaceForBlank;k<YearList.options.length;k++) {
		if ( YearList.options[k].value == pickedYearValue ) {
			YearPick = pickedYearValue;
			break;
		}
	}
	if ( k < YearList.options.length ) {
		YearList.options[k].selected = true;
	}
	return YearPick;
}

// Resets the year to its previous valid value when something invalid is entered
function FixYearInput(YearField) {
   var YearRE = new RegExp('\\d{' + YearField.defaultValue.length + '}');
   if (!YearRE.test(YearField.value)) YearField.value = YearField.defaultValue;
}

// Displays a message in the status bar when hovering over the calendar icon
function cbcalCalIconHover(Over) {
   var Message = (this.isShowing()) ? 'hide' : 'show';
   self.status = (Over) ? 'Click to ' + Message + ' the calendar' : '';
   return true;
}

// Starts the timer over from scratch
function cbcalCalTimerReset() {
   eval('clearTimeout(' + this.timerID + ')');
   eval(this.timerID + '=setTimeout(\'' + this.objName + '.show()\',' + (cbcalHideWait * 1000) + ')');
}

// The timer for the calendar
function cbcalDoTimer(CancelTimer) {
   if (CancelTimer) eval('clearTimeout(' + this.timerID + ')');
   else {
      eval(this.timerID + '=null');
      this.resetTimer();
   }
}

// Show or hide the calendar
function cbcalShowCalendar() {
   if (this.isShowing()) {
      var StopTimer = true;
      this.getCalendar().style.zIndex = --cbcalZCounter;
      this.getCalendar().style.visibility = 'hidden';
      this.fixSelects(false);
   }
   else {
      var StopTimer = false;
      this.fixSelects(true);
      this.getCalendar().style.zIndex = ++cbcalZCounter;
      this.getCalendar().style.visibility = 'visible';
   }
   this.handleTimer(StopTimer);
   self.status = '';
}

// Hides the input elements when the "blank" month is selected
function cbcalSetElementStatus(Hide) {
   // this.getDayList().style.visibility = (Hide) ? 'hidden' : 'visible';
   if ( this.yeardropdownstop == '' ) {
	   this.getYearField().style.visibility = (Hide) ? 'hidden' : 'visible';
   }
/*
   if (Hide) {
      this.getYearField().value = '';
   } else {
      this.getYearField().style.visibility = (Hide) ? 'hidden' : 'visible';
   }
*/
   // this.getCalendarLink().style.visibility = (Hide) ? 'hidden' : 'visible';
}

// Sets the date, based on the year selected (in case year is a drop-down)
function cbcalCheckYearChange(YearList) {
   var DayList = this.getDayList();
   var MonthList = this.getMonthList();

   if (YearList.options[YearList.selectedIndex].value == '') {
      var DayPick = cbcalFixDayList(DayList, 31);
      DayList.selectedIndex = 0;
      MonthList.selectedIndex = 0;
      this.hideElements(true);
      this.setHidden('');
   } else {
      this.hideElements(false);
      if (this.isShowing()) {
         this.resetTimer(); // Gives the user more time to view the calendar with the newly-selected month
         this.getCalendar().style.zIndex = ++cbcalZCounter; // Make sure this calendar is on top of any other calendars
      }
      if ( MonthList.selectedIndex == 0 ) {
      	 MonthList.selectedIndex = 1;
      }
      var DayPick = cbcalFixDayList(DayList, cbcalGetDayCount(cbcalGetGoodYear(YearList.options[YearList.selectedIndex].value), MonthList.options[MonthList.selectedIndex].value));
      this.setPicked(YearList.options[YearList.selectedIndex].value, MonthList.options[MonthList.selectedIndex].value, DayPick);
   }
}

// Sets the date, based on the month selected
function cbcalCheckMonthChange(MonthList) {
   var DayList = this.getDayList();
   var YearList = this.getYearField();
   if (MonthList.options[MonthList.selectedIndex].value == '') {
      var DayPick = cbcalFixDayList(DayList, 31);
      if (this.yeardropdownstop != '') {
	      YearList.selectedIndex = 0;
      }
      MonthList.selectedIndex = 0;
      DayList.selectedIndex = 0;
      this.hideElements(true);
      this.setHidden('');
   } else {
      this.hideElements(false);
      if (this.isShowing()) {
         this.resetTimer(); // Gives the user more time to view the calendar with the newly-selected month
         this.getCalendar().style.zIndex = ++cbcalZCounter; // Make sure this calendar is on top of any other calendars
      }
      var DayPick = cbcalFixDayList(DayList, cbcalGetDayCount(this.picked.yearValue, MonthList.options[MonthList.selectedIndex].value));
      this.setPicked(this.picked.yearValue, MonthList.options[MonthList.selectedIndex].value, DayPick);
      if ((this.yeardropdownstop != '') && (YearList.selectedIndex == 0)) {
      	 var YearPick = cbcalFixYearList(YearList, this.picked.yearValue);
      }
   }
}

// Sets the date, based on the day selected
function cbcalCheckDayChange(DayList) {
   var SpaceForBlank = ( ( DayList.options[0].value == '' ) ? 1 : 0 );

   var MonthList = this.getMonthList();
   var YearList = this.getYearField();
   if (DayList.options[DayList.selectedIndex].value == '') {
      var DayPick = cbcalFixDayList(DayList, 31);
      if (this.yeardropdownstop != '') {
	      YearList.selectedIndex = 0;
      }
      MonthList.selectedIndex = 0;
      DayList.selectedIndex = 0;
      this.hideElements(true);
      this.setHidden('');
   } else {
      this.hideElements(false);
      if (this.isShowing()) {
         this.resetTimer(); // Gives the user more time to view the calendar with the newly-selected month
         this.getCalendar().style.zIndex = ++cbcalZCounter; // Make sure this calendar is on top of any other calendars
         // this.show();
      }
      if ( MonthList.selectedIndex == 0 ) {
      	 MonthList.selectedIndex = 1;
      }
//   if (this.isShowing()) this.show();
      var DayPick = cbcalFixDayList(DayList, cbcalGetDayCount(this.picked.yearValue, MonthList.options[MonthList.selectedIndex].value));
      this.setPicked(this.picked.yearValue, this.picked.monthIndex, DayList.options[DayList.selectedIndex].value);	//DayList.selectedIndex + 1 - SpaceForBlank);
      if ((this.yeardropdownstop != '') && (YearList.selectedIndex == 0)) {
      	 var YearPick = cbcalFixYearList(YearList, this.picked.yearValue);
      }
   }
}

// Changes the date when a valid year has been entered
function cbcalCheckYearInput(YearField) {
   if ((YearField.value.length == YearField.defaultValue.length) && (YearField.defaultValue != YearField.value)) {
      if (this.isShowing()) {
         this.resetTimer(); // Gives the user more time to view the calendar with the newly-entered year
         this.getCalendar().style.zIndex = ++cbcalZCounter; // Make sure this calendar is on top of any other calendars
      }
      var NewYear = cbcalGetGoodYear(YearField.value);
      var MonthList = this.getMonthList();
      var NewDay = cbcalFixDayList(this.getDayList(), cbcalGetDayCount(NewYear, MonthList.options[MonthList.selectedIndex].value));
      this.setPicked(NewYear, MonthList.options[MonthList.selectedIndex].value, NewDay);
      YearField.defaultValue = YearField.value;
   }
}

// OBJECTS:
// ========

// Holds characteristics about a date
function cbcalDateObject() {
   if (Function.call) { // Used when 'call' method of the Function object is supported
      var ParentObject = this;
      var ArgumentStart = 0;
   }
   else { // Used with 'call' method of the Function object is NOT supported
      var ParentObject = arguments[0];
      var ArgumentStart = 1;
   }
   ParentObject.date = (arguments.length == (ArgumentStart+1)) ? new Date(arguments[ArgumentStart+0]) : new Date(cbcalGetGoodYear(arguments[ArgumentStart+0]), arguments[ArgumentStart+1], arguments[ArgumentStart+2]);
   ParentObject.yearValue = ParentObject.date.getFullYear();
   ParentObject.monthIndex = ParentObject.date.getMonth();
   ParentObject.monthName = cbcalMonthNames[ParentObject.monthIndex];
   ParentObject.fullName = ParentObject.monthName + ' ' + ParentObject.yearValue;
   ParentObject.day = ParentObject.date.getDate();
   ParentObject.dayCount = cbcalGetDayCount(ParentObject.yearValue, ParentObject.monthIndex);
   var FirstDate = new Date(ParentObject.yearValue, ParentObject.monthIndex, 1);
   ParentObject.firstDay = FirstDate.getDay();
}

// Keeps track of the date that goes into the hidden field
function cbcalStoredMonthObject(DateFormat, DateYear, DateMonth, DateDay) {
   (Function.call) ? cbcalDateObject.call(this, DateYear, DateMonth, DateDay) : cbcalDateObject(this, DateYear, DateMonth, DateDay);
   this.yearPad = this.yearValue.toString();
   this.monthPad = (this.monthIndex < 9) ? '0' + String(this.monthIndex + 1) : this.monthIndex + 1;
   this.dayPad = (this.day < 10) ? '0' + this.day.toString() : this.day;
   this.monthShort = this.monthName.substr(0,3).toUpperCase();
   // Formats the year with 2 digits instead of 4
   if (DateFormat.indexOf('YYYY') == -1) this.yearPad = this.yearPad.substr(2);
   // Define the date-part delimiter
   if (DateFormat.indexOf('/') >= 0) var Delimiter = '/';
   else if (DateFormat.indexOf('-') >= 0) var Delimiter = '-';
   else if (DateFormat.indexOf('.') >= 0) var Delimiter = '.';	// CB: added line
   else var Delimiter = '';
   // Determine the order of the months and days
   if (/DD?.?((MON)|(MM?M?))/.test(DateFormat)) {
      this.formatted = this.dayPad + Delimiter;
      this.formatted += (RegExp.$1.length == 3) ? this.monthShort : this.monthPad;
   }
   else if (/((MON)|(MM?M?))?.?DD?/.test(DateFormat)) {
      this.formatted = (RegExp.$1.length == 3) ? this.monthShort : this.monthPad;
      this.formatted += Delimiter + this.dayPad;
   }
   // Either prepend or append the year to the formatted date
   this.formatted = (DateFormat.substr(0,2) == 'YY') ? this.yearPad + Delimiter + this.formatted : this.formatted + Delimiter + this.yearPad;
}

// Object for the current displayed month
function cbcalDisplayMonthObject(ParentObject, DateYear, DateMonth, DateDay) {
   (Function.call) ? cbcalDateObject.call(this, DateYear, DateMonth, DateDay) : cbcalDateObject(this, DateYear, DateMonth, DateDay);
   this.displayID = ParentObject.hiddenFieldName + '_Current_ID';
   this.getDisplay = new Function('return document.getElementById(this.displayID)');
   this.dayHover = cbcalDayCellHover;
   this.goCurrent = new Function(ParentObject.objName + '.getCalendar().style.zIndex=++cbcalZCounter;' + ParentObject.objName + '.setDisplayed(cbcalToday.getFullYear(),cbcalToday.getMonth());');
   if (ParentObject.formNumber >= 0) this.getDisplay().innerHTML = this.fullName;
}

// Object for the previous/next buttons
function cbcalNeighborMonthObject(ParentObject, IDText, DateMS) {
   (Function.call) ? cbcalDateObject.call(this, DateMS) : cbcalDateObject(this, DateMS);
   this.buttonID = ParentObject.hiddenFieldName + '_' + IDText + '_ID';
   this.hover = new Function('C','O','cbcalNeighborHover(C,O,this)');
   this.getButton = new Function('return document.getElementById(this.buttonID)');
   this.go = new Function(ParentObject.objName + '.getCalendar().style.zIndex=++cbcalZCounter;' + ParentObject.objName + '.setDisplayed(this.yearValue,this.monthIndex);');
   if (ParentObject.formNumber >= 0) this.getButton().title = this.monthName;
}

// Sets the currently-displayed month object
function cbcalSetDisplayedMonth(DispYear, DispMonth) {
   this.displayed = new cbcalDisplayMonthObject(this, DispYear, DispMonth, 1);
   // Creates the previous and next month objects
   this.previous = new cbcalNeighborMonthObject(this, 'Previous', this.displayed.date.getTime() - 86400000);
   this.next = new cbcalNeighborMonthObject(this, 'Next', this.displayed.date.getTime() + (86400000 * (this.displayed.dayCount + 1)));
   // Creates the HTML for the calendar
   if (this.formNumber >= 0) this.getDayTable().innerHTML = this.buildCalendar();
}

// Sets the current selected date
function cbcalSetPickedMonth(PickedYear, PickedMonth, PickedDay) {
   this.picked = new cbcalStoredMonthObject(this.format, PickedYear, PickedMonth, PickedDay);
   this.setHidden(this.picked.formatted);
   this.setDisplayed(PickedYear, PickedMonth);
}

// The calendar object
function cbcalCalendarObject(DateName, DateFormat, DefaultDate, YearDropDownStop) {

   /* Properties */
   this.hiddenFieldName = DateName;
   this.monthListID = DateName + '_Month_ID';
   this.dayListID = DateName + '_Day_ID';
   this.yearFieldID = DateName + '_Year_ID';
   this.monthDisplayID = DateName + '_Current_ID';
   this.calendarID = DateName + '_ID';
   this.dayTableID = DateName + '_DayTable_ID';
   this.calendarLinkID = this.calendarID + '_Link';
   this.timerID = this.calendarID + '_Timer';
   this.objName = DateName + '_Object';
   this.format = DateFormat;
   this.formNumber = -1;
   this.picked = null;
   this.displayed = null;
   this.previous = null;
   this.next = null;
   this.yeardropdownstop = YearDropDownStop;

   /* Methods */
   this.setPicked = cbcalSetPickedMonth;
   this.setDisplayed = cbcalSetDisplayedMonth;
   this.checkYear = cbcalCheckYearInput;
   this.fixYear = FixYearInput;
   this.changeYear = cbcalCheckYearChange;
   this.changeMonth = cbcalCheckMonthChange;
   this.changeDay = cbcalCheckDayChange;
   this.resetTimer = cbcalCalTimerReset;
   this.hideElements = cbcalSetElementStatus;
   this.show = cbcalShowCalendar;
   this.handleTimer = cbcalDoTimer;
   this.iconHover = cbcalCalIconHover;
   this.buildCalendar = cbcalBuildCalendarDays;
   this.pickDay = cbcalPickDisplayDay;
   this.fixSelects = cbcalFixSelectLists;
   this.setHidden = new Function('D','if (this.formNumber >= 0) this.getHiddenField().value=D');
   // Returns a reference to these elements
   this.getHiddenField = new Function('return document.forms[this.formNumber].elements[this.hiddenFieldName]');
   this.getMonthList = new Function('return document.getElementById(this.monthListID)');
   this.getDayList = new Function('return document.getElementById(this.dayListID)');
   this.getYearField = new Function('return document.getElementById(this.yearFieldID)');
   this.getCalendar = new Function('return document.getElementById(this.calendarID)');
   this.getDayTable = new Function('return document.getElementById(this.dayTableID)');
   this.getCalendarLink = new Function('return document.getElementById(this.calendarLinkID)');
   this.getMonthDisplay = new Function('return document.getElementById(this.monthDisplayID)');
   this.isShowing = new Function('return !(this.getCalendar().style.visibility != \'visible\')');

   /* Constructor */
   // Functions used only by the constructor
   function getMonthIndex(MonthAbbr) { // Returns the index (0-11) of the supplied month abbreviation
      for (var MonPos=0;MonPos<cbcalMonthNames.length;MonPos++) {
         if (cbcalMonthNames[MonPos].substr(0,3).toUpperCase() == MonthAbbr.toUpperCase()) break;
      }
      return MonPos;
   }
   function SetGoodDate(CalObj, Notify) { // Notifies the user about their bad default date, and sets the current system date
      CalObj.setPicked(cbcalToday.getFullYear(), cbcalToday.getMonth(), cbcalToday.getDate());
      if (Notify) alert('WARNING: The supplied date is not in valid \'' + DateFormat + '\' format: ' + DefaultDate + '.\nTherefore, the current system date will be used instead: ' + CalObj.picked.formatted);
   }
   // Main part of the constructor
   if (DefaultDate != '') {
      if ((this.format == 'YYYYMMDD') && (/^(\d{4})(\d{2})(\d{2})$/.test(DefaultDate))) this.setPicked(RegExp.$1, parseInt(RegExp.$2,10)-1, RegExp.$3);
      else {
         // Get the year
         if ((this.format.substr(0,2) == 'YY') && (/^(\d{2,4})(-|\/|\.)/.test(DefaultDate))) { // Year is at the beginning
            var YearPart = cbcalGetGoodYear(RegExp.$1);
            // Determine the order of the months and days
            if (/(-|\/|\.)(\w{1,3})(-|\/|\.)(\w{1,3})$/.test(DefaultDate)) {
               var MidPart = RegExp.$2;
               var EndPart = RegExp.$4;
               if (/D$/.test(this.format)) { // Ends with days
                  var DayPart = EndPart;
                  var MonthPart = MidPart;
               }
               else {
                  var DayPart = MidPart;
                  var MonthPart = EndPart;
               }
               MonthPart = (/\d{1,2}/i.test(MonthPart)) ? parseInt(MonthPart,10)-1 : getMonthIndex(MonthPart);
               this.setPicked(YearPart, MonthPart, DayPart);
            }
            else SetGoodDate(this, true);
         }
         else if (/(-|\/|\.)(\d{2,4})$/.test(DefaultDate)) { // Year is at the end
            var YearPart = cbcalGetGoodYear(RegExp.$2);
            // Determine the order of the months and days
            if (/^(\w{1,3})(-|\/|\.)(\w{1,3})(-|\/|\.)/.test(DefaultDate)) {
               if (this.format.substr(0,1) == 'D') { // Starts with days
                  var DayPart = RegExp.$1;
                  var MonthPart = RegExp.$3;
               }
               else { // Starts with months
                  var MonthPart = RegExp.$1;
                  var DayPart = RegExp.$3;
               }
               MonthPart = (/\d{1,2}/i.test(MonthPart)) ? parseInt(MonthPart,10)-1 : getMonthIndex(MonthPart);
               this.setPicked(YearPart, MonthPart, DayPart);
            }
            else SetGoodDate(this, true);
         }
         else SetGoodDate(this, true);
      }
   }
}
// CB: added month, day, year functions:
function cbcalHtmlMonth( DateName, Required, DefaultDate, InitialStatus ) {
//	var r =	'<span>';
	var r =	'';
	r	+=	'<select class="inputbox" id="' + DateName + '_Month_ID" onChange="' + DateName + '_Object.changeMonth(this)">';	// changed class from calendarDateInput to inputbox
	if (!Required) {
		var NoneSelected = (DefaultDate == '') ? ' selected' : '';
		r	+=	'<option value=""' + NoneSelected + '>' + cbcalUnselectedMonthText + '</option>';
	}
	for (var i=0;i<12;i++) {
		var MonthSelected = ((DefaultDate != '') && (eval(DateName + '_Object.picked.monthIndex') == i)) ? ' selected' : '';
		r	+=	'<option value="' + i + '"' + MonthSelected + '>' + cbcalMonthNames[i] /* .substr(0,3) */ + '</option>';
	}
//	r		+=	'</select></span>';
	r		+=	'</select>';
	return r;
}
function cbcalHtmlDay( DateName, Required, DefaultDate, InitialStatus ) {
//	var r =	'<span><select' /*CBB uncomment this to hide select by default: + InitialStatus */ + ' class="inputbox" id="' + DateName + '_Day_ID" onChange="' + DateName + '_Object.changeDay(this)">';		// changed class from calendarDateInput to inputbox
	var r =	'<select' /*CBB uncomment this to hide select by default: + InitialStatus */ + ' class="inputbox" id="' + DateName + '_Day_ID" onChange="' + DateName + '_Object.changeDay(this)">';		// changed class from calendarDateInput to inputbox
	if (!Required) {
		var NoneSelected = (DefaultDate == '') ? ' selected' : '';
		r	+=	'<option value=""' + NoneSelected + '>' + cbcalUnselectedMonthText + '</option>';
	}
	for (var j=1;j<=eval(DateName + '_Object.picked.dayCount');j++) {
		var DaySelected = ((DefaultDate != '') && (eval(DateName + '_Object.picked.day') == j)) ? ' selected' : '';
		r	+=	'<option value="' + j + '"' + DaySelected + '>' + j + '</option>';
	}
//	r		+=	'</select></span>';
	r		+=	'</select>';
	return r;
}
function cbcalHtmlYearDropDown( DateName, Required, DefaultDate, InitialStatus, YearDropDownStop, YearMin, YearMax ) {
//	var r =	'<span><select' /*CBB uncomment this to hide select by default: + InitialStatus */ + ' class="inputbox" id="' + DateName + '_Year_ID" onChange="' + DateName + '_Object.changeYear(this)">';		// changed class from calendarDateInput to inputbox
	var r =	'<select' /*CBB uncomment this to hide select by default: + InitialStatus */ + ' class="inputbox" id="' + DateName + '_Year_ID" onChange="' + DateName + '_Object.changeYear(this)">';		// changed class from calendarDateInput to inputbox
	if (!Required) {
		var NoneSelected = (DefaultDate == '') ? ' selected' : '';
		r	+=	'<option value=""' + NoneSelected + '>' + cbcalUnselectedMonthText + '</option>';
	}
	var digits = eval(DateName + '_Object.picked.yearPad.length');
	var defaultYear = eval(DateName + '_Object.picked.yearPad');
	var yearsToShow, modulo,y;
	modulo = 10000;
	if ( YearMin === null && YearMax === null ) {
		yearsToShow = 220;
		y = defaultYear - 107;
	} else {
		yearsToShow = YearMax - YearMin + 1;
		y = YearMin;
	}
	if (digits == 2) {
		if ( yearsToShow > 100 ) {
			yearsToShow = 100;
		}
		modulo = 100;
		if ( YearMin === null && YearMax === null ) {
			y = cbcalY2kPivotPoint;
		} else {
			y = YearMin % modulo;
		}
	}
	
	for (var j=0;j<yearsToShow;j++) {
		var YearSelected = ((DefaultDate != '') && (eval(DateName + '_Object.picked.yearPad') == y)) ? ' selected' : '';
		var yy = (y < 10 ? '0':'') + y;
		r	+=	'<option value="' + yy + '"' + YearSelected + '>' + yy + '</option>';
		y	=	(y + 1) % modulo;
	}
//	r		+=	'</select></span>';
	r		+=	'</select>';
	return r;
}

function cbcalHtmlYear( DateName, Required, DefaultDate, InitialStatus ) {
//	return '<span><input' + InitialStatus + ' class="inputbox" type="text" id="' + DateName + '_Year_ID" size="' + eval(DateName + '_Object.picked.yearPad.length') + '" maxlength="' + eval(DateName + '_Object.picked.yearPad.length') + '" title="Year" value="' + eval(DateName + '_Object.picked.yearPad') + '" onKeyPress="return cbcalYearDigitsOnly(event)" onKeyUp="' + DateName + '_Object.checkYear(this)" onBlur="' + DateName + '_Object.fixYear(this)" /></span>';
	return '<input' + InitialStatus + ' class="inputbox" type="text" id="' + DateName + '_Year_ID" size="' + eval(DateName + '_Object.picked.yearPad.length') + '" maxlength="' + eval(DateName + '_Object.picked.yearPad.length') + '" title="Year" value="' + eval(DateName + '_Object.picked.yearPad') + '" onKeyPress="return cbcalYearDigitsOnly(event)" onKeyUp="' + DateName + '_Object.checkYear(this)" onBlur="' + DateName + '_Object.fixYear(this)" />';
	// changed class from calendarDateInput to inputbox
}
function cbcalHtmlYmdReplace( DateName, Required, DateFormat, DefaultDate, InitialStatus, YearDropDownStop, YearMin, YearMax ) {
	var m = cbcalHtmlMonth( DateName, Required, DefaultDate, InitialStatus );
	var d = cbcalHtmlDay(   DateName, Required, DefaultDate, InitialStatus );
	if ( YearDropDownStop == '' ) {
		var y = cbcalHtmlYear(  DateName, Required, DefaultDate, InitialStatus );
	} else {
		var y = cbcalHtmlYearDropDown(  DateName, Required, DefaultDate, InitialStatus, YearDropDownStop, YearMin, YearMax );
	}
	var c = 0;
	var formatted = DateFormat.replace(/(Y{2,4})|((MON)|(MM?M?))|(DD?)/g,
		function(thematch) {
			var r = '';
			if (/(Y{2,4})/g.test(thematch) ) {
				r = y;
			} else if (/((MON)|(MM?M?))/.test(thematch) ) {
				r = m;
			} else if (/(DD?)/.test(thematch) ) {
				r = d;
			}
			if (c++) {
				r = '&nbsp;&nbsp;' + r;
			}
			r += '&nbsp;';
			return r;
		}
	);
	return formatted;
}
// Main function that creates the form elements: CB: added last optional argument to handle arrays
function cbcalDateHtml(DateName, Required, DateFormat, DefaultDate, DateFieldName, AdditionalInputAttributes, YearDropDownStop, YearMin, YearMax) {
   var calhtml;
   if (arguments.length == 0) calhtml = ('<span style="color:red;font-size:1px;font-family:Tahoma;">ERROR: Missing required parameter in call to \'cbcalDateInput\': [name of hidden date field].</span>');
   else {
      // Handle DateFormat
      if (arguments.length < 3) { // The format wasn't passed in, so use default
         DateFormat = cbcalDefaultDateFormat;
         if (arguments.length < 2) Required = false;
      }
      else if (/^(Y{2,4}(-|\/|\.)?)?((MON)|(MM?M?)|(DD?))(-|\/|\.)?((MON)|(MM?M?)|(DD?))((-|\/|\.)Y{2,4})?$/i.test(DateFormat)) DateFormat = DateFormat.toUpperCase();			// CB: added '\.'
      else { // Passed-in DateFormat was invalid, use default format instead
         var AlertMessage = 'WARNING: The supplied date format for the \'' + DateName + '\' field is not valid: ' + DateFormat + '\nTherefore, the default date format will be used instead: ' + cbcalDefaultDateFormat;
         DateFormat = cbcalDefaultDateFormat;
         if (arguments.length == 4) { // DefaultDate was passed in with an invalid date format
            var CurrentDate = new cbcalStoredMonthObject(DateFormat, cbcalToday.getFullYear(), cbcalToday.getMonth(), cbcalToday.getDate());
            AlertMessage += '\n\nThe supplied date (' + DefaultDate + ') cannot be interpreted with the invalid format.\nTherefore, the current system date will be used instead: ' + CurrentDate.formatted;
            DefaultDate = CurrentDate.formatted;
         }
         alert(AlertMessage);
      }
      // Define the current date if it wasn't set already
      if (!CurrentDate) var CurrentDate = new cbcalStoredMonthObject(DateFormat, cbcalToday.getFullYear(), cbcalToday.getMonth(), cbcalToday.getDate());
      // Handle DefaultDate
      if ((arguments.length < 4) || (DefaultDate=='') ) { // The date wasn't passed in   CB: added the or statement to allow also empty string as default
         DefaultDate = (Required) ? CurrentDate.formatted : ''; // If required, use today's date
      }
      // CB 6 lines: Handle DateFieldName and Attrs:
      if (arguments.length < 5) { // The DateFieldName wasn't passed in
         DateFieldName = DateName;
      }
      if (arguments.length < 6) { // The AdditionalInputAttributes wasn't passed in
         AdditionalInputAttributes = '';
      }
      if (arguments.length < 7) { // The YearDropDownStop wasn't passed in
         YearDropDownStop = '1';
      }
      if (arguments.length < 8) { // The YearMin wasn't passed in
         YearMin = null;
      }
      if (arguments.length < 9) { // The YearMax wasn't passed in
         YearMax = null;
      }
      // Creates the calendar object!
      eval(DateName + '_Object=new cbcalCalendarObject(\'' + DateName + '\',\'' + DateFormat + '\',\'' + DefaultDate + '\',\'' + YearDropDownStop + '\')');
      // Determine initial viewable state of day, year, and calendar icon
      if ((Required) || ( (arguments.length >= 4) && (DefaultDate!=''))) {
         var InitialStatus = '';
         var InitialDate = eval(DateName + '_Object.picked.formatted');
      }
      else {
         var InitialStatus = ' style="visibility:hidden"';
         var InitialDate = '';
         eval(DateName + '_Object.setPicked(' + cbcalToday.getFullYear() + ',' + cbcalToday.getMonth() + ',' + cbcalToday.getDate() + ')');
      }
      // set the initial value of hidden field:
   //   document.getElementById(DateName).Value = InitialDate;
      // Create the form elements
         calhtml = ('<span class="cbDateinputJs" style="white-space:nowrap;">');	//CB: added
         // Find this form number
         for (var f=0;f<document.forms.length;f++) {
            for (var e=0;e<document.forms[f].elements.length;e++) {
               if (typeof document.forms[f].elements[e].type == 'string') {
                  if ((document.forms[f].elements[e].type == 'hidden') && (document.forms[f].elements[e].id == DateName)) {						//CB: changed .name to .id
                     eval(DateName + '_Object.formNumber='+f);
                     break;
                  }
               }
            }
         }
         // CB: changed below table to spans and added ordering:
         calhtml += ( cbcalHtmlYmdReplace( DateName, Required, DateFormat, DefaultDate, InitialStatus, YearDropDownStop, YearMin, YearMax ) );
		 // calendar icon:
         // CB: removed and put above: write('<span style="white-space:nowrap;">');
         calhtml += ('<a' /*CBB uncomment to hide calendar icon by default: + InitialStatus */ + ' id="' + DateName + '_ID_Link" href="javascript:' + DateName + '_Object.show()" onMouseOver="return ' + DateName + '_Object.iconHover(true)" onMouseOut="return ' + DateName + '_Object.iconHover(false)"><img src="' + cbcalImageURL + '" align="baseline" title="Calendar" border="0" width="16px" height="15px" /></a>&nbsp;');
         calhtml += ('<span style="position:relative;"><span id="' + DateName + '_ID" style="position:absolute;visibility:hidden;width:' + (cbcalCellWidth * 7) + 'px;background-color:' + cbcalCalBGColor + ';border:1px solid dimgray;" onMouseOver="' + DateName + '_Object.handleTimer(true)" onMouseOut="' + DateName + '_Object.handleTimer(false)">');
         calhtml += '\n' + ('<table class="cbDateinputCalTable" width="' + (cbcalCellWidth * 7) + '" cellspacing="0" cellpadding="1">' + String.fromCharCode(13) + '<tr style="background-color:' + cbcalTopRowBGColor + ';">');
         calhtml += '\n' + ('<td id="' + DateName + '_Previous_ID" style="cursor:default" align="center" class="calendarDateInput" style="height:' + cbcalCellHeight + '" onClick="' + DateName + '_Object.previous.go()" onMouseDown="cbcalVirtualButton(this,true)" onMouseUp="cbcalVirtualButton(this,false)" onMouseOver="return ' + DateName + '_Object.previous.hover(this,true)" onMouseOut="return ' + DateName + '_Object.previous.hover(this,false)" title="' + eval(DateName + '_Object.previous.monthName') + '"><img src="' + cbcalPrevURL + '" /></td>');
         calhtml += '\n' + ('<td id="' + DateName + '_Current_ID" style="cursor:pointer" align="center" class="calendarDateInput" style="height:' + cbcalCellHeight + '" colspan="5" onClick="' + DateName + '_Object.displayed.goCurrent()" onMouseOver="self.status=\'Click to view ' + CurrentDate.fullName + '\';return true;" onMouseOut="self.status=\'\';return true;" title="Show Current Month">' + eval(DateName + '_Object.displayed.fullName') + '</td>');
         calhtml += '\n' + ('<td id="' + DateName + '_Next_ID" style="cursor:default" align="center" class="calendarDateInput" style="height:' + cbcalCellHeight + '" onClick="' + DateName + '_Object.next.go()" onMouseDown="cbcalVirtualButton(this,true)" onMouseUp="cbcalVirtualButton(this,false)" onMouseOver="return ' + DateName + '_Object.next.hover(this,true)" onMouseOut="return ' + DateName + '_Object.next.hover(this,false)" title="' + eval(DateName + '_Object.next.monthName') + '"><img src="' + cbcalNextURL + '" /></td></tr>' + String.fromCharCode(13) + '<tr>');
         for (var w=0;w<7;w++) calhtml += '\n' + ('<td width="' + cbcalCellWidth + '" align="center" class="calendarDateInput" style="height:' + cbcalCellHeight + ';width:' + cbcalCellWidth + 'px;font-weight:bold;border-top:1px solid dimgray;border-bottom:1px solid dimgray;">' + cbcalWeekDays[w] + '</td>');
         calhtml += '\n' + ('</tr>' + String.fromCharCode(13) + '</table>' + String.fromCharCode(13) + '<span id="' + DateName + '_DayTable_ID">' + eval(DateName + '_Object.buildCalendar()') + '</span>' + String.fromCharCode(13) + '</span></span>' + String.fromCharCode(13) + '</span>');
   }
   return calhtml;
}
// DO NOT USE THIS FUNCTION, PROVIDED FOR BACKWARDS COMPATIBILITY ONLY:
function cbcalDateInput(DateName, Required, DateFormat, DefaultDate, DateFieldName, AdditionalInputAttributes, YearDropDownStop, YearMin, YearMax) {
    // CB 6 lines: Handle DateFieldName and Attrs:
    if (arguments.length < 5) { // The DateFieldName wasn't passed in
       DateFieldName = DateName;
    }
    if (arguments.length < 6) { // The AdditionalInputAttributes wasn't passed in
       AdditionalInputAttributes = '';
    }
    if (arguments.length < 7) { // The YearDropDownStop wasn't passed in
       YearDropDownStop = '1';
    }
    if (arguments.length < 8) { // The YearMin wasn't passed in
       YearMin = null;
    }
    if (arguments.length < 9) { // The YearMax wasn't passed in
       YearMax = null;
    }
	document.write('<input type="hidden" name="' + DateFieldName + '" id="' + DateName + '" value="' /* + InitialDate */ + '" ' + AdditionalInputAttributes + ' />');		//CB: Changed DateName to DateFieldName and added id and AdditionalInputAttributes.
	document.write( cbcalDateHtml(DateName, Required, DateFormat, DefaultDate, DateFieldName, AdditionalInputAttributes, YearDropDownStop, YearMin, YearMax) );
}