<?
session_start();
session_destroy();
header("Location: " . $BASE_HREF . "index.php");
exit;
?>