<?	if( $_POST["cid"]!="" && !isset($_POST["new_character_name"]) )
	{
		$character_info_query = mysql_query("SELECT c.cid,c.uid,c.char_name,c.char_title,cp.profile,ch.history FROM characters c, character_profiles cp, character_prefaces ch WHERE c.uid='" . $_SESSION["uid"] . "' AND c.cid='" . $_POST["cid"] . "' AND c.cid=cp.cid AND c.cid=ch.cid") or die("an error has occured while querying the database.[1]");
		$character_info = mysql_fetch_array($character_info_query);
		$formated_char_profile = formatContent($character_info["profile"], 1);
		$formated_char_history = formatContent($character_info["history"], 1);?>
		<form action="<? echo $POST_TO; ?>" method="POST" class="form">
		<input type="hidden" name="current_name" value="<? echo $character_info["char_name"]; ?>">
		<div align="left">
			character name:<br /><input type="text" name="new_character_name" value="<? echo $character_info["char_name"]; ?>" size="25" class="field"><br />
			character title/family name:<br /><input type="text" name="new_character_title" value="<? echo $character_info["char_title"]; ?>" size="25" class="field"><br /><br />
			character profile:<br /><textarea name="new_character_profile" cols="55" rows="10" class="field" style="width: 350px;"><? echo $formated_char_profile; ?></textarea><br />
			character history:<br /><textarea name="new_character_history" cols="55" rows="10" class="field" style="width: 350px;"><? echo $formated_char_history; ?></textarea><br />
		</div>
		<div align="right"><br />
			<input type="hidden" name="cid" value="<? echo $character_info["cid"]; ?>">
			<input type="hidden" name="counterdata" value="<? echo $character_info["uid"]; ?>">
			<input type="submit" name="submit" value="submit&nbsp;&nbsp;edit&nbsp;&nbsp;&nbsp;&raquo;&nbsp;" class="button">
		</div>
		</form>
<?	}
	elseif( $_POST["current_name"]!="" && $_POST["cid"]!="" && $_POST["new_character_name"]!="" && $_POST["counterdata"]==$_SESSION["uid"] )
	{
		if ( $_POST["new_character_name"]!=$_POST["current_name"] ) { $name_check = check_username($_POST["new_character_name"]); } else { $name_check = "UN"; }
		if ( $name_check && check_file($_POST["new_character_name"], $NAMEDENY) )
		{
			if( $_POST["new_character_title"]!="" )
			{
				$formated_title = formatContent($_POST["new_character_title"]);
			} else { $formated_profile = $_POST["new_character_title"]; }
			
			if( $_POST["new_character_profile"]!="" )
			{
				$formated_profile = formatContent($_POST["new_character_profile"]);
			} else { $formated_profile = $_POST["new_character_profile"]; }
			
			if( $_POST["new_character_history"]!="" )
			{
				$formated_history = formatContent($_POST["new_character_history"]);
			} else { $formated_history = $_POST["new_character_history"]; }
			
			$character_update = mysql_query("UPDATE characters SET char_name='" . $_POST["new_character_name"] . "', char_title='" . $_POST["new_character_title"] . "' WHERE cid='" . $_POST["cid"] . "'") or die("an error has occured while updating your character.[2]");
			$profile_update = mysql_query("UPDATE character_profiles SET profile='" . $formated_profile . "' WHERE cid='" . $_POST["cid"] . "'") or die("an error has occured while updating your character profile.[3]");
			$history_update = mysql_query("UPDATE character_prefaces SET history='" . $formated_history . "' WHERE cid='" . $_POST["cid"] . "'") or die("an error has occured while updating your character history.[4]");
			
			log2($ACTION_LOG, $_SESSION["login"], "User Edit Character : " . $_POST["new_character_name"]);
			
			echo "Character " . $_POST["new_character_name"] . ", has been successfully <b>updated</b>.<br /><br />";
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
	else {
		$characters_query = mysql_query("SELECT c.cid,c.char_name,c.char_title FROM characters c WHERE c.uid='" . $_SESSION["uid"] . "'") or die("an error has occured while querying the database.[5]");
		$result_check = mysql_num_rows($characters_query);
		if( $result_check == 0 )
		{
			echo "<option><b>no characters found.</b></option>\n";
		}
		else
		{	?>
			<form action="<? echo $POST_TO; ?>" method="POST" class="form">
			select the character you wish to edit.<br /><br />
			<select name="cid"  class="field">	
<?			while( $characters = mysql_fetch_array($characters_query) )
			{
				if($characters["char_title"]!="")
				{
					$char_title_checked = ", " . $characters["char_title"];
				} else { $char_title_checked = $characters["char_title"]; }
				
				echo "<option value=\"" . $characters["cid"] . "\">" . $characters["char_name"] . $char_title_checked . "</option>\n";
			}	?>
			</select>
			<input type="submit" name="submit" value="&nbsp;&nbsp;&nbsp;edit&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button">
			</form>	
<?		}	
	}		?>
