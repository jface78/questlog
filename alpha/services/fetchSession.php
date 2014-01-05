<?php
session_start();
if (isset($_SESSION['mongol'])) {
  $sessionArray = explode("|", $_SESSION['mongol']);
  header('HTTP/1.0 200 OK');
  //$_SESSION['mongol'] = $_POST['openID'] . "|" . $userID . "|" . $userName . "|" . $handle;
  echo $sessionArray[0] . "&" . $sessionArray[1] . "&" . $sessionArray[2] . "&" . $sessionArray[3];
} else {
  header('HTTP/1.0 404 Not Found');
}
?>