<?php
include '../../../questlog_credentials.php';
include '../utils.php';

switch($_GET['request']) {
  case 'account':
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
      case 'PUT':
        break;
    }
    break;
  case 'password':
    parse_str(file_get_contents("php://input"), $post_vars);
    $email = $post_vars['email'];
    if (empty($email) ||!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      http_response_code(400);
      exit();
    }
    try {
      $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $query = 'SELECT uid FROM user_profiles WHERE user_email=:email';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':email' => $email));
      $uid = $sth -> fetch()[0];
      $hash = md5(time());
      if (count($uid) <= 0) {
        $dbh = null;
        http_response_code(404);
        exit();
      }
      $query = 'INSERT INTO password_recovery(uid, reset_hash) VALUES (:uid, :hash)';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':uid' => $uid, ':hash' => $hash));
      $dbh = null;
      $message = 'Hello,' . "\n\n" . 'Questlog received a request to reset your password. You may do so at the following link:' . "\n\n";
      $message .= 'http://www.questlog.org/new/resetPass.php?hash=' . $hash . "\n\n";
      $message .= 'You have one hour to comply.' . "\n\n";
      $message .= 'Regards,' . "\n" . 'Questlog.org';
      $headers = 'From: no-reply@questlog.org' . "\r\n" .
          'Reply-To: no-reply@questlog.org' . "\r\n" .
          'X-Mailer: PHP/' . phpversion();
      mail($email, 'questlog password recovery', $message, $headers);
      
    } catch(PDOException $error) {
      http_response_code(500);
    }
    break;
}
?>