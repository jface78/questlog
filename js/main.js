var SERVICE_BASE = '';
var user;

function drawWelcomeBox() {
  var div = $('<div></div>');
  $(div).append('<p>Welcome, <b style="color:#FFF;">' + user.name.toUpperCase() + '.</b></p>');
  $(div).append('<p style="line-height:15px;margin-top:5px;">You last logged in on <b>' + formatFullDate(user.date) + '</b> from <b>' + user.ip + '</b></p>');
  $(div).append('<p><button>logout</button></p>');
  $('.loginBox').html(div);
  $(div).find('button').click(function() {
    handleLogout();
  });
}

function drawLoginBox() {
  var form = $('<form id="login" method="POST" accept-charset="utf-8"></form>');
  var ul = $('<ul></ul>');
  $(ul).append('<li><input type="text" id="user" placeholder="user" required></li>');
  $(ul).append('<li><input type="password" id="pass" placeholder="passwd" required></li>');
  $(ul).append('<li><input type="submit" value="login"></li>');
  $(form).append(ul);
  $('.loginBox').html(form);
  
  $('#login').submit(function(e){
    e.preventDefault();
    handleLogin();
  });
}

function checkSession() {
  $.ajax({
    type: 'GET',
    url: './session',
    dataType: 'json',
    statusCode: {
      200: function(data) {
        console.log(data);
        user = data.user_details;
        $('header h3').text('');
        drawWelcomeBox();
      },
      401: function() {
        $('header h3').text('Current Status: CLOSED BETA');
        drawLoginBox();
      }
    }
  });
}

function handleLogout() {
  $.ajax({
    type: 'GET',
    url: './logout',
    dataType: 'json',
    success: function() {
     checkSession(); 
    }
  });
}

function handleLogin() {
  $.ajax({
    type: 'POST',
    data: {name: $.trim($('#user').val()).toLowerCase(), pass: $.trim($('#pass').val())},
    url: './login',
    dataType: 'json',
    statusCode: {
      200: function(data) {
        user = data.user_details;
        drawWelcomeBox();
        $('header h3').text('');
      },
      401: function() {
        console.log('401');
      },
      400: function() {
        console.log('400');
      },
      500: function() {
        console.log('500');
      }
    }
  });
}

$(document).ready(function() {
  console.log('Look away, I\'m hideous!');
  if (new Date().getHours() > 18 || new Date().getHours() < 6) {
    $('#logo').attr('src', 'img/logo_night.gif');
  }
  
  checkSession();
  //drawLoginBox();
});