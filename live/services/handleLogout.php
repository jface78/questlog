<?php
session_start();
session_destroy();
if ( isset( $_COOKIE["mongol"] ) ) {
  setcookie(session_name(), '', time()-3600, '/');
}
?>