<?
	$replycode_query = mysql_query("SELECT r.id,r.uid,r.pid,r.qid,r.code,r.timestamp,u.login_name,q.quest_name FROM reply_codes r,users u,quests q WHERE r.uid=u.uid AND r.qid=q.qid")or die("error during your database query.[1]");
	$replycode_count = mysql_num_rows($replycode_query);
?>
Active Reply Codes:&nbsp;<b><? echo $replycode_count; ?></b>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="javascript: window.location.reload();">refresh list</a><br />
<table border="0" cellpadding="0" cellspacing="0" width="370" class="log-table">
<tr valign="top" bgcolor="<? echo $row_bg; ?>">
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;user:&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;quest:&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;post:&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;code:&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
</tr>
<tr valign="top">
	<td colspan="13" width="500" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
</tr>
<?
$row_counter="1";
while($reply_codes = mysql_fetch_array($replycode_query))
{ ?>
<!-- BEGIN database table //-->
<tr valign="top" bgcolor="<? if($row_counter % 2) { echo "#535456"; } else {  echo "#656566"; } ?>">
	<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap align="right">&nbsp;<? echo $reply_codes["login_name"]; ?>&nbsp;</td>
	<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap width="170">&nbsp;<a href="#?id=<? echo $reply_codes["quest_name"]; ?>" class="loglink"><? echo $reply_codes["quest_name"]; ?></a>&nbsp;</td>
	<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap align="center">&nbsp;<? echo $reply_codes["pid"]; ?>&nbsp;</td>
	<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap width="80" align="center">&nbsp;<? echo $reply_codes["code"]; ?>&nbsp;</td>
	<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
</tr>
<tr valign="top">
	<td colspan="13" width="500" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
</tr>
<!-- END database table //-->
<?
	$row_counter++;
}
?>
</table>