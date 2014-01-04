<link type="text/css" rel="stylesheet" href="jquery_plugins/lwrte/jquery.rte.css" />
<script type="text/javascript" src="jquery_plugins/lwrte/jquery.rte.js"></script>
<script type="text/javascript" src="jquery_plugins/lwrte/jquery.rte.tb.js"></script>
<script type="text/javascript">

function addThread(textID, divID, titleID) {
  var diceString = "";
  var titleVal = $(document.getElementById(titleID)).val();
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
    url: "services/manageQuests.php",
    type: "POST",
    data: {operation: "addThread", questID: "<?php echo $_GET['questID'];?>", postText: escape(postVal), title: titleVal},
    statusCode: {
      200: function(data) {
        var parentDiv = document.getElementById("<?php echo $_GET['divID'];?>");
        var secDCB = getDCBFromChild(parentDiv);
        secDCB.reload();
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
  var inputAdd = document.getElementById(inputAddID);
  var titleID = generateDivID("threadTitle");
  var topDivID = generateDivID("newThreadParent");
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  dcb.textareaID = inputAddID;
  var closeBtnID = generateDivID("closeAddBtn");
  $(document.getElementById(closeBtnID)).attr("onClick", "javascript:closeThis('" + topDivID + "');");
  var addBtnID = generateDivID("addBtn");
  var charSelectID = generateDivID("characterSelect");
  var charSelect = document.getElementById(charSelectID);
  $(document.getElementById(addBtnID)).attr("onClick", "javascript:addThread('" + inputAddID + "', '" + topDivID + "','" + titleID + "');");
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
});
function closeThis(id) {
  var topDiv = document.getElementById(id);
  var dcb = getDCBFromChild(topDiv);
  dcb.close();
}

</script>
<div style="width:100%;height:100%;overflow:hidden;text-align:center;margin-bottom:3px;" id="newThreadParent">
<div style="width:100%;height:20px;vertical-align:middle;margin-bottom:2px;">
Title: <input type="text" id="threadTitle" style="background-color:#a7a5a4;color:#000000;width:85%;">
</div>
<textarea id="inputAdd" style="width:95%;height:50%;background-color:#a7a5a4;border:1px solid #000000;"></textarea>
</div>
<div style="text-align:center;width:95%;">
<button type="button" class="lightButton" id="addBtn">add</button>
&nbsp;<button type="button" id="closeAddBtn" class="lightButton">cancel</button>
</div>