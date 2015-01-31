<?php
session_start();
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
include '../utils.php';
include('../../../../questlog_credentials.php');

if (empty($_GET['request'])) {
  http_response_code(400);
  exit();
}
switch ($_GET['request']) {
  case 'checkSession':
    if (empty($_SESSION['uid'])) {
      echo 'not logged in';
      http_response_code(401);
    } else {
      echo 'logged in';
      http_response_code(200);
    }
    break;
  case 'login':
    if (empty($_POST['user']) || empty($_POST['pass'])) {
      http_response_code(400);
      exit();
    }
    try {
      $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $query = 'SELECT u.uid,u.login_name,g.group_name,l.date,l.ip FROM users u, groups g, user_logins l
          WHERE u.login_name=:name
          AND u.login_hash=:hash
          AND u.gid=g.gid
          AND u.uid=l.uid';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':name' => $_GET['user'], ':hash' => hashPasswd($_GET['user'], $_GET['pass'])));
      $row = $sth -> fetch();
      $dbh = null;

      if(!$row) {
        http_response_code(401);
        exit();
      } else {
        $_SESSION["uid"] = $row["uid"];
        $_SESSION["login"] = $row["login_name"];
        $_SESSION["group"] = $row["group_name"];
        http_response_code(200);
      }
    } catch(PDOException $error) {
      echo $error->getMessage();
      http_response_code(500);
    }
    break;
}
?>