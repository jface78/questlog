<?php
session_start();
require_once("../../mongol_config.php");

if (isset($_SESSION['mongol'])) {
  require_once("../../mongol_connect.php");
  $sessionData = explode("|", $_SESSION['mongol']);
  switch($_POST['operation']) {
    case "getPost":
    $sql = $dbh -> prepare("SELECT questID, isForum FROM posts WHERE postID = :postID");
    $sql -> bindParam(':postID', $_POST['postID']);
    $sql -> execute();
    $questArray = $sql -> fetch();
    $questID = $questArray[0];
    $isForum = $questArray[1];
    if ($isForum != "1") {
      $sql = $dbh -> prepare("SELECT associationID FROM questAssociations WHERE userID = :userID AND questID = :questID");
      $sql -> bindParam(':userID', $sessionData[1]);
      $sql -> bindParam(':questID', $questID);
      $sql -> execute();
      $authorized = $sql -> rowCount();
    } else {
      $sql = $dbh -> prepare("SELECT characterID FROM posts WHERE postID = :postID");
      $sql -> bindParam(':postID', $_POST['postID']);
      $sql -> execute();
      $charID = $sql -> fetch();
      if ($charID[0] == $sessionData[1]) {
        $authorized = 1;
      } else {
        $authorized = 0;
      }
    }
    if ($authorized >= 1) {
      $sql = $dbh -> prepare("SELECT postText FROM posts WHERE postID = :postID");
      $sql -> bindParam(':postID', $_POST['postID']);
      $sql -> execute();
      $text = $sql -> fetch();
      $text = stripSlashes(html_entity_decode($text[0], ENT_QUOTES));
      $newText = "";
      $pattern = "/<br \/>\*\*\* Di[c]?e Roll:[ 0-9=db<>\/+-]* \*\*\*<br \/>/";
      if (preg_match($pattern, $text)) {
        $splitOut = preg_split($pattern, $text);
        for ($i=0; $i < count($splitOut); $i++) {
          $newText .= $splitOut[$i];
        }
        $text = $newText;
      }
      $dbh = null;
      header('HTTP/1.0 200 OK');
      echo $text;
    } else {
      $dbh = null;
      echo $isForum;
      header('HTTP/1.0 401 Unauthorized');
    }
    break;
    case "updatePost":
    $sql = $dbh -> prepare("SELECT questID,isForum FROM posts WHERE postID = :postID");
    $sql -> bindParam(':postID', $_POST['postID']);
    $sql -> execute();
    $questArray = $sql -> fetch();
    $questID = $questArray[0];
    $isForum = $questArray[1];
    if ($isForum == "0") {
      $sql = $dbh -> prepare("SELECT associationID FROM questAssociations WHERE userID = :userID AND questID = :questID");
      $sql -> bindParam(':userID', $sessionData[1]);
      $sql -> bindParam(':questID', $questID);
      $sql -> execute();
      $authorized = $sql -> rowCount();
    } else {
      $sql = $dbh -> prepare("SELECT characterID FROM posts WHERE postID = :postID");
      $sql -> bindParam(':postID', $_POST['postID']);
      $sql -> execute();
      $charID = $sql -> fetch();
      if ($charID[0] == $sessionData[1]) {
        $authorized = 1;
      } else {
        $authorized = 0;
      }
    }
    if ($authorized >= 1) {
      $text = rawurldecode($_POST['postText']);
      $pattern = "/\/r(oll)? [0-9]*?d[0-9]*([+-][0-9]*)?/";
      if (preg_match($pattern, $text) > 0) {
        $text = convertDiceRolls($text, $pattern);
      }
      $sql = $dbh -> prepare("SELECT postText FROM posts WHERE postID = :postID");
      $sql -> bindParam(':postID', $_POST['postID']);
      $sql -> execute();
      $results = $sql -> fetch();
      $storedText = stripSlashes(html_entity_decode($results[0]));
      
      $pattern = "/<br \/>\*\*\* Di[c]?e Roll:[ 0-9=db<>\/+-]* \*\*\*<br \/>/";
      $newText = "";
      if (preg_match($pattern, $storedText)) {
        preg_match_all($pattern, $storedText, $matches);
        $splitOut = preg_split($pattern, $storedText);
        for ($i=0; $i < count($splitOut); $i++) {
          //$text .= $splitOut[$i];
        }
        for ($i=0; $i < count($matches[0]); $i++) {
          $text .= $matches[0][$i];
        }
        //$text = $newText;
      }
      $text = addSlashes(htmlentities($text));
      if ($_POST['characterID'] == "NULL") {
        $_POST['characterID'] = $sessionData[1];
      }
      $sql = $dbh -> prepare("UPDATE posts SET postText = :postText, editDate=now(), characterID = :characterID WHERE postID = :postID");
      $sql -> bindParam(":postText", $text);
      $sql -> bindParam(":postID", $_POST['postID']);
      $sql -> bindParam(":characterID", $_POST['characterID']);
      $sql -> execute();
      $pattern = "/\/msg \[(.+)\] (.+)<br>/";
      $message = array();
      $text = stripSlashes(html_entity_decode($text));
      if (strpos($text, "/msg") !== false) {
        $splitPMArray = explode("/msg", $text);
        $text = "";
        for ($i=0; $i<count($splitPMArray);$i++) {
          $subMsgArray = explode("<br>", $splitPMArray[$i]);
          for ($s=0; $s<count($subMsgArray); $s++) {
            $pattern = "/\[(.+)\]/";
            if (preg_match($pattern, $subMsgArray[$s])) {
              $query = $dbh -> prepare("SELECT characterID FROM posts WHERE postID = :postID");
              $query -> execute(array(':postID' => $_POST['postID']));
              $characterID = $query -> fetch();
              if ($characterID[0] == 0) {
                $query = $dbh -> prepare("SELECT questID FROM posts WHERE postID = :postID");
                $query -> execute(array(':postID' => $_POST['postID']));
                $questID = $query -> fetch();
                $query = $dbh -> prepare("SELECT gmID FROM quests WHERE questID = :questID");
                $query -> execute(array(':questID' => $questID[0]));
                $userID = $query -> fetch();
              } else {
                $query = $dbh -> prepare("SELECT userID FROM questAssociations WHERE questID = :questID");
                $query -> execute(array(':questID' => $questID[0]));
                $userID = $query -> fetch();
              } 
              if ($userID[0] == $sessionData[1]) {
                $nameSplitArrayFirst = explode("[", $subMsgArray[$s]);
                $nameSplitArraySecond = explode("]", $nameSplitArrayFirst[1]);
                $pm = $nameSplitArraySecond[1];
                $privateTo = strToLower($nameSplitArraySecond[0]);
                if ($privateTo == strToLower($sessionData[3])) {
                  $privateTo = "you";
                } else {
                  $privateTo = ucwords($privateTo);
                }
                $text = "<b>Private to " . $privateTo . ":</b> " . $pm . "<br>" ;
                array_push($message, $text);
              }
            } else {
              if ($subMsgArray[$s] != "") {
                array_push($message, $subMsgArray[$s] . "<br>");
              }
            }
          }
        }
        $text = "";
        for ($x=0; $x < count($message); $x++) {
          if ($message[$x] != "") {
            $text .= $message[$x];
          }
        }
      }
      $dbh = null;
      header('HTTP/1.0 200 OK');
      echo $text; 
    } else {
      $dbh = null;
      header('HTTP/1.0 401 Unauthorized');
    }
    break;
    case "addPost":
    $sql = $dbh -> prepare("SELECT isForum FROM quests WHERE questID = :questID");
    $sql -> bindParam(':questID', $_POST['questID']);
    $sql -> execute();
    $isForum = $sql -> fetch();
    if ($isForum[0] == "0") {
      $sql = $dbh -> prepare("SELECT associationID FROM questAssociations WHERE userID = :userID AND questID = :questID");
      $sql -> bindParam(':userID', $sessionData[1]);
      $sql -> bindParam(':questID', $_POST['questID']);
      $sql -> execute();
      $authorized = $sql -> rowCount();
    } else {
      $authorized = 1;
    }
    if ($authorized >= 1) {
      $text = rawurldecode($_POST['postText']);
      $pattern = "/\/r(oll)? ([0-9]*)?d[0-9]+([\+\-]+[0-9]+)?/";
      if (preg_match($pattern, $text) > 0) {
        $text = convertDiceRolls($text, $pattern);
      }
      $text = addSlashes(htmlentities($text));
      if ($_POST['characterID'] == "NULL" || $_POST['characterID'] == "") {
        //$_POST['characterID'] = $sessionData[1];
      }
      $sqlLine = "INSERT INTO posts (questID, sectionID, characterID, postDate, postText, isForum) VALUES(:questID, :sectionID, :characterID, now(), :postText, :isForum);";
      $sqlArray = array(':questID' => $_POST['questID'], ':sectionID' => $_POST['sectionID'], ':characterID' => $_POST['characterID'], ':postText' => $text, ':isForum' => $isForum[0]);
      
      /*
      if ($_POST['characterID'] == "NULL") {
        $sqlLine = "INSERT INTO posts (questID, sectionID, postDate, postText) VALUES(:questID, :sectionID, now(), :postText);";
        $sqlArray = array(':questID' => $_POST['questID'], ':sectionID' => $_POST['sectionID'], ':postText' => $text);
      } else {
        $sqlLine = "INSERT INTO posts (questID, sectionID, characterID, postDate, postText, isForum) VALUES(:questID, :sectionID, :characterID, now(), :postText, :isForum);";
        $sqlArray = array(':questID' => $_POST['questID'], ':sectionID' => $_POST['sectionID'], ':characterID' => $_POST['characterID'], ':postText' => $text, ':isForum' => $isForum[0]);
      }*/
      //$text = urldecode($_POST['text']);
      $sql = $dbh -> prepare($sqlLine);
      $sql -> execute($sqlArray);
      $sql = $dbh -> prepare("SELECT userID FROM emailPosts WHERE questID = :questID");
      $sql -> execute(array(':questID' => $_POST['questID']));
      if ($sql -> rowCount() > 0) {
        while ($results = $sql->fetch()) {
          notify($results['userID'], $_POST['questID'], $dbh);
        }
      }
      $dbh = null;
      header('HTTP/1.0 200 OK');
      echo $text;
    } else {
      $dbh = null;
      header('HTTP/1.0 401 Unauthorized');
    }
    break;
    case "deletePost":
    $sql = $dbh -> prepare("SELECT isForum FROM posts WHERE postID = :postID");
    $sql -> bindParam(':postID', $_POST['postID']);
    $sql -> execute();
    $isForum = $sql -> fetch();
    if ($isForum[0] == "1") {
      $sql = $dbh -> prepare("SELECT characterID FROM posts WHERE postID = :postID");
      $sql -> bindParam(':postID', $_POST['postID']);
      $sql -> execute();
      $charID = $sql -> fetch();
      if ($charID[0] == $sessionData[1]) {
        $authorized = 1;
      } else {
        $authorized = 0;
      }
    } else {
      $sql = $dbh -> prepare("SELECT associationID FROM questAssociations WHERE userID = :userID AND questID = :questID");
      $sql -> bindParam(':userID', $sessionData[1]);
      $sql -> bindParam(':questID', $_POST['questID']);
      $sql -> execute();
      $authorized = $sql -> rowCount();
    }
    if ($authorized > 0) {
      $query = $dbh -> prepare("DELETE FROM posts WHERE postID = :postID");
      $query -> bindParam(':postID', $_POST['postID']);
      $query -> execute();
      $dbh = null;
      header('HTTP/1.0 200 OK');
    } else {
      $dbh = null;
      header('HTTP/1.0 401 Unauthorized');
    }
    break;
  }
} else {
  header('HTTP/1.0 401 Unauthorized');
}

function convertDiceRolls($text, $pattern) {
  $server = "http://" . $_SERVER['SERVER_NAME'] . "/services/fetchDice.php";
  $diceResults = array();
  preg_match_all($pattern, $text, $matches);
  $textArray = preg_split($pattern, $text);
  $diceValues = $matches[0];
  $diceTextResults = array();
  $newText = "";
  for ($i=0; $i<count($diceValues);$i++) {
    $dice = explode("d", $diceValues[$i]);
    $rest = $dice[1];
    $first = $dice[0];
    $spaceSplit = explode(" ", $first);
    if ($spaceSplit[1] == "") {
      $amount = "1";
    } else {
      $amount = $spaceSplit[1];
    }
    $hasPlus = strpos($rest, "+");
    $hasMinus = strpos($rest, "-");

    if ($hasPlus > 0) {
      $splitPlus = explode("+", $rest);
      $modifier = $splitPlus[1];
      $rest = $splitPlus[0];
    } else if ($hasMinus > 0) {
      $splitMinus = explode("-", $rest);
      $modifier = $splitMinus[1];
      $rest = $splitMinus[0];
    } else {
      $modifier = "+0";
    }
    
    $fields = array(
      'number' => urlencode($amount),
      'type' => urlencode($rest),
      'modifier' => urlencode($modifier)
    );
    $fields_string = "";
    foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
    rtrim($fields_string, '&');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_URL, $server);
    curl_setopt($ch,CURLOPT_POST, count($fields));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    $result = curl_exec($ch);
    array_push($diceResults, $result);
    curl_close($ch);
    if ($amount == "1") {
      $howMany = "Die";
    } else {
      $howMany = "Dice";
    }
    if ($modifier == "+0") {
      $modifier = "";
    }
    $splitResults = explode("&", $result);
    $result = $splitResults[0];
    if ($hasPlus) {
      $finalResult = $result + $modifier;
      $finalText = $rest . " = " . $result . " + " . $modifier . " = <b>" . $finalResult . "</b>";
    } else if ($hasMinus) {
      $finalResult = $result - $modifier;
      $finalText = $rest .  " = " . $result . " - " . $modifier . " = <b>" . $finalResult . "</b>";
    } else {
      $finalText = $rest .  " = <b>" . $result . "</b>";
    }
    $diceText = "<br />*** " . $howMany . " Roll: " . $amount . "d" . $finalText . " ***<br />";
    array_push($diceTextResults, $diceText);
  }
  for ($x=0; $x < count($textArray); $x++) {
    $newText .= $textArray[$x] . $diceTextResults[$x];
  }
  return $newText;
}

function notify($userID, $questID, $dbh) {
  if (isset($_SESSION['mongol'])) {
    $posterData = explode("|", $_SESSION['mongol']);
    $posterID = $posterData[1];
    $sql = $dbh -> prepare("SELECT handle FROM users WHERE userID = :userID");
    $sql -> bindParam(':userID', $userID);
    $sql -> execute();
    $recipientName = $sql -> fetch();
    $sql = $dbh -> prepare("SELECT postID, postDate, characterID FROM posts WHERE questID = :questID ORDER BY postDate DESC LIMIT 1");
    $sql -> bindParam(':questID', $questID);
    $sql -> execute();
    $postData = $sql -> fetch();
    if ($postData['characterID'] == 0) {
      $sql = $dbh -> prepare("SELECT handle FROM users WHERE userID = :userID");
      $sql -> bindParam(':userID', $posterID);
      $sql -> execute();
      $posterName = $sql -> fetch();
      $posterName = ucwords($posterName[0]);
    } else {
      $sql = $dbh -> prepare("SELECT characterName FROM characters WHERE characterID = :characterID");
      $sql -> bindParam(':characterID', $postData['characterID']);
      $sql -> execute();
      $posterName = $sql -> fetch();
      $posterName = ucwords($posterName[0]);
    }
    $postTime = $postData['postDate'];
    $sql = $dbh -> prepare("SELECT title FROM quests WHERE questID = :questID");
    $sql -> bindParam(':questID', $questID);
    $sql -> execute();
    $questName = $sql -> fetch();
    $sql = $dbh -> prepare("SELECT email FROM userDetails WHERE userID = :userID");
    $sql -> bindParam(':userID', $userID);
    $sql -> execute();
    $email = $sql -> fetch();
    $firstDate = date("l, F jS Y",strtotime($postData['postDate']));
    $secondDate = date("g:i a", strtotime($postData['postDate']));
    $date = $firstDate . " at " . $secondDate;
    
    $message = "Greetings " . ucwords($recipientName[0]) . ",\n\n";
    $message .= $posterName . " added a post to the above quest, which you're a member of, on " . $date . ".\n\n";
    $message .= "You have indicated that you wanted to be emailed whenever this happens, so here you go.\n\n";
    $message .= "If you want to change your email settings, just visit this quest and click the \"quest options\" button at the top.";
    $to      = $email[0];
    $subject = 'New post in ' . $questName[0];
    $headers = 'From: QuestLog Notifications <no-reply@questlog.org>' . "\r\n" .
    'Reply-To: no-reply@questlog.org' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
    mail($to, $subject, $message, $headers);
  }
}
?>