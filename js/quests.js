function resetQuestVars() {
  currentQuestData = null;
}

function deleteQuest(questID, dialogObject) {
  $.ajax({
    url: API_URL + 'QUEST/QID/' + questID + '?apiKey=' + LOCAL_API_KEY,
    method: 'DELETE',
    dataType: 'json',
    statusCode: {
      200: function(response) {
        dialogObject.dialog('close');
        loadQuestListings();
      }
    }
  });  
}

function renderDeleteQuestWindow(questID, questName) {
  var popupContainer = document.createElement('div');
  $(popupContainer).attr('title', 'Sure about that?');
  $(popupContainer).append('Delete quest ' + questName + '?');
  var dialog = $(popupContainer).dialog({
    height: 150,
    width: 300,
    modal: false,
    buttons: {
      'make it so': function() {
        deleteQuest(questID, dialog);
      },
      'abort! abort!': function() {
        dialog.dialog( "close" );
      }
    },
    close: function() {
    }
  });   
}

function renderBackstoryWindow(questID, questName) {
  $.ajax({
    url: API_URL + 'quest/' + questID,
    data: {apiKey: LOCAL_API_KEY},
    dataType: 'json',
    statusCode: {
      200: function(response) {
        var popupContainer = document.createElement('div');
        $(popupContainer).attr('title', questName + ' - Backstory');
        if (response.backstory) {
          $(popupContainer).append(response.backstory);
        } else {
          $(popupContainer).append('No description.');
        }
        $(popupContainer).dialog({
          height: 400,
          width: '50%',
          modal: false
        });
      }
    }
  });
}

function validateAndUpdateQuest(div, dialogObject, questID) {
  $(div).find('#newQuestError').text('');
  if (!$(div).find('#questName').val().trim().length || !$(div).find('#backstory').val().trim().length) {
    $(div).find('#newQuestError').text('You need a quest title and some backstory details at least.');
    return;
  }
  if (!$(div).find('#questName').val().trim().length > MAX_QUEST_NAME_LENGTH) {
    $(div).find('#newQuestError').text('The quest name cannot exceed ' + MAX_QUEST_NAME_LENGTH + ' characters.');
    return;
  }
  var playersArray = [];
  $(div).find('.addedPlayerBubble').each(function(index, item) {
    if ($(item).data('characterid')) {
      playersArray.push({'character' : $(item).data('characterid')});
    }
  });
  $.ajax({
    url: API_URL + 'quest?apiKey=' + LOCAL_API_KEY,
    method: 'PUT',
    data: {qid: questID, name:$(div).find('#questName').val().trim(), backstory: $(div).find('#backstory').val().trim(),
           privacy: $(div).find('#privacy').val(), players: JSON.stringify(playersArray)
    },
    dataType: 'json',
    statusCode: {
      409: function() {
        $(div).find('#newQuestError').text('A quest by that name already exists.');
      },
      200: function() {
        loadQuestListings();
        dialogObject.dialog('close');
      }
    }
  });
}

function renderEditQuestWindow(questID, title) {
   var popupContainer = document.createElement('div');
  $(popupContainer).attr('title', 'Edit Quest - ' + ucwords(title));
  $.ajax({
    url: TEMPLATE_URL + 'newQuest.html',
    success: function(template) {
      $.ajax({
        url: API_URL + 'quest/' + questID,
        data: {apiKey: LOCAL_API_KEY},
        statusCode: {
          200: function(response) {
            $(popupContainer).append(template);
            $($(popupContainer).find('.halfLeft')[3]).css('visibility', 'hidden');
            $(popupContainer).find('#questName').val(response.title);
            $(popupContainer).find('#backstory').val(santizeTextForTextarea(response.backstory));
            $(popupContainer).find('option').each(function(index, item) {
              if ($(item).val() == response.privacy) {
                $(item).prop('selected', true);
              }
            });
            for (var i=0; i < response.players.length; i++) {
              if (!$(popupContainer).find('#addedPlayers *[data-characterid="' + response.players[i].characterID + '"]').length) {
                addPlayerBubble(popupContainer, response.players[i].characterID, response.players[i].name + ' (' + response.players[i].userName + ')');
              }
            }
            $(popupContainer).find('#playerList').keyup(function(event) {
              if ($(event.target).val().length >= 2) {
                $.ajax({
                  url: API_URL + 'search/players/' + $(event.target).val().trim() + '?apiKey=1',
                  dataType: 'json',
                  success: function(data) {
                    var span = document.createElement('span');
                    var playerID, playerName;
                    for (var i=0; i < data.players.length; i++) {
                      var subDiv = document.createElement('div');
                      $(subDiv).data('id', data.players[i].characterID);
                      $(subDiv).data('name', data.players[i].character + ' (' + data.players[i].userName + ')');
                      $(subDiv).append($(subDiv).data('name'));
                      $(subDiv).attr('data-characterid', $(subDiv).data('id'));
                      $(subDiv).addClass('playerListItem');
                      $(subDiv).click(function(e) {
                        if (!$(popupContainer).find('#addedPlayers *[data-characterid="' + $(e.target).data('id') + '"]').length) {
                          addPlayerBubble(popupContainer, $(e.target).data('id'), $(e.target).data('name'));
                          $(event.target).qtip('destroy');
                        }
                      });
                      $(span).append(subDiv);
                    }
                    if (data.players.length) {
                      $(event.target).qtip({
                        content: {
                          text: span
                        },
                        position: {
                          my: 'top left',
                          at: 'bottom left'
                        },
                        style: {
                          classes: 'qtip-dark qtip-shadow qtip-rounded'
                        },
                        hide: {
                          fixed:true,
                          delay:500
                        },
                        show: {
                          effect: false
                        }
                      });
                      $(event.target).trigger('mouseenter');
                    }
                  }
                });
              }
            });
            var dialog = $(popupContainer).dialog({
              height: $(window).height() * 0.8,
              width: '80%',
              modal: true,
              buttons: {
                'update': function() {
                  validateAndUpdateQuest(popupContainer, dialog, response.questID);
                },
                'never mind': function() {
                  dialog.dialog('close');
                }
              },
              close: function() {
              },
            });
          }
        }
      });
    }
  });
}

function validateAndCreateQuest(div, dialogObject) {
  $(div).find('#newQuestError').text('');
  if (!$(div).find('#questName').val().trim().length || !$(div).find('#backstory').val().trim().length) {
    $(div).find('#newQuestError').text('You need a quest title and some backstory details at least.');
    return;
  }
  if (!$(div).find('#questName').val().trim().length > MAX_QUEST_NAME_LENGTH) {
    $(div).find('#newQuestError').text('The quest name cannot exceed ' + MAX_QUEST_NAME_LENGTH + ' characters.');
    return;
  }
  var playersArray = [];
  $(div).find('.addedPlayerBubble').each(function(index, item) {
    if ($(item).data('characterid')) {
      playersArray.push({'character' : $(item).data('characterid')});
    }
  });
  $.ajax({
    url: API_URL + 'quest/',
    method: 'POST',
    data: {apiKey: LOCAL_API_KEY, name:$(div).find('#questName').val().trim(), backstory: $(div).find('#backstory').val().trim(),
           firstPost: $(div).find('#firstPost').val(), privacy: $(div).find('#privacy').val(), players: JSON.stringify(playersArray)
    },
    dataType: 'json',
    statusCode: {
      409: function() {
        $(div).find('#newQuestError').text('A quest by that name already exists.');
      },
      200: function() {
        loadQuestListings();
        dialogObject.dialog('close');
      }
    }
  });
}

function renderNewQuest() {
  var popupContainer = document.createElement('div');
  $(popupContainer).attr('title', 'New Quest');
  $.ajax({
    url: TEMPLATE_URL + 'newQuest.html',
    success: function(template) {
      $(popupContainer).append(template);
      $(popupContainer).find('#playerList').keyup(function(event) {
        if ($(event.target).val().length >= 2) {
          $.ajax({
            url: API_URL + 'search/players/' + $(event.target).val().trim() + '?apiKey=1',
            dataType: 'json',
            success: function(data) {
              var span = document.createElement('span');
              var playerName, playerID;
              for (var i=0; i < data.players.length; i++) {
                var subDiv = document.createElement('div');
                $(subDiv).data('id', data.players[i].characterID);
                $(subDiv).data('name', data.players[i].character + ' (' + data.players[i].userName + ')');
                $(subDiv).append($(subDiv).data('name'));
                $(subDiv).attr('data-characterid', $(subDiv).data('id'));
                $(subDiv).addClass('playerListItem');
                $(subDiv).click(function(e) {
                  if (!$(popupContainer).find('#addedPlayers *[data-characterid="' + $(e.target).data('id') + '"]').length) {
                    addPlayerBubble(popupContainer, $(e.target).data('id'), $(e.target).data('name'));
                    $(event.target).qtip('destroy');
                  }
                });
                $(span).append(subDiv);
              }
              if (data.players.length) {
                $(event.target).qtip({
                  content: {
                    text: span
                  },
                  position: {
                    my: 'top left',
                    at: 'bottom left'
                  },
                  style: {
                    classes: 'qtip-dark qtip-shadow qtip-rounded'
                  },
                  hide: {
                    fixed:true,
                    delay:500
                  },
                  show: {
                    effect: false
                  }
                });
                $(event.target).trigger('mouseenter');
              }
            }
          });
        }
      });
      var dialog = $(popupContainer).dialog({
        height: $(window).height() * 0.8,
        width: '80%',
        modal: true,
        buttons: {
          'create': function() {
            validateAndCreateQuest(popupContainer, dialog);
          },
          'never mind': function() {
            dialog.dialog('close');
          }
        },
        close: function() {
        },
      });
    }
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
        statusCode: {
          200: function(data) {
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
            if (currentQuestData.hasOwnProperty('players')) {
              for (var i=0; i < currentQuestData.players.length; i++) {
                if (userID == parseInt(currentQuestData.players[i].userID)) {
                  isQuestMember = true;
                }
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
          },
          404: function() {
            
          }
        }
      });
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
            if (response.quests.gmQuests.length) {
              for (var i=0; i < response.quests.gmQuests.length; i++) {
                var tr = document.createElement('tr');
                $(tr).attr('data-questid', response.quests.gmQuests[i].questID);
                $(tr).attr('data-questtitle', response.quests.gmQuests[i].title);
                $(tr).attr('title', response.quests.gmQuests[i].title);
                var td = document.createElement('td');
                $(td).text(response.quests.gmQuests[i].title);
                $(td).addClass('log-left');
                $(td).click(function() {
                  var thisTD = this;
                  $('#questlogLeft').fadeOut('fast', function() {
                    loadQuest($(thisTD).closest('tr').data('questid'));
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
                if (response.quests.gmQuests[i].lastPostDate == 'never') {
                  $(td).text(response.quests.gmQuests[i].lastPostDate);
                } else {
                  var postDate = new Date(parseInt(response.quests.gmQuests[i].lastPostDate)*1000);
                  $(td).text(postDate.toDateString() + ' at ' + postDate.toLocaleTimeString());
                }
                $(tr).append(td);
                td = document.createElement('td');
                $(td).addClass('log-cell');
                var btn = document.createElement('button');
                $(btn).addClass('smallButton');
                $(btn).css('margin-right', '5px');
                $(btn).text('backstory');
                $(btn).click(function() {
                  renderBackstoryWindow($(this).closest('tr').data('questid'), $(this).closest('tr').data('questtitle'));
                });
                $(td).append(btn);
                btn = document.createElement('button');
                $(btn).addClass('smallButton');
                $(btn).css('margin-right', '5px');
                $(btn).text('edit');
                $(btn).click(function() {
                  renderEditQuestWindow($(this).closest('tr').data('questid'), $(this).closest('tr').data('questtitle'));
                });
                $(td).append(btn);
                btn = document.createElement('button');
                $(btn).addClass('smallButton');
                $(btn).text('delete');
                $(btn).click(function() {
                  renderDeleteQuestWindow($(this).closest('tr').data('questid'), $(this).closest('tr').data('questtitle'));
                });
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
                $(tr).attr('data-questid', response.quests.playerQuests[i].questID);
                $(tr).attr('data-questtitle', response.quests.playerQuests[i].title);
                $(tr).attr('title', response.quests.playerQuests[i].title);
                var td = document.createElement('td');
                $(td).text(response.quests.playerQuests[i].title);
                $(td).addClass('log-left');
                $(td).attr('data-quest-id', response.quests.playerQuests[i].questID);
                $(td).click(function() {
                  var thisTD = this;
                  $('#questlogLeft').fadeOut('fast', function() {
                    loadQuest($(thisTD).closest('tr').data('questid'));
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
                $(td).addClass('log-cell alignCenter');
                btn = document.createElement('button');
                $(btn).addClass('smallButton');
                $(btn).text('backstory');
                $(btn).click(function() {
                  renderBackstoryWindow($(this).closest('tr').data('questid'), $(this).closest('tr').data('questtitle'));
                });
                $(td).append(btn);
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
                  { 'visible': false, 'targets':[6,7]},
                  { "iDataSort": 6, "targets": [0],
                    "iDataSort": 7, "targets": [4]
                  }
                ],
                "order": [[ 7, "desc" ]],
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
                $(tr).attr('data-questid', response.quests.otherQuests[i].questID);
                $(tr).attr('data-questtitle', response.quests.otherQuests[i].title);
                $(tr).attr('title', response.quests.otherQuests[i].title);
                var td = document.createElement('td');
                $(td).text(response.quests.otherQuests[i].title);
                $(td).addClass('log-left');
                $(td).attr('data-quest-id', response.quests.otherQuests[i].questID);
                $(td).click(function() {
                  var thisTD = this;
                  $('#questlogLeft').fadeOut('fast', function() {
                    loadQuest($(thisTD).closest('tr').data('questid'));
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
                 $(td).addClass('log-cell alignCenter');
                btn = document.createElement('button');
                $(btn).text('backstory');
                $(btn).addClass('smallButton');
                $(btn).click(function() {
                  renderBackstoryWindow($(this).closest('tr').data('questid'), $(this).closest('tr').data('questtitle'));
                });
                $(td).append(btn);
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
                  { 'visible': false, 'targets':[6,7]},
                  { "iDataSort": 6, "targets": [ 0 ],
                    "iDataSort": 7, "targets": [ 4 ]}
                ],
                "order": [[ 7, "desc" ]],
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