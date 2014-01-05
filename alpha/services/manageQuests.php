<?php
session_start();
require_once("../../mongol_config.php");

if (isset($_SESSION['mongol'])) {
  require_once("../../mongol_connect.php");
  $sessionData = explode("|", $_SESSION['mongol']);
  switch($_POST['operation']) {
    case "getCharacters":
    $sqlString = "SELECT characterName, characterID, userID, thumbPortrait FROM characters WHERE characterName LIKE :term";
    if ($_POST['existingCharacters'] != "") {
      $charactersArray = explode("&", $_POST['existingCharacters']);
      for ($i=0; $i<count($charactersArray)-1; $i++) {
        $sqlString .= " AND characterID != " . $charactersArray[$i];
      }
    }
    $sql = $dbh -> prepare($sqlString);
    $sql -> execute(array(':term' => $_POST['term'] . '%' ));
    $string = "";
    while ($results = $sql->fetch()) {
      $innerSQL = $dbh -> prepare("SELECT blockID FROM blocked WHERE blockerID = :blockerID AND blockedID = :blockedID");
      $innerSQL -> execute(array(':blockerID' => $results['userID'], ':blockedID' => $sessionData[1]));
      $blocked = $innerSQL -> rowCount();
      if ($blocked <= 0) {
        $string .= $results['characterName'] . "&";
        $string .= $results['characterID'] . "&";
        $string .= $results['thumbPortrait'] . "&";
        $string .= $results['userID'] . "&";
        $query = $dbh -> prepare("SELECT handle FROM users WHERE userID = :userID");
        $query -> execute(array(':userID' => $results['userID']));
        $user = $query -> fetch();
        $string .= $user[0] . "|";
      }
    }
    $dbh = null;
    header('HTTP/1.0 200 OK');
    echo $string;
    break;
    case "updateQuest":
        //title: title, descr: descr, visible: visible, players, characters}
    $title = addSlashes(htmlentities($_POST['title']));
    $descr = addSlashes(htmlentities($_POST['descr']));
    $characters = html_entity_decode($_POST['characters']);
    $players = html_entity_decode($_POST['players']);
    $sql = $dbh->prepare("UPDATE quests SET title=:title, descr=:descr, visible=:visible WHERE questID = :questID");
    $sql -> execute( array(':title' => $title, ':descr' => $descr, ':visible' => $_POST['visible'], ':questID' => $_POST['questID']) ); 
    if (isset($_POST['characters'])) {
      $characterIDs = explode("&", $characters);
      $playerIDs = explode("&", $players);
      $characterMatch = false;
      $sql = $dbh -> prepare("SELECT characterID FROM questAssociations WHERE questID=:questID");
      $sql -> execute(array(':questID' => $_POST['questID']));
      $existingCount = $sql -> rowCount();
      if ($existingCount > 0) {
        $existingChars = $sql -> fetchAll(PDO::FETCH_COLUMN);
        for ($i=0; $i < count($playerIDs)-1; $i++) {
          if (!in_array($characterIDs[$i], $existingChars)) {
            if ($playerIDs[$i] != $sessionData[1]) {
              $isUser = true;
              if (!isset($gmName)) {
                $sql = $dbh -> prepare("SELECT handle FROM users WHERE userID = :gmID");
                $sql -> execute(array(':gmID' => $sessionData[1]));
                $gmName = $sql -> fetch();
              }
              $message = "<div style=\"text-align:center;\">";
              $message .= "<b>" . $gmName[0] . "</b> has invited one or more of your characters to join his game, <b>" . $_POST['title'] . ".</b><br /><br />";
              $message .= "<button type=\"button\" id=\"acceptBtn\">accept</button>&nbsp;";
              $message .= "<button type=\"button\" id=\"declineBtn\">decline</button><br /><br />";
              $message .= "<button type=\"button\" id=\"declineAndBlockBtn\">decline and block user</button>";
              $message .= "</div>";
              $message = urlencode($message);
              $sql = $dbh -> prepare("INSERT INTO alerts(userID, dateSent, message, senderID) VALUES(:userID, now(), :msg, :snd)");
              $sql -> execute(array(':userID'=>$playerIDs[$i], ':msg' => $message, ':snd' => $sessionData[1]));
              $alertID = $dbh->lastInsertId();
            }
            if (!isset($alertID)) {
              $alertID = NULL;
              $approved = 1;
            }
            else {
              $approved = 0;
            }
            $sql = $dbh -> prepare("INSERT INTO questAssociations(characterID, questID, userID, approved, alertID)
                                    VALUES(:characterID, :questID, :userID, :approved, :alertID);");
            $sql -> execute(array(':characterID' => $characterIDs[$i], ':questID' => $_POST['questID'],
                                  ':alertID' => $alertID, ':approved' => $approved, ':userID' => $playerIDs[$i]));
            unset($alertID);
          }
        }
        for ($p=0; $p < count($existingChars); $p++) {
          if (!in_array($existingChars[$p], $characterIDs)) {
            echo $existingChars[$p] . "??";
            $sql = $dbh -> prepare("SELECT alertID FROM questAssociations WHERE questID = :questID AND characterID = :characterID");
            $sql -> execute(array(':questID' => $_POST['questID'], ':characterID' => $existingChars[$p]));
            $alertID = $sql -> fetch();
            if (isset($alertID[0])) {
              $sql = $dbh -> prepare("DELETE FROM alerts WHERE alertID = :alertID");
              $sql -> execute(array(':alertID' => $alertID[0]));
            }
            $sql = $dbh -> prepare("DELETE FROM questAssociations WHERE questID = :questID AND characterID = :characterID");
            $sql -> execute(array(':questID' => $_POST['questID'], ':characterID' => $existingChars[$p]));
          }
        }
      } else {
        $alreadyAlerted = array();
        for ($i=0; $i<count($characterIDs)-1;$i++) {
          if ($playerIDs[$i] != $sessionData[1]) {
            if (!in_array($playerIDs[$i], $alreadyAlerted)) {
              if (!isset($gmName)) {
                $sql = $dbh -> prepare("SELECT handle FROM users WHERE userID = :gmID");
                $sql -> execute(array(':gmID' => $sessionData[1]));
                $gmName = $sql -> fetch();
              }
              $message = "<div style=\"text-align:center;\">";
              $message .= "<b>" . $gmName[0] . "</b> has invited you to join his game, <b>" . $_POST['title'] . ".</b><br /><br />";
              $message .= "<button type=\"button\" class=\"lightButton\" id=\"acceptBtn\">accept</button>&nbsp;";
              $message .= "<button type=\"button\" class=\"lightButton\" id=\"declineBtn\">decline</button><br /><br />";
              $message .= "<button type=\"button\" class=\"lightButton\" id=\"declineAndBlockBtn\">decline and block user</button>";
              $message .= "</div>";
              $message = urlencode($message);
              $sql = $dbh -> prepare("INSERT INTO alerts(userID, dateSent, message, senderID) VALUES(:userID, now(), :msg, :snd)");
              $sql -> execute(array(':userID'=>$playerIDs[$i], ':msg' => $message, ':snd' => $sessionData[1]));
              $alertID = $dbh->lastInsertId();
              array_push($alreadyAlerted, $playerIDs[$i]);
              $approved = 0;
            } else {
              $alertID = NULL;
              $approved = 1;
            }
          }
          $sql = $dbh -> prepare("INSERT INTO questAssociations(characterID, questID, userID, approved, alertID)
                                  VALUES(:characterID, :questID, :userID, :approved, :alertID);");
          $sql -> execute(array(':characterID' => $characterIDs[$i], ':questID' => $_POST['questID'],
                                ':alertID' => $alertID, ':approved' => $approved, ':userID' => $playerIDs[$i]));
        }
      }
    }
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "deleteSection":
    $sql = $dbh -> prepare("SELECT userID FROM sections WHERE sectionID = :sectionID");
    $sql -> execute(array(':sectionID' => $_POST['sectionID']));
    $userID = $sql -> fetch();
    if ($userID[0] == $sessionData[1]) {
      $sql = $dbh -> prepare("UPDATE sections SET visible=0 WHERE sectionID = :sectionID");
      $sql -> execute(array(':sectionID' => $_POST['sectionID']));
      $dbh = null;
      header('HTTP/1.0 200 OK');
    } else {
      $dbh = null;
      header('HTTP/1.0 401 Unauthorized');
    }
    break;
    case "addThread":
    $sql = $dbh -> prepare("INSERT INTO sections (questID, sectionTitle, created, userID) VALUE(:questID, :title, now(), :userID)");
    $sql -> execute(array(':questID' => $_POST['questID'], ':title' => $_POST['title'], ':userID' => $sessionData[1]));
    $sectionID = $dbh->lastInsertId();
    $sql = $dbh -> prepare("INSERT INTO posts (questID, characterID, postDate, postText, sectionID, isForum)
                            VALUE(:questID, :charID, now(), :text, :section, 1)");
    $sql -> execute(array(':questID' => $_POST['questID'], ':charID' => $sessionData[1], ':text' => $_POST['postText'], ':section' => $sectionID));
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "newSection":
    $sql = $dbh -> prepare("SELECT isForum FROM quests WHERE questID = :questID");
    $sql -> execute(array(':questID' => $_POST['questID']));
    $isForum = $sql -> fetch();
    if ($isForum[0] == 1) {
      $sql = $dbh -> prepare("INSERT INTO sections(questID, sectionTitle, created, userID) VALUES (:questID, :title, now(), :userID)");
      $sql -> execute(array(':questID' => $_POST['questID'], ":title" => $_POST['title'], ":userID" => $sessionData[1]));
      $dbh = null;
      header('HTTP/1.0 200 OK');
    } else {
      $sql = $dbh -> prepare("SELECT gmID FROM quests WHERE questID = :questID");
      $sql -> execute(array(':questID' => $_POST['questID']));
      $gmID = $sql -> fetch();
      if ($gmID[0] == $sessionData[1]) {
        $sql = $dbh -> prepare("INSERT INTO sections(questID, sectionTitle, created) VALUES (:questID, :title, now())");
        $sql -> execute(array(':questID' => $_POST['questID'], ":title" => $_POST['title']));
        $dbh = null;
        header('HTTP/1.0 200 OK');
      } else {
        $dbh = null;
        header('HTTP/1.0 401 Unauthorized');
      }
    }
    break;
    case "getForums":
    $sql = $dbh -> prepare("SELECT questID, title, descr FROM quests WHERE isForum = 1 AND visible = 1");
    $sql -> execute();
    if ($sql -> rowCount() > 0) {
      $string = "";
      while ($results = $sql -> fetch()) {
        $string .= $results['questID'] . "&";
        $string .= $results['title'] . "&";
        $string .= $results['descr'] . "|";
      }
      $dbh = null;
      header('HTTP/1.0 200 OK');
      echo $string;
    } else {
      $dbh = null;
      header('HTTP/1.0 404 Not Found');
    }
    break;
    case "getSections":
    $sql = $dbh -> prepare("SELECT sectionID,sectionTitle,created,userID FROM sections WHERE visible = 1 AND questID = :questID ORDER BY created ASC");
    $sql -> execute(array(':questID' => $_POST['questID']));
    if ($sql -> rowCount() > 0) {
      $string = "";
      while ($results = $sql->fetch()) {
        $string .= $results['sectionID'] . "&";
        $string .= $results['sectionTitle'] . "&";
        $sqlDate = strtotime($results['created']);
        $date = date("D M j Y", $sqlDate) . " at " . date("G:i:s T", $sqlDate);
        $string .= $date;
        if ($sessionData[1] == $results['userID']) {
          $string .= "&true&?";
        } else {
          $string .= "&false&?";
        }
      }
      $dbh = null;
      header('HTTP/1.0 200 OK');
      echo $string;
    } else {
      $dbh = null;
      header('HTTP/1.0 404 Not Found');
    }
    break;
    case "newQuest":
    //title: title, descr: descr, visible: visible, players, characters}
    $sql = $dbh->prepare("INSERT INTO quests (gmID, title, descr, visible, creationDate) VALUES(:gmID, :title, :descr, :visible, now())");
    $sql -> execute(array(':gmID'=>$sessionData[1], ':title'=>$_POST['title'], ':descr'=>$_POST['descr'], ':visible'=>$_POST['visible'])); 
    if (isset($_POST['characters'])) {
      $sql = $dbh->prepare("SELECT questID FROM quests WHERE gmID = :gmID AND title = :title ORDER BY creationDate DESC LIMIT 1");
      $sql -> execute(array(':gmID'=>$sessionData[1], ':title'=>$_POST['title']));
      $questID = $sql -> fetch();
      $sql = $dbh->prepare("INSERT INTO sections(questID, sectionTitle)VALUES(:questID, 'Part One')");
      $sql -> execute(array(':questID'=>$questID[0]));
      $characterIDs = explode("&", $_POST['characters']);
      $playerIDs = explode("&", $_POST['players']);
      $alreadyAlerted = array();
      for ($i=0; $i < count($playerIDs)-1; $i++) {
        if ($playerIDs[$i] != $sessionData[1]) {
          if (!in_array($playerIDs[$i], $alreadyAlerted)) {
            if (!$gmName) {
              $sql = $dbh -> prepare("SELECT handle FROM users WHERE userID = :gmID");
              $sql -> execute(array(':gmID' => $sessionData[1]));
              $gmName = $sql -> fetch();
            }
            $message = "<div style=\"text-align:center;\">";
            $message .= "<b>" . $gmName[0] . "</b> has invited you to join his game, <b>" . $_POST['title'] . ".</b><br /><br />";
            $message .= "<button type=\"button\" class=\"lightButton\" id=\"acceptBtn\">accept</button>&nbsp;";
            $message .= "<button type=\"button\" class=\"lightButton\" id=\"declineBtn\">decline</button><br /><br />";
            $message .= "<button type=\"button\" class=\"lightButton\" id=\"declineAndBlockBtn\">decline and block user</button>";
            $message .= "</div>";
            $message = urlencode($message);
            $sql = $dbh -> prepare("INSERT INTO alerts(userID, dateSent, message, senderID) VALUES(:userID, now(), :msg, :snd)");
            $sql -> execute(array(':userID'=>$playerIDs[$i], ':msg' => $message, ':snd' => $sessionData[1]));
            $alertID = $dbh->lastInsertId();
            $approved = 0;
            array_push($alreadyAlerted, $playerIDs[$i]);
          } else {
            $approved = 0;
          }
        }
        else {
          $approved = 1;
        }
        if (!$alertID) {
          $alertID = "NULL";
        }
        $sql = $dbh -> prepare("INSERT INTO questAssociations(questID, characterID, userID, alertID, approved) VALUES(:questID, :characterID, :userID, :alertID, :approved)");
        $sql -> execute(array(':questID'=>$questID[0], ':characterID'=>$characterIDs[$i], ':userID'=>$playerIDs[$i], ':alertID'=>$alertID, ':approved' => $approved));
      }
    }
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "getAllQuests":
    $sql = $dbh -> prepare("SELECT questID, title, descr FROM quests WHERE gmID = :userID AND active=1 AND isForum=0");
    $sql -> execute(array(':userID' => $sessionData[1]));
    $gmCount = $sql -> rowCount();
    $questsCount = 0;
    $grabbedQuests = array();
    $string = "";
    $gmQuestsArray = array();
    if ($gmCount > 0) {
      while ($results = $sql->fetch()) {
        $string .= "gm&";
        $string .= $results['questID'] . "&";
        $string .= stripSlashes($results['title']) . "&";
        $string .= stripSlashes($results['descr']) . "&";
        array_push($grabbedQuests, $results['questID']);
        array_push($gmQuestsArray, $results['questID']);
        $query = $dbh -> prepare("SELECT characterID FROM questAssociations WHERE questID = :questID");
        $query -> execute(array(':questID' => $results['questID']));
        if ($query -> rowCount() > 0) {
          while ($innerResults = $query -> fetch()) {
            $innerQuery = $dbh -> prepare("SELECT characterName FROM characters WHERE characterID = :characterID");
            $innerQuery -> execute(array(':characterID' => $innerResults['characterID']));
            $characterNamesArray = $innerQuery -> fetch();
            $string .= $characterNamesArray[0] . "&";
          }
        }
        $string .= "|";
        $questsCount++;
      }
    }
    $sql = $dbh -> prepare("SELECT questID FROM questAssociations WHERE userID = :userID AND active=1 GROUP BY questID");
    $sql -> execute(array(':userID' => $sessionData[1]));
    $pcCount = $sql -> rowCount();
    if ($pcCount > 0) {
      $results = $sql -> fetchAll();
      foreach ($results as $result) {
        if (!in_array($result['questID'], $gmQuestsArray)) {
          array_push($grabbedQuests, $result['questID']);
          $query = $dbh -> prepare("SELECT gmID, title, descr FROM quests WHERE questID = :questID AND active=1");
          $query -> execute(array(':questID' => $result['questID']));
          while($innerResults = $query -> fetch()) {
            $gmID = $innerResults['gmID'];
            $string .= "pc&";
            $string .= $result['questID'] . "&";
            $string .= $innerResults['title'] . "&";
            $string .= $innerResults['descr'] . "&";
          }
          $query = $dbh -> prepare("SELECT handle FROM users WHERE userID = :gmID");
          $query -> execute(array(':gmID' => $gmID));
          $gmName = $query -> fetch();
          $string .= $gmName[0] . "&";
          $query = $dbh -> prepare("SELECT characterID FROM questAssociations WHERE questID = :questID");
          $query -> execute(array(':questID' => $result['questID']));
          while ($innerResults = $query -> fetch()) {
            $innerQuery = $dbh -> prepare("SELECT characterName FROM characters WHERE characterID = :characterID");
            $innerQuery -> execute(array(':characterID' => $innerResults['characterID']));
            $characterNamesArray = $innerQuery -> fetch();
            $string .= $characterNamesArray[0] . "&";
          }
          $string .= "|";
          $questsCount++;
        }
      }
    }
    $sql = $dbh -> prepare("SELECT questID FROM quests WHERE gmID != :userID AND visible=1 AND active=1 AND isForum=0");
    $sql -> execute(array(':userID' => $sessionData[1]));
    $results = $sql -> fetchAll();
    foreach ($results as $result) {
      if (!in_array($result['questID'], $grabbedQuests)) {
        $query = $dbh -> prepare("SELECT gmID, title, descr FROM quests WHERE questID = :questID");
        $query -> execute(array(':questID' => $result['questID']));
        while($innerResults = $query -> fetch()) {
          $gmID = $innerResults['gmID'];
          $string .= "other&";
          $string .= $result['questID'] . "&";
          $string .= $innerResults['title'] . "&";
          $string .= $innerResults['descr'] . "&";
          $query = $dbh -> prepare("SELECT handle FROM users WHERE userID = :gmID");
          $query -> execute(array(':gmID' => $gmID));
          $gmName = $query -> fetch();
          $string .= $gmName[0] . "&";
          $query = $dbh -> prepare("SELECT characterID FROM questAssociations WHERE questID = :questID");
          $query -> execute(array(':questID' => $result['questID']));
          while ($innerResults = $query -> fetch()) {
            $innerQuery = $dbh -> prepare("SELECT characterName FROM characters WHERE characterID = :characterID");
            $innerQuery -> execute(array(':characterID' => $innerResults['characterID']));
            $characterNamesArray = $innerQuery -> fetch();
            $string .= $characterNamesArray[0] . "&";
          }
          $string .= "|";
          $questsCount++;
        }
      }
    }
    $sql = $dbh -> prepare("SELECT questID FROM questAssociations WHERE userID = :userID");
    $sql -> execute(array(':userID' => $sessionData[1]));
    $pcCount = $sql -> rowCount();
    if ($pcCount > 0) {
      $results = $sql -> fetchAll();
      foreach ($results as $result) {
        if (!in_array($result['questID'], $gmQuestsArray)) {
          
          
        }
      }
    }
    if ($questsCount > 0) {
      $dbh = null;
      header('HTTP/1.0 200 OK');
      echo $string;
    } else {
      $dbh = null;
      header('HTTP/1.0 404 Not Found');
    }
    $dbh = null;
    break;
    case "updateSection":
    $sql = $dbh -> prepare("SELECT questID FROM sections WHERE sectionID = :sectionID");
    $sql -> execute(array(':sectionID' => $_POST['sectionID']));
    $questID = $sql -> fetch();
    $sql = $dbh -> prepare("SELECT gmID FROM quests WHERE questID = :questID");
    $sql -> execute(array(':questID' => $questID[0]));
    $gmID = $sql -> fetch();
    if ($gmID[0] == $sessionData[1]) {
      $sql = $dbh->prepare("UPDATE sections SET sectionTitle = :title WHERE sectionID = :sectionID");
      $sql -> execute(array(':title' => $_POST['title'], ':sectionID' => $_POST['sectionID']));
      $dbh = null;
      header('HTTP/1.0 200 OK');
    } else {
      $dbh = null;
      header('HTTP/1.0 401 Unauthorized');
    }
    break;
    case "getQuest":
    if ($_POST['direction'] == "reverse") {
      $dir = "DESC";
      $operator = "<=";
    } else {
      $dir = "ASC";
      $operator = ">=";
    }
    $sql = $dbh->prepare("SELECT postID FROM posts WHERE questID = :questID AND sectionID = :sectionID ORDER BY postID ".$dir);
    $sql -> execute(array(':questID' => $_POST['questID'], ':sectionID' => $_POST['sectionID']));
    $postCount = $sql -> rowCount();
    $string = "";
    $gmHandle = "";
    $gmImg = "";
    $thumbImg = "";

    if ($postCount > 0) {
      $results = $sql -> fetchAll();
      $offset = $_POST['offset'];
      $postID = $results[$offset][0];
      $sql = $dbh->prepare("SELECT postID, isForum, characterID, postDate, postText FROM posts WHERE sectionID = :sectionID AND questID = :questID AND postID " . $operator . " :postID ORDER BY postID " . $dir . " LIMIT 10");
      $sql -> execute(array(':sectionID' => $_POST['sectionID'], ':questID' => $_POST['questID'], ':postID' => $postID));
      $numCount = $sql -> rowCount();
      $string = "";
      while ($results = $sql -> fetch()) {
        $sqlDate = $results['postDate'];
        $date = date("D M j Y", strtotime($sqlDate)) . " at " . date("G:i:s T", strtotime($sqlDate));
        $string .= $results['postID'] . "&?";
        $string .= $date . "&?";
        $text = stripSlashes(html_entity_decode($results['postText'], ENT_QUOTES));
        //$text = $results['postText'];
        $offset = $_POST['offset'];
        $postID = $results[$offset][0];
        //echo $text;
        $pattern = "/\/msg \[(.+)\] (.+)<br>/";
        $message = array();
        if (strpos($text, "/msg") !== false) {
          $splitPMArray = explode("/msg", $text);
          $text = "";
          for ($i=0; $i<count($splitPMArray);$i++) {
            $subMsgArray = explode("<br>", $splitPMArray[$i]);
            for ($s=0; $s<count($subMsgArray); $s++) {
              $pattern = "/\[(.+)\]/";
              if (preg_match($pattern, $subMsgArray[$s])) {
                $query = $dbh -> prepare("SELECT characterID FROM posts WHERE postID = :postID");
                $query -> execute(array(':postID' => $postID));
                $characterID = $query -> fetch();
                if ($characterID[0] == 0) {
                  $query = $dbh -> prepare("SELECT gmID FROM quests WHERE questID = :questID");
                  $query -> execute(array(':questID' => $_POST['questID']));
                  $userID = $query -> fetch();
                } else {
                  $query = $dbh -> prepare("SELECT userID FROM questAssociations WHERE questID = :questID");
                  $query -> execute(array(':questID' => $_POST['questID']));
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
        $string .= $text . "&?";
        $string .= $results['characterID'] . "&?";
        if ($results['characterID'] == "0" && $gmHandle == "") {
          $query = $dbh->prepare("SELECT gmID FROM quests WHERE questID = :questID LIMIT 1");
          $query -> execute(array(':questID' => $_POST['questID']));
          $gmID = $query -> fetch();
          $query = $dbh->prepare("SELECT handle FROM users WHERE userID = :gmID");
          $query -> execute(array(':gmID' => $gmID[0]));
          $gmHandle = $query -> fetch();
          $query = $dbh->prepare("SELECT thumbPortrait FROM userDetails WHERE userID = :gmID");
          $query -> execute(array(':gmID' => $gmID[0]));
          $gmImg = $query -> fetch();
          if ($gmID[0] == $sessionData[1]) {
            $string .= "true&?";
          } else {
            $string .= "false&?";
          }
          $string .= $gmHandle[0] . "&?";
          $string .= "gm&?";
          $string .= $gmImg[0] . "&?";
          $string .= $gmID[0] . "|";
        } else if ($results['characterID'] == "0" && $gmHandle != "") {
          if ($gmID[0] == $sessionData[1]) {
            $string .= "true&?";
          } else {
            $string .= "false&?";
          }
          $string .= $gmHandle[0] . "&?";
          $string .= "gm&?";
          $string .= $gmImg[0] . "&?";
          $string .= $gmID[0] . "|";
        } else if ($results['isForum'] == "1") {
          $query = $dbh->prepare("SELECT handle FROM users WHERE userID = :userID");
          $query -> execute(array(':userID' => $results['characterID']));
          $userHandle = $query -> fetch();
          $query = $dbh->prepare("SELECT thumbPortrait FROM userDetails WHERE userID = :userID");
          $query -> execute(array(':userID' => $results['characterID']));
          $userImg = $query -> fetch();
          if ($results['characterID'] == $sessionData[1]) {
            $string .= "true&?";
          } else {
            $string .= "false&?";
          }
          $string .= $userHandle[0] . "&?pc&?";
          $string .= $userImg[0] . "&?null|";
        } else {
          $query = $dbh -> prepare("SELECT userID,characterName,thumbPortrait FROM characters WHERE characterID = :name");
          $query -> execute(array(':name' => $results['characterID']));
          $name = $query -> fetch();
          if ($name[0] == $sessionData[1]) {
            $string .= "true&?";
          } else {
            $string .= "false&?";
          }
          $string .= $name[1] . "&?" . "pc&?";
          $string .= $name[2] . "&?";
          $string .= "null|";
        }
      }
    }
    if ($postCount > 0 && $numCount > 0) {
      $sql = $dbh -> prepare("SELECT gmID FROM quests WHERE questID = :questID");
      $sql -> execute(array(':questID' => $_POST['questID']));
      $questID = $sql -> fetch();
      $isGM = false;
      if ($questID[0] == $sessionData[1]) {
        $isGM = true;
      }
      $dbh = null;
      header('HTTP/1.0 200 OK');
      echo $string . "&?" . $isGM;
    }
    else if (isset($_POST['isUpdate']) && ($_POST['isUpdate'] == "true") ) {
      $sql = $dbh -> prepare("SELECT gmID FROM quests WHERE questID = :questID");
      $sql -> execute(array(':questID' => $_POST['questID']));
      $questID = $sql -> fetch();
      $isGM = false;
      if ($questID[0] == $sessionData[1]) {
        $isGM = true;
      }
      $dbh = null;
      header('HTTP/1.0 204 No Content');
      echo $isGM;
    }
    else {
      $sql = $dbh -> prepare("SELECT gmID FROM quests WHERE questID = :questID");
      $sql -> execute(array(':questID' => $_POST['questID']));
      $questID = $sql -> fetch();
      $isGM = false;
      if ($questID[0] == $sessionData[1]) {
        $isGM = true;
      }
      $dbh = null;
      header('HTTP/1.0 404 Not Found');
      echo $isGM;
    }
    $dbh = null;
    break;
    case "declineQuest":
    $sql = $dbh->prepare("DELETE FROM alerts WHERE alertID = :alertID");
    $sql->bindParam(':alertID', $_POST['alertID'], PDO::PARAM_INT);
    $sql->execute();
    $sql = $dbh->prepare("DELETE FROM questAssociations WHERE alertID = :alertID AND userID = :userID");
    $sql->bindParam(':alertID', $_POST['alertID'], PDO::PARAM_INT);
    $sql->bindParam(':userID', $sessionData[1], PDO::PARAM_INT);
    $sql->execute();
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "checkIfAdmin":
    $sql = $dbh->prepare("SELECT gmID FROM quests WHERE questID = :questID");
    $sql->bindParam(':questID', $_POST['questID'], PDO::PARAM_INT);
    $sql->execute();
    $gmID = $sql -> fetch();
    $dbh = null;
    header('HTTP/1.0 200 OK');
    if ($sessionData[1] == $gmID[0]) {
      echo "true";
    } else {
      echo "false";
    }
    break;
    case "declineAndBlockQuest":
    $sql = $dbh->prepare("DELETE FROM alerts WHERE alertID = :alertID");
    $sql->bindParam(':alertID', $_POST['alertID'], PDO::PARAM_INT);
    $sql->execute();
    $sql = $dbh->prepare("DELETE FROM questAssociations WHERE alertID = :alertID AND userID = :userID");
    $sql->bindParam(':alertID', $_POST['alertID'], PDO::PARAM_INT);
    $sql->bindParam(':userID', $sessionData[1], PDO::PARAM_INT);
    $sql->execute();
    $sql = $dbh->prepare("INSERT INTO blocked (blockerID, blockedID, blockDate) VALUES (:blockerID, :blockedID, now())");
    $sql -> execute(array(':blockerID'=>$sessionData[1], ':blockedID'=>$_POST['senderID']));
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "acceptQuest":
    $sql = $dbh->prepare("DELETE FROM alerts WHERE alertID = :alertID");
    $sql->bindParam(':alertID', $_POST['alertID'], PDO::PARAM_INT);
    $sql->execute();
    $sql = $dbh->prepare("UPDATE questAssociations SET approved=1 WHERE alertID = :alertID");
    $sql->bindParam(':alertID', $_POST['alertID'], PDO::PARAM_INT);
    $sql->execute();
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "deleteQuest":
    $sql = $dbh -> prepare("SELECT gmID FROM quests WHERE questID = :questID");
    $sql -> bindParam(':questID', $_POST['questID']);
    $sql -> execute();
    $gmID = $sql -> fetch();
    if ($gmID[0] == $sessionData[1]) {
      $sql = $dbh -> prepare("SELECT associationID, alertID FROM questAssociations WHERE questID = :questID");
      $sql -> bindParam(':questID', $_POST['questID']);
      $sql -> execute();
      while ($results = $sql->fetch()) {
        if (!is_null($results['alertID'])) {
          $query = $dbh -> prepare("DELETE FROM alerts WHERE alertID = :alertID");
          $query -> bindParam(':alertID', $results['alertID']);
          $query -> execute();
        }
        $query = $dbh -> prepare("DELETE FROM questAssociations WHERE associationID = :associationID");
        $query -> bindParam(':associationID', $results['associationID']);
        $query -> execute();
      }
      $query = $dbh -> prepare("DELETE FROM quests WHERE questID = :questID");
      $query -> bindParam(':questID', $_POST['questID']);
      $query -> execute();
      $dbh = null;
      header('HTTP/1.0 200 OK');
    } else {
      $dbh = null;
      header('HTTP/1.0 401 Unauthorized');
    }
    break;
    case "getQuestCharacters":
    $sql = $dbh -> prepare("SELECT characterID FROM questAssociations WHERE userID = :userID AND questID = :questID");
    $sql -> bindParam(':userID', $sessionData[1]);
    $sql -> bindParam(':questID', $_POST['questID']);
    $sql -> execute();
    $authorized = $sql -> rowCount();
    $names = "";
    $resultsArray = array();
    if ($authorized >= 1) {
      while ($results = $sql -> fetch()) {
        $charID = $results['characterID'];
        if (is_null($charID)) {
          $names = $sessionData[3] . "&?";
          $names .= $charID . "&?";
          $names .= "|";
          array_push($resultsArray, $names);
        } else {
          $query = $dbh -> prepare("SELECT characterName FROM characters WHERE characterID = :characterID");
          $query -> bindParam(':characterID', $charID);
          $query -> execute();
          $handle = $query -> fetch();
          $names = $handle[0] . "&?";
          $names .= $charID . "&?";
          $names .= "|";
          array_push($resultsArray, $names);
        }
      }
      if (isset($_POST['postID'])) {
        $query = $dbh -> prepare("SELECT characterID FROM posts WHERE postID = :postID");
        $query -> bindParam(':postID', $_POST['postID']);
        $query -> execute();
        $matchedID = $query -> fetch();
        $fixedResults = array();
        $otherResults = array();
        for ($i=0; $i<count($resultsArray);$i++) {
          $subResults = explode("&?", $resultsArray[$i]);
          $oldID = substr($subResults[1], 0, strlen($subResults[1])-1);
          if ($oldID == $matchedID[0]) {
            array_push($fixedResults, $resultsArray[$i]);
          } else {
            array_push($otherResults, $resultsArray[$i]);
          }
        }
        $resultsArray = array_merge($fixedResults, $otherResults);
      } 
      $names = "";
      for ($s = 0; $s < count($resultsArray); $s++) {
        $names .= $resultsArray[$s];
      }
      $dbh = null;
      header('HTTP/1.0 200 OK');
      echo $names;
    } else {
      $dbh = null;
      header('HTTP/1.0 401 Unauthorized');
    }
    break;
    case "getQuestDetails":
    $query = $dbh -> prepare("SELECT title,descr,visible FROM quests WHERE questID = :questID");
    $query -> bindParam(':questID', $_POST['questID']);
    $query -> execute();
    $row = $query -> fetch();
    $string = $row['title'] . "|";
    $string .= $row['descr'] . "|";
    $string .= $row['visible'] . "|";
    $sql = $dbh -> prepare("SELECT characterID FROM questAssociations WHERE characterID IS NOT NULL AND questID = :questID");
    $sql -> bindParam(':questID', $_POST['questID']);
    $sql -> execute();
    while ($results = $sql -> fetch()) {
      $query = $dbh -> prepare("SELECT characterName, characterID, userID, thumbPortrait FROM characters WHERE characterID = :characterID");
      $query -> execute(array(':characterID' => $results['characterID']));
      while ($innerResults = $query -> fetch()) {
        $string .= $innerResults['characterName'] . "&?";
        $string .= $innerResults['characterID'] . "&?";
        $string .= $innerResults['thumbPortrait'] . "&?";
        $string .= $innerResults['userID'] . "&?";
        $query = $dbh -> prepare("SELECT handle FROM users WHERE userID = :userID");
        $query -> execute(array(':userID' => $innerResults['userID']));
        $user = $query -> fetch();
        $string .= $user[0] . "[&?]";
      }
    }
    $dbh = null;
    header('HTTP/1.0 200 OK');
    echo $string;
    break;
    case "checkEmailSettings":
    $sql = $dbh->prepare("SELECT emailID FROM emailPosts WHERE questID = :questID AND userID = :userID");
    $sql->bindParam(':questID', $_POST['questID'], PDO::PARAM_INT);
    $sql->bindParam(':userID', $sessionData[1], PDO::PARAM_INT);
    $sql->execute();
    if ($sql -> rowCount() >= 1) {
      $dbh = null;
      header('HTTP/1.0 200 OK');
    } else {
      $dbh = null;
      header('HTTP/1.0 404 Not Found');
    }
    break;
    case "updateEmailSettings":
      if ($_POST['activate'] == "true") {
        $sql = $dbh -> prepare("SELECT email FROM userDetails WHERE userID = :userID");
        $sql->bindParam(":userID", $sessionData[1], PDO::PARAM_INT);
        $sql -> execute();
        $results = $sql -> fetch();
        if ($results[0] == "none") {
          $dbh = null;
          header('HTTP/1.0 412 Precondition Failed');
        } else {
          $sql = $dbh -> prepare("INSERT INTO emailPosts (questID, userID) VALUES (:questID, :userID)");
          $sql->bindParam(":questID", $_POST['questID'], PDO::PARAM_INT);
          $sql->bindParam(":userID", $sessionData[1], PDO::PARAM_INT);
          $sql->execute();
          $dbh = null;
          header('HTTP/1.0 200 OK');
        }
      } else {
        $sql = $dbh -> prepare("DELETE FROM emailPosts WHERE userID = :userID AND questID = :questID");
        $sql->bindParam(":userID", $sessionData[1], PDO::PARAM_INT);
        $sql->bindParam(":questID", $_POST['questID'], PDO::PARAM_INT);
        $sql->execute();
        $dbh = null;
        header('HTTP/1.0 200 OK');
      }
    break;
  }
} else {
  header('HTTP/1.0 401 Unauthorized');
}