<?
if ( check_session() )
{
	if ( $PLAYER_SCRIPTS!="OFF" )
	{ ?>
	<!-- OPEN player menus -->
		<br /><img src="./img/player_menu_title.gif" width="78" height="13" alt="player menu" border="0" /><br />
		<a href="./<? echo $LOGOUT; ?>">logout</a><br />
		<a href="javascript: script_window('./player.php?script=user_profile&title=User Profile');" onMouseOver="window.status='user profile'; return 0" onMouseOut="window.status=''; return 0">user profile</a><br />
		<a href="javascript: script_window('./player.php?script=user_listing&title=List Registered Users');" onMouseOver="window.status='list users'; return 0" onMouseOut="window.status=''; return 0">list users</a><br />
		<a href="javascript: post_window('./inc/contact.php');">contact form</a><br />
		<img src="./img/px.gif" width="1" height="5" border="0" /><br />
<?		if ( $CHARACTER_NAMES!="OFF" )
		{ ?>
			<a href="javascript: script_window('./player.php?script=character_add&title=Create New Character');" onMouseOver="window.status='character create'; return 0" onMouseOut="window.status=''; return 0">create character</a><br />
			<a href="javascript: script_window('./player.php?script=character_edit&title=Edit Character');" onMouseOver="window.status='characters edit'; return 0" onMouseOut="window.status=''; return 0">edit character</a><br />
			<a href="javascript: script_window('./player.php?script=character_delete&title=Delete Character');" onMouseOver="window.status='characters delete'; return 0" onMouseOut="window.status=''; return 0">delete character</a><br />
			<a href="javascript: script_window('./player.php?script=character_listing&title=List All Characters');" onMouseOver="window.status='list characters'; return 0" onMouseOut="window.status=''; return 0">view characters</a><br />
<?		}?>
		<a href="./log.php">search quests</a><br />
		<a href="javascript: script_window('./player.php?script=login_view&title=User Access Logs');" onMouseOver="window.status='user access logs'; return 0" onMouseOut="window.status=''; return 0">access logs</a><br />
	<!-- CLOSE player menus -->
<?	}
	if ( $GAMEMASTER_SCRIPTS!="OFF" && ($_SESSION["group"]=="gamemaster" || $_SESSION["group"]=="admin") )
	{ ?>
	<!-- OPEN gamemaster menus -->
		<br /><img src="./img/gm_menu_title.gif" width="78" height="13" alt="gm menu" border="0" /><br />
		<a href="javascript: script_window('./gm.php?script=quest_add&title=Create New Quest');" onMouseOver="window.status='host a new quest'; return 0" onMouseOut="window.status=''; return 0">host new quest</a><br />
		<a href="javascript: script_window('./gm.php?script=quest_edit&title=Edit Quest');" onMouseOver="window.status='edit a quest'; return 0" onMouseOut="window.status=''; return 0">edit quests</a><br />
		<a href="javascript: script_window('./gm.php?script=quest_members&title=Edit Quest Members');" onMouseOver="window.status='edit a quest backstory'; return 0" onMouseOut="window.status=''; return 0">quest members</a><br />
		<a href="javascript: script_window('./gm.php?script=preface_edit&title=Edit Backstory');" onMouseOver="window.status='edit a quest backstory'; return 0" onMouseOut="window.status=''; return 0">edit backstory</a><br />
	<!-- CLOSE gamemaster menus -->
<?	}
	if( $ADMIN_SCRIPTS!="OFF" && $_SESSION["group"]=="admin" )
	{ ?>
	<!-- OPEN admin menus -->
		<br /><img src="./img/admin_menu_title.gif" width="78" height="13" alt="admin menu" border="0" /><br />
		<a href="javascript: script_window('./admin.php?script=user_add&title=Add New User');" onMouseOver="window.status='user add'; return 0" onMouseOut="window.status=''; return 0">new user</a><br />
		<a href="javascript: script_window('./admin.php?script=user_edit&title=Edit User');" onMouseOver="window.status='user edit'; return 0" onMouseOut="window.status=''; return 0">edit user</a><br />
		<a href="javascript: script_window('./admin.php?script=user_delete&title=Delete User');" onMouseOver="window.status='user delete'; return 0" onMouseOut="window.status=''; return 0">delete user</a><br />
		<a href="javascript: script_window('./admin.php?script=user_listing&title=List Registered Users');" onMouseOver="window.status='user listing'; return 0" onMouseOut="window.status=''; return 0">list users</a><br />
		<a href="javascript: script_window('./admin.php?script=passwd_hash&title=Password Hasher');" onMouseOver="window.status='passwd hash'; return 0" onMouseOut="window.status=''; return 0">passwd hasher</a><br />
		<img src="./img/px.gif" width="1" height="5" border="0" /><br />
		<a href="javascript: script_window('./admin.php?script=quest_add&title=Create New Quest');" onMouseOver="window.status='quest add'; return 0">new quest</a><br />
		<a href="javascript: script_window('./admin.php?script=quest_edit&title=Edit Quest');" onMouseOver="window.status='quest edit'; return 0" onMouseOut="window.status=''; return 0">edit quest</a><br />
		<a href="javascript: script_window('./admin.php?script=quest_members&title=Edit Quest Members');" onMouseOver="window.status='quest edit'; return 0" onMouseOut="window.status=''; return 0">quest members</a><br />
		<a href="javascript: script_window('./admin.php?script=preface_edit&title=Edit Quest Backstory');" onMouseOver="window.status='backstory edit'; return 0">edit backstory</a><br />
		<a href="javascript: script_window('./admin.php?script=quest_delete&title=Delete Quest');" onMouseOver="window.status='quest delete'; return 0">delete quest</a><br />
		<a href="javascript: script_window('./admin.php?script=reply_codes&title=Reply Codes');" onMouseOver="window.status='view reply codes'; return 0">reply codes</a><br />
	<!-- CLOSE admin menus -->
<?	}
} else {
	if ( $db )
	{
		echo "<br />\n";
		if ( $PUBLIC_REGISTER=="ON" )
		{ ?>
			<a href="javascript: post_window('./inc/register.php');" onMouseOver="window.status='account register'; return 0" onMouseOut="window.status=''; return 0">account register</a><br />
<?		}
		elseif ( $PUBLIC_SIGNUP=="ON" && $PUBLIC_REGISTER!="ON"  )
		{ ?>
			<a href="javascript: post_window('./inc/signup.php');" onMouseOver="window.status='account register'; return 0" onMouseOut="window.status=''; return 0">account register</a><br />
<?		}
		if ( $PUBLIC_CONTACT=="ON" )
		{ ?>
			<a href="javascript: post_window('./inc/contact.php');" onMouseOver="window.status='contact the questlog admin'; return 0" onMouseOut="window.status=''; return 0">contact form</a><br />
<?		}
		if ( $SHOW_TOP_USERS=="ON" )
		{
			echo "<br /><b>Top Users:</b><br />";
			$top_users = get_top_users($LIMIT_TOP_USERS_BY_NUMBER);
			

			
			print_array($top_users);
			
			#while( $top_users )
			#{
			#	$login_name = $top_users["login_name"];
			#	$totalposts = $top_users["totalposts"];
			#	echo "&nbsp;" . $login_name . "&nbsp;(" . $totalposts . ")&nbsp;<br />";
			#}
		}
	}
}
if ( $db )
{ 
	#if ( $RSS_FEED=="ON" ) { echo "<br /><a href=\"" . $RSS . "\"><img src=\"./img/xml.gif\" width=\"36\" height=\"14\" alt=\"Questlog RSS Feed\" border=\"0\" /></a>"; }
	#if ( TRUE )
	#{
		$user_logins = str_replace(", ","<br />", $recent_logins = read_login_log("1000"));
		echo "<br /><strong>Recent Users:</strong><br />";
		echo $user_logins;
		echo "<br />";
	#}	
}
?>
