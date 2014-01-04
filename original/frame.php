<?
session_start();
$page_type = "frame";
require("./inc/control.php");
require($HTMLHEADER);
include($JAVASCRIPT_PATH);
//
// print frame menu //
if ( isset($_GET["x"]) ) { $menu = "&nbsp;<a href=\"" . $BACKLINK . "\">&laquo;&nbsp;back</a>&nbsp;&nbsp;|&nbsp;&nbsp;"; } else { $menu = "&nbsp;"; }
$menu .= "<a href=\"./frame.php?x=about\">About</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"./frame.php?x=news\">News</a>";
if ( check_session("admin") ) { $menu .= "&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"./frame.php?x=admin\">Admin</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"./inc/frame.logs.php?x=login\">view login log</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"./inc/frame.logs.php?x=action\">view action log</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"./inc/frame.logs.php?x=error\">view error log</a>"; }
$menu .= "<br /><br />";
echo $menu;
//
// include frame location //
if ( isset($_GET["x"]) ) 
{
	include($INCLUDE_PATH . "frame." . $_GET["x"] . ".php");
}
else { include($INCLUDE_PATH . "frame.news.php"); }
check_include($HTMLFOOTER);
?>