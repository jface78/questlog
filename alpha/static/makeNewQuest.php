<link rel="stylesheet" type="text/css" href="css/makeNewQuest.css" />

<script type="text/javascript">
$(document).ready(function() {
  var draggingDiv;
  var topDivID = generateDivID("newQuestParent");
  $(document.getElementById("newQuestParent")).attr("id", topDivID);
  var topDiv = document.getElementById(topDivID);
  var btn = $(topDiv).find("#createNewQuestBtn");
  $(btn).attr("onClick", "javascript:createQuest('" + topDivID + "');");
  btn = $(topDiv).find("#searchNewQuestText");
  $(btn).attr("onChange", "javascript:searchChars('" + topDivID + "');");
  
  $(topDiv).find("#questChars").droppable({
    drop: function( event, ui ) {
    //$('#draggable li#'+$(ui.draggable).attr('id')).remove();
      $(ui.draggable).css("visibility", "hidden");
      $(ui.draggable).appendTo($( this ));
      $(ui.draggable).css("background-color", "#FFFFFF");
    }
  });
  $(topDiv).find("#listAllChars").droppable({
    drop: function( event, ui ) {
      $(ui.draggable).appendTo($( this ));
      $(ui.draggable).css("background-color", "#FFFFFF");
    }
  });
  $(topDiv).find("#searchNewQuestText").focus(function () {
    $(topDiv).find("#searchNewQuestText").val("");
  });
  var dcb = getDCBFromChild(topDiv);
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
});

function createQuest(divID) {
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
    var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
    $.ajax({
      type: "POST",
      data: {operation: "newQuest", title: title, descr: descr, visible: visible, players: playersString, characters: charactersString},
      url: "services/manageQuests.php",
      statusCode: {
        200: function(data) {
          $(topDiv).css("text-align", "center");
          var newText = "Your quest has been created.<br /><br />If you added any characters, ";
          newText += "they will be notified when they next log in.";
          newText += "<br /><br /><button type=\"button\" onClick=\"javascript:remoteClose('" + divID + "');\">OK</button>";
          $(topDiv).html(newText);
          reloadContents("static/listQuests.php");
          dcb.scrollToTop();
          waitScreen.close();
        }
      }
    });
  }
}

function searchChars(divID) {
  var topDiv = document.getElementById(divID);
  var dcb = getDCBFromChild(topDiv);
  var search = $(topDiv).find("#searchNewQuestText").val();
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
<div class="newQuestParent" id="newQuestParent">
<div id="questTitleRow">
<div id="newQuestSubDiv">
<label for="newQuestTitleDiv">Quest Title&nbsp;</label>
</div>
<div id="newQuestTitleDiv">
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
<div id="searchInputDiv" tabIndex="-1"><input tabIndex="-1" type="text" id="searchNewQuestText" value="Search..."></div><br />
<div id="listAllChars" class="questChars"></div>
</div>
</div>
<div id="searchButtonDiv"><button id="createNewQuestBtn" class="lightButton" type="button">create</button></div><br />
<div id="questMsgDiv"></div>
</div>
<br /><br /><br />