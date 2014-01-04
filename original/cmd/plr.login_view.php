<?
	$login_query = mysql_query("SELECT u.login_name,l.uid,l.ip,FROM_UNIXTIME(l.date) AS date FROM users u, user_logins l WHERE l.uid=u.uid ORDER BY -l.date")or die($ERROR_DB_QUERY . ".[1]");
	$login_number = mysql_num_rows($login_query);
?>
Total Recorded Logins:&nbsp;<b><? echo $$login_number; ?></b><br />
<table border="0" cellpadding="0" cellspacing="0" width="370" class="log-table">
<tr valign="top" bgcolor="<? echo $row_bg; ?>">
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;&nbsp;user:&nbsp;&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td width="120">&nbsp;&nbsp;date:&nbsp;&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;&nbsp;ip:&nbsp;&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
</tr>
<tr valign="top">
	<td colspan="11" width="500" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
</tr>
<?
$row_counter="1";
while($logs = mysql_fetch_array($login_query))
{
  #$logs["uid"];
	#$login_count = count_posts($users["date"]);
?>
<!-- BEGIN database table //-->
<tr valign="top" bgcolor="<? if($row_counter % 2) { echo "#535456"; } else {  echo "#656566"; } ?>">
	<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap="nowrap" align="left">&nbsp;<? echo $logs["login_name"]; ?>&nbsp;</td>
	<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap="nowrap" width="120">&nbsp;<? echo $logs["date"]; ?>&nbsp;</td>
	<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap="nowrap" align="right">&nbsp;<a href="http://dnstools.com/?count=1&lookup=on&wwwhois=on&arin=on&portNum=80&target=<? echo $logs["ip"]; ?>&submit=Go!" target="new" class="loglink"><? echo $logs["ip"]; ?></a>&nbsp;</td>
	<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
</tr>
<tr valign="top">
	<td colspan="11" width="500" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
</tr>
<!-- END database table //-->
<?
	$row_counter++;
}
?>
</table>
