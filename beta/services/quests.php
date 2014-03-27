<?php
require('../php/helpers.php');
require('../php/DatabaseConnection.php');

if (!isset($_GET['request'])) {
  http_response_code(400);
} else {
  $session = $_SESSION['mongol'];
  $array = explode('|', $session);
  $userID = $array[1];
  $userName = $array[0];
  if (evaluateRequestParam('request', 'getQuests', 'GET')) {
    if (!isset($_SESSION['mongol'])) {
      http_response_code(413);
    } else {
      $db = new DatabaseConnection();
      $questData = $db -> fetchData('quests', ['questID', 'title', 'subtitle'], ['gmID'], [$userID]);
      
      $sqlString = 'SELECT questID FROM members WHERE userID=' . $userID;
      $statement = $db -> connection -> prepare($sqlString);
      $statement -> execute();
      $results = $statement -> fetchAll();
      $sqlString = 'SELECT title,subtitle,questID FROM quests WHERE ';

      for ($i=0; $i < count($results); $i++) {
        $sqlString .= 'questID=' . $results[$i][0];
        if ($i < count($results)-1) {
          $sqlString .= ' OR ';
        }
      }

      $statement = $db -> connection -> prepare($sqlString);
      $statement -> execute();
      $results = $statement -> fetchAll();
      http_response_code(200);
      header('Content-type: text/xml');
      echo '<userQuests>';
      echo '<gmQuests>';
      for ($i=0; $i < count($questData); $i++) {
        echo '<quest>';
        echo '<title>' . $questData[$i]['title'] . '</title>';
        echo '<subtitle>' . $questData[$i]['subtitle'] . '</subtitle>';
        echo '<questID>' . $questData[$i]['questID'] . '</questID>';
        echo '</quest>';
      }
      echo '</gmQuests>';
      echo '<pcQuests>';
      for ($i=0; $i < count($results); $i++) {
        echo '<quest>';
        echo '<title>' . $results[$i]['title'] . '</title>';
        echo '<subtitle>' . $results[$i]['subtitle'] . '</subtitle>';
        echo '<questID>' . $results[$i]['questID'] . '</questID>';
        echo '</quest>';
      }
      echo '</pcQuests>';
      echo '</userQuests>';
    }
  }
}

?>