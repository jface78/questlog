<?php
session_start();
error_reporting(E_ALL);
include('../../../../questlog_credentials.php');
include '../utils.php';

// Check that the session is active.
if (!checkSession()) {
  http_response_code(401);
  exit();
}

if (!empty($_GET['userID']) && !is_numeric($_GET['userID'])) {
  http_response_code(400);
  exit();
}

try {
  $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
  
  if (!empty($_GET['questID']) && is_numeric($_GET['questID'])) {
    $query = 'SELECT uid FROM quests WHERE qid= :questID';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':questID' => $_GET['questID']));
    $users = $sth -> fetchAll();

    $query = 'SELECT cid FROM quest_members WHERE qid= :questID';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':questID' => $_GET['questID']));
    $characters = $sth -> fetchAll();
    $results = [];
    foreach($characters as $row) {
      $query = 'SELECT uid FROM characters WHERE cid= :characterID';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':characterID' => $row['cid']));
      array_push($users, $sth -> fetchAll()[0]);
      
      foreach($users as $user) {
        $query = 'SELECT uid,login_name,user_status,timestamp FROM users WHERE uid=:userID';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':userID' => $user['uid']));
        array_push($results, $sth -> fetchAll()[0]);
      }
    }
  } else if (!empty($_GET['characterID']) && is_numeric($_GET['characterID']) ) {
    $query = 'SELECT uid FROM characters WHERE cid= :characterID';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':characterID' => $_GET['characterID']));
    $users = $sth -> fetchAll();
    foreach($users as $user) {
      $query = 'SELECT uid,login_name,user_status,timestamp FROM users WHERE uid=:userID';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':userID' => $user['uid']));
      $results = $sth -> fetchAll();
    }
  } else {
    $query = 'SELECT uid,login_name,user_status,timestamp FROM users';
    if (!empty($_GET['userID'])) {
      $query .= ' WHERE uid = :userID';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':userID' => $_GET['userID']));
    } else {
      $sth = $dbh -> prepare($query);
      $sth -> execute();
    }
    $results = $sth -> fetchAll();
  }
  if(!count($results)) {
    http_response_code(404);
    exit();
  }
  $index = 0;
  $json_array = [];
  $json_array['users'] = [];
  foreach($results as $row) {
    $json_array['users'][$index]['userID'] = $row['uid'];
    $json_array['users'][$index]['userName'] = $row['login_name'];
    $json_array['users'][$index]['status'] = $row['user_status'];
    $json_array['users'][$index]['created'] = strtotime($row['timestamp']);
    $index++;
  }
  $dbh = null;
  $_SESSION['last_activity'] = time();
  header('Content-Type: application/json');
  echo json_encode($json_array);
  http_response_code(200);
} catch(PDOException $error) {
  echo $error->getMessage();
  http_response_code(500);
}
?>