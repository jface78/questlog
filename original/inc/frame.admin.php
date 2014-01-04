<?
if( check_session("admin") )
{
	$db_query = mysql_query("SELECT newuid,newlogin_name,newuser_email,newlogin_hash,newuser_ip,newuser_timestamp FROM newusers") or die("an error has occured while querying the database.[frame:1]"); ?>
	<table border="0" cellpadding="0" cellspacing="0" width="100%" class="newsframe">
	<tr valign="top">
		<td nowrap="nowrap" width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		<td nowrap="nowrap">&nbsp;&nbsp;name:&nbsp;&nbsp;</td>
		<td nowrap="nowrap" width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		<td nowrap="nowrap">&nbsp;&nbsp;email:&nbsp;&nbsp;</td>
		<td nowrap="nowrap" width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		<td nowrap="nowrap">&nbsp;&nbsp;hash:&nbsp;&nbsp;</td>
		<td nowrap="nowrap" width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		<td nowrap="nowrap">&nbsp;&nbsp;ip:&nbsp;&nbsp;</td>
		<td nowrap="nowrap" width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		<td nowrap="nowrap">&nbsp;&nbsp;timestamp:&nbsp;&nbsp;</td>
		<td nowrap="nowrap" width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	</tr>
	<tr><td colspan="11" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td></tr>
<?	if(mysql_num_rows($db_query)!="0")
	{
		$row_counter="1";
		while($newusers = mysql_fetch_array($db_query))
		{
    		$newuid =  $newusers["newuid"];
			$newlogin_name =  $newusers["newlogin_name"];
			$newuser_email =  $newusers["newuser_email"];
			$newlogin_hash =  $newusers["newlogin_hash"];
			$newuser_ip =  $newusers["newuser_ip"];
			$newuser_time =  $newusers["newuser_timestamp"]; ?>
	<tr valign="top" bgcolor="<? if($row_counter % 2) { echo "#535456"; } else {  echo "#656566"; } ?>">
		<td nowrap="nowrap" width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		<td nowrap="nowrap">&nbsp;&nbsp;<? echo $newlogin_name; ?>&nbsp;&nbsp;</td>
		<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		<td nowrap="nowrap">&nbsp;&nbsp;<? echo $newuser_email; ?>&nbsp;&nbsp;</td>
		<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		<td nowrap="nowrap">&nbsp;&nbsp;<? echo $newlogin_hash; ?>&nbsp;&nbsp;</td>
		<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		<td nowrap="nowrap">&nbsp;&nbsp;<? echo $newuser_ip; ?>&nbsp;&nbsp;</td>
		<td nowrap="nowrap" width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		<td nowrap="nowrap">&nbsp;&nbsp;<? echo $newuser_time; ?>&nbsp;&nbsp;</td>
		<td nowrap="nowrap" width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
	</tr>
	<tr><td colspan="11" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td></tr>
<?			$row_counter++;
		}
	}
	else { echo "<tr><td colspan=\"11\"><br />&nbsp;&nbsp;no new users are recorded in the database.</td></tr>"; } ?>
	</table>
<?
} else { echo "either you are not logged in or you do not have sufficient privileges to access this file."; } ?>