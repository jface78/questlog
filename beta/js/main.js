var TEMPLATE_URL = 'templates/';
var SERVICE_URL = 'services/';

var bubbles = [];
var currentTheme = 'vader';
var menu;
var minimizedArray = [];

var BUBBLE_MINIMIZED_WIDTH = 100;
var BUBBLE_TOOLBAR_HEIGHT = 20;


$(document).ready(function() {
  if (!window.jQuery.ui) {
    var head = document.getElementsByTagName('head');
    var script = document.createElement('script');
    $(script).attr('src', 'js/plugins/jquery-ui.min.js');
    $(head[0]).append(script);
  }
  launchMenu();
  spawn(450,300,true,null,null,'Login', 'login.html');
});

$(document).on('scroll', function() {
  $(document).scrollLeft(0);
});

$(window).resize(function(event) {
  if (menu) {
    menu.startingPosition = document.getElementById('mainContent').clientWidth - 25;
    if (!menu.isOpen) {
      $(menu.parent).css('left', menu.startingPosition);
    } else {
      $(menu.parent).css('left', menu.startingPosition - $(menu.parent).width());
    }
  }
  for (var i = 0; i < minimizedArray.length; i++) {
    var divider = Math.floor($(window).width() / BUBBLE_MINIMIZED_WIDTH);
    var yMultiplier = Math.floor(i / divider);
    var yPos = $(window).height() - ((yMultiplier+1) * BUBBLE_TOOLBAR_HEIGHT);
    var arrayPos = i - (yMultiplier * divider);
    var xPos = BUBBLE_MINIMIZED_WIDTH * arrayPos;
    if (xPos != $(minimizedArray[i].parent).position().left || yPos != $(minimizedArray[i].parent).position().top) {
      $(minimizedArray[i].parent).animate({
        left: xPos,
        top: yPos
      }, 250);
    }
  }
});

function loadTheme(theme) {
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
  //var settings = new Settings();
  //$(settings).on('loadComplete', function(event) {
    menu = new Menu();
    $(menu).on('loadComplete', function(event) {
      
    });
    menu.setup();
  //});
  //settings.setup();
}

function spawn(width, height, centered, left, top, title, content) {
  var bubble = new QuestlogBubble(width, height, centered, left, top, title, content);
  $(bubble).on('loadComplete', function(event) {
    $(bubble.parent).animate({
      opacity:1
    }, 250);
  });
  bubbles.push(bubble);
  bubble.setup();
  
}

function removeFromArray(array, removeItem) {
  return $.grep(array, function(value) {
    return value != removeItem;
  });
}