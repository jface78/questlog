<?php
session_start();
require_once("../../mongol_config.php");

switch($_POST['operation']) {
  case "sendBug":
  if (isset($_SESSION['mongol'])) {
    $sessionData = explode("|", $_SESSION['mongol']);
  }
  if ($_POST['issue'] == "" || $_POST['os'] == "" || $_POST['browser'] == "" || $_POST['descr'] == "") {
    header("HTTP/1.0 400 Bad Request");
  } else {
    $to      = 'jface@jonathanface.com';
    $subject = 'QuestLog Bug Report';
    $message = 'Issue: ' . $_POST['issue'] . "\r\n";
    $message .= 'OS: ' . $_POST['os'] . "\r\n";
    $message .= 'Browser: ' . $_POST['browser'] . "\r\n";
    $message .= 'Description: ' . $_POST['descr'] . "\r\n";
    if (isset($sessionData)) {
    //$openID . "|" . $userID . "|" . $userName . "|" . $handle;
      $message .= 'Reported by: ' . $sessionData[3] . ' (' . $sessionData[2] . ')' . "\r\n";
    }
    $headers = 'From: bugs@questlog.org' . "\r\n" .
      'Reply-To: no-reply@questlog.org' . "\r\n" .
      'X-Mailer: PHP/' . phpversion();
    if (mail($to, $subject, $message, $headers)) {
      header("HTTP/1.0 200 OK");
    } else {
      header("HTTP/1.0 500 Internal Server Error");
    }
  }
  break;
}
?>