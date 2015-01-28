<?php
require('../php/helpers.php');
require('../php/DatabaseConnection.php');

if (!isset($_GET['request'])) {
  http_response_code(400);
} else if (evaluateRequestParam('request', 'isUserNameRegistered', 'GET')) {
  if (!isset($_GET['userName'])) {
    http_response_code(400);
  } else {
    $db = new DatabaseConnection();
    $columns = ['userName'];
    $values = [$_GET['userName']];
    $results = $db -> matchColumns('users', $columns, $values);
    header('Content-type: text/xml');
    echo '<usernameRegistered>';
    if ($results > 0) {
      echo 'true';
    } else {
      echo 'false';
    }
    echo '</usernameRegistered>';
    http_response_code(200);
  }
} else if (evaluateRequestParam('request', 'createUser', 'GET')) {
  if (!isset($_POST['userName']) || !isset($_POST['password']) || !isset($_POST['email'])) {
    http_response_code(400);
  } else {
    if (strlen($_POST['password']) < 8 || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
      http_response_code(400);
    } else {
      $db = new DatabaseConnection();
      $columns = ['userName', 'pass', 'email', 'created', 'lastLogged', 'ip'];
      $values = [$_POST['userName'],
                 better_crypt($_POST['password']),
                 rtrim(strtr(base64_encode($_POST['email']), '+/', '-_'), '='),
                 'now()', 'now()', gethostbyaddr($_SERVER['REMOTE_ADDR'])];
      $db -> newRow('users', $columns, $values);
      
      http_response_code(200);
    }
  }
} else if (evaluateRequestParam('request', 'login', 'GET')) {
  parse_str(file_get_contents('php://input'), $_PUT);
  if (!isset($_PUT['username']) || !isset($_PUT['password'])) {
    http_response_code(400);
  } else {
    $db = new DatabaseConnection();
    $userExists = $db -> matchColumns('users', ['userName'], [$_PUT['username']]);
    if ($userExists == 0) {
      http_response_code(404);
    } else {
      $hash = $db -> fetchColumn('users', 'pass', 'userName', $_PUT['username']);
      $pass = crypt($_PUT['password'], $hash);
      if ($pass == $hash) {
        $userID = $db -> fetchColumn('users', 'userID', 'userName', $_PUT['username']);
        $_SESSION['mongol'] = $_PUT['username'] . '|' . $userID;
        $db -> updateColumns('users', ['lastLogged'], ['now()'], 'userID', $userID);
        http_response_code(200);
      } else {
        http_response_code(403);
      }
    }
  }
} else if (evaluateRequestParam('request', 'logout', 'GET')) {
  unset($_SESSION['mongol']);
  http_response_code(200);
}
?>