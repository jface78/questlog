<?php
  define('THEME_URL', '../css/themes/');
  
  $dirs = scandir(THEME_URL);
  $xml = '<themeList>';
  for ($i=0; $i < count($dirs); $i++) {
    if ($dirs[$i] != '.' && $dirs[$i] != '..') {
      $xml .= '<theme>' . $dirs[$i] . '</theme>';
    }
  }
  $xml .= '</themeList>';
  echo $xml;
?>