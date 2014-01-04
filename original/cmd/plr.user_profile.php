<?
if ( isset($_POST["new_user_email"]) && isset($_POST["user1"]) && isset($_POST["user2"]) )
{
	$sql = "UPDATE users u, user_profiles up SET";
	$logstring = "User Edit Profile:";
	//
	// Check and Update User Email Feild //
	if ( $_POST["new_user_email"]!="" && check_email($_POST["new_user_email"]) )
	{
		$sql .= " up.user_email='" . $_POST["new_user_email"] . "'";
		$logstring .= " + UserEmail";
	}
	//
	// Check and Update User Password/Hash Feild //
	if ( $_POST["user1"]!="" )
	{
		if ( $_POST["user1"]==$_POST["user2"] )
		{
			$new_passwd_hash = hashPasswd($_SESSION["login"],$_POST["user1"]);
			$sql .= ", u.login_hash='$new_passwd_hash'";
			$logstring .= " + Password";
		}
		else { echo "your new passwords do not match, <a href=\"" . $POST_TO . "\">try again</a>\n</body>\n</html>"; exit; }
	}
	//
	// Check and Update User Alert Status Feild //
	if ( $_POST["alert_status"]==1 )
	{
		$sql .= ", up.alert_status='" . $_POST["alert_status"] . "'";
		$logstring .= " + AlertStatus";
	}
	else {
		$sql .= ", up.alert_status='0'";
		$logstring .= " - AlertStatus";
	}
	//
	// Check and Update Alert Email Feild //
	#if ()
	#{
	#	
	#}
	$sql .= " WHERE u.uid='" . $_SESSION["uid"] . "' AND u.uid=up.uid";
	echo $sql;
	if ( mysql_query($sql) )
	{
			#$_SESSION["email"] = $_POST["new_user_email"];
			log2($ACTION_LOG, $_SESSION["login"], $logstring);
			echo  "Your profile has been successfully updated.<br /><br />";
			echo "<a href=\"javascript: opener.window.location.reload(); window.close();\">close window</a>";
			echo "<script language=\"JavaScript\" type=\"text/javascript\"> opener.window.location.reload(); window.setTimeout(\"opener.window.focus(); window.close();\", 2000);</script>";
	} 
	else { echo "An error has interrupted the update of your profile."; }
	#}
	#else { echo "a required feild has been left blank, or the email address is not valid <a href=\"" . $PHP_SELF . "\">try again</a>.[1]"; exit; }
}
else {
	$userinfo_query = mysql_query("SELECT u.login_name,up.user_email,up.alert_status,g.group_name FROM users u,user_profiles up,groups g WHERE u.uid='" . $_SESSION["uid"] . "' AND u.uid=up.uid AND u.gid=g.gid") or die("an error has occured while querying the database.[1]");
	if ( $userinfo = mysql_fetch_array($userinfo_query) )
	{ ?>
	<form action="<? echo $POST_TO; ?>" method="POST" class="form">
	<div align="left">
		your <b>user profile</b> allows you to update your email address, and change your password.<br /><br />
		email: <input type="text" name="new_user_email" value="<? echo $userinfo["user_email"]; ?>" size="25" class="field" /><br /><br />
		post alert by email: <input type="checkbox" name="alert_status" value="1"<? if ( $userinfo["alert_status"]=="1" ) { echo " checked=\"checked\" "; } ?>/><br />
		passwd:<img src="../img/px.gif" width="1" height="1" border="0"> <input type="password" name="user1" size="25" class="field" /><br />
		confirm: <input type="password" name="user2" size="25" class="field" /><br />
		<br />* the password feilds only need to be filled if you are changing your password, otherwise leave them blank.<br /><br />
	</div>
		<br />
		<input type="hidden" name="new_login_name" value="<? echo $userinfo["login_name"]; ?>" /><br />
		<input type="submit" name="submit" value="&nbsp;&nbsp;&nbsp;submit&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button" />
	</form>	
<?	}
	else { echo "Error Retrieving Your User Records."; }
} ?>
