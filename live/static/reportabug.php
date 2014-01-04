<link rel="stylesheet" href="css/reportabug.css" type="text/css">
<script type="text/javascript">
function closeDiv(divID) {
  var dcb = getDCBFromChild(document.getElementById(divID));
  dcb.close();
}
function sendReport(topDivID) {
  var topDiv = document.getElementById(topDivID);
  var errorDiv = $(topDiv).find("#bugErrorDiv");
  var issueSelect = $(topDiv).find("#issueSelect");
  var browserSelect = $(topDiv).find("#browserSelect");
  var osSelect = $(topDiv).find("#osSelect");
  var bugDescrText = $(topDiv).find("#bugDescrText");
  if ($(issueSelect).val() == "default") {
    $(errorDiv).text("You must select your issue from the drop-down menu.");
  } 
  else if ($(browserSelect).val() == "default") {
    $(errorDiv).text("You must select your browser from the drop-down menu.");
  } 
  else if ($(osSelect).val() == "default") {
    $(errorDiv).text("You must select your operating system from the drop-down menu.");
  } 
  else if ($(bugDescrText).val() == "") {
    $(errorDiv).text("Please tell us the problem in the text area above.");
  } 
  else {
    var dcb = getDCBFromChild(topDiv);
    $.ajax({
      url: "services/manageMisc.php",  
      type: "POST",
      data: {operation: "sendBug", issue: $(issueSelect).val(), browser: $(browserSelect).val(),
             os: $(osSelect).val(), descr:$(bugDescrText).val()},
      statusCode: {
        200: function(data) {
          $(topDiv).css("text-align", "center");
          var html = "Your report has been submitted.<br />It will be investigated sometime maybe.<br /><br />";
          html += "<button class=\"lightButton\" onClick=\"javascript:closeDiv('" + topDivID + "');\" type=\"button\">close</button>";
          $(topDiv).html(html);
          dcb.scrollToTop();
        },
        400: function(data) {
          var html = "<b>She's dead, Jim.</b><br />";
          html += "(Error sending report at this time. Please try again later.<br /><br />";
          html += "<button class=\"lightButton\" onClick=\"javascript:closeDiv('" + topDivID + "');\" type=\"button\">close</button>";
          $(topDiv).html(html);
          dcb.scrollToTop();
        },
        500: function(data) {
          var html = "<b>She's dead, Jim.</b><br />";
          html += "(Error sending report at this time. Please try again later.<br /><br />";
          html += "<button class=\"lightButton\" onClick=\"javascript:closeDiv('" + topDivID + "');\" type=\"button\">close</button>";
          $(topDiv).html(html);
          dcb.scrollToTop();
        }
      }
    }); 
  }
}
$(document).ready(function() {
  var topDivID = generateDivID("bugParent");
  var topDiv = document.getElementById(topDivID);
  var btn = $(topDiv).find("#sendBugBtn");
  var dcb = getDCBFromChild(topDiv);
  $(btn).attr("onClick", "javascript:sendReport('" + topDivID + "');");
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
});
</script>
<div id="bugParent" class="topBugDiv">
<div id="bugLeftPanel">
<figure style="margin:0px auto;">
<img src="img/bug.jpg" alt="A bug, you say?" id="bugImg"><br />
</figure>
</div>
<div id="bugRightPanel" style="line-spacing:40px;">
<div class="bugSelectPanel">
<div class="bugSelectLabel">
<div class="bugLabel">Type:&nbsp;</div>
</div>
<div class="bugSelecterDiv">
<select class="bugSelecter" id="issueSelect">
<option selected value="default">Choose...</option>
<option value="account">Account Issue</option>
<option value="ui">User Interface</option>
<option value="create">Quest Creation</option>
<option value="character">Character Creation</option>
<option value="dice">Dice Roller</option>
<option value="other">Other Issue</option>
</select></div>
</div><br /><br />
<div class="bugSelectPanel">
<div class="bugSelectLabel">
<div class="bugLabel">Your browser:&nbsp;</div>
</div>
<div class="bugSelecterDiv">
<select class="bugSelecter" id="browserSelect">
<option selected value="default">Choose...</option>
<option value="chrome">Chrome</option>
<option value="firefox">Firefox</option>
<option value="opera">Opera</option>
<option value="ie">IE... srsly?</option>
<option value="other">Other</option>
</select></div>
</div><br /><br />
<div class="bugSelectPanel">
<div class="bugSelectLabel">
<div class="bugLabel">Operating System:&nbsp;</div>
</div>
<div class="bugSelecterDiv">
<select class="bugSelecter" id="osSelect">
<option selected value="default">Choose...</option>
<option value="windows">Windows</option>
<option value="osx">Mac/OSX</option>
<option value="linux">Linux</option>
<option value="bsd">BSD</option>
<option value="commodore">...Commodore 64?</option>
<option value="other">Other</option>
</select></div>
</div><br /><br />
<div class="bugSelectPanel">
<div class="bugSelectLabel">
<div class="bugLabel">Details&nbsp;</div>
</div>
<div class="bugSelecterDiv">
<textarea id="bugDescrText"></textarea>
</div></div>
<br /><br />
<div id="bugBottomDiv">
<div id="bugErrorDiv"></div>
<button type="button" id="sendBugBtn" class="lightButton">send</button>
</div>
</div>
</div>