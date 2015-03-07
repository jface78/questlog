<?php
require_once 'API.class.php';
include('../../../../questlog_credentials.php');
include '../../php/utils.php';

class QuestlogAPI extends API {

  public function __construct($request, $origin) {
    parent::__construct($request);

    if (!array_key_exists('apiKey', $this->request)) {
      throw new Exception('No API Key provided');
    } else if (!$this -> verifyKey($this->request['apiKey'], $origin)) {
      throw new Exception('Invalid API Key');
    } else if (array_key_exists('token', $this->request) &&
      !$User->get('token', $this->request['token'])) {
      throw new Exception('Invalid User Token');
    }
  }
    
  private function verifyKey($key) {
    if ($key == 1) {
      return true;
    } else {
      return false;
    }
  }

  private function generateQuestListings($results, $json_array, $type) {
    try {
      $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
      $index = 0;
      if (!array_key_exists($type, $json_array)) {
        $json_array['quests'][$type] = [];
      }
      foreach ($results as $row) {
        $json_array['quests'][$type][$index]['title'] = $row['quest_name'];
        $json_array['quests'][$type][$index]['sortable'] = getSortableTitle($row['quest_name']);
        $json_array['quests'][$type][$index]['questID'] = $row['qid'];
        $json_array['quests'][$type][$index]['created'] = strtotime($row['timestamp']);
        $query = 'SELECT count(pid) FROM posts WHERE qid=:qid';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':qid' => $row['qid']));
        $count = $sth -> fetch();
        $json_array['quests'][$type][$index]['count'] = $count['count(pid)'];
        $query = 'SELECT cid FROM posts WHERE qid=:qid ORDER BY post_date DESC LIMIT 1';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':qid' => $row['qid']));
        $cid = $sth -> fetch();
        $query = 'SELECT login_name FROM users WHERE uid=:uid';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':uid' => $row['uid']));
        $gmName = $sth -> fetch();
        $json_array['quests'][$type][$index]['gm'] = $gmName['login_name'];
        $query = 'SELECT timestamp FROM posts WHERE qid=:qid ORDER BY timestamp DESC LIMIT 1';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':qid' => $row['qid']));
        $lastPostDate = $sth -> fetch();
        $json_array['quests'][$type][$index]['lastPostDate'] = time($lastPostDate['timestamp']);
        if ($cid['cid'] == 0) {
          $query = 'SELECT login_name FROM users WHERE uid=:uid';
          $sth = $dbh -> prepare($query);
          $sth -> execute(array(':uid' => $row['uid']));
          $uname = $sth -> fetch();
          $json_array['quests'][$type][$index]['lastPostBy'] = $uname['login_name'];
        } else {
          $query = 'SELECT char_name FROM characters WHERE cid=:cid';
          $sth = $dbh -> prepare($query);
          $sth -> execute(array(':cid' => $cid['cid']));
          $cname = $sth -> fetch();
          $json_array['quests'][$type][$index]['lastPostBy'] = $cname['char_name'];
        }
        $index++;
      }
      $dbh = null;
      return $json_array;
    } catch(PDOException $error) {
      echo $error->getMessage();
      http_response_code(500);
      exit();
    }
  }

  protected function quests() {
    // Check that the session is active.
    if (!checkSession()) {
      http_response_code(401);
      exit();
    }
    try {
      $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
      // Get the user's GM quest.
      $query = 'SELECT qid,uid,quest_name,timestamp FROM quests WHERE uid=:uid';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':uid' => $_SESSION['uid']));
      $questsArr = $sth -> fetchAll();
      $gm_quests = '0';
      foreach ($questsArr as $quest) {
        $gm_quests .= ',' . $quest['qid'];
      }
      $json_array = [];
      $json_array['quests'] = [];
      $json_array = generateQuestListings($questsArr, $json_array, 'gmQuests');

      // Get the user's player quests.
      $query = 'SELECT cid FROM characters WHERE uid=:uid';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':uid' => $_SESSION['uid']));
      $results = $sth -> fetchAll();
      $questsArr = [];
      $index=0;
      $player_chars = '0';
      foreach ($results as $row) {
        $query = 'SELECT qid FROM quest_members WHERE cid=:cid';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':cid' => $row['cid']));
        $player_chars .= ',' . $row['cid'];
        $player_quests = $sth -> fetchAll();
        foreach ($player_quests as $quest) {
          $query = 'SELECT qid,uid,quest_name,timestamp FROM quests WHERE qid=:qid';
          $sth = $dbh -> prepare($query);
          $sth -> execute(array(':qid' => $quest['qid']));
          $questsArr[$index] = $sth -> fetch();
          $index++;
        }
      }
      $json_array = generateQuestListings($questsArr, $json_array, 'playerQuests');

      // Get all remaining  non-player, non-GM quests.
      $query = 'SELECT qid FROM quest_members WHERE qid NOT IN (' . $gm_quests .') AND cid NOT IN (' . $player_chars . ')';
      $sth = $dbh -> prepare($query);
      $sth -> execute();
      $non_quests = $sth -> fetchAll();
      $questsArr = [];
      $index = 0;
      foreach ($non_quests as $quest) {
        $query = 'SELECT qid,uid,quest_name,timestamp FROM quests WHERE qid=:qid AND quest_status != :disabled';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':qid' => $quest['qid'], ':disabled' => 3));
        $results = $sth -> fetch(PDO::FETCH_ASSOC);
        if ($results['quest_name'] != null) {
          $questsArr[$index] = $results;
        }
        $index++;
      }
      usort($questsArr, function($a, $b) {
        return $a['quest_name'] > $b['quest_name'];
      });
      $json_array = generateQuestListings($questsArr, $json_array, 'otherQuests');
      $dbh = null;
      $_SESSION['last_activity'] = time();
      return $json_array;
    } catch(PDOException $error) {
      return $error->getMessage();
      http_response_code(500);
    }
  }
  protected function quest($args) {
    // Check that the session is active.
    if (!checkSession()) {
      http_response_code(401);
      exit();
    }
    $postOrder = 'DESC';
    if (!empty($args[2])) {
      $postOrder = strtoupper($args[2]);
    }
    if (empty($args[0]) || !is_numeric($args[0])) {
      http_response_code(400);
      exit();
    }
    try {
      $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
      $json_array = [];
      $query = 'SELECT qid,quest_name FROM quests WHERE qid = :questID';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':questID' => $args[0]));
      $row = $sth -> fetch();

      if (!$row) {
        http_response_code(404);
        $dbh = null;
        exit();
      }
    $json_array['title'] = $row['quest_name'];
    $json_array['questID'] = $row['qid'];

    if (empty($args[1])) {
      $json_array['pageCount'] = 1;
    } else {
      $query = 'SELECT COUNT(pid) FROM posts WHERE qid=:questID';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':questID' => $args[0]));
      $total = $sth -> fetch();
      $json_array['pageCount'] = ceil($total['COUNT(pid)']/$args[1]);
    }
    $query = 'SELECT pid,uid,cid,timestamp,post_text FROM posts WHERE qid=:questID ORDER BY timestamp ' . $postOrder;
    if (!empty($args[1]) && is_numeric($args[1])) {
      $query .= ' LIMIT ' . $args[1];
    }
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':questID' => $args[0]));
    $results = $sth -> fetchAll();

    $index = 0;
    foreach($results as $row) {
      $json_array['posts'][$index] = array();
      $json_array['posts'][$index]['id'] = $row['pid'];
      $json_array['posts'][$index]['timestamp'] = strtotime($row['timestamp']);
      $json_array['posts'][$index]['text'] = $row['post_text'];
      $json_array['posts'][$index]['uid'] = $row['uid'];
      $json_array['posts'][$index]['cid'] = $row['cid'];
      if ($row['cid'] == '0') {
        $json_array['posts'][$index]['cid'] = 'GM';
        $query = 'SELECT login_name FROM users WHERE uid = :userID';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':userID' => $row['uid']));
        $user = $sth -> fetch();
        $json_array['posts'][$index]['postedBy'] = $user['login_name'];
      } else {
        $query = 'SELECT char_name FROM characters WHERE cid = :characterID';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':characterID' => $row['cid']));
        $character = $sth -> fetch();
        $json_array['posts'][$index]['postedBy'] = $character['char_name'];
      }
      $index++;
    }
    $dbh = null;
    $_SESSION['last_activity'] = time();
    return $json_array;
  } catch(PDOException $error) {
    echo $error->getMessage();
    http_response_code(500);
  }
  }
}
?>