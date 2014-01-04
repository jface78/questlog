<?	if( $_POST["cid"]!="" && !isset($_POST["cid_2"]) && !isset($_POST["counterdata"]) )
	{
		$membership_check_query = mysql_query("SELECT cid FROM quest_members WHERE cid='" . $_POST["cid"] . "'") or die("an error has occured while checking for quest membership.[0]");
		$membership_check = mysql_num_rows($membership_check_query);
		if ( $membership_check == 0 )
		{
			$character_info_query = mysql_query("SELECT c.cid,c.uid,c.char_name,c.char_title,cp.profile,ch.history FROM characters c, character_profiles cp, character_prefaces ch WHERE c.uid='" . $_SESSION["uid"] . "' AND c.cid='" . $_POST["cid"] . "' AND c.cid=cp.cid AND c.cid=ch.cid") or die("an error has occured while querying the database.[1]");
			$character_info = mysql_fetch_array($character_info_query);
			$cid =  $character_info["cid"];
			$char_name =  $character_info["char_name"];
		        $char_title =  $character_info["char_title"];
			$char_profile =  $character_info["profile"];
			$char_history =  $character_info["history"];
			$uid_check =  $character_info["uid"];
			$formated_char_profile = formatContent($char_profile,1);
			$formated_char_history = formatContent($char_history,1);	?>
			<form action="<? echo $POST_TO; ?>" method="POST" class="form">
				<div align="left">
					Are you sure this is the character you want to remove?<br /><br />
					character name:<br />
					<b><? echo $char_name; ?></b><br />
					character title/family name:<br />
					<b><? echo $char_title; ?></b><br /><br />
					character profile:<br />
					<b><? echo $formated_char_profile; ?></b><br /><br />
					character history:<br />
					<b><? echo $formated_char_history; ?></b><br />
				</div>
				<div align="right"><br />
					<input type="hidden" name="cid_2" value="<? echo $cid; ?>">
					<input type="hidden" name="counterdata" value="<? echo $uid_check; ?>">
					<input type="hidden" name="character_name" value="<? echo $char_name; ?>">
					<input type="button" name="close" value="&nbsp;cancel&nbsp;" class="button" onclick="window.close();">
					<input type="submit" name="submit" value="remove&nbsp;character&nbsp;&raquo;" class="button">
				</div>
			</form>
<?		}
		else { echo "This character is a member of 1 or more quests, if you still want to remove the character you must clear all quest memberships first."; }
	}
	elseif( $_POST["cid_2"]!="" && $_POST["counterdata"]==$_SESSION["uid"] )
	{
		$character_delete = mysql_query("DELETE FROM characters WHERE cid='" . $_POST["cid_2"] . "'") or die("an error has occured while removing your character.[2]");
		$profile_delete = mysql_query("DELETE FROM character_profiles WHERE cid='" . $_POST["cid_2"] . "'") or die("an error has occured while removing your character profile.[3]");
		$history_delete = mysql_query("DELETE FROM character_prefaces WHERE cid='" . $_POST["cid_2"] . "'") or die("an error has occured while removing your character history.[4]");
		$membership_delete = mysql_query("DELETE FROM quest_members WHERE cid='" . $_POST["cid_2"] . "'") or die("an error has occured while clearing member lists.[5]");
		
		log2($ACTION_LOG, $_SESSION["login"], "User Delete Character : " . $_POST["character_name"]);
		
		echo "Character " . $_POST["character_name"] . ", has been successfully <b>removed</b>.<br /><br />";
		echo "<hr color=\"#868684\" width=\"380\" align=\"left\" size=\"1\">";
		echo "<a href=\"javascript: opener.window.location.reload(); window.close();\">close window</a>";
		echo "<script language=\"JavaScript\" type=\"text/javascript\"> opener.window.location.reload(); window.setTimeout(\"opener.window.focus(); window.close();\", 5000);</script>";
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
			select the character you wish to remove.<br /><br />
			<select name="cid" class="field">	
<?			while( $characters = mysql_fetch_array($characters_query) )
			{
				$cid =  $characters["cid"];
				$char_name =  $characters["char_name"];
    			$char_title =  $characters["char_title"];
				
				if($char_title!="")
				{
					$char_title_checked = ", " . $char_title;
				} else { $char_title_checked = $char_title; }
				
				echo "<option value=\"" . $cid . "\">" . $char_name . $char_title_checked . "</option>\n";
			}	?>
			</select>
			<input type="submit" name="submit" value="&nbsp;&nbsp;&nbsp;delete&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button">
			</form>	
<?		}	
	}		?>
