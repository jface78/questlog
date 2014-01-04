<?php
session_start();
require_once("../../mongol_config.php");

$numbers = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
$letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M",
              "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
$both = array_merge($numbers, $letters);
$arrayPos = rand(0, count($both)-1);
$random = $both[$arrayPos];
if (in_array($random, $numbers, true)) {
  $character = "number";
} else {
  $character = "letter";
}
?>

<script type="text/javascript">
function submitLogin(type, dcbID) {
  var x = window.width/2 - 350;
  var y = window.height/2 - 250;
  var url = "<?php echo BASE_HREF;?>" + "services/handleLogin.php?login=true&type="+type+"&dcbID="+dcbID;
  var newWin = window.open(url,'name','height=500,width=700,left=' + x + ',top=' + y);
  if (window.focus){
    newWin.focus()
  }
}


$(document).ready(function() {
  var topDivID = generateDivID("signinParent");
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  
  $(document.getElementById("googleClick")).click(function() {
    submitLogin("google", dcb.id);
  });
  $(document.getElementById("yahooClick")).click(function() {
    submitLogin("yahoo", dcb.id);
  });
  $(document.getElementById("twitterClick")).click(function() {
    submitLogin("twitter", dcb.id);
  });
  $(document.getElementById('auth-loginlink')).click(function(){
    $(FB).data("dcb", dcb);
    FB.login();
  });
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
});
</script>

<link rel="stylesheet" href="css/signin.css" type="text/css">
<div id="signinParent" style="width:100%;height:100%;overflow:hidden;">
<div id="dumbassMsg" style="text-align:center;visibility:hidden;"><b>You're already logged in.</b></div><br />
<div class="signinMain" id="signinMain">
  Today's login is brought to you by the following corporations... and the <?php echo $character . " " . $random;?>.<br /><br />
<div class="loginButton">
<a href="#" id="googleClick"><img src="img/google.png" alt="Sign in with Google"></a>
</div>
<div class="loginButton">
<a href="#" id="yahooClick"><img src="img/yahoo.png" alt="Sign in with Yahoo"></a>
</div><br />
<div class="loginButton">
<a href="#" id="auth-loginlink"><img src="img/facebook.png" alt="Sign in with Facebook"></a>
</div>
<div class="loginButton" style="visibility:hidden">
<a href="#" id="twitterClick"><img src="img/twitter.png" alt="Sign in with Twitter"></a>
</div><br />
(choose a login method)
</div>
</div>
<?php
if (isset($_SESSION['mongol'])) {
  echo "<script type=\"text/javascript\">";
  echo "$(document.getElementById('signinMain')).css('visibility', 'hidden');";
  echo "$(document.getElementById('dumbassMsg')).css('visibility', 'visible');";
  echo "</script>";
}
?>