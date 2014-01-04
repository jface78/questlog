<link type="text/css" rel="stylesheet" href="jquery_plugins/lwrte/jquery.rte.css" />
<script type="text/javascript" src="jquery_plugins/lwrte/jquery.rte.js"></script>
<script type="text/javascript" src="jquery_plugins/lwrte/jquery.rte.tb.js"></script>
<script type="text/javascript">

function updatePost(textID, divID, charID) {
  var topDiv = document.getElementById(divID);
  var diceArray = $(topDiv).data("diceArray");
  var dcb = getDCBFromChild(topDiv);
  var diceString = "";
  //var postVal = $(document.getElementById(textID)).get_content();
  var iFrame = document.getElementById(textID);
  var postVal;
  if (iFrame.contentDocument){ // FF
    postVal = iFrame.contentDocument.getElementsByTagName('body')[0].innerHTML;
  } else if (iFrame.contentWindow) { // IE
    postVal = iFrame.contentWindow.document.getElementsByTagName('body')[0].innerHTML;
  }
  var select = $(document.getElementById(charID));
  var charID = $(select).val();
  if (charID == undefined) {
    charID = "NULL";
  }
  $.ajax({
    url: "services/managePosts.php",  
    type: "POST",
    data: {operation: "updatePost", postID: "<?php echo $_GET['postID'];?>", postText: postVal, characterID:charID},
    statusCode: {
      200: function(data) {
        refreshQuests('<?php echo $_GET['tableID'];?>', data);
        dcb.close();
      },
      401: function(data) {
        $(topDiv).html("<b>401 / Unauthorized</b><br /><br />Authorities have been notified.");
      }
    }
  });
}

$(document).ready(function() {
  var controlsID = generateDivID("controlsDiv");
  var inputEditID = generateDivID("inputEdit");
  var inputEdit = document.getElementById(inputEditID);
  var topDivID = generateDivID("mainEditQuestDiv");
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  var charSelectID = generateDivID("characterSelect");
  var charSelect = document.getElementById(charSelectID);
  var closeBtnID = generateDivID("closeEditBtn");
  $(document.getElementById(closeBtnID)).attr("onClick", "javascript:closeThis('" + topDivID + "');");
  var updateBtnID = generateDivID("updateEditBtn");
  $(document.getElementById(updateBtnID)).attr("onClick", "javascript:updatePost('" + inputEditID + "', '" + topDivID + "','" + charSelectID +"');");
  var diceArray = [];
  $(topDiv).data("diceArray", diceArray);
  $.ajax({
    url: "services/managePosts.php",  
    type: "POST",
    data: {operation: "getPost", postID: "<?php echo $_GET['postID'];?>"},
    statusCode: {
      200: function(data) {
        $(topDiv).css("visibility", "visible");
        $(document.getElementById(controlsID)).css("visibility", "visible");
        //var parentHeight = $(topDiv).parent().parent().height() - 100;
        $(inputEdit).val(data.toString());
        $(inputEdit).rte({
          css: ["css/wysiwyg.css"],
          width: "95%",
          height: "70%",
          controls_rte: rte_toolbar,
          controls_html: html_toolbar
        });
      },
      401: function(data) {
        $(topDiv).html("<b>401 / Unauthorized</b><br /><br />Authorities have been notified.");
      }
    }
  });
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
  var participant = '<?php echo $_GET['participant'];?>';
  if (participant != "forum") {
    $.ajax({
      url: "services/manageQuests.php",  
      type: "POST",
      data: {operation: "getQuestCharacters", questID: "<?php echo $_GET['questID'];?>", postID: "<?php echo $_GET['postID'];?>"},
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
        }
      }
    });
  } else {
    $(document.getElementById(controlsID)).css("visibility", "visible");
    $(document.getElementById("charSelectText")).css("visibility", "hidden");
  }
});
function closeThis(id) {
  var topDiv = document.getElementById(id);
  var dcb = getDCBFromChild(topDiv);
  dcb.close();
}

</script>
<div style="width:100%;height:100%;overflow:hidden;text-align:center;margin-bottom:3px;padding-left:3px;visibility:hidden;" id="mainEditQuestDiv">
<textarea id="inputEdit" style="width:95%;height:100%;background-color:#a7a5a4;font-size:10px;font-family:Verdana;"></textarea>
</div>
<div style="width:95%;visibility:hidden;" id="controlsDiv">
<div style="text-align:center;float:left;padding-left:3px;">
<button type="button" class="lightButton" id="updateEditBtn">update</button>
&nbsp;<button type="button" id="closeEditBtn" class="lightButton">cancel</button>
</div>
<div style="text-align:right;float:right;font-size:10px;" id="charSelectText">Posted as: 
<select id="characterSelect" style="font-size:10px;text-overflow:ellipsis;"></select>
</div>
</div>