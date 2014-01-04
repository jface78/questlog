<?	if( $_POST["new_quest_name"]!="" && $_POST["gamemaster"]!="" && $_POST["post_access"]!="" )
	{
		if ( check_questname($_POST["new_quest_name"]) && check_file($_POST["new_quest_name"], $NAMEDENY) )
		{
			if ( $_POST["first_post"]=="" ) { $_POST["first_post"]="Opening post for <b>" . $_POST["new_quest_name"] . "</b>"; }
			$cid = "0";
			mysql_query("INSERT INTO quests(uid,quest_name,quest_status,read_access,post_access) VALUES('" . $_POST["gamemaster"] . "','" . $_POST["new_quest_name"] . "','" . $_POST["quest_status"] . "','" . $_POST["read_access"] . "','" . $_POST["post_access"] . "')") or die("add quest error.[2]");
			$new_qid = mysql_insert_id();
			mysql_query("INSERT INTO quest_prefaces(qid) VALUES('" . $new_qid . "')") or die("add quest error.[3]");
			$datetime = getCurrentDate();
			$formated_post = formatContent($_POST["first_post"]);
			mysql_query("INSERT INTO posts(qid,uid,cid,post_text,post_date,post_ip) VALUES('" . $new_qid . "','" . $_SESSION["uid"] . "','" . $cid . "','" . $formated_post . "','" . $datetime . "','" . $_SERVER["REMOTE_ADDR"] . "')") or die ("add quest error.[4]");
			
			log2($ACTION_LOG, $_SESSION["login"], "Admin Add Quest: " . $_POST["new_quest_name"]);
			
			echo  "the Quest " . $_POST["new_quest_name"] . " has been successfully started. <a href=\"" . $POST_TO . "\">Create another quest</a> or <a href=\"" . $CLOSE . "\">close window</a>.";
			echo "<script language=\"JavaScript\" type=\"text/javascript\">window.setTimeout(\"window.close();\", ". $POPUP_CLOSE_DELAY . ");</script>";
		}
		else { echo $ERROR_NAME; }
	}
	else{ ?>
		<form action="<? echo $POST_TO; ?>" method="POST" class="form">
		<table border="0" cellpadding="0" cellspacing="0" class="form" width="370">
		<tr>
			<td>quest name:<br /><input type="text" name="new_quest_name" size="25" class="field" /></td>
			<td>
				quest owner:<br />
				<select name="gamemaster" class="field">
<?				$db_query = mysql_query("SELECT u.uid,u.login_name FROM users u,groups g WHERE g.group_name!='player' AND g.gid=u.gid") or die("add quest error.[1]");
				while($gms = mysql_fetch_array($db_query))
				{
    				$uid =  $gms["uid"];
					$login_name =  $gms["login_name"];
					echo "<option value=\"" . $uid . "\">" . $login_name . "</option>";
				} ?>
				</select>
			</td>
		</tr><tr>
			<td>
				<br />read access:<br />
				<select name="read_access" class="field">
					<option value="USERS">USERS</option>
					<option value="MEMBERS">MEMBERS</option>
					<option value="OWNER">OWNER</option>
					<option value="ADMIN">ADMIN</option>
				</select>
			</td><td>
				<br />post access:<br />
				<select name="post_access" class="field">
					<option value="USERS">USERS</option>
					<option value="MEMBERS">MEMBERS</option>
					<option value="OWNER">OWNER</option>
					<option value="ADMIN">ADMIN</option>
				</select>
			</td>
		</tr><tr>
			<td colspan="2"><br />first post:<br /><textarea name="first_post" cols="55" rows="20"  class="field" style="width:370; height:200px;"></textarea></td>
		</tr>
		</table><br />
			<input type="submit" name="submit" value="&nbsp;&nbsp;&nbsp;submit&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button" />
			<input type="reset" name="reset" value="&nbsp;&nbsp;&nbsp;clear&nbsp;&nbsp;&nbsp;&nbsp;" class="button" />
		</form>	
<?	} ?>
