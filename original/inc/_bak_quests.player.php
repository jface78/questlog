<?  echo "<br />"; /* "<br /><hr color=\"" . $LINE_COLOR . "\" width=\"100%\" align=\"left\" size=\"1\" noshade=\"noshade\" />"; */
if ( $db )
{
	$quests_query = mysql_query("SELECT q.qid,q.quest_name,q.uid,u.login_name,COUNT(p.pid) AS totalposts FROM quest_members m,characters c,quests q,users u,posts p WHERE u.uid='" . $_SESSION["uid"] . "' AND u.uid=c.uid AND c.cid=m.cid AND m.qid=q.qid AND q.quest_status<'4' AND q.qid=p.qid GROUP BY q.qid ORDER BY totalposts DESC") or die("database error [player quest table]");
	$quest_number = mysql_num_rows($quests_query);
	$heading = "Your&nbsp;" . $login_name . "&nbsp;" . $THREAD_NAME . ":";
	#echo $heading . "&nbsp;&nbsp;&nbsp;" . $quest_number . "<br />"; ?>
	<table border="0" cellpadding="0" cellspacing="0" width="100%" class="logtable">
<?	if ( $quest_number!="0" )
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
		while ( $quests = mysql_fetch_array($quests_query) )
		{
			$qid =  $quests["qid"];
			$quest_name =  $quests["quest_name"];
			$gamemaster_id =  $quests["uid"];
			$quest_gamemaster =  $quests["login_name"];
			$post_number =  $quests["totalposts"];
			$last_date = last2post($qid); 
			$quest_member = $last_date["0"];
			$post_date = $last_date["1"]; ?>
		<!-- BEGIN database table //-->
		<tr valign="top" bgcolor="<? if($row_counter % 2) { echo "#535456"; } else {  echo "#656566"; } ?>">
			<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td nowrap="nowrap" width="25%"><nbr>&nbsp;&nbsp;<a href="<? echo $QUESTLOG; ?>?id=<? echo $qid; ?>" class="loglink"><? echo $quest_name; ?></a>&nbsp;&nbsp;</nbr></td>
			<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td nowrap="nowrap" align="right"><nbr>&nbsp;&nbsp;<? echo $post_number; ?>&nbsp;&nbsp;</nbr></td>
			<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td nowrap="nowrap" width="15%"><nbr>&nbsp;&nbsp;<? echo $quest_gamemaster; ?>&nbsp;&nbsp;</nbr></td>
			<td nowrap="nowrap" width="1" bgcolor="<? echo $BORDER_COLOR; ?>"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td width="60%"><nbr>&nbsp;&nbsp;<? echo $quest_member; ?>&nbsp;&nbsp;</nbr></td>
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
	else { echo "there are no active " . $login_name . " " . $THREAD_NAME . " registered."; } ?>
	</table>
<?
}
else { echo "the database server appears to be offline, try again later."; } ?>
