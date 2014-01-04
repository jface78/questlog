<?php // Shared Functions - srw@udor.net Tokyo 20070817 
#
## DATABASE Functions ##
function db_read_connect($_config_array=array()) /* open a connection to localhost mysql for read queries, write queries might be directed elsewhere */
{ 
	if ( isset($_SESSION['db_config_array']) && is_array($_SESSION['db_config_array']) ) { $_config_array = $_SESSION['db_config_array']; }
	$config_array = array_merge(array("db" => $_SESSION['db'], "dbs_path" => "/usr/home/www/dbs/", "read_host" => "localhost", "write_host" => "localhost", "read_ssl" => "no", "write_ssl" => "no", "report_type" => "source", "report_title" => "Database Status: ", "log_path" => "syslog", "log_read_active" => "no", "log_write_active" => "no", "log_type" => "4"),$_config_array);

	$file = $config_array['dbs_path'] . $config_array['db'] . ".db";
	if ( is_readable($file) && $read=@fopen($file, "r") )
	{
		$data = fread( $read, filesize($file) );
		fclose($read);
		$dbs = explode(":", $data);
		#if( $db_read_link = mysqli_connect($config_array['read_host'], $dbs['0'], $dbs['1'], $config_array['db']) ) 
		if( $db_read_link = mysql_connect($config_array['read_host'], $dbs['0'], $dbs['1']) ) 
		{
			if ( mysql_select_db($config_array['db'], $db_read_link) )
			{			
				$_SESSION['db_read_link'] = $db_read_link;
				report($config_array['report_title'] . "connection OK");
				return $db_read_link;
			} else { report($report_header . mysql_error()); }
		} else { 
			report($config_array['report_title'] . mysql_error(),  $config_array['report_type']);
			return FALSE;
		}
	} else { 
		report($config_array['report_title'] . "there is a problem with the access file", $config_array['report_type']);
		return FALSE;
	}
}

function db_read($_sql,$_read_link="",$_config_array=array()) /* wrapper for db read queries, this function relies on db_read_connect() */
{
	if ( isset($_SESSION['db_config_array']) && is_array($_SESSION['db_config_array']) ) { $_config_array = $_SESSION['db_config_array']; }
	$config_array = array_merge(array("db" => $_SESSION['db'], "dbs_path" => "/usr/home/www/dbs/", "read_host" => "localhost", "write_host" => "localhost", "read_ssl" => "no", "write_ssl" => "no", "report_type" => "source", "report_title" => "Database Status: ", "log_path" => "syslog", "log_read_active" => "no", "log_write_active" => "no", "log_type" => "4"),$_config_array);


	if ( empty($_read_link) ) { $_read_link = $_SESSION['db_read_link']; }
	if ( $read_query = mysql_query($_sql, $_read_link) ) 
	#if ( $read_query = mysql_query($_sql) ) 
	{ 
		logged($_sql, array("log_path" => $config_array['log_path'], "log_active" => $config_array['log_read_active'], "log_type" => "4"));
		return $read_query;
	} else { 
		report("Database Read Error: " . mysql_error()  . ". SQL: " . $_sql);
		logged(mysql_error(), array("log_path" => "syslog", "log_active" => $config_array['log_write_active'], "log_type" => "4"));
		return FALSE;
	}
}

function db_write($_sql,$_config_array=array()) /* wrapper for db write queries, this function opens is own connection */
{
	if ( isset($_SESSION['db_config_array']) && is_array($_SESSION['db_config_array']) ) { $_config_array = $_SESSION['db_config_array']; }
	$config_array = array_merge(array("db" => $_SESSION['db'], "dbs_path" => "/usr/home/www/dbs/", "read_host" => "localhost", "write_host" => "localhost", "read_ssl" => "no", "write_ssl" => "no", "report_type" => "source", "report_title" => "Database Status: ", "log_path" => "syslog", "log_read_active" => "no", "log_write_active" => "no", "log_type" => "4"),$_config_array);

	$file = $config_array['dbs_path'] . $config_array['db'] . ".db";
	if ( is_readable($file) && $read=@fopen($file, "r") )
	{
		$data = fread( $read, filesize($file) );
		fclose($read);
		$dbs = explode(":", $data);
		#if( $db_write_link = mysqli_connect($config_array['write_host'], $dbs['0'], $dbs['1'], $_db) ) 
		if( $db_write_link = mysql_connect($config_array['write_host'], $dbs['0'], $dbs['1']) ) 
		{
		if ( mysql_select_db($config_array['db'], $db_write_link) )
			{			
				if ( $write_query = mysql_query($_sql,$db_write_link) )
				{
   					$_SESSION['db_write_last_id'] = mysql_insert_id($db_write_link);
   					$_SESSION['db_write_rows'] = mysql_affected_rows($db_write_link);
   					logged($_sql, array("log_path" => $config_array['log_path'], "log_active" => $config_array['log_write_active'], "log_type" => "4"));
	   				return mysql_insert_id($db_write_link);
   				} else {
   					report("Database Write Error: " . mysql_error()  . ". SQL: " . $_sql);
   					#report(mysql_error($db_write_link),"Database Write Error", $config_array['report_type']);
   					logged(mysql_error($db_write_link), array("log_path" => "syslog", "log_active" => $config_array['log_write_active'], "log_type" => "4"));
   				}	
			} else { 
				report($report_header . mysql_error($db_read_link));
				logged(mysql_error($db_write_link), array("log_path" => $config_array['log_path'], "log_active" => $config_array['log_write_active'], "log_type" => "4"));
			}
   		} else { report($config_array['report_title'] . mysql_error(), $config_array['report_type']); }
   		mysql_close($db_write_link);
	} else { report($config_array['report_title'] . "there is a problem with the access file", $config_array['report_type']); }
}


function db_query_table_field($_table,$_id="",$_field="data") /* */
{
	if ( empty($_id) && isset($_GET['id']) ) { $_id = $_GET['id']; }
 	if ( is_numeric($_id) ) 
 	{ 
 		$sql = "SELECT " . $_field . " as " . $_table . ",status,timestamp FROM " . $_table . " WHERE cid='" . $_id . "'";
		if ( $query = db_read($sql) )
		{
			if ( $array = mysql_fetch_array($query) ) {
				return $array;
			} else {
				report("Database Read Table-Field Failure: " . $sql );
			}
		} else {
			report("Database Read Table-Field Failure: " . mysql_error());
		}
	}
}

function db_row_count($_sql) /* just count rows returned by a mysql statement */
{
	report($_sql);
	return $count = mysql_num_rows( db_read($_sql) );
}


function db_record_count($_query_link,$_report_type="echo") /* just count rows returned by a mysql statement */
{
	$count = mysql_num_rows($_query_link);
	if ( is_numeric($count) && $count > 0 ) 
	{
		if ( $count > 1) {
			$count_report = "Currently viewing " . $count . " records from the Database";
		} else {
			$count_report = "Currently viewing " . $count . " record from the Database";
		}
		report($count_report,$_report_type);
		return $count;
	} else {
		report("No records found in the Database",$_report_type);
		return "0";
	}
}

#
## LOGIN Functions ##
function passwd_hash($_salt,$_pass) /* create a hashed passwd to be stored in the user table */
{
	return $hash = bin2hex( mhash(MHASH_RIPEMD160, $_salt . $_pass) );
}

function login($_config_array=array(),$_db_array=array()) /* access_level = none | guest | users | super, hash_type = function name | md5 | ripe | none, ssl = no | yes */
{		
	if ( !isset($_SESSION['login_table_array']) || !is_array($_SESSION['login_table_array']) ) { $_SESSION['login_table_array'] = array(); }
	if ( !isset($_SESSION['login_config_array']) || !is_array($_SESSION['login_config_array']) ) { $_SESSION['login_config_array'] = array(); }
	$db_array = array_merge(array("db_table" => "users", "user_id" => "uid", "user_name" => "user", "password" => "pass", "salt" => "salt", "user_status" => "status", "user_level" => "type"),$_SESSION['login_table_array'],$_db_array);
	$config_array = array_merge(array("access_level" => "users", "hash_type" => "passwd_hash", "ssl" => "no", "form_path" => ".", "silent" => "no", "banner" => "yes", "banner_text" => $_SESSION['site_title'], "logout_url" => "logout/", "forgot_url" => "forgot/", "show_last_login" => "yes", "report_type" => "echo", "report_title" => "Login", "log_path" => "mysql", "log_in_active" => "yes", "log_out_active" => "yes", "log_fail_active" => "yes"),$_SESSION['login_config_array'],$_config_array);
	
	if ( !isset($_SERVER["HTTPS"]) && $config_array['ssl']=="yes" ) { header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]); }
	if ( $config_array['ssl']=="yes" ) { $config_array['form_path'] = "https://" . $_SERVER["HTTP_HOST"] . "/" . $config_array['form_path']; }
	
	if ( $config_array['access_level'] != "none" )
	{
		if ( isset($_REQUEST['s']) && $_REQUEST['s'] == "logout" ) 
		{ 
			logged("Logout", array("log_path" =>  $config_array['log_path'], "log_active" => $config_array['log_out_active'], "log_type" => "2"));
			session_unset();
			session_destroy();
			$_SESSION = array();
			report("You have logged out. Thank You.",$config_array['report_type']);
		} 
		
		if ( !isset($_SESSION["user"]) || $_SESSION["ip"] != $_SERVER['REMOTE_ADDR'] || $_SESSION["key"] != md5($_SESSION["uid"] . $_SESSION["user"] . $_SERVER['REMOTE_ADDR']) )
		{
			if ( isset($_POST['form']) && !empty($_POST["user"]) && !empty($_POST["pass"]) && ($_POST["form"] && $_POST["submit"]) == "login" )
			{
				if ( $config_array['banner']=="yes" ) { $_SESSION["show"] = "&nbsp;// " .  $config_array['banner_text'] . " - "; } else { $_SESSION["show"] = ""; }
				if ( $_POST["user"] == "guest" && $_POST["pass"] == "guest" && $login_array['access_level'] == "guest" )
				{
					$_SESSION["uid"] = "0";
					$_SESSION["user"] = $_POST["user"];
					$_SESSION["ip"] = $_SERVER['REMOTE_ADDR'];
					$_SESSION["show"] = "<div id=\"login-show\">Login as " . $_SESSION["user"] . " from " . gethostbyaddr($_SERVER['REMOTE_ADDR']) . " -- <a href=\"./" . $config_array['logout_url'] . "\">logout</a></div>";
					$_SESSION["key"] = md5($_SESSION["uid"] . $_SESSION["user"] . $_SESSION["ip"]);
					report($_SESSION["show"],$config_array['report_type']);
					#report($_SESSION["key"]);
					logged("Accepted Login", array("log_path" =>  $config_array['log_path'], "log_active" => $config_array['log_in_active'], "log_type" => "1"));
					return TRUE;
				} else {
					$query_sql = "SELECT u." . $db_array["user_id"] . ", u." . $db_array["user_name"] . ", u." . $db_array["password"];
					if ( !empty($db_array["salt"]) ) { $query_sql .= ", u." . $db_array["salt"]; }
					$query_sql .= " FROM " . $db_array["db_table"] . " u WHERE u." . $db_array["user_name"] . "='" . $_POST["user"] . "'";
					if ( !empty($db_array["user_status"]) ) { $query_sql .=" AND u." . $db_array["user_status"] . "='0'";  }
					$query_sql .= " LIMIT 1";
					if ( $query_link = mysql_query($query_sql) )
					{
						$query_data = mysql_fetch_array($query_link);
						if ( empty($query_data[$db_array["salt"]]) ) { $query_data[$db_array["salt"]] = $_POST["user"]; }
						switch ($config_array['hash_type']) {
						case "md5":
							$hash = md5($query_data[$db_array["salt"]] . $_POST["pass"]);
							break;
						case "ripe":
							$hash = bin2hex( mhash(MHASH_RIPEMD160, $query_data[$db_array["salt"]] . $_POST["pass"]) );
							break;
						case "none":
							$hash = $_POST["pass"];
							break;
						default:
							if ( function_exists($config_array['hash_type']) ) { $hash = $config_array['hash_type']($query_data[$db_array["salt"]], $_POST["pass"]);} 
							else { $hash = passwd_hash($query_data[$db_array["salt"]], $_POST["pass"]); }
							break;
						}
						if ( $query_data[$db_array["password"]] == $hash )
						{
							$_SESSION["uid"] = $query_data[$db_array["user_id"]];
							$_SESSION["user"] = $query_data[$db_array["user_name"]];
							$_SESSION["ip"] = $_SERVER['REMOTE_ADDR'];
							if ( $config_array['log_path'] == "mysql" && $config_array['show_last_login'] == "yes" ) 
							{ 
								$last_query = mysql_fetch_row( db_read("SELECT ip,timestamp FROM log WHERE uid='" . $_SESSION["uid"] . "' ORDER BY -timestamp LIMIT 1") );
								$last = ". Last connection from " . $last_query["0"] . " on " . $last_query["1"]; } else { $last = "";
							}
							$_SESSION["show"] = "<div id=\"login-show\">Login as " . $_SESSION["user"] . " from " . gethostbyaddr($_SERVER['REMOTE_ADDR']) . $last . " -- <a href=\"./" . $config_array['logout_url'] . "\">logout</a></div>";
							$_SESSION["key"] = md5($_SESSION["uid"] . $_SESSION["user"] . $_SESSION["ip"]);
							report($_SESSION["show"],"echo");
							#report($_SESSION["key"]);
							logged("Accepted Login", array("log_path" =>  $config_array['log_path'], "log_active" => $config_array['log_in_active'], "log_type" => "1"));
							return TRUE;
						} else {
							$_SESSION["user"] = $_POST["user"] . " - " . $_POST["pass"];
							$_SESSION["uid"] = "0";
							report("SQL: " . $query_sql);
							report("HASH: " . $hash);
							report($db_array["user_id"] . " " . $db_array["user_name"] . " "  .$db_array["password"] . " " . $db_array["salt"] . " " . $query_data[$db_array["salt"]]);
							report("your login failed to authenticate. <a href=\"./" . $config_array['forgot_url'] . "\">forgotten password?</a>",$config_array['report_type'],"Login Failure");
							logged("Failed Login", array("log_path" =>  $config_array['log_path'], "log_active" => $config_array['log_fail_active'], "log_type" => "3"));
						}
					} else { 
						$_SESSION["user"] = $_POST["user"] . " - " . $_POST["pass"];
						$_SESSION["uid"] = "0";
						report("SQl: " . $query_sql	);
						report("your login failed to authenticate.<br />" . mysql_error(),$config_array['report_type'],"Login Failure");
						logged("Failed Login", array("log_path" =>  $config_array['log_path'], "log_active" => $config_array['log_fail_active'], "log_type" => "3"));
					}
				}	
			}

			if ( !isset($_SESSION["user"]) || !isset($_SESSION["uid"]) || $_SESSION["ip"] != $_SERVER['REMOTE_ADDR'] || $_SESSION["key"] != md5($_SESSION["uid"] . $_SESSION["user"] . $_SERVER['REMOTE_ADDR']))
			{
				echo "<div id=\"login-area\"><form method=\"POST\" action=\"" . $config_array['form_path'] . "\" id=\"login-form\" name=\"login-form\">
					  <input type=\"hidden\" id=\"form\" name=\"form\" value=\"login\" /><br />
					  <input class=\"field\" type=\"text\" id=\"user\" name=\"user\" value=\"\" /><br />
					  <input class=\"field\" type=\"password\" id=\"pass\" name=\"pass\" value=\"\" /><br />
					  <input class=\"button\" type=\"submit\" id=\"submit\" name=\"submit\" value=\"login\" />
					  </form></div>";
				return FALSE;
			}
		} else {
			report($_SESSION["show"],"echo");
			report($_SESSION["key"]);
			return TRUE;
		}
	} else { report("logins are disabled, try back later.",$config_array['report_type']); return FALSE; }
}


function show_login($_report_type)
{
	if ( isset($_SESSION["user"]) && isset($_SESSION["show"]) && $_SESSION["ip"] != $_SERVER['REMOTE_ADDR'] )
	{
		report($_SESSION["show"],$_report_type);
	}	
}

#
## SECURITY Functions ##
function site_key($_salt="")
{
	if ( empty($_salt) && isset($_SESSION["site_tile"]) ) { $_salt = $_SESSION["site_tile"]; } else { return FALSE; }
	return md5($_salt);
}

#
## LOGGING Functions ##
#function report($_report_text,$_report_type="source",$_report_title="",$_report_email="root",$_report_status) /* source, log, email */
function report($_report_text,$_report_type="source",$_report_title="",$_report_email="root") /* source, log, email */
{
	if ( !empty($_report_text) )
	{
		switch ($_report_type) {
		case "echo":
	    	echo "<span class=\"report-text\">" . $_report_text . "</span><br />\n";
	    	break;
		case "css":
			#$escape_url = "/";
			#echo "<div id=\"report-screen\"></div>";
			#echo "<a href=\"" . $escape_url . "\"><div id=\"report-screen\"><span class=\"report-title\">" . $_report_title . "</span><br /><span class=\"report-text\">" . $_report_text . "</span></div></a><br />\n";
			#echo "<div id=\"report-screen\"><span class=\"report-title\">" . $_report_title . "</span><br /><span class=\"report-text\">" . $_report_text . "</span></div><br />\n";
			echo "<div id=\"report\" class=\"report-text\"><span class=\"report-title\">" . $_report_title . "</span><br />" . $_report_text . "</div><br />\n";
			break;
		case "source":
	    	echo "<!-- " . $_report_text . " //-->\n";
	    	break;
		case "log":
			logged($_report_type);
	    	break;
		case "email":
	    	break;
		}
	}
}

function logged($_log_data="",$_config_array=array()) /* 0 = unknown, 1 = login, 2 = logout, 3 = fail, 4 = db, 5 = script, 6 = debug */
{
	if ( !isset($_SESSION['log_config_array']) || !is_array($_SESSION['log_config_array']) ) { $_SESSION['log_config_array'] = array(); }
	$config_array = array_merge(array("log_path" => "mysql", "log_active" => "yes", "ip_lookup" => "no", "log_type" => "0"),$_SESSION['log_config_array'],$_config_array);
	
	if ( $config_array['log_active'] == "yes" )
	{
		if ( empty($_SESSION['user']) ) { $user = "unknown user"; } else { $user = $_SESSION['user']; }
		if ( empty($_SESSION['uid']) ) { $uid = "0"; } else { $uid = $_SESSION['uid']; }
		if ( $config_array['ip_lookup']=="yes") { $ip = gethostbyaddr($_SERVER['REMOTE_ADDR']); } else { $ip = $_SERVER['REMOTE_ADDR']; }
		
		$data = $_log_data . " for " . $user;
		$log_data = $data . " from " . $ip . " on " . $_SERVER["HTTP_HOST"] . $_SERVER['SCRIPT_NAME'];
		
		switch ($config_array['log_path']) {
		case "mysql":
			$sql = "INSERT DELAYED INTO log(uid,data,ip,script,type,status) VALUES('" . $uid . "','" . $data . "','" . $ip . "','" . $_SERVER['SCRIPT_NAME'] . "','" . $config_array['log_type'] . "','0')";
			mysql_query($sql);
			report(mysql_error());
			break;
		case "syslog":
			define_syslog_variables();
			openlog("php-logged", LOG_PID | LOG_PERROR, LOG_LOCAL0);
   	 		syslog(LOG_INFO, $log_data);
			closelog();
		}
	}
}

#
## VARIABLE Functions ##
function is_set($_var,$_string="") /* */
{
	if ( isset($_var) && !empty($_var) )
	{
		if ( !empty($_string) ) { if ( $_var==$_string ) { return TRUE; } }
		else { return TRUE; }
	}
}

#
## NUMBER Functions ##
function round_down($value)
{
	list($val,$dummy) = explode(".",$value);
   	return $dummy?$val+1:$val;
}

function generate_code($plength) //  string generatePassword(int), generates a random string based on the length passed as an argument, maximum length: 32 //
{
	// First we need to validate the argument that was given to this function
	// If need be, we will change it to a more appropriate value.
	if(!is_numeric($plength) || $plength <= 0) { $plength = 8; }
	if($plength > 32) { $plength = 32; }
	//
	// This is the array of allowable characters.  The ones in this array
	// are restricted to alphanumeric.
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	//
	// This is important:  we need to seed the random number generator
	mt_srand(microtime() * 1000000);
	//
    // Now we simply generate a random string based on the length that was
    // requested in the function argument
	for($i = 0; $i < $plength; $i++)
	{
		$key = mt_rand(0,strlen($chars)-1);
		$pwd = $pwd . $chars{$key};
	}
	//
	// Finally to make it a bit more random, we switch some characters around
	for($i = 0; $i < $plength; $i++)
	{
		$key1 = mt_rand(0,strlen($pwd)-1);
		$key2 = mt_rand(0,strlen($pwd)-1);
		$tmp = $pwd{$key1};
		$pwd{$key1} = $pwd{$key2};
		$pwd{$key2} = $tmp;
	}
	return $pwd;
}

function unix_date($_time="")  /* formate timestamp like the unix date command */
{
	if ( empty($_time) ) { $_time = time(); }
	return $date = date("D M d H:i:s T Y", $_time);
}

#
## CONTENT Functions ##
#function page($_name="",$_include="yes",$_dir_path="./pages/",$_ext="inc",$_default="default",$_404="404",$_report_type="css",$_report_title="Page Error") /* check the _GET[s] var for a valid page heading */
function page($_name_override="",$_config_array=array())
{
	if ( isset($_SESSION['page_array']) && is_array($_SESSION['page_array']) ) { $_config_array = $_SESSION['page_array']; }
	$config_array = array_merge(array("name" => $_REQUEST['s'], "name_prefix" => "", "default_name" => "default", "dir_path" => "pages/", "extension" => "inc", "default_404" => "File Not Found", "set_404_file" => "no", "include" => "yes", "nav_image_path" => "./img/nav/", "nav_image_pre" => "nav_", "nav_image_extension" => "gif", "report_type" => "css", "report_title" => "Page Error"),$_config_array);
	
	$base_path = dirname($_SERVER["SCRIPT_FILENAME"]);
	if ( empty($_name_override) ) { $name = $config_array['name']; } else { $name = $_name_override; };
	if ( $config_array['include'] == "no" ) { $config_array['report_type'] = "source"; }
	#if ( is_dir($base_path . $config_array['dir_path']) ) { report("Pages directory does not seem to exist: " . $base_path . $config_array['dir_path'],$config_array['report_type'],$config_array['report_title']); return FALSE; }
	if ( is_dir($base_path . $config_array['dir_path']) ) { report("Pages directory does not seem to exist: " . $base_path . $config_array['dir_path']); }
	
	
	if ( !empty($name) )
	{
	 
		$page = $config_array['dir_path'] . $name . "." . $config_array['extension'];
		if ( is_readable($page) )
		{
			if ( $config_array['include'] == "yes" ) { include($page); }
			return $page;
		} else {
		
			if ( $config_array['set_404_file'] == "yes" )
			{
			
				$page_404 = $config_array['dir_path'] . $config_array['default_404'] . "." . $config_array['extension'];
				if ( is_readable($page_404) )
				{
					if ( $config_array['include'] == "yes" ) { include($page_404); }
				} else {
					report("404 File Not Found: " . $page_404 . "<br />File Not Found: " . $page);
				}
				return $page_404;
			} else { 
				if ( $config_array['include'] == "yes" ) { report($config_array['default_404'] . ": " . $page); }
				return $config_array['default_404'];
			}
		}
		
		
	} else {
		$page = $config_array['dir_path'] . $config_array['default_name'] . "." . $config_array['extension'];
		if ( is_readable($page) ) { report("Default page does not seem to exist: " . $page); }
		#echo $page . "<br />";
		if ( $config_array['include'] == "yes" ) { include($page); }
	}
	return $page;
}

function page_nav($_by="page",$_config_array=array()) /* by image or page: read in files and loop out a menu of image links */
{
	if ( isset($_SESSION['page_array']) && is_array($_SESSION['page_array']) ) { $_config_array = $_SESSION['page_array']; }
	$config_array = array_merge(array("name" => $_REQUEST['s'], "name_prefix" => "", "default_name" => "default", "dir_path" => "pages/", "extension" => "inc", "default_404" => "File Not Found", "set_404_file" => "no", "include" => "yes", "nav_image_path" => "./img/nav/", "nav_image_prefix" => "nav_", "nav_image_extension" => "gif", "report_type" => "css", "report_title" => "Page Error"),$_config_array);
	
	$pg_path = "./pages/";
	$pg_pre = "pg_";
	$pg_suf = "php";
	$pg_default = "default";
	$img_path = "./img/nav/";
	$img_pre = "nav_";
	$img_suf = "gif";
		
	switch ($_by) {
	case "image":
		$img_dir = opendir($config_array['nav_image_path']);
		while ( false !== ($img_read = readdir($img_dir)) ) 
		{
			$nav_name = substr(substr($img_read, 7), 0, -4);
			$nav_order = substr($img_read, 4, 2);
			#$page = $pg_path . $pg_pre . $nav_order . "_" . $nav_name . "." . $pg_suf;
			$page = $pg_path . $pg_pre . $nav_name . "." . $pg_suf;
			if ( is_readable($img_path . $img_read) && ereg("^" . $config_array['nav_image_prefix'] . "[0-9]{1,2}_[a-z]*\." . $config_array['nav_image_extension'] . "$", $img_read)  ) {
				if ( is_readable($page) && $_REQUEST['s']!=$nav_name ) { $nav_item = "<a href=\"./" . $nav_name . "/\" class=\"navi\"><img src=\"" . $config_array['nav_image_path'] . $config_array['nav_image_prefix'] . $nav_order . "_" . $nav_name . "." . $config_array['nav_image_prefix'] . "\" width=\"84\" height=\"12\" border=\"0\" alt=\"./" . $nav_name . "\" /></a><br /><br />"; } 
				else { $nav_item = "<span class=\"navi\"><img src=\"" . $config_array['nav_image_path'] . $config_array['nav_image_prefix'] . $nav_order . "_" . $nav_name . "_off." . $config_array['nav_image_prefix'] . "\" width=\"84\" height=\"12\" border=\"0\" alt=\"./" . $nav_name . "\" /></span><br /><br />"; }
				echo $nav_item;
			}
		}
	   break;
	default:
		$pg_dir = opendir($pg_path);
		while ( false !== ($pg_read = readdir($pg_dir)) ) 
		{
			$nav_name = substr(substr($pg_read, 6), 0, -4);
			$nav_order = substr($pg_read, 3, 2);
			if ( is_readable($pg_path . $pg_read) && ereg("^" . $pg_pre . "[0-9]{1,2}_[a-z]*\." . $pg_suf . "$", $pg_read)  ) {
				if ( is_readable($img_path . $img_pre . $nav_order . "_" . $nav_name . "." . $img_suf) ) { $nav_item = "<a href=\"./" . $nav_name . "\" class=\"navi\"><img src=\"" . $img_path . $img_pre . $nav_order . "_" . $nav_name . "." . $img_suf . "\" width=\"84\" height=\"12\" border=\"0\" alt=\"./" . $nav_name . "\" /></a><br /><br />"; }
				else { $nav_item = "<a href=\"./" . $nav_name . "\" class=\"sub\">./" . $nav_name . "</a><br /><br />"; }
				echo $nav_item;
			}
		}
		break;
	}
}


function page_section($ROWS,$PERPAGE="") /* generate page sectional navigation $_SERVER["REQUEST_URI"] */
{
	if ( is_numeric($PERPAGE) || $_GET["p"]=="0" )
	{
		list($val,$dummy) = explode( ".", $ROWS / $PERPAGE );
   		$total_pages = $dummy?$val+1:$val;
		if (  $_GET["p"]=="" || $_GET["p"]=="1" ) { $_GET["p"] = 1; $page_nav = "<span class=\"ppagenav\" style=\"color:#E0B2B2;\">1</span>"; } else { $page_nav = "<a href=\"?s=" . $_GET["s"] . "\" class=\"ppagenav\">1</a>"; }
		$bwd = $_GET["p"] - 1;
		$fwd = $_GET["p"] + 1;
		for ( $cr = 2; $cr <= $total_pages; $cr += 1)
		{
			if ( $_GET["p"]==$cr ) { $page_nav.= "<span class=\"ppagenav\">&nbsp;|&nbsp;</span><span class=\"ppagenav\" style=\"color:#E0B2B2;\">" . $cr . "</span>"; }
			else { $page_nav.= "<span class=\"ppagenav\">&nbsp;|&nbsp;</span><a href=\"?s=" . $_GET["s"] . "&p=" . $cr . "\" class=\"ppagenav\">" . $cr . "</a>"; }
		}
		echo "<div align=\"right\">";
  		if	( $_GET["p"] < 2 ) { echo "<img src=\"./img/arrow-left-off.gif\" width=\"16\" height=\"16\" alt=\"" . $bwd  . "\" border=\"0\" />"; } else { echo "<a href=\"?s=" . $_GET["s"] . "&p=" . $bwd . "\"><img src=\"./img/arrow-left-on.gif\" width=\"16\" height=\"16\" alt=\"" . $bwd  . "\" border=\"0\" /></a>"; }
 		echo $page_nav;
 		if	( $total_pages < 2 || $total_pages == $_GET["p"] ) { echo "<img src=\"./img/arrow-right-off.gif\" width=\"16\" height=\"16\" alt=\"" . $fwd . "\" border=\"0\" />"; } else { echo "<a href=\"?s=" . $_GET["s"] . "&p=" . $fwd . "\"><img src=\"./img/arrow-right-on.gif\" width=\"16\" height=\"16\" alt=\"" . $bwd  . "\" border=\"0\" /></a>"; }
		echo "</div>";
	}
}

function bubble($text,$width="100%") /* ie: bubble("<a href="./gallery/" . $gallery_read . "/">" . ucwords(str_replace("_", " ",  $gallery_read)) . "</a> -- " . $date . "<br /><hr /><div class="caption">" . $gallery_meta . "<br /></div>") */
{	
	echo	"<br /><div style=\"width: " . $width . ";\">
  				<b class=\"box\">
  				<b class=\"box1\"><b></b></b>
				<b class=\"box2\"><b></b></b>
  				<b class=\"box3\"></b>
  				<b class=\"box4\"></b>
  				<b class=\"box5\"></b></b>
				<div class=\"boxfg\" align=\"left\"><span class=\"bubble\">" . $text . "</span></div>
				<b class=\"box\">
				<b class=\"box5\"></b>
				<b class=\"box4\"></b>
				<b class=\"box3\"></b>
				<b class=\"box2\"><b></b></b>
				<b class=\"box1\"><b></b></b></b>
			</div><br />";
}
			
function error($text, $mode="echo") /* basically just print error text, mode == echo, debug, log, exit */
{
	echo	"<br /><div style=\"clear: both;\">
  				<b class=\"box\">
  				<b class=\"box1\"><b></b></b>
				<b class=\"box2\"><b></b></b>
  				<b class=\"box3\"></b>
  				<b class=\"box4\"></b>
  				<b class=\"box5\"></b></b>
				<div class=\"boxfg\" align=\"center\"><br /><br /><br /><span class=\"error\">" . $text . "</span><br /><br /><br /></div>
				<b class=\"box\">
				<b class=\"box5\"></b>
				<b class=\"box4\"></b>
				<b class=\"box3\"></b>
				<b class=\"box2\"><b></b></b>
				<b class=\"box1\"><b></b></b></b>
			</div><br /><br />";
}

function format_text($_content,$_type="0") /* format content for record in mysql */
{
	$html = array('<i><blockquote>', '</blockquote></i>', '<b>', '</b>', '<i>', '</i>', '<u>', '</u>', '<s>', '</s>', '<pre>', '</pre>', '<center>', '</center>', '<img border="0" src="', '" height="', '" width="', '" align="', '<a target="new" href="', '</a>', '">', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '<blockquote>', '</blockquote>');
	$code = array('[quote]', '[/quote]', '[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]', '[s]', '[/s]', '[pre]', '[/pre]', '[c]', '[/c]', '[img=', 'h=', 'w=', 'a=', '[url=', '[/url]', '/]', '[tab]', '[block]', '[/block]');
	$brs  = array('<br>','<br />', '<br/>');
	if ( $_type == "0" ) {
		#$smartquotes = str_replace('“', '"', $CONTENT); $smartquotes2 = str_replace('”', '"', $smartquotes); $smartquotes3 = str_replace('’', "'", $smartquotes2);
		$special_content = htmlspecialchars($_content, ENT_QUOTES);
		$newlines = str_replace("\n", "<br />",  $special_content);
		#$trim = eregi_replace('<br[[:space:]]*/?[[:space:]]*>?',"", $newlines);
		$export_content = str_replace($code, $html, $newlines);
		
	} else {
		$newlines = eregi_replace('<br[[:space:]]*/?[[:space:]]*>', "\n", $CONTENT);
		$export_content = str_replace($html, $code, $newlines);
	}
	return $export_content;
}

function protected_email($email) /* protect email syntax from spam */
{
	$email_1 = str_replace("@", " at ",  $email);
	$email_2 = str_replace(".", " dot ",  $email_1);
	return $email_2;
}

function send_mail($from,$to,$subject,$tag,$message,$monitor="working@mercenarylabs.com") /* create and send email to a list of addresses, auto mime-encode for non-aol addresses */
{
	// Add From: header
	$headers = "From: " . $from . "\r\n";
	//
	//specify MIME version 1.0
	$headers .= "MIME-Version: 1.0\r\n";
	//
	//unique boundary
	$boundary = uniqid("HTML");
	//
	//tell e-mail client this e-mail contains//alternate versions
	$headers .= "Content-Type: multipart/alternative" .
   				"; boundary = " . $boundary . "\r\n\r\n";
	//
	//message to people with clients who don't understand MIME
	$headers .= "This is a MIME encoded message.\r\n\r\n";
	//
	//plain text version of message
	$headers .= "--$boundary\r\n" .
   				"Content-Type: text/plain; charset=ISO-8859-1\r\n" .
	$headers .= chunk_split(base64_encode($message));
	//
	//HTML version of message
	$headers .= "--$boundary\r\n" .
   				"Content-Type: text/html; charset=ISO-8859-1\r\n" .
   				"Content-Transfer-Encoding: base64\r\n\r\n";
	$headers .= chunk_split(base64_encode($message));
if ( ereg("aol.com$", $_POST["email"]) ) {
	mail($_POST["email"], $subject, $plain, $headers);
	mail($AFFILIATE_MAILTO, $subject, $plain, $headers);
	mail("worker@mercenarylabs.com", $subject . " " . $_SERVER["REMOTE_ADDR"], $plain, $headers);
} else {
	mail($_POST["email"], $subject, "", $headers);
	mail($AFFILIATE_MAILTO, $subject, "", $headers);
	mail("worker@mercenarylabs.com", $subject . " " . $_SERVER["REMOTE_ADDR"], "", $headers); 
	}
}

function send_simple_email($from, $to, $subject, $message, $cc="", $bcc=""){
	$headers = "From: ".$from."\r\n";
	$headers .= "Reply-To: ".$from."\r\n";
	$headers .= "Return-Path: ".$from."\r\n";
	$headers .= "CC: ".$cc."\r\n";
	$headers .= "BCC: ".$to."\r\n";
	if ( mail($to,$subject,$message,$headers) ) { return "true"; } 
}


function is_base64($encodedString)
{
    $length = strlen($encodedString);
   
    // Check every character.
    for ($i = 0; $i < $length; ++$i) {
      $c = $encodedString[$i];
      if (
        ($c < '0' || $c > '9')
        && ($c < 'a' || $c > 'z')
        && ($c < 'A' || $c > 'Z')
        && ($c != '+')
        && ($c != '/')
        && ($c != '=')
      ) {
        // Bad character found.
        return false;
      }
    }
    // Only good characters found.
    return true;
}


#
## DIAGNOSTIC Funtions ##
function print_array($_array)  /* Gives you a visual on a given array, helped out with debuging some of the more complex scripts like quest members */
{
	$id = current( $_array );
	$key = key( $_array );
	if ($id)
	{
		echo $id . ":" . $key . "<br />";
		
		while ($id = next($_array))
		{
			$key = key( $_array );
			echo $id . ":" . $key . "<br />";
		}
	}
	else { echo "no records found."; }
}


?>