<link rel="stylesheet" href="css/diceRoller.css" type="text/css?sdfd">
<script type="text/javascript">
function setupDice(type) {
  if (soundEnabled) {
    document.getElementById("diceSound").play();
  }
  var number = document.getElementById("d" + type.toString() + "Number").selectedIndex + 1;
  var modifier = document.getElementById("d" + type.toString() + "Modifier").value;
  var resultDiv = document.getElementById("d" + type.toString() + "Results");
  var url = "services/fetchDice.php";
  var dice = $.ajax({
    type: "POST",
    data: {type: type, number: number, modifier: modifier},
    url: url
  });
  var results = dice.done(function(msg) {
    var resultArr = msg.split("&");
    var score = resultArr[0];
    var modifier = resultArr[1];
    var roll = number.toString() + "d" + type.toString() + " = " + score;
    var operator;
    if (parseInt(modifier) != 0) {
      if (modifier.indexOf ("+") == -1 && modifier.indexOf("-") == -1) {
        operator = "+";
        roll += " " + operator + " " + modifier + " = " + (parseInt(msg) + parseInt(modifier));
      } else if (modifier.indexOf("-") > -1) {
        operator = "-";
        modifier = modifier.substring(modifier.indexOf("-")+1, modifier.length);
        roll += " " + operator + " " + modifier + " = " + (parseInt(msg) - parseInt(modifier));
      } else if (modifier.indexOf("+") > -1) {
        operator = "+";
        modifier = modifier.substring(modifier.indexOf("+")+1, modifier.length);
        roll += " " + operator + " " + modifier + " = " + (parseInt(msg) + parseInt(modifier));
      }
    }
    setTimeout(function() {$(resultDiv).text(roll);}, 500);
  });
}
$(document).ready(function() {
  var topDivID = generateDivID("diceParent");
  var topDiv = document.getElementById(topDivID);
  var dcb = getDCBFromChild(topDiv);
  setTimeout(dcb.checkScrollers, 500, $(topDiv));
});
</script>
<div style="width:100%;height:100%;text-align:center;overflow:hidden;" id="diceParent">
<div class="rowBox" style="background-color:#646262;">
<div class="column1Box">
<figure class="diceImg">
<img src="img/dice/d4.png" alt="d4" width="30">
<figcaption>d4</figcaption>
</figure>
</div>
<div class="column2Box">
<div class="column2HeaderA">
Amount<br />
<select id="d4Number" class="column2ContentA">
<?php
for ($i = 1; $i < 21; $i++) {
  echo "<option value=\"" . $i . "\">" . $i . "</option>";
}
?>
</select>
</div>
<div class="column2HeaderB">
+ / -<br />
<input type="text" class="column2ContentB" value="0" style="text-align:center;" id="d4Modifier">
</div>
<div class="column3ContentC">
<button class="darkButton" style="height:18px;" onClick="javascript:setupDice(4);">roll</button>
</div>
</div>
<div class="column3Box" id="d4Results">

</div>
</div>

<div class="rowBox" style="background-color:#3E2C26;">
<div class="column1Box">
<figure class="diceImg">
<img src="img/dice/d6.png" alt="d6">
<figcaption>d6</figcaption>
</figure>
</div>
<div class="column2Box">
<div class="column2HeaderA">
Amount<br />
<select id="d6Number" class="column2ContentA">
<?php
for ($i = 1; $i < 21; $i++) {
  echo "<option value=\"" . $i . "\">" . $i . "</option>";
}
?>
</select>
</div>
<div class="column2HeaderB">
+ / -<br />
<input type="text" class="column2ContentB" value="0" style="text-align:center;" id="d6Modifier">
</div>
<div class="column3ContentC">
<button class="darkButton" style="height:18px;" onClick="javascript:setupDice(6);">roll</button>
</div>
</div>
<div class="column3Box" id="d6Results">

</div>
</div>

<div class="rowBox" style="background-color:#646262;">
<div class="column1Box">
<figure class="diceImg">
<img src="img/dice/d8.png" alt="d8">
<figcaption>d8</figcaption>
</figure>
</div>
<div class="column2Box">
<div class="column2HeaderA">
Amount<br />
<select id="d8Number" class="column2ContentA">
<?php
for ($i = 1; $i < 21; $i++) {
  echo "<option value=\"" . $i . "\">" . $i . "</option>";
}
?>
</select>
</div>
<div class="column2HeaderB">
+ / -<br />
<input type="text" class="column2ContentB" value="0" style="text-align:center;" id="d8Modifier">
</div>
<div class="column3ContentC">
<button class="darkButton" style="height:18px;" onClick="javascript:setupDice(8);">roll</button>
</div>
</div>
<div class="column3Box" id="d8Results">

</div>
</div>

<div class="rowBox" style="background-color:#3E2C26;">
<div class="column1Box">
<figure class="diceImg">
<img src="img/dice/d10.png" alt="d10">
<figcaption>d10</figcaption>
</figure>
</div>
<div class="column2Box">
<div class="column2HeaderA">
Amount<br />
<select id="d10Number" class="column2ContentA">
<?php
for ($i = 1; $i < 21; $i++) {
  echo "<option value=\"" . $i . "\">" . $i . "</option>";
}
?>
</select>
</div>
<div class="column2HeaderB">
+ / -<br />
<input type="text" class="column2ContentB" value="0" style="text-align:center;" id="d10Modifier">
</div>
<div class="column3ContentC">
<button class="darkButton" style="height:18px;" onClick="javascript:setupDice(10);">roll</button>
</div>
</div>
<div class="column3Box" id="d10Results">
</div>
</div>

<div class="rowBox" style="background-color:#646262;">
<div class="column1Box">
<figure class="diceImg">
<img src="img/dice/d12.png" alt="d12">
<figcaption>d12</figcaption>
</figure>
</div>
<div class="column2Box">
<div class="column2HeaderA">
Amount<br />
<select id="d12Number" class="column2ContentA">
<?php
for ($i = 1; $i < 21; $i++) {
  echo "<option value=\"" . $i . "\">" . $i . "</option>";
}
?>
</select>
</div>
<div class="column2HeaderB">
+ / -<br />
<input type="text" class="column2ContentB" value="0" style="text-align:center;" id="d12Modifier">
</div>
<div class="column3ContentC">
<button class="darkButton" style="height:18px;" onClick="javascript:setupDice(12);">roll</button>
</div>
</div>
<div class="column3Box" id="d12Results">
</div>
</div>

<div class="rowBox" style="background-color:#3E2C26;">
<div class="column1Box">
<figure class="diceImg">
<img src="img/dice/d20.png" alt="d20">
<figcaption>d20</figcaption>
</figure>
</div>
<div class="column2Box">
<div class="column2HeaderA">
Amount<br />
<select id="d20Number" class="column2ContentA">
<?php
for ($i = 1; $i < 21; $i++) {
  echo "<option value=\"" . $i . "\">" . $i . "</option>";
}
?>
</select>
</div>
<div class="column2HeaderB">
+ / -<br />
<input type="text" class="column2ContentB" value="0" style="text-align:center;" id="d20Modifier">
</div>
<div class="column3ContentC">
<button class="darkButton" style="height:18px;" onClick="javascript:setupDice(20);">roll</button>
</div>
</div>
<div class="column3Box" id="d20Results">
</div>
</div>

<div class="rowBox" style="background-color:#646262;">
<div class="column1Box">
<figure class="diceImg">
<img src="img/dice/percentile.png" alt="percentile">
<figcaption>d100</figcaption>
</figure>
</div>
<div class="column2Box">
<div class="column2HeaderA">
Amount<br />
<select id="d100Number" class="column2ContentA">
<?php
for ($i = 1; $i < 21; $i++) {
  echo "<option value=\"" . $i . "\">" . $i . "</option>";
}
?>
</select>
</div>
<div class="column2HeaderB">
+ / -<br />
<input type="text" class="column2ContentB" value="0" style="text-align:center;" id="d100Modifier">
</div>
<div class="column3ContentC">
<button class="darkButton" style="height:18px;color:#FFFFFF;" onClick="javascript:setupDice(100);">roll</button>
</div>
</div>
<div class="column3Box" id="d100Results">

</div>
</div>

</div>
