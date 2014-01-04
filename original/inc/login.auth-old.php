<?
if ( $_POST["FORM"]=="ins" && $_POST["ins1"]!="" && $_POST["ins2"]!="" && $_POST["ENTRY_SUBMIT"]!="" )
{
	session_start();
	require("./control.php");
	$name = $_POST["ins1"];
	$pass = $_POST["ins2"];
	require($FUNCTIONS);
	$db = databaseConnection($DATABASE);
	$login_passwd_hash = hashPasswd($name,$pass);
	$user_query = mysql_query("SELECT u.uid,u.login_name,g.group_name,l.last_date,l.last_ip,l.login_count FROM users u, groups g, user_logins l WHERE u.login_name='" . $name . "' AND u.login_hash='" . $login_passwd_hash . "' AND u.gid=g.gid AND u.uid=l.uid") or die("you are not authorized.[1]");
	if ( $result_check = mysql_num_rows($user_query) != 0 )
	{	// if mysql returns rows dump the variables from the array and register into session //
		$user_info = mysql_fetch_array($user_query);
		$_SESSION["uid"] =  $user_info["uid"];
		$_SESSION["login"] = $user_info["login_name"];
		$_SESSION["group"] =  $user_info["group_name"];
		if ( $RECORD_LOGIN=="ON" )
		{	// fun for security to print these on the entry page //
			$_SESSION["last_date"] =  $user_info["last_date"]; // proccess this timestamp with unixStyleDate() before you print //
			$_SESSION["last_ip"] = $user_info["last_ip"];
			$_SESSION["login_count"] = $user_info["login_count"]; // update the login count
			$_SESSION["login_count"]++;
			$date = time(); // store new timestamp to be read by unixStyleDate() next login //
			// return new values to the database, write out the access log, redirect to the main login page //
			mysql_query("UPDATE user_logins SET last_date='$date',last_ip='" . $_SERVER["REMOTE_ADDR"] . "',login_count='" . $_SESSION["login_count"] . "' WHERE uid='" . $_SESSION["uid"] . "'") or die("An erro has occured recording your login.[2]");
		}
		if ( $LOG2_LOGIN=="ON" ) { log2($LOGIN_LOG, $user_info["login_name"]); }
		header("Location: " . $_POST["ENTRY_SUBMIT"]);
	}
    else { session_destroy(); log2($ERROR_LOG, "Failed Login: " . $_POST["ins1"] . " / " . $_POST["ins2"]); header("Location: " . $BASE_HREF); }
}
else { header("Location: " . $BASE_HREF); }
?>
