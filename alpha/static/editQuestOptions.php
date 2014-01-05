<script type="text/javascript">
$(document).ready(function() {
  var topDivID = generateDivID("questOptionsParent");
  var topDiv = document.getElementById(topDivID);
  var closeBtn = $(topDiv).find("#closeBtn");
  var dcb = getDCBFromChild(topDiv);
  $(closeBtn).click(this, function(event) {
    dcb.close();
  });
  var check = $(topDiv).find("#emailCheck");
  $(check).change(function() {
    updateEmailSettings(topDivID);
  });
  checkEmailTurnedOn(topDivID);
});

function updateEmailSettings(topDivID) {
  var topDiv = document.getElementById(topDivID);
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  var check = $(topDiv).find("#emailCheck");
  var activate;
  if ($(check).prop("checked")) {
    activate = true;
  } else {
    activate = false;
  }
  var errorDiv = $(topDiv).find("#errorDiv");
  $(errorDiv).html("");
  $.ajax({
    type: "POST",
    data: {operation: "updateEmailSettings", activate: activate, questID: "<?php echo $_GET['questID'];?>"},
    url: "services/manageQuests.php",
    statusCode: {
      200: function(data) {
        $(errorDiv).css("color", "#000000");
        $(errorDiv).text("Your email preferences have been updated.");
        waitScreen.close();
      },
      412: function(data) {
        $(check).attr("checked", false);
        $(errorDiv).css("color", "red");
        $(errorDiv).html("Error - No email address specified.<br />");
        $(errorDiv).append("To update your email address, click 'settings' from the main menu.");
        waitScreen.close();
      }
    }
  });
}

function checkEmailTurnedOn(topDivID) {
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  var topDiv = document.getElementById(topDivID);
  $.ajax({
    type: "POST",
    data: {operation: "checkEmailSettings", questID: "<?php echo $_GET['questID'];?>"},
    url: "services/manageQuests.php",
    statusCode: {
      200: function(data) {
        var check = $(topDiv).find("#emailCheck");
        $(check).prop("checked", "checked");
        waitScreen.close();
      },
      404: function(data) {
        waitScreen.close();
      }
    }
  });
}

</script>
<div style="padding-left:15px;padding-right:15px;width:100%;height:100%;overflow:hidden;text-align:center;" id="questOptionsParent">
<span style="display:inline-block;vertical-align:middle;line-height:22px;">
<label><span style="vertical-align:middle;">Email me when there's a new post:</span>
<input type="checkbox" id="emailCheck" style="vertical-align:middle;">
</label>
</span><br />
<div style="width:100%;text-align:center;">
<div style="color:red;width:90%;font-size:10px;text-align:center;white-space:normal;" id="errorDiv"></div>
</div>
<br />
<button type="button" class="lightButton" id="closeBtn">close</button>
</div>