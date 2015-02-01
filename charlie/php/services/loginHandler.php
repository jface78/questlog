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
      http_response_code(401);
    } else {
      $json_array = [];
      $json_array['user_details'] = [];
      $json_array['user_details']['name'] = $_SESSION['login'];
      $json_array['user_details']['ip'] = $_SESSION['ip'];
      $json_array['user_details']['date'] = $_SESSION['date'];
      header('Content-Type: application/json');
      echo json_encode($json_array);
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
      $query = 'SELECT u.uid,u.login_name,g.group_name FROM users u, groups g
          WHERE u.login_name=:name
          AND u.login_hash=:hash
          AND u.gid=g.gid';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':name' => $_POST['user'], ':hash' => hashPasswd($_POST['user'], $_POST['pass'])));
      $row = $sth -> fetch();

      if(!$row) {
        $dbh = null;
        http_response_code(401);
        exit();
      } else {
        $_SESSION["uid"] = $row['uid'];
        $_SESSION["login"] = $row['login_name'];
        $_SESSION["group"] = $row['group_name'];

        $query = 'SELECT ip, date FROM user_logins WHERE uid=:uid ORDER BY date DESC LIMIT 1';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':uid' => $row['uid']));
        $login_data = $sth -> fetch();
        if (!$login_data) {
          $login_data['ip'] = $_SERVER['REMOTE_HOST'];
          $login_data['date'] = time();
        }
        $_SESSION["ip"] = $login_data['ip'];
        $_SESSION["date"] = $login_data['date'];
        $query = "INSERT INTO user_logins (date, ip) VALUES (now(),:ip)";
        $sth = $dbh->prepare($query);
        $sth->execute(array(':ip'=>$_SERVER['REMOTE_HOST']));
        $dbh = null;
        $json_array = [];
        $json_array['user_details'] = [];
        $json_array['user_details']['name'] = $row['login_name'];
        $json_array['user_details']['ip'] = $login_data['ip'];
        $json_array['user_details']['date'] = $login_data['date'];
        header('Content-Type: application/json');
        echo json_encode($json_array);
        http_response_code(200);
      }
    } catch(PDOException $error) {
      echo $error->getMessage();
      http_response_code(500);
    }
    break;
}
?>