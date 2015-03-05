<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="generateNames.js"></script>
<script>
function fetchCharacters() {
  $('#characterList').empty();
  for (var i=0; i < 10; i++) {
    var div = document.createElement('div');
    $(div).css('border', '1px solid black');
    $(div).css('padding', '10px');
    $(div).append('<b>' + makeCharacterName() + '</b><br />');
    $(div).append(makeCharacterTraits());
    $('#characterList').append(div);
  }
}
</script>
</head>
<body>
<button onclick="javascript:fetchCharacters();">generate 10 random NPCs</button><br />
<div id="characterList"></div>
</body>
</html>