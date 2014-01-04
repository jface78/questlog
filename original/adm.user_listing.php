<?
	$all_user_query = mysql_query("SELECT u.uid, u.login_name, up.user_email, g.group_name FROM users u, groups g, user_profiles up WHERE u.gid=g.gid AND u.uid=up.uid ORDER BY g.group_name")or die("error during your database query.[1]");
	$user_number = mysql_num_rows($all_user_query);
?>
Current Users:&nbsp;<b><? echo $user_number; ?></b>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="javascript: window.location.reload();">refresh list</a><br />
<table border="0" cellpadding="0" cellspacing="0" width="370" class="logtable">
<tr valign="top" bgcolor="<? echo $row_bg; ?>">
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;uid:&nbsp;</td>
	<!--td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;&nbsp;#&nbsp;&nbsp;</td-->
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;name:&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;group:&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;email:&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
</tr>
<tr valign="top">
	<td colspan="13" width="500" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
</tr>
<?
$row_counter="1";
while($users = mysql_fetch_array($all_user_query))
{
	$uid = $users["uid"];
	$login_name = $users["login_name"];
	$user_email = $users["user_email"];
	$user_group =  $users["group_name"];
	$login_count = $users["login_count"];
?>
<!-- BEGIN database table //-->
<tr valign="top" bgcolor="<? if($row_counter % 2) { echo "#535456"; } else {  echo "#656566"; } ?>">
	<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap align="right">&nbsp;<? echo $uid; ?>&nbsp;</td>
	<!--td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap align="right">&nbsp;<? echo $login_count; ?>&nbsp;</td-->
	<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap width="100">&nbsp;<a href="#?id=<? echo $uid; ?>" class="loglink"><? echo $login_name; ?></a>&nbsp;</td>
	<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap>&nbsp;<? echo $user_group; ?>&nbsp;</td>
	<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap width="150">&nbsp;<? echo $user_email; ?>&nbsp;</td>
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