<link rel="stylesheet" href="css/viewQuest.css?sdfsdd" type="text/css">
<link href='http://fonts.googleapis.com/css?family=Libre+Baskerville:700' rel='stylesheet' type='text/css'>
<script type="text/javascript">
function deletePost(postID, tblID, divID) {
  var conf = confirm("Delete post? For realz?");
  if (conf) {
    $.ajax({
      url: "services/managePosts.php",  
      type: "POST",
      data: {operation: "deletePost", postID: postID, questID: "<?php echo $_GET['questID'];?>", sectionID: "<?php echo $_GET['sectionID'];?>"},
      statusCode: {
        200: function(data) {
          $('#' + tblID).remove();
        },
        401: function(data) {
          var topDiv = document.getElementById(divID);
          $(topDiv).html("<b>401 / Unauthorized</b><br /><br />Authorities have been notified.");
        }
      }
    });
  }
}

function refreshQuests(tblID, postValue, isNew, topDivID) {
  if (isNew) {
    var topDiv = document.getElementById(topDivID);
    if ($(topDiv).data("direction") == "reverse") {
      $(topDiv).data("questOffset", -10);
      $(topDiv).html("");
      loadQuestPosts(topDiv);
    }
  } else {
    var td = $(document.getElementById(tblID)).find("#contentTD");
    $(td).html(postValue);
  }
}

function reverseOrder(div, btn) {
  $(div).data("questOffset", -10);
  if ($(div).data("direction") == "reverse") {
    $(div).data("direction", "forward");
    $(btn).text("ending");
  } else {
    $(div).data("direction", "reverse");
    $(btn).text("beginning");
  }
  $(div).html("");
  loadQuestPosts(div);
}

function loadQuestPosts(topDiv, fromDCB) {
  var dir = $(topDiv).data("direction");
  var dcb = getDCBFromChild(topDiv);
  $(topDiv).data("questOffset", parseInt($(topDiv).data("questOffset")) + 10);
  $(topDiv).data("oldHeight", $(topDiv).height());
  var participant = "<?php echo $_GET['participant'];?>";
  $.ajax({
    url: "services/manageQuests.php",  
    type: "POST",
    data: {operation: "getQuest", questID: "<?php echo $_GET['questID'];?>", sectionID: "<?php echo $_GET['sectionID'];?>",
           direction: dir, offset: $(topDiv).data("questOffset"), isUpdate: fromDCB},
    statusCode: {
      200: function(data) {
        var splitData = data.split("|");
        for (var i=0; i < splitData.length-1; i++) {
          var divColor;
          var dir;
          var subSplit = unescape(splitData[i]).split("&?");
          if (i % 2 == 0) {
            divColor = "gray";
            dir = "left";
          } else {
            divColor = "green";
            dir = "right";
          }
          var postID = subSplit[0];
          var date = subSplit[1];
          var text = subSplit[2];
          var charID = subSplit[3];
          var isViewer = subSplit[4];
          var posterName = subSplit[5];
          var isGM = subSplit[6];
          var thumb = subSplit[7];
          var gmID = subSplit[8]

          var regex = /[\\?\\.\\!]/g;
          var matches = text.match(regex);
          var textDiv = document.createElement("div");
          if (matches != null && matches.length > 4 && participant != "forum") {
            var endpoint = text.search(regex);
            while (text.charAt(endpoint + 1) != " " && text.charAt(endpoint + 1) != "<" && text.charAt(endpoint + 1) < text.length) {
              endpoint++;
            }
            var firstLine = text.substring(0, endpoint+1);
            var rest = text.substring(endpoint+1, text.length);
            var boldTextDiv = document.createElement("div");
            $(boldTextDiv).css("float", "left");
            $(boldTextDiv).css("font-size", "13px");
            $(boldTextDiv).css("letter-spacing", "1px");
            //$(boldTextDiv).css("line-height", "16px");
            $(boldTextDiv).css("text-shadow", "-1px -1px 0 #000, 1px -1px 0 #000,-1px 1px 0 #000, 1px 1px 0 #000");
            $(boldTextDiv).css("font-family", "Libre Baskerville");
            $(boldTextDiv).html(firstLine);
            $(textDiv).append(boldTextDiv);
            $(textDiv).append("&nbsp;");
            $(textDiv).append(rest);
          } else {
            $(textDiv).html(text);
          }
          var div = document.createElement("div");
          $(div).css("text-align", dir);
          
          var tbl = document.createElement("table");
          var tblID = "postTable" + Math.round(Math.random() * 999999);
          $(tbl).attr("id", tblID);
          $(tbl).addClass("postTable");
          $(tbl).attr("cellpadding", "0");
          $(tbl).attr("cellspacing", "0");
          var postedText;
          var charA;
          if (participant == "forum") {
            gmID = charID;
          }
          if (isGM == "gm" || participant == "forum") {
            charA = document.createElement("a");
            $(charA).css("color", "#FFFFFF");
            $(charA).attr("onClick", "javascript:spawnWindow(true, null, \"75%\", \"75%\", true, \"0\", \"0\", \"" + posterName + "\", \"static/viewUser.php?userID=" + gmID + "\");");
            $(charA).text(posterName);
            postedText = document.createElement("span");
            $(postedText).append("Posted by ");
            $(postedText).append(charA);
            $(postedText).append(" - GM - on " + date);
          } else {
            charA = document.createElement("a");
            $(charA).css("color", "#FFFFFF");
            $(charA).attr("onClick", "javascript:spawnWindow(true, null, \"75%\", \"75%\", true, \"0\", \"0\", \"" + escape(posterName) + "\", \"static/viewCharacter.php?characterID=" + charID + "\");");
            $(charA).text(posterName);
            postedText = document.createElement("span");
            $(postedText).append("Posted by ");
            $(postedText).append(charA);
            $(postedText).append(" on " + date);
          }
          if (divColor == "gray") {
            var tr1 = document.createElement("tr");
            var td1 = document.createElement("td");
            $(td1).addClass("grayTopLeft");
            var td2 = document.createElement("td");
            $(td2).attr("colspan", "2");
            $(td2).addClass("grayTop");
            $(td2).append(postedText);
            if (isViewer == "true") {
              var deleteBtn = document.createElement("button");
              $(deleteBtn).attr("type", "button");
              $(deleteBtn).addClass("darkButton");
              $(deleteBtn).append("delete");
              $(deleteBtn).data("postID", postID);
              $(deleteBtn).data("tblID", tblID);
              $(deleteBtn).click(this, function(event) {
                deletePost($(this).data("postID"), $(this).data("tblID"));
              });
              var editBtn = document.createElement("button");
              $(editBtn).attr("type", "button");
              $(editBtn).addClass("darkButton");
              $(editBtn).append("edit");
              $(editBtn).data("postID", postID);
              $(editBtn).data("questID", "<?php echo $_GET['questID'];?>");
              $(editBtn).data("tblID", tblID);
              $(editBtn).click(this, function(event) {
                launchPostEditor($(this).data("postID"), $(this).data("tblID"), $(this).data("questID"), '<?php echo $_GET['participant'];?>');
              });
              $(td2).append("&nbsp;");
              $(td2).append(deleteBtn);
              $(td2).append("&nbsp;");
              $(td2).append(editBtn);
            }
            var td3 = document.createElement("td");
            $(td3).addClass("grayTopRight");
            $(tr1).append(td1);
            $(tr1).append(td2);
            $(tr1).append(td3);

            var tr2 = document.createElement("tr");
            td1 = document.createElement("td");
            $(td1).css("width", "50px");
            $(td1).css("vertical-align", "top");
            var usrImg = document.createElement("img");
            $(usrImg).attr("src", thumb);
            $(usrImg).attr("alt", posterName);
            $(usrImg).addClass("userImage");
            $(usrImg).css("margin-top", "5px");
            $(td1).append(usrImg);
            td2 = document.createElement("td");
            $(td2).addClass("grayLeft");
            td3 = document.createElement("td");
            $(td3).addClass("grayMiddle");
            $(td3).attr("id", "contentTD");
            $(td3).append(textDiv);
            var td4 = document.createElement("td");
            $(td4).addClass("grayRight");
            $(tr2).append(td1);
            $(tr2).append(td2);
            $(tr2).append(td3);
            $(tr2).append(td4);
            
            var tr3 = document.createElement("tr");
            td1 = document.createElement("td");
            td2 = document.createElement("td");
            $(td2).addClass("grayDownLeft");
            td3 = document.createElement("td");
            $(td3).addClass("grayDown");
            td4 = document.createElement("td");
            $(td4).addClass("grayDownRight");
            $(tr3).append(td1);
            $(tr3).append(td2);
            $(tr3).append(td3);
            $(tr3).append(td4);
          } else {
            var tr1 = document.createElement("tr");
            var td1 = document.createElement("td");
            $(td1).addClass("brownTopLeft");
            var td2 = document.createElement("td");
            $(td2).addClass("brownTop");
            var td3 = document.createElement("td");
            $(td3).addClass("brownTopRight");
            var td4 = document.createElement("td");
            $(tr1).append(td1);
            $(tr1).append(td2);
            $(tr1).append(td3);
            $(tr1).append(td4);

            var tr2 = document.createElement("tr");
            td1 = document.createElement("td");
            $(td1).addClass("brownLeft");
            td2 = document.createElement("td");
            $(td2).addClass("brownMiddle");
            $(td2).attr("id", "contentTD");
            $(td2).append(textDiv);
            td3 = document.createElement("td");
            $(td3).addClass("brownRight");
            td4 = document.createElement("td");
            $(td4).css("width", "50px");
            $(td4).css("text-align", "center");
            $(td4).css("vertical-align", "bottom");
            var usrImg = document.createElement("img");
            $(usrImg).attr("src", thumb);
            $(usrImg).attr("alt", posterName);
            $(usrImg).addClass("userImage");
            $(usrImg).css("margin-bottom", "5px");
            $(td4).append(usrImg);
            $(tr2).append(td1);
            $(tr2).append(td2);
            $(tr2).append(td3);
            $(tr2).append(td4);
            var tr3 = document.createElement("tr");
            td1 = document.createElement("td");
            $(td1).addClass("brownDownLeft");
            td2 = document.createElement("td");
            $(td2).attr("colspan", "2");
            $(td2).addClass("brownDown");
            $(td2).append(postedText);
            if (isViewer == "true") {
              var deleteBtn = document.createElement("button");
              $(deleteBtn).attr("type", "button");
              $(deleteBtn).addClass("darkButton");
              $(deleteBtn).append("delete");
              $(deleteBtn).data("postID", postID);
              $(deleteBtn).data("tblID", tblID);
              $(deleteBtn).data("topDivID", $(topDiv).attr("id"));
              $(deleteBtn).click(this, function(event) {
                deletePost($(this).data("postID"), $(this).data("tblID"), $(this).data("topDivID"));
              });
              var editBtn = document.createElement("button");
              $(editBtn).attr("type", "button");
              $(editBtn).append("edit");
              $(editBtn).addClass("darkButton");
              $(editBtn).data("postID", postID);
              $(editBtn).data("questID", "<?php echo $_GET['questID'];?>");
              $(editBtn).data("tblID", tblID);
              $(editBtn).click(this, function(event) {
                launchPostEditor($(this).data("postID"), $(this).data("tblID"), $(this).data("questID"),'<?php echo $_GET['participant'];?>');
              });
              $(td2).append("&nbsp;");
              $(td2).append(deleteBtn);
              $(td2).append("&nbsp;");
              $(td2).append(editBtn);
            }
            td3 = document.createElement("td");
            $(td3).addClass("brownDownRight");
            $(tr3).append(td1);
            $(tr3).append(td2);
            $(tr3).append(td3);
          }
          $(tbl).append(tr1);
          $(tbl).append(tr2);
          $(tbl).append(tr3);
          $(topDiv).append(tbl);
          $(topDiv).append(document.createElement("br"));
        }
        $(topDiv).append(document.createElement("br"));
        if (fromDCB == true) {
          var newHeight = $(topDiv).innerHeight();
          dcb.adjustVerticalDragger($(topDiv).data("oldHeight"));
          $(topDiv).data("oldHeight", newHeight);
        }  
        if ($(topDiv).data("waitScreen")) {
          $(topDiv).data("waitScreen").close();
        }
        setTimeout(dcb.checkScrollers, 500, $(topDiv));
      },
      404: function(data) {
        dcb.stopLoadingNewContent = true;
        $(topDiv).text("No posts yet.");
        if ($(topDiv).data("waitScreen")) {
          $(topDiv).data("waitScreen").close();
        }
      },
      204: function(data) {
        dcb.stopLoadingNewContent = true;
        $(topDiv).append("<br /><b>No more posts.</b>");
        if ($(topDiv).data("waitScreen")) {
          $(topDiv).data("waitScreen").close();
        }
      }
    }
  });
}
$(document).ready(function() {
  var participant = "<?php echo $_GET['participant'];?>";
  var waitScreenQuest = new StandbyScreen(document.getElementById("mainSection"));
  var topDivID = generateDivID("viewQuestParent");
  var topDiv = document.getElementById(topDivID);
  $(topDiv).data("waitScreen", waitScreenQuest);
  var dcb = getDCBFromChild(topDiv);
  var questOffset = "<?php echo $_GET['offset'];?>";
  $(topDiv).data("questOffset", parseInt(questOffset) - 10);
  $(topDiv).data("direction", "reverse");
  var addBtnID = generateDivID("addBtn");
  var addBtn = document.getElementById(addBtnID);
  $(addBtn).attr("onClick", "javascript:launchNewPost('<?php echo $_GET['questID'];?>','<?php echo $_GET['sectionID'];?>','" + topDivID + "', '<?php echo $_GET['participant'];?>');");
  var orderBtnID = generateDivID("orderBtn");
  var orderBtn = document.getElementById(orderBtnID);
  var optionsBtnID = generateDivID("optionsBtn");
  var optionsBtn = document.getElementById(optionsBtnID);
  $(optionsBtn).attr("onClick", "javascript:editQuestOptions('<?php echo $_GET['questID'];?>');");
  var controllerDivID = generateDivID("controllerDiv");
  $(topDiv).data("controller", controllerDivID);
  $(orderBtn).data("topDiv", topDiv);
  $(orderBtn).click(this, function(event) {
    reverseOrder($(this).data("topDiv"), $(this));
  });
  $.ajax({
    url: "services/manageQuests.php",  
    type: "POST",
    data: {operation: "checkIfAdmin", questID: "<?php echo $_GET['questID'];?>"},
    statusCode: {
      200: function(data) {
        var controls = document.getElementById(controllerDivID);
        var btn = document.createElement("button");
        $(btn).attr("type", "button");
        $(btn).addClass("lightButton");
        if (data == "true") {  
          $(btn).html("edit quest");
          $(btn).attr("onClick", "javascript:spawnWindow(true, null, \"75%\", \"75%\", true, \"0\", \"0\", \"Edit Quest\", \"static/editQuest.php?questID=<?php echo $_GET['questID'];?>\");");
        } else {
          $(btn).html("backstory");
          $(btn).attr("onClick", "javascript:spawnWindow(true, null, \"75%\", \"75%\", true, \"0\", \"0\", \"Quest Backstory\", \"static/questBackstory.php?questID=<?php echo $_GET['questID'];?>\");");
        }
        if (participant != "forum") {
          $(controls).append(btn);
        }
        loadQuestPosts(topDiv);
      },
      401: function(data) {
        if ($(topDiv).data("waitScreen")) {
          $(topDiv).data("waitScreen").close();
        }
        $(topDiv).html("<b>401 / Unauthorized</b><br /><br />Authorities have been notified.");
      }
    }
  });
});
</script>
<div id="controllerDiv">
<button type="button" class="lightButton" id="orderBtn">beginning</button>&nbsp;
<?php if ($_GET['participant'] == "gm" || $_GET['participant'] == "pc" || $_GET['participant'] == "forum") {?>
<button type="button" id="addBtn" class="lightButton">new post</button>&nbsp;
<button type="button" id="optionsBtn" class="lightButton">options</button>&nbsp;
<?php } ?>
</div><br />
<div style="width:100%;height:100%;overflow:hidden;text-align:center;vertical-align:top;" id="viewQuestParent">
</div>