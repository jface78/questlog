var SERVICE_URL = 'http://localhost:1337/';
var userID, username, lastLoginTime;

$(document).ready(function() {
  
  checkSession();
  
});

function renderUserBox() {
  var form = $('<form method="GET"></form>');
  $(form).append('<p>Welcome, ' + username + '</p>');
  if (lastLoginTime != null) {
    $(form).append('<p>You last logged in at ' + lastLoginTime + '</p>');
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
  $(form).append('<p>user: <input type="text"></p>');
  $(form).append('<p>pswd: <input type="password"></p>');
  $(form).append('<p><input type="submit" value="LOGIN"></p>');
  $(form).append('</form>');
  $('.loginBox').html(form);
  $(form).submit(function(event) {
    event.preventDefault();
    event.stopPropagation();
    login();
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
      },
      404: function() {
        renderLoginBox();
      }
    }
  });
}

function login() {
  $.post(SERVICE_URL + 'login',  { user: 'holodog', pass: 'liches'}, function( data ) {
    console.log('name: ' + data[0].login_name);
    userID = data[0].uid;
    username = data[0].login_name;
    lastLoginTime = data[0].timestamp;
    renderUserBox();
  }, 'json');
}

function logout() {
  $.get(SERVICE_URL + 'logout', [], function(data) {
    renderLoginBox();
  });
}