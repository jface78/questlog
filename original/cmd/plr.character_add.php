<?	if ( $_POST["new_character_name"]!="" && $_POST["counterdata"]==$_SESSION["uid"] )
	{
		if ( check_username($_POST["new_character_name"]) && check_file($_POST["new_character_name"], $NAMEDENY) )
		{
			if ( $_POST["new_character_profile"]!="" )
			{
				$formated_profile = formatContent($_POST["new_character_profile"]);
			} else { $formated_profile = $_POST["new_character_profile"]; }
			
			if ( $_POST["new_character_history"]!="" )
			{
				$formated_history = formatContent($_POST["new_character_history"]);
			} else { $formated_history = $_POST["new_character_history"]; }
			
			$character_create_query = mysql_query("INSERT INTO characters(uid,char_name,char_title) VALUES('" . $_SESSION["uid"] . "','" . $_POST["new_character_name"] . "','" . $_POST["new_character_title"] . "')") or die("an error has occured while creating your character.[1]");
			$new_cid = mysql_insert_id();
			$profile_create_query = mysql_query("INSERT INTO character_profiles(cid,profile) VALUES('" . $new_cid . "', '" . $formated_profile . "')") or die("an error has occured while creating your character profile.[2]");
			$history_create_query = mysql_query("INSERT INTO character_prefaces(cid,history) VALUES('" . $new_cid . "', '" . $formated_history . "')") or die("an error has occured while creating your character history.[3]");
			
			log2($ACTION_LOG, $_SESSION["login"], "User Add Character : " . $_POST["new_character_name"]);
			
			echo "Character " . $_POST["new_character_name"] . ", has been successfully <b>added</b> to your account.<br /><br />";
			echo "<b>Name:</b> " . $_POST["new_character_name"] . "<br />";
			echo "<b>title/family name:</b> " . $_POST["new_character_title"] . "<br /><br />";
			echo "<b>Profile:</b><br />" . $formated_profile . "<br /><br />";
			echo "<b>History:</b><br />" . $formated_history ."<br /><br />";
			echo "<hr color=\"#868684\" width=\"380\" align=\"left\" size=\"1\">";
			echo "<a href=\"javascript: opener.window.location.reload(); window.close();\">close window</a>";
			echo "<script language=\"JavaScript\" type=\"text/javascript\"> opener.window.location.reload(); window.setTimeout(\"opener.window.focus(); window.close();\", 5000);</script>";
		}
		else { echo $ERROR_NAME; }
	}
	else {	?>
	<form action="<? echo $POST_TO; ?>" method="POST" class="form">
		<div align="left">
			character name:<br />
			<input type="text" name="new_character_name" value="" size="25" class="field"><br />
			character title/family name:<br />
			<input type="text" name="new_character_title" value="" size="25" class="field"><br /><br />
			character profile:<br />
			<textarea name="new_character_profile" cols="55" rows="10" class="field" style="width: 350px;"></textarea><br />
			character history:<br />
			<textarea name="new_character_history" cols="55" rows="10" class="field" style="width: 350px;"></textarea><br />
		</div>
		<div align="right">
			<input type="hidden" name="counterdata" value="<? echo $_SESSION["uid"]; ?>"><br />
			<input type="submit" name="submit" value="create&nbsp;&nbsp;character&nbsp;&nbsp;&nbsp;&raquo;&nbsp;" class="button">
		</div>
	</form>
<?	}	?>
