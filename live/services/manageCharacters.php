<?php
session_start();
require_once("../../mongol_config.php");

if (isset($_SESSION['mongol'])) {
  require_once("../../mongol_connect.php");
  $sessionData = explode("|", $_SESSION['mongol']);
  
  switch($_POST['operation']) {
    case "new":
      $sql = $dbh -> prepare("SELECT characterID FROM characters WHERE characterName = :charName AND userID = :userID");
      $sql -> execute(array(':charName' => $_POST['name'], ':userID' => $sessionData[1]));
      $count = $sql -> rowCount();
      $thumbName = "img/portrait_thumb.jpg";
      $medName = "img/portrait_med.jpg";
      $bigName = "img/portrait_big.jpg";
      if ($count > 0) {
        $dbh = null;
        header('HTTP/1.0 409 Conflict');
        exit();
      }
      else if (file_exists($_FILES['image']['tmp_name']) || is_uploaded_file($_FILES['image']['tmp_name'])) {
        if ($_FILES['image']["error"] == UPLOAD_ERR_OK) {
          $dir1 = "users/" . substr($sessionData[0], 0, 1) . "/";
          $dir2 = $dir1 . substr($sessionData[0], 1, 1) . "/";
          $dir3 = $dir2 . $sessionData[1] . "/";
          $dir = $dir3 . $_POST['name'] . "/";
          if (!file_exists("../" . $dir1)) {
            mkdir("../" . $dir1);
            chmod($dir1, 0775);
          }
          if (!file_exists("../" . $dir2)) {
            mkdir("../" . $dir2);
            chmod($dir2, 0775);
          }
          if (!file_exists("../" . $dir3)) {
            mkdir("../" . $dir3);
            chmod($dir3, 0775);
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
        } else if ($_FILES['image']["error"] == UPLOAD_ERR_INI_SIZE) {
          $dbh = null;
          header('HTTP/1.0 413 Request Entity Too Large');
        }
      }
      if (!isset($_POST['description'])) {
        $_POST['description'] = "";
      }
      if (!isset($_POST['background'])) {
        $_POST['background'] = "";
      }
      if (!isset($_POST['privateGM'])) {
        $_POST['privateGM'] = "";
      }
      $query = $dbh -> prepare("INSERT INTO characterBios (characterAppearance, characterBackground, privateDetails, medPortrait, bigPortrait)
                                VALUES (:appear, :bg, :private, :med, :big)");
      $query -> execute(array(':appear'=> $_POST['description'], ':bg'=>$_POST['background'], ':private' => $_POST['privateGM'],
                              ':med'=>$medName, ':big' => $bigName));
      $sql = $dbh -> prepare("SELECT bioID FROM characterBios ORDER BY bioID DESC LIMIT 1");
      $sql -> execute();
      $bioID = $sql -> fetch();
      $query = $dbh -> prepare("INSERT INTO characters (characterName, thumbPortrait, bioID, userID) VALUES(:name, :thumb, :bio, :user)");
      $query -> execute(array(':name'=>$_POST['name'], ':thumb'=>$thumbName, ':bio'=>$bioID[0], ':user'=>$sessionData[1]));
      $dbh = null;
      header('HTTP/1.0 200 OK');
    break;
    case "listCharacters":
      $sql = $dbh -> prepare("SELECT characterID FROM characters WHERE userID = :userID");
      $sql -> execute(array(':userID' => $sessionData[1]));
      $count = $sql -> rowCount();
      if ($count > 0) {
        $query = "SELECT characterID, characterName, thumbPortrait FROM characters WHERE userID = " . $sessionData[1] . " ORDER BY characterName ASC";
        $string = "";
        foreach ($dbh->query($query) as $row) {
          $string .= $row['characterID'] . "&?";
          $string .= $row['characterName'] . "&?";
          $string .= $row['thumbPortrait'] . "|";
        }
        echo $string;
        $dbh = null;
        header('HTTP/1.0 200 OK');
      } else {
        $dbh = null;
        header('HTTP/1.0 404 Not Found');
      }
    break;
    case "getDetails":
      $sql = "SELECT characterName, bioID, userID FROM characters WHERE characterID = :charID";
      $query = $dbh -> prepare($sql);
      $query -> execute(array(':charID' => $_POST['characterID']));
      if ($query -> rowCount() > 0) {
        $characterStats = $query -> fetch();
        if ($characterStats[2] == $sessionData[1]) {
          $isOwner = 1;
        } else {
          $isOwner = 0;
        }
        $string = $isOwner . "&?";
        $string .= $characterStats[0] . "&?";
        $bioID = $characterStats[1];
        if ($isOwner == 1) {
          $bioSQL = "SELECT characterAppearance, characterBackground, medPortrait, bigPortrait, privateDetails FROM characterBios WHERE bioID = :bioID";
        } else {
          $bioSQL = "SELECT characterAppearance, characterBackground, medPortrait, bigPortrait FROM characterBios WHERE bioID = :bioID";
        }
        $queryBio = $dbh -> prepare($bioSQL);
        $queryBio -> execute(array(':bioID' => $bioID));
        $bioStats = $queryBio -> fetch();
        $string .= stripSlashes(html_entity_decode($bioStats[0], ENT_QUOTES)) . "&?";
        $string .= stripSlashes(html_entity_decode($bioStats[1], ENT_QUOTES)) . "&?";
        $string .= stripSlashes(html_entity_decode($bioStats[2], ENT_QUOTES)) . "&?";
        $string .= stripSlashes(html_entity_decode($bioStats[3], ENT_QUOTES));
        if ($isOwner == 1) {
          $string .= "&?" . stripSlashes(html_entity_decode($bioStats[4], ENT_QUOTES));
        }
        $dbh = null;
        header('HTTP/1.0 200 OK');
        echo $string;
      } else {
        $dbh = null;
        header('HTTP/1.0 404 Not Found');
      }
    break;
    case "update":
      $sql = $dbh -> prepare("UPDATE characters SET characterName = :charName WHERE userID = :userID AND characterID = :charID");
      $sql -> execute(array(':charName' => $_POST['name'], ':charID' => $_POST['charID'],':userID' => $sessionData[1]));
      $sql = $dbh -> prepare("SELECT bioID FROM characters WHERE characterID = :charID");
      $sql -> execute(array(':charID' => $_POST['charID']));
      $bioID = $sql -> fetch();
      $thumbName = "img/portrait_thumb.jpg";
      $medName = "img/portrait_med.jpg";
      $bigName = "img/portrait_big.jpg";
      $uploadedFile = false;
      if(file_exists($_FILES['image']['tmp_name']) || is_uploaded_file($_FILES['image']['tmp_name'])) {
        if ($_FILES['image']["error"] == UPLOAD_ERR_OK) {
          $uploadedFile = true;
          $dir1 = "users/" . substr($sessionData[0], 0, 1) . "/";
          $dir2 = $dir1 . substr($sessionData[0], 1, 1) . "/";
          $dir3 = $dir2 . $sessionData[1] . "/";
          $dir = $dir3 . $_POST['charID'] . "/";
          if (!file_exists("../" . $dir1)) {
            mkdir("../" . $dir1);
            chmod($dir1, 0775);
          }
          if (!file_exists("../" . $dir2)) {
            mkdir("../" . $dir2);
            chmod($dir2, 0775);
          }
          if (!file_exists("../" . $dir3)) {
            mkdir("../" . $dir3);
            chmod($dir3, 0775);
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
        } else if ($_FILES['image']["error"] == UPLOAD_ERR_INI_SIZE) {
          $dbh = null;
          header('HTTP/1.0 413 Request Entity Too Large');
        }
      }
      if ($uploadedFile == true) {
        $sql = $dbh -> prepare("UPDATE characterBios SET characterAppearance = :charAppear, characterBackground = :charBG,
                                privateDetails = :private, medPortrait = :med, bigPortrait = :big
                                WHERE bioID = :bioID");
        $sql -> execute(array(':charAppear' => $_POST['description'], ':charBG' => $_POST['background'],':private' => $_POST['privateGM'],
                              ':med' => $medName, ':big' => $bigName, ':bioID' => $bioID[0]));
        $sql = $dbh -> prepare("UPDATE characters SET thumbPortrait = :thumb WHERE characterID = :charID");
        $sql -> execute(array(':thumb' => $thumbName, ':charID' => $_POST['charID']));
      } else {
        $sql = $dbh -> prepare("UPDATE characterBios SET characterAppearance = :charAppear, characterBackground = :charBG,
                                privateDetails = :private WHERE bioID = :bioID");
        $sql -> execute(array(':charAppear' => $_POST['description'], ':charBG' => $_POST['background'],
                              ':private' => $_POST['privateGM'],':bioID' => $bioID[0]));
      }
      $dbh = null;
      header('HTTP/1.0 200 OK');
    break;
    case "deleteCharacter":
      $sql = $dbh -> prepare("SELECT characterID FROM characters WHERE characterID = :characterID AND userID = :userID");
      $sql -> execute(array(':characterID' => $_POST['characterID'], ':userID' => $sessionData[1]));
      $count = $sql -> rowCount();
      if ($count < 1) {
        $dbh = null;
        header('HTTP/1.0 401 Unauthorized');
      } else {
        $sql = $dbh -> prepare("SELECT questID FROM questAssociations WHERE characterID = :characterID");
        $sql -> execute(array(':characterID' => $_POST['characterID']));
        $count = $sql -> rowCount();
        if ($count > 0) {
          $questID = $sql -> fetch();
          $sql = $dbh -> prepare("SELECT title FROM quests WHERE questID = :questID");
          $sql -> execute(array(':questID' => $questID[0]));
          $title = $sql -> fetch();
          $dbh = null;
          header('HTTP/1.0 409 Conflict');
          echo $title[0];
        } else {
          $sql = $dbh -> prepare("DELETE FROM characters WHERE characterID = :characterID AND userID = :userID");
          $sql -> execute(array(':characterID' => $_POST['characterID'], ':userID' => $sessionData[1]));
          $dbh = null;
          header('HTTP/1.0 200 OK');
        }
      }
    break;
  }
} else {
  header('HTTP/1.0 401 Unauthorized');
}
?>