<?php
if($_POST["SET"]=="1" && $_POST["qid"]!="")
{
	$questinfo_query = mysql_query("SELECT u.login_name,q.uid,q.quest_name,q.quest_status,q.read_access,q.post_access FROM users u, quests q WHERE q.qid='" . $_REQUEST["qid"] . "' AND q.uid=u.uid") or die("an error has occured while querying the database.[2]");
	$questinfo = mysql_fetch_array($questinfo_query);
	$gamemaster_name = $questinfo["login_name"];
	$gamemaster_id =  $questinfo["uid"];
	$quest_name =  $questinfo["quest_name"];
	$quest_status =  $questinfo["quest_status"];
	$read_access =  $questinfo["read_access"];
	$post_access =  $questinfo["post_access"];?>
	<form action="<? echo $POST_TO; ?>" method="POST" class="form">
	<input type="hidden" name="SET" value="2">
	<input type="hidden" name="qid" value="<? echo $_POST["qid"]; ?>">
	<input type="hidden" name="current_name" value="<? echo $quest_name; ?>">
	<table border="0" cellpadding="0" cellspacing="0" class="form" width="370">
	<tr>
		<td>quest name:<br /><input type="text" name="new_quest_name" value="<? echo $quest_name; ?>" size="25" class="field" /></td>
		<td>
			quest owner:<br />
			<select name="gamemaster" class="field">
				<option value="<? echo $gamemaster_id; ?>"><? echo $gamemaster_name; ?></option>
<?				$user_query = mysql_query("SELECT u.uid,u.login_name FROM users u, groups g WHERE g.group_name!='player' AND u.uid!='" . $gamemaster_id . "' AND g.gid=u.gid") or die("error during your database query.[1]");
				while($gms = mysql_fetch_array($user_query))
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
<?				switch ($read_access)
				{
					case "USERS": echo "<option value=\"USERS\">USERS</option>\n"; break;
					case "MEMBERS": echo "<option value=\"MEMBERS\">MEMBERS</option>\n"; break;
					case "OWNER": echo "<option value=\"OWNER\">OWNER</option>\n"; break;
					case "SUPER": echo "<option value=\"SUPER\">SUPER</option>\n"; break;
				} ?>
				<option value="USERS">USERS</option>
				<option value="MEMBERS">MEMBERS</option>
				<option value="OWNER">OWNER</option>
				<option value="SUPER">SUPER</option>
			</select>
		</td><td>
			<br />post access:<br />
			<select name="post_access" class="field">
<?				switch ($post_access)
				{
					case "USERS": echo "<option value=\"USERS\">USERS</option>\n"; break;
					case "MEMBERS": echo "<option value=\"MEMBERS\">MEMBERS</option>\n"; break;
					case "OWNER": echo "<option value=\"OWNER\">OWNER</option>\n"; break;
					case "SUPER": echo "<option value=\"SUPER\">SUPER</option>\n"; break;

				} ?>
				<option value="USERS">USERS</option>
				<option value="MEMBERS">MEMBERS</option>
				<option value="OWNER">OWNER</option>
				<option value="SUPER">SUPER</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<br />Current Characters:<br />
			<div class="list">
<?			$chars_query = mysql_query("SELECT c.char_name FROM characters c,quest_members m WHERE m.qid='" . $_POST["qid"] . "' AND m.cid=c.cid");
			while ( $characters = mysql_fetch_row($chars_query) )
			{
				$characters = $characters["0"];
				echo "<b>" . $characters . "</b><br />";
			} ?>
			</div>
		</td>
	</tr>
	</table><br />
		<input type="submit" name="submit" value="&nbsp;&nbsp;&nbsp;edit&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button" />
		<input type="reset" name="reset" value="&nbsp;&nbsp;&nbsp;clear&nbsp;&nbsp;&nbsp;&nbsp;" class="button" />
	</form>
<?
}
elseif($_POST["SET"]=="2" && $_POST["qid"]!="" &&  $_POST["new_quest_name"]!="" && $_POST["gamemaster"]!="" )
{
	if ( $_POST["new_quest_name"]!=$_POST["current_name"] ) { $name_check = check_questname($_POST["new_quest_name"]); } else { $name_check = "UN"; }
		if ( $name_check && check_file($_POST["new_quest_name"], $NAMEDENY) )
		{
			$sql = "UPDATE quests SET ";
			if ( $name_check == "1" ) { $sql .= "quest_name='" . $_POST["new_quest_name"] . "', "; }
			$sql .= "uid='" . $_POST["gamemaster"] . "', read_access='" . $_POST["read_access"] . "', post_access='" . $_POST["post_access"] . "', quest_status='" . $_POST["quest_status"] . "' WHERE qid='" . $_POST["qid"] . "'";
			mysql_query($sql) or die("an error has occured while querying the database.[4]");
			
			log2($ACTION_LOG, $_SESSION["login"], "Admin Edit Quest: " . $_POST["new_quest_name"]);
			
			echo  "the quest <i>" . $_POST["new_quest_name"] . "</i> has been updated, <a href=\"" . $POST_TO . "\">edit another quest</a> or <a href=\"" . $CLOSE . "\">close window</a>.";
			echo "<script language=\"JavaScript\" type=\"text/javascript\">window.setTimeout(\"window.close();\", ". $POPUP_CLOSE_DELAY . ");</script>";
		}
		else { echo $ERROR_NAME; }
}
else { ?>
	<form action="<? echo $POST_TO; ?>" method="POST" class="form">
		<input type="hidden" name="SET" value="1" />
<?		$quests_query = mysql_query("SELECT q.qid, q.quest_name, q.quest_status FROM quests q") or die("error during your database query.[1]");
	if ( $check_query = mysql_num_rows($quests_query) != "0" )
			{ ?>
				Select quest to edit:<br />
				<select name="qid" class="field">
<?				while($quests = mysql_fetch_array($quests_query))
				{  
    				$qid =  $quests["qid"];
					$quest_name =  $quests["quest_name"];
					$quest_status =  $quests["quest_status"]; ?>
					<option value="<? echo $qid; ?>">
					<?	echo $quest_name; 
						if($quest_status!=0)
						{
							switch ($quest_status)
							{
								case "1": echo " - P"; break;
								case "4": echo " - C"; break;
								default: echo " *";
							}
						} ?>
					</option>
<?				} ?>
				</select>
				<input type="submit" name="submit" value="&nbsp;&nbsp;&nbsp;next&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button" />
			</form>	
<?		}
		else { echo "There are no quests registered in the database, you must <a href=\"./adm/adm.addquest.php\">Add a Quest</a> before you can edit it."; }
} ?>
