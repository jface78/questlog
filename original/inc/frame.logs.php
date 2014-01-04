<?
session_start();
$page_type = "frame";
require("./control.php");
include($HTMLHEADER);
if( check_session("admin") )
{
	$_SESSION["uid"]; $_SESSION["login"]; $_SESSION["email"]; $_SESSION["group"];
	if ( isset($_GET["x"]) )
	{
		$no_log = "no log file found<br />";
		switch ( $_GET["x"] )
		{
    		case login:
				if ( $log = readlog($LOGIN_LOG) )
				{
					$log_file = $LOGIN_LOG;
				}
				else { $log = $no_log; }
				break;
    		case error:
				if ( $log = readlog($ERROR_LOG) )
				{
					$log_file = $ERROR_LOG;
				}
				else { $log = $no_log; }
    	    	break;
			case action:
				if ( $log = readlog($ACTION_LOG) )
				{
					$log_file = $ACTION_LOG;
				}
				else { $log = $no_log; }
				break;
			default:
				$log = $no_log;
		} ?>
		&nbsp;&nbsp;<a href="./frame.php">&nbsp;admin frame</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="./inc/frame.logs.php?x=login">view login log</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="./inc/frame.logs.php?x=action">view action log</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="./inc/frame.logs.php?x=error">view error log</a><br /><br />
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="newsframe">
		<tr valign="top">
			<td width="20"><img src="./img/px.gif" width="20" height="1" border="0" /></td>
			<td nowrap="nowrap" width="100%">
			<?	echo "Reading log from: <b>" . $log_file . "</b><br /><br />" . $log . "<br />[ End of Log ]<br /><br />"; ?>
			</td>
		</tr>
		</table>
<?	} else { echo "the rquested log is not a valid logfile. &nbsp;&nbsp;<a href=\"./inc/frame.detect.php\">&laquo;&nbsp;admin frame</a><br />"; }
} else { echo "access to this log is not possible at this time."; }
check_include($HTMLFOOTER);
?>

