<?php
session_start();
require_once("../../mongol_config.php");

if (isset($_SESSION['mongol'])) {
  require_once("../../mongol_connect.php");
  $sessionData = explode("|", $_SESSION['mongol']);
  switch($_POST['operation']) {
    case "checkAlerts":
    $query = "SELECT alertID FROM alerts WHERE userID = " . $sessionData[1];
    $sql = $dbh -> prepare("SELECT alertID FROM alerts WHERE userID = :userID AND viewed = 0");
    $sql -> execute(array(':userID' => $sessionData[1]));
    $alertCount = $sql -> rowCount();
    if ($alertCount > 0) {
      $string = "";
      foreach($dbh -> query($query) as $row) {
        $string .= $row['alertID'] . "|";
      }
      $dbh = null;
      header('HTTP/1.0 200 OK');
      echo $string;
    } else {
      $dbh = null;
      header('HTTP/1.0 404 Not Found');
    }
    break;
    case "getContent":
    $sql = $dbh -> prepare("SELECT dateSent, message, senderID FROM alerts WHERE alertID = :alertID");
    $sql -> execute(array(':alertID' => $_POST['alertID']));
    $resultsArray = $sql -> fetch();
    $dbh = null;
    header('HTTP/1.0 200 OK');
    echo $resultsArray[0] . "|" . urldecode($resultsArray[1]) . "|" . $resultsArray[2];
    break;
  }
} else {
  header('HTTP/1.0 403 Forbidden');
}
?>
