<?  echo "<br />"; /* "<br /><hr color=\"" . $LINE_COLOR . "\" width=\"100%\" align=\"left\" size=\"1\" noshade=\"noshade\" />"; */
if ( $db )
{
	$sql = "SELECT q.qid,q.uid,q.quest_name,u.login_name,COUNT(p.pid) AS totalposts FROM quests q,users u,posts p WHERE q.quest_status<'4' AND q.uid=u.uid AND q.qid=p.qid";
	if( check_session() )
	{
		$heading = "Other&nbsp;" . $THREAD_NAME . ":";
		$no_quests_msg = "there are no other active " . $THREAD_NAME . "  registered.";
		$player_qids = "'0'";
		$query = mysql_query("SELECT m.qid FROM quest_members m,characters c,users u WHERE u.uid='" . $_SESSION["uid"] . "' AND u.uid=c.uid AND c.cid=m.cid");
		while( $player_quests = mysql_fetch_array($query) )
		{
			$qid = $player_quests["qid"];
			$player_qids .= ",'" . $qid . "'";
 		}
		$sql .= " AND q.uid NOT IN ('" . $_SESSION["uid"] . "') AND q.qid NOT IN (" . $player_qids . ")";
	}
	else { $heading = "Active&nbsp;" . $THREAD_NAME . ":"; $no_quests_msg = "there are no active " . $THREAD_NAME . " registered."; }
	$sql .= " GROUP BY q.qid ORDER BY totalposts DESC";
	
	$all_quests_query = mysql_query($sql) or die("database error [all quest table]");
	$quest_number = mysql_num_rows($all_quests_query);
	#echo $heading . "&nbsp;&nbsp;&nbsp;" . $quest_number . "<br />"; ?>
	<table border="0" cellpadding="0" cellspacing="0" width="100%" class="logtable">
<?	if( $quest_number!="0" )
	{ ?>
		<!-- BEGIN title cells //-->
		<tr valign="top">
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td>&nbsp;<? echo $heading . "&nbsp;" . $quest_number; ?>&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td align="left">&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td>&nbsp;&nbsp;<? echo $OWNER_SUFFIX; ?>:&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td>&nbsp;&nbsp;last post by:&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td>&nbsp;&nbsp;posted on:&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td align="right">&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		</tr>
		<tr valign="top"><td colspan="13" width="100%" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td></tr>
		<!-- END title cells //-->
<?		$row_counter="1";
		while( $all_quests = mysql_fetch_array($all_quests_query) )
		{
			$qid =  $all_quests["qid"];
			$gamemaster_id =  $all_quests["uid"];
			$quest_name =  $all_quests["quest_name"];
			$quest_gamemaster =  $all_quests["login_name"];
			$post_number =  $all_quests["totalposts"];
			$last_date = last2post($qid); 
			$quest_member = $last_date["0"];
			$post_date = $last_date["1"];
			?>
			<!-- BEGIN database table //-->
			<tr valign="top" bgcolor="<? if($row_counter % 2) { echo "#535456"; } else {  echo "#656566"; } ?>">
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap="nowrap" width="25%"><nbr>&nbsp;&nbsp;<a href="<? echo $QUESTLOG; ?>?id=<? echo $qid; ?>" class="loglink"><? echo $quest_name; ?></a>&nbsp;&nbsp;</nbr></td>
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap="nowrap" align="right"><nbr>&nbsp;&nbsp;<? echo $post_number; ?>&nbsp;&nbsp;</nbr></td>
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap="nowrap" width="15%"><nbr>&nbsp;&nbsp;<? echo $quest_gamemaster; ?>&nbsp;&nbsp;</nbr></td>
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td width="100%"><nbr>&nbsp;&nbsp;<? echo $quest_member; ?>&nbsp;&nbsp;</nbr></td>
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap="nowrap"><nbr>&nbsp;&nbsp;<? echo $post_date; ?>&nbsp;&nbsp;</nbr></td>
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap="nowrap" width="70" align="right">&nbsp;<a href="<? echo $BACKSTORY; ?>?id=<? echo $qid; ?>" class="loglink" target="frame">description</a>&nbsp;</td>
				<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			</tr>
			<tr valign="top"><td colspan="13" width="100%" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td></tr>
			<!-- END database table //-->
<?			$row_counter++;
		}
	}
	else { echo $no_quests_msg; } ?>
	</table><br />
<?
}
else { echo "the database server appears to be offline, try again later."; } ?>