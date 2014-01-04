<?
############################################
#   questlog functions file  2003.12.09    #
############################################

function databaseConnection($db)
{
	$file = "/usr/www/dbs/" . $db . ".db";
	if ( is_readable($file) && $read=@fopen($file, "r") )
	{
		$data = fread( $read, filesize($file) );
		fclose($read);
		$DBS = explode(":", $data);
		if( $db_pconnect = @mysql_pconnect("", $DBS["0"], $DBS["1"]) )
		{
			if( $db_select = @mysql_select_db($db, $db_pconnect) )
			{
				return($db_pconnect);
			} else { print("database not found\n"); }
		} else { print("database access denied\n"); }
	} else { print("access file not found\n"); }
}

function hashPasswd($username,$passwd) /* create a hashed passwd to be stored someone safe */
{
	$hash_1 = bin2hex( mhash(MHASH_RIPEMD160, $passwd . $username) );
	$hash_2 = bin2hex( mhash(MHASH_RIPEMD160, $passwd . $hash_1) );
	return($hash_2);
}

function roll_dice($post) /* Roll dice calls from a post, parce dice syntax *<num>d<dice_size>* if <num> does not exist assume value of 1, output: *** 1d20: 16 ****/
{
    eregi("^[0-9]*d(0-9a-z])*@[1-100]$");

}

function log2($LOG_FILE,$LOG_DATA,$DATE_STRING="D M d H:i:s T Y") /* write LOG_DATA to LOG_FILE, advice for LOG_FILE: chown root:www,chmod 770,chflag schg */
{
	if ( @is_writeable(dirname($LOG_FILE)) && $log_fp = @fopen($LOG_FILE, "a") )
	{
		if ( fwrite($log_fp, $LOG_DATA . date($DATE_STRING, time()) . "\n") )
		{
			fclose($log_fp);
			return TRUE;
		}
	}
}

function readlog($LOG_FILE) /* read LOG_FILE and return the contence in an xhtml format */
{
	if ( @is_readable($LOG_FILE) && $log_fp = @fopen($LOG_FILE, "r") )
	{
		if ( $FILE = nl2br(fread($log_fp, filesize($LOG_FILE))) )
		{
			fclose($log_fp);
			return $FILE;
		}
	}
}

function getCurrentDate()
{
	$getdate = getdate(time());
	
	$year = $getdate[year];
	$month = $getdate[mon];
	$day = $getdate[mday];
	$h = $getdate[hours];
	$m = $getdate[minutes];
	$s = $getdate[seconds];
	
	$datetime = "$year-$month-$day $h:$m:$s";
	
	return($datetime);
}

function unixStyleDate($timestamp)  /* formate timestamp like the unix data command */
{
	$date = date( "D M d H:i:s T Y", $timestamp );
	return($date);
}

function gethour()
{
	$hour = date( "H", time() );
	return $hour;
}

function logout()
{
	# End everything. #
	session_unregister('users_id');
	session_unregister('users_name');
	session_unregister('users_email');
	session_unregister('users_group');
	# Destroy everything. #
	session_destroy();
}

function last2post($qid,$datecut="-9")  /* get the username and date of most recent post for a given quest */
{
	$cid = @mysql_fetch_row( @mysql_query("SELECT p.cid FROM posts p WHERE p.qid='" . $qid . "' ORDER BY -p.pid LIMIT 1") );
	if ( $cid["0"]=="0" )
	{
			$sql = "SELECT u.login_name,p.post_date FROM posts p,users u WHERE p.qid='" . $qid . "' AND p.uid=u.uid ORDER BY -p.pid LIMIT 1";
	}
	else {	$sql = "SELECT c.char_name,p.post_date FROM posts p,characters c WHERE p.qid='" . $qid . "' AND p.cid=c.cid ORDER BY -p.pid LIMIT 1";	}
	if ( $lastposter = @mysql_fetch_row( @mysql_query($sql) ) )
	{
		$date = substr($lastposter["1"], 0, $datecut);
		$last = $lastposter["0"];
		$last_date = array($last,$date);
	}
	else { $last_date = array('record not found','000-00-00'); }
	return $last_date;
}

function count_posts($id, $as="0") /* count the total number of posts made by given user, quest, or character id */
{
	if ( is_numeric($as) && is_numeric($id) && $as < 3 )
	{
		switch ($as)
		{
			case "0": $AS = "uid"; break;
			case "1": $AS = "cid"; break;
			case "2": $AS = "qid"; break;
			default: $AS = "uid";
		}
		$total_post_count = mysql_result( mysql_query("SELECT count(pid) FROM posts WHERE " . $AS . "='" . $id . "'"), 0 );
		return $total_post_count;
	}
}

/*
function posts_by_quest($qid) 
{
	$total_post_count = mysql_result( mysql_query("SELECT count(pid) FROM posts WHERE qid='" . $qid . "'"), 0 );
	return $total_post_count;
}

function posts_by_user($uid) 
{
	$total_post_count = mysql_result( mysql_query("SELECT count(pid) FROM posts WHERE uid='" . $uid . "'"), 0 );
	return $total_post_count;
}

function posts_by_char($uid) 
{
	$total_post_count = mysql_result( mysql_query("SELECT count(pid) FROM posts WHERE cid='" . $cid . "'"), 0 );
	return $total_post_count;
}

function login_authorize($dbs)
{
	include a separate connection to the database
	which is not persistant and which is to be closed
	after the session is created.
while ( list($key, $val) = each($_SESSION) )
{
	if ( $val ) { break }
}
}
$quest_info = mysql_query("SELECT m.cid,c.char_name,q.quest_access FROM quest_members m,characters c,quests q WHERE m.qid='" . $qid . "' AND m.qid=q.qid AND m.cid=c.cid AND c.uid='" . $uid . "'")		
$member_check >= "1" || $uid == $quest_owner_id || $questinfo["quest_members"] == "ALL" || $_SESSION["group"] == "admin"
*/

function printCharacterMenu($uid, $qid)
{
	$sql = "SELECT m.cid,c.char_name FROM quest_members m, characters c WHERE m.qid='" . $qid . "' AND m.cid=c.cid AND c.uid='" . $uid . "'";
	$member_check = mysql_num_rows( mysql_query($sql) );
	if ( $member_check >= "1" )
	{
		return TRUE;
	}
}

function file_menu($PATH,$suffix,$prefix="") /* $PATH = "./adm/"; $prefix = "adm."; $suffix = ".php"; */
{
	$suffix_reg = "\\" . $suffix . "$";
	$prefix_reg = "^" . $prefix;
	if ($handle = opendir($PATH))
	{
   		while ( false !== ($files = readdir($handle)) ) 
		{
			if ( !is_readable($files) && $files!="." && $files!=".." && eregi($suffix_reg, $files) && eregi($prefix_reg, $files))
			{
				$file_1 = str_replace($prefix, "", $files);
				$file_2 = str_replace($suffix, "", $file_1);
				$filename = str_replace("_", "&nbsp;&nbsp;", $file_2);
        		print("<a href=\"javascript: script_window('" . $PATH . $files . "');\" onMouseOver=\"window.status='" . $filename . "'; return 0\" onMouseOut=\"window.status=''; return 0\">" . $filename . "</a><br />\n");
        	}
  		 }
   	 	closedir($handle); 
	}
}

function check_length($STRING, $SIZE="40") /* check the length of a string, defaults to hash length */
{
	$length = strlen($STRING);
	if ( $length==$SIZE ) { return TRUE; }
}

function check_passwds($PASS1, $PASS2) /* check the length of a string, defaults to hash length */
{
	if ( $PASS1!="" && $PASS2!="" && $PASS1==$PASS2 ) { return TRUE; }
}

function check_login()
{
	if( $_SESSION["uid"]!="" && $_SESSION["login"]!="" && $_SESSION["email"]!="" && $_SESSION["group"]!="" )
	{
		$sql="SELECT u.user_status,g.group_name FROM users u, groups g WHERE uid='" . $_SESSION["uid"] . "' AND login_name='" . $_SESSION["login"] . "' AND u.gid=g.gid";
		$userinfo = @mysql_fetch_array( @mysql_query($sql) );
		if( $userinfo["user_status"]==0 && $_SESSION["group"]==$userinfo["group_name"] )
		{
			return TRUE;
		}
	}
}

function check_member($uid, $qid) /* varify membership */
{
	$sql = "SELECT m.cid,c.char_name FROM quest_members m,characters c WHERE m.qid='" . $qid . "' AND m.cid=c.cid AND c.uid='" . $uid . "'";
	$member_check = mysql_num_rows( mysql_query($sql) );
	if ( $member_check >= "1" )
	{
		return $member_check;
	}
	else{ return FALSE; }
}

function checkstatus($status, $uid="0", $qid="0")
{
	if ( $status > 0 )
	{
		switch ($status)
		{
    		case 1: //access for logged in users only//
				if ( check_login() )
				{
					return TRUE;
				}
				break;
    		case 2: //access for quest members only//
				if ( check_member($uid,$qid) )
				{
					return TRUE;
				}
    	    	break;
			case 3: //no access for anyone, but still list quest//
				return FALSE;
				break;
			case 4: //no access for anyone and do not list quest//
				return FALSE;
				break;
			default:
				return FALSE;
		}
	}
	else { return TRUE; }
}

function check_session($GROUP="") /* varify login status and group level access if specified */
{
	if ( $GROUP=="" ) { $GROUP=$_SESSION["group"]; }
	if ( isset($_SESSION["uid"]) && isset($_SESSION["login"]) && isset($_SESSION["group"]) )
	{
		if ( $_SESSION["group"]==$GROUP || $_SESSION["group"]=="admin" )
		{
			return "true";
		}
	}
}

function characters($qid,$cid="0") /* create available character listing and specify cid for posts table */
{
	if ( $_SESSION["uid"]!=$qid )
	{
		$members = mysql_fetch_row(mysql_query("SELECT quest_members FROM quests WHERE qid='" . $qid . "'"));
		if ( $members["0"]!="ALL" )
		{
			$char_query = mysql_query("SELECT m.cid,c.char_name FROM quest_members m,characters c WHERE m.qid='" . $qid . "' AND m.cid=c.cid AND c.uid='" . $_SESSION["uid"] . "'");
		}
		else { $char_query = mysql_query("SELECT c.cid,c.char_name FROM characters c WHERE c.uid='" . $_SESSION["uid"] . "'"); }
		$char_count = mysql_num_rows($char_query);
		
		if ( $char_count == 1 && $members["0"]!="ALL")
		{
			$char_array = mysql_fetch_array($char_query);
			echo "<input type=\"hidden\" name=\"cid\" value=\"" . $char_array["cid"] . "\" />\n";
		}
		elseif ( $char_count > 1 || $members["0"]=="ALL" )
		{
			echo "<select name=\"cid\" class=\"field\">\n";
			if ( $char_count==0 ) { echo "<option value=\"0\">no characters found</option>\n"; }
			if ( $cid!="0" || $members["0"]=="ALL" || $_SESSION["uid"]=="admin" ) { echo "<option value=\"" . $cid . "\">&nbsp;</option>\n"; }
			while ( $char_array = mysql_fetch_array($char_query) )
			{
				if ( $cid!=$char_array["cid"] ) { echo "<option value=\"" . $char_array["cid"] . "\">" . $char_array["char_name"] . "</option>\n"; }
			}
			echo "</select><br />\n";
		}
		else { echo "<input type=\"hidden\" name=\"cid\" value=\"0\" />\n"; }
	}
	else { echo "<input type=\"hidden\" name=\"cid\" value=\"0\" />\n"; }
}

function check_access($qid) /* check the a quest row for access rights, this determines who can post */
{
	if ( $_SESSION["group"]=="admin" ) /* admin can always post */
	{
		return TRUE;
	}
	else /* for non-admin run the checks */
	{
		$quest = @mysql_fetch_array(@mysql_query("SELECT uid,quest_members FROM quests WHERE qid='" . $qid . "'"));
		switch ( $quest["quest_members"] )
		{
			case "ALL":  /* quests marked as ALL allow anyone to post */
				return TRUE;
				break;
			case "MEMBERS": /* check current user for quest membership or ownership */
				if ( $_SESSION["group"]=="gamemaster" && $_SESSION["uid"]==$quest["uid"] )
				{
					return TRUE;
				}
				else
				{
					$member_check = mysql_num_rows(@mysql_query("SELECT quest_name q,char_name c FROM quest_members m,quests q,characters c,users u WHERE u.uid='" . $_SESSION["uid"] . "' AND u.uid=c.uid AND c.cid=m.cid AND m.qid='" . $qid . "' AND m.qid=q.qid"));
					if ( $member_check > 0 ) { return TRUE; }
				}
				break;
			case "GM": /* quests marked as GM allow only gamemasters to post */
				if ( $_SESSION["group"]=="gamemaster" ) { return TRUE; } else { return FALSE; }
				break;
			default:
				return FALSE;
		}
	}
}

function mk_array($DB_QUERY) /* makes an array from index 0 of a db query */
{
	$string = "0";
	while ( $rows = mysql_fetch_row($DB_QUERY) )
	{
		$string .= "," . $rows["0"];
	}
	$array = explode(",", $string);
	return $array;
}

function check_file($STRING, $FILE) /* check if name is not banned and return true if it is */
{
	if ( is_readable($FILE) && $file_open = @fopen($FILE, "r") )
	{
		$file_data = fread( $file_open, filesize($FILE) );
		fclose($file_open);
		if ( !stristr($file_data, $STRING) )
		{
			return TRUE;
		}
	}
}

function check_questname($NAME) /* check if quest $NAME is free and return true if it is */
{
	$query = @mysql_query("SELECT quest_name FROM quests WHERE quest_name='" . $NAME . "'");
	$name_check = mysql_num_rows($query);
	if ( $name_check < 1 )
	{
		return TRUE;
	}
	mysql_free_result($query);
}

function check_username($NAME) /* check if user/character $NAME is free and return true if it is */
{
	$query1 = @mysql_query("SELECT char_name FROM characters WHERE char_name='" . $NAME . "'");
	$name_check_1 = mysql_num_rows($query1);
	$query2 = @mysql_query("SELECT login_name FROM users WHERE login_name='" . $NAME . "'");
	$name_check_2 = mysql_num_rows($query2);
	if ( $name_check_1 < 1 && $name_check_2 < 1 )
	{
		return TRUE;
	}
	mysql_free_result($query1);
	mysql_free_result($query2);
}

function check_email($email)
{
	if (eregi("^[0-9a-z_]([-_.]?[0-9a-z])*@[0-9a-z][-.0-9a-z]*\\.[a-z]{2,3}[.]?$", $email))
	{
		$host = substr(strstr($email, '@'), 1) . ".";
		if ( getmxrr($host, $validate_email_temp) || checkdnsrr($host, "ANY") )
		{
			return TRUE;
		}
	}
 }

function check_pending($UID,$QID) /* Users may have 1 pending post per quest, true means they may delay the current post. */
{
	$query = @mysql_query("SELECT pid FROM posts_pending WHERE qid=" . $QID . " AND uid=" . $UID . " LIMIT 1");
	$check = mysql_num_rows($query);
	if ( $check < 1 )
	{
		return TRUE;
	}
	mysql_free_result($query);
}

function get_top_users($top_users_limit="0")
{
	$top_users_sql = "SELECT u.login_name,COUNT(p.pid) AS totalposts FROM users u,posts p WHERE u.uid=p.uid GROUP BY u.uid ORDER BY totalposts DESC";
	if ( $top_users_limit > 0 ) { $top_users_sql .= " LIMIT " . $top_users_limit; }
	$top_users_query = @mysql_query($top_users_sql);
	$top_users_array = @mysql_fetch_array($top_users_query);
	return $top_users_array;
	mysql_free_result($top_users_query);
}

function protected_email($email) /* protect email syntax from spam */
{
	$email_1 = str_replace("@", " at ",  $email);
	$email_2 = str_replace(".", " dot ",  $email_1);
	return $email_2;
}

function formatContent($CONTENT,$TYPE="0") /* format content for record in mysql */
{
	$html = array('<i><blockquote>', '</blockquote></i>', '<b>', '</b>', '<i>', '</i>', '<u>', '</u>', '<s>', '</s>', '<pre>', '</pre>', '<center>', '</center>', '<img border="0" src="', '" height="', '" width="', '" align="', '<a target="new" href="', '</a>', '">', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '<blockquote>', '</blockquote>');
	$code = array('[quote]', '[/quote]', '[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]', '[s]', '[/s]', '[pre]', '[/pre]', '[c]', '[/c]', '[img=', 'h=', 'w=', 'a=', '[url=', '[/url]', '/]', '[tab]', '[block]', '[/block]');
	if ( $TYPE == "0" ) {
		#$smartquotes = str_replace('“', '"', $CONTENT); $smartquotes2 = str_replace('”', '"', $smartquotes); $smartquotes3 = str_replace('’', "'", $smartquotes2);
		$special_content = htmlspecialchars($CONTENT, ENT_QUOTES);
		$newlines = str_replace("\n", "<br />",  $special_content);
		$export_content = str_replace($code, $html, $newlines);
	} else {
		$newlines = eregi_replace('<br[[:space:]]*/?[[:space:]]*>', "\n", $CONTENT);
		$export_content = str_replace($html, $code, $newlines);
	}
	return $export_content;
}

### Diagnostic Funtions ###
function print_array($ARRAY)
{
	$id = current( $ARRAY );
	$key = key( $ARRAY );
	if ($id)
	{
		echo $id . ":" . $key . "<br />";
		
		while ($id = next($ARRAY))
		{
			$key = key( $ARRAY );
			echo $id . ":" . $key . "<br />";
		}
	}
	else { echo "no records found."; }
}

### EOF ###
?>
