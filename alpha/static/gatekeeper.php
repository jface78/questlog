<script type="text/javascript">
function handleLogin(divID) {
  var topDiv = document.getElementById(divID);
  var msg = $(topDiv).find("#loginMsg");
  $(msg).text("invalid credentials");
}

$(document).ready(function() {
  var topDivID = generateDivID("gateParent");
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  var btn = $(topDiv).find("#loginBtn");
  $(btn).attr("onClick", "javascript:handleLogin('" + topDivID + "');");
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
});
</script>
<div id="gateParent" style="width:100%;height:100%;overflow:hidden;white-space:nowrap;text-align:center;">
<img alt="gatekeeper" src="img/gatelogo.png"><br />
<span style="width:200px;vertical-align:middle;">
<div style="font-family:monospace;color:#05fe2e;width:100px;text-align:right;display:inline;">user:&nbsp;</div>
<div style="width:100px;display:inline;text-align:left;">
<input type="text" style="border:1px solid #05fe2e;color:#05fe2e;background-color:#000000;font-family:monospace;width:140px;">
</div>
</span><br />
<span style="width:240px;vertical-align:middle;">
<div style="font-family:monospace;color:#05fe2e;width:100px;text-align:right;display:inline;">pass:&nbsp;</div>
<div style="width:140px;display:inline;text-align:left;">
<input type="password" style="border:1px solid #05fe2e;color:#05fe2e;background-color:#000000;font-family:monospace;width:140px;">
</div>
</span><br />
<span style="width:240px;vertical-align:middle;">
<div style="font-family:monospace;color:#05fe2e;width:100px;text-align:right;display:inline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
<div style="width:140px;display:inline;text-align:left;">
<button id="loginBtn" style="border:1px solid #05fe2e;width:140px;font-size:10px;color:#05fe2e;background-color:#000000;font-family:monospace;">login</button>
</div>
</span>
<br /><br />
<span style="width:240px;vertical-align:middle;">
<div style="font-family:monospace;color:#05fe2e;width:100px;text-align:right;display:inline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
<div style="font-family:monospace;color:#05fe2e;font-size:10px;width:140px;display:inline;text-align:left;" id="loginMsg">
(access restricted)
</div>
</span>
</div>