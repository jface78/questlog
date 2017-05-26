<?php

function hashPasswd($username, $passwd) {
  $hash_1 = bin2hex( mhash(MHASH_RIPEMD160, $passwd . $username) );
  $hash_2 = bin2hex( mhash(MHASH_RIPEMD160, $passwd . $hash_1) );
  return($hash_2);
}
?>