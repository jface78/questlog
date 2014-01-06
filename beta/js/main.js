var TEMPLATE_URL = 'templates/';
var SERVICE_URL = 'services/';

var bubbles = [];
var currentTheme = 'vader';

$(document).ready(function() {
  if (!window.jQuery.ui) {
    var head = document.getElementsByTagName('head');
    var script = document.createElement('script');
    $(script).attr('src', 'js/plugins/jquery-ui.min.js');
    $(head[0]).append(script);
  }
  launchMenu();
});

function loadTheme(theme) {
  //<link rel="stylesheet" type="text/css" href="css/themes/vader/jquery.ui.theme.css">
  //<link rel="stylesheet" type="text/css" href="css/themes/vader/jquery-ui.min.css">
  var head = document.getElementsByTagName('head');
  var link = document.createElement('link');
  $(link).attr('rel', 'stylesheet');
  $(link).attr('type', 'text/css');
  $(link).attr('href', 'css/themes/' + theme + '/jquery.ui.theme.css');
  $(head[0]).append(link);
  $(head[0]).find('link').each(function(increment, item) {
    if ($(item).attr('href') == 'css/themes/' + currentTheme + '/jquery.ui.theme.css' || 
        $(item).attr('href') == 'css/themes/' + currentTheme + '/jquery-ui.min.css' ) {
      $(item).remove();
    }
  });
  var link = document.createElement('link');
  $(link).attr('rel', 'stylesheet');
  $(link).attr('type', 'text/css');
  $(link).attr('href', 'css/themes/' + theme + '/jquery-ui.min.css');
  $(head[0]).append(link);
  currentTheme = theme;
}

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
  bubble2.setup();
  bubbles.push(bubble2);
}