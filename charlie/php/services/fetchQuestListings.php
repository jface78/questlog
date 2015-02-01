<?php
session_start();
error_reporting(E_ALL);
include('../../../../questlog_credentials.php');

function generateQuestListings($results, $json_array, $type) {
  try {
    $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
    $index = 0;
    if (!array_key_exists($type, $json_array)) {
      $json_array['quests'][$type] = [];
    }
    foreach ($results as $row) {
      $json_array['quests'][$type][$index]['title'] = $row['quest_name'];
      $json_array['quests'][$type][$index]['questID'] = $row['qid'];
      $json_array['quests'][$type][$index]['created'] = strtotime($row['timestamp']);
      $query = 'SELECT count(pid) FROM posts WHERE qid=:qid';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':qid' => $row['qid']));
      $count = $sth -> fetch();
      $json_array['quests'][$type][$index]['count'] = $count['count(pid)'];
      $query = 'SELECT cid FROM posts WHERE qid=:qid ORDER BY post_date DESC LIMIT 1';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':qid' => $row['qid']));
      $cid = $sth -> fetch();
      if ($cid['cid'] == 0) {
        $query = 'SELECT login_name FROM users WHERE uid=:uid';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':uid' => $row['uid']));
        $uname = $sth -> fetch();
        $json_array['quests'][$type][$index]['lastPostBy'] = $uname['login_name'];
      } else {
        $query = 'SELECT char_name FROM characters WHERE cid=:cid';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':cid' => $cid['cid']));
        $cname = $sth -> fetch();
        $json_array['quests'][$type][$index]['lastPostBy'] = $cname['char_name'];
      }
      $index++;
    }
    $dbh = null;
    return $json_array;
  } catch(PDOException $error) {
    echo $error->getMessage();
    http_response_code(500);
    exit();
  }
}

try {
  $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
  $query = 'SELECT qid,uid,quest_name,timestamp FROM quests WHERE uid=:uid';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':uid' => $_SESSION['uid']));
  $results = $sth -> fetchAll();
  $json_array = [];
  $json_array['quests'] = [];
  $json_array = generateQuestListings($results, $json_array, 'gmQuests');

  $query = 'SELECT cid FROM characters WHERE uid=:uid';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':uid' => $_SESSION['uid']));
  $results = $sth -> fetchAll();
  foreach ($results as $row) {
    $query = 'SELECT qid FROM quest_members WHERE cid=:cid';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':cid' => $row['cid']));
    $player_quests = $sth -> fetchAll();
    echo $player_quests['qid']. ' <br>';
    $query = 'SELECT qid,uid,quest_name,timestamp FROM quests WHERE qid=:qid';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':qid' => $player_quests['qid']));
    $quest = $sth -> fetchAll();
    print_r($player_quests) . '<br>';
    $json_array = generateQuestListings($quest, $json_array, 'playerQuests');
  }

  $dbh = null;
  header('Content-Type: application/json');
  echo json_encode($json_array);
} catch(PDOException $error) {
  echo $error->getMessage();
  http_response_code(500);
}