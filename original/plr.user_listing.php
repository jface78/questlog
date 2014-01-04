<?
	$all_user_query = mysql_query("SELECT u.uid, u.login_name, up.user_email, g.group_name FROM users u, user_profiles up, groups g WHERE u.uid=up.uid AND u.gid=g.gid ORDER BY g.group_name")or die($ERROR_DB_QUERY . ".[1]");
	$user_number = mysql_num_rows($all_user_query);
?>
Registered Users:&nbsp;<b><? echo $user_number; ?></b><br />
<table border="0" cellpadding="0" cellspacing="0" width="370" class="logtable">
<tr valign="top" bgcolor="<? echo $row_bg; ?>">
	<!--td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;&nbsp;L&nbsp;&nbsp;</td-->
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>&nbsp;&nbsp;P&nbsp;&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td width="120">username:&nbsp;&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td>group:&nbsp;&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<!--td width="150">email:&nbsp;&nbsp;</td>
	<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td-->
</tr>
<tr valign="top">
	<td colspan="11" width="500" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
</tr>
<?
$row_counter="1";
while($users = mysql_fetch_array($all_user_query))
{
	$uid =  $users["uid"];
	$login_name =  $users["login_name"];
	$login_count =  $users["login_count"];
	$post_count = count_posts($users["uid"]);
	#$user_email = protected_email($users["user_email"]);
	
	if( $users["group_name"]=="admin" || $users["group_name"]=="gamemaster" ) { $user_group = "GM"; } else {$user_group = $users["group_name"]; }
?>
<!-- BEGIN database table //-->
<tr valign="top" bgcolor="<? if($row_counter % 2) { echo "#535456"; } else {  echo "#656566"; } ?>">
	<!--td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap="nowrap" align="right">&nbsp;<? echo $login_count; ?>&nbsp;</td-->
	<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap="nowrap" align="right">&nbsp;<? echo $post_count; ?>&nbsp;</td>
	<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap="nowrap" width="120">&nbsp;<? echo $login_name; ?>&nbsp;</td>
	<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<td nowrap="nowrap">&nbsp;<? echo $user_group; ?>&nbsp;</td>
	<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	<!--td nowrap="nowrap" width="150">&nbsp;<? echo $user_email; ?>&nbsp;</td>
	<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td-->
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
