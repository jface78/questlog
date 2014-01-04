<?php
session_start();
$page_title = "plainlog";
$page_type = "story";
require("./inc/control.php");
require($HTMLHEADER);
if( $_GET["id"]!="" )
{
	if ( $db )
	{
		$quest_query = mysql_query("SELECT q.quest_name,q.quest_status FROM quests q WHERE q.qid='". $_GET["id"] . "'") or die("[ query 1 ]");
		$QUEST = mysql_fetch_array($quest_query) or die("[ fetch array 1 ]");
		$post_query_sql = "SELECT p.post_text FROM posts p WHERE p.qid='" . $_GET["id"] . "' ORDER BY p.pid";
		$post_query = mysql_query($post_query_sql) or die("[ query 2 ]");
	        	    
		echo $QUEST["quest_name"] . "<br /><br />";
	       
		#if ( check_status($QUEST["quest_status"], $_SESSION["uid"], $_GET["id"]) )
		if ( check_access() ) 
		{ ?>
			<table border="0" cellpadding="0" cellspacing="0" width="600" class="main">
			<tr>
				<td>
<?					$post_count = "0";
					while($POSTS = mysql_fetch_array($post_query))
					{
					    if($post_count % 2) { $row_bg = $TEXT_1_COLOR; } else {  $row_bg = $TEXT_2_COLOR; }
					    echo "<div style=\"color: " . $row_bg . "\">" . $POSTS["post_text"] . "</div><br />";
					    $post_count++;
					} 
					unset($post_count); ?>
				</td>
			</tr>
			</table>
<?		}
		else { echo "<br /><br /><br />" . $ERROR_THREAD_ACCESS . "<br /><br /><br />"; }
	}
	else { echo "<br /><br /><br />" . $ERROR_DB_OFFLINE . "<br /><br /><br />"; }
}
else { echo "<br /><br />A quest has not been selected, <a href=\"./quests.entry.php\">try again</a>.<br /><br />"; }
check_include($HTMLFOOTER); exit; ?>
