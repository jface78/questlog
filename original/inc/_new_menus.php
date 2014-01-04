<?	if( isset($_SESSION["uid"]) && isset($_SESSION["login"]) && isset($_SESSION["email"]) && isset($_SESSION["group"]) )
	{	?>
		<!-- OPEN player menus -->
		<img src="./img/player_menu_title.gif" width="78" height="13" alt="player menu" border="0"><br />
		<a href="./logout.php">logout</a><br />
		<a href="javascript: post_window('./contact.php');">contact form</a><br />
		<img src="./img/px.gif" width="1" height="5" border="0"><br />
	<?	file_menu("./adm/", ".php", "plr."); ?>
		<!-- CLOSE player menus -->
		<?	if($_SESSION["group"]=="gamemaster" || $_SESSION["group"]=="admin")
			{ 	?>
				<!-- OPEN gamemaster menus -->
				<br /><img src="./img/gm_menu_title.gif" width="78" height="13" alt="GM menu" border="0"><br />
			<?	file_menu("./adm/", ".php", "gm."); ?>
				<!-- CLOSE gamemaster menus -->
		<?	}
			if($_SESSION["group"]=="admin")
			{ 	?>
				<!-- OPEN admin menus -->
				<br /><img src="./img/admin_menu_title.gif" width="78" height="13" alt="admin menu" border="0"><br />
			<?	file_menu("./adm/", ".php", "adm."); ?>
				<!-- CLOSE admin menus -->
<?			}	
	} 
	else {	?>
		<!-- OPEN logout menus -->
		-------------------<br />
		<a href="javascript: post_window('./signup.form.php');" onMouseOver="window.status='account register'; return 0" onMouseOut="window.status=''; return 0">account register</a><br />
		<a href="javascript: post_window('./contact.php');" onMouseOver="window.status='contact the questlog admin'; return 0" onMouseOut="window.status=''; return 0">contact form</a><br />
		<!-- CLOSE logout menus -->
<?	}	?>
<br /><br />

