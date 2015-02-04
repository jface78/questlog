<?php
session_start();
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
include('../../../../questlog_credentials.php');

// session stuff should go here later

if (empty($_GET['questID']) || !is_numeric($_GET['questID'])) {
  http_response_code(400);
  exit();
}

if (empty($_GET['order'])) {
  $postOrder = 'DESC';
} else if (strtoupper($_GET['order']) != 'ASC' && strtoupper($_GET['order']) != 'DESC') {
  http_response_code(400);
  exit();
} else {
  $postOrder = strtoupper($_GET['order']);
}

try {
  $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
  $json_array = [];
  $query = 'SELECT qid,quest_name FROM quests WHERE qid = :questID';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':questID' => $_GET['questID']));
  $row = $sth -> fetch();

  if (!$row) {
    http_response_code(404);
    $dbh = null;
    exit();
  }

  $json_array['title'] = $row['quest_name'];
  $json_array['questID'] = $row['qid'];

  if (empty($_GET['limit'])) {
    $json_array['pageCount'] = 1;
  } else {
    $query = 'SELECT COUNT(pid) FROM posts WHERE qid=:questID';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':questID' => $_GET['questID']));
    $total = $sth -> fetch();
    $json_array['pageCount'] = ceil($total['COUNT(pid)']/$_GET['limit']);
  }
  $query = 'SELECT pid,uid,cid,timestamp,post_text FROM posts WHERE qid=:questID ORDER BY timestamp ' . $postOrder;
  if (!empty($_GET['limit']) && is_numeric($_GET['limit'])) {
    $query .= ' LIMIT ' . $_GET['limit'];
  }
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':questID' => $_GET['questID']));
  $results = $sth -> fetchAll();

  $index = 0;
  foreach($results as $row) {
    $json_array['posts'][$index] = array();
    $json_array['posts'][$index]['id'] = $row['pid'];
    $json_array['posts'][$index]['timestamp'] = strtotime($row['timestamp']);
    $json_array['posts'][$index]['text'] = $row['post_text'];
    $json_array['posts'][$index]['uid'] = $row['uid'];
    $json_array['posts'][$index]['cid'] = $row['cid'];
    if ($row['cid'] == '0') {
      $json_array['posts'][$index]['cid'] = 'GM';
      $query = 'SELECT login_name FROM users WHERE uid = :userID';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':userID' => $row['uid']));
      $user = $sth -> fetch();
      $json_array['posts'][$index]['postedBy'] = $user['login_name'];
    } else {
      $query = 'SELECT char_name FROM characters WHERE cid = :characterID';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':characterID' => $row['cid']));
      $character = $sth -> fetch();
      $json_array['posts'][$index]['postedBy'] = $character['char_name'];
    }
    $index++;
  }
  $dbh = null;
  header('Content-Type: application/json');
  echo json_encode($json_array);
  http_response_code(200);
} catch(PDOException $error) {
  echo $error->getMessage();
  http_response_code(500);
}
?>
