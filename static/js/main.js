var SERVICE_URL = '/service/';
var TEMPLATE_URL = '/templates/';
var userID, username, userip, lastLoginTime;

var DEFAULT_PAGE_LENGTH = 50;
var DEFAULT_PAGE_ORDER = 'DESC';

var currentPageOrder = DEFAULT_PAGE_ORDER;

var EVENT_DESTROYED = 'eventDestroyed';
var EVENT_LOADED = 'eventLoaded';
var EVENT_URL_UPDATED = 'eventUrlUpdated';

var currentQuestPage = 0;

(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-27020062-2', 'auto');
ga('send', 'pageview');


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

$(window).on(EVENT_URL_UPDATED, function() {
  changeLocation();
});
$(window).on('popstate', function(event) {
  changeLocation();
});

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


function renderUserBox() {
  var form = $('<form method="GET"></form>');
  $(form).append('<p>Welcome, ' + username + '</p>');
  if (lastLoginTime != 0) {
    $(form).append('<p>You last logged in on ' + formatDate(lastLoginTime) + ' from ' + userip + '</p>');
  }
  $(form).append('<p style="margin-top:5px;"><button>LOGOUT</button></p>');
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

function checkSession() {
  $.ajax({
    url: SERVICE_URL + 'checkSession',
    dataType: 'json',
    statusCode: {
      200: function(data) {
        userID = data.uid;
        username = data.login_name;
        userip = data.ip;
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
    userip = data.ip;
    lastLoginTime = data.last_login_time;
    renderUserBox();
    fetchAndRenderQuests();
  }, 'json');
}

function logout() {
  $.get(SERVICE_URL + 'logout', [], function(data) {
    userID = null;
    renderLoginBox();
    history.pushState({}, 'quest', '/');
  });
}


function sanitizeTextForUI(text) {
  text = text.replace(/<br ?\/?>/gi, '\n')
  text = text.replace(/\<(.+?)\>/g, "[$1]");
  var toHTML = $('<output>' + text + '</output>');
  $(toHTML).find('.roll').each(function(index, item) {
    console.log('ITEM: ' + item);
    var id = $(item).attr('id');
    $(item).replaceWith('[DICE_ROLL]' + id + '[/DICE_ROLL]');
  });
  return $(toHTML).text();
}

function sanitizeHTML(text) {
  text = text.replace(/<br ?\/?>/gi, '\n')
  var tags = text.match(/\<(.*?)\>/);
  console.log(tags)
  return text;
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