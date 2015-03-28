var API_URL = 'api/v1/';
var TEMPLATE_URL = 'templates/';
var SERVICE_URL = 'php/services/';
var EVENT_LOADED = 'loaded';
var LOCAL_API_KEY = 1;
var isLoggedIn = false;

var currentQuestID;
var currentQuestPage = 1;
var currentQuestLimiter = 50;
var currentQuestSort = 'DESC';
var currentQuestPageTotal = 1;

function resetQuestVars() {
  currentQuestID = null;
  currentQuestPage = 1;
  currentQuestLimiter = 50;
  currentQuestSort = 'DESC';
  currentQuestPageTotal = 1;
}

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

function renderNewPostWindow() {
  var popupContainer = document.createElement('div');
  $(popupContainer).attr('title', 'new post');
  var textArea = document.createElement('textarea');
  $(textArea).addClass('postTextArea');
  $(popupContainer).append(textArea);
  $(document.body).append(popupContainer);
  var dialog = $(popupContainer).dialog({
    height: 300,
    width: 350,
    modal: false,
    buttons: {
      'Post': function() {
        newPost();
      },
      Cancel: function() {
        dialog.dialog( "close" );
      }
    },
    close: function() {
    }
  });
}

function renderEditWindow(button) {
  var popupContainer = document.createElement('div');
  $(popupContainer).attr('title', 'edit post #' + $(button).data('postId'));
  var textArea = document.createElement('textarea');
  $(textArea).addClass('postTextArea');
  var text = $(button).parent().parent().parent().find('.postBody').html();
  text = convertHTMLToBB(text);
  text = $('<div>').html(text).text();
  $(textArea).val(text);
  $(popupContainer).append(textArea);
  $(document.body).append(popupContainer);
  var dialog = $(popupContainer).dialog({
    height: 300,
    width: 350,
    modal: false,
    buttons: {
      'Edit': function() {
        editPost($(button).data('postId'));
      },
      Cancel: function() {
        dialog.dialog( "close" );
      }
    },
    close: function() {
    }
  });
}

function renderDeletePostWindow(button) {
  var popupContainer = document.createElement('div');
  $(popupContainer).attr('title', 'Sure about that?');
  $(popupContainer).append('Delete post #' + $(button).data('postId') + '? ' + generateNPCName() + ' hasn\'t read it yet.');
  $(document.body).append(popupContainer);
  var dialog = $(popupContainer).dialog({
    height: 200,
    width: 350,
    modal: false,
    buttons: {
      'Delete': function() {
        deletePost($(button).data('postId'));
        dialog.dialog('close');
      },
      Cancel: function() {
        dialog.dialog('close');
      }
    },
    close: function() {
    }
  });
}

function newPost() {
}

function editPost() {

}

function deletePost(postID) {
  $.ajax({
    url: SERVICE_URL + 'managePosts.php?postID=' + postID,
    method: 'DELETE',
    success: function(data) {
      $('#post_' + postID).remove();
      $('.postBody').each(function(index, item) {
        $(item).removeClass('even odd');
        if (index % 2 == 0) {
          $(item).addClass('even');
        } else {
          $(item).addClass('odd');
        }
      });
    }
  });
}

function convertHTMLToBB(text) {
  text = text.replace(/<b\s*[\/]?>/gi, '[b]');
  text = text.replace(/<\/b\s*[\/]?>/gi, '[\/b]');
  text = text.replace(/<i\s*[\/]?>/gi, '[i]');
  text = text.replace(/<\/i\s*[\/]?>/gi, '[\/i]');
  return text;
}

function addPagination() {
  $('.questNavigation').empty();
  var buttonStartIndex = currentQuestPage - 2;
  if (buttonStartIndex >= currentQuestPageTotal) {
    buttonStartIndex = currentQuestPage - 4;
  }
  if (buttonStartIndex < 1) {
    buttonStartIndex = 1;
  }
  if (currentQuestPageTotal - buttonStartIndex < 4 && currentQuestPageTotal - 4 > 1) {
    buttonStartIndex = currentQuestPageTotal - 4;
  }
  function addClickEvent(btn) {
    $(btn).click(function(event) {
      var page;
      if ($(btn).text() == '<<') {
        currentQuestPage = 1;
      } else if ($(btn).text() == '>>') {
        currentQuestPage  = currentQuestPageTotal;
      } else {
        currentQuestPage  = parseInt($(btn).text());
      }
      getPostsByPage();
    });
  }
  var btn = document.createElement('button');
  $(btn).addClass('questNavButton');
  $(btn).text('<<');
  if (currentQuestPage > 1) {
    addClickEvent(btn);
  } else {
    $(btn).prop('disabled', true);
    $(btn).addClass('disabled');
  }
  $('.questNavigation').append(btn);
  for (var i=buttonStartIndex; i < buttonStartIndex+5 && i<currentQuestPageTotal+1; i++) {
    btn = document.createElement('button');
    $(btn).addClass('questNavButton');
    $(btn).text(i);
    if (i==currentQuestPage) {
      $(btn).prop('disabled', true);
      $(btn).css('text-decoration', 'underline');
      $(btn).addClass('disabled');
    }
    addClickEvent(btn);
    $('.questNavigation').append(btn);
  }
  var btn = document.createElement('button');
  $(btn).addClass('questNavButton');
  $(btn).text('>>');
  if (currentQuestPage < currentQuestPageTotal) {
    addClickEvent(btn);
  } else {
    $(btn).prop('disabled', true);
    $(btn).addClass('disabled');
  }
  $('.questNavigation').append(btn);
}

function getPostsByPage() {
  $('#questContent').fadeOut('fast', function() {
    $('#questContent').empty();
    var service = API_URL + 'quest/' + currentQuestID;
    if (currentQuestLimiter) {
      service += '/limit/' + currentQuestLimiter;
    }
    if (currentQuestPage) {
      service += '/page/' + currentQuestPage;
    }
    if (currentQuestSort) {
      service += '/order/' + currentQuestSort;
    }
    $.ajax({
      url: service,
      method: 'GET',
      data: {apiKey: LOCAL_API_KEY},
      dataType: 'json',
      statusCode: {
        200: function(response) {
          for (var i=0; i < response.posts.length; i++) {
            var div = document.createElement('div');
            $(div).attr('id', 'post_' + response.posts[i].id);
            var header = document.createElement('header');
            var span = document.createElement('span');
            $(span).addClass('floatLeft');
            $(span).text('#' + response.posts[i].id);
            $(span).append('&nbsp;&nbsp;');
            $(span).append('Posted&nbsp;');
            var date = formatDate(parseInt(response.posts[i].timestamp));
            $(span).append(date + '&nbsp;by&nbsp;');
            var a = document.createElement('a');
            $(a).addClass('characterNameLink');
            $(a).text(response.posts[i].postedBy);
            $(span).append(a);
            $(header).append(span);
            span = document.createElement('span');
            $(span).addClass('floatRight');
            var img = document.createElement('img');
            $(img).addClass('pointer editPostBtn');
            $(img).attr('alt', 'edit post');
            $(img).attr('title', 'edit post');
            $(img).attr('src', 'img/icon.edit_dark.gif');
            $(img).attr('data-post-id', response.posts[i].id);
            $(img).click(function(event) {
              event.preventDefault();
              event.stopPropagation();
              renderEditWindow(this);
            });
            $(span).append(img);
            $(span).append('&nbsp;');
            img = document.createElement('img');
            $(img).attr('alt', 'delete post');
            $(img).attr('title', 'delete post');
            $(img).addClass('pointer deletePostBtn');
            $(img).attr('src', 'img/icon.delete_dark.gif');
            $(img).attr('data-post-id', response.posts[i].id);
            $(img).click(function(event) {
              event.preventDefault();
              event.stopPropagation();
              renderDeletePostWindow(this);
            });
            $(span).append(img);
            $(header).append(span);
            $(header).addClass('postHeader');
            $(div).append(header);
            var section = document.createElement('section');
            $(section).addClass('postBody');
            if (i % 2 == 0) {
              $(section).addClass('even');
            } else {
              $(section).addClass('odd');
            }
            $(section).html(response.posts[i].text);
            $(div).append(section);
            $('#questContent').append(div);
          }
          currentQuestID = parseInt(response.questID);
          currentQuestPage = parseInt(response.currentPage);
          currentQuestLimiter = parseInt(response.delimiter);
          currentQuestPageTotal = parseInt(response.pageCount);
          currentQuestSort = response.order;
          addPagination();
          $('#questContent').fadeIn('fast');
        }
      }
    });
  });
}

function loadQuest(questID) {
  currentQuestID = parseInt(questID);
  $.ajax({
    url: TEMPLATE_URL + 'quest.html',
    success: function(template) {
      $('#questlogLeft').html(template);
      var buttons = $('.questMenu');
      $(buttons[0]).click(function() {
        $('#mainContent').fadeOut('normal', function() {
          resetQuestVars();
          loadQuestListings();
        });
      });
      $(buttons[2]).click(function() {
        if (currentQuestSort == 'ASC') {
          currentQuestSort = 'DESC';
        } else {
          currentQuestSort = 'ASC';
        }
        getPostsByPage(); 
      });
      $(buttons[3]).click(function() {
        getPostsByPage(); 
      });
      $(buttons[4]).click(function() {
        renderNewPostWindow();
      });
      getPostsByPage(); 
      $('#questlogLeft').fadeIn('fast');
    }
  });
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
  var htmlString = ucwords(char.getNumerator(char.age)) + ' ' + char.age + ' ' + char.gender + ' ' + char.race + '. ' +
                   ucwords(char.getPronoun(char.gender)) + '\'s ' + char.getNumerator(char.job) + ' ' + char.job + ' of ' +
                   char.jobSkill + ' skill with ' + char.getNumerator(char.trait1) + ' ' + char.trait1 + ' and ' + 
                   char.trait2 + ' demeanor. ' + char.description;
                   console.log(htmlString);
  $($(div).find('.chargenContent')[0]).html(htmlString);
  console.log($(div).find('.chargenContent').length);
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
    console.log($(this).val());
  });
  $(div).html(controls);
  var contentDiv = document.createElement('div');
  $(contentDiv).addClass('chargenContent');
  $(div).append(contentDiv);
  generateRandomNPC(div);
  $(document.body).append(div);

  var dialog = $(div).dialog({
    height: 300,
    width: 350,
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

function loadQuestListings() {
  $.ajax({
    url: TEMPLATE_URL + 'questListings.html',
    success: function(template) {
      $('#questlogLeft').html(template);
      $.ajax({
        url: API_URL + 'quests',
        method: 'GET',
        data: {apiKey: LOCAL_API_KEY},
        dataType: 'json',
        statusCode: {
          200: function(response) {
            $('#logoutBtn').click(function(event) {
              handleLogout();
            });
            $('#generateNPCBtn').click(function(event) {
              renderRandomNPC();
            });
            for (var i=0; i < response.quests.gmQuests.length; i++) {
              var tr = document.createElement('tr');
              var td = document.createElement('td');
              $(td).text(response.quests.gmQuests[i].title);
              $(td).addClass('log-left');
              $(td).attr('data-quest-id', response.quests.gmQuests[i].questID);
              $(td).click(function() {
                var id = $(this).data('questId');
                $('#questlogLeft').fadeOut('fast', function() {
                  loadQuest(id);
                });
              });
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.gmQuests[i].count);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.gmQuests[i].lastPostBy);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(new Date(parseInt(response.quests.gmQuests[i].lastPostDate)*1000).toDateString());
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              var btn = document.createElement('button');
              $(btn).addClass('smallButton');
              $(btn).css('margin-right', '5px');
              $(btn).text('edit');
              $(td).append(btn);
              btn = document.createElement('button');
              $(btn).addClass('smallButton');
              $(btn).text('delete');
              $(td).append(btn);
              $(tr).append(td);
               td = document.createElement('td');
              $(td).text(response.quests.gmQuests[i].sortable);
              $(tr).append(td);
              $('#gmQuests tbody').append(tr);
            }
            $('#gmQuests').DataTable({
              'paging':false,
              'searching':false,
              'autoWidth':false,
              'language': {
                'search': 'Search GM Quests: ',
                'info': ''
              },
              'columnDefs': [
                {'class': 'alignCenter', 'targets':[4]},
                { 'width': '25%', 'targets': 0 },
                { 'width': '150px', 'targets': 4},
                { 'visible': false, 'targets':[5]},
                { "iDataSort": 5, "targets": [ 0 ] }
              ],
              "order": [[ 0, "asc" ]],
              'initComplete':function() {
                $('#gmQuests_filter').css('text-align', 'right');
                $('#gmQuests_filter input').addClass('field');
              }
            });

            for (var i=0; i < response.quests.playerQuests.length; i++) {
              var tr = document.createElement('tr');
              var td = document.createElement('td');
              $(td).text(response.quests.playerQuests[i].title);
              $(td).addClass('log-left');
              $(td).attr('data-quest-id', response.quests.playerQuests[i].questID);
              $(td).click(function() {
                var id = $(this).data('questId');
                $('#questlogLeft').fadeOut('fast', function() {
                  loadQuest(id);
                });
              });
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.playerQuests[i].count);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.playerQuests[i].gm);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.playerQuests[i].lastPostBy);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(new Date(parseInt(response.quests.playerQuests[i].lastPostDate)*1000).toDateString());
              $(tr).append(td);
               td = document.createElement('td');
              $(td).text(response.quests.playerQuests[i].sortable);
              $(tr).append(td);
              $('#playerQuests tbody').append(tr);
            }
            $('#playerQuests').DataTable({
              'paging':false,
              'searching':false,
              'autoWidth':false,
              'language': {
                'search': 'Search Player Quests: ',
                'info': ''
              },
              'columnDefs': [
                { 'width': '25%', 'targets': 0 },
                { 'visible': false, 'targets':[5]},
                { "iDataSort": 5, "targets": [ 0 ] }
              ],
              "order": [[ 0, "asc" ]],
              'initComplete':function() {
                $('#playerQuests_filter').css('text-align', 'right');
                $('#playerQuests_filter input').addClass('field');
              }
            });

            for (var i=0; i < response.quests.otherQuests.length; i++) {
              var tr = document.createElement('tr');
              var td = document.createElement('td');
              $(td).text(response.quests.otherQuests[i].title);
              $(td).addClass('log-left');
              $(td).attr('data-quest-id', response.quests.otherQuests[i].questID);
              $(td).click(function() {
                var id = $(this).data('questId');
                $('#questlogLeft').fadeOut('fast', function() {
                  loadQuest(id);
                });
              });
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.otherQuests[i].count);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.otherQuests[i].gm);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(response.quests.otherQuests[i].lastPostBy);
              $(tr).append(td);
              td = document.createElement('td');
              $(td).addClass('log-cell');
              $(td).text(new Date(parseInt(response.quests.otherQuests[i].lastPostDate)*1000).toDateString());
              $(tr).append(td);
              td = document.createElement('td');
              $(td).text(response.quests.otherQuests[i].sortable);
              $(tr).append(td);
              $('#otherQuests tbody').append(tr);
            }
            $('#otherQuests').DataTable({
              'paging':false,
              'searching':false,
              'autoWidth':false,
              'language': {
                'search': 'Search Other Quests: ',
                'info': ''
              },
              'columnDefs': [
                { 'width': '25%', 'targets': 0 },
                { 'visible': false, 'targets':[5]},
                { "iDataSort": 5, "targets": [ 0 ] }
              ],
              "order": [[ 0, "asc" ]],
              'initComplete':function() {
                $('#otherQuests_filter').css('text-align', 'right');
                $('#otherQuests_filter input').addClass('field');
              }
            });
            $('#mainContent').fadeIn();
          }
        }
      });
    }
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
  //  discuss at: http://phpjs.org/functions/ucwords/
  // original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  // improved by: Waldo Malqui Silva
  // improved by: Robin
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Onno Marsman
  //    input by: James (http://www.james-bell.co.uk/)
  //   example 1: ucwords('kevin van  zonneveld');
  //   returns 1: 'Kevin Van  Zonneveld'
  //   example 2: ucwords('HELLO WORLD');
  //   returns 2: 'HELLO WORLD'

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
      console.log(date.toDateString());
      if (date.getFullYear() < 2015) {
        var now = new Date();
        if (date.getMonth() > now.getMonth() || (date.getMonth() == now.getMonth() && date.getDate() > now.getDate())) {
          date.setFullYear(2014);
        } else {
          date.setFullYear(2015);
        }
      }
      console.log(date.toDateString());
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