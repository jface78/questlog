<link type="text/css" rel="stylesheet" href="jquery_plugins/lwrte/jquery.rte.css" />
<script type="text/javascript" src="jquery_plugins/lwrte/jquery.rte.js"></script>
<script type="text/javascript" src="jquery_plugins/lwrte/jquery.rte.tb.js"></script>
<script type="text/javascript">

function addPost(textID, divID, charID) {
  var diceString = "";
  var select = $(document.getElementById(charID));
  var charID = select.val();

  if (charID == "") {
    charID = "NULL";
  }
  var topDiv = document.getElementById(divID);
  var dcb = getDCBFromChild(topDiv);
  var iFrame = document.getElementById(textID);
  var postVal;
  if (iFrame.contentDocument){ // FF
    postVal = iFrame.contentDocument.getElementsByTagName('body')[0].innerHTML;
  } else if (iFrame.contentWindow) { // IE
    postVal = iFrame.contentWindow.document.getElementsByTagName('body')[0].innerHTML;
  }
  var waitScreen = new StandbyScreen(document.getElementById("mainSection"));
  $.ajax({
    url: "services/managePosts.php",
    type: "POST",
    data: {operation: "addPost", questID: "<?php echo $_GET['questID'];?>", sectionID: "<?php echo $_GET['sectionID'];?>", postText: escape(postVal), characterID: charID},
    statusCode: {
      200: function(data) {
        refreshQuests(divID, data, true, "<?php echo $_GET['divID'];?>");
        dcb.close();
        waitScreen.close();
      },
      401: function(data) {
        waitScreen.close();
        $(topDiv).html("<b>401 / Unauthorized</b><br /><br />Authorities have been notified.");
        $(document.getElementById($(topDiv).data("closeBtnID"))).css("visibility", "hidden");
        $(document.getElementById($(topDiv).data("addBtnID"))).css("visibility", "hidden");
        $(document.getElementById("charSelectText")).css("visibility", "hidden");
      }
    }
  });
}

$(document).ready(function() {
  var inputAddID = generateDivID("inputAdd");
  var controlsID = generateDivID("controlsDiv");
  var inputAdd = document.getElementById(inputAddID);
  var topDivID = generateDivID("mainAddToQuestDiv");
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  dcb.textareaID = inputAddID;
  var closeBtnID = generateDivID("closeAddBtn");
  $(document.getElementById(closeBtnID)).attr("onClick", "javascript:closeThis('" + topDivID + "');");
  var addBtnID = generateDivID("addBtn");
  var charSelectID = generateDivID("characterSelect");
  var charSelect = document.getElementById(charSelectID);
  $(document.getElementById(addBtnID)).attr("onClick", "javascript:addPost('" + inputAddID + "', '" + topDivID + "','" + charSelectID + "');");
  $(topDiv).data("closeBtnID", closeBtnID);
  $(topDiv).data("charSelect", charSelect);
  $(topDiv).data("addBtnID", addBtnID);
  $(inputAdd).rte({
    css: ["css/wysiwyg.css"],
    width: "95%",
    height: "70%",
    controls_rte: rte_toolbar,
    controls_html: html_toolbar
  });
  
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
  var participant = '<?php echo $_GET['participant'];?>';
  if (participant != "forum") {
    $.ajax({
      url: "services/manageQuests.php",  
      type: "POST",
      data: {operation: "getQuestCharacters", questID: "<?php echo $_GET['questID'];?>"},
      statusCode: {
        200: function(data) {
          var splitNames = data.split("|");
          for (var i = 0; i < splitNames.length-1; i++) {
            //$(charSelect).append(splitNames[i]);
            var splitAgain = splitNames[i].split("&?");
            var option = document.createElement("option");
            
            $(option).val(splitAgain[1]);
            $(option).text(splitAgain[0]);
            $(charSelect).append(option); 
          }
        },
        401: function(data) {
          $(topDiv).html("<b>401 / Unauthorized</b><br /><br />Authorities have been notified.");
          $(document.getElementById($(topDiv).data("closeBtnID"))).css("visibility", "hidden");
          $(document.getElementById($(topDiv).data("addBtnID"))).css("visibility", "hidden");
          $(document.getElementById("charSelectText")).css("visibility", "hidden");
        }
      }
    });
  } else {
    $(document.getElementById("charSelectText")).css("visibility", "hidden");
  }
});
function closeThis(id) {
  var topDiv = document.getElementById(id);
  var dcb = getDCBFromChild(topDiv);
  dcb.close();
}

</script>
<div style="width:100%;height:100%;overflow:hidden;text-align:center;margin-bottom:3px;" id="mainAddToQuestDiv">
<textarea id="inputAdd" style="width:100%;height:50%;background-color:#a7a5a4;"></textarea>
</div>
<div id="controlsDiv" style="width:95%;">
<div style="text-align:center;float:left;padding-left:3px;">
<button type="button" class="lightButton" id="addBtn">add</button>
&nbsp;<button type="button" id="closeAddBtn" class="lightButton">cancel</button>
</div>
<div style="text-align:right;float:right;font-size:10px;" id="charSelectText">Post as: 
<select id="characterSelect" style="font-size:10px;text-overflow:ellipsis;"></select>
</div>
</div>