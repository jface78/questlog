<?php
if ( $db )
{
	#$quests_query = mysql_query("SELECT q.qid AS quest_id,q.quest_name,q.uid,COUNT(p.pid) AS totalposts,(SELECT u.login_name FROM users u,quests q WHERE quest_id=q.qid AND q.uid=u.uid) AS gm_name FROM quest_members m,characters c,quests q,users u,posts p WHERE u.uid='" . $_SESSION["uid"] . "' AND u.uid=c.uid AND c.cid=m.cid AND m.qid=q.qid AND q.quest_status<'4' AND q.qid=p.qid GROUP BY q.qid ORDER BY totalposts DESC") or die("database error [player quest table]");
	$quests_query = mysql_query("SELECT q.qid AS quest_id,q.quest_name,q.uid,COUNT(p.pid) AS totalposts,(SELECT u.login_name FROM users u,quests q WHERE quest_id=q.qid AND q.uid=u.uid) AS gm_name, (SELECT post_date FROM posts WHERE qid=q.qid ORDER BY post_date DESC LIMIT 1) AS last_post FROM quest_members m,characters c,quests q,users u,posts p WHERE u.uid='" . $_SESSION["uid"] . "' AND u.uid=c.uid AND c.cid=m.cid AND m.qid=q.qid AND q.quest_status<'4' AND q.qid=p.qid GROUP BY q.qid ORDER BY last_post DESC") or die("database error [player quest table]");
	$quest_number = mysql_num_rows($quests_query);
	$heading = "Your&nbsp;" . $login_name . "&nbsp;" . $THREAD_NAME . ":";
	#echo $heading . "&nbsp;&nbsp;&nbsp;" . $quest_number . "<br />"; ?>
	<table border="0" cellpadding="0" cellspacing="0" width="100%" class="log-table">
<?	if ( $quest_number!="0" )
	{ ?>
		<!-- BEGIN title cells //-->
		<tr valign="top">
			<td class="log-head">&nbsp;<? echo $heading . "&nbsp;" . $quest_number; ?>&nbsp;</td>
			<td class="log-head" align="left">&nbsp;</td>
			<td class="log-head">&nbsp;&nbsp;<? echo $OWNER_SUFFIX; ?>:&nbsp;</td>
			<td class="log-head">&nbsp;&nbsp;last post by:&nbsp;</td>
			<td class="log-head">&nbsp;&nbsp;posted on:&nbsp;</td>
			<td class="log-head" align="right">&nbsp;</td>
			<td class="log-head" align="right">&nbsp;</td>
		</tr>
		<!-- END title cells //-->
<?		$row_counter="1";
		while ( $quests = mysql_fetch_array($quests_query) )
		{
			$qid =  $quests["quest_id"];
			$quest_name =  $quests["quest_name"];
			$gamemaster_id =  $quests["uid"];
			$quest_gamemaster =  $quests["gm_name"];
			$post_number =  $quests["totalposts"];
			$last_date = last2post($qid);
			#$last_date = $quests["last_post"];  
			$quest_member = $last_date["0"];
			$post_date = $last_date["1"]; ?>
		<!-- BEGIN database table //-->
		<tr valign="top" bgcolor="<? if($row_counter % 2) { echo "#535456"; } else {  echo "#656566"; } ?>">
			<td class="log-left" nowrap="nowrap" width="25%"><nbr>&nbsp;<a href="<? echo $QUESTLOG; ?>?id=<? echo $qid; ?>" class="loglink"><? echo $quest_name; ?></a>&nbsp;</nbr></td>
			<td class="log-cell" nowrap="nowrap" align="right"><nbr>&nbsp;<? echo $post_number; ?>&nbsp;</nbr></td>
			<td class="log-cell" nowrap="nowrap" width="15%"><nbr>&nbsp;<? echo $quest_gamemaster; ?>&nbsp;</nbr></td>
			<td class="log-cell" width="60%"><nbr>&nbsp;<? echo $quest_member; ?>&nbsp;</nbr></td>
			<td class="log-cell" nowrap="nowrap"><nbr>&nbsp;<? echo $post_date; ?>&nbsp;</nbr></td>
			<td class="log-cell" nowrap="nowrap" align="right">&nbsp;<a href="<? echo $BACKSTORY; ?>?id=<? echo $qid; ?>" class="loglink" target="frame">preface</a>&nbsp;</td>
			<td class="log-cell" nowrap="nowrap" align="right">&nbsp;<a href="<? echo $STORY; ?>?id=<? echo $qid; ?>" class="loglink" target="new">storyview</a>&nbsp;</td>
		</tr>
		<!-- END database table //-->
<?			$row_counter++;
		}
	}
	else { echo "there are no active " . $login_name . " " . $THREAD_NAME . " registered."; } ?>
	</table>
<?
}
else { echo "the database server appears to be offline, try again later."; } ?>
