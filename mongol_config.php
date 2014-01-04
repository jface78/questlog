<?php

/**
 * @file
 * A single location to store configuration.
 */
define("DB_HOST", "localhost");

if ($_SERVER['SERVER_NAME'] == "localhost") {
  define('BASE_HREF', 'http://localhost/mongol/');
  define("DB_USER", "root");
  define("DB_PASS", "liches");
  define("DB_NAME", "sidetreks");
} else {
  define('BASE_HREF', 'http://questlog.org/');
  define("DB_USER", "mongol");
  define("DB_PASS", "neic8aiJaev9");
  define("DB_NAME", "mongol");
}
define('FB_ID', '296707567108856');
define('CONSUMER_KEY', 'H4c6VmprDxqu36Di9WWQoQ');
define('CONSUMER_SECRET', 'kmczB3miYIxzE0ImIvguv3YmaQDHwZlhDAPsgm8');
define('OAUTH_CALLBACK', BASE_HREF . 'services/handleLogin.php?twitter=true');




?>
