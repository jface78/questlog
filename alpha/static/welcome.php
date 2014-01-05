<script type="text/javascript">

var closeTimer;
$(document).ready(function() {
  for (var i=0; i < currentWindows.length; i++) {
    currentWindows[i].close();
  }
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  var topDivID = generateDivID("welcomeParent");
  var topDiv = document.getElementById(topDivID);
  $(topDiv).data("waitScreen", waitScreen);
  var handleBtn = $(topDiv).find("#handleBtn");
  $(handleBtn).click( function() {
    checkHandle(topDivID);
  });
  var dcb = getDCBFromChild(topDiv);
  dcb.disableControls();
  $(dcb.mainDiv).css("zIndex", 999999);
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
});
function closeThis(topDivID) {
  var topDiv = document.getElementById(topDivID);
  var waitScreen = $(topDiv).data("waitScreen");
  waitScreen.close();
  var dcb = getDCBFromChild(topDiv);
  clearTimeout(closeTimer);
  if (!getDCBByContent("static/menu.php") || getDCBByContent("static/menu.php") == "") {
    spawnWindow(true, null, '20%', '90%', false, '75%', '5%', 'Menu', 'static/menu.php', '#F0F0F0', false, false, true);
  } else {
    setTimeout(reloadContents, 500, "static/menu.php");
  }
  dcb.close();
}
function checkHandle(topDivID) {
  var topDiv = document.getElementById(topDivID);
  var handleValue = document.getElementById("handleText").value;
  $(document.getElementById("handleError")).text("");
  if (!handleValue || handleValue.length == 0) {
    $(document.getElementById("handleError")).text("You didn't enter anything. Jesus Christ.");
  }
  else if (handleValue.length > 20) {
    $(document.getElementById("handleError")).html("Too long. <b>Read the directions.</b>");
  } else {  
    $.ajax({
      type: "POST",
      url: "services/manageUsers.php",
      data: {operation: "checkHandles", handle: handleValue},
      statusCode: {
        200: function() {
          handle = handleValue;
          var welcomeText = document.getElementById("welcomeText");
          $(welcomeText).html("Ok. You will henceforth be known as <b>" + handleValue +
                                                         ".</b> <br />Go in peace, Long Rifle.<br /><br />");
          var btn = document.createElement("button");
          $(btn).addClass("lightButton");
          $(btn).attr("type", "button");
          $(btn).html("OK");
          $(btn).click( function() {
            closeThis(topDivID);
          });
          $(welcomeText).append(btn);
          closeTimer = setTimeout(closeThis, 10000, topDivID);
          
        },
        409: function() {
          $(document.getElementById("handleError")).text("Handle already exists. Try again.");
        }
      }
    });
  }
}
</script>
<div id="welcomeParent" style="width:100%;height:100%;overflow:hidden;line-height:20px;">
<h2>Welcome, <?php echo $_GET['name'];?>.</h2>
<div id="handleError" style="color:red;">&nbsp;</div>
<div id="welcomeText" style="white-space:normal;">
Our records indicate that this is your first time using questlog.<br />
First thing you have to do is <b>create your handle,</b> which is whatever name you'd like to be identified as when you're not in-character.<br />
Your handle is unique to your account and must be less than 20 characters long.<br /><br />
Go ahead, tough guy: <input type="text" id="handleText">
<button type="button" id="handleBtn" class="lightButton">check handle</button><br />

</div>

</div>