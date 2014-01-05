<link rel="stylesheet" type="text/css" href="css/makeNewQuest.css" />

<script type="text/javascript">
$(document).ready(function() {
  var topDivID = generateDivID("editQuestParent");
  var topDiv = document.getElementById(topDivID);
  $(topDiv).find("#deleteQuestBtn").attr("onClick", "javascript:deleteQuest('" + topDivID + "');");
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  $.ajax({
    type: "POST",
    data: {operation: "getQuestDetails", questID:"<?php echo $_GET['questID'];?>"},
    url: "services/manageQuests.php",
    statusCode: {
      200: function(data) {
        var splitData = data.split("|");
        var title = splitData[0];
        var descr = splitData[1];
        var visible = splitData[2];
        var characters = splitData[3].split("[&?]");
        $(topDiv).find("#questTitle").val(title);
        $(topDiv).find("#questDescr").val(descr);
        if (parseInt(visible) == 1) {
          $(topDiv).find("#questVisible").attr("checked", true);
        } else {
          $(topDiv).find("#questVisible").attr("checked", false);
        }
        
        for (var i=0; i < characters.length-1; i++ ) {
          var characterSplit = characters[i].split("&?");
          var div = document.createElement("div");
          $(div).addClass("searchCharResultBox");
          var parentWidth = $(topDiv).find("#questChars").width();
          $(div).css("width", parentWidth);
          setTimeout(function() {
            var parentWidth = $(topDiv).find("#questChars").width();
            $(div).css("width", parentWidth);
          }, 500);
          $(div).css("text-align", "left");
          var imgDiv = document.createElement("div");
          $(imgDiv).addClass("searchThumbResult");
          var textDiv = document.createElement("div");
          $(textDiv).addClass("searchNameResult");
          $(div).hover(
            function(event) {
              $(this).css("cursor", "pointer");
              $(this).css("background-color", "#EEDD82")
            },
            function(event) {
              $(this).css("background-color", "#3F3F3F")
            }
          );
          $(div).data('names', characterSplit[0]);
          $(div).data('characters', characterSplit[1]);
          $(div).data('user', characterSplit[3]);
          $(imgDiv).html('<img alt="' + characterSplit[0] +'" style="border:1px solid #000000;max-width:50px;max-height:50px;" src="' + characterSplit[2] + '">&nbsp;');
          $(textDiv).html(characterSplit[0] + ' (' + characterSplit[4] + ')');
          $(div).append(imgDiv);
          $(div).append(textDiv);
          $(topDiv).find("#questChars").append(div);
          $(div).dblclick(function() {
            spawnWindow(true, null, '75%', '75%', true, '0', '0',  escape($(this).data("names")), 'static/viewCharacter.php?characterID=' + $(this).data("characters"));
          });
          var startX;
          var startY;
          $(div).draggable({
            appendTo: $(topDiv),
            helper:'clone',
            zIndex: 9999,
            start: function(event, ui) {
              //$('#draggable li#'+$(ui.draggable).attr('id')).remove();
              startX = $(this).css("left");
              startY = $(this).css("top");
              $(this).css("visibility", "hidden");
            },
            stop: function(event, ui) {
              $(this).css("visibility", "visible");
              $(this).css("left", startX);
              $(this).css("top", startY);
            }
          });
        }
        $(topDiv).find("#questChars").scrollTop(0);
        waitScreen.close();
      },
      404: function(data) {
        waitScreen.close();
      },
      401: function(data) {
        $(topDiv).html("<b>401 / Unauthorized</b><br /><br />Authorities have been notified.");
        waitScreen.close();
      }
    }
  });
  setTimeout(makeQuestForm, 500, topDivID);
  //makeQuestForm(topDivID);
});
function closeThis(topDivID) {
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  dcb.close();
}
function deleteQuest(topDivID) {
  var topDiv = document.getElementById(topDivID);
  var conf = confirm("Delete this quest? Srsly?");
  if (conf) {
    var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
    $.ajax({
      type: "POST",
      data: {operation: "deleteQuest", questID:"<?php echo $_GET['questID'];?>"},
      url: "services/manageQuests.php",
      statusCode: {
        200: function(data) {
          $(topDiv).html("<div style=\"text-align:center;\"><b>Quest Deleted. You bastard.</b><br /><br /></div>");
          $(topDiv).append("<div style=\"text-align:center;\"><button type=\"button\" class=\"lightButton\" onClick=\"closeThis('" + topDivID + "');\">ok</button></div>");
          waitScreen.close();
          var dcb = getDCBFromChild(topDiv);
          dcb.scrollToTop();
          reloadContents("static/listQuests.php");
          var dcbArr = getDCBByContent("static/viewQuest.php?questID=<?php echo $_GET['questID'];?>");
          
          for (var i=0; i < dcbArr.length; i++) {
            dcbArr[i].close();
          }
        },
        401: function(data) {
          $(topDiv).html("<b>");
          $(topDiv).html("<div style=\"text-align:center;\"><b>401 / Unauthorized</b><br /><br />Authorities have been notified.<br /><br /></div>");
          $(topDiv).append("<div style=\"text-align:center;\"><button type=\"button\" class=\"lightButton\" onClick=\"closeThis('" + topDivID + "');\">ok</button></div>");
          waitScreen.close();
        }
      }
    });
  }
}

function makeQuestForm(topDivID) {
  
  var topDiv = document.getElementById(topDivID);
  var btn = $(topDiv).find("#updateQuestBtn");
  $(btn).attr("onClick", "javascript:updateQuest('" + topDivID + "');");
  btn = $(topDiv).find("#searchUpdateQuestText");
  $(btn).attr("onChange", "javascript:searchChars('" + topDivID + "');");
  
  $(topDiv).find("#questChars").droppable({
    drop: function( event, ui ) {
    //$('#draggable li#'+$(ui.draggable).attr('id')).remove();
      $(ui.draggable).css("visibility", "hidden");
      $(ui.draggable).prependTo($( this ));
      $(ui.draggable).css("background-color", "#FFFFFF");
    }
  });
  $(topDiv).find("#listAllChars").droppable({
    drop: function( event, ui ) {
      $(ui.draggable).prependTo($( this ));
      $(ui.draggable).css("background-color", "#FFFFFF");
    }
  });
  $(topDiv).find("#searchUpdateQuestText").focus(function () {
    $(topDiv).find("#searchUpdateQuestText").val("");
  });
  var dcb = getDCBFromChild(topDiv);
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
}

function updateQuest(divID) {
  var topDiv = document.getElementById(divID);
  var dcb = getDCBFromChild(topDiv);
  if ($(topDiv).find("#questTitle").val() == "") {
    $(topDiv).find("#questMsgDiv").html("Quests must have names. Duh.<br />");
    dcb.scrollToTop();
  } else {
    var title = $(topDiv).find("#questTitle").val();
    var descr = $(topDiv).find("#questDescr").val();
    var visible = $(topDiv).find("#questVisible").attr("checked");
    if (visible == "checked") {
      visible = "1";
    } else {
      visible = "0";
    }
    var playersArray = $(topDiv).find("#questChars").children();
    var playersString = "";
    var charactersString = "";
    for (var i = 0; i < playersArray.length; i++) {
      playersString += $(playersArray[i]).data("user") + "&";
      charactersString += $(playersArray[i]).data("characters") + "&";
    }
    var waitScreen = new StandbyScreen($(topDiv).find("#mainSection"));
    $.ajax({
      type: "POST",
      data: {operation: "updateQuest", questID:"<?php echo $_GET['questID'];?>", title: title, descr: descr, visible: visible, players: playersString, characters: charactersString},
      url: "services/manageQuests.php",
      statusCode: {
        200: function(data) {
          $(topDiv).css("text-align", "center");
          var newText = "Your quest has been updated.<br /><br />If you added any characters, ";
          newText += "they will be notified when they next log in.";
          newText += "<br /><br /><button type=\"button\" class=\"lightButton\" onClick=\"javascript:remoteClose('" + divID + "');\">OK</button>";
          $(topDiv).html(newText);
          dcb.scrollToTop();
          waitScreen.close();
          setTimeout(reloadContents, 500, "static/listQuests.php");
          setTimeout(reloadContents, 500, "static/viewQuest.php");
        }
      }
    });
  }
}

function searchChars(divID) {
  var topDiv = document.getElementById(divID);
  var dcb = getDCBFromChild(topDiv);
  var search = $(topDiv).find("#searchUpdateQuestText").val();
  var playersArray = $(topDiv).find("#questChars").children();
  var charactersString = "";
  if (playersArray.length > 0) {
    for (var i = 0; i < playersArray.length; i++) {
      charactersString += $(playersArray[i]).data("characters") + "&";
    }
  }
  if (search.length > 0) {
    $.ajax({
      type: "POST",
      dataType: "text",
      data: {operation: "getCharacters", term: search, existingCharacters:charactersString},
      url: "services/manageQuests.php",
      statusCode: {
        200: function(data) {
          $(topDiv).find("#listAllChars").empty();
          var parentWidth = $(topDiv).find("#listAllChars").width();
          var splitArray = data.split("|");
          for (var i=0; i < splitArray.length-1; i++) {
            var div = document.createElement("div");
            $(div).addClass("searchCharResultBox");
            $(div).css("width", parentWidth);
            var imgDiv = document.createElement("div");
            $(imgDiv).addClass("searchThumbResult");
            var textDiv = document.createElement("div");
            $(textDiv).addClass("searchNameResult");
            $(div).hover(
              function(event) {
                $(this).css("cursor", "pointer");
                $(this).css("background-color", "#EEDD82")
              },
              function(event) {
                $(this).css("background-color", "#3F3F3F")
              }
            );
            var subArray = splitArray[i].split("&");
            $(div).data('names', subArray[0]);
            $(div).data('characters', subArray[1]);
            $(div).data('user', subArray[3]);
            $(imgDiv).html('<img alt="' + subArray[0] +'" style="border:1px solid #000000;max-width:50px;max-height:50px;" src="' + subArray[2] + '">&nbsp;');
            $(textDiv).html(subArray[0] + ' (' + subArray[4] + ')');
            $(div).append(imgDiv);
            $(div).append(textDiv);
            $(topDiv).find("#listAllChars").append(div);
            $(div).dblclick(function() {
              spawnWindow(true, null, '75%', '75%', true, '0', '0',  escape($(this).data("names")), 'static/viewCharacter.php?characterID=' + $(this).data("characters"));
            });
            var startX;
            var startY;
            $(div).draggable({
              appendTo: $(topDiv),
              helper:'clone',
              zIndex: 9999,
              start: function(event, ui) {
                //$('#draggable li#'+$(ui.draggable).attr('id')).remove();
                startX = $(this).css("left");
                startY = $(this).css("top");
                $(this).css("visibility", "hidden");
              },
              stop: function(event, ui) {
                $(this).css("visibility", "visible");
                $(this).css("left", startX);
                $(this).css("top", startY);
              }
            });
          }
          $(topDiv).find("#listAllChars").scrollTop(0);
        }
      }
    });
  }
}

</script>
<div class="newQuestParent" id="editQuestParent">
<div id="questTitleRow">
<div id="editQuestSubDiv">
<label for="editQuestTitleDiv">Quest Title&nbsp;</label>
</div>
<div id="editQuestTitleDiv">
<input type="text" id="questTitle">
</div>
</div>
<div id="visibleRow">
<div id="visibleLabel">
<label for="visibleCheck" style="vertical-align:top;">Publicly Viewable?</label></div>
<div id="visibleCheckbox">
<input type="checkbox" checked="checked" id="questVisible" style="vertical-align:top;">
</div>
</div>
<div id="questDescrRow">
<div id="questDescrLabel">
<label for="questDescr" >Description&nbsp;</label>
</div>
<div id="questDescrDiv">
<textarea id="questDescr"></textarea>
</div>
</div>
<br />
<div id="charactersBox">
<div id="addCharactersLabel">
Add Characters</div>
<div id="questCharactersBox">
<div id="questCharactersLabel">Quest Characters</div><br />
<div id="questChars" class="questChars"></div>
</div>
<div id="dividerDiv">
< --- >
</div>
<div id="searchCharactersBox">
<div id="searchInputDiv" tabIndex="-1"><input tabIndex="-1" type="text" id="searchUpdateQuestText" value="Search..."></div><br />
<div id="listAllChars" class="questChars"></div>
</div>
</div>
<div id="searchButtonDiv">
<button id="updateQuestBtn" class="lightButton" type="button">update</button>
&nbsp;
<button id="deleteQuestBtn" class="lightButton" type="button">delete</button>
</div><br />
<div id="questMsgDiv"></div>
</div>
<br /><br /><br />