var TEMPLATE_URL = 'templates/';
var bubbles = [];

$(document).ready(function() {
  if (!window.jQuery.ui) {
    var head = document.getElementsByTagName('head');
    var script = document.createElement('script');
    $(script).attr('src', 'js/plugins/jquery-ui.min.js');
    $(head[0]).append(script);
  }
  launchMenu();
});

function launchMenu() {
  var menu = new QuestlogBubble('20%', '90%', '75%', '5%', 'Settings', 'settings.html');
  bubbles.push(menu);
}

function spawnTest() {
  var bubble2 = new QuestlogBubble();
  bubbles.push(bubble2);
}