<?php
session_start();
date_default_timezone_set('UTC');
include('../../../../questlog_credentials.php');
include '../utils.php';

function getSortableTitle($title) {
  if (strpos(strtolower($title), 'an ') === 0) {
    return substr($title, 3, strlen($title)-1) . ', An';
  } else if (strpos(strtolower($title), 'a ') === 0) {
    return substr($title, 2, strlen($title)-1) . ', A';
  } else if (strpos(strtolower($title), 'the ') === 0) {
    return substr($title, 4, strlen($title)-1) . ', The';
  } else {
    return $title;
  }
}

function generateQuestListings($results, $json_array, $type) {
  try {
    $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
    $index = 0;
    if (!array_key_exists($type, $json_array)) {
      $json_array['quests'][$type] = [];
    }
    foreach ($results as $row) {
      $json_array['quests'][$type][$index]['title'] = $row['quest_name'];
      $json_array['quests'][$type][$index]['sortable'] = getSortableTitle($row['quest_name']);
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
      $query = 'SELECT login_name FROM users WHERE uid=:uid';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':uid' => $row['uid']));
      $gmName = $sth -> fetch();
      $json_array['quests'][$type][$index]['gm'] = $gmName['login_name'];
      $query = 'SELECT timestamp FROM posts WHERE qid=:qid ORDER BY timestamp DESC LIMIT 1';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':qid' => $row['qid']));
      $lastPostDate = $sth -> fetch();
      $json_array['quests'][$type][$index]['lastPostDate'] = time($lastPostDate['timestamp']);
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

// Check that the session is active.
if (!checkSession()) {
  http_response_code(401);
  exit();
}

try {
  $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
  
  // Get the user's GM quest.
  $query = 'SELECT qid,uid,quest_name,timestamp FROM quests WHERE uid=:uid';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':uid' => $_SESSION['uid']));
  $questsArr = $sth -> fetchAll();
  $gm_quests = '0';
  foreach ($questsArr as $quest) {
    $gm_quests .= ',' . $quest['qid'];
  }
  $json_array = [];
  $json_array['quests'] = [];
  $json_array = generateQuestListings($questsArr, $json_array, 'gmQuests');

  // Get the user's player quests.
  $query = 'SELECT cid FROM characters WHERE uid=:uid';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':uid' => $_SESSION['uid']));
  $results = $sth -> fetchAll();
  $questsArr = [];
  $index=0;
  $player_chars = '0';
  foreach ($results as $row) {
    $query = 'SELECT qid FROM quest_members WHERE cid=:cid';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':cid' => $row['cid']));
    $player_chars .= ',' . $row['cid'];
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
  $query = 'SELECT qid FROM quest_members WHERE qid NOT IN (' . $gm_quests .') AND cid NOT IN (' . $player_chars . ')';
  $sth = $dbh -> prepare($query);
  $sth -> execute();
  $non_quests = $sth -> fetchAll();
  $questsArr = [];
  $index = 0;
  foreach ($non_quests as $quest) {
    $query = 'SELECT qid,uid,quest_name,timestamp FROM quests WHERE qid=:qid AND quest_status != :disabled';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':qid' => $quest['qid'], ':disabled' => 3));
    $results = $sth -> fetch(PDO::FETCH_ASSOC);
    if ($results['quest_name'] != null) {
      $questsArr[$index] = $results;
    }
    $index++;
  }
  usort($questsArr, function($a, $b) {
    return $a['quest_name'] > $b['quest_name'];
  });
  $json_array = generateQuestListings($questsArr, $json_array, 'otherQuests');
  $dbh = null;
  $_SESSION['last_activity'] = time();
  http_response_code(200);
  header('Content-Type: application/json');
  echo json_encode($json_array);
} catch(PDOException $error) {
  echo $error->getMessage();
  http_response_code(500);
}