<?
if( check_access("post") )
{
	if ( $_GET["id"]!="" && $_GET["post"]!="" && $_POST["STAGE"]!="1" )
	{
		$post_query = mysql_query("SELECT p.uid,p.cid,p.post_text FROM posts p WHERE p.pid='" . $_GET["post"] . "'") or die("an error has occured while querying the database.[1]");
		$postdata = mysql_fetch_array($post_query);
		$post_owner = $postdata["uid"];
		$cid = $postdata["cid"];
		$post_text_raw = $postdata["post_text"];
		$post_text = formatContent($post_text_raw, "1");
		$quest_query = mysql_query("SELECT q.uid FROM quests q WHERE q.qid='" . $_GET["id"] . "'") or die("an error has occured while querying the database.[2]");
		$questdata = mysql_fetch_array($quest_query);
		$quest_owner =  $questdata["uid"]; ?>
		<form method="post" action="<? echo $POST_TO; ?>" class="text">
			<input type="hidden" name="id" value="<? echo $_GET["id"]; ?>" />
			<input type="hidden" name="post" value="<? echo $_GET["post"]; ?>" />
			<input type="hidden" name="STAGE" value="1" />
			<textarea name="post_content" cols="55" rows="20" class="field" style="width:390px; height:335px;"><? echo $post_text; ?></textarea><br />
			<div align="right"><img src="./img/px.gif" width="1" height="3" border="0" /><br />
			<div>
        &nbsp;<input type="checkbox" name="reply" value="send" />&nbsp;send new reply code&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;<input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&laquo;&nbsp;edit&nbsp;&raquo;&nbsp;&nbsp;&nbsp;" class="button" />&nbsp;
        &nbsp;<input type="button" onclick="javascript: window.close();" value="&nbsp;&nbsp;close&nbsp;&nbsp;" class="button" />&nbsp;
			</div>
			</div>
		</form>
<?	}
	elseif ( $_POST["id"]!="" && $_POST["post"]!="" && isset($_POST["post_content"]) && $_POST["STAGE"]=="1" )
	{
		$post_query = mysql_query("SELECT p.uid  FROM posts p WHERE p.pid='$_POST[post]'") or die("an error has occured while querying the database.[1]");
		$postdata = mysql_fetch_array($post_query);
		$post_owner =  $postdata["uid"];
		$quest_query = mysql_query("SELECT q.uid,q.quest_name FROM quests q WHERE q.qid='$_POST[id]'") or die("an error has occured while querying the database.[1]");
		$questdata = mysql_fetch_array($quest_query);
		$quest_owner =  $questdata["uid"];
		$quest_name =  $questdata["quest_name"];
		$datetime = getCurrentDate();
		$d20_post = d20_roll($_POST["post_content"]);
    $formated_content = formatContent($d20_post['post']);
		
    mysql_query("UPDATE posts SET post_text='$formated_content' WHERE pid='$_POST[post]'") or die("An error occured while posting to your quest.[2]");
    #mysql_query("SELECT rid FROM rolls WHERE rid='$_POST[post]'");
    if ( is_numeric($d20_post['roll']) ) {
        $roll_sql = "INSERT INTO rolls(rid,roll) VALUES('" . $_POST[post] . "','" . $d20_post['roll'] . "')";
        if ( ! $roll_insert = @mysql_query($roll_sql) ) { echo "There is already a roll recorded for this post, your re-roll is being ignored.<br /><br />"; }
    }
		if ( $_POST['reply']=="send" ) {
		  $reply_delete_sql = "DELETE LOW_PRIORITY FROM reply_codes WHERE pid='" . $_POST['post'] . "'";
		  mysql_query($reply_delete_sql) or die("An error occured while removing the old reply code.[3]");
		  reply_post_create($_SESSION["uid"], $_POST['post'], $_POST["id"], $d20_post['post'], $d20_post['roll']);
		  if ( $DEBUG_NOTICES=="YES" ) { send_simple_email($POSTS_EMAIL, $ADMIN_EMAIL, "reply-post debugger", "Post Editing:\n\n" . $reply_delete_sql); }
    }
		if ( $LOG2_POST_EDIT=="ON" ) { log2($ACTION_LOG, $_SESSION["login"] . " - " . $_SERVER["REMOTE_ADDR"] . " - Edit Post: " . $_POST["post"] . " on " . $quest_name . " - " ); }
		echo $_SESSION["login"] . ", your post has been sent. <a href=\"javascript: opener.window.location.reload(); window.close();\">close window</a>";
		echo "<script language=\"JavaScript\" type=\"text/javascript\"> opener.window.location.reload(); window.setTimeout(\"opener.window.focus(); window.close();\", ". $POPUP_CLOSE_DELAY . ");</script>";
	}
	else { echo "<br /><br />A quest log has not been selected, the post script will fail. <a href=\"" . $CLOSE . "\">close windows</a>."; }
}
else { echo $_SESSION["login"] . ", you are not authorized to edit this post. <a href=\"" . $CLOSE . "\">close window</a>"; } exit; ?>