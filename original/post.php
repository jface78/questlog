<?
session_start();
$page_title = "POST";
$page_type = "post";
require("./inc/control.php");
include($HTMLHEADER);
if( check_access("post") )
{ ?>
<table border="0" cellpadding="0" cellspacing="0" width="390" class="main">
<tr>
	<td align="left"><? echo $page_title . "&nbsp;as&nbsp;<b>" . $_SESSION["login"] . "</b>"; ?></td>
	<td align="right"><a href="<? echo $CLOSE; ?>"><img src="./img/closebox.gif" width="11" height="11" alt="close windows" border="0" /></a></td>
</tr>
<tr><td colspan="2" bgcolor="<? echo $LINE_COLOR; ?>" height="1"><img src="./img/px.gif" width="1" height="1" alt="" border="0" /></td></tr>
<tr><td colspan="2">
<div align="left">
<!-- OPEN post script body -->
<?	$script = "./inc/post." . $_GET["script"] . ".php";
	if ( is_readable($script) )
	{
		include($script);
	}
	else { print "no script file found"; } ?>
<!-- CLOSE post script body -->
</div>
</td></tr></table>
<?
}
else {
	session_destroy();
	echo "<script language=\"JavaScript\" type=\"text/javascript\">window.setTimeout(\"window.close();\", ". $POPUP_CLOSE_DELAY . ");</script>";
	echo "You do not seem to be currently logged in to the questlog, <a href=\"" . $CLOSE . "\">close window</a>.<br /><br />";
	echo "if you feel that you have reached this page in error please use the <a href=\"" . $POST_TO . "\">contact from</a> to email the admin.";
}
check_include($HTMLFOOTER);
exit;
?>