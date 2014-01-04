<?	if($_POST["SET"]=="1" && $_POST["uid"]!="")
	{
		$userinfo_query = mysql_query("SELECT u.login_name, up.user_email, g.gid, g.group_name FROM users u, user_profiles up,groups g WHERE u.uid='$_POST[uid]' AND u.uid=up.uid AND u.gid=g.gid") or die("an error has occured while querying the database.[2]");
		$userinfo = mysql_fetch_array($userinfo_query);
			$login_name =  $userinfo["login_name"];
    		$user_email =  $userinfo["user_email"];
			$user_gid =  $userinfo["gid"];
			$user_group_name =  $userinfo["group_name"];
		?>
			<form action="<? echo $POST_TO; ?>" method="POST" class="form">
			<input type="hidden" name="current_name" value="<? echo $login_name; ?>">
			<div align="right">
				user name: <input type="text" name="new_login_name" value="<? echo $login_name; ?>" size="25" class="field"><br />
				email: <input type="text" name="new_user_email" value="<? echo $user_email; ?>" size="25" class="field"><br />
				<br />
				passwd: <input type="password" name="new_login_hash" size="25" class="field"><br />
				confirm: <input type="password" name="new_login_hash_confirm" size="25" class="field"><br />
				<br />
				groups:
				<select name="new_user_group" class="field">
					<option value="<? echo $user_gid; ?>"><? echo $user_group_name; ?></option>
			<?	$groups_query = mysql_query("SELECT g.gid, g.group_name FROM groups g") or die("an error has occured while querying the database.[3]");
				while($groups = mysql_fetch_array($groups_query))
				{   
    				$gid =  $groups["gid"]; 
					$group_name =  $groups["group_name"]; 
					?>
					<option value="<? echo $gid; ?>"><? echo $group_name; ?></option>
			<?	} ?>
				</select><br /><br />
			</div><br />
			<input type="hidden" name="uid" value="<? echo $_POST["uid"]; ?>">
			<input type="hidden" name="SET" value="2">
			<input type="submit" name="submit" value="&nbsp;&nbsp;&nbsp;submit&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button">
		</form>	
<?	}
	elseif($_POST["SET"]=="2" && isset($_POST["uid"]) && isset($_POST["new_login_name"]) && isset($_POST["new_user_email"]) && isset($_POST["new_login_hash"]) && isset($_POST["new_login_hash_confirm"]) && isset($_POST["new_user_group"]))
	{
		if ( $_POST["new_login_name"]!=$_POST["current_name"] ) { $name_check = check_username($_POST["new_login_name"]); } else { $name_check = "UN"; }
		if ( $name_check && check_file($_POST["new_login_name"], $NAMEDENY) )
		{
			if($_POST["new_login_name"]!="" && $_POST["new_user_email"]!="" && $_POST["new_user_group"]!="")
			{
				$sql="UPDATE users SET gid='" . $_POST["new_user_group"] . "', login_name='" . $_POST["new_login_name"] . "', user_email='" . $_POST["new_user_email"] . "'";
				$log_string = "Admin Edit User: " . $_POST["new_login_name"];
				if($_POST["new_login_hash"]!="")
				{
					if($_POST["new_login_hash"]==$_POST["new_login_hash_confirm"])
					{
						$new_passwd_hash = hashPasswd($_POST["new_login_name"],$_POST["new_login_hash"]);
						$sql .= ", login_hash='$new_passwd_hash'";
						$log_string .= " + Password";
					} else{ echo "your new passwds do not match, <a href=\"" . $POST_TO . "\">try again</a>.[1]"; exit; }
				}
				$sql .= "WHERE uid='" . $_POST["uid"] . "'";
				
				if ( mysql_query($sql) )
				{
					log2($ACTION_LOG, $_SESSION["login"], $log_string);
				} echo  "user <i>" . $_POST["new_login_name"] . "</i> has been updated, <a href=\"" . $POST_TO . "\">edit another user</a> or <a href=\"" . $CLOSE . "\">close window</a>.";
			}
			else { echo "a required feild has been left blank, <a href=\"./adm/adm.edituser.php\">try again</a>.[1]"; exit; }
		}
		else { echo $ERROR_NAME; }
	}
	else{ ?>
		<form action="<? echo $POST_TO; ?>" method="POST" class="form">
			Select user to edit:
			<select name="uid" class="field">
		<?	$db_query = mysql_query("SELECT u.uid, u.login_name FROM users u") or die("an error has occured while querying the database.[1]");
			while($users = mysql_fetch_array($db_query))
			{   
    			$uid =  $users["uid"]; 
				$login_name =  $users["login_name"]; 
				?>
				<option value="<? echo $uid; ?>"><? echo $login_name; ?></option>
		<?	} ?>
			</select>
			<br /><br />
			<input type="hidden" name="SET" value="1">
			<input type="submit" name="submit" value="&nbsp;&nbsp;&nbsp;next&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button">
		</form>	
<?	} ?>
