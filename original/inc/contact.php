<?
session_start();
$page_title="Contact Form";
$page_type = "popup";
require("./control.php");
include($HTMLHEADER);

if( $_POST["sender_name"]!="" && $_POST["sender_email"]!="" && $_POST["sender_message"]!="" ) {
	if ( check_email($_POST["sender_email"]) )
	{
		if ( isset($_GET["m"]) )
		{
			if ( check_email($_GET["m"]) )
			{
				$to = $_GET["m"];
			}
			else { echo "This user's email address is not valid"; }
		}
		else { $to = $MAILTO; }
		/* this sets the email headers, you need all the \n for this to format right */
		$mailheaders = "From: " . $_POST["sender_email"] . "\n";
		$mailheaders .= "Reply-To: " . $_POST["sender_email"] . "\n\n";
		/* this will format the email message, as you recieve it */
		$msg = "Message to: " . $_GET["c"] . " from quest: " . $_GET["q"] . "\n";
		$msg .= "From:\t" . $_POST["sender_name"] . "\n";
		$msg .= "Email:\t". $_POST["sender_email"] . "\n";
		$msg .= "sender ip:\t" . $_SERVER["REMOTE_ADDR"] . "\n";
		$msg .= "sender browser:\t" . $_SERVER["HTTP_USER_AGENT"] . "\n";
		$msg .= "Message:\t" . $_POST["sender_message"] . "\n\n";
		if ( @mail ($to, "Questlog Contact Form", $msg, $mailheaders) ) /* recipient, subject, message, mail headers */
		{
			echo "<br /><br />You message has been sent.<br /><br />";
			echo "<script language=\"JavaScript\" type=\"text/javascript\">window.setTimeout(\"window.close();\", ". $POPUP_CLOSE_DELAY . ");</script>";
		}
		else {
			echo "<br /><br />There was an error while trying to send your message.<br /><br />";
			echo "<script language=\"JavaScript\" type=\"text/javascript\">window.setTimeout(\"window.close();\", 6000);</script>";
		}
	}
	else {
		echo "Your email address does not appear to be a valid address. Correct the address before using this function.";
		echo "<script language=\"JavaScript\" type=\"text/javascript\">window.setTimeout(\"window.close();\", 6000);</script>";
	}
}
else {
	if ( check_session() ) { $type="hidden"; } else { $type="text"; session_destroy(); } ?>
	<form method="post" action="<? echo $POST_TO; ?>" class="text">
	<?	if ( isset($_GET["m"]) ) { echo "mail to: <b>" . $_GET["m"] . "</b><br />"; } ?>
		your name: <b><? echo $_SESSION["login"]; ?></b><input type="<? echo $type; ?>" name="sender_name" value="<? echo $_SESSION["login"]; ?>" class="feild"><br />
		your email: <b><? echo $_SESSION["email"];?></b><input type="<? echo $type; ?>" name="sender_email" value="<? echo $_SESSION["email"]; ?>" class="feild"><br /><br />
		your message:<br /><textarea name="sender_message" cols="55" rows="15"  class="feild" style="width: 350px;"></textarea><br /><br />
		<input type="submit" value="&nbsp;&nbsp;send&nbsp;&raquo;&nbsp;" class="button">
	</form>
<?} check_include($HTMLFOOTER); ?>
