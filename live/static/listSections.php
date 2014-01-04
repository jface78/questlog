<script type="text/javascript">

function deleteSection(id, queryString, divID) {
  var topDiv = document.getElementById(divID);
  var conf = confirm("Are you sure? Clicking OK will delete this section and all posts associated with it.");
  if (conf) {
    var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
    $.ajax({
      url: "services/manageQuests.php",  
      type: "POST",
      data: {operation: "deleteSection", sectionID: id},
      statusCode: {
        200: function(data) {
          reloadContents("static/listSections.php" + queryString);
          waitScreen.close();
        },
        401: function(data) {
          $(topDiv).html("<b>401 / Unauthorized</b><br /><br />Authorities have been notified.");
          waitScreen.close();
        }
      }
    });
  }    
}

function updateSectionTitle(value, id, topDiv) {
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  $.ajax({
    url: "services/manageQuests.php",  
    type: "POST",
    data: {operation: "updateSection", title:value, sectionID: id},
    statusCode: {
      401: function(data) {
        $(topDiv).html("<b>401 / Unauthorized</b><br /><br />Authorities have been notified.");
        waitScreen.close();
      },
      200: function(data) {
        waitScreen.close();
      }
    }
  });
}

function placeAddButton(topDiv, questID, participant) {
  $(topDiv).append("<br /><br />");
  var newBtnDiv = document.createElement("div");
  $(newBtnDiv).css("width", "90%");
  //$(newBtnDiv).css("margin-left", "15px");
  $(newBtnDiv).css("float", "left");
  $(newBtnDiv).css("text-align", "left");
  var newBtn = document.createElement("button");
  $(newBtn).attr("type", "button");
  $(newBtn).addClass("lightButton");
  if (participant != "forum") {
    $(newBtn).html("add section");
  } else {
    $(newBtn).html("new topic");
  }
  $(newBtn).click(function() {
    launchNewThread(questID, $(topDiv).attr("id"));
  });
  $(newBtnDiv).append(newBtn);
  $(newBtnDiv).append("<br />");
  $(topDiv).prepend("<br />");
  $(topDiv).prepend(newBtnDiv);
}

$(document).ready(function() {
  var topDivID = generateDivID("mainSectionsParent");
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  var participant = "<?php echo $_GET['participant'];?>";
  var questID = "<?php echo $_GET['questID'];?>";
  var queryString = "?questTitle=" + escape("<?php echo $_GET['questTitle'];?>") + "&questID=<?php echo $_GET['questID'];?>&participant=<?php echo $_GET['participant'];?>";

  $.ajax({
    url: "services/manageQuests.php",  
    type: "POST",
    data: {operation: "getSections", questID: questID},
    statusCode: {
      200: function(data) {
        var splitChapters = data.split("&?");
        var headerDiv = document.createElement("div");
        $(headerDiv).css("width", "90%");
        $(headerDiv).css("float", "left");
        $(headerDiv).css("margin-left", "15px");
        $(headerDiv).css("text-align", "center");
        if (participant != "forum") {
          $(headerDiv).html("This quest is divided into sections. Click one to read it.<br /><br />");
        }
        $(topDiv).append(headerDiv);
        $(topDiv).append("<br /><br />");
        var labelsDiv = document.createElement("div");
        $(labelsDiv).css("width", "90%");
        var titleLabel = document.createElement("div");
        $(titleLabel).css("width", "30%");
        $(titleLabel).css("margin-left", "15px");
        $(titleLabel).css("float", "left");
        $(titleLabel).text("Title");
        var dateLabel = document.createElement("div");
        $(dateLabel).css("width", "30%");
        $(dateLabel).css("float", "left");
        $(dateLabel).text("Created On");
        $(labelsDiv).append(titleLabel);
        $(labelsDiv).append(dateLabel);
        $(topDiv).append(labelsDiv);
        $(topDiv).append("<br />");
        for (var i=0; i < splitChapters.length-1; i++) {
          var div = document.createElement("div");
          var divClass;
          var fontColor;
          var hoverColor;
          var subSplit = splitChapters[i].split("&");
          if (i % 2 == 0) {
            divClass = "grayRow";
            fontColor = "#FFFFFF";
            hoverColor = "#C8C8C8";
          } else {
            divClass = "brownRow";
            fontColor = "#FFFFFF";
            hoverColor = "#C8C8C8";
          }
          $(div).attr("class", divClass);
          $(div).data("hoverColor", hoverColor);
          $(div).data("fontColor", fontColor);
          $(div).data("title", subSplit[1]);
          $(div).data("participant", participant);
          $(div).data("questID", "<?php echo $_GET['questID'];?>");
          $(div).data("questTitle", escape("<?php echo $_GET['questTitle'];?>"));
          $(div).data("sectionID", subSplit[0]);
          $(div).css("color", fontColor);
          $(div).css("height", "32px");
          $(div).css("width", "90%");
          $(div).css("margin-bottom", "5px");
          $(div).css("box-shadow", "3px 3px 3px #888888");
          $(div).css("padding-bottom", "1px");
          $(div).css("text-align", "left");
          $(div).css("float", "left");
          $(div).css("position", "relative");
          $(div).css("display", "table");
          
          var innerDiv = document.createElement("div");
          $(innerDiv).css("display", "table-cell");
          $(innerDiv).css("vertical-align", "middle");
          
          $(div).click(function() {
            var url = "static/viewQuest.php?questID=" + $(this).data("questID") + "&sectionID=" + $(this).data("sectionID") + "&offset=0&participant="+participant;
            spawnWindow(true, null, '80%', '80%', true, '0', '0', $(this).data("questTitle") + " - " + escape($(this).data("title")), url, "#F0F0F0", false, false, false);
          });
          $(div).hover(
            function () {
              $(this).css("cursor", "pointer");
              $(this).css("color", $(this).data("hoverColor"));
            }, 
            function () {
              $(this).css("color", $(this).data("fontColor"));
            }
          );
          var titleDiv = document.createElement("div");
          $(titleDiv).css("margin-top", "2px");
          $(titleDiv).css("width", "30%");
          $(titleDiv).css("margin-left", "15px");
          $(titleDiv).css("overflow", "hidden");
          $(titleDiv).css("float", "left");
          $(titleDiv).html(subSplit[1]);
          $(innerDiv).append(titleDiv);
          
          var timeDiv = document.createElement("div");
          $(timeDiv).css("margin-top", "2px");
          $(timeDiv).css("width", "30%");
          $(timeDiv).css("float", "left");
          $(timeDiv).html(subSplit[2]);
          $(innerDiv).append(timeDiv);
          $(div).append(innerDiv);
          $(topDiv).append(div);
          if (participant == "gm") {
            var btnDiv = document.createElement("div");
            $(btnDiv).css("margin-top", "2px");
            $(btnDiv).css("float", "left");
            $(btnDiv).css("margin-right", "5px");
            $(btnDiv).css("text-align", "right");
            $(btnDiv).css("width", "30%");
            var btn = document.createElement("button");
            $(btn).click(false);
            $(btn).attr("type", "button");
            $(btn).addClass("darkButton");
            $(btn).append("change name");

            function changeNameClick(btnObj) {
              var input = document.createElement("input");
              var orgDiv = $(btnObj).data("titleDiv");
              var orgTxt = $(orgDiv).text();
              var sectionID = $(btnObj).data("sectionID");
              $(input).click(false);
              $(input).attr("type", "text");
              $(input).attr("id", "inputTitle");
              $(input).val(orgTxt);
              $(orgDiv).text("");
              $(orgDiv).append(input);
              
              $(input).data("titleDiv", orgDiv);
              $(input).data("sectionID", sectionID);
              $(input).focusout(function(event) {
                var orgDiv = $(this).data("titleDiv");
                var orgTxt = $(orgDiv).text();
                if ($(this).val() != orgTxt && $(this).val() != "") {
                  updateSectionTitle($(this).val(), $(this).data("sectionID"), topDiv);
                }
                $(this).remove();
                $(orgDiv).html($(this).val());
                $(this).focusout(false);
              });
              $(input).keypress(function(event) {
                if ( (parseInt(event.which) == 13) || (parseInt(event.which) == 0) ) {
                  $(this).trigger("focusout");
                }
              });
            }
            $(btn).data("titleDiv", titleDiv);
            $(btn).data("sectionID", $(div).data("sectionID"));
            $(btn).click( function(event) {
              changeNameClick(this);
            });
            $(btnDiv).append(btn);
            $(btnDiv).append("&nbsp;");
            var delBtn = document.createElement("button");
            $(delBtn).click(false);
            $(delBtn).attr("type", "button");
            $(delBtn).addClass("darkButton");
            $(delBtn).data("sectionID", subSplit[0]);
            //questTitle=" + questTitle + "&questID=" + questID + "&participant=" + participant
            $(delBtn).data("queryString", queryString);
            $(delBtn).click(function(event) {
              deleteSection($(this).data("sectionID"), $(this).data("queryString"), topDivID);
            });
            $(delBtn).html("delete");
            $(btnDiv).append(delBtn);
            $(innerDiv).append(btnDiv);
          }
        }
        waitScreen.close();
        if (participant == "gm" || participant == "forum") {
          setTimeout(placeAddButton, 500, topDiv, questID, participant);
        }
        setTimeout(dcb.checkScrollers, 500, $(topDiv));
      },
      404: function(data) {
        waitScreen.close();
        if (participant == "gm" || participant == "forum") {
          setTimeout(placeAddButton, 500, topDiv, questID, participant);
        }
        if (participant != "forum") {
          $(topDiv).append("<br />No sections in this quest yet.");
        } else {
          $(topDiv).append("<br />No one has posted in this forum yet!");
        }
      }
    }
  });
});
</script>

<div id="mainSectionsParent">
</div>