<!DOCTYPE html>
<html>
  <head>
    <meta charset=utf-8> 
    <title>QUESTLOG</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script type="text/javascript">
      window.jQuery || document.write('<script type="text/javascript" src="js/plugins/jquery.min.js"><\/script>');
    </script>
    <link rel="stylesheet" href="css/main.css" type="text/css">
    <script>
    
    function updatePassword(hashString) {
      $('#passwordError').text('');
      if ($($('input[type="password"]')[0]).val() != $($('input[type="password"]')[1]).val()) {
        $('#passwordError').text('Password mismatch.');
        return;
      }
      if ($($('input[type="password"]')[0]).val().trim() == '') {
        return;
      }
      $.ajax({
        url: 'php/services/manageAccounts.php?request=password',
        method: 'PUT',
        data: {hash: hashString, pass: $($('input[type="password"]')[0]).val()},
        dataType: 'json',
        statusCode: {
          200: function() {
            $('#passwordError').text('Your password has been successfully updated.');
          },
          400: function() {
            $('#passwordError').text('Your request is invalid. Make sure you copied the URL correctly.');
          },
          410: function() {
            $('#passwordError').text('Your request expired. Now you have to start all over again.');
          },
          500: function() {
            $('#passwordError').text('There was an error updating your password. Please try again later.');
          }
        }
      });
    }
    
    $(document).ready(function() {
      $('footer').text('Copyright ' + new Date().getFullYear() + ' QuestLog.org');
      var hour = new Date().getHours();
      if (hour < 6 || hour > 18) {
        $('#titleImg').attr('src', 'img/title.06.gif');
      }
      var vars = [], hash;
      var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
      for(var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
      }
      if (vars.hasOwnProperty('hash')) {
        $('#greeting').text('Enter your new password below.');
        $('#passwordResetForm button').click(function() {
          updatePassword(vars['hash']);
        });
      } else {
        $('#passwordResetForm').remove();
      }
    });
    </script>
  </head>
  <body>
    <div class="wrapper">
      <header class="floatLeft" id="leftHeader">
        <img src="img/title.05.gif" alt="QuestLog" id="titleImg"><br />
        <div id="warningMessage">This is QuestLog. RPGs and collaborative storytelling.<br>
        <span id="greeting" style="color:#fff;font-size:14px;">Nothing to see here.</span>
      </header>
      <hr class="clear"></hr>
      <div id="mainContent">
        <div style="width:300px;text-align:right;" id="passwordResetForm">
          New Password: <input type="password" style="width:150px;"><br />
          Repeat: <input type="password" style="width:150px;"><br />
          <button style="width:150px;">submit</button><br />
          <div style="text-align:center;font-weight:bold;" id="passwordError"></div>
        </div>
      </div>
      <hr></hr>
      <div class="push"></div>
    </div>
  <footer></footer>
  </body>
</html>