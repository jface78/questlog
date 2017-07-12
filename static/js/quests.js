function fetchQuestInfo(box, id) {
  $.ajax({
    url: SERVICE_URL + 'quest/' + id + '/info',
    dataType: 'json',
    statusCode: {
      200: function(data) {
        box.setTitle(data.title + ' &mdash; Preface');
        box.setContent(data.description);
      },
      404: function() {
        $(box.foreground).find('content').text('No description provided.');
      }
    }
  });
}

function showQuestInfo(button) {
  var id = $(button).parent().parent().data('qid');
  var box = new QuestlogOverlay(fetchQuestInfo, id);
  $(box).on(EVENT_LOADED, function() {
    fetchQuestInfo(box, id);
  });
  box.setup();
}

function scrollQuest(qid) {
  if ($(window).scrollTop() + $(window).height() == $(document).height()) {
    console.log('scroll');
    currentQuestPage++;
    fetchAndRenderPosts(qid, (currentQuestPage * DEFAULT_PAGE_LENGTH), DEFAULT_PAGE_LENGTH);
  }
}

function fetchAndRenderQuests() {
  if (userID) {
    var queuedRows = [];
    $.get(TEMPLATE_URL + 'quests_loggedin.html', function(template) {
      $('main').html(template);
      drawPreloader();
      $.ajax({
        url: SERVICE_URL + 'quests',
        dataType: 'json',
        statusCode: {
          200: function(data) {
            var totalGM = 0, totalPlayer = 0, totalOther = 0;
            $.each(data, function(index, item) {
              if (item.type == 'gm') {
                totalGM++;
              }
              if (item.type == 'player') {
                totalPlayer++;
              }
              if (item.type == 'other') {
                totalOther++;
              }
              var tr = $('<tr data-qid="' + item.qid + '"></tr>');
              if (index % 2 === 0) {
                $(tr).addClass('even');
              } else {
                $(tr).addClass('odd');
              }
              $(tr).append('<td><a href="" class="questClick">' + item.name + '</a></td><td>' + item.count + '</td>');
              $(tr).append('<td>' + item.last + ' on ' + formatDate(item.timestamp) +  '</td><td><a href="#">' + item.gmname + '</a></td>');
              var playersStr = '';
              $(item.players).each(function(playerIndex, playerItem) {
                playersStr += '<a class="character" data-cid="' + playerItem.cid + '" href="#">' + playerItem.name + '</a>';
                if (playerIndex < item.players.length-1) {
                  playersStr += ', ';
                }
              });
              $(tr).append('<td>' + playersStr + '</td>');
              var controls = $('<td style="text-align:center;"><i class="icon fa fa-clone" title="preface"></i></td>');
              $(controls).css('width', '25px');
              if (item.type == 'gm') {
                $(controls).css('width', '55px');
                $(controls).append('<a class="icon edit" href=""><img src="/img/icon.edit_dark.gif" alt="edit" title="edit"></a><a class="icon delete" href=""><img src="/img/icon.delete_dark.gif" alt="delete" title="delete"></a>');
              }
              $(tr).append(controls);
              $(controls).find('.fa-clone').click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                showQuestInfo(this);
              });
              $(controls).find('.delete').click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                promptToDeleteQuest(item.qid, item.name)
              });
              $('#' + item.type + 'Quests tbody').append(tr);
              queuedRows.push(tr);
            });
            if (!totalGM) {
              $('#gmQuests tbody').append('<tr class="odd" style="display:table-row;"><td colspan="6">None</td></tr>');
            }
            if (!totalPlayer) {
              $('#playerQuests tbody').append('<tr class="odd" style="display:table-row;"><td colspan="6">None</td></tr>');
            }
            if (!totalOther) {
              $('#otherQuests tbody').append('<tr class="odd" style="display:table-row;"><td colspan="6">None</td></tr>');
            }
            $('.questsTable').find('.questClick').each(function(index, item) {
              $(item).click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                history.pushState({}, 'quest', '/quest/' + $(this).closest('tr').data('qid') + '/');
              });
            });
            $('.questsTable').find('.character').each(function(index, item) {
              $(item).click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                showCharacterInfo($(this).attr('data-cid'));
              });
            });
            clearPreloader();
            fadeInRows(queuedRows);
          }, 404: function(error) {
            
          }
        }
      });
    });
  } else {
    var queuedRows = [];
    $.get(TEMPLATE_URL + 'quests.html', function(template) {
      $('main > .leftContent').html(template);
      drawPreloader();
      $.ajax({
        url: SERVICE_URL + 'quests',
        dataType: 'json',
        statusCode: {
          200: function(data) {
            $('main').html(template);
            $.each(data, function(index, item) {
              var tr = $('<tr data-qid="' + item.qid + '"></tr>');
              if (index % 2 === 0) {
                $(tr).addClass('even');
              } else {
                $(tr).addClass('odd');
              }
              $(tr).append('<td><a href="" class="questClick">' + item.name + '</a></td><td>' + item.count + '</td>');
              $(tr).append('<td>' + item.last + ' on ' + formatDate(item.timestamp) +  '</td><td><a href="#">' + item.gmname + '</a></td>');
              var playersStr = '';
              $(item.players).each(function(playerIndex, playerItem) {
                playersStr += '<a class="character" data-cid="' + playerItem.cid + '" href="#">' + playerItem.name + '</a>';
                if (playerIndex < item.players.length-1) {
                  playersStr += ', ';
                }
              });
              $(tr).append('<td>' + playersStr + '</td>');
              var controls = $('<td style="text-align:center;"><i class="icon fa fa-clone" title="preface"></i></td>');
              if (userID) {
                $(controls).append('<a class="icon" href=""><img src="/img/icon.edit_dark.gif" alt="edit" title="edit"></a><a class="icon" href=""><img src="/img/icon.delete_dark.gif" alt="delete" title="delete"></a>');
              }
              $(tr).append(controls);
              $(controls).find('.fa-clone').click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                showQuestInfo(this);
              });
              $('#allQuests tbody').append(tr);
              queuedRows.push(tr);
            });
            $('.questsTable').find('.questClick').each(function(index, item) {
              $(item).click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                history.pushState({}, 'quest', '/quest/' + $(this).closest('tr').data('qid') + '/');
              });
            });
            $('.questsTable').find('.character').each(function(index, item) {
              $(item).click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                showCharacterInfo($(this).attr('data-cid'));
              });
            });
            clearPreloader();
            if (queuedRows.length) {
              fadeInRows(queuedRows);
            }
          }
        }
      });
    });
  }
}

function fetchAndRenderQuestPermissions(qid) {
  $.ajax({
    url: SERVICE_URL + 'quest/' + qid + '/permissions',
    dataType: 'json',
    statusCode: {
      200: function(data) {
        if (parseInt(data.gmid) == userID) {
          drawQuestParticipantControls();
          return;
        }
        $(data.members).each(function(index, item) {
          console.log(userID);
          if (parseInt(item.uid) == userID) {
            drawQuestParticipantControls();
            return;
          }
        });
      }
    }
  });
}


function drawQuestParticipantControls() {
  $('.postNav ul').append('<li id="createPostBtn">|&nbsp;&nbsp;&nbsp;post&nbsp;&nbsp;&nbsp;</li>');
  $('#createPostBtn').click(function(event) {
    event.preventDefault();
    event.stopPropagation();
    renderPostWindow(window.location.pathname.split('/')[2]);
  });
}

function promptToDeleteQuest(qid, qname) {
  var box = new QuestlogOverlay();
  $(box).on(EVENT_LOADED, function() {
    box.setTitle('Delete Quest &laquo;' + qname + '?&raquo;');
    box.setContent('Are you sure?');
  });
  box.setup(function(){ return deleteQuest(qid);});
}

function deleteQuest(qid) {
  $.ajax({
    url: SERVICE_URL + '/quest/' + qid + '/delete',
    type: 'DELETE',
    success: function(result) {
      history.pushState({}, 'quest', '/');
    }
  });
}