<script type="text/javascript">
$(document).ready(function() {
  var topDiv = document.getElementById("supportParent");
  var dcb = getDCBFromChild(topDiv);
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
});
</script>
<div id="supportParent" style="width:100%;height:100%;overflow:hidden;">
<div style="float:left;text-align:center;width:160px;margin:1px auto;">
<figure style="margin:0px auto;">
<img src="img/support.png" alt="all-day support"><br />
<figcaption style="font-weight:bold;font-size:9px;">Sorry.</figcaption>
</figure>
</div>
<div style="white-space:normal;">
<h1 style="font-size:18px;font-weight:bold;">Sorry, kid.</h1>
No support available at this time.
</div>
</div>