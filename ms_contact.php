<?php
include("functions.php");
include("accesscontrol.php");
header1("");
?>
<link rel="stylesheet" href="style.php?jquery=1" type="text/css" />
<?php
header2(0);

if ($save_action) {
  $pid_array = explode(",",$pid_list);
  $num_pids = count($pid_array);
  $prev_num = 0;
  $prev_pidlist = "";
  $prev_info = "";
  for ($i=0; $i<$num_pids; $i++) {
    $has_prev = 0;
    if (substr($save_action,0,3) != "Yes") {   //skip check if after confirming similar entries
      $sql = "SELECT action.*,FullName FROM action LEFT JOIN person on action.PersonID=".
      "person.PersonID WHERE action.PersonID=".$pid_array[$i].
      " AND action.ActionTypeID=$ctid AND ActionDate='$cdate'";
      $result = sqlquery_checked($sql);
      if (mysqli_num_rows($result) > 0) {
        $has_prev = 1;
        $prev_num++;
        $prev_pidlist .= ",".$pid_array[$i];
        while ($row = mysqli_fetch_object($result)) {
          $prev_info .= "<tr><td><a href=\"individual.php?pid=$pid_array[$i]\" ".
          "target=\"_blank\">$row->FullName</a></td><td>".
          ((strlen($row->Description) > 100)?(substr($row->Description,0,97)."..."):($row->Description)).
          "</td></tr>\n";
        }
      }
    }
    if (!$has_prev) {
      $sql = "INSERT INTO action (PersonID,ActionTypeID,ActionDate,Description) VALUES (".
           $pid_array[$i].",$ctid,'$cdate','$desc')";
      $result = sqlquery_checked($sql);
    }
  }
  echo "<h3>".sprintf(_("%s new records successfully added."),$num_pids-$prev_num)."</h3>\n";
  if ($prev_num > 0) {
    $prev_pidlist = substr($prev_pidlist,1);  //remove the leading comma
    $sql = "SELECT ActionType FROM actiontype WHERE ActionTypeID=$ctid";
    $tempresult = sqlquery_checked($sql);
    $temprow = mysqli_fetch_object($tempresult);
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="confirmform" target="_self">
<input type="hidden" name="pid_list" value="$prev_pidlist">
<input type="hidden" name="ctid" value="$ctid">
<input type="hidden" name="cdate" value="$cdate">
<input type="hidden" name="desc" value="$desc">
<?php
    echo sprintf(_("However, the following %s people already had a action of type \"%s\" on %s."),
    $prev_num,$temprow->ActionType,$cdate)."<br />\n";
    echo _("Do you still want the additional records added?");
    echo "<input type=\"submit\" name=\"save_action\" value=\""._("Yes, add them anyway!")."></form>\n";
    echo _("(You can click on a name to view their individual info - it will open in a new window/tab.)");
    echo "<table><tr><th>"._("Name")."</th><th>"._("Description")."</th></tr>\n".$prev_info."</table>\n";
  }
  exit;
}
?>
  <h3><?=_("Choose action type and date, and fill in a description if desired:")?></h3>
  <form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="actionform" target="_self" onsubmit="return validate();">
    <input type="hidden" name="pid_list" value="<?=$pid_list?>">
    <label class="label-n-input"><?=_("Action Type")?>: <select id="ctid" name="ctid" size="1">
      <option value="" selected>Please select...</option>
<?php
$result = sqlquery_checked("SELECT * FROM actiontype ORDER BY ActionType");
while ($row = mysqli_fetch_object($result)) {
  echo "      <option value=\"".$row->ActionTypeID."\">".$row->ActionType."</option>\n";
}
?>
    </select></label>
    <label class="label-n-input"><?=_("Date")?>: <input type="text" id="cdate" name="cdate" value="" style="width:6em"></label>
    <label class="label-n-input"><?=_("Description")?>: <textarea id="desc" name="desc" style="height:4em;width:30em"></textarea></label>
    <input type="submit" name="save_action" value="<?=_("Save Action Info")?>">
  </form>

<script type="text/JavaScript" src="js/jquery.js"></script>
<script type="text/JavaScript" src="js/jquery-ui.js"></script>
<script type="text/JavaScript">
$(document).ready(function(){
  $(document).ajaxError(function(e, xhr, settings, exception) {
    alert('Error calling ' + settings.url + ': ' + exception);
  }); 
<?php
if($_SESSION['lang']=="ja_JP") echo "  $.datepicker.setDefaults( $.datepicker.regional[\"ja\"] );\n";
?>
  $("#cdate").datepicker({ dateFormat: 'yy-mm-dd' });
  if ($("#cdate").val()=="") $("#cdate").datepicker('setDate', new Date());

  $("#ctid").change(function(){  //insert template text in Action description when applicable ActionType is selected
    if (!$.trim($("#desc").val())) {
      $("#desc").load("ajax_request.php",{'req':'ActionTemplate','ctid':$("#ctid").val()}, function() {
        $(this).change();
      });
    }
  });

});

function validate() {
//Make sure a action type is selected
  if (document.actionform.ctid.value == "") {
    alert("<?=_("Please select a Action Type.")?>");
    return false;
  } else {
    return true;
  }
}
</script>
<?php footer();
?>
