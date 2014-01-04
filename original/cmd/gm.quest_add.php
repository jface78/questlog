<?	if( $_POST["new_quest_name"]!="" && $_POST["quest_members"]!="" && is_numeric($_POST["quest_status"]) )
	{
		if ( check_questname($_POST["new_quest_name"]) )
		{
			if ( $_POST["first_post"]=="" ) { $_POST["first_post"]="Opening post for <b>" . $_POST["new_quest_name"] . "</b>"; }
			$cid = "0";
			mysql_query("INSERT INTO quests(uid,quest_name,quest_status,quest_members) VALUES('" . $_SESSION["uid"] . "','" . $_POST["new_quest_name"] . "','" . $_POST["quest_status"] . "','" . $_POST["quest_members"] . "')") or die("add quest error.[2]");
			$new_qid = mysql_insert_id();
			mysql_query("INSERT INTO backstories(qid,uid) VALUES('" . $new_qid . "','" . $_SESSION["uid"] . "')") or die("add quest error.[3]");
			$datetime = getCurrentDate();
			$formated_post = formatContent($_POST["first_post"]);
			mysql_query("INSERT INTO posts(qid,uid,cid,post_text,post_date,post_ip) VALUES('" . $new_qid . "','" . $_SESSION["uid"] . "','" . $cid . "','" . $formated_post . "','" . $datetime . "','" . $_SERVER["REMOTE_ADDR"] . "')") or die ("add quest error.[4]");
			
			log2($ACTION_LOG, $_SESSION["login"], "GM Add Quest: " . $_POST["new_quest_name"]);
			
			echo  "the Quest " . $_POST["new_quest_name"] . " has been successfully started. <a href=\"" . $POST_TO . "\">Create another quest</a> or <a href=\"" . $CLOSE . "\">close window</a>.";
		}
		else { echo $ERROR_NAME; }
	}
	else{ ?>
		<form action="<? echo $POST_TO; ?>" method="POST" class="form">
		<table border="0" cellpadding="0" cellspacing="0" class="form" width="370">
		<tr>
			<td>quest name:<br /><input type="text" name="new_quest_name" size="25" class="field" /></td>
			<td>&nbsp;</td>
		</tr><tr>
			<td>
				<br />user access:<br />
				<select name="quest_members" class="field">
					<option value="MEMBERS">MEMBERS</option>
					<option value="ALL">ALL</option>
					<option value="GAMEMASTERS">GAMEMASTERS</option>
				</select>
			</td><td>
				<br />quest access:<br />
				<select name="quest_status" class="field">
					<option value="0">public</option>
					<option value="1">private</option>
					<option value="4">closed</option>
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
