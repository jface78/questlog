<script type="text/javascript">
$(document).ready(function() {
  var topDivID = generateDivID("browserParent");
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
});
</script>
<div id="browserParent" style="width:100%;height:100%;overflow:hidden;">
<div style="float:left;white-space:normal;width:50%;">
<div style="font-size:32px;float:left;width:100%;font-weight:bold;">LOL, <?php echo ucwords($_GET['browser']);?>.</div><br /><br /><br />
Your browser is a known member of the Communist Party of America and will not work on any true goddamn site in these United States.<br /><br />
We recommend you use <a href="http://www.google.com/chrome/" target="new" style="text-decoration:underline;">this one instead,</a> comrade.
</div>
<div style="float:right;width:50%;text-align:center;margin:0px auto;">
<figure style="margin:0px auto;">
<img src="img/commie.png" alt="red menace!"><br />
<figcaption style="text-align:center;">Translation: Workers of the World Use <?php echo ucwords($_GET['browser']);?>.</figcaption>
<figure>
</div>
</div>