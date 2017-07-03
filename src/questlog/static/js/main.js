var SERVICE_URL = '/service/';
var TEMPLATE_URL = '/templates/';
var userID, username, lastLoginTime;

var DEFAULT_PAGE_LENGTH = 50;
var DEFAULT_PAGE_ORDER = 'DESC';

var currentPageOrder = DEFAULT_PAGE_ORDER;

var EVENT_DESTROYED = 'eventDestroyed';
var EVENT_LOADED = 'eventLoaded';
var EVENT_URL_UPDATED = 'eventUrlUpdated';

var currentQuestPage = 0;

$(document).ready(function() {
  if (new Date().getHours > 18 || new Date().getHours < 6) {
    $('#logo').attr('src', 'img/logo_night.gif');
  }
  $('#logo').click(function() {
    history.pushState({}, 'quest', '/');
  });
  checkSession();
});

(function(history){
  var pushState = history.pushState;
  history.pushState = function(state) {
    if (typeof history.onpushstate == "function") {
      history.onpushstate({state: state});
    }
    $(window).trigger(EVENT_URL_UPDATED);
    return pushState.apply(history, arguments);
  }
})(window.history);

function changeLocation() {
  $(window).off('scroll');
  window.scrollTo(0,0);
  $('main').fadeOut('fast', function() {
    var dir_parts = window.location.pathname.split('/');
    switch(dir_parts[1]) {
      case 'quest':
        if (parseInt(dir_parts[2])) {
          
          $.get(TEMPLATE_URL + 'posts.html', function(template) {
            currentPageOrder = DEFAULT_PAGE_ORDER;
            currentQuestPage = 0;
            $('main').html(template);
            $('.backBtn').click(function() {
              history.pushState({}, 'quest', '/');
            });
            fetchAndRenderQuestPermissions(parseInt(dir_parts[2]));
            $('.fa-rotate-right').click(function() {
              if (currentPageOrder == 'DESC') {
                currentPageOrder = 'ASC';
                $(this).removeClass('fa-rotate-right');
                $(this).addClass('fa-rotate-left');
              } else {
                currentPageOrder = 'DESC';
                $(this).removeClass('fa-rotate-left');
                $(this).addClass('fa-rotate-right');
              }
              $('main section').fadeOut('fast', function() {
                $('main section').empty();
                $('main section').fadeIn('fast');
                fetchAndRenderPosts(parseInt(dir_parts[2]), 0, DEFAULT_PAGE_LENGTH, currentPageOrder);
              });
            });
            $(window).scroll(function() {
              scrollQuest(dir_parts[2]);
            });
            $('main').fadeIn('fast', function() {
              fetchAndRenderPosts(parseInt(dir_parts[2]));
            });
          });
        }
        break;
      default:
        $(window).off('scroll');
        $('main').empty();
        $('main').fadeIn('fast', function() {
          fetchAndRenderQuests();
        });
    }
  });
}

$(window).on(EVENT_URL_UPDATED, function() {
  changeLocation();
});
$(window).on('popstate', function(event) {
  changeLocation();
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
  return date.toDateString() + ' at ' + prependZero(date.getHours()) + ':' + prependZero(date.getMinutes());
}

function prependZero(string) {
  if (parseInt(string) < 10) {
    return '0' + string;
  } else {
    return string;
  }
}

function fadeInRows(array) {
  $(array).each(function(index, item) {
    $(this).delay(25*index).fadeIn(300);
  });
}

function drawPreloader() {
  $('body').append('<div class="preloaderBG"></div>');
  $('body').append('<div class="preloaderFG"><img src="/img/preloader.gif" alt="loading..." title="loading"></div>');
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

function renderEndPost() {
  $(window).off('scroll');
  var div = $('<div class="postBubble endPost"></div>');
  var content = $('<content>No more posts.</content>');
  $(div).append(content);
  var totalPosts = $('.postBubble').length;
  if ($($('.postBubble')[totalPosts-1]).hasClass('odd')) {
    $(div).addClass('even');
  } else {
    $(div).addClass('odd');
  }
  $('.posts section').append(div);
  $(div).fadeIn('fast');
}

function drawParticipantControls() {
  $('.postNav ul').append('<li>|&nbsp;&nbsp;&nbsp;post&nbsp;&nbsp;&nbsp;</li>');
}

function fetchAndRenderQuestPermissions(qid) {
  $.ajax({
    url: SERVICE_URL + 'quest/' + qid + '/permissions',
    dataType: 'json',
    statusCode: {
      200: function(data) {
        if (parseInt(data.gmid) == userID) {
          drawParticipantControls();
          return;
        }
        $(data.members).each(function(index, item) {
          if (parseInt(item.uid) == userID) {
            drawParticipantControls();
            return;
          }
        });
      }
    }
  });
}

function fetchAndRenderPosts(qid, start, length, order) {
  if (!start) {
    start = 0;
  }
  if (!length) {
    length = DEFAULT_PAGE_LENGTH;
  }
  if (!order) {
    order = currentPageOrder;
  }
  drawPreloader();
  
  $.ajax({
    url: SERVICE_URL + 'quest/' + qid + '?start=' + start + '&length=' + length + '&order=' + order,
    dataType: 'json',
    statusCode: {
      200: function(data) {
        var queuedPosts = [];
        if (!data) {
          renderEndPost();
        }
        $(data).each(function(index, item) {
          var div = $('<div class="postBubble" data-pid="' + item.pid + '"></div>');
          var header = $('<header>Posted on ' + formatDate(item.stamp) + ' by ' + item.poster + '</header>');
          if (parseInt(item.uid) == userID) {
            var span = $('<span class="controls"></span>');
            $(span).append('<a class="icon" href=""><img src="/img/icon.edit_dark.gif" alt="edit" title="edit"></a>');
            $(span).append('<a class="icon" href=""><img src="/img/icon.delete_dark.gif" alt="delete" title="delete"></a>');
            $(header).append(span);
          }
          $(div).append(header);
          var content = $('<content>' + item.text + '</content>');
          $(div).append(content);
          if (index % 2 === 0) {
            $(div).addClass('even');
          } else {
            $(div).addClass('odd');
          }
          $('.posts section').append(div);
          queuedPosts.push(div);
          if (index == data.length-1 && (index+1) < DEFAULT_PAGE_LENGTH) {
            renderEndPost();
          }
        });
        if (queuedPosts.length) {
          fadeInRows(queuedPosts);
        }
        clearPreloader();
      }
    }
  });
}

function fetchAndRenderQuests() {
  if (userID) {
    var queuedRows = [];
    $.get(TEMPLATE_URL + 'quests_loggedin.html', function(template) {
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
              $(tr).append('<td><a href="" class="questClick">' + item.name + '</a></td><td>' + item.count + '</td>');
              $(tr).append('<td>' + item.last + ' on ' + formatDate(item.timestamp) +  '</td><td><a href="#">' + item.gmname + '</a></td>');
              var playersStr = '';
              $(item.players).each(function(playerIndex, playerItem) {
                playersStr += '<a class="character" data-cid="' + playerItem.cid + '" href="#">' + playerItem.name + '</a>';
                if (playerIndex < item.players.length-1) {
                  playersStr += ', ';
                }
              });
              $(tr).append('<td>' + playersStr + '</td>');
              var controls = $('<td style="text-align:center;"><i class="icon fa fa-clone" title="preface"></i></td>');
              $(controls).css('width', '25px');
              if (item.type == 'gm') {
                $(controls).css('width', '55px');
                $(controls).append('<a class="icon" href=""><img src="/img/icon.edit_dark.gif" alt="edit" title="edit"></a><a class="icon" href=""><img src="/img/icon.delete_dark.gif" alt="delete" title="delete"></a>');
              }
              $(tr).append(controls);
              $(controls).find('.fa-clone').click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                showQuestInfo(this);
              });
              $('#' + item.type + 'Quests tbody').append(tr);
              queuedRows.push(tr);
            });
            if (!totalGM) {
              $('#gmQuests tbody').append('<tr class="odd" style="display:table-row;"><td colspan="6">None</td></tr>');
            }
            if (!totalPlayer) {
              $('#playerQuests tbody').append('<tr class="odd" style="display:table-row;"><td colspan="6">None</td></tr>');
            }
            if (!totalOther) {
              $('#otherQuests tbody').append('<tr class="odd" style="display:table-row;"><td colspan="6">None</td></tr>');
            }
            $('.questsTable').find('.questClick').each(function(index, item) {
              $(item).click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                history.pushState({}, 'quest', '/quest/' + $(this).closest('tr').data('qid') + '/');
              });
            });
            $('.questsTable').find('.character').each(function(index, item) {
              $(item).click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                showCharacterInfo($(this).attr('data-cid'));
              });
            });
            clearPreloader();
            fadeInRows(queuedRows);
          }
        }
      });
    });
  } else {
    var queuedRows = [];
    $.get(TEMPLATE_URL + 'quests.html', function(template) {
      $('main > .leftContent').html(template);
      drawPreloader();
      $.ajax({
        url: SERVICE_URL + 'quests',
        dataType: 'json',
        statusCode: {
          200: function(data) {
            $('main').html(template);
            $.each(data, function(index, item) {
              var tr = $('<tr data-qid="' + item.qid + '"></tr>');
              if (index % 2 === 0) {
                $(tr).addClass('even');
              } else {
                $(tr).addClass('odd');
              }
              $(tr).append('<td><a href="" class="questClick">' + item.name + '</a></td><td>' + item.count + '</td>');
              $(tr).append('<td>' + item.last + ' on ' + formatDate(item.timestamp) +  '</td><td><a href="#">' + item.gmname + '</a></td>');
              var playersStr = '';
              $(item.players).each(function(playerIndex, playerItem) {
                playersStr += '<a class="character" data-cid="' + playerItem.cid + '" href="#">' + playerItem.name + '</a>';
                if (playerIndex < item.players.length-1) {
                  playersStr += ', ';
                }
              });
              $(tr).append('<td>' + playersStr + '</td>');
              var controls = $('<td style="text-align:center;"><i class="icon fa fa-clone" title="preface"></i></td>');
              if (userID) {
                $(controls).append('<a class="icon" href=""><img src="/img/icon.edit_dark.gif" alt="edit" title="edit"></a><a class="icon" href=""><img src="/img/icon.delete_dark.gif" alt="delete" title="delete"></a>');
              }
              $(tr).append(controls);
              $(controls).find('.fa-clone').click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                showQuestInfo(this);
              });
              $('#allQuests tbody').append(tr);
              queuedRows.push(tr);
            });
            $('.questsTable').find('.questClick').each(function(index, item) {
              $(item).click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                history.pushState({}, 'quest', '/quest/' + $(this).closest('tr').data('qid') + '/');
              });
            });
            $('.questsTable').find('.character').each(function(index, item) {
              $(item).click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                showCharacterInfo($(this).attr('data-cid'));
              });
            });
            clearPreloader();
            if (queuedRows.length) {
              fadeInRows(queuedRows);
            }
          }
        }
      });
    });
  }
}

function fetchCharacterInfo(box, id) {
  $.ajax({
    url: SERVICE_URL + 'character/' + id,
    dataType: 'json',
    statusCode: {
      200: function(data) {
        box.setTitle(data.name);
        $.get(TEMPLATE_URL + 'character.html', [], function(template) {
          box.setContent(template);
          $('#character').find('#title').html(data.title);
          $('#character').find('#preface').html(data.preface);
          $('#character').find('#profile').html(data.profile);
          $(data.quests).each(function(index, item) {
            $('#character').find('#quests').append('<a href="" data-qid="' + item.qid + '">' + item.name + '</a><br>');
          });
          $('#character').find('#quests a').each(function(index, item) {
            $(item).click(function(event) {
              event.preventDefault();
              event.stopPropagation();
              box.destroy();
              history.pushState({}, 'quest', '/quest/' + $(item).attr('data-qid') + '/');
            });
          });
        });
      },
      404: function() {
        $(box.foreground).find('content').text('No description provided.');
      }
    }
  });
}

function showCharacterInfo(cid) {
  var box = new QuestlogOverlay(fetchCharacterInfo, cid);
  $(box).on(EVENT_LOADED, function() {
    fetchCharacterInfo(box, cid);
  });
  box.setup();
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

function scrollQuest(qid) {
  if ($(window).scrollTop() + $(window).height() == $(document).height()) {
    console.log('scroll');
    currentQuestPage++;
    fetchAndRenderPosts(qid, (currentQuestPage * DEFAULT_PAGE_LENGTH), DEFAULT_PAGE_LENGTH);
  }
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
        changeLocation();
      },
      404: function() {
        renderLoginBox();
        fetchAndRenderQuests();
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
    fetchAndRenderQuests();
  }, 'json');
}

function logout() {
  $.get(SERVICE_URL + 'logout', [], function(data) {
    userID = null;
    renderLoginBox();
    //history.pushState({}, 'quest', '/');
  });
}