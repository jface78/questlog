<?php
define('SESSION_TIMEOUT', 3600);

function killSession() {
  session_unset();
}

function getPostersName($cid, $qid) {
  try {
    $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
    if ($cid != 0) {
      $query = 'SELECT char_name FROM characters WHERE cid=:cid';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':cid' => $cid));
      $dbh = null;
      return $sth -> fetch()[0];
    } else {
      $query = 'SELECT uid FROM quests WHERE qid=:qid';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':qid' => $qid));
      $uid = $sth -> fetch()[0];
      $query = 'SELECT login_name FROM users WHERE uid=:uid';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':uid' => $uid));
      $dbh = null;
      return $sth -> fetch()[0] . ' - GM';
    }
    
  } catch(PDOException $error) {
    http_response_code(500);
    exit();
  }
}

function getSortableTitle($title) {
  if (strpos(strtolower($title), 'an ') === 0) {
    return substr($title, 3, strlen($title)-1) . ', An';
  } else if (strpos(strtolower($title), 'a ') === 0) {
    return substr($title, 2, strlen($title)-1) . ', A';
  } else if (strpos(strtolower($title), 'the ') === 0) {
    return substr($title, 4, strlen($title)-1) . ', The';
  } else {
    return $title;
  }
}

function generateQuestListings($results, $json_array, $type) {
  try {
    $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
    $index = 0;
    if (!array_key_exists($type, $json_array)) {
      $json_array['quests'][$type] = [];
    }
    foreach ($results as $row) {
      if (!empty($row)) {
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
        $json_array['quests'][$type][$index]['lastPostDate'] = strtotime($lastPostDate['timestamp']);
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
    }
    $dbh = null;
    return $json_array;
  } catch(PDOException $error) {
    http_response_code(500);
    exit();
  }
}


function checkSession() {
  if ((isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) || !isset($_SESSION['last_activity'])) {
    killSession();
    return false;
  } else {
    $_SESSION['last_activity'] = time();
    return true;
  }
}

function hashPasswd($username, $passwd) {
  $hash_1 = bin2hex( mhash(MHASH_RIPEMD160, $passwd . $username) );
  $hash_2 = bin2hex( mhash(MHASH_RIPEMD160, $passwd . $hash_1) );
  return($hash_2);
}

function rollDice($number, $type) {
  return rand(1,$type) * $number;
}

function databaseToDisplayText($text, $character) {
  try {
    $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
    $pattern = '/\[r\][0-9]+\[\/r\]/i';
    preg_match_all($pattern, $text, $matches);
    $index = 0;
    foreach ($matches[0] as $match) {
      $pos = strpos($match, '[r]');
      $id = substr($match, $pos+3, strpos($match, '[/r]'));
      $query = 'SELECT roll, type FROM rolls WHERE rid=:rid';
      $sth = $dbh -> prepare($query);
      $sth -> execute(array(':rid' => $id));
      $results = $sth -> fetch();
      $type = $results['type'];
      $pos = strpos($type, 'd');
      $amount = substr($results['type'], 0, $pos);
      $type = substr($results['type'], $pos+1, strlen($results['type']));
      if ($amount == 1) {
        $string = '*** <b>' . $character . '</b> rolled <b>one ' . $type . '-sided die</b>: ';
      } else {
        $string = '<b>*** <b>' . $character . '</b> rolled <b>' . $amount . ' ' . $type . '-sided dice</b>: ';
      }
      $string .= $results['roll'] . ' ***';
      $text = str_replace($matches[0][$index], $string, $text);
      $index++;
    }
    $dbh = null;
    return $text;
  } catch (PDOException $error) {
    http_response_code(500);
  }
}

function sanitizeText($text) {
  //$text = html_entity_decode($text);
  $text = deathToCheaters($text);
  return ircToDatabase($text);
}

function deathToCheaters($text) {
  $pattern = '/\*\*\*(.)+rolled(.)+\*\*\*/i';
  return preg_replace($pattern, '', $text);
}

function ircToDatabase($text) {
  try {
    $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
    $pattern = '/\|r(oll)?\s(\w+)/i';
    preg_match_all($pattern, $text, $matches);
    $index = 0;
    $ids = [];
    foreach ($matches[2] as $match) {
      $pos = strpos($match, 'd');
      $amount = substr($match, 0, $pos);
      if (strlen($amount) == 0) {
        $amount = 1;
      }
      $type = substr($match, $pos+1, strlen($match));
      $results = rollDice($amount, $type);
      $query = 'INSERT INTO rolls (roll, type) VALUES(:results, :type)';
      $sth = $dbh -> prepare($query);
      $type = $amount . 'd' . $type;
      $sth -> execute(array(':results' => $results, ':type' => $type));
      array_push($ids, '[r]' . $dbh -> lastInsertId() . '[/r]');
      $text = str_replace($matches[0][$index], '', $text);
      $index++;
    }
    foreach ($ids as $id) {
      text .= $id;
    }
    $dbh = null;
    return $text;
  } catch(PDOException $error) {
    http_response_code(500);
  }
}


?>