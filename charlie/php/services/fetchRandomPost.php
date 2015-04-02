<?php
include '../../../questlog_credentials.php';
include '../utils.php';

try {
  $json_array = [];
  $dbh = new PDO('mysql:host=' .DB_HOST . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
  $query = 'SELECT qid,uid,quest_name FROM quests WHERE quest_status < 4';
  $sth = $dbh -> prepare($query);
  $sth -> execute();
  $quests = $sth -> fetchAll();
  $random = rand(0, count($quests)-1);
  $json_array['name'] = $quests[$random]['quest_name'];
  $json_array['gm_id'] = $quests[$random]['uid'];
  $json_array['quest_id'] = $quests[$random]['qid'];
  $query = 'SELECT count(pid) FROM posts WHERE qid=:qid';
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':qid' => $json_array['quest_id']));
  $json_array['total_posts'] = $sth -> fetch()[0];
  $json_array['post_number'] = rand(0, $json_array['total_posts']-1);
  $query = 'SELECT pid,cid,post_text,timestamp FROM posts WHERE qid=:qid LIMIT 1 OFFSET ' . $json_array['post_number'];
  $sth = $dbh -> prepare($query);
  $sth -> execute(array(':qid' => $json_array['quest_id']));
  $post = $sth -> fetch();
  $json_array['character_id'] = $post['cid'];
  $json_array['post_id'] = $post['pid'];
  $json_array['text'] = $post['post_text'];
  $json_array['date'] = strtotime($post['timestamp']);
  if ($json_array['character_id'] == 0) {
    $query = 'SELECT login_name from users WHERE uid=:uid';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':uid' => $json_array['gm_id']));
    $json_array['poster_name'] = $sth -> fetch()[0] . ' - GameMaster';
  } else {
    $query = 'SELECT char_name from characters WHERE cid=:cid';
    $sth = $dbh -> prepare($query);
    $sth -> execute(array(':cid' => $json_array['character_id']));
    $json_array['poster_name'] = $sth -> fetch()[0];
  }
  header("Content-Type: application/json");
  echo json_encode($json_array);
  $dbh = null;
} catch(PDOException $error) {
  header('HTTP/1.1 500 Internal Server Error');
  echo $error;
}
?>