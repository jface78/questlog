function signupInit(data) {
  $(data).find('.signupUsernameEnter').focusin(function(event) {
    $(data).find('.signupUsernameCheck').css('display', 'none');
    $(data).find('.signupUsernameError').css('display', 'none');
    $(data).find('.signupUsernameWarning').text('');
  });

  $(data).find('.signupUsernameEnter').focusout(function(event) {
    if ($(this).val().length > 0) { 
      $(data).find('.signupUsernameCheck').css('display', 'inline-block');
    }
  });
  
  $(data).find('.signupPasswordEnter').focusin(function(event) {
    $(data).find('.signupPasswordWarning').text('');
    $(data).find('.signupPasswordCheck').css('display', 'none');
    $(data).find('.signupPasswordError').css('display', 'none');
  });
  $(data).find('.signupPasswordRepeat').focusin(function(event) {
    $(data).find('.signupPasswordRepeatCheck').css('display', 'none');
    $(data).find('.signupPasswordRepeatError').css('display', 'none');
  });
  $(data).find('.signupPasswordRepeat').focusout(function(event) {
    if ($(this).val() == $(data).find('.signupPasswordEnter').val() && $(this).val().length > 0 &&
        $(data).find('.signupPasswordEnter').val() > 0) {
      $(data).find('.signupPasswordCheck').css('display', 'inline-block');
      $(data).find('.signupPasswordRepeatCheck').css('display', 'inline-block');
    } else {
      $(data).find('.signupPasswordWarning').text('Passwords do not match');
      $(data).find('.signupPasswordError').css('display', 'inline-block');
      $(data).find('.signupPasswordRepeatError').css('display', 'inline-block');
    }
  });
  $(data).find('.signupEmailEnter').focusin(function(event) {
    $(data).find('.signupEmailWarning').text('');
    $(data).find('.signupEmailCheck').css('display', 'none');
    $(data).find('.signupEmailError').css('display', 'none');
  });
  $(data).find('.signupEmailRepeat').focusin(function(event) {
    $(data).find('.signupEmailRepeatCheck').css('display', 'none');
    $(data).find('.signupEmailRepeatError').css('display', 'none');
  });
  $(data).find('.signupEmailRepeat').focusout(function(event) {
    if ($(this).val() == $(data).find('.signupEmailEnter').val() && $(this).val().length > 0 &&
        $(data).find('.signupEmailEnter').val() > 0) {
      $(data).find('.signupEmailCheck').css('display', 'inline-block');
      $(data).find('.signupEmailRepeatCheck').css('display', 'inline-block');
    } else {
      $(data).find('.signupEmailWarning').text('Emails do not match');
      $(data).find('.signupEmailError').css('display', 'inline-block');
      $(data).find('.signupEmailRepeatError').css('display', 'inline-block');
    }
  });
  $(data).find('.createBtn').button();
}