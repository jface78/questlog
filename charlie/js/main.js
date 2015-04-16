var API_URL = 'api/v1/';
var TEMPLATE_URL = 'templates/';
var SERVICE_URL = 'php/services/';
var EVENT_LOADED = 'loaded';
var LOCAL_API_KEY = 1;
var isLoggedIn = false;

var currentQuestData;
var userID;
var userName;


function resetQuestVars() {
  currentQuestData = null;
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

function renderNewPostWindow() {
  var popupContainer = document.createElement('div');
  $(popupContainer).attr('title', 'new post');
  var div = document.createElement('div');
  $(div).css('height', '22px');
  $(div).text('posting as ');
  var select = document.createElement('select');
  $(select).addClass('posterSelect');
  if (userID == currentQuestData.gmID) {
    var option = document.createElement('option');
    $(option).val('0');
    $(option).text(currentQuestData.gmName + ' - GM');
    $(select).append(option);
  }
  for (var i=0; i < currentQuestData.players.length; i++) {
    if (currentQuestData.players[i].userID == userID) {
      var option = document.createElement('option');
      $(option).val(currentQuestData.players[i].characterID);
      $(option).text(currentQuestData.players[i].name);
      $(select).append(option);
    }
  }
  $(div).append(select);
  $(popupContainer).append(div);
  var textArea = document.createElement('textarea');
  $(textArea).addClass('postTextArea');
  $(popupContainer).append(textArea);
  var dialog = $(popupContainer).dialog({
    height: 400,
    width: 450,
    modal: false,
    buttons: {
      'Post': function() {
        newPost($(select).val(), $(textArea).val().replace('\n', '<br>'), dialog);
      },
      Cancel: function() {
        dialog.dialog('close');
      }
    },
    close: function() {
    }
  });
}

function renderEditWindow(button) {
  var popupContainer = document.createElement('div');
  $(popupContainer).attr('title', 'edit post #' + $(button).data('post-id'));
  var div = document.createElement('div');
  $(div).text('posting as ');
  var select = document.createElement('select');
  $(select).addClass('posterSelect');
  if (userID == currentQuestData.gmID) {
    var option = document.createElement('option');
    $(option).val('0');
    $(option).text(currentQuestData.gmName + ' - GM');
    if ($(button).data('character-id') == 'GM') {
        $(option).prop('selected', true);
      }
    $(select).append(option);
  }
  for (var i=0; i < currentQuestData.players.length; i++) {
    if (currentQuestData.players[i].userID == userID) {
      var option = document.createElement('option');
      $(option).val(currentQuestData.players[i].characterID);
      $(option).text(currentQuestData.players[i].name);
      if ($(button).data('character-id') == currentQuestData.players[i].characterID) {
        $(option).prop('selected', true);
      }
      $(select).append(option);
    }
  }
  $(div).append(select);
  $(popupContainer).append(div);
  var textArea = document.createElement('textarea');
  $(textArea).addClass('postTextArea');
  var text = $(button).parent().parent().parent().find('.postBody').html();
  text = convertHTMLToBB(text);
  text = santizeTextForTextarea(text);
  //text = $('<div>').html(text).text();
  $(textArea).val(text);
  $(popupContainer).append(textArea);
  var dialog = $(popupContainer).dialog({
    height: 400,
    width: 450,
    modal: false,
    buttons: {
      'Edit': function() {
        editPost($(button).data('post-id'), $(button).data('character-id'), $(textArea).val(), dialog);
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

function sanitizeTextForDB(text) {
  text.replace(/(?:\r\n|\r|\n)/g, '<br>')
  return text.replace(/\//g, '|');
}

function santizeTextForTextarea(text) {
  return text.replace(/<br\s*[\/]?>/gi, '\n');
}

function newPost(characterID, postText, dialog) {
  postText = sanitizeTextForDB(postText);
  $.ajax({
    url: API_URL + 'POSTS/QID/' + currentQuestData.questID + '/CID/' + characterID + '/BODY/' + postText,
    method: 'POST',
    data: {apiKey: LOCAL_API_KEY},
    dataType: 'json',
    statusCode: {
      200: function(response) {
        dialog.dialog('close');
        getPostsByPage();
      }
    }
  });
}

function editPost(postID, characterID, postText, dialog) {
  dbText = sanitizeTextForDB(postText);
  $.ajax({
    url: API_URL + 'POSTS/PID/' + postID + '/CID/' + characterID + '/BODY/' + dbText + '?apiKey=' + LOCAL_API_KEY,
    method: 'PUT',
    dataType: 'json',
    statusCode: {
      200: function(response) {
        dialog.dialog('close');
        var parent = $('#post_' + postID).find('.floatLeft');
        $('#post_' + postID).find('.postBody').html(postText);
        addEditedHover(parent, formatDate(new Date(response.editedDate).getTime()/1000));
      }
    }
  });
}

function deletePost(postID) {
  $.ajax({
    url: API_URL + 'POSTS/PID/' + postID + '?apiKey=' + LOCAL_API_KEY,
    method: 'DELETE',
    dataType: 'json',
    statusCode: {
      200: function() {
        /*
        $('#post_' + postID).remove();
        $('.postBody').each(function(index, item) {
          $(item).removeClass('even odd');
          if (index % 2 == 0) {
            $(item).addClass('even');
          } else {
            $(item).addClass('odd');
          }
        });*/
        getPostsByPage();
      }
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
  var buttonStartIndex = currentQuestData.page - 2;
  if (buttonStartIndex >= currentQuestData.pageCount) {
    buttonStartIndex = currentQuestData.page - 4;
  }
  if (buttonStartIndex < 1) {
    buttonStartIndex = 1;
  }
  if (currentQuestData.pageCount - buttonStartIndex < 4 && currentQuestData.pageCount - 4 > 1) {
    buttonStartIndex = currentQuestData.pageCount - 4;
  }
  function addClickEvent(btn) {
    $(btn).click(function(event) {
      var page;
      if ($(btn).text() == '<') {
        currentQuestData.page--;
      } else if ($(btn).text() == '>') {
        currentQuestData.page++;
      }
      else if ($(btn).text() == '<<') {
        currentQuestData.page = 1;
      } else if ($(btn).text() == '>>') {
        currentQuestData.page  = currentQuestData.pageCount;
      } else {
        currentQuestData.page  = parseInt($(btn).text());
      }
      getPostsByPage();
    });
  }
  var btn = document.createElement('button');
  $(btn).addClass('questNavButton');
  $(btn).text('<<');
  if (currentQuestData.page > 1) {
    addClickEvent(btn);
  } else {
    $(btn).prop('disabled', true);
    $(btn).addClass('disabled');
  }
  $('.questNavigation').append(btn);
  btn = document.createElement('button');
  $(btn).addClass('questNavButton');
  $(btn).text('<');
  if (currentQuestData.page > 1) {
    addClickEvent(btn);
  } else {
    $(btn).prop('disabled', true);
    $(btn).addClass('disabled');
  }
  $('.questNavigation').append(btn);
  for (var i=buttonStartIndex; i < buttonStartIndex+5 && i<currentQuestData.pageCount+1; i++) {
    btn = document.createElement('button');
    $(btn).addClass('questNavButton');
    $(btn).text(i);
    if (i==currentQuestData.page) {
      $(btn).prop('disabled', true);
      $(btn).css('text-decoration', 'underline');
      $(btn).addClass('disabled');
    }
    addClickEvent(btn);
    $('.questNavigation').append(btn);
  }
  btn = document.createElement('button');
  $(btn).addClass('questNavButton');
  $(btn).text('>');
  if (currentQuestData.page < currentQuestData.pageCount) {
    addClickEvent(btn);
  } else {
    $(btn).prop('disabled', true);
    $(btn).addClass('disabled');
  }
  $('.questNavigation').append(btn);
  btn = document.createElement('button');
  $(btn).addClass('questNavButton');
  $(btn).text('>>');
  if (currentQuestData.page < currentQuestData.pageCount) {
    addClickEvent(btn);
  } else {
    $(btn).prop('disabled', true);
    $(btn).addClass('disabled');
  }
  $('.questNavigation').append(btn);
}

function addEditedHover(parent, text) {
  var hoverSpan = document.createElement('span');
  $(hoverSpan).text('*');
  $(hoverSpan).addClass('editedAsterisk');
  $(parent).find('.editedAsterisk').remove();
  var remainder = $(parent).html().split('Posted')[1];
  $(parent).text('Posted');
  $(parent).append(hoverSpan);
  $(parent).append(remainder);
  $(hoverSpan).qtip({
    content: {
      text: 'Edited ' + text
    },
    position: {
      my: 'bottom left',
      at: 'top right',
      target: $(hoverSpan)
    },
    style: {
      classes: 'qtip-dark qtip-shadow qtip-rounded'
    }
  });
}

function renderPostBubble(postObject, index, prepend) {
  var div = document.createElement('div');
  $(div).attr('id', 'post_' + postObject.postID);
  var header = document.createElement('header');
  var span = document.createElement('span');
  $(span).addClass('floatLeft');
  $(span).text('#' + postObject.postID);
  $(span).append('&nbsp;&nbsp;');
  $(span).append('Posted');
  if (postObject.edited != 'never') {
    addEditedHover(span, formatDate(parseInt(postObject.edited)));
  }
  $(span).append('&nbsp;');
  var date = formatDate(parseInt(postObject.date));
  $(span).append(date + '&nbsp;by&nbsp;');
  var a = document.createElement('a');
  $(a).addClass('characterNameLink');
  $(a).text(postObject.character);
	if (postObject.characterID == 'GM') {
		$(a).append(' - GM');
	}
  $(span).append(a);
  $(header).append(span);
  if (postObject.editable == 'true') {
    span = document.createElement('span');
    $(span).addClass('floatRight');
    var img = document.createElement('img');
    $(img).addClass('pointer editPostBtn');
    $(img).attr('alt', 'edit post');
    $(img).attr('title', 'edit post');
    $(img).attr('src', 'img/icon.edit_dark.gif');
    $(img).attr('data-post-id', postObject.postID);
    $(img).attr('data-character-id', postObject.characterID);
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
    $(img).attr('data-post-id', postObject.postID);
    $(img).click(function(event) {
      event.preventDefault();
      event.stopPropagation();
      renderDeletePostWindow(this);
    });
    $(span).append(img);
    $(header).append(span);
  }
  $(header).addClass('postHeader');
  $(div).append(header);
  var section = document.createElement('section');
  $(section).addClass('postBody');
  if (index % 2 == 0) {
    $(section).addClass('even');
  } else {
    $(section).addClass('odd');
  }
  $(section).html(postObject.text);
  $(div).append(section);
  if (prepend) {
    $('#questContent').prepend(div);
  } else {
    $('#questContent').append(div);
  }
}

function resetPostsView() {
  $('.postBody').removeClass('even odd');
  $('.postBody').each(function(index, item) {
    if (index % 2 == 0) {
      $(item).addClass('even');
    } else {
      $(item).addClass('odd');
    }
  });
}

function getPostsByPage() {
  $('#questContent').fadeOut('fast', function() {
    $('#questContent').empty();
    var service = API_URL + 'posts/QID/' + currentQuestData.questID;
    if (currentQuestData.limit) {
      service += '/limit/' + currentQuestData.limit;
    } else {
      service += '/limit/50';
    }
    if (currentQuestData.page) {
      service += '/page/' + currentQuestData.page;
    } else {
      service += '/page/1';
    }
    if (currentQuestData.sort) {
      service += '/order/' + currentQuestData.sort;
    } else {
      service += '/order/DESC';
    }
    $.ajax({
      url: service,
      method: 'GET',
      data: {apiKey: LOCAL_API_KEY},
      dataType: 'json',
      statusCode: {
        200: function(response) {
          for (var i=0; i < response.posts.length; i++) {
            renderPostBubble(response.posts[i], i);
          }
          currentQuestData.page = parseInt(response.currentPage);
          currentQuestData.limit = parseInt(response.delimiter);
          currentQuestData.pageCount = parseInt(response.pageCount);
          currentQuestData.sort = response.order;
          addPagination();
          $('#questContent').fadeIn('fast');
        }
      }
    });
  });
}

function loadQuest(questID) {
  $.ajax({
    url: TEMPLATE_URL + 'quest.html',
    success: function(template) {
      $('#questlogLeft').html(template);
      $.ajax({
        url: API_URL + 'quest/' + questID,
        data: {apiKey: LOCAL_API_KEY},
        success: function(data) {
          currentQuestData = data;
          var buttons = $('.questMenu');
          $(buttons[0]).click(function() {
            $('#mainContent').fadeOut('normal', function() {
              resetQuestVars();
              loadQuestListings();
            });
          });
          $(buttons[2]).click(function() {
            if (currentQuestData.sort == 'ASC') {
              currentQuestData.sort = 'DESC';
            } else {
              currentQuestData.sort = 'ASC';
            }
            getPostsByPage();
          });
          $(buttons[3]).click(function() {
            getPostsByPage(); 
          });
          var isQuestMember = false;
          for (var i=0; i < currentQuestData.players.length; i++) {
            if (userID == parseInt(currentQuestData.players[i].userID)) {
              isQuestMember = true;
            }
          }
          if (!isQuestMember && parseInt(currentQuestData.gmID) == userID) {
            isQuestMember = true;
          }
          if (isQuestMember) {
            $(buttons[4]).click(function() {
              renderNewPostWindow();
            });
          } else {
            $(buttons[4]).remove();
          }
          getPostsByPage();
          $('#questlogLeft').fadeIn('fast');
        }
      });
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
  $('#generateNPCBtn').click(function(event) {
    renderRandomNPC();
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
            if (response.quests.gmQuests.length) {
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
                var postDate = new Date(parseInt(response.quests.gmQuests[i].lastPostDate)*1000);
                $(td).text(postDate.toDateString() + ' at ' + postDate.toLocaleTimeString());
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
                td = document.createElement('td');
                $(td).text(parseInt(response.quests.gmQuests[i].lastPostDate));
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
                  { 'visible': false, 'targets':[5,6]},
                  { "iDataSort": 5, "targets": [ 0 ],
                    "iDataSort": 6, "targets": [3]
                  }
                ],
                "order": [[ 6, "desc" ]],
                'initComplete':function() {
                  $('#gmQuests_filter').css('text-align', 'right');
                  $('#gmQuests_filter input').addClass('field');
                }
              });
            } else {
              var span = document.createElement('span');
              $(span).text('You have no active GM quests.');
              $('#gmQuests').replaceWith(span);
            }

            if (response.quests.playerQuests.length) {
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
                var postDate = new Date(parseInt(response.quests.playerQuests[i].lastPostDate)*1000);
                $(td).text(postDate.toDateString() + ' at ' + postDate.toLocaleTimeString());
                $(tr).append(td);
                td = document.createElement('td');
                $(td).text(response.quests.playerQuests[i].sortable);
                $(tr).append(td);
                td = document.createElement('td');
                $(td).text(parseInt(response.quests.playerQuests[i].lastPostDate));
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
                  { 'visible': false, 'targets':[5,6]},
                  { "iDataSort": 5, "targets": [0],
                    "iDataSort": 6, "targets": [4]
                  }
                ],
                "order": [[ 6, "desc" ]],
                'initComplete':function() {
                  $('#playerQuests_filter').css('text-align', 'right');
                  $('#playerQuests_filter input').addClass('field');
                }
              });
            } else {
              var span = document.createElement('span');
              $(span).text('You have no active player quests.');
              $('#playerQuests').replaceWith(span);
            }

            if (response.quests.otherQuests.length) {
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
                var postDate = new Date(parseInt(response.quests.otherQuests[i].lastPostDate)*1000);
                $(td).text(postDate.toDateString() + ' at ' + postDate.toLocaleTimeString());
                $(tr).append(td);
                td = document.createElement('td');
                $(td).text(response.quests.otherQuests[i].sortable);
                $(tr).append(td);
                td = document.createElement('td');
                $(td).text(parseInt(response.quests.otherQuests[i].lastPostDate));
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
                  { 'visible': false, 'targets':[5,6]},
                  { "iDataSort": 5, "targets": [ 0 ],
                    "iDataSort": 6, "targets": [ 4 ]}
                ],
                "order": [[ 6, "desc" ]],
                'initComplete':function() {
                  $('#otherQuests_filter').css('text-align', 'right');
                  $('#otherQuests_filter input').addClass('field');
                }
              });
            } else {
              var span = document.createElement('span');
              $(span).text('There are no active other quests.');
              $('#otherQuests').replaceWith(span);
            }
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
          console.log('pass? '  + $($(inputs)[3]).val().trim());
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

function updateUserDetails(dialogObject, email) {
  console.log('email: ' + email);
  $('.signupError').text('');
  $.ajax({
    url: API_URL + 'users/email/' + email + '?apiKey=' + LOCAL_API_KEY,
    method: 'PUT',
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
        updateUserDetails(dialog, $(emailInput).val());
      },
      'never mind': function() {
        dialog.dialog('close');
      }
    },
    close: function() {
    }
  });
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