<?php
$number = $_POST['number'];
$type = $_POST['type'];
$modifier = $_POST['modifier'];
$result = 0;
for ($i = 0; $i < $number; $i++) {
  $result += rand(1, $type);
}
$result .= "&" . $modifier;
header('HTTP/1.0 200 OK');
print($result);
?>