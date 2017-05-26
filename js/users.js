function signup(div, dialogObject) {
  $(div).find('.signupAlert').css('opacity', 0);
  $(div).find('.signupError').text('');
  var alerts = $(div).find('.signupAlert');
  var inputs = $(div).find('input');
  if (!$($(inputs)[0]).val().trim().length || !$($(inputs)[1]).val().trim().length ||
      !$($(inputs)[2]).val().trim().length || !$($(inputs)[3]).val().trim().length) {
    $(div).find('.signupAlert').css('opacity', 1);
    $(div).find('.signupError').text('"It\'s so fine and yet so terrible to stand in front of a blank canvas." - Paul Cezanne');
    return;
  }
  if ($($(inputs)[2]).val().trim() != $($(inputs)[3]).val().trim()) {
    $($(alerts)[2]).css('opacity', 1);
    $($(alerts)[3]).css('opacity', 1);
    $(div).find('.signupError').text('"A gentleman would be ashamed should his deeds not match his words." - Confucious');
    return;
  }
  $.ajax({
    url: SERVICE_URL + 'manageAccounts.php?request=account',
    method: 'POST',
    data: {user: $($(inputs)[1]).val().trim(), email: $($(inputs)[0]).val().trim(), pass: $($(inputs)[3]).val().trim()},
    dataType: 'json',
      statusCode: {
        409: function() {
          $($(alerts)[1]).css('opacity', 1);
          $(div).find('.signupError').text('"Taking something from one man and making it worse is plagiarism." - George A. Moore');
        },
        400: function() {
          $($(alerts)[0]).css('opacity', 1);
          $(div).find('.signupError').text('"The world just does not fit conveniently into the format of a 35mm camera." - W. Eugene Smith');
        },
        200: function(response) {
          userID = response.users[0].userID;
          $('#user').val($($(inputs)[1]).val().trim());
          $('#passwd').val($($(inputs)[3]).val().trim());
          dialogObject.dialog('close');
          handleLogin();
        }
      }
  });
}

function renderSignup() {
  var popupContainer = document.createElement('div');
  $(popupContainer).css('font-size', '10px');
  $(popupContainer).attr('title', 'join');
  var rowDiv = document.createElement('div');
  $(rowDiv).addClass('alignCenter');
  var span = document.createElement('span');
  $(span).addClass('signupLabel');
  $(span).html('email&nbsp;');
  $(rowDiv).append(span);
  var input = document.createElement('input');
  $(input).attr('type', 'text');
  $(rowDiv).append(input);
  var sup = document.createElement('sup');
  $(sup).addClass('signupAlert');
  $(sup).text('*');
  $(rowDiv).append(sup);
  $(popupContainer).append(rowDiv);
  var rowDiv = document.createElement('div');
  $(rowDiv).addClass('alignCenter');
  var span = document.createElement('span');
  $(span).addClass('signupLabel');
  $(span).html('desired login&nbsp;');
  $(rowDiv).append(span);
  var input = document.createElement('input');
  $(input).attr('type', 'text');
  $(rowDiv).append(input);
  sup = document.createElement('sup');
  $(sup).addClass('signupAlert');
  $(sup).text('*');
  $(rowDiv).append(sup);
  $(popupContainer).append(rowDiv);
  rowDiv = document.createElement('div');
  $(rowDiv).addClass('alignCenter');
  span = document.createElement('span');
  $(span).addClass('signupLabel');
  $(span).html('passwd&nbsp;');
  $(rowDiv).append(span);
  input = document.createElement('input');
  $(input).attr('type', 'password');
  $(rowDiv).append(input);
  sup = document.createElement('sup');
  $(sup).addClass('signupAlert');
  $(sup).text('*');
  $(rowDiv).append(sup);
  $(popupContainer).append(rowDiv);
  rowDiv = document.createElement('div');
  $(rowDiv).addClass('alignCenter');
  span = document.createElement('span');
  $(span).addClass('signupLabel');
  $(span).html('confirm&nbsp;');
  $(rowDiv).append(span);
  input = document.createElement('input');
  $(input).attr('type', 'password');
  $(rowDiv).append(input);
  sup = document.createElement('sup');
  $(sup).addClass('signupAlert');
  $(sup).text('*');
  $(rowDiv).append(sup);
  $(popupContainer).append(rowDiv);
  rowDiv = document.createElement('div');
  $(rowDiv).addClass('signupError');
  $(popupContainer).append(rowDiv);
  var dialog = $(popupContainer).dialog({
    height: 250,
    width: 400,
    modal: true,
    buttons: {
      'join': function() {
        signup(popupContainer, dialog);
      },
      'never mind': function() {
        dialog.dialog('close');
      }
    },
    close: function() {
    }
  });
}

function resetPassword(div, dialogObject) {
  $(div).find('.signupError').text('');
  if (!$($(div).find('input')[0]).val().trim().length) {
    $(div).find('.signupError').text('<span style="font-style:normal;">Enter your email address, dummy.</span>');
    return;
  }
  else {
    $.ajax({
    url: SERVICE_URL + 'manageAccounts.php?request=password',
    method: 'POST',
    data: {email: $($(div).find('input')[0]).val().trim()},
    dataType: 'json',
      statusCode: {
        400: function() {
          $(div).find('.signupError').text('<span style="font-style:normal;">Invalid email address.</span>');
        },
        404: function() {
          $(div).find('.signupError').text('<span style="font-style:normal;">There is is no such account associated with this email address.</span>');
        },
        200: function() {
          dialogObject.dialog('close');
          var newDiv = document.createElement('div');
          $(newDiv).html('Instructions to reset your password have been sent to the provided email address.<br /><br /><b>This will expire in one hour.</b>');
          var dialog = $(newDiv).dialog({
            height: 250,
            width: 300,
            modal: true,
            buttons: {
              'K': function() {
                dialog.dialog('close');
              }
            }
          });
        }
      }
    });
  }
}

function renderForgotPassword() {
  var popupContainer = document.createElement('div');
  $(popupContainer).css('font-size', '10px');
  $(popupContainer).attr('title', 'recover');
  var rowDiv = document.createElement('div');
  $(rowDiv).addClass('alignCenter');
  var span = document.createElement('span');
  $(span).addClass('signupLabel');
  $(span).html('email&nbsp;');
  $(rowDiv).append(span);
  var input = document.createElement('input');
  $(input).attr('type', 'text');
  $(rowDiv).append(input);
  $(popupContainer).append(rowDiv);
  rowDiv = document.createElement('div');
  $(rowDiv).addClass('signupError');
  $(popupContainer).append(rowDiv);
  var dialog = $(popupContainer).dialog({
    height: 250,
    width: 400,
    modal: true,
    buttons: {
      'reset password': function() {
        resetPassword(popupContainer, dialog);
      },
      'never mind': function() {
        dialog.dialog('close');
      }
    },
    close: function() {
    }
  });
}

function editUser(dialogObject, email) {
  $('.signupError').text('');
  $.ajax({
    url: API_URL + 'users?apiKey=' + LOCAL_API_KEY,
    method: 'PUT',
    data: {email: email},
    dataType: 'json',
    statusCode: {
      200: function() {
        $('.signupError').text('Your details have been updated.');
      },
      400: function() {
        $('.signupError').text('Invalid email address.');
      },
      403: function() {
        $('.signupError').text('You are not authorized to do this.');
      }
    }
  });
}

function renderEditUser() {
  $.ajax({
    url: API_URL + 'users/uid/' + userID,
    method: 'GET',
    dataType: 'json',
    data: {apiKey: LOCAL_API_KEY},
    statusCode: {
      200: function(response) {
        var popupContainer = document.createElement('div');
        $(popupContainer).css('font-size', '10px');
        $(popupContainer).attr('title', 'Edit User');
        var rowDiv = document.createElement('div');
        var span = document.createElement('span');
        $(span).addClass('signupLabel');
        $(span).html('email&nbsp;');
        $(rowDiv).append(span);
        var emailInput = document.createElement('input');
        $(emailInput).addClass('labeledInputText');
        $(emailInput).attr('type', 'text');
        $(emailInput).val(response.users[0].email);
        $(emailInput).focus(function() { $(this).select() });
        $(emailInput).mouseup(function(e){e.preventDefault();});
        $(rowDiv).append(emailInput);
        $(popupContainer).append(rowDiv);
        rowDiv = document.createElement('div');
        span = document.createElement('span');
        $(span).addClass('signupLabel');
        $(span).html('post alert by email&nbsp;');
        $(rowDiv).append(span);
        var input = document.createElement('input');
        $(input).css('margin', '0px');
        $(input).attr('type', 'checkbox');
        $(input).prop('disabled', true);
        $(rowDiv).append(input);
        $(popupContainer).append(rowDiv);
        rowDiv = document.createElement('div');
        $(rowDiv).addClass('signupError');
        $(popupContainer).append(rowDiv);
        var dialog = $(popupContainer).dialog({
          height: 250,
          width: 400,
          modal: true,
          buttons: {
            'update': function() {
              editUser(dialog, $(emailInput).val());
            },
            'never mind': function() {
              dialog.dialog('close');
            }
          },
          close: function() {
          }
        });
      }
    }
  });
}