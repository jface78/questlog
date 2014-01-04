<?
if ( isset($_POST["uid"]) )
{
	if ( $_SESSION["uid"] == $_POST["uid"] )
	{
		echo "You may not remove yourself user from the database. <a href=\"./quests.entry.php\">exit script</a>.";
	}
	else {
		mysql_query("DELETE FROM users WHERE uid='$_POST[uid]'") or die("an error has occured while querying the database.[2]");
		mysql_query("DELETE LOW_PRIORITY FROM logins WHERE uid='$_POST[uid]'") or die("an error has occured while querying the database.[3]");
		$log_string = "Admin Delete User : " . $_POST["login_name"];
		if( isset($_POST["REMOVE_POSTS"]) )
		{
			mysql_query("DELETE LOW_PRIORITY FROM posts WHERE uid='$_POST[uid]'") or die("an error has occured while querying the database.[2]");
			$log_string .= " + All Posts";
		}
		log2($ACTION_LOG, $_SESSION["login"], $log_string);
		echo "user has been successfully removed from the database. <a href=\"" . $POST_TO . "\">Remove another user</a> or <a href=\"" . $CLOSE . "\">close window</a>.";
	}
}
else {
	$db_query = mysql_query("SELECT u.uid, u.login_name FROM users u") or die("an error has occured while querying the database.[1]");
	$check_query = mysql_num_rows($db_query);
	if ( $check_query!="1" )
	{ ?>
		<form action="<? echo $POST_TO; ?>" method="POST" class="form">
			Select user to remove:
			<select name="uid" class="field">
<?			while($users = mysql_fetch_array($db_query))
			{
    			$uid =  $users["uid"]; 
				$login_name =  $users["login_name"]; ?>
				<option value="<? echo $uid; ?>"><? echo $login_name; ?></option>
<?			} ?>
			</select>
			<br /><br />Remove all posts:&nbsp;<input type="checkbox" name="REMOVE_POSTS" /><br /><br />
			<input type="submit" name="submit" value="&nbsp;&nbsp;&nbsp;next&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button">
		</form>	
<?		}
		else { echo "You may not remove the last user from the database. <a href=\"./quests.entry.php\">exit script</a>."; }
} ?>
