<?php
include '../../../questlog_credentials.php';
include '../utils.php';

switch($_SERVER['REQUEST_METHOD']) {
  case 'POST':
    if (empty($_POST['user']) || empty($_POST['email']) || empty($_POST['pass'])) {
      http_response_code(400);
      exit();
    }
    if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
      http_response_code(400);
      exit();
    }
    try {
      $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      
      $query = 'SELECT count(uid) FROM users WHERE login_name=:name';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':name' => $_POST['user']));
      if ($sth -> fetch()[0] > 0) {
        echo 'not here?';
        $dbh = null;
        http_response_code(409);
        exit();
      }
      $query = 'INSERT INTO users (login_name,login_hash,gid) VALUES(:login, :pass, 3)';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':login' => $_POST['user'], ':pass' => hashPasswd($_POST['user'],$_POST['pass'])));
      $id = $dbh->lastInsertId();
      $query = 'INSERT INTO user_profiles (uid, user_email) VALUES(:id, :email)';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':id' => $id, ':email' => $_POST['email']));
      $json_array = [];
      $json_array['users'] = [];
      $json_array['users'][0] = [];
      $json_array['users'][0]['userID'] = $id;
      $dbh = null;
      header("Content-Type: application/json");
      http_response_code(200);
      echo json_encode($json_array);
    } catch(PDOException $error) {
      http_response_code(500);
    }
    break;
}
?>