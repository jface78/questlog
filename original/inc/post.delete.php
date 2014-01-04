<?
if( check_access("post") )
{
	if ( $_GET["id"]!="" && $_GET["post"]!="" && $_POST["STAGE"]!="1" )
	{
		$post_query = mysql_query("SELECT p.uid, p.post_text FROM posts p WHERE p.pid='$_GET[post]'") or die("an error has occured while querying the database.[1]");
		$postdata = mysql_fetch_array($post_query);
		$post_owner = $postdata["uid"];
		$post_text_raw = $postdata["post_text"];
		$post_text = formatContent($post_text_raw, "1");
		$quest_query = mysql_query("SELECT q.uid FROM quests q WHERE q.qid='$_GET[id]'") or die("an error has occured while querying the database.[2]");
		$questdata = mysql_fetch_array($quest_query);
		$quest_owner =  $questdata["uid"]; ?>
		<form method="post" action="<? echo $POST_TO; ?>" class="text">
		<input type="hidden" name="id" value="<? echo $_GET["id"]; ?>" />
		<input type="hidden" name="post" value="<? echo $_GET["post"]; ?>" />
		<input type="hidden" name="STAGE" value="1" />
			Are you certain you want to permanently delete this post?<br /><br /><? echo $post_text; ?><br /><br />
			<input name="submit" type="submit" value="&nbsp;&nbsp;delete&nbsp;&raquo;&nbsp;" class="button" />
			<input name="close" type="button" onclick="javascript: window.close();" value="&nbsp;&nbsp;close&nbsp;&nbsp;" class="button" />
		</form>
<?	}
	elseif ( $_POST["id"]!="" && $_POST["post"]!="" && $_POST["STAGE"]=="1" )
	{
		$post_query = mysql_query("SELECT p.uid  FROM posts p WHERE p.pid='$_POST[post]'") or die("an error has occured while querying the database.[1]");
		$postdata = mysql_fetch_array($post_query);
		$post_owner =  $postdata["uid"];
		$quest_query = mysql_query("SELECT q.uid FROM quests q WHERE q.qid='$_POST[id]'") or die("an error has occured while querying the database.[1]");
		$questdata = mysql_fetch_array($quest_query);
		$quest_owner =  $questdata["uid"];
			$formated_content = formatContent($_POST["post_content"]);
			$datetime = getCurrentDate();
			mysql_query("DELETE LOW_PRIORITY FROM posts WHERE pid='$_POST[post]'") or die("An error occured while posting to your quest.[3]");
		  mysql_query("DELETE LOW_PRIORITY FROM rolls WHERE rid='" . $_POST['post'] . "'") or die("An error occured while removing an associated roll.[4]");
			mysql_query("DELETE LOW_PRIORITY FROM reply_codes WHERE pid='" . $_POST['post'] . "'") or die("An error occured while removing the reply code.[5]");
			echo $_SESSION["login"] . ", your post has been deleted. <a href=\"javascript: opener.window.location.reload(); window.close();\">close</a>";
			echo "<script language=\"JavaScript\" type=\"text/javascript\"> opener.window.location.reload(); window.setTimeout(\"opener.window.focus(); window.close();\", ". $POPUP_CLOSE_DELAY . ");</script>";
	}
	else { echo "<br /><br />A quest log has not been selected, the post script will fail. <a href=\"javascript: window.clsoe();\">exit</a>."; }
}
else { echo $_SESSION["login"] . ", you are not authorized to edit this post. <a href=\"javascript: window.close();\">close</a>"; } exit; ?>
