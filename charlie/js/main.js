var SERVICE_URL = 'php/services/';


$(document).ready(function() {

  $('button').click(function() {
    if (!$('#user').val().trim().length || !($('#passwd').val().trim().length)) {
      $('#warningMessage').text('Fill out both fields, dummy.');
    } else {
      $.ajax({
        url: SERVICE_URL + 'loginHandler.php?request=login',
        type: 'POST',
        data: { user: $('#user').val().trim().toLowerCase(), pass: $('#passwd').val().trim()},
        success: function(response) {
          console.log(response);
        },
        error: function(response) {
          console.log(response);
        }
      });
    }
  });

  var hour = new Date().getHours();
  if (hour < 6 || hour > 18) {
    $('#titleImg').attr('src', 'img/title.06.gif');
  }
  $.ajax({
    url: SERVICE_URL + 'loginHandler.php?request=checkSession',
    success: function(response) {
      //console.log(response);
    },
    error: function(response) {
      //console.log(response);
    }
  });
});