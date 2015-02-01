<?php
session_start();
date_default_timezone_set('UTC');
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

if (empty($_SESSION['uid'])) {
  http_response_code(401);
  exit();
}
try {
  $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
  
  // Get the user's GM quest.
  $query = 'SELECT qid,uid,quest_name,timestamp FROM quests WHERE uid=:uid';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':uid' => $_SESSION['uid']));
  $results = $sth -> fetchAll();
  $json_array = [];
  $json_array['quests'] = [];
  $json_array = generateQuestListings($results, $json_array, 'gmQuests');

  // Get the user's player quests.
  $query = 'SELECT cid FROM characters WHERE uid=:uid';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':uid' => $_SESSION['uid']));
  $results = $sth -> fetchAll();
  $questsArr = [];
  $index=0;
  foreach ($results as $row) {
    $query = 'SELECT qid FROM quest_members WHERE cid=:cid';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':cid' => $row['cid']));
    $player_quests = $sth -> fetchAll();
    foreach ($player_quests as $quest) {
      $query = 'SELECT qid,uid,quest_name,timestamp FROM quests WHERE qid=:qid';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':qid' => $quest['qid']));
      $questsArr[$index] = $sth -> fetch();
      $index++;
     }
  }
  $json_array = generateQuestListings($questsArr, $json_array, 'playerQuests');
  
  // Get all remaining  non-player, non-GM quests.
  $query = 'SELECT q.qid,q.uid,q.quest_name,u.login_name,COUNT(p.pid) AS totalposts FROM quests q,users u,posts p WHERE q.quest_status<4 AND q.uid=u.uid AND q.qid=p.qid';
  
  $refineQuery = 'SELECT m.qid FROM quest_members m,characters c,users u WHERE u.uid=:uid AND u.uid=c.uid AND c.cid=m.cid';
  $sth = $dbh -> prepare($refineQuery);
  $sth -> execute(array(':uid' => $_SESSION['uid']));
  $player_quests = $sth -> fetchAll();
  $player_qids = '0';
  $index = 0;
  foreach ($player_quests as $quest) {
    $player_qids .= ",'" . $player_quests[$index]['qid'] . "'";
    $index++;
  }
  $query .= " AND q.uid NOT IN ('" . $_SESSION["uid"] . "') AND q.qid NOT IN (" . $player_qids . ")";
  $sth = $dbh -> prepare($query);
  $sth -> execute();
  $player_quests = $sth -> fetchAll();
  echo $query;
  print_r($player_quests);

/*
  $query = 'SELECT qid,uid,quest_name,timestamp FROM quests WHERE uid!=:uid';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':uid' => $_SESSION['uid']));
  $results = $sth -> fetchAll();
  $query = 'SELECT cid FROM characters WHERE uid=:uid';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':uid' => $_SESSION['uid']));
  $characters = $sth -> fetchAll();
  $index = 0;
  $playerCharacters = [];
  foreach($characters as $char) {
    $query = 'SELECT qid FROM quest_members WHERE cid=:cid';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':cid' => $char['cid']));
    $playerCharacters[$index] = $sth -> fetchAll();
    $index++;
  }
  $other_quests = [];
  foreach($playerCharacters as $pc) {
    foreach($results as $row) {
      if ($pc['qid'] != $row['qid']) {
        echo $row['quest_name'] . '<br>';
      }
    }
  }
*/

  //$json_array = generateQuestListings($results, $json_array, 'gmQuests');
  
  $dbh = null;
  header('Content-Type: application/json');
  //echo json_encode($json_array);
} catch(PDOException $error) {
  echo $error->getMessage();
  http_response_code(500);
}