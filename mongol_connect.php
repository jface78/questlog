<?php
try {
  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
  if (!isset($dbh)) {
    $dbh = new PDO(
      $dsn,
      DB_USER,
      DB_PASS,
      array(PDO::ATTR_PERSISTENT => false)
    );
  }
  $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>