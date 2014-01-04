<?
if( check_access("post") )
{
  if( !isset($_REQUEST["id"]) || $_REQUEST["id"]=="" )
	{
		echo "<br /><br />A quest log has <b>not</b> been selected, this post script will fail, <a href=\"" . $CLOSE . "\">close window</a>.";
	}
	elseif( is_numeric($_POST["id"]) && is_numeric($_POST["cid"]) && $_POST["post_content"]!="" )
	{
    if ( $_POST["cid"] != last_char_id($_POST["id"]) )
    {
      $member_check_query = mysql_query("SELECT q.uid FROM quests q WHERE q.qid='" . $_POST["id"] . "'") or die("an error has occured while querying the database.[1]");
      $member_check = mysql_fetch_array($member_check_query);
      $quest_owner_id =  $member_check["uid"];
      if ( $LOG_IP=="ON" ) { $ip = $_SERVER["REMOTE_ADDR"]; } else { $ip = "0.0.0.0"; }
      $datetime = getCurrentDate();
      $d20_post = d20_roll($_POST["post_content"]);
      $formated_content = formatContent($d20_post['post']);
      
      #echo "*** " . $d20_post['roll'] . " ***<br />";
      #echo $formated_content;
      
      $insert_sql = "INSERT INTO posts(qid,uid,cid,post_status,post_text,post_date,post_ip) VALUES('" . $_POST["id"] . "','" . $_SESSION["uid"] . "','" . $_POST["cid"] . "','0','" . $formated_content . "','" . $datetime . "','" . $ip . "')";
      $post_insert = mysql_query($insert_sql) or die("An error occured while posting to your quest.[2]");
      $post_id = mysql_insert_id();
      
      if ( is_numeric($d20_post['roll']) ) { $roll_insert = mysql_query("INSERT INTO rolls(rid,roll) VALUES('" . $post_id . "','" . $d20_post['roll'] . "')") or die("An error occured while recording to your roll.[" . $post_id . "','" . $d20_post['roll'] . "]"); }
      
      mysql_query("DELETE LOW_PRIORITY FROM reply_codes WHERE qid='" . $_POST['id'] . "' AND uid='" . $_SESSION["uid"] . "'") or die("An error occured while removing the reply code.[4]");
      reply_post_create($_SESSION["uid"], $post_id, $_POST["id"], $d20_post['post'], $d20_post['roll']);
      
      echo $_SESSION["login"] . ", your post has been sent. <a href=\"javascript: opener.window.location.reload(); window.close();\">close window</a>";
      echo "<script language=\"JavaScript\" type=\"text/javascript\"> opener.window.location.reload(); window.setTimeout(\"opener.window.focus(); window.close();\", ". $POPUP_CLOSE_DELAY . ");</script>";
    }
    else {  echo $_SESSION["login"] . ", this character has already posted. <a href=\"" . $CLOSE . "\">close</a>"; }
	}
	else { ?>
		<form method="post" action="<? echo $POST_TO; ?>" class="text">
			<div style="visibility : hidden;"><? if ( $CHARACTER_NAMES=="ON" ) { $cid = characters($_GET["id"]); } else { echo "<input type=\"hidden\" name=\"cid\" value=\"0\" />"; } ?></div>
			<input type="hidden" name="id" value="<? echo $_GET["id"]; ?>" />
			<textarea name="post_content" cols="55" rows="20"  class="field" style="width:390px; height:335px;"></textarea><br />
			<div align="right"><img src="./img/px.gif" width="1" height="3" border="0" /><br />
			<input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&laquo;&nbsp;post&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button" />&nbsp;
			<input type="button" onclick="javascript: window.close();" value="&nbsp;&nbsp;close&nbsp;&nbsp;" class="button" />&nbsp;
			</div>
		</form>
<?	}
}
else { echo $_SESSION["login"] . ", you are not authorized to post. <a href=\"" . $CLOSE . "\">close</a>"; } exit;?>