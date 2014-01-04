<script type="text/javascript">
$(document).ready(function() {
  var topDivID = generateDivID("contactParent");
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
});
</script>
<div id="contactParent" style="width:100%;height:100%;overflow:hidden;">
<div style="float:right;text-align:center;width:160px;margin:0px auto;">
<figure style="margin:0px auto;">
<img src="img/contact.png" alt="contact"><br />
</figure>
</div>
<h2>
Don't expect a response.
</h2>

</div>