var SERVICE_URL = 'php/services/';
var TEMPLATE_URL = 'templates/';

function handleLogin() {
  if (!$('#user').val().trim().length || !($('#passwd').val().trim().length)) {
    $('#warningMessage').text('Fill out both fields, dummy.');
  } else {
    $.ajax({
      url: SERVICE_URL + 'loginHandler.php?request=login',
      type: 'POST',
      dataType: 'json',
      data: { user: $('#user').val().trim().toLowerCase(), pass: $('#passwd').val().trim()},
      statusCode: {
        200: function(response) {
          addGreetingBox(response.user_details.name, response.user_details.date, response.user_details.ip);
          loadQuestListings();
        },
        401: function(response) {
          $('#warningMessage').text('Does not compute.');
        },
        500: function() {
          $('#warningMessage').text('There was an error logging you in. Try again in 4-6 weeks.');
        }
      }
    });
  }
}

function loadQuestListings() {
  $.ajax({
    url: TEMPLATE_URL + 'questListings',
    success: function(template) {
      
    }
  });
}

function getRandomInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

function generateWelcomeMessage(user) {
  var returnString;
  switch(getRandomInt(0, 9)) {
    case 0:
      returnString = "It's been a long time, " + user + ".";
      break;
    case 1:
      returnString = ucwords(user) + '. You son of a bitch.';
      break;
    case 2:
      returnString = 'Oh hi ' + user;
      break;
    case 3:
      returnString = "You've got a lot of nerve showing your face around here, " + user + ".";
      break;
    case 4:
      returnString = "I'm going to enjoy watching you die, " + user + ".";
      break;
    case 5:
      returnString = "Well, well, well. If it isn't " + user + ".";
      break;
    case 6:
      returnString = "Well met, " + user + ".";
      break;
    case 7:
      returnString = "When I left you I was but a learner, " + user + ". Now I am the master.";
      break;
    case 8:
      returnString = "Welcome, " + user + ". I am QuestLog, protocol website, human-NPC relations.";
      break;
    case 9:
      returnString = "Ugh, it's you again, " + user + ".";
      break;
  }
  return returnString;
}

function ucwords(str) {
  //  discuss at: http://phpjs.org/functions/ucwords/
  // original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  // improved by: Waldo Malqui Silva
  // improved by: Robin
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Onno Marsman
  //    input by: James (http://www.james-bell.co.uk/)
  //   example 1: ucwords('kevin van  zonneveld');
  //   returns 1: 'Kevin Van  Zonneveld'
  //   example 2: ucwords('HELLO WORLD');
  //   returns 2: 'HELLO WORLD'

  return (str + '')
    .replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function($1) {
      return $1.toUpperCase();
    });
}

function formatDate(dateStr) {
  if (dateStr) {
    var date = new Date(parseInt(dateStr)*1000);
    return 'on ' + date.toDateString() + ' at ' + date.toLocaleTimeString();
  } else {
    return 'at an unknown point in time'
  }
}

function addLoginBox() {
  $('.greetingBox').remove();
  var parent = document.createElement('header');
  $(parent).addClass('loginBox');
  var div = document.createElement('div');
  $(div).text('login ');
  var input = document.createElement('input');
  $(input).addClass('field');
  $(input).attr('type', 'text');
  $(input).attr('id', 'user');
  $(div).append(input);
  $(parent).append(div);
  div = document.createElement('div');
  $(div).css('margin-top', '5px');
  $(div).text('passwd ');
  input = document.createElement('input');
  $(input).addClass('field');
  $(input).attr('type', 'password');
  $(input).attr('id', 'passwd');
  $(input).val('Igh4oosothai');
  $(div).append(input);
  $(parent).append(div);
  div = document.createElement('div');
  $(div).css('margin-top', '5px');
  var button = document.createElement('button');
  $(button).text('submit');
  $(div).append(button);
  $(parent).append(div);
  $('#leftHeader').after(parent);
  $('.loginBox button').click(handleLogin);
}

function addGreetingBox(user, date, ip) {
  $('.loginBox').remove();
  var div = document.createElement('header');
  $(div).addClass('greetingBox');
  var span = document.createElement('span');
  $(span).html('<b>'+generateWelcomeMessage(user) + '</b>');
  $(div).append(span);
  $(div).append('<br /><br />');
  span = document.createElement('span');
  $(span).css('color', '#999');
  $(span).append('You last logged in ' + formatDate(date) + ' from ' + ip);
  $(div).append(span);
  $('#leftHeader').after(div);
}

$(document).ready(function() {

  $('footer').text('Copyright ' + new Date().getFullYear() + ' QuestLog.org');
  var hour = new Date().getHours();
  if (hour < 6 || hour > 18) {
    $('#titleImg').attr('src', 'img/title.06.gif');
  }
  $.ajax({
    url: SERVICE_URL + 'loginHandler.php?request=checkSession',
    dataType: 'json',
    statusCode: {
      401: function() {
        addLoginBox();
        $('#warningMessage').text('You are not currently logged in.');
      },
      200: function(response) {
        
        addGreetingBox(response.user_details.name, response.user_details.date, response.user_details.ip);
        $('#warningMessage').text('You are currently logged in.');
      }
    },
    error: function(response) {
      
    }
  });
});