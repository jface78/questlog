<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="generateNames.js"></script>
<script>
function getRandomInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}
function ucwords(str) {
  return (str + '')
    .replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function($1) {
      return $1.toUpperCase();
    });
}

function fetchCharacters() {
  $('#characterList').empty();
  for (var i=0; i < 5; i++) {
    var div = document.createElement('div');
    $(div).css('border', '1px solid black');
    $(div).css('padding', '10px');
    var char = new RandomNPC();
    char.generate();
    $(div).append('<b>' + char.name + ' ' + char.title + ', </b>' + char.getNumerator(char.age) + ' ' + char.age + ' ' + char.gender + ' ' + char.race + '.' );
    $(div).append('<br />');
    $(div).append(ucwords(char.getNumerator(char.job)) + ' ' + char.job + ' of ' + char.jobSkill + ' skill with ' + char.getNumerator(char.trait1) +
                           ' ' + char.trait1 + ' and ' + char.trait2 + ' demeanor.');
    $(div).append(char.description);
    $('#characterList').append(div);
  }
}
</script>
</head>
<body>
<button onclick="javascript:fetchCharacters();">generate 5 random NPCs</button><br />
<div id="characterList"></div>
</body>
</html>