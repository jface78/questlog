<?php
require_once 'API.class.php';
include('../../../../../questlog_credentials.php');
include '../../utils.php';
date_default_timezone_set('UTC');

class QuestlogAPI extends API {
  protected $User;
  public function __construct($request, $origin) {
    parent::__construct($request);
    $applications = array(
      'apiKey' => '28e336ac6c9423d946ba02d19c6a2632' //randomly generated app key 
    );
    if (!array_key_exists('apiKey', $this->request)) {
      throw new Exception('No API Key provided');
    } else if (!in_array($this->request['apiKey'], $applications)) {
    //} else if (!$APIKey->verifyKey($this->request['apiKey'], $origin)) {
      throw new Exception('Invalid API Key');
    } else if (array_key_exists('token', $this->request) && !$User->get('token', $this->request['token'])) {
      throw new Exception('Invalid User Token');
    }
    //$this->User = $User;
    //Define our id-key pairs
    

  }

  protected function quests() {
    if ($this->method == 'GET') {
      if (empty($_GET['userID']) || (!empty($_GET['userID']) && !is_numeric($_GET['userID']))) {
        http_response_code(400);
        exit();
      }
      try {
        $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);

        // Get the user's GM quest.
        $query = 'SELECT qid,uid,quest_name,timestamp FROM quests WHERE uid=:uid';
        $sth = $dbh -> prepare($query);
        $sth -> execute(array(':uid' => $_GET['userID']));
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
        $sth -> execute(array(':uid' => $_GET['userID']));
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
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($json_array);
      } catch(PDOException $error) {
        echo $error->getMessage();
        http_response_code(500);
      }
    }
  }
  protected function example() {
    if ($this->method == 'GET') {
      return "Your name is " . $this->User->name;
    } else {
      return "Only accepts GET requests";
    }
  }
}
?>