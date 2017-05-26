<?php
define('SESSION_TIMEOUT', 3600);

function checkSession() {
  if ((isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) || !isset($_SESSION['last_activity'])) {
    killSession();
    return false;
  } else {
    $_SESSION['last_activity'] = time();
    return true;
  }
}

function killSession() {
  session_unset();
}

function hashPasswd($username, $passwd) {
  $hash_1 = bin2hex( mhash(MHASH_RIPEMD160, $passwd . $username) );
  $hash_2 = bin2hex( mhash(MHASH_RIPEMD160, $passwd . $hash_1) );
  return($hash_2);
}
?>