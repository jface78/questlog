<?php
session_start();
require_once("../../mongol_config.php");
include("twitter/EpiCurl.php");
include("twitter/EpiOAuth.php");
include("twitter/EpiTwitter.php");
require_once("lightopenid/openid.php");



function updateWithJS($id, $name) {
  echo "<script type='text/javascript'>";
  echo "var dcb = window.opener.getDCBFromID('" . $_SESSION['mongol_dcbTmp'] . "');";
  session_destroy();
  echo "dcb.close();";
  echo "window.opener.validateAccount('" . $id . "', '" . $name . "');";
  echo "self.close();";
  echo "</script>";
  
}

if ($_GET['login'] == true) {
  $_SESSION['mongol_dcbTmp'] = $_GET['dcbID'];
  if ($_GET['type'] == "twitter") {
    $twitterObj = new EpiTwitter(CONSUMER_KEY, CONSUMER_SECRET);
    $authenticateURL = $twitterObj->getAuthenticateUrl();
    header("Location: " . $authenticateURL);
  } else {
    $openid = new LightOpenID(BASE_HREF . 'services/handleLogin.php?login=true&type=' . $_GET['type'] . '&dcbID=' . $_GET['dcbID']);
    if(!$openid->mode) {
      if(isset($_GET['login'])) { 
        switch($_GET['type']) {
          case "google":
          $url = "https://www.google.com/accounts/o8/id";
          $openid->required = array('namePerson/first'); 
          break;
          case "yahoo":
          $url = "https://me.yahoo.com";
          $openid->optional = array('namePerson/friendly');
          break;
        }
        $openid->identity = $url;
        header('Location: ' . $openid->authUrl());
      }
    } else {
      if ($openid -> validate() !== false) {
        $id = $openid -> identity;
        $userDetails = $openid->getAttributes();
        if ($_GET['type'] == "yahoo") {
          $name = $userDetails["namePerson/friendly"];
        } else {
          $name = $userDetails["namePerson/first"];
        }
        updateWithJS(md5($id), $name);
      }
    }
  }
}
else if (isset($_GET['oauth_token'])) {
  $twitterObj = new EpiTwitter(CONSUMER_KEY, CONSUMER_SECRET);
  $twitterObj->setToken($_GET['oauth_token']);
  $token = $twitterObj->getAccessToken();
  $twitterObj->setToken($token->oauth_token, $token->oauth_token_secret);
  $userdata = $twitterObj->get_accountVerify_credentials();
  updateWithJS($userdata->id, $userdata->name);
  exit();
}
?>