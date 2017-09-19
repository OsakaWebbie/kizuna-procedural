<?php
include("functions.php");
include("accesscontrol.php");
header1("");

// A REQUEST TO ADD ADDENDANCE RECORD(S)?
if ($newattendance) {
  $pidarray = explode(",",$pid_list);
  //make array of dates (single or range)
  $datearray = array();
  if ($_POST["enddate"] != "") {  //need to do a range of dates
    if ($_POST["date"] > $_POST["enddate"]) die("Error: End Date is earlier than Start Date.");
    for ($day=$_POST["date"]; $day<=$_POST["enddate"]; $day=strftime("%Y-%m-%d", strtotime("$day +1 day"))) {
      if ($_POST["dow".date("w",strtotime($day))]) {
        $datearray[] = $day;
      }
    }
  } else {
    $datearray[] = $_POST["date"];
  }
  //insert for each date and pid (might be only one of each, but...)
  //not combined into a single "insert...select" query because the ON DUPLICATE KEY UPDATE won't add the non-dups in the list
  $added = 0;
  $updated = 0;
  foreach ($datearray as $eachdate) {
    foreach ($pidarray as $eachpid) {
      if ($_POST["starttime"] != "") {
        sqlquery_checked("INSERT INTO attendance(PersonID,EventID,AttendDate,StartTime,EndTime) ".
        "VALUES($eachpid,{$_POST["eid"]},'$eachdate','".$_POST["starttime"].":00','".$_POST["endtime"].":00') ".
        "ON DUPLICATE KEY UPDATE StartTime='".$_POST["starttime"].":00', EndTime='".$_POST["endtime"].":00'");
      } else {
        sqlquery_checked("INSERT INTO attendance(PersonID,EventID,AttendDate) ".
        "VALUES($eachpid,{$_POST["eid"]},'$eachdate') ON DUPLICATE KEY UPDATE AttendDate=AttendDate");
      }
      $affected = mysqli_affected_rows($db);
      if ($affected == 2)  $updated++;
      elseif ($affected == 1)  $added++;
    }
  }
  header2(0);
  echo "<h3>".sprintf(_("%s attendance records added."),$added);
  if ($updated > 0) echo "<br \>".sprintf(_("%s existing records had times updated."),$updated);
  if ($added+$updated > count($datearray)*count($pidarray)) echo "<br \>".sprintf(_("%s existing records were unchanged."),
      count($datearray)*count($pidarray) - $updated - $added);
  echo "</h3>";
  exit;
}
?>
<link rel="stylesheet" href="style.php?jquery=1" type="text/css" />
<style>
body { margin:20px; }
#eventselect label.label-n-input { margin-right:0; }
#dayofweek label { margin-right: 0.5em; }
</style>
<script type="text/JavaScript" src="js/jquery.js"></script>
<script type="text/JavaScript" src="js/jquery-ui.js"></script>
<script type="text/JavaScript" src="js/jquery.ui.timepicker.js"></script>
<script type="text/javascript">
$(document).ready(function(){
  $(document).ajaxError(function(e, xhr, settings, exception) {
    alert('Error calling ' + settings.url + ': ' + exception);
  });
  
/* initially hide past events in dropdown list, but allow toggling */
  $("a#showpast").click(function(e) {
    e.preventDefault();
    $("#currentevents").hide();
    $("#allevents, #eventid option.inactive").show();
  });
  $("a#hidepast").click(function(e) {
    e.preventDefault();
    $("#allevents, #eventid option.inactive").hide();
    $("#currentevents").show();
  });
  $("a#hidepast").click();
<?php
if($_SESSION['lang']=="ja_JP") {
  echo "  $.datepicker.setDefaults( $.datepicker.regional[\"ja\"] );\n";
  echo "  $.timepicker.setDefaults( $.timepicker.regional[\"ja\"] );\n";
}
?>
  $("#attenddate").datepicker({ dateFormat: 'yy-mm-dd' });
  $("#attendenddate").datepicker({ dateFormat: 'yy-mm-dd' });
  $("#attendstarttime").timepicker();
  $("#attendendtime").timepicker();
  
  $("#activeevents").click(function(){  //show or hide active events
    if ($("#activeevents").val()=="<?=_("Show Active")?>") {
      $("#eventid.active").show();
      $("#activeevents").val("<?=_("Hide Active")?>");
    } else {
      $("#eventid.active").hide();
      $("#activeevents").val("<?=_("Show Active")?>");
    }
  });
  $("#oldevents").click(function(){  //show or hide old events
    if ($("#oldevents").val()=="<?=_("Show Old")?>") {
      $("#eventid.old").show();
      $("#oldevents").val("<?=_("Hide Old")?>");
    } else {
      $("#eventid.old").hide();
      $("#oldevents").val("<?=_("Show Old")?>");
    }
  });
  $("#eventid").change(function(){  //display form stuff based on type of event selected
    if ($("#eventid option:selected").hasClass('times')) {
      $("label.times").show();
      //$("label.date").hide();
      //$("label.date > input").val("");
    } else {
      //$("label.date").show();
      $("label.times").hide();
      $("label.times > input").val("");
    }
  });
});

function ValidateAttendance(){
  if (document.attendform.eid.selectedIndex == 0) {
    alert('<?=_("You must select an event.")?>');
    return false;
  }
  if ($('#attenddate').val() == '') {
    alert('<?=_("You must enter a date.")?>');
    $('#attenddate').click();
    return false;
  }
  try { $.datepicker.parseDate('yy-mm-dd', $('#attenddate').val()); }
  catch(error) {
    alert('<?=_("Date is invalid.")?>');
    $('#attenddate').click();
    return false;
  }
  try { $.datepicker.parseDate('yy-mm-dd', $('#attendenddate').val()); }
  catch(error) {
    alert('<?=_("Date is invalid.")?>');
    $('#attendenddate').click();
    return false;
  }
  return true;
}
</script>
<?php
header2(0);
?>
<h3><?=_("Select an event and at least one date, and click the button.")?></h3>
<form name="attendform" method="post" action="ms_attendance.php" onSubmit="return ValidateAttendance()">
  <input type="hidden" name="pid_list" value="<?=$_POST['pid_list']?>" />
  <div id="eventselect">
    <label class="label-n-input"><?=_("Event")?>:
      <select size="1" id="eventid" name="eid">
        <option value="0" selected><?=_("Select...")?></option>
<?php
$result = sqlquery_checked("SELECT EventID,Event,UseTimes,IF(EventEndDate AND EventEndDate<CURDATE(),'inactive','active') AS Active FROM event ORDER BY Event");
while ($row = mysqli_fetch_object($result)) {
  echo "        <option value=\"".$row->EventID."\" class=\"".(($row->UseTimes==1)?"times ":"days ").$row->Active."\">".
      $row->Event."</option>\n";
}
?>
      </select>
    </label>
    <span id="currentevents" style="display:none">
      <span class="comment"><?=("(Showing only current events)")?></span> <a id="showpast" href="#"><?=("Show All")?></a>
    </span>
    <span id="allevents" style="display:none"><a id="hidepast" href="#"><?=("Hide Past Events")?></a></span>
  </div>
  <div id="dates">
    <label class="label-n-input"><?=_("Date")?>:
    <input type="text" name="date" id="attenddate" style="width:6em" value="" /></label>
    <label class="label-n-input date"><?=_("Optional End Date")?>:
    <input type="text" name="enddate" id="attendenddate" style="width:6em" value="" /></label>
  </div>
  <div id="dayofweek">
    <?=_("Days of week for date range")?>:
    <label class="label-n-input"><input type="checkbox" name="dow0" checked /><?=_("Sunday")?></label>
    <label class="label-n-input"><input type="checkbox" name="dow1" checked /><?=_("Monday")?></label>
    <label class="label-n-input"><input type="checkbox" name="dow2" checked /><?=_("Tuesday")?></label>
    <label class="label-n-input"><input type="checkbox" name="dow3" checked /><?=_("Wednesday")?></label>
    <label class="label-n-input"><input type="checkbox" name="dow4" checked /><?=_("Thursday")?></label>
    <label class="label-n-input"><input type="checkbox" name="dow5" checked /><?=_("Friday")?></label>
    <label class="label-n-input"><input type="checkbox" name="dow6" checked /><?=_("Saturday")?></label>
  </div>
  <div id="times">
    <label class="label-n-input times" style="display:none"><?=_("Start Time")?>:
    <input type="text" name="starttime" id="attendstarttime" style="width:4em" value="" /></label>
    <label class="label-n-input times" style="display:none"><?=_("End Time")?>:
    <input type="text" name="endtime" id="attendendtime" style="width:4em" value="" /></label>
  </div>
  <input type="submit" value="<?=_("Save Attendance Entries")?>" name="newattendance" />
</form>
<?php
footer();
?>

