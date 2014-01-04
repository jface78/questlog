<?php
###################################################
#### QUESTLOG CONTROL FILE (./inc/control.php) ####
###################################################
$VERSION = "QUESTLOG_02_20050418_20070711";
$ADMIN_EMAIL = "srwadleigh@gmail.com";
$POSTS_EMAIL = "posts@sa.ensu.us";
$DEBUG_NOTICES = "NO";

date_default_timezone_set("UTC");
error_reporting(E_ALL & ~E_NOTICE | E_STRICT);  // E_ALL & ~E_NOTICE | E_STRICT
ini_set('session.cookie_lifetime', '0');

#### PATHWAYS ####
#$BASE_HREF = "https://www.questlog.org/";
#$BASE_HREF = "http://" . $_SERVER["HTTP_HOST"] . "/questlog/";
#$TRUE_PATH = $_SERVER["DOCUMENT_ROOT"] . "questlog/";

$BASE_HREF = "https://" . $_SERVER["HTTP_HOST"] . "/";
$TRUE_PATH = $_SERVER["DOCUMENT_ROOT"];

# directory paths #
$GLOBAL_INCLUDE_PATH = "/usr/home/www/sites/questlog/original/inc/";
$FULL_INC_PATH = "/usr/home/www/sites/questlog/original/inc/";
$INC_DIR = "inc/";
#$INCLUDE_PATH = $TRUE_PATH . $INC_DIR;
$INCLUDE_PATH = "/usr/home/www/sites/questlog/original/inc/";
#$INCLUDE_URL = $BASE_HREF . $INC_DIR;
$INCLUDE_URL = $INC_DIR;
$COMMAND_DIR = "cmd/";
$COMMAND_PATH = $TRUE_PATH . $COMMAND_DIR;
$COMMAND_URL = $BASE_HREF . $COMMAND_DIR;
$IMG_DIR = "img/";
$IMG_TRUE_PATH = $TRUE_PATH . $IMG_DIR;
$IMG_WEB_PATH = $BASE_HREF . $IMG_DIR;
$IMG_PATH = $TRUE_PATH . $IMG_DIR;
$IMG_URL = $BASE_HREF . $IMG_DIR;

# file paths #
$SUPER_FUNCTIONS = $GLOBAL_INCLUDE_PATH . "global.functions.php";
$FORMMAIL = $GLOBAL_INCLUDE_PATH . "formmail.php";
$FUNCTIONS = $INCLUDE_PATH . "functions.php";
$STYLESHEET = $INCLUDE_PATH . "styles.css.php";
$JAVASCRIPT_PATH = $INCLUDE_PATH . "javascript.php";
$BODYHEAD = $INCLUDE_PATH . "bodyhead.php";
$COPYRIGHT = $INCLUDE_PATH . "copyright.php";
$LOGIN_SUBMIT = $INCLUDE_URL . "login.auth.php";
$LOGIN_FORM = $INCLUDE_PATH . "login.form.php";
$LOGOUT_ACTION = $INCLUDE_PATH . "logout.php";
$NAMEDENY = $INCLUDE_PATH . "names.txt";
$HTMLHEADER = $INCLUDE_PATH . "htmlhead.php";
$HTMLFOOTER = "\n</body>\n</html>";
$LOGOUT = "logout.php";
$ENTRYPAGE = "entry.php";
$ENTRY_SUBMIT = $BASE_HREF . $ENTRYPAGE;
$QUESTLOG = "log.php";
$BACKSTORY = "preface.php";
$PLAINLOG = "story.php";
$STORY = $PLAINLOG;
$RSS = "questlog.rdf";
$CSS = "styles.css";
$LANG = "en-us";

#### SWITCHES ####
// uncomment these to disable or enable options
// by default some options are on while other are off
// expect the log to failsafe in these cases
#$SITE_STATUS = "DEAD";
#$ADMIN_SCRIPTS = "OFF";
#$GAMEMASTER_SCRIPTS = "OFF";
#$PLAYER_SCRIPTS = "OFF";
#$PUBLIC_REGISTER = "ON";
$LOGIN_ONLY = "ON";
$RSS_FEED = "ON";
$PRINT_POST_TOTALS = "OFF";
$SHOW_TOP_USERS = "OFF";
$PENDING_POSTS = "ON";
$PUBLIC_SIGNUP = "ON";
$PUBLIC_CONTACT = "OFF";
$CHARACTER_NAMES = "ON";
$RECORD_LOGIN = "ON";
$SHOW_LAST_LOGIN = "ON"; // this needs $RECORD_LOGIN to be ON //
$LOG_IP = "ON";
$LOG2_LOGIN = "ON";
//$LOG2_ACTION = "ON";
//$LOG2_ERRORS = "ON";
//$LOG2_POST_EDIT=="ON"
//$JAVASCRIPT_POPUPS = "OFF";

#### PREF ####
$DATABASE = "questlog";
$SITE_TITLE = "QUESTLOG";
$RSS_TITLE = "RSS FEED";
$THREAD_NAME = "Quests";
$login_name = "Player";
$GM_NAME = "Gamemaster";
$ADM_NAME = "Admin";
$OWNER_SUFFIX = "GM";
$LOGIN_LOG = $TRUE_PATH . "logs/logins";
$ACTION_LOG = $TRUE_PATH . "logs/actions";
$ERROR_LOG = $TRUE_PATH . "logs/errors";
//$LOGIN_LOG_DATA = " - " . $_SESSIONS["name"] . " - " . $_SERVER["REMOTE_ADDR"] . "\n";
#$COPYSTRING = "&copy;Copyright&nbsp;2001-2003&nbsp;<a href=\"http://questlog.beastmeat.com/\" class=\"smlink\">QUESTLOG</a>.&nbsp;Hosted&nbsp;by&nbsp;<a href=\"http://www.beastmeat.com/\" target=\"new\" class=\"smlink\">BEASTMEAT.com</a>";
$COPYSTRING = "&copy;Copyright 2001-2011 All Rights Reserved. All Content, Characters, Locations, etc Are the Intellectual Property of Respective Users.";

# quest page controls #
$CONTACT_FORM_ADDRESS = $ADMIN_EMAIL;
$LOG_REFRESH = "2800";
$LOG_LIMIT = "40";
$DEFAULT_POST_ORDER = "1";
$POPUP_CLOSE_DELAY = "2000";
$LIMIT_TOP_USERS_BY_NUMBER = "50";
$LIMIT_TOP_USERS_BY_POSTS="100";

# formmail settings #
$MAILTO = "srw";
$RETURN_URL = $BASE_HREF;
$RETURN_METHOD = "HEADER"; /* unset to merely echo out mail success/falure message */

# advanced settings #
$PAGE_UPDATED = $lastupdated = date( "D M d H:i:s T Y", getlastmod() );
$CLOSE = "javascript: window.close();";
$BACKLINK = "javascript:window.history.go(-1);";
$POPCLOSE = "<script language=\"JavaScript\" type=\"text/javascript\">window.setTimeout(\"window.close();\", 2000);</script>";
$BACKURL = $BACKLINK;
#$POST_TO = "http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
$POST_TO = $BASE_HREF . basename($_SERVER["REQUEST_URI"]);

# error messages #
$ERROR_NOFILE = "Sorry, no content was found.";
$ERROR_NOIMG = "Sorry, no image file was found.";
$ERROR_NODB = "Sorry no content was found.";
$ERROR_QUERYDB = "Sorry a problem has occured while contacting database.";
$ERROR_DB_QUERY ="an error has occured while querying the database";
$ERROR_THREAD_ACCESS = "Access to this thread is restricted.";
$ERROR_DB_OFFLINE = "the database server appears to be offline, you should <a href=\"./logout.php\">logout</a>.";
$ERROR_NAME = "The chosen name is already in use, <a href=\"" . $POST_TO . "\">try another</a>.";

#### COLOURS and STYLE ####
$hour = date( "H", time() );

$TITLE_IMG_ALT = "questlog, online gaming.";
#$TITLE_IMG_FILE = "title.01.gif";

if ( $hour > 6 && $hour < 17 ) { $TITLE_IMG_FILE = "title.05.gif"; } else { $TITLE_IMG_FILE = "title.06.gif"; }

$TITLE_IMG_URL = $IMG_WEB_PATH . $TITLE_IMG_FILE;
$TITLE_IMG_SRC = $IMG_TRUE_PATH . $TITLE_IMG_FILE;
if( is_readable($TITLE_IMG_SRC) )
{
	$img_meta = getimagesize($TITLE_IMG_SRC);
	$TITLE_IMG_WIDTH = $img_meta[0];
	$TITLE_IMG_HEIGHT = $img_meta[1];
}
else {
	$TITLE_IMG_SRC = $IMG_PATH . "px.gif"; 
	$TITLE_IMG_WIDTH = "1";
	$TITLE_IMG_HEIGHT = "1";
	$TITLE_IMG_ALT = "";
}

$BAR_BG_1 = $IMG_WEB_PATH . "barbg_2.gif";

#$PAGE_BG_COLOR = "#393C40";
$PAGE_BG_COLOR = "#36373B";
#$PAGE_BG_COLOR = "#151212";
$FRAME_BG_COLOR = "#393939";
$POPUP_BG_COLOR = "#36373B";
$TEXT_1_COLOR = "#FFEC4A";
$TEXT_2_COLOR = "#FFFFFF";
$ROW_1_COLOR = "#535456";
$ROW_2_COLOR = "#656566";
$ROW_HEADING_COLOR = "#514C4C";
$SIDEFRAME_COLOR = "#514C4C";
$BORDER_COLOR = "#000000";
$LOG_BORDER_COLOR = "#000000";

/*
$PAGE_BG_COLOR = "#1A1616";
$FRAME_BG_COLOR = "#393939";
$POPUP_BG_COLOR = "#393C40";
$ROW_1_COLOR = "#111111";
$ROW_2_COLOR = "#201E1E";
$ROW_HEADING_COLOR = "#222222";
$SIDEFRAME_COLOR = "#514C4C";
$TABLE_BORDER_COLOR = "#868684";
*/

$BASE_FONT_COLOR = "#C4C4C4";
$TEXT_COLOR = "#FFFFFF";
$TEXT_HEAD_COLOR = "#FFFFFF";
$LINK_COLOR = "#FFFFFF";
$LINK_HOOVER_COLOR = "#FFFFFF";

$LINE_COLOR = "#000000";
$LINE_1_COLOR = "#868684";
$LINE_2_COLOR = "#000000";

if( is_readable($STYLESHEET) )
{
$FONTS = "verdana,sans-serif";
$LARGE_FONT_SIZE = "14px";
$SMALL_FONT_SIZE = "10px";
$FEILD_OUTLINE_COLOR = "#535456";
$FEILD_FILL_COLOR = "#000000";
$FEILD_TEXT_COLOR = "#535456";
$BUTTON_OUTLINE_COLOR = "";
$BUTTON_FILL_COLOR = "";
$BUTTON_TEXT_COLOR = "";
} else { $errors = "No style sheets found for color integration.\n"; }

#### report error ####
// this is where $errors gets writing the the error log and unsets

#### SOME FUNCTIONS THE NEED TO BE EVERYWHERE THAT THIS FILE IS ####
function check_include($include_path)
{
	if( is_file($include_path) )
	{
		include($include_path);
	}
	else { echo $include_path; }
}

#require_once("/usr/home/www/inc/shared_functions.php");
require_once($FULL_INC_PATH . "functions.php");
require_once($FULL_INC_PATH . "shared_functions.php");

##### VARIFY ADDRESS BAR AND REDIRECT IF NEEDED #####
// vars: $_SERVER["REQUEST_URI"] $_SERVER["HTTP_HOST"]
$BASE_URL = basename($BASE_HREF);
#if ( $_SERVER["HTTP_HOST"] != $BASE_URL ) { header("Location: " . $BASE_HREF); }

#############
#### EOF ####
#############
?>
