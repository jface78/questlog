<?  echo "<br />"; /* "<br /><hr color=\"" . $LINE_COLOR . "\" width=\"100%\" align=\"left\" size=\"1\" noshade=\"noshade\" />"; */
if ( $db )
{
	$all_quests_query = mysql_query("SELECT q.qid,q.uid,q.quest_name,q.quest_access,u.login_name,COUNT(p.pid) AS totalposts FROM quests q,users u,posts p WHERE q.quest_status<'4' AND q.uid='" . $_SESSION["uid"] . "' AND q.uid=u.uid AND p.qid=q.qid GROUP BY q.qid ORDER BY totalposts DESC") or die("gamemaster quest table");
	$quest_number = mysql_num_rows($all_quests_query); 
	$heading = "Your&nbsp;" . $OWNER_SUFFIX . "&nbsp;" . $THREAD_NAME . ":";
	#echo $heading . "&nbsp;&nbsp;&nbsp;" . $quest_number . "<br />"; ?>
	<table border="0" cellpadding="0" cellspacing="0" width="100%" class="logtable">
<?	if($quest_number!="0")
	{ ?>
		<!-- BEGIN title cells //-->
		<tr valign="top">
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td>&nbsp;<? echo $heading . "&nbsp;" . $quest_number; ?>&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td align="left">&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td>&nbsp;&nbsp;last post by:&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td>&nbsp;&nbsp;posted on:&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td align="right">&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td align="right">&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		</tr>
		<tr valign="top"><td colspan="13" width="100%" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td></tr>
		<!-- END title cells //-->
<?		$row_counter="1";
		while ( $all_quests = mysql_fetch_array($all_quests_query) )
		{
			$qid =  $all_quests["qid"];
			$gamemaster_id =  $all_quests["uid"];
			$quest_name =  $all_quests["quest_name"];
			$post_number =  $all_quests["totalposts"];
			$last_date = last2post($qid); 
			$quest_member = $last_date["0"];
			$post_date = $last_date["1"]; ?>
			<!-- BEGIN database table //-->
			<tr valign="top" bgcolor="<? if($row_counter % 2) { echo "#535456"; } else {  echo "#656566"; } ?>">
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap="nowrap" width="24%"><nbr>&nbsp;<a href="<? echo $QUESTLOG; ?>?id=<? echo $qid; ?>" class="loglink"><? echo $quest_name; ?></a>&nbsp;</nbr></td>
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap="nowrap" align="right"><nbr>&nbsp;<? echo $post_number; ?>&nbsp;</nbr></td>
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td width="80%"><nbr>&nbsp;<? echo $quest_member; ?>&nbsp;</nbr></td>
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap="nowrap"><nbr>&nbsp;<? echo $post_date; ?>&nbsp;</nbr></td>
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap="nowrap" align="right">&nbsp;<a href="<? echo $BACKSTORY; ?>?id=<? echo $qid; ?>" class="loglink" target="frame">preface</a>&nbsp;</td>
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap="nowrap" align="right">&nbsp;<a href="<? echo $STORY; ?>?id=<? echo $qid; ?>" class="loglink" target="new">storyview</a>&nbsp;</td>
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			</tr>
			<tr valign="top"><td colspan="13" width="100%" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td></tr>
			<!-- END database table //-->
<?			$row_counter++;
		}
	}
	else { echo "there are no active " . $OWNER_SUFFIX . " " . $THREAD_NAME . " registered."; } ?>
	</table>
<?
}
else { echo "the database server appears to be offline, try again later."; } ?>
