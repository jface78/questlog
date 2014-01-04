<?php
session_start();
require_once("../../mongol_config.php");

if (isset($_SESSION['mongol'])) {
  require_once("../../mongol_connect.php");
  $sessionData = explode("|", $_SESSION['mongol']);
  switch($_POST['operation']) {
    case "new":
    $sql = $dbh -> prepare("SELECT windowID FROM windows WHERE dcbID = :dcbID AND userID = :userID");
    $sql -> execute(array(':dcbID' => $_POST['dcbID'], ':userID' => $sessionData[1]));
    $count = $sql -> rowCount();
    if (!$count) {
      if ($_POST['centered'] == "true") {
        $_POST['centered'] = 1;
      } else {
        $_POST['centered'] = 0;
      }
      if ($_POST['isMaximized'] == "true") {
        $_POST['isMaximized'] = 1;
      } else {
        $_POST['isMaximized'] = 0;
      }
      if ($_POST['isMinimized'] == "true") {
        $_POST['isMinimized'] = 1;
      } else {
        $_POST['isMinimized'] = 0;
      }
      if ($_POST['logoutSafe'] == "true") {
        $_POST['logoutSafe'] = 1;
      } else {
        $_POST['logoutSafe'] = 0;
      }

      $sql = "INSERT INTO windows (dcbID, userID, width, height, centered, leftPx, topPx, title,
                                   url, background, isMaximized, isMinimized, logoutSafe) VALUES
                                  (:dcbID, :userID, :width, :height, :centered, :left, :top,
                                   :title, :url, :background, :isMaximized, :isMinimized, :logoutSafe)";
      $query = $dbh->prepare($sql);
      $query->execute(array(':dcbID'=>$_POST['dcbID'], ':userID'=>$sessionData[1], ':width'=>urldecode($_POST['width']),
                            ':height'=>urldecode($_POST['height']), ':centered'=>$_POST['centered'],
                            ':left'=>urldecode($_POST['left']), ':top'=>urldecode($_POST['top']),
                            ':title'=>$_POST['title'], ':url'=> $_POST['url'],
                            ':background'=>urldecode($_POST['background']), ':isMaximized'=>$_POST['isMaximized'],
                            ':isMinimized'=>$_POST['isMinimized'], ':logoutSafe'=>$_POST['logoutSafe']));
    }
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "close":
    $sql = $dbh -> prepare("DELETE FROM windows WHERE userID = :userID AND dcbID = :dcbID");
    $sql -> execute(array(':dcbID' => $_POST['dcbID'], ':userID' => $sessionData[1]));
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "move":
    $sql = $dbh -> prepare("UPDATE windows SET centered = 0, leftPx = :left, topPx = :top WHERE userID = :userID AND dcbID = :dcbID");
    $sql -> execute(array(':left' => urldecode($_POST['left']), ':top' => urldecode($_POST['top']), ':dcbID' => $_POST['dcbID'], ':userID' => $sessionData[1]));
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "resize":
    $sql = $dbh -> prepare("UPDATE windows SET width = :width, height = :height WHERE userID = :userID AND dcbID = :dcbID");
    $sql -> execute(array(':width' => urldecode($_POST['width']), ':height' => urldecode($_POST['height']), ':dcbID' => $_POST['dcbID'], ':userID' => $sessionData[1]));
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "minimize":
    $sql = $dbh -> prepare("UPDATE windows SET isMinimized = :isMin WHERE userID = :userID AND dcbID = :dcbID");
    $sql -> execute(array(':isMin' => 1, ':dcbID' => $_POST['dcbID'], ':userID' => $sessionData[1]));
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "maximize":
    $sql = $dbh -> prepare("UPDATE windows SET isMaximized = :isMax WHERE userID = :userID AND dcbID = :dcbID");
    $sql -> execute(array(':isMax' => 1, ':dcbID' => $_POST['dcbID'], ':userID' => $sessionData[1]));
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "restore":
    $sql = $dbh -> prepare("UPDATE windows SET isMaximized = :isMax, isMinimized = :isMin WHERE userID = :userID AND dcbID = :dcbID");
    $sql -> execute(array(':isMax' => $_POST['isMaximized'], ':isMin' => $_POST['isMinimized'], ':dcbID' => $_POST['id'], ':userID' => $sessionData[1]));
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "updateID":
    $sql = $dbh -> prepare("UPDATE windows SET dcbID = :newID WHERE userID = :userID AND dcbID = :oldID");
    $sql -> execute(array(':newID' => $_POST['newID'], ':userID' => $sessionData[1], ':oldID' => $_POST['oldID']));
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
    case "title":
    $sql = $dbh -> prepare("UPDATE windows SET title = :title WHERE userID = :userID AND dcbID = :dcbID");
    $sql -> execute(array(':title' => $_POST['title'], ':userID' => $sessionData[1], ':dcbID' => $_POST['dcbID']));
    $dbh = null;
    header('HTTP/1.0 200 OK');
    break;
  }
} else {
  header('HTTP/1.0 401 Unauthorized');
}