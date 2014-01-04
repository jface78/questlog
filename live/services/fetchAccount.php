<?php
session_start();
require_once("../../mongol_config.php");
require_once("../../mongol_connect.php");

//$openID = md5($_POST['openID']);
$openID = $_POST['openID'];
$sql = $dbh -> prepare("SELECT userID FROM users WHERE openID = :openID");
$sql -> execute(array(':openID' => $openID));
$count = $sql -> rowCount();

if (!$count) {
  $sql = "INSERT INTO users (openID, name, joinDate) VALUES (:openID, :name, now())";
  $query = $dbh->prepare($sql);
  $query->execute(array(':openID'=>$openID, ':name'=>$_POST['name']));
  $sql = $dbh -> prepare("SELECT userID FROM users WHERE openID = :openID");
  $sql -> execute(array(':openID' => $openID));
  $userID = $sql -> fetch();
  $sql = "INSERT INTO settings (userID) VALUES (:userID)";
  $query = $dbh->prepare($sql);
  $query->execute(array(':userID'=>$userID[0]));
  $ip = $_SERVER['REMOTE_ADDR'];
  $host = gethostbyaddr($ip);
  $ua = getBrowser();
  $sql = "INSERT INTO userDetails (userID, lastLoggedTime, ip, host, browser, os, resolution)
                           VALUES (:userID, now(), :ip, :host, :browser, :os, :resolution)";
  $query = $dbh->prepare($sql);
  $query->execute(array(':userID'=>$userID[0], ':ip' => $ip, ':host' => $host,
                        ':browser' => addSlashes($ua['name'] . " " . $ua['version']),
                        ':os' => $ua['platform'], ':resolution' => $_POST['resolution']));
  $_SESSION['mongol'] = $openID . "|" . $userID[0] . "|" . $_POST['name'];
  $firstChar = substr($openID, 0, 1);
  $secondChar = substr($openID, 1, 1);
  if (!file_exists("../users/" . $firstChar . "/")) {
    mkdir("../users/" . $firstChar . "/");
    chmod("../users/" . $firstChar . "/", 0775);
  }
  if (!file_exists("../users/" . $firstChar . "/" . $secondChar . "/")) {
    mkdir("../users/" . $firstChar . "/" . $secondChar . "/");
    chmod("../users/" . $firstChar . "/" . $secondChar . "/", 0775);
  }
  mkdir("../users/" . $firstChar . "/" . $secondChar . "/" . $userID[0] . "/");
  chmod("../users/" . $firstChar . "/" . $secondChar . "/" . $userID[0] . "/", 0775);
  $dbh = null;
  header('HTTP/1.0 404 Not Found');
  echo $userID[0] . "&" . $_POST['name'];
} else {
  $sql = $dbh -> prepare("SELECT userID, name, handle FROM users WHERE openID = :openID");
  $sql -> execute(array(':openID' => $openID));
  $details = $sql -> fetch();
  $userID = $details[0];
  $userName = $details[1];
  $handle = $details[2];
  $ip = $_SERVER['REMOTE_ADDR'];
  $host = gethostbyaddr($ip);
  $ua = getBrowser();
  $sql = $dbh -> prepare("UPDATE userDetails SET lastLoggedTime=now(), ip=:ip, host=:host,
                          browser=:browser, os=:os, resolution=:resolution WHERE userID= :userID");
  $sql -> execute(array(':ip' => $ip, ':host' => $host, ':browser' => addSlashes($ua['name'] . " " . $ua['version']),
                        ':os' => $ua['platform'], ':resolution' => $_POST['resolution'], ':userID' => $userID));
  $_SESSION['mongol'] = $openID . "|" . $userID . "|" . $userName . "|" . $handle;
  $sql = $dbh -> prepare("SELECT windowID FROM windows WHERE userID = :userID");
  $sql -> execute(array(':userID' => $userID));
  $winCount = $sql -> rowCount();
  $sql = $dbh -> prepare("SELECT soundEnabled FROM settings WHERE userID = :userID");
  $sql -> execute(array(':userID' => $userID));
  $sound = $sql -> fetch();
  $string = $sound[0] . "|";
  $query = "SELECT * FROM windows WHERE userID = " . $userID;
  if ($winCount > 0) {
    foreach ($dbh->query($query) as $row) {
      $string .= $row['dcbID'] . "&";
      $string .= $row['width'] . "&";
      $string .= $row['height'] . "&";
      $string .= $row['centered'] . "&";
      $string .= $row['leftPx'] . "&";
      $string .= $row['topPx'] . "&";
      $string .= $row['title'] . "&";
      $string .= $row['url'] . "&";
      $string .= $row['background'] . "&";
      $string .= $row['isMinimized'] . "&";
      $string .= $row['isMaximized'] . "&";
      $string .= $row['logoutSafe'] . "|";
    }
  }
  $dbh = null;
  header('HTTP/1.0 200 OK');
  echo $userID[0] . "&" . $userName . "&" . $handle . "|" . $string;
}
function getBrowser() {
  $u_agent = $_SERVER['HTTP_USER_AGENT']; 
  $bname = 'Unknown';
  $platform = 'Unknown';
  $version= "";
  //First get the platform?
  if (preg_match('/linux/i', $u_agent)) {
    $platform = 'linux';
  }
  else if (preg_match('/macintosh|mac os x/i', $u_agent)) {
    $platform = 'mac';
  }
  else if (preg_match('/windows|win32/i', $u_agent)) {
    $platform = 'windows';
  }  
  // Next get the name of the useragent yes seperately and for good reason
  if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) { 
    $bname = 'Internet Explorer'; 
    $ub = "MSIE"; 
  } 
  else if(preg_match('/Firefox/i',$u_agent)) { 
    $bname = 'Mozilla Firefox'; 
    $ub = "Firefox"; 
  } 
  else if(preg_match('/Chrome/i',$u_agent)) { 
    $bname = 'Google Chrome'; 
    $ub = "Chrome"; 
  } 
  else if(preg_match('/Safari/i',$u_agent)) { 
    $bname = 'Apple Safari'; 
    $ub = "Safari"; 
  } 
  else if(preg_match('/Opera/i',$u_agent)) { 
    $bname = 'Opera'; 
    $ub = "Opera"; 
  } 
  else if(preg_match('/Netscape/i',$u_agent)) { 
    $bname = 'Netscape'; 
    $ub = "Netscape"; 
  } 
    
  // finally get the correct version number
  $known = array('Version', $ub, 'other');
  $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
  if (!preg_match_all($pattern, $u_agent, $matches)) {
    // we have no matching number just continue
  }
    
  // see how many we have
  $i = count($matches['browser']);
  if ($i != 1) {
    //we will have two since we are not using 'other' argument yet
    //see if version is before or after the name
    if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
      $version= $matches['version'][0];
    } else {
      $version= $matches['version'][1];
    }
  } else {
    $version= $matches['version'][0];
  } 
  // check if we have a number
  if ($version==null || $version=="") {
    $version="?";
  } 
  return array(
    'userAgent' => $u_agent,
    'name'      => $bname,
    'version'   => $version,
    'platform'  => $platform,
    'pattern'    => $pattern
  );
}
?>