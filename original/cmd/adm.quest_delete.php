<?
if ( isset($_POST["qid"]) )
{
	mysql_query("DELETE LOW_PRIORITY FROM quests WHERE qid='$_POST[qid]'") or die("an error has occured while querying the database.[2]");
	mysql_query("DELETE LOW_PRIORITY FROM quest_prefaces WHERE qid='$_POST[qid]'") or die("an error has occured while querying the database.[3]");
	echo "quest has been successfully removed from the database. <a href=\"" . $POST_TO . "\">Remove another user</a> or <a href=\"" . $CLOSE . "\">close window</a>.";
}
else {
	$quests_query = mysql_query("SELECT q.qid, q.quest_name, q.quest_status FROM quests q") or die("error during your database query.[1]");
	if ( $check_query = mysql_num_rows($quests_query)!="0" )
	{ ?>
		<form action="<? echo $POST_TO; ?>" method="POST" class="form">
			Select quest to edit:
			<select name="qid" class="field">
<?			while ( $quests = mysql_fetch_array($quests_query) )
			{
    			$qid =  $quests["qid"];
				$quest_name =  $quests["quest_name"];
				$quest_status =  $quests["quest_status"]; ?>
				<option value="<? echo $qid; ?>"><? echo $quest_name; if($quest_status!=0){ echo " *"; } ?></option>
<?			} ?>
			</select>
			<br /><br />
			<input type="submit" name="submit" value="&nbsp;&nbsp;&nbsp;next&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button" />
		</form>	
<?	}
	else { echo "There are no quests registered in the database, you must <a href=\"./adm/adm.addquest.php\">Add a Quest</a> before you can remove it."; }
} ?>
