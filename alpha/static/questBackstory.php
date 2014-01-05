<link rel="stylesheet" type="text/css" href="css/makeNewQuest.css" />

<script type="text/javascript">
$(document).ready(function() {
  var topDivID = generateDivID("backstoryParent");
  var topDiv = document.getElementById(topDivID);
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  var closeBtn = $(topDiv).find("#closeBtn");
  $(closeBtn).click(function() {
    closeThis(topDivID);
  });
  
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
        var div = document.createElement("div");
        $(div).css("border-radius", "10px");
        $(div).css("background-color", "#3F3F3F");
        $(div).css("border", "1px solid #000000");
        $(div).css("padding", "5px");
        $(div).css("width", "75%");
        $(div).css("margin", "0 auto");
        $(div).css("color", "#FFFFFF");
        $(div).css("font-size", "10px");
        var titleDiv = document.createElement("div");
        $(titleDiv).css("width", "100%;");
        $(titleDiv).css("font-weight", "bold");
        $(titleDiv).css("text-align", "left");
        $(titleDiv).text(title);
        $(div).append(titleDiv);
        $(div).append(document.createElement("br"));
        var descrDiv = document.createElement("div");
        $(descrDiv).css("text-align", "left");
        $(descrDiv).css("width", "100%");
        $(descrDiv).text(descr);
        $(div).append(descrDiv);
        $(div).append(document.createElement("br"));
        titleDiv = document.createElement("div");
        $(titleDiv).css("width", "100%;");
        $(titleDiv).css("font-weight", "bold");
        $(titleDiv).css("text-align", "left");
        $(titleDiv).text("Players");
        $(div).append(titleDiv);
        $(div).append(document.createElement("br"));
        $(topDiv).prepend(div);
        if (splitData[3]) {
          var characters = splitData[3].split("[&?]");
          for (var i=0; i < characters.length-1; i++ ) {
            var characterSplit = characters[i].split("&?");
            var charDiv = document.createElement("div");
            $(charDiv).css("text-align", "left");
            var charLink = document.createElement("a");
            $(charLink).attr("href", "#");
            $(charLink).css("color", "#FFFFFF");
            $(charLink).data("names", characterSplit[0]);
            $(charLink).data("characters", characterSplit[1]);
            $(charLink).click(function() {
              spawnWindow(true, null, '75%', '75%', true, '0', '0',  escape($(this).data("names")), 'static/viewCharacter.php?characterID=' + $(this).data("characters"));
            });
            var link = characterSplit[0] + " (" + characterSplit[4] + ")";
            $(charLink).text(link);
            $(charDiv).append(charLink);
            $(div).append(charDiv);
            $(div).append(document.createElement("br"));
          }
        }
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
});

function closeThis(topDivID) {
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  dcb.close();
}

</script>
<div class="newQuestParent" id="backstoryParent" style="text-align:center;">
<br /><button id="closeBtn" class="lightButton" type="button">close</button>
</div>