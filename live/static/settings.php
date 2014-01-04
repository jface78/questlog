<script type="text/javascript">
$(document).ready(function() {
  var topDivID = generateDivID("settingsParent");
  var topDiv = document.getElementById(topDivID);

  var img = $(topDiv).find("#userPortrait");
  $(img).data("topDiv", $(topDiv));
  
  var updateBtn = $(topDiv).find("#updateBtn");
  $(updateBtn).attr("onClick", "javascript:updateSettings('" + topDivID + "');");
  
  var text = $(topDiv).find("#nameText");
  $(text).attr("value", handle);
  $(topDiv).find("#nameRow").append(text);
  $(topDiv).find("#nameText").change(function() {
    checkHandle(topDivID);
  });
  var check = $(topDiv).find("#soundCheck");
  if (soundEnabled) {
    $(check).prop("checked", "checked");
  }
  $(topDiv).find("#soundRow").append(check);
  $(check).change(function() {
    //updateSettings(topDivID);
  });
  
  var dropBox = $(topDiv).find("#portraitRow");
  dropBox = dropBox[0];
  dropBox.addEventListener("dragenter", noopHandler, false);
  dropBox.addEventListener("dragexit", noopHandler, false);
  dropBox.addEventListener("dragover", noopHandler, false);
  dropBox.addEventListener("drop", drop, false);
  getUserPortrait(topDivID);
  getUserEmail(topDivID);
});

function checkHandle(topDivID) {
  var topDiv = document.getElementById(topDivID);
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  var newHandle = $(topDiv).find("#nameText").val();
  if (handle != newHandle && newHandle != "") {
    $.ajax({
      type: "POST",
      url: "services/manageUsers.php",
      data: {operation: "checkHandles", handle: newHandle},
      statusCode: {
        200: function() {
          handle = $(topDiv).find("#nameText").val();
          waitScreen.close();
          updateSettings(topDivID);
        },
        409: function() {
          $(topDiv).find("#settingsError").text("Handle already exists. Try again.");
          waitScreen.close();
        }
      }
    });
  } else if (newHandle == "") {
    $(topDiv).find("#settingsError").html("<b>You fool!</b> Handles cannot be blank!");
    waitScreen.close();
  } else {
    updateSettings(topDivID);
  }
}

function noopHandler(event) {
  event.stopPropagation();
  event.preventDefault();
}

function handleReaderLoad(event) {
  var topDiv = $(event.target).data("topDiv");
  var img = $(topDiv).find("#userPortrait");
  $(img).attr("src", event.target.result);
  $(topDiv).find("#dropLabel").html("Drop Your Portrait Here");
}

function handleFiles(files, topDiv) {
  var file = files[0];
  var dot = file.name.lastIndexOf(".");
  var ext = file.name.substr(dot+1, file.name.length);
  if (ext != "gif" && ext != "jpg" && ext != "png" && ext != "jpeg") {
    $(topDiv).find("#dropLabel").html("ERROR - only GIF, PNG, and JPG filetypes permitted.");
  } else {
    $(topDiv).data("file", file);
    $(topDiv).find("#dropLabel").html("Processing " + file.name);
    var reader = new FileReader();
    $(reader).data("topDiv", topDiv);
    reader.onload = handleReaderLoad;
    reader.readAsDataURL(file);       
  }
}

function drop(event) {
  event.stopPropagation();
  event.preventDefault();
  var topDiv = $(event.target).data("topDiv");
  if (event.dataTransfer.files.length > 0) {
    handleFiles(event.dataTransfer.files, topDiv);
  }
}

function getUserEmail(topDivID) {
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  var topDiv = document.getElementById(topDivID);
  $.ajax({
    type: "POST",
    data: {operation: "getEmail"},
    url: "services/manageUsers.php",
    statusCode: {
      200: function(data) {
        $(topDiv).find("#emailText").val(data.toString());
        waitScreen.close();
      },
      404: function(data) {
        waitScreen.close();
      }
    }
  });
}

function getUserPortrait(topDivID) {
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  var topDiv = document.getElementById(topDivID);
  $.ajax({
    type: "POST",
    data: {operation: "getPortrait"},
    url: "services/manageUsers.php",
    statusCode: {
      200: function(data) {
        var img = $(topDiv).find("#userPortrait");
        $(img).attr("src", data.toString());
        waitScreen.close();
        //setTimeout(dcb.checkScrollers, 500, $(topDiv));
      },
      404: function(data) {
        waitScreen.close();
        //setTimeout(dcb.checkScrollers, 500, $(topDiv));
      }
    }
  });
}

function updateSettings(topDivID) {
  var topDiv = document.getElementById(topDivID);
  var handle = $(topDiv).find("#nameText").val();
  var email = $(topDiv).find("#emailText").val();
  if (handle == "") {
    $(topDiv).find("#settingsError").html("<b>You fool!</b> Handles cannot be blank!");
  } else if (email != "none" && email != "" && !validateEmail(email)) {
    $(topDiv).find("#settingsError").html("<b>You fool!</b> Invalid email address!");
  } else {
    $(topDiv).find("#settingsError").html("");
    soundEnabled = $(topDiv).find("#soundCheck").prop("checked");
    var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
    var formData = new FormData();
    if (email == "") {
      email = "none";
    }
    formData.append("operation", "updateSettings");
    formData.append("soundEnabled", soundEnabled);
    formData.append("handle", handle);
    formData.append("email", email);
    if ($(topDiv).data("file")) {
      formData.append("image", $(topDiv).data("file"));
    }
    $.ajax({
      type: "POST",
      data: formData,  
      processData: false,
      contentType: false,
      url: "services/manageUsers.php",
      statusCode: {
        200: function(data) {
          waitScreen.close();
        },
        401: function(data) {
          waitScreen.close();
          $(topDiv).find("#settingsError").html("Invalid image filetype.");
        }
      }
    });
  }
}
</script>

<div style="margin-left:15px;overflow:hidden;vertical-align:top;text-align:left;width:100%;height:100%;line-height:25px;" id="settingsParent">
<span id="nameRow" style="height:25px;">
<label for="nameText" style="text-align:right;vertical-align:middle;width:25%;float:left;">Handle:&nbsp;</label>
<input type="text" id="nameText" style="vertical-align:middle;width:60%;">
</span><br />
<span id="emailRow" style="height:25px;">
<label for="emailText" style="text-align:right;vertical-align:middle;width:25%;float:left;">Email:&nbsp;</label>
<input type="text" id="emailText" style="vertical-align:middle;width:60%;" value="none">
</span><br />
<span id="soundRow" style="height:25px;">
<label for="soundCheck" style="text-align:right;vertical-align:middle;width:25%;float:left;">Sound:&nbsp;</label>
<input type="checkbox" id="soundCheck" style="vertical-align:middle;">
</span><br /><br />
<span id="portraitRow" style="text-align:center;">
<figure style="margin:0px auto;">
<img id="userPortrait" src="img/portrait_med.jpg" style="border:1px solid #000000;max-width:100px;vertical-align:top;">
<figcaption id="dropLabel" style="font-size:10px;">Drop Your Portrait Here</figcaption>
</figure>
</span>
<div id="settingsError" style="width:100%;color:red;white-space:normal;text-align:center;">&nbsp;</div>
<div style="width:100%;text-align:center;">
<button class="lightButton" id="updateBtn">update</button>
</div>

</div>
