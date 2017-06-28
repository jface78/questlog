var SERVICE_URL = 'service/';
var TEMPLATE_URL = 'templates/';
var userID, username, lastLoginTime;

var EVENT_DESTROYED = 'eventDestroyed';
var EVENT_LOADED = 'eventLoaded';

$(document).ready(function() {
  
  checkSession();
  
});

function renderUserBox() {
  var form = $('<form method="GET"></form>');
  $(form).append('<p>Welcome, ' + username + '</p>');
  if (lastLoginTime != null) {
    $(form).append('<p>You last logged in on ' + formatDate(lastLoginTime) + '</p>');
  }
  $(form).append('<p><button>LOGOUT</button></p>');
  $('.loginBox').html(form);
  $(form).submit(function(event) {
    event.preventDefault();
    event.stopPropagation();
    logout();
  });
}

function renderLoginBox() {
  var form = $('<form method="POST"></form>');
  $(form).append('<p>login: <input type="text"></p>');
  $(form).append('<p>passwd: <input type="password"></p>');
  $(form).append('<p><input type="submit" value="submit"></p>');
  $(form).append('</form>');
  $('.loginBox').html(form);
  $(form).submit(function(event) {
    event.preventDefault();
    event.stopPropagation();
    login();
  });
}

function formatDate(date) {
  date = new Date(parseInt(date)*1000);
  return date.toDateString() + ' at ' + date.getHours() + ':' + date.getMinutes();
}

function fadeInRows(array) {
  $(array).each(function(index, item) {
    $(this).delay(25*index).fadeIn(300);
  });
}

function drawPreloader() {
  $('body').append('<div class="preloaderBG"></div>');
  $('body').append('<div class="preloaderFG"><img src="img/preloader.gif" alt="loading..." title="loading"></div>');
  $('.preloaderFG img').css('margin-top', $(document).innerHeight()/2 - 50);
  $('.preloaderFG').fadeIn('fast');
  $('.preloaderBG').fadeIn('fast');
}
function clearPreloader() {
  $('.preloaderFG').fadeOut('fast', function() {
    $(this).remove();
  });
  $('.preloaderBG').fadeOut('fast', function() {
    $(this).remove();
  });
}

function renderQuests() {
  if (userID) {
    var queuedRows = [];
    $.get('templates/quests_loggedin.html', function(template) {
      $('main').html(template);
      drawPreloader();
      $.ajax({
        url: SERVICE_URL + 'quests',
        dataType: 'json',
        statusCode: {
          200: function(data) {
            var totalGM = 0, totalPlayer = 0, totalOther = 0;
            $.each(data, function(index, item) {
              if (item.type == 'gm') {
                totalGM++;
              }
              if (item.type == 'player') {
                totalPlayer++;
              }
              if (item.type == 'other') {
                totalOther++;
              }
              var tr = $('<tr data-qid="' + item.qid + '"></tr>');
              if (index % 2 === 0) {
                $(tr).addClass('even');
              } else {
                $(tr).addClass('odd');
              }
              $(tr).append('<td><a href="#">' + item.name + '</a></td><td>' + item.count + '</td>');
              $(tr).append('<td>' + item.last + ' on ' + formatDate(item.timestamp) +  '</td><td><a href="#">' + item.gmname + '</a></td>');
              var playersStr = '';
              $(item.players).each(function(playerIndex, playerItem) {
                playersStr += '<a href="#">' + playerItem.name + '</a>';
                if (playerIndex < item.players.length-1) {
                  playersStr += ', ';
                }
              });
              $(tr).append('<td>' + playersStr + '</td>');
              var controls = $('<td style="text-align:center;"><i class="icon fa fa-clone" title="preface"></i></td>');
              $(controls).css('width', '25px');
              if (item.type == 'gm') {
                $(controls).css('width', '55px');
                $(controls).append('<i class="icon fa fa-edit" title="edit"></i><i class="icon fa fa-trash-o" title="delete"></i>');
              }
              $(tr).append(controls);
              $(controls).find('.fa-clone').click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                showQuestInfo(this);
              });
              $('#' + item.type + 'Quests tbody').append(tr);
              queuedRows.push(tr);
              if (index == $(data).length-1) {
                clearPreloader();
                fadeInRows(queuedRows);
              }
            });
            if (!totalGM) {
              $('#gmQuests tbody').append('<tr class="odd" style="display:table-row;"><td colspan="6">None</td></tr>');
            }
            if (!totalPlayer) {
              $('#playerQuests tbody').append('<tr class="odd" style="display:table-row;"><td colspan="6">None</td></tr>');
            }
            if (!totalOther) {
              console.log('???');
              $('#otherQuests tbody').append('<tr class="odd" style="display:table-row;"><td colspan="6">None</td></tr>');
            }
          }
        }
      });
    });
  } else {
    var queuedRows = [];
    $.get('templates/quests_loggedin.html', function(template) {
      $('main > .leftContent').html(template);
      drawPreloader();
      $.ajax({
        url: SERVICE_URL + 'quests',
        dataType: 'json',
        statusCode: {
          200: function(data) {
            $.get('templates/quests.html', function(template) {
              $('main').html(template);
              $.each(data, function(index, item) {
                var tr = $('<tr data-qid="' + item.qid + '"></tr>');
                if (index % 2 === 0) {
                  $(tr).addClass('even');
                } else {
                  $(tr).addClass('odd');
                }
                $(tr).append('<td>' + item.name + '</td><td>' + item.count + '</td>');
                $(tr).append('<td>' + item.last + ' on ' + formatDate(item.timestamp) +  '</td><td><a href="#">' + item.gmname + '</a></td>');
                var playersStr = '';
                $(item.players).each(function(playerIndex, playerItem) {
                  playersStr += '<a href="#">' + playerItem.name + '</a>';
                  if (playerIndex < item.players.length-1) {
                    playersStr += ', ';
                  }
                });
                $(tr).append('<td>' + playersStr + '</td>');
                var controls = $('<td style="text-align:center;"><i class="icon fa fa-info" title="info"></i></td>');
                if (userID) {
                  $(controls).append('<i class="icon fa fa-edit" title="edit"></i><i class="icon fa fa-trash-o" title="delete"></i>');
                }
                $(tr).append(controls);
                $(controls).find('.fa-info').click(function(event) {
                  event.preventDefault();
                  event.stopPropagation();
                  showQuestInfo(this);
                });
                $('#allQuests tbody').append(tr);
                queuedRows.push(tr);
                if (index == $(data).length-1) {
                  clearPreloader();
                  fadeInRows(queuedRows);
                }
              });
            });
          }
        }
      });
    });
  }
}

function fetchQuestInfo(box, id) {
  $.ajax({
    url: SERVICE_URL + 'quest/' + id + '/info',
    dataType: 'json',
    statusCode: {
      200: function(data) {
        box.setTitle(data.title + ' &mdash; Preface');
        box.setContent(data.description);
      },
      404: function() {
        $(box.foreground).find('content').text('No description provided.');
      }
    }
  });
}

function showQuestInfo(button) {
  var id = $(button).parent().parent().data('qid');
  var box = new QuestlogOverlay(fetchQuestInfo, id);
  $(box).on(EVENT_LOADED, function() {
    fetchQuestInfo(box, id);
  });
  box.setup();
}

function checkSession() {
  $.ajax({
    url: SERVICE_URL + 'checkSession',
    dataType: 'json',
    statusCode: {
      200: function(data) {
        userID = data.uid;
        username = data.login_name;
        lastLoginTime = data.timestamp;
        renderUserBox();
        renderQuests();
      },
      404: function() {
        renderLoginBox();
        renderQuests();
      }
    }
  });
}

function login() {
  $.post(SERVICE_URL + 'login',  { user: 'holodog', pass: 'liches'}, function( data ) {
    userID = data.uid;
    username = data.name;
    lastLoginTime = data.last_login_time;
    renderUserBox();
    renderQuests();
  }, 'json');
}

function logout() {
  $.get(SERVICE_URL + 'logout', [], function(data) {
    userID = null;
    renderLoginBox();
    renderQuests();
  });
}