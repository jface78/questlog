var SERVICE_BASE = '';
var TEMPLATE_URL = 'templates/';
var user;

function drawWelcomeBox() {
  var div = $('<div></div>');
  $(div).append('<p style="margin:0;">Welcome, <b style="color:#FFF;">' + user.name.toUpperCase() + '.</b></p>');
  $(div).append('<p>You last logged in on <b>' + formatFullDate(user.date) + '</b> from <b>' + user.ip + '</b></p>');
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
  $(ul).append('<li style="padding-top:3px;"><input type="submit" value="login"></li>');
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
        fetchQuests();
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
        fetchQuests();
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

function fetchQuests() {
  $.ajax({
    type: 'GET',
    url: TEMPLATE_URL + 'quests.html',
    success: function(template) {
      $('main').html(template);
      $.ajax({
        type: 'GET',
        url: './quests',
        dataType: 'json',
        statusCode: {
          200: function(data) {
            var quests = data.quests;
            console.log(quests);
            $(quests).each(function(index, item) {
              var tr = $('<tr></tr>');
              $(tr).attr('data-qid', item.qid);
              $(tr).attr('data-gid', item.gid);
              $(tr).append('<td>' + item.name + '</td>');
              $(tr).append('<td>' + item.gm_name + '</td>');
              var players = '';
              $(item.characters).each(function(charIndex, charItem) {
                players += charItem.char_name;
                if (charIndex < item.characters.length-1) {
                  players += ', ';
                }
              });
              $(tr).append('<td>' + players + '</td>');
              $('#allQuests tbody').append(tr);
            });
          }
        }
      });
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