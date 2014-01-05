<?php
session_start();

if (isset($_SESSION['mongol'])){
  $loginText = "Logout";
  $function = "updateWindows(false);";
} else {
  $loginText = "Login / Join";
  $function = "spawnWindow(true, null, '50%', '50%', true, '0', '0', 'Log In / Join', 'static/signin.php');";
}
?>
<script type="text/javascript">
$(document).ready(function() {
  var topDivID = generateDivID("menuParent");
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
});
</script>
<div style="text-align:left;width:100%;height:100%;line-height:25px;overflow:hidden;" id="menuParent">
<a id="loginLogout" href="#" onClick="javascript:<?php echo $function;?>"><?php echo $loginText;?></a><br />
<?php
if (isset($_SESSION['mongol'])) {
?>
<a href="#" onClick="javascript:spawnWindow(true, null, '75%', '75%', true, '0', '0', 'Quests', 'static/listQuests.php');">Quests</a><br />
<a href="#" onClick="javascript:spawnWindow(true, null, '75%', '75%', true, '0', '0', 'New Quest', 'static/makeNewQuest.php');">Make New Quest</a><br />
<a href="#" onClick="javascript:spawnWindow(true, null, '75%', '75%', true, '0', '0', 'Player Characters', 'static/listCharacters.php');">Player Characters</a><br />
<a href="#" onClick="javascript:spawnWindow(true, null, '75%', '75%', true, '0', '0', 'New Character', 'static/makeNewCharacter.php');">Make New Character</a><br />
<a href="#" onClick="javascript:spawnWindow(true, null, '80%', '80%', true, '0', '0', 'Forums', 'static/listForumQuests.php');">Forums</a><br />
<?php
}
?>
<a href="#" onClick="javascript:spawnWindow(true, null, '50%', '80%', true, '0', '0', 'Dice', 'static/diceRoller.php', '#F0F0F0', false, false, true);">Dice</a><br />
<?php
if (isset($_SESSION['mongol'])) {
?>
<a href="#" onClick="javascript:spawnWindow(true, null, '20%', '80%', true, '0', '0', 'Settings', 'static/settings.php', '#F0F0F0', false, false, false);">Settings</a><br />
<?php
}
?>
<a href="#" onClick="javascript:spawnWindow(true, null, '50%', '50%', true, '0', '0', 'HELP COMPUTER', 'static/help.php', '#F0F0F0', false, false, true);">Help</a><br />
</div>
