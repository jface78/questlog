<?php
session_start();
require_once("../mongol_config.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta charset="utf-8">
<meta name="description" content="Forum-based roleplaying games. Online dice and other tools to record and enhance your collaborative storytelling experience.">
<meta name="robots" content="all">
<title>QuestLog - RPGs and Other Distractions</title>
<link rel="shortcut icon" href="img/favicon.ico" />
<link rel="stylesheet" href="css/main.css" type="text/css">
<link rel="stylesheet" href="css/dynamicContentBubble.css" type="text/css">

<script type="text/javascript"
    src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript"
    src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<!--
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.js"></script>
-->
<script type="text/javascript" src="js/globals.js"></script>
<script type="text/javascript" src="js/modules/DynamicContentBubble.js"></script>
<script type="text/javascript" src="js/modules/StandbyScreen.js"></script>
<script type="text/javascript">

$(document).ready(function() {
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  
  var logo = document.getElementById("mainLogo");
  var date = new Date();
  if (date.getHours() >= 18 || date.getHours() < 6) {
    $(logo).attr("src", "img/logo_night.png");
  } else {
    $(logo).attr("src", "img/logo_day.png");
  }
  var randomBG = document.getElementById("randomBG");
  var randomImg = randomImgs[Math.floor(Math.random()*randomImgs.length)];
  $(randomBG).attr("src", randomsDir + randomImg);
  
  $.ajax({
  url: "services/fetchSession.php",
  statusCode: {
    404: function() {
      spawnWindow(true, null, '20%', '90%', false, '75%', '5%', 'Menu', 'static/menu.php', '#F0F0F0', false, false, true);
      waitScreen.close();
    },
    200: function(data) {
      if (data) {
        var split = data.toString().split("&");
        handle = split[3];
        waitScreen.close();
        if (handle == "") {
          createHandle(split[2]);
        } else {
          validateAccount(split[0], split[1]);
        }
      }
    }
  }
  });

  if ($.browser.msie) {
    spawnWindow(true, null, '60%', '50%', false, '10', '10', 'LOL', 'static/browser.php?browser=IE', '#F0F0F0', false, false, true);
  }
  
  $(window).keyup(function(event) {
    if (event.which == 16) {
      shiftDown = false;
    }
    if (event.which == 17) {
      ctrlDown = false;
    }
  });

  $(window).keydown(function(event) {
    if (event.which == 16) {
      shiftDown = true;
    }
    if (event.which == 17) {
      ctrlDown = true;
    }
  });

  $("#weirdIcon").click(function() {
    if (shiftDown == true && ctrlDown == true) {
      spawnWindow(false, null, '60%', '50%', true, '10', '10', 'GateKeeper', 'static/gatekeeper.php', '#000000', false, false, true, "#05fe2e", "#05fe2e");
    }
  });
  $('html, body').animate({ scrollTop: 0 }, 0);
});

$(window).resize(function () {
    waitForFinalEvent(function(){
      for (var i=0; i < currentWindows.length; i++) {
        if (currentWindows[i].isMaximized) {
          currentWindows[i].maximize(this);
        }
        if (currentWindows[i].isMinimized) {
          resetMinimizedWindows(currentWindows[i].tabWidth, currentWindows[i].borderSize);
        }
      }
    }, 500, "final");
});
var waitForFinalEvent = (function () {
  var timers = {};
  return function (callback, ms, uniqueId) {
    if (!uniqueId) {
      uniqueId = "Don't call this twice without a uniqueId";
    }
    if (timers[uniqueId]) {
      clearTimeout (timers[uniqueId]);
    }
    timers[uniqueId] = setTimeout(callback, ms);
  };
})();
$(document).mousemove(this, function(event) {  
  for (var i=0; i < currentWindows.length; i++) {
    if (currentWindows[i].isResizing) {
      var width = event.pageX - currentWindows[i].left;
      var height = event.pageY - currentWindows[i].top;
      if (width > currentWindows[i].minimumWidth && height > currentWindows[i].minimumHeight) {
        currentWindows[i].setSize(width, height);
      }
    }
  }
});

/*******************
* Google Analytics *
*******************/
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-27020062-2']);
_gaq.push(['_setDomainName', 'questlog.org']);
_gaq.push(['_addIgnoredRef', 'questlog.org']);
_gaq.push(['_trackPageview']);

(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
</head>
<body>
<script type="text/javascript">
(function(d){
  var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
  if (d.getElementById(id)) {return;}
    js = d.createElement('script'); js.id = id; js.async = true;
    js.src = "//connect.facebook.net/en_US/all.js";
    ref.parentNode.insertBefore(js, ref);
  }
(document));

window.fbAsyncInit = function() {
  FB.init({
    appId      : <?php echo FB_ID;?>, // App ID
    channelUrl : '//mongol.questlog.org/etc/connect.html', // Path to your Channel File
    status     : false, // check login status
    cookie     : false, // enable cookies to allow the server to access the session
    xfbml      : true  // parse XFBML
  });
  FB.Event.subscribe('auth.statusChange', function(response) {
    if (response.authResponse) {
      // user has auth'd your app and is logged into Facebook
      FB.api('/me', function(me){
        if (me.name) {
          validateAccount(me.id, me.first_name);
          if ($(FB).data("dcb")) {
            $(FB).data("dcb").close();
          }
        }
      })
    }
  });
}
</script>
<div id="fb-root"></div>
<section style="" id="mainSection">
<div style="text-align:center;width:236px;">
<figure style="margin:0px auto;">
<img id="mainLogo" alt="QuestLog"><br />
<figcaption style="font-weight:bold;font-size:9px;">Online RPGs and Other Distractions</figcaption>
</figure>
</div>
<div style="width:100%;text-align:center;">
<figure>
<img alt="random" id="randomBG">
</figure>
</div>
<footer id="mainFooter">
<br />
<u><a href="#" onClick="javascript:spawnWindow(true, null, '50%', '50%', true, '0', '0', 'About', 'static/about.php', '#F0F0F0', false, false, true);">about</a></u> | 
<u><a href="#" onClick="javascript:spawnWindow(true, null, '50%', '50%', true, '0', '0', 'Contact', 'static/contact.php', '#F0F0F0', false, false, true);">contact</a></u> | 
<u><a href="#" onClick="javascript:spawnWindow(true, null, '600', '50%', true, '0', '0', 'Report a Bug', 'static/reportabug.php', '#F0F0F0', false, false, true);">report a bug</a></u> | 
<u><a href="#" onClick="javascript:spawnWindow(true, null, '50%', '50%', true, '0', '0', 'Support', 'static/support.php', '#F0F0F0', false, false, true);">support</a></u> | 
<u><a href="#" onClick="javascript:spawnWindow(true, null, '20%', '90%', false, '75%', '5%', 'Menu', 'static/menu.php', '#F0F0F0', false, false, true);">menu</a></u><br />
All user-generated content belongs to that user, everything else copyright <b>INFINITY</b>,
    questlog.org. A real lawyer wrote this, seriously.
</footer>
<div id="weirdIcon">&Pi;&nbsp;&nbsp;</div>
</section>
<audio id="popupSound" preload="auto">
  <source src="audio/mouthpop.ogg" type="audio/ogg" />
  <source src="audio/mouthpop.mp3" type="audio/mp3" />
</audio>
<audio id="diceSound" preload="auto">
  <source src="audio/diceRoll.ogg" type="audio/ogg" />
  <source src="audio/diceRoll.mp3" type="audio/mp3" />
</audio>
</body>
</html>

