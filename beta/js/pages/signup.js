function signupInit(data) {

  function checkIfValidUsername(name) {
    $(data).find('.signupUsernameCheck').addClass('noDisplay');
    $(data).find('.signupUsernameError').addClass('noDisplay');
    $(data).find('.signupUsernameWarning').text('');
    if (name.length > 0) {
      $.ajax({
        url: SERVICE_URL + 'users.php?request=isUserNameRegistered&userName=' + name,
        type: 'xml',
        statusCode: {
          200: function(serviceData) {
            if ($(serviceData).find('usernameRegistered').text() == 'true') {
              $(data).find('.signupUsernameError').removeClass('noDisplay');
              $(data).find('.signupUsernameWarning').text('name taken');
              return false;
            } else {
              $(data).find('.signupUsernameCheck').removeClass('noDisplay');
              return true;
            }
          },
          400: function(serviceData) {
            $(data).find('.signupUsernameError').removeClass('noDisplay');
            return false;
          }
        }
      });
      return true;
    } else {
      $(data).find('.signupUsernameError').removeClass('noDisplay');
      return false;
    }
  }
  
  function checkIfValidPassword(pass) {
    $(data).find('.signupPasswordCheck').addClass('noDisplay');
    $(data).find('.signupPasswordError').addClass('noDisplay');
    $(data).find('.signupPasswordWarning').text('');
    if (pass.length < 8) {
      $(data).find('.signupPasswordError').removeClass('noDisplay');
      $(data).find('.signupPasswordWarning').text('min 8 characters');
      return false;
    } else {
      $(data).find('.signupPasswordCheck').removeClass('noDisplay');
      return true;
    }
  }
  
  function checkIfValidEmail(email) {
    $(data).find('.signupEmailCheck').addClass('noDisplay');
    $(data).find('.signupEmailError').addClass('noDisplay');
    $(data).find('.signupEmailWarning').text('');
    if (email.length > 0) {
      var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      if (!re.test(email)) {
        $(data).find('.signupEmailError').removeClass('noDisplay');
        $(data).find('.signupEmailWarning').text('invalid email');
        return false;
      } else {
        $(data).find('.signupEmailCheck').removeClass('noDisplay');
        return true;
      }
    } else {
      $(data).find('.signupEmailError').removeClass('noDisplay');
      return false;
    }
  }

  $(data).find('.signupUsernameEnter').focusout(function(event) {
    var name = $(data).find('.signupUsernameEnter').val().toLowerCase();
    $(data).find('.signupUsernameEnter').val(name);
    checkIfValidUsername(name);
  });
  
  $(data).find('.signupPasswordEnter').focusout(function(event) {
    checkIfValidPassword($(data).find('.signupPasswordEnter').val());
  });
  
  $(data).find('.signupEmailEnter').focusout(function(event) {
    var email = $(data).find('.signupEmailEnter').val().toLowerCase();
    $(data).find('.signupEmailEnter').val(email);
    checkIfValidEmail(email)
  });



  $(data).find('.createBtn').button();
  
  $(data).find('.createBtn').click(function(event) {
    if (checkIfValidUsername($(data).find('.signupUsernameEnter').val()) &&
        checkIfValidPassword($(data).find('.signupPasswordEnter').val()) &&
        checkIfValidEmail($(data).find('.signupEmailEnter').val()) ) {
      $.ajax({
        url: SERVICE_URL + 'users.php?request=createUser',
        method: 'POST',
        data: {
               userName: $(data).find('.signupUsernameEnter').val().toLowerCase(),
               password: $(data).find('.signupPasswordEnter').val(),
               email: $(data).find('.signupEmailEnter').val().toLowerCase()
              },
        type: 'xml',
        statusCode: {
          200: function(serviceData) {
          }
        }
      });
      $(event.target).closest('.bubbleContent').html();
      var div = document.createElement('div');
      $(div).css('text-align', 'center');
      var p = document.createElement('p');
      $(p).html('Ok, welcome aboard.');
      $(div).append(p);
      p = document.createElement('p');
      var btn = document.createElement('button');
      $(btn).text('close');
      
      var p = document.createElement('p');
      $(p).append(btn);
      $(div).append(p);
      $(event.target).closest('.bubbleContent').html(div);
      $(btn).click(function(event) {
        $(event.target).closest('.bubbleContent').data('bubble').close();
      });
      $(btn).button();
    }
  });
}