<script type="text/javascript">
var rand = Math.floor((Math.random()*9999)+1);
document.getElementById("alertParent").id = "alertParent" + rand;
var div = "alertParent" + rand;
$(document).ready(function() {
  $.ajax({
    dataType: "text",
    type: "POST",
    data: {operation: "getContent", alertID: <?php echo $_GET['id'];?>},
    url: "services/fetchAlerts.php",
    statusCode: {
      404: function(data) {
        //alert("NONE");
      },
      200: function(data) {
        var splitData = data.split("|");
        $(document.getElementById("alertParent" + rand)).html(splitData[1]);
        if (document.getElementById("declineBtn")) {
          var decline = document.getElementById("declineBtn");
          $(decline).attr("onClick", "javascript:declineQuest('<?php echo $_GET['id'];?>', '" + div + "');");
        }
        if (document.getElementById("declineAndBlockBtn")) {
          var declineAndBlock = document.getElementById("declineAndBlockBtn");
          $(declineAndBlock).attr("onClick", "javascript:declineQuest('<?php echo $_GET['id'];?>', '" + div + "', '" + splitData[2] + "');");
        }
        if (document.getElementById("acceptBtn")) {
          var accept = document.getElementById("acceptBtn");
          $(accept).attr("onClick", "javascript:acceptQuest('<?php echo $_GET['id'];?>', '" + div + "');");
        }
        var topDiv = document.getElementById("alertParent" + rand);
        var dcb = getDCBFromChild(topDiv);
        setTimeout(dcb.checkScrollers, 500, $(topDiv));
      }
    }
  });
});
</script>
<div id="alertParent" style="width:100%;height:100%;overflow:hidden;">

</div>
