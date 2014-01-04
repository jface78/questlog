<?	if ( is_numeric($_GET["char"]) )
	{
		echo "<a href=\"./player.php?script=character_listing&title=List All Characters\">&laquo;&nbsp;back to list</a><br /><br />";
		$character_query = mysql_query("SELECT c.char_name,c.char_title,cp.profile,ch.history FROM characters c,character_profiles cp,character_prefaces ch WHERE c.cid='" . $_GET["char"] . "' AND c.cid=cp.cid AND c.cid=ch.cid") or die("an error has occured while building character profile.[1]");
		$characters = mysql_fetch_array($character_query);
		
		echo "<b>character name:</b> " . $characters["char_name"] . "<br />";
		echo "<b>family name/title:</b> " . $characters["char_title"];
		
		echo "<br /><br /><b>profile:</b><br />";
		echo $characters["profile"];
		echo "<br /><br /><b>history:</b><br />";
		echo $characters["history"] . "<br /><br />";
	        
	}
	else {	?>
		<table border="0" cellpadding="0" cellspacing="0" width="370" class="log-table">
		<tr valign="top" bgcolor="<? echo $row_bg; ?>">
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td nowrap width="5%">&nbsp;P:&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td nowrap width="60%">&nbsp;character name:&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			<td nowrap width="35%">&nbsp;user name:&nbsp;</td>
			<td width="1"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		</tr>
		<tr valign="top">
			<td colspan="11" width="500" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
		</tr>
<?		$character_query = mysql_query("SELECT c.cid,c.uid,c.char_name,c.char_title,u.login_name FROM characters c,users u WHERE c.uid=u.uid") or die("an error has occured while building character list.[2]");
		$row_counter="1";
		while ( $characters = mysql_fetch_array($character_query) )
		{
			$cid =  $characters["cid"];
			$char_name =  $characters["char_name"];
		    $char_title =  $characters["char_title"];
			$uid =  $characters["uid"];
			$login_name =  $characters["login_name"];
			$user_email =  $characters["user_emai"]; 
			$post_count = count_posts($characters["cid"], 1);
			
			if ( $char_title!="" ) { $char_title_checked = ", " . $char_title; } else { $char_title_checked = $char_title; } ?>
			<!-- BEGIN database table //-->
			<tr valign="top" bgcolor="<? if($row_counter % 2) { echo "#535456"; } else {  echo "#656566"; } ?>">
				<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap align="right">&nbsp;<? echo $post_count; ?>&nbsp;</td>
				<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap>&nbsp;<a href="./player.php?script=character_listing&title=Character Profile&char=<? echo $cid; ?>"><? echo $char_name . $char_title_checked; ?></a>&nbsp;</td>
				<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
				<td nowrap>&nbsp;<a href="mailto:<? echo $user_email; ?>"><? echo $login_name; ?></a>&nbsp;</td>
				<td nowrap width="1" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			</tr>
			<tr valign="top">
				<td colspan="11" width="500" bgcolor="#868684"><img src="./img/px.gif" width="1" height="1" border="0" /></td>
			</tr>
			<!-- END database table //-->
<?			$row_counter++;
		}	?>
		</table><br />
<?	}	?>
