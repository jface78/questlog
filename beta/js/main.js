var TEMPLATE_URL = 'templates/';
var bubbles = [];

$(document).ready(function() {
  if (!window.jQuery.ui) {
    var head = document.getElementsByTagName('head');
    var script = document.createElement('script');
    $(script).attr('src', 'js/plugins/jquery-ui.min.js');
    $(head[0]).append(script);
  }
  var bubble = new QuestlogBubble();
  bubbles.push(bubble);
  setTimeout(function() {
    var bubble2 = new QuestlogBubble();
    bubbles.push(bubble2);
  }, 1000);
});

function init() {
  
}