function fetchUserInfo(box, id) {
  $.ajax({
    url: SERVICE_URL + 'user/' + id,
    dataType: 'json',
    statusCode: {
      200: function(data) {
        box.setTitle(data.name);
        $.get(TEMPLATE_URL + 'user.html', [], function(template) {
          box.setContent(template);
          $('#user').find('#last').html(formatDate(data.last_login));
          $(data.gm_quests).each(function(index, item) {
            $('#user').find('#gm_quests').append('<a href="" data-qid="' + item.qid + '">' + item.name + '</a><br>');
          });
          $(data.character_quests).each(function(index, item) {
            $('#user').find('#char_quests').append('<a href="" data-qid="' + item.qid + '">' + item.name + '</a><br>');
          });
          $('#user').find('#quests a').each(function(index, item) {
            $(item).click(function(event) {
              event.preventDefault();
              event.stopPropagation();
              box.destroy();
              history.pushState({}, 'quest', '/quest/' + $(item).attr('data-qid') + '/');
            });
          });
        });
      },
      404: function() {
        
      }
    }
  });
}

function showUserInfo(uid) {
  var box = new QuestlogOverlay(fetchUserInfo, uid);
  $(box).on(EVENT_LOADED, function() {
    fetchUserInfo(box, uid);
  });
  box.setup();
}