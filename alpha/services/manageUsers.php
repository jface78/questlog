<?php
session_start();
require_once("../../mongol_config.php");

if (isset($_SESSION['mongol'])) {
  require_once("../../mongol_connect.php");
  $sessionData = explode("|", $_SESSION['mongol']);
  
  switch($_POST['operation']) {
    case "updateSettings":
      $sql = $dbh -> prepare("UPDATE users SET handle = :name WHERE userID = :userID");
      $sql -> execute(array(':name' => $_POST['handle'],':userID' => $sessionData[1]));
      if ($_POST['soundEnabled'] == "false") {
        $_POST['soundEnabled'] = 0;
      } else {
        $_POST['soundEnabled'] = 1;
      }
      $sql = $dbh -> prepare("UPDATE settings SET soundEnabled = :soundEnabled WHERE userID = :userID");
      $sql -> execute(array(':soundEnabled' => $_POST['soundEnabled'],':userID' => $sessionData[1]));
      $sql = $dbh -> prepare("UPDATE userDetails SET email = :email WHERE userID = :userID");
      $sql -> execute(array(':email' => $_POST['email'],':userID' => $sessionData[1]));
      $uploadedFile = false;
      
      if(file_exists($_FILES['image']['tmp_name']) || is_uploaded_file($_FILES['image']['tmp_name'])) {
        if ($_FILES['image']["error"] == UPLOAD_ERR_OK) {
          $uploadedFile = true;
          $dir1 = "users/" . substr($sessionData[0], 0, 1) . "/";
          $dir2 = $dir1 . substr($sessionData[0], 1, 1) . "/";
          $dir = $dir2 . $sessionData[1] . "/";

          if (!file_exists("../" . $dir1)) {
            mkdir("../" . $dir1);
            chmod($dir1, 0775);
          }
          if (!file_exists("../" . $dir2)) {
            mkdir("../" . $dir2);
            chmod($dir2, 0775);
          }
          if (!file_exists("../" . $dir)) {
            mkdir("../" . $dir);
            chmod($dir, 0775);
          }
          $finfo = new finfo(FILEINFO_MIME);
          $uploadedFile = $_FILES['image']['tmp_name'];
          $type = $finfo->file($uploadedFile);
          $mime = substr($type, 0, strpos($type, ';'));
          if ($mime != "image/jpeg" && $mime != "image/gif" && $mime != "image/png") {
            $dbh = null;
            header('HTTP/1.0 412 Precondition Failed');
            exit();
          }
          switch($mime) {
            case "image/jpeg":
            $src = imagecreatefromjpeg($uploadedFile);
            $ext = ".jpg";
            break;
            case "image/png":
            $src = imagecreatefrompng($uploadedFile);
            $ext = ".png";
            break;
            case "image/gif":
            $src = imagecreatefromgif($uploadedFile);
            $ext = ".gif";
            break;
          }
          list($width,$height)=getimagesize($uploadedFile);
          $thumbWidth = 50;
          $medWidth = 150;
          $bigWidth = 500;

          $thumbHeight=($height/$width)*$thumbWidth;
          $medHeight=($height/$width)*$medWidth;
          $bigHeight=($height/$width)*$bigWidth;
        
          $thumbTmp = imagecreatetruecolor($thumbWidth,$thumbHeight);
          $medTmp = imagecreatetruecolor($medWidth,$medHeight);
          $bigTmp = imagecreatetruecolor($bigWidth,$bigHeight);
          
          imagecopyresampled($thumbTmp,$src,0,0,0,0,$thumbWidth,$thumbHeight,$width,$height);
          imagecopyresampled($medTmp,$src,0,0,0,0,$medWidth,$medHeight,$width,$height);
          imagecopyresampled($bigTmp,$src,0,0,0,0,$bigWidth,$bigHeight,$width,$height);

          $thumbName = $dir . "portrait_thumb" . $ext;
          $medName = $dir . "portrait_med" . $ext;
          $bigName = $dir . "portrait_big" . $ext;

          if (file_exists($thumbName)) {
            unlink($thumbName);
          }
          if (file_exists($medName)) {
            unlink($medName);
          }
          if (file_exists($bigName)) {
            unlink($bigName);
          }
          
          switch($mime) {
            case "image/jpeg":
            imagejpeg($thumbTmp, "../" . $thumbName, 100);
            imagejpeg($medTmp, "../" . $medName, 100);
            imagejpeg($bigTmp, "../" . $bigName, 100);
            break;
            case "image/png":
            $trans_color = imagecolortransparent ( $medTmp ); 
            $trans_index = imagecolorallocate ( $medTmp, $trans_color['red'], $trans_color['green'], $trans_color['blue'] ); 
            imagecolortransparent ( $thumbTmp, $trans_index ); 
            imagecolortransparent ( $medTmp, $trans_index );
            imagecolortransparent ( $bigTmp, $trans_index ); 
            imagepng($thumbTmp, "../" . $thumbName, 1);
            imagepng($medTmp, "../" . $medName, 1);
            imagepng($bigTmp, "../" . $bigName, 1);
            break;
            case "image/gif":
            $trans_color = imagecolortransparent ( $medTmp ); 
            $trans_index = imagecolorallocate ( $medTmp, $trans_color['red'], $trans_color['green'], $trans_color['blue'] ); 
            imagecolortransparent ( $thumbTmp, $trans_index ); 
            imagecolortransparent ( $medTmp, $trans_index );
            imagecolortransparent ( $bigTmp, $trans_index ); 
            imagegif($thumbTmp, "../" . $thumbName, 100);
            imagegif($medTmp, "../" . $medName, 100);
            imagegif($bigTmp, "../" . $bigName, 100);
            break;
          }
          
          imagedestroy($src);
          imagedestroy($thumbTmp);
          imagedestroy($medTmp);
          imagedestroy($bigTmp);
          
          $sql = $dbh -> prepare("UPDATE userDetails SET thumbPortrait = :thumb, medPortrait = :med,
                                  bigPortrait = :big WHERE userID = :userID");
          $sql -> execute(array(':thumb' => $thumbName, ':med' => $medName, ':big' => $bigName, ':userID' => $sessionData[1]));
        } else if ($_FILES['image']["error"] == UPLOAD_ERR_INI_SIZE) {
          $dbh = null;
          header('HTTP/1.0 413 Request Entity Too Large');
        }
      }
      $dbh = null;
      header('HTTP/1.0 200 OK');
    break;
    case "getPortrait":
      $sql = $dbh -> prepare("SELECT medPortrait FROM userDetails WHERE userID = :userID");
      $sql -> execute(array(':userID' => $sessionData[1]));
      $count = $sql -> rowCount();
      if ($count == 0) {
        header('HTTP/1.0 404 Not Found');
      } else {
        $results = $sql -> fetch();
        header('HTTP 1.0 200 OK');
        echo $results[0];
      } 
    break;
    case "getEmail":
      $sql = $dbh -> prepare("SELECT email FROM userDetails WHERE userID = :userID");
      $sql -> execute(array(':userID' => $sessionData[1]));
      $results = $sql -> fetch();
      if ($results[0] == "none") {
        header('HTTP/1.0 404 Not Found');
      } else {
        header('HTTP/1.0 200 OK');
        echo $results[0];
      }
    break;
    case "checkHandles":
      $sql = $dbh -> prepare("SELECT userID FROM users WHERE handle = :handle");
      $sql -> execute(array(':handle'=>$_POST['handle']));
      if ($sql -> rowCount() > 0) {
        $dbh = null;
        header('HTTP/1.0 409 Conflict');
      } else {
        $sql = $dbh -> prepare("UPDATE users SET handle = :handle WHERE userID = :userID");
        $sql -> execute(array(':handle'=>$_POST['handle'], ':userID'=>$sessionData[1]));
        $sql = $dbh -> prepare("INSERT INTO userDetails (userID) VALUES(:userID)");
        $sql -> execute(array(':userID' => $sessionData[1]));
        $sessionArray = explode("|", $_SESSION['mongol']);
        $_SESSION['mongol'] = $sessionArray[0] . "|" . $sessionArray[1] . "|" . $sessionArray[2] . "|" . $_POST['handle'];
        $dbh = null;
        header('HTTP/1.0 200 OK');
      }
    break;
  }
} else {
  header('HTTP/1.0 401 Unauthorized');
}
?>