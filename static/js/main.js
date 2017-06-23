var SERVICE_URL = 'http://localhost:1337/service/';
var userID, username, lastLoginTime;

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

function renderQuests(data) {
  $.get('templates/quests.html', function(template) {
    $('main > .leftContent').html(template);
    console.log(data);
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
      var controls = $('<td style="text-align:center;"><button>info</button></td>');
      if (userID) {
        $(controls).append('&nbsp;<button>edit</button>&nbsp;<button>delete</button>');
      }
      $(tr).append(controls);
      $('#allQuests tbody').append(tr);
    });
  });
}

function fetchQuests() {
  $.ajax({
    url: SERVICE_URL + 'quests',
    dataType: 'json',
    statusCode: {
      200: function(data) {
        renderQuests(data);
      }
    }
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
        lastLoginTime = data.timestamp;
        renderUserBox();
        fetchQuests();
      },
      404: function() {
        renderLoginBox();
        fetchQuests();
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
  }, 'json');
}

function logout() {
  $.get(SERVICE_URL + 'logout', [], function(data) {
    renderLoginBox();
  });
}