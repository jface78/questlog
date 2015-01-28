<?php
session_start();
error_reporting(E_ALL);
include('../../../../questlog_credentials.php');

// session stuff should go here later

if (!empty($_GET['userID']) && !is_numeric($_GET['userID'])) {
  http_response_code(400);
  exit();
}

try {
  $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
  $json_array = [];
  $query = 'SELECT uid,login_name,user_status,timestamp FROM users';
  $sth = $dbh -> prepare($query);
  $sth -> execute();
  $results = $sth -> fetchAll();
  $index = 0;
   $json_array['users'] = [];
  foreach($results as $row) {
    $json_array['users'][$index]['userID'] = $row['uid'];
    $json_array['users'][$index]['userName'] = $row['login_name'];
    $json_array['users'][$index]['status'] = $row['user_status'];
    $json_array['users'][$index]['created'] = strtotime($row['timestamp']);
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