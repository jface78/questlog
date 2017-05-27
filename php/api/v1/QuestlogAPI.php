<?php
session_start();
require_once '../../../../questlog_credentials.php';
require_once 'API.class.php';
include 'utils.php';

class QuestlogAPI extends API {

  public function __construct($request, $origin) {
    parent::__construct($request);
    /*
    if (!array_key_exists('apiKey', $this->request)) {
      throw new Exception('No API Key provided');
    } else if (!$this -> verifyKey($this->request['apiKey'], $origin)) {
      throw new Exception('Invalid API Key');
    } else if (array_key_exists('token', $this->request) && !$User->get('token', $this->request['token'])) {
      throw new Exception('Invalid User Token');
    }*/
  }

  private function verifyKey($key) {
    if ($key == 1) {
      return true;
    } else {
      return false;
    }
  }
  
  protected function login($args) {
    try {
      $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $query = 'SELECT u.uid,u.login_name,g.group_name FROM users u, groups g
          WHERE u.login_name=:name
          AND u.login_hash=:hash
          AND u.gid=g.gid';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':name' => $args['name'], ':hash' => hashPasswd($args['name'], $args['pass'])));
      $row = $sth -> fetch();

      if(!$row) {
        $dbh = null;
        http_response_code(401);
        exit();
      } else {
        $_SESSION['uid'] = $row['uid'];
        $_SESSION['login'] = $row['login_name'];
        $_SESSION['last_activity'] = time();
        $query = 'SELECT ip, date FROM user_logins WHERE uid=:uid ORDER BY id DESC LIMIT 1';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':uid' => $row['uid']));
        $login_data = $sth -> fetch();
        if (!$login_data) {
          $login_data['ip'] = $_SERVER['REMOTE_ADDR'];
          $login_data['date'] = time();
        }
        $_SESSION['ip'] = $login_data['ip'];
        $_SESSION['date'] = $login_data['date'];
        $query = "INSERT INTO user_logins (uid, date, ip) VALUES (:uid, :time, :ip)";
        $sth = $dbh->prepare($query);
        $sth->execute(array(':uid' => $row['uid'],':time' => time(), ':ip' => $login_data['ip']));
        $dbh = null;
        $json_array = [];
        $json_array['user_details'] = [];
        $json_array['user_details']['name'] = $row['login_name'];
        $json_array['user_details']['id'] = $row['uid'];
        $json_array['user_details']['ip'] = $login_data['ip'];
        $json_array['user_details']['date'] = $login_data['date'];
        http_response_code(200);
        return $json_array;
      }
    } catch(PDOException $error) {
      echo $error;
      return 'database_error';
    }
  }
  
  public function session() {
    $json_array = [];
    $json_array['user_details'] = [];
    $json_array['user_details']['name'] = $_SESSION['login'];
    $json_array['user_details']['id'] = $_SESSION['uid'];
    $json_array['user_details']['ip'] = $_SESSION['ip'];
    $json_array['user_details']['date'] = $_SESSION['date'];
    http_response_code(200);
    return $json_array;
  }
  
  public function logout() {
    killSession();
  }
}
?>