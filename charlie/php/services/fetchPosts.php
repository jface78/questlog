<?php
session_start();
error_reporting(E_ALL);
include('../../../../questlog_credentials.php');

// session stuff should go here later

// No parameter was passed.
if (empty($_GET['questID']) && empty($_GET['postID']) && empty($_GET['characterID']) && empty($_GET['userID'])) {
  http_response_code(400);
  exit();
}
// More than one conflicting parameter was passed.
if (!empty($_GET['questID']) && !empty($_GET['postID'])) {
  http_response_code(400);
  exit();
}
if (!empty($_GET['postID']) && (!empty($_GET['characterID']) || !empty($_GET['userID']))) {
  http_response_code(400);
  exit();
}
if (!empty($_GET['characterID']) && !empty($_GET['userID'])) {
  http_response_code(400);
  exit();
}

// Required param was passed, but it's not numeric.
if (!empty($_GET['questID']) && !is_numeric($_GET['questID'])) {
  http_response_code(400);
  exit();
}
if (!empty($_GET['postID']) && !is_numeric($_GET['postID']) ) {
  http_response_code(400);
  exit();
}
if (!empty($_GET['characterID']) && !is_numeric($_GET['characterID']) ) {
  http_response_code(400);
  exit();
}
if (!empty($_GET['userID']) && !is_numeric($_GET['userID']) ) {
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

  if (!empty($_GET['postID'])) {
    $query = 'SELECT uid,cid,qid,pid,post_text,post_date,timestamp FROM posts WHERE pid = :postID';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':postID' => $_GET['postID']));
    $results = $sth -> fetchAll();
    if(!count($results)) {
      http_response_code(404);
      exit();
    }

    // get this post's quest info.
    $query = 'SELECT quest_name FROM quests WHERE qid = :questID';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':questID' => $results[0]['qid']));
    $quest = $sth -> fetch();
    $results[0]['quest_name'] = $quest['quest_name'];

    // get this post's user info.
    $query = 'SELECT login_name FROM users WHERE uid = :userID';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':userID' => $results[0]['uid']));
    $user = $sth -> fetch();
    $results[0]['login_name'] = $user['login_name'];

    // get this post's character info.
    if ($results[0]['cid'] == '0') {
      $results[0]['cid'] = 'GM';
      $results[0]['char_name'] = $user['login_name'];
    } else {
      $query = 'SELECT char_name FROM characters WHERE cid = :characterID';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':characterID' => $results[0]['cid']));
      $character = $sth -> fetch();
      $results[0]['char_name'] = $character['char_name'];
    }
    $results[0]['post_date'] = strtotime($results[0]['post_date']);
    if (strtotime($results[0]['timestamp']) != strtotime($results[0]['post_date'])) {
      $results[0]['edited'] = strtotime($results[0]['timestamp']);
    } else {
      $results[0]['edited'] = 'never';
    }
  } else if (!empty($_GET['userID']) || !empty($_GET['characterID']) || !empty($_GET['questID'])) {
    if (!empty($_GET['userID'])) {
      $whereString = 'WHERE uid = :userID';
      $paramsArray = array(':userID' => $_GET['userID']);
      if (!empty($_GET['questID'])) {
        $whereString .= ' AND qid = :questID';
        $paramsArray[':questID'] = $_GET['questID'];
      }
    } else if (!empty($_GET['characterID'])) {
      $whereString = 'WHERE cid = :characterID';
      $paramsArray = array(':characterID' => $_GET['characterID']);
      if (!empty($_GET['questID'])) {
        $whereString .= ' AND qid = :questID';
        $paramsArray[':questID'] = $_GET['questID'];
      }
    } else if (!empty($_GET['questID'])) {
      $whereString = 'WHERE qid = :questID';
      $paramsArray = array(':questID' => $_GET['questID']);
    }

    $query = 'SELECT uid,cid,qid,pid,post_text,post_date,timestamp FROM posts ' . $whereString . ' ORDER BY timestamp ' . $postOrder;
    if (!empty($_GET['limit'])) {
      $query .= ' LIMIT ' . $_GET['limit'];
    }
    $sth = $dbh -> prepare($query);
    $sth -> execute($paramsArray);
    $results = $sth -> fetchAll();
    if(!count($results)) {
      http_response_code(404);
      exit();
    }
    $index = 0;
    foreach ($results as $row) {
      // get this post's quest info.
      $query = 'SELECT quest_name FROM quests WHERE qid = :questID';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':questID' => $row['qid']));
      $quest = $sth -> fetch();
      $results[$index]['quest_name'] = $quest['quest_name'];
      
      // get this post's user info.
      $query = 'SELECT login_name FROM users WHERE uid = :userID';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':userID' => $row['uid']));
      $user = $sth -> fetch();
      $results[$index]['login_name'] = $user['login_name'];

      // get this post's character info.
      if ($row['cid'] == '0') {
        $results[$index]['cid'] = 'GM';
        $results[$index]['char_name'] = $user['login_name'];
      } else {
        $query = 'SELECT char_name FROM characters WHERE cid = :characterID';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':characterID' => $row['cid']));
        $character = $sth -> fetch();
        $results[$index]['char_name'] = $character['char_name'];
      }
      $results[$index]['post_date'] = strtotime($row['post_date']);
      if (strtotime($row['post_date']) != strtotime($row['post_date'])) {
        $results[$index]['edited'] = strtotime($row['timestamp']);
      } else {
        $results[$index]['edited'] = 'never';
      }
      $index++;
    }
  }
  $index = 0;
  $json_array = [];
  $json_array['posts'] = [];
  foreach ($results as $row) {
    $json_array['posts'][$index] = [];
    $json_array['posts'][$index]['postID'] = $row['pid']; 
    $json_array['posts'][$index]['questID'] = $row['qid'];
    $json_array['posts'][$index]['quest'] = $row['quest_name'];
    $json_array['posts'][$index]['userID'] = $row['uid'];
    $json_array['posts'][$index]['user'] = $row['login_name'];
    $json_array['posts'][$index]['characterID'] = $row['cid'];
    $json_array['posts'][$index]['character'] = $row['char_name'];
    $json_array['posts'][$index]['date'] = $row['post_date'];
    $json_array['posts'][$index]['edited'] = $row['edited'];
    $json_array['posts'][$index]['text'] = $row['post_text'];
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