var API_URL = 'api/v1/';
var TEMPLATE_URL = 'templates/';
var SERVICE_URL = 'php/services/';
var EVENT_LOADED = 'loaded';
var LOCAL_API_KEY = 1;
var MAX_QUEST_NAME_LENGTH = 40;
var isLoggedIn = false;

var currentQuestData;
var userID;
var userName;

function handleLogout() {
  $.ajax({
    url: API_URL + '/logout',
    data: { apiKey: LOCAL_API_KEY},
    success: function(response) {
      isLoggedIn = false;
      resetQuestVars();
      $('#mainContent').fadeOut('normal', function() {
        window.location.reload();
      });
    }
  });
}

function handleLogin() {
  if (!$('#user').val().trim().length || !($('#passwd').val().trim().length)) {
    $('#warningMessage').text('Fill out both fields, dummy.');
  } else {
    $.ajax({
      url: API_URL + 'login/' + $('#user').val().trim().toLowerCase() + '/' + $('#passwd').val().trim(),
      type: 'GET',
      dataType: 'json',
      data: { apiKey: LOCAL_API_KEY},
      statusCode: {
        200: function(response) {
          if (!isLoggedIn) {
            userID = response.user_details.id;
            userName = response.user_details.name;
            isLoggedIn = true;
            $('#mainContent').fadeOut('normal', function() {
              addGreetingBox(response.user_details.name, response.user_details.date, response.user_details.ip);
              $('#warningMessage').text('You are currently logged in.');
              var div = document.createElement('div');
              $(div).attr('id', 'questlogLeft');
              $('#mainContent').html(div);
              generateMenu();
              loadQuestListings();
              /*
                require(['converse'], function (converse) {
                  alert('ok');
    converse.initialize({
        auto_list_rooms: false,
        auto_subscribe: false,
        bosh_API_URL: 'https://bind.conversejs.org', // Please use this connection manager only for testing purposes
        hide_muc_server: false,
        i18n: locales.en, // Refer to ./locale/locales.js to see which locales are supported
        prebind: false,
        show_controlbox_by_default: true,
        roster_groups: true
    });
});*/
            });
          }
        },
        401: function(response) {
          $('#warningMessage').text('Does not compute.');
        },
        500: function(error) {
          $('#warningMessage').text('There was an error logging you in. Try again in 4-6 weeks.');
        }
      }
    });
  }
}

function sanitizeTextForDB(text) {
  text = text.replace(new RegExp('(?:\r\n|\r|\n)','g'), '<br>');
  //text = text.replace(new RegExp('\/', 'g'), '|');
  return encodeURIComponent(text);
}

function santizeTextForTextarea(text) {
  var toHTML = $('<output>' + text + '</output>');
  $(toHTML).find('.roll').each(function(index, item) {
    var id = $(item).attr('id');
    $(item).replaceWith('[DICE_ROLL]' + id + '[/DICE_ROLL]');
  });
  text = $(toHTML).html();
  return text.replace(/<br\s*[\/]?>/gi, '\n');
}

function addPlayerBubble(div, characterID, characterName) {
  var span = document.createElement('span');
  $(span).addClass('addedPlayerBubble');
  $(span).attr('data-characterid', characterID);
  var closer = document.createElement('span');
  $(closer).css({height:'12px', width:'12px', marginRight:'3px'});
  $(closer).click(function(event) {
    $(span).remove();
  });
  $(span).append(closer);
  $(closer).button({
    text: false,
    icons: { primary: "ui-icon-close"}
  });
  $(span).append(characterName);
  $(div).find('#addedPlayers').append(span);
  $(div).find('#addedPlayers').animate({ scrollTop: $(div).find('#addedPlayers').scrollHeight}, 50);
}

function convertHTMLToBB(text) {
  text = text.replace(/<b\s*[\/]?>/gi, '[b]');
  text = text.replace(/<\/b\s*[\/]?>/gi, '[\/b]');
  text = text.replace(/<i\s*[\/]?>/gi, '[i]');
  text = text.replace(/<\/i\s*[\/]?>/gi, '[\/i]');
  return text;
}

function generateRandomNPC(div) {
  var char = new RandomNPC();
  if ($(div).closest('.ui-dialog').find('.genRaceSelect').val() != 'random') {
    char.race = $(div).closest('.ui-dialog').find('.genRaceSelect').val();
  }
  char.generate();
  $(div).attr('title', char.name + ' ' + char.title);
  $(div).closest('.ui-dialog').find('.ui-dialog-title').text(char.name + ' ' + char.title);
  $($(div).find('.chargenContent')[0]).html('');
  var htmlString = '<br /><b>' + char.name + ' ' + char.title + '</b><br /><br />' +
                   ucwords(char.getNumerator(char.age)) + ' ' + char.age + ' ' + char.gender + ' ' + char.race + '. ' +
                   ucwords(char.getPronoun(char.gender)) + '\'s ' + char.getNumerator(char.job) + ' ' + char.job + ' of ' +
                   char.jobSkill + ' skill with ' + char.getNumerator(char.trait1) + ' ' + char.trait1 + ' and ' + 
                   char.trait2 + ' demeanor. ' + char.description;
  $($(div).find('.chargenContent')[0]).html(htmlString);
}

function renderRandomNPC() {
  var char = new RandomNPC();
  var div = document.createElement('div');
  var controls = document.createElement('div');
  $(controls).text('race: ');
  var select = document.createElement('select');
  $(select).addClass('genRaceSelect');
  var option = document.createElement('option');
  $(option).val('random');
  $(option).text('Random');
  $(select).append(option);
  option = document.createElement('option');
  $(option).val('elf');
  $(option).text('Elf');
  $(select).append(option);
  option = document.createElement('option');
  $(option).val('dwarf');
  $(option).text('Dwarf');
  $(select).append(option);
  option = document.createElement('option');
  $(option).val('gnome');
  $(option).text('Gnome');
  $(select).append(option);
  option = document.createElement('option');
  $(option).val('human');
  $(option).text('Human');
  $(select).append(option);
  option = document.createElement('option');
  $(option).val('other');
  $(option).text('Other');
  $(select).append(option);
  $(controls).append(select);
  $(select).change(function() {
    //console.log($(this).val());
  });
  $(div).html(controls);
  var contentDiv = document.createElement('div');
  $(contentDiv).addClass('chargenContent');
  $(div).append(contentDiv);
  generateRandomNPC(div);

  var dialog = $(div).dialog({
    height: 500,
    width: 550,
    modal: false,
    buttons: {
      'regenerate': function() {
        generateRandomNPC(div);
      },
      'ok': function() {
        dialog.dialog( "close" );
      }
    },
    close: function() {
    }
  });   
}

function assignMenuButtonActions() {
  $('#logoutBtn').click(function(event) {
    handleLogout();
  });
  $('#userProfileBtn').click(function(event) {
    renderEditUser();
  });
  $('#newQuestBtn').click(function(event) {
    renderNewQuest();
  });
  $('#generateNPCBtn').click(function(event) {
    renderRandomNPC();
  });
}

function getRandomInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

function generateNPCName() {
  var returnString;
  switch(getRandomInt(0, 7)) {
    case 0:
      returnString = 'Fiseus';
      break;
    case 1:
      returnString = 'Luthien';
      break;
    case 2:
      returnString = 'Ar Awn';
      break;
    case 3:
      returnString = 'Sir Kalar';
      break;
    case 4:
      returnString = 'Shanksmow';
      break;
    case 5:
      returnString = 'Adon';
      break;
    case 6:
      returnString = 'Inspector Doffman';
      break;
    case 7:
      returnString = 'The Elf';
      break;
  }
  return returnString;
}

function generateWelcomeMessage(user) {
  var returnString;
  switch(getRandomInt(0, 7)) {
    case 0:
      returnString = "It's been a long time, " + user + ".";
      break;
    case 1:
      returnString = ucwords(user) + '. You son of a bitch.';
      break;
    case 2:
      returnString = 'Oh hi ' + user;
      break;
    case 3:
      returnString = "You've got a lot of nerve showing your face around here, " + user + ".";
      break;
    case 4:
      returnString = "I'm going to enjoy watching you die, " + user + ".";
      break;
    case 5:
      returnString = "Well, well, well. If it isn't " + user + ".";
      break;
    case 6:
      returnString = "Well met, " + user + ".";
      break;
    case 7:
      returnString = "Ugh, it's you again, " + user + ".";
      break;
  }
  return returnString;
}

function ucwords(str) {
  return (str + '')
    .replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function($1) {
      return $1.toUpperCase();
    });
}

function leadWithZero(numberString) {
  if (parseInt(numberString) < 10) {
    return '0' + numberString;
  } else {
    return numberString;
  }
}

function formatDate(dateStr) {
  if (dateStr) {
    var date = new Date(parseInt(dateStr)*1000);
    date = new Date(date.getTime() + (date.getTimezoneOffset()*60*1000));
    return 'on ' + date.toDateString() + ' at ' + date.toLocaleTimeString();
  } else {
    return 'at an unknown point in time'
  }
}

function addLoginBox() {
  $('.greetingBox').remove();
  var parent = document.createElement('header');
  $(parent).addClass('loginBox');
  var div = document.createElement('div');
  $(div).text('login ');
  var input = document.createElement('input');
  $(input).addClass('field');
  $(input).attr('type', 'text');
  $(input).attr('id', 'user');
  $(input).keypress(function(e) {
    if(e.which == 13) {
      handleLogin();
    }
  });
  $(div).append(input);
  $(parent).append(div);
  div = document.createElement('div');
  $(div).css('margin-top', '5px');
  $(div).text('passwd ');
  input = document.createElement('input');
  $(input).addClass('field');
  $(input).attr('type', 'password');
  $(input).attr('id', 'passwd');
  $(input).val('Igh4oosothai');
  $(input).keypress(function(e) {
    if(e.which == 13) {
      handleLogin();
    }
  });
  $(div).append(input);
  $(parent).append(div);
  div = document.createElement('div');
  $(div).css('margin-top', '5px');
  var button = document.createElement('button');
  $(button).text('submit');
  $(div).append(button);
  $(div).append('<br />');
  var a = document.createElement('a');
  $(a).attr('href', '#');
  $(a).click(function(event) {
    event.preventDefault();
    event.stopPropagation();
    renderSignup();
  });
  $(a).text('join');
  $(a).css('margin-right', '5px');
  $(div).append(a);
  a = document.createElement('a');
  $(a).attr('href', '#');
  $(a).click(function(event) {
    event.preventDefault();
    event.stopPropagation();
    renderForgotPassword();
  });
  $(a).text('forgot?');
  $(div).append(a);
  $(parent).append(div);
  $('#leftHeader').after(parent);
  $(button).click(handleLogin);
  $(button).keypress(function(e) {
    if(e.which == 13) {
      handleLogin();
    }
  });
}

function addGreetingBox(user, date, ip) {
  $('.loginBox').remove();
  var div = document.createElement('header');
  $(div).addClass('greetingBox');
  var span = document.createElement('span');
  $(span).css('font-size', '11px');
  $(span).html('<b>'+generateWelcomeMessage(user) + '</b>');
  $(div).append(span);
  $(div).append('<br /><br />');
  span = document.createElement('span');
  $(span).css('color', '#999');
  $(span).append('You last logged in ' + formatDate(date) + ' from ' + ip);
  $(div).append(span);
  $('#leftHeader').after(div);
}

function generateMenu() {
  var div = document.createElement('div');
  $(div).attr('id', 'questlogRight');
  $.ajax({
    url: TEMPLATE_URL + 'menu.html',
    success: function(template) {
      $(div).html(template);
      $('#mainContent').append(div);
      assignMenuButtonActions();
    }
  });
}

function fetchRandomPost() {
  $.ajax({
    url: SERVICE_URL + '/fetchRandomPost.php',
    dataType: 'json',
    success: function(data) {
      var div = document.createElement('div');
      $(div).addClass('randomPostContainer');
      var subdiv = document.createElement('subdiv');
      $(subdiv).css('margin-left', '3px');
      var span = document.createElement('span');
      $(span).addClass('randomPostTitle');
      $(span).text('Random Post');
      $(subdiv).append(span);
      $(subdiv).append('<br />');
      span = document.createElement('span');
      $(span).css('margin-left', '3px');
      $(span).append('From <i>' + data.name + ',</i> ' + data.post_number +' of ' + data.total_posts + ' total entries.');
      $(subdiv).append(span);
      $(div).append(subdiv);
      subdiv = document.createElement('div');
      $(subdiv).addClass('postHeader');
      var date = new Date(parseInt(data.date)*1000);
      if (date.getFullYear() < 2015) {
        var now = new Date();
        if (date.getMonth() > now.getMonth() || (date.getMonth() == now.getMonth() && date.getDate() > now.getDate())) {
          date.setFullYear(2014);
        } else {
          date.setFullYear(2015);
        }
      }
      var header = '&nbsp;' + data.post_id + '&nbsp;&nbsp;Posted on ' + date.toDateString() + ' at ' +
                            date.toLocaleTimeString() + ' by <a class="characterNameLink">' + data.poster_name + '</a>';
      $(subdiv).append(header);
      $(div).append(subdiv);
      subdiv = document.createElement('div');
      $(subdiv).addClass('postBody odd');
      $(subdiv).html(data.text);
      $(subdiv).find('img').remove();
      $(div).append(subdiv);
      $('#mainContent').html(div);
    }
  });
}

$(document).ready(function() {
  console.log('Questlog BETA');
  // Fallback in case jquery-ui cdn is inaccessible.
  if (!$.ui) {
    head = document.getElementsByTagName('head');
    script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'js/plugins/jquery-ui.min.js';
    head[0].appendChild(script);
  }
  // Fallback in case datatables cdn inaccessible.
  if (!jQuery.fn.dataTable) {
    head = document.getElementsByTagName('head');
    script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'js/plugins/jquery.dataTables.min.js';
    head[0].appendChild(script);
  }
  $('#mainContent').hide();
  $('footer').text('Copyright ' + new Date().getFullYear() + ' QuestLog.org');
  var hour = new Date().getHours();
  if (hour < 6 || hour > 18) {
    $('#titleImg').attr('src', 'img/title.06.gif');
  }
  $.ajax({
    url: API_URL + '/session',
    method: 'GET',
    data: {apiKey: LOCAL_API_KEY},
    dataType: 'json',
    statusCode: {
      401: function() {
        isLoggedIn = false;
        addLoginBox();
        fetchRandomPost();
        $('#mainContent').fadeIn();
      },
      200: function(response) {
        userID = response.user_details.id;
        userName = response.user_details.name;
        isLoggedIn = true;
        $('#mainContent').fadeOut('normal', function() {
          addGreetingBox(response.user_details.name, response.user_details.date, response.user_details.ip);
          $('#warningMessage').text('You are currently logged in.');
          var div = document.createElement('div');
          $(div).attr('id', 'questlogLeft');
          $('#mainContent').html(div);
          generateMenu();
          /*
          require(['converse'], function (converse) {
            alert('ok');
            converse.initialize({
              auto_list_rooms: false,
              auto_subscribe: false,
              bosh_service_url: 'https://bind.conversejs.org', // Please use this connection manager only for testing purposes
              hide_muc_server: false,
              i18n: locales.en, // Refer to ./locale/locales.js to see which locales are supported
              prebind: false,
              show_controlbox_by_default: true,
              roster_groups: true
            });
          });*/
          loadQuestListings();
        });
      }
    },
    error: function(response) {
      
    }
  });
});