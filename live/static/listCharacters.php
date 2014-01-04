
<script type="text/javascript">

function getNewCharWindow(topDivID) {
  spawnWindow(true, null, '75%', '75%', true, '0', '0', 'New Character', 'static/makeNewCharacter.php');
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  dcb.close();
}
function refresh(divID) {
  var topDiv = document.getElementById(divID);
  var dcb = getDCBFromChild(topDiv);
  dcb.reload();
  dcb.scrollToTop();
}
function confirmDelete(name, id, divID) {
  var topDiv = document.getElementById(divID);
  var dcb = getDCBFromChild(topDiv);
  if (confirm("Delete " + name + "? Are you sure?")) {
    var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
    $.ajax({
      type: "POST",
      url: "services/manageCharacters.php",
      data: {operation: "deleteCharacter", characterID:id},
      statusCode: {
        401: function() {
          $(topDiv).html("<b>401 / Unauthorized</b><br /><br />Authorities have been notified.");
          dcb.scrollToTop();
          waitScreen.close();
        },
        409: function(data) {
          $(topDiv).css("text-align", "center");
          $(topDiv).html("<b>409 / Conflict</b><br /><br />");
          $(topDiv).append(name + " is still involved in the quest \"" + data.responseText + "\,\" and must be removed by the GM before s/he can be deleted.");
          $(topDiv).append("<br /><br /><button class=\"lightButton\" onClick=\"javascript:refresh('" + divID + "');\">okay</button>");
          dcb.scrollToTop();
          waitScreen.close();
        },
        200: function() {
          var editDCBs = getDCBByContent("static/viewCharacter.php?characterID=" + id);
          for (var i=0; i < editDCBs.length; i++) {
            editDCBs[i].close();
          }
          var dcb = getDCBFromChild(topDiv);
          dcb.scrollToTop();
          dcb.reload();
          waitScreen.close();
        }
      }
    });
  }
}
$(document).ready(function() {
  var topDivID = generateDivID("listCharactersParent");
  var topDiv = document.getElementById(topDivID);
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  $(topDiv).data("waitScreen", waitScreen);
  var serviceURL = "services/manageCharacters.php";

  $.ajax({
    type: "POST",
    url: serviceURL,
    data: {operation: "listCharacters"},
    statusCode: {
      404: function() {
        var text = "No characters created.<br /><br /><a href=\"#\" onClick=\"javascript:getNewCharWindow('" + topDivID + "');\">Make one.</u></a>";
        $(topDiv).css("text-align", "center");
        $(topDiv).html(text);
        waitScreen.close();
      },
      200: function(data) {
        var text = "";
        var characters = data.toString().split("|");
        var divColor;
        for (var i=0; i < characters.length-1; i++) {
          if (i % 2 == 0) {
            divClass = "brownRow";
          } else {
            divClass = "grayRow";
          }
          var charDiv = document.createElement("div");
          $(charDiv).css("float", "left");
          $(charDiv).css("width", "300px");
          $(charDiv).css("height", "50px");
          $(charDiv).css("vertical-align", "middle");
          $(charDiv).css("margin", "5px");
          $(charDiv).addClass(divClass);
          $(charDiv).css("padding", "15px");
          $(charDiv).css("display", "table");
          var imgDiv = document.createElement("div");
          $(imgDiv).css("display", "table-cell");
          $(imgDiv).css("height", "50px");
          $(imgDiv).css("width", "50px");
          $(imgDiv).css("vertical-align", "middle");
          var textDiv = document.createElement("div");
          $(textDiv).css("text-align", "left");
          $(textDiv).css("display", "table-cell");
          $(textDiv).css("vertical-align", "middle");
          $(textDiv).css("width", "50%");
          var buttonsDiv = document.createElement("div");
          $(buttonsDiv).css("display", "table-cell");
          $(buttonsDiv).css("vertical-align", "middle");
          var editBtn = document.createElement("button");
          $(editBtn).attr("type", "button");
          $(editBtn).addClass("darkButton");
          $(editBtn).html("edit");
          var deleteBtn = document.createElement("button");
          $(deleteBtn).attr("type", "button");
          $(deleteBtn).addClass("darkButton");
          $(deleteBtn).html("delete");
          var subArray = characters[i].split("&?");
          $(deleteBtn).attr("onClick", "javascript:confirmDelete('" + subArray[1] +"', '" + subArray[0] + "', '" + topDivID +"');");
          $(editBtn).attr("onClick", "javascript:spawnWindow(true, null, '75%', '75%', true, '0', '0', '" + escape(subArray[1]) + "', 'static/viewCharacter.php?characterID=" + subArray[0] + "');");
          text = '<img alt="' + subArray[1] + '" src="' + subArray[2] + '" style="border:1px solid #F0F0F0;max-width:50px;max-height:50px;">';
          $(imgDiv).html(text);
          text = '&nbsp;<span id=\"charName' + i + '"\">' + subArray[1] + "</span>";
          $(buttonsDiv).append(editBtn);
          $(buttonsDiv).append("&nbsp;");
          $(buttonsDiv).append(deleteBtn);
          $(textDiv).html(text);
          $(charDiv).append(imgDiv);
          $(charDiv).append(textDiv);
          $(charDiv).append(buttonsDiv);
          $(editBtn).data("charName", "charName" + i);
          $(deleteBtn).data("charName", "charName" + i);
         
          $(topDiv).append(charDiv);
        }
      }
    },
  }).done(function (data) {
    var dcb = getDCBFromChild(topDiv);
    $(topDiv).data("waitScreen").close();
    setTimeout(dcb.checkScrollers, 500, $(topDiv));
  });
});
</script>

<div id="listCharactersParent" style="width:100%;height:100%;line-height:20px;overflow:hidden;">

</div>