<?
$page_title = "account register";
$page_type = "popup";
require("./control.php");
require($HTMLHEADER);
require($JAVASCRIPT_PATH);
?>
<b>Account Register</b><br />
Fill out the form and your account will be activated immediately. If this page does not submit it is b/c a feild has been left blank or the email address is invalid.<br />
<?
if ( isset($_POST["submit"]) && $_POST["newlogin_name"]=="" && $_POST["newuser_email"]=="" )
{
	echo "<br />**** required feilds were left blank ****<br />";
} ?>
<hr color="#868684" width="340" align="left" size="1" />
<table border="0" cellpadding="0" cellspacing="0" class="table" width="340" class="main">
<tr valign="top">
	<td width="340">
<?		if( $_POST["newlogin_name"]!="" && $_POST["newlogin_hash_1"]!="" && $_POST["newlogin_hash_2"]!="" && check_email($_POST["newuser_email"]) )
		{
			if ( $_POST["newlogin_hash_1"]==$_POST["newlogin_hash_2"] )
			{
				$newuser_check = mysql_query("SELECT newlogin_name FROM newusers WHERE newlogin_name='" . $_POST["newlogin_name"] . "'");
				if ( check_username($_POST["newlogin_name"]) && check_file($_POST["new_character_name"], $NAMEDENY) && mysql_num_rows($newuser_check)=="0" )
				{
					$newlogin_hash = hashPasswd($_POST["newlogin_name"],$_POST["newlogin_hash_1"]);
					if ( $id = @mysql_query("INSERT INTO users(gid,login_name,login_hash,user_email,user_status) VALUES('3', '" . $_POST["newlogin_name"] . "', '" . $newlogin_hash . "', '" . $_POST["newuser_email"] . "', '0')") )
					{
						$new_id = mysql_insert_id();
						@mysql_query("INSERT INTO logins(uid,login_count,last_date,last_ip) VALUES('" . $new_id . "', '0', '0000000000', '" . $_SERVER["REMOTE_ADDR"] . "')");
						echo "<br />Account Request has been sent, you will be emailed a password upon account activation. <a href=\"" . $CLOSE . "\">close</a><br /><br /><br /><br />";
						echo "<script language=\"JavaScript\" type=\"text/javascript\">window.setTimeout(\"opener.window.focus(); window.close();\", ". $POPUP_CLOSE_DELAY . ");</script>";
					}
					else { mysql_error(); }
				}
				else { echo "<br />The username <b>" . $_POST["newlogin_name"] . "</b> has already been taken, <a href=\"" . $POST_TO . "\">try a new name</a>."; exit; }
			}
			else { echo "<br />Your passwords to not match, <a href=\"" . $POST_TO . "\">re-enter them</a>."; exit; }
		}
		else { ?>
		<form method="post" action="<? echo $POST_TO; ?>" class="text">
		<input type="hidden" name="browser" value="<? echo $_SERVER["HTTP_USER_AGENT"]; ?>">
		<input type="hidden" name="ip" value="<? echo $_SERVER["REMOTE_ADDR"]; ?>">
			<b>desired account name</b>:<br />
			<input type="text" name="newlogin_name" size="25" class="feild"><br />
			<b>your email address</b>: [ required for account activation ]<br />
			<input type="text" name="newuser_email" size="25" class="feild"><br /><br />
			<b>desired password</b>: [ enter twice ]<br />
			<input type="password" name="newlogin_hash_1" size="25" class="feild">&nbsp;<input type="password" name="newlogin_hash_2" size="25" class="feild"><br /><br />
			<br /><br />
			<input name="submit" type="submit" value="&nbsp;&nbsp;register&nbsp;&raquo;&nbsp;" class="button">
			<input name="close" type="button" onclick="javascript: window.close();" value="&nbsp;&nbsp;close&nbsp;&nbsp;" class="button">
		</form>
	<?	} ?>
	</td>
</tr>
</table>
<?
check_include($HTMLFOOTER);
?>
