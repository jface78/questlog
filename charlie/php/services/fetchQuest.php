<?php
session_start();
error_reporting(E_ALL);
include('../../../../questlog_credentials.php');

// session stuff should go here later

if (empty($_GET['questID']) || !is_numeric($_GET['questID'])) {
  http_response_code(400);
  exit();
}

if (empty($_GET['order'])) {
  $postOrder = 'DESC';
} else if (strtoupper($_GET['order']) != 'ASC' && strtoupper($_GET['order']) != 'DESC') {
  http_response_code(400);
  exit();
} else {
  $postOrder = strtoupper($_GET['order']);
}

try {
  $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
  $json_array = [];
  $query = 'SELECT quest_name FROM quests WHERE qid = :questID';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':questID' => $_GET['questID']));
  $row = $sth -> fetch();

  $json_array['title'] = $row['quest_name'];
  $json_array['questID'] = $_GET['questID'];
  //$json_array['posts'] = array();
  $query = 'SELECT pid,timestamp,post_text FROM posts WHERE qid=:questID ORDER BY timestamp ' . $postOrder;
  if (!empty($_GET['limit']) && is_numeric($_GET['limit'])) {
    $query .= ' LIMIT ' . $_GET['limit'];
  }
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':questID' => $_GET['questID']));
  $results = $sth -> fetchAll();
  $index = 0;
  foreach($results as $row) {
    $json_array['posts'][$index] = array();
    $json_array['posts'][$index]['id'] = $row['pid'];
    $json_array['posts'][$index]['timestamp'] = $row['timestamp'];
    $json_array['posts'][$index]['text'] = $row['post_text'];
    
    $index++;
  }
  $dbh = null;
  header('Content-Type: application/json');
  echo json_encode($json_array);
  http_response_code(200);
} catch(PDOException $error) {
  echo $error->getMessage();
  http_response_code(500);
}
?>