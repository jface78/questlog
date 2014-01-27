$(document).ready(function(event) {
  $('.signupUsernameEnter').focusout(function(event) {
    if ($(this).val().length > 0) {
      $('.signupUsernameCheck').css('display', 'inline-block');
    }
  });
  $('.signupPasswordRepeat').focusout(function(event) {
    if ('.signupPasswordEnter').focu
    if ($(this).val().length > 0) {
      $('.signupPasswordCheck').css('display', 'inline-block');
    }
  });
});
function init() {
alert('loaded');
}
