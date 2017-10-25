function fetchCharacterInfo(box, id) {
  $.ajax({
    url: SERVICE_URL + 'character/' + id,
    dataType: 'json',
    statusCode: {
      200: function(data) {
        box.setTitle(data.name);
        $.get(TEMPLATE_URL + 'character.html', [], function(template) {
          box.setContent(template);
          $('#character').find('#title').html(data.title);
          $('#character').find('#preface').html(data.preface);
          $('#character').find('#profile').html(data.profile);
          $(data.quests).each(function(index, item) {
            $('#character').find('#quests').append('<a href="" data-qid="' + item.qid + '">' + item.name + '</a><br>');
          });
          $('#character').find('#quests a').each(function(index, item) {
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
        $(box.foreground).find('content').text('No description provided.');
      }
    }
  });
}

function showCharacterInfo(cid) {
  console.log('showing');
  var box = new QuestlogOverlay(fetchCharacterInfo, cid);
  $(box).on(EVENT_LOADED, function() {
    console.log('loaded');
    fetchCharacterInfo(box, cid);
  });
  box.setup();
}