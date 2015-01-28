<?php
session_start();
error_reporting(E_ALL);
include('../../../../questlog_credentials.php');

// session stuff should go here later

if (empty($_GET['postID']) || !is_numeric($_GET['postID'])) {
  http_response_code(400);
  exit();
}

try {
  $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
  $json_array = [];
  $query = 'SELECT uid,cid,qid,pid,post_text,post_date,timestamp FROM posts WHERE pid = :postID';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':postID' => $_GET['postID']));
  $row = $sth -> fetch();
  $json_array['postID'] = $row['pid'];
  
  // show this post's quest info.
  $json_array['questID'] = $row['qid'];
  $query = 'SELECT quest_name FROM quests WHERE qid = :questID';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':questID' => $row['qid']));
  $quest = $sth -> fetch();
  $json_array['quest'] = $quest['quest_name'];

  // show this post's user info.
  $json_array['userID'] = $row['uid'];
  $query = 'SELECT login_name FROM users WHERE uid = :userID';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':userID' => $row['uid']));
  $user = $sth -> fetch();
  $json_array['user'] = $user['login_name'];

  // show this post's character info.
  $json_array['characterID'] = $row['cid'];
  if ($row['cid'] == '0') {
    $json_array['characterID'] = 'GM';
    $json_array['character'] = $user['login_name'];
  } else {
    $json_array['characterID'] = $row['cid'];
    $query = 'SELECT char_name FROM characters WHERE cid = :characterID';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':characterID' => $row['cid']));
    $character = $sth -> fetch();
    $json_array['character'] = $character['char_name'];
  }

  $json_array['date'] = strtotime($row['post_date']);
  if (strtotime($row['timestamp']) != strtotime($row['post_date'])) {
    $json_array['edited'] = strtotime($row['timestamp']);
  }
  $json_array['text'] = $row['post_text'];
  $dbh = null;
  header('Content-Type: application/json');
  echo json_encode($json_array);
  http_response_code(200);
} catch(PDOException $error) {
  echo $error->getMessage();
  http_response_code(500);
}