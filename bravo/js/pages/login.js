
function loginInit(data) {
  $(data).find('.usernameEnter').focusout(function(event) {
    if ($(this).val().length > 0) {
      $(data).find('.usernameEnter').val($(data).find('.usernameEnter').val().toLowerCase());
      $(data).find('.usernameCheck').css('visibility', 'visible');
    }
  });
  $(data).find('.passwordEnter').focusout(function(event) {
    if ($(this).val().length > 0) {
      $(data).find('.passwordCheck').css('visibility', 'visible');
    }
  });
  $(data).find('.signupClick').click(function(event) {
    spawn(550, 250, true, null, null, 'Signup', 'signup.html');
    $(event.target).closest('.bubbleContent').data('bubble').close();
  });
  $(data).find('.usernameEnter, .passwordEnter').keyup(function(event){
    if (event.keyCode == 13) {
      handleLogin(data);
    }
  });
  
  function handleLogin(html) {
    if ($(data).find('.usernameEnter').val().length > 0 &&
        $(data).find('.passwordEnter').val().length > 0) {
      var username = $('.usernameEnter').val().toLowerCase();
      var password = $('.passwordEnter').val();
      $.ajax({
        url: SERVICE_URL + 'users.php?request=login',
        method: 'PUT',
        data: {
          username: username,
          password: password
        },
        statusCode: {
          200: function() {
            $(html).closest('.bubbleContent').data('bubble').close();
            menu.login();
          },
          404: function() {
            $(html).find('.loginWarning').text('User does not exist.');
            $(html).closest('.bubbleParent').effect('shake');
          },
          403: function() {
            $(html).find('.loginWarning').text('Wrong password.');
            $(html).closest('.bubbleParent').effect('shake');
          }
        }
      });
    }
  }
};