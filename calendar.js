//JavaScript Calendar v2
//Author: Robert W. Husted (robert.husted@iname.com)
// Date:  Nov'99
//Author2:Eric Freed (helpdesk@freedfamily.org)
// Date:  Feb'02

topBackground   ="white";
bottomBackground="white";
tableBGColor    ="black";
cellColor       ="#ffff99";
headingCellColor="#6699cc";
headingTextColor="white";
dateColor       ="blue";
focusColor      ="#ff0000";
hoverColor      ="darkred";
fontStyle       ="8pt arial, helvetica";
headingFontStyle="8pt arial, helvetica";
bottomBorder    =true;
windowSize      ="width=165,height=175";
cal_opentwice	=true; //This is a workaround for IE to make the window come to top when it was already open

//End of customizable section

todayStr = "Today";
weekdayList  = new Array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
weekdayArray = new Array('Su','Mo','Tu','We','Th','Fr','Sa');
monthArray = new Array('January', 'February', 'March', 'April', 'May', 'June',
			'July', 'August', 'September', 'October', 'November', 'December');
//IF FRENCH
if (navigator.language=="fr") {
 todayStr = "Aujourd'hui";
 weekdayList  = new Array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');
 weekdayArray = new Array('Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa');
 monthArray = new Array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
			'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
}
//IF GERMAN
if (navigator.language=="de") {
 todayStr = "Heute";
 weekdayList  = new Array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');
 weekdayArray = new Array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');
 monthArray = new Array('Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
			'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
}
//IF SPANISH
if (navigator.language=="es") {
 todayStr = "Hoy";
 weekdayList  = new Array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado')
 weekdayArray = new Array('Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa');
 monthArray = new Array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
			'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
}

var calCtrl, initDate, calDateFormat, windowTitle;
var cal  = "loaded";
var isNav= (navigator.appName == "Netscape");
var isIE = !isNav;

var weekdays = "<TR BGCOLOR='"+headingCellColor+"'>";
for (i in weekdayArray) {
 weekdays += "<TD class='heading' align=center>"+weekdayArray[i]+"</TD>";
}
if (bottomBorder) weekdays += "<TD rowspan=7 bgcolor=black></TD>";
weekdays += "</TR>";

blankCell= "<TD align=center class='heading' bgcolor='"+cellColor+"'>&nbsp;</TD>";

var longSpace="";
for (i=0; i<50; i++) { longSpace+="&nbsp;" }

calendarBegin =
"<HTML><HEAD>" +
"<STYLE type='text/css'>" +
"<!--" +
"TD.heading { text-decoration: none; color:" + headingTextColor + "; font: " + headingFontStyle + "; }" +
"A.focusDay { color: " + focusColor + "; text-decoration: none; font: " + fontStyle + "; }" +
"A.weekDay  { color: " + dateColor  + "; text-decoration: none; font: " + fontStyle + "; }" +
"A.weekDay:hover { color: " + hoverColor + "; text-decoration: none; font: " + fontStyle + "; }" +
"-->" +
"</STYLE>" +
"</HEAD>" +
"<BODY BGCOLOR='" + bottomBackground + "'" +
"<CENTER>";
if (isNav) {
calendarBegin += 
"<TABLE CELLPADDING=0 CELLSPACING=1 BORDER=0 ALIGN=CENTER BGCOLOR='" +tableBGColor+ "'><TR><TD>";
}
calendarBegin +=
"<TABLE CELLPADDING=0 CELLSPACING=1 BORDER=0 ALIGN=CENTER BGCOLOR='" +tableBGColor+ "'>" +
weekdays +
"<TR>";

calendarEnd = "";
if (bottomBorder) {
  calendarEnd += "<TR><TD colspan=8></TD></TR>";
}
if (isNav) {
  calendarEnd += "</TD></TR></TABLE>";
}
calendarEnd +=
"</TABLE></CENTER></BODY></HTML>";


var delim, val, DayFormat, DayStr; //used to for next 2 functions to communicate

function markerFound(marker) {
var l=marker.length;
if (DayFormat.indexOf(marker)==0 && DayStr != null) {
 delim = DayStr.indexOf(DayFormat.substring(l,l+1));
 if (delim==0) delim=DayStr.length;
 val =DayStr.substring(0,delim);
 DayStr=DayStr.substring(delim,DayStr.length);
 DayFormat=DayFormat.substring(l,DayFormat.length);
 if (marker.indexOf("mon")==1 || !isNaN(val)) return true;
}
return false;
}

function readComplexDate(DayStr) {
DayFormat = calDateFormat.toLowerCase();
DayStr=DayStr.toLowerCase();
for (i=0; DayFormat.length!=0; DayFormat=DayFormat.substring(1,DayFormat.length)) {
 if (markerFound("dd")) calDate.setDate(val);
 if (markerFound("mm")) calDate.setMonth(val-1);
 if (markerFound("yyyy")) calDate.setFullYear(val);
 if (markerFound("yy")) {
  var n="19"+val;
  if (n<1930) n="20"+val;
  calDate.setFullYear(n);
 }
 if (markerFound("month")) {
  for (i in monthArray) {
   if (monthArray[i].toLowerCase()==val) calDate.setMonth(i);
  }
 }
 if (markerFound("mon")) {
  for (i in monthArray) {
   if (monthArray[i].toLowerCase().substring(0,3)==val) calDate.setMonth(i);
  }
 }
 DayStr=DayStr.substring(1,DayStr.length);
}
}

function showCalendar(dateField,Format,Title) {
if (parseInt(navigator.appVersion)<4) return false;
calDateFormat = Format;
windowTitle = Title;

//set initial date
calDateField = dateField;
calDate = new Date(dateField.value);
if (isNaN(calDate)) {
 calDate = new Date();
 readComplexDate(dateField.value);
}
initDate = new Date(calDate);
calDate.setDate(1);

if (isNav) rows= "'62,*'";
else rows= "'50,*'";
calDocFrameset = 
"<HTML><HEAD><TITLE>"+ windowTitle + longSpace +"</TITLE></HEAD>\n" +
"<FRAMESET ROWS="+ rows +" BORDER='0'>\n" +
"  <FRAME NAME='topCalFrame'    SRC='javascript:parent.opener.calDocTop' SCROLLING='no'>\n" +
"  <FRAME NAME='bottomCalFrame' SRC='javascript:parent.opener.calDocBottom' SCROLLING='no'>\n" +
"</FRAMESET>\n";

calDocBottom = buildBottomCalFrame();

calDocTop =
"<HTML>" +
"<HEAD></HEAD>" +
"<BODY onLoad='calControl.today.focus();calControl.today.blur();' BGCOLOR='" +topBackground+ "'>" +
"<CENTER>" + 
"<FORM NAME='calControl' onSubmit='document.calControl.year.blur();return false;'>" +
getMonthSelect() +
"<INPUT NAME='year' VALUE='" +calDate.getFullYear()+ "' TYPE=TEXT SIZE=4 MAXLENGTH=4 onChange='parent.opener.setYear()'>" +
"<BR><NOBR><INPUT " +
"TYPE=BUTTON NAME='previousYear' VALUE='<<' onClick='parent.opener.setPreviousYear()'><INPUT " +
"TYPE=BUTTON NAME='previousMonth' VALUE=' < ' onClick='parent.opener.setPreviousMonth()'><INPUT " +
"TYPE=BUTTON NAME='today' VALUE="+todayStr+" onClick='parent.opener.setToday()'><INPUT " +
"TYPE=BUTTON NAME='nextMonth' VALUE=' > ' onClick='parent.opener.setNextMonth()'><INPUT " +
"TYPE=BUTTON NAME='nextYear' VALUE='>>' onClick='parent.opener.setNextYear()'>" + 
"</NOBR>" +
"</FORM></CENTER>" +
"<SCRIPT language=JavaScript>" +
"parent.opener.calCtrl=document.calControl;" +
"</SCRIPT></BODY></HTML>";

if (isIE&&cal_opentwice) {
 calWin = window.open('about:blank','calWin','dependent=yes,width=1,height=1,screenX=200,screenY=300,titlebar=yes');
 calWin.close();
}
calWin = window.open('javascript:opener.calDocFrameset','calWin','dependent=yes,'+windowSize+',screenX=200,screenY=300,titlebar=yes');
if (isNav) calWin.focus();
}


function buildBottomCalFrame() {       
var calDoc = calendarBegin;
month=calDate.getMonth();
year =calDate.getFullYear();
day  =initDate.getDate();
var columnCount=0;
var days = getDaysInMonth();
if (day > days) day = days;
var firstOfMonth = new Date (year, month, 1);
var startingPos  = firstOfMonth.getDay();
days += startingPos;
for (i=0; i < startingPos; i++) {
 calDoc += blankCell;
 columnCount++;
}
var currentDay=0;
for (i=startingPos; i < days; i++) {
 currentDay = i-startingPos+1;

 if (currentDay==day && month==initDate.getMonth() && year==initDate.getFullYear())
  dayType = "focusDay";
 else
  dayType = "weekDay";

 calDoc +="<TD align=center bgcolor='" +cellColor+ "'>" +
          "<a class='" +dayType+ "' href='javascript:parent.opener.returnDate(" + 
          currentDay + ")'>&nbsp;" + currentDay + "&nbsp;</a></TD>";

 columnCount++;
 if (columnCount % 7 == 0) calDoc += "</TR><TR>";
}
for (i=days; i<42; i++)  {
 calDoc += blankCell;
 columnCount++;
 if (columnCount % 7 == 0) {
  calDoc += "</TR>";
  if (i<41) calDoc += "<TR>";
 }
}
calDoc += calendarEnd;
return calDoc;
}

function writeCalendar() {
calDocBottom = buildBottomCalFrame();
calWin.frames['bottomCalFrame'].document.open();
calWin.frames['bottomCalFrame'].document.write(calDocBottom);
calWin.frames['bottomCalFrame'].document.close();
}

function setToday() {
calDate = new Date();
returnDate(calDate.getDate());
}

function setYear() {
var year = calCtrl.year.value;
if (isFourDigitYear(year)) {
calDate.setFullYear(year);
writeCalendar();
}
}

function setCurrentMonth() {
var month = calCtrl.month.selectedIndex;
calDate.setMonth(month);
writeCalendar();
}

function setPreviousYear() {
calCtrl.year.value--;
setYear();
}

function setNextYear() {
calCtrl.year.value++;
setYear();
}

function setPreviousMonth() {
var year = calCtrl.year.value;
if (isFourDigitYear(year)) {
 var month = calCtrl.month.selectedIndex;
 if (month == 0) {
  month = 11;
  if (year > 1000) {
   year--;
   calDate.setFullYear(year);
   calCtrl.year.value = year;
  }
 } else {
  month--;
 }
 calDate.setMonth(month);
 calCtrl.month.selectedIndex = month;
 writeCalendar();
}
}

function setNextMonth() {
var year = calCtrl.year.value;
if (isFourDigitYear(year)) {
 var month = calCtrl.month.selectedIndex;
 if (month == 11) {
  month = 0;
  year++;
  calDate.setFullYear(year);
  calCtrl.year.value = year;
 } else {
  month++;
 }
 calDate.setMonth(month);
 calCtrl.month.selectedIndex = month;
 writeCalendar();
}
}

function getDaysInMonth()  {
var days=28;
var month=calDate.getMonth()+1;
var year =calDate.getFullYear();
if (month==1||month==3||month==5||month==7||month==8||month==10||month==12) {
 days=31;
} else if (month==4 || month==6 || month==9 || month==11) {
 days=30;
} else if (month==2 && ( (year%4)==0 && (year%100)!=0 || (year%400)==0 )) {
 days=29;
}
return (days);
}

function isFourDigitYear(year) {
if (year.length != 4 || isNaN(year)) {
calCtrl.year.value = calDate.getFullYear();
calCtrl.year.select();
calCtrl.year.focus();
return false;
}
return true;
}

function getMonthSelect() {
var activeMonth = calDate.getMonth();
monthSelect = "<SELECT NAME='month' onChange='parent.opener.setCurrentMonth()'>";
for (i in monthArray) {
if (i == activeMonth) {
    monthSelect += "<OPTION SELECTED>" + monthArray[i] + "\n";
}
else {
    monthSelect += "<OPTION>" + monthArray[i] + "\n";
}
}
monthSelect += "</SELECT>";
return monthSelect;
}

function jsReplace(inString, find, replace) {
var outString = "";
if (!inString) return "";
if (inString.indexOf(find) != -1) {
t = inString.split(find);
return (t.join(replace));
}
else {
return inString;
}
}

function doNothing() {
}

function makeTwoDigit(inValue) {
var numVal = parseInt(inValue, 10);
if (numVal < 10) {
  return("0" + numVal);
}
else {
  return numVal;
}
}

function returnDate(inDay) {
calDate.setDate(inDay);
var day        =calDate.getDate();
var month      =calDate.getMonth()+1;
var year       =calDate.getFullYear();
var yearString =""+year;
var monthString=monthArray[calDate.getMonth()];
var monthAbbr  =monthString.substring(0,3);
var weekday    =weekdayList[calDate.getDay()];
var weekdayAbbr=weekday.substring(0,3);

outDate=calDateFormat;
outDate=jsReplace(outDate,"DD", makeTwoDigit(day));
outDate=jsReplace(outDate,"dd", day);
outDate=jsReplace(outDate,"MM", makeTwoDigit(month));
outDate=jsReplace(outDate,"mm", month);
outDate=jsReplace(outDate,"yyyy", year);
outDate=jsReplace(outDate,"YY", year);
outDate=jsReplace(outDate,"yy", yearString.substring(2,4));
outDate=jsReplace(outDate,"Month", monthString);
outDate=jsReplace(outDate,"month", monthString.toLowerCase());
outDate=jsReplace(outDate,"MONTH", monthString.toUpperCase());
outDate=jsReplace(outDate,"Mon", monthAbbr);
outDate=jsReplace(outDate,"mon", monthAbbr.toLowerCase());
outDate=jsReplace(outDate,"MON", monthAbbr.toUpperCase());
outDate=jsReplace(outDate,"Weekday", weekday);
outDate=jsReplace(outDate,"weekday", weekday.toLowerCase());
outDate=jsReplace(outDate,"WEEKDAY", weekday.toUpperCase());
outDate=jsReplace(outDate,"Wkdy", weekdayAbbr);
outDate=jsReplace(outDate,"wkdy", weekdayAbbr.toLowerCase());
outDate=jsReplace(outDate,"WKDY", weekdayAbbr.toUpperCase());
outDate=jsReplace(outDate,"th", dayTh(day));
outDate=jsReplace(outDate,"TH", dayTh(day).toUpperCase());

calDateField.value =outDate;
calDateField.focus();
calWin.close();
}

function dayTh(d) {
if (d==1||d==21||d==31)
	return "st"
if (d==2||d==22)
	return "nd"
if (d==3||d==23)
	return "rd"
return "th"
}