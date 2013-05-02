<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
//require_once (dirname(__FILE__).DS.'helper.php');

JHTML::_('behavior.mootools');

$path = JPATH_COMPONENT_SITE;
$comp = substr($path, strpos($path, 'components'));
$document =& JFactory::getDocument();
$document->addStyleSheet($comp . '/assets/css/trailinfo.css');
require_once (JPATH_COMPONENT.DS.'helper.php');
require_once (JPATH_COMPONENT.DS.'assets'.DS.'class.datepicker.php');

$trailid = JRequest::getInt('trailid');
$trailname = JRequest::getString('trailname');
//http://stefangabos.blogspot.com/search/label/Date%20Picker%20PHP%20Class%20Description/
$dpstart=new datepicker();
$dpstart->dateFormat = "d-M-Y";
$dpstart->firstDayOfWeek = 1;

if (!empty($_POST)) { 
	$date = $_POST["date"];
	$duration = $_POST["days"];
	$activity = $_POST["activity_id"];
	$difficulty = $_POST["difficulty_level"];
	$descr = $_POST["desc"];
//	$trailid = comAddlInfoHelper::setInfo($date, $duration, $activity, $difficulty, $descr, $trailid);
	$trailtext = comAddlInfoHelper::setInfo($date, $duration, $activity, $difficulty, $descr, $trailid, $trailname);
	$trailid = substr($trailtext, 0, strpos($trailtext, ':'));
	
	if ($trailid != 0) {
	   echo "<script type=\"text/javascript\">" . 
//"alert (".$trailid.")";
	   	"setTimeout(function(){ " .
	   	"window.parent.location.href = '?option=com_routes&view=traildisplay&tview=". $trailtext . "';" .
//	   	"window.parent.location.href = '?option=com_traildisplay&Itemid=1&tview=". $trailid . "';" .
	   	"}, 50);" .
	   	"</script>";
	}
}
?>
<form method="POST" action="<?php echo $_SERVER['REQUEST_URI'] ; ?>" target="_self">
	<h3>
		Additional info about this trail
	</h3>
	<br />
<?php if (!empty($_POST)) { ?>
	<div class="hidden" id="savediv" style="background-color: blue; text-align: center; color: #ffffff;font: 14px Arial, Helvetica, sans-serif;">Trail and information saved succesfully!</div>
<?php } ?>
	<div class="detailrow">
	  <div class="content">
		<span class="title">Date</span>
		   <span class="small-text">(you can enter tentative future dates if you are still planning this trip)</span>
		<br />
		<br />
		<input type="text" id="date" name="date" class="dates" value="start date">
		<input type="button" id="calendar" onclick="<?=$dpstart->show("date")?>">

		<input type="text" id="days" name="days" class="dates" value="# of days" onfocus="if (this.value=='# of days') {this.value=''}" onblur="if (this.value=='') {this.value='# of days'}">
	  </div>
	</div>

	<div class="detailrow">
	  <div class="content">
		<span class="title">Activity</span>
		<br />
		<br />
<?php
	$list = comAddlInfoHelper::getList();
	foreach ($list as $item) : 
?>
		<div class="inputcon">
		<input type="radio" name="activity_id" value="<?php echo $item->id;?>"><?php echo $item->type;?></input>
		</div>
<?php 
	endforeach; 
?>
		<div style="clear:both">&nbsp;</div>
	  </div>
	</div>
	
	<div class="detailrow">
	  <div class="content">
		<span class="title">Difficulty</span>
		  <span class="small-text">(1 is the easiest)</span>
		<br />
		<br />
		<input type="radio" name="difficulty_level" value="1">1</input>
		<input type="radio" name="difficulty_level" value="2">2</input>
		<input type="radio" name="difficulty_level" value="3">3</input>
		<input type="radio" name="difficulty_level" value="4">4</input>
		<input type="radio" name="difficulty_level" value="5">5</input>
	  </div>
	</div>

	<div class="detailrow">
	  <div class="content">
		<span class="title">Trip Story</span>
		  <span class="small-text">(describe your trip)</span>
		<br />
		<textarea name="desc"></textarea>
	  </div>
	</div>

	<div class="detailrow">
	  <div class="content">
		<button type="submit">
			<?php echo JText::_('Save'); ?>
		</button>
		<button type="button" onclick="submitbutton('save')">
			<?php echo JText::_('Cancel'); ?>
		</button>
	  </div>
	</div>
</form>

