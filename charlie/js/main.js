var SERVICE_URL = 'php/services/';
var TEMPLATE_URL = 'templates/';
var isLoggedIn = false;

function handleLogout() {
  $.ajax({
    url: SERVICE_URL + 'loginHandler.php?request=logout',
    success: function() {
      isLoggedIn = false;
      $('#mainContent').fadeOut('normal', function() {
        window.location.reload();
      });
    }
  });
}

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
          if (!isLoggedIn) {
            isLoggedIn = true;
            $('#mainContent').fadeOut('normal', function() {
              addGreetingBox(response.user_details.name, response.user_details.date, response.user_details.ip);
              $('#warningMessage').text('You are currently logged in.');
              loadQuestListings();
            });
          }
        },
        401: function(response) {
          $('#warningMessage').text('Does not compute.');
        },
        500: function(error) {
          console.log(error.responseText);
          $('#warningMessage').text('There was an error logging you in. Try again in 4-6 weeks.');
        }
      }
    });
  }
}

function loadQuest(questID) {
  $.ajax({
    url: TEMPLATE_URL + 'quest.html',
    success: function(template) {
      $('#questlogLeft').html(template);
      $.ajax({
        url: SERVICE_URL + 'fetchQuestListings.php',
        dataType: 'json',
        statusCode: {
          200: function(response) {
            $('#questlogLeft').fadeIn();
          }
        }
      });
    }
  });
}

function loadQuestListings() {
  $.ajax({
    url: TEMPLATE_URL + 'questListings.html',
    success: function(template) {
      $('#questlogLeft').html(template);
      $.ajax({
        url: SERVICE_URL + 'fetchQuestListings.php',
        dataType: 'json',
        statusCode: {
          200: function(response) {
            $('#logoutBtn').click(function(event) {
              handleLogout();
            });
            for (var i=0; i < response.quests.gmQuests.length; i++) {
              var tr = document.createElement('tr');
              var td = document.createElement('td');
              $(td).text(response.quests.gmQuests[i].title);
              $(td).addClass('log-left');
              $(td).attr('data-quest-id', response.quests.gmQuests[i].questID);
              $(td).click(function() {
                var id = $(this).data('questId');
                $('#questlogLeft').fadeOut('normal', function() {
                  loadQuest($(this).data('questId'));
                });
              });
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.gmQuests[i].count);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.gmQuests[i].lastPostBy);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(new Date(parseInt(response.quests.gmQuests[i].lastPostDate)*1000).toDateString());
              $(tr).append(td);
              $('#gmQuests tbody').append(tr);
            }
            $('#gmQuests').DataTable({
              'paging':false,
              'searching':false,
              'autoWidth':false,
              'language': {
                'search': 'Search GM Quests: ',
                'info': ''
              },
              'columnDefs': [
                { 'width': '25%', 'targets': 0 }
              ],
              'initComplete':function() {
                $('#gmQuests_filter').css('text-align', 'right');
                $('#gmQuests_filter input').addClass('field');
              }
            });

            for (var i=0; i < response.quests.playerQuests.length; i++) {
              var tr = document.createElement('tr');
              var td = document.createElement('td');
              $(td).text(response.quests.playerQuests[i].title);
              $(td).addClass('log-left');
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.playerQuests[i].count);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.playerQuests[i].gm);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.playerQuests[i].lastPostBy);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(new Date(parseInt(response.quests.playerQuests[i].lastPostDate)*1000).toDateString());
              $(tr).append(td);
              $('#playerQuests tbody').append(tr);
            }
            $('#playerQuests').DataTable({
              'paging':false,
              'searching':false,
              'autoWidth':false,
              'language': {
                'search': 'Search Player Quests: ',
                'info': ''
              },
              'columnDefs': [
                { 'width': '25%', 'targets': 0 }
              ],
              'initComplete':function() {
                $('#playerQuests_filter').css('text-align', 'right');
                $('#playerQuests_filter input').addClass('field');
              }
            });

            for (var i=0; i < response.quests.otherQuests.length; i++) {
              var tr = document.createElement('tr');
              var td = document.createElement('td');
              $(td).text(response.quests.otherQuests[i].title);
              $(td).addClass('log-left');
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.otherQuests[i].count);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.otherQuests[i].gm);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.otherQuests[i].lastPostBy);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(new Date(parseInt(response.quests.otherQuests[i].lastPostDate)*1000).toDateString());
              $(tr).append(td);
              $('#otherQuests tbody').append(tr);
            }
            $('#otherQuests').DataTable({
              'paging':false,
              'searching':false,
              'autoWidth':false,
              'language': {
                'search': 'Search Other Quests: ',
                'info': ''
              },
              'columnDefs': [
                { 'width': '25%', 'targets': 0 }
              ],
              'initComplete':function() {
                $('#otherQuests_filter').css('text-align', 'right');
                $('#otherQuests_filter input').addClass('field');
              }
            });
            $('#mainContent').fadeIn();
          }
        }
      });
    }
  });
}

function getRandomInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

function generateWelcomeMessage(user) {
  var returnString;
  switch(getRandomInt(0, 7)) {
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
  $(input).keypress(function(e) {
    if(e.which == 13) {
      handleLogin();
    }
  });
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
  $(input).keypress(function(e) {
    if(e.which == 13) {
      handleLogin();
    }
  });
  $(div).append(input);
  $(parent).append(div);
  div = document.createElement('div');
  $(div).css('margin-top', '5px');
  var button = document.createElement('button');
  $(button).text('submit');
  $(div).append(button);
  $(parent).append(div);
  $('#leftHeader').after(parent);
  $(button).click(handleLogin);
  $(button).keypress(function(e) {
    if(e.which == 13) {
      handleLogin();
    }
  });
}

function addGreetingBox(user, date, ip) {
  $('.loginBox').remove();
  var div = document.createElement('header');
  $(div).addClass('greetingBox');
  var span = document.createElement('span');
  $(span).css('font-size', '11px');
  $(span).html('<b>'+generateWelcomeMessage(user) + '</b>');
  $(div).append(span);
  $(div).append('<br /><br />');
  span = document.createElement('span');
  $(span).css('color', '#999');
  $(span).append('You last logged in ' + formatDate(date) + ' from ' + ip);
  $(div).append(span);
  $('#leftHeader').after(div);
}

function generateMenu() {
  var div = document.createElement('div');
  $(div).attr('id', 'questlogRight');
  var img = document.createElement('img');
  $(img).attr('src', 'img/player_menu_title.gif');
  $(img).attr('alt', 'player menu');
  $(div).append(img);
  $(div).append('<br />');
  var span = document.createElement('span');
  $(span).attr('id', 'logoutBtn');
  $(span).text('logout');
  $(div).append(span);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('user profile');
  $(div).append(span);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('list users');
  $(div).append(span);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('contact form');
  $(div).append(span);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('create character');
  $(div).append(span);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('edit character');
  $(div).append(span);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('delete character');
  $(div).append(span);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('view character');
  $(div).append(span);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('search quests');
  $(div).append(span);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('access logs');
  $(div).append(span);
  $(div).append('<br />');
  img = document.createElement('img');
  $(img).attr('src', 'img/gm_menu_title.gif');
  $(img).attr('alt', 'gm menu');
  $(div).append(img);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('host new quest');
  $(div).append(span);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('edit quest');
  $(div).append(span);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('quest members');
  $(div).append(span);
  $(div).append('<br />');
  span = document.createElement('span');
  $(span).text('edit backstory');
  $(div).append(span);
  $(div).append('<br />');
  return div;
}

$(document).ready(function() {
  $('#mainContent').hide();
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
        //$('#warningMessage').text('You are not currently logged in.');
        $('#mainContent').fadeIn();
      },
      200: function(response) {
        $('#mainContent').fadeOut('normal', function() {
          addGreetingBox(response.user_details.name, response.user_details.date, response.user_details.ip);
          $('#warningMessage').text('You are currently logged in.');
          var div = document.createElement('div');
          $(div).attr('id', 'questlogLeft');
          $('#mainContent').html(div);
          $('#mainContent').append(generateMenu());
          loadQuestListings();
        });
      }
    },
    error: function(response) {
      
    }
  });
});