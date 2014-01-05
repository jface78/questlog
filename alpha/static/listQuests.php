<script type="text/javascript">function closeAndSignIn(dcbID) {  spawnWindow(true, null, '50%', '50%', true, '0', '0', 'Log In / Join', 'static/signin.php');  var dcb = getDCBFromID(dcbID);  dcb.close();}$(document).ready(function() {  var topDivID = generateDivID("mainQuestsParent");  var topDiv = document.getElementById(topDivID);  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));  $(topDiv).data("waitScreen", waitScreen);  var questDiv;  var pcCounter = 0;  var gmCounter = 0;  var otherCounter = 0;    var gmQuests = false;  var pcQuests = false;    $.ajax({    url: "services/manageQuests.php",      type: "POST",    data: {operation: "getAllQuests"},    statusCode: {      200: function(data) {        var splitArray = data.split("|");        for (var i=0; i < splitArray.length-1; i++) {          var subSplit = splitArray[i].split("&");          var div = document.createElement("div");          var divClass;          var fontColor;          var hoverColor;          if (subSplit[0] == "gm") {            gmQuests = true;            if (gmCounter % 2 == 0) {              divClass = "grayRow";              fontColor = "#FFFFFF";              hoverColor = "#C8C8C8";            } else {              divClass = "brownRow";              fontColor = "#FFFFFF";              hoverColor = "#C8C8C8";            }            gmCounter++;          } else if (subSplit[0] == "pc") {            pcQuests = true;            if (pcCounter % 2 == 0) {              divClass = "grayRow";              fontColor = "#FFFFFF";              hoverColor = "#C8C8C8";            } else {              divClass = "brownRow";              fontColor = "#FFFFFF";              hoverColor = "#C8C8C8";            }            pcCounter++;          } else {            if (otherCounter % 2 == 0) {              divClass = "grayRow";              fontColor = "#FFFFFF";              hoverColor = "#C8C8C8";            } else {              divClass = "brownRow";              fontColor = "#FFFFFF";              hoverColor = "#C8C8C8";            }            otherCounter++;          }          if (subSplit[3] == "") {            subSplit[3] = "&nbsp;";          }          if (subSplit[3].length > 200) {            subSplit[3] = subSplit[3].substring(0, 200) + "...";          }          var participant = subSplit[0];          $(div).attr("class", divClass);          $(div).data("questID", subSplit[1]);          $(div).data("title", escape(subSplit[2]));          $(div).data("fontColor", fontColor);          $(div).data("hoverColor", hoverColor);          $(div).data("participant", participant);          $(div).css("color", fontColor);          $(div).css("width", "90%");          $(div).css("display", "table");          $(div).css("height", "32px");          $(div).css("margin-bottom", "5px");          $(div).css("box-shadow", "3px 3px 3px #888888");          $(div).css("text-align", "left");          $(div).css("float", "left");                    var innerDiv = document.createElement("div");          $(innerDiv).css("display", "table-cell");          $(innerDiv).css("vertical-align", "middle");          $(innerDiv).css("height", "100%");                    var titleDiv = document.createElement("div");          $(titleDiv).css("width", "25%");          $(titleDiv).css("margin-left", "15px");          $(titleDiv).css("white-space", "normal");          $(titleDiv).css("overflow", "hidden");          $(titleDiv).css("float", "left");          $(titleDiv).html(subSplit[2]);          var descrDiv = document.createElement("div");          $(descrDiv).css("padding-right", "5px");          $(descrDiv).css("width", "50%");          $(descrDiv).css("white-space", "normal");          $(descrDiv).css("overflow", "hidden");          $(descrDiv).css("float", "left");          $(descrDiv).html(subSplit[3]);          var gmDiv = document.createElement("div");          $(gmDiv).css("width", "15%");          $(gmDiv).css("margin-right", "15px");          $(gmDiv).css("overflow", "hidden");          $(gmDiv).css("white-space", "normal");          $(gmDiv).css("float", "left");          if (subSplit[0] == "gm") {            questDiv = $(topDiv).find("#gmQuests");            $(gmDiv).html("You");          } else if (subSplit[0] == "pc"){            questDiv = $(topDiv).find("#pcQuests");            $(gmDiv).html(subSplit[4]);          }          else {            questDiv = $(topDiv).find("#otherQuests");            $(gmDiv).html(subSplit[4]);          }          var playersText = "";          for (var s=4; s < subSplit.length -1; s++) {            if (subSplit[s] != "") {              playersText += subSplit[s];              if (s < subSplit.length -2) {                playersText += ", ";              }            }          }          var finalText;          if (playersText.length > 40) {            finalText = playersText.substr(0,39) + "...";          } else {            finalText = playersText;          }          $(div).attr("title", finalText);          $(innerDiv).append(titleDiv);          $(innerDiv).append(descrDiv);          $(innerDiv).append(gmDiv);          $(div).append(innerDiv);          $(div).click(function() {            var url = "static/listSections.php?questTitle=" + escape($(this).data("title")) + "&questID=" + $(this).data("questID") + "&participant=" + $(this).data("participant");            spawnWindow(true, null, '80%', '80%', true, '0', '0', $(this).data("title") + "%20-%20Sections", url, "#F0F0F0", false, false, false);          });          $(div).hover(            function () {              $(this).css("cursor", "pointer");              $(this).css("color", $(this).data("hoverColor"));            },             function () {              $(this).css("color", $(this).data("fontColor"));            }          );          $(questDiv).append(div);        }        if (!pcQuests) {          $(topDiv).find("#pcQuestsList").remove();        } else {        }        if (!gmQuests) {          $(topDiv).find("#gmQuestsList").remove();        }      },      404: function(data) {        waitScreen.close();      },      401: function(data) {        $(topDiv).append("You must be logged in to view the forums.<br />");        $(topDiv).append("Click <u><a href=\"#\" onClick=\"javascript:closeAndSignIn('" + dcb.id + "');\">here</a></u> to sign in or create an account.");        waitScreen.close();      }    }  }).done(function (data) {    var dcb = getDCBFromChild(topDiv);    $(topDiv).data("waitScreen").close();    setTimeout(dcb.checkScrollers, 500, $(topDiv));      });})</script><div style="width:100%;height:100%;overflow:hidden;text-align:center;" id="mainQuestsParent"><div id="gmQuestsList"><div style="width:100%;text-align:left;margin-left:15px;">GM Quests</div><div style="width:90%;font-weight:bold;float:left;margin-bottom:3px;"><br /><div style="width:25%;text-align:left;float:left;overflow:hidden;white-space:nowrap;margin-left:15px;">Quest</div><div style="width:50%;text-align:left;float:left;overflow:hidden;white-space:nowrap;">Description</div><div style="width:15%;text-align:left;float:left;overflow:hidden;white-space:nowrap;padding-left:5px;margin-right:15px;">GM</div></div><div style="width:100%;" id="gmQuests"></div><br /><br /></div><div id="pcQuestsList"><div style="float:left;width:100%;text-align:left;margin-left:15px;"><br />PC Quests</div><div style="width:90%;font-weight:bold;float:left;margin-bottom:3px;"><br /><div style="width:25%;text-align:left;float:left;overflow:hidden;white-space:nowrap;margin-left:15px;">Quest</div><div style="width:50%;text-align:left;float:left;overflow:hidden;white-space:nowrap;">Description</div><div style="width:15%;text-align:left;float:left;overflow:hidden;white-space:nowrap;padding-left:5px;margin-right:15px;">GM</div></div><div style="width:100%;" id="pcQuests"></div><br /><br /></div><div style="float:left;width:100%;text-align:left;margin-left:15px;"><br />Other Quests</div><div style="width:90%;font-weight:bold;float:left;margin-bottom:3px;"><br /><div style="width:25%;text-align:left;float:left;overflow:hidden;white-space:nowrap;margin-left:15px;">Quest</div><div style="width:50%;text-align:left;float:left;overflow:hidden;white-space:nowrap;">Description</div><div style="width:15%;text-align:left;float:left;overflow:hidden;white-space:nowrap;padding-left:5px;margin-right:15px;">GM</div></div><div style="width:100%;" id="otherQuests"></div><br /><br /></div>