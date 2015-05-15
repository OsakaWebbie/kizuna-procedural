<?php
include("functions.php");
include("accesscontrol.php");

header1(_("Account Settings"));
?>
<link rel="stylesheet" href="style.php?jquery=1" type="text/css" />
<? header2(1); ?>
<h1 id="title"><? echo _("Account Settings"); ?></h1>

<!-- USER LANGUAGE -->

<form action="do_maint.php?page=account" method="post" name="myuserform" id="myuserform" onsubmit="return validate('user');">
  <fieldset><legend><? echo _("My User Settings"); ?></legend>
  <label class="label-n-input"><? echo _("Language for Interface"); ?>: <select id="mylanguage" name="language" size="1">
    <option value="en_US"<? if($_SESSION['lang']=="en_US") echo " selected"; ?>><? echo _("English"); ?></option>
    <option value="ja_JP"<? if($_SESSION['lang']=="ja_JP") echo " selected"; ?>><? echo _("Japanese"); ?></option>
  </select></label>
  <input type="submit" name="user_upd" value="<? echo _("Save Changes"); ?>"> 
</fieldset></form>

<!-- PASSWORD -->

<form action="do_maint.php?page=account" method="post" name="pwform" autocomplete="off" onsubmit="return validate('pwd');">
  <fieldset><legend><? echo _("Change My Password"); ?></legend>
  <label class="label-n-input"><? echo _("Old"); ?>: <input type="password" id="old_pw" name="old_pw" style="width:8em"></label>
  <label class="label-n-input"><? echo _("New"); ?>: <input type="password" id="new_pw1" name="new_pw1" style="width:8em"></label>
  <label class="label-n-input"><? echo _("New again"); ?>: <input type="password" id="new_pw2" name="new_pw2" style="width:8em"></label>
  <input type="submit" id="pw_upd" name="pw_upd" value="<? echo _("Change Password"); ?>"> 
</fieldset></form>

<script type="text/JavaScript" src="js/jquery.js"></script>
<script type="text/JavaScript" src="js/jquery-ui.js"></script>
<script type="text/JavaScript" src="js/functions.js"></script>

<script type="text/javascript">
function validate(form) {
  switch(form) {
  case "pwd":
    if (document.pwform.old_pw.value == "") {
      alert("<? echo _("You must enter your current password for validation."); ?>");
      return false;
    }
    if (document.pwform.new_pw1.value != document.pwform.new_pw2.value) {
      alert("<? echo _("The two new password entries do not match."); ?>");
      return false;
    }
    break;
  }
}
</script>

<?
footer(1);
?>
