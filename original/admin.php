<?
session_start();
if ( isset($_GET["title"]) ) { $page_title=$_GET["title"]; } elseif ( isset($_GET["script"]) ) { $page_title=$_GET["script"]; } else { $page_title="no file"; }
$page_type = "popup";
require("./inc/control.php");
include($HTMLHEADER);
if ( check_session("admin") )
{ ?>
<table border="0" cellpadding="0" cellspacing="0" width="370" class="main">
<tr>
	<td align="left"><? echo $page_title . "&nbsp;as&nbsp;<b>" . $_SESSION["login"] . "</b>"; ?></td>
	<td align="right"><a href="javascript: window.close();"><img src="./img/closebox.gif" width="11" height="11" alt="close windows" border="0" /></a></td>
</tr>
<tr><td colspan="2"><hr color="#868684" width="370" align="left" size="1" noshade="noshade" /></td></tr>
<tr><td colspan="2">
<div align="left">
<!-- OPEN admin script body -->
<?	$script = "./cmd/adm." . $_GET["script"] . ".php";
	if ( is_readable($script) )
	{
		include($script);
	}
	else { print "no script file found"; } ?>
<!-- CLOSE admin script body -->
</div>
</td></tr></table>
<?
}
else { echo "Access Denied"; }
check_include($HTMLFOOTER);
exit;
?>