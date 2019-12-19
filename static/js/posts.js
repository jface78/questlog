function renderEndPost() {
  $(window).off('scroll');
  var div = $('<div class="postBubble endPost"></div>');
  var content = $('<content>No more posts.</content>');
  $(div).append(content);
  var totalPosts = $('.postBubble').length;
  if ($($('.postBubble')[totalPosts-1]).hasClass('odd')) {
    $(div).addClass('even');
  } else {
    $(div).addClass('odd');
  }
  $('.posts section').append(div);
  $(div).fadeIn('fast');
}

function fetchAndRenderPosts(qid, start, length, order) {
  if (!start) {
    start = 0;
  }
  if (!length) {
    length = DEFAULT_PAGE_LENGTH;
  }
  if (!order) {
    order = currentPageOrder;
  }
  drawPreloader();
  
  $.ajax({
    url: SERVICE_URL + 'quest/' + qid + '?start=' + start + '&length=' + length + '&order=' + order,
    dataType: 'json',
    statusCode: {
      200: function(data) {
        var queuedPosts = [];
        if (!data) {
          renderEndPost();
        }
        $(data).each(function(index, item) {
          var div = $('<div class="postBubble" data-pid="' + item.pid + '"></div>');
          var a;
          if (!item.gmPost) {
            a = '<a class="character" data-cid="' + item.cid + '" href="#">' + item.poster + '</a>';
          } else {
            a = '<a class="gm" data-uid="' + item.uid + '" href="#">' + item.poster + '</a>'
          }
          var header = $('<header>#' + item.pid + '&nbsp;Posted on ' + formatDate(item.stamp) + ' by ' + a + '</header>');
          if (parseInt(item.uid) == userID) {
            var span = $('<span class="controls"></span>');
            $(span).append('<a class="icon edit" href=""><img src="/img/icon.edit_dark.gif" alt="edit" title="edit"></a>');
            $(span).append('<a class="icon delete" href=""><img src="/img/icon.delete_dark.gif" alt="delete" title="delete"></a>');
            $(header).append(span);
          }
          $(div).append(header);
          var content = $('<content>' + item.text + '</content>');
          $(div).append(content);
          if (index % 2 === 0) {
            $(div).addClass('even');
          } else {
            $(div).addClass('odd');
          }
          $('.posts section').append(div);
          queuedPosts.push(div);
          if (index == data.length-1 && (index+1) < DEFAULT_PAGE_LENGTH) {
            renderEndPost();
          }
          $(div).find('.edit').click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            renderPostWindow(item.qid, item.pid);
          });
          $(div).find('.delete').click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            prompt('Delete Post #' + item.pid, 'Are you sure?', function() {return deletePost(item.pid, item.qid)});
          });
          $(div).find('header > a').click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            if ($(this).hasClass('character')) {
              showCharacterInfo($(this).attr('data-cid'));
            } else {
              showUserInfo($(this).attr('data-uid'));
            }
          });
        });
        if (queuedPosts.length) {
          fadeInRows(queuedPosts);
        }
        clearPreloader();
        questPageLoading = false;
      }
    }
  });
}

function updatePost(data) {
  var bubble = $('.postBubble[data-pid="' + data.pid + '"]');
  $(bubble).find('header a.postedBy').text(data.poster);
  $(bubble).find('content').html(data.text);
}

function addPost(data) {
  var div = $('<div class="postBubble" data-pid="' + data.pid + '"></div>');
  var header = $('<header>#' + data.pid + '&nbsp;Posted on ' + formatDate(data.stamp) + ' by <a class="postedBy" href="">' + data.poster + '</a></header>');
  var span = $('<span class="controls"></span>');
  var a = $('<a class="icon edit" href=""><img src="/img/icon.edit_dark.gif" alt="edit" title="edit"></a>');
  $(span).append(a);
  a = $('<a class="icon delete" href=""><img src="/img/icon.delete_dark.gif" alt="delete" title="delete"></a>');
  $(span).append(a);
  $(header).append(span);
  $(div).append(header);
  $(div).append('<content>' + data.text + '</content>');
  if ($($('.posts').find('.postBubble')[0]).hasClass('even')) {
    $(div).addClass('odd');
  } else {
    $(div).addClass('even');
  }
  $('.posts section').prepend(div);
  $(div).find('.edit').click(function(event) {
    event.preventDefault();
    event.stopPropagation();
    renderPostWindow(data.qid, data.pid);
  });
  $(div).find('.delete').click(function(event) {
    event.preventDefault();
    event.stopPropagation();
    prompt('Delete Post #' + data.pid, 'Are you sure?', function() {return deletePost(data.pid, data.qid)});
  });
  fadeInRows([div]);
}

function deletePost(pid, qid) {
  console.log('delete');
  drawPreloader();
  $.ajax({
    url: SERVICE_URL + '/post/' + pid + '/delete',
    type: 'DELETE',
    success: function(result) {
      $('.postBubble[data-pid="' + pid + '"]').remove();
      clearPreloader();
    }, error: function() {
      clearPreloader();
      warn('Error', 'Unable to delete your post at this time.');
    }
  });
}

function saveOrEditPost(qid, cid, text, pid) {
  drawPreloader();
  text = sanitizeTextForDB(text)
  if (pid) {
    $.ajax({
      data: {qid:qid, pid:pid, cid:cid, uid: userID, text:text}, 
      url: SERVICE_URL + 'post/' + pid + '/edit',
      type: 'PUT',
      dataType: 'json',
      success: function(result) {
        updatePost(result)
        clearPreloader();
      }, error: function() {
        clearPreloader();
        warn('Error', 'Unable to edit your post at this time.');
      }
    });
  } else {
    $.ajax({
      data: {qid:qid, cid:cid, uid: userID, text:text}, 
      url: SERVICE_URL + 'quest/' + qid + '/post',
      type: 'POST',
      dataType: 'json',
      success: function(result) {
        addPost(result)
        clearPreloader();
      }, error: function() {
        clearPreloader();
        warn('Error', 'Unable to create your post at this time.');
      }
    });
  }
}

function renderPostWindow(qid, pid) {
  drawPreloader();
  var text =  $('.postBubble[data-pid="' + pid + '"]').find('content').html();
  $.getJSON(SERVICE_URL + 'post/' + qid + '/permissions', [], function(data) {
    var box = new QuestlogOverlay();
    $(box).on(EVENT_LOADED, function() {
      $.get(TEMPLATE_URL + 'editPost.html', [], function(template) {
        var html = $(template);
        if (data.gm) {
          $(html).find('#postAs').append('<option value="0">' + username + ' - GM</option>');
        }
        $(data.characters).each(function(index, item) {
          $(html).find('#postAs').append('<option value="' + item.cid + '">' + item.name + '</option>');
        });
        if (pid) {
          box.setTitle('Editing post #' + pid);
          text = sanitizeTextForUI(text);
          $(html).find('textarea').val(text);
        } else {
          box.setTitle('New post');
        }
        $(html).find('button').click(function() {
          var cid = $('#postAs').val();
          saveOrEditPost(qid, cid, $(html).find('textarea').val(), pid);
          box.destroy();
        });
        box.setContent(html);
      });
    });
    box.setup();
    clearPreloader();
  });
}