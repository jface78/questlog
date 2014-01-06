var TEMPLATE_URL = 'templates/';
var SERVICE_URL = 'services/';

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
  var settings = new Settings();
  $(settings).on('loadComplete', function(event) {
    var menu = new QuestlogBubble('20%', '90%', '75%', '5%', 'Settings');
    $(menu).on('loadComplete', function(event) {
      menu.setContent(settings.dom);
      bubbles.push(menu);
    });
    menu.setup();
  });
  settings.setup();
}

function spawnTest() {
  var bubble2 = new QuestlogBubble();
  bubbles.push(bubble2);
}