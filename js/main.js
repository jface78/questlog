var SERVICE_BASE = '';
var user;

function handleLogin() {
  $.ajax({
    type: 'POST',
    data: {name: $.trim($('#user').val()).toLowerCase(), pass: $.trim($('#pass').val())},
    url: './login',
    dataType: 'json',
    statusCode: {
      200: function(data) {
        var div = $('<div></div>');
        console.log(data);
        user = data.user_details;
        $(div).append('<p>Welcome, <b style="color:#FFF;">' + user.name.toUpperCase() + '.</b></p>');
        $(div).append('<p style="line-height:15px;margin-top:5px;">You last logged in on <b>' + formatFullDate(user.date) + '</b> from <b>' + user.ip + '</b></p>');
        $('.loginBox').html(div);
        console.log(div);
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
  
  $('#login').submit(function(e){
    e.preventDefault();
    handleLogin();
  });
});