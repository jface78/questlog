<table border="0" cellpadding="0" cellspaceing="0" width="700" height="80" class="main">
<tr valign="top">
	<td width="520" align="left" class="borders"><img src="<? echo $TITLE_IMG_URL; ?>" width="<? echo $TITLE_IMG_WIDTH; ?>" height="<? echo $TITLE_IMG_HEIGHT; ?>" alt="<? echo $TITLE_IMG_ALT; ?>" border="0" /></td>
	<td width="180" align="right" class="borders"><? if ( $db && !check_session() ) { if ( $page_type=="log" ) { $ENTRY_SUBMIT = $POST_TO; } include($LOGIN_FORM); } ?></td>
</tr>
<tr>
	<td colspan="2">
<?		if ( isset($_GET["v"]) ) { echo $VERSION . "<br />"; }
		if( check_session() )
		{
			switch($page_type)
			{
				case "log":
					echo "<b>" . $quest_name . "</b>&nbsp;--&nbsp;login&nbsp;as:&nbsp;<i>" . $_SESSION["login"] . "</i>&nbsp;&nbsp;group:&nbsp;<i>" . $_SESSION["group"] . "</i>&nbsp;&nbsp;total&nbsp;posts:&nbsp;<i>" . $total_post_count . "</i>&nbsp;";
					break;
				default:
					$login_message = "login&nbsp;as&nbsp;<i>" . $_SESSION["login"] . "</i>&nbsp;&nbsp;";
					if ( $RECORD_LOGIN=="ON" && $SHOW_LAST_LOGIN=="ON" ) { 
						if ( isset($_SESSION["firstlogin"]) ) { echo "Welcome <i>". $_SESSION["login"] . "</i>, this is your first login to Questlog."; }
						else { echo $login_message . "last&nbsp;connected&nbsp;on&nbsp;<i>" . $last_date . "</i>&nbsp; from:&nbsp;<i>" . gethostbyaddr($_SESSION["last_ip"]) . "</i><br />"; } 
					} else { echo $login_message; }
			}
		}
		else { echo "You are not currently logged in."; } ?>
	</td>
</tr>
<!--tr><td colspan="2" bgcolor="<? echo $LINE_COLOR; ?>" height="1"><img src="./img/px.gif" width="1" height="1" alt="" border="0" /></td></tr-->
</table>
<hr />