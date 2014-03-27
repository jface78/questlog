<?php
session_start();
function better_crypt($input, $rounds = 7) {
  $salt = "";
  $saltChars = array_merge(range('A','Z'), range('a','z'), range(0,9));
  for($i=0; $i < 22; $i++) {
    $salt .= $saltChars[array_rand($saltChars)];
  }
  return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
}

function evaluateRequestParam($param, $checkedValue, $type) {
  switch($type) {
    case 'GET':
      if (isset($_GET[$param])) {
        return $_GET[$param] == $checkedValue ? true : false;
      } else {
        return false;
      }
      break;
    case 'POST':
      if (isset($_POST[$param])) {
        return $_POST[$param] == $checkedValue ? true : false;
      } else {
        return false;
      }
      break;
    case 'PUT':
      if (isset($_PUT[$param])) {
        return $_PUT[$param] == $checkedValue ? true : false;
      } else {
        return false;
      }
      break;
  }
}
?>