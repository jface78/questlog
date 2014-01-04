<?php
require("/usr/home/www/sites/questlog/inc/control.php");
if ( $db = databaseConnection($DATABASE) ) 
{
  // setup empty vars //
  $email = "";
  $from = "";
  $subject = "";
  $headers = "";
  $message = "";
  $splittingheaders = true;
  
  // read from stdin //
  $fd = fopen("php://stdin", "r");
  
  while ( !feof($fd) ) { $email .= fread($fd, 1024); }
  fclose($fd);
  
  // handle email //
  $lines = explode("\n", $email);
    
  for ($i=0; $i<count($lines); $i++)
  {
    if ($splittingheaders) {
      $headers .= $lines[$i]."\n";
      if (preg_match("/^Subject: (.*)/", $lines[$i], $matches)) { $subject = $matches[1]; }
      if (preg_match("/^From: (.*)/", $lines[$i], $matches)) { $from = $matches[1]; }
      if (preg_match("/^Content-Transfer-Encoding: (.*)/", $lines[$i], $matches)) { $encoding = $matches[1]; }
    } 
    else { $message .= $lines[$i]."\n"; }
    if (trim($lines[$i])=="") { $splittingheaders = false; }
  } 
  
  // filter the email data //
  if ( $encoding == "base64" ) { $message = base64_decode($message); }
  if ( preg_match('/[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*/', $from, $match) ) { $email = $match[0]; }
  $cleaned_sender = preg_replace("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+).*$\n/m", "", $message);
  #$cleaned_sender = preg_replace("/^On*[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+).*wrote\:$\n/m", "", $cleaned_sender);
  $cleaned_quotes = preg_replace("/^>.*$\n/m", "", $cleaned_sender);
  $code = str_ireplace(" ", "", str_ireplace("Re:", "", $subject));
  
  // run db checks, submit post, generate a new reply post record and send //
  $email_sql = "SELECT uid FROM user_profiles WHERE user_email='" . $email . "' LIMIT 1";
  $email_uid = mysql_fetch_array(mysql_query($email_sql));
  if ( $email_uid['0'] > 0 )
  {
    $quest_sql = "SELECT r.qid,r.uid,u.login_name,q.quest_name FROM reply_codes r,users u,quests q WHERE r.code='" . $code . "' AND r.uid=u.uid AND r.qid=q.qid";
    $quest = mysql_fetch_array($quest_query = mysql_query($quest_sql));
    if ( $quest['uid'] == $email_uid['0'] ) // if ( $count = count($quest) > 0 )  //
    {
      $char_sql = "SELECT m.cid,c.char_name FROM quest_members m,characters c WHERE m.qid='" . $quest['qid'] . "' AND m.cid=c.cid AND c.uid='" . $quest['uid'] . "'";
      $char_query = mysql_query($char_sql);
      if ( $count = mysql_num_rows($char_query) > 0 )
      {
        $char = mysql_fetch_array($char_query);
        $char_id = $char['cid'];
        $poster_name = $char['char_name'];
        $poster_tag = "character";
      } else {
        $char_id = "0"; // quest owner has no char id //
        $poster_name = $quest['login_name'];
        $poster_tag = "owner";
      }
      
      #if ( $LOG_IP=="ON" ) { $ip = $_SERVER["REMOTE_ADDR"]; } else { $ip = "0.0.0.0"; }
      $ip = "1.2.3.4"; // pulling this from mail headers looks hard, possibly just log the entire header to a separate table //
      $d20_post = d20_roll($cleaned_quotes);
      $trm_post = rtrim($d20_post['post']);
      $log_post = formatContent($trm_post);
      $datetime = getCurrentDate();
      
      // post to the log //
      $post_sql = "INSERT INTO posts(qid,uid,cid,post_status,post_text,post_date,post_ip) VALUES('" . $quest['qid'] . "','" . $quest['uid'] . "','" . $char_id . "','0','" . $log_post . "','" . $datetime . "','" . $ip . "')";
      $post_insert = mysql_query($post_sql) or die("An error occured while posting to your quest.[2]");
      $post_id = mysql_insert_id();
      
      if ( is_numeric($d20_post['roll']) ) { $roll_insert = mysql_query("INSERT INTO rolls(rid,roll) VALUES('" . $post_id . "','" . $d20_post['roll'] . "')") or die("An error occured while recording to your roll.[" . $post_id . "','" . $d20_post['roll'] . "]"); }
      
      // remove old reply code and make send out a new one //
      mysql_query("DELETE LOW_PRIORITY FROM reply_codes WHERE code='" . $code. "'") or die("An error occured while removing the reply code.[4]");
      reply_post_create($quest['uid'], $post_id, $quest['qid'], $trm_post, $d20_post['roll']);
      
      if ( $DEBUG_NOTICES=="YES" ) { send_simple_email($POSTS_EMAIL, $ADMIN_EMAIL, "reply-post test email", "New Post to " . $quest['quest_name'] . " by " . $poster_name . " (" . $poster_tag . ")\n\nencoding: " . $encoding . "\nip: " . $ip . "\ncode: " . $code . "\ndate: " . $datetime . "\nuser id: " . $quest['uid'] . "\nchar id: " . $char_id . "\n\npost text:\n" . $trimmed_message . "\n\nquest sql:\n" . $quest_sql . "\n\ncharacter sql:\n" . $char_sql . "\n\nemail sql:\n" . $email_sql . "\n\npost sql:\n" . $post_sql . "\n\n\nmail headers:\n" . $headers); }
    } else {
      if ( $DEBUG_NOTICES=="YES" ) { send_simple_email($POSTS_EMAIL, $ADMIN_EMAIL, "reply-post debugger", "Post code or user email mismatch: " . $code . " / " . $quest['uid'] . " / " . $email_uid['0'] . " / " . $email . "\n\n" . $quest_sql); }
      send_simple_email($POSTS_EMAIL, $email, $code, "Your post was not accepted, either your reply code is no longer valid or your email address does not match your user id.");
    }
  } else {
    if ( $DEBUG_NOTICES=="YES" ) { send_simple_email($POSTS_EMAIL, $ADMIN_EMAIL, "reply-post debugger", $email_uid['0'] . "\n\n" . $trimmed_message . "\n\n" . $email_sql); }
    send_simple_email($POSTS_EMAIL, $email, $code, "Your post was not accepted because your email address could not be verified.");
  }
} else {
    if ( $DEBUG_NOTICES=="YES" ) { send_simple_email($POSTS_EMAIL, $ADMIN_EMAIL, "reply-post debugger", "Reply-Post script failed to connect with the database."); }
    send_simple_email($POSTS_EMAIL, $email, $code, "Your post was not accepted because the script has failed to connect to the database.");
}
?>