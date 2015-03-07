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

try {
  $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
  switch ($_SERVER['REQUEST_METHOD']) {
    case 'DELETE':
      if (!empty($_GET['postID']) && !is_numeric($_GET['postID'])) {
        http_response_code(400);
        exit();
      }
      $query = 'SELECT uid FROM posts WHERE pid= :postID';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':postID' => $_GET['postID']));
      $user = $sth -> fetch();
      if ($_SESSION['uid'] != $user['uid']) {
        http_response_code(401);
        $dbh = null;
        exit();
      } else {
        $query = 'DELETE FROM posts WHERE pid=:postID';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':postID' => $_GET['postID']));
        http_response_code(200);
      }
      break;
  }
  $dbh = null;
} catch(PDOException $error) {
  echo $error->getMessage();
  http_response_code(500);
}
?>