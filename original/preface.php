<?
session_start();
$page_title = "backstory";
$page_type = "frame";
require("./inc/control.php");
require($HTMLHEADER);
if ( !check_session() ) { session_destroy(); }
if( $_GET["id"]!="" )
{
	$preface_query = mysql_query("SELECT q.quest_name,q.quest_status,q.post_access,q.read_access,p.preface_text FROM quests q, quest_prefaces p WHERE q.qid='$_GET[id]' AND q.qid=p.qid") or die("error during your database query.[1]");
	$preface = mysql_fetch_array($preface_query);
	
	$quest_status = $preface["quest_status"];
	$quest_members = $preface["quest_members"];
	$preface_text = $preface["preface_text"];
	
	echo "&nbsp;Backstory for <b>" . $preface["quest_name"] . "</b><br />";
?>
	&nbsp;<a href="javascript:window.history.go(-1);">&laquo;&nbsp;back</a><br />
	<hr color="#868684" width="100%" align="left" size="1" />
	
	<table border="0" cellpadding="0" cellspaceing="0" width="100%" class="main">
	<tr valign="top">
		<td align="left" class="borders">
		<?	echo $preface_text; ?>
		</td>
	</tr>
	</table>
<?
}
else {
	#require("/usr/www/webroot/questlog/inc/env.php");
	$page_title = "backstory: ERROR";
	check_include($HTMLHEADER);
	echo "<br /><br />A quest backstory has not been selected, this script has failed. <a href=\"./quests.entry.php\">exit</a>.<br /><br />";
} ?>
<br /><hr color="#868684" width="100%" align="left" size="1" />
&nbsp;<a href="javascript:window.history.go(-1);">&laquo;&nbsp;back</a><br />
<? check_include($HTMLFOOTER); exit; ?>
