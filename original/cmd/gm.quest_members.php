<?
if($_POST["SET"]=="1" && $_POST["qid"]!="")
{
	$old_ids_query = mysql_query("SELECT cid FROM quest_members WHERE qid='" . $_POST["qid"] . "'");
	$old_ids = "0,0";
	while ( $old_ids_rows = mysql_fetch_row($old_ids_query) )
	{
		$old_ids .= "," . $old_ids_rows["0"];
	}
	$old_members = explode(",", $old_ids);
	$quest_name = mysql_fetch_row(mysql_query("SELECT quest_name FROM quests WHERE qid='" . $_POST["qid"] . "'")); ?>
	<form action="<? echo $POST_TO; ?>" method="POST" class="form">
	<input type="hidden" name="SET" value="2">
	<input type="hidden" name="qid" value="<? echo $_POST["qid"]; ?>">
	<input type="hidden" name="quest_name" value="<? echo $quest_name["0"]; ?>">
	<input type="hidden" name="old_ids" value="<? echo $old_ids; ?>">
	<table border="0" cellpadding="0" cellspacing="0" class="form" width="370">
	<tr>
		<td>
			Available Characters for quest <b><? echo $quest_name["0"]; ?></b><br />
			<div class="list">
<?			$chars_query = mysql_query("SELECT cid,char_name FROM characters WHERE uid!=" . $_SESSION["uid"]);
			while ( $characters = mysql_fetch_array($chars_query) )
			{
				$cid = $characters["cid"];
				$char_name = $characters["char_name"];
				$char_print = "<input type=\"checkbox\" name=\"" . $cid . "\" value=\"" . $cid . "\"";
				if ( in_array($cid, $old_members) ) { $char_print .= " checked=\"checked\""; }
				$char_print .= " />&nbsp;" . $char_name . "<br />";
				echo $char_print;
			} ?>
			</div>
		</td>
	</tr>
	</table><br />
		<input type="submit" value="&nbsp;&nbsp;&nbsp;edit&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button" />
		<input type="reset" value="&nbsp;&nbsp;&nbsp;clear&nbsp;&nbsp;&nbsp;&nbsp;" class="button" />
	</form>
<?
}
elseif($_POST["SET"]=="2" && $_POST["qid"]!="" && $_POST["quest_name"]!="" && $_POST["old_ids"]!="" )
{
	if ( $id = current($_POST) && $key = key($_POST) )
	{
		$new_ids = "0,0";
		while ( $id = next($_POST) )
		{
			$key = key($_POST);
			if ( $id==$key ) { $new_ids .= "," . $key; }
		}
	}
	$old_members = explode(",", $_POST["old_ids"]);
	$new_members = explode(",", $new_ids);
	//echo $_POST["old_ids"] . "<br />" . $new_ids . "<br /><br />";
	
	$remove_ids = array_diff($old_members, $new_members);
	$add_ids = array_diff($new_members, $old_members);
	//print_array($remove_ids); print_array($add_ids);
	
	$log_string = "GM Quest Members: " . $_POST["quest_name"] . " ";
	if ( $id = current($remove_ids) ) /* proccess character removal */
	{
		$log_string .= "-";
		$remove_sql = "DELETE LOW_PRIORITY FROM quest_members WHERE cid='0' ";
		do {
			//echo "Remove Characer id: " . $id . "<br />";
			$log_string .= $id . " ";
			$remove_sql .= "OR qid='" . $_POST["qid"] . "' AND cid='" . $id . "' ";
		}
		while ( $id = next($remove_ids) );
		//echo $remove_sql;
		mysql_query($remove_sql) or die("error removing members");;
	}
	if ( $id = current($add_ids) ) /* proccess character addition */
	{
		$log_string .= "+";
		$add_sql = "INSERT INTO quest_members VALUES";
		do {
			//echo "Add Characer id: " . $id . "<br />";
			$log_string .= $id . " ";
			$add_sql .= "('" . $_POST["qid"] . "','" . $id . "'),";
		}
		while ( $id = next($add_ids) );
		$add_sql = substr($add_sql, 0, -1);
		//echo $add_sql;
		mysql_query($add_sql) or die("error adding members");
	}
	//echo $log_string;
	log2($ACTION_LOG, $_SESSION["login"], $log_string);
	echo  "Membership has been updated, <a href=\"" . $POST_TO . "\">edit another quest</a> or <a href=\"" . $CLOSE . "\">close window</a>.";
}
else{ ?>
		<form action="<? echo $POST_TO; ?>" method="POST" class="form">
		<input type="hidden" name="SET" value="1" />
<?		$quests_query = mysql_query("SELECT qid,quest_name,quest_status FROM quests WHERE uid='" . $_SESSION["uid"]  . "' AND post_access='MEMBERS'") or die ("error during your database query.[1]");
		if ( $check_query = mysql_num_rows($quests_query) != "0" )
		{ ?>
			Select quest to edit:<br />
			<select name="qid" class="field">
<?			while($quests = mysql_fetch_array($quests_query))
			{
    			$qid = $quests["qid"];
				$quest_name = $quests["quest_name"];
				$quest_status = $quests["quest_status"]; ?>
				<option value="<? echo $qid; ?>"><? echo $quest_name; if($quest_status!=0){ echo " *"; } ?></option>
<?			} ?>
			</select>
			<input type="submit" name="submit" value="&nbsp;&nbsp;&nbsp;next&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button" />
			</form>	
<?		}
		else { echo "There are no quests registered in the database, you must <a href=\"./adm/adm.addquest.php\">Add a Quest</a> before you can edit it."; }
} ?>
